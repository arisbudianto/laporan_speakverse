<?php

namespace App\Services;

use App\Models\AssessmentSubmission;
use App\Models\Badge;
use App\Models\GamificationTransaction;
use App\Models\Lesson;
use App\Models\Unit;
use App\Models\User;
use App\Models\UserLessonProgress;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GamificationService
{
    /**
     * Setiap 500 XP naik satu level.
     */
    public const XP_PER_LEVEL = 500;

    /**
     * Skill utama SpeakVerse.
     */
    private const SKILLS = [
        'listening',
        'reading',
        'writing',
        'speaking',
    ];

    /**
     * Reward setiap menyelesaikan lesson Unit 1–4.
     */
    private const LESSON_REWARDS = [
        'listening' => [
            'xp' => 30,
            'coins' => 10,
        ],

        'reading' => [
            'xp' => 30,
            'coins' => 10,
        ],

        'writing' => [
            'xp' => 40,
            'coins' => 15,
        ],

        'speaking' => [
            'xp' => 40,
            'coins' => 15,
        ],
    ];

    /**
     * Reward Pre-Test dan Post-Test per skill.
     */
    private const ASSESSMENT_REWARDS = [
        'pretest' => [
            'xp' => 50,
            'coins' => 20,
        ],

        'posttest' => [
            'xp' => 75,
            'coins' => 30,
        ],
    ];

    /**
     * Bonus setelah seluruh skill dalam satu Unit selesai.
     */
    private const UNIT_COMPLETION_REWARD = [
        'xp' => 100,
        'coins' => 50,
    ];

    /**
     * Memberikan reward untuk penyelesaian satu skill
     * pada Unit 1–4.
     */
    public function rewardLessonCompletion(
        User $user,
        Lesson $lesson,
        string $skill,
        ?int $score = null
    ): array {
        $skill = strtolower(
            trim($skill)
        );

        if (
            !array_key_exists(
                $skill,
                self::LESSON_REWARDS
            )
        ) {
            return $this->emptyRewardPayload(
                $user
            );
        }

        $lesson->loadMissing('unit');

        /*
        |--------------------------------------------------------------------------
        | Hanya berlaku untuk Unit 1–4
        |--------------------------------------------------------------------------
        */

        if (
            !$lesson->unit
            ||
            $lesson->unit->type !== 'unit'
        ) {
            return $this->emptyRewardPayload(
                $user
            );
        }

        $payload = DB::transaction(
            function () use (
                $user,
                $lesson,
                $skill,
                $score
            ) {
                /*
                |--------------------------------------------------------------------------
                | Lock user
                |--------------------------------------------------------------------------
                |
                | Mencegah race-condition ketika dua request reward masuk
                | hampir bersamaan.
                |
                */

                $lockedUser = User::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $user->id
                    );

                $levelBefore = max(
                    1,
                    (int) $lockedUser->level
                );

                $xpEarned = 0;
                $coinsEarned = 0;

                $reward =
                    self::LESSON_REWARDS[
                        $skill
                    ];

                /*
                |--------------------------------------------------------------------------
                | Event key lesson
                |--------------------------------------------------------------------------
                |
                | Contoh:
                |
                | lesson:15:reading:completed
                |
                | Event yang sama tidak dapat memberikan reward dua kali.
                |
                */

                $eventKey = sprintf(
                    'lesson:%d:%s:completed',
                    $lesson->id,
                    $skill
                );

                /*
                |--------------------------------------------------------------------------
                | XP Lesson
                |--------------------------------------------------------------------------
                */

                $xpEarned +=
                    $this->grantTransaction(
                        $lockedUser,
                        'xp',
                        (int) $reward['xp'],
                        ucfirst($skill)
                            . ' lesson completed',
                        $eventKey,
                        [
                            'lesson_id' =>
                                $lesson->id,

                            'unit_id' =>
                                $lesson->unit_id,

                            'skill' =>
                                $skill,

                            'score' =>
                                $score,
                        ]
                    );

                /*
                |--------------------------------------------------------------------------
                | SpeakCoins Lesson
                |--------------------------------------------------------------------------
                */

                $coinsEarned +=
                    $this->grantTransaction(
                        $lockedUser,
                        'coin',
                        (int) $reward['coins'],
                        ucfirst($skill)
                            . ' lesson completed',
                        $eventKey,
                        [
                            'lesson_id' =>
                                $lesson->id,

                            'unit_id' =>
                                $lesson->unit_id,

                            'skill' =>
                                $skill,

                            'score' =>
                                $score,
                        ]
                    );

                /*
                |--------------------------------------------------------------------------
                | Cek apakah Unit selesai
                |--------------------------------------------------------------------------
                */

                $unitCompletedNow = false;

                if (
                    $this->isUnitCompleted(
                        $lockedUser->id,
                        (int) $lesson->unit_id
                    )
                ) {
                    $unitEventKey = sprintf(
                        'unit:%d:completed',
                        $lesson->unit_id
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | Bonus XP Unit
                    |--------------------------------------------------------------------------
                    */

                    $unitXp =
                        $this->grantTransaction(
                            $lockedUser,
                            'xp',
                            self::UNIT_COMPLETION_REWARD[
                                'xp'
                            ],
                            'Unit completed',
                            $unitEventKey,
                            [
                                'unit_id' =>
                                    $lesson->unit_id,
                            ]
                        );

                    /*
                    |--------------------------------------------------------------------------
                    | Bonus SpeakCoins Unit
                    |--------------------------------------------------------------------------
                    */

                    $unitCoins =
                        $this->grantTransaction(
                            $lockedUser,
                            'coin',
                            self::UNIT_COMPLETION_REWARD[
                                'coins'
                            ],
                            'Unit completed',
                            $unitEventKey,
                            [
                                'unit_id' =>
                                    $lesson->unit_id,
                            ]
                        );

                    $xpEarned +=
                        $unitXp;

                    $coinsEarned +=
                        $unitCoins;

                    /*
                     * True hanya ketika bonus Unit benar-benar
                     * baru diberikan pada request ini.
                     */
                    $unitCompletedNow =
                        $unitXp > 0
                        ||
                        $unitCoins > 0;
                }

                /*
                |--------------------------------------------------------------------------
                | Update level
                |--------------------------------------------------------------------------
                */

                $this->refreshLevel(
                    $lockedUser
                );

                /*
                |--------------------------------------------------------------------------
                | Cek badge
                |--------------------------------------------------------------------------
                */

                $newBadges =
                    $this->evaluateAndUnlockBadges(
                        $lockedUser
                    );

                /*
                 * Setelah badge dicek, refresh level sekali lagi
                 * untuk memastikan nilai terbaru.
                 */
                $this->refreshLevel(
                    $lockedUser
                );

                $lockedUser->refresh();

                return $this->buildRewardPayload(
                    $lockedUser,
                    $levelBefore,
                    $xpEarned,
                    $coinsEarned,
                    $newBadges,
                    $unitCompletedNow,
                    $lesson->unit,
                    $skill,
                    $score
                );
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Sinkronisasi streak
        |--------------------------------------------------------------------------
        */

        $this->syncStreakFromHistory(
            $user->fresh()
        );

        /*
        |--------------------------------------------------------------------------
        | Cek badge streak
        |--------------------------------------------------------------------------
        */

        $freshUser =
            $user->fresh();

        $streakBadges = DB::transaction(
            function () use ($freshUser) {
                $lockedUser = User::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $freshUser->id
                    );

                return $this
                    ->evaluateAndUnlockBadges(
                        $lockedUser
                    );
            }
        );

        /*
         * Jika streak baru menghasilkan badge,
         * gabungkan dengan popup reward.
         */
        if (!empty($streakBadges)) {
            $payload['badges'] =
                collect(
                    $payload['badges']
                )
                    ->merge(
                        $streakBadges
                    )
                    ->unique('code')
                    ->values()
                    ->all();

            $payload['show_popup'] =
                true;

            if (
                !$payload['unit_completed']
                &&
                !$payload['level_up']
            ) {
                $payload['title'] =
                    'Achievement Unlocked!';

                $payload['message'] =
                    'Nice! You just unlocked a new achievement.';
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Refresh data final user
        |--------------------------------------------------------------------------
        */

        $freshUser =
            $user->fresh();

        $payload['total_xp'] =
            (int) $freshUser->xp;

        $payload['total_coins'] =
            (int) $freshUser->coins;

        $payload['current_streak'] =
            (int) $freshUser
                ->current_streak;

        return $payload;
    }

    /**
     * Memberikan reward Pre-Test / Post-Test
     * untuk setiap skill.
     */
    public function rewardAssessmentCompletion(
        User $user,
        AssessmentSubmission $submission
    ): array {
        $type = strtolower(
            trim(
                (string) $submission->type
            )
        );

        $skill = strtolower(
            trim(
                (string) $submission->skill
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Validasi assessment
        |--------------------------------------------------------------------------
        */

        if (
            !array_key_exists(
                $type,
                self::ASSESSMENT_REWARDS
            )
            ||
            !in_array(
                $skill,
                self::SKILLS,
                true
            )
            ||
            strtolower(
                (string) $submission->status
            ) !== 'completed'
        ) {
            return $this->emptyRewardPayload(
                $user
            );
        }

        $payload = DB::transaction(
            function () use (
                $user,
                $submission,
                $type,
                $skill
            ) {
                $lockedUser = User::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $user->id
                    );

                $levelBefore = max(
                    1,
                    (int) $lockedUser->level
                );

                $reward =
                    self::ASSESSMENT_REWARDS[
                        $type
                    ];

                /*
                |--------------------------------------------------------------------------
                | Event key assessment
                |--------------------------------------------------------------------------
                |
                | Tidak memakai submission ID agar assessment yang sama
                | tidak bisa farming XP dengan submission baru.
                |
                */

                $eventKey = sprintf(
                    'assessment:%s:%s:completed',
                    $type,
                    $skill
                );

                /*
                |--------------------------------------------------------------------------
                | XP Assessment
                |--------------------------------------------------------------------------
                */

                $xpEarned =
                    $this->grantTransaction(
                        $lockedUser,
                        'xp',
                        (int) $reward['xp'],
                        ucfirst($type)
                            . ' '
                            . ucfirst($skill)
                            . ' completed',
                        $eventKey,
                        [
                            'submission_id' =>
                                $submission->id,

                            'unit_id' =>
                                $submission->unit_id,

                            'lesson_id' =>
                                $submission->lesson_id,

                            'type' =>
                                $type,

                            'skill' =>
                                $skill,

                            'score' =>
                                $submission
                                    ->final_score,
                        ]
                    );

                /*
                |--------------------------------------------------------------------------
                | SpeakCoins Assessment
                |--------------------------------------------------------------------------
                */

                $coinsEarned =
                    $this->grantTransaction(
                        $lockedUser,
                        'coin',
                        (int) $reward['coins'],
                        ucfirst($type)
                            . ' '
                            . ucfirst($skill)
                            . ' completed',
                        $eventKey,
                        [
                            'submission_id' =>
                                $submission->id,

                            'unit_id' =>
                                $submission->unit_id,

                            'lesson_id' =>
                                $submission->lesson_id,

                            'type' =>
                                $type,

                            'skill' =>
                                $skill,

                            'score' =>
                                $submission
                                    ->final_score,
                        ]
                    );

                /*
                |--------------------------------------------------------------------------
                | Level + Badge
                |--------------------------------------------------------------------------
                */

                $this->refreshLevel(
                    $lockedUser
                );

                $newBadges =
                    $this->evaluateAndUnlockBadges(
                        $lockedUser
                    );

                $this->refreshLevel(
                    $lockedUser
                );

                $lockedUser->refresh();

                $levelAfter = max(
                    1,
                    (int) $lockedUser->level
                );

                return [
                    'show_popup' =>
                        $xpEarned > 0
                        ||
                        $coinsEarned > 0
                        ||
                        !empty($newBadges)
                        ||
                        $levelAfter >
                            $levelBefore,

                    'title' =>
                        $levelAfter >
                        $levelBefore
                            ? 'Level Up!'
                            : 'Assessment Complete!',

                    'message' =>
                        $type === 'pretest'
                            ? 'Baseline progress saved. Keep going!'
                            : 'Great work! Your post-test progress is saved.',

                    'xp_earned' =>
                        $xpEarned,

                    'coins_earned' =>
                        $coinsEarned,

                    'total_xp' =>
                        (int) $lockedUser->xp,

                    'total_coins' =>
                        (int) $lockedUser->coins,

                    'level_before' =>
                        $levelBefore,

                    'level_after' =>
                        $levelAfter,

                    'level_up' =>
                        $levelAfter >
                        $levelBefore,

                    'unit_completed' =>
                        false,

                    'unit' =>
                        null,

                    'skill' =>
                        $skill,

                    'score' =>
                        $submission->final_score !== null
                            ? (int) $submission->final_score
                            : null,

                    'badges' =>
                        $newBadges,

                    'current_streak' =>
                        (int) $lockedUser
                            ->current_streak,
                ];
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Streak
        |--------------------------------------------------------------------------
        */

        $this->syncStreakFromHistory(
            $user->fresh()
        );

        $freshUser =
            $user->fresh();

        $payload['current_streak'] =
            (int) $freshUser
                ->current_streak;

        $payload['total_xp'] =
            (int) $freshUser->xp;

        $payload['total_coins'] =
            (int) $freshUser->coins;

        return $payload;
    }

    /**
     * Sinkronisasi progress lama ke sistem gamifikasi.
     *
     * Aman dipanggil berulang kali karena transaksi reward
     * memiliki unique event_key.
     */
    public function syncHistoricalProgress(
        User $user
    ): void {
        /*
        |--------------------------------------------------------------------------
        | Progress Unit 1–4
        |--------------------------------------------------------------------------
        */

        $unitProgress =
            UserLessonProgress::query()
                ->with([
                    'lesson.unit',
                    'unit',
                ])
                ->where(
                    'user_id',
                    $user->id
                )
                ->where(
                    'status',
                    'completed'
                )
                ->whereHas(
                    'unit',
                    function ($query) {
                        $query->where(
                            'type',
                            'unit'
                        );
                    }
                )
                ->orderBy(
                    'completed_at'
                )
                ->orderBy('id')
                ->get();

        foreach (
            $unitProgress
            as $progress
        ) {
            $lesson =
                $progress->lesson;

            if (!$lesson) {
                continue;
            }

            $this->rewardLessonCompletion(
                $user->fresh(),
                $lesson,
                (string)
                    $progress->skill_type,
                $progress->score !== null
                    ? (int) $progress->score
                    : null
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Pre-Test / Post-Test
        |--------------------------------------------------------------------------
        */

        $assessments =
            AssessmentSubmission::query()
                ->where(
                    'user_id',
                    $user->id
                )
                ->where(
                    'status',
                    'completed'
                )
                ->whereNotNull(
                    'final_score'
                )
                ->whereIn(
                    'type',
                    [
                        'pretest',
                        'posttest',
                    ]
                )
                ->orderBy(
                    'submitted_at'
                )
                ->orderBy('id')
                ->get()
                ->unique(
                    function (
                        $submission
                    ) {
                        return strtolower(
                            (string)
                            $submission->type
                        )
                        . '|'
                        . strtolower(
                            (string)
                            $submission->skill
                        );
                    }
                );

        foreach (
            $assessments
            as $submission
        ) {
            $this
                ->rewardAssessmentCompletion(
                    $user->fresh(),
                    $submission
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Streak
        |--------------------------------------------------------------------------
        */

        $this->syncStreakFromHistory(
            $user->fresh()
        );

        /*
        |--------------------------------------------------------------------------
        | Final Level + Badge Sync
        |--------------------------------------------------------------------------
        */

        DB::transaction(
            function () use ($user) {
                $lockedUser =
                    User::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $user->id
                        );

                $this->refreshLevel(
                    $lockedUser
                );

                $this
                    ->evaluateAndUnlockBadges(
                        $lockedUser
                    );
            }
        );
    }

    /**
     * Data gamifikasi ringkas untuk dashboard
     * dan halaman Rewards.
     */
    public function overview(
        User $user
    ): array {
        $user->refresh();

        /*
        |--------------------------------------------------------------------------
        | Level
        |--------------------------------------------------------------------------
        */

        $level =
            $this->levelData(
                (int) $user->xp
            );

        /*
        |--------------------------------------------------------------------------
        | Badge yang sudah dimiliki
        |--------------------------------------------------------------------------
        */

        $earnedBadgeIds =
            DB::table('user_badges')
                ->where(
                    'user_id',
                    $user->id
                )
                ->pluck(
                    'badge_id'
                );

        /*
        |--------------------------------------------------------------------------
        | Semua badge
        |--------------------------------------------------------------------------
        */

        $badges =
            Badge::query()
                ->where(
                    'is_active',
                    true
                )
                ->orderBy(
                    'sort_order'
                )
                ->orderBy('id')
                ->get()
                ->map(
                    function (
                        Badge $badge
                    ) use (
                        $earnedBadgeIds
                    ) {
                        return [
                            'id' =>
                                $badge->id,

                            'code' =>
                                $badge->code,

                            'name' =>
                                $badge->name,

                            'description' =>
                                $badge
                                    ->description,

                            'icon' =>
                                $badge->icon
                                ?: '🏅',

                            'category' =>
                                $badge->category,

                            'earned' =>
                                $earnedBadgeIds
                                    ->contains(
                                        $badge->id
                                    ),
                        ];
                    }
                )
                ->values()
                ->all();

        return [
            'xp' =>
                (int) $user->xp,

            'coins' =>
                (int) $user->coins,

            'level' =>
                $level,

            'current_streak' =>
                (int) $user
                    ->current_streak,

            'longest_streak' =>
                (int) $user
                    ->longest_streak,

            'badges' =>
                $badges,

            'earned_badges' =>
                collect($badges)
                    ->where(
                        'earned',
                        true
                    )
                    ->values()
                    ->all(),

            'earned_badge_count' =>
                collect($badges)
                    ->where(
                        'earned',
                        true
                    )
                    ->count(),

            'total_badge_count' =>
                count($badges),

            'class_rank' => $this->rankAmong($user, true),
            'school_rank' => $this->rankAmong($user, false),
            'classmates' => $this->peerCount($user, true),
            'schoolmates' => $this->peerCount($user, false),
        ];
    }

    public function studentQuery(?User $viewer = null, bool $sameClass = false)
    {
        $query = User::query()
            ->where(function ($q) {
                $q->whereNull('role')
                    ->orWhere('role', 'student')
                    ->orWhere('role', 'user');
            });

        if ($viewer && $viewer->school) {
            $query->where('school', $viewer->school);
        }

        if ($sameClass && $viewer) {
            if ($viewer->major) {
                $query->where('major', $viewer->major);
            }
            if ($viewer->grade) {
                $query->where('grade', $viewer->grade);
            }
            if ($viewer->parallel) {
                $query->where('parallel', $viewer->parallel);
            }
        }

        return $query;
    }

    public function rankAmong(User $user, bool $sameClass = true): int
    {
        return $this->studentQuery($user, $sameClass)
            ->where(function ($query) use ($user) {
                $query->where('xp', '>', (int) $user->xp)
                    ->orWhere(function ($tie) use ($user) {
                        $tie->where('xp', (int) $user->xp)
                            ->where('id', '<', $user->id);
                    });
            })
            ->count() + 1;
    }

    public function peerCount(User $user, bool $sameClass = true): int
    {
        return max(1, $this->studentQuery($user, $sameClass)->count());
    }

    /**
     * Sinkronisasi seluruh akun siswa.
     *
     * Digunakan agar leaderboard akun lama
     * memiliki XP yang benar.
     */
    public function syncAllStudents(): void
    {
        User::query()
            ->where(
                function ($query) {
                    $query
                        ->whereNull(
                            'role'
                        )
                        ->orWhere(
                            'role',
                            '!=',
                            'admin'
                        );
                }
            )
            ->orderBy('id')
            ->chunkById(
                50,
                function ($users) {
                    foreach (
                        $users
                        as $user
                    ) {
                        $this
                            ->syncHistoricalProgress(
                                $user
                            );
                    }
                }
            );
    }

    /**
     * Sinkronisasi streak berdasarkan histori pembelajaran.
     */
    public function syncStreakFromHistory(
        User $user
    ): void {
        /*
        |--------------------------------------------------------------------------
        | Tanggal dari lesson progress
        |--------------------------------------------------------------------------
        */

        $lessonDates =
            UserLessonProgress::query()
                ->where(
                    'user_id',
                    $user->id
                )
                ->where(
                    'status',
                    'completed'
                )
                ->whereNotNull(
                    'completed_at'
                )
                ->pluck(
                    'completed_at'
                );

        /*
        |--------------------------------------------------------------------------
        | Tanggal dari assessment
        |--------------------------------------------------------------------------
        */

        $assessmentDates =
            AssessmentSubmission::query()
                ->where(
                    'user_id',
                    $user->id
                )
                ->where(
                    'status',
                    'completed'
                )
                ->whereNotNull(
                    'submitted_at'
                )
                ->pluck(
                    'submitted_at'
                );

        /*
        |--------------------------------------------------------------------------
        | Gabungkan tanggal aktivitas
        |--------------------------------------------------------------------------
        */

        $dates =
            $lessonDates
                ->merge(
                    $assessmentDates
                )
                ->filter()
                ->map(
                    function ($date) {
                        return Carbon::parse(
                            $date
                        )->toDateString();
                    }
                )
                ->unique()
                ->sort()
                ->values();

        /*
        |--------------------------------------------------------------------------
        | Hitung streak
        |--------------------------------------------------------------------------
        */

        $currentStreak =
            $this->calculateCurrentStreak(
                $dates
            );

        $longestStreak =
            $this->calculateLongestStreak(
                $dates
            );

        /*
        |--------------------------------------------------------------------------
        | Update user
        |--------------------------------------------------------------------------
        */

        User::query()
            ->whereKey(
                $user->id
            )
            ->update([
                'current_streak' =>
                    $currentStreak,

                'longest_streak' =>
                    $longestStreak,

                'last_activity_date' =>
                    $dates->isNotEmpty()
                        ? $dates->last()
                        : null,
            ]);
    }

    /**
     * Menghasilkan data level berdasarkan XP.
     */
    public function levelData(
        int $xp
    ): array {
        $xp = max(
            0,
            $xp
        );

        /*
         * Contoh:
         *
         * 0 XP   = Level 1
         * 500 XP = Level 2
         * 1000   = Level 3
         */

        $level =
            intdiv(
                $xp,
                self::XP_PER_LEVEL
            )
            + 1;

        /*
         * XP dalam level saat ini.
         */
        $xpIntoLevel =
            $xp
            %
            self::XP_PER_LEVEL;

        /*
         * Persentase menuju level berikutnya.
         */
        $progress =
            (int) round(
                (
                    $xpIntoLevel
                    /
                    self::XP_PER_LEVEL
                )
                * 100
            );

        return [
            'level' =>
                $level,

            'name' =>
                $this->levelName(
                    $level
                ),

            'xp_into_level' =>
                $xpIntoLevel,

            'xp_for_next_level' =>
                self::XP_PER_LEVEL,

            'remaining_xp' =>
                self::XP_PER_LEVEL
                -
                $xpIntoLevel,

            'progress' =>
                max(
                    0,
                    min(
                        100,
                        $progress
                    )
                ),
        ];
    }

    /**
     * Nama level.
     */
    private function levelName(
        int $level
    ): string {
        return match (true) {
            $level >= 8 =>
                'Legend',

            $level >= 6 =>
                'Master',

            $level >= 4 =>
                'Achiever',

            $level >= 3 =>
                'Challenger',

            $level >= 2 =>
                'Explorer',

            default =>
                'Rookie',
        };
    }

    /**
     * Update level user berdasarkan XP.
     */
    private function refreshLevel(
        User $user
    ): void {
        $newLevel =
            $this->levelData(
                (int) $user->xp
            )['level'];

        if (
            (int) $user->level
            !==
            $newLevel
        ) {
            $user->level =
                $newLevel;

            $user->save();
        }
    }

    /**
     * Membuat transaksi XP / Coin satu kali.
     *
     * Return:
     *
     * jumlah reward yang benar-benar baru diberikan.
     */
    private function grantTransaction(
        User $user,
        string $type,
        int $amount,
        string $reason,
        string $eventKey,
        array $metadata = []
    ): int {
        if ($amount === 0) {
            return 0;
        }

        /*
        |--------------------------------------------------------------------------
        | firstOrCreate
        |--------------------------------------------------------------------------
        |
        | Jika event sudah ada, transaksi baru tidak dibuat.
        |
        */

        $transaction =
            GamificationTransaction::firstOrCreate(
                [
                    'user_id' =>
                        $user->id,

                    'event_key' =>
                        $eventKey,

                    'type' =>
                        $type,
                ],
                [
                    'amount' =>
                        $amount,

                    'reason' =>
                        $reason,

                    'metadata' =>
                        $metadata,
                ]
            );

        /*
         * Event lama = reward tidak diberikan lagi.
         */
        if (
            !$transaction
                ->wasRecentlyCreated
        ) {
            return 0;
        }

        /*
        |--------------------------------------------------------------------------
        | Tambah XP
        |--------------------------------------------------------------------------
        */

        if ($type === 'xp') {
            $user->xp = max(
                0,
                (int) $user->xp
                +
                $amount
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Tambah SpeakCoins
        |--------------------------------------------------------------------------
        */

        if ($type === 'coin') {
            $user->coins = max(
                0,
                (int) $user->coins
                +
                $amount
            );
        }

        $user->save();

        return $amount;
    }

    /**
     * Cek apakah seluruh skill pada Unit sudah selesai.
     */
    private function isUnitCompleted(
        int $userId,
        int $unitId
    ): bool {
        $completedSkills =
            UserLessonProgress::query()
                ->where(
                    'user_id',
                    $userId
                )
                ->where(
                    'unit_id',
                    $unitId
                )
                ->where(
                    'status',
                    'completed'
                )
                ->whereIn(
                    'skill_type',
                    self::SKILLS
                )
                ->pluck(
                    'skill_type'
                )
                ->map(
                    fn ($skill) =>
                        strtolower(
                            trim(
                                (string) $skill
                            )
                        )
                )
                ->unique();

        return collect(
            self::SKILLS
        )->every(
            fn ($skill) =>
                $completedSkills
                    ->contains(
                        $skill
                    )
        );
    }

    /**
     * Mengevaluasi semua badge yang mungkin
     * diperoleh user.
     */
    private function evaluateAndUnlockBadges(
        User $user
    ): array {
        $codes = [];

        /*
        |--------------------------------------------------------------------------
        | First Steps
        |--------------------------------------------------------------------------
        */

        if (
            $this->hasCompletedPretest(
                $user->id
            )
        ) {
            $codes[] =
                'first_steps';
        }

        /*
        |--------------------------------------------------------------------------
        | Bookworm
        |--------------------------------------------------------------------------
        */

        if (
            $this->hasCompletedSkill(
                $user->id,
                'reading'
            )
        ) {
            $codes[] =
                'bookworm';
        }

        /*
        |--------------------------------------------------------------------------
        | Speaking Star
        |--------------------------------------------------------------------------
        */

        if (
            $this->hasCompletedSkill(
                $user->id,
                'speaking'
            )
        ) {
            $codes[] =
                'speaking_star';
        }

        /*
        |--------------------------------------------------------------------------
        | Perfect Score
        |--------------------------------------------------------------------------
        */

        if (
            $this->hasPerfectScore(
                $user->id
            )
        ) {
            $codes[] =
                'perfect_score';
        }

        /*
        |--------------------------------------------------------------------------
        | Unit Master
        |--------------------------------------------------------------------------
        */

        if (
            $this->hasCompletedAnyUnit(
                $user->id
            )
        ) {
            $codes[] =
                'unit_master';
        }

        /*
        |--------------------------------------------------------------------------
        | 7-Day Streak
        |--------------------------------------------------------------------------
        */

        if (
            (int)
            $user->current_streak
            >= 7
        ) {
            $codes[] =
                'seven_day_streak';
        }

        /*
        |--------------------------------------------------------------------------
        | Century Club
        |--------------------------------------------------------------------------
        */

        if (
            (int) $user->xp
            >= 1000
        ) {
            $codes[] =
                'century_club';
        }

        /*
        |--------------------------------------------------------------------------
        | Level Up Badge
        |--------------------------------------------------------------------------
        */

        if (
            (int) $user->level
            >= 2
        ) {
            $codes[] =
                'level_up';
        }

        /*
        |--------------------------------------------------------------------------
        | Simpan badge baru
        |--------------------------------------------------------------------------
        */

        $newBadges = [];

        foreach (
            array_unique($codes)
            as $code
        ) {
            $badge =
                Badge::query()
                    ->where(
                        'code',
                        $code
                    )
                    ->where(
                        'is_active',
                        true
                    )
                    ->first();

            if (!$badge) {
                continue;
            }

            /*
             * insertOrIgnore mencegah duplicate badge.
             */
            $inserted =
                DB::table(
                    'user_badges'
                )
                    ->insertOrIgnore([
                        'user_id' =>
                            $user->id,

                        'badge_id' =>
                            $badge->id,

                        'unlocked_at' =>
                            now(),

                        'created_at' =>
                            now(),

                        'updated_at' =>
                            now(),
                    ]);

            /*
             * Hanya kirim badge yang benar-benar
             * baru diperoleh.
             */
            if ($inserted > 0) {
                $newBadges[] = [
                    'code' =>
                        $badge->code,

                    'name' =>
                        $badge->name,

                    'description' =>
                        $badge
                            ->description,

                    'icon' =>
                        $badge->icon
                        ?: '🏅',
                ];
            }
        }

        return $newBadges;
    }

    /**
     * Cek apakah user sudah menyelesaikan
     * seluruh Pre-Test.
     */
    private function hasCompletedPretest(
        int $userId
    ): bool {
        $skills =
            AssessmentSubmission::query()
                ->where(
                    'user_id',
                    $userId
                )
                ->where(
                    'type',
                    'pretest'
                )
                ->where(
                    'status',
                    'completed'
                )
                ->whereNotNull(
                    'final_score'
                )
                ->whereIn(
                    'skill',
                    self::SKILLS
                )
                ->pluck(
                    'skill'
                )
                ->map(
                    fn ($skill) =>
                        strtolower(
                            trim(
                                (string)
                                $skill
                            )
                        )
                )
                ->unique();

        return collect(
            self::SKILLS
        )->every(
            fn ($skill) =>
                $skills->contains(
                    $skill
                )
        );
    }

    /**
     * Cek apakah user pernah menyelesaikan skill tertentu
     * pada Unit 1–4.
     */
    private function hasCompletedSkill(
        int $userId,
        string $skill
    ): bool {
        return UserLessonProgress::query()
            ->where(
                'user_id',
                $userId
            )
            ->where(
                'status',
                'completed'
            )
            ->where(
                'skill_type',
                $skill
            )
            ->whereHas(
                'unit',
                function ($query) {
                    $query->where(
                        'type',
                        'unit'
                    );
                }
            )
            ->exists();
    }

    /**
     * Cek apakah user pernah mendapatkan nilai 100.
     */
    private function hasPerfectScore(
        int $userId
    ): bool {
        /*
        |--------------------------------------------------------------------------
        | Assessment Submission
        |--------------------------------------------------------------------------
        */

        $assessmentPerfect =
            AssessmentSubmission::query()
                ->where(
                    'user_id',
                    $userId
                )
                ->where(
                    'status',
                    'completed'
                )
                ->where(
                    'final_score',
                    '>=',
                    100
                )
                ->exists();

        if ($assessmentPerfect) {
            return true;
        }

        /*
        |--------------------------------------------------------------------------
        | User Lesson Progress
        |--------------------------------------------------------------------------
        */

        return UserLessonProgress::query()
            ->where(
                'user_id',
                $userId
            )
            ->where(
                'status',
                'completed'
            )
            ->where(
                'score',
                '>=',
                100
            )
            ->exists();
    }

    /**
     * Cek apakah user pernah menyelesaikan
     * minimal satu Unit lengkap.
     */
    private function hasCompletedAnyUnit(
        int $userId
    ): bool {
        $unitIds =
            Unit::query()
                ->where(
                    'type',
                    'unit'
                )
                ->where(
                    'status',
                    'active'
                )
                ->pluck(
                    'id'
                );

        foreach (
            $unitIds
            as $unitId
        ) {
            if (
                $this->isUnitCompleted(
                    $userId,
                    (int) $unitId
                )
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Menghitung streak aktif.
     */
    private function calculateCurrentStreak(
        Collection $dates
    ): int {
        if ($dates->isEmpty()) {
            return 0;
        }

        /*
         * Jadikan tanggal sebagai key agar pencarian cepat.
         */
        $dateSet =
            $dates->mapWithKeys(
                fn ($date) => [
                    $date => true,
                ]
            );

        $today =
            Carbon::today();

        $latest =
            Carbon::parse(
                $dates->last()
            )->startOfDay();

        /*
         * Streak dianggap mati jika aktivitas terakhir
         * bukan hari ini atau kemarin.
         */
        if (
            !$latest->isSameDay(
                $today
            )
            &&
            !$latest->isSameDay(
                $today
                    ->copy()
                    ->subDay()
            )
        ) {
            return 0;
        }

        $cursor =
            $latest->copy();

        $streak = 0;

        while (
            $dateSet->has(
                $cursor
                    ->toDateString()
            )
        ) {
            $streak++;

            $cursor->subDay();
        }

        return $streak;
    }

    /**
     * Menghitung streak terpanjang sepanjang histori.
     */
    private function calculateLongestStreak(
        Collection $dates
    ): int {
        if ($dates->isEmpty()) {
            return 0;
        }

        $longest = 1;
        $current = 1;

        for (
            $i = 1;
            $i < $dates->count();
            $i++
        ) {
            $previous =
                Carbon::parse(
                    $dates[
                        $i - 1
                    ]
                )->startOfDay();

            $currentDate =
                Carbon::parse(
                    $dates[$i]
                )->startOfDay();

            /*
             * Apakah currentDate tepat satu hari
             * setelah previous?
             */
            if (
                $previous
                    ->copy()
                    ->addDay()
                    ->isSameDay(
                        $currentDate
                    )
            ) {
                $current++;

                $longest = max(
                    $longest,
                    $current
                );
            } else {
                $current = 1;
            }
        }

        return $longest;
    }

    /**
     * Membentuk response reward untuk frontend.
     */
    private function buildRewardPayload(
        User $user,
        int $levelBefore,
        int $xpEarned,
        int $coinsEarned,
        array $newBadges,
        bool $unitCompletedNow,
        ?Unit $unit,
        string $skill,
        ?int $score
    ): array {
        $levelAfter = max(
            1,
            (int) $user->level
        );

        $levelUp =
            $levelAfter >
            $levelBefore;

        /*
        |--------------------------------------------------------------------------
        | Apakah popup perlu muncul?
        |--------------------------------------------------------------------------
        */

        $showPopup =
            $xpEarned > 0
            ||
            $coinsEarned > 0
            ||
            !empty($newBadges)
            ||
            $unitCompletedNow
            ||
            $levelUp;

        /*
        |--------------------------------------------------------------------------
        | Judul dan feedback popup
        |--------------------------------------------------------------------------
        */

        if ($unitCompletedNow) {
            $title =
                'Unit Completed!';

            $message =
                'Excellent! You completed all four skills in this unit.';
        } elseif ($levelUp) {
            $title =
                'Level Up!';

            $message =
                'Your consistency paid off. A new level has been unlocked.';
        } elseif (!empty($newBadges)) {
            $title =
                'Achievement Unlocked!';

            $message =
                'Nice work! A new badge was added to your collection.';
        } else {
            $title =
                'Great Job!';

            $message =
                $this->emotionalMessage(
                    $skill,
                    $score
                );
        }

        return [
            'show_popup' =>
                $showPopup,

            'title' =>
                $title,

            'message' =>
                $message,

            /*
            |--------------------------------------------------------------------------
            | Reward
            |--------------------------------------------------------------------------
            */

            'xp_earned' =>
                $xpEarned,

            'coins_earned' =>
                $coinsEarned,

            'total_xp' =>
                (int) $user->xp,

            'total_coins' =>
                (int) $user->coins,

            /*
            |--------------------------------------------------------------------------
            | Level
            |--------------------------------------------------------------------------
            */

            'level_before' =>
                $levelBefore,

            'level_after' =>
                $levelAfter,

            'level_up' =>
                $levelUp,

            /*
            |--------------------------------------------------------------------------
            | Unit
            |--------------------------------------------------------------------------
            */

            'unit_completed' =>
                $unitCompletedNow,

            'unit' =>
                $unit
                    ? [
                        'id' =>
                            $unit->id,

                        'title' =>
                            $unit->title,

                        'order_number' =>
                            $unit
                                ->order_number,
                    ]
                    : null,

            /*
            |--------------------------------------------------------------------------
            | Activity
            |--------------------------------------------------------------------------
            */

            'skill' =>
                $skill,

            'score' =>
                $score,

            /*
            |--------------------------------------------------------------------------
            | Achievement
            |--------------------------------------------------------------------------
            */

            'badges' =>
                $newBadges,

            /*
            |--------------------------------------------------------------------------
            | Streak
            |--------------------------------------------------------------------------
            */

            'current_streak' =>
                (int) $user
                    ->current_streak,
        ];
    }

    /**
     * Feedback emosional berdasarkan score.
     */
    private function emotionalMessage(
        string $skill,
        ?int $score
    ): string {
        $skillName =
            ucfirst($skill);

        /*
         * Jika tidak ada score.
         */
        if ($score === null) {
            return "{$skillName} completed. Keep the momentum going!";
        }

        return match (true) {
            /*
             * Perfect score.
             */
            $score >= 100 =>
                "Perfect score! Your {$skillName} performance was outstanding.",

            /*
             * Sangat tinggi.
             */
            $score >= 90 =>
                "Amazing work! You were very close to a perfect {$skillName} score.",

            /*
             * Bagus.
             */
            $score >= 80 =>
                "Strong result! Your {$skillName} progress is moving in the right direction.",

            /*
             * Cukup.
             */
            $score >= 60 =>
                "Good progress! Keep practising {$skillName} and push the score even higher.",

            /*
             * Masih perlu perbaikan.
             */
            default =>
                "Mission complete! Review the feedback and make the next {$skillName} attempt stronger.",
        };
    }

    /**
     * Response kosong ketika activity tidak memenuhi
     * syarat reward.
     */
    private function emptyRewardPayload(
        User $user
    ): array {
        $user->refresh();

        return [
            'show_popup' =>
                false,

            'title' =>
                null,

            'message' =>
                null,

            'xp_earned' =>
                0,

            'coins_earned' =>
                0,

            'total_xp' =>
                (int) (
                    $user->xp
                    ?? 0
                ),

            'total_coins' =>
                (int) (
                    $user->coins
                    ?? 0
                ),

            'level_before' =>
                (int) (
                    $user->level
                    ?? 1
                ),

            'level_after' =>
                (int) (
                    $user->level
                    ?? 1
                ),

            'level_up' =>
                false,

            'unit_completed' =>
                false,

            'unit' =>
                null,

            'skill' =>
                null,

            'score' =>
                null,

            'badges' =>
                [],

            'current_streak' =>
                (int) (
                    $user->current_streak
                    ?? 0
                ),
        ];
    }
}