<x-app-layout>
    @php
        /*
        |--------------------------------------------------------------------------
        | Assessment Result Data
        |--------------------------------------------------------------------------
        */
        $typeLabel = strtoupper(
            $submission->type ?? 'assessment'
        );

        $lessonTitle =
            $submission->lesson?->title
            ?? 'Speaking Assessment';

        $unitTitle =
            $submission->unit?->title
            ?? $typeLabel;

        $finalScore =
            $submission->final_score !== null
                ? max(0, min(100, (int) $submission->final_score))
                : null;

        $criteriaScores =
            is_array($submission->criteria_scores)
                ? $submission->criteria_scores
                : [];

        /*
        |--------------------------------------------------------------------------
        | Assessment Answer
        |--------------------------------------------------------------------------
        */
        $answer =
            $submission->answers->first();

        $transcript =
            $answer?->answer
            ?: null;

        $feedback =
            $submission->feedback
            ?: $answer?->feedback
            ?: null;

        $submittedAt =
            $submission->submitted_at
            ?? $submission->created_at;

        $status =
            strtolower(
                (string) $submission->status
            );

        /*
        |--------------------------------------------------------------------------
        | Score State
        |--------------------------------------------------------------------------
        */
        if ($finalScore === null) {
            $scoreTone = 'waiting';
            $scoreLabel = 'Waiting for Evaluation';
            $scoreDescription =
                'Your speaking assessment has been submitted and is waiting for evaluation.';
        } elseif ($finalScore >= 85) {
            $scoreTone = 'excellent';
            $scoreLabel = 'Excellent';
            $scoreDescription =
                'Your presentation demonstrates very strong speaking performance.';
        } elseif ($finalScore >= 75) {
            $scoreTone = 'very-good';
            $scoreLabel = 'Very Good';
            $scoreDescription =
                'Your presentation is clear and covers most assessment requirements well.';
        } elseif ($finalScore >= 65) {
            $scoreTone = 'good';
            $scoreLabel = 'Good';
            $scoreDescription =
                'Your presentation is understandable, but several areas can still be improved.';
        } elseif ($finalScore >= 50) {
            $scoreTone = 'developing';
            $scoreLabel = 'Developing';
            $scoreDescription =
                'Your presentation shows progress but still needs more practice.';
        } else {
            $scoreTone = 'improve';
            $scoreLabel = 'Needs Improvement';
            $scoreDescription =
                'Review the feedback and practise the required speaking elements again.';
        }

        $statusTone = match ($status) {
            'completed' => 'completed',
            'pending' => 'pending',
            'failed' => 'failed',
            default => 'neutral',
        };

        /*
        |--------------------------------------------------------------------------
        | Criteria Cards
        |--------------------------------------------------------------------------
        */
        $criteria = [
            [
                'key' => 'content_relevance',
                'label' => 'Story Content',
                'short_label' => 'Content',
                'description' =>
                    'Completeness of title, characters, setting, plot, conflict, resolution, moral value, and opinion.',
                'tone' => 'purple',
            ],
            [
                'key' => 'fluency',
                'label' => 'Fluency',
                'short_label' => 'Fluency',
                'description' =>
                    'Smoothness, clarity, pauses, hesitation, and logical flow of the presentation.',
                'tone' => 'blue',
            ],
            [
                'key' => 'pronunciation',
                'label' => 'Pronunciation',
                'short_label' => 'Pronunciation',
                'description' =>
                    'Estimated clarity and understandability based on the generated transcript.',
                'tone' => 'cyan',
            ],
            [
                'key' => 'vocabulary',
                'label' => 'Vocabulary',
                'short_label' => 'Vocabulary',
                'description' =>
                    'Accuracy, appropriateness, and variation of vocabulary used to explain the story.',
                'tone' => 'green',
            ],
            [
                'key' => 'grammar_accuracy',
                'label' => 'Grammar Accuracy',
                'short_label' => 'Grammar',
                'description' =>
                    'Accuracy and variation of sentence structures throughout the presentation.',
                'tone' => 'orange',
            ],
        ];

        $hasCriteriaScores =
            collect($criteria)
                ->contains(
                    function ($criterion) use ($criteriaScores) {
                        return array_key_exists(
                            $criterion['key'],
                            $criteriaScores
                        );
                    }
                );

        $wordCount =
            $transcript
                ? str_word_count(
                    strip_tags($transcript)
                )
                : 0;
    @endphp

    <style>
        .speaking-result-page {
            --card: #ffffff;
            --card-soft: #f8fafc;
            --card-muted: #eef2f7;
            --text: #0f172a;
            --text-soft: #334155;
            --text-muted: #64748b;
            --border: #dbe4ef;
            --border-strong: #cbd5e1;
            --accent: #7c3aed;
            --accent-soft: #f5f3ff;
            --accent-two: #2563eb;
            --success: #059669;
            --success-soft: #ecfdf5;
            --warning: #d97706;
            --warning-soft: #fffbeb;
            --danger: #dc2626;
            --danger-soft: #fef2f2;
            --cyan: #0891b2;
            --cyan-soft: #ecfeff;
            --shadow: 0 16px 40px rgba(15, 23, 42, 0.08);
            --shadow-soft: 0 7px 20px rgba(15, 23, 42, 0.06);

            color: var(--text);
        }

        html.dark .speaking-result-page,
        body.dark .speaking-result-page,
        .dark .speaking-result-page,
        html[data-theme="dark"] .speaking-result-page,
        body[data-theme="dark"] .speaking-result-page {
            --card: #0f172a;
            --card-soft: #111c31;
            --card-muted: #172033;
            --text: #f8fafc;
            --text-soft: #d7e0ec;
            --text-muted: #9fb0c5;
            --border: #26364d;
            --border-strong: #33455f;
            --accent: #a78bfa;
            --accent-soft: #261a42;
            --accent-two: #60a5fa;
            --success: #34d399;
            --success-soft: #0b2e29;
            --warning: #fbbf24;
            --warning-soft: #33250c;
            --danger: #f87171;
            --danger-soft: #371820;
            --cyan: #22d3ee;
            --cyan-soft: #0b2c3a;
            --shadow: 0 20px 48px rgba(0, 0, 0, 0.34);
            --shadow-soft: 0 9px 24px rgba(0, 0, 0, 0.24);
        }

        .speaking-result-page,
        .speaking-result-page * {
            box-sizing: border-box;
        }

        .speaking-result-page {
            width: min(100%, 1320px);
            margin-inline: auto;
        }

        .speaking-result-page .sr-stack {
            display: grid;
            gap: 16px;
        }

        .speaking-result-page .sr-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 22px;
            box-shadow: var(--shadow-soft);
        }

        .speaking-result-page .sr-alert {
            display: flex;
            align-items: flex-start;
            gap: 11px;
            padding: 13px 15px;
            border: 1px solid color-mix(in srgb, var(--success) 38%, transparent);
            border-radius: 15px;
            background: var(--success-soft);
            color: var(--success);
            font-size: 13px;
            line-height: 1.55;
            font-weight: 750;
        }

        .speaking-result-page .sr-alert-icon {
            display: inline-flex;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 9px;
            background: var(--success);
            color: #ffffff;
        }

        .speaking-result-page .sr-alert-icon svg {
            width: 16px;
            height: 16px;
        }

        /*
        |--------------------------------------------------------------------------
        | Hero
        |--------------------------------------------------------------------------
        */
        .speaking-result-page .sr-hero {
            position: relative;
            overflow: hidden;
            display: grid;
            grid-template-columns: minmax(0, 1fr) 285px;
            align-items: center;
            gap: 24px;
            padding: 24px;
        }

        .speaking-result-page .sr-hero::before {
            content: "";
            position: absolute;
            width: 250px;
            height: 250px;
            right: -105px;
            top: -145px;
            border-radius: 999px;
            background: rgba(124, 58, 237, 0.13);
            filter: blur(7px);
            pointer-events: none;
        }

        .speaking-result-page .sr-hero::after {
            content: "";
            position: absolute;
            width: 210px;
            height: 210px;
            left: 34%;
            bottom: -170px;
            border-radius: 999px;
            background: rgba(37, 99, 235, 0.09);
            filter: blur(10px);
            pointer-events: none;
        }

        .speaking-result-page .sr-hero-copy,
        .speaking-result-page .sr-score-panel {
            position: relative;
            z-index: 1;
        }

        .speaking-result-page .sr-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            min-height: 29px;
            padding: 6px 11px;
            border-radius: 999px;
            background: var(--accent-soft);
            color: var(--accent);
            font-size: 11px;
            line-height: 1;
            font-weight: 900;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .speaking-result-page .sr-eyebrow svg {
            width: 14px;
            height: 14px;
        }

        .speaking-result-page .sr-title {
            margin: 12px 0 0;
            color: var(--text);
            font-size: clamp(30px, 3vw, 42px);
            line-height: 1.08;
            font-weight: 900;
            letter-spacing: -0.04em;
        }

        .speaking-result-page .sr-description {
            max-width: 740px;
            margin: 9px 0 0;
            color: var(--text-muted);
            font-size: 14px;
            line-height: 1.65;
            font-weight: 600;
        }

        .speaking-result-page .sr-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
            margin-top: 15px;
        }

        .speaking-result-page .sr-chip {
            display: inline-flex;
            align-items: center;
            min-height: 28px;
            padding: 6px 9px;
            border: 1px solid var(--border);
            border-radius: 999px;
            background: var(--card-soft);
            color: var(--text-soft);
            font-size: 10px;
            line-height: 1;
            font-weight: 900;
        }

        .speaking-result-page .sr-chip-purple {
            border-color: color-mix(in srgb, var(--accent) 35%, var(--border));
            background: var(--accent-soft);
            color: var(--accent);
        }

        .speaking-result-page .sr-chip-green {
            border-color: color-mix(in srgb, var(--success) 35%, var(--border));
            background: var(--success-soft);
            color: var(--success);
        }

        .speaking-result-page .sr-chip-blue {
            border-color: color-mix(in srgb, var(--accent-two) 35%, var(--border));
            background: color-mix(in srgb, var(--accent-two) 10%, var(--card));
            color: var(--accent-two);
        }

        .speaking-result-page .sr-chip-warning {
            border-color: color-mix(in srgb, var(--warning) 35%, var(--border));
            background: var(--warning-soft);
            color: var(--warning);
        }

        .speaking-result-page .sr-chip-danger {
            border-color: color-mix(in srgb, var(--danger) 35%, var(--border));
            background: var(--danger-soft);
            color: var(--danger);
        }

        .speaking-result-page .sr-submitted {
            margin: 11px 0 0;
            color: var(--text-muted);
            font-size: 11px;
            line-height: 1.5;
            font-weight: 650;
        }

        .speaking-result-page .sr-submitted strong {
            color: var(--text-soft);
            font-weight: 900;
        }

        .speaking-result-page .sr-score-panel {
            padding: 16px;
            border: 1px solid var(--border);
            border-radius: 18px;
            background: var(--card-soft);
        }

        .speaking-result-page .sr-score-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
        }

        .speaking-result-page .sr-score-label {
            color: var(--text-muted);
            font-size: 11px;
            font-weight: 800;
        }

        .speaking-result-page .sr-score-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 11px;
            background: var(--accent-soft);
            color: var(--accent);
        }

        .speaking-result-page .sr-score-icon svg {
            width: 17px;
            height: 17px;
        }

        .speaking-result-page .sr-score-value {
            display: flex;
            align-items: flex-end;
            gap: 5px;
            margin-top: 4px;
        }

        .speaking-result-page .sr-score-number {
            color: var(--accent);
            font-size: 48px;
            line-height: 0.95;
            font-weight: 900;
            letter-spacing: -0.045em;
        }

        .speaking-result-page .sr-score-max {
            padding-bottom: 4px;
            color: var(--text-muted);
            font-size: 12px;
            font-weight: 800;
        }

        .speaking-result-page .sr-score-progress {
            height: 8px;
            margin-top: 12px;
            overflow: hidden;
            border-radius: 999px;
            background: var(--card-muted);
        }

        .speaking-result-page .sr-score-progress-bar {
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, var(--accent), var(--accent-two));
        }

        .speaking-result-page .sr-score-state {
            margin-top: 12px;
            padding: 11px 12px;
            border-radius: 13px;
        }

        .speaking-result-page .sr-score-state-title {
            margin: 0;
            font-size: 13px;
            line-height: 1.3;
            font-weight: 900;
        }

        .speaking-result-page .sr-score-state-text {
            margin: 4px 0 0;
            font-size: 10px;
            line-height: 1.5;
            font-weight: 650;
        }

        .speaking-result-page .score-waiting {
            background: var(--warning-soft);
            color: var(--warning);
        }

        .speaking-result-page .score-excellent {
            background: var(--success-soft);
            color: var(--success);
        }

        .speaking-result-page .score-very-good,
        .speaking-result-page .score-good {
            background: color-mix(in srgb, var(--accent-two) 11%, var(--card));
            color: var(--accent-two);
        }

        .speaking-result-page .score-developing {
            background: var(--warning-soft);
            color: var(--warning);
        }

        .speaking-result-page .score-improve {
            background: var(--danger-soft);
            color: var(--danger);
        }

        /*
        |--------------------------------------------------------------------------
        | Section
        |--------------------------------------------------------------------------
        */
        .speaking-result-page .sr-section {
            padding: 18px;
        }

        .speaking-result-page .sr-section-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
        }

        .speaking-result-page .sr-section-title {
            margin: 0;
            color: var(--text);
            font-size: 18px;
            line-height: 1.3;
            font-weight: 900;
            letter-spacing: -0.02em;
        }

        .speaking-result-page .sr-section-description {
            max-width: 760px;
            margin: 4px 0 0;
            color: var(--text-muted);
            font-size: 12px;
            line-height: 1.55;
            font-weight: 650;
        }

        .speaking-result-page .sr-section-badge {
            display: inline-flex;
            flex: 0 0 auto;
            align-items: center;
            min-height: 29px;
            padding: 6px 10px;
            border-radius: 999px;
            background: var(--card-muted);
            color: var(--text-muted);
            font-size: 10px;
            line-height: 1;
            font-weight: 900;
        }

        /*
        |--------------------------------------------------------------------------
        | Rubric
        |--------------------------------------------------------------------------
        */
        .speaking-result-page .sr-rubric-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 9px;
            margin-top: 14px;
        }

        .speaking-result-page .sr-rubric {
            --criterion: var(--accent);
            --criterion-soft: var(--accent-soft);

            min-width: 0;
            padding: 12px;
            border: 1px solid color-mix(in srgb, var(--criterion) 27%, var(--border));
            border-radius: 14px;
            background: var(--criterion-soft);
        }

        .speaking-result-page .criterion-purple {
            --criterion: var(--accent);
            --criterion-soft: var(--accent-soft);
        }

        .speaking-result-page .criterion-blue {
            --criterion: var(--accent-two);
            --criterion-soft: color-mix(in srgb, var(--accent-two) 10%, var(--card));
        }

        .speaking-result-page .criterion-cyan {
            --criterion: var(--cyan);
            --criterion-soft: var(--cyan-soft);
        }

        .speaking-result-page .criterion-green {
            --criterion: var(--success);
            --criterion-soft: var(--success-soft);
        }

        .speaking-result-page .criterion-orange {
            --criterion: var(--warning);
            --criterion-soft: var(--warning-soft);
        }

        .speaking-result-page .sr-rubric-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        .speaking-result-page .sr-rubric-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 10px;
            background: var(--card);
            color: var(--criterion);
        }

        .speaking-result-page .sr-rubric-icon svg {
            width: 16px;
            height: 16px;
        }

        .speaking-result-page .sr-rubric-score {
            color: var(--criterion);
            font-size: 23px;
            line-height: 1;
            font-weight: 900;
        }

        .speaking-result-page .sr-rubric-score small {
            font-size: 10px;
            font-weight: 800;
        }

        .speaking-result-page .sr-rubric-title {
            margin: 9px 0 0;
            color: var(--text);
            font-size: 12px;
            line-height: 1.35;
            font-weight: 900;
        }

        .speaking-result-page .sr-rubric-description {
            margin: 4px 0 0;
            color: var(--text-muted);
            font-size: 10px;
            line-height: 1.5;
            font-weight: 600;
        }

        .speaking-result-page .sr-rubric-progress {
            height: 6px;
            margin-top: 9px;
            overflow: hidden;
            border-radius: 999px;
            background: color-mix(in srgb, var(--criterion) 14%, var(--card));
        }

        .speaking-result-page .sr-rubric-progress-bar {
            height: 100%;
            border-radius: inherit;
            background: var(--criterion);
        }

        .speaking-result-page .sr-empty {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 14px;
            padding: 14px;
            border: 1px dashed var(--border-strong);
            border-radius: 14px;
            background: var(--card-soft);
        }

        .speaking-result-page .sr-empty-icon {
            display: inline-flex;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: 11px;
            background: var(--warning-soft);
            color: var(--warning);
        }

        .speaking-result-page .sr-empty-icon svg {
            width: 18px;
            height: 18px;
        }

        .speaking-result-page .sr-empty-title {
            margin: 0;
            color: var(--text);
            font-size: 13px;
            line-height: 1.4;
            font-weight: 900;
        }

        .speaking-result-page .sr-empty-text {
            margin: 3px 0 0;
            color: var(--text-muted);
            font-size: 11px;
            line-height: 1.5;
            font-weight: 600;
        }

        /*
        |--------------------------------------------------------------------------
        | Feedback and Transcript
        |--------------------------------------------------------------------------
        */
        .speaking-result-page .sr-detail-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
            align-items: start;
        }

        .speaking-result-page .sr-detail-card {
            min-width: 0;
            padding: 18px;
        }

        .speaking-result-page .sr-detail-title-row {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .speaking-result-page .sr-detail-icon {
            display: inline-flex;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 11px;
            background: var(--accent-soft);
            color: var(--accent);
        }

        .speaking-result-page .sr-detail-icon.is-blue {
            background: color-mix(in srgb, var(--accent-two) 11%, var(--card));
            color: var(--accent-two);
        }

        .speaking-result-page .sr-detail-icon svg {
            width: 18px;
            height: 18px;
        }

        .speaking-result-page .sr-detail-title {
            margin: 0;
            color: var(--text);
            font-size: 16px;
            line-height: 1.3;
            font-weight: 900;
        }

        .speaking-result-page .sr-detail-description {
            margin: 3px 0 0;
            color: var(--text-muted);
            font-size: 11px;
            line-height: 1.5;
            font-weight: 650;
        }

        .speaking-result-page .sr-detail-content {
            margin-top: 13px;
            padding: 13px 14px;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: var(--card-soft);
            color: var(--text-soft);
            font-size: 13px;
            line-height: 1.7;
            font-weight: 600;
        }

        .speaking-result-page .sr-transcript-content {
            max-height: 280px;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: var(--border-strong) transparent;
        }

        .speaking-result-page .sr-transcript-content::-webkit-scrollbar {
            width: 7px;
        }

        .speaking-result-page .sr-transcript-content::-webkit-scrollbar-thumb {
            border-radius: 999px;
            background: var(--border-strong);
        }

        .speaking-result-page .sr-note {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            margin-top: 11px;
            padding: 10px 11px;
            border-radius: 12px;
            background: var(--warning-soft);
            color: var(--warning);
            font-size: 10px;
            line-height: 1.5;
            font-weight: 700;
        }

        .speaking-result-page .sr-note svg {
            flex: 0 0 auto;
            width: 15px;
            height: 15px;
            margin-top: 1px;
        }

        /*
        |--------------------------------------------------------------------------
        | Score Guide
        |--------------------------------------------------------------------------
        */
        .speaking-result-page .sr-guide-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 8px;
            margin-top: 13px;
        }

        .speaking-result-page .sr-guide {
            --guide: var(--success);
            --guide-soft: var(--success-soft);

            display: grid;
            grid-template-columns: 34px minmax(0, 1fr);
            align-items: center;
            gap: 10px;
            padding: 10px 11px;
            border: 1px solid color-mix(in srgb, var(--guide) 27%, var(--border));
            border-radius: 13px;
            background: var(--guide-soft);
        }

        .speaking-result-page .guide-blue {
            --guide: var(--accent-two);
            --guide-soft: color-mix(in srgb, var(--accent-two) 10%, var(--card));
        }

        .speaking-result-page .guide-orange {
            --guide: var(--warning);
            --guide-soft: var(--warning-soft);
        }

        .speaking-result-page .guide-red {
            --guide: var(--danger);
            --guide-soft: var(--danger-soft);
        }

        .speaking-result-page .sr-guide-score {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: var(--card);
            color: var(--guide);
            font-size: 16px;
            font-weight: 900;
        }

        .speaking-result-page .sr-guide-title {
            color: var(--text);
            font-size: 11px;
            line-height: 1.3;
            font-weight: 900;
        }

        .speaking-result-page .sr-guide-text {
            margin-top: 2px;
            color: var(--text-muted);
            font-size: 9px;
            line-height: 1.4;
            font-weight: 600;
        }

        /*
        |--------------------------------------------------------------------------
        | Actions
        |--------------------------------------------------------------------------
        */
        .speaking-result-page .sr-actions {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
        }

        .speaking-result-page .sr-button {
            display: inline-flex;
            min-height: 50px;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 11px 16px;
            border: 1px solid transparent;
            border-radius: 14px;
            font-family: inherit;
            font-size: 13px;
            line-height: 1;
            font-weight: 900;
            text-decoration: none;
            cursor: pointer;
            transition:
                transform 160ms ease,
                background-color 160ms ease,
                border-color 160ms ease,
                filter 160ms ease,
                box-shadow 160ms ease;
        }

        .speaking-result-page .sr-button svg {
            width: 17px;
            height: 17px;
        }

        .speaking-result-page .sr-button:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-soft);
        }

        .speaking-result-page .sr-button:focus-visible {
            outline: 3px solid rgba(167, 139, 250, 0.24);
            outline-offset: 2px;
        }

        .speaking-result-page .sr-button-primary {
            background: linear-gradient(100deg, var(--accent), var(--accent-two));
            color: #ffffff;
        }

        .speaking-result-page .sr-button-secondary {
            border-color: var(--border);
            background: var(--card-soft);
            color: var(--text-soft);
        }

        .speaking-result-page .sr-button-secondary:hover {
            border-color: var(--border-strong);
            background: var(--card-muted);
        }

        /*
        |--------------------------------------------------------------------------
        | Responsive
        |--------------------------------------------------------------------------
        */
        @media (max-width: 1040px) {
            .speaking-result-page .sr-rubric-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 820px) {
            .speaking-result-page .sr-hero {
                grid-template-columns: minmax(0, 1fr);
            }

            .speaking-result-page .sr-score-panel {
                width: 100%;
            }

            .speaking-result-page .sr-detail-grid {
                grid-template-columns: minmax(0, 1fr);
            }

            .speaking-result-page .sr-guide-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 620px) {
            .speaking-result-page .sr-stack {
                gap: 14px;
            }

            .speaking-result-page .sr-hero,
            .speaking-result-page .sr-section,
            .speaking-result-page .sr-detail-card {
                padding: 16px;
            }

            .speaking-result-page .sr-title {
                font-size: 30px;
            }

            .speaking-result-page .sr-rubric-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .speaking-result-page .sr-section-head {
                flex-direction: column;
            }

            .speaking-result-page .sr-guide-grid,
            .speaking-result-page .sr-actions {
                grid-template-columns: minmax(0, 1fr);
            }
        }

        @media (max-width: 420px) {
            .speaking-result-page .sr-rubric-grid {
                grid-template-columns: minmax(0, 1fr);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Print
        |--------------------------------------------------------------------------
        */
        @media print {
            body {
                background: #ffffff !important;
            }

            .speaking-result-page {
                width: 100% !important;
                max-width: none !important;
            }

            .speaking-result-page .sr-no-print {
                display: none !important;
            }

            .speaking-result-page .sr-card {
                box-shadow: none !important;
                break-inside: avoid;
            }

            .speaking-result-page .sr-transcript-content {
                max-height: none !important;
                overflow: visible !important;
            }
        }
    </style>

    <div class="speaking-result-page">
        <div class="sr-stack">

            {{-- SESSION MESSAGE --}}
            @if (session('success'))
                <div class="sr-alert">
                    <span class="sr-alert-icon">
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

                    <div>
                        <strong>Assessment submitted.</strong>
                        {{ session('success') }}
                    </div>
                </div>
            @endif

            {{-- RESULT HERO --}}
            <section class="sr-card sr-hero">
                <div class="sr-hero-copy">
                    <span class="sr-eyebrow">
                        <svg
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
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
                                stroke-width="2"
                                d="M5 10a7 7 0 0014 0M12 17v5M8 22h8">
                            </path>
                        </svg>

                        {{ $typeLabel }} Individual Speaking Result
                    </span>

                    <h1 class="sr-title">
                        {{ $lessonTitle }}
                    </h1>

                    <p class="sr-description">
                        Review your speaking score, rubric, transcript,
                        and AI feedback in one concise summary.
                    </p>

                    <div class="sr-meta">
                        <span class="sr-chip">
                            {{ $unitTitle }}
                        </span>

                        <span class="sr-chip sr-chip-green">
                            Individual
                        </span>

                        <span class="sr-chip sr-chip-blue">
                            2-3 minutes
                        </span>

                        @if ($statusTone === 'completed')
                            <span class="sr-chip sr-chip-green">
                                Completed
                            </span>
                        @elseif ($statusTone === 'pending')
                            <span class="sr-chip sr-chip-warning">
                                Evaluation Pending
                            </span>
                        @elseif ($statusTone === 'failed')
                            <span class="sr-chip sr-chip-danger">
                                Failed
                            </span>
                        @else
                            <span class="sr-chip">
                                {{ ucfirst($status) }}
                            </span>
                        @endif
                    </div>

                    @if ($submittedAt)
                        <p class="sr-submitted">
                            Submitted on
                            <strong>
                                {{ $submittedAt->format('d M Y, H:i') }}
                            </strong>
                        </p>
                    @endif
                </div>

                <aside class="sr-score-panel">
                    <div class="sr-score-top">
                        <span class="sr-score-label">
                            Final Score
                        </span>

                        <span class="sr-score-icon">
                            <svg
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                aria-hidden="true">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 6v6l4 2M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                </path>
                            </svg>
                        </span>
                    </div>

                    <div class="sr-score-value">
                        <span class="sr-score-number">
                            {{ $finalScore ?? '-' }}
                        </span>

                        <span class="sr-score-max">
                            / 100
                        </span>
                    </div>

                    <div class="sr-score-progress">
                        <div
                            class="sr-score-progress-bar"
                            style="width: {{ $finalScore ?? 0 }}%">
                        </div>
                    </div>

                    <div class="sr-score-state score-{{ $scoreTone }}">
                        <p class="sr-score-state-title">
                            {{ $scoreLabel }}
                        </p>

                        <p class="sr-score-state-text">
                            {{ $scoreDescription }}
                        </p>
                    </div>
                </aside>
            </section>

            {{-- RUBRIC --}}
            <section class="sr-card sr-section">
                <header class="sr-section-head">
                    <div>
                        <h2 class="sr-section-title">
                            Speaking Rubric
                        </h2>

                        <p class="sr-section-description">
                            Five criteria are scored from 1 to 4.
                        </p>
                    </div>

                    <span class="sr-section-badge">
                        Maximum 4 per criterion
                    </span>
                </header>

                @if ($hasCriteriaScores)
                    <div class="sr-rubric-grid">
                        @foreach ($criteria as $criterion)
                            @php
                                $criterionScore =
                                    array_key_exists(
                                        $criterion['key'],
                                        $criteriaScores
                                    )
                                        ? max(
                                            1,
                                            min(
                                                4,
                                                (int) $criteriaScores[
                                                    $criterion['key']
                                                ]
                                            )
                                        )
                                        : null;

                                $criterionPercentage =
                                    $criterionScore !== null
                                        ? ($criterionScore / 4) * 100
                                        : 0;
                            @endphp

                            <article class="sr-rubric criterion-{{ $criterion['tone'] }}">
                                <div class="sr-rubric-top">
                                    <span class="sr-rubric-icon">
                                        @if ($criterion['key'] === 'content_relevance')
                                            <svg
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                                aria-hidden="true">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M4 19.5A2.5 2.5 0 016.5 17H20M4 4.5A2.5 2.5 0 016.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15z">
                                                </path>
                                            </svg>
                                        @elseif ($criterion['key'] === 'fluency')
                                            <svg
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                                aria-hidden="true">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M4 12h4l2-7 4 14 2-7h4">
                                                </path>
                                            </svg>
                                        @elseif ($criterion['key'] === 'pronunciation')
                                            <svg
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                                aria-hidden="true">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M12 3v18M8 7v10M4 10v4M16 7v10M20 10v4">
                                                </path>
                                            </svg>
                                        @elseif ($criterion['key'] === 'vocabulary')
                                            <svg
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                                aria-hidden="true">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M4 5h16M4 12h10M4 19h16">
                                                </path>
                                            </svg>
                                        @else
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
                                        @endif
                                    </span>

                                    <span class="sr-rubric-score">
                                        {{ $criterionScore ?? '-' }}
                                        <small>/4</small>
                                    </span>
                                </div>

                                <h3 class="sr-rubric-title">
                                    {{ $criterion['label'] }}
                                </h3>

                                <p class="sr-rubric-description">
                                    {{ $criterion['description'] }}
                                </p>

                                <div class="sr-rubric-progress">
                                    <div
                                        class="sr-rubric-progress-bar"
                                        style="width: {{ $criterionPercentage }}%">
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="sr-empty">
                        <span class="sr-empty-icon">
                            <svg
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                aria-hidden="true">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z">
                                </path>
                            </svg>
                        </span>

                        <div>
                            <h3 class="sr-empty-title">
                                Rubric scores are not available
                            </h3>

                            <p class="sr-empty-text">
                                The assessment was saved, but detailed criterion scores
                                have not been recorded.
                            </p>
                        </div>
                    </div>
                @endif
            </section>

            {{-- FEEDBACK + TRANSCRIPT --}}
            <section class="sr-detail-grid">
                <article class="sr-card sr-detail-card">
                    <div class="sr-detail-title-row">
                        <span class="sr-detail-icon">
                            <svg
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                aria-hidden="true">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 3l1.8 4.7 4.7 1.8-4.7 1.8L12 16l-1.8-4.7-4.7-1.8 4.7-1.8L12 3z">
                                </path>
                            </svg>
                        </span>

                        <div>
                            <h2 class="sr-detail-title">
                                AI Feedback
                            </h2>

                            <p class="sr-detail-description">
                                Recommendations for your next presentation.
                            </p>
                        </div>
                    </div>

                    <div class="sr-detail-content">
                        @if ($feedback)
                            {!! nl2br(e(trim((string) $feedback))) !!}
                        @else
                            AI feedback is not available yet.
                        @endif
                    </div>

                    <div class="sr-note">
                        <svg
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z">
                            </path>
                        </svg>

                        <span>
                            Pronunciation is estimated from transcript clarity,
                            not direct acoustic analysis of the raw audio.
                        </span>
                    </div>
                </article>

                <article class="sr-card sr-detail-card">
                    <div class="sr-detail-title-row">
                        <span class="sr-detail-icon is-blue">
                            <svg
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                aria-hidden="true">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8zM14 2v6h6M8 13h8M8 17h8">
                                </path>
                            </svg>
                        </span>

                        <div>
                            <h2 class="sr-detail-title">
                                Submitted Transcript
                            </h2>

                            <p class="sr-detail-description">
                                {{ $wordCount }} words generated from your presentation.
                            </p>
                        </div>
                    </div>

                    <div class="sr-detail-content sr-transcript-content">
                        @if ($transcript)
                            {!! nl2br(e(trim((string) $transcript))) !!}
                        @else
                            The submitted transcript is not available.
                        @endif
                    </div>
                </article>
            </section>

            {{-- SCORE GUIDE --}}
            <section class="sr-card sr-section">
                <header class="sr-section-head">
                    <div>
                        <h2 class="sr-section-title">
                            Rubric Score Guide
                        </h2>

                        <p class="sr-section-description">
                            A compact guide to the meaning of each criterion score.
                        </p>
                    </div>
                </header>

                <div class="sr-guide-grid">
                    <article class="sr-guide">
                        <span class="sr-guide-score">4</span>

                        <div>
                            <div class="sr-guide-title">Excellent</div>
                            <div class="sr-guide-text">
                                Clear, consistent, and complete.
                            </div>
                        </div>
                    </article>

                    <article class="sr-guide guide-blue">
                        <span class="sr-guide-score">3</span>

                        <div>
                            <div class="sr-guide-title">Good</div>
                            <div class="sr-guide-text">
                                Strong with minor weaknesses.
                            </div>
                        </div>
                    </article>

                    <article class="sr-guide guide-orange">
                        <span class="sr-guide-score">2</span>

                        <div>
                            <div class="sr-guide-title">Developing</div>
                            <div class="sr-guide-text">
                                Partly demonstrated; improvement needed.
                            </div>
                        </div>
                    </article>

                    <article class="sr-guide guide-red">
                        <span class="sr-guide-score">1</span>

                        <div>
                            <div class="sr-guide-title">Needs Improvement</div>
                            <div class="sr-guide-text">
                                Unclear, incomplete, or difficult to understand.
                            </div>
                        </div>
                    </article>
                </div>
            </section>

            {{-- ACTIONS --}}
            <div class="sr-actions sr-no-print">
                <a
                    href="{{ route('missions') }}"
                    class="sr-button sr-button-primary">
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

                    Back to Missions
                </a>

                <button
                    type="button"
                    onclick="window.print()"
                    class="sr-button sr-button-secondary">
                    <svg
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6z">
                        </path>
                    </svg>

                    Print Result
                </button>

                <button
                    type="button"
                    onclick="window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    })"
                    class="sr-button sr-button-secondary">
                    <svg
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M18 15l-6-6-6 6">
                        </path>
                    </svg>

                    Back to Top
                </button>
            </div>

        </div>
    </div>
</x-app-layout>