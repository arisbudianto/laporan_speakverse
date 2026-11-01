@props([
    'gamification' => [],
])

@php
    $xp = (int) ($gamification['xp'] ?? 0);
    $coins = (int) ($gamification['coins'] ?? 0);

    $currentStreak = (int) (
        $gamification['current_streak']
        ?? 0
    );

    $longestStreak = (int) (
        $gamification['longest_streak']
        ?? 0
    );

    $earnedBadgeCount = (int) (
        $gamification['earned_badge_count']
        ?? 0
    );

    $totalBadgeCount = (int) (
        $gamification['total_badge_count']
        ?? 0
    );

    $level = $gamification['level'] ?? [];

    $levelNumber = (int) (
        $level['level']
        ?? 1
    );

    $levelName = (string) (
        $level['name']
        ?? 'Rookie'
    );

    $levelProgress = (int) (
        $level['progress']
        ?? 0
    );

    $xpIntoLevel = (int) (
        $level['xp_into_level']
        ?? 0
    );

    $xpForNextLevel = (int) (
        $level['xp_for_next_level']
        ?? 500
    );

    $remainingXp = (int) (
        $level['remaining_xp']
        ?? 500
    );

    $earnedBadges = collect(
        $gamification['earned_badges']
        ?? []
    )
        ->take(4)
        ->values();
@endphp


<section
    class="overflow-hidden rounded-3xl
           border border-slate-200
           bg-white shadow-sm
           dark:border-white/10
           dark:bg-white/[0.04]"
