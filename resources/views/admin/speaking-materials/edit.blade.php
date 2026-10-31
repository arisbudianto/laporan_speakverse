@extends('layouts.admin')

@section('content')
    @php
        /*
        |--------------------------------------------------------------------------
        | Material Context
        |--------------------------------------------------------------------------
        */

        $lesson = $material->lesson;

        $isAssessment = in_array(
            $lesson?->unit?->type,
            [
                'pretest',
                'posttest',
            ],
            true
        );

        $assessmentLabel = $isAssessment
            ? strtoupper((string) $lesson?->unit?->type)
            : null;

        /*
        |--------------------------------------------------------------------------
        | Default Assessment Values
        |--------------------------------------------------------------------------
        */

        $assessmentPoints = [
            'Title',
            'Main characters',
            'Setting',
            'Plot',
            'Conflict',
            'Resolution',
            'Moral value',
            'Your opinion',
        ];

        $defaultAssessmentScenario =
            'You will give an individual presentation to your friends about one fable or short story. Present the story clearly from the beginning until the end.';

        $defaultAssessmentExample = <<<'EXAMPLE'
Good morning everyone.

Today I want to tell you about The Hare and the Tortoise.

The main characters are the Hare and the Tortoise.

The story happened in a forest.

One day the Hare laughed at the Tortoise because he walked very slowly.

The Hare challenged the Tortoise to a race.

During the race, the Hare became overconfident and slept under a tree.

The Tortoise kept walking slowly and finally won the race.

The moral value is that slow and steady wins the race.

I like this story because it teaches us never to give up.

