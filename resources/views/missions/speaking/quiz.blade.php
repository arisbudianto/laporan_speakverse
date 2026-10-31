<x-app-layout>
    @php
        /*
        |--------------------------------------------------------------------------
        | Page Context
        |--------------------------------------------------------------------------
        |
        | PRETEST / POSTTEST:
        | - Individual speaking
        | - Tidak memilih partner
        | - Tidak memilih Student A / Student B
        | - Durasi 2–3 menit
        |
        | Unit 1–4:
        | - Konsep pair speaking tetap dipertahankan
        | - Memilih partner
        | - Memilih role Student A / Student B
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

        /*
        |--------------------------------------------------------------------------
        | Activity Mode
        |--------------------------------------------------------------------------
        */
        $isPairWork =
            !$isAssessment &&
            (bool) $material->is_pair_work;

        /*
        |--------------------------------------------------------------------------
        | Duration
        |--------------------------------------------------------------------------
        |
        | Assessment selalu 2–3 menit.
        |
        */
        $minimumSeconds = $isAssessment
            ? 120
            : (int) ($material->min_duration ?? 0);

        $maximumSeconds = $isAssessment
            ? 180
            : (int) ($material->max_duration ?? 0);

        $minimumMinutes = $minimumSeconds
            ? (int) ceil($minimumSeconds / 60)
            : null;

        $maximumMinutes = $maximumSeconds
            ? (int) ceil($maximumSeconds / 60)
            : null;

        /*
        |--------------------------------------------------------------------------
        | Presentation / Discussion Points
        |--------------------------------------------------------------------------
        */
        $discussionPoints =
            is_array($material->discussion_points)
                ? $material->discussion_points
                : [];

        /*
        |--------------------------------------------------------------------------
        | Clean Task Text
        |--------------------------------------------------------------------------
        | Remove leading and trailing blank lines from database content while
        | preserving intentional line breaks inside the text.
        */
        $instructionText = str_replace(
            ["\r\n", "\r"],
            "\n",
            (string) ($material->instruction ?? '')
        );

        $instructionText = trim($instructionText);
        $instructionText = preg_replace(
            "/\n[ \t]*\n(?:[ \t]*\n)+/u",
            "\n\n",
            $instructionText
        ) ?? $instructionText;

        $scenarioSource = $material->scenario
            ?: (
                $isAssessment
                    ? 'Present one fable or short story individually.'
                    : 'Follow the speaking task instruction.'
            );

        $scenarioText = str_replace(
            ["\r\n", "\r"],
            "\n",
            (string) $scenarioSource
        );

        $scenarioText = trim($scenarioText);
        $scenarioText = preg_replace(
            "/\n[ \t]*\n(?:[ \t]*\n)+/u",
            "\n\n",
            $scenarioText
        ) ?? $scenarioText;

        /*
        |--------------------------------------------------------------------------
        | Routes
        |--------------------------------------------------------------------------
        */
        $submitRoute = $isAssessment
            ? route(
                'student.assessment.speaking.submit',
                [
                    'type' =>
                        $assessmentType,

                    'material' =>
                        $material,
                ]
            )
            : route(
                'student.speaking.submit',
                [
                    'lesson' =>
                        $lesson,

                    'material' =>
                        $material,
                ]
            );

        $backRoute = $isAssessment
            ? route(
                'student.assessment.show',
                [
                    'type' =>
                        $assessmentType,

                    'skill' =>
                        'speaking',
                ]
            )
            : route(
                'student.speaking',
                $lesson
            );
    @endphp

    <style>
        /*
        |--------------------------------------------------------------------------
        | Speaking Recording Page
        |--------------------------------------------------------------------------
        */
        .sv-speaking-quiz,
        .sv-speaking-quiz * {
            box-sizing: border-box;
        }

        .sv-speaking-quiz {
            width: 100%;
            max-width: 1120px;
            margin: 0 auto;
        }

        /*
        |--------------------------------------------------------------------------
        | Recording Buttons
        |--------------------------------------------------------------------------
        */
        .sv-record-button {
            display: inline-flex !important;
            min-height: 52px;
            align-items: center;
            justify-content: center;
            gap: 9px;
            padding: 13px 21px;
            border: 0 !important;
            border-radius: 14px;
            font-size: 14px;
            font-weight: 800;
            line-height: 1;
            cursor: pointer;
            transition:
                transform 160ms ease,
                background 160ms ease,
                opacity 160ms ease,
                box-shadow 160ms ease;
        }

        .sv-record-button:hover:not(:disabled) {
            transform: translateY(-1px);
        }

        .sv-record-button:disabled {
            cursor: not-allowed !important;
            opacity: 0.45 !important;
            transform: none !important;
            box-shadow: none !important;
        }

        .sv-record-button-start {
            background: #16a34a !important;
            color: #ffffff !important;
            box-shadow: 0 12px 24px rgba(22, 163, 74, 0.2) !important;
        }

        .sv-record-button-start:hover:not(:disabled) {
            background: #15803d !important;
        }

        .sv-record-button-stop {
            background: #dc2626 !important;
            color: #ffffff !important;
            box-shadow: 0 12px 24px rgba(220, 38, 38, 0.18) !important;
        }

        .sv-record-button-stop:hover:not(:disabled) {
            background: #b91c1c !important;
        }

        /*
        |--------------------------------------------------------------------------
        | Submit Button
        |--------------------------------------------------------------------------
        */
        .sv-speaking-submit {
            display: inline-flex !important;
            min-height: 56px;
            align-items: center;
            justify-content: center;
            gap: 10px;
            border: 0 !important;
            border-radius: 15px;
            background:
                linear-gradient(
                    135deg,
                    #8b5cf6 0%,
                    #7c3aed 48%,
                    #4f46e5 100%
                ) !important;
            color: #ffffff !important;
            font-size: 15px;
            font-weight: 850;
            cursor: pointer;
            box-shadow: 0 16px 32px rgba(124, 58, 237, 0.22) !important;
            transition:
                transform 160ms ease,
                box-shadow 160ms ease,
                opacity 160ms ease;
        }

        .sv-speaking-submit:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 20px 38px rgba(124, 58, 237, 0.28) !important;
        }

        .sv-speaking-submit:disabled {
            cursor: not-allowed;
            opacity: 0.65;
            transform: none !important;
        }

        /*
        |--------------------------------------------------------------------------
        | Role Cards
        |--------------------------------------------------------------------------
        */
        .sv-speaking-role-card {
            position: relative;
            overflow: hidden;
            border-style: solid !important;
            border-width: 1px !important;
            transition:
                border-color 170ms ease,
                box-shadow 170ms ease,
                transform 170ms ease;
        }

        .sv-speaking-role-a {
            border-color: #e9d5ff !important;
            background: #faf5ff !important;
        }

        .sv-speaking-role-b {
            border-color: #bfdbfe !important;
            background: #eff6ff !important;
        }

        .dark .sv-speaking-role-a {
            border-color: rgba(168, 85, 247, 0.35) !important;
            background: rgba(126, 34, 206, 0.1) !important;
        }

        .dark .sv-speaking-role-b {
            border-color: rgba(59, 130, 246, 0.35) !important;
            background: rgba(37, 99, 235, 0.1) !important;
        }

        .sv-speaking-role-card.is-selected {
            transform: translateY(-2px);
        }

        .sv-speaking-role-a.is-selected {
            border-color: #a855f7 !important;
            box-shadow:
                0 0 0 3px rgba(168, 85, 247, 0.18),
                0 16px 30px rgba(126, 34, 206, 0.08) !important;
        }

        .sv-speaking-role-b.is-selected {
            border-color: #3b82f6 !important;
            box-shadow:
                0 0 0 3px rgba(59, 130, 246, 0.18),
                0 16px 30px rgba(37, 99, 235, 0.08) !important;
        }

        /*
        |--------------------------------------------------------------------------
        | Loading Spinner
        |--------------------------------------------------------------------------
        */
        .sv-loading-spinner {
            width: 48px;
            height: 48px;
            margin: 0 auto;
            border: 4px solid #e2e8f0;
            border-top-color: #7c3aed;
            border-radius: 999px;
            animation: sv-spin 0.8s linear infinite;
        }

        .dark .sv-loading-spinner {
            border-color: #334155;
            border-top-color: #a78bfa;
        }

        @keyframes sv-spin {
            to {
                transform: rotate(360deg);
            }
        }

        @media (max-width: 639px) {
            .sv-record-button,
            .sv-speaking-submit {
                width: 100%;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | SpeakVerse Speaking Quiz — Compact Professional Layout
        |--------------------------------------------------------------------------
        |
        | Scoped only to this page. This override keeps all Blade variables,
        | routes, recording logic, transcript logic, pair selection, and submit
        | behavior intact while making the interface denser and better aligned.
        |
        */
        .sv-speaking-quiz {
            --sv-card: #ffffff;
            --sv-card-soft: #f8fafc;
            --sv-card-muted: #f1f5f9;
            --sv-border: #dbe4ef;
            --sv-border-strong: #cbd5e1;
            --sv-text: #0f172a;
            --sv-text-soft: #334155;
            --sv-text-muted: #64748b;
            --sv-purple: #7c3aed;
            --sv-purple-soft: #f5f3ff;
            --sv-blue: #2563eb;
            --sv-blue-soft: #eff6ff;
            --sv-cyan: #0891b2;
            --sv-emerald: #059669;
            --sv-shadow: 0 10px 28px rgba(15, 23, 42, 0.07);

            display: grid;
            gap: 14px;
            width: min(100%, 1120px);
            color: var(--sv-text);
        }

        .dark .sv-speaking-quiz {
            --sv-card: #0f172a;
            --sv-card-soft: #111c31;
            --sv-card-muted: #172033;
            --sv-border: #26364d;
            --sv-border-strong: #33455f;
            --sv-text: #f8fafc;
            --sv-text-soft: #d7e0ec;
            --sv-text-muted: #9fb0c5;
            --sv-purple: #a78bfa;
            --sv-purple-soft: #24183d;
            --sv-blue: #60a5fa;
            --sv-blue-soft: #142748;
            --sv-cyan: #22d3ee;
            --sv-emerald: #34d399;
            --sv-shadow: 0 14px 36px rgba(0, 0, 0, 0.24);
        }

        .sv-speaking-quiz > * {
            margin-top: 0 !important;
            margin-bottom: 0 !important;
        }

        .sv-speaking-quiz .sv-quiz-header,
        .sv-speaking-quiz .sv-task-summary,
        .sv-speaking-quiz .sv-recording-section,
        .sv-speaking-quiz .sv-result-section > div:first-child {
            border-color: var(--sv-border) !important;
            border-radius: 20px !important;
            background: var(--sv-card) !important;
            box-shadow: var(--sv-shadow) !important;
        }

        /*
        |--------------------------------------------------------------------------
        | Header
        |--------------------------------------------------------------------------
        */
        .sv-speaking-quiz .sv-quiz-header {
            padding: 18px 20px !important;
        }

        .sv-speaking-quiz .sv-quiz-header > .relative.grid {
            gap: 16px !important;
        }

        .sv-speaking-quiz .sv-quiz-header h1 {
            margin-top: 10px !important;
            color: var(--sv-text) !important;
            font-size: clamp(1.75rem, 3vw, 2.35rem) !important;
            line-height: 1.08 !important;
            letter-spacing: -0.035em !important;
        }

        .sv-speaking-quiz .sv-quiz-header h1 + p {
            margin-top: 8px !important;
            max-width: 680px !important;
            color: var(--sv-text-muted) !important;
            font-size: 14px !important;
            line-height: 1.55 !important;
        }

        .sv-speaking-quiz .sv-quiz-header > .relative.grid > div:first-child > div:first-child {
            min-height: 28px !important;
            padding: 6px 10px !important;
            border-radius: 999px !important;
            font-size: 11px !important;
        }

        .sv-speaking-quiz .sv-quiz-header > .relative.grid > div:last-child > div {
            padding: 13px 15px !important;
            border-color: var(--sv-border) !important;
            border-radius: 15px !important;
            background: var(--sv-card-soft) !important;
        }

        .sv-speaking-quiz .sv-quiz-header > .relative.grid > div:last-child > div > div {
            gap: 11px !important;
        }

        .sv-speaking-quiz .sv-quiz-header > .relative.grid > div:last-child > div > div > div:first-child {
            width: 40px !important;
            height: 40px !important;
            border-radius: 12px !important;
        }

        .sv-speaking-quiz .sv-quiz-header > .relative.grid > div:last-child p:first-child {
            font-size: 11px !important;
        }

        .sv-speaking-quiz .sv-quiz-header > .relative.grid > div:last-child p:last-child {
            margin-top: 2px !important;
            font-size: 18px !important;
            line-height: 1.2 !important;
        }

        /*
        |--------------------------------------------------------------------------
        | Instruction, Scenario, and Discussion Points
        |--------------------------------------------------------------------------
        */
        .sv-speaking-quiz .sv-task-summary {
            padding: 18px 20px !important;
        }

        .sv-speaking-quiz .sv-task-summary > .grid {
            align-items: stretch !important;
            gap: 14px !important;
        }

        .sv-speaking-quiz .sv-task-summary > .grid > article {
            display: flex;
            min-width: 0;
            flex-direction: column;
        }

        .sv-speaking-quiz .sv-task-summary > .grid > article > .flex {
            gap: 10px !important;
        }

        .sv-speaking-quiz .sv-task-summary > .grid > article > .flex > div:first-child {
            width: 36px !important;
            height: 36px !important;
            border-radius: 11px !important;
        }

        .sv-speaking-quiz .sv-task-summary h2 {
            color: var(--sv-text) !important;
            font-size: 16px !important;
            line-height: 1.3 !important;
        }

        .sv-speaking-quiz .sv-task-summary h2 + p {
            margin-top: 2px !important;
            color: var(--sv-text-muted) !important;
            font-size: 11px !important;
            line-height: 1.4 !important;
        }

        .sv-speaking-quiz .sv-task-summary > .grid > article > div:last-child {
            flex: 1;
            margin-top: 11px !important;
            padding: 13px 14px !important;
            border-color: var(--sv-border) !important;
            border-radius: 14px !important;
            background: var(--sv-card-soft) !important;
        }

        .sv-speaking-quiz .sv-task-summary > .grid > article > div:last-child p {
            color: var(--sv-text-soft) !important;
            font-size: 13px !important;
            line-height: 1.65 !important;
        }

        .sv-speaking-quiz .sv-task-summary > .mt-8 {
            margin-top: 16px !important;
            padding-top: 16px !important;
            border-color: var(--sv-border) !important;
        }

        .sv-speaking-quiz .sv-task-summary > .mt-8 h2 {
            font-size: 16px !important;
        }

        .sv-speaking-quiz .sv-task-summary > .mt-8 h2 + p {
            margin-top: 3px !important;
            font-size: 11px !important;
        }

        .sv-speaking-quiz .sv-task-summary > .mt-8 .mt-5.grid {
            margin-top: 10px !important;
            gap: 8px !important;
        }

        .sv-speaking-quiz .sv-task-summary > .mt-8 .mt-5.grid > div {
            min-height: 42px !important;
            align-items: center !important;
            gap: 9px !important;
            padding: 9px 11px !important;
            border-color: var(--sv-border) !important;
            border-radius: 12px !important;
            background: var(--sv-card-soft) !important;
        }

        .sv-speaking-quiz .sv-task-summary > .mt-8 .mt-5.grid > div > span {
            width: 26px !important;
            height: 26px !important;
            border-radius: 8px !important;
            font-size: 11px !important;
        }

        .sv-speaking-quiz .sv-task-summary > .mt-8 .mt-5.grid > div > p {
            padding-top: 0 !important;
            color: var(--sv-text-soft) !important;
            font-size: 12px !important;
            line-height: 1.45 !important;
        }

        /*
        |--------------------------------------------------------------------------
        | Recording Form
        |--------------------------------------------------------------------------
        */
        .sv-speaking-quiz .sv-recording-section {
            padding: 18px 20px !important;
        }

        .sv-speaking-quiz .sv-recording-section > .mb-7 {
            margin-bottom: 15px !important;
            gap: 10px !important;
        }

        .sv-speaking-quiz .sv-recording-section > .mb-7 h2 {
            color: var(--sv-text) !important;
            font-size: 18px !important;
            line-height: 1.3 !important;
        }

        .sv-speaking-quiz .sv-recording-section > .mb-7 h2 + p {
            margin-top: 4px !important;
            max-width: 720px !important;
            color: var(--sv-text-muted) !important;
            font-size: 12px !important;
            line-height: 1.5 !important;
        }

        .sv-speaking-quiz .sv-recording-section > .mb-7 > span {
            padding: 7px 11px !important;
            font-size: 11px !important;
        }

        .sv-speaking-quiz #speakingForm {
            display: grid;
            grid-template-columns: minmax(0, 0.9fr) minmax(0, 1.1fr);
            gap: 14px !important;
        }

        .sv-speaking-quiz #speakingForm > .sv-pair-settings,
        .sv-speaking-quiz #speakingForm > .sv-role-grid,
        .sv-speaking-quiz #speakingForm > .sv-individual-notice,
        .sv-speaking-quiz #speakingForm > .sv-submit-row {
            grid-column: 1 / -1;
        }

        .sv-speaking-quiz .sv-pair-settings {
            gap: 12px !important;
        }

        .sv-speaking-quiz .sv-pair-settings label {
            margin-bottom: 6px !important;
            color: var(--sv-text) !important;
            font-size: 12px !important;
        }

        .sv-speaking-quiz .sv-pair-settings select {
            min-height: 46px !important;
            padding: 10px 12px !important;
            border-color: var(--sv-border-strong) !important;
            border-radius: 12px !important;
            background: var(--sv-card-soft) !important;
            color: var(--sv-text) !important;
            font-size: 13px !important;
        }

        .sv-speaking-quiz .sv-role-grid {
            align-items: stretch !important;
            gap: 12px !important;
        }

        .sv-speaking-quiz .sv-role-grid > article {
            height: 100%;
            min-height: 150px;
            padding: 15px !important;
            border-radius: 15px !important;
        }

        .sv-speaking-quiz .sv-role-grid > article .flex.items-center {
            gap: 10px !important;
        }

        .sv-speaking-quiz .sv-role-grid > article .flex.items-center > span {
            width: 36px !important;
            height: 36px !important;
            border-radius: 10px !important;
            font-size: 13px !important;
        }

        .sv-speaking-quiz .sv-role-grid > article h3 {
            margin-top: 0 !important;
            font-size: 16px !important;
        }

        .sv-speaking-quiz .sv-role-grid > article .mt-5.h-px {
            margin-top: 12px !important;
        }

        .sv-speaking-quiz .sv-role-grid > article p.mt-5 {
            margin-top: 12px !important;
            color: var(--sv-text-soft) !important;
            font-size: 12px !important;
            line-height: 1.6 !important;
        }

        .sv-speaking-quiz .sv-individual-notice {
            padding: 14px !important;
            border-radius: 15px !important;
        }

        .sv-speaking-quiz .sv-recording-panel,
        .sv-speaking-quiz .sv-transcript-panel {
            min-width: 0;
            align-self: start;
        }

        .sv-speaking-quiz .sv-recording-panel {
            display: flex;
            flex-direction: column;
            gap: 14px;
            padding: 17px !important;
            border-color: var(--sv-border) !important;
            border-radius: 17px !important;
            background: var(--sv-card-soft) !important;
        }

        .sv-speaking-quiz .sv-recording-header {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 150px;
            align-items: start;
            gap: 14px;
        }

        .sv-speaking-quiz .sv-recording-copy {
            min-width: 0;
            padding-top: 2px;
        }

        .sv-speaking-quiz .sv-recording-copy h2,
        .sv-speaking-quiz .sv-transcript-title {
            margin: 0;
            color: var(--sv-text) !important;
            font-size: 18px !important;
            line-height: 1.3 !important;
            font-weight: 900;
            letter-spacing: -0.02em;
        }

        .sv-speaking-quiz .sv-recording-copy p,
        .sv-speaking-quiz .sv-transcript-description {
            margin: 5px 0 0 !important;
            color: var(--sv-text-muted) !important;
            font-size: 12px !important;
            line-height: 1.55 !important;
            font-weight: 600;
        }

        .sv-speaking-quiz .sv-recording-timer {
            min-width: 0;
            padding: 11px 13px;
            border: 1px solid var(--sv-border);
            border-radius: 14px;
            background: var(--sv-card);
            text-align: center;
        }

        .sv-speaking-quiz .sv-recording-timer-label {
            margin: 0;
            color: var(--sv-text-muted);
            font-size: 11px;
            line-height: 1.3;
            font-weight: 800;
        }

        .sv-speaking-quiz .sv-recording-timer #recordTimer {
            margin: 3px 0 0 !important;
            color: #a855f7 !important;
            font-size: 27px !important;
            line-height: 1 !important;
            font-weight: 900;
            font-variant-numeric: tabular-nums;
        }

        .sv-speaking-quiz .sv-recording-timer #durationHint {
            margin: 5px 0 0 !important;
            color: var(--sv-text-muted) !important;
            font-size: 10px !important;
            line-height: 1.35 !important;
            font-weight: 750;
        }

        .sv-speaking-quiz .sv-recording-actions {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 9px;
        }

        .sv-speaking-quiz .sv-record-button {
            width: 100%;
            min-height: 48px !important;
            padding: 11px 14px !important;
            border-radius: 13px !important;
            font-size: 13px !important;
        }

        .sv-speaking-quiz .sv-record-state-card {
            display: flex;
            align-items: flex-start;
            gap: 11px;
            padding: 12px 13px;
            border: 1px solid var(--sv-border);
            border-radius: 13px;
            background: var(--sv-card);
        }

        .sv-speaking-quiz .sv-record-state-indicator {
            display: flex;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 9px;
            background: var(--sv-card-muted);
        }

        .sv-speaking-quiz #recordStatusDot {
            width: 8px;
            height: 8px;
            margin: 0 !important;
            border-radius: 999px;
            background: #94a3b8;
        }

        .sv-speaking-quiz .sv-record-state-copy {
            min-width: 0;
        }

        .sv-speaking-quiz #recordStatus {
            display: block;
            margin: 0;
            color: var(--sv-text-soft);
            font-size: 12px;
            line-height: 1.4;
            font-weight: 850;
        }

        .sv-speaking-quiz .sv-record-state-help {
            display: block;
            margin-top: 2px;
            color: var(--sv-text-muted);
            font-size: 10px;
            line-height: 1.45;
            font-weight: 650;
        }

        .sv-speaking-quiz .sv-record-flow {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 7px;
        }

        .sv-speaking-quiz .sv-record-flow-item {
            display: flex;
            min-width: 0;
            align-items: center;
            gap: 7px;
            padding: 8px 9px;
            border: 1px solid var(--sv-border);
            border-radius: 11px;
            background: color-mix(in srgb, var(--sv-card) 70%, transparent);
            color: var(--sv-text-muted);
            font-size: 10px;
            line-height: 1.35;
            font-weight: 750;
        }

        .sv-speaking-quiz .sv-record-flow-number {
            display: inline-flex;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
            border-radius: 7px;
            background: rgba(139, 92, 246, 0.12);
            color: #a855f7;
            font-size: 9px;
            font-weight: 900;
        }

        .sv-speaking-quiz .sv-recording-panel #audioPreview {
            margin-top: 0 !important;
            min-height: 42px;
        }

        .sv-speaking-quiz .sv-transcript-panel {
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding: 17px;
            border: 1px solid var(--sv-border);
            border-radius: 17px;
            background: var(--sv-card-soft);
        }

        .sv-speaking-quiz .sv-transcript-header {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(184px, 210px);
            align-items: start;
            gap: 13px;
        }

        .sv-speaking-quiz .sv-transcript-heading {
            min-width: 0;
            padding-top: 2px;
        }

        .sv-speaking-quiz .sv-recognition-card {
            display: grid;
            grid-template-columns: 30px minmax(0, 1fr);
            gap: 9px;
            align-items: center;
            min-width: 0;
            padding: 9px 10px;
            border: 1px solid var(--sv-border);
            border-radius: 13px;
            background: var(--sv-card);
            transition:
                border-color 160ms ease,
                background-color 160ms ease;
        }

        .sv-speaking-quiz .sv-recognition-icon {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border-radius: 10px;
            background: var(--sv-card-muted);
            color: var(--sv-text-muted);
        }

        .sv-speaking-quiz .sv-recognition-icon svg {
            width: 15px;
            height: 15px;
        }

        .sv-speaking-quiz #recognitionStatusDot {
            position: absolute;
            right: -1px;
            bottom: -1px;
            width: 8px;
            height: 8px;
            border: 2px solid var(--sv-card);
            border-radius: 999px;
            background: #94a3b8;
        }

        .sv-speaking-quiz .sv-recognition-copy {
            min-width: 0;
        }

        .sv-speaking-quiz #recognitionStatusLabel {
            display: block;
            overflow: hidden;
            color: var(--sv-text-soft);
            font-size: 10px;
            line-height: 1.35;
            font-weight: 900;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .sv-speaking-quiz #recognitionStatusDetail {
            display: block;
            overflow: hidden;
            margin-top: 2px;
            color: var(--sv-text-muted);
            font-size: 9px;
            line-height: 1.35;
            font-weight: 650;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .sv-speaking-quiz .sv-recognition-card.is-ready {
            border-color: var(--sv-border);
        }

        .sv-speaking-quiz .sv-recognition-card.is-ready #recognitionStatusDot {
            background: #94a3b8;
        }

        .sv-speaking-quiz .sv-recognition-card.is-listening {
            border-color: rgba(34, 197, 94, 0.4);
            background: rgba(34, 197, 94, 0.07);
        }

        .sv-speaking-quiz .sv-recognition-card.is-listening .sv-recognition-icon,
        .sv-speaking-quiz .sv-recognition-card.is-listening #recognitionStatusLabel {
            color: #22c55e;
        }

        .sv-speaking-quiz .sv-recognition-card.is-listening #recognitionStatusDot {
            background: #22c55e;
            animation: sv-recognition-pulse 1.4s ease-in-out infinite;
        }

        .sv-speaking-quiz .sv-recognition-card.is-warning {
            border-color: rgba(245, 158, 11, 0.42);
            background: rgba(245, 158, 11, 0.08);
        }

        .sv-speaking-quiz .sv-recognition-card.is-warning .sv-recognition-icon,
        .sv-speaking-quiz .sv-recognition-card.is-warning #recognitionStatusLabel {
            color: #f59e0b;
        }

        .sv-speaking-quiz .sv-recognition-card.is-warning #recognitionStatusDot {
            background: #f59e0b;
        }

        .sv-speaking-quiz .sv-recognition-card.is-stopped {
            border-color: rgba(100, 116, 139, 0.38);
            background: color-mix(in srgb, var(--sv-card-muted) 70%, transparent);
        }

        .sv-speaking-quiz .sv-recognition-card.is-stopped #recognitionStatusDot {
            background: #64748b;
        }

        @keyframes sv-recognition-pulse {
            0%, 100% {
                box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.42);
            }

            50% {
                box-shadow: 0 0 0 5px rgba(34, 197, 94, 0);
            }
        }

        .sv-speaking-quiz .sv-transcript-panel textarea {
            min-height: 300px !important;
            width: 100%;
            padding: 14px !important;
            border-color: var(--sv-border-strong) !important;
            border-radius: 13px !important;
            background: var(--sv-card) !important;
            color: var(--sv-text) !important;
            font-size: 13px !important;
            line-height: 1.65 !important;
            resize: vertical;
        }

        .sv-speaking-quiz .sv-transcript-note {
            display: flex;
            align-items: flex-start;
            gap: 9px;
            margin: 0 !important;
            padding: 10px 11px !important;
            border: 1px solid rgba(245, 158, 11, 0.36) !important;
            border-radius: 11px !important;
            background: rgba(245, 158, 11, 0.07) !important;
            color: var(--sv-text-soft) !important;
            font-size: 10px !important;
            line-height: 1.5 !important;
            font-weight: 650;
        }

        .sv-speaking-quiz .sv-transcript-note svg {
            width: 15px;
            height: 15px;
            flex: 0 0 auto;
            margin-top: 1px;
            color: #f59e0b;
        }

        .sv-speaking-quiz .sv-submit-row {
            gap: 10px !important;
        }

        .sv-speaking-quiz .sv-speaking-submit,
        .sv-speaking-quiz .sv-submit-row > a {
            min-height: 50px !important;
            padding: 12px 18px !important;
            border-radius: 13px !important;
            font-size: 13px !important;
        }

        /*
        |--------------------------------------------------------------------------
        | Result
        |--------------------------------------------------------------------------
        */
        .sv-speaking-quiz .sv-result-section {
            gap: 14px !important;
        }

        .sv-speaking-quiz .sv-result-section > div:first-child {
            padding: 18px 20px !important;
        }

        .sv-speaking-quiz .sv-result-section .text-center > div:first-child {
            width: 54px !important;
            height: 54px !important;
            border-radius: 16px !important;
            font-size: 26px !important;
        }

        .sv-speaking-quiz .sv-result-section .text-center h2 {
            margin-top: 12px !important;
            font-size: 25px !important;
        }

        .sv-speaking-quiz .sv-result-section .text-center h2 + p {
            margin-top: 5px !important;
            font-size: 13px !important;
            line-height: 1.55 !important;
        }

        .sv-speaking-quiz #scoreGrid {
            margin-top: 16px !important;
            gap: 9px !important;
        }

        .sv-speaking-quiz #scoreGrid > article {
            padding: 12px !important;
            border-radius: 13px !important;
        }

        .sv-speaking-quiz #scoreGrid > article > p:first-child {
            font-size: 11px !important;
        }

        .sv-speaking-quiz #scoreGrid > article > p:last-child {
            margin-top: 5px !important;
            font-size: 23px !important;
        }

        .sv-speaking-quiz #totalScoreWrapper {
            margin-top: 14px !important;
            padding: 16px !important;
            border-radius: 15px !important;
        }

        .sv-speaking-quiz #totalScore {
            font-size: 48px !important;
        }

        .sv-speaking-quiz .sv-result-section .mt-8 {
            margin-top: 15px !important;
        }

        .sv-speaking-quiz .sv-result-section .mt-8.grid {
            gap: 10px !important;
        }

        .sv-speaking-quiz .sv-result-section .mt-8.grid > article,
        .sv-speaking-quiz .sv-result-section .mt-3.rounded-3xl {
            padding: 14px !important;
            border-radius: 14px !important;
        }

        .sv-speaking-quiz .sv-result-section h3 {
            font-size: 16px !important;
        }

        .sv-speaking-quiz .sv-result-section h3 + p,
        .sv-speaking-quiz .sv-result-section h3 + div p {
            font-size: 13px !important;
            line-height: 1.65 !important;
        }

        .sv-speaking-quiz .sv-result-section > .grid {
            gap: 10px !important;
        }

        .sv-speaking-quiz .sv-result-section > .grid a {
            min-height: 50px !important;
            padding: 12px 16px !important;
            border-radius: 13px !important;
            font-size: 13px !important;
        }

        /*
        |--------------------------------------------------------------------------
        | Responsive
        |--------------------------------------------------------------------------
        */
        @media (max-width: 1099px) {
            .sv-speaking-quiz .sv-recording-header,
            .sv-speaking-quiz .sv-transcript-header {
                grid-template-columns: minmax(0, 1fr);
            }

            .sv-speaking-quiz .sv-recording-timer,
            .sv-speaking-quiz .sv-recognition-card {
                width: 100%;
            }
        }

        @media (max-width: 1023px) {
            .sv-speaking-quiz #speakingForm {
                grid-template-columns: minmax(0, 1fr);
            }

            .sv-speaking-quiz #speakingForm > * {
                grid-column: 1 / -1;
            }

            .sv-speaking-quiz .sv-recording-panel,
            .sv-speaking-quiz .sv-transcript-panel {
                height: auto;
            }

            .sv-speaking-quiz .sv-transcript-panel textarea {
                min-height: 220px !important;
            }
        }

        @media (max-width: 767px) {
            .sv-speaking-quiz .sv-recording-actions,
            .sv-speaking-quiz .sv-record-flow {
                grid-template-columns: minmax(0, 1fr);
            }

            .sv-speaking-quiz .sv-transcript-header {
                gap: 10px;
            }

            .sv-speaking-quiz {
                gap: 12px;
            }

            .sv-speaking-quiz .sv-quiz-header,
            .sv-speaking-quiz .sv-task-summary,
            .sv-speaking-quiz .sv-recording-section,
            .sv-speaking-quiz .sv-result-section > div:first-child {
                padding: 15px !important;
                border-radius: 17px !important;
            }

            .sv-speaking-quiz .sv-quiz-header > .relative.grid {
                gap: 12px !important;
            }

            .sv-speaking-quiz .sv-task-summary > .grid {
                gap: 13px !important;
            }

            .sv-speaking-quiz .sv-task-summary > .grid > article > div:last-child {
                min-height: 0 !important;
            }

            .sv-speaking-quiz .sv-role-grid > article {
                min-height: 0;
            }

            .sv-speaking-quiz .sv-recording-panel > .flex {
                align-items: stretch !important;
            }

            .sv-speaking-quiz .sv-recording-panel .min-w-\[190px\] {
                width: 100%;
                min-width: 0 !important;
            }

            .sv-speaking-quiz .sv-submit-row,
            .sv-speaking-quiz .sv-submit-row > * {
                width: 100%;
            }

            .sv-speaking-quiz #scoreGrid {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            }
        }

        @media (max-width: 479px) {
            .sv-speaking-quiz .sv-quiz-header h1 {
                font-size: 1.65rem !important;
            }

            .sv-speaking-quiz .sv-pair-settings,
            .sv-speaking-quiz .sv-role-grid,
            .sv-speaking-quiz #scoreGrid {
                grid-template-columns: minmax(0, 1fr) !important;
            }

            .sv-speaking-quiz .sv-record-button {
                width: 100% !important;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Recording & Transcript — Refined Final Override
        |--------------------------------------------------------------------------
        */
        .sv-speaking-quiz #speakingForm {
            align-items: start !important;
            grid-template-columns: minmax(0, 0.88fr) minmax(0, 1.12fr) !important;
        }

        .sv-speaking-quiz .sv-recording-panel,
        .sv-speaking-quiz .sv-transcript-panel {
            height: auto !important;
            min-height: 0 !important;
            align-self: start !important;
        }

        .sv-speaking-quiz .sv-recording-panel {
            gap: 12px !important;
            padding: 15px !important;
        }

        .sv-speaking-quiz .sv-recording-header {
            grid-template-columns: minmax(0, 1fr) 126px !important;
            align-items: center !important;
            gap: 12px !important;
        }

        .sv-speaking-quiz .sv-recording-copy h2,
        .sv-speaking-quiz .sv-transcript-title {
            font-size: 17px !important;
        }

        .sv-speaking-quiz .sv-recording-copy p,
        .sv-speaking-quiz .sv-transcript-description {
            font-size: 11px !important;
            line-height: 1.5 !important;
        }

        .sv-speaking-quiz .sv-recording-timer {
            padding: 9px 10px !important;
            border-radius: 12px !important;
        }

        .sv-speaking-quiz .sv-recording-timer #recordTimer {
            font-size: 24px !important;
        }

        .sv-speaking-quiz .sv-recording-actions {
            gap: 8px !important;
        }

        .sv-speaking-quiz .sv-record-button {
            min-height: 44px !important;
            padding: 10px 12px !important;
            font-size: 12px !important;
        }

        .sv-speaking-quiz .sv-record-state-card {
            padding: 10px 11px !important;
            border-radius: 12px !important;
        }

        .sv-speaking-quiz .sv-record-guidance {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            padding: 9px 10px;
            border: 1px solid var(--sv-border);
            border-radius: 11px;
            background: color-mix(in srgb, var(--sv-card) 72%, transparent);
            color: var(--sv-text-muted);
            font-size: 10px;
            line-height: 1.5;
            font-weight: 650;
        }

        .sv-speaking-quiz .sv-record-guidance svg {
            width: 14px;
            height: 14px;
            flex: 0 0 auto;
            margin-top: 1px;
            color: var(--sv-purple);
        }

        .sv-speaking-quiz .sv-transcript-panel {
            gap: 10px !important;
            padding: 15px !important;
        }

        .sv-speaking-quiz .sv-transcript-header {
            display: block !important;
        }

        .sv-speaking-quiz .sv-transcript-eyebrow {
            display: inline-flex;
            align-items: center;
            min-height: 22px;
            margin-bottom: 5px;
            padding: 4px 7px;
            border-radius: 999px;
            background: rgba(34, 211, 238, 0.1);
            color: var(--sv-cyan);
            font-size: 8px;
            line-height: 1;
            font-weight: 900;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        .sv-speaking-quiz .sv-live-transcript-status {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 10px 11px;
            border: 1px solid var(--sv-border);
            border-radius: 12px;
            background: var(--sv-card);
            transition:
                border-color 160ms ease,
                background-color 160ms ease;
        }

        .sv-speaking-quiz .sv-live-transcript-main {
            display: flex;
            min-width: 0;
            align-items: center;
            gap: 9px;
        }

        .sv-speaking-quiz .sv-live-transcript-icon {
            display: inline-flex;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border-radius: 9px;
            background: var(--sv-card-muted);
            color: var(--sv-text-muted);
        }

        .sv-speaking-quiz .sv-live-transcript-icon svg {
            width: 15px;
            height: 15px;
        }

        .sv-speaking-quiz .sv-live-transcript-copy {
            min-width: 0;
        }

        .sv-speaking-quiz #recognitionStatusLabel {
            display: block;
            color: var(--sv-text-soft);
            font-size: 11px;
            line-height: 1.35;
            font-weight: 900;
        }

        .sv-speaking-quiz #recognitionStatusDetail {
            display: block;
            margin-top: 2px;
            color: var(--sv-text-muted);
            font-size: 9px;
            line-height: 1.4;
            font-weight: 650;
        }

        .sv-speaking-quiz .sv-live-transcript-chip {
            display: inline-flex;
            flex: 0 0 auto;
            align-items: center;
            gap: 6px;
            min-height: 28px;
            padding: 6px 8px;
            border-radius: 999px;
            background: var(--sv-card-muted);
            color: var(--sv-text-muted);
            font-size: 9px;
            line-height: 1;
            font-weight: 900;
        }

        .sv-speaking-quiz #recognitionStatusDot {
            position: static !important;
            width: 7px !important;
            height: 7px !important;
            border: 0 !important;
            border-radius: 999px;
            background: #94a3b8;
        }

        .sv-speaking-quiz .sv-live-transcript-status.is-listening {
            border-color: rgba(34, 197, 94, 0.38);
            background: rgba(34, 197, 94, 0.06);
        }

        .sv-speaking-quiz .sv-live-transcript-status.is-listening .sv-live-transcript-icon,
        .sv-speaking-quiz .sv-live-transcript-status.is-listening #recognitionStatusLabel {
            color: #22c55e;
        }

        .sv-speaking-quiz .sv-live-transcript-status.is-listening .sv-live-transcript-chip {
            background: rgba(34, 197, 94, 0.12);
            color: #22c55e;
        }

        .sv-speaking-quiz .sv-live-transcript-status.is-listening #recognitionStatusDot {
            background: #22c55e;
            animation: sv-recognition-pulse 1.4s ease-in-out infinite;
        }

        .sv-speaking-quiz .sv-live-transcript-status.is-warning {
            border-color: rgba(245, 158, 11, 0.4);
            background: rgba(245, 158, 11, 0.07);
        }

        .sv-speaking-quiz .sv-live-transcript-status.is-warning .sv-live-transcript-icon,
        .sv-speaking-quiz .sv-live-transcript-status.is-warning #recognitionStatusLabel {
            color: #f59e0b;
        }

        .sv-speaking-quiz .sv-live-transcript-status.is-warning .sv-live-transcript-chip {
            background: rgba(245, 158, 11, 0.12);
            color: #f59e0b;
        }

        .sv-speaking-quiz .sv-live-transcript-status.is-warning #recognitionStatusDot {
            background: #f59e0b;
        }

        .sv-speaking-quiz .sv-live-transcript-status.is-stopped {
            background: color-mix(in srgb, var(--sv-card-muted) 68%, transparent);
        }

        .sv-speaking-quiz .sv-live-transcript-status.is-stopped #recognitionStatusDot {
            background: #64748b;
        }

        .sv-speaking-quiz .sv-transcript-panel textarea {
            min-height: 230px !important;
            padding: 13px !important;
            font-size: 12px !important;
            line-height: 1.6 !important;
        }

        .sv-speaking-quiz .sv-transcript-note {
            padding: 9px 10px !important;
            font-size: 9px !important;
        }

        @media (max-width: 1023px) {
            .sv-speaking-quiz #speakingForm {
                grid-template-columns: minmax(0, 1fr) !important;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Discussion Point Number — High Contrast
        |--------------------------------------------------------------------------
        */
        .sv-speaking-quiz .sv-discussion-number {
            border: 1px solid #a7f3d0 !important;
            background: #d1fae5 !important;
            color: #047857 !important;
            font-variant-numeric: tabular-nums;
            text-shadow: none !important;
        }

        html.dark .sv-speaking-quiz .sv-discussion-number,
        body.dark .sv-speaking-quiz .sv-discussion-number,
        .dark .sv-speaking-quiz .sv-discussion-number,
        html[data-theme="dark"] .sv-speaking-quiz .sv-discussion-number,
        body[data-theme="dark"] .sv-speaking-quiz .sv-discussion-number {
            border-color: #10b981 !important;
            background: #064e3b !important;
            color: #ecfdf5 !important;
            box-shadow: inset 0 0 0 1px rgba(110, 231, 183, 0.14) !important;
        }

        @media (max-width: 639px) {
            .sv-speaking-quiz .sv-recording-header {
                grid-template-columns: minmax(0, 1fr) !important;
            }

            .sv-speaking-quiz .sv-recording-timer {
                width: 100%;
            }

            .sv-speaking-quiz .sv-live-transcript-status {
                align-items: flex-start;
                flex-direction: column;
            }

            .sv-speaking-quiz .sv-live-transcript-chip {
                align-self: flex-start;
            }
        }

    </style>

    <div
        class="sv-speaking-quiz
        space-y-6 sm:space-y-8">

        {{-- ================================================================
            HEADER
        ================================================================= --}}
        <section
            class="sv-quiz-header relative overflow-hidden
            rounded-[28px]
            border border-slate-200
            bg-white p-5 shadow-sm
            dark:border-white/10
            dark:bg-slate-900
            sm:p-7 lg:p-9">

            <div
                class="pointer-events-none
                absolute -right-24 -top-24
                h-72 w-72 rounded-full
                bg-purple-500/10 blur-3xl">
            </div>

            <div
                class="pointer-events-none
                absolute -bottom-28 left-20
                h-64 w-64 rounded-full
                bg-blue-500/10 blur-3xl">
            </div>

            <div
                class="relative grid min-w-0
                grid-cols-1 gap-8
                lg:grid-cols-12
                lg:items-center">

                <div class="min-w-0 lg:col-span-8">

                    <div
                        class="inline-flex max-w-full
                        items-center gap-2
                        rounded-full
                        border border-purple-200
                        bg-purple-50 px-4 py-2
                        text-sm font-bold
                        text-purple-700
                        dark:border-purple-500/20
                        dark:bg-purple-500/10
                        dark:text-purple-300">

                        <svg
                            class="h-4 w-4 shrink-0"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            aria-hidden="true">

                            <rect
                                x="8"
                                y="2"
                                width="8"
                                height="13"
                                rx="4">
                            </rect>

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M5 10a7 7 0 0 0 14 0M12 17v5M8 22h8">
                            </path>
                        </svg>

                        <span class="truncate">
                            @if ($isAssessment)
                                {{ $assessmentLabel }}
                                Individual Speaking Assessment
                            @elseif ($isPairWork)
                                Pair Speaking Task
                            @else
                                Individual Speaking Task
                            @endif
                        </span>
                    </div>

                    <h1
                        class="mt-5 break-words
                        text-3xl font-black
                        leading-tight tracking-tight
                        text-slate-950
                        dark:text-white
                        sm:text-4xl lg:text-5xl">

                        {{ $material->title }}
                    </h1>

                    <p
                        class="mt-4 max-w-3xl
                        text-base leading-7
                        text-slate-600
                        dark:text-slate-400
                        sm:text-lg sm:leading-8">

                        @if ($isAssessment)
                            Record one individual presentation about
                            a fable or short story. Speak clearly and
                            include all required story elements.
                        @elseif ($isPairWork)
                            Record one complete conversation with your
                            partner and submit one shared recording.
                        @else
                            Record your speaking response and submit it
                            for evaluation.
                        @endif
                    </p>
                </div>

                {{-- DURATION CARD --}}
                <div class="lg:col-span-4">

                    <div
                        class="rounded-3xl
                        border border-slate-200
                        bg-slate-50 p-6
                        dark:border-white/10
                        dark:bg-white/5">

                        <div class="flex items-center gap-4">

                            <div
                                class="flex h-12 w-12
                                shrink-0 items-center
                                justify-center rounded-2xl
                                bg-blue-100 text-blue-700
                                dark:bg-blue-500/10
                                dark:text-blue-300">

                                <svg
                                    class="h-6 w-6"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    aria-hidden="true">

                                    <circle
                                        cx="12"
                                        cy="12"
                                        r="9">
                                    </circle>

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M12 7v5l3 2">
                                    </path>
                                </svg>
                            </div>

                            <div>
                                <p
                                    class="text-sm font-semibold
                                    text-slate-500
                                    dark:text-slate-400">

                                    Required Duration
                                </p>

                                <p
                                    class="mt-1 text-2xl
                                    font-black text-blue-600
                                    dark:text-blue-400">

                                    @if (
                                        $minimumMinutes &&
                                        $maximumMinutes
                                    )
                                        {{ $minimumMinutes }}
                                        –
                                        {{ $maximumMinutes }}
                                        minutes
                                    @elseif ($minimumMinutes)
                                        Minimum
                                        {{ $minimumMinutes }}
                                        minutes
                                    @elseif ($maximumMinutes)
                                        Maximum
                                        {{ $maximumMinutes }}
                                        minutes
                                    @else
                                        Flexible
                                    @endif
                                </p>
                            </div>
                        </div>

                        @if ($isAssessment)
                            <div
                                class="mt-5 rounded-2xl
                                border border-amber-200
                                bg-amber-50 p-4
                                text-sm font-semibold
                                leading-6 text-amber-800
                                dark:border-amber-500/20
                                dark:bg-amber-500/10
                                dark:text-amber-200">

                                The recording cannot be submitted
                                if it is shorter than 2 minutes or
                                longer than 3 minutes.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        {{-- ================================================================
            TASK SUMMARY
        ================================================================= --}}
        <section
            class="sv-task-summary rounded-[28px]
            border border-slate-200
            bg-white p-5 shadow-sm
            dark:border-white/10
            dark:bg-slate-900
            sm:p-7 lg:p-8">

            <div
                class="grid grid-cols-1
                gap-8 lg:grid-cols-2">

                {{-- INSTRUCTION --}}
                <article class="min-w-0">

                    <div class="flex items-center gap-3">

                        <div
                            class="flex h-11 w-11
                            shrink-0 items-center
                            justify-center rounded-xl
                            bg-purple-100
                            text-purple-700
                            dark:bg-purple-500/10
                            dark:text-purple-300">

                            <svg
                                class="h-5 w-5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                aria-hidden="true">

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
                        </div>

                        <div>
                            <h2
                                class="text-xl font-black
                                text-slate-950
                                dark:text-white">

                                Instruction
                            </h2>

                            <p
                                class="mt-1 text-sm
                                text-slate-500
                                dark:text-slate-400">

                                Follow the task instruction carefully.
                            </p>
                        </div>
                    </div>

                    <div
                        class="mt-5 rounded-2xl
                        border border-purple-200
                        bg-purple-50 p-5
                        dark:border-purple-500/20
                        dark:bg-purple-500/10">

                        <p
                            class="break-words leading-8
                            text-slate-700
                            dark:text-slate-200">{!! nl2br(e($instructionText)) !!}</p>
                    </div>
                </article>

                {{-- SCENARIO / CONTEXT --}}
                <article class="min-w-0">

                    <div class="flex items-center gap-3">

                        <div
                            class="flex h-11 w-11
                            shrink-0 items-center
                            justify-center rounded-xl
                            bg-blue-100
                            text-blue-700
                            dark:bg-blue-500/10
                            dark:text-blue-300">

                            @if ($isAssessment)
                                <svg
                                    class="h-5 w-5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    aria-hidden="true">

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M12 3v12M8 7l4-4 4 4M5 15h14v6H5z">
                                    </path>
                                </svg>
                            @else
                                <svg
                                    class="h-5 w-5"
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
                            @endif
                        </div>

                        <div>
                            <h2
                                class="text-xl font-black
                                text-slate-950
                                dark:text-white">

                                @if ($isAssessment)
                                    Presentation Context
                                @else
                                    Conversation Scenario
                                @endif
                            </h2>

                            <p
                                class="mt-1 text-sm
                                text-slate-500
                                dark:text-slate-400">

                                @if ($isAssessment)
                                    Use this context for your presentation.
                                @else
                                    Use this situation for your conversation.
                                @endif
                            </p>
                        </div>
                    </div>

                    <div
                        class="mt-5 rounded-2xl
                        border border-slate-200
                        bg-slate-50 p-5
                        dark:border-white/10
                        dark:bg-white/5">

                        <p
                            class="break-words leading-8
                            text-slate-700
                            dark:text-slate-300">{!! nl2br(e($scenarioText)) !!}</p>
                    </div>
                </article>
            </div>

            {{-- PRESENTATION / DISCUSSION POINTS --}}
            @if (count($discussionPoints))
                <div
                    class="mt-8 border-t
                    border-slate-200 pt-8
                    dark:border-white/10">

                    <div>
                        <h2
                            class="text-xl font-black
                            text-slate-950
                            dark:text-white">

                            @if ($isAssessment)
                                Your Presentation Should Include
                            @else
                                Points to Discuss
                            @endif
                        </h2>

                        <p
                            class="mt-2 text-sm leading-6
                            text-slate-500
                            dark:text-slate-400">

                            @if ($isAssessment)
                                Cover all of these elements in your
                                individual presentation.
                            @else
                                Cover all of these points during
                                the conversation.
                            @endif
                        </p>
                    </div>

                    <div
                        class="mt-5 grid
                        grid-cols-1 gap-3
                        md:grid-cols-2">

                        @foreach (
                            $discussionPoints
                            as $index => $point
                        )
                            <div
                                class="flex min-w-0
                                items-start gap-3
                                rounded-2xl
                                border border-slate-200
                                bg-slate-50 p-4
                                dark:border-white/10
                                dark:bg-white/5">

                                <span
                                    class="sv-discussion-number
                                    flex h-9 w-9
                                    shrink-0 items-center
                                    justify-center rounded-xl
                                    bg-emerald-100
                                    text-sm font-black
                                    text-emerald-700
                                    dark:bg-emerald-900
                                    dark:text-emerald-50">

                                    {{ $index + 1 }}
                                </span>

                                <p
                                    class="min-w-0 break-words
                                    pt-1 text-sm leading-6
                                    text-slate-700
                                    dark:text-slate-300
                                    sm:text-base">

                                    {{ $point }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- SUPPORTING EXAMPLE --}}
            @if ($isAssessment && $material->passage)
                <div
                    class="mt-8 border-t
                    border-slate-200 pt-8
                    dark:border-white/10">

                    <div
                        class="rounded-3xl
                        border border-amber-200
                        bg-amber-50 p-5
                        dark:border-amber-500/20
                        dark:bg-amber-500/10
                        sm:p-6">

                        <div class="flex items-start gap-4">

                            <div
                                class="flex h-11 w-11
                                shrink-0 items-center
                                justify-center rounded-xl
                                bg-amber-100
                                text-amber-700
                                dark:bg-amber-500/15
                                dark:text-amber-300">

                                <svg
                                    class="h-5 w-5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    aria-hidden="true">

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20M4 4.5A2.5 2.5 0 0 1 6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15Z">
                                    </path>
                                </svg>
                            </div>

                            <div class="min-w-0">

                                <h2
                                    class="text-xl font-black
                                    text-amber-900
                                    dark:text-amber-200">

                                    Example Presentation
                                </h2>

                                <p
                                    class="mt-2 text-sm leading-6
                                    text-amber-800
                                    dark:text-amber-200">

                                    Use this example only to understand
                                    the presentation structure. Do not
                                    read or copy it as your own answer.
                                </p>
                            </div>
                        </div>

                        <div
                            class="mt-5 rounded-2xl
                            border border-amber-200
                            bg-white/70 p-5
                            dark:border-amber-500/20
                            dark:bg-slate-900/40">

                            <p
                                class="whitespace-pre-line
                                break-words leading-8
                                text-slate-700
                                dark:text-slate-200">

                                {{ $material->passage }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </section>

        {{-- ================================================================
            RECORDING FORM
        ================================================================= --}}
        <section
            id="speakingSection"
            class="sv-recording-section rounded-[28px]
            border border-slate-200
            bg-white p-5 shadow-sm
            dark:border-white/10
            dark:bg-slate-900
            sm:p-7 lg:p-8">

            <div
                class="mb-7 flex flex-col gap-4
                sm:flex-row sm:items-start
                sm:justify-between">

                <div>

                    <h2
                        class="text-2xl font-black
                        text-slate-950
                        dark:text-white">

                        @if ($isAssessment)
                            Individual Presentation
                        @elseif ($isPairWork)
                            Participants and Roles
                        @else
                            Speaking Recording
                        @endif
                    </h2>

                    <p
                        class="mt-2 max-w-2xl
                        text-sm leading-6
                        text-slate-500
                        dark:text-slate-400
                        sm:text-base">

                        @if ($isAssessment)
                            Complete the recording by yourself. No partner
                            or Student A/B role is required.
                        @elseif ($isPairWork)
                            Select your partner and choose your role before
                            starting the recording.
                        @else
                            Prepare your response before starting
                            the recording.
                        @endif
                    </p>
                </div>

                <span
                    class="inline-flex w-fit
                    items-center rounded-full
                    border px-4 py-2
                    text-sm font-bold
                    {{ $isAssessment || !$isPairWork
                        ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300'
                        : 'border-purple-200 bg-purple-50 text-purple-700 dark:border-purple-500/20 dark:bg-purple-500/10 dark:text-purple-300' }}">

                    {{ $isAssessment || !$isPairWork
                        ? '1 Student'
                        : '2 Students' }}
                </span>
            </div>

            <form
                id="speakingForm"
                action="{{ $submitRoute }}"
                method="POST"
                enctype="multipart/form-data"
                class="space-y-8">

                @csrf

                {{-- Saran perbaikan Modul 5 (Speaking) — Bantuan Sebelum Merekam --}}
                <div>
                    <button
                        type="button"
                        id="preTaskHelpBtn"
                        class="inline-flex items-center justify-center
                        rounded-2xl border border-slate-200 dark:border-white/10
                        bg-white dark:bg-white/5
                        px-4 py-2 text-sm font-bold
                        text-purple-600 dark:text-purple-400
                        hover:bg-purple-50 dark:hover:bg-purple-500/10
                        transition">
                        💡 Bantuan sebelum merekam
                    </button>
                </div>

                <div
                    id="preTaskHelpPanel"
                    hidden
                    class="rounded-3xl border border-purple-200 dark:border-purple-500/20
                    bg-purple-50 dark:bg-purple-500/10 p-5 sm:p-6">
                    <p class="mb-1 text-xs font-black text-purple-700 dark:text-purple-400">Struktur yang disarankan</p>
                    <p id="preTaskStructureTip" class="text-sm leading-7 text-slate-700 dark:text-slate-300"></p>

                    <p class="mb-1 mt-3 text-xs font-black text-purple-700 dark:text-purple-400">Kosakata yang mungkin berguna</p>
                    <ul id="preTaskVocabList" class="list-disc pl-5 text-sm leading-7 text-slate-700 dark:text-slate-300"></ul>

                    <p class="mb-1 mt-3 text-xs font-black text-purple-700 dark:text-purple-400">Pengingat tata bahasa</p>
                    <p id="preTaskGrammarTip" class="text-sm leading-7 text-slate-700 dark:text-slate-300"></p>
                </div>

                {{-- ========================================================
                    PAIR SETTINGS — UNIT 1–4 ONLY
                ======================================================== --}}
                @if ($isPairWork)
                    <div
                        class="sv-pair-settings grid grid-cols-1
                        gap-6 lg:grid-cols-2">

                        {{-- PARTNER --}}
                        <div>
                            <label
                                for="partner_user_id"
                                class="mb-2 block
                                font-bold text-slate-900
                                dark:text-white">

                                Select Partner
                            </label>

                            <select
                                id="partner_user_id"
                                name="partner_user_id"
                                required
                                class="w-full rounded-2xl
                                border border-slate-300
                                bg-white px-4 py-4
                                text-slate-900
                                focus:border-purple-500
                                focus:ring-purple-500
                                dark:border-white/10
                                dark:bg-slate-950
                                dark:text-white">

                                <option value="">
                                    Choose your partner
                                </option>

                                @foreach ($partners as $partner)
                                    <option
                                        value="{{ $partner->id }}"
                                        @selected(
                                            old('partner_user_id') ==
                                            $partner->id
                                        )>

                                        {{ $partner->name }}
                                    </option>
                                @endforeach
                            </select>

                            @if ($partners->isEmpty())
                                <p
                                    class="mt-2 text-sm
                                    font-semibold text-red-500">

                                    No other student account is currently
                                    available as a partner.
                                </p>
                            @endif

                            @error('partner_user_id')
                                <p
                                    class="mt-2 text-sm
                                    font-semibold text-red-500">

                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- ROLE --}}
                        <div>
                            <label
                                for="submitter_role"
                                class="mb-2 block
                                font-bold text-slate-900
                                dark:text-white">

                                Your Role
                            </label>

                            <select
                                id="submitter_role"
                                name="submitter_role"
                                required
                                class="w-full rounded-2xl
                                border border-slate-300
                                bg-white px-4 py-4
                                text-slate-900
                                focus:border-purple-500
                                focus:ring-purple-500
                                dark:border-white/10
                                dark:bg-slate-950
                                dark:text-white">

                                <option value="">
                                    Choose your role
                                </option>

                                <option
                                    value="A"
                                    @selected(
                                        old('submitter_role') === 'A'
                                    )>

                                    Student A
                                </option>

                                <option
                                    value="B"
                                    @selected(
                                        old('submitter_role') === 'B'
                                    )>

                                    Student B
                                </option>
                            </select>

                            @error('submitter_role')
                                <p
                                    class="mt-2 text-sm
                                    font-semibold text-red-500">

                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>

                    {{-- ROLE DETAILS --}}
                    <div
                        class="sv-role-grid grid grid-cols-1
                        gap-5 lg:grid-cols-2">

                        {{-- STUDENT A --}}
                        <article
                            id="roleACard"
                            class="sv-speaking-role-card
                            sv-speaking-role-a
                            rounded-3xl p-6">

                            <div
                                class="pointer-events-none
                                absolute -right-10 -top-10
                                h-32 w-32 rounded-full
                                bg-purple-500/10 blur-2xl">
                            </div>

                            <div class="relative">

                                <div class="flex items-center gap-3">

                                    <span
                                        class="flex h-11 w-11
                                        items-center justify-center
                                        rounded-xl bg-purple-600
                                        font-black text-white">

                                        A
                                    </span>

                                    <div>
                                        <p
                                            class="text-xs font-black
                                            uppercase tracking-wider
                                            text-purple-600
                                            dark:text-purple-300">

                                            Role
                                        </p>

                                        <h3
                                            class="mt-1 text-xl
                                            font-black text-slate-950
                                            dark:text-white">

                                            Student A
                                        </h3>
                                    </div>
                                </div>

                                <div
                                    class="mt-5 h-px w-full
                                    bg-purple-200/70
                                    dark:bg-purple-400/15">
                                </div>

                                <p
                                    class="mt-5 whitespace-pre-line
                                    break-words leading-8
                                    text-slate-700
                                    dark:text-slate-300">

                                    {{ $material->role_a }}
                                </p>
                            </div>
                        </article>

                        {{-- STUDENT B --}}
                        <article
                            id="roleBCard"
                            class="sv-speaking-role-card
                            sv-speaking-role-b
                            rounded-3xl p-6">

                            <div
                                class="pointer-events-none
                                absolute -right-10 -top-10
                                h-32 w-32 rounded-full
                                bg-blue-500/10 blur-2xl">
                            </div>

                            <div class="relative">

                                <div class="flex items-center gap-3">

                                    <span
                                        class="flex h-11 w-11
                                        items-center justify-center
                                        rounded-xl bg-blue-600
                                        font-black text-white">

                                        B
                                    </span>

                                    <div>
                                        <p
                                            class="text-xs font-black
                                            uppercase tracking-wider
                                            text-blue-600
                                            dark:text-blue-300">

                                            Role
                                        </p>

                                        <h3
                                            class="mt-1 text-xl
                                            font-black text-slate-950
                                            dark:text-white">

                                            Student B
                                        </h3>
                                    </div>
                                </div>

                                <div
                                    class="mt-5 h-px w-full
                                    bg-blue-200/70
                                    dark:bg-blue-400/15">
                                </div>

                                <p
                                    class="mt-5 whitespace-pre-line
                                    break-words leading-8
                                    text-slate-700
                                    dark:text-slate-300">

                                    {{ $material->role_b }}
                                </p>
                            </div>
                        </article>
                    </div>
                @endif

                {{-- ========================================================
                    INDIVIDUAL ASSESSMENT NOTICE
                ======================================================== --}}
                @if ($isAssessment)
                    <div
                        class="sv-individual-notice rounded-3xl
                        border border-emerald-200
                        bg-emerald-50 p-5
                        dark:border-emerald-500/20
                        dark:bg-emerald-500/10
                        sm:p-6">

                        <div class="flex items-start gap-4">

                            <div
                                class="flex h-11 w-11
                                shrink-0 items-center
                                justify-center rounded-xl
                                bg-emerald-100
                                text-emerald-700
                                dark:bg-emerald-500/15
                                dark:text-emerald-300">

                                <svg
                                    class="h-5 w-5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    aria-hidden="true">

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M20 21a8 8 0 0 0-16 0M12 13a5 5 0 1 0 0-10 5 5 0 0 0 0 10Z">
                                    </path>
                                </svg>
                            </div>

                            <div class="min-w-0">

                                <h3
                                    class="font-black
                                    text-emerald-900
                                    dark:text-emerald-200">

                                    Individual assessment
                                </h3>

                                <p
                                    class="mt-2 text-sm leading-6
                                    text-emerald-800
                                    dark:text-emerald-200">

                                    Only your voice should be included in
                                    this recording. Present your chosen
                                    story independently for 2–3 minutes.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- ========================================================
                    RECORDING PANEL
                ======================================================== --}}
                <div
                    class="sv-recording-panel rounded-3xl
                    border border-slate-200
                    bg-slate-50/60
                    dark:border-white/10
                    dark:bg-white/[0.03]">

                    <div class="sv-recording-header">

                        <div class="sv-recording-copy">
                            <h2>
                                @if ($isAssessment)
                                    Record Presentation
                                @elseif ($isPairWork)
                                    Record Conversation
                                @else
                                    Record Speaking Response
                                @endif
                            </h2>

                            <p>
                                @if ($isAssessment)
                                    Keep the microphone close and speak clearly
                                    throughout the presentation.
                                @elseif ($isPairWork)
                                    Place the device between both students so
                                    each voice can be heard clearly.
                                @else
                                    Keep the microphone close and speak clearly.
                                @endif
                            </p>
                        </div>

                        <div class="sv-recording-timer">
                            <p class="sv-recording-timer-label">
                                Recording Time
                            </p>

                            <p id="recordTimer">00:00</p>

                            <p id="durationHint">
                                @if (
                                    $minimumMinutes &&
                                    $maximumMinutes
                                )
                                    Target {{ $minimumMinutes }}–{{ $maximumMinutes }} min
                                @else
                                    Record clearly
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="sv-recording-actions">
                        <button
                            id="startBtn"
                            type="button"
                            class="sv-record-button
                            sv-record-button-start">

                            <svg
                                class="h-5 w-5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                aria-hidden="true">

                                <rect
                                    x="8"
                                    y="2"
                                    width="8"
                                    height="13"
                                    rx="4">
                                </rect>

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M5 10a7 7 0 0 0 14 0M12 17v5M8 22h8">
                                </path>
                            </svg>

                            Start Recording
                        </button>

                        <button
                            id="stopBtn"
                            type="button"
                            disabled
                            class="sv-record-button
                            sv-record-button-stop">

                            <svg
                                class="h-5 w-5"
                                viewBox="0 0 24 24"
                                fill="currentColor"
                                aria-hidden="true">

                                <rect
                                    x="6"
                                    y="6"
                                    width="12"
                                    height="12"
                                    rx="2">
                                </rect>
                            </svg>

                            Stop Recording
                        </button>
                    </div>

                    <div class="sv-record-state-card">
                        <span class="sv-record-state-indicator">
                            <span id="recordStatusDot"></span>
                        </span>

                        <span class="sv-record-state-copy">
                            <strong id="recordStatus">
                                Ready to record
                            </strong>

                            <small class="sv-record-state-help">
                                Start recording when both speakers are ready.
                            </small>
                        </span>
                    </div>

                    <div class="sv-record-guidance">
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            aria-hidden="true">
                            <circle cx="12" cy="12" r="9"></circle>
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 8h.01M11 12h1v4h1">
                            </path>
                        </svg>

                        <span>
                            Start once both speakers are ready, speak naturally,
                            then stop and review the transcript.
                        </span>
                    </div>

                    <input
                        id="audioInput"
                        type="file"
                        name="audio_file"
                        hidden>

                    <input
                        id="audioDurationInput"
                        type="hidden"
                        name="audio_duration"
                        value="0">

                    <audio
                        id="audioPreview"
                        controls
                        class="hidden w-full">
                    </audio>
                </div>

                {{-- ========================================================
                    TRANSCRIPT
                ======================================================== --}}
                <div class="sv-transcript-panel">

                    <div class="sv-transcript-header">
                        <div class="sv-transcript-heading">
                            <span class="sv-transcript-eyebrow">
                                Live transcript
                            </span>

                            <label
                                for="transcript"
                                class="sv-transcript-title">

                                @if ($isAssessment)
                                    Presentation Transcript
                                @elseif ($isPairWork)
                                    Conversation Transcript
                                @else
                                    Speaking Transcript
                                @endif
                            </label>

                            <p class="sv-transcript-description">
                                Your speech is converted into editable text while
                                recording. Review it before submitting.
                            </p>
                        </div>
                    </div>

                    <div
                        id="recognitionStatus"
                        class="sv-live-transcript-status is-ready"
                        role="status"
                        aria-live="polite">

                        <div class="sv-live-transcript-main">
                            <span class="sv-live-transcript-icon">
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    aria-hidden="true">

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M4 12h2l2-5 4 10 2-5h6">
                                    </path>
                                </svg>
                            </span>

                            <span class="sv-live-transcript-copy">
                                <strong id="recognitionStatusLabel">
                                    Ready to capture speech
                                </strong>

                                <small id="recognitionStatusDetail">
                                    Starts automatically when recording begins.
                                </small>
                            </span>
                        </div>

                        <span class="sv-live-transcript-chip">
                            <span id="recognitionStatusDot"></span>
                            <span id="recognitionStateText">Ready</span>
                        </span>
                    </div>

                    <textarea
                        id="transcript"
                        name="transcript"
                        rows="12"
                        required
                        placeholder="{{ $isAssessment
                            ? 'Your individual presentation transcript will appear here...'
                            : (
                                $isPairWork
                                    ? 'Your conversation transcript will appear here...'
                                    : 'Your speaking transcript will appear here...'
                            ) }}"
                        class="w-full rounded-3xl
                        border border-slate-300
                        bg-white p-5
                        leading-8 text-slate-900
                        placeholder:text-slate-400
                        focus:border-purple-500
                        focus:ring-purple-500
                        dark:border-white/10
                        dark:bg-slate-950
                        dark:text-white"></textarea>

                    <p class="sv-transcript-note">
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            aria-hidden="true">

                            <circle cx="12" cy="12" r="9"></circle>

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 8h.01M11 12h1v4h1">
                            </path>
                        </svg>

                        <span>
                            Pronunciation scoring currently uses transcript
                            clarity. You may correct recognition mistakes before
                            submitting.
                        </span>
                    </p>
                </div>

                {{-- ========================================================
                    SUBMIT
                ======================================================== --}}
                <div
                    class="sv-submit-row flex flex-col gap-3
                    sm:flex-row">

                    <button
                        id="submitBtn"
                        type="submit"
                        class="sv-speaking-submit flex-1">

                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2.2"
                            aria-hidden="true">

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="m22 2-7 20-4-9-9-4Z">
                            </path>

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M22 2 11 13">
                            </path>
                        </svg>

                        <span id="submitButtonText">
                            @if ($isAssessment)
                                Submit {{ $assessmentLabel }}
                                Individual Speaking
                            @elseif ($isPairWork)
                                Submit Pair Speaking Task
                            @else
                                Submit Speaking Task
                            @endif
                        </span>
                    </button>

                    <a
                        href="{{ $backRoute }}"
                        class="inline-flex min-h-14
                        items-center justify-center
                        rounded-2xl
                        bg-slate-200 px-7 py-4
                        font-bold text-slate-900
                        transition hover:bg-slate-300
                        dark:bg-white/10
                        dark:text-white
                        dark:hover:bg-white/15">

                        Back
                    </a>
                </div>
            </form>
        </section>

        {{-- ================================================================
            RESULT
        ================================================================= --}}
        <section
            id="resultSection"
            class="sv-result-section hidden space-y-6">

            <div
                class="rounded-[28px]
                border border-slate-200
                bg-white p-5 shadow-sm
                dark:border-white/10
                dark:bg-slate-900
                sm:p-7 lg:p-10">

                {{-- RESULT HEADER --}}
                <div class="text-center">

                    <div
                        class="mx-auto flex h-20 w-20
                        items-center justify-center
                        rounded-3xl
                        bg-emerald-100
                        text-4xl
                        dark:bg-emerald-500/10">

                        ✓
                    </div>

                    <h2
                        class="mt-6 text-3xl
                        font-black tracking-tight
                        text-slate-950
                        dark:text-white
                        sm:text-4xl">

                        @if ($isAssessment)
                            {{ $assessmentLabel }}
                            Individual Speaking Result
                        @else
                            Speaking Task Result
                        @endif
                    </h2>

                    <p
                        id="resultSubmissionMessage"
                        class="mx-auto mt-3
                        max-w-2xl text-base
                        leading-7 text-slate-500
                        dark:text-slate-400">

                        @if ($isAssessment)
                            Your individual presentation has been submitted
                            and evaluated.
                        @elseif ($isPairWork)
                            Your shared conversation has been submitted
                            and evaluated.
                        @else
                            Your speaking task has been submitted
                            and evaluated.
                        @endif
                    </p>
                </div>

                {{-- SCORE GRID --}}
                <div
                    id="scoreGrid"
                    class="mt-9 grid
                    grid-cols-1 gap-4
                    sm:grid-cols-2
                    xl:grid-cols-5">

                    {{-- CONTENT --}}
                    <article
                        class="rounded-2xl
                        border border-purple-200
                        bg-purple-50 p-5
                        dark:border-purple-500/20
                        dark:bg-purple-500/10">

                        <p
                            class="text-sm font-semibold
                            text-purple-700
                            dark:text-purple-300">

                            @if ($isAssessment)
                                Story Content
                            @else
                                Content Relevance
                            @endif
                        </p>

                        <p
                            class="mt-3 text-3xl
                            font-black text-purple-700
                            dark:text-purple-300">

                            <span id="detailsScore">
                                0
                            </span>

                            <span class="text-base">
                                /4
                            </span>
                        </p>
                    </article>

                    {{-- FLUENCY --}}
                    <article
                        class="rounded-2xl
                        border border-blue-200
                        bg-blue-50 p-5
                        dark:border-blue-500/20
                        dark:bg-blue-500/10">

                        <p
                            class="text-sm font-semibold
                            text-blue-700
                            dark:text-blue-300">

                            Fluency
                        </p>

                        <p
                            class="mt-3 text-3xl
                            font-black text-blue-700
                            dark:text-blue-300">

                            <span id="fluencyScore">
                                0
                            </span>

                            <span class="text-base">
                                /4
                            </span>
                        </p>
                    </article>

                    {{-- PRONUNCIATION --}}
                    <article
                        class="rounded-2xl
                        border border-cyan-200
                        bg-cyan-50 p-5
                        dark:border-cyan-500/20
                        dark:bg-cyan-500/10">

                        <p
                            class="text-sm font-semibold
                            text-cyan-700
                            dark:text-cyan-300">

                            Pronunciation
                        </p>

                        <p
                            class="mt-3 text-3xl
                            font-black text-cyan-700
                            dark:text-cyan-300">

                            <span id="pronunciationScore">
                                0
                            </span>

                            <span class="text-base">
                                /4
                            </span>
                        </p>
                    </article>

                    {{-- VOCABULARY --}}
                    <article
                        class="rounded-2xl
                        border border-emerald-200
                        bg-emerald-50 p-5
                        dark:border-emerald-500/20
                        dark:bg-emerald-500/10">

                        <p
                            class="text-sm font-semibold
                            text-emerald-700
                            dark:text-emerald-300">

                            Vocabulary
                        </p>

                        <p
                            class="mt-3 text-3xl
                            font-black text-emerald-700
                            dark:text-emerald-300">

                            <span id="vocabularyScore">
                                0
                            </span>

                            <span class="text-base">
                                /4
                            </span>
                        </p>
                    </article>

                    {{-- GRAMMAR --}}
                    <article
                        class="rounded-2xl
                        border border-orange-200
                        bg-orange-50 p-5
                        dark:border-orange-500/20
                        dark:bg-orange-500/10">

                        <p
                            class="text-sm font-semibold
                            text-orange-700
                            dark:text-orange-300">

                            Grammar
                        </p>

                        <p
                            class="mt-3 text-3xl
                            font-black text-orange-700
                            dark:text-orange-300">

                            <span id="grammarScore">
                                0
                            </span>

                            <span class="text-base">
                                /4
                            </span>
                        </p>
                    </article>
                </div>

                {{-- TOTAL SCORE --}}
                <div
                    id="totalScoreWrapper"
                    class="mt-8 rounded-3xl
                    border border-slate-200
                    bg-slate-50 p-7
                    text-center
                    dark:border-white/10
                    dark:bg-white/5">

                    <p
                        class="text-sm font-bold
                        uppercase tracking-[0.18em]
                        text-slate-500
                        dark:text-slate-400">

                        Final Score
                    </p>

                    <div
                        class="mt-3 flex items-end
                        justify-center gap-2">

                        <span
                            id="totalScore"
                            class="text-6xl font-black
                            leading-none text-purple-600
                            dark:text-purple-400
                            sm:text-7xl">

                            0
                        </span>

                        <span
                            class="pb-2 text-xl
                            font-bold text-slate-400">

                            /100
                        </span>
                    </div>
                </div>

                {{-- STRENGTHS AND IMPROVEMENTS --}}
                <div
                    class="mt-8 grid
                    grid-cols-1 gap-5
                    lg:grid-cols-2">

                    <article
                        class="rounded-3xl
                        border border-emerald-200
                        bg-emerald-50 p-6
                        dark:border-emerald-500/20
                        dark:bg-emerald-500/10">

                        <h3
                            class="text-xl font-black
                            text-emerald-800
                            dark:text-emerald-300">

                            Strengths
                        </h3>

                        <p
                            id="strengthsText"
                            class="mt-3 whitespace-pre-line
                            leading-7 text-slate-700
                            dark:text-slate-300">

                            -
                        </p>
                    </article>

                    <article
                        class="rounded-3xl
                        border border-orange-200
                        bg-orange-50 p-6
                        dark:border-orange-500/20
                        dark:bg-orange-500/10">

                        <h3
                            class="text-xl font-black
                            text-orange-800
                            dark:text-orange-300">

                            Improvements
                        </h3>

                        <p
                            id="improvementsText"
                            class="mt-3 whitespace-pre-line
                            leading-7 text-slate-700
                            dark:text-slate-300">

                            -
                        </p>
                    </article>
                </div>

                {{-- AI FEEDBACK --}}
                <div class="mt-8">

                    <h3
                        class="text-xl font-black
                        text-slate-950
                        dark:text-white">

                        AI Feedback
                    </h3>

                    <div
                        class="mt-3 rounded-3xl
                        border border-slate-200
                        bg-slate-50 p-5
                        dark:border-white/10
                        dark:bg-white/5
                        sm:p-6">

                        <p
                            id="feedbackText"
                            class="whitespace-pre-line
                            leading-8 text-slate-700
                            dark:text-slate-300">

                            -
                        </p>
                    </div>

                    {{-- Saran perbaikan Modul 5 (Speaking) — Bantu Pahami Feedback --}}
                    <div class="mt-3">
                        <button
                            type="button"
                            id="explainFeedbackBtn"
                            class="inline-flex items-center justify-center
                            rounded-2xl border border-slate-200 dark:border-white/10
                            bg-white dark:bg-white/5
                            px-4 py-2 text-sm font-bold
                            text-purple-600 dark:text-purple-400
                            hover:bg-purple-50 dark:hover:bg-purple-500/10
                            transition">
                            💡 Bantu saya paham feedback ini
                        </button>
                    </div>

                    <div
                        id="explainFeedbackPanel"
                        hidden
                        class="mt-3 rounded-3xl border border-purple-200 dark:border-purple-500/20
                        bg-purple-50 dark:bg-purple-500/10 p-5 sm:p-6">
                        <p class="mb-1 text-xs font-black text-purple-700 dark:text-purple-400">Penjelasan sederhana</p>
                        <p id="explainFeedbackText" class="text-sm leading-7 text-slate-700 dark:text-slate-300"></p>
                        <p class="mb-1 mt-3 text-xs font-black text-purple-700 dark:text-purple-400">Saran untuk lain kali</p>
                        <p id="explainFeedbackTip" class="text-sm leading-7 text-slate-700 dark:text-slate-300"></p>
                    </div>
                </div>

                {{-- GRAMMAR ERRORS --}}
                <div
                    id="grammarErrorsWrapper"
                    class="mt-8 hidden">

                    <h3
                        class="text-xl font-black
                        text-slate-950
                        dark:text-white">

                        Grammar Corrections
                    </h3>

                    <div
                        id="grammarErrorsList"
                        class="mt-3 space-y-3">
                    </div>
                </div>

                {{-- TRANSCRIPT RESULT --}}
                <div class="mt-8">

                    <h3
                        class="text-xl font-black
                        text-slate-950
                        dark:text-white">

                        Submitted Transcript
                    </h3>

                    <div
                        class="mt-3 rounded-3xl
                        border border-slate-200
                        bg-slate-50 p-5
                        dark:border-white/10
                        dark:bg-white/5
                        sm:p-6">

                        <p
                            id="resultTranscript"
                            class="whitespace-pre-line
                            break-words leading-8
                            text-slate-700
                            dark:text-slate-300">

                            -
                        </p>
                    </div>
                </div>
            </div>

            {{-- RESULT ACTIONS --}}
            <div
                class="grid grid-cols-1
                gap-4 md:grid-cols-2">

                <a
                    id="resultBackLink"
                    href="{{ $backRoute }}"
                    class="inline-flex min-h-14
                    w-full items-center
                    justify-center rounded-2xl
                    bg-slate-200 px-6 py-4
                    font-bold text-slate-900
                    transition hover:bg-slate-300
                    dark:bg-white/10
                    dark:text-white
                    dark:hover:bg-white/15">

                    @if ($isAssessment)
                        Back to Assessment
                    @else
                        Back to Speaking
                    @endif
                </a>

                <a
                    id="assessmentResultLink"
                    href="#"
                    class="hidden min-h-14
                    w-full items-center
                    justify-center rounded-2xl
                    bg-gradient-to-r
                    from-purple-500
                    to-indigo-600
                    px-6 py-4
                    font-bold text-white
                    shadow-lg
                    shadow-purple-500/20
                    transition hover:scale-[1.01]">

                    View Complete Assessment Result
                </a>

                <a
                    id="missionsResultLink"
                    href="{{ route('missions') }}"
                    class="inline-flex min-h-14
                    w-full items-center
                    justify-center rounded-2xl
                    bg-gradient-to-r
                    from-purple-500
                    to-indigo-600
                    px-6 py-4
                    font-bold text-white
                    shadow-lg
                    shadow-purple-500/20
                    transition hover:scale-[1.01]">

                    Back to Missions
                </a>
            </div>
        </section>
    </div>

    {{-- ================================================================
        LOADING MODAL
    ================================================================= --}}
    <div
        id="loadingModal"
        class="fixed inset-0 z-[9999]
        hidden items-center justify-center
        bg-slate-950/75 p-6
        backdrop-blur-sm">

        <div
            class="w-full max-w-md
            rounded-3xl
            border border-slate-200
            bg-white p-8
            text-center shadow-2xl
            dark:border-white/10
            dark:bg-slate-900">

            <div class="sv-loading-spinner"></div>

            <h2
                class="mt-6 text-2xl
                font-black text-slate-950
                dark:text-white">

                AI is evaluating...
            </h2>

            <p
                class="mt-3 text-sm leading-6
                text-slate-500
                dark:text-slate-400">

                @if ($isAssessment)
                    Please wait while your individual presentation
                    is being assessed.
                @elseif ($isPairWork)
                    Please wait while your conversation
                    is being assessed.
                @else
                    Please wait while your speaking response
                    is being assessed.
                @endif
            </p>

            <p
                class="mt-4 text-xs font-semibold
                text-slate-400">

                Do not close or refresh this page.
            </p>
        </div>
    </div>

    {{-- ================================================================
        JAVASCRIPT
    ================================================================= --}}
    <script>
        /*
        |--------------------------------------------------------------------------
        | Page Configuration
        |--------------------------------------------------------------------------
        */
        const isAssessment =
            @json($isAssessment);

        const isPairWork =
            @json($isPairWork);

        const minimumSeconds =
            @json($minimumSeconds);

        const maximumSeconds =
            @json($maximumSeconds);

        /*
        |--------------------------------------------------------------------------
        | Recording State
        |--------------------------------------------------------------------------
        */
        let mediaRecorder = null;
        let mediaStream = null;
        let audioChunks = [];
        let recordingStartedAt = null;
        let timerInterval = null;
        let recordedSeconds = 0;
        let audioPreviewUrl = null;
        let currentSubmissionId = null;

        // Saran perbaikan Modul 5 (Speaking) — Bantuan Sebelum Merekam.
        // CATATAN: TIDAK dibungkus DOMContentLoaded — script ini
        // ditempatkan di akhir <body>, jadi DOM sudah siap saat baris
        // ini jalan (sama seperti deklarasi lain di atas). Membungkus
        // dengan DOMContentLoaded di sini justru BERISIKO: event itu
        // mungkin sudah lewat duluan, membuat listener tidak pernah
        // terdaftar.
        const preTaskHelpBtn = document.getElementById('preTaskHelpBtn');
        if (preTaskHelpBtn) {
            preTaskHelpBtn.addEventListener('click', async function () {
                const tokenInput = document.querySelector('#speakingForm input[name="_token"]');
                const csrfToken = tokenInput ? tokenInput.value : '';

                preTaskHelpBtn.disabled = true;
                preTaskHelpBtn.textContent = 'Memuat...';

                try {
                    const response = await fetch(
                        @json(route('student.speaking.preTaskHelp', $lesson)),
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
                    document.getElementById('preTaskHelpPanel').hidden = false;
                } catch (error) {
                    console.error('Pre-task help error:', error);
                    alert(error.message || 'Bantuan tidak tersedia saat ini.');
                } finally {
                    preTaskHelpBtn.disabled = false;
                    preTaskHelpBtn.textContent = '💡 Bantuan sebelum merekam';
                }
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Elements
        |--------------------------------------------------------------------------
        */
        const startBtn =
            document.getElementById(
                'startBtn'
            );

        const stopBtn =
            document.getElementById(
                'stopBtn'
            );

        const recordTimer =
            document.getElementById(
                'recordTimer'
            );

        const recordStatus =
            document.getElementById(
                'recordStatus'
            );

        const recordStatusDot =
            document.getElementById(
                'recordStatusDot'
            );

        const audioInput =
            document.getElementById(
                'audioInput'
            );

        const audioDurationInput =
            document.getElementById(
                'audioDurationInput'
            );

        const audioPreview =
            document.getElementById(
                'audioPreview'
            );

        const transcriptInput =
            document.getElementById(
                'transcript'
            );

        const recognitionStatus =
            document.getElementById(
                'recognitionStatus'
            );


        const recognitionStatusLabel =
            document.getElementById(
                'recognitionStatusLabel'
            );

        const recognitionStatusDetail =
            document.getElementById(
                'recognitionStatusDetail'
            );

        const recognitionStateText =
            document.getElementById(
                'recognitionStateText'
            );

        const speakingForm =
            document.getElementById(
                'speakingForm'
            );

        const speakingSection =
            document.getElementById(
                'speakingSection'
            );

        const resultSection =
            document.getElementById(
                'resultSection'
            );

        const loadingModal =
            document.getElementById(
                'loadingModal'
            );

        const submitBtn =
            document.getElementById(
                'submitBtn'
            );

        const submitButtonText =
            document.getElementById(
                'submitButtonText'
            );

        const submitterRole =
            document.getElementById(
                'submitter_role'
            );

        const partnerInput =
            document.getElementById(
                'partner_user_id'
            );

        const assessmentResultLink =
            document.getElementById(
                'assessmentResultLink'
            );

        const missionsResultLink =
            document.getElementById(
                'missionsResultLink'
            );

        /*
        |--------------------------------------------------------------------------
        | Speech Recognition State
        |--------------------------------------------------------------------------
        */
        let speechRecognition = null;
        let finalTranscript = '';
        let recognitionRunning = false;
        let shouldRestartRecognition = false;

        const SpeechRecognition =
            window.SpeechRecognition ||
            window.webkitSpeechRecognition;

        /*
        |--------------------------------------------------------------------------
        | Speech Recognition
        |--------------------------------------------------------------------------
        */
        function setRecognitionState(
            state,
            label,
            detail
        ) {
            if (!recognitionStatus) {
                return;
            }

            recognitionStatus.className =
                'sv-live-transcript-status is-' + state;

            if (recognitionStatusLabel) {
                recognitionStatusLabel.textContent =
                    label;
            }

            if (recognitionStatusDetail) {
                recognitionStatusDetail.textContent =
                    detail;
            }

            if (recognitionStateText) {
                const stateLabels = {
                    ready: 'Ready',
                    listening: 'Listening',
                    warning: 'Attention',
                    stopped: 'Paused'
                };

                recognitionStateText.textContent =
                    stateLabels[state] || 'Ready';
            }
        }

        if (SpeechRecognition) {
            speechRecognition =
                new SpeechRecognition();

            speechRecognition.lang =
                'en-US';

            speechRecognition.continuous =
                true;

            speechRecognition.interimResults =
                true;

            setRecognitionState(
                'ready',
                'Ready to capture speech',
                'Starts automatically when recording begins.'
            );

            speechRecognition.onstart =
                function () {
                    recognitionRunning =
                        true;

                    setRecognitionState(
                        'listening',
                        'Transcribing speech',
                        isAssessment
                            ? 'Capturing your presentation'
                            : (
                                isPairWork
                                    ? 'Capturing both speakers'
                                    : 'Capturing your speech'
                            )
                    );
                };

            speechRecognition.onresult =
                function (event) {
                    let interimTranscript =
                        '';

                    for (
                        let index =
                            event.resultIndex;

                        index <
                            event.results.length;

                        index++
                    ) {
                        const recognisedText =
                            event
                                .results[index][0]
                                .transcript;

                        if (
                            event
                                .results[index]
                                .isFinal
                        ) {
                            finalTranscript +=
                                recognisedText +
                                ' ';
                        } else {
                            interimTranscript +=
                                recognisedText;
                        }
                    }

                    transcriptInput.value =
                        (
                            finalTranscript +
                            interimTranscript
                        ).trim();
                };

            speechRecognition.onend =
                function () {
                    recognitionRunning =
                        false;

                    if (
                        shouldRestartRecognition &&
                        mediaRecorder &&
                        mediaRecorder.state ===
                            'recording'
                    ) {
                        window.setTimeout(
                            function () {
                                try {
                                    speechRecognition.start();
                                } catch (error) {
                                    console.warn(
                                        'Speech recognition restart failed.',
                                        error
                                    );
                                }
                            },
                            250
                        );

                        return;
                    }

                    setRecognitionState(
                        'stopped',
                        'Transcription paused',
                        'Review or edit the text before submitting.'
                    );
                };

            speechRecognition.onerror =
                function (event) {
                    console.warn(
                        'Speech recognition error:',
                        event.error
                    );

                    if (
                        event.error ===
                        'not-allowed'
                    ) {
                        shouldRestartRecognition =
                            false;

                        setRecognitionState(
                            'warning',
                            'Microphone permission blocked',
                            'Allow microphone access or type the transcript manually.'
                        );
                    } else if (
                        event.error ===
                        'no-speech'
                    ) {
                        setRecognitionState(
                            'warning',
                            'No clear speech detected',
                            'Speak closer to the microphone and try again.'
                        );
                    } else {
                        setRecognitionState(
                            'warning',
                            'Live transcription unavailable',
                            'You can still type the transcript manually.'
                        );
                    }
                };
        } else {
            setRecognitionState(
                'warning',
                'Live transcription is not supported',
                'Use a supported browser or type the transcript manually.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Helpers
        |--------------------------------------------------------------------------
        */
        function formatTime(
            totalSeconds
        ) {
            const minutes =
                Math.floor(
                    totalSeconds / 60
                );

            const seconds =
                totalSeconds % 60;

            return (
                String(minutes)
                    .padStart(2, '0') +
                ':' +
                String(seconds)
                    .padStart(2, '0')
            );
        }

        function updateRecordStatus(
            message,
            state = 'ready'
        ) {
            recordStatus.textContent =
                message;

            const stateClasses = {
                ready: {
                    text:
                        'text-slate-500 dark:text-slate-400',

                    dot:
                        'bg-slate-400'
                },

                recording: {
                    text:
                        'text-red-600 dark:text-red-400',

                    dot:
                        'bg-red-500 animate-pulse'
                },

                success: {
                    text:
                        'text-green-600 dark:text-green-400',

                    dot:
                        'bg-green-500'
                },

                warning: {
                    text:
                        'text-amber-700 dark:text-amber-300',

                    dot:
                        'bg-amber-500'
                }
            };

            const config =
                stateClasses[state] ||
                stateClasses.ready;

            recordStatus.className =
                'block text-xs font-extrabold leading-5 ' +
                config.text;

            recordStatusDot.className =
                'h-2 w-2 rounded-full ' +
                config.dot;
        }

        function startTimer() {
            recordingStartedAt =
                Date.now();

            recordedSeconds =
                0;

            recordTimer.textContent =
                '00:00';

            audioDurationInput.value =
                '0';

            timerInterval =
                window.setInterval(
                    function () {
                        recordedSeconds =
                            Math.floor(
                                (
                                    Date.now() -
                                    recordingStartedAt
                                ) / 1000
                            );

                        recordTimer.textContent =
                            formatTime(
                                recordedSeconds
                            );

                        audioDurationInput.value =
                            recordedSeconds;

                        /*
                        |--------------------------------------------------------------------------
                        | Minimum duration reached
                        |--------------------------------------------------------------------------
                        */
                        if (
                            minimumSeconds > 0 &&
                            recordedSeconds >=
                                minimumSeconds
                        ) {
                            recordTimer.classList.remove(
                                'text-purple-600',
                                'dark:text-purple-400'
                            );

                            recordTimer.classList.add(
                                'text-green-600',
                                'dark:text-green-400'
                            );
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | Maximum duration reached
                        |--------------------------------------------------------------------------
                        */
                        if (
                            maximumSeconds > 0 &&
                            recordedSeconds >=
                                maximumSeconds
                        ) {
                            stopRecording();

                            alert(
                                'Maximum recording duration reached.'
                            );
                        }
                    },
                    500
                );
        }

        function stopTimer() {
            if (timerInterval) {
                window.clearInterval(
                    timerInterval
                );

                timerInterval =
                    null;
            }

            if (recordingStartedAt) {
                recordedSeconds =
                    Math.max(
                        1,
                        Math.floor(
                            (
                                Date.now() -
                                recordingStartedAt
                            ) / 1000
                        )
                    );

                audioDurationInput.value =
                    recordedSeconds;

                recordTimer.textContent =
                    formatTime(
                        recordedSeconds
                    );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Start Recording
        |--------------------------------------------------------------------------
        */
        async function startRecording() {
            if (
                !navigator.mediaDevices ||
                !navigator.mediaDevices.getUserMedia
            ) {
                alert(
                    'Audio recording is not supported by this browser.'
                );

                return;
            }

            try {
                /*
                |--------------------------------------------------------------------------
                | Bersihkan audio sebelumnya
                |--------------------------------------------------------------------------
                */
                if (audioPreviewUrl) {
                    URL.revokeObjectURL(
                        audioPreviewUrl
                    );

                    audioPreviewUrl =
                        null;
                }

                audioPreview.pause();
                audioPreview.removeAttribute(
                    'src'
                );
                audioPreview.load();
                audioPreview.classList.add(
                    'hidden'
                );

                audioInput.value =
                    '';

                mediaStream =
                    await navigator
                        .mediaDevices
                        .getUserMedia({
                            audio: true
                        });

                audioChunks =
                    [];

                finalTranscript =
                    '';

                transcriptInput.value =
                    '';

                let options =
                    {};

                if (
                    MediaRecorder.isTypeSupported(
                        'audio/webm;codecs=opus'
                    )
                ) {
                    options = {
                        mimeType:
                            'audio/webm;codecs=opus'
                    };
                } else if (
                    MediaRecorder.isTypeSupported(
                        'audio/webm'
                    )
                ) {
                    options = {
                        mimeType:
                            'audio/webm'
                    };
                }

                mediaRecorder =
                    new MediaRecorder(
                        mediaStream,
                        options
                    );

                mediaRecorder.ondataavailable =
                    function (event) {
                        if (
                            event.data &&
                            event.data.size > 0
                        ) {
                            audioChunks.push(
                                event.data
                            );
                        }
                    };

                mediaRecorder.onstop =
                    createAudioFile;

                mediaRecorder.onerror =
                    function (event) {
                        console.error(
                            'MediaRecorder error:',
                            event
                        );

                        updateRecordStatus(
                            'Recording failed. Please try again.',
                            'warning'
                        );
                    };

                mediaRecorder.start(
                    1000
                );

                startBtn.disabled =
                    true;

                stopBtn.disabled =
                    false;

                updateRecordStatus(
                    isAssessment
                        ? 'Recording your individual presentation...'
                        : (
                            isPairWork
                                ? 'Recording... both students should speak clearly.'
                                : 'Recording... speak clearly.'
                        ),
                    'recording'
                );

                recordTimer.classList.remove(
                    'text-green-600',
                    'dark:text-green-400'
                );

                recordTimer.classList.add(
                    'text-purple-600',
                    'dark:text-purple-400'
                );

                startTimer();

                shouldRestartRecognition =
                    true;

                if (speechRecognition) {
                    try {
                        speechRecognition.start();
                    } catch (error) {
                        console.warn(
                            'Speech recognition could not start.',
                            error
                        );
                    }
                }
            } catch (error) {
                console.error(
                    error
                );

                updateRecordStatus(
                    'Microphone access could not be started.',
                    'warning'
                );

                alert(
                    'Microphone access could not be started. Please allow microphone permission and try again.'
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Stop Recording
        |--------------------------------------------------------------------------
        */
        function stopRecording() {
            if (
                mediaRecorder &&
                mediaRecorder.state ===
                    'recording'
            ) {
                mediaRecorder.stop();
            }

            shouldRestartRecognition =
                false;

            if (
                speechRecognition &&
                recognitionRunning
            ) {
                try {
                    speechRecognition.stop();
                } catch (error) {
                    console.warn(
                        error
                    );
                }
            }

            stopTimer();

            startBtn.disabled =
                false;

            stopBtn.disabled =
                true;

            if (mediaStream) {
                mediaStream
                    .getTracks()
                    .forEach(
                        function (track) {
                            track.stop();
                        }
                    );

                mediaStream =
                    null;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Create Audio File
        |--------------------------------------------------------------------------
        */
        function createAudioFile() {
            if (
                !audioChunks.length
            ) {
                updateRecordStatus(
                    'No audio data was recorded. Please try again.',
                    'warning'
                );

                return;
            }

            const mimeType =
                mediaRecorder?.mimeType ||
                'audio/webm';

            const audioBlob =
                new Blob(
                    audioChunks,
                    {
                        type:
                            mimeType
                    }
                );

            const filePrefix =
                isAssessment
                    ? 'individual-speaking-assessment'
                    : (
                        isPairWork
                            ? 'pair-speaking'
                            : 'individual-speaking'
                    );

            const file =
                new File(
                    [
                        audioBlob
                    ],
                    filePrefix +
                        '-' +
                        Date.now() +
                        '.webm',
                    {
                        type:
                            mimeType
                    }
                );

            const transfer =
                new DataTransfer();

            transfer.items.add(
                file
            );

            audioInput.files =
                transfer.files;

            audioPreviewUrl =
                URL.createObjectURL(
                    audioBlob
                );

            audioPreview.src =
                audioPreviewUrl;

            audioPreview.classList.remove(
                'hidden'
            );

            updateRecordStatus(
                'Recording finished. Review the audio and transcript before submitting.',
                'success'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Button Events
        |--------------------------------------------------------------------------
        */
        startBtn.addEventListener(
            'click',
            startRecording
        );

        stopBtn.addEventListener(
            'click',
            stopRecording
        );

        /*
        |--------------------------------------------------------------------------
        | Role Highlight — Pair Speaking Only
        |--------------------------------------------------------------------------
        */
        if (submitterRole) {
            submitterRole.addEventListener(
                'change',
                function () {
                    const roleACard =
                        document.getElementById(
                            'roleACard'
                        );

                    const roleBCard =
                        document.getElementById(
                            'roleBCard'
                        );

                    if (
                        roleACard
                    ) {
                        roleACard.classList.remove(
                            'is-selected'
                        );
                    }

                    if (
                        roleBCard
                    ) {
                        roleBCard.classList.remove(
                            'is-selected'
                        );
                    }

                    if (
                        this.value === 'A' &&
                        roleACard
                    ) {
                        roleACard.classList.add(
                            'is-selected'
                        );
                    }

                    if (
                        this.value === 'B' &&
                        roleBCard
                    ) {
                        roleBCard.classList.add(
                            'is-selected'
                        );
                    }
                }
            );

            /*
            |--------------------------------------------------------------------------
            | Terapkan highlight old value
            |--------------------------------------------------------------------------
            */
            submitterRole.dispatchEvent(
                new Event('change')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Form Submission
        |--------------------------------------------------------------------------
        */
        speakingForm.addEventListener(
            'submit',
            async function (event) {
                event.preventDefault();

                /*
                |--------------------------------------------------------------------------
                | Pair validation
                |--------------------------------------------------------------------------
                */
                if (
                    isPairWork &&
                    (
                        !partnerInput ||
                        !partnerInput.value
                    )
                ) {
                    alert(
                        'Please select your partner.'
                    );

                    return;
                }

                if (
                    isPairWork &&
                    (
                        !submitterRole ||
                        !submitterRole.value
                    )
                ) {
                    alert(
                        'Please select your role.'
                    );

                    return;
                }

                /*
                |--------------------------------------------------------------------------
                | Audio validation
                |--------------------------------------------------------------------------
                */
                if (
                    !audioInput.files.length
                ) {
                    alert(
                        isAssessment
                            ? 'Please record your presentation first.'
                            : (
                                isPairWork
                                    ? 'Please record the conversation first.'
                                    : 'Please record your speaking response first.'
                            )
                    );

                    return;
                }

                /*
                |--------------------------------------------------------------------------
                | Transcript validation
                |--------------------------------------------------------------------------
                */
                if (
                    !transcriptInput.value.trim()
                ) {
                    alert(
                        'Transcript is required. Correct or type the transcript before submitting.'
                    );

                    transcriptInput.focus();

                    return;
                }

                /*
                |--------------------------------------------------------------------------
                | Duration validation
                |--------------------------------------------------------------------------
                */
                const duration =
                    Number(
                        audioDurationInput.value
                    );

                if (
                    !Number.isFinite(
                        duration
                    ) ||
                    duration <= 0
                ) {
                    alert(
                        'The recording duration could not be detected. Please record again.'
                    );

                    return;
                }

                if (
                    minimumSeconds > 0 &&
                    duration <
                        minimumSeconds
                ) {
                    alert(
                        'Recording is too short. Minimum duration is ' +
                        Math.ceil(
                            minimumSeconds / 60
                        ) +
                        ' minute(s).'
                    );

                    return;
                }

                if (
                    maximumSeconds > 0 &&
                    duration >
                        maximumSeconds
                ) {
                    alert(
                        'Recording is too long. Maximum duration is ' +
                        Math.ceil(
                            maximumSeconds / 60
                        ) +
                        ' minute(s).'
                    );

                    return;
                }

                /*
                |--------------------------------------------------------------------------
                | Loading state
                |--------------------------------------------------------------------------
                */
                loadingModal.classList.remove(
                    'hidden'
                );

                loadingModal.classList.add(
                    'flex'
                );

                submitBtn.disabled =
                    true;

                const originalSubmitText =
                    submitButtonText.textContent;

                submitButtonText.textContent =
                    'Submitting...';

                const formData =
                    new FormData(
                        speakingForm
                    );

                try {
                    const response =
                        await fetch(
                            speakingForm.action,
                            {
                                method:
                                    'POST',

                                body:
                                    formData,

                                headers: {
                                    'Accept':
                                        'application/json'
                                }
                            }
                        );

                    let result =
                        {};

                    try {
                        result =
                            await response.json();
                    } catch (jsonError) {
                        console.error(
                            'Invalid JSON response:',
                            jsonError
                        );

                        throw new Error(
                            'The server returned an invalid response.'
                        );
                    }

                    loadingModal.classList.remove(
                        'flex'
                    );

                    loadingModal.classList.add(
                        'hidden'
                    );

                    submitBtn.disabled =
                        false;

                    submitButtonText.textContent =
                        originalSubmitText;

                    if (!response.ok) {
                        const validationErrors =
                            result.errors
                                ? Object
                                    .values(
                                        result.errors
                                    )
                                    .flat()
                                    .join('\n')
                                : null;

                        alert(
                            validationErrors ||
                            result.message ||
                            'Failed to submit speaking task.'
                        );

                        /*
                        |--------------------------------------------------------------------------
                        | Existing assessment result
                        |--------------------------------------------------------------------------
                        */
                        if (
                            result.assessment_result_url
                        ) {
                            window.location.href =
                                result.assessment_result_url;
                        }

                        return;
                    }

                    showResult(
                        result
                    );
                } catch (error) {
                    loadingModal.classList.remove(
                        'flex'
                    );

                    loadingModal.classList.add(
                        'hidden'
                    );

                    submitBtn.disabled =
                        false;

                    submitButtonText.textContent =
                        originalSubmitText;

                    console.error(
                        error
                    );

                    alert(
                        error.message ||
                        'Something went wrong while submitting the speaking task.'
                    );
                }
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Show Result
        |--------------------------------------------------------------------------
        */
        function showResult(
            result
        ) {
            speakingSection.classList.add(
                'hidden'
            );

            resultSection.classList.remove(
                'hidden'
            );

            const hasScores =
                result.total_score !== null &&
                result.total_score !== undefined;

            document
                .getElementById(
                    'scoreGrid'
                )
                .classList
                .toggle(
                    'hidden',
                    !hasScores
                );

            document
                .getElementById(
                    'totalScoreWrapper'
                )
                .classList
                .toggle(
                    'hidden',
                    !hasScores
                );

            document
                .getElementById(
                    'detailsScore'
                )
                .textContent =
                    result.details_score ??
                    0;

            document
                .getElementById(
                    'fluencyScore'
                )
                .textContent =
                    result.fluency_score ??
                    0;

            document
                .getElementById(
                    'pronunciationScore'
                )
                .textContent =
                    result.pronunciation_score ??
                    0;

            document
                .getElementById(
                    'vocabularyScore'
                )
                .textContent =
                    result.vocabulary_score ??
                    0;

            document
                .getElementById(
                    'grammarScore'
                )
                .textContent =
                    result.grammar_score ??
                    0;

            document
                .getElementById(
                    'totalScore'
                )
                .textContent =
                    result.total_score ??
                    '-';

            document
                .getElementById(
                    'feedbackText'
                )
                .textContent =
                    result.feedback ??
                    '-';

            // Saran perbaikan Modul 5 (Speaking) — Bantu Pahami Feedback.
            currentSubmissionId = result.assessment_submission_id ?? null;
            const explainPanelEl = document.getElementById('explainFeedbackPanel');
            if (explainPanelEl) {
                explainPanelEl.hidden = true;
            }

            document
                .getElementById(
                    'strengthsText'
                )
                .textContent =
                    result.strengths ??
                    '-';

            document
                .getElementById(
                    'improvementsText'
                )
                .textContent =
                    result.improvements ??
                    '-';

            document
                .getElementById(
                    'resultTranscript'
                )
                .textContent =
                    result.transcript ??
                    '-';

            renderGrammarErrors(
                result.grammar_errors ??
                []
            );

            /*
            |--------------------------------------------------------------------------
            | Assessment result link
            |--------------------------------------------------------------------------
            */
            if (
                isAssessment &&
                result.assessment_result_url &&
                assessmentResultLink
            ) {
                assessmentResultLink.href =
                    result.assessment_result_url;

                assessmentResultLink.classList.remove(
                    'hidden'
                );

                assessmentResultLink.classList.add(
                    'inline-flex'
                );

                if (missionsResultLink) {
                    missionsResultLink.classList.add(
                        'hidden'
                    );

                    missionsResultLink.classList.remove(
                        'inline-flex'
                    );
                }
            }

            window.scrollTo({
                top:
                    0,

                behavior:
                    'smooth'
            });
        }

        // Saran perbaikan Modul 5 (Speaking) — Bantu Pahami Feedback.
        const explainFeedbackBtn = document.getElementById('explainFeedbackBtn');
        if (explainFeedbackBtn) {
            explainFeedbackBtn.addEventListener('click', async function () {
                if (!currentSubmissionId) {
                    return;
                }

                const tokenInput = speakingForm.querySelector('input[name="_token"]');
                const csrfToken = tokenInput ? tokenInput.value : '';

                explainFeedbackBtn.disabled = true;
                explainFeedbackBtn.textContent = 'Memuat...';

                try {
                    const urlTemplate = @json(route('student.speaking.explainFeedback', [$lesson, '__SUBMISSION_ID__']));
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

        /*
        |--------------------------------------------------------------------------
        | Grammar Errors
        |--------------------------------------------------------------------------
        */
        function renderGrammarErrors(
            errors
        ) {
            const wrapper =
                document.getElementById(
                    'grammarErrorsWrapper'
                );

            const list =
                document.getElementById(
                    'grammarErrorsList'
                );

            list.innerHTML =
                '';

            if (
                !Array.isArray(
                    errors
                ) ||
                errors.length === 0
            ) {
                wrapper.classList.add(
                    'hidden'
                );

                return;
            }

            errors.forEach(
                function (error) {
                    const item =
                        document.createElement(
                            'article'
                        );

                    item.className =
                        'rounded-2xl border ' +
                        'border-slate-200 bg-slate-50 ' +
                        'p-5 dark:border-white/10 ' +
                        'dark:bg-white/5';

                    const original =
                        escapeHtml(
                            error.original ||
                            '-'
                        );

                    const correction =
                        escapeHtml(
                            error.correction ||
                            '-'
                        );

                    const explanation =
                        escapeHtml(
                            error.explanation ||
                            ''
                        );

                    item.innerHTML = `
                        <div
                            class="grid grid-cols-1
                            gap-5 md:grid-cols-2">

                            <div>
                                <p
                                    class="text-sm font-black
                                    uppercase tracking-wider
                                    text-red-600
                                    dark:text-red-400">

                                    Original
                                </p>

                                <p
                                    class="mt-2 break-words
                                    leading-7 text-slate-700
                                    dark:text-slate-300">

                                    ${original}
                                </p>
                            </div>

                            <div>
                                <p
                                    class="text-sm font-black
                                    uppercase tracking-wider
                                    text-green-600
                                    dark:text-green-400">

                                    Correction
                                </p>

                                <p
                                    class="mt-2 break-words
                                    leading-7 text-slate-700
                                    dark:text-slate-300">

                                    ${correction}
                                </p>
                            </div>
                        </div>

                        ${
                            explanation
                                ? `
                                    <div
                                        class="mt-5 border-t
                                        border-slate-200 pt-5
                                        dark:border-white/10">

                                        <p
                                            class="text-sm font-black
                                            uppercase tracking-wider
                                            text-blue-600
                                            dark:text-blue-400">

                                            Explanation
                                        </p>

                                        <p
                                            class="mt-2 break-words
                                            leading-7 text-slate-700
                                            dark:text-slate-300">

                                            ${explanation}
                                        </p>
                                    </div>
                                `
                                : ''
                        }
                    `;

                    list.appendChild(
                        item
                    );
                }
            );

            wrapper.classList.remove(
                'hidden'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Escape HTML
        |--------------------------------------------------------------------------
        */
        function escapeHtml(
            value
        ) {
            const element =
                document.createElement(
                    'div'
                );

            element.textContent =
                String(value);

            return element.innerHTML;
        }

        /*
        |--------------------------------------------------------------------------
        | Cleanup
        |--------------------------------------------------------------------------
        */
        window.addEventListener(
            'beforeunload',
            function () {
                shouldRestartRecognition =
                    false;

                if (
                    mediaRecorder &&
                    mediaRecorder.state ===
                        'recording'
                ) {
                    mediaRecorder.stop();
                }

                if (
                    speechRecognition &&
                    recognitionRunning
                ) {
                    try {
                        speechRecognition.stop();
                    } catch (error) {
                        console.warn(
                            error
                        );
                    }
                }

                if (mediaStream) {
                    mediaStream
                        .getTracks()
                        .forEach(
                            function (track) {
                                track.stop();
                            }
                        );
                }

                if (audioPreviewUrl) {
                    URL.revokeObjectURL(
                        audioPreviewUrl
                    );
                }
            }
        );
    </script>
</x-app-layout>