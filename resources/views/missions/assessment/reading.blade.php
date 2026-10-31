<x-app-layout>
    <style>
        .reading-page {
            --page-surface: #f1f5f9;
            --card: #ffffff;
            --card-soft: #f8fafc;
            --card-muted: #f1f5f9;
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
            --shadow: 0 14px 35px rgba(15, 23, 42, 0.08);
            --shadow-soft: 0 6px 18px rgba(15, 23, 42, 0.06);

            color: var(--text);
        }

        html.dark .reading-page,
        body.dark .reading-page,
        .dark .reading-page,
        html[data-theme="dark"] .reading-page,
        body[data-theme="dark"] .reading-page {
            --page-surface: #020617;
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
            --shadow: 0 18px 42px rgba(0, 0, 0, 0.34);
            --shadow-soft: 0 8px 22px rgba(0, 0, 0, 0.24);
        }

        .reading-page,
        .reading-page * {
            box-sizing: border-box;
        }

        .reading-page [hidden],
        .reading-page [data-reading-group][hidden],
        .reading-page [data-question-step][hidden],
        .reading-page #reading-next-button[hidden],
        .reading-page #reading-submit-button[hidden] {
            display: none !important;
        }

        .reading-page .reading-shell {
            width: min(100%, 1480px);
            margin-inline: auto;
            display: grid;
            gap: 18px;
        }

        .reading-page .reading-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 24px;
            box-shadow: var(--shadow-soft);
        }

        .reading-page .reading-assessment-header {
            position: relative;
            overflow: hidden;
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: center;
            gap: 24px;
            padding: 24px 26px;
        }

        .reading-page .reading-assessment-header::after {
            content: "";
            position: absolute;
            width: 230px;
            height: 230px;
            right: -95px;
            top: -125px;
            border-radius: 999px;
            background: color-mix(in srgb, var(--accent) 14%, transparent);
            pointer-events: none;
        }

        .reading-page .reading-header-copy,
        .reading-page .reading-header-stats {
            position: relative;
            z-index: 1;
        }

        .reading-page .reading-eyebrow {
            display: inline-flex;
            align-items: center;
            min-height: 30px;
            padding: 6px 12px;
            border-radius: 999px;
            background: var(--accent-soft);
            color: var(--accent);
            font-size: 12px;
            font-weight: 900;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        .reading-page .reading-title {
            margin: 12px 0 0;
            color: var(--text);
            font-size: clamp(28px, 3vw, 40px);
            line-height: 1.08;
            font-weight: 900;
            letter-spacing: -0.035em;
        }

        .reading-page .reading-subtitle {
            margin: 7px 0 0;
            color: var(--text-muted);
            font-size: 15px;
            line-height: 1.55;
            font-weight: 600;
        }

        .reading-page .reading-header-stats {
            display: grid;
            grid-template-columns: repeat(2, minmax(112px, 1fr));
            gap: 10px;
        }

        .reading-page .reading-stat {
            min-width: 112px;
            padding: 14px 16px;
            border: 1px solid var(--border);
            border-radius: 16px;
            background: var(--card-soft);
        }

        .reading-page .reading-stat-label {
            color: var(--text-muted);
            font-size: 12px;
            line-height: 1.4;
            font-weight: 800;
        }

        .reading-page .reading-stat-value {
            margin-top: 3px;
            color: var(--text);
            font-size: 25px;
            line-height: 1;
            font-weight: 900;
        }

        .reading-page .reading-stat-value.is-accent {
            color: var(--accent);
        }

        .reading-page .reading-alert {
            padding: 14px 16px;
            border-radius: 16px;
            font-size: 14px;
            line-height: 1.6;
            font-weight: 700;
        }

        .reading-page .reading-alert-success {
            border: 1px solid color-mix(in srgb, var(--success) 34%, transparent);
            background: var(--success-soft);
            color: var(--success);
        }

        .reading-page .reading-alert-error {
            border: 1px solid color-mix(in srgb, var(--danger) 34%, transparent);
            background: var(--danger-soft);
            color: var(--danger);
        }

        .reading-page .reading-progress {
            padding: 17px 20px;
        }

        .reading-page .reading-progress-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .reading-page .reading-progress-title {
            margin: 0;
            color: var(--text);
            font-size: 15px;
            line-height: 1.4;
            font-weight: 900;
        }

        .reading-page .reading-progress-description {
            margin: 3px 0 0;
            color: var(--text-muted);
            font-size: 13px;
            line-height: 1.5;
            font-weight: 600;
        }

        .reading-page .reading-progress-badge {
            flex: 0 0 auto;
            min-width: 48px;
            padding: 9px 10px;
            border-radius: 14px;
            background: var(--accent-soft);
            color: var(--accent);
            text-align: center;
            font-size: 13px;
            font-weight: 900;
        }

        .reading-page .reading-progress-track {
            height: 8px;
            margin-top: 13px;
            overflow: hidden;
            border-radius: 999px;
            background: var(--card-muted);
        }

        .reading-page .reading-progress-bar {
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, var(--accent), var(--accent-strong));
            transition: width 240ms ease;
        }

        .reading-page .reading-stage {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 18px;
            align-items: start;
        }

        .reading-page .reading-panel {
            min-width: 0;
            overflow: hidden;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 22px;
            box-shadow: var(--shadow);
        }

        .reading-page .reading-passage-panel {
            align-self: start;
        }

        .reading-page .reading-panel-header {
            display: flex;
            align-items: center;
            gap: 14px;
            min-height: 82px;
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            background: var(--card);
        }

        .reading-page .reading-panel-icon,
        .reading-page .reading-question-number {
            display: inline-flex;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            width: 46px;
            height: 46px;
            border-radius: 14px;
            background: var(--accent-soft);
            color: var(--accent);
        }

        .reading-page .reading-panel-icon svg {
            width: 22px;
            height: 22px;
        }

        .reading-page .reading-panel-heading {
            min-width: 0;
            display: grid;
            gap: 4px;
        }

        .reading-page .reading-panel-kicker {
            margin: 0;
            color: var(--accent);
            font-size: 11px;
            line-height: 1.3;
            font-weight: 900;
            letter-spacing: 0.11em;
            text-transform: uppercase;
        }

        .reading-page .reading-panel-title {
            margin: 0;
            overflow: hidden;
            color: var(--text);
            font-size: 20px;
            line-height: 1.25;
            font-weight: 900;
            letter-spacing: -0.02em;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .reading-page .reading-panel-meta {
            color: var(--text-muted);
            font-size: 12px;
            line-height: 1.35;
            font-weight: 700;
        }

        .reading-page .reading-passage-body {
            padding: 20px;
        }

        .reading-page .reading-instruction {
            margin: 0 0 18px;
            padding: 14px 16px;
            border: 1px solid color-mix(in srgb, var(--accent) 34%, var(--border));
            border-radius: 15px;
            background: var(--accent-soft);
        }

        .reading-page .reading-instruction-label {
            margin: 0;
            color: var(--accent);
            font-size: 11px;
            line-height: 1.3;
            font-weight: 900;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        .reading-page .reading-instruction-text {
            margin: 5px 0 0;
            color: var(--text-soft);
            font-size: 14px;
            line-height: 1.65;
            font-weight: 600;
        }

        .reading-page .reading-material-image,
        .reading-page .reading-question-image {
            margin: 0 0 18px;
            overflow: hidden;
            border: 1px solid var(--border);
            border-radius: 16px;
            background: var(--card-soft);
        }

        .reading-page .reading-material-image img,
        .reading-page .reading-question-image img {
            display: block;
            width: 100%;
            max-height: 300px;
            object-fit: contain;
            padding: 10px;
        }

        .reading-page .reading-passage-copy {
            color: var(--text-soft);
            font-size: 15px;
            line-height: 1.82;
            font-weight: 500;
        }

        .reading-page .reading-passage-copy p {
            margin: 0 0 14px;
        }

        .reading-page .reading-passage-copy p:last-child {
            margin-bottom: 0;
        }

        .reading-page .reading-question-card {
            padding: 20px;
        }

        .reading-page .reading-question-head {
            display: flex;
            align-items: flex-start;
            gap: 13px;
        }

        .reading-page .reading-question-number {
            width: 42px;
            height: 42px;
            font-size: 15px;
            font-weight: 900;
        }

        .reading-page .reading-question-copy {
            min-width: 0;
            flex: 1;
        }

        .reading-page .reading-question-label {
            margin: 1px 0 0;
            color: var(--accent);
            font-size: 11px;
            line-height: 1.3;
            font-weight: 900;
            letter-spacing: 0.11em;
            text-transform: uppercase;
        }

        .reading-page .reading-question-text {
            margin: 7px 0 0;
            color: var(--text);
            font-size: 18px;
            line-height: 1.48;
            font-weight: 900;
            letter-spacing: -0.018em;
        }

        .reading-page .reading-question-help {
            margin: 5px 0 0;
            color: var(--text-muted);
            font-size: 13px;
            line-height: 1.5;
            font-weight: 600;
        }

        .reading-page .reading-question-image {
            margin-top: 16px;
            margin-bottom: 0;
        }

        .reading-page .reading-options {
            display: grid;
            gap: 9px;
            margin-top: 18px;
        }

        .reading-page .reading-option {
            position: relative;
            display: grid;
            grid-template-columns: 34px minmax(0, 1fr) 22px;
            align-items: center;
            gap: 11px;
            min-height: 56px;
            padding: 10px 13px;
            border: 1px solid var(--border);
            border-radius: 15px;
            background: var(--card-soft);
            cursor: pointer;
            transition:
                border-color 160ms ease,
                background-color 160ms ease,
                transform 160ms ease,
                box-shadow 160ms ease;
        }

        .reading-page .reading-option:hover {
            transform: translateY(-1px);
            border-color: color-mix(in srgb, var(--accent) 55%, var(--border));
            background: color-mix(in srgb, var(--accent-soft) 62%, var(--card));
            box-shadow: var(--shadow-soft);
        }

        .reading-page .reading-option:focus-within {
            outline: 3px solid color-mix(in srgb, var(--accent) 24%, transparent);
            outline-offset: 2px;
        }

        .reading-page .reading-option.is-selected,
        .reading-page .reading-option:has(.reading-option-input:checked) {
            border-color: var(--accent);
            background: var(--accent-soft);
            box-shadow: 0 0 0 1px color-mix(in srgb, var(--accent) 24%, transparent);
        }

        .reading-page .reading-option-input {
            position: absolute;
            width: 1px;
            height: 1px;
            overflow: hidden;
            opacity: 0;
            pointer-events: none;
        }

        .reading-page .reading-option-letter {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border: 1px solid var(--border-strong);
            border-radius: 10px;
            background: var(--card);
            color: var(--text-soft);
            font-size: 13px;
            font-weight: 900;
            transition: all 160ms ease;
        }

        .reading-page .reading-option.is-selected .reading-option-letter,
        .reading-page .reading-option:has(.reading-option-input:checked) .reading-option-letter {
            border-color: var(--accent);
            background: var(--accent);
            color: #ffffff;
        }

        .reading-page .reading-option-text {
            min-width: 0;
            color: var(--text-soft);
            font-size: 14px;
            line-height: 1.55;
            font-weight: 650;
        }

        .reading-page .reading-option.is-selected .reading-option-text,
        .reading-page .reading-option:has(.reading-option-input:checked) .reading-option-text {
            color: var(--text);
        }

        .reading-page .reading-option-check {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            height: 22px;
            border-radius: 999px;
            color: transparent;
            transition: all 160ms ease;
        }

        .reading-page .reading-option-check svg {
            width: 15px;
            height: 15px;
        }

        .reading-page .reading-option.is-selected .reading-option-check,
        .reading-page .reading-option:has(.reading-option-input:checked) .reading-option-check {
            background: var(--accent);
            color: #ffffff;
        }

        .reading-page .reading-answer-error {
            margin-top: 12px;
            padding: 11px 13px;
            border: 1px solid color-mix(in srgb, var(--danger) 38%, transparent);
            border-radius: 13px;
            background: var(--danger-soft);
            color: var(--danger);
            font-size: 13px;
            line-height: 1.5;
            font-weight: 800;
        }

        .reading-page .reading-navigation {
            padding: 14px;
        }

        .reading-page .reading-navigation-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
        }

        .reading-page .reading-navigation-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .reading-page .reading-button {
            display: inline-flex;
            min-height: 52px;
            align-items: center;
            justify-content: center;
            gap: 9px;
            padding: 12px 19px;
            border: 1px solid transparent;
            border-radius: 14px;
            font-family: inherit;
            font-size: 15px;
            line-height: 1;
            font-weight: 900;
            text-decoration: none;
            cursor: pointer;
            transition:
                transform 160ms ease,
                background-color 160ms ease,
                border-color 160ms ease,
                box-shadow 160ms ease,
                opacity 160ms ease;
        }

        .reading-page .reading-button svg {
            width: 18px;
            height: 18px;
        }

        .reading-page .reading-button:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: var(--shadow-soft);
        }

        .reading-page .reading-button:focus-visible {
            outline: 3px solid color-mix(in srgb, var(--accent) 28%, transparent);
            outline-offset: 2px;
        }

        .reading-page .reading-button:disabled {
            cursor: not-allowed;
            opacity: 0.45;
            transform: none;
            box-shadow: none;
        }

        .reading-page .reading-button-secondary {
            border-color: var(--border);
            background: var(--card-soft);
            color: var(--text-soft);
        }

        .reading-page .reading-button-secondary:hover:not(:disabled) {
            border-color: var(--border-strong);
            background: var(--card-muted);
        }

        .reading-page .reading-button-primary {
            min-width: 205px;
            background: linear-gradient(100deg, var(--accent), var(--accent-strong));
            color: #ffffff;
        }

        .reading-page .reading-button-submit {
            min-width: 220px;
            background: linear-gradient(100deg, #059669, #0891b2);
            color: #ffffff;
        }

        .reading-page .reading-empty {
            padding: 42px 22px;
            text-align: center;
        }

        .reading-page .reading-empty-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 58px;
            height: 58px;
            border-radius: 17px;
            background: var(--card-muted);
            color: var(--text-muted);
        }

        .reading-page .reading-empty-icon svg {
            width: 28px;
            height: 28px;
        }

        .reading-page .reading-empty-title {
            margin: 16px 0 0;
            color: var(--text);
            font-size: 20px;
            font-weight: 900;
        }

        .reading-page .reading-empty-text {
            max-width: 520px;
            margin: 7px auto 0;
            color: var(--text-muted);
            font-size: 14px;
            line-height: 1.65;
            font-weight: 600;
        }

        .reading-page .reading-result-hero {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: center;
            gap: 22px;
            padding: 24px;
        }

        .reading-page .reading-score-card {
            min-width: 190px;
            padding: 17px 19px;
            border: 1px solid var(--border);
            border-radius: 18px;
            background: var(--card-soft);
        }

        .reading-page .reading-score-label {
            color: var(--text-muted);
            font-size: 12px;
            font-weight: 800;
        }

        .reading-page .reading-score-value {
            margin-top: 4px;
            color: var(--accent);
            font-size: 46px;
            line-height: 1;
            font-weight: 900;
        }

        .reading-page .reading-score-value small {
            color: var(--text-muted);
            font-size: 13px;
        }

        .reading-page .reading-result-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .reading-page .reading-result-metric {
            padding: 17px;
        }

        .reading-page .reading-result-label {
            color: var(--text-muted);
            font-size: 12px;
            font-weight: 800;
        }

        .reading-page .reading-result-value {
            margin-top: 8px;
            color: var(--text);
            font-size: 18px;
            line-height: 1.35;
            font-weight: 900;
        }

        .reading-page .reading-status {
            display: inline-flex;
            padding: 7px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 900;
        }

        .reading-page .reading-status-completed {
            background: var(--success-soft);
            color: var(--success);
        }

        .reading-page .reading-status-failed {
            background: var(--danger-soft);
            color: var(--danger);
        }

        .reading-page .reading-status-pending {
            background: var(--warning-soft);
            color: var(--warning);
        }

        .reading-page .reading-result-section {
            padding: 20px;
        }

        .reading-page .reading-section-title {
            margin: 0;
            color: var(--text);
            font-size: 19px;
            font-weight: 900;
        }

        .reading-page .reading-section-subtitle {
            margin: 4px 0 0;
            color: var(--text-muted);
            font-size: 13px;
            font-weight: 600;
        }

        .reading-page .reading-feedback-box {
            margin-top: 14px;
            padding: 15px;
            border-radius: 15px;
            background: var(--card-soft);
            color: var(--text-soft);
            font-size: 14px;
            line-height: 1.75;
            font-weight: 600;
        }

        .reading-page .reading-answer-list {
            margin-top: 14px;
            display: grid;
            gap: 10px;
        }

        .reading-page .reading-answer-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 14px 15px;
            border: 1px solid var(--border);
            border-radius: 15px;
            background: var(--card-soft);
        }

        .reading-page .reading-answer-main {
            display: flex;
            align-items: center;
            gap: 11px;
            min-width: 0;
        }

        .reading-page .reading-answer-number {
            display: inline-flex;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: var(--accent-soft);
            color: var(--accent);
            font-size: 13px;
            font-weight: 900;
        }

        .reading-page .reading-answer-title {
            color: var(--text);
            font-size: 14px;
            font-weight: 900;
        }

        .reading-page .reading-answer-selected {
            margin-top: 2px;
            color: var(--text-muted);
            font-size: 12px;
            font-weight: 700;
        }

        .reading-page .reading-answer-badges {
            display: flex;
            align-items: center;
            gap: 7px;
            flex: 0 0 auto;
        }

        .reading-page .reading-answer-badge {
            display: inline-flex;
            padding: 6px 9px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 900;
        }

        .reading-page .reading-answer-correct {
            background: var(--success-soft);
            color: var(--success);
        }

        .reading-page .reading-answer-wrong {
            background: var(--danger-soft);
            color: var(--danger);
        }

        .reading-page .reading-answer-score {
            background: var(--card-muted);
            color: var(--text-soft);
        }

        .reading-page .reading-result-actions {
            display: flex;
            justify-content: space-between;
            gap: 12px;
        }

        @media (min-width: 1080px) {
            .reading-page .reading-stage {
                grid-template-columns: minmax(0, 56fr) minmax(390px, 44fr);
            }

            .reading-page .reading-passage-panel {
                position: sticky;
                top: 104px;
            }

            .reading-page .reading-passage-body {
                max-height: calc(100vh - 286px);
                overflow-y: auto;
                overscroll-behavior: contain;
                scrollbar-width: thin;
                scrollbar-color: var(--border-strong) transparent;
            }

            .reading-page .reading-passage-body::-webkit-scrollbar {
                width: 8px;
            }

            .reading-page .reading-passage-body::-webkit-scrollbar-track {
                background: transparent;
            }

            .reading-page .reading-passage-body::-webkit-scrollbar-thumb {
                border-radius: 999px;
                background: var(--border-strong);
            }
        }

        @media (max-width: 760px) {
            .reading-page .reading-shell {
                gap: 14px;
            }

            .reading-page .reading-assessment-header,
            .reading-page .reading-result-hero {
                grid-template-columns: minmax(0, 1fr);
                padding: 19px;
            }

            .reading-page .reading-header-stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .reading-page .reading-stat {
                min-width: 0;
            }

            .reading-page .reading-progress {
                padding: 15px 16px;
            }

            .reading-page .reading-panel-header {
                min-height: 74px;
                padding: 14px 16px;
            }

            .reading-page .reading-passage-body,
            .reading-page .reading-question-card {
                padding: 16px;
            }

            .reading-page .reading-panel-title {
                font-size: 18px;
            }

            .reading-page .reading-question-text {
                font-size: 16px;
            }

            .reading-page .reading-option {
                grid-template-columns: 32px minmax(0, 1fr) 20px;
                min-height: 54px;
                padding: 9px 11px;
            }

            .reading-page .reading-option-letter {
                width: 32px;
                height: 32px;
            }

            .reading-page .reading-navigation-inner,
            .reading-page .reading-navigation-group,
            .reading-page .reading-result-actions {
                width: 100%;
                flex-direction: column;
                align-items: stretch;
            }

            .reading-page .reading-button,
            .reading-page .reading-button-primary,
            .reading-page .reading-button-submit {
                width: 100%;
                min-width: 0;
            }

            .reading-page .reading-result-grid {
                grid-template-columns: minmax(0, 1fr);
            }

            .reading-page .reading-score-card {
                min-width: 0;
            }

            .reading-page .reading-answer-item {
                align-items: flex-start;
                flex-direction: column;
            }
        }


        /* =========================================================
           COMPACT RESULT PAGE
           ========================================================= */
        .reading-page.reading-result-page .reading-shell {
            width: min(100%, 1180px);
            gap: 12px;
        }

        .reading-page .reading-result-summary-card {
            overflow: hidden;
        }

        .reading-page .reading-result-summary-main {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: center;
            gap: 22px;
            padding: 18px 20px;
        }

        .reading-page .reading-result-heading {
            display: flex;
            align-items: center;
            gap: 14px;
            min-width: 0;
        }

        .reading-page .reading-result-heading-icon {
            display: inline-flex;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            width: 46px;
            height: 46px;
            border-radius: 14px;
            background: var(--accent-soft);
            color: var(--accent);
        }

        .reading-page .reading-result-heading-icon svg {
            width: 22px;
            height: 22px;
        }

        .reading-page .reading-result-heading-copy {
            min-width: 0;
        }

        .reading-page .reading-result-heading-copy .reading-eyebrow {
            min-height: 24px;
            padding: 4px 9px;
            font-size: 10px;
        }

        .reading-page .reading-result-heading-copy .reading-title {
            margin-top: 7px;
            font-size: clamp(24px, 2.5vw, 32px);
            line-height: 1.08;
        }

        .reading-page .reading-result-heading-copy .reading-subtitle {
            margin-top: 4px;
            font-size: 13px;
        }

        .reading-page .reading-result-score {
            min-width: 150px;
            padding: 13px 16px;
            border: 1px solid var(--border);
            border-radius: 16px;
            background: var(--card-soft);
        }

        .reading-page .reading-result-score-label {
            color: var(--text-muted);
            font-size: 11px;
            font-weight: 800;
        }

        .reading-page .reading-result-score-row {
            display: flex;
            align-items: flex-end;
            gap: 5px;
            margin-top: 3px;
        }

        .reading-page .reading-result-score-value {
            color: var(--accent);
            font-size: 38px;
            line-height: 0.95;
            font-weight: 900;
            letter-spacing: -0.04em;
        }

        .reading-page .reading-result-score-max {
            padding-bottom: 3px;
            color: var(--text-muted);
            font-size: 11px;
            font-weight: 800;
        }

        .reading-page .reading-result-score.is-good .reading-result-score-value {
            color: var(--success);
        }

        .reading-page .reading-result-score.is-average .reading-result-score-value {
            color: var(--accent-strong);
        }

        .reading-page .reading-result-score.is-low .reading-result-score-value {
            color: var(--warning);
        }

        .reading-page .reading-result-metrics-row {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            border-top: 1px solid var(--border);
            background: var(--card-soft);
        }

        .reading-page .reading-result-metric-compact {
            display: flex;
            align-items: center;
            gap: 11px;
            min-width: 0;
            padding: 13px 18px;
        }

        .reading-page .reading-result-metric-compact + .reading-result-metric-compact {
            border-left: 1px solid var(--border);
        }

        .reading-page .reading-result-metric-icon {
            display: inline-flex;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: var(--card);
            color: var(--accent);
            border: 1px solid var(--border);
        }

        .reading-page .reading-result-metric-icon svg {
            width: 17px;
            height: 17px;
        }

        .reading-page .reading-result-metric-copy {
            min-width: 0;
        }

        .reading-page .reading-result-metric-label {
            color: var(--text-muted);
            font-size: 10px;
            line-height: 1.3;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.07em;
        }

        .reading-page .reading-result-metric-value {
            margin-top: 2px;
            overflow: hidden;
            color: var(--text);
            font-size: 14px;
            line-height: 1.35;
            font-weight: 900;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .reading-page .reading-feedback-compact {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr);
            align-items: start;
            gap: 13px;
            padding: 15px 17px;
        }

        .reading-page .reading-feedback-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: 12px;
            background: var(--accent-soft);
            color: var(--accent);
        }

        .reading-page .reading-feedback-icon svg {
            width: 19px;
            height: 19px;
        }

        .reading-page .reading-feedback-content {
            min-width: 0;
        }

        .reading-page .reading-feedback-heading {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 12px;
        }

        .reading-page .reading-feedback-heading .reading-section-title {
            font-size: 16px;
        }

        .reading-page .reading-feedback-text {
            margin: 5px 0 0;
            color: var(--text-soft);
            font-size: 13px;
            line-height: 1.65;
            font-weight: 600;
        }

        .reading-page .reading-answer-section-compact {
            padding: 16px;
        }

        .reading-page .reading-answer-section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 12px;
        }

        .reading-page .reading-answer-section-header .reading-section-title {
            font-size: 17px;
        }

        .reading-page .reading-answer-section-header .reading-section-subtitle {
            margin-top: 2px;
            font-size: 12px;
        }

        .reading-page .reading-answer-count {
            flex: 0 0 auto;
            padding: 7px 10px;
            border-radius: 999px;
            background: var(--accent-soft);
            color: var(--accent);
            font-size: 11px;
            font-weight: 900;
        }

        .reading-page .reading-answer-list-compact {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
        }

        .reading-page .reading-answer-item-compact {
            display: grid;
            grid-template-columns: 32px minmax(0, 1fr) auto;
            align-items: center;
            gap: 10px;
            min-width: 0;
            min-height: 58px;
            padding: 9px 11px;
            border: 1px solid var(--border);
            border-radius: 13px;
            background: var(--card-soft);
            transition: border-color 150ms ease, background-color 150ms ease;
        }

        .reading-page .reading-answer-item-compact:hover {
            border-color: var(--border-strong);
            background: var(--card-muted);
        }

        .reading-page .reading-answer-number-compact {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 9px;
            background: var(--accent-soft);
            color: var(--accent);
            font-size: 12px;
            font-weight: 900;
        }

        .reading-page .reading-answer-info-compact {
            min-width: 0;
        }

        .reading-page .reading-answer-title-compact {
            color: var(--text);
            font-size: 13px;
            line-height: 1.35;
            font-weight: 900;
        }

        .reading-page .reading-answer-selected-compact {
            margin-top: 2px;
            color: var(--text-muted);
            font-size: 11px;
            line-height: 1.35;
            font-weight: 700;
        }

        .reading-page .reading-answer-selected-compact strong {
            color: var(--text-soft);
        }

        .reading-page .reading-answer-result-compact {
            display: flex;
            align-items: center;
            gap: 6px;
            flex: 0 0 auto;
        }

        .reading-page .reading-answer-badge-compact {
            display: inline-flex;
            align-items: center;
            min-height: 24px;
            padding: 5px 8px;
            border-radius: 999px;
            font-size: 10px;
            line-height: 1;
            font-weight: 900;
        }

        .reading-page .reading-result-actions-compact {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
            padding-top: 2px;
        }

        .reading-page .reading-result-actions-compact .reading-button {
            min-height: 46px;
            padding: 10px 16px;
            font-size: 13px;
        }

        .reading-page .reading-result-actions-compact .reading-button-primary {
            min-width: 156px;
        }

        @media (max-width: 900px) {
            .reading-page .reading-answer-list-compact {
                grid-template-columns: minmax(0, 1fr);
            }
        }

        @media (max-width: 700px) {
            .reading-page .reading-result-summary-main {
                grid-template-columns: minmax(0, 1fr);
                padding: 16px;
            }

            .reading-page .reading-result-score {
                width: 100%;
                min-width: 0;
            }

            .reading-page .reading-result-metrics-row {
                grid-template-columns: minmax(0, 1fr);
            }

            .reading-page .reading-result-metric-compact + .reading-result-metric-compact {
                border-left: 0;
                border-top: 1px solid var(--border);
            }

            .reading-page .reading-feedback-compact {
                padding: 14px;
            }

            .reading-page .reading-answer-section-header {
                align-items: flex-start;
            }

            .reading-page .reading-answer-item-compact {
                grid-template-columns: 32px minmax(0, 1fr);
            }

            .reading-page .reading-answer-result-compact {
                grid-column: 2;
                justify-content: flex-start;
            }

            .reading-page .reading-result-actions-compact {
                flex-direction: column-reverse;
                align-items: stretch;
            }

            .reading-page .reading-result-actions-compact .reading-button,
            .reading-page .reading-result-actions-compact .reading-button-primary {
                width: 100%;
                min-width: 0;
            }
        }

    </style>

    @if (isset($submission))
        @php
            $score = (int) ($submission->final_score ?? 0);

            $statusClass = match ($submission->status) {
                'completed' => 'reading-status-completed',
                'failed' => 'reading-status-failed',
                default => 'reading-status-pending',
            };

            $scoreClass = match (true) {
                $score >= 80 => 'is-good',
                $score >= 60 => 'is-average',
                default => 'is-low',
            };
        @endphp

        <div class="reading-page reading-result-page">
            <div class="reading-shell">
                @if (session('success'))
                    <div class="reading-alert reading-alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <section class="reading-card reading-result-summary-card">
                    <div class="reading-result-summary-main">
                        <div class="reading-result-heading">
                            <div class="reading-result-heading-icon">
                                <svg
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z">
                                    </path>
                                </svg>
                            </div>

                            <div class="reading-result-heading-copy">
                                <span class="reading-eyebrow">Assessment Result</span>

                                <h1 class="reading-title">
                                    {{ strtoupper($submission->type) }} Reading
                                </h1>

                                <p class="reading-subtitle">
                                    {{ $submission->lesson->title ?? 'Reading Assessment' }}
                                </p>
                            </div>
                        </div>

                        <div class="reading-result-score {{ $scoreClass }}">
                            <div class="reading-result-score-label">Final Score</div>

                            <div class="reading-result-score-row">
                                <span class="reading-result-score-value">
                                    {{ $score }}
                                </span>

                                <span class="reading-result-score-max">
                                    / 100
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="reading-result-metrics-row">
                        <div class="reading-result-metric-compact">
                            <span class="reading-result-metric-icon">
                                <svg
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                                    </path>
                                </svg>
                            </span>

                            <div class="reading-result-metric-copy">
                                <div class="reading-result-metric-label">Status</div>

                                <div class="reading-result-metric-value">
                                    <span class="reading-status {{ $statusClass }}">
                                        {{ ucfirst($submission->status) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="reading-result-metric-compact">
                            <span class="reading-result-metric-icon">
                                <svg
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                            </span>

                            <div class="reading-result-metric-copy">
                                <div class="reading-result-metric-label">Submitted At</div>

                                <div class="reading-result-metric-value">
                                    {{ $submission->submitted_at?->format('d M Y, H:i') ??
                                        $submission->created_at->format('d M Y, H:i') }}
                                </div>
                            </div>
                        </div>

                        <div class="reading-result-metric-compact">
                            <span class="reading-result-metric-icon">
                                <svg
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 8l2 2 4-4">
                                    </path>
                                </svg>
                            </span>

                            <div class="reading-result-metric-copy">
                                <div class="reading-result-metric-label">Total Answers</div>

                                <div class="reading-result-metric-value">
                                    {{ $submission->answers->count() }} answers
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="reading-card reading-feedback-compact">
                    <div class="reading-feedback-icon">
                        <svg
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.86 9.86 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                            </path>
                        </svg>
                    </div>

                    <div class="reading-feedback-content">
                        <div class="reading-feedback-heading">
                            <h2 class="reading-section-title">Feedback</h2>
                        </div>

                        <p class="reading-feedback-text">
                            {!! nl2br(e(trim((string) ($submission->feedback ?? 'No feedback is available yet.')))) !!}
                        </p>
                    </div>
                </section>

                <section class="reading-card reading-answer-section-compact">
                    <div class="reading-answer-section-header">
                        <div>
                            <h2 class="reading-section-title">Answer Details</h2>

                            <p class="reading-section-subtitle">
                                Review every submitted answer and its score.
                            </p>
                        </div>

                        <span class="reading-answer-count">
                            {{ $submission->answers->count() }} answers
                        </span>
                    </div>

                    <div class="reading-answer-list-compact">
                        @forelse ($submission->answers as $index => $answer)
                            <article class="reading-answer-item-compact">
                                <span class="reading-answer-number-compact">
                                    {{ $index + 1 }}
                                </span>

                                <div class="reading-answer-info-compact">
                                    <div class="reading-answer-title-compact">
                                        Question {{ $index + 1 }}
                                    </div>

                                    <div class="reading-answer-selected-compact">
                                        Selected answer:
                                        <strong>{{ $answer->selected_option ?? '-' }}</strong>
                                    </div>
                                </div>

                                <div class="reading-answer-result-compact">
                                    @if ($answer->is_correct)
                                        <span class="reading-answer-badge-compact reading-answer-correct">
                                            Correct
                                        </span>
                                    @else
                                        <span class="reading-answer-badge-compact reading-answer-wrong">
                                            Wrong
                                        </span>
                                    @endif

                                    <span class="reading-answer-badge-compact reading-answer-score">
                                        {{ $answer->score ?? 0 }}/{{ $answer->max_score ?? 0 }}
                                    </span>
                                </div>
                            </article>
                        @empty
                            <div class="reading-empty" style="grid-column: 1 / -1;">
                                <div class="reading-empty-icon">
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
                                </div>

                                <h3 class="reading-empty-title">No Answer Details</h3>

                                <p class="reading-empty-text">
                                    Answer details are not available.
                                </p>
                            </div>
                        @endforelse
                    </div>
                </section>

                <div class="reading-result-actions-compact">
                    <a
                        href="{{ route('missions') }}"
                        class="reading-button reading-button-secondary">
                        Back to Missions
                    </a>

                    <a
                        href="{{ route('progress') }}"
                        class="reading-button reading-button-primary">
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
            $materialCount = $materials->count();
            $questionNumber = 0;
        @endphp

        <div class="reading-page">
            <div class="reading-shell">
                <section class="reading-card reading-assessment-header">
                    <div class="reading-header-copy">
                        <span class="reading-eyebrow">
                            {{ strtoupper($type) }} Assessment
                        </span>

                        <h1 class="reading-title">Reading Test</h1>

                        <p class="reading-subtitle">
                            {{ $lesson->title }} Assessment
                        </p>
                    </div>

                    <div class="reading-header-stats">
                        <article class="reading-stat">
                            <div class="reading-stat-label">Passages</div>
                            <div class="reading-stat-value">{{ $materialCount }}</div>
                        </article>

                        <article class="reading-stat">
                            <div class="reading-stat-label">Questions</div>
                            <div class="reading-stat-value is-accent">{{ $questionCount }}</div>
                        </article>
                    </div>
                </section>

                @if (session('success'))
                    <div class="reading-alert reading-alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="reading-alert reading-alert-error">
                        {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="reading-alert reading-alert-error">
                        <strong>Please check your answers.</strong>

                        <ul style="margin: 7px 0 0; padding-left: 20px;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($materialCount > 0 && $questionCount > 0)
                    <form
                        id="reading-assessment-form"
                        action="{{ route('student.assessment.submit', [
                            'type' => $type,
                            'skill' => $skill,
                        ]) }}"
                        method="POST"
                        novalidate>
                        @csrf

                        <div class="reading-shell">
                            <section
                                id="reading-progress-card"
                                class="reading-card reading-progress">
                                <div class="reading-progress-row">
                                    <div>
                                        <p
                                            id="reading-progress-title"
                                            class="reading-progress-title"
                                            aria-live="polite">
                                            Question 1 of {{ $questionCount }}
                                        </p>

                                        <p
                                            id="reading-progress-description"
                                            class="reading-progress-description">
                                            Passage 1 - Read the passage and select one answer.
                                        </p>
                                    </div>

                                    <div class="reading-progress-badge">
                                        <span id="reading-progress-percent">
                                            {{ round(100 / $questionCount) }}
                                        </span>%
                                    </div>
                                </div>

                                <div class="reading-progress-track">
                                    <div
                                        id="reading-progress-bar"
                                        class="reading-progress-bar"
                                        style="width: {{ 100 / $questionCount }}%">
                                    </div>
                                </div>
                            </section>

                            @foreach ($materials as $materialIndex => $material)
                                @php
                                    $instructionText = preg_replace(
                                        '/\s+/u',
                                        ' ',
                                        trim((string) ($material->instruction ?? ''))
                                    );

                                    $rawPassage = str_replace(
                                        ["\r\n", "\r"],
                                        "\n",
                                        trim((string) ($material->passage ?? ''))
                                    );

                                    $rawPassage = preg_replace(
                                        '/([.!?])(?=[A-Z])/u',
                                        '$1 ',
                                        $rawPassage
                                    );

                                    $passageParagraphs = preg_split(
                                        '/\n\s*\n/u',
                                        $rawPassage,
                                        -1,
                                        PREG_SPLIT_NO_EMPTY
                                    ) ?: [];
                                @endphp

                                <section
                                    data-reading-group
                                    data-group-index="{{ $materialIndex }}"
                                    @if ($materialIndex !== 0) hidden @endif
                                    class="reading-stage">
                                    <article class="reading-panel reading-passage-panel">
                                        <header class="reading-panel-header">
                                            <div class="reading-panel-icon">
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
                                            </div>

                                            <div class="reading-panel-heading">
                                                <p class="reading-panel-kicker">
                                                    Reading Passage
                                                </p>

                                                <h2 class="reading-panel-title">
                                                    {{ $material->title }}
                                                </h2>

                                                <div class="reading-panel-meta">
                                                    Passage {{ $materialIndex + 1 }} of {{ $materialCount }}
                                                </div>
                                            </div>
                                        </header>

                                        <div class="reading-passage-body">
                                            @if ($instructionText !== '')
                                                <div class="reading-instruction">
                                                    <p class="reading-instruction-label">
                                                        Instruction
                                                    </p>

                                                    <p class="reading-instruction-text">
                                                        {{ $instructionText }}
                                                    </p>
                                                </div>
                                            @endif

                                            @if (!empty($material->image))
                                                <figure class="reading-material-image">
                                                    <img
                                                        src="{{ asset('storage/' . $material->image) }}"
                                                        alt="{{ $material->title }}"
                                                        loading="{{ $materialIndex === 0 ? 'eager' : 'lazy' }}">
                                                </figure>
                                            @endif

                                            <div class="reading-passage-copy">
                                                @forelse ($passageParagraphs as $paragraph)
                                                    <p>
                                                        {{ preg_replace('/\s+/u', ' ', trim($paragraph)) }}
                                                    </p>
                                                @empty
                                                    <p>Passage is not available.</p>
                                                @endforelse
                                            </div>
                                        </div>
                                    </article>

                                    <div>
                                        @forelse ($material->questions as $question)
                                            @php
                                                $questionNumber++;
                                                $currentStepIndex = $questionNumber - 1;
                                            @endphp

                                            <article
                                                data-question-step
                                                data-step-index="{{ $currentStepIndex }}"
                                                data-question-number="{{ $questionNumber }}"
                                                data-passage-number="{{ $materialIndex + 1 }}"
                                                @if ($questionNumber !== 1) hidden @endif
                                                class="reading-panel reading-question-card">
                                                <div class="reading-question-head">
                                                    <div class="reading-question-number">
                                                        {{ $questionNumber }}
                                                    </div>

                                                    <div class="reading-question-copy">
                                                        <p class="reading-question-label">
                                                            Reading Question
                                                        </p>

                                                        <h3 class="reading-question-text">
                                                            {{ $question->question }}
                                                        </h3>

                                                        <p class="reading-question-help">
                                                            Select one answer.
                                                        </p>
                                                    </div>
                                                </div>

                                                @if (!empty($question->image))
                                                    <figure class="reading-question-image">
                                                        <img
                                                            src="{{ asset('storage/' . $question->image) }}"
                                                            alt="Question {{ $questionNumber }} image"
                                                            loading="lazy">
                                                    </figure>
                                                @endif

                                                <div
                                                    data-answer-options
                                                    class="reading-options">
                                                    @foreach (['A', 'B', 'C', 'D', 'E'] as $option)
                                                        @php
                                                            $field = 'option_' . strtolower($option);
                                                            $optionValue = $question->$field;
                                                            $isChecked = old(
                                                                'answers.' . $question->id
                                                            ) === $option;
                                                        @endphp

                                                        @if (!empty($optionValue))
                                                            <label
                                                                class="reading-option {{ $isChecked ? 'is-selected' : '' }}">
                                                                <input
                                                                    type="radio"
                                                                    name="answers[{{ $question->id }}]"
                                                                    value="{{ $option }}"
                                                                    @checked($isChecked)
                                                                    class="reading-option-input">

                                                                <span class="reading-option-letter">
                                                                    {{ $option }}
                                                                </span>

                                                                <span class="reading-option-text">
                                                                    {{ $optionValue }}
                                                                </span>

                                                                <span class="reading-option-check">
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
                                                    class="reading-answer-error"
                                                    hidden>
                                                    Please select one answer before continuing.
                                                </div>
                                            </article>
                                        @empty
                                            <article class="reading-panel reading-empty">
                                                <div class="reading-empty-icon">
                                                    <svg
                                                        fill="none"
                                                        stroke="currentColor"
                                                        viewBox="0 0 24 24"
                                                        aria-hidden="true">
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z">
                                                        </path>
                                                    </svg>
                                                </div>

                                                <h3 class="reading-empty-title">
                                                    No Questions
                                                </h3>

                                                <p class="reading-empty-text">
                                                    This passage does not have any questions.
                                                </p>
                                            </article>
                                        @endforelse
                                    </div>
                                </section>
                            @endforeach

                            <section class="reading-card reading-navigation">
                                <div class="reading-navigation-inner">
                                    <div class="reading-navigation-group">
                                        <a
                                            href="{{ route('missions') }}"
                                            class="reading-button reading-button-secondary">
                                            Back
                                        </a>

                                        <button
                                            id="reading-previous-button"
                                            type="button"
                                            class="reading-button reading-button-secondary">
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

                                    <div class="reading-navigation-group">
                                        <button
                                            id="reading-next-button"
                                            type="button"
                                            class="reading-button reading-button-primary">
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
                                            id="reading-submit-button"
                                            type="submit"
                                            class="reading-button reading-button-submit">
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

                                            <span id="reading-submit-label">
                                                Submit Assessment
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </form>
                @else
                    <section class="reading-card reading-empty">
                        <div class="reading-empty-icon">
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
                        </div>

                        <h2 class="reading-empty-title">
                            No Reading Materials Yet
                        </h2>

                        <p class="reading-empty-text">
                            The administrator has not added reading passages and questions
                            for this assessment.
                        </p>

                        <a
                            href="{{ route('missions') }}"
                            class="reading-button reading-button-primary"
                            style="margin-top: 18px;">
                            Back to Missions
                        </a>
                    </section>
                @endif
            </div>
        </div>

        @if ($materialCount > 0 && $questionCount > 0)
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const form = document.getElementById('reading-assessment-form');

                    if (!form) {
                        return;
                    }

                    const groups = Array.from(
                        form.querySelectorAll('[data-reading-group]')
                    );

                    const steps = Array.from(
                        form.querySelectorAll('[data-question-step]')
                    );

                    const previousButton = document.getElementById(
                        'reading-previous-button'
                    );

                    const nextButton = document.getElementById(
                        'reading-next-button'
                    );

                    const submitButton = document.getElementById(
                        'reading-submit-button'
                    );

                    const submitLabel = document.getElementById(
                        'reading-submit-label'
                    );

                    const progressCard = document.getElementById(
                        'reading-progress-card'
                    );

                    const progressTitle = document.getElementById(
                        'reading-progress-title'
                    );

                    const progressDescription = document.getElementById(
                        'reading-progress-description'
                    );

                    const progressPercent = document.getElementById(
                        'reading-progress-percent'
                    );

                    const progressBar = document.getElementById(
                        'reading-progress-bar'
                    );

                    let currentStep = 0;
                    let isSubmitting = false;

                    function getSelectedAnswer(step) {
                        return step.querySelector(
                            'input[type="radio"]:checked'
                        );
                    }

                    function updateOptionStates(step) {
                        const options = step.querySelectorAll(
                            '.reading-option'
                        );

                        options.forEach(function (option) {
                            const input = option.querySelector(
                                '.reading-option-input'
                            );

                            option.classList.toggle(
                                'is-selected',
                                Boolean(input && input.checked)
                            );
                        });
                    }

                    function clearStepError(step) {
                        const error = step.querySelector(
                            '[data-answer-error]'
                        );

                        if (error) {
                            error.hidden = true;
                        }
                    }

                    function showStepError(step) {
                        const error = step.querySelector(
                            '[data-answer-error]'
                        );

                        if (error) {
                            error.hidden = false;
                        }

                        const firstInput = step.querySelector(
                            '.reading-option-input'
                        );

                        if (firstInput) {
                            firstInput.focus();
                        }
                    }

                    function validateStep(stepIndex) {
                        const step = steps[stepIndex];

                        if (!step) {
                            return false;
                        }

                        if (!getSelectedAnswer(step)) {
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
                        const activeStep = steps[currentStep];

                        if (!activeStep) {
                            return;
                        }

                        const activeGroup = activeStep.closest(
                            '[data-reading-group]'
                        );

                        groups.forEach(function (group) {
                            group.hidden = group !== activeGroup;
                        });

                        steps.forEach(function (step) {
                            step.hidden = step !== activeStep;
                        });

                        const displayedQuestion = currentStep + 1;
                        const percentage = Math.round(
                            (displayedQuestion / steps.length) * 100
                        );

                        const passageNumber =
                            activeStep.dataset.passageNumber || '1';

                        progressTitle.textContent =
                            'Question ' +
                            displayedQuestion +
                            ' of ' +
                            steps.length;

                        progressDescription.textContent =
                            'Passage ' +
                            passageNumber +
                            ' - Read the passage and select one answer.';

                        progressPercent.textContent = percentage;
                        progressBar.style.width = percentage + '%';

                        previousButton.disabled = currentStep === 0;
                        nextButton.hidden = currentStep === steps.length - 1;
                        submitButton.hidden = currentStep !== steps.length - 1;

                        updateOptionStates(activeStep);

                        if (shouldScroll) {
                            scrollToAssessment();
                        }
                    }

                    steps.forEach(function (step) {
                        const inputs = step.querySelectorAll(
                            '.reading-option-input'
                        );

                        inputs.forEach(function (input) {
                            input.addEventListener('change', function () {
                                updateOptionStates(step);
                                clearStepError(step);
                            });
                        });

                        updateOptionStates(step);
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

                        const firstUnansweredStep = steps.findIndex(
                            function (step) {
                                return !getSelectedAnswer(step);
                            }
                        );

                        if (firstUnansweredStep !== -1) {
                            event.preventDefault();
                            currentStep = firstUnansweredStep;
                            renderStep(true);
                            showStepError(steps[firstUnansweredStep]);
                            return;
                        }

                        isSubmitting = true;
                        submitButton.disabled = true;

                        if (submitLabel) {
                            submitLabel.textContent =
                                'Processing assessment...';
                        }
                    });

                    const firstUnansweredStep = steps.findIndex(
                        function (step) {
                            return !getSelectedAnswer(step);
                        }
                    );

                    currentStep =
                        firstUnansweredStep >= 0
                            ? firstUnansweredStep
                            : 0;

                    renderStep(false);
                });
            </script>
        @endif
    @endif
</x-app-layout>
