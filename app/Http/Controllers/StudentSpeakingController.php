<?php

namespace App\Http\Controllers;

use App\Models\AssessmentAnswer;
use App\Models\AssessmentSubmission;
use App\Models\Lesson;
use App\Models\SpeakingMaterial;
use App\Models\SpeakingSubmission;
use App\Models\Unit;
use App\Models\User;
use App\Models\UserLessonProgress;
use App\Services\GamificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Throwable;

class StudentSpeakingController extends Controller
{
    /**
     * Durasi wajib Speaking Pretest/Posttest.
     *
     * 2 menit = 120 detik.
     * 3 menit = 180 detik.
     */
    private const ASSESSMENT_MIN_DURATION = 120;

    private const ASSESSMENT_MAX_DURATION = 180;

    /**
     * Menampilkan informasi Speaking Task Unit 1–4.
     */
    public function index(Lesson $lesson)
    {
        $this->ensureSpeakingLesson($lesson);

        /*
        |--------------------------------------------------------------------------
        | Jangan menggunakan firstOrFail()
        |--------------------------------------------------------------------------
        |
        | Jika admin belum membuat Speaking Task, halaman tetap dibuka dan
        | menampilkan empty state.
        |
        */
        $material = SpeakingMaterial::query()
            ->where('lesson_id', $lesson->id)
            ->first();

        return view(
            'missions.speaking.index',
            compact(
                'lesson',
                'material'
            )
        );
    }

