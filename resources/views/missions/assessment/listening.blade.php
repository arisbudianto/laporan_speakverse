<x-app-layout>
    <style>
        .listening-page {
            --lp-page: #f1f5f9;
            --lp-card: #ffffff;
            --lp-card-soft: #f8fafc;
            --lp-card-muted: #eef2f7;
            --lp-text: #0f172a;
            --lp-text-soft: #334155;
            --lp-text-muted: #64748b;
            --lp-border: #dbe4ef;
            --lp-border-strong: #cbd5e1;
            --lp-accent: #0891b2;
            --lp-accent-strong: #2563eb;
            --lp-accent-soft: #ecfeff;
            --lp-accent-border: #a5f3fc;
            --lp-success: #047857;
            --lp-success-soft: #ecfdf5;
            --lp-success-border: #a7f3d0;
            --lp-danger: #dc2626;
            --lp-danger-soft: #fef2f2;
            --lp-danger-border: #fecaca;
            --lp-warning: #b45309;
            --lp-warning-soft: #fffbeb;
            --lp-warning-border: #fde68a;
            --lp-shadow: 0 16px 38px rgba(15, 23, 42, 0.08);
            --lp-shadow-soft: 0 6px 18px rgba(15, 23, 42, 0.06);
            color: var(--lp-text);
        }

        html.dark .listening-page,
        body.dark .listening-page,
        .dark .listening-page,
        html[data-theme="dark"] .listening-page,
        body[data-theme="dark"] .listening-page {
            --lp-page: #020617;
            --lp-card: #0f172a;
            --lp-card-soft: #111c31;
            --lp-card-muted: #18243a;
            --lp-text: #f8fafc;
            --lp-text-soft: #d7e0ec;
            --lp-text-muted: #94a3b8;
            --lp-border: #26364d;
            --lp-border-strong: #3b4d67;
            --lp-accent: #22d3ee;
            --lp-accent-strong: #60a5fa;
            --lp-accent-soft: #0b2c3a;
            --lp-accent-border: #155e75;
            --lp-success: #34d399;
            --lp-success-soft: #0b2e29;
            --lp-success-border: #166534;
            --lp-danger: #f87171;
            --lp-danger-soft: #371820;
            --lp-danger-border: #7f1d1d;
            --lp-warning: #fbbf24;
            --lp-warning-soft: #33250c;
            --lp-warning-border: #854d0e;
            --lp-shadow: 0 18px 42px rgba(0, 0, 0, 0.34);
            --lp-shadow-soft: 0 8px 22px rgba(0, 0, 0, 0.24);
        }

        .listening-page,
        .listening-page * {
            box-sizing: border-box;
        }

        .listening-page [hidden],
        .listening-page [data-listening-group][hidden],
        .listening-page [data-question-step][hidden],
        .listening-page #listening-next-button[hidden],
        .listening-page #listening-submit-button[hidden] {
            display: none !important;
        }

        .listening-page .lp-shell {
            width: min(100%, 1480px);
            margin-inline: auto;
            display: grid;
            gap: 18px;
        }

        .listening-page .lp-card,
        .listening-page .lp-panel {
            background: var(--lp-card);
            border: 1px solid var(--lp-border);
            box-shadow: var(--lp-shadow-soft);
        }

        .listening-page .lp-card {
            border-radius: 24px;
        }

        .listening-page .lp-panel {
            min-width: 0;
            overflow: hidden;
            border-radius: 22px;
            box-shadow: var(--lp-shadow);
        }

        .listening-page .lp-assessment-header,
        .listening-page .lp-result-hero {
            position: relative;
            overflow: hidden;
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: center;
            gap: 24px;
            padding: 24px 26px;
        }

        .listening-page .lp-assessment-header::after,
        .listening-page .lp-result-hero::after {
            content: "";
            position: absolute;
            width: 230px;
            height: 230px;
            right: -90px;
            top: -125px;
            border-radius: 999px;
            background: rgba(34, 211, 238, 0.12);
            pointer-events: none;
        }

        .listening-page .lp-header-copy,
        .listening-page .lp-header-stats,
        .listening-page .lp-score-card {
            position: relative;
            z-index: 1;
        }

        .listening-page .lp-eyebrow {
            display: inline-flex;
            align-items: center;
            min-height: 30px;
            padding: 6px 12px;
            border-radius: 999px;
            background: var(--lp-accent-soft);
            color: var(--lp-accent);
            font-size: 12px;
            line-height: 1;
            font-weight: 900;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        .listening-page .lp-title {
            margin: 12px 0 0;
            color: var(--lp-text);
            font-size: clamp(28px, 3vw, 40px);
            line-height: 1.08;
            font-weight: 900;
            letter-spacing: -0.035em;
        }

        .listening-page .lp-subtitle {
            margin: 7px 0 0;
            color: var(--lp-text-muted);
            font-size: 15px;
            line-height: 1.55;
            font-weight: 600;
        }

        .listening-page .lp-header-stats {
            display: grid;
            grid-template-columns: repeat(2, minmax(116px, 1fr));
            gap: 10px;
        }

        .listening-page .lp-stat {
            min-width: 116px;
            padding: 14px 16px;
            border: 1px solid var(--lp-border);
            border-radius: 16px;
            background: var(--lp-card-soft);
        }

        .listening-page .lp-stat-label,
        .listening-page .lp-result-label,
        .listening-page .lp-score-label {
            color: var(--lp-text-muted);
            font-size: 12px;
            line-height: 1.4;
            font-weight: 800;
        }

        .listening-page .lp-stat-value {
            margin-top: 3px;
            color: var(--lp-text);
            font-size: 25px;
            line-height: 1;
            font-weight: 900;
        }

        .listening-page .lp-stat-value.is-accent {
            color: var(--lp-accent);
        }

        .listening-page .lp-alert {
            padding: 14px 16px;
            border-radius: 16px;
            font-size: 14px;
            line-height: 1.6;
            font-weight: 700;
        }

        .listening-page .lp-alert-success {
            border: 1px solid var(--lp-success-border);
            background: var(--lp-success-soft);
            color: var(--lp-success);
        }

        .listening-page .lp-alert-error {
            border: 1px solid var(--lp-danger-border);
            background: var(--lp-danger-soft);
            color: var(--lp-danger);
        }

        .listening-page .lp-progress {
            padding: 17px 20px;
        }

        .listening-page .lp-progress-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .listening-page .lp-progress-title {
            margin: 0;
            color: var(--lp-text);
            font-size: 15px;
            line-height: 1.4;
            font-weight: 900;
        }

        .listening-page .lp-progress-description {
            margin: 3px 0 0;
            color: var(--lp-text-muted);
            font-size: 13px;
            line-height: 1.5;
            font-weight: 600;
        }

        .listening-page .lp-progress-badge {
            flex: 0 0 auto;
            min-width: 48px;
            padding: 9px 10px;
            border-radius: 14px;
            background: var(--lp-accent-soft);
            color: var(--lp-accent);
            text-align: center;
            font-size: 13px;
            font-weight: 900;
        }

        .listening-page .lp-progress-track {
            height: 8px;
            margin-top: 13px;
            overflow: hidden;
            border-radius: 999px;
            background: var(--lp-card-muted);
        }

        .listening-page .lp-progress-bar {
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, var(--lp-accent), var(--lp-accent-strong));
            transition: width 240ms ease;
        }

        .listening-page .lp-stage {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 18px;
            align-items: start;
        }

        .listening-page .lp-material-header {
            display: flex;
            align-items: center;
            gap: 14px;
            min-height: 82px;
            padding: 16px 20px;
            border-bottom: 1px solid var(--lp-border);
            background: var(--lp-card);
        }

        .listening-page .lp-material-icon,
        .listening-page .lp-question-number {
            display: inline-flex;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            width: 46px;
            height: 46px;
            border-radius: 14px;
            background: var(--lp-accent-soft);
            color: var(--lp-accent);
        }

        .listening-page .lp-material-icon svg {
            width: 23px;
            height: 23px;
        }

        .listening-page .lp-material-heading {
            min-width: 0;
            flex: 1;
            display: grid;
            gap: 4px;
        }

        .listening-page .lp-panel-kicker,
        .listening-page .lp-question-label,
        .listening-page .lp-instruction-label {
            margin: 0;
            color: var(--lp-accent);
            font-size: 11px;
            line-height: 1.3;
            font-weight: 900;
            letter-spacing: 0.11em;
            text-transform: uppercase;
        }

        .listening-page .lp-panel-title {
            margin: 0;
            overflow: hidden;
            color: var(--lp-text);
            font-size: 20px;
            line-height: 1.25;
            font-weight: 900;
            letter-spacing: -0.02em;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .listening-page .lp-panel-meta {
            color: var(--lp-text-muted);
            font-size: 12px;
            line-height: 1.35;
            font-weight: 700;
        }

        .listening-page .lp-count-badge {
            flex: 0 0 auto;
            padding: 8px 11px;
            border: 1px solid var(--lp-border);
            border-radius: 12px;
            background: var(--lp-card-soft);
            color: var(--lp-text-muted);
            font-size: 12px;
            font-weight: 900;
            white-space: nowrap;
        }

        .listening-page .lp-material-body {
            padding: 20px;
        }

        .listening-page .lp-instruction {
            margin: 0 0 16px;
            padding: 13px 15px;
            border: 1px solid var(--lp-accent-border);
            border-radius: 15px;
            background: var(--lp-accent-soft);
        }

        .listening-page .lp-instruction-text {
            margin: 5px 0 0;
            color: var(--lp-text-soft);
            font-size: 14px;
            line-height: 1.65;
            font-weight: 600;
        }

        .listening-page .lp-audio-player {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr);
            align-items: center;
            gap: 14px;
            padding: 15px;
            border: 1px solid var(--lp-border);
            border-radius: 17px;
            background: var(--lp-card-soft);
        }

        .listening-page .lp-audio-toggle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 52px;
            height: 52px;
            border: 0;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--lp-accent), var(--lp-accent-strong));
            color: #ffffff;
            cursor: pointer;
            box-shadow: var(--lp-shadow-soft);
            transition: transform 160ms ease, box-shadow 160ms ease, opacity 160ms ease;
        }

        .listening-page .lp-audio-toggle:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: var(--lp-shadow);
        }

        .listening-page .lp-audio-toggle:focus-visible {
            outline: 3px solid rgba(34, 211, 238, 0.28);
            outline-offset: 3px;
        }

        .listening-page .lp-audio-toggle:disabled {
            cursor: not-allowed;
            opacity: 0.5;
        }

        .listening-page .lp-audio-toggle svg {
            width: 22px;
            height: 22px;
        }

        .listening-page .lp-audio-main {
            min-width: 0;
            display: grid;
            gap: 9px;
        }

        .listening-page .lp-audio-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .listening-page .lp-audio-title {
            color: var(--lp-text);
            font-size: 14px;
            line-height: 1.4;
            font-weight: 900;
        }

        .listening-page .lp-audio-state {
            color: var(--lp-accent);
            font-size: 12px;
            line-height: 1.4;
            font-weight: 900;
            white-space: nowrap;
        }

        .listening-page .lp-audio-track-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: center;
            gap: 12px;
        }

        .listening-page .lp-audio-range {
            width: 100%;
            height: 6px;
            margin: 0;
            accent-color: var(--lp-accent);
            cursor: pointer;
        }

        .listening-page .lp-audio-time {
            color: var(--lp-text-muted);
            font-variant-numeric: tabular-nums;
            font-size: 11px;
            font-weight: 800;
            white-space: nowrap;
        }

        .listening-page .lp-audio-missing {
            padding: 13px 15px;
            border: 1px solid var(--lp-danger-border);
            border-radius: 15px;
            background: var(--lp-danger-soft);
            color: var(--lp-danger);
            font-size: 13px;
            line-height: 1.55;
            font-weight: 800;
        }

        .listening-page .lp-transcript {
            margin-top: 14px;
            overflow: hidden;
            border: 1px solid var(--lp-border);
            border-radius: 15px;
            background: var(--lp-card-soft);
        }

        .listening-page .lp-transcript summary {
            cursor: pointer;
            padding: 13px 15px;
            color: var(--lp-text-soft);
            font-size: 13px;
            font-weight: 900;
        }

        .listening-page .lp-transcript-copy {
            padding: 14px 15px;
            border-top: 1px solid var(--lp-border);
            color: var(--lp-text-soft);
            font-size: 14px;
            line-height: 1.75;
            font-weight: 550;
        }

        .listening-page .lp-question-card {
            padding: 20px;
        }

        .listening-page .lp-question-head {
            display: flex;
            align-items: flex-start;
            gap: 13px;
        }

        .listening-page .lp-question-number {
            width: 42px;
            height: 42px;
            font-size: 15px;
            font-weight: 900;
        }

        .listening-page .lp-question-copy {
            min-width: 0;
            flex: 1;
        }

        .listening-page .lp-question-text {
            margin: 7px 0 0;
            color: var(--lp-text);
            font-size: 18px;
            line-height: 1.48;
            font-weight: 900;
            letter-spacing: -0.018em;
        }

        .listening-page .lp-question-help,
        .listening-page .lp-question-instruction {
            margin: 5px 0 0;
            color: var(--lp-text-muted);
            font-size: 13px;
            line-height: 1.5;
            font-weight: 600;
        }

        .listening-page .lp-question-instruction {
            margin-top: 0;
            margin-bottom: 8px;
            color: var(--lp-accent);
            font-weight: 800;
        }

        .listening-page .lp-question-image {
            margin: 16px 0 0;
            overflow: hidden;
            border: 1px solid var(--lp-border);
            border-radius: 16px;
            background: var(--lp-card-soft);
        }

        .listening-page .lp-question-image img {
            display: block;
            width: 100%;
            max-height: 280px;
            object-fit: contain;
            padding: 10px;
        }

        .listening-page .lp-options {
            display: grid;
            gap: 9px;
            margin-top: 18px;
        }

        .listening-page .lp-option {
            position: relative;
            display: grid;
            grid-template-columns: 34px minmax(0, 1fr) 22px;
            align-items: center;
            gap: 11px;
            min-height: 56px;
            padding: 10px 13px;
            border: 1px solid var(--lp-border);
            border-radius: 15px;
            background: var(--lp-card-soft);
            cursor: pointer;
            transition: border-color 160ms ease, background-color 160ms ease,
                transform 160ms ease, box-shadow 160ms ease;
        }

        .listening-page .lp-option:hover {
            transform: translateY(-1px);
            border-color: var(--lp-accent);
            background: var(--lp-accent-soft);
            box-shadow: var(--lp-shadow-soft);
        }

        .listening-page .lp-option:focus-within {
            outline: 3px solid rgba(34, 211, 238, 0.24);
            outline-offset: 2px;
        }

        .listening-page .lp-option.is-selected,
        .listening-page .lp-option:has(.lp-option-input:checked) {
            border-color: var(--lp-accent);
            background: var(--lp-accent-soft);
            box-shadow: 0 0 0 1px rgba(34, 211, 238, 0.18);
        }

        .listening-page .lp-option-input {
            position: absolute;
            width: 1px;
            height: 1px;
            overflow: hidden;
            opacity: 0;
            pointer-events: none;
        }

        .listening-page .lp-option-letter {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border: 1px solid var(--lp-border-strong);
            border-radius: 10px;
            background: var(--lp-card);
            color: var(--lp-text-soft);
            font-size: 13px;
            font-weight: 900;
            transition: all 160ms ease;
        }

        .listening-page .lp-option.is-selected .lp-option-letter,
        .listening-page .lp-option:has(.lp-option-input:checked) .lp-option-letter {
            border-color: var(--lp-accent);
            background: var(--lp-accent);
            color: #ffffff;
        }

        .listening-page .lp-option-text {
            min-width: 0;
            color: var(--lp-text-soft);
            font-size: 14px;
            line-height: 1.55;
            font-weight: 650;
        }

        .listening-page .lp-option.is-selected .lp-option-text,
        .listening-page .lp-option:has(.lp-option-input:checked) .lp-option-text {
            color: var(--lp-text);
        }

        .listening-page .lp-option-check {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            height: 22px;
            border-radius: 999px;
            color: transparent;
            transition: all 160ms ease;
        }

        .listening-page .lp-option-check svg {
            width: 15px;
            height: 15px;
        }

        .listening-page .lp-option.is-selected .lp-option-check,
        .listening-page .lp-option:has(.lp-option-input:checked) .lp-option-check {
            background: var(--lp-accent);
            color: #ffffff;
        }

        .listening-page .lp-answer-error {
            margin-top: 12px;
            padding: 11px 13px;
            border: 1px solid var(--lp-danger-border);
            border-radius: 13px;
            background: var(--lp-danger-soft);
            color: var(--lp-danger);
            font-size: 13px;
            line-height: 1.5;
            font-weight: 800;
        }

        .listening-page .lp-navigation {
            padding: 14px;
        }

        .listening-page .lp-navigation-inner,
        .listening-page .lp-navigation-group,
        .listening-page .lp-result-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .listening-page .lp-navigation-inner,
        .listening-page .lp-result-actions {
            justify-content: space-between;
        }

        .listening-page .lp-button {
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
            transition: transform 160ms ease, background-color 160ms ease,
                border-color 160ms ease, box-shadow 160ms ease, opacity 160ms ease;
        }

        .listening-page .lp-button svg {
            width: 18px;
            height: 18px;
        }

        .listening-page .lp-button:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: var(--lp-shadow-soft);
        }

        .listening-page .lp-button:focus-visible {
            outline: 3px solid rgba(34, 211, 238, 0.28);
            outline-offset: 2px;
        }

        .listening-page .lp-button:disabled {
            cursor: not-allowed;
            opacity: 0.45;
            transform: none;
            box-shadow: none;
        }

        .listening-page .lp-button-secondary {
            border-color: var(--lp-border);
            background: var(--lp-card-soft);
            color: var(--lp-text-soft);
        }

        .listening-page .lp-button-secondary:hover:not(:disabled) {
            border-color: var(--lp-border-strong);
            background: var(--lp-card-muted);
        }

        .listening-page .lp-button-primary {
            min-width: 205px;
            background: linear-gradient(100deg, var(--lp-accent), var(--lp-accent-strong));
            color: #ffffff;
        }

        .listening-page .lp-button-submit {
            min-width: 220px;
            background: linear-gradient(100deg, #059669, #0891b2);
            color: #ffffff;
        }

        .listening-page .lp-empty {
            padding: 42px 22px;
            text-align: center;
        }

        .listening-page .lp-empty-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 58px;
            height: 58px;
            border-radius: 17px;
            background: var(--lp-card-muted);
            color: var(--lp-text-muted);
        }

        .listening-page .lp-empty-icon svg {
            width: 28px;
            height: 28px;
        }

        .listening-page .lp-empty-title {
            margin: 16px 0 0;
            color: var(--lp-text);
            font-size: 20px;
            font-weight: 900;
        }

        .listening-page .lp-empty-text {
            max-width: 520px;
            margin: 7px auto 0;
            color: var(--lp-text-muted);
            font-size: 14px;
            line-height: 1.65;
            font-weight: 600;
        }

        .listening-page .lp-score-card {
            min-width: 190px;
            padding: 17px 19px;
            border: 1px solid var(--lp-border);
            border-radius: 18px;
            background: var(--lp-card-soft);
        }

        .listening-page .lp-score-value {
            margin-top: 4px;
            color: var(--lp-accent);
            font-size: 46px;
            line-height: 1;
            font-weight: 900;
        }

        .listening-page .lp-score-value small {
            color: var(--lp-text-muted);
            font-size: 13px;
        }

        .listening-page .lp-result-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .listening-page .lp-result-metric {
            padding: 17px;
        }

        .listening-page .lp-result-value {
            margin-top: 8px;
            color: var(--lp-text);
            font-size: 18px;
            line-height: 1.35;
            font-weight: 900;
        }

        .listening-page .lp-status {
            display: inline-flex;
            padding: 7px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 900;
        }

        .listening-page .lp-status-completed {
            background: var(--lp-success-soft);
            color: var(--lp-success);
        }

        .listening-page .lp-status-failed {
            background: var(--lp-danger-soft);
            color: var(--lp-danger);
        }

        .listening-page .lp-status-pending {
            background: var(--lp-warning-soft);
            color: var(--lp-warning);
        }

        .listening-page .lp-result-section {
            padding: 20px;
        }

        .listening-page .lp-section-title {
            margin: 0;
            color: var(--lp-text);
            font-size: 19px;
            font-weight: 900;
        }

        .listening-page .lp-section-subtitle {
            margin: 4px 0 0;
            color: var(--lp-text-muted);
            font-size: 13px;
            font-weight: 600;
        }

        .listening-page .lp-feedback-box {
            margin-top: 14px;
            padding: 15px;
            border-radius: 15px;
            background: var(--lp-card-soft);
            color: var(--lp-text-soft);
            font-size: 14px;
            line-height: 1.75;
            font-weight: 600;
        }

        .listening-page .lp-answer-list {
            margin-top: 14px;
            display: grid;
            gap: 10px;
        }

        .listening-page .lp-answer-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 14px 15px;
            border: 1px solid var(--lp-border);
            border-radius: 15px;
            background: var(--lp-card-soft);
        }

        .listening-page .lp-answer-main {
            display: flex;
            align-items: center;
            gap: 11px;
            min-width: 0;
        }

        .listening-page .lp-answer-number {
            display: inline-flex;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: var(--lp-accent-soft);
            color: var(--lp-accent);
            font-size: 13px;
            font-weight: 900;
        }

        .listening-page .lp-answer-title {
            color: var(--lp-text);
            font-size: 14px;
            font-weight: 900;
        }

        .listening-page .lp-answer-selected {
            margin-top: 2px;
            color: var(--lp-text-muted);
            font-size: 12px;
            font-weight: 700;
        }

        .listening-page .lp-answer-badges {
            display: flex;
            align-items: center;
            gap: 7px;
            flex: 0 0 auto;
        }

        .listening-page .lp-answer-badge {
            display: inline-flex;
            padding: 6px 9px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 900;
        }

        .listening-page .lp-answer-correct {
            background: var(--lp-success-soft);
            color: var(--lp-success);
        }

        .listening-page .lp-answer-wrong {
            background: var(--lp-danger-soft);
            color: var(--lp-danger);
        }

        .listening-page .lp-answer-score {
            background: var(--lp-card-muted);
            color: var(--lp-text-soft);
        }

        @media (min-width: 1080px) {
            .listening-page .lp-stage {
                grid-template-columns: minmax(0, 55fr) minmax(390px, 45fr);
            }

            .listening-page .lp-material-panel {
                position: sticky;
                top: 104px;
            }
        }

        @media (min-width: 820px) {
            .listening-page .lp-answer-list {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 760px) {
            .listening-page .lp-shell {
                gap: 14px;
            }

            .listening-page .lp-assessment-header,
            .listening-page .lp-result-hero {
                grid-template-columns: minmax(0, 1fr);
                padding: 19px;
            }

            .listening-page .lp-header-stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .listening-page .lp-stat {
                min-width: 0;
            }

            .listening-page .lp-progress {
                padding: 15px 16px;
            }

            .listening-page .lp-material-header {
                min-height: 74px;
                padding: 14px 16px;
            }

            .listening-page .lp-material-body,
            .listening-page .lp-question-card {
                padding: 16px;
            }

            .listening-page .lp-panel-title {
                font-size: 18px;
            }

            .listening-page .lp-count-badge {
                display: none;
            }

            .listening-page .lp-question-text {
                font-size: 16px;
            }

            .listening-page .lp-audio-player {
                grid-template-columns: 46px minmax(0, 1fr);
                padding: 12px;
            }

            .listening-page .lp-audio-toggle {
                width: 46px;
                height: 46px;
                border-radius: 14px;
            }

            .listening-page .lp-audio-track-row {
                grid-template-columns: minmax(0, 1fr);
                gap: 6px;
            }

            .listening-page .lp-option {
                grid-template-columns: 32px minmax(0, 1fr) 20px;
                min-height: 54px;
                padding: 9px 11px;
            }

            .listening-page .lp-option-letter {
                width: 32px;
                height: 32px;
            }

            .listening-page .lp-navigation-inner,
            .listening-page .lp-navigation-group,
            .listening-page .lp-result-actions {
                width: 100%;
                flex-direction: column;
                align-items: stretch;
            }

            .listening-page .lp-button,
            .listening-page .lp-button-primary,
            .listening-page .lp-button-submit {
                width: 100%;
                min-width: 0;
            }

            .listening-page .lp-result-grid {
                grid-template-columns: minmax(0, 1fr);
            }

            .listening-page .lp-score-card {
                min-width: 0;
            }

            .listening-page .lp-answer-item {
                align-items: flex-start;
                flex-direction: column;
            }
        }
    </style>

    @if (isset($submission))
        @php
            $score = (int) ($submission->final_score ?? 0);

            $statusClass = match ($submission->status) {
                'completed' => 'lp-status-completed',
                'failed' => 'lp-status-failed',
                default => 'lp-status-pending',
            };
        @endphp

        <div class="listening-page">
            <div class="lp-shell">
                @if (session('success'))
                    <div class="lp-alert lp-alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <section class="lp-card lp-result-hero">
                    <div>
                        <span class="lp-eyebrow">Assessment Result</span>

                        <h1 class="lp-title">
                            {{ strtoupper($submission->type) }} Listening
                        </h1>

                        <p class="lp-subtitle">
                            {{ $submission->lesson->title ?? 'Listening Assessment' }}
                        </p>
                    </div>

                    <div class="lp-score-card">
                        <div class="lp-score-label">Final Score</div>

                        <div class="lp-score-value">
                            {{ $score }}
                            <small>/ 100</small>
                        </div>
                    </div>
                </section>

                <section class="lp-result-grid">
                    <article class="lp-card lp-result-metric">
                        <div class="lp-result-label">Status</div>

                        <div class="lp-result-value">
                            <span class="lp-status {{ $statusClass }}">
                                {{ ucfirst($submission->status) }}
                            </span>
                        </div>
                    </article>

                    <article class="lp-card lp-result-metric">
                        <div class="lp-result-label">Submitted At</div>

                        <div class="lp-result-value">
                            {{ $submission->submitted_at?->format('d M Y, H:i')
                                ?? $submission->created_at->format('d M Y, H:i') }}
                        </div>
                    </article>

                    <article class="lp-card lp-result-metric">
                        <div class="lp-result-label">Total Answers</div>

                        <div class="lp-result-value">
                            {{ $submission->answers->count() }}
                        </div>
                    </article>
                </section>

                <section class="lp-card lp-result-section">
                    <h2 class="lp-section-title">Feedback</h2>

                    <p class="lp-section-subtitle">
                        General evaluation of your listening assessment.
                    </p>

                    <div class="lp-feedback-box">
                        {!! nl2br(e(trim((string) ($submission->feedback ?? 'No feedback is available yet.')))) !!}
                    </div>
                </section>

                <section class="lp-card lp-result-section">
                    <h2 class="lp-section-title">Answer Details</h2>

                    <p class="lp-section-subtitle">
                        Review every submitted answer and its score.
                    </p>

                    <div class="lp-answer-list">
                        @forelse ($submission->answers as $index => $answer)
                            <article class="lp-answer-item">
                                <div class="lp-answer-main">
                                    <span class="lp-answer-number">
                                        {{ $index + 1 }}
                                    </span>

                                    <div>
                                        <div class="lp-answer-title">
                                            Question {{ $index + 1 }}
                                        </div>

                                        <div class="lp-answer-selected">
                                            Selected answer:
                                            {{ $answer->selected_option ?? '-' }}
                                        </div>
                                    </div>
                                </div>

                                <div class="lp-answer-badges">
                                    @if ($answer->is_correct)
                                        <span class="lp-answer-badge lp-answer-correct">
                                            Correct
                                        </span>
                                    @else
                                        <span class="lp-answer-badge lp-answer-wrong">
                                            Wrong
                                        </span>
                                    @endif

                                    <span class="lp-answer-badge lp-answer-score">
                                        {{ $answer->score ?? 0 }}
                                        /
                                        {{ $answer->max_score ?? 0 }}
                                    </span>
                                </div>
                            </article>
                        @empty
                            <div class="lp-empty">
                                <div class="lp-empty-icon">
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

                                <h3 class="lp-empty-title">No Answer Details</h3>

                                <p class="lp-empty-text">
                                    Answer details are not available.
                                </p>
                            </div>
                        @endforelse
                    </div>
                </section>

                <div class="lp-result-actions">
                    <a
                        href="{{ route('missions') }}"
                        class="lp-button lp-button-secondary">
                        Back to Missions
                    </a>

                    <a
                        href="{{ route('progress') }}"
                        class="lp-button lp-button-primary">
                        View Progress
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

        <div class="listening-page">
            <div class="lp-shell">
                <section class="lp-card lp-assessment-header">
                    <div class="lp-header-copy">
                        <span class="lp-eyebrow">
                            {{ strtoupper($type) }} Assessment
                        </span>

                        <h1 class="lp-title">Listening Test</h1>

                        <p class="lp-subtitle">
                            {{ $lesson->title }} Assessment
                        </p>
                    </div>

                    <div class="lp-header-stats">
                        <article class="lp-stat">
                            <div class="lp-stat-label">Audio Materials</div>
                            <div class="lp-stat-value">{{ $materialCount }}</div>
                        </article>

                        <article class="lp-stat">
                            <div class="lp-stat-label">Questions</div>
                            <div class="lp-stat-value is-accent">{{ $questionCount }}</div>
                        </article>
                    </div>
                </section>

                @if (session('success'))
                    <div class="lp-alert lp-alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="lp-alert lp-alert-error">
                        {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="lp-alert lp-alert-error">
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
                        id="listening-assessment-form"
                        action="{{ route('student.assessment.submit', [
                            'type' => $type,
                            'skill' => $skill,
                        ]) }}"
                        method="POST"
                        novalidate>
                        @csrf

                        <div class="lp-shell">
                            <section
                                id="listening-progress-card"
                                class="lp-card lp-progress">
                                <div class="lp-progress-row">
                                    <div>
                                        <p
                                            id="listening-progress-title"
                                            class="lp-progress-title"
                                            aria-live="polite">
                                            Question 1 of {{ $questionCount }}
                                        </p>

                                        <p
                                            id="listening-progress-description"
                                            class="lp-progress-description">
                                            Audio 1 - Listen carefully and select one answer.
                                        </p>
                                    </div>

                                    <div class="lp-progress-badge">
                                        <span id="listening-progress-percent">
                                            {{ round(100 / $questionCount) }}
                                        </span>%
                                    </div>
                                </div>

                                <div class="lp-progress-track">
                                    <div
                                        id="listening-progress-bar"
                                        class="lp-progress-bar"
                                        style="width: {{ 100 / $questionCount }}%">
                                    </div>
                                </div>
                            </section>

                            @foreach ($materials as $materialIndex => $material)
                                @php
                                    $audioUrl = null;

                                    if (!empty($material->audio_file)) {
                                        $audioVersion = $material->updated_at
                                            ? $material->updated_at->timestamp
                                            : time();

                                        $audioUrl = asset(
                                            'storage/' . $material->audio_file
                                        ) . '?v=' . $audioVersion;
                                    }

                                    $instructionText = preg_replace(
                                        '/\s+/u',
                                        ' ',
                                        trim((string) ($material->instruction ?? ''))
                                    );

                                    $transcript = trim((string) ($material->passage ?? ''));
                                @endphp

                                <section
                                    data-listening-group
                                    data-group-index="{{ $materialIndex }}"
                                    @if ($materialIndex !== 0) hidden @endif
                                    class="lp-stage">
                                    <article class="lp-panel lp-material-panel">
                                        <header class="lp-material-header">
                                            <div class="lp-material-icon">
                                                <svg
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                    aria-hidden="true">
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M9 19V6l12-2v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-2c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-2">
                                                    </path>
                                                </svg>
                                            </div>

                                            <div class="lp-material-heading">
                                                <p class="lp-panel-kicker">
                                                    Listening Audio
                                                </p>

                                                <h2 class="lp-panel-title">
                                                    {{ $material->title }}
                                                </h2>

                                                <div class="lp-panel-meta">
                                                    Audio {{ $materialIndex + 1 }} of {{ $materialCount }}
                                                </div>
                                            </div>

                                            <span class="lp-count-badge">
                                                {{ $material->questions->count() }}
                                                {{ \Illuminate\Support\Str::plural(
                                                    'Question',
                                                    $material->questions->count()
                                                ) }}
                                            </span>
                                        </header>

                                        <div class="lp-material-body">
                                            @if ($instructionText !== '')
                                                <div class="lp-instruction">
                                                    <p class="lp-instruction-label">
                                                        Instruction
                                                    </p>

                                                    <p class="lp-instruction-text">
                                                        {{ $instructionText }}
                                                    </p>
                                                </div>
                                            @endif

                                            @if ($audioUrl)
                                                <div
                                                    class="lp-audio-player"
                                                    data-audio-player>
                                                    <audio
                                                        data-audio-element
                                                        preload="metadata"
                                                        src="{{ $audioUrl }}">
                                                    </audio>

                                                    <button
                                                        type="button"
                                                        class="lp-audio-toggle"
                                                        data-audio-toggle
                                                        aria-label="Play audio">
                                                        <svg
                                                            data-icon-play
                                                            fill="currentColor"
                                                            viewBox="0 0 24 24"
                                                            aria-hidden="true">
                                                            <path d="M8 5v14l11-7z"></path>
                                                        </svg>

                                                        <svg
                                                            data-icon-pause
                                                            fill="currentColor"
                                                            viewBox="0 0 24 24"
                                                            aria-hidden="true"
                                                            hidden>
                                                            <path d="M6 5h4v14H6zm8 0h4v14h-4z"></path>
                                                        </svg>
                                                    </button>

                                                    <div class="lp-audio-main">
                                                        <div class="lp-audio-head">
                                                            <div class="lp-audio-title">
                                                                Shared audio for this section
                                                            </div>

                                                            <div
                                                                class="lp-audio-state"
                                                                data-audio-state>
                                                                Ready
                                                            </div>
                                                        </div>

                                                        <div class="lp-audio-track-row">
                                                            <input
                                                                type="range"
                                                                min="0"
                                                                max="100"
                                                                value="0"
                                                                step="0.1"
                                                                class="lp-audio-range"
                                                                data-audio-range
                                                                aria-label="Audio progress">

                                                            <div class="lp-audio-time">
                                                                <span data-audio-current>0:00</span>
                                                                /
                                                                <span data-audio-duration>0:00</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="lp-audio-missing">
                                                    Audio is not available for this material.
                                                </div>
                                            @endif

                                            @if ($transcript !== '')
                                                <details class="lp-transcript">
                                                    <summary>Audio Script / Passage</summary>

                                                    <div class="lp-transcript-copy">
                                                        {!! nl2br(e($transcript)) !!}
                                                    </div>
                                                </details>
                                            @endif
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
                                                data-material-number="{{ $materialIndex + 1 }}"
                                                @if ($questionNumber !== 1) hidden @endif
                                                class="lp-panel lp-question-card">
                                                @if (!empty($question->instruction))
                                                    <p class="lp-question-instruction">
                                                        {{ $question->instruction }}
                                                    </p>
                                                @endif

                                                <div class="lp-question-head">
                                                    <div class="lp-question-number">
                                                        {{ $questionNumber }}
                                                    </div>

                                                    <div class="lp-question-copy">
                                                        <p class="lp-question-label">
                                                            Listening Question
                                                        </p>

                                                        <h3 class="lp-question-text">
                                                            {{ $question->question }}
                                                        </h3>

                                                        <p class="lp-question-help">
                                                            Select one answer.
                                                        </p>
                                                    </div>
                                                </div>

                                                @if (!empty($question->image))
                                                    <figure class="lp-question-image">
                                                        <img
                                                            src="{{ asset('storage/' . $question->image) }}"
                                                            alt="Question {{ $questionNumber }} image"
                                                            loading="lazy">
                                                    </figure>
                                                @endif

                                                <div
                                                    data-answer-options
                                                    class="lp-options">
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
                                                                class="lp-option {{ $isChecked ? 'is-selected' : '' }}">
                                                                <input
                                                                    type="radio"
                                                                    name="answers[{{ $question->id }}]"
                                                                    value="{{ $option }}"
                                                                    @checked($isChecked)
                                                                    class="lp-option-input">

                                                                <span class="lp-option-letter">
                                                                    {{ $option }}
                                                                </span>

                                                                <span class="lp-option-text">
                                                                    {{ $optionValue }}
                                                                </span>

                                                                <span class="lp-option-check">
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
                                                    class="lp-answer-error"
                                                    hidden>
                                                    Please select one answer before continuing.
                                                </div>
                                            </article>
                                        @empty
                                            <article class="lp-panel lp-empty">
                                                <div class="lp-empty-icon">
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

                                                <h3 class="lp-empty-title">No Questions</h3>

                                                <p class="lp-empty-text">
                                                    This audio material does not have any questions.
                                                </p>
                                            </article>
                                        @endforelse
                                    </div>
                                </section>
                            @endforeach

                            <section class="lp-card lp-navigation">
                                <div class="lp-navigation-inner">
                                    <div class="lp-navigation-group">
                                        <a
                                            href="{{ route('missions') }}"
                                            class="lp-button lp-button-secondary">
                                            Back
                                        </a>

                                        <button
                                            id="listening-previous-button"
                                            type="button"
                                            class="lp-button lp-button-secondary">
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

                                    <div class="lp-navigation-group">
                                        <button
                                            id="listening-next-button"
                                            type="button"
                                            class="lp-button lp-button-primary">
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
                                            id="listening-submit-button"
                                            type="submit"
                                            class="lp-button lp-button-submit">
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

                                            <span id="listening-submit-label">
                                                Submit Assessment
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </form>
                @else
                    <section class="lp-card lp-empty">
                        <div class="lp-empty-icon">
                            <svg
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                aria-hidden="true">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 19V6l12-2v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-2c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-2">
                                </path>
                            </svg>
                        </div>

                        <h2 class="lp-empty-title">No Listening Materials Yet</h2>

                        <p class="lp-empty-text">
                            The administrator has not added audio materials and questions
                            for this assessment.
                        </p>

                        <a
                            href="{{ route('missions') }}"
                            class="lp-button lp-button-primary"
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
                    const form = document.getElementById('listening-assessment-form');

                    if (!form) {
                        return;
                    }

                    const groups = Array.from(
                        form.querySelectorAll('[data-listening-group]')
                    );

                    const steps = Array.from(
                        form.querySelectorAll('[data-question-step]')
                    );

                    const previousButton = document.getElementById(
                        'listening-previous-button'
                    );

                    const nextButton = document.getElementById(
                        'listening-next-button'
                    );

                    const submitButton = document.getElementById(
                        'listening-submit-button'
                    );

                    const submitLabel = document.getElementById(
                        'listening-submit-label'
                    );

                    const progressCard = document.getElementById(
                        'listening-progress-card'
                    );

                    const progressTitle = document.getElementById(
                        'listening-progress-title'
                    );

                    const progressDescription = document.getElementById(
                        'listening-progress-description'
                    );

                    const progressPercent = document.getElementById(
                        'listening-progress-percent'
                    );

                    const progressBar = document.getElementById(
                        'listening-progress-bar'
                    );

                    const players = Array.from(
                        form.querySelectorAll('[data-audio-player]')
                    );

                    let currentStep = 0;
                    let isSubmitting = false;
                    let activeAudio = null;
                    let activePlayer = null;

                    function formatTime(seconds) {
                        if (!Number.isFinite(seconds) || seconds < 0) {
                            return '0:00';
                        }

                        const minutes = Math.floor(seconds / 60);
                        const remainingSeconds = Math.floor(seconds % 60)
                            .toString()
                            .padStart(2, '0');

                        return minutes + ':' + remainingSeconds;
                    }

                    function setPlayerState(player, state) {
                        const playIcon = player.querySelector('[data-icon-play]');
                        const pauseIcon = player.querySelector('[data-icon-pause]');
                        const stateLabel = player.querySelector('[data-audio-state]');

                        if (playIcon) {
                            playIcon.hidden = state === 'playing';
                        }

                        if (pauseIcon) {
                            pauseIcon.hidden = state !== 'playing';
                        }

                        if (stateLabel) {
                            const labels = {
                                ready: 'Ready',
                                playing: 'Playing',
                                paused: 'Paused',
                                ended: 'Replay',
                                error: 'Audio error'
                            };

                            stateLabel.textContent = labels[state] || 'Ready';
                        }
                    }

                    function stopOtherAudio(exceptAudio) {
                        players.forEach(function (player) {
                            const audio = player.querySelector('[data-audio-element]');

                            if (!audio || audio === exceptAudio) {
                                return;
                            }

                            if (!audio.paused) {
                                audio.pause();
                            }
                        });
                    }

                    players.forEach(function (player) {
                        const audio = player.querySelector('[data-audio-element]');
                        const toggle = player.querySelector('[data-audio-toggle]');
                        const range = player.querySelector('[data-audio-range]');
                        const current = player.querySelector('[data-audio-current]');
                        const duration = player.querySelector('[data-audio-duration]');

                        if (!audio || !toggle || !range || !current || !duration) {
                            return;
                        }

                        setPlayerState(player, 'ready');

                        audio.addEventListener('loadedmetadata', function () {
                            duration.textContent = formatTime(audio.duration);
                        });

                        audio.addEventListener('durationchange', function () {
                            duration.textContent = formatTime(audio.duration);
                        });

                        audio.addEventListener('timeupdate', function () {
                            current.textContent = formatTime(audio.currentTime);

                            range.value = audio.duration > 0
                                ? String((audio.currentTime / audio.duration) * 100)
                                : '0';
                        });

                        audio.addEventListener('play', function () {
                            stopOtherAudio(audio);
                            activeAudio = audio;
                            activePlayer = player;
                            setPlayerState(player, 'playing');
                        });

                        audio.addEventListener('pause', function () {
                            if (!audio.ended) {
                                setPlayerState(
                                    player,
                                    audio.currentTime > 0 ? 'paused' : 'ready'
                                );
                            }
                        });

                        audio.addEventListener('ended', function () {
                            setPlayerState(player, 'ended');
                            range.value = '100';

                            if (activeAudio === audio) {
                                activeAudio = null;
                                activePlayer = null;
                            }
                        });

                        audio.addEventListener('error', function () {
                            setPlayerState(player, 'error');
                            toggle.disabled = true;
                        });

                        toggle.addEventListener('click', async function () {
                            if (audio.ended) {
                                audio.currentTime = 0;
                            }

                            if (audio.paused) {
                                try {
                                    await audio.play();
                                } catch (error) {
                                    console.error('Audio playback failed:', error);
                                    setPlayerState(player, 'error');
                                }
                            } else {
                                audio.pause();
                            }
                        });

                        range.addEventListener('input', function () {
                            if (!Number.isFinite(audio.duration) || audio.duration <= 0) {
                                return;
                            }

                            audio.currentTime = (Number(range.value) / 100) * audio.duration;
                        });
                    });

                    function getSelectedAnswer(step) {
                        return step.querySelector('.lp-option-input:checked');
                    }

                    function updateOptionStates(step) {
                        const options = step.querySelectorAll('.lp-option');

                        options.forEach(function (option) {
                            const input = option.querySelector('.lp-option-input');

                            option.classList.toggle(
                                'is-selected',
                                Boolean(input && input.checked)
                            );
                        });
                    }

                    function clearStepError(step) {
                        const error = step.querySelector('[data-answer-error]');

                        if (error) {
                            error.hidden = true;
                        }
                    }

                    function showStepError(step) {
                        const error = step.querySelector('[data-answer-error]');

                        if (error) {
                            error.hidden = false;
                        }

                        const firstInput = step.querySelector('.lp-option-input');

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
                            '[data-listening-group]'
                        );

                        groups.forEach(function (group) {
                            group.hidden = group !== activeGroup;
                        });

                        steps.forEach(function (step) {
                            step.hidden = step !== activeStep;
                        });

                        if (activeAudio && activePlayer && !activeGroup.contains(activePlayer)) {
                            activeAudio.pause();
                        }

                        const displayedQuestion = currentStep + 1;
                        const percentage = Math.round(
                            (displayedQuestion / steps.length) * 100
                        );

                        const materialNumber =
                            activeStep.dataset.materialNumber || '1';

                        progressTitle.textContent =
                            'Question ' + displayedQuestion + ' of ' + steps.length;

                        progressDescription.textContent =
                            'Audio ' +
                            materialNumber +
                            ' - Listen carefully and select one answer.';

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
                        const inputs = step.querySelectorAll('.lp-option-input');

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

                        if (activeAudio) {
                            activeAudio.pause();
                        }

                        if (submitLabel) {
                            submitLabel.textContent = 'Processing assessment...';
                        }
                    });

                    document.addEventListener('visibilitychange', function () {
                        if (document.hidden && activeAudio) {
                            activeAudio.pause();
                        }
                    });

                    window.addEventListener('beforeunload', function () {
                        if (activeAudio) {
                            activeAudio.pause();
                        }
                    });

                    const firstUnansweredStep = steps.findIndex(
                        function (step) {
                            return !getSelectedAnswer(step);
                        }
                    );

                    currentStep = firstUnansweredStep >= 0
                        ? firstUnansweredStep
                        : 0;

                    renderStep(false);
                });
            </script>
        @endif
    @endif
</x-app-layout>
