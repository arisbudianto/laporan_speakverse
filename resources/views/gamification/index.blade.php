<x-app-layout>

    @php
        $level = $gamification['level'] ?? [];

        $currentLevel = max(
            1,
            (int) ($level['level'] ?? 1)
        );

        $levelName = (string) (
            $level['name']
            ?? 'Rookie'
        );

        $xpIntoLevel = (int) (
            $level['xp_into_level']
            ?? 0
        );

        $xpForNextLevel = max(
            1,
            (int) (
                $level['xp_for_next_level']
                ?? 500
            )
        );

        $remainingXp = max(
            0,
            (int) (
                $level['remaining_xp']
                ?? 0
            )
        );

        $levelProgress = max(
            0,
            min(
                100,
                (int) (
                    $level['progress']
                    ?? 0
                )
            )
        );

        $totalXp = (int) (
            $gamification['xp']
            ?? 0
        );

        $coins = (int) (
            $gamification['coins']
            ?? 0
        );

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

        $badges = collect(
            $gamification['badges']
            ?? []
        );

        $totalRewardItems = $rewardItems->count();

        $ownedCount = $ownedRewardItemIds->count();
    @endphp


    <style>
        /*
        |--------------------------------------------------------------------------
        | SPEAKVERSE REWARDS
        |--------------------------------------------------------------------------
        |
        | Native CSS + inline SVG.
        | Tidak membutuhkan npm build.
        | Tidak menggunakan emoji Unicode.
        |
        */

        .sv-rewards {
            --card: #ffffff;
            --card-soft: #f8fafc;
            --card-muted: #f1f5f9;

            --text: #0f172a;
            --text-soft: #334155;
            --muted: #64748b;

            --border: #e2e8f0;

            --blue: #2563eb;
            --blue-soft: #eff6ff;

            --cyan: #0891b2;
            --cyan-soft: #ecfeff;

            --amber: #d97706;
            --amber-soft: #fffbeb;

            --orange: #ea580c;
            --orange-soft: #fff7ed;

            --green: #059669;
            --green-soft: #ecfdf5;

            --violet: #7c3aed;
            --violet-soft: #f5f3ff;

            --red: #dc2626;
            --red-soft: #fef2f2;

            --shadow:
                0 16px 42px
                rgba(15, 23, 42, .07);

            width: 100%;
            max-width: 1180px;

            margin: 0 auto;

            color: var(--text);
        }


        html.dark .sv-rewards,
        .dark .sv-rewards {
            --card: #0f172a;
            --card-soft: #111c31;
            --card-muted: #172238;

            --text: #f8fafc;
            --text-soft: #d6e0ed;
            --muted: #94a3b8;

            --border: #26364d;

            --blue: #60a5fa;
            --blue-soft: #132640;

            --cyan: #22d3ee;
            --cyan-soft: #0b2c3a;

            --amber: #fbbf24;
            --amber-soft: #33250c;

            --orange: #fb923c;
            --orange-soft: #351c0c;

            --green: #34d399;
            --green-soft: #0b2e29;

            --violet: #c084fc;
            --violet-soft: #2a183e;

            --red: #f87171;
            --red-soft: #32171c;

            --shadow:
                0 18px 48px
                rgba(0, 0, 0, .28);
        }


        .sv-rewards,
        .sv-rewards * {
            box-sizing: border-box;
        }


        .sv-svg {
            width: 20px;
            height: 20px;

            display: block;

            fill: none;
            stroke: currentColor;

            stroke-width: 1.9;
            stroke-linecap: round;
            stroke-linejoin: round;
        }


        /*
        |--------------------------------------------------------------------------
        | ALERT
        |--------------------------------------------------------------------------
        */

        .sv-alert {
            display: flex;

            align-items: center;

            gap: 10px;

            margin-bottom: 18px;

            padding:
                13px
                15px;

            border: 1px solid;

            border-radius: 15px;

            font-size: 12px;
            font-weight: 700;

            line-height: 1.5;
        }


        .sv-alert-success {
            border-color:
                rgba(16, 185, 129, .25);

            background:
                var(--green-soft);

            color:
                var(--green);
        }


        .sv-alert-error {
            border-color:
                rgba(239, 68, 68, .25);

            background:
                var(--red-soft);

            color:
                var(--red);
        }


        .sv-alert-mark {
            display: grid;

            width: 26px;
            height: 26px;

            flex: 0 0 26px;

            place-items: center;

            border-radius: 8px;

            background:
                rgba(255, 255, 255, .45);

            font-size: 12px;
            font-weight: 900;
        }


        /*
        |--------------------------------------------------------------------------
        | HERO
        |--------------------------------------------------------------------------
        */

        .sv-hero {
            position: relative;

            overflow: hidden;

            border:
                1px solid
                var(--border);

            border-radius: 28px;

            background:
                var(--card);

            box-shadow:
                var(--shadow);
        }


        .sv-hero::before {
            content: "";

            position: absolute;

            width: 430px;
            height: 430px;

            top: -290px;
            right: -100px;

            border-radius: 999px;

            background:
                rgba(6, 182, 212, .12);

            pointer-events: none;
        }


        .sv-hero::after {
            content: "";

            position: absolute;

            width: 360px;
            height: 360px;

            bottom: -290px;
            left: 34%;

            border-radius: 999px;

            background:
                rgba(124, 58, 237, .09);

            pointer-events: none;
        }


        .sv-hero-grid {
            position: relative;

            z-index: 1;

            display: grid;

            grid-template-columns:
                minmax(0, 1.2fr)
                minmax(330px, .8fr);

            gap: 30px;

            padding: 30px;
        }


        .sv-chip-row {
            display: flex;

            flex-wrap: wrap;

            align-items: center;

            gap: 8px;
        }


        .sv-chip {
            display: inline-flex;

            align-items: center;

            gap: 7px;

            min-height: 31px;

            padding:
                0
                11px;

            border-radius: 999px;

            font-size: 10px;
            font-weight: 900;

            letter-spacing: .07em;

            text-transform: uppercase;
        }


        .sv-chip-blue {
            background:
                var(--blue-soft);

            color:
                var(--blue);
        }


        .sv-chip-neutral {
            background:
                var(--card-muted);

            color:
                var(--text-soft);
        }


        .sv-title {
            max-width: 670px;

            margin:
                18px
                0
                0;

            color:
                var(--text);

            font-size:
                clamp(
                    34px,
                    4vw,
                    48px
                );

            font-weight: 900;

            line-height: 1.04;

            letter-spacing: -.05em;
        }


        .sv-description {
            max-width: 680px;

            margin:
                13px
                0
                0;

            color:
                var(--muted);

            font-size: 14px;

            line-height: 1.7;
        }


        /*
        |--------------------------------------------------------------------------
        | LEVEL PROGRESS
        |--------------------------------------------------------------------------
        */

        .sv-level-box {
            margin-top: 27px;

            padding:
                18px
                19px;

            border:
                1px solid
                var(--border);

            border-radius: 18px;

            background:
                var(--card-soft);
        }


        .sv-level-head {
            display: flex;

            align-items: center;
            justify-content: space-between;

            gap: 14px;
        }


        .sv-level-title {
            color:
                var(--text);

            font-size: 12px;
            font-weight: 900;
        }


        .sv-level-xp {
            color:
                var(--muted);

            font-size: 10px;
            font-weight: 800;
        }


        .sv-progress-track {
            height: 10px;

            margin-top: 12px;

            overflow: hidden;

            border-radius: 999px;

            background:
                var(--card-muted);
        }


        .sv-progress-value {
            height: 100%;

            border-radius: inherit;

            background:
                linear-gradient(
                    90deg,
                    #06b6d4,
                    #2563eb,
                    #7c3aed
                );
        }


        .sv-level-bottom {
            display: flex;

            align-items: center;
            justify-content: space-between;

            gap: 12px;

            margin-top: 10px;
        }


        .sv-level-remaining {
            color:
                var(--muted);

            font-size: 10px;
            font-weight: 600;
        }


        .sv-level-percent {
            color:
                var(--blue);

            font-size: 10px;
            font-weight: 900;
        }


        /*
        |--------------------------------------------------------------------------
        | STATS
        |--------------------------------------------------------------------------
        */

        .sv-stat-grid {
            display: grid;

            grid-template-columns:
                repeat(
                    2,
                    minmax(0, 1fr)
                );

            gap: 11px;
        }


        .sv-stat {
            position: relative;

            overflow: hidden;

            min-height: 128px;

            padding: 17px;

            border:
                1px solid
                var(--border);

            border-radius: 19px;

            background:
                var(--card-soft);
        }


        .sv-stat-top {
            display: flex;

            align-items: center;
            justify-content: space-between;

            gap: 10px;
        }


        .sv-stat-label {
            color:
                var(--muted);

            font-size: 9px;
            font-weight: 900;

            letter-spacing: .08em;

            text-transform: uppercase;
        }


        .sv-icon-box {
            display: grid;

            width: 36px;
            height: 36px;

            flex: 0 0 36px;

            place-items: center;

            border-radius: 12px;
        }


        .sv-icon-blue {
            background:
                var(--blue-soft);

            color:
                var(--blue);
        }


        .sv-icon-amber {
            background:
                var(--amber-soft);

            color:
                var(--amber);
        }


        .sv-icon-orange {
            background:
                var(--orange-soft);

            color:
                var(--orange);
        }


        .sv-icon-violet {
            background:
                var(--violet-soft);

            color:
                var(--violet);
        }


        .sv-stat-value {
            margin-top: 16px;

            color:
                var(--text);

            font-size: 27px;
            font-weight: 900;

            line-height: 1;
        }


        .sv-stat-small {
            font-size: 12px;
            font-weight: 700;

            color:
                var(--muted);
        }


        .sv-stat-caption {
            margin-top: 7px;

            color:
                var(--muted);

            font-size: 10px;

            line-height: 1.4;
        }


        /*
        |--------------------------------------------------------------------------
        | SECTION
        |--------------------------------------------------------------------------
        */

        .sv-section {
            margin-top: 20px;

            border:
                1px solid
                var(--border);

            border-radius: 28px;

            background:
                var(--card);

            box-shadow:
                var(--shadow);
        }


        .sv-section-body {
            padding: 25px;
        }


        .sv-section-header {
            display: flex;

            align-items: flex-end;
            justify-content: space-between;

            gap: 20px;
        }


        .sv-section-info {
            min-width: 0;
        }


        .sv-eyebrow {
            display: inline-flex;

            align-items: center;

            gap: 7px;

            font-size: 9px;
            font-weight: 900;

            letter-spacing: .13em;

            text-transform: uppercase;
        }


        .sv-eyebrow-amber {
            color:
                var(--amber);
        }


        .sv-eyebrow-violet {
            color:
                var(--violet);
        }


        .sv-eyebrow-green {
            color:
                var(--green);
        }


        .sv-eyebrow-blue {
            color:
                var(--blue);
        }


        .sv-section-title {
            margin:
                6px
                0
                0;

            color:
                var(--text);

            font-size: 22px;
            font-weight: 900;

            letter-spacing: -.025em;
        }


        .sv-section-description {
            max-width: 700px;

            margin:
                6px
                0
                0;

            color:
                var(--muted);

            font-size: 12px;

            line-height: 1.6;
        }


        .sv-counter {
            flex: 0 0 auto;

            padding:
                8px
                12px;

            border-radius: 999px;

            background:
                var(--card-soft);

            color:
                var(--muted);

            font-size: 10px;
            font-weight: 800;
        }


        /*
        |--------------------------------------------------------------------------
        | BADGES
        |--------------------------------------------------------------------------
        */

        .sv-badge-grid {
            display: grid;

            grid-template-columns:
                repeat(
                    4,
                    minmax(0, 1fr)
                );

            gap: 12px;

            margin-top: 22px;
        }


        .sv-badge {
            position: relative;

            min-height: 190px;

            padding: 17px;

            border:
                1px solid
                var(--border);

            border-radius: 19px;

            background:
                var(--card-soft);
        }


        .sv-badge.is-earned {
            background:
                var(--card);
        }


        .sv-badge.is-locked {
            opacity: .58;
        }


        .sv-badge-mark {
            display: grid;

            width: 54px;
            height: 54px;

            place-items: center;

            border-radius: 17px;

            background:
                var(--amber-soft);

            color:
                var(--amber);
        }


        .sv-badge.is-locked
        .sv-badge-mark {
            background:
                var(--card-muted);

            color:
                var(--muted);
        }


        .sv-badge-status {
            position: absolute;

            top: 13px;
            right: 13px;

            padding:
                5px
                8px;

            border-radius: 999px;

            font-size: 8px;
            font-weight: 900;

            letter-spacing: .07em;

            text-transform: uppercase;
        }


        .sv-badge-status-earned {
            background:
                var(--green-soft);

            color:
                var(--green);
        }


        .sv-badge-status-locked {
            background:
                var(--card-muted);

            color:
                var(--muted);
        }


        .sv-badge-name {
            margin:
                16px
                0
                0;

            color:
                var(--text);

            font-size: 14px;
            font-weight: 900;
        }


        .sv-badge-description {
            margin:
                6px
                0
                0;

            color:
                var(--muted);

            font-size: 11px;

            line-height: 1.55;
        }


        /*
        |--------------------------------------------------------------------------
        | SHOP
        |--------------------------------------------------------------------------
        */

        .sv-balance {
            display: inline-flex;

            align-items: center;

            gap: 7px;

            padding:
                8px
                11px;

            border:
                1px solid
                rgba(245, 158, 11, .25);

            border-radius: 13px;

            background:
                var(--amber-soft);

            color:
                var(--amber);

            font-size: 10px;
            font-weight: 900;

            white-space: nowrap;
        }


        .sv-shop-grid {
            display: grid;

            grid-template-columns:
                repeat(
                    4,
                    minmax(0, 1fr)
                );

            gap: 12px;

            margin-top: 22px;
        }


        .sv-product {
            display: flex;

            min-height: 245px;

            flex-direction: column;

            padding: 17px;

            border:
                1px solid
                var(--border);

            border-radius: 19px;

            background:
                var(--card-soft);
        }


        .sv-product-icon {
            display: grid;

            width: 54px;
            height: 54px;

            place-items: center;

            border-radius: 17px;

            background:
                var(--violet-soft);

            color:
                var(--violet);
        }


        .sv-product-name {
            margin:
                16px
                0
                0;

            color:
                var(--text);

            font-size: 14px;
            font-weight: 900;
        }


        .sv-product-type {
            margin-top: 5px;

            color:
                var(--violet);

            font-size: 8px;
            font-weight: 900;

            letter-spacing: .09em;

            text-transform: uppercase;
        }


        .sv-product-description {
            flex: 1;

            margin:
                11px
                0
                0;

            color:
                var(--muted);

            font-size: 11px;

            line-height: 1.55;
        }


        .sv-product-footer {
            display: flex;

            align-items: center;
            justify-content: space-between;

            gap: 10px;

            margin-top: 16px;

            padding-top: 14px;

            border-top:
                1px solid
                var(--border);
        }


        .sv-price {
            display: inline-flex;

            align-items: center;

            gap: 6px;

            color:
                var(--amber);

            font-size: 12px;
            font-weight: 900;
        }


        .sv-button {
            display: inline-flex;

            min-height: 34px;

            align-items: center;
            justify-content: center;

            border: 0;

            border-radius: 10px;

            padding:
                0
                11px;

            font-family: inherit;

            font-size: 10px;
            font-weight: 900;
        }


        .sv-button-active {
            background:
                var(--blue);

            color: #ffffff;

            cursor: pointer;
        }


        .sv-button-disabled {
            background:
                var(--card-muted);

            color:
                var(--muted);

            cursor: not-allowed;
        }


        .sv-owned {
            display: inline-flex;

            min-height: 34px;

            align-items: center;
            justify-content: center;

            gap: 5px;

            padding:
                0
                10px;

            border-radius: 10px;

            background:
                var(--green-soft);

            color:
                var(--green);

            font-size: 10px;
            font-weight: 900;
        }


        /*
        |--------------------------------------------------------------------------
        | BOTTOM
        |--------------------------------------------------------------------------
        */

        .sv-bottom-grid {
            display: grid;

            grid-template-columns:
                minmax(0, 1.35fr)
                minmax(300px, .65fr);

            gap: 20px;

            margin-top: 20px;
        }


        .sv-bottom-card {
            border:
                1px solid
                var(--border);

            border-radius: 28px;

            background:
                var(--card);

            box-shadow:
                var(--shadow);
        }


        .sv-bottom-body {
            padding: 25px;
        }


        /*
        |--------------------------------------------------------------------------
        | TRANSACTIONS
        |--------------------------------------------------------------------------
        */

        .sv-transactions {
            max-height: 370px;

            margin-top: 18px;

            overflow-y: auto;

            padding-right: 7px;

            scrollbar-width: thin;

            scrollbar-color:
                var(--border)
                transparent;
        }


        .sv-transactions::-webkit-scrollbar {
            width: 7px;
        }


        .sv-transactions::-webkit-scrollbar-track {
            background: transparent;
        }


        .sv-transactions::-webkit-scrollbar-thumb {
            border-radius: 999px;

            background:
                var(--border);
        }


        .sv-transaction {
            display: flex;

            align-items: center;
            justify-content: space-between;

            gap: 15px;

            min-height: 68px;

            padding:
                11px
                0;

            border-bottom:
                1px solid
                var(--border);
        }


        .sv-transaction:last-child {
            border-bottom: 0;
        }


        .sv-transaction-main {
            display: flex;

            align-items: center;

            gap: 12px;

            min-width: 0;
        }


        .sv-transaction-icon {
            display: grid;

            width: 40px;
            height: 40px;

            flex: 0 0 40px;

            place-items: center;

            border-radius: 13px;
        }


        .sv-transaction-icon-xp {
            background:
                var(--blue-soft);

            color:
                var(--blue);
        }


        .sv-transaction-icon-coin {
            background:
                var(--amber-soft);

            color:
                var(--amber);
        }


        .sv-transaction-icon-spend {
            background:
                var(--red-soft);

            color:
                var(--red);
        }


        .sv-transaction-copy {
            min-width: 0;
        }


        .sv-transaction-reason {
            overflow: hidden;

            color:
                var(--text);

            font-size: 12px;
            font-weight: 800;

            line-height: 1.4;

            text-overflow: ellipsis;
            white-space: nowrap;
        }


        .sv-transaction-time {
            margin-top: 4px;

            color:
                var(--muted);

            font-size: 9px;
        }


        .sv-transaction-amount {
            flex: 0 0 auto;

            font-size: 12px;
            font-weight: 900;
        }


        .sv-amount-xp {
            color:
                var(--blue);
        }


        .sv-amount-coin {
            color:
                var(--amber);
        }


        .sv-amount-spend {
            color:
                var(--red);
        }


        /*
        |--------------------------------------------------------------------------
        | SOCIAL CARD
        |--------------------------------------------------------------------------
        */

        .sv-social {
            position: relative;

            min-height: 315px;

            overflow: hidden;
        }


        .sv-social::after {
            content: "";

            position: absolute;

            width: 230px;
            height: 230px;

            right: -110px;
            bottom: -120px;

            border-radius: 999px;

            background:
                rgba(37, 99, 235, .11);

            pointer-events: none;
        }


        .sv-social-body {
            position: relative;

            z-index: 1;

            display: flex;

            min-height: 315px;

            flex-direction: column;

            justify-content: space-between;

            padding: 25px;
        }


        .sv-social-icon {
            display: grid;

            width: 58px;
            height: 58px;

            place-items: center;

            border-radius: 18px;

            background:
                linear-gradient(
                    135deg,
                    #06b6d4,
                    #2563eb
                );

            color: #ffffff;

            box-shadow:
                0 12px 28px
                rgba(37, 99, 235, .20);
        }


        .sv-social-title {
            margin:
                18px
                0
                0;

            color:
                var(--text);

            font-size: 23px;
            font-weight: 900;

            letter-spacing: -.03em;
        }


        .sv-social-description {
            margin:
                9px
                0
                0;

            color:
                var(--muted);

            font-size: 12px;

            line-height: 1.65;
        }


        .sv-social-link {
            display: inline-flex;

            width: fit-content;

            min-height: 42px;

            align-items: center;
            justify-content: center;

            gap: 8px;

            padding:
                0
                15px;

            border-radius: 12px;

            background:
                var(--blue);

            color: #ffffff;

            text-decoration: none;

            font-size: 11px;
            font-weight: 900;
        }


        /*
        |--------------------------------------------------------------------------
        | EMPTY
        |--------------------------------------------------------------------------
        */

        .sv-empty {
            padding:
                30px
                18px;

            border:
                1px dashed
                var(--border);

            border-radius: 16px;

            color:
                var(--muted);

            text-align: center;

            font-size: 11px;
        }


        /*
        |--------------------------------------------------------------------------
        | RESPONSIVE
        |--------------------------------------------------------------------------
        */

        @media (
            max-width: 1050px
        ) {
            .sv-hero-grid {
                grid-template-columns:
                    1fr;
            }


            .sv-badge-grid,
            .sv-shop-grid {
                grid-template-columns:
                    repeat(
                        2,
                        minmax(0, 1fr)
                    );
            }


            .sv-bottom-grid {
                grid-template-columns:
                    1fr;
            }
        }


        @media (
            max-width: 700px
        ) {
            .sv-hero-grid,
            .sv-section-body,
            .sv-bottom-body,
            .sv-social-body {
                padding: 20px;
            }


            .sv-section-header {
                align-items: flex-start;

                flex-direction: column;
            }


            .sv-counter,
            .sv-balance {
                width: fit-content;
            }
        }


        @media (
            max-width: 560px
        ) {
            .sv-title {
                font-size: 32px;
            }


            .sv-stat-grid,
            .sv-badge-grid,
            .sv-shop-grid {
                grid-template-columns:
                    1fr;
            }


            .sv-stat {
                min-height: 110px;
            }


            .sv-product {
                min-height: auto;
            }


            .sv-product-footer {
                align-items: flex-start;

                flex-direction: column;
            }


            .sv-product-footer form,
            .sv-button,
            .sv-owned {
                width: 100%;
            }


            .sv-transaction {
                align-items: flex-start;
            }
        }
    </style>


    <div class="sv-rewards">

        {{-- ========================================================= --}}
        {{-- FLASH --}}
        {{-- ========================================================= --}}

        @if (session('success'))

            <div class="sv-alert sv-alert-success">

                <span class="sv-alert-mark">
                    OK
                </span>

                <span>
                    {{ session('success') }}
                </span>

            </div>

        @endif


        @if (session('error'))

            <div class="sv-alert sv-alert-error">

                <span class="sv-alert-mark">
                    !
                </span>

                <span>
                    {{ session('error') }}
                </span>

            </div>

        @endif


        {{-- ========================================================= --}}
        {{-- HERO --}}
        {{-- ========================================================= --}}

        <section class="sv-hero">

            <div class="sv-hero-grid">

                {{-- ================================================= --}}
                {{-- LEFT --}}
                {{-- ================================================= --}}

                <div>

                    <div class="sv-chip-row">

                        <span class="sv-chip sv-chip-blue">

                            <svg
                                class="sv-svg"
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                            >
                                <path d="M20 12v8H4v-8" />
                                <path d="M2 7h20v5H2z" />
                                <path d="M12 7v13" />
                                <path d="M12 7H7.5A2.5 2.5 0 1 1 10 4.5L12 7Z" />
                                <path d="M12 7h4.5A2.5 2.5 0 1 0 14 4.5L12 7Z" />
                            </svg>

                            SpeakVerse Rewards

                        </span>


                        <span class="sv-chip sv-chip-neutral">

                            Level {{ $currentLevel }}
                            -
                            {{ $levelName }}

                        </span>

                    </div>


                    <h1 class="sv-title">
                        Learn. Earn. Unlock.
                    </h1>


                    <p class="sv-description">
                        Complete learning activities to earn XP and SpeakCoins.
                        Build your learning streak, unlock achievements,
                        redeem cosmetic rewards, and climb the SpeakVerse
                        leaderboard.
                    </p>


                    {{-- ============================================= --}}
                    {{-- LEVEL --}}
                    {{-- ============================================= --}}

                    <div class="sv-level-box">

                        <div class="sv-level-head">

                            <span class="sv-level-title">
                                Level {{ $currentLevel }} Progress
                            </span>


                            <span class="sv-level-xp">
                                {{ number_format($xpIntoLevel) }}
                                /
                                {{ number_format($xpForNextLevel) }}
                                XP
                            </span>

                        </div>


                        <div class="sv-progress-track">

                            <div
                                class="sv-progress-value"
                                style="width: {{ $levelProgress }}%;"
                            ></div>

                        </div>


                        <div class="sv-level-bottom">

                            <span class="sv-level-remaining">

                                @if ($remainingXp > 0)

                                    {{ number_format($remainingXp) }}
                                    XP remaining until Level
                                    {{ $currentLevel + 1 }}.

                                @else

                                    Next level milestone reached.

                                @endif

                            </span>


                            <span class="sv-level-percent">
                                {{ $levelProgress }}%
                            </span>

                        </div>

                    </div>

                </div>


                {{-- ================================================= --}}
                {{-- STATS --}}
                {{-- ================================================= --}}

                <div class="sv-stat-grid">

                    {{-- XP --}}
                    <div class="sv-stat">

                        <div class="sv-stat-top">

                            <span class="sv-stat-label">
                                Total XP
                            </span>


                            <span class="sv-icon-box sv-icon-blue">

                                <svg
                                    class="sv-svg"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path d="M13 2 4.5 13H11l-1 9 8.5-11H12l1-9Z" />
                                </svg>

                            </span>

                        </div>


                        <div class="sv-stat-value">
                            {{ number_format($totalXp) }}
                        </div>


                        <div class="sv-stat-caption">
                            Lifetime experience earned
                        </div>

                    </div>


                    {{-- COINS --}}
                    <div class="sv-stat">

                        <div class="sv-stat-top">

                            <span class="sv-stat-label">
                                SpeakCoins
                            </span>


                            <span class="sv-icon-box sv-icon-amber">

                                <svg
                                    class="sv-svg"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <circle cx="12" cy="12" r="8" />
                                    <circle cx="12" cy="12" r="5" />
                                    <path d="M9.5 10.5h4a1.5 1.5 0 0 1 0 3h-4" />
                                    <path d="M12 8.5v7" />
                                </svg>

                            </span>

                        </div>


                        <div class="sv-stat-value">
                            {{ number_format($coins) }}
                        </div>


                        <div class="sv-stat-caption">
                            Available reward currency
                        </div>

                    </div>


                    {{-- STREAK --}}
                    <div class="sv-stat">

                        <div class="sv-stat-top">

                            <span class="sv-stat-label">
                                Current Streak
                            </span>


                            <span class="sv-icon-box sv-icon-orange">

                                <svg
                                    class="sv-svg"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path d="M12 22c4 0 7-3 7-7 0-3-1.5-5.5-4.5-8.5.2 2.5-1 4-2.5 5-1-2.5-2.5-4.5-5-6.5.2 3-2 5.5-2 9.5 0 4.5 3 7.5 7 7.5Z" />
                                    <path d="M9 18c0-2 1.3-3.2 3-4.5.2 1.5 1 2.2 2 3 .5.4 1 1.2 1 2 0 1.7-1.3 3-3 3s-3-1.3-3-3.5Z" />
                                </svg>

                            </span>

                        </div>


                        <div class="sv-stat-value">

                            {{ number_format($currentStreak) }}

                            <span class="sv-stat-small">
                                days
                            </span>

                        </div>


                        <div class="sv-stat-caption">
                            Best streak:
                            {{ number_format($longestStreak) }}
                            days
                        </div>

                    </div>


                    {{-- BADGES --}}
                    <div class="sv-stat">

                        <div class="sv-stat-top">

                            <span class="sv-stat-label">
                                Achievements
                            </span>


                            <span class="sv-icon-box sv-icon-violet">

                                <svg
                                    class="sv-svg"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <circle cx="12" cy="8" r="5" />
                                    <path d="M8.5 12 7 22l5-3 5 3-1.5-10" />
                                </svg>

                            </span>

                        </div>


                        <div class="sv-stat-value">

                            {{ number_format($earnedBadgeCount) }}

                            <span class="sv-stat-small">
                                /
                                {{ number_format($totalBadgeCount) }}
                            </span>

                        </div>


                        <div class="sv-stat-caption">
                            Achievements unlocked
                        </div>

                    </div>

                </div>

            </div>

        </section>


        {{-- ========================================================= --}}
        {{-- BADGES --}}
        {{-- ========================================================= --}}

        <section class="sv-section">

            <div class="sv-section-body">

                <div class="sv-section-header">

                    <div class="sv-section-info">

                        <div class="sv-eyebrow sv-eyebrow-amber">

                            <svg
                                class="sv-svg"
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                            >
                                <circle cx="12" cy="8" r="5" />
                                <path d="M8.5 12 7 22l5-3 5 3-1.5-10" />
                            </svg>

                            Achievements

                        </div>


                        <h2 class="sv-section-title">
                            Badge Collection
                        </h2>


                        <p class="sv-section-description">
                            Complete activities, maintain learning streaks,
                            earn strong scores, and complete Units to unlock
                            achievement badges.
                        </p>

                    </div>


                    <div class="sv-counter">

                        {{ number_format($earnedBadgeCount) }}

                        of

                        {{ number_format($totalBadgeCount) }}

                        unlocked

                    </div>

                </div>


                <div class="sv-badge-grid">

                    @foreach ($badges as $badge)

                        @php
                            $earned = (bool) (
                                $badge['earned']
                                ?? false
                            );
                        @endphp


                        <article
                            class="sv-badge {{
                                $earned
                                    ? 'is-earned'
                                    : 'is-locked'
                            }}"
                        >

                            <span
                                class="sv-badge-status {{
                                    $earned
                                        ? 'sv-badge-status-earned'
                                        : 'sv-badge-status-locked'
                                }}"
                            >
                                {{ $earned ? 'Unlocked' : 'Locked' }}
                            </span>


                            <div class="sv-badge-mark">

                                @if ($earned)

                                    <svg
                                        class="sv-svg"
                                        viewBox="0 0 24 24"
                                        aria-hidden="true"
                                    >
                                        <circle cx="12" cy="8" r="5" />
                                        <path d="M8.5 12 7 22l5-3 5 3-1.5-10" />
                                        <path d="m10 8 1.3 1.3L14 6.8" />
                                    </svg>

                                @else

                                    <svg
                                        class="sv-svg"
                                        viewBox="0 0 24 24"
                                        aria-hidden="true"
                                    >
                                        <rect x="5" y="10" width="14" height="10" rx="2" />
                                        <path d="M8 10V7a4 4 0 0 1 8 0v3" />
                                    </svg>

                                @endif

                            </div>


                            <h3 class="sv-badge-name">
                                {{
                                    $badge['name']
                                    ??
                                    'Achievement'
                                }}
                            </h3>


                            <p class="sv-badge-description">
                                {{
                                    $badge['description']
                                    ??
                                    'Complete learning activities to unlock this achievement.'
                                }}
                            </p>

                        </article>

                    @endforeach

                </div>

            </div>

        </section>


        {{-- ========================================================= --}}
        {{-- SHOP --}}
        {{-- ========================================================= --}}

        <section class="sv-section">

            <div class="sv-section-body">

                <div class="sv-section-header">

                    <div class="sv-section-info">

                        <div class="sv-eyebrow sv-eyebrow-violet">

                            <svg
                                class="sv-svg"
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                            >
                                <path d="M6 8h12l1 12H5L6 8Z" />
                                <path d="M9 9V6a3 3 0 0 1 6 0v3" />
                            </svg>

                            Reward Store

                        </div>


                        <h2 class="sv-section-title">
                            Spend Your SpeakCoins
                        </h2>


                        <p class="sv-section-description">
                            Redeem cosmetic rewards using the SpeakCoins you
                            earn from learning. Purchases do not change your
                            assessment score or XP ranking.
                        </p>

                    </div>


                    <div class="sv-balance">

                        <svg
                            class="sv-svg"
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                        >
                            <circle cx="12" cy="12" r="8" />
                            <circle cx="12" cy="12" r="5" />
                        </svg>

                        {{ number_format($coins) }}
                        available

                    </div>

                </div>


                <div class="sv-shop-grid">

                    @forelse ($rewardItems as $item)

                        @php
                            $owned =
                                $ownedRewardItemIds
                                    ->contains(
                                        (int) $item->id
                                    );

                            $canBuy =
                                !$owned
                                &&
                                $coins >=
                                (int) $item->price_coins;
                        @endphp


                        <article class="sv-product">

                            <div class="sv-product-icon">

                                <svg
                                    class="sv-svg"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path d="M20 12v8H4v-8" />
                                    <path d="M2 7h20v5H2z" />
                                    <path d="M12 7v13" />
                                    <path d="M12 7H7.5A2.5 2.5 0 1 1 10 4.5L12 7Z" />
                                    <path d="M12 7h4.5A2.5 2.5 0 1 0 14 4.5L12 7Z" />
                                </svg>

                            </div>


                            <h3 class="sv-product-name">
                                {{ $item->name }}
                            </h3>


                            <div class="sv-product-type">
                                {{ $item->type }}
                            </div>


                            <p class="sv-product-description">
                                {{
                                    $item->description
                                    ?:
                                    'Cosmetic SpeakVerse reward.'
                                }}
                            </p>


                            <div class="sv-product-footer">

                                <span class="sv-price">

                                    <svg
                                        class="sv-svg"
                                        viewBox="0 0 24 24"
                                        aria-hidden="true"
                                    >
                                        <circle cx="12" cy="12" r="8" />
                                        <circle cx="12" cy="12" r="5" />
                                    </svg>

                                    {{
                                        number_format(
                                            (int) $item->price_coins
                                        )
                                    }}

                                </span>


                                @if ($owned)

                                    <span class="sv-owned">
                                        Owned
                                    </span>

                                @else

                                    <form
                                        method="POST"
                                        action="{{ route('gamification.purchase', $item) }}"
                                        onsubmit="return confirm('Redeem this reward using SpeakCoins?');"
                                    >
                                        @csrf

                                        <button
                                            type="submit"
                                            @disabled(!$canBuy)
                                            class="sv-button {{
                                                $canBuy
                                                    ? 'sv-button-active'
                                                    : 'sv-button-disabled'
                                            }}"
                                        >
                                            {{
                                                $canBuy
                                                    ? 'Redeem'
                                                    : 'Not enough'
                                            }}
                                        </button>

                                    </form>

                                @endif

                            </div>

                        </article>


                    @empty

                        <div class="sv-empty">
                            Reward Store is currently empty.
                        </div>

                    @endforelse

                </div>

            </div>

        </section>


        {{-- ========================================================= --}}
        {{-- BOTTOM --}}
        {{-- ========================================================= --}}

        <div class="sv-bottom-grid">

            {{-- ===================================================== --}}
            {{-- RECENT REWARDS --}}
            {{-- ===================================================== --}}

            <section class="sv-bottom-card">

                <div class="sv-bottom-body">

                    <div class="sv-section-info">

                        <div class="sv-eyebrow sv-eyebrow-green">

                            <svg
                                class="sv-svg"
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                            >
                                <path d="M13 2 4.5 13H11l-1 9 8.5-11H12l1-9Z" />
                            </svg>

                            Activity

                        </div>


                        <h2 class="sv-section-title">
                            Recent Rewards
                        </h2>


                        <p class="sv-section-description">
                            Your latest XP and SpeakCoin transactions.
                        </p>

                    </div>


                    @if ($recentTransactions->isNotEmpty())

                        <div class="sv-transactions">

                            @foreach ($recentTransactions as $transaction)

                                @php
                                    $isXp =
                                        $transaction->type
                                        ===
                                        'xp';

                                    $isCoinIncome =
                                        $transaction->type
                                        ===
                                        'coin'
                                        &&
                                        $transaction->amount >= 0;

                                    $isSpend =
                                        $transaction->type
                                        ===
                                        'coin'
                                        &&
                                        $transaction->amount < 0;
                                @endphp


                                <div class="sv-transaction">

                                    <div class="sv-transaction-main">

                                        <div
                                            class="sv-transaction-icon {{
                                                $isXp
                                                    ? 'sv-transaction-icon-xp'
                                                    : (
                                                        $isCoinIncome
                                                            ? 'sv-transaction-icon-coin'
                                                            : 'sv-transaction-icon-spend'
                                                    )
                                            }}"
                                        >

                                            @if ($isXp)

                                                <svg
                                                    class="sv-svg"
                                                    viewBox="0 0 24 24"
                                                    aria-hidden="true"
                                                >
                                                    <path d="M13 2 4.5 13H11l-1 9 8.5-11H12l1-9Z" />
                                                </svg>

                                            @elseif ($isCoinIncome)

                                                <svg
                                                    class="sv-svg"
                                                    viewBox="0 0 24 24"
                                                    aria-hidden="true"
                                                >
                                                    <circle cx="12" cy="12" r="8" />
                                                    <circle cx="12" cy="12" r="5" />
                                                </svg>

                                            @else

                                                <svg
                                                    class="sv-svg"
                                                    viewBox="0 0 24 24"
                                                    aria-hidden="true"
                                                >
                                                    <path d="M6 8h12l1 12H5L6 8Z" />
                                                    <path d="M9 9V6a3 3 0 0 1 6 0v3" />
                                                </svg>

                                            @endif

                                        </div>


                                        <div class="sv-transaction-copy">

                                            <div class="sv-transaction-reason">
                                                {{ $transaction->reason }}
                                            </div>


                                            <div class="sv-transaction-time">

                                                {{
                                                    $transaction
                                                        ->created_at
                                                        ?->diffForHumans()
                                                }}

                                            </div>

                                        </div>

                                    </div>


                                    <div
                                        class="sv-transaction-amount {{
                                            $isXp
                                                ? 'sv-amount-xp'
                                                : (
                                                    $isCoinIncome
                                                        ? 'sv-amount-coin'
                                                        : 'sv-amount-spend'
                                                )
                                        }}"
                                    >

                                        {{
                                            $transaction->amount > 0
                                                ? '+'
                                                : ''
                                        }}

                                        {{
                                            number_format(
                                                (int) $transaction->amount
                                            )
                                        }}

                                        {{
                                            $isXp
                                                ? ' XP'
                                                : ' Coins'
                                        }}

                                    </div>

                                </div>

                            @endforeach

                        </div>

                    @else

                        <div
                            class="sv-empty"
                            style="margin-top: 18px;"
                        >
                            No reward activity yet.
                        </div>

                    @endif

                </div>

            </section>


            {{-- ===================================================== --}}
            {{-- LEADERBOARD --}}
            {{-- ===================================================== --}}

            <section class="sv-bottom-card sv-social">

                <div class="sv-social-body">

                    <div>

                        <div class="sv-social-icon">

                            <svg
                                class="sv-svg"
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                                style="
                                    width: 28px;
                                    height: 28px;
                                "
                            >
                                <path d="M8 4h8v4a4 4 0 0 1-8 0V4Z" />
                                <path d="M9 16h6" />
                                <path d="M12 12v4" />
                                <path d="M8 20h8" />
                                <path d="M8 6H4v1a4 4 0 0 0 4 4" />
                                <path d="M16 6h4v1a4 4 0 0 1-4 4" />
                            </svg>

                        </div>


                        <div
                            class="sv-eyebrow sv-eyebrow-blue"
                            style="margin-top: 20px;"
                        >
                            Social Ranking
                        </div>


                        <h2 class="sv-social-title">
                            Climb the Leaderboard
                        </h2>


                        <p class="sv-social-description">
                            Compare your lifetime XP with other SpeakVerse
                            learners and see how high you can climb.
                        </p>

                    </div>


                    <a
                        href="{{ route('gamification.leaderboard') }}"
                        class="sv-social-link"
                    >
                        Open Leaderboard

                        <svg
                            class="sv-svg"
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                        >
                            <path d="M5 12h14" />
                            <path d="m13 6 6 6-6 6" />
                        </svg>

                    </a>

                </div>

            </section>

        </div>

    </div>

</x-app-layout>