Thank you.
EXAMPLE;

        /*
        |--------------------------------------------------------------------------
        | Form Values
        |--------------------------------------------------------------------------
        */

        $discussionPoints = old(
            'discussion_points',
            $material->discussion_points ?? []
        );

        if (
            !is_array($discussionPoints) ||
            count($discussionPoints) === 0
        ) {
            $discussionPoints = $isAssessment
                ? $assessmentPoints
                : [''];
        }

        $minDurationMinutes = $isAssessment
            ? 2
            : old(
                'min_duration_minutes',
                $material->min_duration
                    ? (int) ceil($material->min_duration / 60)
                    : 2
            );

        $maxDurationMinutes = $isAssessment
            ? 3
            : old(
                'max_duration_minutes',
                $material->max_duration
                    ? (int) ceil($material->max_duration / 60)
                    : 5
            );

        $scenarioValue = old(
            'scenario',
            $material->scenario
                ?: (
                    $isAssessment
                        ? $defaultAssessmentScenario
                        : ''
                )
        );

        $passageValue = old(
            'passage',
            $material->passage
                ?: (
                    $isAssessment
                        ? $defaultAssessmentExample
                        : ''
                )
        );
    @endphp

    <style>
        .speaking-edit-page {
            --page-card: #ffffff;
            --page-card-soft: #f8fafc;
            --page-card-muted: #f1f5f9;
            --page-input: #ffffff;
            --page-text: #0f172a;
            --page-text-soft: #334155;
            --page-muted: #64748b;
            --page-border: #dbe3ee;
            --page-border-strong: #cbd5e1;
            --page-purple: #7c3aed;
            --page-purple-soft: #f3e8ff;
            --page-blue: #2563eb;
            --page-blue-soft: #eff6ff;
            --page-cyan: #0891b2;
            --page-cyan-soft: #ecfeff;
            --page-green: #059669;
            --page-green-soft: #ecfdf5;
            --page-amber: #d97706;
            --page-amber-soft: #fffbeb;
            --page-red: #dc2626;
            --page-red-soft: #fef2f2;
            --page-shadow: 0 10px 30px rgba(15, 23, 42, 0.07);
            --page-shadow-soft: 0 4px 14px rgba(15, 23, 42, 0.05);

            color: var(--page-text);
        }

        html.dark .speaking-edit-page,
        .dark .speaking-edit-page {
            --page-card: #0f172a;
            --page-card-soft: #111c31;
            --page-card-muted: #172033;
            --page-input: #0b1324;
            --page-text: #f8fafc;
            --page-text-soft: #d8e1ee;
            --page-muted: #94a3b8;
            --page-border: #26364d;
            --page-border-strong: #33465f;
            --page-purple: #c084fc;
            --page-purple-soft: #2b1745;
            --page-blue: #60a5fa;
            --page-blue-soft: #122846;
            --page-cyan: #22d3ee;
            --page-cyan-soft: #0d2d38;
            --page-green: #34d399;
            --page-green-soft: #0c2f29;
            --page-amber: #fbbf24;
            --page-amber-soft: #33260c;
            --page-red: #f87171;
            --page-red-soft: #391820;
            --page-shadow: 0 16px 38px rgba(0, 0, 0, 0.28);
            --page-shadow-soft: 0 7px 20px rgba(0, 0, 0, 0.2);
        }

        .speaking-edit-page,
        .speaking-edit-page * {
            box-sizing: border-box;
        }

        .speaking-edit-page button,
        .speaking-edit-page input,
        .speaking-edit-page textarea {
            font: inherit;
        }

        .speaking-edit-page .edit-shell {
            width: min(100%, 1180px);
            margin-inline: auto;
            display: grid;
            gap: 16px;
        }

        .speaking-edit-page .edit-card {
            border: 1px solid var(--page-border);
            border-radius: 22px;
            background: var(--page-card);
            box-shadow: var(--page-shadow-soft);
        }

        /*
        |--------------------------------------------------------------------------
        | Header
        |--------------------------------------------------------------------------
        */

        .speaking-edit-page .page-hero {
            position: relative;
            overflow: hidden;
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: center;
            gap: 24px;
            padding: 22px 24px;
        }

        .speaking-edit-page .page-hero::after {
            content: "";
            position: absolute;
            top: -120px;
            right: -90px;
            width: 260px;
            height: 260px;
            border-radius: 999px;
            background: rgba(124, 58, 237, 0.1);
            filter: blur(22px);
            pointer-events: none;
        }

        html.dark .speaking-edit-page .page-hero::after,
        .dark .speaking-edit-page .page-hero::after {
            background: rgba(168, 85, 247, 0.12);
        }

        .speaking-edit-page .hero-copy,
        .speaking-edit-page .hero-summary {
            position: relative;
            z-index: 1;
        }

        .speaking-edit-page .hero-toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 9px;
            margin-bottom: 15px;
        }

        .speaking-edit-page .back-link,
        .speaking-edit-page .context-badge {
            display: inline-flex;
            min-height: 38px;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 8px 13px;
            border-radius: 11px;
            font-size: 13px;
            line-height: 1;
            font-weight: 800;
            text-decoration: none;
        }

        .speaking-edit-page .back-link {
            border: 1px solid var(--page-border);
            background: var(--page-card-soft);
            color: var(--page-text-soft);
            transition:
                background-color 160ms ease,
                border-color 160ms ease,
                transform 160ms ease;
        }

        .speaking-edit-page .back-link:hover {
            transform: translateY(-1px);
            border-color: var(--page-border-strong);
            background: var(--page-card-muted);
        }

        .speaking-edit-page .back-link svg,
        .speaking-edit-page .context-badge svg {
            width: 16px;
            height: 16px;
        }

        .speaking-edit-page .context-badge {
            border: 1px solid rgba(124, 58, 237, 0.22);
            background: var(--page-purple-soft);
            color: var(--page-purple);
        }

        .speaking-edit-page .hero-title {
            margin: 0;
            max-width: 790px;
            color: var(--page-text);
            font-size: clamp(26px, 3vw, 38px);
            line-height: 1.12;
            font-weight: 900;
            letter-spacing: -0.035em;
        }

        .speaking-edit-page .hero-description {
            max-width: 790px;
            margin: 9px 0 0;
            color: var(--page-muted);
            font-size: 14px;
            line-height: 1.7;
            font-weight: 500;
        }

        .speaking-edit-page .hero-summary {
            display: flex;
            align-items: stretch;
            gap: 9px;
        }

        .speaking-edit-page .summary-item {
            display: flex;
            min-width: 145px;
            align-items: center;
            gap: 11px;
            padding: 12px 14px;
            border: 1px solid var(--page-border);
            border-radius: 15px;
            background: var(--page-card-soft);
        }

        .speaking-edit-page .summary-icon {
            display: inline-flex;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 11px;
            background: var(--page-purple-soft);
            color: var(--page-purple);
        }

        .speaking-edit-page .summary-icon.is-blue {
            background: var(--page-blue-soft);
            color: var(--page-blue);
        }

        .speaking-edit-page .summary-icon svg {
            width: 18px;
            height: 18px;
        }

        .speaking-edit-page .summary-label {
            display: block;
            color: var(--page-muted);
            font-size: 10px;
            line-height: 1.3;
            font-weight: 900;
            letter-spacing: 0.09em;
            text-transform: uppercase;
        }

        .speaking-edit-page .summary-value {
            display: block;
            margin-top: 3px;
            color: var(--page-text);
            font-size: 14px;
            line-height: 1.3;
            font-weight: 900;
        }

        /*
        |--------------------------------------------------------------------------
        | Lesson Context
        |--------------------------------------------------------------------------
        */

        .speaking-edit-page .lesson-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            padding: 15px 18px;
        }

        .speaking-edit-page .lesson-main {
            display: flex;
            min-width: 0;
            align-items: center;
            gap: 13px;
        }

        .speaking-edit-page .lesson-icon {
            display: inline-flex;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            width: 46px;
            height: 46px;
            border-radius: 14px;
            background: var(--page-purple-soft);
            color: var(--page-purple);
        }

        .speaking-edit-page .lesson-icon svg {
            width: 22px;
            height: 22px;
        }

        .speaking-edit-page .lesson-label {
            margin: 0;
            color: var(--page-muted);
            font-size: 11px;
            line-height: 1.3;
            font-weight: 900;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .speaking-edit-page .lesson-title {
            margin: 3px 0 0;
            color: var(--page-text);
            font-size: 18px;
            line-height: 1.35;
            font-weight: 900;
        }

        .speaking-edit-page .lesson-description {
            max-width: 630px;
            margin: 3px 0 0;
            overflow: hidden;
            color: var(--page-muted);
            font-size: 13px;
            line-height: 1.45;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .speaking-edit-page .lesson-tags {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 7px;
        }

        .speaking-edit-page .lesson-tag {
            display: inline-flex;
            align-items: center;
            padding: 7px 10px;
            border-radius: 999px;
            font-size: 11px;
            line-height: 1;
            font-weight: 900;
        }

        .speaking-edit-page .lesson-tag.is-purple {
            background: var(--page-purple-soft);
            color: var(--page-purple);
        }

        .speaking-edit-page .lesson-tag.is-green {
            background: var(--page-green-soft);
            color: var(--page-green);
        }

        .speaking-edit-page .lesson-tag.is-blue {
            background: var(--page-blue-soft);
            color: var(--page-blue);
        }

        /*
        |--------------------------------------------------------------------------
        | Validation Summary
        |--------------------------------------------------------------------------
        */

        .speaking-edit-page .validation-summary {
            padding: 15px 17px;
            border: 1px solid rgba(220, 38, 38, 0.25);
            border-radius: 16px;
            background: var(--page-red-soft);
            color: var(--page-red);
            font-size: 13px;
            line-height: 1.6;
            font-weight: 650;
        }

        .speaking-edit-page .validation-summary strong {
            display: block;
            margin-bottom: 5px;
            font-weight: 900;
        }

        .speaking-edit-page .validation-summary ul {
            margin: 0;
            padding-left: 19px;
        }

        /*
        |--------------------------------------------------------------------------
        | Form Sections
        |--------------------------------------------------------------------------
        */

        .speaking-edit-page .edit-form {
            display: grid;
            gap: 16px;
        }

        .speaking-edit-page .form-section {
            overflow: hidden;
        }

        .speaking-edit-page .section-header {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 17px 19px;
            border-bottom: 1px solid var(--page-border);
        }

        .speaking-edit-page .section-icon {
            display: inline-flex;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: 12px;
            background: var(--page-purple-soft);
            color: var(--page-purple);
        }

        .speaking-edit-page .section-icon.is-blue {
            background: var(--page-blue-soft);
            color: var(--page-blue);
        }

        .speaking-edit-page .section-icon.is-green {
            background: var(--page-green-soft);
            color: var(--page-green);
        }

        .speaking-edit-page .section-icon.is-cyan {
            background: var(--page-cyan-soft);
            color: var(--page-cyan);
        }

        .speaking-edit-page .section-icon svg {
            width: 19px;
            height: 19px;
        }

        .speaking-edit-page .section-heading {
            min-width: 0;
        }

        .speaking-edit-page .section-title {
            margin: 0;
            color: var(--page-text);
            font-size: 17px;
            line-height: 1.35;
            font-weight: 900;
        }

        .speaking-edit-page .section-description {
            margin: 3px 0 0;
            color: var(--page-muted);
            font-size: 12px;
            line-height: 1.55;
            font-weight: 500;
        }

        .speaking-edit-page .section-body {
            display: grid;
            gap: 18px;
            padding: 19px;
        }

        .speaking-edit-page .field-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .speaking-edit-page .field-full {
            grid-column: 1 / -1;
        }

        .speaking-edit-page .field-label {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 7px;
            color: var(--page-text);
            font-size: 13px;
            line-height: 1.4;
            font-weight: 850;
        }

        .speaking-edit-page .field-optional {
            color: var(--page-muted);
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .speaking-edit-page .field-control {
            display: block;
            width: 100%;
            border: 1px solid var(--page-border-strong);
            border-radius: 13px;
            outline: none;
            background: var(--page-input);
            color: var(--page-text);
            font-size: 14px;
            line-height: 1.6;
            transition:
                border-color 150ms ease,
                box-shadow 150ms ease,
                background-color 150ms ease;
        }

        .speaking-edit-page input.field-control {
            min-height: 46px;
            padding: 10px 13px;
        }

        .speaking-edit-page textarea.field-control {
            min-height: 120px;
            padding: 12px 13px;
            resize: vertical;
        }

        .speaking-edit-page .field-control::placeholder {
            color: var(--page-muted);
            opacity: 0.75;
        }

        .speaking-edit-page .field-control:focus {
            border-color: var(--page-blue);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.13);
        }

        html.dark .speaking-edit-page .field-control:focus,
        .dark .speaking-edit-page .field-control:focus {
            box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.16);
        }

        .speaking-edit-page .field-help {
            margin: 6px 0 0;
            color: var(--page-muted);
            font-size: 11px;
            line-height: 1.55;
        }

        .speaking-edit-page .field-error {
            margin: 6px 0 0;
            color: var(--page-red);
            font-size: 12px;
            line-height: 1.45;
            font-weight: 800;
        }

        /*
        |--------------------------------------------------------------------------
        | Roles
        |--------------------------------------------------------------------------
        */

        .speaking-edit-page .role-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .speaking-edit-page .role-card {
            padding: 15px;
            border: 1px solid var(--page-border);
            border-radius: 16px;
            background: var(--page-card-soft);
        }

        .speaking-edit-page .role-heading {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 11px;
        }

        .speaking-edit-page .role-letter {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: var(--page-purple);
            color: #ffffff;
            font-size: 13px;
            font-weight: 900;
        }

        .speaking-edit-page .role-letter.is-blue {
            background: var(--page-blue);
        }

        .speaking-edit-page .role-title {
            color: var(--page-text);
            font-size: 14px;
            font-weight: 900;
        }

        /*
        |--------------------------------------------------------------------------
        | Requirements
        |--------------------------------------------------------------------------
        */

        .speaking-edit-page .requirement-note,
        .speaking-edit-page .locked-note {
            padding: 13px 15px;
            border-radius: 14px;
        }

        .speaking-edit-page .requirement-note {
            border: 1px solid rgba(5, 150, 105, 0.22);
            background: var(--page-green-soft);
        }

        .speaking-edit-page .requirement-note strong,
        .speaking-edit-page .locked-note strong {
            display: block;
            font-size: 13px;
            font-weight: 900;
        }

        .speaking-edit-page .requirement-note strong {
            color: var(--page-green);
        }

        .speaking-edit-page .requirement-note p,
        .speaking-edit-page .locked-note p {
            margin: 3px 0 0;
            color: var(--page-text-soft);
            font-size: 11px;
            line-height: 1.55;
            font-weight: 600;
        }

        .speaking-edit-page .points-list {
            display: grid;
            gap: 9px;
        }

        .speaking-edit-page .discussion-point {
            display: grid;
            grid-template-columns: 34px minmax(0, 1fr) 38px;
            align-items: center;
            gap: 9px;
        }

        .speaking-edit-page .discussion-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: var(--page-purple-soft);
            color: var(--page-purple);
            font-size: 12px;
            font-weight: 900;
        }

        .speaking-edit-page .remove-point {
            display: inline-flex;
            width: 38px;
            height: 38px;
            align-items: center;
            justify-content: center;
            border: 1px solid transparent;
            border-radius: 11px;
            background: var(--page-red-soft);
            color: var(--page-red);
            cursor: pointer;
            transition:
                background-color 150ms ease,
                transform 150ms ease;
        }

        .speaking-edit-page .remove-point:hover {
            transform: translateY(-1px);
            background: rgba(220, 38, 38, 0.14);
        }

        .speaking-edit-page .remove-point svg {
            width: 17px;
            height: 17px;
        }

        .speaking-edit-page .add-point {
            display: inline-flex;
            min-height: 42px;
            width: fit-content;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 14px;
            border: 1px solid rgba(124, 58, 237, 0.22);
            border-radius: 12px;
            background: var(--page-purple-soft);
            color: var(--page-purple);
            font-size: 12px;
            line-height: 1;
            font-weight: 900;
            cursor: pointer;
        }

        .speaking-edit-page .add-point svg {
            width: 17px;
            height: 17px;
        }

        /*
        |--------------------------------------------------------------------------
        | Duration and Settings
        |--------------------------------------------------------------------------
        */

        .speaking-edit-page .settings-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
        }

        .speaking-edit-page .setting-item {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
            padding: 12px;
            border: 1px solid var(--page-border);
            border-radius: 14px;
            background: var(--page-card-soft);
        }

        .speaking-edit-page .setting-icon {
            display: inline-flex;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: var(--page-blue-soft);
            color: var(--page-blue);
        }

        .speaking-edit-page .setting-icon.is-purple {
            background: var(--page-purple-soft);
            color: var(--page-purple);
        }

        .speaking-edit-page .setting-icon.is-green {
            background: var(--page-green-soft);
            color: var(--page-green);
        }

        .speaking-edit-page .setting-icon svg {
            width: 17px;
            height: 17px;
        }

        .speaking-edit-page .setting-label {
            color: var(--page-muted);
            font-size: 10px;
            line-height: 1.35;
            font-weight: 850;
        }

        .speaking-edit-page .setting-value {
            margin-top: 2px;
            color: var(--page-text);
            font-size: 13px;
            line-height: 1.35;
            font-weight: 900;
        }

        .speaking-edit-page .locked-note {
            border: 1px solid rgba(217, 119, 6, 0.22);
            background: var(--page-amber-soft);
        }

        .speaking-edit-page .locked-note strong {
            color: var(--page-amber);
        }

        .speaking-edit-page .duration-grid,
        .speaking-edit-page .toggle-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .speaking-edit-page .duration-control {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: center;
            gap: 9px;
        }

        .speaking-edit-page .duration-unit {
            color: var(--page-muted);
            font-size: 12px;
            font-weight: 800;
        }

        .speaking-edit-page .toggle-card {
            position: relative;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 14px;
            border: 1px solid var(--page-border);
            border-radius: 15px;
            background: var(--page-card-soft);
            cursor: pointer;
        }

        .speaking-edit-page .toggle-card input[type="checkbox"] {
            width: 18px;
            height: 18px;
            margin-top: 2px;
            accent-color: var(--page-purple);
        }

        .speaking-edit-page .toggle-title {
            display: block;
            color: var(--page-text);
            font-size: 13px;
            font-weight: 900;
        }

        .speaking-edit-page .toggle-description {
            display: block;
            margin-top: 3px;
            color: var(--page-muted);
            font-size: 11px;
            line-height: 1.55;
        }

        /*
        |--------------------------------------------------------------------------
        | Image
        |--------------------------------------------------------------------------
        */

        .speaking-edit-page .support-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.15fr) minmax(320px, 0.85fr);
            gap: 16px;
            align-items: start;
        }

        .speaking-edit-page .current-image-card,
        .speaking-edit-page .upload-box {
            overflow: hidden;
            border: 1px solid var(--page-border);
            border-radius: 14px;
            background: var(--page-card-soft);
        }

        .speaking-edit-page .current-image-card img {
            display: block;
            width: 100%;
            max-height: 280px;
            object-fit: contain;
            padding: 9px;
            background: var(--page-input);
        }

        .speaking-edit-page .no-image {
            display: flex;
            min-height: 160px;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: var(--page-muted);
            text-align: center;
            font-size: 12px;
            font-weight: 750;
        }

        .speaking-edit-page .upload-box {
            margin-top: 10px;
            padding: 13px;
            border-style: dashed;
        }

        .speaking-edit-page .file-control {
            display: block;
            width: 100%;
            color: var(--page-text-soft);
            font-size: 12px;
        }

        .speaking-edit-page .file-control::file-selector-button {
            margin-right: 11px;
            padding: 8px 12px;
            border: 0;
            border-radius: 9px;
            background: var(--page-card-muted);
            color: var(--page-text-soft);
            font-weight: 850;
            cursor: pointer;
        }

        .speaking-edit-page .upload-meta {
            margin: 8px 0 0;
            color: var(--page-muted);
            font-size: 11px;
            line-height: 1.5;
        }

        /*
        |--------------------------------------------------------------------------
        | Action Bar
        |--------------------------------------------------------------------------
        */

        .speaking-edit-page .action-bar {
            position: sticky;
            bottom: 14px;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 12px;
            border: 1px solid var(--page-border);
            border-radius: 17px;
            background: rgba(255, 255, 255, 0.92);
            box-shadow: var(--page-shadow);
            backdrop-filter: blur(14px);
        }

        html.dark .speaking-edit-page .action-bar,
        .dark .speaking-edit-page .action-bar {
            background: rgba(15, 23, 42, 0.92);
        }

        .speaking-edit-page .action-note {
            min-width: 0;
            color: var(--page-muted);
            font-size: 11px;
            line-height: 1.5;
            font-weight: 600;
        }

        .speaking-edit-page .action-buttons {
            display: flex;
            flex: 0 0 auto;
            align-items: center;
            gap: 9px;
        }

        .speaking-edit-page .action-button {
            display: inline-flex;
            min-height: 46px;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 11px 17px;
            border: 1px solid transparent;
            border-radius: 12px;
            font-size: 13px;
            line-height: 1;
            font-weight: 900;
            text-decoration: none;
            cursor: pointer;
            transition:
                transform 150ms ease,
                box-shadow 150ms ease,
                opacity 150ms ease;
        }

        .speaking-edit-page .action-button:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: var(--page-shadow-soft);
        }

        .speaking-edit-page .action-button:disabled {
            cursor: wait;
            opacity: 0.7;
        }

        .speaking-edit-page .action-button svg {
            width: 17px;
            height: 17px;
        }

        .speaking-edit-page .action-cancel {
            border-color: var(--page-border);
            background: var(--page-card-soft);
            color: var(--page-text-soft);
        }

        .speaking-edit-page .action-save {
            min-width: 205px;
            background: linear-gradient(100deg, #7c3aed, #2563eb);
            color: #ffffff;
        }

        /*
        |--------------------------------------------------------------------------
        | Responsive
        |--------------------------------------------------------------------------
        */

        @media (max-width: 960px) {
            .speaking-edit-page .page-hero {
                grid-template-columns: minmax(0, 1fr);
            }

            .speaking-edit-page .hero-summary {
                width: 100%;
            }

            .speaking-edit-page .summary-item {
                flex: 1;
            }

            .speaking-edit-page .settings-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .speaking-edit-page .support-grid {
                grid-template-columns: minmax(0, 1fr);
            }
        }

        @media (max-width: 720px) {
            .speaking-edit-page .edit-shell {
                gap: 13px;
            }

            .speaking-edit-page .page-hero {
                gap: 18px;
                padding: 18px;
                border-radius: 18px;
            }

            .speaking-edit-page .hero-title {
                font-size: 27px;
            }

            .speaking-edit-page .hero-summary {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .speaking-edit-page .summary-item {
                min-width: 0;
                padding: 11px;
            }

            .speaking-edit-page .lesson-card {
                align-items: flex-start;
                flex-direction: column;
                padding: 14px;
            }

            .speaking-edit-page .lesson-description {
                white-space: normal;
            }

            .speaking-edit-page .lesson-tags {
                justify-content: flex-start;
            }

            .speaking-edit-page .field-grid,
            .speaking-edit-page .role-grid,
            .speaking-edit-page .duration-grid,
            .speaking-edit-page .toggle-grid {
                grid-template-columns: minmax(0, 1fr);
            }

            .speaking-edit-page .section-header,
            .speaking-edit-page .section-body {
                padding: 15px;
            }

            .speaking-edit-page .settings-grid {
                grid-template-columns: minmax(0, 1fr);
            }

            .speaking-edit-page .discussion-point {
                grid-template-columns: 32px minmax(0, 1fr) 36px;
            }

            .speaking-edit-page .action-bar {
                position: static;
                align-items: stretch;
                flex-direction: column;
            }

            .speaking-edit-page .action-buttons {
                width: 100%;
                flex-direction: column-reverse;
            }

            .speaking-edit-page .action-button {
                width: 100%;
            }
        }
    </style>

    <div class="speaking-edit-page">
        <div class="edit-shell">

            {{-- ========================================================= --}}
            {{-- PAGE HEADER --}}
            {{-- ========================================================= --}}

            <section class="edit-card page-hero">
                <div class="hero-copy">
                    <div class="hero-toolbar">
                        <a
                            href="{{ route('admin.speaking-materials.index', $material->lesson_id) }}"
                            class="back-link">

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

                            Back to Speaking Tasks
                        </a>

                        <span class="context-badge">
                            <svg
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                aria-hidden="true">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 18.5a6.5 6.5 0 006.5-6.5V6a6.5 6.5 0 00-13 0v6a6.5 6.5 0 006.5 6.5zm0 0V22m-4 0h8">
                                </path>
                            </svg>

                            @if ($isAssessment)
                                {{ $assessmentLabel }} Individual Speaking
                            @else
                                Pair Speaking Lesson
                            @endif
                        </span>
                    </div>

                    <h1 class="hero-title">
                        @if ($isAssessment)
                            Edit Individual Speaking Assessment
                        @else
                            Edit Speaking Task
                        @endif
                    </h1>

                    <p class="hero-description">
                        @if ($isAssessment)
                            Update the individual fable or short-story
                            presentation completed by one student for 2-3 minutes.
                        @else
                            Update the pair-speaking activity, student roles,
                            discussion points, duration, and AI evaluation.
                        @endif
                    </p>
                </div>

                <div class="hero-summary">
                    <article class="summary-item">
                        <span class="summary-icon">
                            @if ($isAssessment)
                                <svg
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 12a4 4 0 100-8 4 4 0 000 8zm-7 8a7 7 0 0114 0">
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
                                        d="M17 20h5v-2a4 4 0 00-4-4h-1M7 20H2v-2a4 4 0 014-4h1m10-4a4 4 0 100-8 4 4 0 000 8zM7 10a4 4 0 100-8 4 4 0 000 8z">
                                    </path>
                                </svg>
                            @endif
                        </span>

                        <span>
                            <span class="summary-label">Mode</span>
                            <span class="summary-value">
                                {{ $isAssessment ? 'Individual' : 'Pair Work' }}
                            </span>
                        </span>
                    </article>

                    <article class="summary-item">
                        <span class="summary-icon is-blue">
                            <svg
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                aria-hidden="true">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M17 20h5v-2a4 4 0 00-4-4h-1M7 20H2v-2a4 4 0 014-4h1m10-4a4 4 0 100-8 4 4 0 000 8zM7 10a4 4 0 100-8 4 4 0 000 8z">
                                </path>
                            </svg>
                        </span>

                        <span>
                            <span class="summary-label">Participants</span>
                            <span class="summary-value">
                                {{ $isAssessment ? '1 Student' : '2 Students' }}
                            </span>
                        </span>
                    </article>
                </div>
            </section>

            {{-- ========================================================= --}}
            {{-- CURRENT LESSON --}}
            {{-- ========================================================= --}}

            <section class="edit-card lesson-card">
                <div class="lesson-main">
                    <span class="lesson-icon">
                        <svg
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 18.5a6.5 6.5 0 006.5-6.5V6a6.5 6.5 0 00-13 0v6a6.5 6.5 0 006.5 6.5zm0 0V22m-4 0h8">
                            </path>
                        </svg>
                    </span>

                    <div>
                        <p class="lesson-label">Current lesson</p>

                        <h2 class="lesson-title">
                            {{ $lesson?->title ?? 'Speaking' }}
                        </h2>

                        @if ($lesson?->description)
                            <p class="lesson-description">
                                {{ $lesson->description }}
                            </p>
                        @endif
                    </div>
                </div>

                <div class="lesson-tags">
                    @if ($isAssessment)
                        <span class="lesson-tag is-purple">
                            {{ $assessmentLabel }}
                        </span>

                        <span class="lesson-tag is-green">
                            Individual
                        </span>
                    @else
                        <span class="lesson-tag is-purple">
                            Pair Work
                        </span>
                    @endif

                    <span class="lesson-tag is-blue">
                        Speaking
                    </span>
                </div>
            </section>

            @if ($errors->any())
                <div class="validation-summary">
                    <strong>Please review the following fields:</strong>

                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- ========================================================= --}}
            {{-- FORM --}}
            {{-- ========================================================= --}}

            <form
                id="speaking-material-form"
                action="{{ route('admin.speaking-materials.update', $material->id) }}"
                method="POST"
                enctype="multipart/form-data"
                class="edit-form">

                @csrf
                @method('PUT')

                {{-- ===================================================== --}}
                {{-- TASK INFORMATION --}}
                {{-- ===================================================== --}}

                <section class="edit-card form-section">
                    <header class="section-header">
                        <span class="section-icon">
                            <svg
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                aria-hidden="true">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 12h6m-6 4h6M9 8h6M6 3h9l3 3v15H6V3z">
                                </path>
                            </svg>
                        </span>

                        <div class="section-heading">
                            <h2 class="section-title">Task Information</h2>

                            <p class="section-description">
                                @if ($isAssessment)
                                    Update the individual presentation title,
                                    instruction, and presentation context.
                                @else
                                    Update the task title, instruction, and
                                    conversation situation.
                                @endif
                            </p>
                        </div>
                    </header>

                    <div class="section-body">
                        <div class="field-grid">
                            <div class="field-full">
                                <label for="title" class="field-label">
                                    <span>Task Title</span>
                                </label>

                                <input
                                    id="title"
                                    type="text"
                                    name="title"
                                    value="{{ old('title', $material->title) }}"
                                    placeholder="{{ $isAssessment
                                        ? 'Activity 2 - Individual Speaking'
                                        : 'Example: Favourite Fable Discussion' }}"
                                    required
                                    class="field-control">

                                @error('title')
                                    <p class="field-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="instruction" class="field-label">
                                    <span>Instruction</span>
                                </label>

                                <textarea
                                    id="instruction"
                                    name="instruction"
                                    rows="{{ $isAssessment ? 6 : 5 }}"
                                    required
                                    placeholder="{{ $isAssessment
                                        ? 'Explain what the student must present.'
                                        : 'Explain what both students must do.' }}"
                                    class="field-control">{{ old('instruction', $material->instruction) }}</textarea>

                                <p class="field-help">
                                    Keep the instruction short, direct, and easy
                                    for students to understand.
                                </p>

                                @error('instruction')
                                    <p class="field-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="scenario" class="field-label">
                                    <span>
                                        {{ $isAssessment
                                            ? 'Presentation Context'
                                            : 'Conversation Scenario' }}
                                    </span>

                                    @if ($isAssessment)
                                        <span class="field-optional">Optional</span>
                                    @endif
                                </label>

                                <textarea
                                    id="scenario"
                                    name="scenario"
                                    rows="5"
                                    @if (!$isAssessment) required @endif
                                    placeholder="{{ $isAssessment
                                        ? 'Describe the individual presentation situation.'
                                        : 'Describe the conversation situation and context.' }}"
                                    class="field-control">{{ $scenarioValue }}</textarea>

                                <p class="field-help">
                                    @if ($isAssessment)
                                        This is an individual presentation context,
                                        not a Student A and Student B conversation.
                                    @else
                                        Explain the setting and context for both
                                        students.
                                    @endif
                                </p>

                                @error('scenario')
                                    <p class="field-error">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </section>

                {{-- ===================================================== --}}
                {{-- STUDENT ROLES --}}
                {{-- ===================================================== --}}

                @if (!$isAssessment)
                    <section class="edit-card form-section">
                        <header class="section-header">
                            <span class="section-icon is-blue">
                                <svg
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M17 20h5v-2a4 4 0 00-4-4h-1M7 20H2v-2a4 4 0 014-4h1m10-4a4 4 0 100-8 4 4 0 000 8zM7 10a4 4 0 100-8 4 4 0 000 8z">
                                    </path>
                                </svg>
                            </span>

                            <div class="section-heading">
                                <h2 class="section-title">Student Roles</h2>

                                <p class="section-description">
                                    Update the responsibilities of Student A and
                                    Student B.
                                </p>
                            </div>
                        </header>

                        <div class="section-body">
                            <div class="role-grid">
                                <article class="role-card">
                                    <div class="role-heading">
                                        <span class="role-letter">A</span>
                                        <span class="role-title">Student A Role</span>
                                    </div>

                                    <textarea
                                        id="role_a"
                                        name="role_a"
                                        rows="6"
                                        required
                                        placeholder="Describe Student A's role and responsibilities."
                                        class="field-control">{{ old('role_a', $material->role_a) }}</textarea>

                                    @error('role_a')
                                        <p class="field-error">{{ $message }}</p>
                                    @enderror
                                </article>

                                <article class="role-card">
                                    <div class="role-heading">
                                        <span class="role-letter is-blue">B</span>
                                        <span class="role-title">Student B Role</span>
                                    </div>

                                    <textarea
                                        id="role_b"
                                        name="role_b"
                                        rows="6"
                                        required
                                        placeholder="Describe Student B's role and responsibilities."
                                        class="field-control">{{ old('role_b', $material->role_b) }}</textarea>

                                    @error('role_b')
                                        <p class="field-error">{{ $message }}</p>
                                    @enderror
                                </article>
                            </div>
                        </div>
                    </section>
                @endif

                {{-- ===================================================== --}}
                {{-- REQUIREMENTS / DISCUSSION POINTS --}}
                {{-- ===================================================== --}}

                <section class="edit-card form-section">
                    <header class="section-header">
                        <span class="section-icon is-green">
                            <svg
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                aria-hidden="true">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 11l3 3L22 4M2 12l5 5M2 5h5M2 19h5">
                                </path>
                            </svg>
                        </span>

                        <div class="section-heading">
                            <h2 class="section-title">
                                {{ $isAssessment
                                    ? 'Presentation Requirements'
                                    : 'Discussion Points' }}
                            </h2>

                            <p class="section-description">
                                @if ($isAssessment)
                                    Update the story elements required in the
                                    individual presentation.
                                @else
                                    Update the topics that both students must
                                    discuss.
                                @endif
                            </p>
                        </div>
                    </header>

                    <div class="section-body">
                        @if ($isAssessment)
                            <div class="requirement-note">
                                <strong>Individual presentation structure</strong>

                                <p>
                                    The AI checks whether the student covers each
                                    requirement clearly and completely.
                                </p>
                            </div>
                        @endif

                        <div
                            id="discussionPointsContainer"
                            class="points-list">

                            @foreach ($discussionPoints as $index => $point)
                                <div class="discussion-point">
                                    <span class="discussion-number">
                                        {{ $index + 1 }}
                                    </span>

                                    <input
                                        type="text"
                                        name="discussion_points[]"
                                        value="{{ $point }}"
                                        placeholder="{{ $isAssessment
                                            ? 'Enter presentation requirement'
                                            : 'Enter discussion point' }}"
                                        required
                                        class="field-control">

                                    <button
                                        type="button"
                                        class="remove-point"
                                        onclick="removeDiscussionPoint(this)"
                                        aria-label="Remove this item">

                                        <svg
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                            aria-hidden="true">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12">
                                            </path>
                                        </svg>
                                    </button>
                                </div>
                            @endforeach
                        </div>

                        <button
                            type="button"
                            class="add-point"
                            onclick="addDiscussionPoint()">

                            <svg
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                aria-hidden="true">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 5v14m-7-7h14">
                                </path>
                            </svg>

                            {{ $isAssessment
                                ? 'Add Presentation Requirement'
                                : 'Add Discussion Point' }}
                        </button>

                        @error('discussion_points')
                            <p class="field-error">{{ $message }}</p>
                        @enderror

                        @error('discussion_points.*')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                </section>

                {{-- ===================================================== --}}
                {{-- DURATION AND EVALUATION --}}
                {{-- ===================================================== --}}

                <section class="edit-card form-section">
                    <header class="section-header">
                        <span class="section-icon is-cyan">
                            <svg
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                aria-hidden="true">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z">
                                </path>
                            </svg>
                        </span>

                        <div class="section-heading">
                            <h2 class="section-title">
                                Duration and Evaluation
                            </h2>

                            <p class="section-description">
                                @if ($isAssessment)
                                    This assessment uses a locked individual
                                    configuration.
                                @else
                                    Update recording duration and evaluation
                                    settings.
                                @endif
                            </p>
                        </div>
                    </header>

                    <div class="section-body">
                        @if ($isAssessment)
                            <input
                                type="hidden"
                                name="min_duration_minutes"
                                value="2">

                            <input
                                type="hidden"
                                name="max_duration_minutes"
                                value="3">

                            <input
                                type="hidden"
                                name="is_pair_work"
                                value="0">

                            <input
                                type="hidden"
                                name="ai_evaluation_enabled"
                                value="1">

                            <div class="settings-grid">
                                <article class="setting-item">
                                    <span class="setting-icon">
                                        <svg
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                            aria-hidden="true">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z">
                                            </path>
                                        </svg>
                                    </span>

                                    <span>
                                        <span class="setting-label">Minimum</span>
                                        <span class="setting-value">2 minutes</span>
                                    </span>
                                </article>

                                <article class="setting-item">
                                    <span class="setting-icon">
                                        <svg
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                            aria-hidden="true">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z">
                                            </path>
                                        </svg>
                                    </span>

                                    <span>
                                        <span class="setting-label">Maximum</span>
                                        <span class="setting-value">3 minutes</span>
                                    </span>
                                </article>

                                <article class="setting-item">
                                    <span class="setting-icon is-purple">
                                        <svg
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                            aria-hidden="true">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M12 12a4 4 0 100-8 4 4 0 000 8zm-7 8a7 7 0 0114 0">
                                            </path>
                                        </svg>
                                    </span>

                                    <span>
                                        <span class="setting-label">
                                            Activity mode
                                        </span>

                                        <span class="setting-value">
                                            Individual
                                        </span>
                                    </span>
                                </article>

                                <article class="setting-item">
                                    <span class="setting-icon is-green">
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
                                    </span>

                                    <span>
                                        <span class="setting-label">
                                            AI Evaluation
                                        </span>

                                        <span class="setting-value">Enabled</span>
                                    </span>
                                </article>
                            </div>

                            <div class="locked-note">
                                <strong>Assessment settings are locked</strong>

                                <p>
                                    Saving automatically keeps Pair Work disabled,
                                    duration at 2-3 minutes, and AI Evaluation
                                    enabled.
                                </p>
                            </div>
                        @else
                            <div class="duration-grid">
                                <div>
                                    <label
                                        for="min_duration_minutes"
                                        class="field-label">

                                        <span>Minimum Duration</span>
                                    </label>

                                    <div class="duration-control">
                                        <input
                                            id="min_duration_minutes"
                                            type="number"
                                            name="min_duration_minutes"
                                            min="1"
                                            max="60"
                                            value="{{ $minDurationMinutes }}"
                                            required
                                            class="field-control">

                                        <span class="duration-unit">
                                            minutes
                                        </span>
                                    </div>

                                    @error('min_duration_minutes')
                                        <p class="field-error">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label
                                        for="max_duration_minutes"
                                        class="field-label">

                                        <span>Maximum Duration</span>
                                    </label>

                                    <div class="duration-control">
                                        <input
                                            id="max_duration_minutes"
                                            type="number"
                                            name="max_duration_minutes"
                                            min="1"
                                            max="60"
                                            value="{{ $maxDurationMinutes }}"
                                            required
                                            class="field-control">

                                        <span class="duration-unit">
                                            minutes
                                        </span>
                                    </div>

                                    @error('max_duration_minutes')
                                        <p class="field-error">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="toggle-grid">
                                <label class="toggle-card">
                                    <input
                                        type="hidden"
                                        name="is_pair_work"
                                        value="0">

                                    <input
                                        type="checkbox"
                                        name="is_pair_work"
                                        value="1"
                                        @checked(
                                            old(
                                                'is_pair_work',
                                                $material->is_pair_work
                                                    ? '1'
                                                    : '0'
                                            ) == '1'
                                        )>

                                    <span>
                                        <span class="toggle-title">
                                            Pair Work
                                        </span>

                                        <span class="toggle-description">
                                            This activity is completed by two
                                            students working together.
                                        </span>
                                    </span>
                                </label>

                                <label class="toggle-card">
                                    <input
                                        type="hidden"
                                        name="ai_evaluation_enabled"
                                        value="0">

                                    <input
                                        type="checkbox"
                                        name="ai_evaluation_enabled"
                                        value="1"
                                        @checked(
                                            old(
                                                'ai_evaluation_enabled',
                                                $material->ai_evaluation_enabled
                                                    ? '1'
                                                    : '0'
                                            ) == '1'
                                        )>

                                    <span>
                                        <span class="toggle-title">
                                            AI Evaluation
                                        </span>

                                        <span class="toggle-description">
                                            Evaluate the submitted recording with
                                            the pair-speaking rubric.
                                        </span>
                                    </span>
                                </label>
                            </div>

                            @error('is_pair_work')
                                <p class="field-error">{{ $message }}</p>
                            @enderror

                            @error('ai_evaluation_enabled')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        @endif
                    </div>
                </section>

                {{-- ===================================================== --}}
                {{-- SUPPORTING CONTENT --}}
                {{-- ===================================================== --}}

                <section class="edit-card form-section">
                    <header class="section-header">
                        <span class="section-icon is-blue">
                            <svg
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                aria-hidden="true">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 5a2 2 0 012-2h12a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm4 4h8M8 13h8M8 17h5">
                                </path>
                            </svg>
                        </span>

                        <div class="section-heading">
                            <h2 class="section-title">
                                {{ $isAssessment
                                    ? 'Example Presentation'
                                    : 'Supporting Content' }}
                            </h2>

                            <p class="section-description">
                                Update the optional supporting text and image
                                shown to students.
                            </p>
                        </div>
                    </header>

                    <div class="section-body">
                        <div class="support-grid">
                            <div>
                                <label for="passage" class="field-label">
                                    <span>
                                        {{ $isAssessment
                                            ? 'Presentation Example'
                                            : 'Supporting Text' }}
                                    </span>

                                    <span class="field-optional">Optional</span>
                                </label>

                                <textarea
                                    id="passage"
                                    name="passage"
                                    rows="{{ $isAssessment ? 14 : 10 }}"
                                    placeholder="{{ $isAssessment
                                        ? 'Enter an example individual presentation.'
                                        : 'Optional dialogue, vocabulary, or supporting material.' }}"
                                    class="field-control">{{ $passageValue }}</textarea>

                                @error('passage')
                                    <p class="field-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="field-label">
                                    <span>Supporting Image</span>
                                    <span class="field-optional">Optional</span>
                                </label>

                                <div class="current-image-card">
                                    @if ($material->image)
                                        <img
                                            id="currentImagePreview"
                                            src="{{ asset('storage/' . $material->image) }}"
                                            alt="{{ $material->title }}">
                                    @else
                                        <div
                                            id="noImagePlaceholder"
                                            class="no-image">

                                            No supporting image is currently
                                            uploaded.
                                        </div>

                                        <img
                                            id="currentImagePreview"
                                            src=""
                                            alt="Selected supporting image"
                                            style="display: none;">
                                    @endif
                                </div>

                                <div class="upload-box">
                                    <input
                                        id="image"
                                        type="file"
                                        name="image"
                                        accept="image/jpeg,image/png,image/webp"
                                        class="file-control">

                                    <p class="upload-meta">
                                        {{ $material->image
                                            ? 'Select a new image only when you want to replace the current image.'
                                            : 'Select an image to add supporting material.' }}
                                        JPG, PNG, or WEBP. Maximum 2 MB.
                                    </p>
                                </div>

                                @error('image')
                                    <p class="field-error">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </section>

                {{-- ===================================================== --}}
                {{-- ACTION BAR --}}
                {{-- ===================================================== --}}

                <div class="action-bar">
                    <p class="action-note">
                        Existing data remains unchanged unless you edit the
                        corresponding field and save it.
                    </p>

                    <div class="action-buttons">
                        <a
                            href="{{ route('admin.speaking-materials.index', $material->lesson_id) }}"
                            class="action-button action-cancel">

                            Cancel
                        </a>

                        <button
                            id="saveSpeakingMaterialButton"
                            type="submit"
                            class="action-button action-save">

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

                            <span id="saveSpeakingMaterialLabel">
                                {{ $isAssessment
                                    ? 'Update Individual Assessment'
                                    : 'Save Changes' }}
                            </span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const isAssessment = @json($isAssessment);

            const discussionContainer = document.getElementById(
                'discussionPointsContainer'
            );

            const materialForm = document.getElementById(
                'speaking-material-form'
            );

            const saveButton = document.getElementById(
                'saveSpeakingMaterialButton'
            );

            const saveLabel = document.getElementById(
                'saveSpeakingMaterialLabel'
            );

            const imageInput = document.getElementById('image');
            const currentImagePreview = document.getElementById(
                'currentImagePreview'
            );

            const noImagePlaceholder = document.getElementById(
                'noImagePlaceholder'
            );

            let isSubmitting = false;

            function createPointElement() {
                const wrapper = document.createElement('div');

                wrapper.className = 'discussion-point';

                const placeholder = isAssessment
                    ? 'Enter presentation requirement'
                    : 'Enter discussion point';

                wrapper.innerHTML = `
                    <span class="discussion-number"></span>

                    <input
                        type="text"
                        name="discussion_points[]"
                        placeholder="${placeholder}"
                        required
                        class="field-control">

                    <button
                        type="button"
                        class="remove-point"
                        aria-label="Remove this item">

                        <svg
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            aria-hidden="true">

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                    </button>
                `;

                const removeButton = wrapper.querySelector('.remove-point');

                removeButton.addEventListener('click', function () {
                    removeDiscussionPoint(removeButton);
                });

                return wrapper;
            }

            window.addDiscussionPoint = function () {
                if (!discussionContainer) {
                    return;
                }

                const element = createPointElement();

                discussionContainer.appendChild(element);
                updateDiscussionNumbers();

                const input = element.querySelector('input');

                if (input) {
                    input.focus();
                }
            };

            window.removeDiscussionPoint = function (button) {
                if (!discussionContainer) {
                    return;
                }

                const items = discussionContainer.querySelectorAll(
                    '.discussion-point'
                );

                if (items.length <= 1) {
                    window.alert(
                        isAssessment
                            ? 'At least one presentation requirement is required.'
                            : 'At least one discussion point is required.'
                    );

                    return;
                }

                const item = button.closest('.discussion-point');

                if (item) {
                    item.remove();
                    updateDiscussionNumbers();
                }
            };

            window.updateDiscussionNumbers = function () {
                if (!discussionContainer) {
                    return;
                }

                discussionContainer
                    .querySelectorAll('.discussion-point')
                    .forEach(function (item, index) {
                        const number = item.querySelector(
                            '.discussion-number'
                        );

                        if (number) {
                            number.textContent = index + 1;
                        }
                    });
            };

            if (imageInput && currentImagePreview) {
                imageInput.addEventListener('change', function () {
                    const file = imageInput.files
                        ? imageInput.files[0]
                        : null;

                    if (!file) {
                        return;
                    }

                    if (!file.type.startsWith('image/')) {
                        imageInput.value = '';

                        window.alert(
                            'Please select a valid image file.'
                        );

                        return;
                    }

                    const reader = new FileReader();

                    reader.addEventListener('load', function (event) {
                        if (noImagePlaceholder) {
                            noImagePlaceholder.style.display = 'none';
                        }

                        currentImagePreview.src = event.target.result;
                        currentImagePreview.style.display = 'block';
                    });

                    reader.readAsDataURL(file);
                });
            }

            if (materialForm) {
                materialForm.addEventListener('submit', function (event) {
                    if (isSubmitting) {
                        event.preventDefault();
                        return;
                    }

                    if (!materialForm.checkValidity()) {
                        return;
                    }

                    isSubmitting = true;

                    if (saveButton) {
                        saveButton.disabled = true;
                    }

                    if (saveLabel) {
                        saveLabel.textContent = 'Saving changes...';
                    }
                });
            }

            updateDiscussionNumbers();
        });
    </script>
@endsection
