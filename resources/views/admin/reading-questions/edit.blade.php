@extends('layouts.admin')

@section('content')
    @php
        $backRoute = $question->reading_material_id
            ? route(
                'admin.reading-questions.index',
                $question->reading_material_id
            )
            : route(
                'admin.reading-materials.index',
                $question->lesson_id
            );

        $selectedSubSkill = old(
            'sub_skill',
            $question->sub_skill
        );

        $selectedCorrectAnswer = old(
            'correct_answer',
            $question->correct_answer
        );
    @endphp


    <style>
        /*
        |--------------------------------------------------------------------------
        | READING QUESTION EDIT
        |--------------------------------------------------------------------------
        |
        | Semua style memakai prefix "rqe-" supaya aman dari CSS global
        | layouts.admin dan tetap konsisten di light / dark mode.
        |
        */

        .rqe-page {
            --rqe-text: #0f172a;
            --rqe-text-soft: #334155;
            --rqe-muted: #64748b;
            --rqe-muted-soft: #94a3b8;

            --rqe-panel: #ffffff;
            --rqe-panel-soft: #f8fafc;
            --rqe-panel-hover: #f1f5f9;
            --rqe-input: #ffffff;

            --rqe-border: #e2e8f0;
            --rqe-border-strong: #cbd5e1;

            --rqe-blue: #2563eb;
            --rqe-blue-hover: #1d4ed8;
            --rqe-blue-soft: #eff6ff;
            --rqe-blue-border: #bfdbfe;

            --rqe-green: #047857;
            --rqe-green-soft: #ecfdf5;
            --rqe-green-border: #a7f3d0;

            --rqe-amber: #b45309;
            --rqe-amber-soft: #fffbeb;
            --rqe-amber-border: #fde68a;

            --rqe-red: #dc2626;
            --rqe-red-soft: #fef2f2;
            --rqe-red-border: #fecaca;

            width: 100%;
            color: var(--rqe-text);
        }

        html.dark .rqe-page,
        body.dark .rqe-page,
        .dark .rqe-page,
        [data-theme="dark"] .rqe-page {
            --rqe-text: #f8fafc;
            --rqe-text-soft: #e2e8f0;
            --rqe-muted: #94a3b8;
            --rqe-muted-soft: #64748b;

            --rqe-panel: #1e293b;
            --rqe-panel-soft: #0f172a;
            --rqe-panel-hover: #263449;
            --rqe-input: #0f172a;

            --rqe-border: #334155;
            --rqe-border-strong: #475569;

            --rqe-blue: #60a5fa;
            --rqe-blue-hover: #93c5fd;
            --rqe-blue-soft: rgba(59, 130, 246, .10);
            --rqe-blue-border: rgba(96, 165, 250, .24);

            --rqe-green: #6ee7b7;
            --rqe-green-soft: rgba(16, 185, 129, .10);
            --rqe-green-border: rgba(110, 231, 183, .22);

            --rqe-amber: #fbbf24;
            --rqe-amber-soft: rgba(245, 158, 11, .10);
            --rqe-amber-border: rgba(251, 191, 36, .22);

            --rqe-red: #fca5a5;
            --rqe-red-soft: rgba(239, 68, 68, .10);
            --rqe-red-border: rgba(248, 113, 113, .22);
        }

        .rqe-page,
        .rqe-page * {
            box-sizing: border-box;
        }


        /* ==========================================================
         | HEADER
         * ========================================================== */

        .rqe-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;

            gap: 24px;

            margin-bottom: 24px;
        }

        .rqe-header-copy {
            min-width: 0;
        }

        .rqe-eyebrow {
            margin: 0 0 7px;

            color: var(--rqe-blue) !important;

            font-size: 11px;
            font-weight: 900;
            line-height: 1;

            letter-spacing: .13em;
            text-transform: uppercase;
        }

        .rqe-title {
            margin: 0;

            color: var(--rqe-text) !important;

            font-size: clamp(28px, 2.2vw, 36px);
            font-weight: 900;
            line-height: 1.12;

            letter-spacing: -.035em;
        }

        .rqe-subtitle {
            max-width: 720px;

            margin: 9px 0 0;

            color: var(--rqe-muted) !important;

            font-size: 13px;
            line-height: 1.65;
        }


        /* ==========================================================
         | BUTTON
         * ========================================================== */

        .rqe-btn {
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

        .rqe-btn-secondary {
            color: var(--rqe-text) !important;
            background: var(--rqe-panel) !important;

            border: 1px solid var(--rqe-border) !important;
        }

        .rqe-btn-secondary:hover {
            background: var(--rqe-panel-hover) !important;
            border-color: var(--rqe-border-strong) !important;
        }

        .rqe-btn-primary {
            color: #ffffff !important;
            background: #2563eb !important;

            border: 1px solid #2563eb !important;
        }

        .rqe-btn-primary:hover {
            background: #1d4ed8 !important;
            border-color: #1d4ed8 !important;
        }


        /* ==========================================================
         | VALIDATION SUMMARY
         * ========================================================== */

        .rqe-errors {
            margin-bottom: 20px;

            padding: 15px 17px;

            color: var(--rqe-red) !important;
            background: var(--rqe-red-soft) !important;

            border: 1px solid var(--rqe-red-border);
            border-radius: 12px;
        }

        .rqe-errors-title {
            margin: 0;

            font-size: 13px;
            font-weight: 900;
        }

        .rqe-errors-list {
            margin: 8px 0 0;
            padding-left: 19px;

            font-size: 11px;
            font-weight: 650;
            line-height: 1.7;
        }


        /* ==========================================================
         | MAIN LAYOUT
         * ========================================================== */

        .rqe-layout {
            display: grid;

            grid-template-columns:
                minmax(0, 1fr)
                320px;

            gap: 20px;

            align-items: start;
        }

        .rqe-main {
            display: grid;

            min-width: 0;

            gap: 20px;
        }

        .rqe-sidebar {
            min-width: 0;
        }

        .rqe-sidebar-inner {
            position: sticky;
            top: 105px;

            display: grid;

            gap: 14px;
        }


        /* ==========================================================
         | PANEL
         * ========================================================== */

        .rqe-panel {
            overflow: hidden;

            background: var(--rqe-panel) !important;

            border: 1px solid var(--rqe-border);
            border-radius: 14px;
        }

        .rqe-panel-header {
            display: flex;

            align-items: flex-start;
            justify-content: space-between;

            gap: 18px;

            padding: 18px 20px;

            background: var(--rqe-panel) !important;

            border-bottom: 1px solid var(--rqe-border);
        }

        .rqe-panel-kicker {
            margin: 0 0 5px;

            color: var(--rqe-blue) !important;

            font-size: 9px;
            font-weight: 900;
            line-height: 1;

            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .rqe-panel-title {
            margin: 0;

            color: var(--rqe-text) !important;

            font-size: 15px;
            font-weight: 900;
            line-height: 1.35;
        }

        .rqe-panel-description {
            max-width: 670px;

            margin: 5px 0 0;

            color: var(--rqe-muted) !important;

            font-size: 11px;
            line-height: 1.6;
        }

        .rqe-panel-body {
            padding: 20px;

            background: var(--rqe-panel) !important;
        }


        /* ==========================================================
         | FORM FIELD
         * ========================================================== */

        .rqe-field {
            min-width: 0;
        }

        .rqe-field + .rqe-field {
            margin-top: 18px;
        }

        .rqe-label-row {
            display: flex;

            align-items: center;
            justify-content: space-between;

            gap: 10px;

            margin-bottom: 7px;
        }

        .rqe-label {
            margin: 0;

            color: var(--rqe-text) !important;

            font-size: 11px;
            font-weight: 850;
            line-height: 1.4;
        }

        .rqe-required {
            color: #ef4444 !important;
        }

        .rqe-optional {
            color: var(--rqe-muted) !important;

            font-size: 9px;
            font-weight: 750;

            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .rqe-helper {
            margin: 7px 0 0;

            color: var(--rqe-muted) !important;

            font-size: 9px;
            line-height: 1.55;
        }

        .rqe-error {
            margin: 7px 0 0;

            color: var(--rqe-red) !important;

            font-size: 10px;
            font-weight: 750;
            line-height: 1.5;
        }


        /* ==========================================================
         | INPUT / SELECT / TEXTAREA
         * ========================================================== */

        .rqe-input,
        .rqe-select,
        .rqe-textarea {
            display: block !important;

            width: 100% !important;

            color: var(--rqe-text) !important;
            background: var(--rqe-input) !important;

            border: 1px solid var(--rqe-border-strong) !important;
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

        .rqe-input,
        .rqe-select {
            height: 42px !important;
            min-height: 42px !important;

            padding: 0 12px !important;
        }

        .rqe-textarea {
            min-height: 145px !important;

            padding: 12px !important;

            resize: vertical;
        }

        .rqe-input::placeholder,
        .rqe-textarea::placeholder {
            color: var(--rqe-muted-soft) !important;

            opacity: 1;
        }

        .rqe-input:focus,
        .rqe-select:focus,
        .rqe-textarea:focus {
            border-color: #3b82f6 !important;

            box-shadow:
                0 0 0 3px rgba(59, 130, 246, .11) !important;
        }

        .rqe-control-error {
            border-color: #ef4444 !important;
        }

        .rqe-select option {
            color: var(--rqe-text);
            background: var(--rqe-input);
        }


        /* ==========================================================
         | ANSWER OPTIONS
         * ========================================================== */

        .rqe-options-grid {
            display: grid;

            grid-template-columns:
                repeat(2, minmax(0, 1fr));

            gap: 13px;
        }

        .rqe-option {
            position: relative;

            min-width: 0;

            padding: 13px;

            background: var(--rqe-panel-soft) !important;

            border: 1px solid var(--rqe-border);
            border-radius: 11px;

            transition:
                background-color .15s ease,
                border-color .15s ease;
        }

        .rqe-option:hover {
            border-color: var(--rqe-border-strong);
        }

        .rqe-option:focus-within {
            background: var(--rqe-panel) !important;
            border-color: var(--rqe-blue-border);
        }

        .rqe-option.is-correct {
            background: var(--rqe-green-soft) !important;
            border-color: var(--rqe-green-border);
        }

        .rqe-option.option-e {
            grid-column: 1 / -1;
        }

        .rqe-option-heading {
            display: flex;

            align-items: center;
            justify-content: space-between;

            gap: 10px;

            margin-bottom: 9px;
        }

        .rqe-option-left {
            display: flex;

            min-width: 0;

            align-items: center;

            gap: 8px;
        }

        .rqe-letter {
            display: inline-flex;

            flex: 0 0 auto;

            width: 28px;
            height: 28px;

            align-items: center;
            justify-content: center;

            color: var(--rqe-blue) !important;
            background: var(--rqe-blue-soft) !important;

            border: 1px solid var(--rqe-blue-border);
            border-radius: 8px;

            font-size: 10px;
            font-weight: 900;
        }

        .rqe-option.is-correct .rqe-letter {
            color: var(--rqe-green) !important;
            background: var(--rqe-green-soft) !important;

            border-color: var(--rqe-green-border);
        }

        .rqe-option-name {
            color: var(--rqe-text) !important;

            font-size: 10px;
            font-weight: 850;
        }

        .rqe-option-right {
            display: flex;

            align-items: center;

            gap: 6px;
        }

        .rqe-option-meta {
            color: var(--rqe-muted) !important;

            font-size: 8px;
            font-weight: 800;
            line-height: 1;

            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .rqe-option-meta.required {
            color: var(--rqe-red) !important;
        }

        .rqe-correct-indicator {
            display: none;

            align-items: center;
            justify-content: center;

            min-height: 20px;

            padding: 0 7px;

            color: var(--rqe-green) !important;
            background: var(--rqe-green-soft) !important;

            border: 1px solid var(--rqe-green-border);
            border-radius: 999px;

            font-size: 8px;
            font-weight: 900;
            line-height: 1;

            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .rqe-option.is-correct .rqe-correct-indicator {
            display: inline-flex;
        }

        .rqe-option .rqe-input {
            height: 40px !important;
            min-height: 40px !important;
        }


        /* ==========================================================
         | SETTINGS
         * ========================================================== */

        .rqe-settings {
            overflow: hidden;

            background: var(--rqe-panel) !important;

            border: 1px solid var(--rqe-border);
            border-radius: 14px;
        }

        .rqe-settings-head {
            padding: 17px 18px;

            background: var(--rqe-panel) !important;

            border-bottom: 1px solid var(--rqe-border);
        }

        .rqe-settings-title {
            margin: 0;

            color: var(--rqe-text) !important;

            font-size: 14px;
            font-weight: 900;
        }

        .rqe-settings-description {
            margin: 4px 0 0;

            color: var(--rqe-muted) !important;

            font-size: 10px;
            line-height: 1.55;
        }

        .rqe-settings-body {
            padding: 18px;

            background: var(--rqe-panel) !important;
        }

        .rqe-settings-divider {
            height: 1px;

            margin: 18px 0;

            background: var(--rqe-border);
        }

        .rqe-warning {
            margin-top: 8px;

            padding: 9px 10px;

            color: var(--rqe-amber) !important;
            background: var(--rqe-amber-soft) !important;

            border: 1px solid var(--rqe-amber-border);
            border-radius: 9px;

            font-size: 9px;
            font-weight: 700;
            line-height: 1.55;
        }


        /* ==========================================================
         | SCORE INFO
         * ========================================================== */

        .rqe-score-info {
            display: flex;

            align-items: flex-start;

            gap: 8px;

            margin-top: 8px;
        }

        .rqe-score-dot {
            flex: 0 0 auto;

            width: 5px;
            height: 5px;

            margin-top: 5px;

            background: var(--rqe-muted);

            border-radius: 999px;
        }

        .rqe-score-copy {
            margin: 0;

            color: var(--rqe-muted) !important;

            font-size: 9px;
            line-height: 1.55;
        }


        /* ==========================================================
         | SAVE PANEL
         * ========================================================== */

        .rqe-save {
            overflow: hidden;

            background: var(--rqe-panel) !important;

            border: 1px solid var(--rqe-border);
            border-radius: 14px;
        }

        .rqe-save-copy {
            padding: 15px 17px;

            background: var(--rqe-panel) !important;

            border-bottom: 1px solid var(--rqe-border);
        }

        .rqe-save-title {
            margin: 0;

            color: var(--rqe-text) !important;

            font-size: 11px;
            font-weight: 850;
        }

        .rqe-save-description {
            margin: 4px 0 0;

            color: var(--rqe-muted) !important;

            font-size: 9px;
            line-height: 1.55;
        }

        .rqe-save-actions {
            display: grid;

            grid-template-columns: 1fr;

            gap: 8px;

            padding: 14px;

            background: var(--rqe-panel-soft) !important;
        }

        .rqe-save-actions .rqe-btn {
            width: 100% !important;
        }


        /* ==========================================================
         | RESPONSIVE
         * ========================================================== */

        @media (max-width: 1100px) {
            .rqe-layout {
                grid-template-columns:
                    minmax(0, 1fr)
                    285px;
            }
        }

        @media (max-width: 900px) {
            .rqe-layout {
                grid-template-columns: 1fr;
            }

            .rqe-sidebar-inner {
                position: static;
            }

            .rqe-sidebar {
                order: -1;
            }

            .rqe-settings-body {
                display: grid;

                grid-template-columns:
                    repeat(3, minmax(0, 1fr));

                gap: 14px;
            }

            .rqe-settings-body .rqe-field + .rqe-field {
                margin-top: 0;
            }

            .rqe-settings-divider {
                display: none;
            }

            .rqe-save-actions {
                grid-template-columns:
                    1fr 1fr;
            }
        }

        @media (max-width: 700px) {
            .rqe-header {
                flex-direction: column;
                align-items: stretch;
            }

            .rqe-header > .rqe-btn {
                width: 100% !important;
            }

            .rqe-options-grid {
                grid-template-columns: 1fr;
            }

            .rqe-option.option-e {
                grid-column: auto;
            }

            .rqe-settings-body {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .rqe-panel-header,
            .rqe-panel-body {
                padding-left: 15px;
                padding-right: 15px;
            }

            .rqe-option {
                padding: 11px;
            }

            .rqe-option-heading {
                align-items: flex-start;
            }

            .rqe-option-right {
                align-items: flex-end;
                flex-direction: column;
            }

            .rqe-save-actions {
                grid-template-columns: 1fr;
            }
        }
    </style>


    <div class="rqe-page mx-auto w-full max-w-[1280px]">

        {{-- ========================================================= --}}
        {{-- HEADER --}}
        {{-- ========================================================= --}}
        <header class="rqe-header">

            <div class="rqe-header-copy">

                <p class="rqe-eyebrow">
                    Reading Management
                </p>

                <h1 class="rqe-title">
                    Edit Reading Question
                </h1>

                <p class="rqe-subtitle">
                    Update the question, answer choices, Reading sub-skill,
                    correct answer, and assessment score.
                </p>

            </div>


            <a
                href="{{ $backRoute }}"
                class="rqe-btn rqe-btn-secondary">

                ← Back to Questions

            </a>

        </header>


        {{-- ========================================================= --}}
        {{-- VALIDATION ERRORS --}}
        {{-- ========================================================= --}}
        @if ($errors->any())

            <div class="rqe-errors">

                <p class="rqe-errors-title">
                    Please correct the following errors:
                </p>

                <ul class="rqe-errors-list">

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
            id="readingQuestionEditForm"
            action="{{ route(
                'admin.reading-questions.update',
                $question->id
            ) }}"
            method="POST">

            @csrf
            @method('PUT')


            <div class="rqe-layout">

                {{-- ================================================= --}}
                {{-- MAIN CONTENT --}}
                {{-- ================================================= --}}
                <main class="rqe-main">

                    {{-- ============================================= --}}
                    {{-- QUESTION --}}
                    {{-- ============================================= --}}
                    <section class="rqe-panel">

                        <div class="rqe-panel-header">

                            <div>

                                <p class="rqe-panel-kicker">
                                    Content
                                </p>

                                <h2 class="rqe-panel-title">
                                    Question Content
                                </h2>

                                <p class="rqe-panel-description">
                                    Edit the question students will answer
                                    after reading the material.
                                </p>

                            </div>

                        </div>


                        <div class="rqe-panel-body">

                            <div class="rqe-field">

                                <div class="rqe-label-row">

                                    <label
                                        for="question"
                                        class="rqe-label">

                                        Question
                                        <span class="rqe-required">*</span>

                                    </label>

                                </div>


                                <textarea
                                    id="question"
                                    name="question"
                                    rows="6"
                                    required
                                    placeholder="Enter the Reading question..."
                                    class="rqe-textarea @error('question') rqe-control-error @enderror">{{ old('question', $question->question) }}</textarea>


                                <p class="rqe-helper">
                                    Write a clear question that can be answered
                                    using information from the Reading material.
                                </p>


                                @error('question')

                                    <p class="rqe-error">
                                        {{ $message }}
                                    </p>

                                @enderror

                            </div>

                        </div>

                    </section>


                    {{-- ============================================= --}}
                    {{-- ANSWER OPTIONS --}}
                    {{-- ============================================= --}}
                    <section class="rqe-panel">

                        <div class="rqe-panel-header">

                            <div>

                                <p class="rqe-panel-kicker">
                                    Multiple Choice
                                </p>

                                <h2 class="rqe-panel-title">
                                    Answer Options
                                </h2>

                                <p class="rqe-panel-description">
                                    Options A, B, C, and D are required.
                                    Option E can be left empty when it is
                                    not needed.
                                </p>

                            </div>

                        </div>


                        <div class="rqe-panel-body">

                            <div class="rqe-options-grid">

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
                                        class="rqe-option
                                               {{ $option === 'e' ? 'option-e' : '' }}
                                               {{ $isCorrect ? 'is-correct' : '' }}"
                                        data-reading-option="{{ $label }}">


                                        {{-- OPTION HEADER --}}
                                        <div class="rqe-option-heading">

                                            <div class="rqe-option-left">

                                                <span class="rqe-letter">
                                                    {{ $label }}
                                                </span>


                                                <span class="rqe-option-name">
                                                    Option {{ $label }}
                                                </span>

                                            </div>


                                            <div class="rqe-option-right">

                                                <span class="rqe-correct-indicator">
                                                    Correct
                                                </span>


                                                @if ($optional)

                                                    <span class="rqe-option-meta">
                                                        Optional
                                                    </span>

                                                @else

                                                    <span
                                                        class="rqe-option-meta required">

                                                        Required

                                                    </span>

                                                @endif

                                            </div>

                                        </div>


                                        {{-- OPTION INPUT --}}
                                        <input
                                            id="{{ $field }}"
                                            type="text"
                                            name="{{ $field }}"
                                            value="{{ old(
                                                $field,
                                                $question->$field
                                            ) }}"
                                            {{ $optional ? '' : 'required' }}
                                            placeholder="Enter option {{ $label }}"
                                            class="rqe-input @error($field) rqe-control-error @enderror">


                                        @error($field)

                                            <p class="rqe-error">
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
                <aside class="rqe-sidebar">

                    <div class="rqe-sidebar-inner">

                        {{-- ========================================= --}}
                        {{-- SETTINGS --}}
                        {{-- ========================================= --}}
                        <section class="rqe-settings">

                            <div class="rqe-settings-head">

                                <h2 class="rqe-settings-title">
                                    Question Settings
                                </h2>

                                <p class="rqe-settings-description">
                                    Configure classification, answer key,
                                    and scoring.
                                </p>

                            </div>


                            <div class="rqe-settings-body">

                                {{-- ================================= --}}
                                {{-- SUB-SKILL --}}
                                {{-- ================================= --}}
                                <div class="rqe-field">

                                    <div class="rqe-label-row">

                                        <label
                                            for="sub_skill"
                                            class="rqe-label">

                                            Sub-Skill
                                            <span class="rqe-required">*</span>

                                        </label>

                                    </div>


                                    <select
                                        id="sub_skill"
                                        name="sub_skill"
                                        required
                                        class="rqe-select @error('sub_skill') rqe-control-error @enderror">

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


                                    @if (empty($question->sub_skill))

                                        <div class="rqe-warning">
                                            This existing question has no
                                            sub-skill yet. Select one before
                                            saving.
                                        </div>

                                    @endif


                                    @error('sub_skill')

                                        <p class="rqe-error">
                                            {{ $message }}
                                        </p>

                                    @enderror

                                </div>


                                <div class="rqe-settings-divider"></div>


                                {{-- ================================= --}}
                                {{-- CORRECT ANSWER --}}
                                {{-- ================================= --}}
                                <div class="rqe-field">

                                    <div class="rqe-label-row">

                                        <label
                                            for="correct_answer"
                                            class="rqe-label">

                                            Correct Answer
                                            <span class="rqe-required">*</span>

                                        </label>

                                    </div>


                                    <select
                                        id="correct_answer"
                                        name="correct_answer"
                                        required
                                        class="rqe-select @error('correct_answer') rqe-control-error @enderror">

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


                                    <p class="rqe-helper">
                                        The selected answer is highlighted
                                        automatically in the option list.
                                    </p>


                                    @error('correct_answer')

                                        <p class="rqe-error">
                                            {{ $message }}
                                        </p>

                                    @enderror

                                </div>


                                <div class="rqe-settings-divider"></div>


                                {{-- ================================= --}}
                                {{-- SCORE --}}
                                {{-- ================================= --}}
                                <div class="rqe-field">

                                    <div class="rqe-label-row">

                                        <label
                                            for="score"
                                            class="rqe-label">

                                            Score
                                            <span class="rqe-required">*</span>

                                        </label>

                                    </div>


                                    <input
                                        id="score"
                                        type="number"
                                        name="score"
                                        value="{{ old(
                                            'score',
                                            $question->score
                                        ) }}"
                                        min="1"
                                        max="100"
                                        required
                                        class="rqe-input @error('score') rqe-control-error @enderror">


                                    <div class="rqe-score-info">

                                        <span class="rqe-score-dot"></span>

                                        <p class="rqe-score-copy">
                                            Accepted score range is
                                            1 to 100.
                                        </p>

                                    </div>


                                    @error('score')

                                        <p class="rqe-error">
                                            {{ $message }}
                                        </p>

                                    @enderror

                                </div>


                                <div class="rqe-settings-divider"></div>


                                {{-- ================================= --}}
                                {{-- MODUL 2 — DIAGNOSTIC ENGINE (optional) --}}
                                {{-- ================================= --}}
                                <div class="rqe-field">

                                    <div class="rqe-label-row">
                                        <label class="rqe-label">
                                            Error Classification
                                        </label>
                                    </div>

                                    <p class="rqe-helper">
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
                                    $existingMap = old('error_if_wrong', $question->error_if_wrong ?? []);
                                @endphp

                                @foreach (['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D', 'e' => 'E'] as $optKey => $optLabel)
                                    <div class="rqe-field">
                                        <div class="rqe-label-row">
                                            <label for="error_code_{{ $optKey }}" class="rqe-label">
                                                If student picks "{{ $optLabel }}"…
                                            </label>
                                        </div>

                                        <select
                                            id="error_code_{{ $optKey }}"
                                            name="error_code_{{ $optKey }}"
                                            class="rqe-select @error('error_code_' . $optKey) rqe-control-error @enderror">

                                            <option value="">— not labeled —</option>

                                            @foreach ($errorCodeLabels as $code => $codeLabel)
                                                <option value="{{ $code }}"
                                                    @selected(($existingMap[$optLabel] ?? null) === $code)>
                                                    {{ $codeLabel }}
                                                </option>
                                            @endforeach
                                        </select>

                                        @error('error_code_' . $optKey)
                                            <p class="rqe-error">{{ $message }}</p>
                                        @enderror
                                    </div>
                                @endforeach

                                <div class="rqe-field">
                                    <div class="rqe-label-row">
                                        <label for="rationale" class="rqe-label">
                                            Rationale (why the correct answer is correct)
                                        </label>
                                    </div>

                                    <textarea
                                        id="rationale"
                                        name="rationale"
                                        rows="2"
                                        class="rqe-textarea @error('rationale') rqe-control-error @enderror">{{ old('rationale', $question->rationale) }}</textarea>

                                    @error('rationale')
                                        <p class="rqe-error">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="rqe-field">
                                    <div class="rqe-label-row">
                                        <label for="text_span" class="rqe-label">
                                            Supporting Text Span (quote from the passage)
                                        </label>
                                    </div>

                                    <textarea
                                        id="text_span"
                                        name="text_span"
                                        rows="2"
                                        class="rqe-textarea @error('text_span') rqe-control-error @enderror">{{ old('text_span', $question->text_span) }}</textarea>

                                    @error('text_span')
                                        <p class="rqe-error">{{ $message }}</p>
                                    @enderror
                                </div>

                            </div>

                        </section>


                        {{-- ========================================= --}}
                        {{-- SAVE --}}
                        {{-- ========================================= --}}
                        <section class="rqe-save">

                            <div class="rqe-save-copy">

                                <p class="rqe-save-title">
                                    Save your changes
                                </p>

                                <p class="rqe-save-description">
                                    Review the question, answer options,
                                    and correct answer before updating.
                                </p>

                            </div>


                            <div class="rqe-save-actions">

                                <button
                                    type="submit"
                                    class="rqe-btn rqe-btn-primary">

                                    Save Changes

                                </button>


                                <a
                                    href="{{ $backRoute }}"
                                    class="rqe-btn rqe-btn-secondary">

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
        document.addEventListener('DOMContentLoaded', function () {
            const correctAnswerSelect =
                document.getElementById('correct_answer');

            const optionCards =
                document.querySelectorAll(
                    '[data-reading-option]'
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
                        (card.dataset.readingOption || '')
                        .toUpperCase();

                    if (option === selectedAnswer) {
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
        });
    </script>
@endsection