<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Tambahan data gamifikasi pada users
        |--------------------------------------------------------------------------
        */

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('xp')
                ->default(0)
                ->after('role')
                ->index();

            $table->unsignedBigInteger('coins')
                ->default(0)
                ->after('xp');

            $table->unsignedInteger('level')
                ->default(1)
                ->after('coins')
                ->index();

            $table->unsignedInteger('current_streak')
                ->default(0)
                ->after('level');

            $table->unsignedInteger('longest_streak')
                ->default(0)
                ->after('current_streak');

            $table->date('last_activity_date')
                ->nullable()
                ->after('longest_streak');
        });

        /*
        |--------------------------------------------------------------------------
        | Badges / Achievements
        |--------------------------------------------------------------------------
        */

        Schema::create('badges', function (Blueprint $table) {
            $table->id();

            $table->string('code')
                ->unique();

            $table->string('name');

            $table->text('description')
                ->nullable();

            $table->string('icon')
                ->nullable();

            $table->string('category')
                ->default('achievement');

            $table->unsignedInteger('sort_order')
                ->default(0);

            $table->boolean('is_active')
                ->default(true);

            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | Badge yang sudah diperoleh user
        |--------------------------------------------------------------------------
        */

        Schema::create('user_badges', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('badge_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->timestamp('unlocked_at');

            $table->timestamps();

            $table->unique([
                'user_id',
                'badge_id',
            ]);
        });

        /*
        |--------------------------------------------------------------------------
        | Riwayat XP dan SpeakCoins
        |--------------------------------------------------------------------------
        |
        | event_key digunakan agar XP/Coin tidak bisa didapat berulang kali
        | dari activity yang sama.
        |
        */

        Schema::create(
            'gamification_transactions',
            function (Blueprint $table) {
                $table->id();

                $table->foreignId('user_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->string(
                    'type',
                    20
                );

                /*
                 * Contoh:
                 *
                 * xp
                 * coin
                 */

                $table->integer('amount');

                $table->string('reason');

                $table->string('event_key');

                $table->json('metadata')
                    ->nullable();

                $table->timestamps();

                /*
                 * User tidak dapat menerima reward yang sama
                 * dua kali untuk event yang sama.
                 */
                $table->unique(
                    [
                        'user_id',
                        'event_key',
                        'type',
                    ],
                    'gamification_event_unique'
                );

                $table->index([
                    'user_id',
                    'created_at',
                ]);
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Reward Shop
        |--------------------------------------------------------------------------
        */

        Schema::create(
            'reward_items',
            function (Blueprint $table) {
                $table->id();

                $table->string('code')
                    ->unique();

                $table->string('name');

                $table->text('description')
                    ->nullable();

                $table->string('icon')
                    ->nullable();

                $table->string('type')
                    ->default('cosmetic');

                $table->unsignedInteger(
                    'price_coins'
                );

                $table->json('metadata')
                    ->nullable();

                $table->unsignedInteger(
                    'sort_order'
                )->default(0);

                $table->boolean('is_active')
                    ->default(true);

                $table->timestamps();
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Reward yang dimiliki user
        |--------------------------------------------------------------------------
        */

        Schema::create(
            'user_reward_items',
            function (Blueprint $table) {
                $table->id();

                $table->foreignId('user_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->foreignId(
                    'reward_item_id'
                )
                    ->constrained()
                    ->cascadeOnDelete();

                $table->unsignedInteger(
                    'quantity'
                )->default(1);

                $table->timestamp(
                    'acquired_at'
                );

                $table->timestamps();

                $table->unique([
                    'user_id',
                    'reward_item_id',
                ]);
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Default Badge
        |--------------------------------------------------------------------------
        */

        $now = now();

        DB::table('badges')->insert([
            [
                'code' =>
                    'first_steps',

                'name' =>
                    'First Steps',

                'description' =>
                    'Complete all four skills in the Pre-Test.',

                'icon' =>
                    '🚩',

                'category' =>
                    'milestone',

                'sort_order' =>
                    10,

                'is_active' =>
                    true,

                'created_at' =>
                    $now,

                'updated_at' =>
                    $now,
            ],

            [
                'code' =>
                    'bookworm',

                'name' =>
                    'Bookworm',

                'description' =>
                    'Complete your first Reading lesson.',

                'icon' =>
                    '📖',

                'category' =>
                    'skill',

                'sort_order' =>
                    20,

                'is_active' =>
                    true,

                'created_at' =>
                    $now,

                'updated_at' =>
                    $now,
            ],

            [
                'code' =>
                    'speaking_star',

                'name' =>
                    'Speaking Star',

                'description' =>
                    'Complete your first Speaking lesson.',

                'icon' =>
                    '🎙️',

                'category' =>
                    'skill',

                'sort_order' =>
                    30,

                'is_active' =>
                    true,

                'created_at' =>
                    $now,

                'updated_at' =>
                    $now,
            ],

            [
                'code' =>
                    'perfect_score',

                'name' =>
                    'Perfect Score',

                'description' =>
                    'Earn a score of 100 on any completed activity.',

                'icon' =>
                    '⭐',

                'category' =>
                    'performance',

                'sort_order' =>
                    40,

                'is_active' =>
                    true,

                'created_at' =>
                    $now,

                'updated_at' =>
                    $now,
            ],

            [
                'code' =>
                    'unit_master',

                'name' =>
                    'Unit Master',

                'description' =>
                    'Complete Listening, Reading, Writing, and Speaking in one Unit.',

                'icon' =>
                    '🏅',

                'category' =>
                    'milestone',

                'sort_order' =>
                    50,

                'is_active' =>
                    true,

                'created_at' =>
                    $now,

                'updated_at' =>
                    $now,
            ],

            [
                'code' =>
                    'seven_day_streak',

                'name' =>
                    '7-Day Streak',

                'description' =>
                    'Learn for seven consecutive days.',

                'icon' =>
                    '🔥',

                'category' =>
                    'streak',

                'sort_order' =>
                    60,

                'is_active' =>
                    true,

                'created_at' =>
                    $now,

                'updated_at' =>
                    $now,
            ],

            [
                'code' =>
                    'century_club',

                'name' =>
                    'Century Club',

                'description' =>
                    'Earn at least 1,000 XP.',

                'icon' =>
                    '⚡',

                'category' =>
                    'xp',

                'sort_order' =>
                    70,

                'is_active' =>
                    true,

                'created_at' =>
                    $now,

                'updated_at' =>
                    $now,
            ],

            [
                'code' =>
                    'level_up',

                'name' =>
                    'Level Up',

                'description' =>
                    'Reach Level 2 for the first time.',

                'icon' =>
                    '📈',

                'category' =>
                    'level',

                'sort_order' =>
                    80,

                'is_active' =>
                    true,

                'created_at' =>
                    $now,

                'updated_at' =>
                    $now,
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Default Reward Shop
        |--------------------------------------------------------------------------
        */

        DB::table('reward_items')->insert([
            [
                'code' =>
                    'profile_frame_sky',

                'name' =>
                    'Sky Profile Frame',

                'description' =>
                    'A clean blue profile frame for your SpeakVerse profile.',

                'icon' =>
                    '🖼️',

                'type' =>
                    'profile_frame',

                'price_coins' =>
                    120,

                'metadata' =>
                    json_encode([
                        'theme' =>
                            'sky',
                    ]),

                'sort_order' =>
                    10,

                'is_active' =>
                    true,

                'created_at' =>
                    $now,

                'updated_at' =>
                    $now,
            ],

            [
                'code' =>
                    'title_bookworm',

                'name' =>
                    'Bookworm Title',

                'description' =>
                    'Unlock the “Bookworm” cosmetic title.',

                'icon' =>
                    '📚',

                'type' =>
                    'title',

                'price_coins' =>
                    160,

                'metadata' =>
                    json_encode([
                        'title' =>
                            'Bookworm',
                    ]),

                'sort_order' =>
                    20,

                'is_active' =>
                    true,

                'created_at' =>
                    $now,

                'updated_at' =>
                    $now,
            ],

            [
                'code' =>
                    'profile_frame_sunset',

                'name' =>
                    'Sunset Profile Frame',

                'description' =>
                    'A warm profile frame for your achievement collection.',

                'icon' =>
                    '🌅',

                'type' =>
                    'profile_frame',

                'price_coins' =>
                    220,

                'metadata' =>
                    json_encode([
                        'theme' =>
                            'sunset',
                    ]),

                'sort_order' =>
                    30,

                'is_active' =>
                    true,

                'created_at' =>
                    $now,

                'updated_at' =>
                    $now,
            ],

            [
                'code' =>
                    'title_speakverse_star',

                'name' =>
                    'SpeakVerse Star Title',

                'description' =>
                    'A premium cosmetic title for active learners.',

                'icon' =>
                    '🌟',

                'type' =>
                    'title',

                'price_coins' =>
                    300,

                'metadata' =>
                    json_encode([
                        'title' =>
                            'SpeakVerse Star',
                    ]),

                'sort_order' =>
                    40,

                'is_active' =>
                    true,

                'created_at' =>
                    $now,

                'updated_at' =>
                    $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'user_reward_items'
        );

        Schema::dropIfExists(
            'reward_items'
        );

        Schema::dropIfExists(
            'gamification_transactions'
        );

        Schema::dropIfExists(
            'user_badges'
        );

        Schema::dropIfExists(
            'badges'
        );

        Schema::table(
            'users',
            function (Blueprint $table) {
                $table->dropColumn([
                    'xp',
                    'coins',
                    'level',
                    'current_streak',
                    'longest_streak',
                    'last_activity_date',
                ]);
            }
        );
    }
};