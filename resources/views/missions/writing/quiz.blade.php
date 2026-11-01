<x-app-layout>
    @php
        $question = $material->questions->first();

        $displayQuestion = $question
            ? preg_replace(
                '/^\s*(?:question\s*)?\d+\s*[\.\)\-:]\s*/iu',
                '',
                trim((string) $question->question)
            )
            : null;

        if ($question && $displayQuestion === '') {
            $displayQuestion = trim((string) $question->question);
        }
    @endphp

    <style>
        .writing-quiz-page {
            --card: #ffffff;
            --card-soft: #f8fafc;
            --card-muted: #eef2f7;
            --text: #0f172a;
            --text-soft: #334155;
            --text-muted: #64748b;
            --border: #dbe4ef;
            --border-strong: #cbd5e1;
            --accent: #059669;
            --accent-strong: #0891b2;
            --accent-soft: #ecfdf5;
            --accent-soft-2: #ecfeff;
            --danger: #dc2626;
            --danger-soft: #fef2f2;
            --success: #059669;
            --success-soft: #ecfdf5;
            --shadow: 0 14px 34px rgba(15, 23, 42, 0.08);
            --shadow-soft: 0 6px 18px rgba(15, 23, 42, 0.06);

            width: min(100%, 1160px);
            margin-inline: auto;
            color: var(--text);
        }

        html.dark .writing-quiz-page,
        body.dark .writing-quiz-page,
        .dark .writing-quiz-page,
        html[data-theme="dark"] .writing-quiz-page,
        body[data-theme="dark"] .writing-quiz-page {
            --card: #0f172a;
            --card-soft: #111c31;
            --card-muted: #172033;
            --text: #f8fafc;
            --text-soft: #d7e0ec;
            --text-muted: #9fb0c5;
            --border: #26364d;
            --border-strong: #33455f;
            --accent: #34d399;
            --accent-strong: #22d3ee;
            --accent-soft: #0b2e29;
            --accent-soft-2: #0b2c3a;
            --danger: #f87171;
            --danger-soft: #371820;
            --success: #34d399;
            --success-soft: #0b2e29;
            --shadow: 0 18px 42px rgba(0, 0, 0, 0.34);
            --shadow-soft: 0 8px 22px rgba(0, 0, 0, 0.24);
        }

        .writing-quiz-page,
        .writing-quiz-page * {
            box-sizing: border-box;
        }

        .writing-quiz-page [hidden],
        .writing-quiz-page #writingWorkspace[hidden],
        .writing-quiz-page #resultSection[hidden],
        .writing-quiz-page #loadingModal[hidden] {
            display: none !important;
        }

        .writing-quiz-page .wq-stack {
            display: grid;
            gap: 14px;
        }

        .writing-quiz-page .wq-card {
            border: 1px solid var(--border);
            border-radius: 20px;
            background: var(--card);
            box-shadow: var(--shadow-soft);
        }

        /*
        |--------------------------------------------------------------------------
        | Compact Header
        |--------------------------------------------------------------------------
        */

        .writing-quiz-page .wq-overview {
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            padding: 16px 18px;
        }

        .writing-quiz-page .wq-overview::after {
            content: "";
            position: absolute;
            top: -105px;
            right: -80px;
            width: 190px;
            height: 190px;
            border-radius: 999px;
            background: rgba(52, 211, 153, 0.12);
            pointer-events: none;
        }

        .writing-quiz-page .wq-overview-main,
        .writing-quiz-page .wq-overview-meta {
            position: relative;
            z-index: 1;
        }

        .writing-quiz-page .wq-overview-main {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .writing-quiz-page .wq-overview-icon {
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

        .writing-quiz-page .wq-overview-icon svg {
            width: 21px;
            height: 21px;
        }

        .writing-quiz-page .wq-overview-copy {
            min-width: 0;
        }

        .writing-quiz-page .wq-kicker {
            margin: 0;
            color: var(--accent);
            font-size: 10px;
            line-height: 1.3;
            font-weight: 900;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        .writing-quiz-page .wq-title {
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

        .writing-quiz-page .wq-description {
            margin: 3px 0 0;
            color: var(--text-muted);
            font-size: 11px;
            line-height: 1.45;
            font-weight: 650;
        }

        .writing-quiz-page .wq-overview-meta {
            flex: 0 0 auto;
        }

        .writing-quiz-page .wq-status {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            min-height: 34px;
            padding: 8px 11px;
            border: 1px solid rgba(5, 150, 105, 0.22);
            border-radius: 12px;
            background: var(--success-soft);
            color: var(--success);
            font-size: 11px;
            line-height: 1;
            font-weight: 900;
        }

        .writing-quiz-page .wq-status-dot {
            width: 7px;
            height: 7px;
            border-radius: 999px;
            background: currentColor;
            box-shadow: 0 0 0 4px rgba(52, 211, 153, 0.12);
        }

        /*
        |--------------------------------------------------------------------------
        | Main Workspace
        |--------------------------------------------------------------------------
        */

        .writing-quiz-page .wq-workspace {
            display: grid;
            grid-template-columns: minmax(0, 0.92fr) minmax(420px, 1.08fr);
            gap: 14px;
            align-items: stretch;
        }

        .writing-quiz-page .wq-panel {
            display: flex;
            min-width: 0;
            height: 100%;
            flex-direction: column;
            overflow: hidden;
            border: 1px solid var(--border);
            border-radius: 20px;
            background: var(--card);
            box-shadow: var(--shadow);
        }

        .writing-quiz-page .wq-panel-header {
            display: flex;
            align-items: center;
            gap: 11px;
            min-height: 62px;
            padding: 13px 15px;
            border-bottom: 1px solid var(--border);
            background: var(--card-soft);
        }

        .writing-quiz-page .wq-panel-icon {
            display: inline-flex;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 11px;
            background: var(--accent-soft);
            color: var(--accent);
        }

        .writing-quiz-page .wq-panel-icon svg {
            width: 18px;
            height: 18px;
        }

        .writing-quiz-page .wq-panel-heading {
            min-width: 0;
        }

        .writing-quiz-page .wq-panel-title {
            margin: 0;
            color: var(--text);
            font-size: 14px;
            line-height: 1.35;
            font-weight: 900;
        }

        .writing-quiz-page .wq-panel-subtitle {
            margin: 2px 0 0;
            color: var(--text-muted);
            font-size: 10.5px;
            line-height: 1.45;
            font-weight: 650;
        }

        /*
        |--------------------------------------------------------------------------
        | Question Panel
        |--------------------------------------------------------------------------
        */

        .writing-quiz-page .wq-question-body {
            padding: 15px;
        }

        .writing-quiz-page .wq-question-box {
            padding: 13px 14px;
            border: 1px solid rgba(5, 150, 105, 0.24);
            border-radius: 14px;
            background: var(--accent-soft);
        }

        .writing-quiz-page .wq-question-label {
            margin: 0;
            color: var(--accent);
            font-size: 9px;
            line-height: 1.3;
            font-weight: 900;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        .writing-quiz-page .wq-question-text {
            margin: 5px 0 0;
            color: var(--text);
            font-size: 13.5px;
            line-height: 1.65;
            font-weight: 680;
        }

        .writing-quiz-page .wq-reference {
            margin: 13px 0 0;
            overflow: hidden;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: var(--card-soft);
        }

        .writing-quiz-page .wq-reference-link {
            display: block;
            text-decoration: none;
        }

        .writing-quiz-page .wq-reference img {
            display: block;
            width: 100%;
            max-height: 360px;
            object-fit: contain;
            padding: 9px;
        }

        .writing-quiz-page .wq-reference-caption {
            padding: 8px 11px;
            border-top: 1px solid var(--border);
            color: var(--text-muted);
            font-size: 10px;
            line-height: 1.45;
            font-weight: 650;
            text-align: center;
        }

        /*
        |--------------------------------------------------------------------------
        | Answer Panel
        |--------------------------------------------------------------------------
        */

        .writing-quiz-page .wq-answer-body {
            display: flex;
            min-height: 0;
            flex: 1;
            padding: 15px;
        }

        .writing-quiz-page .wq-answer-body form {
            display: flex;
            min-width: 0;
            min-height: 0;
            flex: 1;
            flex-direction: column;
        }

        .writing-quiz-page .wq-answer-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 8px;
        }

        .writing-quiz-page .wq-answer-label {
            color: var(--text);
            font-size: 12px;
            line-height: 1.4;
            font-weight: 900;
        }

        .writing-quiz-page .wq-counter {
            color: var(--text-muted);
            font-size: 10.5px;
            line-height: 1.4;
            font-weight: 750;
        }

        .writing-quiz-page .wq-textarea {
            display: block;
            width: 100%;
            min-height: 365px;
            flex: 1;
            resize: vertical;
            padding: 13px 14px;
            border: 1px solid var(--border-strong);
            border-radius: 14px;
            background: var(--card-soft);
            color: var(--text);
            font: inherit;
            font-size: 13.5px;
            line-height: 1.72;
            outline: none;
            transition:
                border-color 150ms ease,
                box-shadow 150ms ease,
                background-color 150ms ease;
        }

        .writing-quiz-page .wq-textarea::placeholder {
            color: var(--text-muted);
        }

        .writing-quiz-page .wq-textarea:hover {
            border-color: var(--border-strong);
        }

        .writing-quiz-page .wq-textarea:focus {
            border-color: var(--accent);
            background: var(--card);
            box-shadow: 0 0 0 4px rgba(52, 211, 153, 0.12);
        }

        .writing-quiz-page .wq-textarea.is-invalid {
            border-color: var(--danger);
            box-shadow: 0 0 0 4px rgba(248, 113, 113, 0.1);
        }

        .writing-quiz-page .wq-answer-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-top: 8px;
        }

        .writing-quiz-page .wq-minimum {
            color: var(--text-muted);
            font-size: 10.5px;
            line-height: 1.45;
            font-weight: 700;
        }

        .writing-quiz-page .wq-minimum.is-ready {
            color: var(--success);
            font-weight: 900;
        }

        .writing-quiz-page .wq-error {
            color: var(--danger);
            font-size: 10.5px;
            line-height: 1.45;
            font-weight: 850;
            text-align: right;
        }

        .writing-quiz-page .wq-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-top: auto;
            padding-top: 13px;
        }

        .writing-quiz-page .wq-actions-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .writing-quiz-page .wq-button {
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
                box-shadow 150ms ease,
                opacity 150ms ease,
                filter 150ms ease;
        }

        .writing-quiz-page .wq-button svg {
            width: 16px;
            height: 16px;
        }

        .writing-quiz-page .wq-button:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: var(--shadow-soft);
        }

        .writing-quiz-page .wq-button:focus-visible {
            outline: 3px solid rgba(52, 211, 153, 0.22);
            outline-offset: 2px;
        }

        .writing-quiz-page .wq-button:disabled {
            cursor: not-allowed;
            opacity: 0.55;
            transform: none;
            box-shadow: none;
        }

        .writing-quiz-page .wq-button-secondary {
            border-color: var(--border);
            background: var(--card-soft);
            color: var(--text-soft);
        }

        .writing-quiz-page .wq-button-secondary:hover:not(:disabled) {
            border-color: var(--border-strong);
            background: var(--card-muted);
        }

        .writing-quiz-page .wq-button-primary {
            min-width: 175px;
            background: linear-gradient(
                100deg,
                var(--accent),
                var(--accent-strong)
            );
            color: #ffffff;
        }

        /*
        |--------------------------------------------------------------------------
        | Loading Modal
        |--------------------------------------------------------------------------
        */

        .writing-quiz-page .wq-loading {
            position: fixed;
            inset: 0;
            z-index: 70;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: rgba(2, 6, 23, 0.72);
            backdrop-filter: blur(8px);
        }

        .writing-quiz-page .wq-loading-card {
            width: min(100%, 360px);
            padding: 22px;
            border: 1px solid var(--border);
            border-radius: 20px;
            background: var(--card);
            box-shadow: var(--shadow);
            text-align: center;
        }

        .writing-quiz-page .wq-spinner {
            width: 42px;
            height: 42px;
            margin-inline: auto;
            border: 4px solid var(--card-muted);
            border-top-color: var(--accent);
            border-radius: 999px;
            animation: wq-spin 0.8s linear infinite;
        }

        @keyframes wq-spin {
            to {
                transform: rotate(360deg);
            }
        }

        .writing-quiz-page .wq-loading-title {
            margin: 14px 0 0;
            color: var(--text);
            font-size: 17px;
            line-height: 1.35;
            font-weight: 900;
        }

        .writing-quiz-page .wq-loading-text {
            margin: 5px 0 0;
            color: var(--text-muted);
            font-size: 11.5px;
            line-height: 1.55;
            font-weight: 650;
        }

        /*
        |--------------------------------------------------------------------------
        | Result
        |--------------------------------------------------------------------------
        */

        .writing-quiz-page .wq-result {
            display: grid;
            gap: 14px;
        }

        .writing-quiz-page .wq-result-summary {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: center;
            gap: 18px;
            padding: 18px;
        }

        .writing-quiz-page .wq-result-badge {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            min-height: 29px;
            padding: 6px 10px;
            border-radius: 999px;
            background: var(--success-soft);
            color: var(--success);
            font-size: 10px;
            line-height: 1;
            font-weight: 900;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .writing-quiz-page .wq-result-title {
            margin: 9px 0 0;
            color: var(--text);
            font-size: 23px;
            line-height: 1.2;
            font-weight: 900;
            letter-spacing: -0.03em;
        }

        .writing-quiz-page .wq-result-text {
            margin: 5px 0 0;
            color: var(--text-muted);
            font-size: 11.5px;
            line-height: 1.55;
            font-weight: 650;
        }

        .writing-quiz-page .wq-score-card {
            min-width: 145px;
            padding: 13px 15px;
            border: 1px solid var(--border);
            border-radius: 15px;
            background: var(--card-soft);
            text-align: center;
        }

        .writing-quiz-page .wq-score-label {
            color: var(--text-muted);
            font-size: 10px;
            line-height: 1.4;
            font-weight: 850;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .writing-quiz-page .wq-score-value {
            margin-top: 4px;
            color: var(--accent);
            font-size: 40px;
            line-height: 0.95;
            font-weight: 900;
            letter-spacing: -0.04em;
        }

        .writing-quiz-page .wq-result-details {
            padding: 16px;
        }

        .writing-quiz-page .wq-section-title {
            margin: 0;
            color: var(--text);
            font-size: 15px;
            line-height: 1.4;
            font-weight: 900;
        }

        .writing-quiz-page .wq-section-subtitle {
            margin: 3px 0 0;
            color: var(--text-muted);
            font-size: 10.5px;
            line-height: 1.45;
            font-weight: 650;
        }

        .writing-quiz-page .wq-rubric-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 8px;
            margin-top: 12px;
        }

        .writing-quiz-page .wq-rubric {
            padding: 11px;
            border: 1px solid var(--border);
            border-radius: 13px;
            background: var(--card-soft);
        }

        .writing-quiz-page .wq-rubric-label {
            color: var(--text-muted);
            font-size: 9.5px;
            line-height: 1.35;
            font-weight: 800;
        }

        .writing-quiz-page .wq-rubric-value {
            margin-top: 4px;
            color: var(--text);
            font-size: 19px;
            line-height: 1;
            font-weight: 900;
        }

        .writing-quiz-page .wq-rubric-value small {
            color: var(--text-muted);
            font-size: 10px;
        }

        .writing-quiz-page .wq-feedback {
            margin-top: 12px;
            padding: 13px 14px;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: var(--card-soft);
            color: var(--text-soft);
            font-size: 12px;
            line-height: 1.65;
            font-weight: 620;
        }

        /*
        |--------------------------------------------------------------------------
        | Saran perbaikan Modul 5 (Writing) — Bantu Pahami Feedback
        |--------------------------------------------------------------------------
        */

        .writing-quiz-page .wq-explain-row {
            margin-top: 12px;
        }

        .writing-quiz-page .wq-explain-panel {
            margin-top: 10px;
            padding: 13px 14px;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: var(--card-soft);
            font-size: 12px;
            line-height: 1.6;
        }

        .writing-quiz-page .wq-explain-label {
            margin: 0 0 4px;
            font-size: 11px;
            font-weight: 800;
            color: var(--text-muted);
        }

        .writing-quiz-page .wq-pretask-row {
            margin-bottom: 12px;
        }

        .writing-quiz-page .wq-vocab-list {
            margin: 0;
            padding-left: 18px;
            font-size: 12px;
            line-height: 1.7;
        }

        .writing-quiz-page .wq-result-actions {
            display: flex;
            justify-content: space-between;
            gap: 8px;
        }

        /*
        |--------------------------------------------------------------------------
        | Empty State
        |--------------------------------------------------------------------------
        */

        .writing-quiz-page .wq-empty {
            padding: 36px 20px;
            text-align: center;
        }

        .writing-quiz-page .wq-empty-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 54px;
            height: 54px;
            border-radius: 16px;
            background: var(--card-muted);
            color: var(--text-muted);
        }

        .writing-quiz-page .wq-empty-icon svg {
            width: 27px;
            height: 27px;
        }

        .writing-quiz-page .wq-empty-title {
            margin: 13px 0 0;
            color: var(--text);
            font-size: 19px;
            line-height: 1.3;
            font-weight: 900;
        }

        .writing-quiz-page .wq-empty-text {
            margin: 5px 0 0;
            color: var(--text-muted);
            font-size: 11.5px;
            line-height: 1.55;
            font-weight: 650;
        }

        /*
        |--------------------------------------------------------------------------
        | Responsive
        |--------------------------------------------------------------------------
        */

        @media (max-width: 940px) {
            .writing-quiz-page .wq-workspace {
                grid-template-columns: minmax(0, 1fr);
            }

            .writing-quiz-page .wq-textarea {
                min-height: 290px;
            }

            .writing-quiz-page .wq-rubric-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .writing-quiz-page .wq-rubric:last-child {
                grid-column: span 2;
            }
        }

        @media (max-width: 700px) {
            .writing-quiz-page .wq-overview {
                align-items: flex-start;
                flex-direction: column;
                padding: 15px;
            }

            .writing-quiz-page .wq-overview-meta {
                width: 100%;
            }

            .writing-quiz-page .wq-status {
                width: 100%;
                justify-content: center;
            }

            .writing-quiz-page .wq-actions,
            .writing-quiz-page .wq-actions-group,
            .writing-quiz-page .wq-result-actions {
                width: 100%;
                flex-direction: column;
                align-items: stretch;
            }

            .writing-quiz-page .wq-button,
            .writing-quiz-page .wq-button-primary {
                width: 100%;
                min-width: 0;
            }

            .writing-quiz-page .wq-answer-meta {
                align-items: flex-start;
                flex-direction: column;
            }

            .writing-quiz-page .wq-error {
                text-align: left;
            }

            .writing-quiz-page .wq-result-summary {
                grid-template-columns: minmax(0, 1fr);
            }

            .writing-quiz-page .wq-score-card {
                min-width: 0;
            }
        }

        @media (max-width: 430px) {
            .writing-quiz-page .wq-title {
                font-size: 19px;
            }

            .writing-quiz-page .wq-question-text {
                font-size: 13px;
            }

            .writing-quiz-page .wq-textarea {
                min-height: 250px;
                font-size: 13px;
            }

            .writing-quiz-page .wq-rubric-grid {
                grid-template-columns: minmax(0, 1fr);
            }

            .writing-quiz-page .wq-rubric:last-child {
                grid-column: auto;
            }
        }
    </style>

    <div class="writing-quiz-page">
        <div class="wq-stack">

            {{-- COMPACT HEADER --}}
            <section class="wq-card wq-overview">
                <div class="wq-overview-main">
                    <span class="wq-overview-icon">
                        <svg
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                            </path>
                        </svg>
                    </span>

                    <div class="wq-overview-copy">
                        <p class="wq-kicker">
                            Writing Quiz
                        </p>

                        <h1 class="wq-title">
                            {{ $lesson->title }}
                        </h1>

                        <p class="wq-description">
                            Read the question, review the reference, and write a complete answer.
                        </p>
                    </div>
                </div>

                <div class="wq-overview-meta">
                    <span class="wq-status">
                        <span class="wq-status-dot"></span>
                        Ready
                    </span>
                </div>
            </section>

            @if ($question)
                {{-- MAIN WORKSPACE --}}
                <section
                    id="writingWorkspace"
                    class="wq-workspace">

                    {{-- QUESTION AND REFERENCE --}}
                    <article class="wq-panel">
                        <header class="wq-panel-header">
                            <span class="wq-panel-icon">
                                <svg
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907C12.355 13.03 12 13.355 12 13.75V15m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                    </path>
                                </svg>
                            </span>

                            <div class="wq-panel-heading">
                                <h2 class="wq-panel-title">
                                    Writing Question
                                </h2>

                                <p class="wq-panel-subtitle">
                                    Read the question and reference before writing.
                                </p>
                            </div>
                        </header>

                        <div class="wq-question-body">
                            <div class="wq-question-box">
                                <p class="wq-question-label">
                                    Question
                                </p>

                                <p class="wq-question-text">
                                    {{ $displayQuestion }}
                                </p>
                            </div>

                            @if (!empty($question->image))
                                <figure class="wq-reference">
                                    <a
                                        href="{{ asset('storage/' . $question->image) }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="wq-reference-link">

                                        <img
                                            src="{{ asset('storage/' . $question->image) }}"
                                            alt="Reference image for the writing question"
                                            loading="eager">
                                    </a>

                                    <figcaption class="wq-reference-caption">
                                        Click the image to open the full-size reference.
                                    </figcaption>
                                </figure>
                            @endif
                        </div>
                    </article>

                    {{-- ANSWER FORM --}}
                    <article class="wq-panel">
                        <header class="wq-panel-header">
                            <span class="wq-panel-icon">
                                <svg
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 20h9M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z">
                                    </path>
                                </svg>
                            </span>

                            <div class="wq-panel-heading">
                                <h2 class="wq-panel-title">
                                    Your Answer
                                </h2>

                                <p class="wq-panel-subtitle">
                                    Minimum 20 characters.
                                </p>
                            </div>
                        </header>

                        <div class="wq-answer-body">
                            <form
                                id="writingForm"
                                action="{{ route('student.writing.submit', [
                                    'lesson' => $lesson,
                                    'material' => $material,
                                ]) }}"
                                method="POST"
                                novalidate>

                                @csrf

                                {{-- Saran perbaikan Modul 5 (Writing) — Bantuan Sebelum Menulis --}}
                                <div class="wq-pretask-row">
                                    <button type="button" id="preTaskHelpBtn" class="wq-button wq-button-secondary">
                                        💡 Bantuan sebelum menulis
                                    </button>
                                </div>

                                <div id="preTaskHelpPanel" class="wq-explain-panel" hidden>
                                    <p class="wq-explain-label">Struktur teks</p>
                                    <p id="preTaskStructureTip"></p>
                                    <p class="wq-explain-label" style="margin-top: 10px;">Kosakata yang mungkin berguna</p>
                                    <ul id="preTaskVocabList" class="wq-vocab-list"></ul>
                                    <p class="wq-explain-label" style="margin-top: 10px;">Pengingat tata bahasa</p>
                                    <p id="preTaskGrammarTip"></p>
                                    <p class="wq-explain-label" style="margin-top: 10px;">Pengingat mechanics</p>
                                    <p id="preTaskMechanicsTip"></p>
                                </div>

                                <div class="wq-answer-toolbar">
                                    <label
                                        for="answerInput"
                                        class="wq-answer-label">
                                        Your writing answer
                                    </label>

                                    <span class="wq-counter">
                                        <span id="wordCount">0</span>
                                        words ·
                                        <span id="charCount">0</span>
                                        characters
                                    </span>
                                </div>

                                <textarea
                                    id="answerInput"
                                    name="answer"
                                    minlength="20"
                                    required
                                    placeholder="Write your answer here..."
                                    class="wq-textarea">{{ old('answer') }}</textarea>

                                <div class="wq-answer-meta">
                                    <span
                                        id="minStatus"
                                        class="wq-minimum">
                                        Add at least 20 characters before submitting.
                                    </span>

                                    <span
                                        id="answerError"
                                        class="wq-error"
                                        hidden>
                                        Please write a longer and complete answer.
                                    </span>
                                </div>

                                <div class="wq-actions">
                                    <div class="wq-actions-group">
                                        <a
                                            href="{{ route('student.writing', $lesson) }}"
                                            class="wq-button wq-button-secondary">
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

                                            Back
                                        </a>
                                    </div>

                                    <div class="wq-actions-group">
                                        <button
                                            type="submit"
                                            id="submitBtn"
                                            class="wq-button wq-button-primary">
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
                                                Submit Writing
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </article>
                </section>

                {{-- LOADING --}}
                <div
                    id="loadingModal"
                    class="wq-loading"
                    hidden>
                    <div
                        class="wq-loading-card"
                        role="status"
                        aria-live="polite">
                        <div class="wq-spinner"></div>

                        <h2 class="wq-loading-title">
                            Evaluating your writing
                        </h2>

                        <p class="wq-loading-text">
                            Please wait while the result is being processed.
                        </p>
                    </div>
                </div>

                {{-- RESULT --}}
                <section
                    id="resultSection"
                    class="wq-result"
                    hidden>
                    <article class="wq-card wq-result-summary">
                        <div>
                            <span class="wq-result-badge">
                                Assessment Completed
                            </span>

                            <h2 class="wq-result-title">
                                Writing Result
                            </h2>

                            <p class="wq-result-text">
                                Review your rubric scores and feedback below.
                            </p>
                        </div>

                        <div class="wq-score-card">
                            <div class="wq-score-label">
                                Final Score
                            </div>

                            <div
                                id="totalScore"
                                class="wq-score-value">
                                0
                            </div>
                        </div>
                    </article>

                    <article class="wq-card wq-result-details">
                        <h3 class="wq-section-title">
                            Scoring Criteria
                        </h3>

                        <p class="wq-section-subtitle">
                            Each writing criterion is scored from 0 to 4.
                        </p>

                        <div class="wq-rubric-grid">
                            <div class="wq-rubric">
                                <div class="wq-rubric-label">
                                    Orientation
                                </div>

                                <div class="wq-rubric-value">
                                    <span id="orientationScore">0</span>
                                    <small>/ 4</small>
                                </div>
                            </div>

                            <div class="wq-rubric">
                                <div class="wq-rubric-label">
                                    Complication
                                </div>

                                <div class="wq-rubric-value">
                                    <span id="complicationScore">0</span>
                                    <small>/ 4</small>
                                </div>
                            </div>

                            <div class="wq-rubric">
                                <div class="wq-rubric-label">
                                    Resolution
                                </div>

                                <div class="wq-rubric-value">
                                    <span id="resolutionScore">0</span>
                                    <small>/ 4</small>
                                </div>
                            </div>

                            <div class="wq-rubric">
                                <div class="wq-rubric-label">
                                    Organization
                                </div>

                                <div class="wq-rubric-value">
                                    <span id="organizationScore">0</span>
                                    <small>/ 4</small>
                                </div>
                            </div>

                            <div class="wq-rubric">
                                <div class="wq-rubric-label">
                                    Mechanics
                                </div>

                                <div class="wq-rubric-value">
                                    <span id="mechanicsScore">0</span>
                                    <small>/ 4</small>
                                </div>
                            </div>
                        </div>

                        <div
                            id="feedbackText"
                            class="wq-feedback">
                            -
                        </div>

                        {{-- Saran perbaikan Modul 5 (Writing) — Bantu Pahami Feedback --}}
                        <div class="wq-explain-row">
                            <button type="button" id="explainFeedbackBtn" class="wq-button wq-button-secondary">
                                💡 Bantu saya paham feedback ini
                            </button>
                        </div>

                        <div id="explainFeedbackPanel" class="wq-explain-panel" hidden>
                            <p class="wq-explain-label">Penjelasan sederhana</p>
                            <p id="explainFeedbackText"></p>
                            <p class="wq-explain-label" style="margin-top: 10px;">Saran untuk lain kali</p>
                            <p id="explainFeedbackTip"></p>
                        </div>
                    </article>

                    <div class="wq-result-actions">
                        <a
                            href="{{ route('student.writing', $lesson) }}"
                            class="wq-button wq-button-secondary">
                            Back to Writing
                        </a>

                        <a
                            href="{{ route('missions') }}"
                            class="wq-button wq-button-primary">
                            Back to Missions
                        </a>
                    </div>
                </section>
            @else
                {{-- EMPTY STATE --}}
                <section class="wq-card wq-empty">
                    <span class="wq-empty-icon">
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

                    <h2 class="wq-empty-title">
                        No Writing Question Yet
                    </h2>

                    <p class="wq-empty-text">
                        The administrator has not added a writing question
                        for this lesson.
                    </p>

                    <a
                        href="{{ route('student.writing', $lesson) }}"
                        class="wq-button wq-button-primary"
                        style="margin-top: 16px;">
                        Back to Writing
                    </a>
                </section>
            @endif
        </div>
    </div>

    @if ($question)
        <script>
            document.addEventListener(
                'DOMContentLoaded',
                function () {
                    const writingForm =
                        document.getElementById(
                            'writingForm'
                        );

                    if (!writingForm) {
                        return;
                    }

                    const loadingModal =
                        document.getElementById(
                            'loadingModal'
                        );

                    const writingWorkspace =
                        document.getElementById(
                            'writingWorkspace'
                        );

                    const resultSection =
                        document.getElementById(
                            'resultSection'
                        );

                    const answerInput =
                        document.getElementById(
                            'answerInput'
                        );

                    const charCount =
                        document.getElementById(
                            'charCount'
                        );

                    const wordCount =
                        document.getElementById(
                            'wordCount'
                        );

                    const minStatus =
                        document.getElementById(
                            'minStatus'
                        );

                    const answerError =
                        document.getElementById(
                            'answerError'
                        );

                    const submitBtn =
                        document.getElementById(
                            'submitBtn'
                        );

                    const submitLabel =
                        document.getElementById(
                            'submitLabel'
                        );

                    let isSubmitting = false;
                    let currentSubmissionId = null;

                    // Saran perbaikan Modul 5 (Writing) — Bantuan Sebelum Menulis.
                    const preTaskHelpBtn = document.getElementById('preTaskHelpBtn');
                    if (preTaskHelpBtn) {
                        preTaskHelpBtn.addEventListener('click', async function () {
                            const tokenInput = writingForm.querySelector('input[name="_token"]');
                            const csrfToken = tokenInput ? tokenInput.value : '';

                            preTaskHelpBtn.disabled = true;
                            preTaskHelpBtn.textContent = 'Memuat...';

                            try {
                                const response = await fetch(
                                    @json(route('student.writing.preTaskHelp', $lesson)),
                                    {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': csrfToken,
                                            'Accept': 'application/json',
                                            'Content-Type': 'application/json'
                                        }
                                    }
                                );

                                const result = await response.json();

                                if (!response.ok) {
                                    throw new Error(result.message || 'Bantuan tidak tersedia saat ini.');
                                }

                                document.getElementById('preTaskStructureTip').textContent = result.structure_tip;

                                const vocabList = document.getElementById('preTaskVocabList');
                                vocabList.innerHTML = '';
                                (result.vocabulary_suggestions || []).forEach(function (item) {
                                    const li = document.createElement('li');
                                    li.textContent = item;
                                    vocabList.appendChild(li);
                                });

                                document.getElementById('preTaskGrammarTip').textContent = result.grammar_tip;

                                const mechanicsEl = document.getElementById('preTaskMechanicsTip');
                                if (result.mechanics_tip) {
                                    mechanicsEl.textContent = result.mechanics_tip;
                                    mechanicsEl.hidden = false;
                                } else {
                                    mechanicsEl.hidden = true;
                                }

                                document.getElementById('preTaskHelpPanel').hidden = false;
                            } catch (error) {
                                console.error('Pre-task help error:', error);
                                alert(error.message || 'Bantuan tidak tersedia saat ini.');
                            } finally {
                                preTaskHelpBtn.disabled = false;
                                preTaskHelpBtn.textContent = '💡 Bantuan sebelum menulis';
                            }
                        });
                    }

                    function countWords(value) {
                        const cleaned =
                            value.trim();

                        if (cleaned === '') {
                            return 0;
                        }

                        return cleaned
                            .split(/\s+/)
                            .filter(Boolean)
                            .length;
                    }

                    function updateCounters() {
                        const value =
                            answerInput.value;

                        const length =
                            value.length;

                        charCount.textContent =
                            length;

                        wordCount.textContent =
                            countWords(value);

                        const isReady =
                            value.trim().length >= 20;

                        minStatus.classList.toggle(
                            'is-ready',
                            isReady
                        );

                        minStatus.textContent =
                            isReady
                                ? 'Ready to submit.'
                                : 'Add at least 20 characters before submitting.';

                        if (isReady) {
                            clearError();
                        }
                    }

                    function showError(message) {
                        answerInput.classList.add(
                            'is-invalid'
                        );

                        answerError.textContent =
                            message;

                        answerError.hidden =
                            false;

                        answerInput.focus();
                    }

                    function clearError() {
                        answerInput.classList.remove(
                            'is-invalid'
                        );

                        answerError.hidden =
                            true;
                    }

                    function setLoading(isLoading) {
                        loadingModal.hidden =
                            !isLoading;

                        submitBtn.disabled =
                            isLoading;

                        submitLabel.textContent =
                            isLoading
                                ? 'Submitting...'
                                : 'Submit Writing';
                    }

                    function scrollToResult() {
                        const navbarOffset =
                            104;

                        const target =
                            resultSection
                                .getBoundingClientRect()
                                .top +
                            window.scrollY -
                            navbarOffset;

                        window.scrollTo({
                            top: Math.max(
                                0,
                                target
                            ),
                            behavior: 'smooth'
                        });
                    }

                    function showResult(result) {
                        writingWorkspace.hidden =
                            true;

                        resultSection.hidden =
                            false;

                        document
                            .getElementById(
                                'orientationScore'
                            )
                            .textContent =
                                result.orientation_score ?? 0;

                        document
                            .getElementById(
                                'complicationScore'
                            )
                            .textContent =
                                result.complication_score ?? 0;

                        document
                            .getElementById(
                                'resolutionScore'
                            )
                            .textContent =
                                result.resolution_score ?? 0;

                        document
                            .getElementById(
                                'organizationScore'
                            )
                            .textContent =
                                result.organization_score ?? 0;

                        document
                            .getElementById(
                                'mechanicsScore'
                            )
                            .textContent =
                                result.mechanics_score ?? 0;

                        document
                            .getElementById(
                                'totalScore'
                            )
                            .textContent =
                                result.total_score ?? 0;

                        document
                            .getElementById(
                                'feedbackText'
                            )
                            .textContent =
                                result.feedback ?? '-';

                        // Saran perbaikan Modul 5 (Writing) — Bantu Pahami Feedback.
                        currentSubmissionId = result.assessment_submission_id ?? null;
                        const explainPanel = document.getElementById('explainFeedbackPanel');
                        if (explainPanel) {
                            explainPanel.hidden = true;
                        }

                        scrollToResult();
                    }

                    const explainFeedbackBtn = document.getElementById('explainFeedbackBtn');
                    if (explainFeedbackBtn) {
                        explainFeedbackBtn.addEventListener('click', async function () {
                            if (!currentSubmissionId) {
                                return;
                            }

                            const tokenInput = writingForm.querySelector('input[name="_token"]');
                            const csrfToken = tokenInput ? tokenInput.value : '';

                            explainFeedbackBtn.disabled = true;
                            explainFeedbackBtn.textContent = 'Memuat...';

                            try {
                                const urlTemplate = @json(route('student.writing.explainFeedback', [$lesson, '__SUBMISSION_ID__']));
                                const response = await fetch(
                                    urlTemplate.replace('__SUBMISSION_ID__', currentSubmissionId),
                                    {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': csrfToken,
                                            'Accept': 'application/json',
                                            'Content-Type': 'application/json'
                                        }
                                    }
                                );

                                const result = await response.json();

                                if (!response.ok) {
                                    throw new Error(result.message || 'Penjelasan tidak tersedia saat ini.');
                                }

                                document.getElementById('explainFeedbackText').textContent = result.simple_explanation;
                                document.getElementById('explainFeedbackTip').textContent = result.tip_for_next_time;
                                document.getElementById('explainFeedbackPanel').hidden = false;
                            } catch (error) {
                                console.error('Explain feedback error:', error);
                                alert(error.message || 'Penjelasan tidak tersedia saat ini.');
                            } finally {
                                explainFeedbackBtn.disabled = false;
                                explainFeedbackBtn.textContent = '💡 Bantu saya paham feedback ini';
                            }
                        });
                    }

                    answerInput.addEventListener(
                        'input',
                        updateCounters
                    );

                    writingForm.addEventListener(
                        'submit',
                        async function (event) {
                            event.preventDefault();

                            if (isSubmitting) {
                                return;
                            }

                            const answer =
                                answerInput.value.trim();

                            if (answer.length < 20) {
                                showError(
                                    'Please write at least 20 characters before submitting.'
                                );

                                return;
                            }

                            clearError();

                            isSubmitting = true;
                            setLoading(true);

                            try {
                                const response =
                                    await fetch(
                                        writingForm.action,
                                        {
                                            method: 'POST',

                                            body:
                                                new FormData(
                                                    writingForm
                                                ),

                                            headers: {
                                                'Accept':
                                                    'application/json',

                                                'X-CSRF-TOKEN':
                                                    writingForm
                                                        .querySelector(
                                                            'input[name="_token"]'
                                                        )
                                                        .value
                                            }
                                        }
                                    );

                                let result = {};

                                try {
                                    result =
                                        await response.json();
                                } catch (jsonError) {
                                    throw new Error(
                                        'The server returned an invalid response.'
                                    );
                                }

                                if (!response.ok) {
                                    const validationMessage =
                                        result.errors &&
                                        result.errors.answer &&
                                        result.errors.answer[0]
                                            ? result.errors.answer[0]
                                            : result.message;

                                    throw new Error(
                                        validationMessage ||
                                        'Failed to submit writing.'
                                    );
                                }

                                showResult(result);
                            } catch (error) {
                                console.error(
                                    'Writing submit error:',
                                    error
                                );

                                showError(
                                    error.message ||
                                    'Something went wrong while submitting your writing.'
                                );
                            } finally {
                                isSubmitting = false;
                                setLoading(false);
                            }
                        }
                    );

                    updateCounters();
                }
            );
        </script>
    @endif
</x-app-layout>