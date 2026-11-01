@extends('layouts.admin')

@section('content')
    @php
        $contextTitle = $material->title ?? 'Reading Material';

        $backRoute = route(
            'admin.reading-materials.index',
            $material->lesson_id
        );

        $createRoute = route(
            'admin.reading-questions.create',
            $material->id
        );

        $unassignedCount = $questions
            ->filter(
                fn ($question) =>
                    blank($question->sub_skill)
            )
            ->count();

        $usedUnassignedCount = $questions
            ->filter(function ($question) use ($usedQuestionCounts) {
                return blank($question->sub_skill)
                    && (int) (
                        $usedQuestionCounts[$question->id]
                        ?? 0
                    ) > 0;
            })
            ->count();
    @endphp

    <style>
        .rqb-page {
            --bg: #ffffff;
            --bg-soft: #f8fafc;
            --bg-muted: #f1f5f9;
            --text: #0f172a;
            --text-soft: #334155;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --border-strong: #cbd5e1;
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --success: #059669;
            --warning: #d97706;
            --danger: #dc2626;
            --shadow: 0 12px 32px rgba(15, 23, 42, .06);

            width: 100%;
            max-width: 1500px;
            margin: 0 auto;
            padding: 20px 0 40px;
            color: var(--text);
        }

        .dark .rqb-page {
            --bg: #111827;
            --bg-soft: #172033;
            --bg-muted: #1e293b;
            --text: #f8fafc;
            --text-soft: #d7e0ec;
            --text-muted: #94a3b8;
            --border: #27364a;
            --border-strong: #3b4a61;
            --primary: #3b82f6;
            --primary-hover: #2563eb;
            --success: #34d399;
            --warning: #fbbf24;
            --danger: #f87171;
            --shadow: none;
        }

        .rqb-page,
        .rqb-page * {
            box-sizing: border-box;
        }

        .rqb-stack > * + * {
            margin-top: 18px;
        }

        .rqb-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 18px;
        }

        .rqb-title {
            margin: 0;
            color: var(--text);
            font-size: 30px;
            line-height: 1.15;
            font-weight: 900;
            letter-spacing: -.03em;
        }

        .rqb-subtitle {
            margin: 7px 0 0;
            color: var(--text-muted);
            font-size: 14px;
            line-height: 1.6;
        }

        .rqb-actions {
            display: flex;
            gap: 10px;
        }

        .rqb-btn {
            display: inline-flex;
            min-height: 42px;
            align-items: center;
            justify-content: center;
            padding: 10px 16px;
            border-radius: 11px;
            font-size: 13px;
            line-height: 1;
            font-weight: 850;
            text-decoration: none;
            cursor: pointer;
            transition: .16s ease;
        }

        .rqb-btn-secondary {
            border: 1px solid var(--border-strong);
            background: var(--bg);
            color: var(--text-soft);
        }

        .rqb-btn-secondary:hover {
            background: var(--bg-muted);
            color: var(--text);
        }

        .rqb-btn-primary {
            border: 1px solid var(--primary);
            background: var(--primary);
            color: #fff;
        }

        .rqb-btn-primary:hover {
            background: var(--primary-hover);
            border-color: var(--primary-hover);
        }

        .rqb-alert {
            padding: 13px 15px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 750;
        }

        .rqb-alert-success {
            border: 1px solid rgba(16, 185, 129, .25);
            background: rgba(16, 185, 129, .09);
            color: var(--success);
        }

        .rqb-alert-error {
            border: 1px solid rgba(239, 68, 68, .25);
            background: rgba(239, 68, 68, .09);
            color: var(--danger);
        }

        .rqb-summary {
            display: grid;
            grid-template-columns: minmax(0, 1fr) repeat(3, auto);
            gap: 14px;
            align-items: center;
            padding: 20px;
            border: 1px solid var(--border);
            border-radius: 18px;
            background: var(--bg);
            box-shadow: var(--shadow);
        }

        .rqb-summary-label {
            color: var(--primary);
            font-size: 11px;
            font-weight: 900;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .rqb-summary-title {
            margin: 5px 0 0;
            color: var(--text);
            font-size: 18px;
            font-weight: 900;
        }

        .rqb-stat {
            min-width: 115px;
            padding: 11px 14px;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: var(--bg-soft);
        }

        .rqb-stat-label {
            color: var(--text-muted);
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .rqb-stat-value {
            margin-top: 3px;
            color: var(--text);
            font-size: 20px;
            font-weight: 900;
        }

        .rqb-stat-warning .rqb-stat-value {
            color: var(--warning);
        }

        .rqb-card {
            overflow: hidden;
            border: 1px solid var(--border);
            border-radius: 18px;
            background: var(--bg);
            box-shadow: var(--shadow);
        }

        .rqb-card-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 18px 20px;
            border-bottom: 1px solid var(--border);
        }

        .rqb-card-title {
            margin: 0;
            color: var(--text);
            font-size: 17px;
            font-weight: 900;
        }

        .rqb-card-desc {
            margin: 5px 0 0;
            color: var(--text-muted);
            font-size: 12px;
            line-height: 1.5;
        }

        .rqb-list-head,
        .rqb-row {
            display: grid;
            grid-template-columns:
                minmax(270px, 2.2fr)
                minmax(190px, .95fr)
                95px
                80px
                150px;
            gap: 14px;
            align-items: center;
        }

        .rqb-list-head {
            padding: 12px 20px;
            background: var(--bg-soft);
            border-bottom: 1px solid var(--border);
            color: var(--text-muted);
            font-size: 10px;
            font-weight: 900;
            letter-spacing: .06em;
            text-transform: uppercase;
        }

        .rqb-row {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
        }

        .rqb-row:last-child {
            border-bottom: 0;
        }

        .rqb-row.is-priority {
            background: rgba(245, 158, 11, .045);
        }

        .rqb-question {
            min-width: 0;
        }

        .rqb-question-text {
            margin: 0;
            color: var(--text-soft);
            font-size: 13px;
            line-height: 1.6;
            font-weight: 700;
            overflow-wrap: anywhere;
        }

        .rqb-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 8px;
        }

        .rqb-badge {
            display: inline-flex;
            align-items: center;
            padding: 5px 8px;
            border-radius: 999px;
            font-size: 10px;
            line-height: 1;
            font-weight: 850;
        }

        .rqb-used {
            background: rgba(37, 99, 235, .10);
            color: var(--primary);
        }

        .rqb-needs {
            background: rgba(245, 158, 11, .13);
            color: var(--warning);
        }

        .rqb-assigned {
            background: rgba(16, 185, 129, .10);
            color: var(--success);
        }

        .rqb-select {
            width: 100%;
            min-height: 42px;
            padding: 8px 35px 8px 11px;
            border: 1px solid var(--border-strong);
            border-radius: 10px;
            background: var(--bg-soft);
            color: var(--text);
            font-size: 12px;
            font-weight: 750;
            outline: none;
        }

        .rqb-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, .12);
        }

        .rqb-center {
            text-align: center;
        }

        .rqb-answer {
            display: inline-flex;
            min-width: 34px;
            min-height: 30px;
            align-items: center;
            justify-content: center;
            padding: 0 9px;
            border-radius: 999px;
            background: rgba(16, 185, 129, .10);
            color: var(--success);
            font-size: 12px;
            font-weight: 900;
        }

        .rqb-score {
            color: var(--text-soft);
            font-size: 13px;
            font-weight: 900;
        }

        .rqb-row-actions {
            display: flex;
            justify-content: flex-end;
            gap: 7px;
        }

        .rqb-mini {
            display: inline-flex;
            min-height: 35px;
            align-items: center;
            justify-content: center;
            padding: 8px 11px;
            border-radius: 9px;
            font-size: 11px;
            font-weight: 850;
            text-decoration: none;
            cursor: pointer;
        }

        .rqb-edit {
            border: 1px solid rgba(59, 130, 246, .23);
            background: rgba(59, 130, 246, .09);
            color: var(--primary);
        }

        .rqb-delete {
            border: 1px solid rgba(239, 68, 68, .23);
            background: rgba(239, 68, 68, .08);
            color: var(--danger);
        }

        .rqb-save {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            padding: 15px 20px;
            border-top: 1px solid var(--border);
            background: var(--bg-soft);
        }

        .rqb-save-text {
            color: var(--text-muted);
            font-size: 12px;
            line-height: 1.5;
        }

        .rqb-empty {
            padding: 50px 20px;
            text-align: center;
            color: var(--text-muted);
        }

        .rqb-modal {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 18px;
            background: rgba(2, 6, 23, .72);
        }

        .rqb-modal.is-open {
            display: flex;
        }

        .rqb-modal-panel {
            width: 100%;
            max-width: 430px;
            padding: 22px;
            border: 1px solid var(--border);
            border-radius: 17px;
            background: var(--bg);
        }

        .rqb-modal-title {
            margin: 0;
            color: var(--text);
            font-size: 18px;
            font-weight: 900;
        }

        .rqb-modal-copy {
            margin: 8px 0 0;
            color: var(--text-muted);
            font-size: 13px;
            line-height: 1.6;
        }

        .rqb-modal-question {
            margin-top: 13px;
            padding: 12px;
            border-radius: 11px;
            background: var(--bg-soft);
            color: var(--text-soft);
            font-size: 12px;
            line-height: 1.5;
        }

        .rqb-modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            margin-top: 18px;
        }

        @media (max-width: 1050px) {
            .rqb-summary {
                grid-template-columns: repeat(3, 1fr);
            }

            .rqb-summary-main {
                grid-column: 1 / -1;
            }

            .rqb-list-head {
                display: none;
            }

            .rqb-row {
                grid-template-columns: minmax(0, 1fr) minmax(180px, .55fr);
            }

            .rqb-row .rqb-center,
            .rqb-row-actions {
                justify-self: start;
            }

            .rqb-row-actions {
                justify-content: flex-start;
            }
        }

        @media (max-width: 700px) {
            .rqb-header {
                align-items: stretch;
                flex-direction: column;
            }

            .rqb-actions {
                display: grid;
                grid-template-columns: 1fr;
            }

            .rqb-summary {
                grid-template-columns: 1fr;
            }

            .rqb-summary-main {
                grid-column: auto;
            }

            .rqb-row {
                grid-template-columns: 1fr;
            }

            .rqb-card-head,
            .rqb-save {
                align-items: stretch;
                flex-direction: column;
            }

            .rqb-save .rqb-btn {
                width: 100%;
            }
        }
    </style>

    <div class="rqb-page">
        <div class="rqb-stack">

            <header class="rqb-header">
                <div>
                    <h1 class="rqb-title">
                        Reading Questions
                    </h1>

                    <p class="rqb-subtitle">
                        Assign sub-skills in bulk so Reading performance can be
                        analysed in more detail on the Progress page.
                    </p>
                </div>

                <div class="rqb-actions">
                    <a
                        href="{{ $backRoute }}"
                        class="rqb-btn rqb-btn-secondary">
                        ← Back
                    </a>

                    <a
                        href="{{ $createRoute }}"
                        class="rqb-btn rqb-btn-primary">
                        + Add Question
                    </a>
                </div>
            </header>

            @if (session('success'))
                <div class="rqb-alert rqb-alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="rqb-alert rqb-alert-error">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rqb-alert rqb-alert-error">
                    <strong>Please check the submitted data.</strong>

                    <ul style="margin: 8px 0 0; padding-left: 18px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <section class="rqb-summary">
                <div class="rqb-summary-main">
                    <div class="rqb-summary-label">
                        Reading Material
                    </div>

                    <h2 class="rqb-summary-title">
                        {{ $contextTitle }}
                    </h2>
                </div>

                <div class="rqb-stat">
                    <div class="rqb-stat-label">Questions</div>
                    <div class="rqb-stat-value">
                        {{ $questions->count() }}
                    </div>
                </div>

                <div class="rqb-stat rqb-stat-warning">
                    <div class="rqb-stat-label">Not Assigned</div>
                    <div class="rqb-stat-value">
                        {{ $unassignedCount }}
                    </div>
                </div>

                <div class="rqb-stat rqb-stat-warning">
                    <div class="rqb-stat-label">Used + Missing</div>
                    <div class="rqb-stat-value">
                        {{ $usedUnassignedCount }}
                    </div>
                </div>
            </section>

            <section class="rqb-card">
                <div class="rqb-card-head">
                    <div>
                        <h3 class="rqb-card-title">
                            Bulk Sub-Skill Assignment
                        </h3>

                        <p class="rqb-card-desc">
                            Prioritise questions marked “Used in results” and
                            “Needs sub-skill” because they already affect existing
                            student assessment history.
                        </p>
                    </div>
                </div>

                @if ($questions->isNotEmpty())
                    <form
                        action="{{ route('admin.reading-questions.bulk-sub-skills', $material->id) }}"
                        method="POST">

                        @csrf
                        @method('PUT')

                        <div class="rqb-list-head">
                            <div>Question</div>
                            <div>Sub-Skill</div>
                            <div style="text-align:center;">Correct</div>
                            <div style="text-align:center;">Score</div>
                            <div style="text-align:right;">Actions</div>
                        </div>

                        @foreach ($questions as $question)
                            @php
                                $usageCount = (int) (
                                    $usedQuestionCounts[$question->id]
                                    ?? 0
                                );

                                $needsSubSkill =
                                    blank($question->sub_skill);

                                $priority =
                                    $usageCount > 0
                                    && $needsSubSkill;
                            @endphp

                            <div class="rqb-row {{ $priority ? 'is-priority' : '' }}">
                                <div class="rqb-question">
                                    <p class="rqb-question-text">
                                        {{ \Illuminate\Support\Str::limit(
                                            trim(strip_tags($question->question)),
                                            220
                                        ) }}
                                    </p>

                                    <div class="rqb-meta">
                                        @if ($usageCount > 0)
                                            <span class="rqb-badge rqb-used">
                                                Used in {{ $usageCount }} result(s)
                                            </span>
                                        @endif

                                        @if ($needsSubSkill)
                                            <span class="rqb-badge rqb-needs">
                                                Needs sub-skill
                                            </span>
                                        @else
                                            <span class="rqb-badge rqb-assigned">
                                                Assigned
                                            </span>
                                        @endif

                                        @if (! empty($question->error_if_wrong))
                                            @if ($question->error_labels_source === 'ai_suggested')
                                                <span class="rqb-badge" style="background:#fef3c7;color:#92400e;">
                                                    🤖 AI Suggested — Review
                                                </span>
                                            @else
                                                <span class="rqb-badge" style="background:#ede9fe;color:#5b21b6;">
                                                    ✓ Error-labeled
                                                </span>
                                            @endif
                                        @endif
                                    </div>
                                </div>

                                <div>
                                    <select
                                        name="sub_skills[{{ $question->id }}]"
                                        class="rqb-select">

                                        <option value="">
                                            Select sub-skill
                                        </option>

                                        @foreach ($subSkillOptions as $value => $label)
                                            <option
                                                value="{{ $value }}"
                                                @selected(
                                                    old(
                                                        'sub_skills.' . $question->id,
                                                        $question->sub_skill
                                                    ) === $value
                                                )>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="rqb-center">
                                    <span class="rqb-answer">
                                        {{ $question->correct_answer }}
                                    </span>
                                </div>

                                <div class="rqb-center">
                                    <span class="rqb-score">
                                        {{ $question->score }}
                                    </span>
                                </div>

                                <div class="rqb-row-actions">
                                    <a
                                        href="{{ route(
                                            'admin.reading-questions.edit',
                                            $question->id
                                        ) }}"
                                        class="rqb-mini rqb-edit">
                                        Edit
                                    </a>

                                    <button
                                        type="button"
                                        class="rqb-mini rqb-delete"
                                        data-url="{{ route(
                                            'admin.reading-questions.destroy',
                                            $question->id
                                        ) }}"
                                        data-question="{{ \Illuminate\Support\Str::limit(
                                            trim(strip_tags($question->question)),
                                            90
                                        ) }}"
                                        onclick="openReadingDeleteModal(
                                            this.dataset.url,
                                            this.dataset.question
                                        )">
                                        Delete
                                    </button>
                                </div>
                            </div>
                        @endforeach

                        <div class="rqb-save">
                            <div class="rqb-save-text">
                                Empty dropdowns are ignored. Existing assignments
                                will remain unchanged unless you choose another category.
                            </div>

                            <button
                                type="submit"
                                class="rqb-btn rqb-btn-primary">
                                Save All Sub-Skills
                            </button>
                        </div>
                    </form>
                @else
                    <div class="rqb-empty">
                        No Reading questions found for this material.
                    </div>
                @endif
            </section>
        </div>
    </div>

    <div
        id="readingDeleteModal"
        class="rqb-modal">

        <div class="rqb-modal-panel">
            <h3 class="rqb-modal-title">
                Delete Reading Question
            </h3>

            <p class="rqb-modal-copy">
                This action cannot be undone.
            </p>

            <div
                id="readingDeleteQuestion"
                class="rqb-modal-question">
                Reading question
            </div>

            <div class="rqb-modal-actions">
                <button
                    type="button"
                    class="rqb-btn rqb-btn-secondary"
                    onclick="closeReadingDeleteModal()">
                    Cancel
                </button>

                <form
                    id="readingDeleteForm"
                    method="POST">

                    @csrf
                    @method('DELETE')

                    <button
                        type="submit"
                        class="rqb-btn"
                        style="
                            background:#dc2626;
                            border:1px solid #dc2626;
                            color:#fff;
                        ">
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openReadingDeleteModal(url, question) {
            const modal =
                document.getElementById('readingDeleteModal');

            const form =
                document.getElementById('readingDeleteForm');

            const text =
                document.getElementById('readingDeleteQuestion');

            if (!modal || !form || !text) {
                return;
            }

            form.action = url;
            text.textContent = question || 'Reading question';

            modal.classList.add('is-open');
            document.body.style.overflow = 'hidden';
        }

        function closeReadingDeleteModal() {
            const modal =
                document.getElementById('readingDeleteModal');

            if (!modal) {
                return;
            }

            modal.classList.remove('is-open');
            document.body.style.overflow = '';
        }

        document.addEventListener(
            'keydown',
            function (event) {
                if (event.key === 'Escape') {
                    closeReadingDeleteModal();
                }
            }
        );

        document.addEventListener(
            'DOMContentLoaded',
            function () {
                const modal =
                    document.getElementById('readingDeleteModal');

                if (!modal) {
                    return;
                }

                modal.addEventListener(
                    'click',
                    function (event) {
                        if (event.target === modal) {
                            closeReadingDeleteModal();
                        }
                    }
                );
            }
        );
    </script>
@endsection