    /**
     * Menampilkan halaman pelaksanaan Speaking Task Unit 1–4.
     *
     * Unit 1–4 tetap menggunakan konsep pair speaking.
     */
    public function quiz(Lesson $lesson)
    {
        $this->ensureSpeakingLesson($lesson);

        $material = SpeakingMaterial::query()
            ->where('lesson_id', $lesson->id)
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Speaking Task belum tersedia
        |--------------------------------------------------------------------------
        */
        if (!$material) {
            return redirect()
                ->route(
                    'student.speaking',
                    $lesson
                )
                ->with(
                    'error',
                    'Speaking task belum tersedia. Silakan tunggu sampai admin menambahkan task.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Daftar partner untuk Unit 1–4
        |--------------------------------------------------------------------------
        |
        | Admin tidak ditampilkan dan siswa tidak dapat memilih dirinya sendiri.
        |
        */
        $partners = User::query()
            ->whereKeyNot(Auth::id())
            ->where(function ($query) {
                $query
                    ->whereNull('role')
                    ->orWhere('role', '!=', 'admin');
            })
            ->orderBy('name')
            ->get([
                'id',
                'name',
            ]);

        return view(
            'missions.speaking.quiz',
            compact(
                'lesson',
                'material',
                'partners'
            )
        );
    }

    /**
     * Menampilkan halaman pelaksanaan Speaking Pretest/Posttest.
     *
     * Pretest/Posttest selalu menggunakan individual speaking.
     */
    public function assessmentQuiz(string $type)
    {
        $this->ensureAssessmentType($type);

        /*
        |--------------------------------------------------------------------------
        | Ambil unit assessment
        |--------------------------------------------------------------------------
        */
        $unit = Unit::query()
            ->where('type', $type)
            ->where('status', 'active')
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Ambil lesson Speaking
        |--------------------------------------------------------------------------
        */
        $lesson = Lesson::query()
            ->where('unit_id', $unit->id)
            ->where('skill_type', 'speaking')
            ->where('status', 'active')
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Cegah assessment dikerjakan lebih dari sekali
        |--------------------------------------------------------------------------
        */
        $existing = AssessmentSubmission::query()
            ->where('user_id', Auth::id())
            ->where('lesson_id', $lesson->id)
            ->where('type', $type)
            ->where('skill', 'speaking')
            ->whereIn(
                'status',
                [
                    'pending',
                    'completed',
                ]
            )
            ->latest()
            ->first();

        if ($existing) {
            return redirect()->route(
                'student.assessment.result',
                $existing->id
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Ambil Speaking Material
        |--------------------------------------------------------------------------
        */
        $material = SpeakingMaterial::query()
            ->where('lesson_id', $lesson->id)
            ->first();

        if (!$material) {
            return redirect()
                ->route(
                    'student.assessment.show',
                    [
                        'type' => $type,
                        'skill' => 'speaking',
                    ]
                )
                ->with(
                    'error',
                    'Speaking assessment belum tersedia. Silakan tunggu sampai admin menambahkan task.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Paksa mode individual pada tampilan assessment
        |--------------------------------------------------------------------------
        |
        | setAttribute() hanya mengubah object untuk request ini dan tidak
        | menyimpan perubahan langsung ke database.
        |
        | Hal ini juga menjaga kompatibilitas dengan material assessment lama
        | yang sebelumnya masih tersimpan sebagai pair work.
        |
        */
        $material->setAttribute(
            'is_pair_work',
            false
        );

        $material->setAttribute(
            'min_duration',
            self::ASSESSMENT_MIN_DURATION
        );

        $material->setAttribute(
            'max_duration',
            self::ASSESSMENT_MAX_DURATION
        );

        /*
        |--------------------------------------------------------------------------
        | Assessment individual tidak membutuhkan partner
        |--------------------------------------------------------------------------
        |
        | Variable tetap dikirim agar Blade lama tidak mengalami undefined
        | variable selama proses pembaruan file dilakukan bertahap.
        |
        */
        $partners = collect();

        $assessmentType = $type;

        return view(
            'missions.speaking.quiz',
            compact(
                'lesson',
                'material',
                'partners',
                'assessmentType'
            )
        );
    }

    /**
     * Menyimpan rekaman Speaking Task Unit 1–4.
     *
     * Konsep Unit 1–4 tetap menggunakan percakapan dua siswa.
     */
    public function submit(
        Request $request,
        Lesson $lesson,
        SpeakingMaterial $material
    ): JsonResponse {
        $this->ensureSpeakingLesson($lesson);

        /*
        |--------------------------------------------------------------------------
        | Pastikan material berasal dari lesson yang sama
        |--------------------------------------------------------------------------
        */
        abort_unless(
            (int) $material->lesson_id ===
            (int) $lesson->id,
            404
        );

        $isPairWork =
            (bool) $material->is_pair_work;

        $validated = $request->validate([
            'audio_file' => [
                'required',
                'file',
                'max:20480',
            ],

            'transcript' => [
                'required',
                'string',
                'min:5',
                'max:50000',
            ],

            'audio_duration' => [
                'required',
                'integer',
                'min:1',
                'max:7200',
            ],

            /*
            |--------------------------------------------------------------------------
            | Student A/B hanya wajib pada pair speaking
            |--------------------------------------------------------------------------
            */
            'submitter_role' => [
                Rule::requiredIf($isPairWork),
                'nullable',
                Rule::in([
                    'A',
                    'B',
                ]),
            ],

            /*
            |--------------------------------------------------------------------------
            | Partner hanya wajib pada pair speaking
            |--------------------------------------------------------------------------
            */
            'partner_user_id' => [
                Rule::requiredIf($isPairWork),
                'nullable',
                'integer',
                Rule::notIn([
                    Auth::id(),
                ]),
                Rule::exists('users', 'id')
                    ->where(function ($query) {
                        $query->where(function ($roleQuery) {
                            $roleQuery
                                ->whereNull('role')
                                ->orWhere(
                                    'role',
                                    '!=',
                                    'admin'
                                );
                        });
                    }),
            ],
        ]);

        $audioDuration =
            (int) $validated['audio_duration'];

        /*
        |--------------------------------------------------------------------------
        | Validasi durasi Unit 1–4
        |--------------------------------------------------------------------------
        */
        $durationError = $this->validateRecordingDuration(
            $audioDuration,
            $material->min_duration
                ? (int) $material->min_duration
                : null,
            $material->max_duration
                ? (int) $material->max_duration
                : null
        );

        if ($durationError) {
            return response()->json(
                [
                    'message' => $durationError,
                ],
                422
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Simpan audio
        |--------------------------------------------------------------------------
        */
        $audioPath = $request
            ->file('audio_file')
            ->store(
                'speaking',
                'public'
            );

        /*
        |--------------------------------------------------------------------------
        | Buat submission
        |--------------------------------------------------------------------------
        */
        $submission = SpeakingSubmission::create([
            'user_id' =>
                Auth::id(),

            'partner_user_id' =>
                $isPairWork
                    ? (
                        $validated['partner_user_id']
                        ?? null
                    )
                    : null,

            'speaking_material_id' =>
                $material->id,

            'submitter_role' =>
                $isPairWork
                    ? (
                        $validated['submitter_role']
                        ?? null
                    )
                    : null,

            'audio_file' =>
                $audioPath,

            'audio_duration' =>
                $audioDuration,

            'transcript' =>
                trim(
                    $validated['transcript']
                ),

            'status' =>
                'processing',
        ]);

        try {
            /*
            |--------------------------------------------------------------------------
            | AI Evaluation dimatikan oleh admin
            |--------------------------------------------------------------------------
            */
            if (!$material->ai_evaluation_enabled) {
                $submission->update([
                    'feedback' =>
                        'The recording was submitted successfully. AI evaluation is disabled for this task.',

                    'status' =>
                        'completed',

                    'evaluated_at' =>
                        now(),
                ]);

                $this->saveLessonProgress(
                    $lesson,
                    $submission,
                    0
                );

                $gamification =
                    $this->rewardSpeakingParticipants(
                        $lesson,
                        $submission,
                        0
                    );

                return response()->json([
                    'message' =>
                        'Speaking task submitted successfully.',

                    'ai_evaluation_enabled' =>
                        false,

                    'details_score' =>
                        null,

                    'fluency_score' =>
                        null,

                    'pronunciation_score' =>
                        null,

                    'vocabulary_score' =>
                        null,

                    'grammar_score' =>
                        null,

                    'total_score' =>
                        null,

                    'feedback' =>
                        $submission->feedback,

                    'strengths' =>
                        null,

                    'improvements' =>
                        null,

                    'grammar_errors' =>
                        [],

                    'transcript' =>
                        $submission->transcript,

                    'gamification' =>
                        $gamification,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Evaluasi pair speaking Unit 1–4
            |--------------------------------------------------------------------------
            */
            $result = $this->evaluateWithAi(
                $material,
                $submission->transcript,
                false
            );

            $totalRubric =
                $result['details_score'] +
                $result['fluency_score'] +
                $result['pronunciation_score'] +
                $result['vocabulary_score'] +
                $result['grammar_score'];

            $totalScore = (int) round(
                (
                    $totalRubric /
                    20
                ) * 100
            );

            DB::transaction(function () use (
                $submission,
                $result,
                $totalScore,
                $lesson,
                $material
            ) {
                $submission->update([
                    'details_score' =>
                        $result['details_score'],

                    'fluency_score' =>
                        $result['fluency_score'],

                    'pronunciation_score' =>
                        $result['pronunciation_score'],

                    'vocabulary_score' =>
                        $result['vocabulary_score'],

                    'grammar_score' =>
                        $result['grammar_score'],

                    'total_score' =>
                        $totalScore,

                    'feedback' =>
                        $result['feedback'],

                    'strengths' =>
                        $result['strengths'],

                    'improvements' =>
                        $result['improvements'],

                    'grammar_errors' =>
                        $result['grammar_errors'],

                    'raw_ai_response' =>
                        $result['raw_ai_response'],

                    'status' =>
                        'completed',

                    'evaluated_at' =>
                        now(),
                ]);

                $this->saveLessonProgress(
                    $lesson,
                    $submission,
                    $totalScore
                );

                /*
                |--------------------------------------------------------------------------
                | Mirror Unit Speaking ke Assessment History
                |--------------------------------------------------------------------------
                |
                | Unit Speaking sebelumnya hanya tersimpan pada SpeakingSubmission
                | dan UserLessonProgress. Progress page membaca AssessmentSubmission
                | untuk history, grafik tren, dan sub-skill. Karena itu hasil Unit
                | Speaking juga disalin ke AssessmentSubmission/AssessmentAnswer.
                |
                */
                $this->saveUnitAssessmentResults(
                    $lesson,
                    $material,
                    $submission,
                    $totalScore
                );
            });

            $submission->refresh();

            $gamification =
                $this->rewardSpeakingParticipants(
                    $lesson,
                    $submission,
                    (int) $submission->total_score
                );

            return response()->json([
                'message' =>
                    'Speaking task evaluated successfully.',

                'ai_evaluation_enabled' =>
                    true,

                'details_score' =>
                    $submission->details_score,

                'fluency_score' =>
                    $submission->fluency_score,

                'pronunciation_score' =>
                    $submission->pronunciation_score,

                'vocabulary_score' =>
                    $submission->vocabulary_score,

                'grammar_score' =>
                    $submission->grammar_score,

                'total_score' =>
                    $submission->total_score,

                'feedback' =>
                    $submission->feedback,

                'strengths' =>
                    $submission->strengths,

                'improvements' =>
                    $submission->improvements,

                'grammar_errors' =>
                    $submission->grammar_errors
                    ?? [],

                'transcript' =>
                    $submission->transcript,

                'gamification' =>
                    $gamification,
            ]);
        } catch (Throwable $exception) {
            Log::error(
                'Speaking evaluation failed.',
                [
                    'submission_id' =>
                        $submission->id,

                    'message' =>
                        $exception->getMessage(),
                ]
            );

            $submission->update([
                'status' =>
                    'completed',

                'feedback' =>
                    'Your recording was saved, but the AI evaluation could not be completed.',

                'evaluated_at' =>
                    now(),
            ]);

            return response()->json([
                'message' =>
                    'Your recording was saved, but AI evaluation failed. Please contact the administrator.',

                'ai_evaluation_enabled' =>
                    true,

                'details_score' =>
                    null,

                'fluency_score' =>
                    null,

                'pronunciation_score' =>
                    null,

                'vocabulary_score' =>
                    null,

                'grammar_score' =>
                    null,

                'total_score' =>
                    null,

                'feedback' =>
                    $submission->feedback,

                'strengths' =>
                    null,

                'improvements' =>
                    null,

                'grammar_errors' =>
                    [],

                'transcript' =>
                    $submission->transcript,
            ]);
        }
    }

    /**
     * Menyimpan Individual Speaking Pretest/Posttest.
     */
    public function assessmentSubmit(
        Request $request,
        string $type,
        SpeakingMaterial $material
    ): JsonResponse {
        $this->ensureAssessmentType($type);

        /*
        |--------------------------------------------------------------------------
        | Load lesson dan unit
        |--------------------------------------------------------------------------
        */
        $material->load(
            'lesson.unit'
        );

        $lesson =
            $material->lesson;

        /*
        |--------------------------------------------------------------------------
        | Pastikan material adalah Speaking Pretest/Posttest yang benar
        |--------------------------------------------------------------------------
        */
        abort_unless(
            $lesson &&
            $lesson->skill_type === 'speaking' &&
            $lesson->unit &&
            $lesson->unit->type === $type &&
            $lesson->unit->status === 'active',
            404
        );

        /*
        |--------------------------------------------------------------------------
        | Pretest/Posttest wajib AI Evaluation
        |--------------------------------------------------------------------------
        */
        if (!$material->ai_evaluation_enabled) {
            return response()->json(
                [
                    'message' =>
                        'AI evaluation must be enabled for a speaking pre-test/post-test.',
                ],
                422
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Validasi individual speaking
        |--------------------------------------------------------------------------
        |
        | Tidak ada:
        | - partner_user_id
        | - submitter_role
        |
        */
        $validated = $request->validate([
            'audio_file' => [
                'required',
                'file',
                'max:20480',
            ],

            'transcript' => [
                'required',
                'string',
                'min:5',
                'max:50000',
            ],

            'audio_duration' => [
                'required',
                'integer',
                'min:1',
                'max:7200',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Pastikan siswa belum pernah menyelesaikan assessment
        |--------------------------------------------------------------------------
        */
        $existingSubmission = AssessmentSubmission::query()
            ->where('user_id', Auth::id())
            ->where('lesson_id', $lesson->id)
            ->where('type', $type)
            ->where('skill', 'speaking')
            ->whereIn(
                'status',
                [
                    'pending',
                    'completed',
                ]
            )
            ->first();

        if ($existingSubmission) {
            return response()->json(
                [
                    'message' =>
                        'You have already completed this speaking assessment.',

                    'assessment_result_url' =>
                        route(
                            'student.assessment.result',
                            $existingSubmission->id
                        ),
                ],
                422
            );
        }

        $audioDuration =
            (int) $validated['audio_duration'];

        /*
        |--------------------------------------------------------------------------
        | Pretest/Posttest selalu 2–3 menit
        |--------------------------------------------------------------------------
        |
        | Controller tidak bergantung pada nilai lama di database sehingga
        | assessment lama yang sebelumnya 2–5 menit tetap dipaksa menjadi
        | durasi individual speaking 2–3 menit.
        |
        */
        $durationError = $this->validateRecordingDuration(
            $audioDuration,
            self::ASSESSMENT_MIN_DURATION,
            self::ASSESSMENT_MAX_DURATION
        );

        if ($durationError) {
            return response()->json(
                [
                    'message' =>
                        $durationError,
                ],
                422
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Simpan audio
        |--------------------------------------------------------------------------
        */
        $audioPath = $request
            ->file('audio_file')
            ->store(
                'speaking',
                'public'
            );

        /*
        |--------------------------------------------------------------------------
        | Satu submission hanya untuk satu siswa
        |--------------------------------------------------------------------------
        */
        $submission = SpeakingSubmission::create([
            'user_id' =>
                Auth::id(),

            'partner_user_id' =>
                null,

            'speaking_material_id' =>
                $material->id,

            'submitter_role' =>
                null,

            'audio_file' =>
                $audioPath,

            'audio_duration' =>
                $audioDuration,

            'transcript' =>
                trim(
                    $validated['transcript']
                ),

            'status' =>
                'processing',
        ]);

        try {
            /*
            |--------------------------------------------------------------------------
            | Evaluasi individual speaking
            |--------------------------------------------------------------------------
            */
            $result = $this->evaluateWithAi(
                $material,
                $submission->transcript,
                true
            );

            $totalRubric =
                $result['details_score'] +
                $result['fluency_score'] +
                $result['pronunciation_score'] +
                $result['vocabulary_score'] +
                $result['grammar_score'];

            /*
            |--------------------------------------------------------------------------
            | Konversi rubric 5 × 4 menjadi skor 0–100
            |--------------------------------------------------------------------------
            */
            $totalScore = (int) round(
                (
                    $totalRubric /
                    20
                ) * 100
            );

            $assessmentSubmission = null;

            DB::transaction(function () use (
                $submission,
                $result,
                $totalScore,
                $lesson,
                $material,
                $type,
                &$assessmentSubmission
            ) {
                /*
                |--------------------------------------------------------------------------
                | Update SpeakingSubmission
                |--------------------------------------------------------------------------
                */
                $submission->update([
                    'details_score' =>
                        $result['details_score'],

                    'fluency_score' =>
                        $result['fluency_score'],

                    'pronunciation_score' =>
                        $result['pronunciation_score'],

                    'vocabulary_score' =>
                        $result['vocabulary_score'],

                    'grammar_score' =>
                        $result['grammar_score'],

                    'total_score' =>
                        $totalScore,

                    'feedback' =>
                        $result['feedback'],

                    'strengths' =>
                        $result['strengths'],

                    'improvements' =>
                        $result['improvements'],

                    'grammar_errors' =>
                        $result['grammar_errors'],

                    'raw_ai_response' =>
                        $result['raw_ai_response'],

                    'status' =>
                        'completed',

                    'evaluated_at' =>
                        now(),
                ]);

                /*
                |--------------------------------------------------------------------------
                | Simpan progress hanya untuk siswa yang sedang login
                |--------------------------------------------------------------------------
                */
                $this->saveLessonProgress(
                    $lesson,
                    $submission,
                    $totalScore
                );

                /*
                |--------------------------------------------------------------------------
                | Simpan hasil Pretest/Posttest
                |--------------------------------------------------------------------------
                */
                $assessmentSubmission =
                    $this->saveAssessmentResult(
                        $type,
                        $lesson,
                        $material,
                        $submission,
                        $totalScore
                    );
            });

            $submission->refresh();

            $gamification =
                $assessmentSubmission
                    ? app(GamificationService::class)
                        ->rewardAssessmentCompletion(
                            Auth::user(),
                            $assessmentSubmission
                        )
                    : null;

            return response()->json([
                'message' =>
                    'Individual speaking assessment evaluated successfully.',

                'ai_evaluation_enabled' =>
                    true,

                'details_score' =>
                    $submission->details_score,

                'fluency_score' =>
                    $submission->fluency_score,

                'pronunciation_score' =>
                    $submission->pronunciation_score,

                'vocabulary_score' =>
                    $submission->vocabulary_score,

                'grammar_score' =>
                    $submission->grammar_score,

                'total_score' =>
                    $submission->total_score,

                'feedback' =>
                    $submission->feedback,

                'strengths' =>
                    $submission->strengths,

                'improvements' =>
                    $submission->improvements,

                'grammar_errors' =>
                    $submission->grammar_errors
                    ?? [],

                'transcript' =>
                    $submission->transcript,

                'assessment_result_url' =>
                    $assessmentSubmission
                        ? route(
                            'student.assessment.result',
                            $assessmentSubmission->id
                        )
                        : null,

                'gamification' =>
                    $gamification,
            ]);
        } catch (Throwable $exception) {
            Log::error(
                'Individual speaking assessment evaluation failed.',
                [
                    'submission_id' =>
                        $submission->id,

                    'type' =>
                        $type,

                    'user_id' =>
                        Auth::id(),

                    'message' =>
                        $exception->getMessage(),
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | Audio dan transcript tetap disimpan
            |--------------------------------------------------------------------------
            |
            | AssessmentSubmission belum dibuat agar siswa dapat mencoba kembali
            | setelah masalah AI diselesaikan.
            |
            */
            $submission->update([
                'status' =>
                    'completed',

                'feedback' =>
                    'Your recording was saved, but the AI evaluation could not be completed.',

                'evaluated_at' =>
                    now(),
            ]);

            return response()->json([
                'message' =>
                    'Your recording was saved, but AI evaluation failed. Please contact the administrator.',

                'ai_evaluation_enabled' =>
                    true,

                'details_score' =>
                    null,

                'fluency_score' =>
                    null,

                'pronunciation_score' =>
                    null,

                'vocabulary_score' =>
                    null,

                'grammar_score' =>
                    null,

                'total_score' =>
                    null,

                'feedback' =>
                    $submission->feedback,

                'strengths' =>
                    null,

                'improvements' =>
                    null,

                'grammar_errors' =>
                    [],

                'transcript' =>
                    $submission->transcript,

                'assessment_result_url' =>
                    null,
            ]);
        }
    }

    /**
     * Mengirim transcript ke AI.
     */
    private function evaluateWithAi(
        SpeakingMaterial $material,
        string $transcript,
        bool $isIndividualAssessment
    ): array {
        $apiKey =
            config('services.dinoiki.key');

        if (!$apiKey) {
            throw new \RuntimeException(
                'DINOIKI_API_KEY is not configured.'
            );
        }

        $baseUrl = rtrim(
            (string) config(
                'services.dinoiki.base_url',
                'https://ai.dinoiki.com/v1'
            ),
            '/'
        );

        $model = config(
            'services.dinoiki.chat_model',
            'gpt-4o'
        );

        /*
        |--------------------------------------------------------------------------
        | Gunakan prompt berbeda
        |--------------------------------------------------------------------------
        |
        | Pretest/Posttest:
        | - individual story presentation
        |
        | Unit 1–4:
        | - pair speaking conversation
        |
        */
        $prompt = $isIndividualAssessment
            ? $this->buildIndividualAssessmentPrompt(
                $material,
                $transcript
            )
            : $this->buildPairSpeakingPrompt(
                $material,
                $transcript
            );

        $systemInstruction = $isIndividualAssessment
            ? 'You are an English individual speaking evaluator for Indonesian vocational high school students. Return only valid JSON.'
            : 'You are an English pair speaking evaluator for Indonesian vocational high school students. Return only valid JSON.';

        $response = Http::withToken($apiKey)
            ->acceptJson()
            ->asJson()
            ->timeout(90)
            ->retry(
                2,
                1000
            )
            ->post(
                $baseUrl .
                '/chat/completions',
                [
                    'model' =>
                        $model,

                    'messages' => [
                        [
                            'role' =>
                                'system',

                            'content' =>
                                $systemInstruction,
                        ],
                        [
                            'role' =>
                                'user',

                            'content' =>
                                $prompt,
                        ],
                    ],

                    'max_completion_tokens' =>
                        1400,
                ]
            );

        if (!$response->successful()) {
            throw new \RuntimeException(
                'AI request failed with HTTP status ' .
                $response->status() .
                '.'
            );
        }

        $content = data_get(
            $response->json(),
            'choices.0.message.content',
            ''
        );

        $content = $this->cleanJsonResponse(
            (string) $content
        );

        $decoded = json_decode(
            $content,
            true
        );

        if (!is_array($decoded)) {
            throw new \RuntimeException(
                'AI returned an invalid JSON response.'
            );
        }

        return [
            /*
            |--------------------------------------------------------------------------
            | details_score digunakan sebagai Content Relevance
            |--------------------------------------------------------------------------
            */
            'details_score' =>
                $this->normaliseRubricScore(
                    $decoded['content_relevance']
                    ?? $decoded['details_score']
                    ?? $decoded['details']
                    ?? 1
                ),

            'fluency_score' =>
                $this->normaliseRubricScore(
                    $decoded['fluency']
                    ?? $decoded['fluency_score']
                    ?? 1
                ),

            'pronunciation_score' =>
                $this->normaliseRubricScore(
                    $decoded['pronunciation']
                    ?? $decoded['pronunciation_score']
                    ?? 1
                ),

            'vocabulary_score' =>
                $this->normaliseRubricScore(
                    $decoded['vocabulary']
                    ?? $decoded['vocabulary_score']
                    ?? 1
                ),

            'grammar_score' =>
                $this->normaliseRubricScore(
                    $decoded['grammar_accuracy']
                    ?? $decoded['grammar']
                    ?? $decoded['grammar_score']
                    ?? 1
                ),

            'feedback' =>
                trim(
                    (string) (
                        $decoded['feedback']
                        ?? 'No feedback was provided.'
                    )
                ),

            'strengths' =>
                trim(
                    (string) (
                        data_get(
                            $decoded,
                            'strengths'
                        )
                        ?? data_get(
                            $decoded,
                            'feedback.strengths'
                        )
                        ?? 'No specific strengths were provided.'
                    )
                ),

            'improvements' =>
                trim(
                    (string) (
                        data_get(
                            $decoded,
                            'improvements'
                        )
                        ?? data_get(
                            $decoded,
                            'feedback.improvements'
                        )
                        ?? 'Continue practising fluency, grammar, pronunciation, and vocabulary.'
                    )
                ),

            'grammar_errors' =>
                $this->normaliseGrammarErrors(
                    $decoded['grammar_errors']
                    ?? data_get(
                        $decoded,
                        'feedback.grammar_errors_found',
                        []
                    )
                ),

            'raw_ai_response' =>
                $decoded,
        ];
    }

    /**
     * Prompt AI untuk Pair Speaking Unit 1–4.
     */
    private function buildPairSpeakingPrompt(
        SpeakingMaterial $material,
        string $transcript
    ): string {
        $discussionPoints = collect(
            $material->discussion_points
            ?? []
        )
            ->map(function (
                $point,
                $index
            ) {
                return (
                    $index + 1
                ) .
                '. ' .
                $point;
            })
            ->implode("\n");

        return <<<PROMPT
Evaluate the following pair speaking conversation for an Indonesian vocational high school English class.

TASK TITLE:
{$material->title}

INSTRUCTION:
{$material->instruction}

SCENARIO:
{$material->scenario}

STUDENT A ROLE:
{$material->role_a}

STUDENT B ROLE:
{$material->role_b}

DISCUSSION POINTS:
{$discussionPoints}

STUDENT CONVERSATION TRANSCRIPT:
{$transcript}

Use scores from 1 to 4 for every criterion.

CONTENT RELEVANCE
4 = Covers all required discussion points completely and appropriately.
3 = Covers most required discussion points with generally relevant information.
2 = Covers only a small number of required points or gives limited relevant information.
1 = The response is mostly irrelevant or does not address the task.

FLUENCY
4 = Smooth and natural delivery with only reasonable pauses.
3 = Generally fluent with a few pauses or hesitations.
2 = Frequent pauses, hesitation, or incomplete ideas.
1 = Very hesitant and difficult to follow.

PRONUNCIATION
Important limitation: the response is provided as a browser-generated transcript, not raw audio. Do not claim certainty about pronunciation. Use transcript clarity and recognition quality only as limited supporting evidence.
4 = Transcript is consistently clear and coherent, suggesting highly understandable speech.
3 = Mostly clear with a few recognition or clarity issues.
2 = Several unclear or incorrectly recognised phrases.
1 = Frequently unclear or difficult to recognise.

VOCABULARY
4 = Varied, accurate, and appropriate vocabulary.
3 = Adequate vocabulary with some variation.
2 = Limited and repetitive vocabulary.
1 = Very limited or frequently inappropriate vocabulary.

GRAMMAR ACCURACY
4 = Accurate and varied sentence structures with very few errors.
3 = Some minor errors, but meaning remains clear.
2 = Frequent errors, although the main meaning can still be understood.
1 = Serious errors that frequently obscure meaning.

Return ONLY one valid JSON object with this exact structure:

{
  "content_relevance": 1,
  "fluency": 1,
  "pronunciation": 1,
  "vocabulary": 1,
  "grammar_accuracy": 1,
  "feedback": "Constructive feedback in Bahasa Indonesia, 3 to 6 sentences.",
  "strengths": "Kelebihan utama siswa dalam Bahasa Indonesia, 1 to 3 sentences.",
  "improvements": "Hal yang perlu ditingkatkan dalam Bahasa Indonesia, 1 to 3 sentences.",
  "grammar_errors": [
    {
      "original": "incorrect sentence",
      "correction": "correct sentence",
      "explanation": "short explanation in Bahasa Indonesia"
    }
  ]
}

Do not return Markdown.
Do not place the JSON inside code fences.
PROMPT;
    }

    /**
     * Prompt AI untuk Individual Speaking Pretest/Posttest.
     */
    private function buildIndividualAssessmentPrompt(
        SpeakingMaterial $material,
        string $transcript
    ): string {
        $presentationPoints = collect(
            $material->discussion_points
            ?? []
        )
            ->map(function (
                $point,
                $index
            ) {
                return (
                    $index + 1
                ) .
                '. ' .
                $point;
            })
            ->implode("\n");

        $supportingContent =
            trim(
                (string) (
                    $material->passage
                    ?? ''
                )
            );

        if ($supportingContent === '') {
            $supportingContent =
                'No supporting example was provided.';
        }

        return <<<PROMPT
Evaluate the following INDIVIDUAL SPEAKING presentation for an Indonesian vocational high school English pre-test or post-test.

The student is speaking individually. This is NOT a dialogue, NOT pair work, and there is no Student A or Student B.

ACTIVITY:
Activity 2 – Individual Speaking

EXPECTED DURATION:
2–3 minutes

TASK TITLE:
{$material->title}

INSTRUCTION:
{$material->instruction}

PRESENTATION CONTEXT:
{$material->scenario}

THE PRESENTATION SHOULD INCLUDE:
{$presentationPoints}

The expected core story elements are:

1. Title
2. Main characters
3. Setting
4. Plot or sequence of events
5. Conflict or problem
6. Resolution
7. Moral value
8. Student's personal opinion

SUPPORTING EXAMPLE OR MATERIAL:
{$supportingContent}

STUDENT INDIVIDUAL PRESENTATION TRANSCRIPT:
{$transcript}

Evaluate only the student's actual transcript. The supporting example is provided only to understand the expected activity and must not be treated as the student's answer.

Use scores from 1 to 4 for every criterion.

CONTENT RELEVANCE AND COMPLETENESS
4 = The presentation clearly and appropriately includes the title, main characters, setting, plot, conflict, resolution, moral value, and personal opinion.
3 = The presentation includes most required story elements, but one or two elements are missing, unclear, or insufficiently developed.
2 = The presentation includes only some required elements and gives limited explanation of the story.
1 = The presentation includes very few required elements, is mostly irrelevant, or cannot be understood as a complete story presentation.

FLUENCY
4 = The presentation is smooth, logically connected, and easy to follow, with only reasonable pauses.
3 = The presentation is generally fluent with a few pauses, repetitions, or hesitations.
2 = The presentation contains frequent pauses, hesitation, repetition, or incomplete ideas.
1 = The presentation is very hesitant and difficult to follow.

PRONUNCIATION
Important limitation: the response is provided as a browser-generated transcript, not direct acoustic analysis of the raw audio. Do not claim certainty about pronunciation. Use transcript clarity and speech-recognition quality only as limited supporting evidence.
4 = The transcript is consistently clear and coherent, suggesting highly understandable speech.
3 = The transcript is mostly clear with a few recognition or clarity issues.
2 = Several phrases appear unclear or incorrectly recognised.
1 = The transcript is frequently unclear or difficult to recognise.

VOCABULARY
4 = Uses varied, accurate, and appropriate vocabulary for retelling and discussing a story.
3 = Uses adequate and generally appropriate vocabulary with some variation.
2 = Uses limited, repetitive, or occasionally inappropriate vocabulary.
1 = Uses very limited vocabulary that frequently prevents clear communication.

GRAMMAR ACCURACY
4 = Uses accurate and varied sentence structures with very few grammatical errors.
3 = Contains some minor grammatical errors, but the meaning remains clear.
2 = Contains frequent grammatical errors, although the main meaning can still be understood.
1 = Contains serious grammatical errors that frequently obscure the meaning.

FEEDBACK REQUIREMENTS
- Write feedback in Bahasa Indonesia.
- Explain whether the student included the required story elements.
- Mention the strongest part of the presentation.
- Mention the most important improvement.
- Do not describe the presentation as a dialogue or pair conversation.
- Do not mention Student A, Student B, or a partner.

Return ONLY one valid JSON object with this exact structure:

{
  "content_relevance": 1,
  "fluency": 1,
  "pronunciation": 1,
  "vocabulary": 1,
  "grammar_accuracy": 1,
  "feedback": "Feedback konstruktif dalam Bahasa Indonesia, 3 sampai 6 kalimat.",
  "strengths": "Kelebihan utama presentasi siswa dalam Bahasa Indonesia, 1 sampai 3 kalimat.",
  "improvements": "Hal terpenting yang perlu ditingkatkan dalam Bahasa Indonesia, 1 sampai 3 kalimat.",
  "grammar_errors": [
    {
      "original": "incorrect sentence",
      "correction": "correct sentence",
      "explanation": "penjelasan singkat dalam Bahasa Indonesia"
    }
  ]
}

Do not return Markdown.
Do not place the JSON inside code fences.
PROMPT;
    }

    /**
     * Menyimpan hasil Speaking Unit 1–4 ke AssessmentSubmission.
     *
     * Pair speaking berlaku untuk pengirim dan partner sehingga keduanya
     * memperoleh history, trend, dan sub-skill yang sama untuk aktivitas ini.
     */
    private function saveUnitAssessmentResults(
        Lesson $lesson,
        SpeakingMaterial $material,
        SpeakingSubmission $speakingSubmission,
        int $score
    ): void {
        $userIds = collect([
            $speakingSubmission->user_id,
            $speakingSubmission->partner_user_id,
        ])
            ->filter()
            ->unique()
            ->values();

        foreach ($userIds as $userId) {
            $assessmentSubmission = AssessmentSubmission::create([
                'user_id' => (int) $userId,
                'unit_id' => $lesson->unit_id,
                'lesson_id' => $lesson->id,
                'type' => 'unit',
                'skill' => 'speaking',
                'final_score' => max(0, min(100, $score)),
                'criteria_scores' => [
                    'content_relevance' =>
                        $speakingSubmission->details_score,

                    'fluency' =>
                        $speakingSubmission->fluency_score,

                    'pronunciation' =>
                        $speakingSubmission->pronunciation_score,

                    'vocabulary' =>
                        $speakingSubmission->vocabulary_score,

                    'grammar_accuracy' =>
                        $speakingSubmission->grammar_score,
                ],
                'status' => 'completed',
                'feedback' => $speakingSubmission->feedback,
                'submitted_at' =>
                    $speakingSubmission->evaluated_at
                    ?? now(),
            ]);

            AssessmentAnswer::create([
                'assessment_submission_id' =>
                    $assessmentSubmission->id,

                'question_type' =>
                    'speaking',

                /*
                 * Speaking Unit memakai material sebagai satu task.
                 */
                'question_id' =>
                    $material->id,

                'answer' =>
                    $speakingSubmission->transcript,

                'selected_option' =>
                    null,

                'is_correct' =>
                    null,

                'details_score' =>
                    $speakingSubmission->details_score,

                'fluency_score' =>
                    $speakingSubmission->fluency_score,

                'pronunciation_score' =>
                    $speakingSubmission->pronunciation_score,

                'vocabulary_score' =>
                    $speakingSubmission->vocabulary_score,

                'grammar_score' =>
                    $speakingSubmission->grammar_score,

                'score' =>
                    max(0, min(100, $score)),

                'max_score' =>
                    100,

                'feedback' =>
                    $speakingSubmission->feedback,
            ]);
        }
    }

    /**
     * Menyimpan progress lesson.
     *
     * Unit pair speaking:
     * - progress disimpan untuk pengirim dan partner.
     *
     * Pretest/Posttest individual:
     * - partner_user_id null sehingga hanya pengirim yang disimpan.
     */
    private function saveLessonProgress(
        Lesson $lesson,
        SpeakingSubmission $submission,
        int $score
    ): void {
        $userIds = collect([
            $submission->user_id,
            $submission->partner_user_id,
        ])
            ->filter()
            ->unique()
            ->values();

        foreach ($userIds as $userId) {
            UserLessonProgress::updateOrCreate(
                [
                    'user_id' =>
                        $userId,

                    'lesson_id' =>
                        $lesson->id,

                    'skill_type' =>
                        'speaking',
                ],
                [
                    'unit_id' =>
                        $lesson->unit_id,

                    'status' =>
                        'completed',

                    'score' =>
                        $score,

                    'completed_at' =>
                        now(),
                ]
            );
        }
    }

    /**
     * Menyimpan hasil Speaking Pretest/Posttest.
     *
     * Karena assessment individual, hanya user_id pengirim yang tersimpan.
     */
    private function saveAssessmentResult(
        string $type,
        Lesson $lesson,
        SpeakingMaterial $material,
        SpeakingSubmission $speakingSubmission,
        int $score
    ): AssessmentSubmission {
        /*
        |--------------------------------------------------------------------------
        | Satu assessment hanya untuk siswa yang mengirim
        |--------------------------------------------------------------------------
        */
        $assessmentSubmission =
            AssessmentSubmission::updateOrCreate(
                [
                    'user_id' =>
                        $speakingSubmission->user_id,

                    'lesson_id' =>
                        $lesson->id,

                    'type' =>
                        $type,

                    'skill' =>
                        'speaking',
                ],
                [
                    'unit_id' =>
                        $lesson->unit_id,

                    'final_score' =>
                        $score,

                    'criteria_scores' => [
                        'content_relevance' =>
                            $speakingSubmission->details_score,

                        'fluency' =>
                            $speakingSubmission->fluency_score,

                        'pronunciation' =>
                            $speakingSubmission->pronunciation_score,

                        'vocabulary' =>
                            $speakingSubmission->vocabulary_score,

                        'grammar_accuracy' =>
                            $speakingSubmission->grammar_score,
                    ],

                    'status' =>
                        'completed',

                    'feedback' =>
                        $speakingSubmission->feedback,

                    'submitted_at' =>
                        now(),
                ]
            );

        /*
        |--------------------------------------------------------------------------
        | Satu answer mewakili satu individual speaking activity
        |--------------------------------------------------------------------------
        |
        | speaking_material_id disimpan di question_id agar tidak membutuhkan
        | migration baru.
        |
        */
        AssessmentAnswer::updateOrCreate(
            [
                'assessment_submission_id' =>
                    $assessmentSubmission->id,

                'question_type' =>
                    'speaking',

                'question_id' =>
                    $material->id,
            ],
            [
                'answer' =>
                    $speakingSubmission->transcript,

                'selected_option' =>
                    null,

                'is_correct' =>
                    null,

                'details_score' =>
                    $speakingSubmission->details_score,

                'fluency_score' =>
                    $speakingSubmission->fluency_score,

                'pronunciation_score' =>
                    $speakingSubmission->pronunciation_score,

                'vocabulary_score' =>
                    $speakingSubmission->vocabulary_score,

                'grammar_score' =>
                    $speakingSubmission->grammar_score,

                'score' =>
                    $score,

                'max_score' =>
                    100,

                'feedback' =>
                    $speakingSubmission->feedback,
            ]
        );

        return $assessmentSubmission;
    }

    /**
     * Memberikan reward gamifikasi untuk peserta Speaking Unit 1–4.
     *
     * Pair speaking:
     * - pengirim mendapat reward,
     * - partner juga mendapat reward,
     * - response popup hanya dikembalikan untuk user yang sedang login.
     */
    private function rewardSpeakingParticipants(
        Lesson $lesson,
        SpeakingSubmission $submission,
        int $score
    ): array {
        $gamificationService =
            app(GamificationService::class);

        $userIds = collect([
            $submission->user_id,
            $submission->partner_user_id,
        ])
            ->filter()
            ->unique()
            ->values();

        $currentUserReward = null;

        foreach ($userIds as $userId) {
            $participant = User::query()
                ->find((int) $userId);

            if (!$participant) {
                continue;
            }

            $reward =
                $gamificationService
                    ->rewardLessonCompletion(
                        $participant,
                        $lesson,
                        'speaking',
                        $score
                    );

            if (
                (int) $participant->id ===
                (int) Auth::id()
            ) {
                $currentUserReward =
                    $reward;
            }
        }

        return $currentUserReward ?? [
            'show_popup' => false,
            'title' => null,
            'message' => null,
            'xp_earned' => 0,
            'coins_earned' => 0,
            'badges' => [],
            'unit_completed' => false,
            'level_up' => false,
        ];
    }

    /**
     * Validasi durasi rekaman.
     */
    private function validateRecordingDuration(
        int $audioDuration,
        ?int $minimumDuration,
        ?int $maximumDuration
    ): ?string {
        if (
            $minimumDuration &&
            $audioDuration < $minimumDuration
        ) {
            return sprintf(
                'Recording is too short. Minimum duration is %d minute(s).',
                (int) ceil(
                    $minimumDuration /
                    60
                )
            );
        }

        if (
            $maximumDuration &&
            $audioDuration > $maximumDuration
        ) {
            return sprintf(
                'Recording is too long. Maximum duration is %d minute(s).',
                (int) ceil(
                    $maximumDuration /
                    60
                )
            );
        }

        return null;
    }

    /**
     * Memastikan assessment type valid.
     */
    private function ensureAssessmentType(
        string $type
    ): void {
        abort_unless(
            in_array(
                $type,
                [
                    'pretest',
                    'posttest',
                ],
                true
            ),
            404
        );
    }

    /**
     * Memastikan URL mengarah ke lesson Speaking.
     */
    private function ensureSpeakingLesson(
        Lesson $lesson
    ): void {
        abort_unless(
            $lesson->skill_type ===
            'speaking',
            404
        );
    }

    /**
     * Membersihkan code fence JSON dari AI.
     */
    private function cleanJsonResponse(
        string $content
    ): string {
        $content =
            trim($content);

        $content = preg_replace(
            '/^```(?:json)?\s*/i',
            '',
            $content
        );

        $content = preg_replace(
            '/\s*```$/',
            '',
            $content
        );

        $firstBrace =
            strpos(
                $content,
                '{'
            );

        $lastBrace =
            strrpos(
                $content,
                '}'
            );

        if (
            $firstBrace !== false &&
            $lastBrace !== false &&
            $lastBrace >= $firstBrace
        ) {
            return substr(
                $content,
                $firstBrace,
                $lastBrace -
                $firstBrace +
                1
            );
        }

        return trim($content);
    }

    /**
     * Memastikan skor rubric selalu berada pada 1–4.
     */
    private function normaliseRubricScore(
        mixed $score
    ): int {
        return max(
            1,
            min(
                4,
                (int) $score
            )
        );
    }

    /**
     * Menyeragamkan format grammar error.
     */
    private function normaliseGrammarErrors(
        mixed $errors
    ): array {
        if (!is_array($errors)) {
            return [];
        }

        return collect($errors)
            ->map(function ($error) {
                if (is_string($error)) {
                    return [
                        'original' =>
                            $error,

                        'correction' =>
                            '',

                        'explanation' =>
                            '',
                    ];
                }

                if (!is_array($error)) {
                    return null;
                }

                return [
                    'original' =>
                        (string) (
                            $error['original']
                            ?? $error['incorrect']
                            ?? ''
                        ),

                    'correction' =>
                        (string) (
                            $error['correction']
                            ?? $error['correct']
                            ?? ''
                        ),

                    'explanation' =>
                        (string) (
                            $error['explanation']
                            ?? ''
                        ),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }
}