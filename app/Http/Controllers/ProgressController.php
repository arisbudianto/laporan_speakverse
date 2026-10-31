<?php

namespace App\Http\Controllers;

use App\Models\AssessmentSubmission;
use App\Models\Lesson;
use App\Models\ListeningQuestion;
use App\Models\ReadingQuestion;
use App\Models\UserLessonProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class ProgressController extends Controller
{
    private const SKILLS = [
        'listening',
        'reading',
        'writing',
        'speaking',
    ];

    private const STAGES = [
        'pretest' => 'Pre-Test',
        'unit_1' => 'Unit 1',
        'unit_2' => 'Unit 2',
        'unit_3' => 'Unit 3',
        'unit_4' => 'Unit 4',
        'posttest' => 'Post-Test',
    ];

    private const TYPES = [
        'pretest',
        'unit',
        'posttest',
    ];

    private const STATUSES = [
        'completed',
        'pending',
        'failed',
    ];

    private const SORTS = [
        'newest',
        'oldest',
        'highest',
        'lowest',
    ];

    /**
     * Halaman Progress.
     */
    public function index(Request $request)
    {
        $userId = (int) Auth::id();

        $filters = [
            'skill' => $this->resolveFilter(
                strtolower(
                    trim(
                        (string) $request->query(
                            'skill',
                            'all'
                        )
                    )
                ),
                self::SKILLS
            ),

            'type' => $this->resolveFilter(
                strtolower(
                    trim(
                        (string) $request->query(
                            'type',
                            'all'
                        )
                    )
                ),
                self::TYPES
            ),

            'status' => $this->resolveFilter(
                strtolower(
                    trim(
                        (string) $request->query(
                            'status',
                            'all'
                        )
                    )
                ),
                self::STATUSES
            ),

            'sort' => $this->resolveFilter(
                strtolower(
                    trim(
                        (string) $request->query(
                            'sort',
                            'newest'
                        )
                    )
                ),
                self::SORTS,
                'newest'
            ),
        ];

        /*
        |--------------------------------------------------------------------------
        | Semua submission
        |--------------------------------------------------------------------------
        */

        $allSubmissions = AssessmentSubmission::query()
            ->with([
                'unit',
                'lesson',
                'lesson.unit',
                'answers',
            ])
            ->where(
                'user_id',
                $userId
            )
            ->orderByDesc('submitted_at')
            ->orderByDesc('id')
            ->get();

        $completedAttempts = $allSubmissions
            ->filter(function ($submission) {
                return strtolower(
                    trim(
                        (string) $submission->status
                    )
                ) === 'completed'
                    && $submission->final_score !== null;
            })
            ->values();

        /*
         * Statistik agregat hanya menggunakan latest submission
         * dari assessment yang sama.
         */
        $completed = $completedAttempts
            ->unique(function ($submission) {
                return implode(
                    '|',
                    [
                        strtolower(
                            trim(
                                (string) $submission->type
                            )
                        ),

                        strtolower(
                            trim(
                                (string) $submission->skill
                            )
                        ),

                        $submission->lesson_id
                            ?? $this->resolveStageKey(
                                $submission
                            )
                            ?? 'submission-' .
                                $submission->id,
                    ]
                );
            })
            ->values();

        $this->syncCompletedAssessments(
            $completed,
            $userId
        );

        /*
        |--------------------------------------------------------------------------
        | Lesson progress
        |--------------------------------------------------------------------------
        */

        $lessonProgress = UserLessonProgress::query()
            ->where(
                'user_id',
                $userId
            )
            ->where(
                'status',
                'completed'
            )
            ->orderByDesc('completed_at')
            ->orderByDesc('id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Lesson aktif
        |--------------------------------------------------------------------------
        */

        $activeLessons = Lesson::query()
            ->where(
                'status',
                'active'
            )
            ->whereIn(
                'skill_type',
                self::SKILLS
            )
            ->get([
                'id',
                'unit_id',
                'skill_type',
                'title',
                'order_number',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Summary
        |--------------------------------------------------------------------------
        */

        $averageScore = $completed->isNotEmpty()
            ? (int) round(
                $completed->avg(
                    'final_score'
                )
            )
            : 0;

        $totalCompleted =
            $completedAttempts->count();

        $pretestAverage =
            $this->averageByType(
                $completed,
                'pretest'
            );

        $posttestAverage =
            $this->averageByType(
                $completed,
                'posttest'
            );

        /*
        |--------------------------------------------------------------------------
        | Detail per skill
        |--------------------------------------------------------------------------
        */

        $skillDetails = [];

        foreach (self::SKILLS as $skill) {
            $activeLessonIds = $activeLessons
                ->filter(
                    function ($lesson) use ($skill) {
                        return strtolower(
                            trim(
                                (string) $lesson->skill_type
                            )
                        ) === $skill;
                    }
                )
                ->pluck('id')
                ->map(
                    fn ($id) =>
                        (int) $id
                )
                ->unique()
                ->values();

            $assessmentLessonIds = $completed
                ->filter(
                    function ($submission) use ($skill) {
                        return strtolower(
                            trim(
                                (string) $submission->skill
                            )
                        ) === $skill;
                    }
                )
                ->pluck('lesson_id')
                ->filter()
                ->map(
                    fn ($id) =>
                        (int) $id
                );

            $progressLessonIds = $lessonProgress
                ->filter(
                    function ($progress) use ($skill) {
                        return strtolower(
                            trim(
                                (string) $progress->skill_type
                            )
                        ) === $skill;
                    }
                )
                ->pluck('lesson_id')
                ->filter()
                ->map(
                    fn ($id) =>
                        (int) $id
                );

            $completedLessonIds =
                $assessmentLessonIds
                    ->merge(
                        $progressLessonIds
                    )
                    ->unique()
                    ->intersect(
                        $activeLessonIds
                    )
                    ->values();

            $totalLessons =
                $activeLessonIds->count();

            $completedLessons =
                $completedLessonIds->count();

            $completionPercentage =
                $totalLessons > 0
                    ? (int) round(
                        (
                            $completedLessons /
                            $totalLessons
                        ) * 100
                    )
                    : 0;

            $completionPercentage = max(
                0,
                min(
                    100,
                    $completionPercentage
                )
            );

            $skillAttempts =
                $completedAttempts
                    ->filter(
                        function ($submission) use ($skill) {
                            return strtolower(
                                trim(
                                    (string) $submission->skill
                                )
                            ) === $skill;
                        }
                    )
                    ->sortBy(
                        fn ($submission) =>
                            $this->submissionTimestamp(
                                $submission
                            )
                    )
                    ->values();

            $skillCompleted =
                $completed
                    ->filter(
                        function ($submission) use ($skill) {
                            return strtolower(
                                trim(
                                    (string) $submission->skill
                                )
                            ) === $skill;
                        }
                    )
                    ->values();

            $averageSkillScore =
                $skillCompleted->isNotEmpty()
                    ? (int) round(
                        $skillCompleted->avg(
                            'final_score'
                        )
                    )
                    : null;

            $bestSkillScore =
                $skillAttempts->isNotEmpty()
                    ? (int) $skillAttempts->max(
                        'final_score'
                    )
                    : null;

            $firstSubmission =
                $skillAttempts->first();

            $latestSkillSubmission =
                $skillAttempts->last();

            $firstScore =
                $firstSubmission?->final_score !== null
                    ? (int) $firstSubmission->final_score
                    : null;

            $latestScore =
                $latestSkillSubmission?->final_score !== null
                    ? (int) $latestSkillSubmission->final_score
                    : null;

            $latestPretest =
                $skillAttempts
                    ->filter(
                        function ($submission) {
                            return strtolower(
                                trim(
                                    (string) $submission->type
                                )
                            ) === 'pretest';
                        }
                    )
                    ->last();

            $latestPosttest =
                $skillAttempts
                    ->filter(
                        function ($submission) {
                            return strtolower(
                                trim(
                                    (string) $submission->type
                                )
                            ) === 'posttest';
                        }
                    )
                    ->last();

            $pretestScore =
                $latestPretest?->final_score !== null
                    ? (int) $latestPretest->final_score
                    : null;

            $posttestScore =
                $latestPosttest?->final_score !== null
                    ? (int) $latestPosttest->final_score
                    : null;

            $improvement = null;
            $improvementBasis = null;

            if (
                $pretestScore !== null
                &&
                $posttestScore !== null
            ) {
                $improvement =
                    $posttestScore -
                    $pretestScore;

                $improvementBasis =
                    'Pre-test to post-test';
            } elseif (
                $pretestScore !== null
                &&
                $latestScore !== null
                &&
                $latestPretest?->id
                    !==
                $latestSkillSubmission?->id
            ) {
                $improvement =
                    $latestScore -
                    $pretestScore;

                $improvementBasis =
                    'Pre-test to latest';
            } elseif (
                $firstScore !== null
                &&
                $latestScore !== null
                &&
                $skillAttempts->count() >= 2
            ) {
                $improvement =
                    $latestScore -
                    $firstScore;

                $improvementBasis =
                    'First to latest';
            }

            /*
            |--------------------------------------------------------------------------
            | Sub-skill
            |--------------------------------------------------------------------------
            |
            | Writing/Speaking:
            | berasal dari rubric score AssessmentAnswer.
            |
            | Reading/Listening:
            | berasal dari kategori sub_skill pada question.
            |
            */
            $subskills = $this->buildSubskillScores(
                $skill,
                $latestSkillSubmission
            );

            $skillDetails[$skill] = [
                'completed' =>
                    $completedLessons,

                'total' =>
                    $totalLessons,

                'percentage' =>
                    $completionPercentage,

                'assessment_count' =>
                    $skillAttempts->count(),

                'average_score' =>
                    $averageSkillScore,

                'best_score' =>
                    $bestSkillScore,

                'first_score' =>
                    $firstScore,

                'latest_score' =>
                    $latestScore,

                'pretest_score' =>
                    $pretestScore,

                'posttest_score' =>
                    $posttestScore,

                'improvement' =>
                    $improvement,

                'improvement_basis' =>
                    $improvementBasis,

                'level' =>
                    $this->resolveEstimatedCefr(
                        $latestScore
                    ),

                'subskills' =>
                    $subskills,

                'subskills_available' =>
                    count($subskills) > 0,
            ];
        }

        $skillAverages =
            collect($skillDetails)
                ->mapWithKeys(
                    function ($detail, $skill) {
                        return [
                            $skill =>
                                $detail['percentage'],
                        ];
                    }
                )
                ->all();

        /*
        |--------------------------------------------------------------------------
        | Overall level
        |--------------------------------------------------------------------------
        */

        $latestSkillScores =
            collect(self::SKILLS)
                ->map(
                    function ($skill) use ($skillDetails) {
                        return $skillDetails[$skill]['latest_score']
                            ?? null;
                    }
                )
                ->filter(
                    fn ($score) =>
                        $score !== null
                )
                ->values();

        $overallCoverage =
            $latestSkillScores->count();

        $overallScore =
            $latestSkillScores->isNotEmpty()
                ? (int) round(
                    $latestSkillScores->avg()
                )
                : null;

        $overallLevel =
            $this->resolveEstimatedCefr(
                $overallScore
            );

        $bestSubmission =
            $completedAttempts
                ->sortByDesc(
                    'final_score'
                )
                ->first();

        $latestSubmission =
            $completedAttempts
                ->sortByDesc(
                    fn ($submission) =>
                        $this->submissionTimestamp(
                            $submission
                        )
                )
                ->first();

        $trendChart =
            $this->buildTrendChart(
                $completedAttempts
            );

        /*
        |--------------------------------------------------------------------------
        | History
        |--------------------------------------------------------------------------
        */

        $submissions =
            $this->filterHistory(
                $allSubmissions,
                $filters
            );

        $historyTotal =
            $allSubmissions->count();

        $historyFilteredTotal =
            $submissions->count();

        return view(
            'progress.index',
            compact(
                'submissions',
                'allSubmissions',
                'completed',
                'completedAttempts',
                'lessonProgress',
                'averageScore',
                'totalCompleted',
                'pretestAverage',
                'posttestAverage',
                'skillAverages',
                'skillDetails',
                'overallScore',
                'overallCoverage',
                'overallLevel',
                'bestSubmission',
                'latestSubmission',
                'trendChart',
                'filters',
                'historyTotal',
                'historyFilteredTotal'
            )
        );
    }

    /**
     * Menghasilkan nilai sub-skill dari submission terbaru.
     */
    private function buildSubskillScores(
        string $skill,
        ?AssessmentSubmission $submission
    ): array {
        if (!$submission) {
            return [];
        }

        $submission->loadMissing(
            'answers'
        );

        if ($skill === 'writing') {
            return $this->buildRubricSubskills(
                $submission->answers,
                [
                    'orientation_score' =>
                        'Orientation',

                    'complication_score' =>
                        'Complication',

                    'resolution_score' =>
                        'Resolution',

                    'organization_score' =>
                        'Organization',

                    'mechanics_score' =>
                        'Mechanics',
                ]
            );
        }

        if ($skill === 'speaking') {
            return $this->buildRubricSubskills(
                $submission->answers,
                [
                    'details_score' =>
                        'Content Relevance',

                    'fluency_score' =>
                        'Fluency',

                    'pronunciation_score' =>
                        'Pronunciation',

                    'vocabulary_score' =>
                        'Vocabulary',

                    'grammar_score' =>
                        'Grammar Accuracy',
                ]
            );
        }

        if (
            $skill === 'reading'
            ||
            $skill === 'listening'
        ) {
            return $this->buildObjectiveSubskills(
                $skill,
                $submission->answers
            );
        }

        return [];
    }

    /**
     * Rubric AI menggunakan skala 1–4.
     * Pada progress dikonversi menjadi 25–100.
     */
    private function buildRubricSubskills(
        Collection $answers,
        array $criteria
    ): array {
        $result = [];

        foreach ($criteria as $field => $label) {
            $values = $answers
                ->pluck($field)
                ->filter(
                    fn ($value) =>
                        $value !== null
                )
                ->map(
                    fn ($value) =>
                        max(
                            1,
                            min(
                                4,
                                (int) $value
                            )
                        )
                )
                ->values();

            if ($values->isEmpty()) {
                continue;
            }

            $rubricAverage =
                (float) $values->avg();

            $score = (int) round(
                (
                    $rubricAverage /
                    4
                ) * 100
            );

            $result[] = [
                'key' => $field,
                'label' => $label,
                'score' =>
                    max(
                        0,
                        min(
                            100,
                            $score
                        )
                    ),
                'items' =>
                    $values->count(),
            ];
        }

        return $result;
    }

    /**
     * Reading/Listening dihitung berdasarkan kategori sub_skill question.
     */
    private function buildObjectiveSubskills(
        string $skill,
        Collection $answers
    ): array {
        if ($answers->isEmpty()) {
            return [];
        }

        $questionIds = $answers
            ->pluck('question_id')
            ->filter()
            ->map(
                fn ($id) =>
                    (int) $id
            )
            ->unique()
            ->values();

        if ($questionIds->isEmpty()) {
            return [];
        }

        $questions =
            $skill === 'reading'
                ? ReadingQuestion::query()
                    ->whereIn(
                        'id',
                        $questionIds
                    )
                    ->get([
                        'id',
                        'sub_skill',
                    ])
                : ListeningQuestion::query()
                    ->whereIn(
                        'id',
                        $questionIds
                    )
                    ->get([
                        'id',
                        'sub_skill',
                    ]);

        $questionSubskills = $questions
            ->filter(
                function ($question) {
                    return trim(
                        (string) $question->sub_skill
                    ) !== '';
                }
            )
            ->mapWithKeys(
                function ($question) {
                    return [
                        (int) $question->id =>
                            trim(
                                (string) $question->sub_skill
                            ),
                    ];
                }
            );

        if ($questionSubskills->isEmpty()) {
            return [];
        }

        $grouped = [];

        foreach ($answers as $answer) {
            $questionId =
                (int) $answer->question_id;

            $subSkill =
                $questionSubskills->get(
                    $questionId
                );

            if (!$subSkill) {
                continue;
            }

            $key = strtolower(
                preg_replace(
                    '/[^a-z0-9]+/i',
                    '_',
                    $subSkill
                )
            );

            $key = trim(
                $key,
                '_'
            );

            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'label' =>
                        $this->humaniseSubskill(
                            $subSkill
                        ),

                    'earned' =>
                        0,

                    'maximum' =>
                        0,

                    'items' =>
                        0,
                ];
            }

            $grouped[$key]['earned'] +=
                max(
                    0,
                    (int) (
                        $answer->score
                        ?? 0
                    )
                );

            $grouped[$key]['maximum'] +=
                max(
                    0,
                    (int) (
                        $answer->max_score
                        ?? 0
                    )
                );

            $grouped[$key]['items']++;
        }

        $result = [];

        foreach ($grouped as $key => $data) {
            if ($data['maximum'] <= 0) {
                continue;
            }

            $score = (int) round(
                (
                    $data['earned'] /
                    $data['maximum']
                ) * 100
            );

            $result[] = [
                'key' =>
                    $key,

                'label' =>
                    $data['label'],

                'score' =>
                    max(
                        0,
                        min(
                            100,
                            $score
                        )
                    ),

                'items' =>
                    $data['items'],
            ];
        }

        usort(
            $result,
            fn ($a, $b) =>
                strcmp(
                    $a['label'],
                    $b['label']
                )
        );

        return $result;
    }

    private function humaniseSubskill(
        string $value
    ): string {
        $value = preg_replace(
            '/[_-]+/',
            ' ',
            trim($value)
        );

        return ucwords(
            strtolower(
                (string) $value
            )
        );
    }

    /**
     * Data grafik tahapan belajar.
     */
    private function buildTrendChart(
        Collection $completedAttempts
    ): array {
        $stageKeys =
            array_keys(
                self::STAGES
            );

        $datasets = [];
        $pointCount = 0;

        foreach (self::SKILLS as $skill) {
            $stageScores =
                array_fill_keys(
                    $stageKeys,
                    null
                );

            $skillAttempts =
                $completedAttempts
                    ->filter(
                        function ($submission) use ($skill) {
                            return strtolower(
                                trim(
                                    (string) $submission->skill
                                )
                            ) === $skill;
                        }
                    )
                    ->sortBy(
                        fn ($submission) =>
                            $this->submissionTimestamp(
                                $submission
                            )
                    )
                    ->values();

            foreach ($skillAttempts as $submission) {
                $stageKey =
                    $this->resolveStageKey(
                        $submission
                    );

                if (
                    !$stageKey
                    ||
                    !array_key_exists(
                        $stageKey,
                        $stageScores
                    )
                ) {
                    continue;
                }

                $stageScores[$stageKey] =
                    max(
                        0,
                        min(
                            100,
                            (int) $submission->final_score
                        )
                    );
            }

            $values =
                array_values(
                    $stageScores
                );

            $pointCount +=
                collect($values)
                    ->filter(
                        fn ($score) =>
                            $score !== null
                    )
                    ->count();

            $datasets[$skill] =
                $values;
        }

        return [
            'labels' =>
                array_values(
                    self::STAGES
                ),

            'datasets' =>
                $datasets,

            'point_count' =>
                $pointCount,

            'count' =>
                $pointCount,
        ];
    }

    /**
     * Mapping submission ke Pre-Test/Unit/Post-Test.
     */
    private function resolveStageKey(
        AssessmentSubmission $submission
    ): ?string {
        $type = strtolower(
            trim(
                (string) $submission->type
            )
        );

        if ($type === 'pretest') {
            return 'pretest';
        }

        if ($type === 'posttest') {
            return 'posttest';
        }

        if ($type !== 'unit') {
            return null;
        }

        $unitTitle = trim(
            (string) (
                $submission->unit?->title
                ??
                $submission->lesson?->unit?->title
                ??
                ''
            )
        );

        $unitNumber =
            $this->extractUnitNumber(
                $unitTitle
            );

        if ($unitNumber !== null) {
            return 'unit_' .
                $unitNumber;
        }

        $lessonTitle = trim(
            (string) (
                $submission->lesson?->title
                ??
                ''
            )
        );

        $unitNumber =
            $this->extractUnitNumber(
                $lessonTitle
            );

        if ($unitNumber !== null) {
            return 'unit_' .
                $unitNumber;
        }

        $orderNumber =
            $submission->unit?->order_number
            ??
            $submission->lesson?->unit?->order_number;

        if (
            $orderNumber !== null
            &&
            is_numeric(
                $orderNumber
            )
        ) {
            $number =
                (int) $orderNumber;

            if (
                $number >= 1
                &&
                $number <= 4
            ) {
                return 'unit_' .
                    $number;
            }
        }

        return null;
    }

    private function extractUnitNumber(
        ?string $text
    ): ?int {
        $text = trim(
            (string) $text
        );

        if ($text === '') {
            return null;
        }

        if (
            preg_match(
                '/\bunit[\s_-]*([1-4])\b/i',
                $text,
                $matches
            )
        ) {
            return (int) $matches[1];
        }

        return null;
    }

    private function averageByType(
        Collection $completed,
        string $type
    ): int {
        $items = $completed
            ->filter(
                function ($submission) use ($type) {
                    return strtolower(
                        trim(
                            (string) $submission->type
                        )
                    ) === $type;
                }
            );

        return $items->isNotEmpty()
            ? (int) round(
                $items->avg(
                    'final_score'
                )
            )
            : 0;
    }

    private function filterHistory(
        Collection $submissions,
        array $filters
    ): Collection {
        $filtered =
            $submissions;

        if ($filters['skill'] !== 'all') {
            $filtered =
                $filtered->filter(
                    function ($submission) use ($filters) {
                        return strtolower(
                            trim(
                                (string) $submission->skill
                            )
                        ) === $filters['skill'];
                    }
                );
        }

        if ($filters['type'] !== 'all') {
            $filtered =
                $filtered->filter(
                    function ($submission) use ($filters) {
                        return strtolower(
                            trim(
                                (string) $submission->type
                            )
                        ) === $filters['type'];
                    }
                );
        }

        if ($filters['status'] !== 'all') {
            $filtered =
                $filtered->filter(
                    function ($submission) use ($filters) {
                        return strtolower(
                            trim(
                                (string) $submission->status
                            )
                        ) === $filters['status'];
                    }
                );
        }

        $filtered = match (
            $filters['sort']
        ) {
            'oldest' =>
                $filtered->sortBy(
                    fn ($submission) =>
                        $this->submissionTimestamp(
                            $submission
                        )
                ),

            'highest' =>
                $filtered->sortByDesc(
                    function ($submission) {
                        return $submission->final_score !== null
                            ? (int) $submission->final_score
                            : -1;
                    }
                ),

            'lowest' =>
                $filtered->sortBy(
                    function ($submission) {
                        return $submission->final_score !== null
                            ? (int) $submission->final_score
                            : 101;
                    }
                ),

            default =>
                $filtered->sortByDesc(
                    fn ($submission) =>
                        $this->submissionTimestamp(
                            $submission
                        )
                ),
        };

        return $filtered
            ->values();
    }

    private function resolveFilter(
        string $value,
        array $allowed,
        string $default = 'all'
    ): string {
        if (
            $value === 'all'
            &&
            $default === 'all'
        ) {
            return 'all';
        }

        return in_array(
            $value,
            $allowed,
            true
        )
            ? $value
            : $default;
    }

    private function submissionTimestamp(
        AssessmentSubmission $submission
    ): int {
        $date =
            $submission->submitted_at
            ??
            $submission->created_at;

        return $date?->timestamp
            ?? 0;
    }

    private function resolveEstimatedCefr(
        ?int $score
    ): array {
        if ($score === null) {
            return [
                'code' =>
                    'N/A',

                'label' =>
                    'Not Assessed',

                'description' =>
                    'Complete an assessment to estimate your level.',
            ];
        }

        $score = max(
            0,
            min(
                100,
                $score
            )
        );

        return match (true) {
            $score >= 90 => [
                'code' => 'C2',
                'label' => 'Proficient',
                'description' =>
                    'Highly proficient English performance.',
            ],

            $score >= 75 => [
                'code' => 'C1',
                'label' => 'Advanced',
                'description' =>
                    'Advanced English performance.',
            ],

            $score >= 60 => [
                'code' => 'B2',
                'label' => 'Upper Intermediate',
                'description' =>
                    'Independent upper-intermediate performance.',
            ],

            $score >= 45 => [
                'code' => 'B1',
                'label' => 'Intermediate',
                'description' =>
                    'Independent intermediate performance.',
            ],

            $score >= 30 => [
                'code' => 'A2',
                'label' => 'Elementary',
                'description' =>
                    'Basic everyday English performance.',
            ],

            default => [
                'code' => 'A1',
                'label' => 'Beginner',
                'description' =>
                    'Foundational English performance.',
            ],
        };
    }

    /**
     * Sinkronisasi completed assessment ke UserLessonProgress.
     */
    private function syncCompletedAssessments(
        Collection $completedSubmissions,
        int $userId
    ): void {
        foreach ($completedSubmissions as $submission) {
            $skill = strtolower(
                trim(
                    (string) $submission->skill
                )
            );

            if (
                !in_array(
                    $skill,
                    self::SKILLS,
                    true
                )
            ) {
                continue;
            }

            if (!$submission->lesson_id) {
                continue;
            }

            $unitId =
                $submission->unit_id
                ?:
                $submission->lesson?->unit_id;

            if (!$unitId) {
                continue;
            }

            UserLessonProgress::updateOrCreate(
                [
                    'user_id' =>
                        $userId,

                    'lesson_id' =>
                        (int) $submission->lesson_id,

                    'skill_type' =>
                        $skill,
                ],
                [
                    'unit_id' =>
                        (int) $unitId,

                    'status' =>
                        'completed',

                    'score' =>
                        max(
                            0,
                            min(
                                100,
                                (int) $submission->final_score
                            )
                        ),

                    'completed_at' =>
                        $submission->submitted_at
                        ??
                        $submission->created_at
                        ??
                        now(),
                ]
            );
        }
    }
}
