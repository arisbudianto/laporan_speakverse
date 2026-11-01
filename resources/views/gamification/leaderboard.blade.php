<x-app-layout>

    @php
        $totalLearners = $leaders->count();

        $userXp = (int) ($user->xp ?? 0);

        $userLevel = max(
            1,
            (int) ($user->level ?? 1)
        );

        $topLeader = $leaders->first();

        $topXp = $topLeader
            ? (int) ($topLeader->xp ?? 0)
            : 0;

        $xpDifference = max(
            0,
            $topXp - $userXp
        );
    @endphp


    <style>
        /*
        |--------------------------------------------------------------------------
        | SPEAKVERSE LEADERBOARD
        |--------------------------------------------------------------------------
        |
        | CSS native.
        | Tidak membutuhkan npm / Tailwind rebuild.
        |
        */

        .sv-leaderboard-page {
            --sv-card: #ffffff;
            --sv-card-soft: #f8fafc;
            --sv-card-muted: #f1f5f9;

            --sv-text: #0f172a;
            --sv-text-soft: #334155;
            --sv-text-muted: #64748b;

            --sv-border: #e2e8f0;

            --sv-primary: #2563eb;
            --sv-primary-soft: #eff6ff;

            --sv-cyan: #0891b2;
            --sv-cyan-soft: #ecfeff;

            --sv-purple: #7c3aed;
            --sv-purple-soft: #f5f3ff;

            --sv-amber: #d97706;
            --sv-amber-soft: #fffbeb;

            --sv-green: #059669;
            --sv-green-soft: #ecfdf5;

            --sv-danger: #dc2626;

            --sv-shadow:
                0 14px 38px
                rgba(15, 23, 42, .07);

            width: 100%;
            max-width: 1180px;

            margin: 0 auto;

            color: var(--sv-text);
        }


        html.dark .sv-leaderboard-page,
        .dark .sv-leaderboard-page {
            --sv-card: #0f172a;
            --sv-card-soft: #111c31;
            --sv-card-muted: #172033;

            --sv-text: #f8fafc;
            --sv-text-soft: #d7e0ec;
            --sv-text-muted: #94a3b8;

            --sv-border: #26364d;

            --sv-primary: #60a5fa;
            --sv-primary-soft: #132640;

            --sv-cyan: #22d3ee;
            --sv-cyan-soft: #0b2c3a;

            --sv-purple: #c084fc;
            --sv-purple-soft: #2a183e;

            --sv-amber: #fbbf24;
            --sv-amber-soft: #33250c;

            --sv-green: #34d399;
            --sv-green-soft: #0b2e29;

            --sv-danger: #f87171;

            --sv-shadow:
                0 18px 44px
                rgba(0, 0, 0, .28);
        }


        .sv-leaderboard-page,
        .sv-leaderboard-page * {
            box-sizing: border-box;
        }


        /*
        |--------------------------------------------------------------------------
        | HERO
        |--------------------------------------------------------------------------
        */

        .sv-leaderboard-hero {
            position: relative;

            overflow: hidden;

            border:
                1px solid
                var(--sv-border);

            border-radius: 28px;

            background:
                var(--sv-card);

            box-shadow:
                var(--sv-shadow);
        }


        .sv-leaderboard-hero::before {
            content: "";

            position: absolute;

            width: 340px;
            height: 340px;

            top: -210px;
            right: -90px;

            border-radius: 999px;

            background:
                rgba(6, 182, 212, .10);

            pointer-events: none;
        }


        .sv-leaderboard-hero::after {
            content: "";

            position: absolute;

            width: 280px;
            height: 280px;

            right: 230px;
            bottom: -200px;

            border-radius: 999px;

            background:
                rgba(124, 58, 237, .08);

            pointer-events: none;
        }


        .sv-leaderboard-hero-inner {
            position: relative;

            z-index: 1;

            padding: 28px;
        }


        .sv-leaderboard-heading-row {
            display: flex;

            align-items: flex-start;
            justify-content: space-between;

            gap: 24px;
        }


        .sv-leaderboard-heading {
            min-width: 0;
        }


        .sv-leaderboard-eyebrow {
            display: inline-flex;

            align-items: center;

            gap: 7px;

            padding:
                7px
                11px;

            border-radius: 999px;

            background:
                var(--sv-primary-soft);

            color:
                var(--sv-primary);

            font-size: 11px;
            font-weight: 900;

            letter-spacing: .11em;

            text-transform: uppercase;
        }


        .sv-leaderboard-title {
            margin:
                14px
                0
                0;

            color:
                var(--sv-text);

            font-size:
                clamp(
                    30px,
                    4vw,
                    42px
                );

            line-height: 1.05;

            font-weight: 900;

            letter-spacing: -.045em;
        }


        .sv-leaderboard-description {
            max-width: 680px;

            margin:
                10px
                0
                0;

            color:
                var(--sv-text-muted);

            font-size: 14px;

            line-height: 1.7;
        }


        /*
        |--------------------------------------------------------------------------
        | REWARDS BUTTON
        |--------------------------------------------------------------------------
        */

        .sv-rewards-button {
            display: inline-flex;

            flex: 0 0 auto;

            align-items: center;
            justify-content: center;

            gap: 9px;

            min-height: 46px;

            padding:
                0
                17px;

            border:
                1px solid
                var(--sv-border);

            border-radius: 14px;

            background:
                var(--sv-card-soft);

            color:
                var(--sv-text-soft);

            text-decoration: none;

            font-size: 13px;
            font-weight: 800;

            transition:
                transform .18s ease,
                border-color .18s ease,
                background .18s ease;
        }


        .sv-rewards-button:hover {
            transform:
                translateY(-1px);

            border-color:
                var(--sv-primary);

            background:
                var(--sv-primary-soft);

            color:
                var(--sv-primary);
        }


        /*
        |--------------------------------------------------------------------------
        | CURRENT USER SUMMARY
        |--------------------------------------------------------------------------
        */

        .sv-current-rank {
            display: grid;

            grid-template-columns:
                minmax(0, 1fr)
                repeat(
                    3,
                    minmax(120px, auto)
                );

            gap: 10px;

            margin-top: 26px;

            padding: 10px;

            border:
                1px solid
                var(--sv-border);

            border-radius: 20px;

            background:
                var(--sv-card-soft);
        }


        .sv-current-rank-main {
            display: flex;

            align-items: center;

            gap: 15px;

            min-width: 0;

            padding:
                13px
                14px;
        }


        .sv-current-rank-icon {
            display: grid;

            width: 52px;
            height: 52px;

            flex: 0 0 52px;

            place-items: center;

            border-radius: 17px;

            background:
                linear-gradient(
                    135deg,
                    #06b6d4,
                    #2563eb
                );

            color: #ffffff;

            font-size: 22px;

            box-shadow:
                0 10px 26px
                rgba(37, 99, 235, .20);
        }


        .sv-current-rank-label {
            color:
                var(--sv-text-muted);

            font-size: 10px;
            font-weight: 800;

            letter-spacing: .07em;

            text-transform: uppercase;
        }


        .sv-current-rank-number {
            margin-top: 4px;

            color:
                var(--sv-text);

            font-size: 27px;
            font-weight: 900;

            line-height: 1;
        }


        .sv-current-stat {
            display: flex;

            flex-direction: column;

            align-items: flex-end;
            justify-content: center;

            min-width: 0;

            padding:
                10px
                14px;

            border-left:
                1px solid
                var(--sv-border);
        }


        .sv-current-stat-label {
            color:
                var(--sv-text-muted);

            font-size: 9px;
            font-weight: 800;

            letter-spacing: .08em;

            text-transform: uppercase;
        }


        .sv-current-stat-value {
            margin-top: 5px;

            color:
                var(--sv-text);

            font-size: 15px;
            font-weight: 900;
        }


        .sv-current-stat-value.is-blue {
            color:
                var(--sv-primary);
        }


        .sv-current-stat-value.is-amber {
            color:
                var(--sv-amber);
        }


        /*
        |--------------------------------------------------------------------------
        | RANKING CARD
        |--------------------------------------------------------------------------
        */

        .sv-ranking-card {
            margin-top: 18px;

            overflow: hidden;

            border:
                1px solid
                var(--sv-border);

            border-radius: 28px;

            background:
                var(--sv-card);

            box-shadow:
                var(--sv-shadow);
        }


        /*
        |--------------------------------------------------------------------------
        | RANKING HEADER
        |--------------------------------------------------------------------------
        */

        .sv-ranking-card-header {
            display: flex;

            align-items: center;
            justify-content: space-between;

            gap: 20px;

            padding:
                20px
                24px;

            border-bottom:
                1px solid
                var(--sv-border);
        }


        .sv-ranking-title-area {
            min-width: 0;
        }


        .sv-ranking-card-title {
            margin: 0;

            color:
                var(--sv-text);

            font-size: 18px;
            font-weight: 900;

            letter-spacing: -.02em;
        }


        .sv-ranking-card-subtitle {
            margin:
                4px
                0
                0;

            color:
                var(--sv-text-muted);

            font-size: 12px;

            line-height: 1.5;
        }


        .sv-ranking-header-actions {
            display: flex;

            flex: 0 0 auto;

            align-items: center;

            gap: 8px;
        }


        .sv-ranking-count {
            padding:
                8px
                11px;

            border-radius: 999px;

            background:
                var(--sv-card-soft);

            color:
                var(--sv-text-muted);

            font-size: 10px;
            font-weight: 800;

            white-space: nowrap;
        }


        .sv-my-position-button {
            display: inline-flex;

            align-items: center;
            justify-content: center;

            gap: 6px;

            min-height: 35px;

            padding:
                0
                11px;

            border:
                1px solid
                var(--sv-border);

            border-radius: 11px;

            background:
                var(--sv-card-soft);

            color:
                var(--sv-text-soft);

            font-family:
                inherit;

            font-size: 10px;
            font-weight: 800;

            cursor: pointer;

            transition:
                background .18s ease,
                color .18s ease,
                border-color .18s ease;
        }


        .sv-my-position-button:hover {
            border-color:
                var(--sv-primary);

            background:
                var(--sv-primary-soft);

            color:
                var(--sv-primary);
        }


        /*
        |--------------------------------------------------------------------------
        | TOOLBAR
        |--------------------------------------------------------------------------
        */

        .sv-ranking-toolbar {
            display: flex;

            align-items: center;
            justify-content: space-between;

            gap: 12px;

            padding:
                12px
                22px;

            border-bottom:
                1px solid
                var(--sv-border);

            background:
                var(--sv-card-soft);
        }


        .sv-search-wrap {
            position: relative;

            width: min(
                100%,
                330px
            );
        }


        .sv-search-icon {
            position: absolute;

            top: 50%;
            left: 12px;

            transform:
                translateY(-50%);

            color:
                var(--sv-text-muted);

            font-size: 13px;

            pointer-events: none;
        }


        .sv-search-input {
            width: 100%;

            height: 38px;

            border:
                1px solid
                var(--sv-border);

            border-radius: 12px;

            outline: 0;

            background:
                var(--sv-card);

            color:
                var(--sv-text);

            padding:
                0
                13px
                0
                35px;

            font-family: inherit;

            font-size: 12px;
            font-weight: 600;

            transition:
                border-color .18s ease,
                box-shadow .18s ease;
        }


        .sv-search-input::placeholder {
            color:
                var(--sv-text-muted);
        }


        .sv-search-input:focus {
            border-color:
                var(--sv-primary);

            box-shadow:
                0 0 0 3px
                rgba(37, 99, 235, .10);
        }


        html.dark
        .sv-search-input:focus {
            box-shadow:
                0 0 0 3px
                rgba(96, 165, 250, .10);
        }


        .sv-ranking-scroll-hint {
            display: flex;

            align-items: center;

            gap: 6px;

            color:
                var(--sv-text-muted);

            font-size: 10px;
            font-weight: 700;

            white-space: nowrap;
        }


        /*
        |--------------------------------------------------------------------------
        | INTERNAL SCROLL CONTAINER
        |--------------------------------------------------------------------------
        |
        | Ini bagian utama supaya leaderboard tidak membuat
        | seluruh halaman menjadi panjang.
        |
        */

        .sv-ranking-scroll {
            position: relative;

            max-height: 545px;

            overflow-y: auto;
            overflow-x: hidden;

            overscroll-behavior: contain;

            scrollbar-width: thin;

            scrollbar-color:
                var(--sv-border)
                transparent;
        }


        .sv-ranking-scroll::-webkit-scrollbar {
            width: 8px;
        }


        .sv-ranking-scroll::-webkit-scrollbar-track {
            background:
                transparent;
        }


        .sv-ranking-scroll::-webkit-scrollbar-thumb {
            border:
                2px solid
                transparent;

            border-radius: 999px;

            background:
                var(--sv-border);

            background-clip:
                padding-box;
        }


        .sv-ranking-scroll::-webkit-scrollbar-thumb:hover {
            background:
                var(--sv-text-muted);

            background-clip:
                padding-box;
        }


        /*
        |--------------------------------------------------------------------------
        | TABLE HEADER
        |--------------------------------------------------------------------------
        */

        .sv-ranking-table-head {
            position: sticky;

            top: 0;

            z-index: 10;

            display: grid;

            grid-template-columns:
                72px
                minmax(0, 1fr)
                120px
                150px;

            align-items: center;

            gap: 14px;

            min-height: 48px;

            padding:
                0
                22px;

            border-bottom:
                1px solid
                var(--sv-border);

            background:
                var(--sv-card-soft);

            color:
                var(--sv-text-muted);

            font-size: 9px;
            font-weight: 900;

            letter-spacing: .09em;

            text-transform: uppercase;

            box-shadow:
                0 4px 12px
                rgba(15, 23, 42, .03);
        }


        html.dark
        .sv-ranking-table-head {
            box-shadow:
                0 5px 14px
                rgba(0, 0, 0, .14);
        }


        .sv-ranking-table-head > :nth-child(3),
        .sv-ranking-table-head > :nth-child(4) {
            text-align: right;
        }


        /*
        |--------------------------------------------------------------------------
        | ROW
        |--------------------------------------------------------------------------
        */

        .sv-ranking-row {
            position: relative;

            display: grid;

            grid-template-columns:
                72px
                minmax(0, 1fr)
                120px
                150px;

            align-items: center;

            gap: 14px;

            min-height: 82px;

            padding:
                13px
                22px;

            border-bottom:
                1px solid
                var(--sv-border);

            background:
                var(--sv-card);

            transition:
                background .18s ease;
        }


        .sv-ranking-row:last-child {
            border-bottom: 0;
        }


        .sv-ranking-row:hover {
            background:
                var(--sv-card-soft);
        }


        .sv-ranking-row.is-current-user {
            background:
                var(--sv-primary-soft);
        }


        .sv-ranking-row.is-current-user::before {
            content: "";

            position: absolute;

            top: 11px;
            bottom: 11px;
            left: 0;

            width: 4px;

            border-radius:
                0
                8px
                8px
                0;

            background:
                var(--sv-primary);
        }


        .sv-ranking-row[hidden] {
            display: none !important;
        }


        /*
        |--------------------------------------------------------------------------
        | RANK
        |--------------------------------------------------------------------------
        */

        .sv-rank-cell {
            display: flex;

            align-items: center;
        }


        .sv-rank-badge {
            display: inline-grid;

            min-width: 38px;
            height: 38px;

            place-items: center;

            border-radius: 13px;

            background:
                var(--sv-card-muted);

            color:
                var(--sv-text-soft);

            font-size: 13px;
            font-weight: 900;
        }


        .sv-rank-badge.rank-1 {
            background:
                var(--sv-amber-soft);

            color:
                var(--sv-amber);

            font-size: 20px;
        }


        .sv-rank-badge.rank-2 {
            background:
                var(--sv-card-muted);

            color:
                var(--sv-text-soft);

            font-size: 20px;
        }


        .sv-rank-badge.rank-3 {
            background:
                #fff7ed;

            color:
                #c2410c;

            font-size: 20px;
        }


        html.dark
        .sv-rank-badge.rank-3,
        .dark
        .sv-rank-badge.rank-3 {
            background:
                rgba(194, 65, 12, .13);

            color:
                #fb923c;
        }


        /*
        |--------------------------------------------------------------------------
        | LEARNER
        |--------------------------------------------------------------------------
        */

        .sv-learner-cell {
            display: flex;

            align-items: center;

            gap: 13px;

            min-width: 0;
        }


        .sv-learner-avatar {
            display: grid;

            width: 45px;
            height: 45px;

            flex: 0 0 45px;

            place-items: center;

            border-radius: 15px;

            background:
                linear-gradient(
                    135deg,
                    #06b6d4,
                    #2563eb
                );

            color: #ffffff;

            font-size: 15px;
            font-weight: 900;

            box-shadow:
                0 8px 20px
                rgba(37, 99, 235, .16);
        }


        .sv-learner-info {
            min-width: 0;
        }


        .sv-learner-name-line {
            display: flex;

            align-items: center;

            gap: 7px;

            min-width: 0;
        }


        .sv-learner-name {
            overflow: hidden;

            color:
                var(--sv-text);

            font-size: 14px;
            font-weight: 900;

            line-height: 1.3;

            text-overflow: ellipsis;

            white-space: nowrap;
        }


        .sv-you-badge {
            flex: 0 0 auto;

            padding:
                4px
                7px;

            border-radius: 999px;

            background:
                var(--sv-primary);

            color: #ffffff;

            font-size: 8px;
            font-weight: 900;

            letter-spacing: .08em;

            text-transform: uppercase;
        }


        .sv-learner-meta {
            margin-top: 4px;

            color:
                var(--sv-text-muted);

            font-size: 11px;
            font-weight: 600;
        }


        /*
        |--------------------------------------------------------------------------
        | LEVEL
        |--------------------------------------------------------------------------
        */

        .sv-level-cell {
            text-align: right;
        }


        .sv-level-pill {
            display: inline-flex;

            align-items: center;

            gap: 6px;

            padding:
                6px
                9px;

            border-radius: 999px;

            background:
                var(--sv-purple-soft);

            color:
                var(--sv-purple);

            font-size: 11px;
            font-weight: 800;
        }


        /*
        |--------------------------------------------------------------------------
        | XP
        |--------------------------------------------------------------------------
        */

        .sv-xp-cell {
            text-align: right;
        }


        .sv-xp-value {
            color:
                var(--sv-primary);

            font-size: 15px;
            font-weight: 900;
        }


        .sv-xp-label {
            margin-top: 3px;

            color:
                var(--sv-text-muted);

            font-size: 10px;
            font-weight: 700;
        }


        /*
        |--------------------------------------------------------------------------
        | SEARCH EMPTY
        |--------------------------------------------------------------------------
        */

        .sv-search-empty {
            display: none;

            padding:
                46px
                24px;

            color:
                var(--sv-text-muted);

            text-align: center;
        }


        .sv-search-empty.is-visible {
            display: block;
        }


        .sv-search-empty-icon {
            display: grid;

            width: 52px;
            height: 52px;

            margin:
                0 auto;

            place-items: center;

            border-radius: 17px;

            background:
                var(--sv-card-soft);

            font-size: 23px;
        }


        .sv-search-empty-title {
            margin-top: 12px;

            color:
                var(--sv-text);

            font-size: 14px;
            font-weight: 900;
        }


        .sv-search-empty-text {
            margin-top: 5px;

            font-size: 11px;
        }


        /*
        |--------------------------------------------------------------------------
        | EMPTY DATABASE
        |--------------------------------------------------------------------------
        */

        .sv-ranking-empty {
            padding:
                52px
                24px;

            text-align: center;
        }


        .sv-ranking-empty-icon {
            display: grid;

            width: 58px;
            height: 58px;

            margin:
                0 auto;

            place-items: center;

            border-radius: 18px;

            background:
                var(--sv-card-soft);

            font-size: 27px;
        }


        .sv-ranking-empty-title {
            margin:
                15px
                0
                0;

            color:
                var(--sv-text);

            font-size: 16px;
            font-weight: 900;
        }


        .sv-ranking-empty-text {
            margin:
                6px
                0
                0;

            color:
                var(--sv-text-muted);

            font-size: 13px;
        }


        /*
        |--------------------------------------------------------------------------
        | CARD FOOTER
        |--------------------------------------------------------------------------
        */

        .sv-ranking-card-footer {
            display: flex;

            align-items: center;
            justify-content: space-between;

            gap: 12px;

            min-height: 46px;

            padding:
                0
                22px;

            border-top:
                1px solid
                var(--sv-border);

            background:
                var(--sv-card-soft);

            color:
                var(--sv-text-muted);

            font-size: 10px;
            font-weight: 600;
        }


        .sv-visible-count strong {
            color:
                var(--sv-text);

            font-weight: 900;
        }


        /*
        |--------------------------------------------------------------------------
        | PAGE FOOTER
        |--------------------------------------------------------------------------
        */

        .sv-ranking-footer {
            display: flex;

            align-items: center;
            justify-content: center;

            gap: 7px;

            margin-top: 17px;

            color:
                var(--sv-text-muted);

            font-size: 11px;

            text-align: center;
        }


        /*
        |--------------------------------------------------------------------------
        | RESPONSIVE
        |--------------------------------------------------------------------------
        */

        @media (
            max-width:
            900px
        ) {
            .sv-current-rank {
                grid-template-columns:
                    repeat(
                        2,
                        minmax(
                            0,
                            1fr
                        )
                    );
            }


            .sv-current-rank-main {
                grid-column:
                    1
                    /
                    -1;

                border-bottom:
                    1px solid
                    var(--sv-border);
            }


            .sv-current-stat {
                align-items: flex-start;

                border-left: 0;
            }


            .sv-current-stat:nth-of-type(3) {
                border-left:
                    1px solid
                    var(--sv-border);
            }


            .sv-current-stat:last-child {
                display: none;
            }


            .sv-ranking-table-head,
            .sv-ranking-row {
                grid-template-columns:
                    62px
                    minmax(
                        0,
                        1fr
                    )
                    120px;
            }


            .sv-ranking-table-head > :nth-child(3) {
                display: none;
            }


            .sv-level-cell {
                display: none;
            }
        }


        @media (
            max-width:
            700px
        ) {
            .sv-leaderboard-hero-inner {
                padding:
                    22px
                    18px;
            }


            .sv-leaderboard-heading-row {
                flex-direction: column;
            }


            .sv-rewards-button {
                width: 100%;
            }


            .sv-ranking-card-header {
                align-items: flex-start;

                padding:
                    18px;
            }


            .sv-ranking-header-actions {
                flex-direction: column;

                align-items: flex-end;
            }


            .sv-ranking-toolbar {
                align-items: flex-start;

                flex-direction: column;
            }


            .sv-search-wrap {
                width: 100%;
            }


            .sv-ranking-scroll-hint {
                display: none;
            }


            .sv-ranking-table-head {
                display: none;
            }


            .sv-ranking-scroll {
                max-height: 510px;
            }


            .sv-ranking-row {
                grid-template-columns:
                    48px
                    minmax(
                        0,
                        1fr
                    )
                    auto;

                gap: 10px;

                min-height: 76px;

                padding:
                    12px
                    14px;
            }


            .sv-rank-badge {
                min-width: 36px;
                height: 36px;
            }


            .sv-learner-avatar {
                width: 40px;
                height: 40px;

                flex-basis: 40px;

                border-radius: 13px;
            }


            .sv-xp-value {
                font-size: 13px;
            }


            .sv-xp-label {
                display: none;
            }


            .sv-ranking-card-footer {
                padding:
                    0
                    15px;
            }
        }


        @media (
            max-width:
            460px
        ) {
            .sv-leaderboard-title {
                font-size: 28px;
            }


            .sv-current-rank {
                display: block;
            }


            .sv-current-rank-main {
                border-bottom:
                    1px solid
                    var(--sv-border);
            }


            .sv-current-stat {
                display: inline-flex;

                width:
                    calc(
                        50%
                        -
                        3px
                    );

                padding:
                    13px
                    12px;

                vertical-align: top;
            }


            .sv-current-stat:nth-of-type(3) {
                border-left:
                    1px solid
                    var(--sv-border);
            }


            .sv-current-stat:last-child {
                display: none;
            }


            .sv-ranking-count {
                display: none;
            }


            .sv-ranking-row {
                grid-template-columns:
                    42px
                    minmax(
                        0,
                        1fr
                    )
                    auto;
            }


            .sv-learner-avatar {
                display: none;
            }


            .sv-ranking-card-footer {
                justify-content: center;
            }


            .sv-ranking-card-footer > :last-child {
                display: none;
            }
        }
    </style>


    <div class="sv-leaderboard-page">

        {{-- ========================================================= --}}
        {{-- HERO --}}
        {{-- ========================================================= --}}

        <section class="sv-leaderboard-hero">

            <div class="sv-leaderboard-hero-inner">

                <div class="sv-leaderboard-heading-row">

                    <div class="sv-leaderboard-heading">

                        <div class="sv-leaderboard-eyebrow">

                            <span aria-hidden="true">
                                🏆
                            </span>

                            Social Leaderboard

                        </div>


                        <h1 class="sv-leaderboard-title">
                            SpeakVerse XP Ranking
                        </h1>


                        <p class="sv-leaderboard-description">
                            Bandingkan XP dengan siswa di kelas atau sekolah yang sama.
                        </p>

                    </div>


                    <a
                        href="{{ route('gamification.index') }}"
                        class="sv-rewards-button"
                    >

                        <span aria-hidden="true">
                            🎁
                        </span>

                        <span>
                            Rewards
                        </span>

                    </a>

                </div>


                {{-- ================================================= --}}
                {{-- CURRENT USER --}}
                {{-- ================================================= --}}

                <div class="sv-current-rank">

                    <div class="sv-current-rank-main">

                        <div class="sv-current-rank-icon">
                            🏅
                        </div>


                        <div>

                            <div class="sv-current-rank-label">
                                Your Current Rank
                            </div>

                            <div class="sv-current-rank-number">
                                #{{ number_format($currentRank) }}
                            </div>

                        </div>

                    </div>


                    <div class="sv-current-stat">

                        <div class="sv-current-stat-label">
                            Total XP
                        </div>

                        <div
                            class="sv-current-stat-value is-blue"
                        >
                            {{ number_format($userXp) }}
                            XP
                        </div>

                    </div>


                    <div class="sv-current-stat">

                        <div class="sv-current-stat-label">
                            Current Level
                        </div>

                        <div
                            class="sv-current-stat-value is-amber"
                        >
                            Level {{ $userLevel }}
                        </div>

                    </div>


                    <div class="sv-current-stat">

                        <div class="sv-current-stat-label">
                            Learners
                        </div>

                        <div class="sv-current-stat-value">
                            {{ number_format($totalLearners) }}
                        </div>

                    </div>

                </div>

            </div>

        </section>


        {{-- ========================================================= --}}
        {{-- GLOBAL RANKING --}}
        {{-- ========================================================= --}}

        <section class="sv-ranking-card">

            {{-- ===================================================== --}}
            {{-- HEADER --}}
            {{-- ===================================================== --}}

            <div class="sv-ranking-card-header">

                <div class="sv-ranking-title-area">

                    <h2 class="sv-ranking-card-title">
                        {{ ($scope ?? 'class') === 'class' ? 'Peringkat Kelas' : 'Peringkat Sekolah' }}
                    </h2>

                    <p class="sv-ranking-card-subtitle">
                        Diurutkan dari XP tertinggi.
                        <a href="{{ route('gamification.leaderboard', ['scope' => 'class']) }}">Kelas</a>
                        ·
                        <a href="{{ route('gamification.leaderboard', ['scope' => 'school']) }}">Sekolah</a>
                    </p>

                </div>


                <div class="sv-ranking-header-actions">

                    <button
                        type="button"
                        id="sv-my-position-button"
                        class="sv-my-position-button"
                    >
                        <span aria-hidden="true">
                            📍
                        </span>

                        My Position
                    </button>


                    <div class="sv-ranking-count">

                        {{ number_format($totalLearners) }}

                        {{ $totalLearners === 1 ? 'learner' : 'learners' }}

                    </div>

                </div>

            </div>


            {{-- ===================================================== --}}
            {{-- TOOLBAR --}}
            {{-- ===================================================== --}}

            @if ($leaders->isNotEmpty())

                <div class="sv-ranking-toolbar">

                    <div class="sv-search-wrap">

                        <span class="sv-search-icon">
                            🔍
                        </span>

                        <input
                            id="sv-leader-search"
                            type="search"
                            class="sv-search-input"
                            placeholder="Search learner..."
                            autocomplete="off"
                        >

                    </div>


                    <div class="sv-ranking-scroll-hint">

                        <span>
                            ↕
                        </span>

                        Scroll to browse all learners

                    </div>

                </div>

            @endif


            {{-- ===================================================== --}}
            {{-- SCROLL AREA --}}
            {{-- ===================================================== --}}

            @if ($leaders->isNotEmpty())

                <div
                    id="sv-ranking-scroll"
                    class="sv-ranking-scroll"
                >

                    {{-- ================================================= --}}
                    {{-- TABLE HEADER --}}
                    {{-- ================================================= --}}

                    <div class="sv-ranking-table-head">

                        <div>
                            Rank
                        </div>

                        <div>
                            Learner
                        </div>

                        <div>
                            Level
                        </div>

                        <div>
                            Total XP
                        </div>

                    </div>


                    {{-- ================================================= --}}
                    {{-- ROWS --}}
                    {{-- ================================================= --}}

                    <div id="sv-ranking-rows">

                        @foreach ($leaders as $index => $leader)

                            @php
                                $rank = $index + 1;

                                $isMe =
                                    (int) $leader->id
                                    ===
                                    (int) $user->id;

                                $initial =
                                    strtoupper(
                                        substr(
                                            trim(
                                                (string) $leader->name
                                            ),
                                            0,
                                            1
                                        )
                                    );

                                $rankIcon = match ($rank) {
                                    1 => '🥇',
                                    2 => '🥈',
                                    3 => '🥉',
                                    default => null,
                                };

                                $rankClass = match ($rank) {
                                    1 => 'rank-1',
                                    2 => 'rank-2',
                                    3 => 'rank-3',
                                    default => '',
                                };
                            @endphp


                            <div
                                @if ($isMe)
                                    id="sv-current-user-row"
                                @endif

                                class="sv-ranking-row {{ $isMe ? 'is-current-user' : '' }}"

                                data-leader-row

                                data-leader-name="{{ strtolower($leader->name) }}"
                            >

                                {{-- ===================================== --}}
                                {{-- RANK --}}
                                {{-- ===================================== --}}

                                <div class="sv-rank-cell">

                                    <div
                                        class="sv-rank-badge {{ $rankClass }}"
                                    >

                                        @if ($rankIcon)

                                            {{ $rankIcon }}

                                        @else

                                            #{{ $rank }}

                                        @endif

                                    </div>

                                </div>


                                {{-- ===================================== --}}
                                {{-- LEARNER --}}
                                {{-- ===================================== --}}

                                <div class="sv-learner-cell">

                                    <div class="sv-learner-avatar">

                                        {{ $initial ?: '?' }}

                                    </div>


                                    <div class="sv-learner-info">

                                        <div class="sv-learner-name-line">

                                            <div class="sv-learner-name">

                                                {{ $leader->name }}

                                            </div>


                                            @if ($isMe)

                                                <span class="sv-you-badge">
                                                    You
                                                </span>

                                            @endif

                                        </div>


                                        <div class="sv-learner-meta">

                                            @if ($rank === 1)

                                                Top learner

                                            @elseif ($isMe)

                                                Your position

                                            @else

                                                SpeakVerse learner

                                            @endif

                                        </div>

                                    </div>

                                </div>


                                {{-- ===================================== --}}
                                {{-- LEVEL --}}
                                {{-- ===================================== --}}

                                <div class="sv-level-cell">

                                    <span class="sv-level-pill">

                                        <span aria-hidden="true">
                                            ✦
                                        </span>

                                        Level
                                        {{
                                            max(
                                                1,
                                                (int) ($leader->level ?? 1)
                                            )
                                        }}

                                    </span>

                                </div>


                                {{-- ===================================== --}}
                                {{-- XP --}}
                                {{-- ===================================== --}}

                                <div class="sv-xp-cell">

                                    <div class="sv-xp-value">

                                        {{
                                            number_format(
                                                (int) ($leader->xp ?? 0)
                                            )
                                        }}

                                        XP

                                    </div>


                                    <div class="sv-xp-label">

                                        @if ($rank === 1)

                                            Leader

                                        @elseif (
                                            (int) ($leader->xp ?? 0)
                                            ===
                                            $topXp
                                        )

                                            Joint leader

                                        @else

                                            {{
                                                number_format(
                                                    max(
                                                        0,
                                                        $topXp
                                                        -
                                                        (int) ($leader->xp ?? 0)
                                                    )
                                                )
                                            }}

                                            XP behind #1

                                        @endif

                                    </div>

                                </div>

                            </div>

                        @endforeach

                    </div>


                    {{-- ================================================= --}}
                    {{-- SEARCH EMPTY --}}
                    {{-- ================================================= --}}

                    <div
                        id="sv-search-empty"
                        class="sv-search-empty"
                    >

                        <div class="sv-search-empty-icon">
                            🔎
                        </div>

                        <div class="sv-search-empty-title">
                            Learner not found
                        </div>

                        <div class="sv-search-empty-text">
                            Try searching with another name.
                        </div>

                    </div>

                </div>


                {{-- ================================================= --}}
                {{-- FOOTER --}}
                {{-- ================================================= --}}

                <div class="sv-ranking-card-footer">

                    <div class="sv-visible-count">

                        Showing

                        <strong id="sv-visible-count">
                            {{ number_format($totalLearners) }}
                        </strong>

                        of

                        <strong>
                            {{ number_format($totalLearners) }}
                        </strong>

                        learners

                    </div>


                    <div>
                        Rankings are based on total XP.
                    </div>

                </div>

            @else

                {{-- ================================================= --}}
                {{-- DATABASE EMPTY --}}
                {{-- ================================================= --}}

                <div class="sv-ranking-empty">

                    <div class="sv-ranking-empty-icon">
                        🏆
                    </div>


                    <h3 class="sv-ranking-empty-title">
                        No ranking data yet
                    </h3>


                    <p class="sv-ranking-empty-text">
                        Complete learning activities to start appearing
                        on the SpeakVerse leaderboard.
                    </p>

                </div>

            @endif

        </section>


        {{-- ========================================================= --}}
        {{-- PAGE FOOTER --}}
        {{-- ========================================================= --}}

        <div class="sv-ranking-footer">

            <span aria-hidden="true">
                ⚡
            </span>


            @if (
                $currentRank > 1
                &&
                $xpDifference > 0
            )

                <span>
                    You need

                    <strong>
                        {{ number_format($xpDifference) }}
                        XP
                    </strong>

                    to match the current #1 learner.
                </span>

            @elseif ($currentRank === 1)

                <span>
                    You're currently leading the SpeakVerse leaderboard.
                </span>

            @else

                <span>
                    Keep completing missions to climb the ranking.
                </span>

            @endif

        </div>

    </div>


    {{-- ============================================================= --}}
    {{-- LEADERBOARD INTERACTION --}}
    {{-- ============================================================= --}}
    {{-- Tidak membutuhkan npm build. --}}

    <script>
        (() => {
            'use strict';

            const searchInput =
                document.getElementById(
                    'sv-leader-search'
                );

            const rankingRows =
                Array.from(
                    document.querySelectorAll(
                        '[data-leader-row]'
                    )
                );

            const visibleCount =
                document.getElementById(
                    'sv-visible-count'
                );

            const emptyState =
                document.getElementById(
                    'sv-search-empty'
                );

            const myPositionButton =
                document.getElementById(
                    'sv-my-position-button'
                );

            const currentUserRow =
                document.getElementById(
                    'sv-current-user-row'
                );


            /*
            |--------------------------------------------------------------------------
            | SEARCH
            |--------------------------------------------------------------------------
            */

            const filterLearners =
                () => {
                    if (!searchInput) {
                        return;
                    }

                    const keyword =
                        searchInput
                            .value
                            .trim()
                            .toLowerCase();

                    let matched =
                        0;


                    rankingRows.forEach(
                        (row) => {
                            const learnerName =
                                String(
                                    row.dataset.leaderName
                                    ??
                                    ''
                                );

                            const visible =
                                keyword === ''
                                ||
                                learnerName.includes(
                                    keyword
                                );

                            row.hidden =
                                !visible;

                            if (visible) {
                                matched += 1;
                            }
                        }
                    );


                    if (visibleCount) {
                        visibleCount.textContent =
                            matched.toLocaleString();
                    }


                    if (emptyState) {
                        emptyState.classList.toggle(
                            'is-visible',
                            matched === 0
                        );
                    }
                };


            searchInput
                ?.addEventListener(
                    'input',
                    filterLearners
                );


            /*
            |--------------------------------------------------------------------------
            | MY POSITION
            |--------------------------------------------------------------------------
            */

            myPositionButton
                ?.addEventListener(
                    'click',
                    () => {
                        if (!currentUserRow) {
                            return;
                        }


                        /*
                         * Bersihkan pencarian agar row user
                         * pasti terlihat.
                         */

                        if (searchInput) {
                            searchInput.value =
                                '';

                            filterLearners();
                        }


                        currentUserRow.scrollIntoView({
                            behavior:
                                'smooth',

                            block:
                                'center',
                        });


                        /*
                         * Flash kecil sebagai feedback visual.
                         */

                        currentUserRow.animate(
                            [
                                {
                                    transform:
                                        'scale(1)',
                                },

                                {
                                    transform:
                                        'scale(1.008)',
                                },

                                {
                                    transform:
                                        'scale(1)',
                                },
                            ],
                            {
                                duration:
                                    550,

                                easing:
                                    'ease-out',
                            }
                        );
                    }
                );
        })();
    </script>

</x-app-layout>