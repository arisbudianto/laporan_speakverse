<x-app-layout>

    <div id="listeningQuizPage">

        {{-- =========================================================
            HEADER QUIZ
        ========================================================== --}}
        <section class="listening-card quiz-header-card">
            <div class="quiz-header-decoration"></div>

            <div class="quiz-header-content">
                <div class="quiz-header-copy">
                    <div class="quiz-type-badge">
                        <span aria-hidden="true">🎧</span>
                        <span>Listening Quiz</span>
                    </div>

                    <h1 class="quiz-page-title">
                        {{ $lesson->title }} Quiz
                    </h1>

                    <p class="quiz-page-description">
                        Dengarkan audio dan jawab seluruh pertanyaan yang tersedia.
                    </p>
                </div>

                <div class="quiz-total-card">
                    <span class="quiz-total-label">
                        Total Soal
                    </span>

                    <strong class="quiz-total-value">
                        {{ $questions->count() }} Soal
                    </strong>
                </div>
            </div>
        </section>

        @if ($questions->count())

            {{-- =====================================================
                AUDIO DAN INSTRUKSI UTAMA
            ====================================================== --}}
            @if (
                isset($material) &&
                ($material->instruction || $material->audio_file)
            )
                <section class="listening-card main-audio-card">

                    @if ($material->instruction)
                        <div class="main-instruction">
                            <h2 class="main-instruction-title">
                                Instruction
                            </h2>

                            <p class="main-instruction-text">
                                {{ $material->instruction }}
                            </p>
                        </div>
                    @endif

                    @if ($material->audio_file)
                        <div class="main-audio-row">
                            <div class="audio-information">
                                <div class="audio-icon" aria-hidden="true">
                                    🎧
                                </div>

                                <div class="audio-copy">
                                    <h2 class="audio-title">
                                        Audio Material
                                    </h2>

                                    <p class="audio-description">
                                        Putar audio untuk menjawab soal
                                    </p>
                                </div>
                            </div>

                            <div class="audio-player-container">
                                <audio
                                    controls
                                    preload="metadata"
                                    class="quiz-audio-player"
                                >
                                    <source
                                        src="{{ asset('storage/' . $material->audio_file) }}"
                                    >

                                    Browser kamu tidak mendukung pemutar audio.
                                </audio>
                            </div>
                        </div>
                    @endif
                </section>
            @endif

            {{-- =====================================================
                FORM QUIZ
            ====================================================== --}}
            <section id="quizCard">
                <form id="quizForm" novalidate>
                    @csrf

                    <div class="question-list">
                        @foreach ($questions as $index => $question)
                            <article
                                class="listening-card question-card question-item"
                                data-question-id="{{ $question->id }}"
                            >
                                {{-- HEADER SOAL --}}
                                <div class="question-header">
                                    <span class="question-number">
                                        Soal No. {{ $index + 1 }}
                                    </span>

                                    <span class="question-score">
                                        Poin: {{ $question->score }}
                                    </span>
                                </div>

                                {{-- INSTRUKSI PER SOAL --}}
                                @if ($question->instruction)
                                    <div class="question-instruction">
                                        <h3 class="question-instruction-title">
                                            Instruksi Soal
                                        </h3>

                                        <p class="question-instruction-text">
                                            {{ $question->instruction }}
                                        </p>
                                    </div>
                                @endif

                                {{-- AUDIO PER SOAL --}}
                                @if ($question->audio_file)
                                    <div class="question-audio-box">
                                        <div class="question-audio-information">
                                            <div
                                                class="question-audio-icon"
                                                aria-hidden="true"
                                            >
                                                🎧
                                            </div>

                                            <div>
                                                <h3 class="question-audio-title">
                                                    Audio Soal
                                                </h3>

                                                <p class="question-audio-label">
                                                    Nomor {{ $index + 1 }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="question-audio-player">
                                            <audio
                                                controls
                                                preload="metadata"
                                                class="quiz-audio-player"
                                            >
                                                <source
                                                    src="{{ asset('storage/' . $question->audio_file) }}"
                                                >

                                                Browser kamu tidak mendukung pemutar audio.
                                            </audio>
                                        </div>
                                    </div>
                                @endif

                                {{-- TEKS PERTANYAAN --}}
                                <h2 class="question-text">
                                    {{ $question->question }}
                                </h2>

                                {{-- PILIHAN JAWABAN --}}
                                <div
                                    class="answer-list"
                                    role="radiogroup"
                                    aria-label="Jawaban soal nomor {{ $index + 1 }}"
                                >
                                    @foreach (['A', 'B', 'C', 'D', 'E'] as $option)
                                        @php
                                            $field = 'option_' . strtolower($option);

                                            $inputId =
                                                'question_' .
                                                $question->id .
                                                '_option_' .
                                                strtolower($option);
                                        @endphp

                                        @if (!empty($question->$field))
                                            <label
                                                for="{{ $inputId }}"
                                                class="answer-option"
                                            >
                                                <input
                                                    id="{{ $inputId }}"
                                                    type="radio"
                                                    name="question_{{ $question->id }}"
                                                    value="{{ $option }}"
                                                    class="answer-radio"
                                                >

                                                <span class="answer-text">
                                                    {{ $question->$field }}
                                                </span>
                                            </label>
                                        @endif
                                    @endforeach
                                </div>

                                {{-- Saran perbaikan Modul 5 (perluasan ke Listening) --}}
                                <p class="lq-answer-error" data-answer-error hidden>
                                    Please select one answer before checking.
                                </p>

                                <div class="lq-check-row" data-check-row>
                                    <button type="button" class="lq-check-btn" data-check-btn>
                                        Check
                                    </button>

                                    <button type="button" class="lq-hint-btn" data-hint-btn>
                                        💡 I need a hint
                                    </button>

                                    <span class="lq-checks-remaining" data-checks-remaining hidden></span>
                                </div>

                                <div class="lq-feedback" data-feedback hidden></div>

                                <div class="lq-hint-panel" data-hint-panel hidden>
                                    <p class="lq-hint-label" data-hint-error-label></p>
                                    <div class="lq-hint-history" data-hint-history></div>
                                    <div class="lq-hint-socratic" data-hint-socratic hidden>
                                        <textarea
                                            class="lq-hint-socratic-input"
                                            data-socratic-input
                                            rows="2"
                                            placeholder="Tulis jawaban singkatmu di sini..."></textarea>
                                        <button type="button" class="lq-hint-socratic-submit" data-socratic-submit>
                                            Kirim Jawaban
                                        </button>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    {{-- PESAN VALIDASI --}}
                    <div
                        id="quizValidationMessage"
                        class="quiz-validation-message is-hidden"
                        role="alert"
                    >
                        <div class="validation-icon" aria-hidden="true">
                            !
                        </div>

                        <div>
                            <strong>Jawaban belum lengkap</strong>

                            <p id="quizValidationText">
                                Silakan jawab seluruh soal terlebih dahulu.
                            </p>
                        </div>
                    </div>

                    {{-- =================================================
                        TOMBOL SUBMIT
                    ================================================== --}}
                    <div id="listeningQuizSubmitArea">
                        <button
                            id="listeningQuizSubmitButton"
                            type="button"
                            disabled
                            style="
                                background-color: #059669 !important;
                                background-image: linear-gradient(
                                    135deg,
                                    #10b981 0%,
                                    #047857 100%
                                ) !important;
                                color: #ffffff !important;
                                border-color: #047857 !important;
                                opacity: 1 !important;
                            "
                        >
                            <svg
                                id="submitButtonIcon"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2.25"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                            >
                                <path d="M5 12l4 4L19 6"></path>
                            </svg>

                            <span id="submitBtnText">
                                Finish Quiz
                            </span>
                        </button>
                    </div>
                </form>
            </section>

            {{-- =====================================================
                HASIL QUIZ
            ====================================================== --}}
            <section id="resultSection" class="result-section is-hidden">
                <div class="listening-card result-card">
                    <div class="result-icon" aria-hidden="true">
                        🎉
                    </div>

                    <h2 class="result-title">
                        Quiz Selesai!
                    </h2>

                    <p class="result-description" id="finalMessage">
                        Jawaban kamu telah berhasil disimpan.
                    </p>

                    <div class="result-score-card">
                        <span class="result-score-label">
                            Total Skor Kamu
                        </span>

                        <strong id="finalScore" class="result-score-value">
                            0
                        </strong>
                    </div>
                </div>

                <div class="result-actions">
                    <a
                        href="#"
                        id="reviewLink"
                        class="result-secondary-button"
                        hidden
                    >
                        Lihat Pembahasan
                    </a>

                    <a
                        href="{{ route('student.listening', $lesson) }}"
                        class="result-secondary-button"
                    >
                        Back to Listening
                    </a>

                    <a
                        href="{{ route('missions') }}"
                        class="result-primary-button"
                    >
                        Back to Missions
                    </a>
                </div>
            </section>

        @else
            {{-- =====================================================
                BELUM ADA SOAL
            ====================================================== --}}
            <section class="listening-card empty-state">
                <div class="empty-state-icon" aria-hidden="true">
                    ❓
                </div>

                <h2 class="empty-state-title">
                    No Questions Yet
                </h2>

                <p class="empty-state-description">
                    Admin belum menambahkan soal untuk listening ini.
                </p>
            </section>
        @endif

    </div>

    <style>
        /*
        |--------------------------------------------------------------------------
        | THEME VARIABLES
        |--------------------------------------------------------------------------
        */

        #listeningQuizPage {
            --page-text: #0f172a;
            --muted-text: #64748b;
            --soft-text: #94a3b8;

            --card-background: #ffffff;
            --card-border: #dbe4ef;
            --soft-background: #f8fafc;

            --option-background: #ffffff;
            --option-hover-background: #faf5ff;

            --purple-text: #7e22ce;
            --purple-background: #f3e8ff;
            --purple-border: #e9d5ff;

            display: flex;
            flex-direction: column;
            gap: 24px;

            width: 100%;
            color: var(--page-text);
        }

        html.dark #listeningQuizPage,
        .dark #listeningQuizPage {
            --page-text: #f8fafc;
            --muted-text: #a7b2c5;
            --soft-text: #7f8ba1;

            --card-background: #0f172a;
            --card-border: #2a3950;
            --soft-background: #172033;

            --option-background: #1e293b;
            --option-hover-background: #273449;

            --purple-text: #d8b4fe;
            --purple-background: rgba(168, 85, 247, 0.14);
            --purple-border: rgba(192, 132, 252, 0.3);
        }

        /*
        |--------------------------------------------------------------------------
        | RESET KHUSUS HALAMAN QUIZ
        |--------------------------------------------------------------------------
        */

        #listeningQuizPage *,
        #listeningQuizPage *::before,
        #listeningQuizPage *::after {
            box-sizing: border-box;
        }

        #listeningQuizPage button,
        #listeningQuizPage input,
        #listeningQuizPage audio {
            font: inherit;
        }

        #listeningQuizPage .is-hidden {
            display: none !important;
        }

        /*
        |--------------------------------------------------------------------------
        | CARD
        |--------------------------------------------------------------------------
        */

        #listeningQuizPage .listening-card {
            border: 1px solid var(--card-border) !important;
            border-radius: 28px;

            background-color: var(--card-background) !important;
            color: var(--page-text) !important;

            box-shadow:
                0 1px 2px rgba(15, 23, 42, 0.04),
                0 12px 30px rgba(15, 23, 42, 0.035);
        }

        html.dark #listeningQuizPage .listening-card,
        .dark #listeningQuizPage .listening-card {
            box-shadow:
                0 1px 2px rgba(0, 0, 0, 0.2),
                0 16px 36px rgba(0, 0, 0, 0.12);
        }

        /*
        |--------------------------------------------------------------------------
        | HEADER
        |--------------------------------------------------------------------------
        */

        #listeningQuizPage .quiz-header-card {
            position: relative;
            overflow: hidden;
            padding: 34px;
        }

        #listeningQuizPage .quiz-header-decoration {
            position: absolute;
            top: -100px;
            right: -90px;

            width: 270px;
            height: 270px;

            border-radius: 9999px;
            background: rgba(168, 85, 247, 0.12);

            filter: blur(60px);
            pointer-events: none;
        }

        #listeningQuizPage .quiz-header-content {
            position: relative;
            z-index: 1;

            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 28px;
        }

        #listeningQuizPage .quiz-header-copy {
            min-width: 0;
        }

        #listeningQuizPage .quiz-type-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;

            margin-bottom: 16px;
            padding: 8px 14px;

            border: 1px solid var(--purple-border);
            border-radius: 9999px;

            background-color: var(--purple-background) !important;
            color: var(--purple-text) !important;

            font-size: 14px;
            font-weight: 800;
            line-height: 1;
        }

        #listeningQuizPage .quiz-page-title {
            margin: 0;

            color: var(--page-text) !important;

            font-size: clamp(28px, 4vw, 42px);
            font-weight: 900;
            line-height: 1.15;
        }

        #listeningQuizPage .quiz-page-description {
            max-width: 700px;
            margin: 12px 0 0;

            color: var(--muted-text) !important;

            font-size: 15px;
            line-height: 1.7;
        }

        #listeningQuizPage .quiz-total-card {
            display: flex;
            flex-shrink: 0;
            flex-direction: column;
            gap: 5px;

            min-width: 150px;
            padding: 16px 20px;

            border: 1px solid var(--card-border);
            border-radius: 18px;

            background-color: var(--soft-background) !important;
        }

        #listeningQuizPage .quiz-total-label {
            color: var(--muted-text) !important;

            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        #listeningQuizPage .quiz-total-value {
            color: var(--purple-text) !important;

            font-size: 25px;
            font-weight: 900;
            line-height: 1.2;
        }

        /*
        |--------------------------------------------------------------------------
        | AUDIO UTAMA
        |--------------------------------------------------------------------------
        */

        #listeningQuizPage .main-audio-card {
            padding: 20px;
        }

        #listeningQuizPage .main-instruction {
            margin-bottom: 16px;
            padding: 14px 16px;

            border: 1px solid var(--purple-border);
            border-radius: 16px;

            background-color: var(--purple-background) !important;
        }

        #listeningQuizPage .main-instruction-title,
        #listeningQuizPage .question-instruction-title {
            margin: 0 0 5px;

            color: var(--purple-text) !important;

            font-size: 11px;
            font-weight: 900;
            letter-spacing: 0.09em;
            text-transform: uppercase;
        }

        #listeningQuizPage .main-instruction-text,
        #listeningQuizPage .question-instruction-text {
            margin: 0;

            color: var(--muted-text) !important;

            font-size: 14px;
            line-height: 1.65;
        }

        #listeningQuizPage .main-audio-row {
            display: flex;
            align-items: center;
            gap: 22px;
        }

        #listeningQuizPage .audio-information {
            display: flex;
            flex-shrink: 0;
            align-items: center;
            gap: 12px;
        }

        #listeningQuizPage .audio-icon,
        #listeningQuizPage .question-audio-icon {
            display: flex;
            flex-shrink: 0;
            align-items: center;
            justify-content: center;

            width: 46px;
            height: 46px;

            border-radius: 14px;

            background-color: var(--purple-background) !important;

            font-size: 21px;
        }

        #listeningQuizPage .audio-title {
            margin: 0;

            color: var(--page-text) !important;

            font-size: 15px;
            font-weight: 800;
            line-height: 1.3;
        }

        #listeningQuizPage .audio-description {
            margin: 3px 0 0;

            color: var(--muted-text) !important;

            font-size: 12px;
            line-height: 1.4;
        }

        #listeningQuizPage .audio-player-container {
            min-width: 0;
            flex: 1;
        }

        #listeningQuizPage .quiz-audio-player {
            display: block;
            width: 100%;
            height: 42px;

            color-scheme: light;
        }

        html.dark #listeningQuizPage .quiz-audio-player,
        .dark #listeningQuizPage .quiz-audio-player {
            color-scheme: dark;
        }

        /*
        |--------------------------------------------------------------------------
        | PERTANYAAN
        |--------------------------------------------------------------------------
        */

        #listeningQuizPage .question-list {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        #listeningQuizPage .question-card {
            padding: 26px;

            transition:
                border-color 180ms ease,
                box-shadow 180ms ease;
        }

        #listeningQuizPage .question-card.is-unanswered {
            border-color: #ef4444 !important;

            box-shadow:
                0 0 0 3px rgba(239, 68, 68, 0.1),
                0 12px 30px rgba(239, 68, 68, 0.05);
        }

        #listeningQuizPage .question-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;

            margin-bottom: 18px;
        }

        #listeningQuizPage .question-number {
            display: inline-flex;
            align-items: center;

            padding: 6px 13px;

            border: 1px solid var(--purple-border);
            border-radius: 9999px;

            background-color: var(--purple-background) !important;
            color: var(--purple-text) !important;

            font-size: 12px;
            font-weight: 900;
            line-height: 1;
        }

        #listeningQuizPage .question-score {
            color: var(--muted-text) !important;

            font-size: 12px;
            font-weight: 700;
        }

        #listeningQuizPage .question-instruction {
            margin-bottom: 16px;
            padding: 13px 15px;

            border: 1px solid var(--card-border);
            border-radius: 15px;

            background-color: var(--soft-background) !important;
        }

        #listeningQuizPage .question-audio-box {
            display: flex;
            align-items: center;
            gap: 18px;

            margin-bottom: 18px;
            padding: 14px;

            border: 1px solid var(--card-border);
            border-radius: 16px;

            background-color: var(--soft-background) !important;
        }

        #listeningQuizPage .question-audio-information {
            display: flex;
            flex-shrink: 0;
            align-items: center;
            gap: 11px;
        }

        #listeningQuizPage .question-audio-title {
            margin: 0;

            color: var(--page-text) !important;

            font-size: 12px;
            font-weight: 800;
        }

        #listeningQuizPage .question-audio-label {
            margin: 2px 0 0;

            color: var(--muted-text) !important;

            font-size: 11px;
        }

        #listeningQuizPage .question-audio-player {
            min-width: 0;
            flex: 1;
        }

        #listeningQuizPage .question-text {
            margin: 0 0 18px;

            color: var(--page-text) !important;

            font-size: clamp(17px, 2vw, 21px);
            font-weight: 800;
            line-height: 1.55;
        }

        /*
        |--------------------------------------------------------------------------
        | PILIHAN JAWABAN
        |--------------------------------------------------------------------------
        */

        #listeningQuizPage .answer-list {
            display: flex;
            flex-direction: column;
            gap: 9px;
        }

        #listeningQuizPage .answer-option {
            display: flex;
            align-items: center;
            gap: 13px;

            min-height: 54px;
            padding: 13px 17px;

            border: 1px solid var(--card-border) !important;
            border-radius: 14px;

            background-color: var(--option-background) !important;
            color: var(--page-text) !important;

            cursor: pointer;
            user-select: none;

            transition:
                border-color 160ms ease,
                background-color 160ms ease,
                box-shadow 160ms ease,
                transform 160ms ease;
        }

        #listeningQuizPage .answer-option:hover {
            border-color: #a855f7 !important;
            background-color: var(--option-hover-background) !important;

            transform: translateY(-1px);
        }

        #listeningQuizPage .answer-option.is-selected {
            border-color: #9333ea !important;
            background-color: #faf5ff !important;

            box-shadow:
                0 0 0 1px rgba(147, 51, 234, 0.1),
                0 6px 16px rgba(147, 51, 234, 0.07);
        }

        html.dark #listeningQuizPage .answer-option.is-selected,
        .dark #listeningQuizPage .answer-option.is-selected {
            border-color: #c084fc !important;
            background-color: rgba(126, 34, 206, 0.25) !important;

            box-shadow:
                0 0 0 1px rgba(192, 132, 252, 0.12);
        }

        #listeningQuizPage .answer-radio {
            width: 19px !important;
            height: 19px !important;
            min-width: 19px !important;
            flex-shrink: 0;

            margin: 0 !important;

            accent-color: #9333ea;
            cursor: pointer;
        }

        #listeningQuizPage .answer-text {
            min-width: 0;

            color: var(--page-text) !important;

            font-size: 15px;
            font-weight: 600;
            line-height: 1.55;
        }

        /*
        |--------------------------------------------------------------------------
        | VALIDASI
        |--------------------------------------------------------------------------
        */

        #listeningQuizPage .quiz-validation-message {
            display: flex;
            align-items: flex-start;
            gap: 12px;

            margin-top: 18px;
            padding: 14px 16px;

            border: 1px solid #fecaca;
            border-radius: 14px;

            background-color: #fef2f2 !important;
            color: #991b1b !important;
        }

        html.dark #listeningQuizPage .quiz-validation-message,
        .dark #listeningQuizPage .quiz-validation-message {
            border-color: rgba(248, 113, 113, 0.35);

            background-color: rgba(127, 29, 29, 0.2) !important;
            color: #fecaca !important;
        }

        #listeningQuizPage .validation-icon {
            display: flex;
            flex-shrink: 0;
            align-items: center;
            justify-content: center;

            width: 24px;
            height: 24px;

            border-radius: 9999px;

            background-color: #dc2626 !important;
            color: #ffffff !important;

            font-size: 13px;
            font-weight: 900;
        }

        #listeningQuizPage .quiz-validation-message strong {
            display: block;

            margin-bottom: 2px;

            color: inherit !important;

            font-size: 14px;
            font-weight: 800;
        }

        #listeningQuizPage .quiz-validation-message p {
            margin: 0;

            color: inherit !important;

            font-size: 13px;
            line-height: 1.5;
        }

        /*
        |--------------------------------------------------------------------------
        | TOMBOL SUBMIT
        |--------------------------------------------------------------------------
        */

        #listeningQuizSubmitArea {
            display: flex !important;
            align-items: center !important;
            justify-content: flex-end !important;

            width: 100% !important;
            margin: 0 !important;
            padding: 20px 0 2px !important;

            border: 0 !important;

            background: transparent !important;
            background-color: transparent !important;

            box-shadow: none !important;
        }

        #listeningQuizSubmitButton {
            position: relative !important;

            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 10px !important;

            width: auto !important;
            min-width: 280px !important;
            min-height: 54px !important;

            margin: 0 !important;
            padding: 14px 26px !important;

            border: 1px solid #047857 !important;
            border-radius: 15px !important;

            background: #059669 !important;
            background-color: #059669 !important;
            background-image:
                linear-gradient(
                    135deg,
                    #10b981 0%,
                    #059669 48%,
                    #047857 100%
                ) !important;

            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;

            opacity: 1 !important;
            visibility: visible !important;

            font-family: inherit !important;
            font-size: 15px !important;
            font-weight: 800 !important;
            line-height: 1.2 !important;

            text-decoration: none !important;
            text-shadow: none !important;

            box-shadow:
                0 12px 24px rgba(5, 150, 105, 0.26),
                0 3px 8px rgba(4, 120, 87, 0.16) !important;

            mix-blend-mode: normal !important;
            filter: none !important;

            cursor: pointer !important;

            appearance: none !important;
            -webkit-appearance: none !important;

            isolation: isolate;

            transition:
                transform 160ms ease,
                box-shadow 160ms ease,
                filter 160ms ease !important;
        }

        #listeningQuizSubmitButton *,
        #listeningQuizSubmitButton span,
        #listeningQuizSubmitButton svg {
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;

            opacity: 1 !important;
            visibility: visible !important;

            mix-blend-mode: normal !important;
        }

        #listeningQuizSubmitButton svg {
            width: 20px !important;
            height: 20px !important;
            flex-shrink: 0;

            fill: none !important;
            stroke: #ffffff !important;
        }

        #listeningQuizSubmitButton:hover:not(:disabled) {
            background: #047857 !important;
            background-color: #047857 !important;
            background-image:
                linear-gradient(
                    135deg,
                    #059669 0%,
                    #047857 100%
                ) !important;

            transform: translateY(-2px);

            box-shadow:
                0 16px 30px rgba(5, 150, 105, 0.32),
                0 4px 10px rgba(4, 120, 87, 0.2) !important;
        }

        #listeningQuizSubmitButton:focus-visible {
            outline: 3px solid rgba(16, 185, 129, 0.35) !important;
            outline-offset: 3px !important;
        }

        #listeningQuizSubmitButton:disabled {
            background: #64748b !important;
            background-color: #64748b !important;
            background-image: none !important;

            border-color: #64748b !important;

            opacity: 0.72 !important;

            cursor: not-allowed !important;
            transform: none !important;

            box-shadow: none !important;
        }

        /*
        |--------------------------------------------------------------------------
        | HASIL
        |--------------------------------------------------------------------------
        */

        #listeningQuizPage .result-section {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        #listeningQuizPage .result-card {
            padding: 42px 24px;
            text-align: center;
        }

        #listeningQuizPage .result-icon {
            margin-bottom: 14px;

            font-size: 54px;
            line-height: 1;
        }

        #listeningQuizPage .result-title {
            margin: 0;

            color: var(--page-text) !important;

            font-size: clamp(28px, 4vw, 40px);
            font-weight: 900;
            line-height: 1.2;
        }

        #listeningQuizPage .result-description {
            margin: 9px 0 0;

            color: var(--muted-text) !important;

            font-size: 15px;
        }

        #listeningQuizPage .result-score-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;

            max-width: 340px;
            margin: 28px auto 0;
            padding: 23px;

            border: 1px solid var(--purple-border);
            border-radius: 22px;

            background-color: var(--purple-background) !important;
        }

        #listeningQuizPage .result-score-label {
            color: var(--muted-text) !important;

            font-size: 14px;
            font-weight: 700;
        }

        #listeningQuizPage .result-score-value {
            color: var(--purple-text) !important;

            font-size: clamp(52px, 8vw, 72px);
            font-weight: 900;
            line-height: 1;
        }

        #listeningQuizPage .result-actions {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        #listeningQuizPage .result-secondary-button,
        #listeningQuizPage .result-primary-button {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;

            min-height: 50px;
            padding: 13px 20px;

            border-radius: 14px;

            font-size: 14px;
            font-weight: 800;

            text-align: center;
            text-decoration: none !important;

            transition:
                transform 160ms ease,
                background-color 160ms ease;
        }

        #listeningQuizPage .result-secondary-button {
            border: 1px solid var(--card-border) !important;

            background-color: var(--card-background) !important;
            color: var(--page-text) !important;
        }

        #listeningQuizPage .result-secondary-button:hover {
            background-color: var(--soft-background) !important;

            transform: translateY(-1px);
        }

        #listeningQuizPage .result-primary-button {
            border: 1px solid #6d28d9 !important;

            background:
                linear-gradient(
                    135deg,
                    #9333ea,
                    #4f46e5
                ) !important;

            color: #ffffff !important;

            box-shadow:
                0 9px 20px rgba(126, 34, 206, 0.2);
        }

        #listeningQuizPage .result-primary-button:hover {
            transform: translateY(-1px);

            filter: brightness(1.04);
        }

        /*
        |--------------------------------------------------------------------------
        | EMPTY STATE
        |--------------------------------------------------------------------------
        */

        #listeningQuizPage .empty-state {
            padding: 52px 24px;
            text-align: center;
        }

        #listeningQuizPage .empty-state-icon {
            margin-bottom: 14px;

            font-size: 50px;
        }

        #listeningQuizPage .empty-state-title {
            margin: 0;

            color: var(--page-text) !important;

            font-size: 25px;
            font-weight: 900;
        }

        #listeningQuizPage .empty-state-description {
            margin: 8px 0 0;

            color: var(--muted-text) !important;

            font-size: 14px;
        }

        /*
        |--------------------------------------------------------------------------
        | RESPONSIVE
        |--------------------------------------------------------------------------
        */

        @media (max-width: 767px) {
            #listeningQuizPage {
                gap: 18px;
            }

            #listeningQuizPage .listening-card {
                border-radius: 22px;
            }

            #listeningQuizPage .quiz-header-card {
                padding: 22px;
            }

            #listeningQuizPage .quiz-header-content {
                align-items: stretch;
                flex-direction: column;
                gap: 18px;
            }

            #listeningQuizPage .quiz-total-card {
                width: 100%;
                min-width: 0;
            }

            #listeningQuizPage .main-audio-card {
                padding: 16px;
            }

            #listeningQuizPage .main-audio-row,
            #listeningQuizPage .question-audio-box {
                align-items: stretch;
                flex-direction: column;
            }

            #listeningQuizPage .question-card {
                padding: 19px;
            }

            #listeningQuizPage .question-header {
                margin-bottom: 15px;
            }

            #listeningQuizPage .answer-option {
                min-height: 50px;
                padding: 12px 14px;
            }

            #listeningQuizPage .answer-text {
                font-size: 14px;
            }

            #listeningQuizSubmitArea {
                padding-top: 16px !important;
            }

            #listeningQuizSubmitButton {
                width: 100% !important;
                min-width: 0 !important;
            }

            #listeningQuizPage .result-actions {
                grid-template-columns: 1fr;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Saran perbaikan Modul 5 (perluasan ke Listening) — Check/Hint
        |--------------------------------------------------------------------------
        */

        #listeningQuizPage .lq-answer-error {
            margin: 10px 0 0;
            color: #dc2626 !important;
            font-size: 12px;
            font-weight: 700;
        }

        #listeningQuizPage .lq-check-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
            margin-top: 16px;
        }

        #listeningQuizPage .lq-check-btn,
        #listeningQuizPage .lq-hint-btn,
        #listeningQuizPage .lq-hint-socratic-submit {
            display: inline-flex;
            align-items: center;
            justify-content: center;

            min-height: 42px;
            padding: 10px 18px;

            border-radius: 12px;
            border: 1px solid transparent;

            font-size: 13px;
            font-weight: 800;
            cursor: pointer;

            transition: transform 160ms ease, filter 160ms ease;
        }

        #listeningQuizPage .lq-check-btn {
            background-image: linear-gradient(135deg, #10b981 0%, #047857 100%) !important;
            color: #ffffff !important;
            border-color: #047857 !important;
        }

        #listeningQuizPage .lq-check-btn:hover:not(:disabled) {
            transform: translateY(-1px);
            filter: brightness(1.05);
        }

        #listeningQuizPage .lq-hint-btn {
            border-color: var(--purple-border) !important;
            background-color: var(--purple-background) !important;
            color: var(--purple-text) !important;
        }

        #listeningQuizPage .lq-hint-btn:hover:not(:disabled) {
            filter: brightness(1.03);
        }

        #listeningQuizPage .lq-check-btn:disabled,
        #listeningQuizPage .lq-hint-btn:disabled,
        #listeningQuizPage .lq-hint-socratic-submit:disabled {
            opacity: 0.55;
            cursor: not-allowed;
        }

        #listeningQuizPage .lq-checks-remaining {
            font-size: 11px;
            font-weight: 800;
            color: var(--muted-text) !important;
        }

        #listeningQuizPage .lq-feedback {
            margin-top: 12px;
            padding: 11px 14px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 700;
        }

        #listeningQuizPage .lq-feedback.is-correct {
            background-color: #dcfce7 !important;
            color: #166534 !important;
        }

        #listeningQuizPage .lq-feedback.is-incorrect {
            background-color: #fef2f2 !important;
            color: #991b1b !important;
        }

        #listeningQuizPage .lq-feedback.is-locked {
            background-color: var(--soft-background) !important;
            color: var(--muted-text) !important;
        }

        html.dark #listeningQuizPage .lq-feedback.is-correct,
        .dark #listeningQuizPage .lq-feedback.is-correct {
            background-color: rgba(22, 163, 74, 0.18) !important;
            color: #86efac !important;
        }

        html.dark #listeningQuizPage .lq-feedback.is-incorrect,
        .dark #listeningQuizPage .lq-feedback.is-incorrect {
            background-color: rgba(220, 38, 38, 0.18) !important;
            color: #fca5a5 !important;
        }

        #listeningQuizPage .lq-hint-panel {
            margin-top: 12px;
            padding: 14px;
            border: 1px solid var(--purple-border);
            border-radius: 16px;
            background-color: var(--purple-background) !important;
        }

        #listeningQuizPage .lq-hint-label {
            margin: 0 0 8px;
            font-size: 11px;
            font-weight: 800;
            color: var(--purple-text) !important;
        }

        #listeningQuizPage .lq-hint-history {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        #listeningQuizPage .lq-hint-item {
            padding: 9px 12px;
            border-radius: 10px;
            background-color: var(--card-background) !important;
            border: 1px solid var(--card-border);
            font-size: 12px;
            line-height: 1.5;
            color: var(--muted-text) !important;
        }

        #listeningQuizPage .lq-hint-item-level {
            display: inline-block;
            margin-bottom: 4px;
            padding: 2px 8px;
            border-radius: 999px;
            background-color: #9333ea;
            color: #ffffff;
            font-size: 10px;
            font-weight: 900;
        }

        #listeningQuizPage .lq-hint-socratic {
            margin-top: 10px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        #listeningQuizPage .lq-hint-socratic-input {
            width: 100%;
            padding: 9px 12px;
            border: 1px solid var(--card-border);
            border-radius: 10px;
            background-color: var(--card-background) !important;
            color: var(--page-text) !important;
            font-size: 12px;
            resize: vertical;
        }

        #listeningQuizPage .lq-hint-socratic-submit {
            align-self: flex-start;
            background-color: var(--card-background) !important;
            color: var(--page-text) !important;
            border-color: var(--card-border) !important;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const maxChecksPerQuestion = {{ (int) config('learning.scoring.max_checks_per_question', 3) }};

            const checkUrl = @json(route('student.listening.check', $lesson));
            const hintUrl = @json(route('student.listening.hint', $lesson));
            const finishUrl = @json(route('student.listening.finish', $lesson));
            const shownUrl = @json(route('student.listening.shown', $lesson));
            const abandonUrl = @json(route('student.listening.abandon', $lesson));

            // Modul 1 — Interaction Logger: sesi kuis ini (null kalau
            // logging gagal dibuka server-side, mis. tidak ada soal).
            const quizSessionId = @json($quizSessionId ?? null);
            const csrfToken = @json(csrf_token());

            const cards = Array.from(document.querySelectorAll('#listeningQuizPage .question-item'));
            const quizCard = document.getElementById('quizCard');
            const resultSection = document.getElementById('resultSection');
            const finalScore = document.getElementById('finalScore');
            const finalMessage = document.getElementById('finalMessage');
            const reviewLink = document.getElementById('reviewLink');

            const finishBtn = document.getElementById('listeningQuizSubmitButton');
            const finishBtnText = document.getElementById('submitBtnText');
            const finishBtnIcon = document.getElementById('submitButtonIcon');

            if (cards.length === 0 || !finishBtn) {
                return;
            }

            // Semua soal tampil sekaligus di halaman ini, jadi "waktu
            // soal pertama tampil" = saat halaman selesai dimuat, sama
            // untuk semua soal.
            const pageLoadedAt = Date.now();
            let quizFinished = false;

            // Saran perbaikan Modul 1: beri tahu server bahwa SEMUA
            // soal sudah tampil sekarang, supaya response_ms nanti
            // dihitung dari jam SERVER. Fire-and-forget per soal.
            if (quizSessionId) {
                cards.forEach(function (card) {
                    const questionId = card.dataset.questionId;
                    fetch(shownUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            quiz_session_id: quizSessionId,
                            question_id: Number(questionId)
                        }),
                        keepalive: true
                    }).catch(function (error) {
                        console.warn('markShown gagal (tidak fatal):', error);
                    });
                });
            }

            // Saran perbaikan Modul 1: catat 'abandon' kalau siswa
            // menutup tab / pindah halaman SEBELUM Finish.
            window.addEventListener('pagehide', function () {
                if (quizFinished || !quizSessionId) {
                    return;
                }
                const data = new URLSearchParams();
                data.append('_token', csrfToken);
                data.append('quiz_session_id', quizSessionId);
                navigator.sendBeacon(abandonUrl, data);
            });

            const state = {};
            cards.forEach(function (card) {
                state[card.dataset.questionId] = {
                    locked: false,
                    isCorrect: null,
                    checksUsed: 0,
                    checksRemaining: maxChecksPerQuestion,
                    socraticStep: 0
                };
            });

            let isChecking = false;
            let isRequestingHint = false;
            let isFinishing = false;

            function getSelectedAnswer(card) {
                const questionId = card.dataset.questionId;
                return card.querySelector('input[name="question_' + questionId + '"]:checked');
            }

            function setOptionsDisabled(card, disabled) {
                card.querySelectorAll('.answer-radio').forEach(function (input) {
                    input.disabled = disabled;
                });
            }

            function clearError(card) {
                const error = card.querySelector('[data-answer-error]');
                if (error) {
                    error.hidden = true;
                }
            }

            function showError(card) {
                const error = card.querySelector('[data-answer-error]');
                if (error) {
                    error.hidden = false;
                }
            }

            function updateFinishAvailability() {
                const allLocked = cards.every(function (card) {
                    const s = state[card.dataset.questionId];
                    return s && s.locked;
                });
                finishBtn.disabled = !allLocked;
            }

            function renderCardState(card) {
                const questionId = card.dataset.questionId;
                const qState = state[questionId];

                const checkBtn = card.querySelector('[data-check-btn]');
                const hintBtn = card.querySelector('[data-hint-btn]');
                const checksRemainingEl = card.querySelector('[data-checks-remaining]');
                const feedbackEl = card.querySelector('[data-feedback]');

                setOptionsDisabled(card, qState.locked);

                if (qState.checksUsed === 0) {
                    checkBtn.hidden = false;
                    checkBtn.disabled = false;
                    checkBtn.textContent = 'Check';
                    checksRemainingEl.hidden = true;
                    feedbackEl.hidden = true;
                    hintBtn.hidden = false;
                    updateFinishAvailability();
                    return;
                }

                checksRemainingEl.hidden = false;
                checksRemainingEl.textContent = qState.checksRemaining > 0
                    ? qState.checksRemaining + ' check' + (qState.checksRemaining === 1 ? '' : 's') + ' left'
                    : 'No checks left';

                feedbackEl.hidden = false;

                if (qState.isCorrect) {
                    feedbackEl.className = 'lq-feedback is-correct';
                    feedbackEl.textContent = 'Correct! This question is locked in.';
                    checkBtn.hidden = true;
                    hintBtn.hidden = true;
                } else if (qState.locked) {
                    feedbackEl.className = 'lq-feedback is-locked';
                    feedbackEl.textContent = "You've used all your checks for this question. You can still read hints to understand it.";
                    checkBtn.hidden = true;
                    hintBtn.hidden = false;
                } else {
                    feedbackEl.className = 'lq-feedback is-incorrect';
                    feedbackEl.textContent = 'Not quite — try again, or ask for a hint.';
                    checkBtn.hidden = false;
                    checkBtn.disabled = false;
                    checkBtn.textContent = 'Check Again';
                    hintBtn.hidden = false;
                }

                updateFinishAvailability();
            }

            async function performCheck(card) {
                if (isChecking) {
                    return;
                }

                const questionId = card.dataset.questionId;
                const selected = getSelectedAnswer(card);

                if (!selected) {
                    showError(card);
                    return;
                }
                clearError(card);

                const checkBtn = card.querySelector('[data-check-btn]');
                isChecking = true;
                checkBtn.disabled = true;
                checkBtn.textContent = 'Checking...';

                try {
                    const response = await fetch(checkUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            quiz_session_id: quizSessionId,
                            question_id: Number(questionId),
                            selected_answer: selected.value,
                            response_ms: Date.now() - pageLoadedAt
                        })
                    });

                    const result = await response.json();

                    if (!response.ok) {
                        throw new Error(result.message || 'Could not check this answer.');
                    }

                    state[questionId] = {
                        locked: Boolean(result.locked),
                        isCorrect: Boolean(result.is_correct),
                        checksUsed: result.checks_used,
                        checksRemaining: result.checks_remaining,
                        socraticStep: state[questionId].socraticStep
                    };

                    renderCardState(card);
                } catch (error) {
                    console.error('Listening check error:', error);
                    alert(error.message || 'Could not check this answer. Please try again.');
                    const qState = state[questionId];
                    checkBtn.disabled = false;
                    checkBtn.textContent = qState.checksUsed > 0 ? 'Check Again' : 'Check';
                } finally {
                    isChecking = false;
                }
            }

            function appendHintHistoryItem(card, levelLabel, text) {
                const history = card.querySelector('[data-hint-history]');
                const item = document.createElement('div');
                item.className = 'lq-hint-item';

                const badge = document.createElement('span');
                badge.className = 'lq-hint-item-level';
                badge.textContent = levelLabel;

                const body = document.createElement('div');
                body.textContent = text;

                item.appendChild(badge);
                item.appendChild(body);
                history.appendChild(item);
            }

            async function requestHint(card, socraticAnswer, socraticStep) {
                if (isRequestingHint) {
                    return;
                }

                const questionId = card.dataset.questionId;
                const hintBtn = card.querySelector('[data-hint-btn]');
                const hintPanel = card.querySelector('[data-hint-panel]');
                const hintErrorLabel = card.querySelector('[data-hint-error-label]');
                const hintSocratic = card.querySelector('[data-hint-socratic]');
                const socraticInput = card.querySelector('[data-socratic-input]');
                const socraticSubmitBtn = card.querySelector('[data-socratic-submit]');

                isRequestingHint = true;
                hintBtn.disabled = true;
                socraticSubmitBtn.disabled = true;

                try {
                    const payload = {
                        quiz_session_id: quizSessionId,
                        question_id: Number(questionId)
                    };
                    if (socraticAnswer !== undefined) {
                        payload.socratic_answer = socraticAnswer;
                        payload.socratic_step = socraticStep;
                    }

                    const response = await fetch(hintUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    });

                    const result = await response.json();

                    if (!response.ok) {
                        throw new Error(result.message || 'Could not load a hint right now.');
                    }

                    hintPanel.hidden = false;
                    hintErrorLabel.textContent = result.error_hint_label || '';
                    hintErrorLabel.hidden = !result.error_hint_label;

                    if (result.socratic_complete === true) {
                        appendHintHistoryItem(
                            card,
                            'L3',
                            "You've worked through all the guiding questions. Try selecting your answer again!"
                        );
                        hintSocratic.hidden = true;
                    } else if (result.socratic_question) {
                        appendHintHistoryItem(card, 'L3 · Q' + (result.socratic_step + 1), result.socratic_question);
                        state[questionId].socraticStep = result.socratic_step;
                        hintSocratic.hidden = false;
                        socraticInput.value = '';
                        socraticInput.focus();
                    } else if (result.hint_text) {
                        appendHintHistoryItem(card, 'L' + result.level, result.hint_text);
                        hintSocratic.hidden = true;
                    }
                } catch (error) {
                    console.error('Listening hint error:', error);
                    alert(error.message || 'Could not load a hint right now. Please try again.');
                } finally {
                    isRequestingHint = false;
                    hintBtn.disabled = false;
                    socraticSubmitBtn.disabled = false;
                }
            }

            cards.forEach(function (card) {
                card.querySelectorAll('.answer-radio').forEach(function (input) {
                    input.addEventListener('change', function () {
                        const answerList = input.closest('.answer-list');
                        if (answerList) {
                            answerList.querySelectorAll('.answer-option').forEach(function (label) {
                                label.classList.remove('is-selected');
                            });
                        }
                        const selectedLabel = input.closest('.answer-option');
                        if (selectedLabel) {
                            selectedLabel.classList.add('is-selected');
                        }
                        clearError(card);
                    });
                });

                card.querySelector('[data-check-btn]').addEventListener('click', function () {
                    performCheck(card);
                });

                card.querySelector('[data-hint-btn]').addEventListener('click', function () {
                    requestHint(card);
                });

                card.querySelector('[data-socratic-submit]').addEventListener('click', function () {
                    const socraticInput = card.querySelector('[data-socratic-input]');
                    const answer = socraticInput.value.trim();
                    if (answer === '') {
                        socraticInput.focus();
                        return;
                    }
                    const questionId = card.dataset.questionId;
                    requestHint(card, answer, state[questionId].socraticStep);
                });
            });

            finishBtn.addEventListener('click', async function () {
                if (isFinishing || finishBtn.disabled) {
                    return;
                }

                isFinishing = true;
                finishBtn.disabled = true;
                if (finishBtnText) {
                    finishBtnText.textContent = 'Saving result...';
                }
                if (finishBtnIcon) {
                    finishBtnIcon.style.display = 'none';
                }

                try {
                    const response = await fetch(finishUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            quiz_session_id: quizSessionId
                        })
                    });

                    const result = await response.json();

                    if (!response.ok || !result.success) {
                        throw new Error(result.message || 'The result could not be saved.');
                    }

                    quizFinished = true;

                    if (finalScore) {
                        finalScore.textContent = result.score ?? 0;
                    }
                    if (finalMessage) {
                        finalMessage.textContent = result.message || 'Jawaban kamu telah berhasil disimpan.';
                    }
                    if (reviewLink && result.review_url) {
                        reviewLink.href = result.review_url;
                        reviewLink.hidden = false;
                    }

                    document.querySelectorAll('#listeningQuizPage audio').forEach(function (audio) {
                        audio.pause();
                    });

                    if (quizCard) {
                        quizCard.classList.add('is-hidden');
                    }

                    if (resultSection) {
                        resultSection.classList.remove('is-hidden');
                        resultSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                } catch (error) {
                    console.error('Listening finish error:', error);
                    alert(error.message || 'The result could not be saved. Please try again.');
                    isFinishing = false;
                    finishBtn.disabled = false;
                    if (finishBtnText) {
                        finishBtnText.textContent = 'Finish Quiz';
                    }
                    if (finishBtnIcon) {
                        finishBtnIcon.style.display = '';
                    }
                }
            });

            cards.forEach(function (card) {
                renderCardState(card);
            });
        });
    </script>

</x-app-layout>