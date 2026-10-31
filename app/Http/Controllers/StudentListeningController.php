<?php

namespace App\Http\Controllers;

use App\Models\AssessmentAnswer;
use App\Models\AssessmentSubmission;
use App\Models\Lesson;
use App\Models\LearningEvent;
use App\Models\ListeningMaterial;
use App\Models\ListeningQuestion;
use App\Models\UserLessonProgress;
use App\Services\GamificationService;
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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StudentListeningController extends Controller
{
    /**
     * Menampilkan materi listening.
     */
    public function listening(Lesson $lesson): View|RedirectResponse
    {
        $material = ListeningMaterial::with('questions')
            ->where('lesson_id', $lesson->id)
            ->first();

        if (! $material) {
            return redirect()
                ->route('missions')
                ->with('error', 'Listening material is not available yet. Please wait until the admin adds it.');
        }

        return view('missions.listening.index', [
            'lesson' => $lesson,
            'material' => $material,
        ]);
    }

    /**
     * Menampilkan halaman quiz listening.
     *
     * Modul 1: sesi kuis dibuka di sini (sebelumnya listening TIDAK
     * PERNAH memanggil InteractionLogger sama sekali).
     */
    public function quiz(Lesson $lesson, InteractionLogger $interactionLogger): View|RedirectResponse
    {
        $material = ListeningMaterial::with([
            'questions' => function ($query) {
                $query->orderBy('id');
            },
        ])
            ->where('lesson_id', $lesson->id)
            ->first();

        if (! $material) {
            return redirect()
                ->route('missions')
                ->with('error', 'Listening quiz is not available yet. Please wait until the admin adds it.');
        }

        $quizSessionId = $material->questions->isNotEmpty()
            ? $interactionLogger->startSession(Auth::id(), $lesson, 'listening')
            : null;

        return view('missions.listening.quiz', [
            'lesson' => $lesson,
            'material' => $material,
            'questions' => $material->questions,
            'quizSessionId' => $quizSessionId,
        ]);
    }

    /**
     * Alur LAMA: submit sekali di akhir. TIDAK diubah perilakunya —
     * hanya direfactor secara internal supaya memakai helper bersama
     * saveListeningSubmission() (dipakai juga oleh finish()), sama
     * seperti StudentReadingController::complete().
     */
    public function complete(
        Request $request,
        Lesson $lesson,
        GamificationService $gamificationService,
        InteractionLogger $interactionLogger
    ): JsonResponse {
        $validated = $request->validate([
            'answers' => ['required', 'array'],
            'answers.*' => ['required', 'string', 'in:A,B,C,D,E'],
        ]);

        $material = ListeningMaterial::with([
            'questions' => function ($query) {
                $query->orderBy('id');
            },
        ])
            ->where('lesson_id', $lesson->id)
            ->firstOrFail();

        $answers = $validated['answers'];

        $missingQuestions = $material->questions->filter(
            fn ($question) => ! array_key_exists($question->id, $answers)
        );

        if ($missingQuestions->isNotEmpty()) {
            throw ValidationException::withMessages([
                'answers' => 'Semua soal wajib dijawab sebelum quiz dikirim.',
            ]);
        }

        $resolvedAnswers = [];

        foreach ($material->questions as $question) {
            $selectedAnswer = strtoupper(trim((string) $answers[$question->id]));
            $correctAnswer = strtoupper(trim((string) $question->correct_answer));
            $isCorrect = $selectedAnswer === $correctAnswer;

            $resolvedAnswers[$question->id] = [
                'selected' => $selectedAnswer,
                'is_correct' => $isCorrect,
            ];
        }

        return $this->saveListeningSubmission(
            $lesson,
            $material,
            $resolvedAnswers,
            null,
            'Listening quiz completed.',
            $interactionLogger,
            $gamificationService
        );
    }

    /**
     * Modul 5 (perluasan ke Listening): akhiri kuis SETELAH siswa
     * mengerjakan lewat check()/hint() per soal. Skor diambil dari
     * attempt_no=1 (percobaan PERTAMA), sesuai
     * config('learning.scoring.formula').
     */
    public function finish(Request $request, Lesson $lesson, GamificationService $gamificationService, InteractionLogger $interactionLogger): JsonResponse
    {
        $validated = $request->validate([
            'quiz_session_id' => ['required', 'string', 'max:36'],
        ]);

        $quizSessionId = $validated['quiz_session_id'];

        $material = ListeningMaterial::with([
            'questions' => function ($query) {
                $query->orderBy('id');
            },
        ])
            ->where('lesson_id', $lesson->id)
            ->firstOrFail();

        $firstAttempts = LearningEvent::query()
            ->where('quiz_session_id', $quizSessionId)
            ->whereIn('event_type', ['select', 'revise'])
            ->where('attempt_no', 1)
            ->get()
            ->keyBy('question_id');

        $resolvedAnswers = [];

        foreach ($material->questions as $question) {
            $attempt = $firstAttempts->get($question->id);

            $resolvedAnswers[$question->id] = [
                'selected' => $attempt->selected_answer ?? null,
                'is_correct' => (bool) ($attempt->is_correct ?? false),
            ];
        }

        $feedback = $this->buildDynamicFeedback($quizSessionId, $material);

        return $this->saveListeningSubmission(
            $lesson,
            $material,
            $resolvedAnswers,
            $quizSessionId,
            $feedback,
            $interactionLogger,
            $gamificationService
        );
    }

    /**
     * Modul 5: halaman pembahasan setelah Finish (atau setelah
     * complete() lama). Sama persis dengan
     * StudentReadingController::review() — lihat penjelasan lengkap
     * di sana.
     */
    public function review(Lesson $lesson, AssessmentSubmission $submission): View
    {
        abort_unless($submission->user_id === Auth::id(), 403);
        abort_unless(
            $submission->lesson_id === $lesson->id && $submission->skill === 'listening',
            404
        );

        $submission->load('answers');

        $material = ListeningMaterial::with([
            'questions' => function ($query) {
                $query->orderBy('id');
            },
        ])
            ->where('lesson_id', $lesson->id)
            ->orderBy('id')
            ->firstOrFail();

        $answersByQuestion = $submission->answers->keyBy('question_id');

        // error_code hanya tersedia untuk submission yang punya
        // quiz_session_id (dibuat lewat alur Modul 5/check-hint-finish).
        // Submission lama (sebelum kolom ini ada) tetap bisa
        // ditinjau, cuma tanpa label jenis kesalahan.
        $errorCodesByQuestion = collect();
        if ($submission->quiz_session_id) {
            $errorCodesByQuestion = LearningEvent::query()
                ->where('quiz_session_id', $submission->quiz_session_id)
                ->whereIn('event_type', ['select', 'revise'])
                ->where('attempt_no', 1)
                ->whereNotNull('error_code')
                ->pluck('error_code', 'question_id');
        }

        $items = [];
        foreach ($material->questions as $question) {
            $answer = $answersByQuestion->get($question->id);
            $errorCode = $errorCodesByQuestion->get($question->id);

            $items[] = [
                'question' => $question,
                'selected' => $answer->selected_option ?? null,
                'is_correct' => (bool) ($answer->is_correct ?? false),
                'error_label' => $this->friendlyErrorLabel($errorCode),
            ];
        }

        return view(
            'missions.listening.review',
            [
                'lesson' => $lesson,
                'submission' => $submission,
                'items' => $items,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Modul 5 — In-Quiz Tutor UX (perluasan dari Reading ke Listening)
    |--------------------------------------------------------------------------
    */

    /**
     * Saran perbaikan Modul 1: dipanggil klien begitu satu soal
     * tampil di layar, supaya response_ms bisa dihitung dari jam
     * server.
     */
    public function markShown(Request $request, Lesson $lesson, InteractionLogger $interactionLogger): JsonResponse
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
     * saat siswa menutup tab / pindah halaman di tengah kuis.
     */
    public function abandon(Request $request, Lesson $lesson, InteractionLogger $interactionLogger): JsonResponse
    {
        $validated = $request->validate([
            'quiz_session_id' => ['required', 'string', 'max:36'],
        ]);

        $interactionLogger->logAbandon($validated['quiz_session_id'], Auth::id(), $lesson, 'listening');

        return response()->json(['success' => true]);
    }

    /**
     * Modul 5: cek satu jawaban tanpa mengakhiri kuis.
     */
    public function check(
        Request $request,
        Lesson $lesson,
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

        $question = ListeningQuestion::query()
            ->whereHas('material', function ($query) use ($lesson) {
                $query->where('lesson_id', $lesson->id);
            })
            ->find($validated['question_id']);

        if (! $question) {
            return response()->json([
                'message' => 'Question not found for this lesson.',
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

        $responseMs = $interactionLogger->resolveResponseMs(
            $validated['quiz_session_id'],
            $question->id,
            $validated['response_ms'] ?? null
        );

        $interactionLogger->logSelection(
            $validated['quiz_session_id'],
            Auth::id(),
            $lesson,
            'listening',
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
     * Modul 5: minta bantuan untuk satu soal.
     */
    public function hint(
        Request $request,
        Lesson $lesson,
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

        $question = ListeningQuestion::with('material')
            ->whereHas('material', function ($query) use ($lesson) {
                $query->where('lesson_id', $lesson->id);
            })
            ->find($validated['question_id']);

        if (! $question) {
            return response()->json([
                'message' => 'Question not found for this lesson.',
            ], 404);
        }

        $sessionId = $validated['quiz_session_id'];

        $override = $overrideService->resolveForStudent(Auth::user());
        $forceTemplateOnly = (bool) ($override['disable_llm'] ?? false);

        $decision = $adaptationPolicy->resolveHintLevel(
            $sessionId,
            $question->id,
            $lesson->id,
            'listening',
            $override['level_override'] ?? null,
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
                $lesson,
                'listening',
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
        // ke prompt LLM. "text" di sini pakai transkrip audio
        // (ListeningMaterial->passage) sebagai pengganti passage
        // Reading — sama-sama teks konteks yang relevan.
        $context = [
            'skill' => 'listening',
            'question_id' => $question->id,
            'text' => $question->text_span
                ?: Str::limit((string) ($question->material->passage ?? ''), 1500, ''),
            'question' => $question->question,
            'options' => array_filter([
                'A' => $question->option_a,
                'B' => $question->option_b,
                'C' => $question->option_c,
                'D' => $question->option_d,
                'E' => $question->option_e,
            ], fn ($v) => $v !== null && trim((string) $v) !== ''),
            'error_code' => $latestAttempt->error_code ?? null,
            'level' => $level,
            'hint_history' => $priorHints
                ->filter(fn ($h) => ($h->payload['type'] ?? null) === 'hint_text_shown')
                ->map(fn ($h) => $h->payload['hint_text'] ?? null)
                ->filter()
                ->values()
                ->all(),
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
                $lesson,
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
            $lesson,
            'listening',
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
        Lesson $lesson,
        ListeningQuestion $question,
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
                $lesson,
                'listening',
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
            $lesson,
            'listening',
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
            'inferential' => 'Soal ini meminta kesimpulan dari audio.',
            'syntactic' => 'Soal ini tentang struktur atau rujukan dalam kalimat.',
            'context_misconception' => 'Soal ini menguji ketelitian mendengarkan, bukan pengetahuan umum.',
            default => null,
        };
    }

    /**
     * Modul 5: inti penyimpanan hasil — dipakai BERSAMA oleh
     * complete() (alur lama) dan finish() (alur baru).
     */
    private function saveListeningSubmission(
        Lesson $lesson,
        ListeningMaterial $material,
        array $resolvedAnswers,
        ?string $quizSessionId,
        string $feedbackText,
        InteractionLogger $interactionLogger,
        GamificationService $gamificationService
    ): JsonResponse {
        $submission = DB::transaction(function () use ($lesson, $material, $resolvedAnswers, $feedbackText, $quizSessionId) {
            $earnedScore = 0;
            $maximumScore = 0;

            $submission = AssessmentSubmission::create([
                'user_id' => Auth::id(),
                'unit_id' => $lesson->unit_id,
                'lesson_id' => $lesson->id,
                'type' => 'unit',
                'skill' => 'listening',
                'final_score' => 0,
                'status' => 'completed',
                'feedback' => $feedbackText,
                'quiz_session_id' => $quizSessionId,
                'submitted_at' => now(),
            ]);

            foreach ($material->questions as $question) {
                $resolved = $resolvedAnswers[$question->id] ?? ['selected' => null, 'is_correct' => false];
                $selectedAnswer = $resolved['selected'];
                $isCorrect = (bool) $resolved['is_correct'];

                $questionMaximumScore = max(0, (int) $question->score);
                $questionEarnedScore = $isCorrect ? $questionMaximumScore : 0;

                $earnedScore += $questionEarnedScore;
                $maximumScore += $questionMaximumScore;

                AssessmentAnswer::create([
                    'assessment_submission_id' => $submission->id,
                    'question_type' => 'listening',
                    'question_id' => $question->id,
                    'selected_option' => $selectedAnswer,
                    'is_correct' => $isCorrect,
                    'score' => $questionEarnedScore,
                    'max_score' => $questionMaximumScore,
                    'feedback' => $isCorrect ? 'Correct answer.' : 'Incorrect answer.',
                ]);
            }

            $finalScore = $maximumScore > 0
                ? (int) round(($earnedScore / $maximumScore) * 100)
                : 0;

            $submission->update(['final_score' => $finalScore]);

            UserLessonProgress::updateOrCreate(
                [
                    'user_id' => Auth::id(),
                    'lesson_id' => $lesson->id,
                    'skill_type' => 'listening',
                ],
                [
                    'unit_id' => $lesson->unit_id,
                    'status' => 'completed',
                    'score' => $finalScore,
                    'completed_at' => now(),
                ]
            );

            return $submission->fresh();
        });

        if ($quizSessionId) {
            $interactionLogger->logSubmit(
                $quizSessionId,
                Auth::id(),
                $lesson,
                'listening',
                [
                    'final_score' => (int) $submission->final_score,
                    'submission_id' => $submission->id,
                ]
            );
        }

        $gamification = $gamificationService->rewardLessonCompletion(
            Auth::user(),
            $lesson,
            'listening',
            (int) $submission->final_score
        );

        return response()->json([
            'success' => true,
            'score' => (int) $submission->final_score,
            'submission_id' => $submission->id,
            'message' => $feedbackText,
            'review_url' => route('student.listening.review', [$lesson, $submission]),
            'gamification' => $gamification,
        ]);
    }

    /**
     * Modul 5: feedback dinamis, breakdown lengkap (bukan cuma satu
     * jenis error dominan) — lihat penjelasan lengkap di
     * StudentReadingController::buildDynamicFeedback(), logikanya
     * identik di sini.
     */
    private function buildDynamicFeedback(string $quizSessionId, ListeningMaterial $material): string
    {
        $totalQuestions = $material->questions->count();

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
                    : 'Kuis selesai.';
            } else {
                $labels = [
                    'lexical' => 'soal tentang arti kata (lexical)',
                    'inferential' => 'soal inferensial',
                    'syntactic' => 'soal struktur kalimat (syntactic)',
                    'context_misconception' => 'soal yang butuh ketelitian mendengarkan',
                ];

                $strategies = [
                    'lexical' => 'Coba dengarkan lagi konteks kalimatnya sebelum menentukan arti sebuah kata.',
                    'inferential' => 'Coba strategi gabungkan dua petunjuk dari audio sebelum memilih opsi.',
                    'syntactic' => 'Coba telusuri kata ganti/rujukan mundur ke kata benda terdekat sebelum menjawab.',
                    'context_misconception' => 'Coba fokus HANYA pada apa yang benar-benar diucapkan, bukan pengetahuan umum yang kamu miliki.',
                ];

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
