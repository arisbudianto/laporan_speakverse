<?php

namespace App\Http\Controllers;

use App\Models\AssessmentSubmission;
use App\Models\Unit;
use App\Models\UserLessonProgress;
use App\Services\GamificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StudentDashboardController extends Controller
{
    /**
     * Daftar skill yang tersedia dalam setiap tahap pembelajaran.
     */
    private const SKILLS = [
        'listening',
        'reading',
        'writing',
        'speaking',
    ];

    /**
     * Menampilkan dashboard siswa.
     */
    public function index(
        GamificationService $gamificationService
    ): View|RedirectResponse {
        $user = Auth::user();

        /*
        |--------------------------------------------------------------------------
        | Redirect admin
        |--------------------------------------------------------------------------
        */

        if ($user->role === 'admin') {
            return redirect()
                ->route('admin.dashboard');
        }

        if ($user->role === 'teacher') {
            return redirect()
                ->route('teacher.dashboard');
        }

        $userId = $user->id;

        /*
        |--------------------------------------------------------------------------
        | Sinkronisasi gamification
        |--------------------------------------------------------------------------
        |
        | Progress lama yang sudah ada sebelum sistem gamifikasi dibuat
        | akan dikonversi menjadi XP, SpeakCoins, badge, level, dan streak.
        |
        | Aman dipanggil berulang kali karena setiap reward memakai
        | event_key unik.
        |
        */

        $gamificationService
            ->syncHistoricalProgress(
                $user
            );

        $user->refresh();

        $gamification =
            $gamificationService
                ->overview(
                    $user
                );

        /*
        |--------------------------------------------------------------------------
        | Mengambil seluruh tahap pembelajaran
        |--------------------------------------------------------------------------
        */

        $units = Unit::query()
            ->with([
                'lessons' => function ($query) {
                    $query
                        ->where(
                            'status',
                            'active'
                        )
                        ->orderBy(
                            'order_number'
                        );
                },
            ])
            ->where(
                'status',
                'active'
            )
            ->orderBy(
                'order_number'
            )
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Progress lesson Unit 1–4
        |--------------------------------------------------------------------------
        */

        $lessonProgress =
            UserLessonProgress::query()
                ->where(
                    'user_id',
                    $userId
                )
                ->where(
                    'status',
                    'completed'
                )
                ->get();

        /*
        |--------------------------------------------------------------------------
        | Progress Pre-test dan Post-test
        |--------------------------------------------------------------------------
        */

        $assessmentSubmissions =
            AssessmentSubmission::query()
                ->where(
                    'user_id',
                    $userId
                )
                ->where(
                    'status',
                    'completed'
                )
                ->whereNotNull(
                    'final_score'
                )
                ->get();

        /*
        |--------------------------------------------------------------------------
        | Membentuk data progress setiap tahap
        |--------------------------------------------------------------------------
        */

        $stageData = [];

        $previousStageCompleted =
            true;

        foreach (
            $units as $index => $unit
        ) {
            /*
            |--------------------------------------------------------------------------
            | Unit biasa
            |--------------------------------------------------------------------------
            */

            if (
                $unit->type === 'unit'
            ) {
                $completedSkills =
                    $lessonProgress
                        ->where(
                            'unit_id',
                            $unit->id
                        )
                        ->pluck(
                            'skill_type'
                        );
            }

            /*
            |--------------------------------------------------------------------------
            | Pre-Test / Post-Test
            |--------------------------------------------------------------------------
            */

            else {
                $completedSkills =
                    $assessmentSubmissions
                        ->where(
                            'unit_id',
                            $unit->id
                        )
                        ->where(
                            'type',
                            $unit->type
                        )
                        ->pluck(
                            'skill'
                        );
            }

            /*
            |--------------------------------------------------------------------------
            | Bersihkan skill
            |--------------------------------------------------------------------------
            |
            | unique() mencegah duplicate submission membuat progress
            | menjadi lebih dari 100%.
            |
            */

            $completedSkills =
                $completedSkills
                    ->filter(
                        fn ($skill) =>
                            in_array(
                                $skill,
                                self::SKILLS,
                                true
                            )
                    )
                    ->unique()
                    ->values();

            $completedCount =
                $completedSkills
                    ->count();

            /*
            |--------------------------------------------------------------------------
            | Progress percentage
            |--------------------------------------------------------------------------
            */

            $progressPercentage =
                count(self::SKILLS) > 0
                    ? (int) round(
                        (
                            $completedCount
                            /
                            count(self::SKILLS)
                        )
                        *
                        100
                    )
                    : 0;

            $isCompleted =
                $completedCount
                >=
                count(self::SKILLS);

            /*
            |--------------------------------------------------------------------------
            | Unlock stage
            |--------------------------------------------------------------------------
            |
            | Tahap pertama selalu terbuka.
            |
            | Tahap berikutnya hanya terbuka jika tahap sebelumnya
            | sudah selesai.
            |
            */

            $isUnlocked =
                $index === 0
                    ? true
                    : $previousStageCompleted;

            /*
            |--------------------------------------------------------------------------
            | Skill berikutnya
            |--------------------------------------------------------------------------
            */

            $nextSkill =
                collect(
                    self::SKILLS
                )
                    ->first(
                        fn ($skill) =>
                            !$completedSkills
                                ->contains(
                                    $skill
                                )
                    );

            /*
            |--------------------------------------------------------------------------
            | URL tindakan
            |--------------------------------------------------------------------------
            */

            $actionUrl =
                $this->resolveStageUrl(
                    $unit,
                    $nextSkill,
                    $isUnlocked,
                    $isCompleted
                );

            /*
            |--------------------------------------------------------------------------
            | Stage data
            |--------------------------------------------------------------------------
            */

            $stageData[
                $unit->id
            ] = [
                'completed_skills' =>
                    $completedSkills
                        ->toArray(),

                'completed_count' =>
                    $completedCount,

                'total_skills' =>
                    count(
                        self::SKILLS
                    ),

                'progress' =>
                    min(
                        $progressPercentage,
                        100
                    ),

                'is_completed' =>
                    $isCompleted,

                'is_unlocked' =>
                    $isUnlocked,

                'next_skill' =>
                    $nextSkill,

                'action_url' =>
                    $actionUrl,
            ];

            /*
             * Digunakan untuk menentukan unlock stage berikutnya.
             */
            $previousStageCompleted =
                $isCompleted;
        }

        /*
        |--------------------------------------------------------------------------
        | Statistik keseluruhan
        |--------------------------------------------------------------------------
        */

        $completedSkillTotal =
            collect(
                $stageData
            )
                ->sum(
                    'completed_count'
                );

        $totalMissions =
            $units->count()
            *
            count(
                self::SKILLS
            );

        $overallProgress =
            $totalMissions > 0
                ? (int) round(
                    (
                        $completedSkillTotal
                        /
                        $totalMissions
                    )
                    *
                    100
                )
                : 0;

        $completedStages =
            collect(
                $stageData
            )
                ->where(
                    'is_completed',
                    true
                )
                ->count();

        $totalStages =
            $units->count();

        /*
        |--------------------------------------------------------------------------
        | Gamification Statistics
        |--------------------------------------------------------------------------
        |
        | Sebelumnya XP dihitung dari jumlah skill × 100.
        |
        | Sekarang XP merupakan nilai permanen yang tersimpan
        | pada tabel users dan transaksi gamification.
        |
        */

        $totalXp =
            (int) $gamification[
                'xp'
            ];

        $totalCoins =
            (int) $gamification[
                'coins'
            ];

        $dailyStreak =
            (int) $gamification[
                'current_streak'
            ];

        /*
        |--------------------------------------------------------------------------
        | Level
        |--------------------------------------------------------------------------
        */

        $currentLevel =
            'Level '
            .
            $gamification[
                'level'
            ]['level'];

        $currentLevelName =
            $gamification[
                'level'
            ]['name'];

        /*
        |--------------------------------------------------------------------------
        | Tahap berikutnya
        |--------------------------------------------------------------------------
        */

        $nextStage =
            $units->first(
                function (
                    $unit
                ) use (
                    $stageData
                ) {
                    $data =
                        $stageData[
                            $unit->id
                        ];

                    return
                        $data[
                            'is_unlocked'
                        ]
                        &&
                        !$data[
                            'is_completed'
                        ];
                }
            );

        /*
        |--------------------------------------------------------------------------
        | Tombol Continue Learning
        |--------------------------------------------------------------------------
        */

        if ($nextStage) {
            $nextActionUrl =
                $stageData[
                    $nextStage->id
                ]['action_url'];

            $nextActionLabel =
                'Continue Learning';
        } elseif (
            $units->isNotEmpty()
        ) {
            $nextActionUrl =
                route(
                    'progress'
                );

            $nextActionLabel =
                'View Progress';
        } else {
            $nextActionUrl =
                route(
                    'missions'
                );

            $nextActionLabel =
                'Get Started';
        }

        /*
        |--------------------------------------------------------------------------
        | Return Dashboard
        |--------------------------------------------------------------------------
        */

        return view(
            'dashboard',
            [
                /*
                |--------------------------------------------------------------------------
                | User
                |--------------------------------------------------------------------------
                */

                'user' =>
                    $user,

                /*
                |--------------------------------------------------------------------------
                | Learning Path
                |--------------------------------------------------------------------------
                */

                'units' =>
                    $units,

                'stageData' =>
                    $stageData,

                'skills' =>
                    self::SKILLS,

                /*
                |--------------------------------------------------------------------------
                | Gamification
                |--------------------------------------------------------------------------
                */

                'gamification' =>
                    $gamification,

                'totalXp' =>
                    $totalXp,

                'totalCoins' =>
                    $totalCoins,

                'dailyStreak' =>
                    $dailyStreak,

                'currentLevel' =>
                    $currentLevel,

                'currentLevelName' =>
                    $currentLevelName,

                /*
                |--------------------------------------------------------------------------
                | Progress
                |--------------------------------------------------------------------------
                */

                'completedMissions' =>
                    $completedSkillTotal,

                'totalMissions' =>
                    $totalMissions,

                'completedStages' =>
                    $completedStages,

                'totalStages' =>
                    $totalStages,

                'overallProgress' =>
                    $overallProgress,

                /*
                |--------------------------------------------------------------------------
                | Next Stage
                |--------------------------------------------------------------------------
                */

                'nextStage' =>
                    $nextStage,

                'nextActionUrl' =>
                    $nextActionUrl,

                'nextActionLabel' =>
                    $nextActionLabel,
            ]
        );
    }

    /**
     * Menentukan URL tujuan setiap tahap.
     */
    private function resolveStageUrl(
        Unit $unit,
        ?string $nextSkill,
        bool $isUnlocked,
        bool $isCompleted
    ): string {
        /*
        |--------------------------------------------------------------------------
        | Locked
        |--------------------------------------------------------------------------
        */

        if (!$isUnlocked) {
            return '#';
        }

        /*
        |--------------------------------------------------------------------------
        | Stage sudah selesai
        |--------------------------------------------------------------------------
        */

        if (
            $isCompleted
            ||
            !$nextSkill
        ) {
            return route(
                'missions'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Pre-Test / Post-Test
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $unit->type,
                [
                    'pretest',
                    'posttest',
                ],
                true
            )
        ) {
            return route(
                'student.assessment.show',
                [
                    'type' =>
                        $unit->type,

                    'skill' =>
                        $nextSkill,
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Unit 1–4
        |--------------------------------------------------------------------------
        */

        $lesson =
            $unit
                ->lessons
                ->firstWhere(
                    'skill_type',
                    $nextSkill
                );

        /*
         * Jika lesson belum tersedia.
         */
        if (!$lesson) {
            return route(
                'missions'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Route per skill
        |--------------------------------------------------------------------------
        */

        return match (
            $nextSkill
        ) {
            'listening' =>
                route(
                    'student.listening',
                    [
                        'lesson' =>
                            $lesson->id,
                    ]
                ),

            'reading' =>
                route(
                    'student.reading',
                    [
                        'lesson' =>
                            $lesson->id,
                    ]
                ),

            'writing' =>
                route(
                    'student.writing',
                    [
                        'lesson' =>
                            $lesson->id,
                    ]
                ),

            'speaking' =>
                route(
                    'student.speaking',
                    [
                        'lesson' =>
                            $lesson->id,
                    ]
                ),

            default =>
                route(
                    'missions'
                ),
        };
    }
}