>

    {{-- ========================================================= --}}
    {{-- HEADER --}}
    {{-- ========================================================= --}}

    <div
        class="border-b border-slate-200
               px-5 py-5
               dark:border-white/10
               sm:px-6"
    >
        <div
            class="flex flex-col gap-4
                   sm:flex-row
                   sm:items-center
                   sm:justify-between"
        >

            <div class="min-w-0">

                <div class="flex flex-wrap items-center gap-2">

                    <span
                        class="inline-flex items-center
                               rounded-full
                               bg-blue-50
                               px-3 py-1
                               text-[11px] font-black
                               uppercase tracking-[0.14em]
                               text-blue-700
                               dark:bg-blue-500/10
                               dark:text-blue-300"
                    >
                        Your Progress
                    </span>


                    <span
                        class="inline-flex items-center
                               rounded-full
                               bg-slate-100
                               px-3 py-1
                               text-[11px] font-black
                               text-slate-600
                               dark:bg-white/10
                               dark:text-slate-300"
                    >
                        Level {{ $levelNumber }}
                    </span>

                </div>


                <h2
                    class="mt-3
                           text-xl font-black tracking-tight
                           text-slate-900
                           dark:text-white
                           sm:text-2xl"
                >
                    {{ $levelName }}
                </h2>


                <p
                    class="mt-1 text-sm
                           text-slate-500
                           dark:text-slate-400"
                >
                    Keep completing missions to earn XP,
                    SpeakCoins, badges, and new levels.
                </p>

            </div>


            {{-- ACTION BUTTONS --}}
            <div
                class="flex shrink-0
                       flex-wrap gap-2"
            >

                <a
                    href="{{ route('gamification.index') }}"
                    class="inline-flex items-center
                           justify-center gap-2
                           rounded-xl
                           border border-slate-200
                           bg-white
                           px-4 py-2.5
                           text-sm font-black
                           text-slate-700
                           transition
                           hover:bg-slate-50
                           dark:border-white/10
                           dark:bg-white/[0.03]
                           dark:text-slate-200
                           dark:hover:bg-white/[0.07]"
                >
                    <span aria-hidden="true">
                        🎁
                    </span>

                    <span>
                        Rewards
                    </span>
                </a>


                <a
                    href="{{ route('gamification.leaderboard') }}"
                    class="inline-flex items-center
                           justify-center gap-2
                           rounded-xl
                           bg-blue-600
                           px-4 py-2.5
                           text-sm font-black
                           text-white
                           transition
                           hover:bg-blue-700
                           focus:outline-none
                           focus:ring-4
                           focus:ring-blue-500/20"
                >
                    <span aria-hidden="true">
                        🏆
                    </span>

                    <span>
                        Ranking
                    </span>
                </a>

            </div>

        </div>
    </div>


    {{-- ========================================================= --}}
    {{-- BODY --}}
    {{-- ========================================================= --}}

    <div class="p-5 sm:p-6">

        {{-- ===================================================== --}}
        {{-- STATS --}}
        {{-- ===================================================== --}}

        <div
            class="grid grid-cols-2 gap-3
                   lg:grid-cols-4"
        >

            {{-- XP --}}
            <div
                class="rounded-2xl
                       border border-slate-200
                       bg-slate-50
                       p-4
                       dark:border-white/10
                       dark:bg-black/20"
            >
                <div
                    class="flex items-center
                           justify-between gap-3"
                >
                    <p
                        class="text-xs font-bold
                               text-slate-500
                               dark:text-slate-400"
                    >
                        Total XP
                    </p>

                    <span
                        class="grid h-8 w-8
                               place-items-center
                               rounded-xl
                               bg-blue-100
                               text-sm
                               dark:bg-blue-500/15"
                    >
                        ⚡
                    </span>
                </div>


                <p
                    class="mt-3
                           text-2xl font-black
                           text-slate-900
                           dark:text-white"
                >
                    {{ number_format($xp) }}
                </p>


                <p
                    class="mt-1 text-[11px]
                           font-semibold
                           text-slate-400
                           dark:text-slate-500"
                >
                    Lifetime experience
                </p>
            </div>


            {{-- SPEAKCOINS --}}
            <div
                class="rounded-2xl
                       border border-slate-200
                       bg-slate-50
                       p-4
                       dark:border-white/10
                       dark:bg-black/20"
            >
                <div
                    class="flex items-center
                           justify-between gap-3"
                >
                    <p
                        class="text-xs font-bold
                               text-slate-500
                               dark:text-slate-400"
                    >
                        SpeakCoins
                    </p>

                    <span
                        class="grid h-8 w-8
                               place-items-center
                               rounded-xl
                               bg-amber-100
                               text-sm
                               dark:bg-amber-500/15"
                    >
                        🪙
                    </span>
                </div>


                <p
                    class="mt-3
                           text-2xl font-black
                           text-slate-900
                           dark:text-white"
                >
                    {{ number_format($coins) }}
                </p>


                <p
                    class="mt-1 text-[11px]
                           font-semibold
                           text-slate-400
                           dark:text-slate-500"
                >
                    Available to redeem
                </p>
            </div>


            {{-- STREAK --}}
            <div
                class="rounded-2xl
                       border border-slate-200
                       bg-slate-50
                       p-4
                       dark:border-white/10
                       dark:bg-black/20"
            >
                <div
                    class="flex items-center
                           justify-between gap-3"
                >
                    <p
                        class="text-xs font-bold
                               text-slate-500
                               dark:text-slate-400"
                    >
                        Current Streak
                    </p>

                    <span
                        class="grid h-8 w-8
                               place-items-center
                               rounded-xl
                               bg-orange-100
                               text-sm
                               dark:bg-orange-500/15"
                    >
                        🔥
                    </span>
                </div>


                <p
                    class="mt-3
                           text-2xl font-black
                           text-slate-900
                           dark:text-white"
                >
                    {{ $currentStreak }}

                    <span
                        class="text-sm font-bold
                               text-slate-400"
                    >
                        days
                    </span>
                </p>


                <p
                    class="mt-1 text-[11px]
                           font-semibold
                           text-slate-400
                           dark:text-slate-500"
                >
                    Best:
                    {{ $longestStreak }}
                    days
                </p>
            </div>


            {{-- BADGES --}}
            <div
                class="rounded-2xl
                       border border-slate-200
                       bg-slate-50
                       p-4
                       dark:border-white/10
                       dark:bg-black/20"
            >
                <div
                    class="flex items-center
                           justify-between gap-3"
                >
                    <p
                        class="text-xs font-bold
                               text-slate-500
                               dark:text-slate-400"
                    >
                        Achievements
                    </p>

                    <span
                        class="grid h-8 w-8
                               place-items-center
                               rounded-xl
                               bg-violet-100
                               text-sm
                               dark:bg-violet-500/15"
                    >
                        🏅
                    </span>
                </div>


                <p
                    class="mt-3
                           text-2xl font-black
                           text-slate-900
                           dark:text-white"
                >
                    {{ $earnedBadgeCount }}

                    <span
                        class="text-sm font-bold
                               text-slate-400"
                    >
                        /
                        {{ $totalBadgeCount }}
                    </span>
                </p>


                <p
                    class="mt-1 text-[11px]
                           font-semibold
                           text-slate-400
                           dark:text-slate-500"
                >
                    Badges unlocked
                </p>
            </div>

        </div>


        {{-- ===================================================== --}}
        {{-- LEVEL PROGRESS --}}
        {{-- ===================================================== --}}

        <div
            class="mt-5 rounded-2xl
                   border border-slate-200
                   p-4
                   dark:border-white/10
                   sm:p-5"
        >

            <div
                class="flex items-center
                       justify-between gap-4"
            >

                <div>

                    <p
                        class="text-sm font-black
                               text-slate-900
                               dark:text-white"
                    >
                        Level {{ $levelNumber }}
                        progress
                    </p>

                    <p
                        class="mt-1 text-xs
                               text-slate-500
                               dark:text-slate-400"
                    >
                        {{ number_format($remainingXp) }}
                        XP until Level
                        {{ $levelNumber + 1 }}
                    </p>

                </div>


                <div class="text-right">

                    <p
                        class="text-sm font-black
                               text-blue-600
                               dark:text-blue-400"
                    >
                        {{ $levelProgress }}%
                    </p>

                    <p
                        class="text-[11px]
                               font-semibold
                               text-slate-400
                               dark:text-slate-500"
                    >
                        {{ number_format($xpIntoLevel) }}
                        /
                        {{ number_format($xpForNextLevel) }}
                        XP
                    </p>

                </div>

            </div>


            <div
                class="mt-4 h-2.5
                       overflow-hidden rounded-full
                       bg-slate-100
                       dark:bg-white/10"
            >
                <div
                    class="h-full rounded-full
                           bg-blue-600
                           transition-all
                           duration-700"
                    style="width: {{ max(0, min(100, $levelProgress)) }}%"
                ></div>
            </div>

        </div>


        {{-- ===================================================== --}}
        {{-- RECENT BADGES --}}
        {{-- ===================================================== --}}

        @if ($earnedBadges->isNotEmpty())

            <div class="mt-5">

                <div
                    class="flex items-center
                           justify-between gap-4"
                >

                    <div>

                        <p
                            class="text-sm font-black
                                   text-slate-900
                                   dark:text-white"
                        >
                            Latest achievements
                        </p>

                        <p
                            class="mt-0.5 text-xs
                                   text-slate-500
                                   dark:text-slate-400"
                        >
                            Badges currently in your collection.
                        </p>

                    </div>


                    <a
                        href="{{ route('gamification.index') }}"
                        class="text-xs font-black
                               text-blue-600
                               transition
                               hover:text-blue-700
                               dark:text-blue-400
                               dark:hover:text-blue-300"
                    >
                        View all
                    </a>

                </div>


                <div
                    class="mt-3
                           grid gap-2
                           sm:grid-cols-2
                           xl:grid-cols-4"
                >

                    @foreach ($earnedBadges as $badge)

                        <div
                            class="flex items-center gap-3
                                   rounded-2xl
                                   border border-slate-200
                                   px-3 py-3
                                   dark:border-white/10"
                        >

                            <div
                                class="grid h-10 w-10
                                       shrink-0 place-items-center
                                       rounded-xl
                                       bg-amber-50
                                       text-xl
                                       dark:bg-amber-500/10"
                            >
                                {{ $badge['icon'] ?? '🏅' }}
                            </div>


                            <div class="min-w-0">

                                <p
                                    class="truncate
                                           text-xs font-black
                                           text-slate-900
                                           dark:text-white"
                                >
                                    {{ $badge['name'] ?? 'Achievement' }}
                                </p>

                                <p
                                    class="mt-0.5
                                           text-[10px] font-bold
                                           uppercase tracking-wider
                                           text-emerald-600
                                           dark:text-emerald-400"
                                >
                                    Unlocked
                                </p>

                            </div>

                        </div>

                    @endforeach

                </div>

            </div>

        @else

            {{-- ================================================= --}}
            {{-- NO BADGE YET --}}
            {{-- ================================================= --}}

            <div
                class="mt-5 flex flex-col
                       gap-3 rounded-2xl
                       border border-dashed
                       border-slate-300
                       bg-slate-50/70
                       p-4
                       dark:border-white/15
                       dark:bg-white/[0.02]
                       sm:flex-row
                       sm:items-center
                       sm:justify-between"
            >

                <div
                    class="flex items-center gap-3"
                >

                    <div
                        class="grid h-10 w-10
                               shrink-0 place-items-center
                               rounded-xl
                               bg-slate-100
                               text-xl
                               dark:bg-white/10"
                    >
                        🔒
                    </div>


                    <div>

                        <p
                            class="text-sm font-black
                                   text-slate-900
                                   dark:text-white"
                        >
                            Your first badge is waiting.
                        </p>

                        <p
                            class="mt-0.5 text-xs
                                   text-slate-500
                                   dark:text-slate-400"
                        >
                            Keep completing learning activities
                            to unlock achievements.
                        </p>

                    </div>

                </div>


                <a
                    href="{{ route('gamification.index') }}"
                    class="shrink-0
                           text-xs font-black
                           text-blue-600
                           hover:text-blue-700
                           dark:text-blue-400
                           dark:hover:text-blue-300"
                >
                    See achievements
                </a>

            </div>

        @endif

    </div>

</section>