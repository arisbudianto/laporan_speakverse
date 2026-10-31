<?php

namespace App\Http\Controllers;

use App\Models\LearningEvent;
use App\Models\VocabularyPretest;
use App\Models\VocabularyPretestResult;
use App\Services\Learning\AdaptationPolicy;
use App\Services\Learning\CefrBandResolver;
use App\Services\Learning\DiagnosticEngine;
use App\Services\Learning\InteractionLogger;
use App\Services\Learning\ScaffoldingEngine;
use App\Services\Learning\TeacherOverrideService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class VocabularyPretestController extends Controller
{
    public function index(InteractionLogger $interactionLogger): View
    {
        $questions = VocabularyPretest::where(
            'category',
            'vocabulary'
        )
            ->inRandomOrder()
            ->take(20)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Modul 1 — Interaction Logger: buka sesi
        |--------------------------------------------------------------------------
        |
        | Vocabulary Pretest tidak terikat ke satu Lesson, jadi
        | lesson di-null-kan (lihat migration
        | make_lesson_id_nullable_on_learning_events_table). Kalau
        | tidak ada soal sama sekali, tidak perlu buka sesi.
        */
        $quizSessionId = $questions->isNotEmpty()
            ? $interactionLogger->startSession(
                Auth::id(),
                null,
                'vocabulary'
            )
            : null;

        return view(
            'vocabulary.pretest',
            [
                'questions' => $questions,
                'quizSessionId' => $quizSessionId,
            ]
        );
    }

    /**
     * Alur LAMA: submit sekali di akhir lewat form biasa (bukan
     * AJAX). TIDAK diubah perilakunya sama sekali — hanya
     * direfactor secara internal supaya memakai helper bersama
     * saveVocabularySubmission() (dipakai juga oleh finish() di
     * Modul 5), sama seperti StudentReadingController::complete().
     */
    public function submit(Request $request, InteractionLogger $interactionLogger, DiagnosticEngine $diagnosticEngine): RedirectResponse
    {
        $validated = $request->validate([
            'answers' => ['required', 'array', 'min:1'],
            'answers.*' => ['required', 'in:A,B,C,D,E'],
            'quiz_session_id' => ['nullable', 'string', 'max:36'],
            'events_json' => ['nullable', 'string'],
        ]);

        $quizSessionId = $validated['quiz_session_id'] ?? null;

        if ($quizSessionId) {
            $events = json_decode($validated['events_json'] ?? '[]', true);

            if (is_array($events)) {
                foreach ($events as $event) {
                    if (! isset($event['question_id'])) {
                        continue;
                    }

                    $interactionLogger->logSelection(
                        $quizSessionId,
                        Auth::id(),
                        null,
                        'vocabulary',
                        (int) $event['question_id'],
                        $event['selected_answer'] ?? null,
                        null,
                        isset($event['response_ms']) ? (int) $event['response_ms'] : null
                    );
                }
            }
        }

        $questionIds = array_keys($validated['answers']);

        $questions = VocabularyPretest::query()
            ->whereIn('id', $questionIds)
            ->get();

        $resolvedAnswers = [];

        foreach ($questions as $question) {
            $answer = $validated['answers'][$question->id] ?? null;
            $isCorrect = $answer !== null && $question->correct_answer === $answer;

            if ($quizSessionId && $answer !== null) {
                $diagnosis = $diagnosticEngine->classify($question, $answer);

                $interactionLogger->enrichAttempt(
                    $quizSessionId,
                    $question->id,
                    $isCorrect,
                    $diagnosis['error_code'],
                    $diagnosis['error_confidence'],
                    $diagnosis['classifier']
                );
            }

            $resolvedAnswers[$question->id] = [
                'selected' => $answer,
                'is_correct' => $isCorrect,
            ];
        }

        $result = $this->saveVocabularySubmission(
            $questions,
            $resolvedAnswers,
            $quizSessionId,
            null,
            $interactionLogger
        );

        return back()->with(
            'success',
            'Your score : ' . $result->score
        );
    }

    /**
     * Saran perbaikan Modul 5 — Halaman Pembahasan.
     *
     * Vocabulary Pretest tidak punya tabel jawaban tersendiri (beda
     * dari Reading yang punya AssessmentAnswer) — direkonstruksi
     * SELURUHNYA dari learning_events (Modul 1) lewat quiz_session_id
     * yang tersimpan di hasil ini.
     */
    public function review(VocabularyPretestResult $result): View
    {
        abort_unless($result->user_id === Auth::id(), 403);

        $items = [];

        if ($result->quiz_session_id) {
            $firstAttempts = LearningEvent::query()
                ->where('quiz_session_id', $result->quiz_session_id)
                ->whereIn('event_type', ['select', 'revise'])
                ->where('attempt_no', 1)
                ->get()
                ->keyBy('question_id');

            $questions = VocabularyPretest::query()
                ->whereIn('id', $firstAttempts->keys())
                ->get()
                ->keyBy('id');

            foreach ($firstAttempts as $questionId => $attempt) {
                $question = $questions->get($questionId);

                if (! $question) {
                    // Soal mungkin sudah dihapus admin sejak siswa
                    // mengerjakan — lewati saja, jangan sampai
                    // halaman pembahasan error total.
                    continue;
                }

                $items[] = [
                    'question' => $question,
                    'selected' => $attempt->selected_answer,
                    'is_correct' => (bool) $attempt->is_correct,
                    'error_label' => $this->friendlyErrorLabel($attempt->error_code),
                ];
            }
        }

        return view(
            'vocabulary.review',
            [
                'result' => $result,
                // quiz_session_id null (hasil dari SEBELUM perbaikan
                // ini) berarti tidak ada data untuk direkonstruksi —
                // ditampilkan sebagai pesan di view, bukan error.
                'hasSessionData' => $result->quiz_session_id !== null,
                'items' => $items,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Modul 5 — In-Quiz Tutor UX
    |--------------------------------------------------------------------------
    |
    | check(), hint(), dan finish() di bawah ini menerapkan pola yang
    | SAMA PERSIS dengan StudentReadingController — lihat komentar di
    | sana untuk penjelasan lengkap tiap keputusan desain. submit() di
    | atas TIDAK disentuh perilakunya, tetap berfungsi sebagai
    | cadangan.
    */

    /**
     * Saran perbaikan Modul 1: dipanggil klien begitu satu soal
     * tampil di layar, supaya response_ms di check() bisa dihitung
     * dari jam server. Lihat penjelasan lengkap di
     * StudentReadingController::markShown().
     */
    public function markShown(Request $request, InteractionLogger $interactionLogger): JsonResponse
    {
        $validated = $request->validate([
            'quiz_session_id' => ['required', 'string', 'max:36'],
            'question_id' => ['required', 'integer'],
        ]);

        $interactionLogger->markQuestionShown($validated['quiz_session_id'], $validated['question_id']);

        return response()->json(['success' => true]);
    }

    /**
     * Saran perbaikan Modul 1: dipanggil lewat navigator.sendBeacon()
     * saat siswa menutup tab / pindah halaman di tengah pretest.
     */
    public function abandon(Request $request, InteractionLogger $interactionLogger): JsonResponse
    {
        $validated = $request->validate([
            'quiz_session_id' => ['required', 'string', 'max:36'],
        ]);

        $interactionLogger->logAbandon($validated['quiz_session_id'], Auth::id(), null, 'vocabulary');

        return response()->json(['success' => true]);
    }

    /**
     * Modul 5: cek satu jawaban tanpa mengakhiri pretest.
     */
    public function check(
        Request $request,
        InteractionLogger $interactionLogger,
        DiagnosticEngine $diagnosticEngine,
        TeacherOverrideService $overrideService
    ): JsonResponse {
        $validated = $request->validate([
            'quiz_session_id' => ['required', 'string', 'max:36'],
            'question_id' => ['required', 'integer'],
            'selected_answer' => ['required', 'in:A,B,C,D,E'],
            'response_ms' => ['nullable', 'integer', 'min:0'],
        ]);

        $question = VocabularyPretest::query()
            ->where('category', 'vocabulary')
            ->find($validated['question_id']);

        if (! $question) {
            return response()->json([
                'message' => 'Question not found.',
            ], 404);
        }

        $maxChecks = (int) config('learning.scoring.max_checks_per_question', 3);

        $checksUsed = LearningEvent::query()
            ->where('quiz_session_id', $validated['quiz_session_id'])
            ->where('question_id', $question->id)
            ->whereIn('event_type', ['select', 'revise'])
            ->count();

        if ($checksUsed >= $maxChecks) {
            return response()->json([
                'message' => 'Maximum checks reached for this question.',
                'checks_used' => $checksUsed,
                'checks_remaining' => 0,
                'locked' => true,
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Modul 6 — Teacher HITL Override: wajib minta hint sebelum
        | RE-check (percobaan pertama tetap selalu boleh langsung).
        |--------------------------------------------------------------------------
        */
        if ($checksUsed > 0) {
            $override = $overrideService->resolveForStudent(Auth::user());

            if ($override && $override['require_hint_before_recheck']) {
                $hintAlreadyRequested = LearningEvent::query()
                    ->where('quiz_session_id', $validated['quiz_session_id'])
                    ->where('question_id', $question->id)
                    ->where('event_type', 'hint_request')
                    ->exists();

                if (! $hintAlreadyRequested) {
                    return response()->json([
                        'message' => 'Your teacher requires you to view a hint before checking this question again.',
                        'require_hint_first' => true,
                        'checks_used' => $checksUsed,
                        'checks_remaining' => max(0, $maxChecks - $checksUsed),
                        'locked' => false,
                    ], 422);
                }
            }
        }

        $correctAnswer = strtoupper(trim((string) $question->correct_answer));
        $selectedAnswer = strtoupper(trim((string) $validated['selected_answer']));
        $isCorrect = $selectedAnswer === $correctAnswer;

        // Saran perbaikan Modul 1: response_ms dari jam server.
        $responseMs = $interactionLogger->resolveResponseMs(
            $validated['quiz_session_id'],
            $question->id,
            $validated['response_ms'] ?? null
        );

        $interactionLogger->logSelection(
            $validated['quiz_session_id'],
            Auth::id(),
            null,
            'vocabulary',
            $question->id,
            $selectedAnswer,
            $isCorrect,
            $responseMs
        );

        $diagnosis = $diagnosticEngine->classify($question, $selectedAnswer);

        $interactionLogger->enrichAttempt(
            $validated['quiz_session_id'],
            $question->id,
            $isCorrect,
            $diagnosis['error_code'],
            $diagnosis['error_confidence'],
            $diagnosis['classifier']
        );

        $newChecksUsed = $checksUsed + 1;
        $checksRemaining = max(0, $maxChecks - $newChecksUsed);

        return response()->json([
            'is_correct' => $isCorrect,
            'checks_used' => $newChecksUsed,
            'checks_remaining' => $checksRemaining,
            'locked' => $isCorrect || $checksRemaining === 0,
        ]);
    }

    /**
     * Modul 5: minta bantuan untuk satu soal vocabulary.
     */
    public function hint(
        Request $request,
        AdaptationPolicy $adaptationPolicy,
        ScaffoldingEngine $scaffoldingEngine,
        InteractionLogger $interactionLogger,
        TeacherOverrideService $overrideService,
        CefrBandResolver $cefrBandResolver
    ): JsonResponse {
        $validated = $request->validate([
            'quiz_session_id' => ['required', 'string', 'max:36'],
            'question_id' => ['required', 'integer'],
            'socratic_answer' => ['nullable', 'string', 'max:1000'],
            'socratic_step' => ['nullable', 'integer', 'min:0'],
        ]);

        $question = VocabularyPretest::query()
            ->where('category', 'vocabulary')
            ->find($validated['question_id']);

        if (! $question) {
            return response()->json([
                'message' => 'Question not found.',
            ], 404);
        }

        $sessionId = $validated['quiz_session_id'];

        // Modul 6 — Teacher HITL Override: dibaca SEBELUM Modul 4
        // memutuskan level.
        $override = $overrideService->resolveForStudent(Auth::user());
        $forceTemplateOnly = (bool) ($override['disable_llm'] ?? false);

        // Modul 4: level bantuan saat ini. lessonId null karena
        // Vocabulary Pretest tidak terikat Lesson.
        $decision = $adaptationPolicy->resolveHintLevel(
            $sessionId,
            $question->id,
            null,
            'vocabulary',
            $override['level_override'] ?? null,
            // Saran perbaikan Modul 4: fading lintas sesi.
            Auth::id()
        );
        $level = $decision['level'];

        $priorHints = LearningEvent::query()
            ->where('quiz_session_id', $sessionId)
            ->where('question_id', $question->id)
            ->where('event_type', 'hint_request')
            ->orderBy('id')
            ->get();

        if ($level === 3 && $request->filled('socratic_answer')) {
            $interactionLogger->logHintRequest(
                $sessionId,
                Auth::id(),
                null,
                'vocabulary',
                $question->id,
                $level,
                [
                    'type' => 'socratic_mini_answer',
                    'step' => $validated['socratic_step'] ?? null,
                    'answer' => $validated['socratic_answer'],
                ]
            );
        }

        $latestAttempt = LearningEvent::query()
            ->where('quiz_session_id', $sessionId)
            ->where('question_id', $question->id)
            ->whereIn('event_type', ['select', 'revise'])
            ->latest('id')
            ->first();

        // Modul 3 guardrail: correct_answer/correct_answer_text HANYA
        // untuk pemeriksaan kebocoran internal, TIDAK PERNAH dikirim
        // ke prompt LLM dan TIDAK PERNAH dikembalikan mentah di
        // response JSON di bawah.
        $context = [
            'skill' => 'vocabulary',
            'question_id' => $question->id,
            'text' => (string) ($question->text_span ?? ''),
            'question' => $question->question,
            'options' => [
                'A' => $question->option_a,
                'B' => $question->option_b,
                'C' => $question->option_c,
                'D' => $question->option_d,
                'E' => $question->option_e,
            ],
            'error_code' => $latestAttempt->error_code ?? null,
            'level' => $level,
            'hint_history' => $priorHints
                ->filter(fn ($h) => ($h->payload['type'] ?? null) === 'hint_text_shown')
                ->map(fn ($h) => $h->payload['hint_text'] ?? null)
                ->filter()
                ->values()
                ->all(),
            // Saran perbaikan: sebelumnya SELALU null.
            'cefr_band' => $cefrBandResolver->resolveForStudent(Auth::user()),
            'correct_answer' => $question->correct_answer,
            'correct_answer_text' => $question->{'option_' . strtolower((string) $question->correct_answer)},
        ];

        if ($level === 3) {
            return $this->handleSocraticHint(
                $context,
                $priorHints,
                $decision,
                $scaffoldingEngine,
                $interactionLogger,
                $sessionId,
                $question,
                $forceTemplateOnly,
                $override['policy_ids'] ?? [],
                Auth::id()
            );
        }

        $result = $scaffoldingEngine->generate($context, $forceTemplateOnly, Auth::id());

        $interactionLogger->logHintRequest(
            $sessionId,
            Auth::id(),
            null,
            'vocabulary',
            $question->id,
            $level,
            [
                'type' => 'hint_text_shown',
                'hint_text' => $result['hint_text'],
                'policy_version' => $decision['policy_version'],
                'source' => $result['source'],
                'reasons' => $decision['reasons'],
                'teacher_policy_ids' => $override['policy_ids'] ?? [],
            ]
        );

        return response()->json([
            'level' => $level,
            'hint_text' => $result['hint_text'],
            'socratic_question' => null,
            'socratic_complete' => null,
            'error_hint_label' => $this->friendlyErrorLabel($context['error_code']),
        ]);
    }

    private function handleSocraticHint(
        array $context,
        $priorHints,
        array $decision,
        ScaffoldingEngine $scaffoldingEngine,
        InteractionLogger $interactionLogger,
        string $sessionId,
        VocabularyPretest $question,
        bool $forceTemplateOnly = false,
        array $teacherPolicyIds = [],
        ?int $userId = null
    ): JsonResponse {
        $result = $scaffoldingEngine->generate($context, $forceTemplateOnly, $userId);
        $questions = $result['socratic_questions'];

        $answeredSteps = $priorHints
            ->filter(fn ($h) => ($h->payload['type'] ?? null) === 'socratic_mini_answer')
            ->count();

        $nextIndex = $answeredSteps;

        if ($questions === [] || $nextIndex >= count($questions)) {
            $interactionLogger->logHintRequest(
                $sessionId,
                Auth::id(),
                null,
                'vocabulary',
                $question->id,
                3,
                [
                    'type' => 'socratic_complete',
                    'policy_version' => $decision['policy_version'],
                    'teacher_policy_ids' => $teacherPolicyIds,
                ]
            );

            return response()->json([
                'level' => 3,
                'hint_text' => null,
                'socratic_question' => null,
                'socratic_complete' => true,
                'error_hint_label' => $this->friendlyErrorLabel($context['error_code']),
            ]);
        }

        $interactionLogger->logHintRequest(
            $sessionId,
            Auth::id(),
            null,
            'vocabulary',
            $question->id,
            3,
            [
                'type' => 'socratic_question_shown',
                'step' => $nextIndex,
                'question_text' => $questions[$nextIndex],
                'policy_version' => $decision['policy_version'],
                'source' => $result['source'],
                'teacher_policy_ids' => $teacherPolicyIds,
            ]
        );

        return response()->json([
            'level' => 3,
            'hint_text' => null,
            'socratic_question' => $questions[$nextIndex],
            'socratic_step' => $nextIndex,
            'socratic_total' => count($questions),
            'socratic_complete' => false,
            'error_hint_label' => $this->friendlyErrorLabel($context['error_code']),
        ]);
    }

    private function friendlyErrorLabel(?string $errorCode): ?string
    {
        return match ($errorCode) {
            'lexical' => 'Soal ini menguji arti kata sesuai konteks.',
            'inferential' => 'Soal ini meminta kesimpulan dari konteks.',
            'syntactic' => 'Soal ini tentang bentuk kata/struktur kalimat.',
            'context_misconception' => 'Soal ini menguji ketelitian, bukan pengetahuan umum.',
            default => null,
        };
    }

    /**
     * Modul 5: akhiri pretest SETELAH siswa mengerjakan lewat alur
     * check()/hint() per soal — bukan submit-sekali seperti submit()
     * di atas. Skor diambil dari attempt_no=1 (percobaan PERTAMA),
     * sesuai config('learning.scoring.formula').
     */
    public function finish(Request $request, InteractionLogger $interactionLogger): JsonResponse
    {
        $validated = $request->validate([
            'quiz_session_id' => ['required', 'string', 'max:36'],
            // Daftar ID soal yang ditampilkan di sesi ini (diambil
            // dari halaman index()) — Vocabulary Pretest tidak punya
            // "material" tetap seperti Reading, jadi klien yang
            // memberi tahu soal mana saja yang termasuk sesi ini.
            'question_ids' => ['required', 'array', 'min:1'],
            'question_ids.*' => ['integer'],
        ]);

        $quizSessionId = $validated['quiz_session_id'];

        $questions = VocabularyPretest::query()
            ->whereIn('id', $validated['question_ids'])
            ->get();

        $firstAttempts = LearningEvent::query()
            ->where('quiz_session_id', $quizSessionId)
            ->whereIn('event_type', ['select', 'revise'])
            ->where('attempt_no', 1)
            ->get()
            ->keyBy('question_id');

        $resolvedAnswers = [];

        foreach ($questions as $question) {
            $attempt = $firstAttempts->get($question->id);

            $resolvedAnswers[$question->id] = [
                'selected' => $attempt->selected_answer ?? null,
                'is_correct' => (bool) ($attempt->is_correct ?? false),
            ];
        }

        $feedback = $this->buildDynamicFeedback($quizSessionId, $questions);

        $result = $this->saveVocabularySubmission(
            $questions,
            $resolvedAnswers,
            $quizSessionId,
            $feedback,
            $interactionLogger
        );

        return response()->json([
            'success' => true,
            'score' => $result->score,
            'result_id' => $result->id,
            // Saran perbaikan Modul 5 — Halaman Pembahasan.
            'review_url' => route('vocabulary.pretest.review', $result),
            'message' => $feedback,
        ]);
    }

    /**
     * Modul 5: inti penyimpanan hasil — dipakai BERSAMA oleh submit()
     * (alur lama) dan finish() (alur baru). Mengembalikan model
     * VocabularyPretestResult (bukan cuma skor) supaya pemanggil
     * bisa membuat link ke halaman pembahasan (lihat review()).
     *
     * @param \Illuminate\Support\Collection<int, VocabularyPretest> $questions
     * @param array<int, array{selected: ?string, is_correct: bool}> $resolvedAnswers
     */
    /**
     * Saran perbaikan Modul 5 — Halaman Pembahasan: dikembalikan
     * sebagai model (bukan cuma int skor seperti sebelumnya) supaya
     * pemanggil bisa membuat link ke halaman pembahasan.
     */
    private function saveVocabularySubmission(
        $questions,
        array $resolvedAnswers,
        ?string $quizSessionId,
        ?string $feedback,
        InteractionLogger $interactionLogger
    ): VocabularyPretestResult {
        $score = 0;

        foreach ($questions as $question) {
            $resolved = $resolvedAnswers[$question->id] ?? ['selected' => null, 'is_correct' => false];

            if ($resolved['is_correct']) {
                $score += 5;
            }
        }

        $result = VocabularyPretestResult::create([
            'user_id' => Auth::id(),
            'quiz_session_id' => $quizSessionId,
            'score' => $score,
            'feedback' => $feedback,
        ]);

        if ($quizSessionId) {
            $interactionLogger->logSubmit(
                $quizSessionId,
                Auth::id(),
                null,
                'vocabulary',
                [
                    'score' => $score,
                ]
            );
        }

        return $result;
    }

    /**
     * Modul 5: feedback dinamis — lihat penjelasan lengkap & catatan
     * bug yang pernah terjadi di
     * StudentReadingController::buildDynamicFeedback(), logikanya
     * identik di sini.
     *
     * @param \Illuminate\Support\Collection<int, VocabularyPretest> $questions
     */
    private function buildDynamicFeedback(string $quizSessionId, $questions): string
    {
        $totalQuestions = $questions->count();

        $firstAttempts = LearningEvent::query()
            ->where('quiz_session_id', $quizSessionId)
            ->whereIn('event_type', ['select', 'revise'])
            ->where('attempt_no', 1)
            ->get();

        $checkedQuestionIds = $firstAttempts->pluck('question_id')->unique();
        $uncheckedCount = max(0, $totalQuestions - $checkedQuestionIds->count());
        $wrongCheckedCount = $firstAttempts->where('is_correct', false)->count();
        $totalNeedsWork = $wrongCheckedCount + $uncheckedCount;

        $hintCountsByLevel = LearningEvent::query()
            ->where('quiz_session_id', $quizSessionId)
            ->where('event_type', 'hint_request')
            ->whereNotNull('hint_level_shown')
            ->selectRaw('hint_level_shown, count(*) as total')
            ->groupBy('hint_level_shown')
            ->pluck('total', 'hint_level_shown');

        if ($totalQuestions > 0 && $totalNeedsWork === 0) {
            $base = "Semua {$totalQuestions} soal benar di percobaan pertama — kerja bagus!";
        } else {
            $wrongFirstAttemptsByCode = $firstAttempts
                ->where('is_correct', false)
                ->whereNotNull('error_code')
                ->groupBy('error_code')
                ->map(fn ($group) => $group->count());

            if ($wrongFirstAttemptsByCode->isEmpty()) {
                $base = $totalQuestions > 0
                    ? "{$totalNeedsWork}/{$totalQuestions} soal masih perlu diperbaiki."
                    : 'Pretest selesai.';
            } else {
                $labels = [
                    'lexical' => 'soal tentang arti kata (lexical)',
                    'inferential' => 'soal yang butuh menyimpulkan dari konteks',
                    'syntactic' => 'soal tentang bentuk kata/struktur (syntactic)',
                    'context_misconception' => 'soal yang butuh ketelitian',
                ];

                $strategies = [
                    'lexical' => 'Coba baca kalimat contohnya secara penuh dulu sebelum menentukan arti kata itu.',
                    'inferential' => 'Coba kaitkan kata itu dengan konteks kalimat di sekitarnya sebelum memilih.',
                    'syntactic' => 'Perhatikan bentuk kata (kata benda/kerja/sifat) yang cocok untuk kalimat itu.',
                    'context_misconception' => 'Coba fokus pada konteks yang diberikan, bukan makna umum yang biasa kamu pakai.',
                ];

                // Saran perbaikan Modul 5: sebutkan SEMUA jenis error
                // yang ditemukan (diurutkan dari paling sering),
                // bukan cuma satu yang paling dominan.
                $sortedByCount = $wrongFirstAttemptsByCode->sortDesc();

                $breakdownParts = [];
                foreach ($sortedByCount as $code => $count) {
                    $label = $labels[$code] ?? $code;
                    $breakdownParts[] = "{$count}x {$label}";
                }
                $breakdown = implode(', ', $breakdownParts);

                $dominantCode = $sortedByCount->keys()->first();
                $strategy = $strategies[$dominantCode] ?? '';

                $base = "{$totalNeedsWork}/{$totalQuestions} soal masih perlu diperbaiki ({$breakdown}). {$strategy}";
            }
        }

        $hintParts = [];
        foreach ([1, 2, 3] as $level) {
            $count = (int) ($hintCountsByLevel[$level] ?? 0);
            if ($count > 0) {
                $hintParts[] = "{$count} hint L{$level}";
            }
        }

        if ($hintParts !== []) {
            $base .= ' Kamu memakai ' . implode(' dan ', $hintParts) . '.';
        }

        return $base;
    }
}
