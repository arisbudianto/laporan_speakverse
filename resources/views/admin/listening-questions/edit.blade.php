@extends('layouts.admin')

@section('content')
    @php
        $isLegacyLessonMode = is_null($question->listening_material_id);

        $backRoute = $question->listening_material_id
            ? route(
                'admin.listening-questions.index',
                $question->listening_material_id
            )
            : route(
                'admin.listening-materials.index',
                $question->lesson_id
            );

        $currentAudioUrl = null;

        if ($question->audio_file) {
            $audioVersion = $question->updated_at
                ? $question->updated_at->timestamp
                : time();

            $currentAudioUrl =
                asset('storage/' . $question->audio_file)
                . '?v='
                . $audioVersion;
        }

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
        /* ============================================================
         | LISTENING QUESTION EDIT V2
         * ============================================================ */

        .lqe2-page {
            --page-text: #0f172a;
            --page-soft-text: #334155;
            --page-muted: #64748b;
            --page-muted-light: #94a3b8;

            --panel: #ffffff;
            --panel-soft: #f8fafc;
            --panel-hover: #f1f5f9;
            --input: #ffffff;

            --border: #e2e8f0;
            --border-strong: #cbd5e1;

            --blue: #2563eb;
            --blue-hover: #1d4ed8;
            --blue-soft: #eff6ff;
            --blue-border: #bfdbfe;

            --green: #047857;
            --green-soft: #ecfdf5;
            --green-border: #a7f3d0;

            --amber: #b45309;
            --amber-soft: #fffbeb;
            --amber-border: #fde68a;

            --red: #dc2626;
            --red-soft: #fef2f2;
            --red-border: #fecaca;

            width: 100%;
            color: var(--page-text);
        }

        .dark .lqe2-page {
            --page-text: #f8fafc;
            --page-soft-text: #e2e8f0;
            --page-muted: #94a3b8;
            --page-muted-light: #64748b;

            --panel: #1e293b;
            --panel-soft: #0f172a;
            --panel-hover: #263449;
            --input: #0f172a;

            --border: #334155;
            --border-strong: #475569;

            --blue: #60a5fa;
            --blue-hover: #93c5fd;
            --blue-soft: rgba(59, 130, 246, .10);
            --blue-border: rgba(96, 165, 250, .24);

            --green: #6ee7b7;
            --green-soft: rgba(16, 185, 129, .10);
            --green-border: rgba(110, 231, 183, .22);

            --amber: #fbbf24;
            --amber-soft: rgba(245, 158, 11, .10);
            --amber-border: rgba(251, 191, 36, .22);

            --red: #fca5a5;
            --red-soft: rgba(239, 68, 68, .10);
            --red-border: rgba(248, 113, 113, .22);
        }

        .lqe2-page,
        .lqe2-page * {
            box-sizing: border-box;
        }

        /* ============================================================
         | HEADER
         * ============================================================ */

        .lqe2-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 24px;
            margin-bottom: 24px;
        }

        .lqe2-header-copy {
            min-width: 0;
        }

        .lqe2-eyebrow {
            margin: 0 0 7px;
            color: var(--blue);
            font-size: 11px;
            font-weight: 900;
            line-height: 1;
            letter-spacing: .13em;
            text-transform: uppercase;
        }

        .lqe2-title {
            margin: 0;
            color: var(--page-text) !important;
            font-size: clamp(28px, 2.2vw, 36px);
            font-weight: 900;
            line-height: 1.12;
            letter-spacing: -.035em;
        }

        .lqe2-subtitle {
            max-width: 690px;
            margin: 9px 0 0;
            color: var(--page-muted) !important;
            font-size: 13px;
            line-height: 1.65;
        }

        /* ============================================================
         | BUTTON
         * ============================================================ */

        .lqe2-btn {
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

        .lqe2-btn-secondary {
            color: var(--page-text) !important;
            background: var(--panel) !important;
            border: 1px solid var(--border) !important;
        }

        .lqe2-btn-secondary:hover {
            background: var(--panel-hover) !important;
            border-color: var(--border-strong) !important;
        }

        .lqe2-btn-primary {
            color: #ffffff !important;
            background: #2563eb !important;
            border: 1px solid #2563eb !important;
        }

        .lqe2-btn-primary:hover {
            background: #1d4ed8 !important;
            border-color: #1d4ed8 !important;
        }

        /* ============================================================
         | ERRORS
         * ============================================================ */

        .lqe2-errors {
            margin-bottom: 20px;
            padding: 15px 17px;
            color: var(--red);
            background: var(--red-soft);
            border: 1px solid var(--red-border);
            border-radius: 12px;
        }

        .lqe2-errors-title {
            margin: 0;
            font-size: 13px;
            font-weight: 900;
        }

        .lqe2-errors-list {
            margin: 8px 0 0;
            padding-left: 19px;
            font-size: 11px;
            font-weight: 650;
            line-height: 1.7;
        }

        /* ============================================================
         | MAIN LAYOUT
         * ============================================================ */

        .lqe2-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 330px;
            gap: 20px;
            align-items: start;
        }

        .lqe2-main {
            display: grid;
            gap: 20px;
            min-width: 0;
        }

        .lqe2-sidebar {
            min-width: 0;
        }

        .lqe2-sidebar-inner {
            position: sticky;
            top: 105px;
            display: grid;
            gap: 14px;
        }

        /* ============================================================
         | PANELS
         * ============================================================ */

        .lqe2-panel {
            overflow: hidden;
            background: var(--panel) !important;
            border: 1px solid var(--border);
            border-radius: 14px;
        }

        .lqe2-panel-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 18px;

            padding: 18px 20px;

            border-bottom: 1px solid var(--border);
        }

        .lqe2-panel-kicker {
            margin: 0 0 5px;
            color: var(--blue) !important;
            font-size: 9px;
            font-weight: 900;
            line-height: 1;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .lqe2-panel-title {
            margin: 0;
            color: var(--page-text) !important;
            font-size: 15px;
            font-weight: 900;
            line-height: 1.35;
        }

        .lqe2-panel-description {
            margin: 5px 0 0;
            color: var(--page-muted) !important;
            font-size: 11px;
            line-height: 1.6;
        }

        .lqe2-panel-body {
            padding: 20px;
        }

        /* ============================================================
         | FORM FIELDS
         * ============================================================ */

        .lqe2-field {
            min-width: 0;
        }

        .lqe2-field + .lqe2-field {
            margin-top: 18px;
        }

        .lqe2-label-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 7px;
        }

        .lqe2-label {
            margin: 0;
            color: var(--page-text) !important;
            font-size: 11px;
            font-weight: 850;
            line-height: 1.4;
        }

        .lqe2-required {
            color: #ef4444 !important;
        }

        .lqe2-optional {
            color: var(--page-muted) !important;
            font-size: 9px;
            font-weight: 750;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .lqe2-helper {
            margin: 7px 0 0;
            color: var(--page-muted) !important;
            font-size: 9px;
            line-height: 1.55;
        }

        .lqe2-error {
            margin: 7px 0 0;
            color: var(--red) !important;
            font-size: 10px;
            font-weight: 750;
            line-height: 1.5;
        }

        /* ============================================================
         | CONTROLS
         * ============================================================ */

        .lqe2-input,
        .lqe2-textarea,
        .lqe2-select {
            display: block !important;
            width: 100% !important;

            color: var(--page-text) !important;
            background: var(--input) !important;

            border: 1px solid var(--border-strong) !important;
            border-radius: 9px !important;

            font-family: inherit !important;
            font-size: 12px !important;
            font-weight: 600 !important;
            line-height: 1.55 !important;

            outline: none !important;
            box-shadow: none !important;

            transition:
                border-color .15s ease,
                box-shadow .15s ease !important;
        }

        .lqe2-input,
        .lqe2-select {
            height: 42px !important;
            min-height: 42px !important;
            padding: 0 12px !important;
        }

        .lqe2-textarea {
            min-height: 96px !important;
            padding: 11px 12px !important;
            resize: vertical;
        }

        .lqe2-question-textarea {
            min-height: 132px !important;
        }

        .lqe2-input::placeholder,
        .lqe2-textarea::placeholder {
            color: var(--page-muted-light) !important;
            opacity: 1;
        }

        .lqe2-input:focus,
        .lqe2-textarea:focus,
        .lqe2-select:focus {
            border-color: #3b82f6 !important;

            box-shadow:
                0 0 0 3px rgba(59, 130, 246, .11) !important;
        }

        .lqe2-control-error {
            border-color: #ef4444 !important;
        }

        .lqe2-select option {
            color: var(--page-text);
            background: var(--input);
        }

        /* ============================================================
         | QUESTION CONTENT
         * ============================================================ */

        .lqe2-content-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 18px;
        }

        /* ============================================================
         | ANSWER OPTIONS
         * ============================================================ */

        .lqe2-options-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .lqe2-option {
            position: relative;
            min-width: 0;

            padding: 14px;

            background: var(--panel-soft) !important;

            border: 1px solid var(--border);
            border-radius: 11px;

            transition:
                border-color .15s ease,
                background-color .15s ease;
        }

        .lqe2-option:hover {
            border-color: var(--border-strong);
        }

        .lqe2-option:focus-within {
            background: var(--panel) !important;
            border-color: var(--blue-border);
        }

        .lqe2-option.is-correct {
            background: var(--green-soft) !important;
            border-color: var(--green-border);
        }

        .lqe2-option.option-e {
            grid-column: 1 / -1;
        }

        .lqe2-option-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;

            margin-bottom: 9px;
        }

        .lqe2-option-left {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 0;
        }

        .lqe2-letter {
            display: inline-flex;
            flex: 0 0 auto;

            width: 28px;
            height: 28px;

            align-items: center;
            justify-content: center;

            color: var(--blue) !important;
            background: var(--blue-soft) !important;

            border: 1px solid var(--blue-border);
            border-radius: 8px;

            font-size: 10px;
            font-weight: 900;
        }

        .lqe2-option.is-correct .lqe2-letter {
            color: var(--green) !important;
            background: var(--green-soft) !important;
            border-color: var(--green-border);
        }

        .lqe2-option-name {
            color: var(--page-text) !important;
            font-size: 10px;
            font-weight: 850;
        }

        .lqe2-option-meta {
            color: var(--page-muted) !important;
            font-size: 8px;
            font-weight: 800;
            line-height: 1;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .lqe2-option-meta.required {
            color: var(--red) !important;
        }

        .lqe2-correct-indicator {
            display: none;

            padding: 4px 7px;

            color: var(--green) !important;
            background: var(--green-soft) !important;

            border: 1px solid var(--green-border);
            border-radius: 999px;

            font-size: 8px;
            font-weight: 900;
            line-height: 1;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .lqe2-option.is-correct .lqe2-correct-indicator {
            display: inline-flex;
        }

        .lqe2-option-right {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .lqe2-option .lqe2-input {
            height: 40px !important;
            min-height: 40px !important;
        }

        /* ============================================================
         | SETTINGS SIDEBAR
         * ============================================================ */

        .lqe2-settings {
            background: var(--panel) !important;
            border: 1px solid var(--border);
            border-radius: 14px;
            overflow: hidden;
        }

        .lqe2-settings-head {
            padding: 17px 18px;
            border-bottom: 1px solid var(--border);
        }

        .lqe2-settings-title {
            margin: 0;
            color: var(--page-text) !important;
            font-size: 14px;
            font-weight: 900;
        }

        .lqe2-settings-description {
            margin: 4px 0 0;
            color: var(--page-muted) !important;
            font-size: 10px;
            line-height: 1.55;
        }

        .lqe2-settings-body {
            padding: 18px;
        }

        .lqe2-settings-divider {
            height: 1px;
            margin: 18px 0;
            background: var(--border);
        }

        .lqe2-warning {
            margin-top: 8px;
            padding: 9px 10px;

            color: var(--amber) !important;
            background: var(--amber-soft) !important;

            border: 1px solid var(--amber-border);
            border-radius: 9px;

            font-size: 9px;
            font-weight: 700;
            line-height: 1.55;
        }

        /* ============================================================
         | SAVE AREA
         * ============================================================ */

        .lqe2-save-panel {
            overflow: hidden;
            background: var(--panel) !important;
            border: 1px solid var(--border);
            border-radius: 14px;
        }

        .lqe2-save-copy {
            padding: 15px 17px;
            border-bottom: 1px solid var(--border);
        }

        .lqe2-save-title {
            margin: 0;
            color: var(--page-text) !important;
            font-size: 11px;
            font-weight: 850;
        }

        .lqe2-save-description {
            margin: 4px 0 0;
            color: var(--page-muted) !important;
            font-size: 9px;
            line-height: 1.55;
        }

        .lqe2-save-actions {
            display: grid;
            grid-template-columns: 1fr;
            gap: 8px;

            padding: 14px;

            background: var(--panel-soft) !important;
        }

        .lqe2-save-actions .lqe2-btn {
            width: 100% !important;
        }

        /* ============================================================
         | LEGACY AUDIO
         * ============================================================ */

        .lqe2-audio-section {
            background: var(--panel) !important;
            border: 1px solid var(--border);
            border-radius: 14px;
            overflow: hidden;
        }

        .lqe2-audio-notice {
            padding: 14px 18px;

            color: var(--amber) !important;
            background: var(--amber-soft) !important;

            border-bottom: 1px solid var(--amber-border);
        }

        .lqe2-audio-notice-title {
            margin: 0;
            font-size: 11px;
            font-weight: 900;
        }

        .lqe2-audio-notice-copy {
            margin: 4px 0 0;
            color: var(--page-muted) !important;
            font-size: 9px;
            line-height: 1.55;
        }

        .lqe2-audio-body {
            padding: 18px;
        }

        .lqe2-audio {
            display: block;
            width: 100% !important;
            height: 40px !important;
        }

        .dark .lqe2-audio {
            color-scheme: dark;
        }

        .lqe2-remove-audio {
            display: flex;
            align-items: center;
            gap: 7px;

            margin-top: 10px;

            color: var(--red) !important;

            font-size: 9px;
            font-weight: 750;
            cursor: pointer;
        }

        .lqe2-remove-audio input {
            width: 14px !important;
            height: 14px !important;
            accent-color: #dc2626;
        }

        .lqe2-file {
            display: block !important;
            width: 100% !important;

            padding: 9px !important;

            color: var(--page-soft-text) !important;
            background: var(--input) !important;

            border: 1px solid var(--border-strong) !important;
            border-radius: 9px !important;

            font-family: inherit !important;
            font-size: 10px !important;
        }

        /* ============================================================
         | RESPONSIVE
         * ============================================================ */

        @media (max-width: 1100px) {
            .lqe2-layout {
                grid-template-columns: minmax(0, 1fr) 290px;
            }
        }

        @media (max-width: 900px) {
            .lqe2-layout {
                grid-template-columns: 1fr;
            }

            .lqe2-sidebar-inner {
                position: static;
            }

            .lqe2-sidebar {
                order: -1;
            }

            .lqe2-settings-body {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 14px;
            }

            .lqe2-settings-body .lqe2-field + .lqe2-field {
                margin-top: 0;
            }

            .lqe2-settings-divider {
                display: none;
            }

            .lqe2-save-actions {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 700px) {
            .lqe2-header {
                flex-direction: column;
                align-items: stretch;
            }

            .lqe2-header > .lqe2-btn {
                width: 100% !important;
            }

            .lqe2-options-grid {
                grid-template-columns: 1fr;
            }

            .lqe2-option.option-e {
                grid-column: auto;
            }

            .lqe2-settings-body {
                grid-template-columns: 1fr;
            }

            .lqe2-settings-body .lqe2-field + .lqe2-field {
                margin-top: 0;
            }
        }

        @media (max-width: 480px) {
            .lqe2-panel-header,
            .lqe2-panel-body,
            .lqe2-audio-body {
                padding-left: 15px;
                padding-right: 15px;
            }

            .lqe2-option {
                padding: 12px;
            }

            .lqe2-save-actions {
                grid-template-columns: 1fr;
            }
        }
    </style>


    <div class="lqe2-page mx-auto w-full max-w-[1280px]">

        {{-- ========================================================= --}}
        {{-- HEADER --}}
        {{-- ========================================================= --}}
        <header class="lqe2-header">

            <div class="lqe2-header-copy">

                <p class="lqe2-eyebrow">
                    Listening Management
                </p>

                <h1 class="lqe2-title">
                    Edit Listening Question
                </h1>

                <p class="lqe2-subtitle">
                    Update the question content, answer choices,
                    classification, correct answer, and assessment score.
                </p>

            </div>


            <a
                href="{{ $backRoute }}"
                class="lqe2-btn lqe2-btn-secondary">

                ← Back to Questions

            </a>

        </header>


        {{-- ========================================================= --}}
        {{-- VALIDATION ERRORS --}}
        {{-- ========================================================= --}}
        @if ($errors->any())

            <div class="lqe2-errors">

                <p class="lqe2-errors-title">
                    Please correct the following errors:
                </p>

                <ul class="lqe2-errors-list">

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
            id="listeningQuestionEditForm"
            action="{{ route(
                'admin.listening-questions.update',
                $question->id
            ) }}"
            method="POST"
            enctype="multipart/form-data">

            @csrf
            @method('PUT')


            <div class="lqe2-layout">

                {{-- ================================================= --}}
                {{-- MAIN COLUMN --}}
                {{-- ================================================= --}}
                <main class="lqe2-main">

                    {{-- ============================================= --}}
                    {{-- QUESTION CONTENT --}}
                    {{-- ============================================= --}}
                    <section class="lqe2-panel">

                        <div class="lqe2-panel-header">

                            <div>

                                <p class="lqe2-panel-kicker">
                                    Content
                                </p>

                                <h2 class="lqe2-panel-title">
                                    Question Content
                                </h2>

                                <p class="lqe2-panel-description">
                                    Define what students will read after
                                    listening to the audio.
                                </p>

                            </div>

                        </div>


                        <div class="lqe2-panel-body">

                            <div class="lqe2-content-grid">

                                {{-- INSTRUCTION --}}
                                <div class="lqe2-field">

                                    <div class="lqe2-label-row">

                                        <label
                                            for="instruction"
                                            class="lqe2-label">

                                            Instruction

                                        </label>

                                        <span class="lqe2-optional">
                                            Optional
                                        </span>

                                    </div>


                                    <textarea
                                        id="instruction"
                                        name="instruction"
                                        rows="3"
                                        placeholder="Example: Listen to the audio and choose the correct answer."
                                        class="lqe2-textarea @error('instruction') lqe2-control-error @enderror">{{ old('instruction', $question->instruction) }}</textarea>


                                    <p class="lqe2-helper">
                                        Use this only when the question needs
                                        additional instructions.
                                    </p>


                                    @error('instruction')

                                        <p class="lqe2-error">
                                            {{ $message }}
                                        </p>

                                    @enderror

                                </div>


                                {{-- QUESTION --}}
                                <div class="lqe2-field">

                                    <div class="lqe2-label-row">

                                        <label
                                            for="question"
                                            class="lqe2-label">

                                            Question
                                            <span class="lqe2-required">*</span>

                                        </label>

                                    </div>


                                    <textarea
                                        id="question"
                                        name="question"
                                        rows="5"
                                        required
                                        placeholder="Enter the Listening question..."
                                        class="lqe2-textarea lqe2-question-textarea @error('question') lqe2-control-error @enderror">{{ old('question', $question->question) }}</textarea>


                                    @error('question')

                                        <p class="lqe2-error">
                                            {{ $message }}
                                        </p>

                                    @enderror

                                </div>

                            </div>

                        </div>

                    </section>


                    {{-- ============================================= --}}
                    {{-- LEGACY AUDIO --}}
                    {{-- ============================================= --}}
                    @if ($isLegacyLessonMode)

                        <section class="lqe2-audio-section">

                            <div class="lqe2-audio-notice">

                                <p class="lqe2-audio-notice-title">
                                    Legacy Per-Question Audio
                                </p>

                                <p class="lqe2-audio-notice-copy">
                                    This question still stores audio directly.
                                    Material-based questions use audio from the
                                    Listening Material instead.
                                </p>

                            </div>


                            <div class="lqe2-audio-body">

                                @if ($currentAudioUrl)

                                    <div class="lqe2-field">

                                        <div class="lqe2-label-row">

                                            <span class="lqe2-label">
                                                Current Audio
                                            </span>

                                        </div>


                                        <audio
                                            controls
                                            preload="metadata"
                                            class="lqe2-audio"
                                            src="{{ $currentAudioUrl }}">
                                        </audio>


                                        <label class="lqe2-remove-audio">

                                            <input
                                                type="checkbox"
                                                name="remove_audio"
                                                value="1"
                                                @checked(old('remove_audio'))>

                                            <span>
                                                Remove current audio
                                            </span>

                                        </label>

                                    </div>

                                @endif


                                <div class="lqe2-field">

                                    <div class="lqe2-label-row">

                                        <label
                                            for="audio_file"
                                            class="lqe2-label">

                                            Replace Audio

                                        </label>

                                        <span class="lqe2-optional">
                                            Optional
                                        </span>

                                    </div>


                                    <input
                                        id="audio_file"
                                        type="file"
                                        name="audio_file"
                                        accept=".mp3,.wav,.m4a,.ogg,audio/mpeg,audio/wav,audio/mp4,audio/ogg"
                                        class="lqe2-file">


                                    @error('audio_file')

                                        <p class="lqe2-error">
                                            {{ $message }}
                                        </p>

                                    @enderror

                                </div>

                            </div>

                        </section>

                    @endif


                    {{-- ============================================= --}}
                    {{-- ANSWER OPTIONS --}}
                    {{-- ============================================= --}}
                    <section class="lqe2-panel">

                        <div class="lqe2-panel-header">

                            <div>

                                <p class="lqe2-panel-kicker">
                                    Multiple Choice
                                </p>

                                <h2 class="lqe2-panel-title">
                                    Answer Options
                                </h2>

                                <p class="lqe2-panel-description">
                                    Options A and B are required.
                                    Options C, D, and E can be left empty.
                                </p>

                            </div>

                        </div>


                        <div class="lqe2-panel-body">

                            <div class="lqe2-options-grid">

                                @foreach (['a', 'b', 'c', 'd', 'e'] as $option)

                                    @php
                                        $field = 'option_' . $option;
                                        $label = strtoupper($option);

                                        $required = in_array(
                                            $option,
                                            ['a', 'b']
                                        );

                                        $isCorrect =
                                            strtoupper($option)
                                            === $selectedCorrectAnswer;
                                    @endphp


                                    <div
                                        class="lqe2-option {{ $option === 'e' ? 'option-e' : '' }} {{ $isCorrect ? 'is-correct' : '' }}"
                                        data-option-card="{{ $label }}">

                                        <div class="lqe2-option-heading">

                                            <div class="lqe2-option-left">

                                                <span class="lqe2-letter">
                                                    {{ $label }}
                                                </span>


                                                <span class="lqe2-option-name">
                                                    Option {{ $label }}
                                                </span>

                                            </div>


                                            <div class="lqe2-option-right">

                                                <span class="lqe2-correct-indicator">
                                                    Correct
                                                </span>


                                                @if ($required)

                                                    <span
                                                        class="lqe2-option-meta required">

                                                        Required

                                                    </span>

                                                @else

                                                    <span class="lqe2-option-meta">
                                                        Optional
                                                    </span>

                                                @endif

                                            </div>

                                        </div>


                                        <input
                                            id="{{ $field }}"
                                            type="text"
                                            name="{{ $field }}"
                                            value="{{ old(
                                                $field,
                                                $question->$field
                                            ) }}"
                                            {{ $required ? 'required' : '' }}
                                            placeholder="Enter option {{ $label }}"
                                            class="lqe2-input @error($field) lqe2-control-error @enderror">


                                        @error($field)

                                            <p class="lqe2-error">
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
                <aside class="lqe2-sidebar">

                    <div class="lqe2-sidebar-inner">

                        {{-- ========================================= --}}
                        {{-- SETTINGS --}}
                        {{-- ========================================= --}}
                        <section class="lqe2-settings">

                            <div class="lqe2-settings-head">

                                <h2 class="lqe2-settings-title">
                                    Question Settings
                                </h2>

                                <p class="lqe2-settings-description">
                                    Configure classification and assessment
                                    values for this question.
                                </p>

                            </div>


                            <div class="lqe2-settings-body">

                                {{-- SUB SKILL --}}
                                <div class="lqe2-field">

                                    <div class="lqe2-label-row">

                                        <label
                                            for="sub_skill"
                                            class="lqe2-label">

                                            Sub-Skill
                                            <span class="lqe2-required">*</span>

                                        </label>

                                    </div>


                                    <select
                                        id="sub_skill"
                                        name="sub_skill"
                                        required
                                        class="lqe2-select @error('sub_skill') lqe2-control-error @enderror">

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

                                        <div class="lqe2-warning">
                                            This question does not have a
                                            sub-skill yet. Select one before
                                            saving.
                                        </div>

                                    @endif


                                    @error('sub_skill')

                                        <p class="lqe2-error">
                                            {{ $message }}
                                        </p>

                                    @enderror

                                </div>


                                <div class="lqe2-settings-divider"></div>


                                {{-- CORRECT ANSWER --}}
                                <div class="lqe2-field">

                                    <div class="lqe2-label-row">

                                        <label
                                            for="correct_answer"
                                            class="lqe2-label">

                                            Correct Answer
                                            <span class="lqe2-required">*</span>

                                        </label>

                                    </div>


                                    <select
                                        id="correct_answer"
                                        name="correct_answer"
                                        required
                                        class="lqe2-select @error('correct_answer') lqe2-control-error @enderror">

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


                                    <p class="lqe2-helper">
                                        The selected option is highlighted
                                        in the answer list.
                                    </p>


                                    @error('correct_answer')

                                        <p class="lqe2-error">
                                            {{ $message }}
                                        </p>

                                    @enderror

                                </div>


                                <div class="lqe2-settings-divider"></div>


                                {{-- SCORE --}}
                                <div class="lqe2-field">

                                    <div class="lqe2-label-row">

                                        <label
                                            for="score"
                                            class="lqe2-label">

                                            Score
                                            <span class="lqe2-required">*</span>

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
                                        class="lqe2-input @error('score') lqe2-control-error @enderror">


                                    <p class="lqe2-helper">
                                        Accepted range: 1–100.
                                    </p>


                                    @error('score')

                                        <p class="lqe2-error">
                                            {{ $message }}
                                        </p>

                                    @enderror

                                </div>

                            </div>

                        </section>


                        {{-- ========================================= --}}
                        {{-- SAVE PANEL --}}
                        {{-- ========================================= --}}
                        <section class="lqe2-save-panel">

                            <div class="lqe2-save-copy">

                                <p class="lqe2-save-title">
                                    Save your changes
                                </p>

                                <p class="lqe2-save-description">
                                    Review the question and answer key before
                                    updating.
                                </p>

                            </div>


                            <div class="lqe2-save-actions">

                                <button
                                    type="submit"
                                    class="lqe2-btn lqe2-btn-primary">

                                    Save Changes

                                </button>


                                <a
                                    href="{{ $backRoute }}"
                                    class="lqe2-btn lqe2-btn-secondary">

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
                document.querySelectorAll('[data-option-card]');

            if (!correctAnswerSelect || !optionCards.length) {
                return;
            }

            function updateCorrectOptionHighlight() {
                const selectedAnswer =
                    correctAnswerSelect.value.toUpperCase();

                optionCards.forEach(function (card) {
                    const option =
                        (card.dataset.optionCard || '')
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
                updateCorrectOptionHighlight
            );

            updateCorrectOptionHighlight();
        });
    </script>
@endsection