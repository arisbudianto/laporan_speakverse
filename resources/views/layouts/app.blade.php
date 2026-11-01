<!DOCTYPE html>

<html translate="no"
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    x-data="{
        darkMode: localStorage.getItem('darkMode') === 'true',
        mobileMenu: false,
        profileMenu: false
    }"
    x-init="
        $watch('darkMode', value => {
            localStorage.setItem('darkMode', value);
            document.documentElement.classList.toggle('dark', value);
        });

        document.documentElement.classList.toggle('dark', darkMode);
    "
    :class="{ 'dark': darkMode }"
>

<head>

    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >


    <title>
        SpeakVerse
    </title>


    {{-- ========================================================= --}}
    {{-- FONT --}}
    {{-- ========================================================= --}}

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap"
        rel="stylesheet"
    >


    {{-- ========================================================= --}}
    {{-- VITE --}}
    {{-- ========================================================= --}}

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])


    {{-- ========================================================= --}}
    {{-- ALPINE CLOAK --}}
    {{-- ========================================================= --}}

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>


    {{-- ========================================================= --}}
    {{-- GAMIFICATION POPUP STYLE --}}
    {{-- ========================================================= --}}
    {{--
        CSS ditulis langsung di layout supaya Instant Reward Popup
        tetap bekerja pada hosting yang tidak memiliki Node/npm.
    --}}

    <style>
        @keyframes svRewardBackdropIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes svRewardCardIn {
            0% {
                opacity: 0;
                transform:
                    translateY(24px)
                    scale(.94);
            }

            65% {
                opacity: 1;
                transform:
                    translateY(-3px)
                    scale(1.01);
            }

            100% {
                opacity: 1;
                transform:
                    translateY(0)
                    scale(1);
            }
        }

        @keyframes svRewardIconPop {
            0% {
                opacity: 0;
                transform:
                    scale(.5)
                    rotate(-8deg);
            }

            60% {
                opacity: 1;
                transform:
                    scale(1.12)
                    rotate(3deg);
            }

            100% {
                opacity: 1;
                transform:
                    scale(1)
                    rotate(0);
            }
        }

        @keyframes svRewardShine {
            0% {
                transform:
                    translateX(-160%)
                    rotate(20deg);
            }

            100% {
                transform:
                    translateX(420%)
                    rotate(20deg);
            }
        }

        @keyframes svRewardConfetti {
            0% {
                opacity: 1;

                transform:
                    translate3d(
                        0,
                        -10vh,
                        0
                    )
                    rotate(0deg);
            }

            100% {
                opacity: 0;

                transform:
                    translate3d(
                        var(--sv-confetti-x),
                        110vh,
                        0
                    )
                    rotate(
                        var(--sv-confetti-rotation)
                    );
            }
        }

        @keyframes svRewardPulse {
            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.04);
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Backdrop
        |--------------------------------------------------------------------------
        */

        #sv-gamification-backdrop {
            position: fixed;

            inset: 0;

            z-index: 999990;

            display: flex;

            align-items: center;
            justify-content: center;

            padding: 20px;

            background:
                rgba(
                    2,
                    6,
                    23,
                    .72
                );

            -webkit-backdrop-filter:
                blur(12px);

            backdrop-filter:
                blur(12px);

            animation:
                svRewardBackdropIn
                .2s
                ease-out
                both;
        }


        /*
        |--------------------------------------------------------------------------
        | Card
        |--------------------------------------------------------------------------
        */

        #sv-gamification-card {
            position: relative;

            z-index: 999992;

            width:
                min(
                    100%,
                    480px
                );

            max-height:
                calc(
                    100vh
                    -
                    40px
                );

            overflow-y: auto;
            overflow-x: hidden;

            border:
                1px solid
                #e2e8f0;

            border-radius:
                30px;

            background:
                #ffffff;

            color:
                #0f172a;

            box-shadow:
                0
                35px
                100px
                rgba(
                    2,
                    6,
                    23,
                    .35
                );

            padding:
                30px;

            text-align: center;

            animation:
                svRewardCardIn
                .42s
                cubic-bezier(
                    .2,
                    .8,
                    .2,
                    1
                )
                both;
        }

        html.dark
        #sv-gamification-card {
            border-color:
                rgba(
                    255,
                    255,
                    255,
                    .10
                );

            background:
                #0f172a;

            color:
                #f8fafc;

            box-shadow:
                0
                35px
                100px
                rgba(
                    0,
                    0,
                    0,
                    .55
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Decorative Glow
        |--------------------------------------------------------------------------
        */

        .sv-gamification-glow {
            position: absolute;

            width: 220px;
            height: 220px;

            top: -130px;
            left: 50%;

            transform:
                translateX(-50%);

            border-radius: 999px;

            background:
                rgba(
                    34,
                    211,
                    238,
                    .17
                );

            filter:
                blur(55px);

            pointer-events: none;
        }


        /*
        |--------------------------------------------------------------------------
        | Reward Icon
        |--------------------------------------------------------------------------
        */

        .sv-gamification-icon {
            position: relative;

            width: 82px;
            height: 82px;

            margin:
                0 auto;

            display: grid;

            place-items: center;

            overflow: hidden;

            border:
                1px solid
                #dbeafe;

            border-radius:
                25px;

            background:
                linear-gradient(
                    135deg,
                    #ecfeff,
                    #dbeafe,
                    #ede9fe
                );

            font-size: 42px;

            box-shadow:
                0
                15px
                40px
                rgba(
                    37,
                    99,
                    235,
                    .14
                );

            animation:
                svRewardIconPop
                .55s
                ease-out
                both;
        }

        html.dark
        .sv-gamification-icon {
            border-color:
                rgba(
                    96,
                    165,
                    250,
                    .20
                );

            background:
                linear-gradient(
                    135deg,
                    rgba(
                        6,
                        182,
                        212,
                        .13
                    ),
                    rgba(
                        37,
                        99,
                        235,
                        .13
                    ),
                    rgba(
                        124,
                        58,
                        237,
                        .13
                    )
                );
        }

        .sv-gamification-icon::after {
            content: "";

            position: absolute;

            top: -30px;
            left: -40px;

            width: 22px;
            height: 150px;

            background:
                rgba(
                    255,
                    255,
                    255,
                    .45
                );

            transform:
                rotate(20deg);

            animation:
                svRewardShine
                1.5s
                ease-in-out
                .35s
                both;
        }


        /*
        |--------------------------------------------------------------------------
        | Typography
        |--------------------------------------------------------------------------
        */

        .sv-gamification-eyebrow {
            display:
                inline-flex;

            align-items: center;

            gap: 6px;

            margin-top: 18px;

            border-radius: 999px;

            background:
                #ecfeff;

            padding:
                6px 11px;

            color:
                #0891b2;

            font-size: 10px;
            font-weight: 900;

            line-height: 1;

            letter-spacing: .12em;

            text-transform: uppercase;
        }

        html.dark
        .sv-gamification-eyebrow {
            background:
                rgba(
                    34,
                    211,
                    238,
                    .10
                );

            color:
                #67e8f9;
        }

        .sv-gamification-title {
            margin:
                14px
                0
                0;

            font-family:
                Outfit,
                sans-serif;

            color:
                #0f172a;

            font-size:
                28px;

            font-weight: 900;

            line-height: 1.15;

            letter-spacing: -.035em;
        }

        html.dark
        .sv-gamification-title {
            color:
                #f8fafc;
        }

        .sv-gamification-message {
            max-width: 390px;

            margin:
                9px
                auto
                0;

            color:
                #64748b;

            font-size: 14px;

            font-weight: 500;

            line-height: 1.65;
        }

        html.dark
        .sv-gamification-message {
            color:
                #94a3b8;
        }


        /*
        |--------------------------------------------------------------------------
        | Reward Stats
        |--------------------------------------------------------------------------
        */

        .sv-gamification-stats {
            display: grid;

            grid-template-columns:
                repeat(
                    2,
                    minmax(
                        0,
                        1fr
                    )
                );

            gap: 10px;

            margin-top: 22px;
        }

        .sv-gamification-stat {
            position: relative;

            overflow: hidden;

            border:
                1px solid
                #e2e8f0;

            border-radius:
                19px;

            background:
                #f8fafc;

            padding:
                15px 12px;
        }

        html.dark
        .sv-gamification-stat {
            border-color:
                rgba(
                    255,
                    255,
                    255,
                    .08
                );

            background:
                rgba(
                    255,
                    255,
                    255,
                    .035
                );
        }

        .sv-gamification-stat-icon {
            display: block;

            margin-bottom: 6px;

            font-size: 18px;
        }

        .sv-gamification-stat-label {
            display: block;

            color:
                #64748b;

            font-size: 10px;
            font-weight: 800;

            letter-spacing: .08em;

            text-transform: uppercase;
        }

        html.dark
        .sv-gamification-stat-label {
            color:
                #94a3b8;
        }

        .sv-gamification-stat-value {
            display: block;

            margin-top: 4px;

            color:
                #0f172a;

            font-size: 21px;
            font-weight: 900;

            line-height: 1.2;
        }

        html.dark
        .sv-gamification-stat-value {
            color:
                #f8fafc;
        }


        /*
        |--------------------------------------------------------------------------
        | Special Achievement Panels
        |--------------------------------------------------------------------------
        */

        .sv-gamification-specials {
            display: grid;

            gap: 9px;

            margin-top: 15px;
        }

        .sv-gamification-special {
            display: flex;

            align-items: center;

            gap: 12px;

            border:
                1px solid
                #fde68a;

            border-radius:
                17px;

            background:
                #fffbeb;

            padding:
                12px 13px;

            color:
                #92400e;

            text-align: left;
        }

        html.dark
        .sv-gamification-special {
            border-color:
                rgba(
                    245,
                    158,
                    11,
                    .22
                );

            background:
                rgba(
                    245,
                    158,
                    11,
                    .09
                );

            color:
                #fcd34d;
        }

        .sv-gamification-special-icon {
            display: grid;

            width: 42px;
            height: 42px;

            flex: 0 0 42px;

            place-items: center;

            border-radius: 13px;

            background:
                rgba(
                    245,
                    158,
                    11,
                    .12
                );

            font-size: 23px;
        }

        .sv-gamification-special-label {
            display: block;

            font-size: 10px;
            font-weight: 900;

            letter-spacing: .09em;

            text-transform: uppercase;
        }

        .sv-gamification-special-value {
            display: block;

            margin-top: 3px;

            font-size: 14px;
            font-weight: 900;

            line-height: 1.35;
        }


        /*
        |--------------------------------------------------------------------------
        | Summary
        |--------------------------------------------------------------------------
        */

        .sv-gamification-total {
            display: flex;

            align-items: center;
            justify-content: space-between;

            gap: 12px;

            margin-top: 15px;

            border:
                1px solid
                #e2e8f0;

            border-radius:
                17px;

            padding:
                12px 14px;

            text-align: left;
        }

        html.dark
        .sv-gamification-total {
            border-color:
                rgba(
                    255,
                    255,
                    255,
                    .08
                );
        }

        .sv-gamification-total-label {
            color:
                #64748b;

            font-size: 11px;
            font-weight: 700;
        }

        html.dark
        .sv-gamification-total-label {
            color:
                #94a3b8;
        }

        .sv-gamification-total-value {
            margin-top: 2px;

            color:
                #0f172a;

            font-size: 13px;
            font-weight: 900;
        }

        html.dark
        .sv-gamification-total-value {
            color:
                #f8fafc;
        }

        .sv-gamification-total-divider {
            width: 1px;
            height: 30px;

            background:
                #e2e8f0;
        }

        html.dark
        .sv-gamification-total-divider {
            background:
                rgba(
                    255,
                    255,
                    255,
                    .08
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Button
        |--------------------------------------------------------------------------
        */

        .sv-gamification-button {
            width: 100%;

            margin-top: 19px;

            border: 0;

            border-radius: 17px;

            background:
                linear-gradient(
                    135deg,
                    #06b6d4,
                    #2563eb
                );

            color:
                #ffffff;

            padding:
                14px 18px;

            font-family:
                Outfit,
                sans-serif;

            font-size: 14px;
            font-weight: 900;

            cursor: pointer;

            box-shadow:
                0
                12px
                30px
                rgba(
                    37,
                    99,
                    235,
                    .22
                );

            transition:
                transform .18s ease,
                box-shadow .18s ease,
                filter .18s ease;
        }

        .sv-gamification-button:hover {
            transform:
                translateY(-1px);

            filter:
                brightness(1.04);

            box-shadow:
                0
                16px
                36px
                rgba(
                    37,
                    99,
                    235,
                    .27
                );
        }

        .sv-gamification-button:active {
            transform:
                scale(.985);
        }


        /*
        |--------------------------------------------------------------------------
        | Confetti
        |--------------------------------------------------------------------------
        */

        .sv-gamification-confetti {
            position: fixed;

            z-index: 999991;

            top: -30px;

            width: 9px;
            height: 15px;

            border-radius: 2px;

            pointer-events: none;

            animation:
                svRewardConfetti
                var(--sv-confetti-duration)
                linear
                forwards;
        }


        /*
        |--------------------------------------------------------------------------
        | Mobile
        |--------------------------------------------------------------------------
        */

        @media (
            max-width:
            520px
        ) {
            #sv-gamification-backdrop {
                align-items: flex-end;

                padding:
                    12px;
            }

            #sv-gamification-card {
                width: 100%;

                max-height:
                    calc(
                        100vh
                        -
                        24px
                    );

                border-radius:
                    27px;

                padding:
                    25px 18px
                    20px;
            }

            .sv-gamification-icon {
                width: 72px;
                height: 72px;

                border-radius:
                    22px;

                font-size:
                    37px;
            }

            .sv-gamification-title {
                font-size:
                    24px;
            }

            .sv-gamification-message {
                font-size:
                    13px;
            }

            .sv-gamification-stat {
                padding:
                    13px 8px;
            }

            .sv-gamification-stat-value {
                font-size:
                    18px;
            }

            .sv-gamification-total {
                padding:
                    11px 12px;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Reduced Motion
        |--------------------------------------------------------------------------
        */

        @media (
            prefers-reduced-motion:
            reduce
        ) {
            #sv-gamification-backdrop,
            #sv-gamification-card,
            .sv-gamification-icon,
            .sv-gamification-confetti {
                animation:
                    none !important;
            }

            .sv-gamification-button {
                transition:
                    none !important;
            }
        }
    </style>

    <meta name="google" content="notranslate">
    @include('partials.anti-copy')
</head>


<body
    class="font-[Outfit]
           bg-slate-100
           text-slate-900
           transition-colors
           duration-300
           overflow-x-hidden
           dark:bg-[#050816]
           dark:text-white"
>

    {{-- ========================================================= --}}
    {{-- BACKGROUND --}}
    {{-- ========================================================= --}}

    <div
        class="pointer-events-none
               fixed inset-0
               overflow-hidden"
    >

        {{-- GLOW LEFT --}}
        <div
            class="absolute
                   left-[-200px]
                   top-[-200px]
                   h-[450px]
                   w-[450px]
                   rounded-full
                   bg-cyan-500/10
                   blur-3xl"
        ></div>


        {{-- GLOW RIGHT --}}
        <div
            class="absolute
                   bottom-[-200px]
                   right-[-200px]
                   h-[450px]
                   w-[450px]
                   rounded-full
                   bg-purple-500/10
                   blur-3xl"
        ></div>

    </div>


    {{-- ========================================================= --}}
    {{-- APP --}}
    {{-- ========================================================= --}}

    <div
        class="relative z-10
               flex min-h-screen
               flex-col"
    >

        {{-- ===================================================== --}}
        {{-- NAVBAR --}}
        {{-- ===================================================== --}}

        <header
            class="sticky top-0
                   z-50
                   border-b
                   border-slate-200
                   bg-white/80
                   backdrop-blur-xl
                   dark:border-white/10
                   dark:bg-[#050816]/80"
        >

            <div
                class="mx-auto
                       max-w-7xl
                       px-5
                       lg:px-10"
            >

                <div
                    class="flex h-20
                           items-center
                           justify-between"
                >

                    {{-- ========================================= --}}
                    {{-- LEFT --}}
                    {{-- ========================================= --}}

                    <div
                        class="flex
                               items-center
                               gap-12"
                    >

                        {{-- LOGO --}}
                        <a
                            href="{{ route('dashboard') }}"
                            class="group
                                   flex items-center
                                   gap-4"
                        >

                            <div
                                class="flex h-12 w-12
                                       items-center
                                       justify-center
                                       rounded-2xl
                                       bg-gradient-to-br
                                       from-cyan-400
                                       to-blue-600
                                       text-xl font-bold
                                       text-white
                                       shadow-lg
                                       shadow-cyan-500/20
                                       transition
                                       group-hover:scale-105"
                            >
                                S
                            </div>


                            <div class="hidden sm:block">

                                <h1
                                    class="text-xl
                                           font-bold
                                           tracking-wide"
                                >
                                    SpeakVerse
                                </h1>


                                <p
                                    class="text-xs
                                           text-slate-500
                                           dark:text-slate-400"
                                >
                                    English Learning Platform
                                </p>

                            </div>

                        </a>


                        {{-- ===================================== --}}
                        {{-- DESKTOP MENU --}}
                        {{-- ===================================== --}}

                        <nav
                            class="hidden items-center lg:flex"
                            style="gap: clamp(1.25rem, 1.8vw, 2rem); white-space: nowrap;"
                        >

                            <a
                                href="{{ route('dashboard') }}"
                                class="text-sm
                                       font-semibold
                                       transition
                                       {{
                                           request()->routeIs('dashboard')
                                               ? 'text-cyan-400'
                                               : 'text-slate-600 dark:text-slate-300 hover:text-cyan-400'
                                       }}"
                            >
                                Dashboard
                            </a>


                            <a
                                href="{{ route('missions') }}"
                                class="text-sm
                                       font-semibold
                                       transition
                                       {{
                                           request()->routeIs('missions*')
                                               || request()->routeIs('student.*')
                                               ? 'text-cyan-400'
                                               : 'text-slate-600 dark:text-slate-300 hover:text-cyan-400'
                                       }}"
                            >
                                Missions
                            </a>


                            <a
                                href="{{ route('progress') }}"
                                class="text-sm
                                       font-semibold
                                       transition
                                       {{
                                           request()->routeIs('progress')
                                               ? 'text-cyan-400'
                                               : 'text-slate-600 dark:text-slate-300 hover:text-cyan-400'
                                       }}"
                            >
                                Progress
                            </a>


                            {{-- ================================= --}}
                            {{-- REWARDS --}}
                            {{-- ================================= --}}

                            <a
                                href="{{ route('gamification.index') }}"
                                class="text-sm
                                       font-semibold
                                       transition
                                       {{
                                           request()->routeIs('gamification.index')
                                               ? 'text-cyan-400'
                                               : 'text-slate-600 dark:text-slate-300 hover:text-cyan-400'
                                       }}"
                            >
                                Rewards
                            </a>


                            {{-- ================================= --}}
                            {{-- LEADERBOARD --}}
                            {{-- ================================= --}}

                            <a
                                href="{{ route('gamification.leaderboard') }}"
                                class="text-sm
                                       font-semibold
                                       transition
                                       {{
                                           request()->routeIs('gamification.leaderboard')
                                               ? 'text-cyan-400'
                                               : 'text-slate-600 dark:text-slate-300 hover:text-cyan-400'
                                       }}"
                            >
                                Leaderboard
                            </a>


                            @if (
                                (Auth::user()->role ?? 'user')
                                ===
                                'admin'
                            )

                                <a
                                    href="{{ route('admin.dashboard') }}"
                                    class="text-sm
                                           font-semibold
                                           text-red-400
                                           transition
                                           hover:text-red-300"
                                >
                                    Admin Panel
                                </a>

                            @endif

                        </nav>

                    </div>


                    {{-- ========================================= --}}
                    {{-- RIGHT --}}
                    {{-- ========================================= --}}

                    <div
                        class="flex
                               items-center
                               gap-3"
                    >

                        {{-- ===================================== --}}
                        {{-- DARK MODE --}}
                        {{-- ===================================== --}}

                        <button
                            type="button"
                            @click="darkMode = !darkMode"
                            class="flex h-12 w-12
                                   items-center
                                   justify-center
                                   rounded-2xl
                                   border
                                   border-slate-200
                                   bg-white
                                   shadow-sm
                                   transition
                                   hover:scale-105
                                   dark:border-white/10
                                   dark:bg-white/5"
                            aria-label="Toggle dark mode"
                        >

                            <span
                                x-cloak
                                x-show="darkMode"
                            >
                                ☀️
                            </span>

                            <span
                                x-cloak
                                x-show="!darkMode"
                            >
                                🌙
                            </span>

                        </button>


                        {{-- ===================================== --}}
                        {{-- PROFILE --}}
                        {{-- ===================================== --}}

                        <div class="relative">

                            {{-- BUTTON --}}
                            <button
                                type="button"
                                @click="profileMenu = !profileMenu"
                                class="flex
                                       items-center
                                       gap-3
                                       rounded-2xl
                                       border
                                       border-slate-200
                                       bg-white
                                       py-2 pl-3 pr-2
                                       shadow-sm
                                       transition
                                       hover:bg-slate-50
                                       dark:border-white/10
                                       dark:bg-white/5
                                       dark:hover:bg-white/10"
                            >

                                <div
                                    class="hidden
                                           text-right
                                           md:block"
                                >

                                    <h3
                                        class="text-sm
                                               font-semibold
                                               leading-none"
                                    >
                                        {{ Auth::user()->name }}
                                    </h3>


                                    <p
                                        class="mt-1
                                               text-xs
                                               capitalize
                                               text-slate-500
                                               dark:text-slate-400"
                                    >
                                        {{ Auth::user()->role ?? 'student' }}
                                    </p>

                                </div>


                                <div
                                    class="flex h-11 w-11
                                           items-center
                                           justify-center
                                           rounded-2xl
                                           bg-gradient-to-br
                                           from-cyan-400
                                           to-blue-600
                                           font-bold
                                           text-white
                                           shadow-lg
                                           shadow-cyan-500/20"
                                >
                                    {{
                                        strtoupper(
                                            substr(
                                                Auth::user()->name,
                                                0,
                                                1
                                            )
                                        )
                                    }}
                                </div>

                            </button>


                            {{-- ================================= --}}
                            {{-- PROFILE DROPDOWN --}}
                            {{-- ================================= --}}

                            <div
                                x-cloak
                                x-show="profileMenu"

                                @click.outside="
                                    profileMenu = false
                                "

                                x-transition:enter="
                                    transition
                                    ease-out
                                    duration-200
                                "

                                x-transition:enter-start="
                                    opacity-0
                                    translate-y-2
                                    scale-95
                                "

                                x-transition:enter-end="
                                    opacity-100
                                    translate-y-0
                                    scale-100
                                "

                                x-transition:leave="
                                    transition
                                    ease-in
                                    duration-150
                                "

                                x-transition:leave-start="
                                    opacity-100
                                    translate-y-0
                                    scale-100
                                "

                                x-transition:leave-end="
                                    opacity-0
                                    translate-y-2
                                    scale-95
                                "

                                class="absolute
                                       right-0 z-50
                                       mt-4 w-72
                                       overflow-hidden
                                       rounded-[28px]
                                       border
                                       border-slate-200
                                       bg-white
                                       shadow-[0_20px_60px_rgba(0,0,0,0.18)]
                                       dark:border-white/10
                                       dark:bg-[#0f172a]
                                       dark:shadow-[0_20px_60px_rgba(0,0,0,0.45)]"
                            >

                                {{-- HEADER --}}
                                <div
                                    class="border-b
                                           border-slate-200
                                           bg-slate-50
                                           p-5
                                           dark:border-white/10
                                           dark:bg-white/[0.03]"
                                >

                                    <div
                                        class="flex
                                               items-center
                                               gap-4"
                                    >

                                        <div
                                            class="flex h-14 w-14
                                                   items-center
                                                   justify-center
                                                   rounded-2xl
                                                   bg-gradient-to-br
                                                   from-cyan-400
                                                   to-blue-600
                                                   text-lg font-bold
                                                   text-white
                                                   shadow-lg
                                                   shadow-cyan-500/20"
                                        >
                                            {{
                                                strtoupper(
                                                    substr(
                                                        Auth::user()->name,
                                                        0,
                                                        1
                                                    )
                                                )
                                            }}
                                        </div>


                                        <div class="min-w-0">

                                            <h3
                                                class="truncate
                                                       text-lg
                                                       font-bold
                                                       leading-none"
                                            >
                                                {{ Auth::user()->name }}
                                            </h3>


                                            <p
                                                class="mt-2
                                                       truncate
                                                       text-sm
                                                       text-slate-500
                                                       dark:text-slate-400"
                                            >
                                                {{ Auth::user()->email }}
                                            </p>

                                        </div>

                                    </div>

                                </div>


                                {{-- MENU --}}
                                <div
                                    class="flex
                                           flex-col
                                           gap-1
                                           p-3"
                                >

                                    <a
                                        href="{{ route('profile.edit') }}"
                                        class="flex
                                               items-center
                                               gap-3
                                               rounded-2xl
                                               px-4 py-3
                                               text-sm
                                               font-medium
                                               transition
                                               hover:bg-slate-100
                                               dark:hover:bg-white/[0.06]"
                                    >
                                        <span>
                                            ⚙️
                                        </span>

                                        <span>
                                            Profile Settings
                                        </span>
                                    </a>


                                    <a
                                        href="{{ route('progress') }}"
                                        class="flex
                                               items-center
                                               gap-3
                                               rounded-2xl
                                               px-4 py-3
                                               text-sm
                                               font-medium
                                               transition
                                               hover:bg-slate-100
                                               dark:hover:bg-white/[0.06]"
                                    >
                                        <span>
                                            📈
                                        </span>

                                        <span>
                                            My Progress
                                        </span>
                                    </a>


                                    <a
                                        href="{{ route('gamification.index') }}"
                                        class="flex
                                               items-center
                                               gap-3
                                               rounded-2xl
                                               px-4 py-3
                                               text-sm
                                               font-medium
                                               transition
                                               hover:bg-slate-100
                                               dark:hover:bg-white/[0.06]"
                                    >
                                        <span>
                                            🎁
                                        </span>

                                        <span>
                                            Rewards
                                        </span>
                                    </a>


                                    <a
                                        href="{{ route('gamification.leaderboard') }}"
                                        class="flex
                                               items-center
                                               gap-3
                                               rounded-2xl
                                               px-4 py-3
                                               text-sm
                                               font-medium
                                               transition
                                               hover:bg-slate-100
                                               dark:hover:bg-white/[0.06]"
                                    >
                                        <span>
                                            🏆
                                        </span>

                                        <span>
                                            Leaderboard
                                        </span>
                                    </a>


                                    <div
                                        class="my-2
                                               border-t
                                               border-slate-200
                                               dark:border-white/10"
                                    ></div>


                                    <form
                                        method="POST"
                                        action="{{ route('logout') }}"
                                    >
                                        @csrf

                                        <button
                                            type="submit"
                                            class="flex w-full
                                                   items-center
                                                   gap-3
                                                   rounded-2xl
                                                   px-4 py-3
                                                   text-sm
                                                   font-medium
                                                   text-red-400
                                                   transition
                                                   hover:bg-red-500/10"
                                        >
                                            <span>
                                                🚪
                                            </span>

                                            <span>
                                                Logout
                                            </span>
                                        </button>

                                    </form>

                                </div>

                            </div>

                        </div>


                        {{-- ===================================== --}}
                        {{-- MOBILE BUTTON --}}
                        {{-- ===================================== --}}

                        <button
                            type="button"
                            @click="mobileMenu = !mobileMenu"
                            class="flex h-12 w-12
                                   items-center
                                   justify-center
                                   rounded-2xl
                                   border
                                   border-slate-200
                                   bg-white
                                   shadow-sm
                                   lg:hidden
                                   dark:border-white/10
                                   dark:bg-white/5"
                            aria-label="Open navigation menu"
                        >
                            ☰
                        </button>

                    </div>

                </div>

            </div>

        </header>


        {{-- ===================================================== --}}
        {{-- MOBILE OVERLAY --}}
        {{-- ===================================================== --}}

        <div
            x-cloak
            x-show="mobileMenu"
            x-transition.opacity
            @click="mobileMenu = false"
            class="fixed inset-0
                   z-40
                   bg-black/30
                   lg:hidden"
        ></div>


        {{-- ===================================================== --}}
        {{-- MOBILE MENU --}}
        {{-- ===================================================== --}}

        <aside
            x-cloak
            x-show="mobileMenu"

            x-transition:enter="
                transition
                duration-300
            "

            x-transition:enter-start="
                translate-x-full
            "

            x-transition:enter-end="
                translate-x-0
            "

            x-transition:leave="
                transition
                duration-300
            "

            x-transition:leave-start="
                translate-x-0
            "

            x-transition:leave-end="
                translate-x-full
            "

            class="fixed
                   right-0 top-0
                   z-50
                   h-full
                   w-[300px]
                   overflow-y-auto
                   border-l
                   border-slate-200
                   bg-white
                   p-6
                   lg:hidden
                   dark:border-white/10
                   dark:bg-[#0b1220]"
        >

            <div
                class="mb-10
                       flex items-center
                       justify-between"
            >

                <div>

                    <h2
                        class="text-xl
                               font-bold"
                    >
                        Menu
                    </h2>

                    <p
                        class="mt-1
                               text-xs
                               text-slate-500
                               dark:text-slate-400"
                    >
                        SpeakVerse Learning
                    </p>

                </div>


                <button
                    type="button"
                    @click="mobileMenu = false"
                    class="h-10 w-10
                           rounded-xl
                           bg-slate-100
                           dark:bg-white/10"
                    aria-label="Close navigation menu"
                >
                    ✕
                </button>

            </div>


            <nav
                class="flex
                       flex-col
                       gap-2"
            >

                <a
                    href="{{ route('dashboard') }}"
                    class="rounded-2xl
                           px-4 py-3
                           transition
                           {{
                               request()->routeIs('dashboard')
                                   ? 'bg-cyan-500/10 text-cyan-400 font-semibold'
                                   : 'hover:bg-slate-100 dark:hover:bg-white/10'
                           }}"
                >
                    Dashboard
                </a>


                <a
                    href="{{ route('missions') }}"
                    class="rounded-2xl
                           px-4 py-3
                           transition
                           {{
                               request()->routeIs('missions*')
                                   || request()->routeIs('student.*')
                                   ? 'bg-cyan-500/10 text-cyan-400 font-semibold'
                                   : 'hover:bg-slate-100 dark:hover:bg-white/10'
                           }}"
                >
                    Missions
                </a>


                <a
                    href="{{ route('progress') }}"
                    class="rounded-2xl
                           px-4 py-3
                           transition
                           {{
                               request()->routeIs('progress')
                                   ? 'bg-cyan-500/10 text-cyan-400 font-semibold'
                                   : 'hover:bg-slate-100 dark:hover:bg-white/10'
                           }}"
                >
                    Progress
                </a>


                <a
                    href="{{ route('gamification.index') }}"
                    class="rounded-2xl
                           px-4 py-3
                           transition
                           {{
                               request()->routeIs('gamification.index')
                                   ? 'bg-cyan-500/10 text-cyan-400 font-semibold'
                                   : 'hover:bg-slate-100 dark:hover:bg-white/10'
                           }}"
                >
                    🎁 Rewards
                </a>


                <a
                    href="{{ route('gamification.leaderboard') }}"
                    class="rounded-2xl
                           px-4 py-3
                           transition
                           {{
                               request()->routeIs('gamification.leaderboard')
                                   ? 'bg-cyan-500/10 text-cyan-400 font-semibold'
                                   : 'hover:bg-slate-100 dark:hover:bg-white/10'
                           }}"
                >
                    🏆 Leaderboard
                </a>


                <a
                    href="{{ route('profile.edit') }}"
                    class="rounded-2xl
                           px-4 py-3
                           transition
                           {{
                               request()->routeIs('profile.edit')
                                   ? 'bg-cyan-500/10 text-cyan-400 font-semibold'
                                   : 'hover:bg-slate-100 dark:hover:bg-white/10'
                           }}"
                >
                    Profile Settings
                </a>


                @if (
                    (Auth::user()->role ?? 'user')
                    ===
                    'admin'
                )

                    <a
                        href="{{ route('admin.dashboard') }}"
                        class="rounded-2xl
                               px-4 py-3
                               font-semibold
                               text-red-400
                               transition
                               hover:bg-red-500/10"
                    >
                        Admin Panel
                    </a>

                @endif


                <div
                    class="my-2
                           border-t
                           border-slate-200
                           dark:border-white/10"
                ></div>


                <form
                    method="POST"
                    action="{{ route('logout') }}"
                >
                    @csrf

                    <button
                        type="submit"
                        class="w-full
                               rounded-2xl
                               px-4 py-3
                               text-left
                               text-red-400
                               transition
                               hover:bg-red-500/10"
                    >
                        Logout
                    </button>

                </form>

            </nav>

        </aside>


        {{-- ===================================================== --}}
        {{-- CONTENT --}}
        {{-- ===================================================== --}}

        <main class="flex-1">

            <div
                class="mx-auto
                       max-w-7xl
                       px-5
                       py-10
                       lg:px-10"
            >
                {{ $slot }}
            </div>

        </main>

    </div>


    {{-- ========================================================= --}}
    {{-- GAMIFICATION POPUP --}}
    {{-- ========================================================= --}}

    @php
        $serverGamificationReward =
            session('gamification_reward');

        $hasServerGamificationReward =
            is_array($serverGamificationReward)
            &&
            (
                $serverGamificationReward['show_popup']
                ??
                false
            );
    @endphp


    <script>
        (() => {
            'use strict';

            /*
            |--------------------------------------------------------------------------
            | SpeakVerse Gamification Popup
            |--------------------------------------------------------------------------
            |
            | Inline fallback ini tidak membutuhkan npm / Vite rebuild.
            |
            | Mendukung:
            |
            | - Reward dari session Laravel
            | - Reward dari response JSON fetch()
            | - XP
            | - SpeakCoins
            | - Unit Complete
            | - Level Up
            | - Badge
            | - Confetti
            |
            */

            const STORAGE_KEY =
                'speakverse_pending_gamification_reward';

            const STORAGE_MAX_AGE =
                2 * 60 * 1000;

            const rewardQueue = [];

            let rewardIsOpen =
                false;


            /*
            |--------------------------------------------------------------------------
            | Escape HTML
            |--------------------------------------------------------------------------
            */

            function escapeHtml(value) {
                return String(
                    value ?? ''
                )
                    .replaceAll(
                        '&',
                        '&amp;'
                    )
                    .replaceAll(
                        '<',
                        '&lt;'
                    )
                    .replaceAll(
                        '>',
                        '&gt;'
                    )
                    .replaceAll(
                        '"',
                        '&quot;'
                    )
                    .replaceAll(
                        "'",
                        '&#039;'
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | Normalisasi Number
            |--------------------------------------------------------------------------
            */

            function numberValue(value) {
                const number =
                    Number(value ?? 0);

                return Number.isFinite(number)
                    ? number
                    : 0;
            }


            /*
            |--------------------------------------------------------------------------
            | Number Format
            |--------------------------------------------------------------------------
            */

            function formatNumber(value) {
                return numberValue(
                    value
                ).toLocaleString();
            }


            /*
            |--------------------------------------------------------------------------
            | Pending Reward Storage
            |--------------------------------------------------------------------------
            |
            | Digunakan jika halaman melakukan redirect sangat cepat setelah
            | menerima response JSON.
            |
            | Popup akan muncul lagi pada halaman tujuan.
            |
            */

            function storePendingReward(
                reward
            ) {
                try {
                    sessionStorage.setItem(
                        STORAGE_KEY,
                        JSON.stringify({
                            createdAt:
                                Date.now(),

                            reward:
                                reward,
                        })
                    );
                } catch (error) {
                    /*
                     * Storage failure tidak boleh
                     * merusak proses belajar.
                     */
                }
            }


            function clearPendingReward() {
                try {
                    sessionStorage.removeItem(
                        STORAGE_KEY
                    );
                } catch (error) {
                    /*
                     * Ignore storage error.
                     */
                }
            }


            function getPendingReward() {
                try {
                    const raw =
                        sessionStorage.getItem(
                            STORAGE_KEY
                        );

                    if (!raw) {
                        return null;
                    }

                    const stored =
                        JSON.parse(raw);

                    if (
                        !stored
                        ||
                        !stored.reward
                        ||
                        !stored.createdAt
                    ) {
                        clearPendingReward();

                        return null;
                    }

                    if (
                        Date.now()
                        -
                        Number(
                            stored.createdAt
                        )
                        >
                        STORAGE_MAX_AGE
                    ) {
                        clearPendingReward();

                        return null;
                    }

                    return stored.reward;
                } catch (error) {
                    clearPendingReward();

                    return null;
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Confetti
            |--------------------------------------------------------------------------
            */

            function createConfetti() {
                const colors = [
                    '#06b6d4',
                    '#2563eb',
                    '#7c3aed',
                    '#f59e0b',
                    '#10b981',
                    '#ef4444',
                ];

                const reducedMotion =
                    window.matchMedia(
                        '(prefers-reduced-motion: reduce)'
                    ).matches;

                if (reducedMotion) {
                    return;
                }

                for (
                    let index = 0;
                    index < 48;
                    index += 1
                ) {
                    const piece =
                        document.createElement(
                            'span'
                        );

                    piece.className =
                        'sv-gamification-confetti';

                    piece.style.left =
                        `${
                            Math.random()
                            *
                            100
                        }vw`;

                    piece.style.background =
                        colors[
                            Math.floor(
                                Math.random()
                                *
                                colors.length
                            )
                        ];

                    piece.style.setProperty(
                        '--sv-confetti-duration',
                        `${
                            2.8
                            +
                            (
                                Math.random()
                                *
                                2.3
                            )
                        }s`
                    );

                    piece.style.setProperty(
                        '--sv-confetti-x',
                        `${
                            -140
                            +
                            (
                                Math.random()
                                *
                                280
                            )
                        }px`
                    );

                    piece.style.setProperty(
                        '--sv-confetti-rotation',
                        `${
                            520
                            +
                            (
                                Math.random()
                                *
                                720
                            )
                        }deg`
                    );

                    piece.style.animationDelay =
                        `${
                            Math.random()
                            *
                            .4
                        }s`;

                    document.body.appendChild(
                        piece
                    );

                    window.setTimeout(
                        () => {
                            piece.remove();
                        },
                        5900
                    );
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Special Panels
            |--------------------------------------------------------------------------
            */

            function buildSpecialPanels(
                reward,
                badges
            ) {
                const panels = [];


                /*
                 * Unit Completed
                 */

                if (
                    reward.unit_completed
                ) {
                    const unitTitle =
                        reward.unit
                            ?.title
                        ||
                        'Learning Unit';

                    panels.push(`
                        <div
                            class="sv-gamification-special"
                        >
                            <span
                                class="sv-gamification-special-icon"
                            >
                                🏆
                            </span>

                            <div>
                                <span
                                    class="sv-gamification-special-label"
                                >
                                    Unit Completed
                                </span>

                                <span
                                    class="sv-gamification-special-value"
                                >
                                    ${
                                        escapeHtml(
                                            unitTitle
                                        )
                                    }
                                </span>
                            </div>
                        </div>
                    `);
                }


                /*
                 * Level Up
                 */

                if (
                    reward.level_up
                ) {
                    panels.push(`
                        <div
                            class="sv-gamification-special"
                        >
                            <span
                                class="sv-gamification-special-icon"
                            >
                                📈
                            </span>

                            <div>
                                <span
                                    class="sv-gamification-special-label"
                                >
                                    Level Up
                                </span>

                                <span
                                    class="sv-gamification-special-value"
                                >
                                    Level
                                    ${
                                        numberValue(
                                            reward.level_before
                                            ||
                                            1
                                        )
                                    }

                                    →

                                    Level
                                    ${
                                        numberValue(
                                            reward.level_after
                                            ||
                                            1
                                        )
                                    }
                                </span>
                            </div>
                        </div>
                    `);
                }


                /*
                 * Badges
                 */

                badges.forEach(
                    (badge) => {
                        panels.push(`
                            <div
                                class="sv-gamification-special"
                            >
                                <span
                                    class="sv-gamification-special-icon"
                                >
                                    ${
                                        escapeHtml(
                                            badge.icon
                                            ||
                                            '🏅'
                                        )
                                    }
                                </span>

                                <div>
                                    <span
                                        class="sv-gamification-special-label"
                                    >
                                        Achievement Unlocked
                                    </span>

                                    <span
                                        class="sv-gamification-special-value"
                                    >
                                        ${
                                            escapeHtml(
                                                badge.name
                                                ||
                                                'New Badge'
                                            )
                                        }
                                    </span>
                                </div>
                            </div>
                        `);
                    }
                );


                if (
                    panels.length === 0
                ) {
                    return '';
                }

                return `
                    <div
                        class="sv-gamification-specials"
                    >
                        ${
                            panels.join('')
                        }
                    </div>
                `;
            }


            /*
            |--------------------------------------------------------------------------
            | Render Reward
            |--------------------------------------------------------------------------
            */

            function renderReward(
                reward,
                options = {}
            ) {
                if (
                    !reward
                    ||
                    !reward.show_popup
                ) {
                    return;
                }

                if (rewardIsOpen) {
                    rewardQueue.push({
                        reward:
                            reward,

                        options:
                            options,
                    });

                    return;
                }


                rewardIsOpen =
                    true;


                if (
                    options.persist
                ) {
                    storePendingReward(
                        reward
                    );
                }


                const badges =
                    Array.isArray(
                        reward.badges
                    )
                        ? reward.badges
                        : [];


                /*
                |--------------------------------------------------------------------------
                | Icon
                |--------------------------------------------------------------------------
                */

                const icon =
                    reward.unit_completed
                        ? '🏆'
                        : reward.level_up
                            ? '✨'
                            : badges.length > 0
                                ? '🏅'
                                : '🎉';


                /*
                |--------------------------------------------------------------------------
                | Eyebrow
                |--------------------------------------------------------------------------
                */

                const eyebrow =
                    reward.unit_completed
                        ? 'Unit Reward'
                        : reward.level_up
                            ? 'New Milestone'
                            : badges.length > 0
                                ? 'Achievement'
                                : 'Mission Reward';


                /*
                |--------------------------------------------------------------------------
                | Backdrop
                |--------------------------------------------------------------------------
                */

                const backdrop =
                    document.createElement(
                        'div'
                    );

                backdrop.id =
                    'sv-gamification-backdrop';


                /*
                |--------------------------------------------------------------------------
                | Special Panels
                |--------------------------------------------------------------------------
                */

                const specials =
                    buildSpecialPanels(
                        reward,
                        badges
                    );


                /*
                |--------------------------------------------------------------------------
                | Totals
                |--------------------------------------------------------------------------
                */

                const totalXp =
                    formatNumber(
                        reward.total_xp
                        ??
                        0
                    );

                const totalCoins =
                    formatNumber(
                        reward.total_coins
                        ??
                        0
                    );


                /*
                |--------------------------------------------------------------------------
                | HTML
                |--------------------------------------------------------------------------
                */

                backdrop.innerHTML = `
                    <div
                        id="sv-gamification-card"
                        role="dialog"
                        aria-modal="true"
                        aria-labelledby="sv-gamification-title"
                    >
                        <div
                            class="sv-gamification-glow"
                        ></div>


                        <div
                            class="sv-gamification-icon"
                            aria-hidden="true"
                        >
                            ${icon}
                        </div>


                        <div
                            class="sv-gamification-eyebrow"
                        >
                            ✦
                            ${
                                escapeHtml(
                                    eyebrow
                                )
                            }
                        </div>


                        <h2
                            id="sv-gamification-title"
                            class="sv-gamification-title"
                        >
                            ${
                                escapeHtml(
                                    reward.title
                                    ||
                                    'Great Job!'
                                )
                            }
                        </h2>


                        <p
                            class="sv-gamification-message"
                        >
                            ${
                                escapeHtml(
                                    reward.message
                                    ||
                                    'Your learning progress has been saved.'
                                )
                            }
                        </p>


                        <div
                            class="sv-gamification-stats"
                        >
                            <div
                                class="sv-gamification-stat"
                            >
                                <span
                                    class="sv-gamification-stat-icon"
                                >
                                    ⚡
                                </span>

                                <span
                                    class="sv-gamification-stat-label"
                                >
                                    XP Earned
                                </span>

                                <span
                                    class="sv-gamification-stat-value"
                                >
                                    +${
                                        formatNumber(
                                            reward.xp_earned
                                            ||
                                            0
                                        )
                                    }
                                    XP
                                </span>
                            </div>


                            <div
                                class="sv-gamification-stat"
                            >
                                <span
                                    class="sv-gamification-stat-icon"
                                >
                                    🪙
                                </span>

                                <span
                                    class="sv-gamification-stat-label"
                                >
                                    SpeakCoins
                                </span>

                                <span
                                    class="sv-gamification-stat-value"
                                >
                                    +${
                                        formatNumber(
                                            reward.coins_earned
                                            ||
                                            0
                                        )
                                    }
                                </span>
                            </div>
                        </div>


                        ${specials}


                        <div
                            class="sv-gamification-total"
                        >
                            <div>
                                <div
                                    class="sv-gamification-total-label"
                                >
                                    Total XP
                                </div>

                                <div
                                    class="sv-gamification-total-value"
                                >
                                    ⚡
                                    ${totalXp}
                                    XP
                                </div>
                            </div>


                            <div
                                class="sv-gamification-total-divider"
                            ></div>


                            <div>
                                <div
                                    class="sv-gamification-total-label"
                                >
                                    Balance
                                </div>

                                <div
                                    class="sv-gamification-total-value"
                                >
                                    🪙
                                    ${totalCoins}
                                </div>
                            </div>


                            <div
                                class="sv-gamification-total-divider"
                            ></div>


                            <div>
                                <div
                                    class="sv-gamification-total-label"
                                >
                                    Streak
                                </div>

                                <div
                                    class="sv-gamification-total-value"
                                >
                                    🔥
                                    ${
                                        formatNumber(
                                            reward.current_streak
                                            ||
                                            0
                                        )
                                    }
                                </div>
                            </div>
                        </div>


                        <button
                            type="button"
                            class="sv-gamification-button"
                        >
                            Continue Learning
                        </button>
                    </div>
                `;


                /*
                |--------------------------------------------------------------------------
                | Close Handler
                |--------------------------------------------------------------------------
                */

                let closed =
                    false;


                const previousOverflow =
                    document.body
                        .style
                        .overflow;


                document.body
                    .style
                    .overflow =
                        'hidden';


                const close =
                    () => {
                        if (closed) {
                            return;
                        }

                        closed =
                            true;


                        document.removeEventListener(
                            'keydown',
                            handleEscape
                        );


                        backdrop.remove();


                        document.body
                            .style
                            .overflow =
                                previousOverflow;


                        /*
                         * Jika user sudah melihat popup
                         * dan menutupnya, pending reward
                         * tidak perlu ditampilkan lagi.
                         */
                        clearPendingReward();


                        rewardIsOpen =
                            false;


                        /*
                         * Tampilkan reward berikutnya
                         * jika queue berisi data.
                         */
                        if (
                            rewardQueue.length
                            >
                            0
                        ) {
                            const next =
                                rewardQueue.shift();

                            window.setTimeout(
                                () => {
                                    renderReward(
                                        next.reward,
                                        next.options
                                    );
                                },
                                120
                            );
                        }
                    };


                const handleEscape =
                    (event) => {
                        if (
                            event.key
                            ===
                            'Escape'
                        ) {
                            close();
                        }
                    };


                /*
                |--------------------------------------------------------------------------
                | Events
                |--------------------------------------------------------------------------
                */

                backdrop
                    .querySelector(
                        '.sv-gamification-button'
                    )
                    ?.addEventListener(
                        'click',
                        close
                    );


                backdrop.addEventListener(
                    'click',
                    (event) => {
                        if (
                            event.target
                            ===
                            backdrop
                        ) {
                            close();
                        }
                    }
                );


                document.addEventListener(
                    'keydown',
                    handleEscape
                );


                /*
                |--------------------------------------------------------------------------
                | Append
                |--------------------------------------------------------------------------
                */

                document.body.appendChild(
                    backdrop
                );


                /*
                 * Fokus ke tombol supaya keyboard
                 * navigation langsung bekerja.
                 */
                window.setTimeout(
                    () => {
                        backdrop
                            .querySelector(
                                '.sv-gamification-button'
                            )
                            ?.focus();
                    },
                    100
                );


                /*
                |--------------------------------------------------------------------------
                | Confetti
                |--------------------------------------------------------------------------
                */

                if (
                    reward.unit_completed
                    ||
                    reward.level_up
                    ||
                    badges.length > 0
                ) {
                    createConfetti();
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Global Public Function
            |--------------------------------------------------------------------------
            */

            window.showGamificationReward =
                function (
                    reward,
                    options = {}
                ) {
                    renderReward(
                        reward,
                        options
                    );
                };


            /*
            |--------------------------------------------------------------------------
            | Fetch Interceptor
            |--------------------------------------------------------------------------
            |
            | Unit Listening / Reading / Writing / Speaking mengembalikan
            | gamification melalui JSON.
            |
            | Response asli di-clone sehingga script quiz tetap dapat membaca
            | response tanpa terganggu.
            |
            */

            if (
                typeof window.fetch
                ===
                'function'
                &&
                !window.__speakverseInlineFetchWrapped
            ) {
                window.__speakverseInlineFetchWrapped =
                    true;


                const originalFetch =
                    window.fetch.bind(
                        window
                    );


                window.fetch =
                    async (...args) => {
                        const response =
                            await originalFetch(
                                ...args
                            );

                        try {
                            const cloned =
                                response.clone();


                            const contentType =
                                cloned.headers.get(
                                    'content-type'
                                )
                                ||
                                '';


                            if (
                                contentType.includes(
                                    'application/json'
                                )
                            ) {
                                const data =
                                    await cloned.json();


                                const reward =
                                    data
                                        ?.gamification;


                                if (
                                    reward
                                        ?.show_popup
                                ) {
                                    /*
                                     * Persist terlebih dahulu.
                                     *
                                     * Jika script quiz melakukan redirect
                                     * sangat cepat, reward masih dapat
                                     * ditampilkan pada halaman tujuan.
                                     */
                                    storePendingReward(
                                        reward
                                    );


                                    renderReward(
                                        reward,
                                        {
                                            persist:
                                                false,
                                        }
                                    );
                                }
                            }
                        } catch (error) {
                            /*
                             * Popup error tidak boleh merusak
                             * proses utama quiz.
                             */
                            console.debug(
                                'SpeakVerse reward popup skipped.',
                                error
                            );
                        }


                        return response;
                    };
            }


            /*
            |--------------------------------------------------------------------------
            | Laravel Session Reward
            |--------------------------------------------------------------------------
            */

            const serverReward =
                @json(
                    $hasServerGamificationReward
                        ? $serverGamificationReward
                        : null
                );


            /*
            |--------------------------------------------------------------------------
            | Show Initial Reward
            |--------------------------------------------------------------------------
            */

            function showInitialReward() {
                /*
                 * Prioritaskan reward dari Laravel session.
                 */
                if (
                    serverReward
                    &&
                    serverReward.show_popup
                ) {
                    clearPendingReward();

                    renderReward(
                        serverReward,
                        {
                            persist:
                                false,
                        }
                    );

                    return;
                }


                /*
                 * Jika sebelumnya request JSON
                 * sempat redirect sebelum popup selesai,
                 * tampilkan pending reward.
                 */
                const pendingReward =
                    getPendingReward();


                if (
                    pendingReward
                    &&
                    pendingReward.show_popup
                ) {
                    renderReward(
                        pendingReward,
                        {
                            persist:
                                false,
                        }
                    );
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Jalankan setelah DOM siap
            |--------------------------------------------------------------------------
            */

            if (
                document.readyState
                ===
                'loading'
            ) {
                document.addEventListener(
                    'DOMContentLoaded',
                    showInitialReward,
                    {
                        once:
                            true,
                    }
                );
            } else {
                showInitialReward();
            }
        })();
    </script>

</body>

</html>