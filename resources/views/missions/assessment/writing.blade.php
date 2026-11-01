<x-app-layout>
    <style>
        .writing-page {
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
            --warning: #d97706;
            --warning-soft: #fffbeb;
            --shadow: 0 16px 40px rgba(15, 23, 42, 0.08);
            --shadow-soft: 0 7px 20px rgba(15, 23, 42, 0.06);

            color: var(--text);
        }

        html.dark .writing-page,
        body.dark .writing-page,
        .dark .writing-page,
        html[data-theme="dark"] .writing-page,
        body[data-theme="dark"] .writing-page {
            --card: #0f172a;
            --card-soft: #111c31;
            --card-muted: #172033;
            --text: #f8fafc;
            --text-soft: #d7e0ec;
            --text-muted: #9fb0c5;
            --border: #26364d;
            --border-strong: #33455f;
            --accent: #22d3ee;
            --accent-strong: #60a5fa;
            --accent-soft: #0b2c3a;
            --success: #34d399;
            --success-soft: #0b2e29;
            --danger: #f87171;
            --danger-soft: #371820;
            --warning: #fbbf24;
            --warning-soft: #33250c;
            --shadow: 0 20px 48px rgba(0, 0, 0, 0.34);
            --shadow-soft: 0 9px 24px rgba(0, 0, 0, 0.24);
        }

        .writing-page,
        .writing-page * {
            box-sizing: border-box;
        }

        .writing-page [hidden],
        .writing-page [data-writing-step][hidden],
        .writing-page #writing-next-button[hidden],
        .writing-page #writing-submit-button[hidden] {
            display: none !important;
        }

        .writing-page .writing-shell {
            width: min(100%, 1320px);
            margin-inline: auto;
            display: grid;
            gap: 16px;
        }

        .writing-page .writing-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 22px;
            box-shadow: var(--shadow-soft);
        }

        .writing-page .writing-alert {
            padding: 14px 16px;
            border-radius: 15px;
            font-size: 14px;
            line-height: 1.55;
            font-weight: 750;
        }

        .writing-page .writing-alert-success {
            border: 1px solid color-mix(in srgb, var(--success) 36%, transparent);
            background: var(--success-soft);
            color: var(--success);
        }

        .writing-page .writing-alert-error {
            border: 1px solid color-mix(in srgb, var(--danger) 36%, transparent);
            background: var(--danger-soft);
            color: var(--danger);
        }

        .writing-page .writing-eyebrow {
            display: inline-flex;
            align-items: center;
            min-height: 29px;
            padding: 6px 11px;
            border-radius: 999px;
            background: var(--accent-soft);
            color: var(--accent);
            font-size: 11px;
            line-height: 1;
            font-weight: 900;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        .writing-page .writing-title {
            margin: 11px 0 0;
            color: var(--text);
            font-size: clamp(28px, 3vw, 40px);
            line-height: 1.08;
            font-weight: 900;
            letter-spacing: -0.04em;
        }

        .writing-page .writing-subtitle {
            margin: 7px 0 0;
            color: var(--text-muted);
            font-size: 14px;
            line-height: 1.55;
            font-weight: 650;
        }

        .writing-page .writing-result-hero,
        .writing-page .writing-assessment-header {
            position: relative;
            overflow: hidden;
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: center;
            gap: 22px;
            padding: 22px 24px;
        }

        .writing-page .writing-result-hero::after,
        .writing-page .writing-assessment-header::after {
            content: "";
            position: absolute;
            width: 220px;
            height: 220px;
            top: -120px;
            right: -90px;
            border-radius: 999px;
            background: rgba(34, 211, 238, 0.11);
            pointer-events: none;
        }

        .writing-page .writing-result-copy,
        .writing-page .writing-score-card,
        .writing-page .writing-assessment-copy,
        .writing-page .writing-assessment-stats {
            position: relative;
            z-index: 1;
        }

        .writing-page .writing-score-card {
            min-width: 205px;
            padding: 16px 18px;
            border: 1px solid var(--border);
            border-radius: 17px;
            background: var(--card-soft);
        }

        .writing-page .writing-score-label {
            color: var(--text-muted);
            font-size: 12px;
            font-weight: 800;
        }

        .writing-page .writing-score-row {
            display: flex;
            align-items: flex-end;
            gap: 5px;
            margin-top: 4px;
        }

        .writing-page .writing-score-value {
            color: var(--accent);
            font-size: 46px;
            line-height: 0.95;
            font-weight: 900;
            letter-spacing: -0.04em;
        }

        .writing-page .writing-score-max {
            padding-bottom: 3px;
            color: var(--text-muted);
            font-size: 13px;
            font-weight: 800;
        }

        .writing-page .writing-result-metrics {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .writing-page .writing-result-metric {
            padding: 16px 17px;
        }

        .writing-page .writing-result-label {
            color: var(--text-muted);
            font-size: 12px;
            line-height: 1.4;
            font-weight: 800;
        }

        .writing-page .writing-result-value {
            margin-top: 7px;
            color: var(--text);
            font-size: 17px;
            line-height: 1.35;
            font-weight: 900;
        }

        .writing-page .writing-status {
            display: inline-flex;
            align-items: center;
            min-height: 29px;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 11px;
            line-height: 1;
            font-weight: 900;
        }

        .writing-page .writing-status-completed {
            background: var(--success-soft);
            color: var(--success);
        }

        .writing-page .writing-status-failed {
            background: var(--danger-soft);
            color: var(--danger);
        }

        .writing-page .writing-status-pending {
            background: var(--warning-soft);
            color: var(--warning);
        }

        .writing-page .writing-result-section {
            padding: 18px;
        }

        .writing-page .writing-section-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
        }

        .writing-page .writing-section-title {
            margin: 0;
            color: var(--text);
            font-size: 18px;
            line-height: 1.3;
            font-weight: 900;
            letter-spacing: -0.02em;
        }

        .writing-page .writing-section-subtitle {
            margin: 4px 0 0;
            color: var(--text-muted);
            font-size: 12px;
            line-height: 1.55;
            font-weight: 650;
        }

        .writing-page .writing-section-badge {
            display: inline-flex;
            align-items: center;
            min-height: 29px;
            padding: 6px 10px;
            border-radius: 999px;
            background: var(--card-muted);
            color: var(--text-muted);
            font-size: 11px;
            font-weight: 900;
        }

        .writing-page .writing-feedback-box {
            margin-top: 13px;
            padding: 14px 15px;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: var(--card-soft);
            color: var(--text-soft);
            font-size: 14px;
            line-height: 1.7;
            font-weight: 600;
        }

        .writing-page .writing-answer-list {
            display: grid;
            gap: 12px;
            margin-top: 14px;
        }

        .writing-page .writing-answer-item {
            overflow: hidden;
            border: 1px solid var(--border);
            border-radius: 16px;
            background: var(--card-soft);
        }

        .writing-page .writing-answer-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 13px 14px;
            border-bottom: 1px solid var(--border);
        }

        .writing-page .writing-answer-heading {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }

        .writing-page .writing-answer-number {
            display: inline-flex;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: var(--accent-soft);
            color: var(--accent);
            font-size: 12px;
            font-weight: 900;
        }

        .writing-page .writing-answer-title {
            color: var(--text);
            font-size: 14px;
            font-weight: 900;
        }

        .writing-page .writing-answer-score {
            display: inline-flex;
            flex: 0 0 auto;
            align-items: center;
            min-height: 28px;
            padding: 6px 9px;
            border-radius: 999px;
            background: var(--card-muted);
            color: var(--text-soft);
            font-size: 11px;
            font-weight: 900;
        }

        .writing-page .writing-answer-body {
            display: grid;
            gap: 13px;
            padding: 14px;
        }

        .writing-page .writing-answer-label {
            margin: 0 0 6px;
            color: var(--text-muted);
            font-size: 10px;
            line-height: 1.3;
            font-weight: 900;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        .writing-page .writing-answer-copy {
            padding: 12px 13px;
            border: 1px solid var(--border);
            border-radius: 13px;
            background: var(--card);
            color: var(--text-soft);
            font-size: 13px;
            line-height: 1.65;
            font-weight: 550;
        }

        .writing-page .writing-rubric-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 8px;
        }

        .writing-page .writing-rubric {
            padding: 10px 11px;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: var(--card);
        }

        .writing-page .writing-rubric-label {
            color: var(--text-muted);
            font-size: 10px;
            line-height: 1.3;
            font-weight: 800;
        }

        .writing-page .writing-rubric-score {
            margin-top: 3px;
            color: var(--text);
            font-size: 17px;
            line-height: 1;
            font-weight: 900;
        }

        .writing-page .writing-rubric-score small {
            color: var(--text-muted);
            font-size: 10px;
            font-weight: 800;
        }

        .writing-page .writing-ai-feedback {
            padding: 12px 13px;
            border: 1px solid color-mix(in srgb, var(--accent) 30%, var(--border));
            border-radius: 13px;
            background: var(--accent-soft);
        }

        .writing-page .writing-ai-label {
            margin: 0 0 5px;
            color: var(--accent);
            font-size: 10px;
            line-height: 1.3;
            font-weight: 900;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        .writing-page .writing-ai-copy {
            margin: 0;
            color: var(--text-soft);
            font-size: 13px;
            line-height: 1.65;
            font-weight: 600;
        }

        .writing-page .writing-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .writing-page .writing-button {
            display: inline-flex;
            min-height: 50px;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 11px 18px;
            border: 1px solid transparent;
            border-radius: 14px;
            font-family: inherit;
            font-size: 14px;
            line-height: 1;
            font-weight: 900;
            text-decoration: none;
            cursor: pointer;
            transition:
                transform 160ms ease,
                background-color 160ms ease,
                border-color 160ms ease,
                filter 160ms ease,
                box-shadow 160ms ease,
                opacity 160ms ease;
        }

        .writing-page .writing-button svg {
            width: 18px;
            height: 18px;
        }

        .writing-page .writing-button:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: var(--shadow-soft);
        }

        .writing-page .writing-button:focus-visible {
            outline: 3px solid rgba(34, 211, 238, 0.25);
            outline-offset: 2px;
        }

        .writing-page .writing-button:disabled {
            cursor: not-allowed;
            opacity: 0.45;
            transform: none;
            box-shadow: none;
        }

        .writing-page .writing-button-secondary {
            border-color: var(--border);
            background: var(--card-soft);
            color: var(--text-soft);
        }

        .writing-page .writing-button-secondary:hover:not(:disabled) {
            border-color: var(--border-strong);
            background: var(--card-muted);
        }

        .writing-page .writing-button-primary {
            min-width: 185px;
            background: linear-gradient(100deg, var(--accent), var(--accent-strong));
            color: #ffffff;
        }

        .writing-page .writing-button-submit {
            min-width: 210px;
            background: linear-gradient(100deg, #059669, #0891b2);
            color: #ffffff;
        }

        .writing-page .writing-assessment-stats {
            display: grid;
            grid-template-columns: repeat(2, minmax(105px, 1fr));
            gap: 10px;
        }

        .writing-page .writing-assessment-stat {
            padding: 13px 15px;
            border: 1px solid var(--border);
            border-radius: 15px;
            background: var(--card-soft);
        }

        .writing-page .writing-assessment-stat-label {
            color: var(--text-muted);
            font-size: 11px;
            font-weight: 800;
        }

        .writing-page .writing-assessment-stat-value {
            margin-top: 3px;
            color: var(--text);
            font-size: 23px;
            line-height: 1;
            font-weight: 900;
        }

        .writing-page .writing-progress {
            padding: 15px 17px;
        }

        .writing-page .writing-progress-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
        }

        .writing-page .writing-progress-title {
            margin: 0;
            color: var(--text);
            font-size: 14px;
            line-height: 1.4;
            font-weight: 900;
        }

        .writing-page .writing-progress-description {
            margin: 3px 0 0;
            color: var(--text-muted);
            font-size: 12px;
            line-height: 1.5;
            font-weight: 650;
        }

        .writing-page .writing-progress-badge {
            min-width: 48px;
            padding: 8px 10px;
            border-radius: 13px;
            background: var(--accent-soft);
            color: var(--accent);
            text-align: center;
            font-size: 12px;
            font-weight: 900;
        }

        .writing-page .writing-progress-track {
            height: 8px;
            margin-top: 12px;
            overflow: hidden;
            border-radius: 999px;
            background: var(--card-muted);
        }

        .writing-page .writing-progress-bar {
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, var(--accent), var(--accent-strong));
            transition: width 240ms ease;
        }

        .writing-page .writing-question-card {
            padding: 19px;
        }

        .writing-page .writing-question-head {
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .writing-page .writing-question-number {
            display: inline-flex;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: var(--accent-soft);
            color: var(--accent);
            font-size: 14px;
            font-weight: 900;
        }

        .writing-page .writing-question-copy {
            min-width: 0;
            flex: 1;
        }

        .writing-page .writing-question-label {
            margin: 0;
            color: var(--accent);
            font-size: 10px;
            line-height: 1.3;
            font-weight: 900;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        .writing-page .writing-question-text {
            margin: 6px 0 0;
            color: var(--text);
            font-size: 17px;
            line-height: 1.55;
            font-weight: 550;
            letter-spacing: -0.015em;
        }

        .writing-page .writing-question-image {
            margin: 15px 0 0;
            overflow: hidden;
            border: 1px solid var(--border);
            border-radius: 15px;
            background: var(--card-soft);
        }

        .writing-page .writing-question-image img {
            display: block;
            width: 100%;
            max-height: 360px;
            object-fit: contain;
            padding: 10px;
        }

        .writing-page .writing-answer-field {
            margin-top: 17px;
        }

        .writing-page .writing-answer-field-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 7px;
        }

        .writing-page .writing-answer-field-label {
            color: var(--text);
            font-size: 13px;
            font-weight: 900;
        }

        .writing-page .writing-word-count {
            color: var(--text-muted);
            font-size: 11px;
            font-weight: 800;
        }

        .writing-page .writing-textarea {
            display: block;
            width: 100%;
            min-height: 225px;
            resize: vertical;
            padding: 14px 15px;
            border: 1px solid var(--border-strong);
            border-radius: 15px;
            background: var(--card-soft);
            color: var(--text);
            font-family: inherit;
            font-size: 14px;
            line-height: 1.7;
            outline: none;
            transition:
                border-color 160ms ease,
                box-shadow 160ms ease,
                background-color 160ms ease;
        }

        .writing-page .writing-textarea::placeholder {
            color: var(--text-muted);
        }

        .writing-page .writing-textarea:focus {
            border-color: var(--accent);
            background: var(--card);
            box-shadow: 0 0 0 4px rgba(34, 211, 238, 0.12);
        }

        .writing-page .writing-textarea.is-error {
            border-color: var(--danger);
            box-shadow: 0 0 0 4px color-mix(in srgb, var(--danger) 12%, transparent);
        }

        .writing-page .writing-answer-help-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-top: 7px;
        }

        .writing-page .writing-answer-help {
            color: var(--text-muted);
            font-size: 11px;
            line-height: 1.5;
            font-weight: 650;
        }

        .writing-page .writing-answer-error {
            color: var(--danger);
            font-size: 11px;
            line-height: 1.5;
            font-weight: 850;
        }

        .writing-page .writing-navigation {
            padding: 13px;
        }

        .writing-page .writing-navigation-inner,
        .writing-page .writing-navigation-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .writing-page .writing-navigation-inner {
            justify-content: space-between;
        }

        .writing-page .writing-empty {
            padding: 42px 22px;
            text-align: center;
        }

        .writing-page .writing-empty-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 58px;
            height: 58px;
            border-radius: 17px;
            background: var(--card-muted);
            color: var(--text-muted);
        }

        .writing-page .writing-empty-icon svg {
            width: 28px;
            height: 28px;
        }

        .writing-page .writing-empty-title {
            margin: 15px 0 0;
            color: var(--text);
            font-size: 19px;
            font-weight: 900;
        }

        .writing-page .writing-empty-text {
            max-width: 520px;
            margin: 6px auto 0;
            color: var(--text-muted);
            font-size: 13px;
            line-height: 1.6;
            font-weight: 650;
        }

        @media (max-width: 900px) {
            .writing-page .writing-rubric-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 720px) {
            .writing-page .writing-shell {
                gap: 14px;
            }

            .writing-page .writing-result-hero,
            .writing-page .writing-assessment-header {
                grid-template-columns: minmax(0, 1fr);
                padding: 19px;
            }

            .writing-page .writing-score-card {
                min-width: 0;
            }

            .writing-page .writing-result-metrics {
                grid-template-columns: minmax(0, 1fr);
            }

            .writing-page .writing-rubric-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .writing-page .writing-actions,
            .writing-page .writing-navigation-inner,
            .writing-page .writing-navigation-group {
                width: 100%;
                flex-direction: column;
                align-items: stretch;
            }

            .writing-page .writing-button,
            .writing-page .writing-button-primary,
            .writing-page .writing-button-submit {
                width: 100%;
                min-width: 0;
            }

            .writing-page .writing-answer-help-row {
                align-items: flex-start;
                flex-direction: column;
            }
        }

        @media (max-width: 460px) {
            .writing-page .writing-rubric-grid {
                grid-template-columns: minmax(0, 1fr);
            }

            .writing-page .writing-answer-top {
                align-items: flex-start;
                flex-direction: column;
            }

            .writing-page .writing-answer-score {
                align-self: flex-start;
            }
        }
    </style>

    @if (isset($submission))
        @php
            $score = (int) ($submission->final_score ?? 0);

            $statusClass = match ($submission->status) {
                'completed' => 'writing-status-completed',
                'failed' => 'writing-status-failed',
                default => 'writing-status-pending',
            };
        @endphp

        <div class="writing-page">
            <div class="writing-shell">
                @if (session('success'))
                    <div class="writing-alert writing-alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <section class="writing-card writing-result-hero">
                    <div class="writing-result-copy">
                        <span class="writing-eyebrow">
                            Assessment Result
                        </span>

                        <h1 class="writing-title">
                            {{ strtoupper($submission->type) }}
                            {{ ucfirst($submission->skill) }}
                        </h1>

                        <p class="writing-subtitle">
                            {{ $submission->lesson->title ?? 'Writing Assessment' }}
                        </p>
                    </div>

                    <aside class="writing-score-card">
                        <div class="writing-score-label">
                            Final Score
                        </div>

                        <div class="writing-score-row">
                            <span class="writing-score-value">
                                {{ $score }}
                            </span>

                            <span class="writing-score-max">
                                / 100
                            </span>
                        </div>
                    </aside>
                </section>

                <section class="writing-result-metrics">
                    <article class="writing-card writing-result-metric">
                        <div class="writing-result-label">
                            Status
                        </div>

                        <div class="writing-result-value">
                            <span class="writing-status {{ $statusClass }}">
                                {{ ucfirst($submission->status) }}
                            </span>
                        </div>
                    </article>

                    <article class="writing-card writing-result-metric">
                        <div class="writing-result-label">
                            Submitted At
                        </div>

                        <div class="writing-result-value">
                            {{ $submission->submitted_at?->format('d M Y, H:i')
                                ?? $submission->created_at?->format('d M Y, H:i')
                                ?? '-' }}
                        </div>
                    </article>

                    <article class="writing-card writing-result-metric">
                        <div class="writing-result-label">
                            Total Answers
                        </div>

                        <div class="writing-result-value">
                            {{ $submission->answers->count() }}
                        </div>
                    </article>
                </section>

                <section class="writing-card writing-result-section">
                    <header class="writing-section-head">
                        <div>
                            <h2 class="writing-section-title">
                                Overall Feedback
                            </h2>

                            <p class="writing-section-subtitle">
                                General evaluation of your writing assessment.
                            </p>
                        </div>
                    </header>

                    <div class="writing-feedback-box">
                        {!! nl2br(e(trim((string) ($submission->feedback ?? 'No feedback is available yet.')))) !!}
                    </div>
                </section>

                <section class="writing-card writing-result-section">
                    <header class="writing-section-head">
                        <div>
                            <h2 class="writing-section-title">
                                Answer Details
                            </h2>

                            <p class="writing-section-subtitle">
                                Review your answer, rubric scores, and AI feedback.
                            </p>
                        </div>

                        <span class="writing-section-badge">
                            {{ $submission->answers->count() }} answers
                        </span>
                    </header>

                    <div class="writing-answer-list">
                        @forelse ($submission->answers as $index => $answer)
                            @php
                                $rubrics = [
                                    'Orientation' => $answer->orientation_score,
                                    'Complication' => $answer->complication_score,
                                    'Resolution' => $answer->resolution_score,
                                    'Organization' => $answer->organization_score,
                                    'Mechanics' => $answer->mechanics_score,
                                ];
                            @endphp

                            <article class="writing-answer-item">
                                <div class="writing-answer-top">
                                    <div class="writing-answer-heading">
                                        <span class="writing-answer-number">
                                            {{ $index + 1 }}
                                        </span>

                                        <span class="writing-answer-title">
                                            Question {{ $index + 1 }}
                                        </span>
                                    </div>

                                    <span class="writing-answer-score">
                                        {{ $answer->score ?? 0 }}
                                        /
                                        {{ $answer->max_score ?? 100 }}
                                    </span>
                                </div>

                                <div class="writing-answer-body">
                                    <div>
                                        <p class="writing-answer-label">
                                            Your Answer
                                        </p>

                                        <div class="writing-answer-copy">
                                            {!! nl2br(e(trim((string) ($answer->answer ?: '-')))) !!}
                                        </div>
                                    </div>

                                    <div>
                                        <p class="writing-answer-label">
                                            Scoring Criteria
                                        </p>

                                        <div class="writing-rubric-grid">
                                            @foreach ($rubrics as $label => $rubricScore)
                                                <div class="writing-rubric">
                                                    <div class="writing-rubric-label">
                                                        {{ $label }}
                                                    </div>

                                                    <div class="writing-rubric-score">
                                                        {{ $rubricScore ?? 0 }}
                                                        <small>/ 4</small>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    @if ($answer->feedback)
                                        <div class="writing-ai-feedback">
                                            <p class="writing-ai-label">
                                                AI Feedback
                                            </p>

                                            <p class="writing-ai-copy">
                                                {!! nl2br(e(trim((string) $answer->feedback))) !!}
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </article>
                        @empty
                            <div class="writing-empty">
                                <span class="writing-empty-icon">
                                    <svg
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                        aria-hidden="true">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 012 2v3a2 2 0 01-2 2H4a2 2 0 01-2-2v-3a2 2 0 012-2m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                                        </path>
                                    </svg>
                                </span>

                                <h3 class="writing-empty-title">
                                    No Answer Details
                                </h3>

                                <p class="writing-empty-text">
                                    Answer details are not available.
                                </p>
                            </div>
                        @endforelse
                    </div>
                </section>

                <div class="writing-actions">
                    <a
                        href="{{ route('missions') }}"
                        class="writing-button writing-button-secondary">
                        Back to Missions
                    </a>

                    <a
                        href="{{ route('progress') }}"
                        class="writing-button writing-button-primary">
                        View Progress

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
                    </a>
                </div>
            </div>
        </div>
    @else
        @php
            $questionCount = $questions->count();
        @endphp

        <div class="writing-page">
            <div class="writing-shell">
                <section class="writing-card writing-assessment-header">
                    <div class="writing-assessment-copy">
                        <span class="writing-eyebrow">
                            {{ strtoupper($type) }} Assessment
                        </span>

                        <h1 class="writing-title">
                            {{ ucfirst($skill) }} Test
                        </h1>

                        <p class="writing-subtitle">
                            {{ $lesson->title }} Assessment
                        </p>
                    </div>

                    <div class="writing-assessment-stats">
                        <article class="writing-assessment-stat">
                            <div class="writing-assessment-stat-label">
                                Questions
                            </div>

                            <div class="writing-assessment-stat-value">
                                {{ $questionCount }}
                            </div>
                        </article>

                        <article class="writing-assessment-stat">
                            <div class="writing-assessment-stat-label">
                                Skill
                            </div>

                            <div class="writing-assessment-stat-value">
                                Writing
                            </div>
                        </article>
                    </div>
                </section>

                @if (session('success'))
                    <div class="writing-alert writing-alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="writing-alert writing-alert-error">
                        {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="writing-alert writing-alert-error">
                        <strong>Please check your answers.</strong>

                        <ul style="margin: 7px 0 0; padding-left: 20px;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($questionCount > 0)
                    <form
                        id="writing-assessment-form"
                        action="{{ route('student.assessment.submit', [
                            'type' => $type,
                            'skill' => $skill,
                        ]) }}"
                        method="POST"
                        data-total-questions="{{ $questionCount }}">
                        @csrf

                        <div class="writing-shell">
                            <section
                                id="writing-progress-card"
                                class="writing-card writing-progress">
                                <div class="writing-progress-row">
                                    <div>
                                        <p
                                            id="writing-progress-title"
                                            class="writing-progress-title"
                                            aria-live="polite">
                                            Question 1 of {{ $questionCount }}
                                        </p>

                                        <p class="writing-progress-description">
                                            Complete each question before continuing.
                                        </p>
                                    </div>

                                    <div class="writing-progress-badge">
                                        <span id="writing-progress-percent">
                                            {{ round(100 / $questionCount) }}
                                        </span>%
                                    </div>
                                </div>

                                <div class="writing-progress-track">
                                    <div
                                        id="writing-progress-bar"
                                        class="writing-progress-bar"
                                        style="width: {{ 100 / $questionCount }}%">
                                    </div>
                                </div>
                            </section>

                            @foreach ($questions as $index => $question)
                                <article
                                    data-writing-step
                                    data-step-index="{{ $index }}"
                                    @if ($index !== 0) hidden @endif
                                    class="writing-card writing-question-card">
                                    <div class="writing-question-head">
                                        <span class="writing-question-number">
                                            {{ $index + 1 }}
                                        </span>

                                        <div class="writing-question-copy">
                                            <p class="writing-question-label">
                                                Writing Question
                                            </p>

                                            <h2 class="writing-question-text">
                                                {{ $question->question }}
                                            </h2>
                                        </div>
                                    </div>

                                    @if (!empty($question->image))
                                        <figure class="writing-question-image">
                                            <a
                                                href="{{ asset('storage/' . $question->image) }}"
                                                target="_blank"
                                                rel="noopener noreferrer">
                                                <img
                                                    src="{{ asset('storage/' . $question->image) }}"
                                                    alt="Reference image for question {{ $index + 1 }}"
                                                    loading="{{ $index === 0 ? 'eager' : 'lazy' }}">
                                            </a>
                                        </figure>
                                    @endif

                                    <div class="writing-answer-field">
                                        <div class="writing-answer-field-head">
                                            <label
                                                for="answer-{{ $question->id }}"
                                                class="writing-answer-field-label">
                                                Your Writing Answer
                                            </label>

                                            <span
                                                data-word-count
                                                class="writing-word-count">
                                                0 words
                                            </span>
                                        </div>

                                        <textarea
                                            id="answer-{{ $question->id }}"
                                            name="answers[{{ $question->id }}]"
                                            rows="8"
                                            data-answer-input
                                            placeholder="Write your answer here..."
                                            class="writing-textarea">{{ old('answers.' . $question->id) }}</textarea>

                                        <div class="writing-answer-help-row">
                                            <span class="writing-answer-help">
                                                Write a complete and clear response in English.
                                            </span>

                                            <span
                                                data-answer-error
                                                class="writing-answer-error"
                                                hidden>
                                                Please write your answer before continuing.
                                            </span>
                                        </div>
                                    </div>
                                </article>
                            @endforeach

                            <section class="writing-card writing-navigation">
                                <div class="writing-navigation-inner">
                                    <div class="writing-navigation-group">
                                        <a
                                            href="{{ route('missions') }}"
                                            class="writing-button writing-button-secondary">
                                            Back
                                        </a>

                                        <button
                                            id="writing-previous-button"
                                            type="button"
                                            class="writing-button writing-button-secondary">
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

                                    <div class="writing-navigation-group">
                                        <button
                                            id="writing-next-button"
                                            type="button"
                                            class="writing-button writing-button-primary">
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
                                            id="writing-submit-button"
                                            type="submit"
                                            class="writing-button writing-button-submit">
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

                                            <span id="writing-submit-label">
                                                Submit Assessment
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </form>
                @else
                    <section class="writing-card writing-empty">
                        <span class="writing-empty-icon">
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

                        <h2 class="writing-empty-title">
                            No Questions Yet
                        </h2>

                        <p class="writing-empty-text">
                            The administrator has not added writing questions
                            for this assessment.
                        </p>

                        <a
                            href="{{ route('missions') }}"
                            class="writing-button writing-button-primary"
                            style="margin-top: 18px;">
                            Back to Missions
                        </a>
                    </section>
                @endif
            </div>
        </div>

        @if ($questionCount > 0)
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const form = document.getElementById(
                        'writing-assessment-form'
                    );

                    if (!form) {
                        return;
                    }

                    const steps = Array.from(
                        form.querySelectorAll('[data-writing-step]')
                    );

                    const previousButton = document.getElementById(
                        'writing-previous-button'
                    );

                    const nextButton = document.getElementById(
                        'writing-next-button'
                    );

                    const submitButton = document.getElementById(
                        'writing-submit-button'
                    );

                    const submitLabel = document.getElementById(
                        'writing-submit-label'
                    );

                    const progressCard = document.getElementById(
                        'writing-progress-card'
                    );

                    const progressTitle = document.getElementById(
                        'writing-progress-title'
                    );

                    const progressPercent = document.getElementById(
                        'writing-progress-percent'
                    );

                    const progressBar = document.getElementById(
                        'writing-progress-bar'
                    );

                    let currentStep = 0;
                    let isSubmitting = false;

                    function countWords(value) {
                        const cleanedValue = value.trim();

                        if (!cleanedValue) {
                            return 0;
                        }

                        return cleanedValue
                            .split(/\s+/)
                            .filter(Boolean)
                            .length;
                    }

                    function updateWordCounter(step) {
                        const textarea = step.querySelector(
                            '[data-answer-input]'
                        );

                        const counter = step.querySelector(
                            '[data-word-count]'
                        );

                        if (!textarea || !counter) {
                            return;
                        }

                        const wordCount = countWords(textarea.value);

                        counter.textContent =
                            wordCount +
                            (wordCount === 1 ? ' word' : ' words');
                    }

                    function clearStepError(step) {
                        const textarea = step.querySelector(
                            '[data-answer-input]'
                        );

                        const error = step.querySelector(
                            '[data-answer-error]'
                        );

                        if (textarea) {
                            textarea.classList.remove('is-error');
                        }

                        if (error) {
                            error.hidden = true;
                        }
                    }

                    function showStepError(step) {
                        const textarea = step.querySelector(
                            '[data-answer-input]'
                        );

                        const error = step.querySelector(
                            '[data-answer-error]'
                        );

                        if (textarea) {
                            textarea.classList.add('is-error');
                            textarea.focus();
                        }

                        if (error) {
                            error.hidden = false;
                        }
                    }

                    function validateStep(stepIndex) {
                        const step = steps[stepIndex];

                        if (!step) {
                            return false;
                        }

                        const textarea = step.querySelector(
                            '[data-answer-input]'
                        );

                        if (!textarea || textarea.value.trim() === '') {
                            showStepError(step);
                            return false;
                        }

                        clearStepError(step);
                        return true;
                    }

                    function scrollToAssessment() {
                        if (!progressCard) {
                            return;
                        }

                        const navbarOffset = 104;
                        const target =
                            progressCard.getBoundingClientRect().top +
                            window.scrollY -
                            navbarOffset;

                        window.scrollTo({
                            top: Math.max(0, target),
                            behavior: 'smooth'
                        });
                    }

                    function renderStep(shouldScroll = false) {
                        steps.forEach(function (step, index) {
                            step.hidden = index !== currentStep;
                        });

                        const displayedQuestion = currentStep + 1;
                        const percentage = Math.round(
                            (displayedQuestion / steps.length) * 100
                        );

                        progressTitle.textContent =
                            'Question ' +
                            displayedQuestion +
                            ' of ' +
                            steps.length;

                        progressPercent.textContent = percentage;
                        progressBar.style.width = percentage + '%';

                        previousButton.disabled = currentStep === 0;
                        nextButton.hidden =
                            currentStep === steps.length - 1;
                        submitButton.hidden =
                            currentStep !== steps.length - 1;

                        updateWordCounter(steps[currentStep]);

                        if (shouldScroll) {
                            scrollToAssessment();
                        }
                    }

                    steps.forEach(function (step) {
                        const textarea = step.querySelector(
                            '[data-answer-input]'
                        );

                        if (!textarea) {
                            return;
                        }

                        updateWordCounter(step);

                        textarea.addEventListener('input', function () {
                            updateWordCounter(step);

                            if (textarea.value.trim() !== '') {
                                clearStepError(step);
                            }
                        });
                    });

                    nextButton.addEventListener('click', function () {
                        if (!validateStep(currentStep)) {
                            return;
                        }

                        if (currentStep < steps.length - 1) {
                            currentStep++;
                            renderStep(true);
                        }
                    });

                    previousButton.addEventListener('click', function () {
                        if (currentStep > 0) {
                            currentStep--;
                            renderStep(true);
                        }
                    });

                    form.addEventListener('submit', function (event) {
                        if (isSubmitting) {
                            event.preventDefault();
                            return;
                        }

                        const firstInvalidStep = steps.findIndex(
                            function (step) {
                                const textarea = step.querySelector(
                                    '[data-answer-input]'
                                );

                                return !textarea ||
                                    textarea.value.trim() === '';
                            }
                        );

                        if (firstInvalidStep !== -1) {
                            event.preventDefault();
                            currentStep = firstInvalidStep;
                            renderStep(true);
                            showStepError(steps[firstInvalidStep]);
                            return;
                        }

                        isSubmitting = true;
                        submitButton.disabled = true;

                        if (submitLabel) {
                            submitLabel.textContent =
                                'Processing assessment...';
                        }
                    });

                    const firstIncompleteStep = steps.findIndex(
                        function (step) {
                            const textarea = step.querySelector(
                                '[data-answer-input]'
                            );

                            return !textarea ||
                                textarea.value.trim() === '';
                        }
                    );

                    currentStep =
                        firstIncompleteStep >= 0
                            ? firstIncompleteStep
                            : 0;

                    renderStep(false);
                });
            </script>
        @endif
    @endif
</x-app-layout>