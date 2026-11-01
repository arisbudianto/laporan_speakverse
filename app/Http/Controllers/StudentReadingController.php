<?php

namespace App\Http\Controllers;

use App\Models\AssessmentAnswer;
use App\Models\AssessmentSubmission;
use App\Models\Lesson;
use App\Models\LearningEvent;
use App\Models\ReadingMaterial;
use App\Models\ReadingQuestion;
use App\Models\UserLessonProgress;
use App\Services\GamificationService;
use App\Services\Learning\AdaptationPolicy;
use App\Services\Learning\CefrBandResolver;
use App\Services\Learning\DiagnosticEngine;
use App\Services\Learning\InteractionLogger;
use App\Services\Learning\ScaffoldingEngine;
use App\Services\Learning\TeacherOverrideService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StudentReadingController extends Controller
{
    /**
     * Menampilkan satu reading passage.
     */
    public function reading(
        Lesson $lesson
    ) {
        $material = ReadingMaterial::with([
            'questions' => function ($query) {
                $query->orderBy('id');
            },
        ])
            ->where(
                'lesson_id',
                $lesson->id
            )
            ->orderBy('id')
            ->first();

        if (!$material) {
            return redirect()
                ->route('missions')
                ->with(
                    'error',
                    'Reading material is not available yet. Please wait until the admin adds it.'
                );
        }

        return view(
            'missions.reading.index',
            compact(
                'lesson',
                'material'
            )
        );
    }

    /**
     * Menampilkan semua soal dari reading passage.
     */
    public function quiz(
        Lesson $lesson,
        InteractionLogger $interactionLogger
    ) {
        $material = ReadingMaterial::with([
            'questions' => function ($query) {
                $query->orderBy('id');
            },
        ])
            ->where(
                'lesson_id',
                $lesson->id
            )
            ->orderBy('id')
            ->first();

        if (!$material) {
            return redirect()
                ->route('missions')
                ->with(
                    'error',
                    'Reading quiz is not available yet. Please wait until the admin adds it.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Modul 1 — Interaction Logger: buka sesi kuis
        |--------------------------------------------------------------------------
        |
        | quizSessionId bisa null jika logging gagal (lihat catatan
        | keandalan di InteractionLogger) — halaman kuis tetap harus
        | bisa dibuka siswa meski ini gagal, jadi tidak di-abort di
        | sini. Frontend cukup tidak mengirim event lanjutan kalau
        | sesi null.
        */
        $quizSessionId = $interactionLogger->startSession(
            Auth::id(),
            $lesson,
            'reading'
        );

        return view(
            'missions.reading.quiz',
            [
                'lesson' =>
                    $lesson,

                'material' =>
                    $material,

                'questions' =>
                    $material->questions,

                'quizSessionId' =>
                    $quizSessionId,
            ]
        );
    }

    /**
     * Saran perbaikan Modul 5 — Halaman Pembahasan.
     *
     * Menampilkan hasil satu submission Reading: tiap soal, jawaban
     * siswa, jawaban benar, rationale (Modul 2), dan label ramah
     * error_code kalau salah. Diambil dari AssessmentAnswer (skor)
     * digabung learning_events (error_code, lewat quiz_session_id
     * yang tersimpan di submission).
     */
    public function review(
        Lesson $lesson,
        AssessmentSubmission $submission
    ): View {
        abort_unless($submission->user_id === Auth::id(), 403);
        abort_unless(
            $submission->lesson_id === $lesson->id && $submission->skill === 'reading',
            404
        );

        $submission->load('answers');

        $material = ReadingMaterial::with([
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
            'missions.reading.review',
            [
                'lesson' => $lesson,
                'submission' => $submission,
                'items' => $items,
            ]
        );
    }

    /**
     * Menyimpan jawaban quiz reading Unit 1–4.
     */
    public function complete(
        Request $request,
        Lesson $lesson,
        GamificationService $gamificationService,
        InteractionLogger $interactionLogger,
        DiagnosticEngine $diagnosticEngine
    ): JsonResponse {
        /*
        |--------------------------------------------------------------------------
        | Validasi Jawaban
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate([
                'answers' => [
                    'required',
                    'array',
                    'min:1',
                ],

                'answers.*' => [
                    'required',
                    'in:A,B,C,D,E',
                ],

                /*
                |--------------------------------------------------------------------------
                | Modul 1 — Interaction Logger (opsional)
                |--------------------------------------------------------------------------
                |
                | Nullable/opsional supaya klien lama (atau JS yang
                | gagal memuat) tetap bisa submit jawaban seperti
                | biasa — logging tidak boleh jadi syarat submit.
                */
                'quiz_session_id' => [
                    'nullable',
                    'string',
                    'max:36',
                ],

                'events' => [
                    'nullable',
                    'array',
                ],

                'events.*.question_id' => [
                    'required_with:events',
                    'integer',
                ],

                'events.*.selected_answer' => [
                    'nullable',
                    'string',
                    'max:10',
                ],

                'events.*.response_ms' => [
                    'nullable',
                    'integer',
                    'min:0',
                ],
            ]);

        /*
        |--------------------------------------------------------------------------
        | Modul 1 — Interaction Logger: catat event jawaban
        |--------------------------------------------------------------------------
        |
        | Ini TIDAK mempengaruhi skor sama sekali — logika skor tetap
        | 100% dari $validated['answers'] seperti sebelumnya di bawah.
        | is_correct sengaja null di sini: pada tahap ini (sebelum
        | Modul 5 mengubah kuis jadi "Check per soal") siswa belum
        | tahu benar/salah saat memilih, jadi tidak jujur untuk
        | mengisi is_correct di titik ini.
        */
        $quizSessionId = $validated['quiz_session_id'] ?? null;

        if ($quizSessionId) {
            foreach (($validated['events'] ?? []) as $event) {
                $interactionLogger->logSelection(
                    $quizSessionId,
                    Auth::id(),
                    $lesson,
                    'reading',
                    (int) $event['question_id'],
                    $event['selected_answer'] ?? null,
                    null,
                    isset($event['response_ms']) ? (int) $event['response_ms'] : null
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Ambil Reading Material
        |--------------------------------------------------------------------------
        */

        $material =
            ReadingMaterial::with([
                'questions' => function ($query) {
                    $query->orderBy('id');
                },
            ])
                ->where(
                    'lesson_id',
                    $lesson->id
                )
                ->orderBy('id')
                ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Pastikan seluruh soal sudah dijawab
        |--------------------------------------------------------------------------
        */

        $answers =
            $validated['answers'];

        $missingQuestions =
            $material
                ->questions
                ->filter(
                    function ($question) use ($answers) {
                        return !array_key_exists(
                            $question->id,
                            $answers
                        );
                    }
                );

        if ($missingQuestions->isNotEmpty()) {
            return response()->json(
                [
                    'message' =>
                        'Semua soal wajib dijawab sebelum quiz dikirim.',
                ],
                422
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Modul 5: bangun $resolvedAnswers, lalu simpan lewat helper
        | bersama saveReadingSubmission() (dipakai juga oleh finish()
        | di bawah). Ini REFACTOR MURNI — perilaku eksternal complete()
        | (validasi, format response, teks feedback, rumus skor) SAMA
        | PERSIS seperti sebelumnya, hanya kode internalnya
        | dirapikan supaya tidak duplikat dengan finish().
        |--------------------------------------------------------------------------
        */

        $resolvedAnswers = [];

        foreach ($material->questions as $question) {
            $selected = $answers[$question->id] ?? null;

            $correctAnswer = strtoupper(trim((string) $question->correct_answer));
            $selectedAnswer = $selected !== null
                ? strtoupper(trim((string) $selected))
                : null;

            $isCorrect = $selectedAnswer !== null && $selectedAnswer === $correctAnswer;

            /*
            |--------------------------------------------------------------------------
            | Modul 2 — Diagnostic Engine
            |--------------------------------------------------------------------------
            |
            | Melengkapi attempt di learning_events (Modul 1) dengan
            | hasil penilaian & klasifikasi error. Sama sekali tidak
            | mempengaruhi skor — murni menambah label untuk dashboard
            | guru dan bahan Modul 3/4.
            */
            if ($quizSessionId && $selectedAnswer !== null) {
                $diagnosis = $diagnosticEngine->classify($question, $selectedAnswer);

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
                'selected' => $selectedAnswer,
                'is_correct' => $isCorrect,
            ];
        }

        return $this->saveReadingSubmission(
            $lesson,
            $material,
            $resolvedAnswers,
            $quizSessionId,
            'Reading quiz completed.',
            $interactionLogger,
            $gamificationService
        );
    }

    /**
     * Modul 5: akhiri kuis SETELAH siswa mengerjakan lewat alur
     * check()/hint() per soal (Tahap 5b) — bukan submit-sekali seperti
     * complete() di atas. Tidak menerima 'answers' dari klien sama
     * sekali: skor diambil dari attempt_no=1 (percobaan PERTAMA) yang
     * sudah tercatat di learning_events lewat check(), sesuai
     * keputusan formula skor di config('learning.scoring.formula').
     */
    public function finish(
        Request $request,
        Lesson $lesson,
        GamificationService $gamificationService,
        InteractionLogger $interactionLogger
    ): JsonResponse {
        $validated = $request->validate([
            'quiz_session_id' => ['required', 'string', 'max:36'],
        ]);

        $quizSessionId = $validated['quiz_session_id'];

        $material = ReadingMaterial::with([
            'questions' => function ($query) {
                $query->orderBy('id');
            },
        ])
            ->where('lesson_id', $lesson->id)
            ->orderBy('id')
            ->firstOrFail();

        // Percobaan PERTAMA (attempt_no=1) per soal — sumber
        // kebenaran skor, formula 'first_attempt' (lihat
        // config/learning.php). Soal yang tidak pernah di-Check sama
        // sekali (attempt tidak ada) dihitung salah/0, sama seperti
        // soal kosong di alur complete() lama.
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

        return $this->saveReadingSubmission(
            $lesson,
            $material,
            $resolvedAnswers,
            $quizSessionId,
            $feedback,
            $interactionLogger,
            $gamificationService
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Modul 5 — In-Quiz Tutor UX (Tahap 5a: backend)
    |--------------------------------------------------------------------------
    |
    | check() dan hint() di bawah ini BERDAMPINGAN dengan complete()
    | di atas, TIDAK menggantikannya. quiz.blade.php saat ini masih
    | memakai alur lama (jawab semua → submit sekali via /complete).
    | Endpoint di bawah disiapkan untuk Tahap 5b (UI baru) yang akan
    | memakai keduanya secara per-soal.
    */

    /**
     * Saran perbaikan Modul 1: dipanggil klien begitu satu soal
     * tampil di layar. Server mencatat waktunya SENDIRI (lihat
     * InteractionLogger::markQuestionShown()) supaya response_ms
     * yang dihitung di check() nanti otoritatif dari jam server,
     * bukan angka yang dikirim mentah oleh klien.
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
     * saat siswa menutup tab / pindah halaman DI TENGAH kuis (belum
     * sempat Finish). Endpoint ini SENGAJA sangat sederhana dan
     * toleran — beacon adalah "fire and forget", tidak ada balasan
     * yang benar-benar dibaca klien.
     */
    public function abandon(Request $request, Lesson $lesson, InteractionLogger $interactionLogger): JsonResponse
    {
        $validated = $request->validate([
            'quiz_session_id' => ['required', 'string', 'max:36'],
        ]);

        $interactionLogger->logAbandon($validated['quiz_session_id'], Auth::id(), $lesson, 'reading');

        return response()->json(['success' => true]);
    }

    /**
     * Modul 5: cek satu jawaban tanpa mengakhiri kuis.
     *
     * Menulis ke learning_events lewat InteractionLogger (Modul 1) +
     * DiagnosticEngine (Modul 2) — MEKANISME PERSIS SAMA seperti di
     * complete(), hanya saja dipanggil per soal, real-time, bukan
     * sekaligus di akhir. Sama sekali tidak menyentuh
     * AssessmentSubmission/AssessmentAnswer — itu baru dibuat di
     * complete() (belum diubah di Tahap 5a ini).
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

        $question = ReadingQuestion::query()
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

        // Saran perbaikan Modul 1: response_ms dihitung dari jam
        // SERVER (lewat markQuestionShown() sebelumnya), bukan
        // dipercaya mentah dari klien. Angka dari klien
        // ($validated['response_ms']) hanya jadi cadangan kalau
        // penanda server tidak ada.
        $responseMs = $interactionLogger->resolveResponseMs(
            $validated['quiz_session_id'],
            $question->id,
            $validated['response_ms'] ?? null
        );

        // Modul 1: catat percobaan (select/revise ditentukan otomatis
        // oleh InteractionLogger berdasarkan riwayat, bukan ditebak
        // di sini).
        $interactionLogger->logSelection(
            $validated['quiz_session_id'],
            Auth::id(),
            $lesson,
            'reading',
            $question->id,
            $selectedAnswer,
            $isCorrect,
            $responseMs
        );

        // Modul 2: klasifikasi error kalau salah, lalu lengkapi
        // attempt yang baru saja dicatat (is_correct sudah benar dari
        // logSelection di atas; enrichAttempt melengkapi kolom
        // diagnostiknya).
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
            // Soal dikunci (tidak bisa dicoba lagi) begitu benar,
            // ATAU begitu batas percobaan habis.
            'locked' => $isCorrect || $checksRemaining === 0,
        ]);
    }

    /**
     * Modul 5: minta bantuan untuk satu soal.
     *
     * TIDAK dipanggil otomatis oleh check() — ini tombol terpisah
     * ("I need a hint"), sesuai instruksi dokumen rencana. Memanggil
     * Modul 4 (tentukan level) lalu Modul 3 (hasilkan teks bantuan).
     *
     * error_code MENTAH tidak pernah dikembalikan ke klien di sini —
     * hanya label ramah lewat friendlyErrorLabel().
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
            // Diisi kalau ini permintaan hint LANJUTAN untuk level 3
            // (Socratic), berisi jawaban mini siswa untuk pertanyaan
            // sebelumnya.
            'socratic_answer' => ['nullable', 'string', 'max:1000'],
            'socratic_step' => ['nullable', 'integer', 'min:0'],
        ]);

        $question = ReadingQuestion::with('material')
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

        // Modul 6 — Teacher HITL Override: dibaca SEBELUM Modul 4
        // memutuskan level, sesuai urutan di dokumen rencana
        // ("Service policy dibaca M4 sebelum generate hint").
        $override = $overrideService->resolveForStudent(Auth::user());
        $forceTemplateOnly = (bool) ($override['disable_llm'] ?? false);

        // Modul 4: level bantuan saat ini.
        $decision = $adaptationPolicy->resolveHintLevel(
            $sessionId,
            $question->id,
            $lesson->id,
            'reading',
            $override['level_override'] ?? null,
            // Saran perbaikan Modul 4: userId dikirim supaya fading
            // bisa melihat lintas sesi (hari lain, lesson lain),
            // bukan cuma sesi ini.
            Auth::id()
        );
        $level = $decision['level'];

        $priorHints = LearningEvent::query()
            ->where('quiz_session_id', $sessionId)
            ->where('question_id', $question->id)
            ->where('event_type', 'hint_request')
            ->orderBy('id')
            ->get();

        // Kalau siswa baru saja menjawab satu pertanyaan Socratic,
        // catat jawaban mininya dulu sebagai event tersendiri sebelum
        // menentukan pertanyaan berikutnya.
        if ($level === 3 && $request->filled('socratic_answer')) {
            $interactionLogger->logHintRequest(
                $sessionId,
                Auth::id(),
                $lesson,
                'reading',
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
        // untuk pemeriksaan kebocoran internal di ScaffoldingEngine —
        // TIDAK PERNAH dikirim ke prompt LLM (lihat service itu
        // sendiri) dan TIDAK PERNAH dikembalikan di response JSON di
        // bawah.
        $context = [
            'skill' => 'reading',
            'question_id' => $question->id,
            'text' => $question->text_span
                ?: Str::limit((string) ($question->material->passage ?? ''), 1500, ''),
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
            // Saran perbaikan: sebelumnya SELALU null — sekarang
            // diisi dari skor rata-rata Pre-Test siswa (lihat
            // CefrBandResolver). Null tetap mungkin untuk siswa yang
            // belum pernah mengerjakan Pre-Test sama sekali.
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
            'reading',
            $question->id,
            $level,
            [
                'type' => 'hint_text_shown',
                'hint_text' => $result['hint_text'],
                'policy_version' => $decision['policy_version'],
                'source' => $result['source'],
                'reasons' => $decision['reasons'],
                // Modul 6: "Log siapa yang override" — ID kebijakan
                // guru yang mempengaruhi keputusan level di atas
                // (kosong kalau tidak ada override aktif).
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

    /**
     * Level 3 menampilkan SATU pertanyaan Socratic per permintaan
     * (bukan langsung 2–3 sekaligus), sesuai instruksi UI di dokumen
     * rencana.
     */
    private function handleSocraticHint(
        array $context,
        $priorHints,
        array $decision,
        ScaffoldingEngine $scaffoldingEngine,
        InteractionLogger $interactionLogger,
        string $sessionId,
        Lesson $lesson,
        ReadingQuestion $question,
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
                'reading',
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
            'reading',
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

    /**
     * Modul 5: "Jangan tampilkan error_code mentah ke siswa
     * ('inferential'); ubah jadi bahasa belajar." Ini satu-satunya
     * tempat error_code boleh "bocor" ke klien — dan hanya dalam
     * bentuk kalimat ramah ini, tidak pernah sebagai string mentah.
     */
    private function friendlyErrorLabel(?string $errorCode): ?string
    {
        return match ($errorCode) {
            'lexical' => 'Soal ini menguji arti kata sesuai konteks kalimat.',
            'inferential' => 'Soal ini meminta kesimpulan dari teks.',
            'syntactic' => 'Soal ini tentang struktur atau rujukan dalam kalimat.',
            'context_misconception' => 'Soal ini menguji ketelitian membaca teks, bukan pengetahuan umum.',
            default => null,
        };
    }

    /**
     * Modul 5: inti penyimpanan hasil Reading — dipakai BERSAMA oleh
     * complete() (alur lama, submit sekali) dan finish() (alur baru,
     * per soal). $resolvedAnswers = [question_id => ['selected' =>
     * ?string, 'is_correct' => bool]], sudah ditentukan oleh
     * pemanggil (dari body request untuk complete(), dari
     * learning_events attempt_no=1 untuk finish()) — method ini
     * sendiri tidak peduli DARI MANA jawabannya berasal, cuma
     * menyimpan & menghitung skor.
     */
    private function saveReadingSubmission(
        Lesson $lesson,
        ReadingMaterial $material,
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
                'quiz_session_id' => $quizSessionId,
                'type' => 'unit',
                'skill' => 'reading',
                'final_score' => 0,
                'status' => 'completed',
                'feedback' => $feedbackText,
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
                    'question_type' => 'reading',
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
                    'skill_type' => 'reading',
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
                'reading',
                [
                    'final_score' => (int) $submission->final_score,
                    'submission_id' => $submission->id,
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Gamification Reward
        |--------------------------------------------------------------------------
        |
        | Reading selesai: +30 XP, +10 SpeakCoins. Bonus Unit
        | diberikan otomatis oleh GamificationService kalau Reading
        | adalah skill terakhir yang diselesaikan di Unit ini.
        */

        $gamification = $gamificationService->rewardLessonCompletion(
            Auth::user(),
            $lesson,
            'reading',
            (int) $submission->final_score
        );

        return response()->json([
            'success' => true,
            'score' => (int) $submission->final_score,
            'submission_id' => $submission->id,
            // Saran perbaikan Modul 5 — Halaman Pembahasan.
            'review_url' => route('student.reading.review', [$lesson, $submission]),
            'message' => $feedbackText,

            /*
             * Nantinya dibaca oleh resources/js/gamification.js
             * untuk menampilkan popup reward.
             */
            'gamification' => $gamification,
        ]);
    }

    /**
     * Modul 5: feedback dinamis pengganti string statis "Reading quiz
     * completed." — sesuai contoh di dokumen rencana: "3/8
     * inferensial masih lemah. Kamu memakai 4 hint L1 dan 2 hint L2.
     * Coba strategi gabungkan dua petunjuk sebelum memilih opsi."
     *
     * Dihitung dari learning_events sesi ini: error_code mana yang
     * paling sering muncul di percobaan pertama yang SALAH (bukan
     * error_code mentah yang ditampilkan — selalu lewat label ramah),
     * plus berapa kali tiap level hint dipakai.
     *
     * PENTING (bug yang pernah terjadi & sudah diperbaiki): "tidak
     * ada percobaan salah yang PUNYA error_code" bukan berarti
     * "semua benar" — bisa juga berarti ada yang salah/tidak pernah
     * di-Check sama sekali, tapi soalnya belum dianotasi admin
     * (error_if_wrong belum diisi, lihat Modul 2). Jumlah salah yang
     * SEBENARNYA ($totalNeedsWork) dihitung terpisah dari soal urusan
     * ada/tidaknya label error_code.
     */
    private function buildDynamicFeedback(string $quizSessionId, ReadingMaterial $material): string
    {
        $totalQuestions = $material->questions->count();

        $firstAttempts = LearningEvent::query()
            ->where('quiz_session_id', $quizSessionId)
            ->whereIn('event_type', ['select', 'revise'])
            ->where('attempt_no', 1)
            ->get();

        // Soal yang tidak pernah di-Check sama sekali juga dihitung
        // "masih perlu diperbaiki" — skornya 0 di saveReadingSubmission()
        // (lihat finish()), jadi feedback-nya harus konsisten dengan itu.
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
                // Ada yang salah/belum dikerjakan, tapi belum ada
                // satu pun label error_code untuk soal-soal itu
                // (belum dianotasi admin) — jangan sebut jenis error
                // spesifik kalau memang belum diketahui.
                $base = $totalQuestions > 0
                    ? "{$totalNeedsWork}/{$totalQuestions} soal masih perlu diperbaiki."
                    : 'Kuis selesai.';
            } else {
                $labels = [
                    'lexical' => 'soal tentang arti kata (lexical)',
                    'inferential' => 'soal inferensial',
                    'syntactic' => 'soal struktur kalimat (syntactic)',
                    'context_misconception' => 'soal yang butuh ketelitian membaca',
                ];

                $strategies = [
                    'lexical' => 'Coba baca satu kalimat penuh dulu sebelum menentukan arti sebuah kata — jangan menilai dari kata itu sendiri.',
                    'inferential' => 'Coba strategi gabungkan dua petunjuk dari teks sebelum memilih opsi.',
                    'syntactic' => 'Coba telusuri kata ganti/rujukan mundur ke kata benda terdekat sebelum menjawab.',
                    'context_misconception' => 'Coba fokus HANYA pada apa yang tertulis di teks, bukan pengetahuan umum yang kamu miliki.',
                ];

                // Saran perbaikan Modul 5: sebutkan SEMUA jenis error
                // yang ditemukan (diurutkan dari paling sering),
                // bukan cuma satu yang paling dominan seperti
                // sebelumnya — supaya siswa (dan guru lewat
                // AssessmentSubmission->feedback) dapat gambaran utuh,
                // bukan cuma potongan.
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