<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssessmentSubmission;
use App\Models\User;
use App\Models\VocabularyPretestResult;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class AnalyticsController extends Controller
{
    /**
     * Daftar skill yang digunakan pada assessment.
     */
    private const SKILLS = [
        'listening',
        'reading',
        'writing',
        'speaking',
    ];

    /**
     * Menampilkan halaman Analytics Admin.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));

        /*
        |--------------------------------------------------------------------------
        | Statistik utama
        |--------------------------------------------------------------------------
        */

        $totalStudents = User::query()
            ->where('role', '!=', 'admin')
            ->count();

        $completedAssessments = AssessmentSubmission::query()
            ->whereHas('user', function ($query) {
                $query->where('role', '!=', 'admin');
            })
            ->where('status', 'completed')
            ->whereIn('type', ['pretest', 'posttest'])
            ->count();

        $completedVocabularyPretests = VocabularyPretestResult::query()
            ->whereHas('user', function ($query) {
                $query->where('role', '!=', 'admin');
            })
            ->distinct('user_id')
            ->count('user_id');

        $totalCompletedTests = $completedAssessments
            + $completedVocabularyPretests;

        $averagePretest = $this->averageScoreByType('pretest');

        $averagePosttest = $this->averageScoreByType('posttest');

        $averageImprovement = $this->calculateAverageImprovement();

        /*
        |--------------------------------------------------------------------------
        | Performa setiap skill
        |--------------------------------------------------------------------------
        */

        $skillPerformance = collect(self::SKILLS)
            ->map(function (string $skill) {
                return [
                    'skill' => $skill,
                    'label' => ucfirst($skill),
                    'pretest' => $this->averageSkillScore(
                        $skill,
                        'pretest'
                    ),
                    'posttest' => $this->averageSkillScore(
                        $skill,
                        'posttest'
                    ),
                ];
            });

        /*
        |--------------------------------------------------------------------------
        | Aktivitas tujuh hari terakhir
        |--------------------------------------------------------------------------
        */

        $weeklyActivity = $this->getWeeklyActivity();

        /*
        |--------------------------------------------------------------------------
        | Data nilai setiap siswa
        |--------------------------------------------------------------------------
        */

        $students = User::query()
            ->where('role', '!=', 'admin')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });
            })
            ->with([
                'assessmentSubmissions' => function ($query) {
                    $query
                        ->where('status', 'completed')
                        ->whereIn('type', ['pretest', 'posttest'])
                        ->orderByDesc('submitted_at')
                        ->orderByDesc('id');
                },

                'vocabularyPretestResults' => function ($query) {
                    $query
                        ->orderByDesc('created_at')
                        ->orderByDesc('id');
                },

                'lessonProgress' => function ($query) {
                    $query
                        ->where('status', 'completed')
                        ->orderByDesc('completed_at')
                        ->orderByDesc('id');
                },
            ])
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $students->setCollection(
            $students->getCollection()
                ->map(function (User $user) {
                    return $this->buildStudentAnalytics($user);
                })
        );

        return view('admin.analytics.analytic', compact(
            'search',
            'totalStudents',
            'totalCompletedTests',
            'averagePretest',
            'averagePosttest',
            'averageImprovement',
            'skillPerformance',
            'weeklyActivity',
            'students'
        ));
    }

    /**
     * Menghitung rata-rata nilai berdasarkan tipe assessment.
     */
    private function averageScoreByType(string $type): float
    {
        return round(
            (float) AssessmentSubmission::query()
                ->whereHas('user', function ($query) {
                    $query->where('role', '!=', 'admin');
                })
                ->where('status', 'completed')
                ->where('type', $type)
                ->whereNotNull('final_score')
                ->avg('final_score'),
            1
        );
    }

    /**
     * Menghitung rata-rata nilai berdasarkan skill dan tipe assessment.
     */
    private function averageSkillScore(
        string $skill,
        string $type
    ): float {
        return round(
            (float) AssessmentSubmission::query()
                ->whereHas('user', function ($query) {
                    $query->where('role', '!=', 'admin');
                })
                ->where('status', 'completed')
                ->where('skill', $skill)
                ->where('type', $type)
                ->whereNotNull('final_score')
                ->avg('final_score'),
            1
        );
    }

    /**
     * Menghitung rata-rata peningkatan nilai dari pretest ke posttest.
     *
     * Perhitungan hanya dilakukan ketika siswa memiliki nilai pretest
     * dan posttest pada skill yang sama.
     */
    private function calculateAverageImprovement(): float
    {
        $submissions = AssessmentSubmission::query()
            ->whereHas('user', function ($query) {
                $query->where('role', '!=', 'admin');
            })
            ->where('status', 'completed')
            ->whereIn('type', ['pretest', 'posttest'])
            ->whereNotNull('final_score')
            ->orderByDesc('submitted_at')
            ->orderByDesc('id')
            ->get([
                'id',
                'user_id',
                'type',
                'skill',
                'final_score',
                'submitted_at',
            ]);

        $improvements = collect();

        $submissions
            ->groupBy('user_id')
            ->each(function (Collection $userSubmissions) use (
                $improvements
            ) {
                foreach (self::SKILLS as $skill) {
                    $pretest = $userSubmissions
                        ->first(function ($submission) use ($skill) {
                            return $submission->skill === $skill
                                && $submission->type === 'pretest';
                        });

                    $posttest = $userSubmissions
                        ->first(function ($submission) use ($skill) {
                            return $submission->skill === $skill
                                && $submission->type === 'posttest';
                        });

                    if ($pretest && $posttest) {
                        $improvements->push(
                            (float) $posttest->final_score
                            - (float) $pretest->final_score
                        );
                    }
                }
            });

        if ($improvements->isEmpty()) {
            return 0;
        }

        return round((float) $improvements->average(), 1);
    }

    /**
     * Mengambil jumlah aktivitas pretest, posttest,
     * dan Vocabulary Pretest selama tujuh hari terakhir.
     */
    private function getWeeklyActivity(): Collection
    {
        return collect(range(6, 0))
            ->map(function (int $daysAgo) {
                $date = Carbon::today()->subDays($daysAgo);

                $assessmentCount = AssessmentSubmission::query()
                    ->whereHas('user', function ($query) {
                        $query->where('role', '!=', 'admin');
                    })
                    ->where('status', 'completed')
                    ->whereIn('type', ['pretest', 'posttest'])
                    ->whereDate('submitted_at', $date->toDateString())
                    ->count();

                $vocabularyCount = VocabularyPretestResult::query()
                    ->whereHas('user', function ($query) {
                        $query->where('role', '!=', 'admin');
                    })
                    ->whereDate('created_at', $date->toDateString())
                    ->distinct('user_id')
                    ->count('user_id');

                return [
                    'date' => $date->toDateString(),
                    'label' => $date->translatedFormat('D'),
                    'full_label' => $date->translatedFormat('d M Y'),
                    'count' => $assessmentCount + $vocabularyCount,
                ];
            });
    }

    /**
     * Menyusun data Analytics untuk satu siswa.
     */
    private function buildStudentAnalytics(User $user): array
    {
        /*
         * Submission sudah diurutkan dari yang terbaru.
         * Jika terdapat data ganda, nilai yang digunakan adalah yang terbaru.
         */
        $latestSubmissions = $user->assessmentSubmissions
            ->unique(function ($submission) {
                return $submission->type . '-' . $submission->skill;
            });

        $scores = [];

        foreach (self::SKILLS as $skill) {
            $scores[$skill] = [
                'pretest' => $this->findSubmissionScore(
                    $latestSubmissions,
                    $skill,
                    'pretest'
                ),

                'posttest' => $this->findSubmissionScore(
                    $latestSubmissions,
                    $skill,
                    'posttest'
                ),
            ];
        }

        $vocabularyScore = optional(
            $user->vocabularyPretestResults->first()
        )->score;

        $pretestScores = collect(self::SKILLS)
            ->map(function (string $skill) use ($scores) {
                return $scores[$skill]['pretest'];
            })
            ->filter(function ($score) {
                return $score !== null;
            });

        $posttestScores = collect(self::SKILLS)
            ->map(function (string $skill) use ($scores) {
                return $scores[$skill]['posttest'];
            })
            ->filter(function ($score) {
                return $score !== null;
            });

        $allScores = collect();

        if ($vocabularyScore !== null) {
            $allScores->push((float) $vocabularyScore);
        }

        $allScores = $allScores
            ->merge($pretestScores)
            ->merge($posttestScores);

        $averagePretest = $pretestScores->isNotEmpty()
            ? round((float) $pretestScores->average(), 1)
            : null;

        $averagePosttest = $posttestScores->isNotEmpty()
            ? round((float) $posttestScores->average(), 1)
            : null;

        $improvement = $averagePretest !== null
            && $averagePosttest !== null
                ? round($averagePosttest - $averagePretest, 1)
                : null;

        $completedAssessmentCount = $latestSubmissions
            ->filter(function ($submission) {
                return $submission->final_score !== null;
            })
            ->count();

        if ($vocabularyScore !== null) {
            $completedAssessmentCount++;
        }

        /*
         * Total assessment:
         * 1 Vocabulary Pretest
         * 4 Skill Pretest
         * 4 Skill Posttest
         */
        $totalRequiredAssessments = 9;

        $completionPercentage = round(
            ($completedAssessmentCount / $totalRequiredAssessments) * 100
        );

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'initial' => strtoupper(
                mb_substr($user->name ?: $user->email, 0, 1)
            ),

            'vocabulary' => $vocabularyScore !== null
                ? (float) $vocabularyScore
                : null,

            'scores' => $scores,

            'average_pretest' => $averagePretest,
            'average_posttest' => $averagePosttest,

            'average_score' => $allScores->isNotEmpty()
                ? round((float) $allScores->average(), 1)
                : null,

            'improvement' => $improvement,

            'completed_assessments' => $completedAssessmentCount,
            'total_required_assessments' => $totalRequiredAssessments,
            'completion_percentage' => min(
                $completionPercentage,
                100
            ),

            'completed_lessons' => $user->lessonProgress->count(),
        ];
    }

    /**
     * Mencari nilai submission berdasarkan skill dan type.
     */
    private function findSubmissionScore(
        Collection $submissions,
        string $skill,
        string $type
    ): ?float {
        $submission = $submissions
            ->first(function ($submission) use ($skill, $type) {
                return $submission->skill === $skill
                    && $submission->type === $type;
            });

        if (!$submission || $submission->final_score === null) {
            return null;
        }

        return (float) $submission->final_score;
    }
}