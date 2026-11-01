@extends('layouts.admin')

@section('content')
    @php
        $contextTitle = $material->title ?? 'Listening Material';

        $backRoute = route(
            'admin.listening-questions.index',
            $material->id
        );

        $oldQuestions = old('questions');

        if (!is_array($oldQuestions) || count($oldQuestions) === 0) {
            $oldQuestions = [
                [
                    'instruction' => '',
                    'question' => '',
                    'sub_skill' => '',
                    'option_a' => '',
                    'option_b' => '',
                    'option_c' => '',
                    'option_d' => '',
                    'option_e' => '',
                    'correct_answer' => 'A',
                    'score' => 10,
                ],
            ];
        }
    @endphp


    <style>
        /*
        |--------------------------------------------------------------------------
        | LISTENING QUESTIONS CREATE
        |--------------------------------------------------------------------------
        |
        | Semua style dibuat scoped menggunakan prefix "lqc-".
        | Tujuannya supaya CSS global layouts.admin tidak merusak form,
        | input, card, button, maupun dark mode halaman ini.
        |
        */

        .lqc-page {
            --lqc-text: #0f172a;
            --lqc-text-soft: #334155;
            --lqc-muted: #64748b;
            --lqc-muted-soft: #94a3b8;

            --lqc-panel: #ffffff;
            --lqc-panel-soft: #f8fafc;
            --lqc-panel-hover: #f1f5f9;
            --lqc-input: #ffffff;

            --lqc-border: #e2e8f0;
            --lqc-border-strong: #cbd5e1;

            --lqc-blue: #2563eb;
            --lqc-blue-hover: #1d4ed8;
            --lqc-blue-soft: #eff6ff;
            --lqc-blue-border: #bfdbfe;

            --lqc-green: #047857;
            --lqc-green-soft: #ecfdf5;
            --lqc-green-border: #a7f3d0;

            --lqc-amber: #b45309;
            --lqc-amber-soft: #fffbeb;
            --lqc-amber-border: #fde68a;

            --lqc-red: #dc2626;
            --lqc-red-soft: #fef2f2;
            --lqc-red-border: #fecaca;

            width: 100%;
            color: var(--lqc-text);
        }

        html.dark .lqc-page,
        body.dark .lqc-page,
        .dark .lqc-page,
        [data-theme="dark"] .lqc-page {
            --lqc-text: #f8fafc;
            --lqc-text-soft: #e2e8f0;
            --lqc-muted: #94a3b8;
            --lqc-muted-soft: #64748b;

            --lqc-panel: #1e293b;
            --lqc-panel-soft: #0f172a;
            --lqc-panel-hover: #263449;
            --lqc-input: #0f172a;

            --lqc-border: #334155;
            --lqc-border-strong: #475569;

            --lqc-blue: #60a5fa;
            --lqc-blue-hover: #93c5fd;
            --lqc-blue-soft: rgba(59, 130, 246, .10);
            --lqc-blue-border: rgba(96, 165, 250, .24);

            --lqc-green: #6ee7b7;
            --lqc-green-soft: rgba(16, 185, 129, .10);
            --lqc-green-border: rgba(110, 231, 183, .22);

            --lqc-amber: #fbbf24;
            --lqc-amber-soft: rgba(245, 158, 11, .10);
            --lqc-amber-border: rgba(251, 191, 36, .22);

            --lqc-red: #fca5a5;
            --lqc-red-soft: rgba(239, 68, 68, .10);
            --lqc-red-border: rgba(248, 113, 113, .22);
        }

        .lqc-page,
        .lqc-page * {
            box-sizing: border-box;
        }


        /* ============================================================
         | PAGE HEADER
         * ============================================================ */

        .lqc-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;

            gap: 24px;

            margin-bottom: 22px;
        }

        .lqc-header-copy {
            min-width: 0;
        }

        .lqc-eyebrow {
            margin: 0 0 7px;

            color: var(--lqc-blue) !important;

            font-size: 11px;
            font-weight: 900;
            line-height: 1;

            letter-spacing: .13em;
            text-transform: uppercase;
        }

        .lqc-title {
            margin: 0;

            color: var(--lqc-text) !important;

            font-size: clamp(28px, 2.2vw, 36px);
            font-weight: 900;
            line-height: 1.12;

            letter-spacing: -.035em;
        }

        .lqc-subtitle {
            max-width: 760px;

            margin: 9px 0 0;

            color: var(--lqc-muted) !important;

            font-size: 13px;
            line-height: 1.65;
        }

        .lqc-subtitle strong {
            color: var(--lqc-blue) !important;
            font-weight: 850;
        }


        /* ============================================================
         | BUTTONS
         * ============================================================ */

        .lqc-btn {
            display: inline-flex !important;

            width: auto !important;
            min-width: 0 !important;

            height: 42px !important;
            min-height: 42px !important;
            max-height: 42px !important;

            align-items: center !important;
            justify-content: center !important;

            padding: 0 16px !important;

            border-radius: 10px !important;

            font-family: inherit !important;
            font-size: 12px !important;
            font-weight: 850 !important;
            line-height: 1 !important;

            text-decoration: none !important;
            white-space: nowrap !important;

            cursor: pointer !important;

            transition:
                background-color .15s ease,
                border-color .15s ease,
                color .15s ease !important;
        }

        .lqc-btn-secondary {
            color: var(--lqc-text) !important;
            background: var(--lqc-panel) !important;

            border: 1px solid var(--lqc-border) !important;
        }

        .lqc-btn-secondary:hover {
            background: var(--lqc-panel-hover) !important;
            border-color: var(--lqc-border-strong) !important;
        }

        .lqc-btn-primary {
            color: #ffffff !important;
            background: #2563eb !important;

            border: 1px solid #2563eb !important;
        }

        .lqc-btn-primary:hover {
            background: #1d4ed8 !important;
            border-color: #1d4ed8 !important;
        }

        .lqc-btn-add {
            color: var(--lqc-green) !important;
            background: var(--lqc-green-soft) !important;

            border: 1px solid var(--lqc-green-border) !important;
        }

        .lqc-btn-add:hover {
            border-color: var(--lqc-green) !important;
        }

        .lqc-btn-remove {
            height: 34px !important;
            min-height: 34px !important;
            max-height: 34px !important;

            padding: 0 11px !important;

            color: var(--lqc-red) !important;
            background: var(--lqc-red-soft) !important;

            border: 1px solid var(--lqc-red-border) !important;

            font-size: 10px !important;
        }

        .lqc-btn-remove:hover {
            border-color: var(--lqc-red) !important;
        }


        /* ============================================================
         | ERROR SUMMARY
         * ============================================================ */

        .lqc-errors {
            margin-bottom: 20px;

            padding: 15px 17px;

            color: var(--lqc-red) !important;
            background: var(--lqc-red-soft) !important;

            border: 1px solid var(--lqc-red-border);
            border-radius: 12px;
        }

        .lqc-errors-title {
            margin: 0;

            font-size: 13px;
            font-weight: 900;
        }

        .lqc-errors-list {
            margin: 8px 0 0;
            padding-left: 19px;

            font-size: 11px;
            font-weight: 650;
            line-height: 1.7;
        }


        /* ============================================================
         | MATERIAL
         * ============================================================ */

        .lqc-material {
            margin-bottom: 20px;

            overflow: hidden;

            background: var(--lqc-panel) !important;

            border: 1px solid var(--lqc-border);
            border-radius: 14px;
        }

        .lqc-material-main {
            display: flex;

            align-items: center;
            justify-content: space-between;

            gap: 20px;

            padding: 16px 18px;
        }

        .lqc-material-copy {
            min-width: 0;
        }

        .lqc-material-label {
            margin: 0 0 5px;

            color: var(--lqc-blue) !important;

            font-size: 9px;
            font-weight: 900;
            line-height: 1;

            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .lqc-material-title {
            margin: 0;

            color: var(--lqc-text) !important;

            font-size: 15px;
            font-weight: 900;
            line-height: 1.45;

            overflow-wrap: anywhere;
        }

        .lqc-question-count {
            display: inline-flex;

            flex: 0 0 auto;

            min-height: 30px;

            align-items: center;
            justify-content: center;

            padding: 0 11px;

            color: var(--lqc-muted) !important;
            background: var(--lqc-panel-soft) !important;

            border: 1px solid var(--lqc-border);
            border-radius: 999px;

            font-size: 10px;
            font-weight: 850;
            line-height: 1;
        }

        .lqc-question-count strong {
            margin-right: 4px;

            color: var(--lqc-text) !important;
        }


        /* ============================================================
         | AUDIO
         * ============================================================ */

        .lqc-audio-wrap {
            padding: 13px 18px 16px;

            background: var(--lqc-panel-soft) !important;

            border-top: 1px solid var(--lqc-border);
        }

        .lqc-audio-head {
            display: flex;

            align-items: center;
            justify-content: space-between;

            gap: 14px;

            margin-bottom: 9px;
        }

        .lqc-audio-title {
            margin: 0;

            color: var(--lqc-text) !important;

            font-size: 11px;
            font-weight: 850;
        }

        .lqc-audio-hint {
            color: var(--lqc-muted) !important;

            font-size: 9px;
            font-weight: 650;
        }

        .lqc-audio {
            display: block;

            width: 100% !important;
            max-width: 100% !important;

            height: 40px !important;
        }

        .dark .lqc-audio {
            color-scheme: dark;
        }


        /* ============================================================
         | QUESTIONS STACK
         * ============================================================ */

        .lqc-questions {
            display: grid;

            gap: 18px;
        }


        /* ============================================================
         | QUESTION CARD
         * ============================================================ */

        .lqc-question-card {
            overflow: hidden;

            background: var(--lqc-panel) !important;

            border: 1px solid var(--lqc-border);
            border-radius: 15px;

            transition:
                border-color .15s ease,
                box-shadow .15s ease;
        }

        .lqc-question-card:focus-within {
            border-color: var(--lqc-border-strong);
        }

        .lqc-question-head {
            display: flex;

            align-items: center;
            justify-content: space-between;

            gap: 18px;

            padding: 15px 18px;

            background: var(--lqc-panel-soft) !important;

            border-bottom: 1px solid var(--lqc-border);
        }

        .lqc-question-heading {
            display: flex;

            min-width: 0;

            align-items: center;

            gap: 10px;
        }

        .lqc-number {
            display: inline-flex;

            flex: 0 0 auto;

            width: 30px;
            height: 30px;

            align-items: center;
            justify-content: center;

            color: var(--lqc-blue) !important;
            background: var(--lqc-blue-soft) !important;

            border: 1px solid var(--lqc-blue-border);
            border-radius: 9px;

            font-size: 10px;
            font-weight: 900;
        }

        .lqc-question-title-wrap {
            min-width: 0;
        }

        .lqc-question-title {
            margin: 0;

            color: var(--lqc-text) !important;

            font-size: 13px;
            font-weight: 900;
            line-height: 1.3;
        }

        .lqc-question-subtitle {
            margin: 3px 0 0;

            color: var(--lqc-muted) !important;

            font-size: 9px;
            line-height: 1.4;
        }


        /* ============================================================
         | QUESTION LAYOUT
         * ============================================================ */

        .lqc-question-layout {
            display: grid;

            grid-template-columns:
                minmax(0, 1fr)
                300px;

            align-items: stretch;
        }

        .lqc-question-main {
            min-width: 0;

            padding: 20px;
        }

        .lqc-question-settings {
            min-width: 0;

            padding: 20px;

            background: var(--lqc-panel-soft) !important;

            border-left: 1px solid var(--lqc-border);
        }

        .lqc-settings-title {
            margin: 0 0 16px;

            color: var(--lqc-text) !important;

            font-size: 11px;
            font-weight: 900;
        }

        .lqc-settings-divider {
            height: 1px;

            margin: 17px 0;

            background: var(--lqc-border);
        }


        /* ============================================================
         | SECTION HEADING
         * ============================================================ */

        .lqc-section-head {
            margin-bottom: 14px;
        }

        .lqc-section-kicker {
            margin: 0 0 4px;

            color: var(--lqc-blue) !important;

            font-size: 8px;
            font-weight: 900;
            line-height: 1;

            letter-spacing: .11em;
            text-transform: uppercase;
        }

        .lqc-section-title {
            margin: 0;

            color: var(--lqc-text) !important;

            font-size: 12px;
            font-weight: 900;
            line-height: 1.4;
        }

        .lqc-section-description {
            margin: 4px 0 0;

            color: var(--lqc-muted) !important;

            font-size: 9px;
            line-height: 1.55;
        }


        /* ============================================================
         | FIELD
         * ============================================================ */

        .lqc-field {
            min-width: 0;
        }

        .lqc-field + .lqc-field {
            margin-top: 16px;
        }

        .lqc-label-row {
            display: flex;

            align-items: center;
            justify-content: space-between;

            gap: 10px;

            margin-bottom: 7px;
        }

        .lqc-label {
            margin: 0;

            color: var(--lqc-text) !important;

            font-size: 10px;
            font-weight: 850;
            line-height: 1.4;
        }

        .lqc-required {
            color: #ef4444 !important;
        }

        .lqc-optional {
            color: var(--lqc-muted) !important;

            font-size: 8px;
            font-weight: 750;

            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .lqc-helper {
            margin: 6px 0 0;

            color: var(--lqc-muted) !important;

            font-size: 8px;
            line-height: 1.55;
        }

        .lqc-field-error {
            margin: 6px 0 0;

            color: var(--lqc-red) !important;

            font-size: 9px;
            font-weight: 750;
            line-height: 1.5;
        }


        /* ============================================================
         | INPUTS
         * ============================================================ */

        .lqc-input,
        .lqc-select,
        .lqc-textarea {
            display: block !important;

            width: 100% !important;

            color: var(--lqc-text) !important;
            background: var(--lqc-input) !important;

            border: 1px solid var(--lqc-border-strong) !important;
            border-radius: 9px !important;

            font-family: inherit !important;
            font-size: 11px !important;
            font-weight: 600 !important;
            line-height: 1.55 !important;

            outline: none !important;
            box-shadow: none !important;

            transition:
                border-color .15s ease,
                box-shadow .15s ease,
                background-color .15s ease !important;
        }

        .lqc-input,
        .lqc-select {
            height: 40px !important;
            min-height: 40px !important;

            padding: 0 11px !important;
        }

        .lqc-textarea {
            min-height: 92px !important;

            padding: 10px 11px !important;

            resize: vertical;
        }

        .lqc-question-input {
            min-height: 110px !important;
        }

        .lqc-input::placeholder,
        .lqc-textarea::placeholder {
            color: var(--lqc-muted-soft) !important;

            opacity: 1;
        }

        .lqc-input:focus,
        .lqc-select:focus,
        .lqc-textarea:focus {
            border-color: #3b82f6 !important;

            box-shadow:
                0 0 0 3px rgba(59, 130, 246, .10) !important;
        }

        .lqc-control-error {
            border-color: #ef4444 !important;
        }

        .lqc-select option {
            color: var(--lqc-text);
            background: var(--lqc-input);
        }


        /* ============================================================
         | OPTIONS
         * ============================================================ */

        .lqc-options-section {
            margin-top: 20px;
        }

        .lqc-options-grid {
            display: grid;

            grid-template-columns:
                repeat(2, minmax(0, 1fr));

            gap: 11px;
        }

        .lqc-option {
            min-width: 0;

            padding: 11px;

            background: var(--lqc-panel-soft) !important;

            border: 1px solid var(--lqc-border);
            border-radius: 10px;

            transition:
                background-color .15s ease,
                border-color .15s ease;
        }

        .lqc-option:hover {
            border-color: var(--lqc-border-strong);
        }

        .lqc-option:focus-within {
            background: var(--lqc-panel) !important;
            border-color: var(--lqc-blue-border);
        }

        .lqc-option.is-correct {
            background: var(--lqc-green-soft) !important;
            border-color: var(--lqc-green-border);
        }

        .lqc-option.option-e {
            grid-column: 1 / -1;
        }

        .lqc-option-head {
            display: flex;

            align-items: center;
            justify-content: space-between;

            gap: 8px;

            margin-bottom: 7px;
        }

        .lqc-option-left {
            display: flex;

            min-width: 0;

            align-items: center;

            gap: 7px;
        }

        .lqc-letter {
            display: inline-flex;

            flex: 0 0 auto;

            width: 25px;
            height: 25px;

            align-items: center;
            justify-content: center;

            color: var(--lqc-blue) !important;
            background: var(--lqc-blue-soft) !important;

            border: 1px solid var(--lqc-blue-border);
            border-radius: 7px;

            font-size: 9px;
            font-weight: 900;
        }

        .lqc-option.is-correct .lqc-letter {
            color: var(--lqc-green) !important;
            background: var(--lqc-green-soft) !important;

            border-color: var(--lqc-green-border);
        }

        .lqc-option-name {
            color: var(--lqc-text) !important;

            font-size: 9px;
            font-weight: 850;
        }

        .lqc-option-right {
            display: flex;

            align-items: center;

            gap: 5px;
        }

        .lqc-option-meta {
            color: var(--lqc-muted) !important;

            font-size: 7px;
            font-weight: 800;
            line-height: 1;

            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .lqc-option-meta.required {
            color: var(--lqc-red) !important;
        }

        .lqc-correct-indicator {
            display: none;

            min-height: 18px;

            align-items: center;
            justify-content: center;

            padding: 0 6px;

            color: var(--lqc-green) !important;
            background: var(--lqc-green-soft) !important;

            border: 1px solid var(--lqc-green-border);
            border-radius: 999px;

            font-size: 7px;
            font-weight: 900;
            line-height: 1;

            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .lqc-option.is-correct .lqc-correct-indicator {
            display: inline-flex;
        }

        .lqc-option .lqc-input {
            height: 38px !important;
            min-height: 38px !important;
        }


        /* ============================================================
         | FOOTER ACTION BAR
         * ============================================================ */

        .lqc-actions {
            display: flex;

            align-items: center;
            justify-content: space-between;

            gap: 16px;

            margin-top: 20px;

            padding: 14px;

            background: var(--lqc-panel) !important;

            border: 1px solid var(--lqc-border);
            border-radius: 14px;
        }

        .lqc-actions-left,
        .lqc-actions-right {
            display: flex;

            align-items: center;

            gap: 9px;
        }

        .lqc-actions-copy {
            color: var(--lqc-muted) !important;

            font-size: 9px;
            line-height: 1.5;
        }


        /* ============================================================
         | RESPONSIVE
         * ============================================================ */

        @media (max-width: 1050px) {
            .lqc-question-layout {
                grid-template-columns:
                    minmax(0, 1fr)
                    270px;
            }
        }

        @media (max-width: 850px) {
            .lqc-question-layout {
                grid-template-columns: 1fr;
            }

            .lqc-question-settings {
                border-top: 1px solid var(--lqc-border);
                border-left: 0;
            }

            .lqc-question-settings {
                display: grid;

                grid-template-columns:
                    repeat(3, minmax(0, 1fr));

                gap: 14px;
            }

            .lqc-question-settings .lqc-settings-title {
                grid-column: 1 / -1;
                margin-bottom: 0;
            }

            .lqc-question-settings .lqc-field + .lqc-field {
                margin-top: 0;
            }

            .lqc-settings-divider {
                display: none;
            }
        }

        @media (max-width: 700px) {
            .lqc-header {
                flex-direction: column;

                align-items: stretch;
            }

            .lqc-header > .lqc-btn {
                width: 100% !important;
            }

            .lqc-material-main {
                flex-direction: column;

                align-items: flex-start;
            }

            .lqc-audio-head {
                flex-direction: column;

                align-items: flex-start;

                gap: 3px;
            }

            .lqc-options-grid {
                grid-template-columns: 1fr;
            }

            .lqc-option.option-e {
                grid-column: auto;
            }

            .lqc-question-settings {
                grid-template-columns: 1fr;
            }

            .lqc-actions {
                flex-direction: column;

                align-items: stretch;
            }

            .lqc-actions-left,
            .lqc-actions-right {
                width: 100%;
            }

            .lqc-actions-left .lqc-btn,
            .lqc-actions-right .lqc-btn {
                flex: 1 1 0;
            }
        }

        @media (max-width: 480px) {
            .lqc-question-head {
                align-items: flex-start;
            }

            .lqc-question-main,
            .lqc-question-settings {
                padding: 15px;
            }

            .lqc-option {
                padding: 10px;
            }

            .lqc-option-head {
                align-items: flex-start;
            }

            .lqc-option-right {
                align-items: flex-end;
                flex-direction: column;
            }

            .lqc-actions-left,
            .lqc-actions-right {
                flex-direction: column;
            }

            .lqc-actions-left .lqc-btn,
            .lqc-actions-right .lqc-btn {
                width: 100% !important;
            }
        }
    </style>


    <div class="lqc-page mx-auto w-full max-w-[1350px]">

        {{-- ========================================================= --}}
        {{-- HEADER --}}
        {{-- ========================================================= --}}
        <header class="lqc-header">

            <div class="lqc-header-copy">

                <p class="lqc-eyebrow">
                    Listening Management
                </p>

                <h1 class="lqc-title">
                    Add Listening Questions
                </h1>

                <p class="lqc-subtitle">
                    Create one or more Listening questions for
                    <strong>{{ $contextTitle }}</strong>.
                    All questions will use the same material audio.
                </p>

            </div>


            <a
                href="{{ $backRoute }}"
                class="lqc-btn lqc-btn-secondary">

                ← Back to Questions

            </a>

        </header>


        {{-- ========================================================= --}}
        {{-- ERRORS --}}
        {{-- ========================================================= --}}
        @if ($errors->any())

            <div class="lqc-errors">

                <p class="lqc-errors-title">
                    Please correct the following errors:
                </p>

                <ul class="lqc-errors-list">

                    @foreach ($errors->all() as $error)

                        <li>
                            {{ $error }}
                        </li>

                    @endforeach

                </ul>

            </div>

        @endif


        {{-- ========================================================= --}}
        {{-- MATERIAL INFORMATION --}}
        {{-- ========================================================= --}}
        <section class="lqc-material">

            <div class="lqc-material-main">

                <div class="lqc-material-copy">

                    <p class="lqc-material-label">
                        Listening Material
                    </p>

                    <h2 class="lqc-material-title">
                        {{ $contextTitle }}
                    </h2>

                </div>


                <div
                    id="questionCountBadge"
                    class="lqc-question-count">

                    <strong>
                        {{ count($oldQuestions) }}
                    </strong>

                    {{ count($oldQuestions) === 1 ? 'Question' : 'Questions' }}

                </div>

            </div>


            @if (!empty($material->audio_file))

                <div class="lqc-audio-wrap">

                    <div class="lqc-audio-head">

                        <p class="lqc-audio-title">
                            Shared Material Audio
                        </p>

                        <span class="lqc-audio-hint">
                            All questions below use this audio
                        </span>

                    </div>


                    <audio
                        controls
                        preload="metadata"
                        class="lqc-audio">

                        <source
                            src="{{ asset(
                                'storage/' . $material->audio_file
                            ) }}">

                        Your browser does not support audio playback.

                    </audio>

                </div>

            @endif

        </section>


        {{-- ========================================================= --}}
        {{-- FORM --}}
        {{-- ========================================================= --}}
        <form
            action="{{ route(
                'admin.listening-questions.store',
                $material->id
            ) }}"
            method="POST">

            @csrf


            {{-- ===================================================== --}}
            {{-- QUESTION CARDS --}}
            {{-- ===================================================== --}}
            <div
                id="questionsContainer"
                class="lqc-questions">


                @foreach ($oldQuestions as $index => $row)

                    @php
                        $selectedAnswer =
                            strtoupper(
                                $row['correct_answer']
                                ?? 'A'
                            );
                    @endphp


                    <section
                        class="question-card lqc-question-card"
                        data-question-card>


                        {{-- ========================================= --}}
                        {{-- QUESTION HEADER --}}
                        {{-- ========================================= --}}
                        <div class="lqc-question-head">

                            <div class="lqc-question-heading">

                                <span
                                    class="lqc-number question-number">

                                    {{ $loop->iteration }}

                                </span>


                                <div class="lqc-question-title-wrap">

                                    <h2 class="lqc-question-title">
                                        Question #{{ $loop->iteration }}
                                    </h2>

                                    <p class="lqc-question-subtitle">
                                        Configure content, options, and assessment settings.
                                    </p>

                                </div>

                            </div>


                            <button
                                type="button"
                                onclick="removeQuestionCard(this)"
                                class="remove-question-button lqc-btn lqc-btn-remove">

                                Remove

                            </button>

                        </div>


                        {{-- ========================================= --}}
                        {{-- QUESTION BODY --}}
                        {{-- ========================================= --}}
                        <div class="lqc-question-layout">


                            {{-- ===================================== --}}
                            {{-- MAIN CONTENT --}}
                            {{-- ===================================== --}}
                            <div class="lqc-question-main">

                                {{-- CONTENT HEADING --}}
                                <div class="lqc-section-head">

                                    <p class="lqc-section-kicker">
                                        Content
                                    </p>

                                    <h3 class="lqc-section-title">
                                        Question Content
                                    </h3>

                                    <p class="lqc-section-description">
                                        Add an optional instruction and the question students will answer.
                                    </p>

                                </div>


                                {{-- INSTRUCTION --}}
                                <div class="lqc-field">

                                    <div class="lqc-label-row">

                                        <label class="lqc-label">
                                            Instruction
                                        </label>

                                        <span class="lqc-optional">
                                            Optional
                                        </span>

                                    </div>


                                    <input
                                        type="text"
                                        name="questions[{{ $index }}][instruction]"
                                        value="{{ $row['instruction'] ?? '' }}"
                                        placeholder="Example: Listen to the dialogue and choose the correct answer..."
                                        class="lqc-input @error("questions.$index.instruction") lqc-control-error @enderror">


                                    <p class="lqc-helper">
                                        Use an instruction only when students need additional context.
                                    </p>


                                    @error("questions.$index.instruction")

                                        <p class="lqc-field-error">
                                            {{ $message }}
                                        </p>

                                    @enderror

                                </div>


                                {{-- QUESTION --}}
                                <div class="lqc-field">

                                    <div class="lqc-label-row">

                                        <label class="lqc-label">
                                            Question
                                            <span class="lqc-required">*</span>
                                        </label>

                                    </div>


                                    <textarea
                                        name="questions[{{ $index }}][question]"
                                        required
                                        rows="4"
                                        placeholder="Enter the Listening question..."
                                        class="lqc-textarea lqc-question-input @error("questions.$index.question") lqc-control-error @enderror">{{ $row['question'] ?? '' }}</textarea>


                                    @error("questions.$index.question")

                                        <p class="lqc-field-error">
                                            {{ $message }}
                                        </p>

                                    @enderror

                                </div>


                                {{-- ================================= --}}
                                {{-- ANSWER OPTIONS --}}
                                {{-- ================================= --}}
                                <div class="lqc-options-section">

                                    <div class="lqc-section-head">

                                        <p class="lqc-section-kicker">
                                            Multiple Choice
                                        </p>

                                        <h3 class="lqc-section-title">
                                            Answer Options
                                        </h3>

                                        <p class="lqc-section-description">
                                            Options A and B are required. Options C–E are optional.
                                        </p>

                                    </div>


                                    <div class="lqc-options-grid">

                                        @foreach (['a', 'b', 'c', 'd', 'e'] as $option)

                                            @php
                                                $field =
                                                    'option_' . $option;

                                                $label =
                                                    strtoupper($option);

                                                $required =
                                                    in_array(
                                                        $option,
                                                        ['a', 'b']
                                                    );

                                                $isCorrect =
                                                    $selectedAnswer === $label;
                                            @endphp


                                            <div
                                                class="lqc-option
                                                       {{ $option === 'e' ? 'option-e' : '' }}
                                                       {{ $isCorrect ? 'is-correct' : '' }}"
                                                data-option-card="{{ $label }}">


                                                <div class="lqc-option-head">

                                                    <div class="lqc-option-left">

                                                        <span class="lqc-letter">
                                                            {{ $label }}
                                                        </span>

                                                        <span class="lqc-option-name">
                                                            Option {{ $label }}
                                                        </span>

                                                    </div>


                                                    <div class="lqc-option-right">

                                                        <span class="lqc-correct-indicator">
                                                            Correct
                                                        </span>


                                                        @if ($required)

                                                            <span
                                                                class="lqc-option-meta required">

                                                                Required

                                                            </span>

                                                        @else

                                                            <span class="lqc-option-meta">
                                                                Optional
                                                            </span>

                                                        @endif

                                                    </div>

                                                </div>


                                                <input
                                                    type="text"
                                                    name="questions[{{ $index }}][{{ $field }}]"
                                                    value="{{ $row[$field] ?? '' }}"
                                                    {{ $required ? 'required' : '' }}
                                                    placeholder="Enter option {{ $label }}"
                                                    class="lqc-input @error("questions.$index.$field") lqc-control-error @enderror">


                                                @error("questions.$index.$field")

                                                    <p class="lqc-field-error">
                                                        {{ $message }}
                                                    </p>

                                                @enderror

                                            </div>

                                        @endforeach

                                    </div>

                                </div>

                            </div>


                            {{-- ===================================== --}}
                            {{-- SETTINGS --}}
                            {{-- ===================================== --}}
                            <aside class="lqc-question-settings">

                                <h3 class="lqc-settings-title">
                                    Question Settings
                                </h3>


                                {{-- SUB-SKILL --}}
                                <div class="lqc-field">

                                    <div class="lqc-label-row">

                                        <label class="lqc-label">
                                            Sub-Skill
                                            <span class="lqc-required">*</span>
                                        </label>

                                    </div>


                                    <select
                                        name="questions[{{ $index }}][sub_skill]"
                                        required
                                        class="lqc-select @error("questions.$index.sub_skill") lqc-control-error @enderror">

                                        <option value="">
                                            Select sub-skill
                                        </option>


                                        @foreach ($subSkillOptions as $value => $label)

                                            <option
                                                value="{{ $value }}"
                                                @selected(
                                                    ($row['sub_skill'] ?? '')
                                                    === $value
                                                )>

                                                {{ $label }}

                                            </option>

                                        @endforeach

                                    </select>


                                    <p class="lqc-helper">
                                        Used for Listening sub-skill performance.
                                    </p>


                                    @error("questions.$index.sub_skill")

                                        <p class="lqc-field-error">
                                            {{ $message }}
                                        </p>

                                    @enderror

                                </div>


                                <div class="lqc-settings-divider"></div>


                                {{-- CORRECT ANSWER --}}
                                <div class="lqc-field">

                                    <div class="lqc-label-row">

                                        <label class="lqc-label">
                                            Correct Answer
                                            <span class="lqc-required">*</span>
                                        </label>

                                    </div>


                                    <select
                                        name="questions[{{ $index }}][correct_answer]"
                                        required
                                        class="lqc-select lqc-correct-select @error("questions.$index.correct_answer") lqc-control-error @enderror">

                                        @foreach (['A', 'B', 'C', 'D', 'E'] as $option)

                                            <option
                                                value="{{ $option }}"
                                                @selected(
                                                    $selectedAnswer === $option
                                                )>

                                                Option {{ $option }}

                                            </option>

                                        @endforeach

                                    </select>


                                    <p class="lqc-helper">
                                        Selected option is highlighted in the answer list.
                                    </p>


                                    @error("questions.$index.correct_answer")

                                        <p class="lqc-field-error">
                                            {{ $message }}
                                        </p>

                                    @enderror

                                </div>


                                <div class="lqc-settings-divider"></div>


                                {{-- SCORE --}}
                                <div class="lqc-field">

                                    <div class="lqc-label-row">

                                        <label class="lqc-label">
                                            Score / Points
                                            <span class="lqc-required">*</span>
                                        </label>

                                    </div>


                                    <input
                                        type="number"
                                        name="questions[{{ $index }}][score]"
                                        value="{{ $row['score'] ?? 10 }}"
                                        min="1"
                                        max="100"
                                        required
                                        class="lqc-input @error("questions.$index.score") lqc-control-error @enderror">


                                    <p class="lqc-helper">
                                        Accepted range: 1–100.
                                    </p>


                                    @error("questions.$index.score")

                                        <p class="lqc-field-error">
                                            {{ $message }}
                                        </p>

                                    @enderror

                                </div>

                            </aside>

                        </div>

                    </section>

                @endforeach

            </div>


            {{-- ===================================================== --}}
            {{-- PAGE ACTIONS --}}
            {{-- ===================================================== --}}
            <div class="lqc-actions">

                <div class="lqc-actions-left">

                    <button
                        type="button"
                        onclick="addQuestionCard()"
                        class="lqc-btn lqc-btn-add">

                        + Add Another Question

                    </button>

                </div>


                <div class="lqc-actions-right">

                    <a
                        href="{{ $backRoute }}"
                        class="lqc-btn lqc-btn-secondary">

                        Cancel

                    </a>


                    <button
                        type="submit"
                        class="lqc-btn lqc-btn-primary">

                        Save All Questions

                    </button>

                </div>

            </div>

        </form>

    </div>


    {{-- ============================================================= --}}
    {{-- JAVASCRIPT --}}
    {{-- ============================================================= --}}
    <script>
        const listeningSubSkillOptions = @json($subSkillOptions);

        let questionIndex = {{ count($oldQuestions) }};


        /*
        |--------------------------------------------------------------------------
        | Escape HTML
        |--------------------------------------------------------------------------
        */

        function escapeHtml(value) {
            return String(value)
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }


        /*
        |--------------------------------------------------------------------------
        | Sub-skill options
        |--------------------------------------------------------------------------
        */

        function subSkillOptionsHtml() {
            let options =
                '<option value="">Select sub-skill</option>';


            Object.entries(
                listeningSubSkillOptions
            ).forEach(function ([value, label]) {

                options += `
                    <option value="${escapeHtml(value)}">
                        ${escapeHtml(label)}
                    </option>
                `;

            });


            return options;
        }


        /*
        |--------------------------------------------------------------------------
        | New question template
        |--------------------------------------------------------------------------
        */

        function buildQuestionCard(index) {
            return `
                <section
                    class="question-card lqc-question-card"
                    data-question-card>

                    <div class="lqc-question-head">

                        <div class="lqc-question-heading">

                            <span class="lqc-number question-number">
                                1
                            </span>

                            <div class="lqc-question-title-wrap">

                                <h2 class="lqc-question-title">
                                    Question
                                </h2>

                                <p class="lqc-question-subtitle">
                                    Configure content, options, and assessment settings.
                                </p>

                            </div>

                        </div>


                        <button
                            type="button"
                            onclick="removeQuestionCard(this)"
                            class="remove-question-button lqc-btn lqc-btn-remove">

                            Remove

                        </button>

                    </div>


                    <div class="lqc-question-layout">

                        <div class="lqc-question-main">

                            <div class="lqc-section-head">

                                <p class="lqc-section-kicker">
                                    Content
                                </p>

                                <h3 class="lqc-section-title">
                                    Question Content
                                </h3>

                                <p class="lqc-section-description">
                                    Add an optional instruction and the question students will answer.
                                </p>

                            </div>


                            <div class="lqc-field">

                                <div class="lqc-label-row">

                                    <label class="lqc-label">
                                        Instruction
                                    </label>

                                    <span class="lqc-optional">
                                        Optional
                                    </span>

                                </div>


                                <input
                                    type="text"
                                    name="questions[${index}][instruction]"
                                    placeholder="Example: Listen to the dialogue and choose the correct answer..."
                                    class="lqc-input">


                                <p class="lqc-helper">
                                    Use an instruction only when students need additional context.
                                </p>

                            </div>


                            <div class="lqc-field">

                                <div class="lqc-label-row">

                                    <label class="lqc-label">
                                        Question
                                        <span class="lqc-required">*</span>
                                    </label>

                                </div>


                                <textarea
                                    name="questions[${index}][question]"
                                    required
                                    rows="4"
                                    placeholder="Enter the Listening question..."
                                    class="lqc-textarea lqc-question-input"></textarea>

                            </div>


                            <div class="lqc-options-section">

                                <div class="lqc-section-head">

                                    <p class="lqc-section-kicker">
                                        Multiple Choice
                                    </p>

                                    <h3 class="lqc-section-title">
                                        Answer Options
                                    </h3>

                                    <p class="lqc-section-description">
                                        Options A and B are required. Options C–E are optional.
                                    </p>

                                </div>


                                <div class="lqc-options-grid">

                                    ${buildOptionHtml(index, 'A', true)}

                                    ${buildOptionHtml(index, 'B', true)}

                                    ${buildOptionHtml(index, 'C', false)}

                                    ${buildOptionHtml(index, 'D', false)}

                                    ${buildOptionHtml(index, 'E', false, true)}

                                </div>

                            </div>

                        </div>


                        <aside class="lqc-question-settings">

                            <h3 class="lqc-settings-title">
                                Question Settings
                            </h3>


                            <div class="lqc-field">

                                <div class="lqc-label-row">

                                    <label class="lqc-label">
                                        Sub-Skill
                                        <span class="lqc-required">*</span>
                                    </label>

                                </div>


                                <select
                                    name="questions[${index}][sub_skill]"
                                    required
                                    class="lqc-select">

                                    ${subSkillOptionsHtml()}

                                </select>


                                <p class="lqc-helper">
                                    Used for Listening sub-skill performance.
                                </p>

                            </div>


                            <div class="lqc-settings-divider"></div>


                            <div class="lqc-field">

                                <div class="lqc-label-row">

                                    <label class="lqc-label">
                                        Correct Answer
                                        <span class="lqc-required">*</span>
                                    </label>

                                </div>


                                <select
                                    name="questions[${index}][correct_answer]"
                                    required
                                    class="lqc-select lqc-correct-select">

                                    <option value="A" selected>
                                        Option A
                                    </option>

                                    <option value="B">
                                        Option B
                                    </option>

                                    <option value="C">
                                        Option C
                                    </option>

                                    <option value="D">
                                        Option D
                                    </option>

                                    <option value="E">
                                        Option E
                                    </option>

                                </select>


                                <p class="lqc-helper">
                                    Selected option is highlighted in the answer list.
                                </p>

                            </div>


                            <div class="lqc-settings-divider"></div>


                            <div class="lqc-field">

                                <div class="lqc-label-row">

                                    <label class="lqc-label">
                                        Score / Points
                                        <span class="lqc-required">*</span>
                                    </label>

                                </div>


                                <input
                                    type="number"
                                    name="questions[${index}][score]"
                                    value="10"
                                    min="1"
                                    max="100"
                                    required
                                    class="lqc-input">


                                <p class="lqc-helper">
                                    Accepted range: 1–100.
                                </p>

                            </div>

                        </aside>

                    </div>

                </section>
            `;
        }


        /*
        |--------------------------------------------------------------------------
        | Option template
        |--------------------------------------------------------------------------
        */

        function buildOptionHtml(
            index,
            letter,
            required,
            fullWidth = false
        ) {
            const lower =
                letter.toLowerCase();

            const requiredAttribute =
                required ? 'required' : '';

            const requirementLabel =
                required ? 'Required' : 'Optional';

            const requirementClass =
                required
                    ? 'lqc-option-meta required'
                    : 'lqc-option-meta';

            const correctClass =
                letter === 'A'
                    ? 'is-correct'
                    : '';

            const fullWidthClass =
                fullWidth
                    ? 'option-e'
                    : '';

            return `
                <div
                    class="lqc-option ${fullWidthClass} ${correctClass}"
                    data-option-card="${letter}">

                    <div class="lqc-option-head">

                        <div class="lqc-option-left">

                            <span class="lqc-letter">
                                ${letter}
                            </span>

                            <span class="lqc-option-name">
                                Option ${letter}
                            </span>

                        </div>


                        <div class="lqc-option-right">

                            <span class="lqc-correct-indicator">
                                Correct
                            </span>

                            <span class="${requirementClass}">
                                ${requirementLabel}
                            </span>

                        </div>

                    </div>


                    <input
                        type="text"
                        name="questions[${index}][option_${lower}]"
                        ${requiredAttribute}
                        placeholder="Enter option ${letter}"
                        class="lqc-input">

                </div>
            `;
        }


        /*
        |--------------------------------------------------------------------------
        | Add question
        |--------------------------------------------------------------------------
        */

        function addQuestionCard() {
            const container =
                document.getElementById(
                    'questionsContainer'
                );

            if (!container) {
                return;
            }


            const html =
                buildQuestionCard(
                    questionIndex
                );


            container.insertAdjacentHTML(
                'beforeend',
                html
            );


            const cards =
                container.querySelectorAll(
                    '[data-question-card]'
                );

            const newCard =
                cards[cards.length - 1];


            if (newCard) {
                initializeQuestionCard(
                    newCard
                );

                newCard.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }


            questionIndex++;

            updateQuestionTitles();
        }


        /*
        |--------------------------------------------------------------------------
        | Remove question
        |--------------------------------------------------------------------------
        */

        function removeQuestionCard(button) {
            const cards =
                document.querySelectorAll(
                    '[data-question-card]'
                );


            /*
             * Minimal satu question harus tetap ada.
             */
            if (cards.length <= 1) {
                return;
            }


            const card =
                button.closest(
                    '[data-question-card]'
                );

            if (!card) {
                return;
            }


            card.remove();

            updateQuestionTitles();
        }


        /*
        |--------------------------------------------------------------------------
        | Correct answer highlight
        |--------------------------------------------------------------------------
        */

        function updateCorrectAnswerHighlight(card) {
            if (!card) {
                return;
            }


            const select =
                card.querySelector(
                    '.lqc-correct-select'
                );

            const optionCards =
                card.querySelectorAll(
                    '[data-option-card]'
                );


            if (!select || !optionCards.length) {
                return;
            }


            const selected =
                (select.value || '')
                .toUpperCase();


            optionCards.forEach(
                function (optionCard) {

                    const option =
                        (
                            optionCard.dataset.optionCard
                            || ''
                        ).toUpperCase();


                    if (
                        selected !== '' &&
                        option === selected
                    ) {
                        optionCard.classList.add(
                            'is-correct'
                        );
                    } else {
                        optionCard.classList.remove(
                            'is-correct'
                        );
                    }

                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Initialize single card
        |--------------------------------------------------------------------------
        */

        function initializeQuestionCard(card) {
            if (!card) {
                return;
            }


            const correctAnswerSelect =
                card.querySelector(
                    '.lqc-correct-select'
                );


            if (correctAnswerSelect) {

                correctAnswerSelect.addEventListener(
                    'change',
                    function () {
                        updateCorrectAnswerHighlight(
                            card
                        );
                    }
                );

            }


            updateCorrectAnswerHighlight(
                card
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Update numbering / remove buttons / count
        |--------------------------------------------------------------------------
        */

        function updateQuestionTitles() {
            const cards =
                document.querySelectorAll(
                    '[data-question-card]'
                );


            cards.forEach(
                function (card, index) {

                    const title =
                        card.querySelector(
                            '.lqc-question-title'
                        );

                    const number =
                        card.querySelector(
                            '.question-number'
                        );


                    if (title) {
                        title.textContent =
                            `Question #${index + 1}`;
                    }


                    if (number) {
                        number.textContent =
                            index + 1;
                    }

                }
            );


            /*
             * Remove hanya ditampilkan jika question > 1.
             */
            const removeButtons =
                document.querySelectorAll(
                    '.remove-question-button'
                );


            removeButtons.forEach(
                function (button) {

                    button.style.display =
                        cards.length > 1
                            ? 'inline-flex'
                            : 'none';

                }
            );


            /*
             * Update badge jumlah question.
             */
            const badge =
                document.getElementById(
                    'questionCountBadge'
                );


            if (badge) {
                const label =
                    cards.length === 1
                        ? 'Question'
                        : 'Questions';


                badge.innerHTML =
                    `<strong>${cards.length}</strong>${label}`;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | On load
        |--------------------------------------------------------------------------
        */

        document.addEventListener(
            'DOMContentLoaded',
            function () {

                document
                    .querySelectorAll(
                        '[data-question-card]'
                    )
                    .forEach(
                        function (card) {

                            initializeQuestionCard(
                                card
                            );

                        }
                    );


                updateQuestionTitles();

            }
        );
    </script>
@endsection