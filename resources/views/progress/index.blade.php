<x-app-layout>
    {{-- SPEAKVERSE PROGRESS UI VERSION: 2026-08-09-SUBSKILL-FINAL --}}

    <style>
        .progress-page {
            --card: #ffffff;
            --soft: #f8fafc;
            --muted: #eef2f7;

            --text: #0f172a;
            --text-soft: #334155;
            --text-muted: #64748b;

            --border: #dbe4ef;

            --cyan: #0891b2;
            --cyan-soft: #ecfeff;

            --blue: #2563eb;
            --blue-soft: #eff6ff;

            --purple: #7c3aed;
            --purple-soft: #f5f3ff;

            --orange: #d97706;
            --orange-soft: #fffbeb;

            --green: #059669;
            --green-soft: #ecfdf5;

            --red: #dc2626;
            --red-soft: #fef2f2;

            --shadow:
                0 12px 32px rgba(15, 23, 42, .07);

            color: var(--text);
        }

        html.dark .progress-page,
        body.dark .progress-page,
        .dark .progress-page,
        html[data-theme="dark"] .progress-page,
        body[data-theme="dark"] .progress-page {
            --card: #0f172a;
            --soft: #111c31;
            --muted: #172033;

            --text: #f8fafc;
            --text-soft: #d7e0ec;
            --text-muted: #9fb0c5;

            --border: #26364d;

            --cyan: #22d3ee;
            --cyan-soft: #0b2c3a;

            --blue: #60a5fa;
            --blue-soft: #132640;

            --purple: #c084fc;
            --purple-soft: #2a183e;

            --orange: #fbbf24;
            --orange-soft: #33250c;

            --green: #34d399;
            --green-soft: #0b2e29;

            --red: #f87171;
            --red-soft: #371820;

            --shadow:
                0 18px 42px rgba(0, 0, 0, .28);
        }

        .progress-page,
        .progress-page * {
            box-sizing: border-box;
        }

        .progress-shell {
            width: min(100%, 1480px);
            margin: 0 auto;
            display: grid;
            gap: 16px;
        }

        .progress-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 22px;
            box-shadow: var(--shadow);
        }

        /* ==============================================================
           HERO
        ============================================================== */

        .progress-hero {
            position: relative;
            overflow: hidden;

            display: grid;
            grid-template-columns:
                minmax(0, 1fr)
                340px;

            align-items: center;

            gap: 28px;

            padding: 26px;
        }

        .progress-hero::before {
            content: "";

            position: absolute;

            width: 330px;
            height: 330px;

            top: -160px;
            right: -85px;

            border-radius: 999px;

            background:
                rgba(34, 211, 238, .09);

            pointer-events: none;
        }

        .progress-hero::after {
            content: "";

            position: absolute;

            width: 250px;
            height: 250px;

            bottom: -160px;
            right: 100px;

            border-radius: 999px;

            background:
                rgba(37, 99, 235, .07);

            pointer-events: none;
        }

        .progress-hero-copy,
        .level-card {
            position: relative;
            z-index: 1;
        }

        .progress-eyebrow {
            display: inline-flex;
            align-items: center;

            gap: 8px;

            padding: 7px 12px;

            border-radius: 999px;

            background: var(--cyan-soft);
            color: var(--cyan);

            font-size: 11px;
            font-weight: 900;

            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .progress-eyebrow svg {
            width: 15px;
            height: 15px;
        }

        .progress-title {
            margin: 13px 0 0;

            color: var(--text);

            font-size:
                clamp(31px, 3vw, 44px);

            line-height: 1.06;

            font-weight: 900;

            letter-spacing: -.045em;
        }

        .progress-subtitle {
            max-width: 760px;

            margin: 10px 0 0;

            color: var(--text-muted);

            font-size: 14px;

            line-height: 1.7;

            font-weight: 600;
        }

        /* ==============================================================
           CEFR
        ============================================================== */

        .level-card {
            padding: 18px;

            border: 1px solid var(--border);
            border-radius: 19px;

            background: var(--soft);
        }

        .level-head {
            display: flex;

            align-items: center;
            justify-content: space-between;

            gap: 12px;
        }

        .level-label {
            color: var(--text-muted);

            font-size: 10px;
            font-weight: 900;

            letter-spacing: .07em;

            text-transform: uppercase;
        }

        .level-coverage {
            display: inline-flex;

            align-items: center;

            padding: 6px 9px;

            border-radius: 999px;

            background: var(--cyan-soft);
            color: var(--cyan);

            font-size: 9px;
            font-weight: 900;

            white-space: nowrap;
        }

        .level-main {
            display: grid;

            grid-template-columns:
                auto minmax(0, 1fr);

            align-items: center;

            gap: 15px;

            margin-top: 15px;
        }

        .level-code {
            min-width: 85px;

            color: var(--cyan);

            font-size: 50px;

            line-height: 1;

            font-weight: 950;

            letter-spacing: -.06em;
        }

        .level-name {
            color: var(--text);

            font-size: 15px;
            font-weight: 900;
        }

        .level-description {
            margin-top: 3px;

            color: var(--text-muted);

            font-size: 10px;

            line-height: 1.45;

            font-weight: 650;
        }

        .level-score-row {
            display: flex;

            align-items: flex-end;
            justify-content: space-between;

            gap: 14px;

            margin-top: 15px;
            padding-top: 13px;

            border-top: 1px solid var(--border);
        }

        .level-score-label {
            color: var(--text-muted);

            font-size: 10px;
            font-weight: 800;
        }

        .level-score {
            color: var(--text);

            font-size: 24px;

            line-height: 1;

            font-weight: 950;
        }

        .level-score small {
            color: var(--text-muted);

            font-size: 10px;
            font-weight: 800;
        }

        .level-note {
            margin-top: 10px;

            color: var(--text-muted);

            font-size: 9px;

            line-height: 1.5;

            font-weight: 600;
        }

        /* ==============================================================
           METRICS
        ============================================================== */

        .progress-metrics {
            display: grid;

            grid-template-columns:
                repeat(4, minmax(0, 1fr));

            gap: 12px;
        }

        .progress-metric {
            min-width: 0;

            padding: 17px 18px;
        }

        .metric-head {
            display: flex;

            align-items: center;
            justify-content: space-between;

            gap: 12px;
        }

        .metric-label {
            color: var(--text-muted);

            font-size: 10px;
            font-weight: 850;
        }

        .metric-icon {
            display: inline-flex;

            align-items: center;
            justify-content: center;

            width: 34px;
            height: 34px;

            flex: 0 0 auto;

            border-radius: 11px;

            background: var(--metric-soft);
            color: var(--metric);
        }

        .metric-icon svg {
            width: 18px;
            height: 18px;
        }

        .metric-value {
            margin-top: 9px;

            color: var(--metric);

            font-size: 34px;

            line-height: 1;

            font-weight: 950;

            letter-spacing: -.04em;
        }

        .metric-foot {
            margin-top: 5px;

            color: var(--text-muted);

            font-size: 9px;

            line-height: 1.45;

            font-weight: 700;
        }

        .metric-cyan {
            --metric: var(--cyan);
            --metric-soft: var(--cyan-soft);
        }

        .metric-green {
            --metric: var(--green);
            --metric-soft: var(--green-soft);
        }

        .metric-blue {
            --metric: var(--blue);
            --metric-soft: var(--blue-soft);
        }

        .metric-purple {
            --metric: var(--purple);
            --metric-soft: var(--purple-soft);
        }

        /* ==============================================================
           SECTIONS
        ============================================================== */

        .progress-section {
            min-width: 0;

            padding: 20px;
        }

        .section-head {
            display: flex;

            align-items: flex-start;
            justify-content: space-between;

            gap: 18px;

            margin-bottom: 17px;
        }

        .section-copy {
            min-width: 0;
        }

        .section-title {
            margin: 0;

            color: var(--text);

            font-size: 18px;

            line-height: 1.3;

            font-weight: 950;

            letter-spacing: -.025em;
        }

        .section-subtitle {
            margin: 4px 0 0;

            color: var(--text-muted);

            font-size: 10px;

            line-height: 1.55;

            font-weight: 650;
        }

        .section-badge {
            flex: 0 0 auto;

            padding: 7px 10px;

            border-radius: 999px;

            background: var(--muted);
            color: var(--text-muted);

            font-size: 9px;
            font-weight: 900;
        }

        /* ==============================================================
           CHART
        ============================================================== */

        .trend-toolbar {
            display: flex;

            flex-wrap: wrap;

            justify-content: flex-end;

            gap: 6px;
        }

        .trend-filter {
            appearance: none;

            padding: 7px 10px;

            border: 1px solid var(--border);
            border-radius: 999px;

            background: var(--soft);
            color: var(--text-muted);

            cursor: pointer;

            font-family: inherit;

            font-size: 9px;
            font-weight: 900;

            transition:
                border-color .16s ease,
                color .16s ease,
                background .16s ease,
                transform .16s ease;
        }

        .trend-filter:hover {
            transform: translateY(-1px);

            border-color: var(--cyan);

            color: var(--text);
        }

        .trend-filter.active {
            border-color: var(--cyan);

            background: var(--cyan-soft);

            color: var(--cyan);
        }

        .trend-chart-container {
            position: relative;

            width: 100%;
            height: 315px;

            margin-top: 4px;
        }

        .trend-chart-container canvas {
            width: 100% !important;
            height: 315px !important;
        }

        .trend-footer {
            display: flex;

            align-items: center;
            justify-content: space-between;

            flex-wrap: wrap;

            gap: 12px;

            margin-top: 13px;

            padding-top: 13px;

            border-top: 1px solid var(--border);
        }

        .trend-legend {
            display: flex;

            flex-wrap: wrap;

            gap: 13px;
        }

        .trend-legend-item {
            display: inline-flex;

            align-items: center;

            gap: 6px;

            color: var(--text-muted);

            font-size: 9px;
            font-weight: 800;
        }

        .trend-dot {
            width: 8px;
            height: 8px;

            border-radius: 999px;

            background: var(--dot);
        }

        .trend-helper {
            color: var(--text-muted);

            font-size: 9px;
            font-weight: 650;
        }

        .trend-empty {
            min-height: 250px;

            display: grid;

            place-items: center;

            text-align: center;

            color: var(--text-muted);

            font-size: 11px;

            line-height: 1.6;

            font-weight: 700;
        }

        /* ==============================================================
           SKILL CARDS
        ============================================================== */

        .skill-grid {
            display: grid;

            grid-template-columns:
                repeat(2, minmax(0, 1fr));

            gap: 12px;
        }

        .skill-card {
            --skill: var(--cyan);
            --skill-soft: var(--cyan-soft);

            min-width: 0;

            padding: 17px;

            border: 1px solid var(--border);
            border-radius: 17px;

            background: var(--soft);
        }

        .skill-listening {
            --skill: var(--cyan);
            --skill-soft: var(--cyan-soft);
        }

        .skill-reading {
            --skill: var(--blue);
            --skill-soft: var(--blue-soft);
        }

        .skill-writing {
            --skill: var(--purple);
            --skill-soft: var(--purple-soft);
        }

        .skill-speaking {
            --skill: var(--orange);
            --skill-soft: var(--orange-soft);
        }

        .skill-head {
            display: flex;

            align-items: center;
            justify-content: space-between;

            gap: 14px;
        }

        .skill-heading {
            display: flex;

            align-items: center;

            gap: 10px;

            min-width: 0;
        }

        .skill-icon {
            width: 38px;
            height: 38px;

            display: inline-flex;

            align-items: center;
            justify-content: center;

            flex: 0 0 auto;

            border-radius: 11px;

            background: var(--skill-soft);
            color: var(--skill);

            font-size: 10px;
            font-weight: 950;
        }

        .skill-name {
            color: var(--text);

            font-size: 14px;
            font-weight: 950;
        }

        .skill-attempts {
            margin-top: 2px;

            color: var(--text-muted);

            font-size: 9px;
            font-weight: 700;
        }

        .skill-level {
            padding: 6px 9px;

            border-radius: 999px;

            background: var(--skill-soft);
            color: var(--skill);

            font-size: 10px;
            font-weight: 950;
        }

        .skill-score-row {
            display: flex;

            align-items: flex-end;
            justify-content: space-between;

            gap: 14px;

            margin-top: 18px;
        }

        .skill-current-label {
            color: var(--text-muted);

            font-size: 9px;
            font-weight: 800;
        }

        .skill-current-score {
            margin-top: 4px;

            color: var(--skill);

            font-size: 33px;

            line-height: 1;

            font-weight: 950;

            letter-spacing: -.04em;
        }

        .skill-current-score small {
            color: var(--text-muted);

            font-size: 10px;
            font-weight: 800;
        }

        .skill-improvement {
            display: flex;

            flex-direction: column;

            align-items: flex-end;

            gap: 2px;

            text-align: right;
        }

        .improvement-value {
            font-size: 14px;
            font-weight: 950;
        }

        .improvement-positive {
            color: var(--green);
        }

        .improvement-negative {
            color: var(--red);
        }

        .improvement-neutral {
            color: var(--text-muted);
        }

        .improvement-label {
            color: var(--text-muted);

            font-size: 8px;
            font-weight: 700;
        }

        .skill-stats {
            display: grid;

            grid-template-columns:
                repeat(4, minmax(0, 1fr));

            gap: 7px;

            margin-top: 16px;
        }

        .skill-stat {
            min-width: 0;

            padding: 10px;

            border: 1px solid var(--border);
            border-radius: 12px;

            background: var(--card);
        }

        .skill-stat-label {
            overflow: hidden;

            color: var(--text-muted);

            font-size: 8px;
            font-weight: 800;

            white-space: nowrap;
            text-overflow: ellipsis;
        }

        .skill-stat-value {
            margin-top: 4px;

            color: var(--text);

            font-size: 16px;

            line-height: 1;

            font-weight: 950;
        }

        .subskill-panel {
            margin-top: 16px;
            padding: 13px;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: var(--card);
        }

        .subskill-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 10px;
        }

        .subskill-title {
            color: var(--text);
            font-size: 9px;
            font-weight: 950;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .subskill-badge {
            padding: 4px 7px;
            border-radius: 999px;
            background: var(--skill-soft);
            color: var(--skill);
            font-size: 8px;
            font-weight: 900;
            white-space: nowrap;
        }

        .subskill-list {
            display: grid;
            gap: 9px;
        }

        .subskill-row {
            min-width: 0;
        }

        .subskill-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .subskill-label {
            min-width: 0;
            overflow: hidden;
            color: var(--text-soft);
            font-size: 9px;
            font-weight: 800;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .subskill-score {
            flex: 0 0 auto;
            color: var(--skill);
            font-size: 10px;
            font-weight: 950;
        }

        .subskill-track {
            height: 6px;
            margin-top: 5px;
            overflow: hidden;
            border-radius: 999px;
            background: var(--muted);
        }

        .subskill-fill {
            height: 100%;
            border-radius: inherit;
            background: var(--skill);
        }

        .subskill-empty {
            color: var(--text-muted);
            font-size: 9px;
            line-height: 1.55;
            font-weight: 650;
        }

        .skill-completion {
            margin-top: 16px;
        }

        .completion-head {
            display: flex;

            justify-content: space-between;

            gap: 12px;
        }

        .completion-label,
        .completion-value {
            font-size: 9px;
            font-weight: 800;
        }

        .completion-label {
            color: var(--text-muted);
        }

        .completion-value {
            color: var(--skill);
        }

        .skill-track {
            overflow: hidden;

            height: 7px;

            margin-top: 8px;

            border-radius: 999px;

            background: var(--muted);
        }

        .skill-bar {
            height: 100%;

            border-radius: inherit;

            background: var(--skill);
        }

        /* ==============================================================
           FILTER
        ============================================================== */

        .history-filter-form {
            display: grid;

            grid-template-columns:
                repeat(3, minmax(130px, 1fr))
                minmax(160px, 1.15fr)
                auto
                auto;

            align-items: end;

            gap: 8px;

            margin-top: 15px;

            padding: 13px;

            border: 1px solid var(--border);
            border-radius: 16px;

            background: var(--soft);
        }

        .filter-group {
            min-width: 0;
        }

        .filter-label {
            display: block;

            margin: 0 0 5px 2px;

            color: var(--text-muted);

            font-size: 8px;
            font-weight: 900;

            letter-spacing: .05em;

            text-transform: uppercase;
        }

        .filter-select {
            width: 100%;
            height: 39px;

            padding:
                0 32px
                0 11px;

            border: 1px solid var(--border);
            border-radius: 11px;

            outline: none;

            background: var(--card);
            color: var(--text);

            font-family: inherit;

            font-size: 10px;
            font-weight: 750;
        }

        .filter-select:focus {
            border-color: var(--cyan);

            box-shadow:
                0 0 0 3px rgba(8, 145, 178, .10);
        }

        .filter-button,
        .filter-reset {
            min-height: 39px;

            display: inline-flex;

            align-items: center;
            justify-content: center;

            gap: 6px;

            padding: 8px 13px;

            border-radius: 11px;

            font-family: inherit;

            font-size: 9px;
            font-weight: 900;

            text-decoration: none;

            white-space: nowrap;
        }

        .filter-button {
            border: 0;

            cursor: pointer;

            background:
                linear-gradient(
                    100deg,
                    var(--cyan),
                    var(--blue)
                );

            color: #ffffff;
        }

        .filter-reset {
            border: 1px solid var(--border);

            background: var(--card);
            color: var(--text-muted);
        }

        .filter-button svg,
        .filter-reset svg {
            width: 13px;
            height: 13px;
        }

        /* ==============================================================
           HISTORY
        ============================================================== */

        .history-list {
            display: grid;

            gap: 9px;

            max-height: 620px;

            overflow-y: auto;

            overscroll-behavior: contain;

            padding-right: 4px;

            scrollbar-width: thin;

            scrollbar-color:
                var(--border)
                transparent;
        }

        .history-list::-webkit-scrollbar {
            width: 7px;
        }

        .history-list::-webkit-scrollbar-track {
            background: transparent;
        }

        .history-list::-webkit-scrollbar-thumb {
            border-radius: 999px;

            background: var(--border);
        }

        .history-item {
            display: grid;

            grid-template-columns:
                minmax(0, 1fr)
                auto;

            align-items: center;

            gap: 18px;

            padding: 13px 14px;

            border: 1px solid var(--border);
            border-radius: 15px;

            background: var(--soft);

            transition:
                transform .16s ease,
                box-shadow .16s ease;
        }

        .history-item:hover {
            transform: translateY(-1px);

            box-shadow: var(--shadow);
        }

        .history-main {
            min-width: 0;
        }

        .history-badges {
            display: flex;

            flex-wrap: wrap;

            gap: 6px;

            margin-bottom: 7px;
        }

        .history-badge {
            display: inline-flex;

            align-items: center;

            padding: 5px 8px;

            border-radius: 999px;

            font-size: 8px;

            line-height: 1;

            font-weight: 950;

            letter-spacing: .04em;

            text-transform: uppercase;
        }

        .history-type {
            background: var(--cyan-soft);
            color: var(--cyan);
        }

        .history-skill {
            background: var(--muted);
            color: var(--text-muted);
        }

        .history-completed {
            background: var(--green-soft);
            color: var(--green);
        }

        .history-pending {
            background: var(--orange-soft);
            color: var(--orange);
        }

        .history-failed {
            background: var(--red-soft);
            color: var(--red);
        }

        .history-title {
            margin: 0;

            overflow: hidden;

            color: var(--text);

            font-size: 14px;

            line-height: 1.35;

            font-weight: 950;

            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .history-meta {
            margin: 4px 0 0;

            color: var(--text-muted);

            font-size: 9px;

            line-height: 1.55;

            font-weight: 650;
        }

        .history-side {
            display: flex;

            align-items: center;

            gap: 12px;
        }

        .history-score {
            min-width: 66px;

            text-align: right;
        }

        .history-score-label {
            color: var(--text-muted);

            font-size: 8px;
            font-weight: 850;

            text-transform: uppercase;
        }

        .history-score-value {
            margin-top: 2px;

            font-size: 24px;

            line-height: 1;

            font-weight: 950;
        }

        .score-high {
            color: var(--green);
        }

        .score-mid {
            color: var(--blue);
        }

        .score-low {
            color: var(--orange);
        }

        .score-empty {
            color: var(--text-muted);
        }

        .progress-button {
            min-height: 40px;

            display: inline-flex;

            align-items: center;
            justify-content: center;

            gap: 7px;

            padding: 8px 12px;

            border-radius: 11px;

            background:
                linear-gradient(
                    100deg,
                    var(--cyan),
                    var(--blue)
                );

            color: #ffffff;

            font-size: 9px;
            font-weight: 900;

            text-decoration: none;

            white-space: nowrap;
        }

        .progress-button svg {
            width: 14px;
            height: 14px;
        }

        .result-unavailable {
            display: inline-flex;

            min-height: 40px;

            align-items: center;
            justify-content: center;

            padding: 8px 12px;

            border: 1px solid var(--border);
            border-radius: 11px;

            background: var(--card);
            color: var(--text-muted);

            font-size: 9px;
            font-weight: 800;
        }

        /* ==============================================================
           EMPTY
        ============================================================== */

        .progress-empty {
            padding: 50px 20px;

            text-align: center;
        }

        .progress-empty-icon {
            width: 58px;
            height: 58px;

            display: inline-flex;

            align-items: center;
            justify-content: center;

            border-radius: 17px;

            background: var(--muted);
            color: var(--text-muted);
        }

        .progress-empty-icon svg {
            width: 27px;
            height: 27px;
        }

        .progress-empty-title {
            margin: 14px 0 0;

            color: var(--text);

            font-size: 17px;
            font-weight: 950;
        }

        .progress-empty-text {
            margin: 5px 0 0;

            color: var(--text-muted);

            font-size: 10px;

            line-height: 1.6;

            font-weight: 650;
        }

        /* ==============================================================
           RESPONSIVE
        ============================================================== */

        @media (max-width: 1120px) {
            .progress-hero {
                grid-template-columns:
                    minmax(0, 1fr)
                    300px;
            }

            .history-filter-form {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));
            }

            .filter-button,
            .filter-reset {
                width: 100%;
            }
        }

        @media (max-width: 900px) {
            .progress-hero {
                grid-template-columns:
                    minmax(0, 1fr);
            }

            .progress-metrics {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));
            }

            .skill-grid {
                grid-template-columns:
                    minmax(0, 1fr);
            }

            .section-head {
                flex-direction: column;
            }

            .trend-toolbar {
                justify-content: flex-start;
            }
        }

        @media (max-width: 720px) {
            .progress-hero {
                padding: 19px;
            }

            .progress-title {
                font-size: 30px;
            }

            .progress-section {
                padding: 16px;
            }

            .history-filter-form {
                grid-template-columns:
                    minmax(0, 1fr);
            }

            .history-item {
                grid-template-columns:
                    minmax(0, 1fr);

                gap: 12px;
            }

            .history-title {
                white-space: normal;
            }

            .history-side {
                justify-content: space-between;
            }

            .history-score {
                text-align: left;
            }

            .trend-chart-container,
            .trend-chart-container canvas {
                height: 280px !important;
            }
        }

        @media (max-width: 520px) {
            .progress-metrics {
                grid-template-columns:
                    minmax(0, 1fr);
            }

            .skill-stats {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));
            }

            .history-side {
                flex-direction: column;

                align-items: stretch;
            }

            .progress-button,
            .result-unavailable {
                width: 100%;
            }

            .level-main {
                grid-template-columns:
                    minmax(0, 1fr);
            }

            .level-code {
                min-width: 0;
            }

            .trend-toolbar {
                width: 100%;

                overflow-x: auto;

                flex-wrap: nowrap;

                padding-bottom: 3px;
            }

            .trend-filter {
                flex: 0 0 auto;
            }

            .trend-footer {
                align-items: flex-start;

                flex-direction: column;
            }
        }
    </style>


    @php
        $skillMeta = [
            'listening' => [
                'label' => 'Listening',
                'short' => 'LI',
            ],

            'reading' => [
                'label' => 'Reading',
                'short' => 'RE',
            ],

            'writing' => [
                'label' => 'Writing',
                'short' => 'WR',
            ],

            'speaking' => [
                'label' => 'Speaking',
                'short' => 'SP',
            ],
        ];
    @endphp


    <div class="progress-page">
        <div class="progress-shell">

            {{-- ========================================================= --}}
            {{-- HERO --}}
            {{-- ========================================================= --}}

            <section class="progress-card progress-hero">

                <div class="progress-hero-copy">

                    <div class="progress-eyebrow">
                        <svg
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M3 3v18h18M7 16l4-4 3 3 5-7"
                            />
                        </svg>

                        Learning Progress
                    </div>

                    <h1 class="progress-title">
                        Track Your English Journey
                    </h1>

                    <p class="progress-subtitle">
                        Monitor your Listening, Reading, Writing, and Speaking
                        development from pre-test, through every learning unit,
                        until post-test.
                    </p>

                </div>


                <aside class="level-card">

                    <div class="level-head">

                        <span class="level-label">
                            Estimated CEFR
                        </span>

                        <span class="level-coverage">
                            {{ $overallCoverage }}/4 skills measured
                        </span>

                    </div>


                    <div class="level-main">

                        <div class="level-code">
                            {{ $overallLevel['code'] }}
                        </div>

                        <div>
                            <div class="level-name">
                                {{ $overallLevel['label'] }}
                            </div>

                            <div class="level-description">
                                {{ $overallLevel['description'] }}
                            </div>
                        </div>

                    </div>


                    <div class="level-score-row">

                        <div class="level-score-label">
                            Overall Score
                        </div>

                        <div class="level-score">
                            {{ $overallScore ?? '-' }}

                            <small>/ 100</small>
                        </div>

                    </div>


                    <div class="level-note">
                        Estimated from the latest available SpeakVerse score
                        for each skill. This indicator is not an official
                        CEFR certification.
                    </div>

                </aside>

            </section>


            {{-- ========================================================= --}}
            {{-- METRICS --}}
            {{-- ========================================================= --}}

            <section class="progress-metrics">

                <article class="progress-card progress-metric metric-cyan">

                    <div class="metric-head">

                        <span class="metric-label">
                            Average Score
                        </span>

                        <span class="metric-icon">
                            <svg
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 17v-6m4 6V7m4 10v-3M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"
                                />
                            </svg>
                        </span>

                    </div>

                    <div class="metric-value">
                        {{ $averageScore }}
                    </div>

                    <div class="metric-foot">
                        Across completed assessments
                    </div>

                </article>


                <article class="progress-card progress-metric metric-green">

                    <div class="metric-head">

                        <span class="metric-label">
                            Completed Tests
                        </span>

                        <span class="metric-icon">
                            <svg
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                        </span>

                    </div>

                    <div class="metric-value">
                        {{ $totalCompleted }}
                    </div>

                    <div class="metric-foot">
                        Finished assessment attempts
                    </div>

                </article>


                <article class="progress-card progress-metric metric-blue">

                    <div class="metric-head">

                        <span class="metric-label">
                            Pre-Test Average
                        </span>

                        <span class="metric-icon">
                            <svg
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V6m0 10v2m9-6a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                        </span>

                    </div>

                    <div class="metric-value">
                        {{ $pretestAverage }}
                    </div>

                    <div class="metric-foot">
                        Current pre-test performance
                    </div>

                </article>


                <article class="progress-card progress-metric metric-purple">

                    <div class="metric-head">

                        <span class="metric-label">
                            Post-Test Average
                        </span>

                        <span class="metric-icon">
                            <svg
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z"
                                />
                            </svg>
                        </span>

                    </div>

                    <div class="metric-value">
                        {{ $posttestAverage }}
                    </div>

                    <div class="metric-foot">
                        Current post-test performance
                    </div>

                </article>

            </section>


            {{-- ========================================================= --}}
            {{-- TREND --}}
            {{-- ========================================================= --}}

            <section class="progress-card progress-section">

                <header class="section-head">

                    <div class="section-copy">

                        <h2 class="section-title">
                            Skill Performance Trend
                        </h2>

                        <p class="section-subtitle">
                            Compare your latest score at each learning stage,
                            from Pre-Test through Post-Test.
                        </p>

                    </div>


                    @if ($trendChart['count'] > 0)

                        <div class="trend-toolbar">

                            <button
                                type="button"
                                class="trend-filter active"
                                data-trend-filter="all"
                            >
                                All Skills
                            </button>

                            <button
                                type="button"
                                class="trend-filter"
                                data-trend-filter="listening"
                            >
                                Listening
                            </button>

                            <button
                                type="button"
                                class="trend-filter"
                                data-trend-filter="reading"
                            >
                                Reading
                            </button>

                            <button
                                type="button"
                                class="trend-filter"
                                data-trend-filter="writing"
                            >
                                Writing
                            </button>

                            <button
                                type="button"
                                class="trend-filter"
                                data-trend-filter="speaking"
                            >
                                Speaking
                            </button>

                        </div>

                    @endif

                </header>


                @if ($trendChart['count'] > 0)

                    <div class="trend-chart-container">

                        <canvas
                            id="skillTrendChart"
                            aria-label="Skill performance trend"
                        ></canvas>

                    </div>


                    <div class="trend-footer">

                        <div class="trend-legend">

                            <span class="trend-legend-item">

                                <span
                                    class="trend-dot"
                                    style="--dot: #06b6d4;"
                                ></span>

                                Listening

                            </span>


                            <span class="trend-legend-item">

                                <span
                                    class="trend-dot"
                                    style="--dot: #3b82f6;"
                                ></span>

                                Reading

                            </span>


                            <span class="trend-legend-item">

                                <span
                                    class="trend-dot"
                                    style="--dot: #8b5cf6;"
                                ></span>

                                Writing

                            </span>


                            <span class="trend-legend-item">

                                <span
                                    class="trend-dot"
                                    style="--dot: #f59e0b;"
                                ></span>

                                Speaking

                            </span>

                        </div>


                        <div class="trend-helper">
                            Latest completed score per learning stage
                        </div>

                    </div>

                @else

                    <div class="trend-empty">
                        Complete an assessment to begin displaying your
                        skill performance trend.
                    </div>

                @endif

            </section>


            {{-- ========================================================= --}}
            {{-- DETAILED SKILLS --}}
            {{-- ========================================================= --}}

            <section class="progress-card progress-section">

                <header class="section-head">

                    <div class="section-copy">

                        <h2 class="section-title">
                            Detailed Skill Progress
                        </h2>

                        <p class="section-subtitle">
                            Review performance and lesson completion
                            separately for each English skill.
                        </p>

                    </div>

                    <span class="section-badge">
                        4 skills
                    </span>

                </header>


                <div class="skill-grid">

                    @foreach ($skillMeta as $skill => $meta)

                        @php
                            $detail = $skillDetails[$skill];

                            $improvement =
                                $detail['improvement'];

                            $improvementClass =
                                match (true) {
                                    $improvement === null
                                        => 'improvement-neutral',

                                    $improvement > 0
                                        => 'improvement-positive',

                                    $improvement < 0
                                        => 'improvement-negative',

                                    default
                                        => 'improvement-neutral',
                                };

                            $improvementText =
                                match (true) {
                                    $improvement === null
                                        => '—',

                                    $improvement > 0
                                        => '+' . $improvement,

                                    default
                                        => (string) $improvement,
                                };
                        @endphp


                        <article
                            class="
                                skill-card
                                skill-{{ $skill }}
                            "
                        >

                            <div class="skill-head">

                                <div class="skill-heading">

                                    <span class="skill-icon">
                                        {{ $meta['short'] }}
                                    </span>

                                    <div>

                                        <div class="skill-name">
                                            {{ $meta['label'] }}
                                        </div>

                                        <div class="skill-attempts">
                                            {{ $detail['assessment_count'] }}
                                            assessment
                                            {{ $detail['assessment_count'] === 1 ? 'attempt' : 'attempts' }}
                                        </div>

                                    </div>

                                </div>


                                <span class="skill-level">
                                    {{ $detail['level']['code'] }}
                                </span>

                            </div>


                            <div class="skill-score-row">

                                <div>

                                    <div class="skill-current-label">
                                        Latest Score
                                    </div>

                                    <div class="skill-current-score">
                                        {{ $detail['latest_score'] ?? '-' }}

                                        <small>/ 100</small>
                                    </div>

                                </div>


                                <div class="skill-improvement">

                                    <span
                                        class="
                                            improvement-value
                                            {{ $improvementClass }}
                                        "
                                    >
                                        {{ $improvementText }}
                                    </span>

                                    <span class="improvement-label">
                                        {{ $detail['improvement_basis'] ?? 'Need more results' }}
                                    </span>

                                </div>

                            </div>


                            <div class="skill-stats">

                                <div class="skill-stat">

                                    <div class="skill-stat-label">
                                        Average
                                    </div>

                                    <div class="skill-stat-value">
                                        {{ $detail['average_score'] ?? '-' }}
                                    </div>

                                </div>


                                <div class="skill-stat">

                                    <div class="skill-stat-label">
                                        Best
                                    </div>

                                    <div class="skill-stat-value">
                                        {{ $detail['best_score'] ?? '-' }}
                                    </div>

                                </div>


                                <div class="skill-stat">

                                    <div class="skill-stat-label">
                                        Pre-Test
                                    </div>

                                    <div class="skill-stat-value">
                                        {{ $detail['pretest_score'] ?? '-' }}
                                    </div>

                                </div>


                                <div class="skill-stat">

                                    <div class="skill-stat-label">
                                        Post-Test
                                    </div>

                                    <div class="skill-stat-value">
                                        {{ $detail['posttest_score'] ?? '-' }}
                                    </div>

                                </div>

                            </div>


                            <div class="subskill-panel">

                                <div class="subskill-head">

                                    <span class="subskill-title">
                                        Sub-skill Performance
                                    </span>

                                    @if ($detail['subskills_available'])

                                        <span class="subskill-badge">
                                            {{ count($detail['subskills']) }}
                                            criteria
                                        </span>

                                    @endif

                                </div>


                                @if ($detail['subskills_available'])

                                    <div class="subskill-list">

                                        @foreach (
                                            $detail['subskills']
                                            as $subskill
                                        )

                                            <div class="subskill-row">

                                                <div class="subskill-meta">

                                                    <span class="subskill-label">
                                                        {{ $subskill['label'] }}
                                                    </span>

                                                    <span class="subskill-score">
                                                        {{ $subskill['score'] }}
                                                        /100
                                                    </span>

                                                </div>


                                                <div class="subskill-track">

                                                    <div
                                                        class="subskill-fill"
                                                        style="
                                                            width:
                                                            {{ max(
                                                                0,
                                                                min(
                                                                    100,
                                                                    (int) $subskill['score']
                                                                )
                                                            ) }}%;
                                                        "
                                                    ></div>

                                                </div>

                                            </div>

                                        @endforeach

                                    </div>

                                @else

                                    <div class="subskill-empty">

                                        @if (
                                            $skill === 'reading'
                                            ||
                                            $skill === 'listening'
                                        )

                                            Sub-skill categories have not been
                                            assigned to the questions yet.

                                        @else

                                            Complete an assessment with rubric
                                            scoring to display sub-skill results.

                                        @endif

                                    </div>

                                @endif

                            </div>


                            <div class="skill-completion">

                                <div class="completion-head">

                                    <span class="completion-label">
                                        Lesson Completion
                                    </span>

                                    <span class="completion-value">
                                        {{ $detail['completed'] }}
                                        /
                                        {{ $detail['total'] }}
                                        ·
                                        {{ $detail['percentage'] }}%
                                    </span>

                                </div>


                                <div class="skill-track">

                                    <div
                                        class="skill-bar"
                                        style="
                                            width:
                                            {{ max(
                                                0,
                                                min(
                                                    100,
                                                    (int) $detail['percentage']
                                                )
                                            ) }}%;
                                        "
                                    ></div>

                                </div>

                            </div>

                        </article>

                    @endforeach

                </div>

            </section>


            {{-- ========================================================= --}}
            {{-- ASSESSMENT HISTORY --}}
            {{-- ========================================================= --}}

            <section class="progress-card progress-section">

                <header>

                    <div
                        class="section-head"
                        style="margin-bottom: 0;"
                    >

                        <div class="section-copy">

                            <h2 class="section-title">
                                Assessment History
                            </h2>

                            <p class="section-subtitle">
                                Review assessment records and filter results
                                by skill, type, status, or score.
                            </p>

                        </div>


                        <span class="section-badge">

                            @if (
                                $historyFilteredTotal
                                ===
                                $historyTotal
                            )

                                {{ $historyTotal }} records

                            @else

                                {{ $historyFilteredTotal }}
                                of
                                {{ $historyTotal }} records

                            @endif

                        </span>

                    </div>


                    <form
                        method="GET"
                        action="{{ route('progress') }}"
                        class="history-filter-form"
                    >

                        <div class="filter-group">

                            <label
                                for="history-skill"
                                class="filter-label"
                            >
                                Skill
                            </label>

                            <select
                                id="history-skill"
                                name="skill"
                                class="filter-select"
                            >

                                <option
                                    value="all"
                                    @selected(
                                        $filters['skill']
                                        ===
                                        'all'
                                    )
                                >
                                    All Skills
                                </option>


                                @foreach (
                                    $skillMeta
                                    as $skill => $meta
                                )

                                    <option
                                        value="{{ $skill }}"
                                        @selected(
                                            $filters['skill']
                                            ===
                                            $skill
                                        )
                                    >
                                        {{ $meta['label'] }}
                                    </option>

                                @endforeach

                            </select>

                        </div>


                        <div class="filter-group">

                            <label
                                for="history-type"
                                class="filter-label"
                            >
                                Assessment
                            </label>

                            <select
                                id="history-type"
                                name="type"
                                class="filter-select"
                            >

                                <option
                                    value="all"
                                    @selected(
                                        $filters['type']
                                        ===
                                        'all'
                                    )
                                >
                                    All Types
                                </option>

                                <option
                                    value="pretest"
                                    @selected(
                                        $filters['type']
                                        ===
                                        'pretest'
                                    )
                                >
                                    Pre-Test
                                </option>

                                <option
                                    value="unit"
                                    @selected(
                                        $filters['type']
                                        ===
                                        'unit'
                                    )
                                >
                                    Unit
                                </option>

                                <option
                                    value="posttest"
                                    @selected(
                                        $filters['type']
                                        ===
                                        'posttest'
                                    )
                                >
                                    Post-Test
                                </option>

                            </select>

                        </div>


                        <div class="filter-group">

                            <label
                                for="history-status"
                                class="filter-label"
                            >
                                Status
                            </label>

                            <select
                                id="history-status"
                                name="status"
                                class="filter-select"
                            >

                                <option
                                    value="all"
                                    @selected(
                                        $filters['status']
                                        ===
                                        'all'
                                    )
                                >
                                    All Status
                                </option>

                                <option
                                    value="completed"
                                    @selected(
                                        $filters['status']
                                        ===
                                        'completed'
                                    )
                                >
                                    Completed
                                </option>

                                <option
                                    value="pending"
                                    @selected(
                                        $filters['status']
                                        ===
                                        'pending'
                                    )
                                >
                                    Pending
                                </option>

                                <option
                                    value="failed"
                                    @selected(
                                        $filters['status']
                                        ===
                                        'failed'
                                    )
                                >
                                    Failed
                                </option>

                            </select>

                        </div>


                        <div class="filter-group">

                            <label
                                for="history-sort"
                                class="filter-label"
                            >
                                Sort By
                            </label>

                            <select
                                id="history-sort"
                                name="sort"
                                class="filter-select"
                            >

                                <option
                                    value="newest"
                                    @selected(
                                        $filters['sort']
                                        ===
                                        'newest'
                                    )
                                >
                                    Newest First
                                </option>

                                <option
                                    value="oldest"
                                    @selected(
                                        $filters['sort']
                                        ===
                                        'oldest'
                                    )
                                >
                                    Oldest First
                                </option>

                                <option
                                    value="highest"
                                    @selected(
                                        $filters['sort']
                                        ===
                                        'highest'
                                    )
                                >
                                    Highest Score
                                </option>

                                <option
                                    value="lowest"
                                    @selected(
                                        $filters['sort']
                                        ===
                                        'lowest'
                                    )
                                >
                                    Lowest Score
                                </option>

                            </select>

                        </div>


                        <button
                            type="submit"
                            class="filter-button"
                        >

                            <svg
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M3 4a1 1 0 011-1h16a1 1 0 01.8 1.6L14 13.667V19a1 1 0 01-.553.894l-4 2A1 1 0 018 21v-7.333L3.2 4.6A1 1 0 013 4z"
                                />
                            </svg>

                            Apply

                        </button>


                        <a
                            href="{{ route('progress') }}"
                            class="filter-reset"
                        >

                            <svg
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                                />
                            </svg>

                            Reset

                        </a>

                    </form>

                </header>


                <div
                    class="history-list"
                    style="margin-top: 15px;"
                >

                    @forelse (
                        $submissions
                        as $submission
                    )

                        @php
                            $score =
                                $submission->final_score;

                            $submissionType =
                                strtolower(
                                    trim(
                                        (string) $submission->type
                                    )
                                );

                            $submissionStatus =
                                strtolower(
                                    trim(
                                        (string) $submission->status
                                    )
                                );

                            $typeLabel =
                                match ($submissionType) {
                                    'pretest'
                                        => 'PRE-TEST',

                                    'posttest'
                                        => 'POST-TEST',

                                    'unit'
                                        => 'UNIT',

                                    default
                                        => strtoupper(
                                            (string) $submission->type
                                        ),
                                };

                            $statusClass =
                                match ($submissionStatus) {
                                    'completed'
                                        => 'history-completed',

                                    'failed'
                                        => 'history-failed',

                                    default
                                        => 'history-pending',
                                };

                            $scoreClass =
                                match (true) {
                                    $score === null
                                        => 'score-empty',

                                    $score >= 80
                                        => 'score-high',

                                    $score >= 60
                                        => 'score-mid',

                                    default
                                        => 'score-low',
                                };

                            $title =
                                $submissionType === 'unit'
                                    ? (
                                        (
                                            $submission->unit->title
                                            ?? 'Unit'
                                        )
                                        .
                                        ' - '
                                        .
                                        ucfirst(
                                            (string) $submission->skill
                                        )
                                    )
                                    : (
                                        $typeLabel
                                        .
                                        ' - '
                                        .
                                        ucfirst(
                                            (string) $submission->skill
                                        )
                                    );

                            $description =
                                $submissionType === 'unit'
                                    ? (
                                        $submission->unit->subtitle
                                        ??
                                        $submission->lesson->title
                                        ??
                                        '-'
                                    )
                                    : (
                                        $submission->lesson->title
                                        ??
                                        '-'
                                    );

                            $date =
                                $submission
                                    ->submitted_at
                                    ?->format(
                                        'd M Y, H:i'
                                    )
                                ??
                                $submission
                                    ->created_at
                                    ?->format(
                                        'd M Y, H:i'
                                    )
                                ??
                                '-';
                        @endphp


                        <article class="history-item">

                            <div class="history-main">

                                <div class="history-badges">

                                    <span
                                        class="
                                            history-badge
                                            history-type
                                        "
                                    >
                                        {{ $typeLabel }}
                                    </span>


                                    <span
                                        class="
                                            history-badge
                                            history-skill
                                        "
                                    >
                                        {{ ucfirst(
                                            (string) $submission->skill
                                        ) }}
                                    </span>


                                    <span
                                        class="
                                            history-badge
                                            {{ $statusClass }}
                                        "
                                    >
                                        {{ ucfirst(
                                            (string) $submission->status
                                        ) }}
                                    </span>

                                </div>


                                <h3 class="history-title">
                                    {{ $title }}
                                </h3>


                                <p class="history-meta">
                                    {{ $description }}

                                    &middot;

                                    {{ $date }}
                                </p>

                            </div>


                            <div class="history-side">

                                <div class="history-score">

                                    <div class="history-score-label">
                                        Score
                                    </div>

                                    <div
                                        class="
                                            history-score-value
                                            {{ $scoreClass }}
                                        "
                                    >
                                        {{ $score ?? '-' }}
                                    </div>

                                </div>


                                @if (
                                    $submissionStatus
                                    ===
                                    'completed'
                                )

                                    <a
                                        href="{{ route(
                                            'student.assessment.result',
                                            $submission->id
                                        ) }}"
                                        class="progress-button"
                                    >

                                        View Result

                                        <svg
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M9 5l7 7-7 7"
                                            />
                                        </svg>

                                    </a>

                                @else

                                    <span class="result-unavailable">
                                        No Result
                                    </span>

                                @endif

                            </div>

                        </article>


                    @empty

                        <div class="progress-empty">

                            <span class="progress-empty-icon">

                                <svg
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l3.414 3.414A1 1 0 0117 7.414V19a2 2 0 01-2 2z"
                                    />
                                </svg>

                            </span>


                            <h3 class="progress-empty-title">
                                No Assessment Found
                            </h3>


                            <p class="progress-empty-text">
                                No assessment matches the selected filters.
                                Try changing or resetting the filters.
                            </p>

                        </div>

                    @endforelse

                </div>

            </section>

        </div>
    </div>


    {{-- ============================================================= --}}
    {{-- CHART.JS VIA CDN --}}
    {{-- ============================================================= --}}

    @if ($trendChart['count'] > 0)

        <script
            src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"
        ></script>


        <script>
            (function () {

                function initialiseProgressChart() {

                    const canvas =
                        document.getElementById(
                            'skillTrendChart'
                        );

                    if (!canvas) {
                        return;
                    }


                    if (typeof window.Chart === 'undefined') {

                        console.error(
                            'SpeakVerse Progress: Chart.js failed to load.'
                        );

                        return;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Laravel Data
                    |--------------------------------------------------------------------------
                    */

                    const labels =
                        @json($trendChart['labels']);

                    const trendData =
                        @json($trendChart['datasets']);


                    /*
                    |--------------------------------------------------------------------------
                    | Skill Configuration
                    |--------------------------------------------------------------------------
                    */

                    const skillConfig = {

                        listening: {
                            label: 'Listening',
                            color: '#06b6d4',
                            background:
                                'rgba(6, 182, 212, 0.12)',
                        },

                        reading: {
                            label: 'Reading',
                            color: '#3b82f6',
                            background:
                                'rgba(59, 130, 246, 0.12)',
                        },

                        writing: {
                            label: 'Writing',
                            color: '#8b5cf6',
                            background:
                                'rgba(139, 92, 246, 0.12)',
                        },

                        speaking: {
                            label: 'Speaking',
                            color: '#f59e0b',
                            background:
                                'rgba(245, 158, 11, 0.12)',
                        },

                    };


                    /*
                    |--------------------------------------------------------------------------
                    | Chart Theme
                    |--------------------------------------------------------------------------
                    */

                    const page =
                        document.querySelector(
                            '.progress-page'
                        );


                    function getTheme() {

                        const styles =
                            getComputedStyle(page);

                        return {

                            text:
                                styles
                                    .getPropertyValue(
                                        '--text-muted'
                                    )
                                    .trim()
                                ||
                                '#64748b',

                            textMain:
                                styles
                                    .getPropertyValue(
                                        '--text'
                                    )
                                    .trim()
                                ||
                                '#0f172a',

                            border:
                                styles
                                    .getPropertyValue(
                                        '--border'
                                    )
                                    .trim()
                                ||
                                '#dbe4ef',

                            card:
                                styles
                                    .getPropertyValue(
                                        '--card'
                                    )
                                    .trim()
                                ||
                                '#ffffff',

                        };
                    }


                    const theme =
                        getTheme();


                    /*
                    |--------------------------------------------------------------------------
                    | Dataset
                    |--------------------------------------------------------------------------
                    */

                    const datasets =
                        Object.entries(
                            skillConfig
                        ).map(
                            function (
                                [skill, config]
                            ) {

                                return {

                                    skill: skill,

                                    label:
                                        config.label,

                                    data:
                                        trendData[skill]
                                        ?? [],

                                    borderColor:
                                        config.color,

                                    backgroundColor:
                                        config.background,

                                    pointBackgroundColor:
                                        config.color,

                                    pointBorderColor:
                                        theme.card,

                                    pointHoverBackgroundColor:
                                        config.color,

                                    pointHoverBorderColor:
                                        theme.card,

                                    pointBorderWidth: 2,

                                    pointHoverBorderWidth: 3,

                                    borderWidth: 2.5,

                                    pointRadius: 4.5,

                                    pointHoverRadius: 6,

                                    /*
                                     * Sedikit smoothing agar grafik tetap natural.
                                     */
                                    tension: .25,

                                    /*
                                     * Hubungkan dua hasil yang tersedia walaupun terdapat
                                     * learning stage kosong di tengah.
                                     *
                                     * Kalau stage yang dilewati kosong, segment tersebut
                                     * akan dibuat putus-putus di bagian "segment" di bawah.
                                     */
                                    spanGaps: true,

                                    showLine: true,

                                    fill: false,

                                    /*
                                    |--------------------------------------------------------------------------
                                    | Segment Style
                                    |--------------------------------------------------------------------------
                                    |
                                    | Jika dua data bersebelahan:
                                    |
                                    | Pre-Test → Unit 1
                                    |
                                    | garis dibuat solid.
                                    |
                                    | Jika ada stage kosong:
                                    |
                                    | Pre-Test → (Unit 1 kosong) → Unit 2
                                    |
                                    | garis dibuat dashed / putus-putus untuk menunjukkan
                                    | bahwa terdapat data yang belum tersedia.
                                    |
                                    */

                                    segment: {

                                        borderDash: function (context) {

                                            const fromIndex =
                                                context.p0DataIndex;

                                            const toIndex =
                                                context.p1DataIndex;

                                            const skippedStages =
                                                toIndex - fromIndex;

                                            /*
                                             * > 1 berarti ada satu atau lebih stage kosong.
                                             */
                                            if (skippedStages > 1) {

                                                return [
                                                    7,
                                                    6
                                                ];
                                            }

                                            /*
                                             * Data berurutan = garis normal.
                                             */
                                            return undefined;
                                        },

                                    },

                                };

                            }
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Create Chart
                    |--------------------------------------------------------------------------
                    */

                    const chart =
                        new window.Chart(
                            canvas.getContext('2d'),
                            {

                                type: 'line',

                                data: {
                                    labels: labels,
                                    datasets: datasets,
                                },

                                options: {

                                    responsive: true,

                                    maintainAspectRatio:
                                        false,

                                    normalized: true,

                                    interaction: {
                                        mode: 'index',
                                        intersect: false,
                                    },

                                    animation: {
                                        duration: 400,
                                    },

                                    layout: {
                                        padding: {
                                            top: 8,
                                            right: 8,
                                            bottom: 2,
                                            left: 0,
                                        },
                                    },

                                    plugins: {

                                        legend: {
                                            display: false,
                                        },

                                        tooltip: {

                                            enabled: true,

                                            usePointStyle: true,

                                            backgroundColor:
                                                theme.card,

                                            titleColor:
                                                theme.textMain,

                                            bodyColor:
                                                theme.text,

                                            borderColor:
                                                theme.border,

                                            borderWidth: 1,

                                            padding: 11,

                                            cornerRadius: 10,

                                            displayColors: true,

                                            callbacks: {

                                                title:
                                                    function (
                                                        items
                                                    ) {

                                                        if (
                                                            !items.length
                                                        ) {
                                                            return '';
                                                        }

                                                        return (
                                                            labels[
                                                                items[0]
                                                                    .dataIndex
                                                            ]
                                                            ?? ''
                                                        );
                                                    },

                                                label:
                                                    function (
                                                        context
                                                    ) {

                                                        if (
                                                            context.raw
                                                            ===
                                                            null
                                                            ||
                                                            context.raw
                                                            ===
                                                            undefined
                                                        ) {
                                                            return null;
                                                        }

                                                        return (
                                                            context
                                                                .dataset
                                                                .label
                                                            +
                                                            ': '
                                                            +
                                                            context.raw
                                                            +
                                                            ' / 100'
                                                        );
                                                    },

                                            },

                                            filter:
                                                function (
                                                    item
                                                ) {

                                                    return (
                                                        item.raw
                                                        !==
                                                        null
                                                        &&
                                                        item.raw
                                                        !==
                                                        undefined
                                                    );
                                                },

                                        },

                                    },

                                    scales: {

                                        x: {

                                            offset: false,

                                            grid: {
                                                display: false,
                                            },

                                            border: {
                                                color:
                                                    theme.border,
                                            },

                                            ticks: {

                                                color:
                                                    theme.text,

                                                maxRotation: 0,

                                                minRotation: 0,

                                                autoSkip: false,

                                                padding: 9,

                                                font: {
                                                    size: 10,
                                                    weight: '600',
                                                },

                                            },

                                        },


                                        y: {

                                            beginAtZero: true,

                                            min: 0,

                                            max: 100,

                                            ticks: {

                                                stepSize: 20,

                                                color:
                                                    theme.text,

                                                padding: 9,

                                                font: {
                                                    size: 10,
                                                    weight: '600',
                                                },

                                            },

                                            grid: {

                                                color:
                                                    theme.border,

                                                drawTicks:
                                                    false,

                                            },

                                            border: {
                                                display: false,
                                            },

                                        },

                                    },

                                },

                            }
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Skill Filters
                    |--------------------------------------------------------------------------
                    */

                    const filterButtons =
                        document.querySelectorAll(
                            '[data-trend-filter]'
                        );


                    filterButtons.forEach(
                        function (button) {

                            button.addEventListener(
                                'click',
                                function () {

                                    const selected =
                                        this.dataset
                                            .trendFilter;


                                    filterButtons
                                        .forEach(
                                            function (
                                                item
                                            ) {

                                                item
                                                    .classList
                                                    .remove(
                                                        'active'
                                                    );

                                            }
                                        );


                                    this.classList.add(
                                        'active'
                                    );


                                    chart
                                        .data
                                        .datasets
                                        .forEach(
                                            function (
                                                dataset
                                            ) {

                                                dataset.hidden =
                                                    (
                                                        selected
                                                        !==
                                                        'all'
                                                    )
                                                    &&
                                                    (
                                                        dataset
                                                            .skill
                                                        !==
                                                        selected
                                                    );

                                            }
                                        );


                                    chart.update();

                                }
                            );

                        }
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Dark / Light Mode
                    |--------------------------------------------------------------------------
                    */

                    function updateChartTheme() {

                        const currentTheme =
                            getTheme();


                        chart.options
                            .scales
                            .x
                            .ticks
                            .color =
                            currentTheme.text;


                        chart.options
                            .scales
                            .x
                            .border
                            .color =
                            currentTheme.border;


                        chart.options
                            .scales
                            .y
                            .ticks
                            .color =
                            currentTheme.text;


                        chart.options
                            .scales
                            .y
                            .grid
                            .color =
                            currentTheme.border;


                        chart.options
                            .plugins
                            .tooltip
                            .backgroundColor =
                            currentTheme.card;


                        chart.options
                            .plugins
                            .tooltip
                            .titleColor =
                            currentTheme.textMain;


                        chart.options
                            .plugins
                            .tooltip
                            .bodyColor =
                            currentTheme.text;


                        chart.options
                            .plugins
                            .tooltip
                            .borderColor =
                            currentTheme.border;


                        chart
                            .data
                            .datasets
                            .forEach(
                                function (
                                    dataset
                                ) {

                                    dataset
                                        .pointBorderColor =
                                        currentTheme.card;

                                    dataset
                                        .pointHoverBorderColor =
                                        currentTheme.card;

                                }
                            );


                        chart.update('none');

                    }


                    const themeObserver =
                        new MutationObserver(
                            updateChartTheme
                        );


                    themeObserver.observe(
                        document.documentElement,
                        {
                            attributes: true,

                            attributeFilter: [
                                'class',
                                'data-theme',
                            ],
                        }
                    );


                    if (document.body) {

                        themeObserver.observe(
                            document.body,
                            {
                                attributes: true,

                                attributeFilter: [
                                    'class',
                                    'data-theme',
                                ],
                            }
                        );

                    }


                    console.log(
                        'SpeakVerse Progress Chart loaded',
                        {
                            labels:
                                labels,

                            datasets:
                                trendData,
                        }
                    );

                }


                /*
                 * Support script yang ditempatkan sebelum atau
                 * sesudah DOMContentLoaded.
                 */
                if (
                    document.readyState
                    ===
                    'loading'
                ) {

                    document.addEventListener(
                        'DOMContentLoaded',
                        initialiseProgressChart
                    );

                } else {

                    initialiseProgressChart();

                }

            })();
        </script>

    @endif

</x-app-layout>