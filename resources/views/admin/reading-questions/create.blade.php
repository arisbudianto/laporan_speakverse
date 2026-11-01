@extends('layouts.admin')

@section('content')
    @php
        $contextTitle = $material->title ?? 'Reading Material';

        $backRoute = route(
            'admin.reading-questions.index',
            $material->id
        );

        $selectedCorrectAnswer = old('correct_answer');
        $selectedSubSkill = old('sub_skill');
    @endphp


    <style>
        /*
        |--------------------------------------------------------------------------
        | READING QUESTION CREATE
        |--------------------------------------------------------------------------
        |
        | Semua CSS dibuat scoped dengan prefix "rqc-" supaya tidak bentrok
        | dengan CSS global dari layouts.admin.
        |
        */

        .rqc-page {
            --rqc-text: #0f172a;
            --rqc-text-soft: #334155;
            --rqc-muted: #64748b;
            --rqc-muted-soft: #94a3b8;

            --rqc-panel: #ffffff;
            --rqc-panel-soft: #f8fafc;
            --rqc-panel-hover: #f1f5f9;
            --rqc-input: #ffffff;

            --rqc-border: #e2e8f0;
            --rqc-border-strong: #cbd5e1;

            --rqc-blue: #2563eb;
            --rqc-blue-hover: #1d4ed8;
            --rqc-blue-soft: #eff6ff;
            --rqc-blue-border: #bfdbfe;

            --rqc-green: #047857;
            --rqc-green-soft: #ecfdf5;
            --rqc-green-border: #a7f3d0;

            --rqc-amber: #b45309;
            --rqc-amber-soft: #fffbeb;
            --rqc-amber-border: #fde68a;

            --rqc-red: #dc2626;
            --rqc-red-soft: #fef2f2;
            --rqc-red-border: #fecaca;

            width: 100%;
            color: var(--rqc-text);
        }

        html.dark .rqc-page,
        body.dark .rqc-page,
        .dark .rqc-page,
        [data-theme="dark"] .rqc-page {
            --rqc-text: #f8fafc;
            --rqc-text-soft: #e2e8f0;
            --rqc-muted: #94a3b8;
            --rqc-muted-soft: #64748b;

            --rqc-panel: #1e293b;
            --rqc-panel-soft: #0f172a;
            --rqc-panel-hover: #263449;
            --rqc-input: #0f172a;

            --rqc-border: #334155;
            --rqc-border-strong: #475569;

            --rqc-blue: #60a5fa;
            --rqc-blue-hover: #93c5fd;
            --rqc-blue-soft: rgba(59, 130, 246, 0.10);
            --rqc-blue-border: rgba(96, 165, 250, 0.24);

            --rqc-green: #6ee7b7;
            --rqc-green-soft: rgba(16, 185, 129, 0.10);
            --rqc-green-border: rgba(110, 231, 183, 0.22);

            --rqc-amber: #fbbf24;
            --rqc-amber-soft: rgba(245, 158, 11, 0.10);
            --rqc-amber-border: rgba(251, 191, 36, 0.22);

            --rqc-red: #fca5a5;
            --rqc-red-soft: rgba(239, 68, 68, 0.10);
            --rqc-red-border: rgba(248, 113, 113, 0.22);
        }

        .rqc-page,
        .rqc-page * {
            box-sizing: border-box;
        }


        /* ==========================================================
         | PAGE HEADER
         * ========================================================== */

        .rqc-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;

            gap: 24px;

            margin-bottom: 24px;
        }

        .rqc-header-copy {
            min-width: 0;
        }

        .rqc-eyebrow {
            margin: 0 0 7px;

            color: var(--rqc-blue) !important;

            font-size: 11px;
            font-weight: 900;
            line-height: 1;

            letter-spacing: .13em;
            text-transform: uppercase;
        }

        .rqc-title {
            margin: 0;

            color: var(--rqc-text) !important;

            font-size: clamp(28px, 2.2vw, 36px);
            font-weight: 900;
            line-height: 1.12;

            letter-spacing: -.035em;
        }

        .rqc-subtitle {
            max-width: 720px;

            margin: 9px 0 0;

            color: var(--rqc-muted) !important;

            font-size: 13px;
            line-height: 1.65;
        }

        .rqc-subtitle strong {
            color: var(--rqc-blue) !important;
            font-weight: 800;
        }


        /* ==========================================================
         | BUTTONS
         * ========================================================== */

        .rqc-btn {
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

        .rqc-btn-secondary {
            color: var(--rqc-text) !important;
            background: var(--rqc-panel) !important;

            border: 1px solid var(--rqc-border) !important;
        }

        .rqc-btn-secondary:hover {
            background: var(--rqc-panel-hover) !important;
            border-color: var(--rqc-border-strong) !important;
        }

        .rqc-btn-primary {
            color: #ffffff !important;
            background: #2563eb !important;

            border: 1px solid #2563eb !important;
        }

        .rqc-btn-primary:hover {
            background: #1d4ed8 !important;
            border-color: #1d4ed8 !important;
        }


        /* ==========================================================
         | VALIDATION SUMMARY
         * ========================================================== */

        .rqc-errors {
            margin-bottom: 20px;

            padding: 15px 17px;

            color: var(--rqc-red) !important;
            background: var(--rqc-red-soft) !important;

            border: 1px solid var(--rqc-red-border);
            border-radius: 12px;
        }

        .rqc-errors-title {
            margin: 0;

            font-size: 13px;
            font-weight: 900;
        }

        .rqc-errors-list {
            margin: 8px 0 0;
            padding-left: 19px;

            font-size: 11px;
            font-weight: 650;
            line-height: 1.7;
        }


        /* ==========================================================
         | MAIN LAYOUT
         * ========================================================== */

        .rqc-layout {
            display: grid;

            grid-template-columns:
                minmax(0, 1fr)
                320px;

            gap: 20px;

            align-items: start;
        }

        .rqc-main {
            display: grid;

            min-width: 0;

            gap: 20px;
        }

        .rqc-sidebar {
            min-width: 0;
        }

        .rqc-sidebar-inner {
            position: sticky;
            top: 105px;

            display: grid;

            gap: 14px;
        }


        /* ==========================================================
         | PANEL
         * ========================================================== */

        .rqc-panel {
            overflow: hidden;

            background: var(--rqc-panel) !important;

            border: 1px solid var(--rqc-border);
            border-radius: 14px;
        }

        .rqc-panel-header {
            padding: 18px 20px;

            background: var(--rqc-panel) !important;

            border-bottom: 1px solid var(--rqc-border);
        }

        .rqc-panel-kicker {
            margin: 0 0 5px;

            color: var(--rqc-blue) !important;

            font-size: 9px;
            font-weight: 900;
            line-height: 1;

            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .rqc-panel-title {
            margin: 0;

            color: var(--rqc-text) !important;

            font-size: 15px;
            font-weight: 900;
            line-height: 1.35;
        }

        .rqc-panel-description {
            max-width: 680px;

            margin: 5px 0 0;

            color: var(--rqc-muted) !important;

            font-size: 11px;
            line-height: 1.6;
        }

        .rqc-panel-body {
            padding: 20px;

            background: var(--rqc-panel) !important;
        }


        /* ==========================================================
         | FORM FIELD
         * ========================================================== */

        .rqc-field {
            min-width: 0;
        }

        .rqc-field + .rqc-field {
            margin-top: 18px;
        }

        .rqc-label-row {
            display: flex;

            align-items: center;
            justify-content: space-between;

            gap: 10px;

            margin-bottom: 7px;
        }

        .rqc-label {
            margin: 0;

            color: var(--rqc-text) !important;

            font-size: 11px;
            font-weight: 850;
            line-height: 1.4;
        }

        .rqc-required {
            color: #ef4444 !important;
        }

        .rqc-optional {
            color: var(--rqc-muted) !important;

            font-size: 9px;
            font-weight: 750;

            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .rqc-helper {
            margin: 7px 0 0;

            color: var(--rqc-muted) !important;

            font-size: 9px;
            line-height: 1.55;
        }

        .rqc-error {
            margin: 7px 0 0;

            color: var(--rqc-red) !important;

            font-size: 10px;
            font-weight: 750;
            line-height: 1.5;
        }


        /* ==========================================================
         | CONTROLS
         * ========================================================== */

        .rqc-input,
        .rqc-select,
        .rqc-textarea {
            display: block !important;

            width: 100% !important;

            color: var(--rqc-text) !important;
            background: var(--rqc-input) !important;

            border: 1px solid var(--rqc-border-strong) !important;
            border-radius: 9px !important;

            font-family: inherit !important;
            font-size: 12px !important;
            font-weight: 600 !important;
            line-height: 1.55 !important;

            outline: none !important;
            box-shadow: none !important;

            transition:
                border-color .15s ease,
                box-shadow .15s ease,
                background-color .15s ease !important;
        }

        .rqc-input,
        .rqc-select {
            height: 42px !important;
            min-height: 42px !important;

            padding: 0 12px !important;
        }

        .rqc-textarea {
            min-height: 150px !important;

            padding: 12px !important;

            resize: vertical;
        }

        .rqc-input::placeholder,
        .rqc-textarea::placeholder {
            color: var(--rqc-muted-soft) !important;

            opacity: 1;
        }

        .rqc-input:focus,
        .rqc-select:focus,
        .rqc-textarea:focus {
            border-color: #3b82f6 !important;

            box-shadow:
                0 0 0 3px rgba(59, 130, 246, .11) !important;
        }

        .rqc-control-error {
            border-color: #ef4444 !important;
        }

        .rqc-select option {
            color: var(--rqc-text);
            background: var(--rqc-input);
        }


        /* ==========================================================
         | ANSWER OPTIONS
         * ========================================================== */

        .rqc-options-grid {
            display: grid;

            grid-template-columns:
                repeat(2, minmax(0, 1fr));

            gap: 13px;
        }

        .rqc-option {
            position: relative;

            min-width: 0;

            padding: 13px;

            background: var(--rqc-panel-soft) !important;

            border: 1px solid var(--rqc-border);
            border-radius: 11px;

            transition:
                background-color .15s ease,
                border-color .15s ease;
        }

        .rqc-option:hover {
            border-color: var(--rqc-border-strong);
        }

        .rqc-option:focus-within {
            background: var(--rqc-panel) !important;
            border-color: var(--rqc-blue-border);
        }

        .rqc-option.is-correct {
            background: var(--rqc-green-soft) !important;

            border-color: var(--rqc-green-border);
        }

        .rqc-option.option-e {
            grid-column: 1 / -1;
        }

        .rqc-option-heading {
            display: flex;

            align-items: center;
            justify-content: space-between;

            gap: 10px;

            margin-bottom: 9px;
        }

        .rqc-option-left {
            display: flex;

            min-width: 0;

            align-items: center;

            gap: 8px;
        }

        .rqc-letter {
            display: inline-flex;

            flex: 0 0 auto;

            width: 28px;
            height: 28px;

            align-items: center;
            justify-content: center;

            color: var(--rqc-blue) !important;
            background: var(--rqc-blue-soft) !important;

            border: 1px solid var(--rqc-blue-border);
            border-radius: 8px;

            font-size: 10px;
            font-weight: 900;
        }

        .rqc-option.is-correct .rqc-letter {
            color: var(--rqc-green) !important;
            background: var(--rqc-green-soft) !important;

            border-color: var(--rqc-green-border);
        }

        .rqc-option-name {
            color: var(--rqc-text) !important;

            font-size: 10px;
            font-weight: 850;
        }

        .rqc-option-right {
            display: flex;

            align-items: center;

            gap: 6px;
        }

        .rqc-option-meta {
            color: var(--rqc-muted) !important;

            font-size: 8px;
            font-weight: 800;
            line-height: 1;

            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .rqc-option-meta.required {
            color: var(--rqc-red) !important;
        }

        .rqc-correct-indicator {
            display: none;

            min-height: 20px;

            align-items: center;
            justify-content: center;

            padding: 0 7px;

            color: var(--rqc-green) !important;
            background: var(--rqc-green-soft) !important;

            border: 1px solid var(--rqc-green-border);
            border-radius: 999px;

            font-size: 8px;
            font-weight: 900;
            line-height: 1;

            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .rqc-option.is-correct .rqc-correct-indicator {
            display: inline-flex;
        }

        .rqc-option .rqc-input {
            height: 40px !important;
            min-height: 40px !important;
        }


        /* ==========================================================
         | MATERIAL CONTEXT
         * ========================================================== */

        .rqc-context {
            overflow: hidden;

            background: var(--rqc-panel) !important;

            border: 1px solid var(--rqc-border);
            border-radius: 14px;
        }

        .rqc-context-body {
            padding: 17px 18px;
        }

        .rqc-context-label {
            margin: 0 0 6px;

            color: var(--rqc-blue) !important;

            font-size: 9px;
            font-weight: 900;
            line-height: 1;

            letter-spacing: .11em;
            text-transform: uppercase;
        }

        .rqc-context-title {
            margin: 0;

            color: var(--rqc-text) !important;

            font-size: 14px;
            font-weight: 900;
            line-height: 1.45;

            overflow-wrap: anywhere;
        }

        .rqc-context-copy {
            margin: 7px 0 0;

            color: var(--rqc-muted) !important;

            font-size: 9px;
            line-height: 1.55;
        }


        /* ==========================================================
         | SETTINGS
         * ========================================================== */

        .rqc-settings {
            overflow: hidden;

            background: var(--rqc-panel) !important;

            border: 1px solid var(--rqc-border);
            border-radius: 14px;
        }

        .rqc-settings-head {
            padding: 17px 18px;

            border-bottom: 1px solid var(--rqc-border);
        }

        .rqc-settings-title {
            margin: 0;

            color: var(--rqc-text) !important;

            font-size: 14px;
            font-weight: 900;
        }

        .rqc-settings-description {
            margin: 4px 0 0;

            color: var(--rqc-muted) !important;

            font-size: 10px;
            line-height: 1.55;
        }

        .rqc-settings-body {
            padding: 18px;
        }

        .rqc-settings-divider {
            height: 1px;

            margin: 18px 0;

            background: var(--rqc-border);
        }


        /* ==========================================================
         | SCORE INFO
         * ========================================================== */

        .rqc-score-info {
            display: flex;

            align-items: flex-start;

            gap: 8px;

            margin-top: 8px;
        }

        .rqc-score-dot {
            flex: 0 0 auto;

            width: 5px;
            height: 5px;

            margin-top: 5px;

            background: var(--rqc-muted);

            border-radius: 999px;
        }

        .rqc-score-copy {
            margin: 0;

            color: var(--rqc-muted) !important;

            font-size: 9px;
            line-height: 1.55;
        }


        /* ==========================================================
         | SAVE PANEL
         * ========================================================== */

        .rqc-save {
            overflow: hidden;

            background: var(--rqc-panel) !important;

            border: 1px solid var(--rqc-border);
            border-radius: 14px;
        }

        .rqc-save-copy {
            padding: 15px 17px;

            border-bottom: 1px solid var(--rqc-border);
        }

        .rqc-save-title {
            margin: 0;

            color: var(--rqc-text) !important;

            font-size: 11px;
            font-weight: 850;
        }

        .rqc-save-description {
            margin: 4px 0 0;

            color: var(--rqc-muted) !important;

            font-size: 9px;
            line-height: 1.55;
        }

        .rqc-save-actions {
            display: grid;

            grid-template-columns: 1fr;

            gap: 8px;

            padding: 14px;

            background: var(--rqc-panel-soft) !important;
        }

        .rqc-save-actions .rqc-btn {
            width: 100% !important;
        }


        /* ==========================================================
         | RESPONSIVE
         * ========================================================== */

        @media (max-width: 1100px) {
            .rqc-layout {
                grid-template-columns:
                    minmax(0, 1fr)
                    285px;
            }
        }

        @media (max-width: 900px) {
            .rqc-layout {
                grid-template-columns: 1fr;
            }

            .rqc-sidebar {
                order: -1;
            }

            .rqc-sidebar-inner {
                position: static;
            }

            .rqc-settings-body {
                display: grid;

                grid-template-columns:
                    repeat(3, minmax(0, 1fr));

                gap: 14px;
            }

            .rqc-settings-body .rqc-field + .rqc-field {
                margin-top: 0;
            }

            .rqc-settings-divider {
                display: none;
            }

            .rqc-save-actions {
                grid-template-columns:
                    1fr 1fr;
            }
        }

        @media (max-width: 700px) {
            .rqc-header {
                flex-direction: column;
                align-items: stretch;
            }

            .rqc-header > .rqc-btn {
                width: 100% !important;
            }

            .rqc-options-grid {
                grid-template-columns: 1fr;
            }

            .rqc-option.option-e {
                grid-column: auto;
            }

            .rqc-settings-body {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .rqc-panel-header,
            .rqc-panel-body {
                padding-left: 15px;
                padding-right: 15px;
            }

            .rqc-option {
                padding: 11px;
            }

            .rqc-option-heading {
                align-items: flex-start;
            }

            .rqc-option-right {
                align-items: flex-end;
                flex-direction: column;
            }

            .rqc-save-actions {
                grid-template-columns: 1fr;
            }
        }
    </style>


    <div class="rqc-page mx-auto w-full max-w-[1280px]">

        {{-- ========================================================= --}}
        {{-- PAGE HEADER --}}
        {{-- ========================================================= --}}
        <header class="rqc-header">

            <div class="rqc-header-copy">

                <p class="rqc-eyebrow">
                    Reading Management
                </p>

                <h1 class="rqc-title">
                    Add Reading Question
                </h1>

                <p class="rqc-subtitle">
                    Create a new multiple-choice question for
                    <strong>{{ $contextTitle }}</strong>.
                </p>

            </div>


            <a
                href="{{ $backRoute }}"
                class="rqc-btn rqc-btn-secondary">

                ← Back to Questions

            </a>

        </header>


        {{-- ========================================================= --}}
        {{-- ERROR SUMMARY --}}
        {{-- ========================================================= --}}
        @if ($errors->any())

            <div class="rqc-errors">

                <p class="rqc-errors-title">
                    Please check the form again.
                </p>

                <ul class="rqc-errors-list">

                    @foreach ($errors->all() as $error)

                        <li>
                            {{ $error }}
                        </li>

                    @endforeach

                </ul>

            </div>

        @endif


        {{-- ========================================================= --}}
        {{-- FORM --}}
        {{-- ========================================================= --}}
        <form
            id="readingQuestionCreateForm"
            action="{{ route(
                'admin.reading-questions.store',
                $material->id
            ) }}"
            method="POST">

            @csrf


            <div class="rqc-layout">

                {{-- ================================================= --}}
                {{-- MAIN CONTENT --}}
                {{-- ================================================= --}}
                <main class="rqc-main">

                    {{-- ============================================= --}}
                    {{-- QUESTION CONTENT --}}
                    {{-- ============================================= --}}
                    <section class="rqc-panel">

                        <div class="rqc-panel-header">

                            <p class="rqc-panel-kicker">
                                Content
                            </p>

                            <h2 class="rqc-panel-title">
                                Question Content
                            </h2>

                            <p class="rqc-panel-description">
                                Write a clear question that students can
                                answer using information from the Reading
                                material.
                            </p>

                        </div>


                        <div class="rqc-panel-body">

                            <div class="rqc-field">

                                <div class="rqc-label-row">

                                    <label
                                        for="question"
                                        class="rqc-label">

                                        Question
                                        <span class="rqc-required">*</span>

                                    </label>

                                </div>


                                <textarea
                                    id="question"
                                    name="question"
                                    rows="6"
                                    required
                                    placeholder="Enter the Reading question..."
                                    class="rqc-textarea @error('question') rqc-control-error @enderror">{{ old('question') }}</textarea>


                                <p class="rqc-helper">
                                    Keep the wording concise and make sure
                                    only one answer option is clearly correct.
                                </p>


                                @error('question')

                                    <p class="rqc-error">
                                        {{ $message }}
                                    </p>

                                @enderror

                            </div>

                        </div>

                    </section>


                    {{-- ============================================= --}}
                    {{-- ANSWER OPTIONS --}}
                    {{-- ============================================= --}}
                    <section class="rqc-panel">

                        <div class="rqc-panel-header">

                            <p class="rqc-panel-kicker">
                                Multiple Choice
                            </p>

                            <h2 class="rqc-panel-title">
                                Answer Options
                            </h2>

                            <p class="rqc-panel-description">
                                Options A, B, C, and D are required.
                                Option E is optional.
                            </p>

                        </div>


                        <div class="rqc-panel-body">

                            <div class="rqc-options-grid">

                                @foreach (['a', 'b', 'c', 'd', 'e'] as $option)

                                    @php
                                        $field = 'option_' . $option;

                                        $label = strtoupper($option);

                                        $optional = $option === 'e';

                                        $isCorrect =
                                            strtoupper($option)
                                            === $selectedCorrectAnswer;
                                    @endphp


                                    <div
                                        class="rqc-option
                                               {{ $option === 'e' ? 'option-e' : '' }}
                                               {{ $isCorrect ? 'is-correct' : '' }}"
                                        data-reading-create-option="{{ $label }}">


                                        {{-- OPTION HEADER --}}
                                        <div class="rqc-option-heading">

                                            <div class="rqc-option-left">

                                                <span class="rqc-letter">
                                                    {{ $label }}
                                                </span>


                                                <span class="rqc-option-name">
                                                    Option {{ $label }}
                                                </span>

                                            </div>


                                            <div class="rqc-option-right">

                                                <span class="rqc-correct-indicator">
                                                    Correct
                                                </span>


                                                @if ($optional)

                                                    <span class="rqc-option-meta">
                                                        Optional
                                                    </span>

                                                @else

                                                    <span
                                                        class="rqc-option-meta required">

                                                        Required

                                                    </span>

                                                @endif

                                            </div>

                                        </div>


                                        {{-- INPUT --}}
                                        <input
                                            id="{{ $field }}"
                                            type="text"
                                            name="{{ $field }}"
                                            value="{{ old($field) }}"
                                            {{ $optional ? '' : 'required' }}
                                            placeholder="Enter option {{ $label }}"
                                            class="rqc-input @error($field) rqc-control-error @enderror">


                                        @error($field)

                                            <p class="rqc-error">
                                                {{ $message }}
                                            </p>

                                        @enderror

                                    </div>

                                @endforeach

                            </div>

                        </div>

                    </section>

                </main>


                {{-- ================================================= --}}
                {{-- SIDEBAR --}}
                {{-- ================================================= --}}
                <aside class="rqc-sidebar">

                    <div class="rqc-sidebar-inner">

                        {{-- ========================================= --}}
                        {{-- MATERIAL CONTEXT --}}
                        {{-- ========================================= --}}
                        <section class="rqc-context">

                            <div class="rqc-context-body">

                                <p class="rqc-context-label">
                                    Reading Material
                                </p>

                                <h2 class="rqc-context-title">
                                    {{ $contextTitle }}
                                </h2>

                                <p class="rqc-context-copy">
                                    This question will be attached to this
                                    Reading material.
                                </p>

                            </div>

                        </section>


                        {{-- ========================================= --}}
                        {{-- SETTINGS --}}
                        {{-- ========================================= --}}
                        <section class="rqc-settings">

                            <div class="rqc-settings-head">

                                <h2 class="rqc-settings-title">
                                    Question Settings
                                </h2>

                                <p class="rqc-settings-description">
                                    Configure classification, answer key,
                                    and scoring before saving.
                                </p>

                            </div>


                            <div class="rqc-settings-body">

                                {{-- ================================= --}}
                                {{-- SUB-SKILL --}}
                                {{-- ================================= --}}
                                <div class="rqc-field">

                                    <div class="rqc-label-row">

                                        <label
                                            for="sub_skill"
                                            class="rqc-label">

                                            Sub-Skill
                                            <span class="rqc-required">*</span>

                                        </label>

                                    </div>


                                    <select
                                        id="sub_skill"
                                        name="sub_skill"
                                        required
                                        class="rqc-select @error('sub_skill') rqc-control-error @enderror">

                                        <option value="">
                                            Select sub-skill
                                        </option>


                                        @foreach ($subSkillOptions as $value => $label)

                                            <option
                                                value="{{ $value }}"
                                                @selected(
                                                    $selectedSubSkill === $value
                                                )>

                                                {{ $label }}

                                            </option>

                                        @endforeach

                                    </select>


                                    <p class="rqc-helper">
                                        Used to calculate Reading sub-skill
                                        performance on the Progress page.
                                    </p>


                                    @error('sub_skill')

                                        <p class="rqc-error">
                                            {{ $message }}
                                        </p>

                                    @enderror

                                </div>


                                <div class="rqc-settings-divider"></div>


                                {{-- ================================= --}}
                                {{-- CORRECT ANSWER --}}
                                {{-- ================================= --}}
                                <div class="rqc-field">

                                    <div class="rqc-label-row">

                                        <label
                                            for="correct_answer"
                                            class="rqc-label">

                                            Correct Answer
                                            <span class="rqc-required">*</span>

                                        </label>

                                    </div>


                                    <select
                                        id="correct_answer"
                                        name="correct_answer"
                                        required
                                        class="rqc-select @error('correct_answer') rqc-control-error @enderror">

                                        <option value="">
                                            Select correct answer
                                        </option>


                                        @foreach (['A', 'B', 'C', 'D', 'E'] as $option)

                                            <option
                                                value="{{ $option }}"
                                                @selected(
                                                    $selectedCorrectAnswer
                                                    === $option
                                                )>

                                                Option {{ $option }}

                                            </option>

                                        @endforeach

                                    </select>


                                    <p class="rqc-helper">
                                        The selected option will be highlighted
                                        automatically in the answer list.
                                    </p>


                                    @error('correct_answer')

                                        <p class="rqc-error">
                                            {{ $message }}
                                        </p>

                                    @enderror

                                </div>


                                <div class="rqc-settings-divider"></div>


                                {{-- ================================= --}}
                                {{-- SCORE --}}
                                {{-- ================================= --}}
                                <div class="rqc-field">

                                    <div class="rqc-label-row">

                                        <label
                                            for="score"
                                            class="rqc-label">

                                            Score
                                            <span class="rqc-required">*</span>

                                        </label>

                                    </div>


                                    <input
                                        id="score"
                                        type="number"
                                        name="score"
                                        value="{{ old('score', 10) }}"
                                        min="1"
                                        max="100"
                                        required
                                        class="rqc-input @error('score') rqc-control-error @enderror">


                                    <div class="rqc-score-info">

                                        <span class="rqc-score-dot"></span>

                                        <p class="rqc-score-copy">
                                            Accepted score range is
                                            1 to 100.
                                        </p>

                                    </div>


                                    @error('score')

                                        <p class="rqc-error">
                                            {{ $message }}
                                        </p>

                                    @enderror

                                </div>


                                <div class="rqc-settings-divider"></div>


                                {{-- ================================= --}}
                                {{-- MODUL 2 — DIAGNOSTIC ENGINE (optional) --}}
                                {{-- ================================= --}}
                                <div class="rqc-field">

                                    <div class="rqc-label-row">
                                        <label class="rqc-label">
                                            Error Classification
                                        </label>
                                    </div>

                                    <p class="rqc-helper">
                                        Optional. For each WRONG option,
                                        pick why a student who picks it
                                        is wrong. Leave "not labeled" if
                                        unsure — that answer just won't
                                        get an error label yet.
                                    </p>

                                </div>

                                @php
                                    $errorCodeLabels = [
                                        'lexical' => 'Lexical (word meaning/collocation/form)',
                                        'inferential' => 'Inferential (failed to infer from text)',
                                        'syntactic' => 'Syntactic (wrong grammatical structure)',
                                        'context_misconception' => 'Context Misconception (outside knowledge conflicts with text)',
                                    ];
                                    $existingMap = old('error_if_wrong', []);
                                @endphp

                                @foreach (['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D', 'e' => 'E'] as $optKey => $optLabel)
                                    <div class="rqc-field">
                                        <div class="rqc-label-row">
                                            <label for="error_code_{{ $optKey }}" class="rqc-label">
                                                If student picks "{{ $optLabel }}"…
                                            </label>
                                        </div>

                                        <select
                                            id="error_code_{{ $optKey }}"
                                            name="error_code_{{ $optKey }}"
                                            class="rqc-select @error('error_code_' . $optKey) rqc-control-error @enderror">

                                            <option value="">— not labeled —</option>

                                            @foreach ($errorCodeLabels as $code => $codeLabel)
                                                <option value="{{ $code }}"
                                                    @selected(($existingMap[$optLabel] ?? null) === $code)>
                                                    {{ $codeLabel }}
                                                </option>
                                            @endforeach
                                        </select>

                                        @error('error_code_' . $optKey)
                                            <p class="rqc-error">{{ $message }}</p>
                                        @enderror
                                    </div>
                                @endforeach

                                <div class="rqc-field">
                                    <div class="rqc-label-row">
                                        <label for="rationale" class="rqc-label">
                                            Rationale (why the correct answer is correct)
                                        </label>
                                    </div>

                                    <textarea
                                        id="rationale"
                                        name="rationale"
                                        rows="2"
                                        class="rqc-textarea @error('rationale') rqc-control-error @enderror">{{ old('rationale') }}</textarea>

                                    @error('rationale')
                                        <p class="rqc-error">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="rqc-field">
                                    <div class="rqc-label-row">
                                        <label for="text_span" class="rqc-label">
                                            Supporting Text Span (quote from the passage)
                                        </label>
                                    </div>

                                    <textarea
                                        id="text_span"
                                        name="text_span"
                                        rows="2"
                                        class="rqc-textarea @error('text_span') rqc-control-error @enderror">{{ old('text_span') }}</textarea>

                                    @error('text_span')
                                        <p class="rqc-error">{{ $message }}</p>
                                    @enderror
                                </div>

                            </div>

                        </section>


                        {{-- ========================================= --}}
                        {{-- SAVE PANEL --}}
                        {{-- ========================================= --}}
                        <section class="rqc-save">

                            <div class="rqc-save-copy">

                                <p class="rqc-save-title">
                                    Create this question
                                </p>

                                <p class="rqc-save-description">
                                    Review the question, options, classification,
                                    and answer key before saving.
                                </p>

                            </div>


                            <div class="rqc-save-actions">

                                <button
                                    type="submit"
                                    class="rqc-btn rqc-btn-primary">

                                    Save Question

                                </button>


                                <a
                                    href="{{ $backRoute }}"
                                    class="rqc-btn rqc-btn-secondary">

                                    Cancel

                                </a>

                            </div>

                        </section>

                    </div>

                </aside>

            </div>

        </form>

    </div>


    {{-- ============================================================= --}}
    {{-- CORRECT ANSWER HIGHLIGHT --}}
    {{-- ============================================================= --}}
    <script>
        document.addEventListener(
            'DOMContentLoaded',
            function () {
                const correctAnswerSelect =
                    document.getElementById('correct_answer');

                const optionCards =
                    document.querySelectorAll(
                        '[data-reading-create-option]'
                    );

                if (!correctAnswerSelect || !optionCards.length) {
                    return;
                }


                function updateCorrectAnswerHighlight() {
                    const selectedAnswer =
                        (correctAnswerSelect.value || '')
                        .toUpperCase();


                    optionCards.forEach(function (card) {
                        const option =
                            (card.dataset.readingCreateOption || '')
                            .toUpperCase();


                        if (
                            selectedAnswer !== '' &&
                            option === selectedAnswer
                        ) {
                            card.classList.add('is-correct');
                        } else {
                            card.classList.remove('is-correct');
                        }
                    });
                }


                correctAnswerSelect.addEventListener(
                    'change',
                    updateCorrectAnswerHighlight
                );


                updateCorrectAnswerHighlight();
            }
        );
    </script>
@endsection