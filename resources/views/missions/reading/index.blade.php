<x-app-layout>
    @php
        $questionCount = $material->questions->count();

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

        $estimatedMinutes = max(
            5,
            min(
                20,
                (int) ceil(
                    max(1, str_word_count(strip_tags($rawPassage))) / 150
                ) + 3
            )
        );
    @endphp

    <style>
        .reading-unit-page {
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
            --warning: #d97706;
            --warning-soft: #fffbeb;
            --shadow: 0 16px 40px rgba(15, 23, 42, 0.08);
            --shadow-soft: 0 7px 20px rgba(15, 23, 42, 0.06);

            color: var(--text);
        }

        html.dark .reading-unit-page,
        body.dark .reading-unit-page,
        .dark .reading-unit-page,
        html[data-theme="dark"] .reading-unit-page,
        body[data-theme="dark"] .reading-unit-page {
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
            --warning: #fbbf24;
            --warning-soft: #33250c;
            --shadow: 0 20px 48px rgba(0, 0, 0, 0.34);
            --shadow-soft: 0 9px 24px rgba(0, 0, 0, 0.24);
        }

        .reading-unit-page,
        .reading-unit-page * {
            box-sizing: border-box;
        }

        .reading-unit-page {
            width: min(100%, 1320px);
            margin-inline: auto;
        }

        .reading-unit-page .reading-unit-stack {
            display: grid;
            gap: 16px;
        }

        .reading-unit-page .reading-unit-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 22px;
            box-shadow: var(--shadow-soft);
        }

        /*
        |--------------------------------------------------------------------------
        | Hero
        |--------------------------------------------------------------------------
        */

        .reading-unit-page .reading-unit-hero {
            position: relative;
            overflow: hidden;
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: center;
            gap: 24px;
            padding: 23px 25px;
        }

        .reading-unit-page .reading-unit-hero::before {
            content: "";
            position: absolute;
            width: 240px;
            height: 240px;
            right: -100px;
            top: -140px;
            border-radius: 999px;
            background: rgba(34, 211, 238, 0.12);
            filter: blur(7px);
            pointer-events: none;
        }

        .reading-unit-page .reading-unit-hero::after {
            content: "";
            position: absolute;
            width: 210px;
            height: 210px;
            left: -130px;
            bottom: -155px;
            border-radius: 999px;
            background: rgba(37, 99, 235, 0.09);
            filter: blur(10px);
            pointer-events: none;
        }

        .reading-unit-page .reading-unit-hero-copy,
        .reading-unit-page .reading-unit-stats {
            position: relative;
            z-index: 1;
        }

        .reading-unit-page .reading-unit-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-height: 29px;
            padding: 6px 11px;
            border: 1px solid rgba(8, 145, 178, 0.18);
            border-radius: 999px;
            background: var(--accent-soft);
            color: var(--accent);
            font-size: 11px;
            line-height: 1;
            font-weight: 900;
            letter-spacing: 0.09em;
            text-transform: uppercase;
        }

        .reading-unit-page .reading-unit-eyebrow svg {
            width: 15px;
            height: 15px;
        }

        .reading-unit-page .reading-unit-title {
            margin: 12px 0 0;
            color: var(--text);
            font-size: clamp(30px, 3vw, 42px);
            line-height: 1.06;
            font-weight: 900;
            letter-spacing: -0.04em;
        }

        .reading-unit-page .reading-unit-subtitle {
            max-width: 690px;
            margin: 8px 0 0;
            color: var(--text-muted);
            font-size: 14px;
            line-height: 1.65;
            font-weight: 600;
        }

        .reading-unit-page .reading-unit-stats {
            display: grid;
            grid-template-columns: repeat(2, minmax(112px, 1fr));
            gap: 9px;
            min-width: 255px;
        }

        .reading-unit-page .reading-unit-stat {
            padding: 13px 14px;
            border: 1px solid var(--border);
            border-radius: 15px;
            background: var(--card-soft);
        }

        .reading-unit-page .reading-unit-stat-label {
            color: var(--text-muted);
            font-size: 10px;
            line-height: 1.4;
            font-weight: 850;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .reading-unit-page .reading-unit-stat-value {
            margin-top: 4px;
            color: var(--text);
            font-size: 23px;
            line-height: 1;
            font-weight: 900;
            letter-spacing: -0.03em;
        }

        .reading-unit-page .reading-unit-stat-value.is-cyan {
            color: var(--accent);
        }

        .reading-unit-page .reading-unit-stat-value.is-blue {
            color: var(--accent-strong);
        }

        .reading-unit-page .reading-unit-stat-value.is-warning {
            color: var(--warning);
        }

        .reading-unit-page .reading-unit-stat-value.is-success {
            color: var(--success);
        }

        /*
        |--------------------------------------------------------------------------
        | Material
        |--------------------------------------------------------------------------
        */

        .reading-unit-page .reading-material-card {
            overflow: hidden;
        }

        .reading-unit-page .reading-material-header {
            display: flex;
            align-items: center;
            gap: 13px;
            padding: 16px 18px;
            border-bottom: 1px solid var(--border);
            background: var(--card);
        }

        .reading-unit-page .reading-material-icon {
            display: inline-flex;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            border-radius: 13px;
            background: var(--accent-soft);
            color: var(--accent);
        }

        .reading-unit-page .reading-material-icon svg {
            width: 21px;
            height: 21px;
        }

        .reading-unit-page .reading-material-heading {
            min-width: 0;
        }

        .reading-unit-page .reading-material-kicker {
            margin: 0;
            color: var(--accent);
            font-size: 10px;
            line-height: 1.3;
            font-weight: 900;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        .reading-unit-page .reading-material-title {
            margin: 4px 0 0;
            color: var(--text);
            font-size: 20px;
            line-height: 1.3;
            font-weight: 900;
            letter-spacing: -0.02em;
        }

        .reading-unit-page .reading-material-meta {
            margin: 3px 0 0;
            color: var(--text-muted);
            font-size: 11px;
            line-height: 1.45;
            font-weight: 650;
        }

        .reading-unit-page .reading-material-body {
            padding: 18px;
        }

        .reading-unit-page .reading-material-intro {
            display: grid;
            gap: 14px;
        }

        .reading-unit-page .reading-instruction {
            padding: 13px 14px;
            border: 1px solid rgba(8, 145, 178, 0.22);
            border-radius: 14px;
            background: var(--accent-soft);
        }

        .reading-unit-page .reading-instruction-label {
            margin: 0;
            color: var(--accent);
            font-size: 10px;
            line-height: 1.3;
            font-weight: 900;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        .reading-unit-page .reading-instruction-text {
            margin: 4px 0 0;
            color: var(--text-soft);
            font-size: 13px;
            line-height: 1.6;
            font-weight: 650;
        }

        .reading-unit-page .reading-material-image {
            overflow: hidden;
            border: 1px solid var(--border);
            border-radius: 16px;
            background: var(--card-soft);
        }

        .reading-unit-page .reading-material-image img {
            display: block;
            width: 100%;
            max-height: 360px;
            object-fit: contain;
            padding: 10px;
        }

        .reading-unit-page .reading-passage {
            margin-top: 16px;
            color: var(--text-soft);
            font-size: 14px;
            line-height: 1.82;
            font-weight: 520;
        }

        .reading-unit-page .reading-passage p {
            margin: 0 0 13px;
        }

        .reading-unit-page .reading-passage p:last-child {
            margin-bottom: 0;
        }

        /*
        |--------------------------------------------------------------------------
        | CTA
        |--------------------------------------------------------------------------
        */

        .reading-unit-page .reading-cta {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: center;
            gap: 18px;
            padding: 18px;
        }

        .reading-unit-page .reading-cta-copy {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            min-width: 0;
        }

        .reading-unit-page .reading-cta-icon {
            display: inline-flex;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: var(--success-soft);
            color: var(--success);
        }

        .reading-unit-page .reading-cta-icon svg {
            width: 20px;
            height: 20px;
        }

        .reading-unit-page .reading-cta-title {
            margin: 0;
            color: var(--text);
            font-size: 17px;
            line-height: 1.35;
            font-weight: 900;
            letter-spacing: -0.015em;
        }

        .reading-unit-page .reading-cta-text {
            margin: 4px 0 0;
            color: var(--text-muted);
            font-size: 12px;
            line-height: 1.55;
            font-weight: 650;
        }

        .reading-unit-page .reading-start-button {
            display: inline-flex;
            min-height: 50px;
            min-width: 205px;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 11px 18px;
            border: 0;
            border-radius: 14px;
            background: linear-gradient(
                100deg,
                var(--accent),
                var(--accent-strong)
            );
            color: #ffffff;
            font-family: inherit;
            font-size: 14px;
            line-height: 1;
            font-weight: 900;
            text-decoration: none;
            box-shadow: 0 10px 24px rgba(8, 145, 178, 0.17);
            transition:
                transform 160ms ease,
                filter 160ms ease,
                box-shadow 160ms ease;
        }

        .reading-unit-page .reading-start-button svg {
            width: 18px;
            height: 18px;
        }

        .reading-unit-page .reading-start-button:hover {
            transform: translateY(-1px);
            filter: brightness(1.05);
            box-shadow: var(--shadow);
        }

        .reading-unit-page .reading-start-button:focus-visible {
            outline: 3px solid rgba(34, 211, 238, 0.25);
            outline-offset: 2px;
        }

        /*
        |--------------------------------------------------------------------------
        | Responsive
        |--------------------------------------------------------------------------
        */

        @media (max-width: 820px) {
            .reading-unit-page .reading-unit-hero {
                grid-template-columns: minmax(0, 1fr);
            }

            .reading-unit-page .reading-unit-stats {
                min-width: 0;
                width: 100%;
            }
        }

        @media (max-width: 640px) {
            .reading-unit-page .reading-unit-stack {
                gap: 14px;
            }

            .reading-unit-page .reading-unit-hero,
            .reading-unit-page .reading-material-body,
            .reading-unit-page .reading-cta {
                padding: 16px;
            }

            .reading-unit-page .reading-unit-title {
                font-size: 30px;
            }

            .reading-unit-page .reading-material-header {
                padding: 14px 16px;
            }

            .reading-unit-page .reading-material-title {
                font-size: 18px;
            }

            .reading-unit-page .reading-passage {
                font-size: 13.5px;
                line-height: 1.75;
            }

            .reading-unit-page .reading-cta {
                grid-template-columns: minmax(0, 1fr);
            }

            .reading-unit-page .reading-start-button {
                width: 100%;
                min-width: 0;
            }
        }

        @media (max-width: 420px) {
            .reading-unit-page .reading-unit-stats {
                grid-template-columns: minmax(0, 1fr);
            }
        }
    </style>

    <div class="reading-unit-page">
        <div class="reading-unit-stack">

            {{-- HERO --}}
            <section class="reading-unit-card reading-unit-hero">
                <div class="reading-unit-hero-copy">
                    <span class="reading-unit-eyebrow">
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

                        Reading Mission
                    </span>

                    <h1 class="reading-unit-title">
                        {{ $lesson->title }}
                    </h1>

                    <p class="reading-unit-subtitle">
                        {{ $lesson->description
                            ?? 'Read the passage carefully, identify the main ideas, and continue to the exercise when you are ready.' }}
                    </p>
                </div>

                <div class="reading-unit-stats">
                    <article class="reading-unit-stat">
                        <div class="reading-unit-stat-label">
                            Questions
                        </div>

                        <div class="reading-unit-stat-value is-cyan">
                            {{ $questionCount }}
                        </div>
                    </article>

                    <article class="reading-unit-stat">
                        <div class="reading-unit-stat-label">
                            Estimated Time
                        </div>

                        <div class="reading-unit-stat-value is-blue">
                            {{ $estimatedMinutes }}m
                        </div>
                    </article>

                    <article class="reading-unit-stat">
                        <div class="reading-unit-stat-label">
                            Level
                        </div>

                        <div class="reading-unit-stat-value is-warning">
                            Basic
                        </div>
                    </article>

                    <article class="reading-unit-stat">
                        <div class="reading-unit-stat-label">
                            Status
                        </div>

                        <div class="reading-unit-stat-value is-success">
                            Ready
                        </div>
                    </article>
                </div>
            </section>

            {{-- MATERIAL --}}
            <section class="reading-unit-card reading-material-card">
                <header class="reading-material-header">
                    <span class="reading-material-icon">
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
                    </span>

                    <div class="reading-material-heading">
                        <p class="reading-material-kicker">
                            Reading Material
                        </p>

                        <h2 class="reading-material-title">
                            {{ $material->title }}
                        </h2>

                        <p class="reading-material-meta">
                            Read the passage carefully before starting the exercise.
                        </p>
                    </div>
                </header>

                <div class="reading-material-body">
                    <div class="reading-material-intro">
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
                                    loading="eager">
                            </figure>
                        @endif
                    </div>

                    <article class="reading-passage">
                        @forelse ($passageParagraphs as $paragraph)
                            <p>
                                {{ preg_replace(
                                    '/\s+/u',
                                    ' ',
                                    trim($paragraph)
                                ) }}
                            </p>
                        @empty
                            <p>
                                Reading passage is not available.
                            </p>
                        @endforelse
                    </article>
                </div>
            </section>

            {{-- CTA --}}
            <section class="reading-unit-card reading-cta">
                <div class="reading-cta-copy">
                    <span class="reading-cta-icon">
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
                    </span>

                    <div>
                        <h2 class="reading-cta-title">
                            Ready for the Exercise?
                        </h2>

                        <p class="reading-cta-text">
                            Continue when you understand the passage and are ready
                            to answer {{ $questionCount }}
                            {{ \Illuminate\Support\Str::plural('question', $questionCount) }}.
                        </p>
                    </div>
                </div>

                <a
                    href="{{ route('student.reading.quiz', $lesson) }}"
                    class="reading-start-button">
                    Start Exercise

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
            </section>

        </div>
    </div>
</x-app-layout>