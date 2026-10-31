<x-app-layout>
    @php
        /*
        |--------------------------------------------------------------------------
        | Page Context
        |--------------------------------------------------------------------------
        |
        | PRETEST / POSTTEST:
        | - Individual Speaking
        |
        | Unit 1–4:
        | - Pair / Individual Speaking mengikuti data material
        |
        */
        $isAssessment =
            isset($assessmentType) &&
            in_array(
                $assessmentType,
                [
                    'pretest',
                    'posttest',
                ],
                true
            );

        $assessmentLabel = $isAssessment
            ? strtoupper($assessmentType)
            : null;
    @endphp

    <style>
        /*
        |--------------------------------------------------------------------------
        | Speaking Introduction Page
        |--------------------------------------------------------------------------
        |
        | Seluruh style dibatasi di .sv-speaking-page agar tidak memengaruhi
        | halaman lain.
        |
        */
        .sv-speaking-page {
            --sv-page: #f1f5f9;
            --sv-card: #ffffff;
            --sv-card-soft: #f8fafc;
            --sv-card-muted: #f1f5f9;
            --sv-text: #0f172a;
            --sv-text-soft: #334155;
            --sv-muted: #64748b;
            --sv-border: #dbe4ef;
            --sv-border-strong: #cbd5e1;
            --sv-purple: #7c3aed;
            --sv-purple-soft: #f5f3ff;
            --sv-purple-border: #ddd6fe;
            --sv-blue: #2563eb;
            --sv-blue-soft: #eff6ff;
            --sv-blue-border: #bfdbfe;
            --sv-cyan: #0891b2;
            --sv-cyan-soft: #ecfeff;
            --sv-emerald: #059669;
            --sv-emerald-soft: #ecfdf5;
            --sv-emerald-border: #a7f3d0;
            --sv-amber: #b45309;
            --sv-amber-soft: #fffbeb;
            --sv-amber-border: #fde68a;
            --sv-danger: #dc2626;
            --sv-danger-soft: #fef2f2;
            --sv-danger-border: #fecaca;
            --sv-shadow: 0 12px 30px rgba(15, 23, 42, 0.07);
            --sv-shadow-soft: 0 5px 16px rgba(15, 23, 42, 0.05);

            width: min(100%, 1180px);
            margin-inline: auto;
            color: var(--sv-text);
        }

        html.dark .sv-speaking-page,
        body.dark .sv-speaking-page,
        .dark .sv-speaking-page,
        html[data-theme="dark"] .sv-speaking-page,
        body[data-theme="dark"] .sv-speaking-page {
            --sv-page: #020617;
            --sv-card: #0f172a;
            --sv-card-soft: #111c31;
            --sv-card-muted: #172033;
            --sv-text: #f8fafc;
            --sv-text-soft: #d3deec;
            --sv-muted: #94a3b8;
            --sv-border: #26364d;
            --sv-border-strong: #3a4b66;
            --sv-purple: #c4b5fd;
            --sv-purple-soft: #24163f;
            --sv-purple-border: #56378a;
            --sv-blue: #93c5fd;
            --sv-blue-soft: #10264a;
            --sv-blue-border: #28558e;
            --sv-cyan: #67e8f9;
            --sv-cyan-soft: #0b2b38;
            --sv-emerald: #6ee7b7;
            --sv-emerald-soft: #0d3028;
            --sv-emerald-border: #176449;
            --sv-amber: #fcd34d;
            --sv-amber-soft: #35270b;
            --sv-amber-border: #6f5516;
            --sv-danger: #fca5a5;
            --sv-danger-soft: #3a171d;
            --sv-danger-border: #7f2834;
            --sv-shadow: 0 16px 38px rgba(0, 0, 0, 0.28);
            --sv-shadow-soft: 0 7px 18px rgba(0, 0, 0, 0.2);
        }

        .sv-speaking-page,
        .sv-speaking-page * {
            box-sizing: border-box;
        }

        .sv-speaking-page a {
            text-decoration: none;
        }

        .sv-speaking-page .sv-stack {
            display: grid;
            gap: 16px;
        }

        .sv-speaking-page .sv-card {
            min-width: 0;
            border: 1px solid var(--sv-border);
            border-radius: 20px;
            background: var(--sv-card);
            box-shadow: var(--sv-shadow-soft);
        }

        /* Alerts */
        .sv-speaking-page .sv-alert {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 14px 16px;
            border: 1px solid var(--sv-amber-border);
            border-radius: 16px;
            background: var(--sv-amber-soft);
            color: var(--sv-amber);
        }

        .sv-speaking-page .sv-alert-icon {
            display: inline-flex;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 11px;
            background: var(--sv-card);
        }

        .sv-speaking-page .sv-alert-icon svg {
            width: 18px;
            height: 18px;
        }

        .sv-speaking-page .sv-alert-title {
            margin: 0;
            font-size: 14px;
            font-weight: 900;
        }

        .sv-speaking-page .sv-alert-text {
            margin: 3px 0 0;
            font-size: 13px;
            line-height: 1.6;
            font-weight: 650;
        }

        /* Hero */
        .sv-speaking-page .sv-hero {
            position: relative;
            overflow: hidden;
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(350px, 0.72fr);
            align-items: center;
            gap: 22px;
            padding: 22px;
        }

        .sv-speaking-page .sv-hero::after {
            content: "";
            position: absolute;
            width: 240px;
            height: 240px;
            right: -100px;
            top: -120px;
            border-radius: 999px;
            background: rgba(124, 58, 237, 0.13);
            filter: blur(18px);
            pointer-events: none;
        }

        .sv-speaking-page .sv-hero-copy,
        .sv-speaking-page .sv-hero-stats {
            position: relative;
            z-index: 1;
        }

        .sv-speaking-page .sv-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            min-height: 29px;
            padding: 6px 10px;
            border: 1px solid var(--sv-purple-border);
            border-radius: 999px;
            background: var(--sv-purple-soft);
            color: var(--sv-purple);
            font-size: 11px;
            line-height: 1;
            font-weight: 900;
            letter-spacing: 0.07em;
            text-transform: uppercase;
        }

        .sv-speaking-page .sv-eyebrow svg {
            width: 14px;
            height: 14px;
        }

        .sv-speaking-page .sv-title {
            margin: 12px 0 0;
            color: var(--sv-text);
            font-size: clamp(27px, 3.2vw, 40px);
            line-height: 1.1;
            font-weight: 950;
            letter-spacing: -0.035em;
        }

        .sv-speaking-page .sv-subtitle {
            max-width: 680px;
            margin: 8px 0 0;
            color: var(--sv-muted);
            font-size: 14px;
            line-height: 1.7;
            font-weight: 600;
        }

        .sv-speaking-page .sv-hero-stats {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 9px;
        }

        .sv-speaking-page .sv-stat {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
            min-height: 68px;
            padding: 11px 12px;
            border: 1px solid var(--sv-border);
            border-radius: 15px;
            background: var(--sv-card-soft);
        }

        .sv-speaking-page .sv-stat-icon {
            display: inline-flex;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 11px;
            background: var(--sv-card);
            color: var(--sv-purple);
            box-shadow: inset 0 0 0 1px var(--sv-border);
        }

        .sv-speaking-page .sv-stat-icon.is-blue {
            color: var(--sv-blue);
        }

        .sv-speaking-page .sv-stat-icon.is-emerald {
            color: var(--sv-emerald);
        }

        .sv-speaking-page .sv-stat-icon.is-amber {
            color: var(--sv-amber);
        }

        .sv-speaking-page .sv-stat-icon svg {
            width: 17px;
            height: 17px;
        }

        .sv-speaking-page .sv-stat-label {
            color: var(--sv-muted);
            font-size: 10px;
            line-height: 1.2;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .sv-speaking-page .sv-stat-value {
            margin-top: 3px;
            overflow: hidden;
            color: var(--sv-text);
            font-size: 14px;
            line-height: 1.3;
            font-weight: 900;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* Section headings */
        .sv-speaking-page .sv-section {
            padding: 18px;
        }

        .sv-speaking-page .sv-section-head {
            display: flex;
            align-items: center;
            gap: 11px;
            margin-bottom: 14px;
        }

        .sv-speaking-page .sv-section-icon {
            display: inline-flex;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: 12px;
            background: var(--sv-purple-soft);
            color: var(--sv-purple);
        }

        .sv-speaking-page .sv-section-icon.is-blue {
            background: var(--sv-blue-soft);
            color: var(--sv-blue);
        }

        .sv-speaking-page .sv-section-icon.is-emerald {
            background: var(--sv-emerald-soft);
            color: var(--sv-emerald);
        }

        .sv-speaking-page .sv-section-icon svg {
            width: 18px;
            height: 18px;
        }

        .sv-speaking-page .sv-section-title {
            margin: 0;
            color: var(--sv-text);
            font-size: 17px;
            line-height: 1.3;
            font-weight: 900;
            letter-spacing: -0.015em;
        }

        .sv-speaking-page .sv-section-description {
            margin: 2px 0 0;
            color: var(--sv-muted);
            font-size: 12px;
            line-height: 1.5;
            font-weight: 600;
        }

        /* Briefing */
        .sv-speaking-page .sv-briefing-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .sv-speaking-page .sv-brief-card {
            min-width: 0;
            padding: 14px 15px;
            border: 1px solid var(--sv-border);
            border-radius: 15px;
            background: var(--sv-card-soft);
        }

        .sv-speaking-page .sv-brief-label {
            display: flex;
            align-items: center;
            gap: 7px;
            margin: 0;
            color: var(--sv-purple);
            font-size: 11px;
            line-height: 1.3;
            font-weight: 900;
            letter-spacing: 0.07em;
            text-transform: uppercase;
        }

        .sv-speaking-page .sv-brief-card.is-blue .sv-brief-label {
            color: var(--sv-blue);
        }

        .sv-speaking-page .sv-brief-label svg {
            width: 15px;
            height: 15px;
        }

        .sv-speaking-page .sv-brief-text {
            margin: 9px 0 0;
            color: var(--sv-text-soft);
            font-size: 13px;
            line-height: 1.72;
            font-weight: 600;
            white-space: pre-line;
            overflow-wrap: anywhere;
        }

        /* Unified activity setup */
        .sv-speaking-page .sv-activity {
            padding: 18px;
        }

        .sv-speaking-page .sv-activity-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            align-items: stretch;
        }

        .sv-speaking-page .sv-activity-grid.is-single {
            grid-template-columns: minmax(0, 1fr);
        }

        .sv-speaking-page .sv-activity-pane {
            min-width: 0;
            padding: 0 18px;
        }

        .sv-speaking-page .sv-activity-pane:first-child {
            padding-left: 0;
        }

        .sv-speaking-page .sv-activity-pane:last-child {
            padding-right: 0;
        }

        .sv-speaking-page .sv-activity-pane + .sv-activity-pane {
            border-left: 1px solid var(--sv-border);
        }

        .sv-speaking-page .sv-activity-pane .sv-section-head {
            margin-bottom: 12px;
        }

        .sv-speaking-page .sv-support-section {
            padding: 16px 18px;
        }

        .sv-speaking-page .sv-support-section .sv-section-head {
            margin-bottom: 12px;
        }

        /* Role cards */
        .sv-speaking-page .sv-role-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 11px;
        }

        .sv-speaking-page .sv-role {
            position: relative;
            overflow: hidden;
            min-width: 0;
            padding: 14px;
            border: 1px solid var(--sv-purple-border);
            border-radius: 16px;
            background: var(--sv-purple-soft);
        }

        .sv-speaking-page .sv-role.is-b {
            border-color: var(--sv-blue-border);
            background: var(--sv-blue-soft);
        }

        .sv-speaking-page .sv-role-head {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sv-speaking-page .sv-role-letter {
            display: inline-flex;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 11px;
            background: #7c3aed;
            color: #ffffff;
            font-size: 14px;
            font-weight: 900;
            box-shadow: 0 8px 18px rgba(124, 58, 237, 0.22);
        }

        .sv-speaking-page .sv-role.is-b .sv-role-letter {
            background: #2563eb;
            box-shadow: 0 8px 18px rgba(37, 99, 235, 0.22);
        }

        .sv-speaking-page .sv-role-label {
            color: var(--sv-muted);
            font-size: 9px;
            line-height: 1.2;
            font-weight: 900;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .sv-speaking-page .sv-role-title {
            margin: 2px 0 0;
            color: var(--sv-text);
            font-size: 14px;
            line-height: 1.3;
            font-weight: 900;
        }

        .sv-speaking-page .sv-role-text {
            margin: 12px 0 0;
            padding-top: 11px;
            border-top: 1px solid var(--sv-purple-border);
            color: var(--sv-text-soft);
            font-size: 12px;
            line-height: 1.65;
            font-weight: 600;
            white-space: pre-line;
            overflow-wrap: anywhere;
        }

        .sv-speaking-page .sv-role.is-b .sv-role-text {
            border-top-color: var(--sv-blue-border);
        }

        /* Discussion points */
        .sv-speaking-page .sv-point-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
        }

        .sv-speaking-page .sv-point {
            display: flex;
            align-items: flex-start;
            gap: 9px;
            min-width: 0;
            min-height: 48px;
            padding: 10px 11px;
            border: 1px solid var(--sv-border);
            border-radius: 13px;
            background: var(--sv-card-soft);
        }

        .sv-speaking-page .sv-point-number {
            display: inline-flex;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            width: 25px;
            height: 25px;
            border-radius: 8px;
            background: var(--sv-emerald-soft);
            color: var(--sv-emerald);
            font-size: 10px;
            font-weight: 900;
        }

        .sv-speaking-page .sv-point-text {
            min-width: 0;
            margin: 2px 0 0;
            color: var(--sv-text-soft);
            font-size: 12px;
            line-height: 1.5;
            font-weight: 650;
            overflow-wrap: anywhere;
        }

        /* Supporting material */
        .sv-speaking-page .sv-details {
            overflow: hidden;
            border: 1px solid var(--sv-border);
            border-radius: 16px;
            background: var(--sv-card-soft);
        }

        .sv-speaking-page .sv-details summary {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            min-height: 54px;
            padding: 12px 14px;
            color: var(--sv-text);
            font-size: 13px;
            font-weight: 900;
            cursor: pointer;
            list-style: none;
        }

        .sv-speaking-page .sv-details summary::-webkit-details-marker {
            display: none;
        }

        .sv-speaking-page .sv-details summary::after {
            content: "+";
            display: inline-flex;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            width: 26px;
            height: 26px;
            border-radius: 8px;
            background: var(--sv-card-muted);
            color: var(--sv-muted);
            font-size: 18px;
            line-height: 1;
            font-weight: 700;
        }

        .sv-speaking-page .sv-details[open] summary::after {
            content: "−";
        }

        .sv-speaking-page .sv-details-body {
            display: grid;
            gap: 12px;
            padding: 0 14px 14px;
            border-top: 1px solid var(--sv-border);
        }

        .sv-speaking-page .sv-material-image {
            margin-top: 14px;
            overflow: hidden;
            border: 1px solid var(--sv-border);
            border-radius: 13px;
            background: var(--sv-card);
        }

        .sv-speaking-page .sv-material-image img {
            display: block;
            width: 100%;
            max-height: 360px;
            object-fit: contain;
        }

        .sv-speaking-page .sv-material-passage {
            margin-top: 14px;
            padding: 13px 14px;
            border: 1px solid var(--sv-border);
            border-radius: 13px;
            background: var(--sv-card);
            color: var(--sv-text-soft);
            font-size: 13px;
            line-height: 1.72;
            font-weight: 600;
            white-space: pre-line;
            overflow-wrap: anywhere;
        }

        /* Reminder */
        .sv-speaking-page .sv-reminder {
            padding: 15px;
            border: 1px solid var(--sv-amber-border);
            border-radius: 16px;
            background: var(--sv-amber-soft);
        }

        .sv-speaking-page .sv-reminder-title {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0;
            color: var(--sv-amber);
            font-size: 13px;
            font-weight: 900;
        }

        .sv-speaking-page .sv-reminder-title svg {
            width: 17px;
            height: 17px;
        }

        .sv-speaking-page .sv-reminder-list {
            display: grid;
            gap: 7px;
            margin: 10px 0 0;
            padding: 0;
            list-style: none;
        }

        .sv-speaking-page .sv-reminder-item {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            color: var(--sv-amber);
            font-size: 12px;
            line-height: 1.55;
            font-weight: 650;
        }

        .sv-speaking-page .sv-reminder-dot {
            flex: 0 0 auto;
            width: 5px;
            height: 5px;
            margin-top: 7px;
            border-radius: 999px;
            background: currentColor;
        }

        /* Start panel */
        .sv-speaking-page .sv-start {
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            padding: 18px 20px;
            border: 1px solid #4c3d79;
            border-radius: 20px;
            background:
                radial-gradient(circle at 92% 4%, rgba(139, 92, 246, 0.28), transparent 33%),
                linear-gradient(135deg, #0f172a 0%, #172033 52%, #24204f 100%);
            box-shadow: 0 16px 36px rgba(15, 23, 42, 0.2);
        }

        .sv-speaking-page .sv-start-copy {
            min-width: 0;
        }

        .sv-speaking-page .sv-start-kicker {
            margin: 0;
            color: #c4b5fd;
            font-size: 10px;
            line-height: 1.2;
            font-weight: 900;
            letter-spacing: 0.11em;
            text-transform: uppercase;
        }

        .sv-speaking-page .sv-start-title {
            margin: 5px 0 0;
            color: #ffffff;
            font-size: 19px;
            line-height: 1.3;
            font-weight: 900;
        }

        .sv-speaking-page .sv-start-text {
            max-width: 700px;
            margin: 5px 0 0;
            color: #cbd5e1;
            font-size: 12px;
            line-height: 1.6;
            font-weight: 600;
        }

        .sv-speaking-page .sv-start-actions {
            flex: 0 0 auto;
            text-align: center;
        }

        .sv-speaking-page .sv-start-button {
            display: inline-flex;
            min-height: 50px;
            align-items: center;
            justify-content: center;
            gap: 9px;
            padding: 12px 18px;
            border: 1px solid rgba(255, 255, 255, 0.16);
            border-radius: 14px;
            background: linear-gradient(100deg, #8b5cf6, #4f46e5);
            color: #ffffff !important;
            font-size: 13px;
            line-height: 1;
            font-weight: 900;
            box-shadow: 0 12px 26px rgba(79, 70, 229, 0.34);
            transition:
                transform 160ms ease,
                filter 160ms ease,
                box-shadow 160ms ease;
        }

        .sv-speaking-page .sv-start-button:hover {
            transform: translateY(-1px);
            filter: brightness(1.07);
            box-shadow: 0 15px 30px rgba(79, 70, 229, 0.42);
        }

        .sv-speaking-page .sv-start-button:focus-visible {
            outline: 3px solid rgba(196, 181, 253, 0.5);
            outline-offset: 3px;
        }

        .sv-speaking-page .sv-start-button svg {
            width: 17px;
            height: 17px;
        }

        .sv-speaking-page .sv-mic-note {
            margin: 7px 0 0;
            color: #94a3b8;
            font-size: 10px;
            line-height: 1.4;
            font-weight: 600;
        }

        /* Empty state */
        .sv-speaking-page .sv-empty {
            padding: 46px 22px;
            text-align: center;
        }

        .sv-speaking-page .sv-empty-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 58px;
            height: 58px;
            border-radius: 17px;
            background: var(--sv-purple-soft);
            color: var(--sv-purple);
        }

        .sv-speaking-page .sv-empty-icon svg {
            width: 27px;
            height: 27px;
        }

        .sv-speaking-page .sv-empty-title {
            margin: 15px 0 0;
            color: var(--sv-text);
            font-size: 22px;
            font-weight: 900;
        }

        .sv-speaking-page .sv-empty-text {
            max-width: 580px;
            margin: 7px auto 0;
            color: var(--sv-muted);
            font-size: 13px;
            line-height: 1.7;
            font-weight: 600;
        }

        .sv-speaking-page .sv-back-button {
            display: inline-flex;
            min-height: 46px;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 18px;
            padding: 11px 17px;
            border: 1px solid var(--sv-border);
            border-radius: 13px;
            background: var(--sv-card-soft);
            color: var(--sv-text) !important;
            font-size: 13px;
            font-weight: 900;
        }

        .sv-speaking-page .sv-back-button svg {
            width: 17px;
            height: 17px;
        }

        @media (max-width: 980px) {
            .sv-speaking-page .sv-hero {
                grid-template-columns: minmax(0, 1fr);
            }

            .sv-speaking-page .sv-hero-stats {
                max-width: none;
            }
        }

        @media (max-width: 860px) {
            .sv-speaking-page .sv-activity-grid {
                grid-template-columns: minmax(0, 1fr);
                gap: 16px;
            }

            .sv-speaking-page .sv-activity-pane,
            .sv-speaking-page .sv-activity-pane:first-child,
            .sv-speaking-page .sv-activity-pane:last-child {
                padding: 0;
            }

            .sv-speaking-page .sv-activity-pane + .sv-activity-pane {
                padding-top: 16px;
                border-top: 1px solid var(--sv-border);
                border-left: 0;
            }
        }

        @media (max-width: 680px) {
            .sv-speaking-page .sv-stack {
                gap: 13px;
            }

            .sv-speaking-page .sv-hero {
                gap: 16px;
                padding: 17px;
            }

            .sv-speaking-page .sv-hero-stats,
            .sv-speaking-page .sv-briefing-grid,
            .sv-speaking-page .sv-role-grid,
            .sv-speaking-page .sv-point-grid {
                grid-template-columns: minmax(0, 1fr);
            }

            .sv-speaking-page .sv-section {
                padding: 15px;
            }

            .sv-speaking-page .sv-start {
                align-items: stretch;
                flex-direction: column;
                padding: 17px;
            }

            .sv-speaking-page .sv-start-actions,
            .sv-speaking-page .sv-start-button {
                width: 100%;
            }

            .sv-speaking-page .sv-stat-value {
                white-space: normal;
            }
        }
    </style>

    <div class="sv-speaking-page">
        <div class="sv-stack">
            {{-- ================================================================
                SESSION ERROR
            ================================================================= --}}
            @if (session('error'))
                <div class="sv-alert" role="alert">
                    <span class="sv-alert-icon" aria-hidden="true">
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z">
                            </path>
                        </svg>
                    </span>

                    <div>
                        <p class="sv-alert-title">Speaking Task Unavailable</p>
                        <p class="sv-alert-text">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            {{-- ================================================================
                EMPTY STATE
            ================================================================= --}}
            @if (!$material)
                <section class="sv-card sv-empty">
                    <span class="sv-empty-icon" aria-hidden="true">
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2">
                            <rect width="8" height="13" x="8" y="2" rx="4"></rect>
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M5 10a7 7 0 0 0 14 0M12 17v5M8 22h8">
                            </path>
                        </svg>
                    </span>

                    <h1 class="sv-empty-title">Speaking Task Belum Tersedia</h1>

                    <p class="sv-empty-text">
                        @if ($isAssessment)
                            Task untuk {{ $assessmentLabel }} Individual Speaking belum ditambahkan oleh administrator.
                        @else
                            Speaking task untuk lesson {{ $lesson->title }} belum ditambahkan oleh administrator.
                        @endif
                    </p>

                    <a href="{{ route('missions') }}" class="sv-back-button">
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            aria-hidden="true">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="m15 18-6-6 6-6">
                            </path>
                        </svg>

                        Back to Missions
                    </a>
                </section>
            @else
                @php
                    $isPairWork =
                        !$isAssessment &&
                        (bool) $material->is_pair_work;

                    $minimumMinutes = $isAssessment
                        ? 2
                        : (
                            $material->min_duration
                                ? (int) ceil($material->min_duration / 60)
                                : null
                        );

                    $maximumMinutes = $isAssessment
                        ? 3
                        : (
                            $material->max_duration
                                ? (int) ceil($material->max_duration / 60)
                                : null
                        );

                    if ($minimumMinutes && $maximumMinutes) {
                        $durationLabel = $minimumMinutes === $maximumMinutes
                            ? $minimumMinutes . ' min'
                            : $minimumMinutes . '–' . $maximumMinutes . ' min';
                    } elseif ($minimumMinutes) {
                        $durationLabel = 'Min. ' . $minimumMinutes . ' min';
                    } elseif ($maximumMinutes) {
                        $durationLabel = 'Max. ' . $maximumMinutes . ' min';
                    } else {
                        $durationLabel = 'Flexible';
                    }

                    $discussionPoints = is_array($material->discussion_points)
                        ? array_values(
                            array_filter(
                                $material->discussion_points,
                                fn ($point) => trim((string) $point) !== ''
                            )
                        )
                        : [];

                    $startRoute = $isAssessment
                        ? route(
                            'student.assessment.speaking.quiz',
                            [
                                'type' => $assessmentType,
                            ]
                        )
                        : route(
                            'student.speaking.quiz',
                            $lesson
                        );
                @endphp

                {{-- ================================================================
                    COMPACT HERO
                ================================================================= --}}
                <section class="sv-card sv-hero">
                    <div class="sv-hero-copy">
                        <span class="sv-eyebrow">
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                aria-hidden="true">
                                <rect width="8" height="13" x="8" y="2" rx="4"></rect>
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M5 10a7 7 0 0 0 14 0M12 17v5M8 22h8">
                                </path>
                            </svg>

                            @if ($isAssessment)
                                {{ $assessmentLabel }} Individual Speaking
                            @elseif ($isPairWork)
                                Pair Speaking Mission
                            @else
                                Individual Speaking Mission
                            @endif
                        </span>

                        <h1 class="sv-title">{{ $material->title }}</h1>

                        <p class="sv-subtitle">
                            @if ($isAssessment)
                                Choose one fable or short story and present it individually. Explain the important story elements and your opinion clearly.
                            @elseif ($isPairWork)
                                {{ $lesson->description ?: 'Practise an English conversation with a partner and submit one shared recording.' }}
                            @else
                                {{ $lesson->description ?: 'Complete this speaking activity and submit your recording.' }}
                            @endif
                        </p>
                    </div>

                    <div class="sv-hero-stats">
                        <article class="sv-stat">
                            <span class="sv-stat-icon" aria-hidden="true">
                                @if ($isAssessment || !$isPairWork)
                                    <svg
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M20 21a8 8 0 0 0-16 0M12 13a5 5 0 1 0 0-10 5 5 0 0 0 0 10Z">
                                        </path>
                                    </svg>
                                @else
                                    <svg
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75">
                                        </path>
                                    </svg>
                                @endif
                            </span>

                            <div>
                                <div class="sv-stat-label">Mode</div>
                                <div class="sv-stat-value">
                                    {{ $isAssessment || !$isPairWork ? 'Individual' : 'Pair Work' }}
                                </div>
                            </div>
                        </article>

                        <article class="sv-stat">
                            <span class="sv-stat-icon is-blue" aria-hidden="true">
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2">
                                    <circle cx="12" cy="12" r="9"></circle>
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M12 7v5l3 2">
                                    </path>
                                </svg>
                            </span>

                            <div>
                                <div class="sv-stat-label">Duration</div>
                                <div class="sv-stat-value">{{ $durationLabel }}</div>
                            </div>
                        </article>

                        <article class="sv-stat">
                            <span class="sv-stat-icon is-amber" aria-hidden="true">
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M8 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2M14 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM2 21v-2a4 4 0 0 1 3-3.87M8 3.13a4 4 0 0 0 0 7.75">
                                    </path>
                                </svg>
                            </span>

                            <div>
                                <div class="sv-stat-label">Participants</div>
                                <div class="sv-stat-value">
                                    {{ $isAssessment || !$isPairWork ? '1 Student' : 'Student A & B' }}
                                </div>
                            </div>
                        </article>

                        <article class="sv-stat">
                            <span class="sv-stat-icon is-emerald" aria-hidden="true">
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="m12 3 1.8 4.7L18.5 9.5l-4.7 1.8L12 16l-1.8-4.7-4.7-1.8 4.7-1.8L12 3Z">
                                    </path>
                                </svg>
                            </span>

                            <div>
                                <div class="sv-stat-label">Evaluation</div>
                                <div class="sv-stat-value">
                                    {{ $isAssessment || $material->ai_evaluation_enabled
                                        ? 'AI Rubric'
                                        : 'Submission Only' }}
                                </div>
                            </div>
                        </article>
                    </div>
                </section>

                {{-- ================================================================
                    TASK BRIEFING
                ================================================================= --}}
                <section class="sv-card sv-section">
                    <div class="sv-section-head">
                        <span class="sv-section-icon" aria-hidden="true">
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z">
                                </path>
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M14 2v6h6M8 13h8M8 17h6">
                                </path>
                            </svg>
                        </span>

                        <div>
                            <h2 class="sv-section-title">Task Briefing</h2>
                            <p class="sv-section-description">
                                Read the instruction and understand the conversation context.
                            </p>
                        </div>
                    </div>

                    <div class="sv-briefing-grid">
                        <article class="sv-brief-card">
                            <p class="sv-brief-label">
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    aria-hidden="true">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="m5 12 4 4L19 6">
                                    </path>
                                </svg>

                                Task Instruction
                            </p>

                            <p class="sv-brief-text">
                                {{ $material->instruction ?: 'Follow the speaking task instructions carefully.' }}
                            </p>
                        </article>

                        <article class="sv-brief-card is-blue">
                            <p class="sv-brief-label">
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    aria-hidden="true">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4Z">
                                    </path>
                                </svg>

                                {{ $isAssessment ? 'Presentation Context' : 'Conversation Scenario' }}
                            </p>

                            <p class="sv-brief-text">
                                {{ $material->scenario
                                    ?: (
                                        $isAssessment
                                            ? 'Present one fable or short story individually to your friends.'
                                            : 'Follow the speaking task instruction.'
                                    ) }}
                            </p>
                        </article>
                    </div>
                </section>

                {{-- ================================================================
                    ACTIVITY SETUP
                ================================================================= --}}
                @if (!$isAssessment || count($discussionPoints))
                    <section class="sv-card sv-activity">
                        <div
                            class="sv-activity-grid {{ $isAssessment || !count($discussionPoints) ? 'is-single' : '' }}">

                            {{-- STUDENT ROLES — UNIT 1–4 ONLY --}}
                            @if (!$isAssessment)
                                <div class="sv-activity-pane">
                                    <div class="sv-section-head">
                                        <span class="sv-section-icon" aria-hidden="true">
                                            <svg
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75">
                                                </path>
                                            </svg>
                                        </span>

                                        <div>
                                            <h2 class="sv-section-title">Student Roles</h2>
                                            <p class="sv-section-description">
                                                Decide the roles before starting the recording.
                                            </p>
                                        </div>
                                    </div>

                                    <div class="sv-role-grid">
                                        <article class="sv-role">
                                            <div class="sv-role-head">
                                                <span class="sv-role-letter">A</span>

                                                <div>
                                                    <div class="sv-role-label">Role</div>
                                                    <h3 class="sv-role-title">Student A</h3>
                                                </div>
                                            </div>

                                            <p class="sv-role-text">
                                                {{ $material->role_a ?: 'Follow Student A instructions during the conversation.' }}
                                            </p>
                                        </article>

                                        <article class="sv-role is-b">
                                            <div class="sv-role-head">
                                                <span class="sv-role-letter">B</span>

                                                <div>
                                                    <div class="sv-role-label">Role</div>
                                                    <h3 class="sv-role-title">Student B</h3>
                                                </div>
                                            </div>

                                            <p class="sv-role-text">
                                                {{ $material->role_b ?: 'Follow Student B instructions during the conversation.' }}
                                            </p>
                                        </article>
                                    </div>
                                </div>
                            @endif

                            {{-- DISCUSSION POINTS / PRESENTATION CHECKLIST --}}
                            @if (count($discussionPoints))
                                <div class="sv-activity-pane">
                                    <div class="sv-section-head">
                                        <span class="sv-section-icon is-emerald" aria-hidden="true">
                                            <svg
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="m9 11 3 3L22 4M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11">
                                                </path>
                                            </svg>
                                        </span>

                                        <div>
                                            <h2 class="sv-section-title">
                                                {{ $isAssessment
                                                    ? 'Presentation Checklist'
                                                    : 'Discussion Points' }}
                                            </h2>
                                            <p class="sv-section-description">
                                                Cover every point in the speaking activity.
                                            </p>
                                        </div>
                                    </div>

                                    <div class="sv-point-grid">
                                        @foreach ($discussionPoints as $index => $point)
                                            <article class="sv-point">
                                                <span class="sv-point-number">
                                                    {{ $index + 1 }}
                                                </span>

                                                <p class="sv-point-text">{{ $point }}</p>
                                            </article>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </section>
                @endif

                {{-- ================================================================
                    SUPPORTING MATERIAL
                ================================================================= --}}
                @if ($material->image || $material->passage)
                    <section class="sv-card sv-support-section">
                        <div class="sv-section-head">
                            <span class="sv-section-icon is-blue" aria-hidden="true">
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M4 5a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5Z">
                                    </path>
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="m8 13 2.5-2.5L14 14l2-2 4 4M8.5 8.5h.01">
                                    </path>
                                </svg>
                            </span>

                            <div>
                                <h2 class="sv-section-title">
                                    {{ $isAssessment ? 'Example Presentation' : 'Supporting Material' }}
                                </h2>
                                <p class="sv-section-description">
                                    Open only when you need the reference.
                                </p>
                            </div>
                        </div>

                        <details class="sv-details">
                            <summary>View supporting material</summary>

                            <div class="sv-details-body">
                                @if ($material->image)
                                    <div class="sv-material-image">
                                        <img
                                            src="{{ asset('storage/' . $material->image) }}"
                                            alt="{{ $material->title }}"
                                            loading="lazy">
                                    </div>
                                @endif

                                @if ($material->passage)
                                    <div class="sv-material-passage">
                                        {{ $material->passage }}
                                    </div>
                                @endif
                            </div>
                        </details>
                    </section>
                @endif

                {{-- ================================================================
                    ASSESSMENT REMINDER
                ================================================================= --}}
                @if ($isAssessment)
                    <aside class="sv-reminder">
                        <h2 class="sv-reminder-title">
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                aria-hidden="true">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z">
                                </path>
                            </svg>

                            Important Assessment Rules
                        </h2>

                        <ul class="sv-reminder-list">
                            <li class="sv-reminder-item">
                                <span class="sv-reminder-dot"></span>
                                Complete the presentation by yourself.
                            </li>
                            <li class="sv-reminder-item">
                                <span class="sv-reminder-dot"></span>
                                Record between 2 and 3 minutes.
                            </li>
                            <li class="sv-reminder-item">
                                <span class="sv-reminder-dot"></span>
                                Speak clearly and cover all required story elements.
                            </li>
                        </ul>
                    </aside>
                @endif

                {{-- ================================================================
                    START SPEAKING
                ================================================================= --}}
                <section class="sv-start">
                    <div class="sv-start-copy">
                        <p class="sv-start-kicker">
                            @if ($isAssessment)
                                Individual speaking assessment
                            @elseif ($isPairWork)
                                Pair speaking task
                            @else
                                Speaking task
                            @endif
                        </p>

                        <h2 class="sv-start-title">Ready to Start?</h2>

                        <p class="sv-start-text">
                            @if ($isAssessment)
                                Prepare your story and record one complete presentation lasting {{ $durationLabel }}.
                            @elseif ($isPairWork)
                                Prepare your partner, choose the roles, and record one complete conversation together.
                            @else
                                Prepare your response and record one complete speaking submission.
                            @endif
                        </p>
                    </div>

                    <div class="sv-start-actions">
                        <a href="{{ $startRoute }}" class="sv-start-button">
                            <span>
                                @if ($isAssessment)
                                    Start {{ $assessmentLabel }} Speaking
                                @elseif ($isPairWork)
                                    Start Pair Speaking
                                @else
                                    Start Speaking Task
                                @endif
                            </span>

                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2.4"
                                aria-hidden="true">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M5 12h14m-6-6 6 6-6 6">
                                </path>
                            </svg>
                        </a>

                        <p class="sv-mic-note">Make sure your microphone is ready.</p>
                    </div>
                </section>
            @endif
        </div>
    </div>
</x-app-layout>