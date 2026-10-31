<x-app-layout>
    @php
        $questionCount = $questions->count();
    @endphp

    <style>
        .reading-quiz-page {
            --card: #ffffff;
            --card-soft: #f8fafc;
            --card-muted: #eef2f7;
            --text: #0f172a;
            --text-soft: #334155;
            --text-muted: #64748b;
            --border: #dbe4ef;
            --border-strong: #cbd5e1;
            --accent: #0891b2;
            --accent-strong: #2563eb;
            --accent-soft: #ecfeff;
            --success: #059669;
            --success-soft: #ecfdf5;
            --danger: #dc2626;
            --danger-soft: #fef2f2;
            --shadow: 0 12px 30px rgba(15, 23, 42, 0.07);
            --shadow-soft: 0 5px 16px rgba(15, 23, 42, 0.05);

            width: min(100%, 1100px);
            margin-inline: auto;
            color: var(--text);
        }

        html.dark .reading-quiz-page,
        body.dark .reading-quiz-page,
        .dark .reading-quiz-page,
        html[data-theme="dark"] .reading-quiz-page,
        body[data-theme="dark"] .reading-quiz-page {
            --card: #0f172a;
            --card-soft: #111c31;
            --card-muted: #172033;
            --text: #f8fafc;
            --text-soft: #d8e1ed;
            --text-muted: #9fb0c5;
            --border: #26364d;
            --border-strong: #354861;
            --accent: #22d3ee;
            --accent-strong: #60a5fa;
            --accent-soft: #0b2c3a;
            --success: #34d399;
            --success-soft: #0b2e29;
            --danger: #f87171;
            --danger-soft: #371820;
            --shadow: 0 16px 36px rgba(0, 0, 0, 0.3);
            --shadow-soft: 0 7px 20px rgba(0, 0, 0, 0.22);
        }

        .reading-quiz-page,
        .reading-quiz-page * {
            box-sizing: border-box;
        }

        .reading-quiz-page [hidden],
        .reading-quiz-page .question-slide[hidden],
        .reading-quiz-page #nextBtn[hidden],
        .reading-quiz-page #submitBtn[hidden],
        .reading-quiz-page #resultSection[hidden] {
            display: none !important;
        }

        .reading-quiz-page .rq-stack {
            display: grid;
            gap: 14px;
        }

        .reading-quiz-page .rq-card {
            border: 1px solid var(--border);
            border-radius: 20px;
            background: var(--card);
            box-shadow: var(--shadow-soft);
        }

        /*
        |--------------------------------------------------------------------------
        | Compact Overview
        |--------------------------------------------------------------------------
        */

        .reading-quiz-page .rq-overview {
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            padding: 16px 18px;
        }

        .reading-quiz-page .rq-overview::after {
            content: "";
            position: absolute;
            top: -95px;
            right: -75px;
            width: 180px;
            height: 180px;
            border-radius: 999px;
            background: rgba(34, 211, 238, 0.11);
            pointer-events: none;
        }

        .reading-quiz-page .rq-overview-main,
        .reading-quiz-page .rq-overview-meta {
            position: relative;
            z-index: 1;
        }

        .reading-quiz-page .rq-overview-main {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .reading-quiz-page .rq-overview-icon {
            display: inline-flex;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            border-radius: 13px;
            background: var(--accent-soft);
            color: var(--accent);
        }

        .reading-quiz-page .rq-overview-icon svg {
            width: 21px;
            height: 21px;
        }

        .reading-quiz-page .rq-overview-copy {
            min-width: 0;
        }

        .reading-quiz-page .rq-overview-kicker {
            margin: 0;
            color: var(--accent);
            font-size: 10px;
            line-height: 1.3;
            font-weight: 900;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        .reading-quiz-page .rq-overview-title {
            margin: 3px 0 0;
            overflow: hidden;
            color: var(--text);
            font-size: 21px;
            line-height: 1.25;
            font-weight: 900;
            letter-spacing: -0.025em;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .reading-quiz-page .rq-overview-description {
            margin: 3px 0 0;
            color: var(--text-muted);
            font-size: 11px;
            line-height: 1.45;
            font-weight: 650;
        }

        .reading-quiz-page .rq-overview-meta {
            display: flex;
            align-items: center;
            gap: 8px;
            flex: 0 0 auto;
        }

        .reading-quiz-page .rq-meta-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            min-height: 32px;
            padding: 7px 10px;
            border: 1px solid var(--border);
            border-radius: 11px;
            background: var(--card-soft);
            color: var(--text-soft);
            font-size: 10px;
            line-height: 1;
            font-weight: 850;
        }

        .reading-quiz-page .rq-meta-chip svg {
            width: 14px;
            height: 14px;
            color: var(--accent);
        }

        /*
        |--------------------------------------------------------------------------
        | Workspace
        |--------------------------------------------------------------------------
        */

        .reading-quiz-page .rq-workspace {
            overflow: hidden;
        }

        .reading-quiz-page .rq-workspace-head {
            padding: 13px 16px;
            border-bottom: 1px solid var(--border);
            background: var(--card-soft);
        }

        .reading-quiz-page .rq-progress-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
        }

        .reading-quiz-page .rq-progress-title {
            margin: 0;
            color: var(--text);
            font-size: 13px;
            line-height: 1.4;
            font-weight: 900;
        }

        .reading-quiz-page .rq-progress-description {
            margin: 2px 0 0;
            color: var(--text-muted);
            font-size: 11px;
            line-height: 1.45;
            font-weight: 650;
        }

        .reading-quiz-page .rq-progress-percent {
            flex: 0 0 auto;
            min-width: 45px;
            padding: 7px 8px;
            border-radius: 11px;
            background: var(--accent-soft);
            color: var(--accent);
            text-align: center;
            font-size: 11px;
            line-height: 1;
            font-weight: 900;
        }

        .reading-quiz-page .rq-progress-track {
            height: 6px;
            margin-top: 10px;
            overflow: hidden;
            border-radius: 999px;
            background: var(--card-muted);
        }

        .reading-quiz-page .rq-progress-bar {
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(
                90deg,
                var(--accent),
                var(--accent-strong)
            );
            transition: width 220ms ease;
        }

        /*
        |--------------------------------------------------------------------------
        | Question
        |--------------------------------------------------------------------------
        */

        .reading-quiz-page .rq-question-area {
            padding: 17px 18px 18px;
        }

        .reading-quiz-page .rq-question-heading {
            display: flex;
            align-items: flex-start;
            gap: 11px;
        }

        .reading-quiz-page .rq-question-number {
            display: inline-flex;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 11px;
            background: var(--accent-soft);
            color: var(--accent);
            font-size: 13px;
            line-height: 1;
            font-weight: 900;
        }

        .reading-quiz-page .rq-question-copy {
            min-width: 0;
            flex: 1;
        }

        .reading-quiz-page .rq-question-label {
            margin: 0;
            color: var(--accent);
            font-size: 9px;
            line-height: 1.3;
            font-weight: 900;
            letter-spacing: 0.11em;
            text-transform: uppercase;
        }

        .reading-quiz-page .rq-question-text {
            margin: 5px 0 0;
            color: var(--text);
            font-size: 16px;
            line-height: 1.58;
            font-weight: 780;
            letter-spacing: -0.01em;
        }

        .reading-quiz-page .rq-question-help {
            margin: 5px 0 0;
            color: var(--text-muted);
            font-size: 11px;
            line-height: 1.45;
            font-weight: 650;
        }

        .reading-quiz-page .rq-question-image {
            margin: 14px 0 0;
            overflow: hidden;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: var(--card-soft);
        }

        .reading-quiz-page .rq-question-image img {
            display: block;
            width: 100%;
            max-height: 290px;
            object-fit: contain;
            padding: 9px;
        }

        /*
        |--------------------------------------------------------------------------
        | Options without A/B/C/D/E
        |--------------------------------------------------------------------------
        */

        .reading-quiz-page .rq-options {
            display: grid;
            gap: 8px;
            margin-top: 14px;
        }

        .reading-quiz-page .rq-option {
            position: relative;
            display: grid;
            grid-template-columns: 24px minmax(0, 1fr) 20px;
            align-items: center;
            gap: 10px;
            min-height: 50px;
            padding: 10px 12px;
            border: 1px solid var(--border);
            border-radius: 13px;
            background: var(--card-soft);
            cursor: pointer;
            transition:
                transform 150ms ease,
                border-color 150ms ease,
                background-color 150ms ease,
                box-shadow 150ms ease;
        }

        .reading-quiz-page .rq-option:hover {
            transform: translateY(-1px);
            border-color: var(--accent);
            background: var(--accent-soft);
            box-shadow: var(--shadow-soft);
        }

        .reading-quiz-page .rq-option:focus-within {
            outline: 3px solid rgba(34, 211, 238, 0.2);
            outline-offset: 2px;
        }

        .reading-quiz-page .rq-option.is-selected,
        .reading-quiz-page .rq-option:has(.rq-option-input:checked) {
            border-color: var(--accent);
            background: var(--accent-soft);
            box-shadow: 0 0 0 1px rgba(34, 211, 238, 0.12);
        }

        .reading-quiz-page .rq-option-input {
            position: absolute;
            width: 1px;
            height: 1px;
            overflow: hidden;
            opacity: 0;
            pointer-events: none;
        }

        .reading-quiz-page .rq-radio-visual {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
            border: 2px solid var(--border-strong);
            border-radius: 999px;
            background: var(--card);
            transition:
                border-color 150ms ease,
                background-color 150ms ease;
        }

        .reading-quiz-page .rq-radio-dot {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: transparent;
            transform: scale(0);
            transition:
                transform 150ms ease,
                background-color 150ms ease;
        }

        .reading-quiz-page .rq-option.is-selected .rq-radio-visual,
        .reading-quiz-page .rq-option:has(.rq-option-input:checked) .rq-radio-visual {
            border-color: var(--accent);
            background: var(--accent);
        }

        .reading-quiz-page .rq-option.is-selected .rq-radio-dot,
        .reading-quiz-page .rq-option:has(.rq-option-input:checked) .rq-radio-dot {
            background: #ffffff;
            transform: scale(1);
        }

        .reading-quiz-page .rq-option-text {
            min-width: 0;
            color: var(--text-soft);
            font-size: 13.5px;
            line-height: 1.52;
            font-weight: 620;
        }

        .reading-quiz-page .rq-option.is-selected .rq-option-text,
        .reading-quiz-page .rq-option:has(.rq-option-input:checked) .rq-option-text {
            color: var(--text);
            font-weight: 750;
        }

        .reading-quiz-page .rq-option-check {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
            border-radius: 999px;
            color: transparent;
            transition:
                color 150ms ease,
                background-color 150ms ease;
        }

        .reading-quiz-page .rq-option-check svg {
            width: 14px;
            height: 14px;
        }

        .reading-quiz-page .rq-option.is-selected .rq-option-check,
        .reading-quiz-page .rq-option:has(.rq-option-input:checked) .rq-option-check {
            background: var(--accent);
            color: #ffffff;
        }

        .reading-quiz-page .rq-error {
            margin-top: 10px;
            padding: 10px 12px;
            border: 1px solid rgba(220, 38, 38, 0.24);
            border-radius: 11px;
            background: var(--danger-soft);
            color: var(--danger);
            font-size: 11px;
            line-height: 1.45;
            font-weight: 800;
        }

        /*
        |--------------------------------------------------------------------------
        | Footer Navigation
        |--------------------------------------------------------------------------
        */

        .reading-quiz-page .rq-workspace-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 12px 14px;
            border-top: 1px solid var(--border);
            background: var(--card-soft);
        }

        .reading-quiz-page .rq-footer-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .reading-quiz-page .rq-button {
            display: inline-flex;
            min-height: 44px;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 10px 15px;
            border: 1px solid transparent;
            border-radius: 12px;
            font-family: inherit;
            font-size: 12px;
            line-height: 1;
            font-weight: 900;
            text-decoration: none;
            cursor: pointer;
            transition:
                transform 150ms ease,
                border-color 150ms ease,
                background-color 150ms ease,
                opacity 150ms ease,
                box-shadow 150ms ease,
                filter 150ms ease;
        }

        .reading-quiz-page .rq-button svg {
            width: 16px;
            height: 16px;
        }

        .reading-quiz-page .rq-button:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: var(--shadow-soft);
        }

        .reading-quiz-page .rq-button:focus-visible {
            outline: 3px solid rgba(34, 211, 238, 0.22);
            outline-offset: 2px;
        }

        .reading-quiz-page .rq-button:disabled {
            cursor: not-allowed;
            opacity: 0.42;
            transform: none;
            box-shadow: none;
        }

        .reading-quiz-page .rq-button-secondary {
            border-color: var(--border);
            background: var(--card);
            color: var(--text-soft);
        }

        .reading-quiz-page .rq-button-secondary:hover:not(:disabled) {
            border-color: var(--border-strong);
            background: var(--card-muted);
        }

        .reading-quiz-page .rq-button-primary {
            min-width: 160px;
            background: linear-gradient(
                100deg,
                var(--accent),
                var(--accent-strong)
            );
            color: #ffffff;
        }

        .reading-quiz-page .rq-button-submit {
            min-width: 175px;
            background: linear-gradient(
                100deg,
                #059669,
                #0891b2
            );
            color: #ffffff;
        }

        /*
        |--------------------------------------------------------------------------
        | Result
        |--------------------------------------------------------------------------
        */

        .reading-quiz-page .rq-result {
            padding: 22px;
            text-align: center;
        }

        .reading-quiz-page .rq-result-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            height: 50px;
            border-radius: 15px;
            background: var(--success-soft);
            color: var(--success);
        }

        .reading-quiz-page .rq-result-icon svg {
            width: 25px;
            height: 25px;
        }

        .reading-quiz-page .rq-result-title {
            margin: 12px 0 0;
            color: var(--text);
            font-size: 22px;
            line-height: 1.25;
            font-weight: 900;
            letter-spacing: -0.025em;
        }

        .reading-quiz-page .rq-result-description {
            max-width: 500px;
            margin: 5px auto 0;
            color: var(--text-muted);
            font-size: 12px;
            line-height: 1.55;
            font-weight: 650;
        }

        .reading-quiz-page .rq-score {
            display: inline-flex;
            align-items: flex-end;
            gap: 5px;
            margin-top: 16px;
            padding: 13px 18px;
            border: 1px solid var(--border);
            border-radius: 15px;
            background: var(--card-soft);
        }

        .reading-quiz-page .rq-score-value {
            color: var(--accent);
            font-size: 44px;
            line-height: 0.95;
            font-weight: 900;
            letter-spacing: -0.045em;
        }

        .reading-quiz-page .rq-score-max {
            padding-bottom: 4px;
            color: var(--text-muted);
            font-size: 11px;
            font-weight: 850;
        }

        .reading-quiz-page .rq-result-actions {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 16px;
        }

        /*
        |--------------------------------------------------------------------------
        | Empty State
        |--------------------------------------------------------------------------
        */

        .reading-quiz-page .rq-empty {
            padding: 36px 20px;
            text-align: center;
        }

        .reading-quiz-page .rq-empty-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 54px;
            height: 54px;
            border-radius: 16px;
            background: var(--card-muted);
            color: var(--text-muted);
        }

        .reading-quiz-page .rq-empty-icon svg {
            width: 27px;
            height: 27px;
        }

        .reading-quiz-page .rq-empty-title {
            margin: 13px 0 0;
            color: var(--text);
            font-size: 19px;
            line-height: 1.3;
            font-weight: 900;
        }

        .reading-quiz-page .rq-empty-description {
            margin: 5px 0 0;
            color: var(--text-muted);
            font-size: 12px;
            line-height: 1.55;
            font-weight: 650;
        }

        /*
        |--------------------------------------------------------------------------
        | Responsive
        |--------------------------------------------------------------------------
        */

        @media (max-width: 700px) {
            .reading-quiz-page .rq-overview {
                align-items: flex-start;
                flex-direction: column;
                padding: 15px;
            }

            .reading-quiz-page .rq-overview-meta {
                width: 100%;
                flex-wrap: wrap;
            }

            .reading-quiz-page .rq-meta-chip {
                flex: 1 1 auto;
                justify-content: center;
            }

            .reading-quiz-page .rq-question-area {
                padding: 15px;
            }

            .reading-quiz-page .rq-workspace-footer {
                align-items: stretch;
                flex-direction: column-reverse;
            }

            .reading-quiz-page .rq-footer-group {
                width: 100%;
                flex-direction: column;
                align-items: stretch;
            }

            .reading-quiz-page .rq-button,
            .reading-quiz-page .rq-button-primary,
            .reading-quiz-page .rq-button-submit {
                width: 100%;
                min-width: 0;
            }

            .reading-quiz-page .rq-result-actions {
                flex-direction: column;
            }
        }

        @media (max-width: 430px) {
            .reading-quiz-page .rq-overview-title {
                font-size: 19px;
            }

            .reading-quiz-page .rq-question-text {
                font-size: 15px;
                line-height: 1.55;
            }

            .reading-quiz-page .rq-option {
                grid-template-columns: 22px minmax(0, 1fr) 18px;
                gap: 9px;
                min-height: 48px;
                padding: 9px 10px;
            }

            .reading-quiz-page .rq-radio-visual {
                width: 19px;
                height: 19px;
            }

            .reading-quiz-page .rq-option-text {
                font-size: 13px;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Modul 5 — In-Quiz Tutor UX
        |--------------------------------------------------------------------------
        */

        .reading-quiz-page .rq-check-row {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 14px;
        }

        .reading-quiz-page .rq-button-hint {
            border-color: var(--border);
            background: var(--card);
            color: var(--accent-strong);
        }

        .reading-quiz-page .rq-button-hint:hover:not(:disabled) {
            border-color: var(--accent);
            background: var(--accent-soft);
        }

        .reading-quiz-page .rq-checks-remaining {
            font-size: 11px;
            font-weight: 800;
            color: var(--text-muted);
        }

        .reading-quiz-page .rq-feedback {
            margin-top: 12px;
            padding: 11px 13px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 800;
            line-height: 1.5;
        }

        .reading-quiz-page .rq-feedback.is-correct {
            border: 1px solid rgba(5, 150, 105, 0.24);
            background: var(--success-soft);
            color: var(--success);
        }

        .reading-quiz-page .rq-feedback.is-incorrect {
            border: 1px solid rgba(220, 38, 38, 0.24);
            background: var(--danger-soft);
            color: var(--danger);
        }

        .reading-quiz-page .rq-feedback.is-locked {
            border: 1px solid var(--border);
            background: var(--card-muted);
            color: var(--text-muted);
        }

        .reading-quiz-page .rq-hint-panel {
            margin-top: 12px;
            padding: 13px 14px;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: var(--accent-soft);
        }

        .reading-quiz-page .rq-hint-label {
            margin: 0 0 8px;
            font-size: 11px;
            font-weight: 800;
            color: var(--accent-strong);
        }

        .reading-quiz-page .rq-hint-history {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .reading-quiz-page .rq-hint-item {
            padding: 9px 11px;
            border-radius: 10px;
            background: var(--card);
            border: 1px solid var(--border);
            font-size: 12px;
            line-height: 1.5;
            color: var(--text-soft);
        }

        .reading-quiz-page .rq-hint-item-level {
            display: inline-block;
            margin-bottom: 4px;
            padding: 2px 8px;
            border-radius: 999px;
            background: var(--accent-strong);
            color: #ffffff;
            font-size: 10px;
            font-weight: 900;
        }

        .reading-quiz-page .rq-hint-socratic {
            margin-top: 10px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .reading-quiz-page .rq-hint-socratic-input {
            width: 100%;
            padding: 9px 11px;
            border: 1px solid var(--border);
            border-radius: 10px;
            background: var(--card);
            color: var(--text);
            font-family: inherit;
            font-size: 12px;
            resize: vertical;
        }
    </style>

    <div class="reading-quiz-page">
        <div class="rq-stack">

            {{-- COMPACT OVERVIEW --}}
            <section class="rq-card rq-overview">
                <div class="rq-overview-main">
                    <span class="rq-overview-icon">
                        <svg
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                            </path>
                        </svg>
                    </span>

                    <div class="rq-overview-copy">
                        <p class="rq-overview-kicker">
                            Reading Quiz
                        </p>

                        <h1 class="rq-overview-title">
                            {{ $lesson->title }}
                        </h1>

                        <p class="rq-overview-description">
                            Choose the best answer based on the reading material.
                        </p>
                    </div>
                </div>

                <div class="rq-overview-meta">
                    <span class="rq-meta-chip">
                        <svg
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01">
                            </path>
                        </svg>

                        {{ $questionCount }}
                        {{ \Illuminate\Support\Str::plural('Question', $questionCount) }}
                    </span>
                </div>
            </section>

            @if ($questionCount > 0)
                <section
                    id="quizWorkspace"
                    class="rq-card rq-workspace">

                    {{-- WORKSPACE HEADER --}}
                    <header
                        id="quizProgressCard"
                        class="rq-workspace-head">
                        <div class="rq-progress-row">
                            <div>
                                <p
                                    id="questionCounter"
                                    class="rq-progress-title"
                                    aria-live="polite">
                                    Question 1 of {{ $questionCount }}
                                </p>

                                <p class="rq-progress-description">
                                    Select one answer before continuing.
                                </p>
                            </div>

                            <div class="rq-progress-percent">
                                <span id="progressPercent">
                                    {{ round(100 / $questionCount) }}
                                </span>%
                            </div>
                        </div>

                        <div class="rq-progress-track">
                            <div
                                id="progressBar"
                                class="rq-progress-bar"
                                style="width: {{ 100 / $questionCount }}%">
                            </div>
                        </div>
                    </header>

                    {{-- QUESTION FORM --}}
                    <form
                        id="quizForm"
                        novalidate>
                        @csrf

                        @foreach ($questions as $index => $question)
                            @php
                                $displayQuestion = preg_replace(
                                    '/^\s*(?:question\s*)?\d+\s*[\.\)\-:]\s*/iu',
                                    '',
                                    trim((string) $question->question)
                                );

                                $displayQuestion = $displayQuestion !== ''
                                    ? $displayQuestion
                                    : trim((string) $question->question);
                            @endphp

                            <article
                                class="question-slide rq-question-area"
                                data-step-index="{{ $index }}"
                                data-question-id="{{ $question->id }}"
                                @if ($index !== 0) hidden @endif>

                                <div class="rq-question-heading">
                                    <span class="rq-question-number">
                                        {{ $index + 1 }}
                                    </span>

                                    <div class="rq-question-copy">
                                        <p class="rq-question-label">
                                            Reading Question
                                        </p>

                                        <h2 class="rq-question-text">
                                            {{ $displayQuestion }}
                                        </h2>

                                        <p class="rq-question-help">
                                            Choose the most appropriate answer.
                                        </p>
                                    </div>
                                </div>

                                @if (!empty($question->image))
                                    <figure class="rq-question-image">
                                        <img
                                            src="{{ asset('storage/' . $question->image) }}"
                                            alt="Question {{ $index + 1 }} image"
                                            loading="{{ $index === 0 ? 'eager' : 'lazy' }}">
                                    </figure>
                                @endif

                                <div
                                    class="rq-options"
                                    data-answer-options>
                                    @foreach (['A', 'B', 'C', 'D', 'E'] as $option)
                                        @php
                                            $field =
                                                'option_' .
                                                strtolower($option);

                                            $optionValue =
                                                $question->$field;
                                        @endphp

                                        @if (!empty($optionValue))
                                            <label class="rq-option">
                                                <input
                                                    type="radio"
                                                    name="answers[{{ $question->id }}]"
                                                    value="{{ $option }}"
                                                    class="rq-option-input">

                                                <span class="rq-radio-visual">
                                                    <span class="rq-radio-dot"></span>
                                                </span>

                                                <span class="rq-option-text">
                                                    {{ $optionValue }}
                                                </span>

                                                <span class="rq-option-check">
                                                    <svg
                                                        fill="none"
                                                        stroke="currentColor"
                                                        viewBox="0 0 24 24"
                                                        aria-hidden="true">
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2.4"
                                                            d="M5 13l4 4L19 7">
                                                        </path>
                                                    </svg>
                                                </span>
                                            </label>
                                        @endif
                                    @endforeach
                                </div>

                                <div
                                    data-answer-error
                                    class="rq-error"
                                    hidden>
                                    Please select one answer before continuing.
                                </div>

                                {{-- MODUL 5 — In-Quiz Tutor UX --}}
                                <div class="rq-check-row" data-check-row>
                                    <button
                                        type="button"
                                        class="rq-button rq-button-primary"
                                        data-check-btn>
                                        Check
                                    </button>

                                    <button
                                        type="button"
                                        class="rq-button rq-button-hint"
                                        data-hint-btn>
                                        <svg
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                            aria-hidden="true">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z">
                                            </path>
                                        </svg>
                                        I need a hint
                                    </button>

                                    <span class="rq-checks-remaining" data-checks-remaining hidden></span>
                                </div>

                                <div class="rq-feedback" data-feedback hidden></div>

                                <div class="rq-hint-panel" data-hint-panel hidden>
                                    <p class="rq-hint-label" data-hint-error-label></p>
                                    <div class="rq-hint-history" data-hint-history></div>
                                    <div class="rq-hint-socratic" data-hint-socratic hidden>
                                        <textarea
                                            class="rq-hint-socratic-input"
                                            data-socratic-input
                                            rows="2"
                                            placeholder="Tulis jawaban singkatmu di sini..."></textarea>
                                        <button
                                            type="button"
                                            class="rq-button rq-button-secondary"
                                            data-socratic-submit>
                                            Kirim Jawaban
                                        </button>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </form>

                    {{-- NAVIGATION --}}
                    <footer
                        id="navigationButtons"
                        class="rq-workspace-footer">
                        <div class="rq-footer-group">
                            <a
                                href="{{ route('student.reading', $lesson) }}"
                                class="rq-button rq-button-secondary">
                                Back to Reading
                            </a>

                            <button
                                id="prevBtn"
                                type="button"
                                class="rq-button rq-button-secondary">
                                <svg
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M15 19l-7-7 7-7">
                                    </path>
                                </svg>

                                Previous
                            </button>
                        </div>

                        <div class="rq-footer-group">
                            <button
                                id="nextBtn"
                                type="button"
                                class="rq-button rq-button-primary">
                                Next Question

                                <svg
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 5l7 7-7 7">
                                    </path>
                                </svg>
                            </button>

                            <button
                                id="submitBtn"
                                type="button"
                                class="rq-button rq-button-submit"
                                hidden>
                                <svg
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M5 13l4 4L19 7">
                                    </path>
                                </svg>

                                <span id="submitLabel">
                                    Finish Quiz
                                </span>
                            </button>
                        </div>
                    </footer>
                </section>

                {{-- RESULT --}}
                <section
                    id="resultSection"
                    class="rq-card rq-result"
                    hidden>
                    <span class="rq-result-icon">
                        <svg
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2.4"
                                d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                    </span>

                    <h2 class="rq-result-title">
                        Quiz Completed
                    </h2>

                    <p class="rq-result-description" id="finalMessage">
                        Your reading result has been calculated and saved.
                    </p>

                    <div class="rq-score">
                        <span
                            id="finalScore"
                            class="rq-score-value">
                            0
                        </span>

                        <span class="rq-score-max">
                            / 100
                        </span>
                    </div>

                    <div class="rq-result-actions">
                        <a
                            id="reviewLink"
                            href="#"
                            class="rq-button rq-button-secondary"
                            hidden>
                            Lihat Pembahasan
                        </a>

                        <a
                            href="{{ route('student.reading', $lesson) }}"
                            class="rq-button rq-button-secondary">
                            Back to Reading
                        </a>

                        <a
                            href="{{ route('missions') }}"
                            class="rq-button rq-button-primary">
                            Back to Missions
                        </a>
                    </div>
                </section>
            @else
                <section class="rq-card rq-empty">
                    <span class="rq-empty-icon">
                        <svg
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                            </path>
                        </svg>
                    </span>

                    <h2 class="rq-empty-title">
                        No Questions Yet
                    </h2>

                    <p class="rq-empty-description">
                        The administrator has not added questions
                        for this reading lesson.
                    </p>

                    <a
                        href="{{ route('student.reading', $lesson) }}"
                        class="rq-button rq-button-primary"
                        style="margin-top: 16px;">
                        Back to Reading
                    </a>
                </section>
            @endif

        </div>
    </div>

    @if ($questionCount > 0)
        <script>
            document.addEventListener(
                'DOMContentLoaded',
                function () {
                    const totalQuestions = {{ $questionCount }};
                    const maxChecksPerQuestion = {{ (int) config('learning.scoring.max_checks_per_question', 3) }};

                    const checkUrl = @json(route('student.reading.check', $lesson));
                    const hintUrl = @json(route('student.reading.hint', $lesson));
                    const finishUrl = @json(route('student.reading.finish', $lesson));
                    const shownUrl = @json(route('student.reading.shown', $lesson));
                    const abandonUrl = @json(route('student.reading.abandon', $lesson));

                    // Modul 1 — Interaction Logger: sesi kuis ini
                    // (null kalau logging gagal dibuka server-side —
                    // lihat InteractionLogger). Kalau null, endpoint
                    // check/hint/finish tetap dipanggil, hanya tidak
                    // ada pencatatan learning_events untuk sesi ini.
                    const quizSessionId = @json($quizSessionId);

                    const csrfToken = @json(csrf_token());

                    const quizWorkspace = document.getElementById('quizWorkspace');
                    const progressCard = document.getElementById('quizProgressCard');
                    const counter = document.getElementById('questionCounter');
                    const progressPercent = document.getElementById('progressPercent');
                    const progressBar = document.getElementById('progressBar');
                    const prevBtn = document.getElementById('prevBtn');
                    const nextBtn = document.getElementById('nextBtn');
                    const submitBtn = document.getElementById('submitBtn');
                    const submitLabel = document.getElementById('submitLabel');
                    const resultSection = document.getElementById('resultSection');
                    const finalScore = document.getElementById('finalScore');
                    const finalMessage = document.getElementById('finalMessage');

                    const slides = Array.from(document.querySelectorAll('.question-slide'));

                    // Modul 5: state per soal (dikunci dari server —
                    // ini cuma cache di sisi klien untuk kontrol UI,
                    // server tetap yang menegakkan aturannya).
                    const state = {};
                    slides.forEach(function (slide) {
                        state[slide.dataset.questionId] = {
                            locked: false,
                            isCorrect: null,
                            checksUsed: 0,
                            checksRemaining: maxChecksPerQuestion,
                            socraticStep: 0
                        };
                    });

                    const questionShownAt = {};
                    let quizFinished = false;

                    function markQuestionShown(index) {
                        const slide = slides[index];
                        if (!slide) {
                            return;
                        }
                        const questionId = slide.dataset.questionId;
                        if (questionShownAt[questionId] === undefined) {
                            questionShownAt[questionId] = Date.now();

                            // Saran perbaikan Modul 1: beri tahu server
                            // "soal ini baru tampil" supaya response_ms
                            // nanti bisa dihitung dari jam SERVER, bukan
                            // dipercaya mentah dari klien. Fire-and-forget
                            // - kalau gagal, check() tetap jalan dengan
                            // fallback ke waktu klien (lihat responseMsFor).
                            if (quizSessionId) {
                                fetch(shownUrl, {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': csrfToken,
                                        'Accept': 'application/json',
                                        'Content-Type': 'application/json'
                                    },
                                    body: JSON.stringify({
                                        quiz_session_id: quizSessionId,
                                        question_id: Number(questionId)
                                    }),
                                    keepalive: true
                                }).catch(function (error) {
                                    console.warn('markShown gagal (tidak fatal):', error);
                                });
                            }
                        }
                    }

                    function responseMsFor(questionId) {
                        const shownAt = questionShownAt[questionId] ?? Date.now();
                        return Date.now() - shownAt;
                    }

                    // Saran perbaikan Modul 1: catat 'abandon' kalau siswa
                    // menutup tab / pindah halaman SEBELUM Finish. pagehide
                    // dipilih (bukan beforeunload) karena lebih andal di
                    // browser modern, termasuk saat berpindah ke aplikasi
                    // lain di HP. sendBeacon dipakai karena permintaan
                    // biasa (fetch) sering terputus saat halaman menutup.
                    window.addEventListener('pagehide', function () {
                        if (quizFinished || !quizSessionId) {
                            return;
                        }
                        const data = new URLSearchParams();
                        data.append('_token', csrfToken);
                        data.append('quiz_session_id', quizSessionId);
                        navigator.sendBeacon(abandonUrl, data);
                    });

                    let currentQuestion = 0;
                    let isChecking = false;
                    let isRequestingHint = false;
                    let isFinishing = false;

                    function getSelectedAnswer(slide) {
                        return slide.querySelector('.rq-option-input:checked');
                    }

                    function updateOptionStates(slide) {
                        const options = slide.querySelectorAll('.rq-option');
                        options.forEach(function (option) {
                            const input = option.querySelector('.rq-option-input');
                            option.classList.toggle('is-selected', Boolean(input && input.checked));
                        });
                    }

                    function setOptionsDisabled(slide, disabled) {
                        slide.querySelectorAll('.rq-option-input').forEach(function (input) {
                            input.disabled = disabled;
                        });
                    }

                    function clearError(slide) {
                        const error = slide.querySelector('[data-answer-error]');
                        if (error) {
                            error.hidden = true;
                        }
                    }

                    function showError(slide) {
                        const error = slide.querySelector('[data-answer-error]');
                        if (error) {
                            error.hidden = false;
                        }
                        const firstInput = slide.querySelector('.rq-option-input:not(:disabled)');
                        if (firstInput) {
                            firstInput.focus();
                        }
                    }

                    function scrollToWorkspace() {
                        if (!progressCard) {
                            return;
                        }
                        const navbarOffset = 104;
                        const target = progressCard.getBoundingClientRect().top + window.scrollY - navbarOffset;
                        window.scrollTo({ top: Math.max(0, target), behavior: 'smooth' });
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Modul 5 — render bantuan visual per soal (feedback, tombol Check/Hint,
                    | sisa percobaan) berdasarkan state[] saat ini.
                    |--------------------------------------------------------------------------
                    */
                    function renderQuestionState(slide) {
                        const questionId = slide.dataset.questionId;
                        const qState = state[questionId];

                        const checkBtn = slide.querySelector('[data-check-btn]');
                        const hintBtn = slide.querySelector('[data-hint-btn]');
                        const checksRemainingEl = slide.querySelector('[data-checks-remaining]');
                        const feedbackEl = slide.querySelector('[data-feedback]');

                        setOptionsDisabled(slide, qState.locked);

                        if (qState.checksUsed === 0) {
                            checkBtn.hidden = false;
                            checkBtn.disabled = false;
                            checkBtn.textContent = 'Check';
                            checksRemainingEl.hidden = true;
                            feedbackEl.hidden = true;
                            hintBtn.hidden = false;
                            return;
                        }

                        checksRemainingEl.hidden = false;
                        checksRemainingEl.textContent = qState.checksRemaining > 0
                            ? qState.checksRemaining + ' check' + (qState.checksRemaining === 1 ? '' : 's') + ' left'
                            : 'No checks left';

                        feedbackEl.hidden = false;

                        if (qState.isCorrect) {
                            feedbackEl.className = 'rq-feedback is-correct';
                            feedbackEl.textContent = 'Correct! This question is locked in.';
                            checkBtn.hidden = true;
                            hintBtn.hidden = true;
                        } else if (qState.locked) {
                            feedbackEl.className = 'rq-feedback is-locked';
                            feedbackEl.textContent = "You've used all your checks for this question. You can still read hints to understand it, then move on.";
                            checkBtn.hidden = true;
                            hintBtn.hidden = false;
                        } else {
                            feedbackEl.className = 'rq-feedback is-incorrect';
                            feedbackEl.textContent = 'Not quite — try again, or ask for a hint.';
                            checkBtn.hidden = false;
                            checkBtn.disabled = false;
                            checkBtn.textContent = 'Check Again';
                            hintBtn.hidden = false;
                        }
                    }

                    function renderQuestion(shouldScroll = false) {
                        slides.forEach(function (slide, index) {
                            slide.hidden = index !== currentQuestion;
                        });

                        markQuestionShown(currentQuestion);

                        const slide = slides[currentQuestion];
                        const displayedQuestion = currentQuestion + 1;
                        const percentage = Math.round((displayedQuestion / totalQuestions) * 100);

                        counter.textContent = 'Question ' + displayedQuestion + ' of ' + totalQuestions;
                        progressPercent.textContent = percentage;
                        progressBar.style.width = percentage + '%';

                        prevBtn.disabled = currentQuestion === 0;

                        const isLastQuestion = currentQuestion === totalQuestions - 1;
                        const isLocked = state[slide.dataset.questionId].locked;

                        nextBtn.hidden = isLastQuestion;
                        nextBtn.disabled = !isLocked;

                        submitBtn.hidden = !isLastQuestion;
                        submitBtn.disabled = !isLocked;

                        updateOptionStates(slide);
                        renderQuestionState(slide);

                        if (shouldScroll) {
                            scrollToWorkspace();
                        }
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Modul 5 — Check
                    |--------------------------------------------------------------------------
                    */
                    async function performCheck(slide) {
                        if (isChecking) {
                            return;
                        }

                        const questionId = slide.dataset.questionId;
                        const selected = getSelectedAnswer(slide);

                        if (!selected) {
                            showError(slide);
                            return;
                        }
                        clearError(slide);

                        const checkBtn = slide.querySelector('[data-check-btn]');
                        isChecking = true;
                        checkBtn.disabled = true;
                        checkBtn.textContent = 'Checking...';

                        try {
                            const response = await fetch(checkUrl, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken,
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    quiz_session_id: quizSessionId,
                                    question_id: Number(questionId),
                                    selected_answer: selected.value,
                                    response_ms: responseMsFor(questionId)
                                })
                            });

                            const result = await response.json();

                            if (!response.ok) {
                                throw new Error(result.message || 'Could not check this answer.');
                            }

                            state[questionId] = {
                                locked: Boolean(result.locked),
                                isCorrect: Boolean(result.is_correct),
                                checksUsed: result.checks_used,
                                checksRemaining: result.checks_remaining,
                                socraticStep: state[questionId].socraticStep
                            };

                            renderQuestionState(slide);

                            const isLastQuestion = currentQuestion === totalQuestions - 1;
                            if (state[questionId].locked) {
                                if (isLastQuestion) {
                                    submitBtn.disabled = false;
                                } else {
                                    nextBtn.disabled = false;
                                }
                            }
                        } catch (error) {
                            console.error('Reading check error:', error);
                            alert(error.message || 'Could not check this answer. Please try again.');
                            const qState = state[questionId];
                            checkBtn.disabled = false;
                            checkBtn.textContent = qState.checksUsed > 0 ? 'Check Again' : 'Check';
                        } finally {
                            isChecking = false;
                        }
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Modul 5 — Hint (termasuk langkah Socratic satu-per-satu)
                    |--------------------------------------------------------------------------
                    */
                    function appendHintHistoryItem(slide, levelLabel, text) {
                        const history = slide.querySelector('[data-hint-history]');
                        const item = document.createElement('div');
                        item.className = 'rq-hint-item';

                        const badge = document.createElement('span');
                        badge.className = 'rq-hint-item-level';
                        badge.textContent = levelLabel;

                        const body = document.createElement('div');
                        body.textContent = text;

                        item.appendChild(badge);
                        item.appendChild(body);
                        history.appendChild(item);
                    }

                    async function requestHint(slide, socraticAnswer, socraticStep) {
                        if (isRequestingHint) {
                            return;
                        }

                        const questionId = slide.dataset.questionId;
                        const hintBtn = slide.querySelector('[data-hint-btn]');
                        const hintPanel = slide.querySelector('[data-hint-panel]');
                        const hintErrorLabel = slide.querySelector('[data-hint-error-label]');
                        const hintSocratic = slide.querySelector('[data-hint-socratic]');
                        const socraticInput = slide.querySelector('[data-socratic-input]');
                        const socraticSubmitBtn = slide.querySelector('[data-socratic-submit]');

                        isRequestingHint = true;
                        hintBtn.disabled = true;
                        socraticSubmitBtn.disabled = true;

                        try {
                            const payload = {
                                quiz_session_id: quizSessionId,
                                question_id: Number(questionId)
                            };
                            if (socraticAnswer !== undefined) {
                                payload.socratic_answer = socraticAnswer;
                                payload.socratic_step = socraticStep;
                            }

                            const response = await fetch(hintUrl, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken,
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify(payload)
                            });

                            const result = await response.json();

                            if (!response.ok) {
                                throw new Error(result.message || 'Could not load a hint right now.');
                            }

                            hintPanel.hidden = false;
                            hintErrorLabel.textContent = result.error_hint_label || '';
                            hintErrorLabel.hidden = !result.error_hint_label;

                            if (result.socratic_complete === true) {
                                appendHintHistoryItem(
                                    slide,
                                    'L3',
                                    "You've worked through all the guiding questions for this hint level. Try selecting your answer again with what you've figured out!"
                                );
                                hintSocratic.hidden = true;
                            } else if (result.socratic_question) {
                                appendHintHistoryItem(slide, 'L3 · Q' + (result.socratic_step + 1), result.socratic_question);
                                state[questionId].socraticStep = result.socratic_step;
                                hintSocratic.hidden = false;
                                socraticInput.value = '';
                                socraticInput.focus();
                            } else if (result.hint_text) {
                                appendHintHistoryItem(slide, 'L' + result.level, result.hint_text);
                                hintSocratic.hidden = true;
                            }
                        } catch (error) {
                            console.error('Reading hint error:', error);
                            alert(error.message || 'Could not load a hint right now. Please try again.');
                        } finally {
                            isRequestingHint = false;
                            hintBtn.disabled = false;
                            socraticSubmitBtn.disabled = false;
                        }
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Wire up per-slide controls
                    |--------------------------------------------------------------------------
                    */
                    slides.forEach(function (slide) {
                        const inputs = slide.querySelectorAll('.rq-option-input');
                        inputs.forEach(function (input) {
                            input.addEventListener('change', function () {
                                updateOptionStates(slide);
                                clearError(slide);
                            });
                        });
                        updateOptionStates(slide);

                        const checkBtn = slide.querySelector('[data-check-btn]');
                        checkBtn.addEventListener('click', function () {
                            performCheck(slide);
                        });

                        const hintBtn = slide.querySelector('[data-hint-btn]');
                        hintBtn.addEventListener('click', function () {
                            requestHint(slide);
                        });

                        const socraticSubmitBtn = slide.querySelector('[data-socratic-submit]');
                        socraticSubmitBtn.addEventListener('click', function () {
                            const socraticInput = slide.querySelector('[data-socratic-input]');
                            const answer = socraticInput.value.trim();
                            if (answer === '') {
                                socraticInput.focus();
                                return;
                            }
                            const questionId = slide.dataset.questionId;
                            requestHint(slide, answer, state[questionId].socraticStep);
                        });
                    });

                    nextBtn.addEventListener('click', function () {
                        const slide = slides[currentQuestion];
                        if (!state[slide.dataset.questionId].locked) {
                            return;
                        }
                        if (currentQuestion < totalQuestions - 1) {
                            currentQuestion++;
                            renderQuestion(true);
                        }
                    });

                    prevBtn.addEventListener('click', function () {
                        if (currentQuestion > 0) {
                            currentQuestion--;
                            renderQuestion(true);
                        }
                    });

                    /*
                    |--------------------------------------------------------------------------
                    | Modul 5 — Finish
                    |--------------------------------------------------------------------------
                    */
                    submitBtn.addEventListener('click', async function () {
                        if (isFinishing) {
                            return;
                        }
                        const lastSlide = slides[totalQuestions - 1];
                        if (!state[lastSlide.dataset.questionId].locked) {
                            return;
                        }

                        isFinishing = true;
                        submitBtn.disabled = true;
                        submitLabel.textContent = 'Saving result...';

                        try {
                            const response = await fetch(finishUrl, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken,
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    quiz_session_id: quizSessionId
                                })
                            });

                            const result = await response.json();

                            if (!response.ok || !result.success) {
                                throw new Error(result.message || 'The result could not be saved.');
                            }

                            finalScore.textContent = result.score ?? 0;
                            finalMessage.textContent = result.message || 'Your reading result has been calculated and saved.';

                            // Saran perbaikan Modul 1: kuis selesai secara
                            // sah — pagehide setelah ini TIDAK dianggap
                            // 'abandon' lagi.
                            quizFinished = true;

                            // Saran perbaikan Modul 5 — Halaman Pembahasan.
                            const reviewLink = document.getElementById('reviewLink');
                            if (result.review_url) {
                                reviewLink.href = result.review_url;
                                reviewLink.hidden = false;
                            }

                            quizWorkspace.hidden = true;
                            resultSection.hidden = false;

                            const navbarOffset = 104;
                            const target = resultSection.getBoundingClientRect().top + window.scrollY - navbarOffset;
                            window.scrollTo({ top: Math.max(0, target), behavior: 'smooth' });
                        } catch (error) {
                            console.error('Reading finish error:', error);
                            alert(error.message || 'The result could not be saved. Please try again.');
                            isFinishing = false;
                            submitBtn.disabled = false;
                            submitLabel.textContent = 'Finish Quiz';
                        }
                    });

                    renderQuestion(false);
                }
            );
        </script>
    @endif
</x-app-layout>