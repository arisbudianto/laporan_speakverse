@extends('layouts.admin')

@section('content')
    @php
        $contextTitle =
            $material->title
            ?? 'Listening Material';

        $backRoute = route(
            'admin.listening-materials.index',
            $material->lesson_id
        );

        $createRoute = route(
            'admin.listening-questions.create',
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

        $materialAudioUrl = !empty($material->audio_file)
            ? asset(
                'storage/' . $material->audio_file
            )
            : null;
    @endphp

    <style>
        .lqb-page {
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
            --cyan: #0891b2;
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

        .dark .lqb-page {
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
            --cyan: #22d3ee;
            --success: #34d399;
            --warning: #fbbf24;
            --danger: #f87171;
            --shadow: none;
        }

        .lqb-page,
        .lqb-page * {
            box-sizing: border-box;
        }

        .lqb-stack > * + * {
            margin-top: 18px;
        }

        .lqb-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 18px;
        }

        .lqb-title {
            margin: 0;
            color: var(--text);
            font-size: 30px;
            line-height: 1.15;
            font-weight: 900;
            letter-spacing: -.03em;
        }

        .lqb-subtitle {
            margin: 7px 0 0;
            color: var(--text-muted);
            font-size: 14px;
            line-height: 1.6;
        }

        .lqb-actions {
            display: flex;
            gap: 10px;
        }

        .lqb-btn {
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

        .lqb-btn-secondary {
            border: 1px solid var(--border-strong);
            background: var(--bg);
            color: var(--text-soft);
        }

        .lqb-btn-secondary:hover {
            background: var(--bg-muted);
            color: var(--text);
        }

        .lqb-btn-primary {
            border: 1px solid var(--primary);
            background: var(--primary);
            color: #fff;
        }

        .lqb-btn-primary:hover {
            background: var(--primary-hover);
            border-color: var(--primary-hover);
        }

        .lqb-alert {
            padding: 13px 15px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 750;
        }

        .lqb-alert-success {
            border: 1px solid rgba(16, 185, 129, .25);
            background: rgba(16, 185, 129, .09);
            color: var(--success);
        }

        .lqb-alert-error {
            border: 1px solid rgba(239, 68, 68, .25);
            background: rgba(239, 68, 68, .09);
            color: var(--danger);
        }

        .lqb-summary {
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

        .lqb-summary-label {
            color: var(--cyan);
            font-size: 11px;
            font-weight: 900;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .lqb-summary-title {
            margin: 5px 0 0;
            color: var(--text);
            font-size: 18px;
            font-weight: 900;
        }

        .lqb-stat {
            min-width: 115px;
            padding: 11px 14px;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: var(--bg-soft);
        }

        .lqb-stat-label {
            color: var(--text-muted);
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .lqb-stat-value {
            margin-top: 3px;
            color: var(--text);
            font-size: 20px;
            font-weight: 900;
        }

        .lqb-stat-warning .lqb-stat-value {
            color: var(--warning);
        }

        .lqb-audio {
            padding: 16px 20px;
            border: 1px solid var(--border);
            border-radius: 16px;
            background: var(--bg);
        }

        .lqb-audio-label {
            margin-bottom: 9px;
            color: var(--text-muted);
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .lqb-audio audio {
            width: 100%;
        }

        .lqb-card {
            overflow: hidden;
            border: 1px solid var(--border);
            border-radius: 18px;
            background: var(--bg);
            box-shadow: var(--shadow);
        }

        .lqb-card-head {
            padding: 18px 20px;
            border-bottom: 1px solid var(--border);
        }

        .lqb-card-title {
            margin: 0;
            color: var(--text);
            font-size: 17px;
            font-weight: 900;
        }

        .lqb-card-desc {
            margin: 5px 0 0;
            color: var(--text-muted);
            font-size: 12px;
            line-height: 1.5;
        }

        .lqb-list-head,
        .lqb-row {
            display: grid;
            grid-template-columns:
                minmax(290px, 2.1fr)
                minmax(190px, .9fr)
                95px
                80px
                150px;
            gap: 14px;
            align-items: center;
        }

        .lqb-list-head {
            padding: 12px 20px;
            background: var(--bg-soft);
            border-bottom: 1px solid var(--border);
            color: var(--text-muted);
            font-size: 10px;
            font-weight: 900;
            letter-spacing: .06em;
            text-transform: uppercase;
        }

        .lqb-row {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
        }

        .lqb-row:last-child {
            border-bottom: 0;
        }

        .lqb-row.is-priority {
            background: rgba(245, 158, 11, .045);
        }

        .lqb-question-text {
            margin: 0;
            color: var(--text-soft);
            font-size: 13px;
            line-height: 1.6;
            font-weight: 750;
            overflow-wrap: anywhere;
        }

        .lqb-instruction {
            margin: 5px 0 0;
            color: var(--text-muted);
            font-size: 11px;
            line-height: 1.5;
        }

        .lqb-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 8px;
        }

        .lqb-badge {
            display: inline-flex;
            align-items: center;
            padding: 5px 8px;
            border-radius: 999px;
            font-size: 10px;
            line-height: 1;
            font-weight: 850;
        }

        .lqb-used {
            background: rgba(37, 99, 235, .10);
            color: var(--primary);
        }

        .lqb-needs {
            background: rgba(245, 158, 11, .13);
            color: var(--warning);
        }

        .lqb-assigned {
            background: rgba(16, 185, 129, .10);
            color: var(--success);
        }

        .lqb-select {
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

        .lqb-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, .12);
        }

        .lqb-center {
            text-align: center;
        }

        .lqb-answer {
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

        .lqb-score {
            color: var(--text-soft);
            font-size: 13px;
            font-weight: 900;
        }

        .lqb-row-actions {
            display: flex;
            justify-content: flex-end;
            gap: 7px;
        }

        .lqb-mini {
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

        .lqb-edit {
            border: 1px solid rgba(59, 130, 246, .23);
            background: rgba(59, 130, 246, .09);
            color: var(--primary);
        }

        .lqb-delete {
            border: 1px solid rgba(239, 68, 68, .23);
            background: rgba(239, 68, 68, .08);
            color: var(--danger);
        }

        .lqb-save {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            padding: 15px 20px;
            border-top: 1px solid var(--border);
            background: var(--bg-soft);
        }

        .lqb-save-text {
            color: var(--text-muted);
            font-size: 12px;
            line-height: 1.5;
        }

        .lqb-empty {
            padding: 50px 20px;
            text-align: center;
            color: var(--text-muted);
        }

        .lqb-modal {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 18px;
            background: rgba(2, 6, 23, .72);
        }

        .lqb-modal.is-open {
            display: flex;
        }

        .lqb-modal-panel {
            width: 100%;
            max-width: 430px;
            padding: 22px;
            border: 1px solid var(--border);
            border-radius: 17px;
            background: var(--bg);
        }

        .lqb-modal-title {
            margin: 0;
            color: var(--text);
            font-size: 18px;
            font-weight: 900;
        }

        .lqb-modal-copy {
            margin: 8px 0 0;
            color: var(--text-muted);
            font-size: 13px;
            line-height: 1.6;
        }

        .lqb-modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            margin-top: 18px;
        }

        @media (max-width: 1050px) {
            .lqb-summary {
                grid-template-columns: repeat(3, 1fr);
            }

            .lqb-summary-main {
                grid-column: 1 / -1;
            }

            .lqb-list-head {
                display: none;
            }

            .lqb-row {
                grid-template-columns: minmax(0, 1fr) minmax(180px, .55fr);
            }

            .lqb-row .lqb-center,
            .lqb-row-actions {
                justify-self: start;
            }

            .lqb-row-actions {
                justify-content: flex-start;
            }
        }

        @media (max-width: 700px) {
            .lqb-header {
                align-items: stretch;
                flex-direction: column;
            }

            .lqb-actions {
                display: grid;
                grid-template-columns: 1fr;
            }

            .lqb-summary {
                grid-template-columns: 1fr;
            }

            .lqb-summary-main {
                grid-column: auto;
            }

            .lqb-row {
                grid-template-columns: 1fr;
            }

            .lqb-save {
                align-items: stretch;
                flex-direction: column;
            }

            .lqb-save .lqb-btn {
                width: 100%;
            }
        }
    </style>

    <div class="lqb-page">
        <div class="lqb-stack">

            <header class="lqb-header">
                <div>
                    <h1 class="lqb-title">
                        Listening Questions
                    </h1>

                    <p class="lqb-subtitle">
                        Assign Listening sub-skills in bulk so existing and future
                        assessment results can be analysed in more detail.
                    </p>
                </div>

                <div class="lqb-actions">
                    <a
                        href="{{ $backRoute }}"
                        class="lqb-btn lqb-btn-secondary">
                        ← Back
                    </a>

                    <a
                        href="{{ $createRoute }}"
                        class="lqb-btn lqb-btn-primary">
                        + Add Question
                    </a>
                </div>
            </header>

            @if (session('success'))
                <div class="lqb-alert lqb-alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="lqb-alert lqb-alert-error">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="lqb-alert lqb-alert-error">
                    <strong>Please check the submitted data.</strong>

                    <ul style="margin:8px 0 0;padding-left:18px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <section class="lqb-summary">
                <div class="lqb-summary-main">
                    <div class="lqb-summary-label">
                        Listening Material
                    </div>

                    <h2 class="lqb-summary-title">
                        {{ $contextTitle }}
                    </h2>
                </div>

                <div class="lqb-stat">
                    <div class="lqb-stat-label">
                        Questions
                    </div>

                    <div class="lqb-stat-value">
                        {{ $questions->count() }}
                    </div>
                </div>

                <div class="lqb-stat lqb-stat-warning">
                    <div class="lqb-stat-label">
                        Not Assigned
                    </div>

                    <div class="lqb-stat-value">
                        {{ $unassignedCount }}
                    </div>
                </div>

                <div class="lqb-stat lqb-stat-warning">
                    <div class="lqb-stat-label">
                        Used + Missing
                    </div>

                    <div class="lqb-stat-value">
                        {{ $usedUnassignedCount }}
                    </div>
                </div>
            </section>

            @if ($materialAudioUrl)
                <section class="lqb-audio">
                    <div class="lqb-audio-label">
                        Material Audio
                    </div>

                    <audio
                        controls
                        preload="metadata"
                        src="{{ $materialAudioUrl }}">
                        Your browser does not support audio playback.
                    </audio>
                </section>
            @endif

            <section class="lqb-card">
                <div class="lqb-card-head">
                    <h3 class="lqb-card-title">
                        Bulk Sub-Skill Assignment
                    </h3>

                    <p class="lqb-card-desc">
                        Questions marked “Used in results” should be prioritised
                        because they already affect existing Progress data.
                    </p>
                </div>

                @if ($questions->isNotEmpty())
                    <form
                        action="{{ route('admin.listening-questions.bulk-sub-skills', $material->id) }}"
                        method="POST">

                        @csrf
                        @method('PUT')

                        <div class="lqb-list-head">
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

                            <div class="lqb-row {{ $priority ? 'is-priority' : '' }}">
                                <div>
                                    <p class="lqb-question-text">
                                        {{ \Illuminate\Support\Str::limit(
                                            trim(strip_tags($question->question)),
                                            220
                                        ) }}
                                    </p>

                                    @if (!blank($question->instruction))
                                        <p class="lqb-instruction">
                                            {{ \Illuminate\Support\Str::limit(
                                                trim(strip_tags($question->instruction)),
                                                130
                                            ) }}
                                        </p>
                                    @endif

                                    <div class="lqb-meta">
                                        @if ($usageCount > 0)
                                            <span class="lqb-badge lqb-used">
                                                Used in {{ $usageCount }} result(s)
                                            </span>
                                        @endif

                                        @if ($needsSubSkill)
                                            <span class="lqb-badge lqb-needs">
                                                Needs sub-skill
                                            </span>
                                        @else
                                            <span class="lqb-badge lqb-assigned">
                                                Assigned
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div>
                                    <select
                                        name="sub_skills[{{ $question->id }}]"
                                        class="lqb-select">

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

                                <div class="lqb-center">
                                    <span class="lqb-answer">
                                        {{ $question->correct_answer }}
                                    </span>
                                </div>

                                <div class="lqb-center">
                                    <span class="lqb-score">
                                        {{ $question->score }}
                                    </span>
                                </div>

                                <div class="lqb-row-actions">
                                    <a
                                        href="{{ route(
                                            'admin.listening-questions.edit',
                                            $question->id
                                        ) }}"
                                        class="lqb-mini lqb-edit">
                                        Edit
                                    </a>

                                    <button
                                        type="button"
                                        class="lqb-mini lqb-delete"
                                        data-url="{{ route(
                                            'admin.listening-questions.destroy',
                                            $question->id
                                        ) }}"
                                        onclick="openListeningDeleteModal(
                                            this.dataset.url
                                        )">
                                        Delete
                                    </button>
                                </div>
                            </div>
                        @endforeach

                        <div class="lqb-save">
                            <div class="lqb-save-text">
                                Empty dropdowns are ignored. Existing assignments
                                remain unchanged unless another category is selected.
                            </div>

                            <button
                                type="submit"
                                class="lqb-btn lqb-btn-primary">
                                Save All Sub-Skills
                            </button>
                        </div>
                    </form>
                @else
                    <div class="lqb-empty">
                        No Listening questions found for this material.
                    </div>
                @endif
            </section>
        </div>
    </div>

    <div
        id="listeningDeleteModal"
        class="lqb-modal">

        <div class="lqb-modal-panel">
            <h3 class="lqb-modal-title">
                Delete Listening Question
            </h3>

            <p class="lqb-modal-copy">
                Are you sure? This action cannot be undone.
            </p>

            <div class="lqb-modal-actions">
                <button
                    type="button"
                    class="lqb-btn lqb-btn-secondary"
                    onclick="closeListeningDeleteModal()">
                    Cancel
                </button>

                <form
                    id="listeningDeleteForm"
                    method="POST">

                    @csrf
                    @method('DELETE')

                    <button
                        type="submit"
                        class="lqb-btn"
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
        function openListeningDeleteModal(url) {
            const modal =
                document.getElementById('listeningDeleteModal');

            const form =
                document.getElementById('listeningDeleteForm');

            if (!modal || !form) {
                return;
            }

            form.action = url;

            modal.classList.add('is-open');
            document.body.style.overflow = 'hidden';
        }

        function closeListeningDeleteModal() {
            const modal =
                document.getElementById('listeningDeleteModal');

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
                    closeListeningDeleteModal();
                }
            }
        );

        document.addEventListener(
            'DOMContentLoaded',
            function () {
                const modal =
                    document.getElementById('listeningDeleteModal');

                if (!modal) {
                    return;
                }

                modal.addEventListener(
                    'click',
                    function (event) {
                        if (event.target === modal) {
                            closeListeningDeleteModal();
                        }
                    }
                );
            }
        );
    </script>
@endsection