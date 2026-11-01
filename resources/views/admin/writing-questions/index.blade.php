@extends('layouts.admin')

@section('content')
    @php
        $isLessonMode = isset($lesson);

        $lessonTitle = $isLessonMode
            ? ($lesson->title ?? ucfirst($lesson->skill_type ?? 'Writing'))
            : null;

        $unitTitle = $isLessonMode
            ? optional($lesson->unit)->title
            : null;

        $unitType = $isLessonMode
            ? optional($lesson->unit)->type
            : null;

        $isAssessment = $isLessonMode
            && in_array(
                $unitType,
                [
                    'pretest',
                    'posttest',
                ],
                true
            );

        $assessmentLabel = $isAssessment
            ? strtoupper($unitType)
            : null;

        $contextTitle = $isLessonMode
            ? (
                $unitTitle
                    ? $unitTitle . ' — ' . $lessonTitle
                    : $lessonTitle
            )
            : ($material->title ?? 'Writing Material');

        $createRoute = $isLessonMode
            ? route(
                'admin.writing-lesson-questions.create',
                $lesson->id
            )
            : route(
                'admin.writing-questions.create',
                $material->id
            );

        $backRoute = $isLessonMode
            ? route('admin.learning')
            : route(
                'admin.writing-materials.index',
                $material->lesson_id
            );

        $questionCount = $questions->count();
    @endphp

    <style>
        /* ============================================================
           WRITING QUESTIONS PAGE
           Scoped styles prevent global admin styles from breaking
           light/dark mode consistency.
        ============================================================ */
        .wq-page {
            --wq-card: #ffffff;
            --wq-card-soft: #f8fafc;
            --wq-card-muted: #f1f5f9;
            --wq-border: #e2e8f0;
            --wq-border-soft: #edf2f7;
            --wq-text: #0f172a;
            --wq-text-soft: #475569;
            --wq-text-muted: #64748b;

            width: 100%;
            max-width: 1500px;
            margin: 0 auto;
            padding-top: 18px;
            padding-bottom: 32px;
            color: var(--wq-text);
        }

        .dark .wq-page {
            --wq-card: #111827;
            --wq-card-soft: #172033;
            --wq-card-muted: #1e293b;
            --wq-border: #263449;
            --wq-border-soft: #1e2a3b;
            --wq-text: #f1f5f9;
            --wq-text-soft: #cbd5e1;
            --wq-text-muted: #94a3b8;
        }

        .wq-stack > * + * {
            margin-top: 20px;
        }

        /* ============================================================
           PAGE HEADER
        ============================================================ */
        .wq-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 24px;
        }

        .wq-header-copy {
            min-width: 0;
        }

        .wq-context-label {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            margin-bottom: 8px;
            color: #7c3aed !important;
            font-size: 11px;
            line-height: 1;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .dark .wq-context-label {
            color: #c4b5fd !important;
        }

        .wq-context-dot {
            width: 6px;
            height: 6px;
            border-radius: 999px;
            background: currentColor;
        }

        .wq-title {
            margin: 0;
            color: var(--wq-text) !important;
            font-size: 30px;
            line-height: 1.15;
            font-weight: 800;
            letter-spacing: -0.025em;
        }

        .wq-subtitle {
            margin: 8px 0 0;
            max-width: 680px;
            color: var(--wq-text-muted) !important;
            font-size: 14px;
            line-height: 1.65;
        }

        .wq-header-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 0 0 auto;
        }

        /* ============================================================
           BUTTONS
        ============================================================ */
        .wq-btn {
            display: inline-flex !important;
            min-height: 42px;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 15px;
            border-radius: 10px;
            font-size: 14px;
            line-height: 1;
            font-weight: 700;
            text-decoration: none !important;
            transition:
                background 150ms ease,
                border-color 150ms ease,
                color 150ms ease,
                transform 150ms ease,
                box-shadow 150ms ease;
            cursor: pointer;
        }

        .wq-btn:active {
            transform: scale(0.985);
        }

        .wq-btn:focus-visible,
        .wq-action:focus-visible,
        .wq-modal-cancel:focus-visible,
        .wq-modal-delete:focus-visible {
            outline: none;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.18) !important;
        }

        .wq-btn-back {
            border: 1px solid #cbd5e1 !important;
            background: #ffffff !important;
            color: #475569 !important;
        }

        .wq-btn-back:hover {
            border-color: #94a3b8 !important;
            background: #f8fafc !important;
            color: #0f172a !important;
        }

        .dark .wq-btn-back {
            border-color: #334155 !important;
            background: #1e293b !important;
            color: #e2e8f0 !important;
        }

        .dark .wq-btn-back:hover {
            border-color: #475569 !important;
            background: #273449 !important;
            color: #ffffff !important;
        }

        .wq-btn-primary {
            border: 1px solid #2563eb !important;
            background: #2563eb !important;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.14) !important;
        }

        .wq-btn-primary:hover {
            border-color: #1d4ed8 !important;
            background: #1d4ed8 !important;
            color: #ffffff !important;
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.18) !important;
        }

        .dark .wq-btn-primary {
            border-color: #3b82f6 !important;
            background: #3b82f6 !important;
            color: #ffffff !important;
        }

        .dark .wq-btn-primary:hover {
            border-color: #2563eb !important;
            background: #2563eb !important;
        }

        /* ============================================================
           FLASH MESSAGES
        ============================================================ */
        .wq-alert {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 11px 14px;
            border-radius: 12px;
            font-size: 14px;
            line-height: 1.5;
            font-weight: 650;
        }

        .wq-alert-icon {
            display: flex;
            width: 30px;
            height: 30px;
            flex: 0 0 30px;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            font-size: 14px;
            font-weight: 900;
        }

        .wq-alert-success {
            border: 1px solid #bbf7d0 !important;
            background: #f0fdf4 !important;
            color: #047857 !important;
        }

        .wq-alert-success .wq-alert-icon {
            background: #d1fae5 !important;
            color: #047857 !important;
        }

        .dark .wq-alert-success {
            border-color: rgba(16, 185, 129, 0.18) !important;
            background: rgba(16, 185, 129, 0.08) !important;
            color: #6ee7b7 !important;
        }

        .dark .wq-alert-success .wq-alert-icon {
            background: rgba(16, 185, 129, 0.14) !important;
            color: #6ee7b7 !important;
        }

        .wq-alert-error {
            border: 1px solid #fecaca !important;
            background: #fef2f2 !important;
            color: #b91c1c !important;
        }

        .wq-alert-error .wq-alert-icon {
            background: #fee2e2 !important;
            color: #dc2626 !important;
        }

        .dark .wq-alert-error {
            border-color: rgba(239, 68, 68, 0.18) !important;
            background: rgba(239, 68, 68, 0.08) !important;
            color: #fca5a5 !important;
        }

        .dark .wq-alert-error .wq-alert-icon {
            background: rgba(239, 68, 68, 0.14) !important;
            color: #fca5a5 !important;
        }

        /* ============================================================
           CARDS
        ============================================================ */
        .wq-card {
            border: 1px solid var(--wq-border) !important;
            border-radius: 18px;
            background: var(--wq-card) !important;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.035);
        }

        .dark .wq-card {
            box-shadow: none !important;
        }

        /* ============================================================
           CONTEXT SUMMARY
        ============================================================ */
        .wq-summary {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            padding: 20px 22px;
        }

        .wq-summary-main {
            display: flex;
            min-width: 0;
            align-items: center;
            gap: 14px;
        }

        .wq-summary-icon {
            display: flex;
            width: 44px;
            height: 44px;
            flex: 0 0 44px;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            border: 1px solid #ede9fe !important;
            background: #f5f3ff !important;
            color: #7c3aed !important;
            font-size: 20px;
        }

        .dark .wq-summary-icon {
            border-color: rgba(139, 92, 246, 0.16) !important;
            background: rgba(139, 92, 246, 0.09) !important;
            color: #c4b5fd !important;
        }

        .wq-summary-copy {
            min-width: 0;
        }

        .wq-summary-label {
            margin: 0;
            color: var(--wq-text-muted) !important;
            font-size: 12px;
            font-weight: 700;
        }

        .wq-summary-title {
            margin: 4px 0 0;
            color: var(--wq-text) !important;
            font-size: 18px;
            line-height: 1.4;
            font-weight: 750;
            overflow-wrap: anywhere;
        }

        .wq-summary-description {
            margin: 5px 0 0;
            max-width: 700px;
            color: var(--wq-text-muted) !important;
            font-size: 13px;
            line-height: 1.55;
        }

        .wq-summary-badges {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 7px;
        }

        .wq-badge {
            display: inline-flex;
            min-height: 28px;
            align-items: center;
            justify-content: center;
            padding: 5px 11px;
            border-radius: 999px;
            font-size: 11px;
            line-height: 1;
            font-weight: 750;
            white-space: nowrap;
        }

        .wq-badge-assessment {
            border: 1px solid #ddd6fe !important;
            background: #f5f3ff !important;
            color: #7c3aed !important;
        }

        .dark .wq-badge-assessment {
            border-color: rgba(139, 92, 246, 0.18) !important;
            background: rgba(139, 92, 246, 0.09) !important;
            color: #c4b5fd !important;
        }

        .wq-badge-skill {
            border: 1px solid #bfdbfe !important;
            background: #eff6ff !important;
            color: #1d4ed8 !important;
        }

        .dark .wq-badge-skill {
            border-color: rgba(59, 130, 246, 0.18) !important;
            background: rgba(59, 130, 246, 0.09) !important;
            color: #93c5fd !important;
        }

        .wq-badge-count {
            border: 1px solid #e2e8f0 !important;
            background: #f8fafc !important;
            color: #475569 !important;
        }

        .dark .wq-badge-count {
            border-color: #334155 !important;
            background: #1e293b !important;
            color: #cbd5e1 !important;
        }

        /* ============================================================
           QUESTION LIST
        ============================================================ */
        .wq-list {
            overflow: hidden;
        }

        .wq-list-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 16px;
            padding: 18px 22px;
            border-bottom: 1px solid var(--wq-border) !important;
            background: var(--wq-card) !important;
        }

        .wq-list-title {
            margin: 0;
            color: var(--wq-text) !important;
            font-size: 18px;
            font-weight: 750;
        }

        .wq-list-description {
            margin: 5px 0 0;
            color: var(--wq-text-muted) !important;
            font-size: 13px;
            line-height: 1.55;
        }

        /* ============================================================
           DESKTOP TABLE
        ============================================================ */
        .wq-table-wrap {
            display: block;
            overflow-x: auto;
        }

        .wq-table {
            width: 100%;
            min-width: 880px;
            border-collapse: collapse !important;
            border-spacing: 0 !important;
        }

        .wq-table thead,
        .wq-table thead tr,
        .wq-table thead th {
            background: #f8fafc !important;
        }

        .dark .wq-table thead,
        .dark .wq-table thead tr,
        .dark .wq-table thead th {
            background: #172033 !important;
        }

        .wq-table th {
            padding: 12px 20px;
            border: 0 !important;
            border-bottom: 1px solid var(--wq-border) !important;
            color: #64748b !important;
            font-size: 11px;
            line-height: 1;
            font-weight: 800;
            letter-spacing: 0.055em;
            text-align: left;
            text-transform: uppercase;
        }

        .dark .wq-table th {
            color: #94a3b8 !important;
        }

        .wq-table th:last-child {
            text-align: right;
        }

        .wq-table tbody,
        .wq-table tbody tr,
        .wq-table tbody td {
            background: var(--wq-card) !important;
        }

        .wq-table tbody tr {
            transition: background 150ms ease;
        }

        .wq-table tbody tr:hover,
        .wq-table tbody tr:hover td {
            background: var(--wq-card-soft) !important;
        }

        .wq-table td {
            padding: 17px 20px;
            border: 0 !important;
            border-bottom: 1px solid var(--wq-border-soft) !important;
            vertical-align: middle;
        }

        .wq-table tbody tr:last-child td {
            border-bottom: 0 !important;
        }

        .wq-image-wrap {
            display: flex;
            width: 104px;
            height: 78px;
            overflow: hidden;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--wq-border) !important;
            border-radius: 11px;
            background: var(--wq-card-soft) !important;
        }

        .wq-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .wq-no-image {
            color: var(--wq-text-muted) !important;
            font-size: 11px;
            font-weight: 700;
        }

        .wq-question-text {
            margin: 0;
            max-width: 760px;
            color: var(--wq-text-soft) !important;
            font-size: 14px;
            line-height: 1.65;
            font-weight: 500;
            overflow-wrap: anywhere;
        }

        .wq-row-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 7px;
        }

        .wq-action {
            display: inline-flex !important;
            min-height: 36px;
            align-items: center;
            justify-content: center;
            padding: 8px 12px;
            border-radius: 9px;
            font-size: 12px;
            line-height: 1;
            font-weight: 700;
            text-decoration: none !important;
            cursor: pointer;
            transition:
                background 150ms ease,
                border-color 150ms ease,
                color 150ms ease;
        }

        .wq-action-edit {
            border: 1px solid #bfdbfe !important;
            background: #eff6ff !important;
            color: #1d4ed8 !important;
        }

        .wq-action-edit:hover {
            background: #dbeafe !important;
        }

        .dark .wq-action-edit {
            border-color: rgba(59, 130, 246, 0.18) !important;
            background: rgba(59, 130, 246, 0.09) !important;
            color: #93c5fd !important;
        }

        .dark .wq-action-edit:hover {
            background: rgba(59, 130, 246, 0.14) !important;
        }

        .wq-action-delete {
            border: 1px solid #fecaca !important;
            background: #fef2f2 !important;
            color: #b91c1c !important;
        }

        .wq-action-delete:hover {
            background: #fee2e2 !important;
        }

        .dark .wq-action-delete {
            border-color: rgba(239, 68, 68, 0.18) !important;
            background: rgba(239, 68, 68, 0.08) !important;
            color: #fca5a5 !important;
        }

        .dark .wq-action-delete:hover {
            background: rgba(239, 68, 68, 0.13) !important;
        }

        /* ============================================================
           MOBILE / TABLET CARDS
        ============================================================ */
        .wq-mobile-list {
            display: none;
        }

        .wq-mobile-item {
            padding: 17px;
            border-bottom: 1px solid var(--wq-border-soft) !important;
            background: var(--wq-card) !important;
        }

        .wq-mobile-item:last-child {
            border-bottom: 0 !important;
        }

        .wq-mobile-layout {
            display: flex;
            gap: 14px;
        }

        .wq-mobile-image {
            width: 112px;
            flex: 0 0 112px;
        }

        .wq-mobile-content {
            min-width: 0;
            flex: 1;
        }

        .wq-mobile-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
            margin-top: 13px;
        }

        /* ============================================================
           EMPTY STATE
        ============================================================ */
        .wq-empty {
            padding: 54px 20px;
            text-align: center;
            background: var(--wq-card) !important;
        }

        .wq-empty-icon {
            display: flex;
            width: 52px;
            height: 52px;
            margin: 0 auto;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            border: 1px solid #ede9fe !important;
            background: #f5f3ff !important;
            color: #7c3aed !important;
            font-size: 22px;
        }

        .dark .wq-empty-icon {
            border-color: rgba(139, 92, 246, 0.16) !important;
            background: rgba(139, 92, 246, 0.09) !important;
            color: #c4b5fd !important;
        }

        .wq-empty-title {
            margin: 16px 0 0;
            color: var(--wq-text) !important;
            font-size: 18px;
            font-weight: 750;
        }

        .wq-empty-text {
            margin: 7px auto 0;
            max-width: 430px;
            color: var(--wq-text-muted) !important;
            font-size: 14px;
            line-height: 1.6;
        }

        .wq-empty .wq-btn-primary {
            margin-top: 20px;
        }

        /* ============================================================
           DELETE MODAL
        ============================================================ */
        .wq-modal {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 16px;
            background: rgba(2, 6, 23, 0.72) !important;
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
        }

        .wq-modal.is-open {
            display: flex;
        }

        .wq-modal-panel {
            width: 100%;
            max-width: 430px;
            overflow: hidden;
            border: 1px solid #e2e8f0 !important;
            border-radius: 18px;
            background: #ffffff !important;
            box-shadow: 0 24px 70px rgba(2, 6, 23, 0.26);
            opacity: 0;
            transform: translateY(8px) scale(0.98);
            transition:
                opacity 170ms ease,
                transform 170ms ease;
        }

        .wq-modal-panel.is-visible {
            opacity: 1;
            transform: translateY(0) scale(1);
        }

        .dark .wq-modal-panel {
            border-color: #253247 !important;
            background: #111827 !important;
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.48);
        }

        .wq-modal-header {
            padding: 19px 20px;
            border-bottom: 1px solid #e2e8f0 !important;
            background: #ffffff !important;
        }

        .dark .wq-modal-header {
            border-bottom-color: #253247 !important;
            background: #111827 !important;
        }

        .wq-modal-title {
            margin: 0;
            color: #0f172a !important;
            font-size: 18px;
            font-weight: 800;
        }

        .dark .wq-modal-title {
            color: #f1f5f9 !important;
        }

        .wq-modal-subtitle {
            margin: 5px 0 0;
            color: #64748b !important;
            font-size: 13px;
        }

        .dark .wq-modal-subtitle {
            color: #94a3b8 !important;
        }

        .wq-modal-body {
            padding: 20px;
            background: #ffffff !important;
        }

        .dark .wq-modal-body {
            background: #111827 !important;
        }

        .wq-modal-copy {
            margin: 0;
            color: #475569 !important;
            font-size: 14px;
            line-height: 1.6;
        }

        .dark .wq-modal-copy {
            color: #cbd5e1 !important;
        }

        .wq-modal-question {
            margin: 12px 0 0;
            padding: 12px 14px;
            border: 1px solid #e2e8f0 !important;
            border-radius: 12px;
            background: #f8fafc !important;
            color: #334155 !important;
            font-size: 13px;
            line-height: 1.6;
            font-weight: 650;
            overflow-wrap: anywhere;
        }

        .dark .wq-modal-question {
            border-color: #253247 !important;
            background: #182235 !important;
            color: #e2e8f0 !important;
        }

        .wq-modal-warning {
            margin-top: 12px;
            padding: 11px 13px;
            border: 1px solid #fecaca !important;
            border-radius: 11px;
            background: #fef2f2 !important;
            color: #b91c1c !important;
            font-size: 12px;
            line-height: 1.55;
            font-weight: 650;
        }

        .dark .wq-modal-warning {
            border-color: rgba(239, 68, 68, 0.16) !important;
            background: rgba(239, 68, 68, 0.07) !important;
            color: #fca5a5 !important;
        }

        .wq-modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 9px;
            padding: 14px 20px;
            border-top: 1px solid #e2e8f0 !important;
            background: #f8fafc !important;
        }

        .dark .wq-modal-footer {
            border-top-color: #253247 !important;
            background: #0f172a !important;
        }

        .wq-modal-cancel,
        .wq-modal-delete {
            display: inline-flex !important;
            min-height: 40px;
            align-items: center;
            justify-content: center;
            padding: 9px 15px;
            border-radius: 9px;
            font-size: 13px;
            line-height: 1;
            font-weight: 750;
            cursor: pointer;
            transition:
                background 150ms ease,
                border-color 150ms ease,
                color 150ms ease;
        }

        .wq-modal-cancel {
            border: 1px solid #cbd5e1 !important;
            background: #ffffff !important;
            color: #475569 !important;
        }

        .wq-modal-cancel:hover {
            background: #f1f5f9 !important;
        }

        .dark .wq-modal-cancel {
            border-color: #334155 !important;
            background: #1e293b !important;
            color: #e2e8f0 !important;
        }

        .dark .wq-modal-cancel:hover {
            background: #273449 !important;
        }

        .wq-modal-delete {
            border: 1px solid #dc2626 !important;
            background: #dc2626 !important;
            color: #ffffff !important;
        }

        .wq-modal-delete:hover {
            border-color: #b91c1c !important;
            background: #b91c1c !important;
        }

        /* ============================================================
           RESPONSIVE
        ============================================================ */
        @media (max-width: 1023px) {
            .wq-table-wrap {
                display: none;
            }

            .wq-mobile-list {
                display: block;
            }
        }

        @media (max-width: 767px) {
            .wq-page {
                padding-top: 12px;
                padding-bottom: 24px;
            }

            .wq-stack > * + * {
                margin-top: 16px;
            }

            .wq-header {
                align-items: stretch;
                flex-direction: column;
            }

            .wq-header-actions {
                display: grid;
                grid-template-columns: 1fr;
            }

            .wq-title {
                font-size: 26px;
            }

            .wq-summary {
                align-items: flex-start;
                flex-direction: column;
                padding: 17px;
            }

            .wq-summary-main {
                align-items: flex-start;
            }

            .wq-summary-badges {
                justify-content: flex-start;
            }

            .wq-list-header {
                align-items: flex-start;
                flex-direction: column;
                padding: 17px;
            }

            .wq-mobile-layout {
                flex-direction: column;
            }

            .wq-mobile-image {
                width: 100%;
                flex-basis: auto;
            }

            .wq-mobile-image .wq-image-wrap {
                width: 100%;
                height: 170px;
            }

            .wq-mobile-actions {
                display: grid;
                grid-template-columns: 1fr 1fr;
            }

            .wq-mobile-actions .wq-action {
                width: 100%;
                min-height: 40px;
            }

            .wq-modal {
                align-items: flex-end;
                padding: 10px;
            }

            .wq-modal-panel {
                max-width: none;
            }

            .wq-modal-footer {
                flex-direction: column-reverse;
            }

            .wq-modal-footer form,
            .wq-modal-cancel,
            .wq-modal-delete {
                width: 100%;
            }
        }
    </style>

    <div class="wq-page">
        <div class="wq-stack">

            {{-- ========================================================
                PAGE HEADER
            ======================================================== --}}
            <header class="wq-header">
                <div class="wq-header-copy">

                    <div class="wq-context-label">
                        <span class="wq-context-dot"></span>

                        @if ($isAssessment)
                            {{ $assessmentLabel }} Writing Assessment
                        @elseif ($isLessonMode)
                            Writing Assessment
                        @else
                            Writing Material
                        @endif
                    </div>

                    <h1 class="wq-title">
                        Writing Questions
                    </h1>

                    <p class="wq-subtitle">
                        @if ($isLessonMode)
                            Manage writing questions for this assessment.
                        @else
                            Manage questions for this writing material.
                        @endif
                    </p>
                </div>

                <div class="wq-header-actions">
                    <a
                        href="{{ $backRoute }}"
                        class="wq-btn wq-btn-back">

                        <span aria-hidden="true">←</span>

                        {{ $isLessonMode
                            ? 'Back to Learning'
                            : 'Back to Writing Materials' }}
                    </a>

                    <a
                        href="{{ $createRoute }}"
                        class="wq-btn wq-btn-primary">

                        <span aria-hidden="true">+</span>
                        Add Question
                    </a>
                </div>
            </header>

            {{-- ========================================================
                SUCCESS MESSAGE
            ======================================================== --}}
            @if (session('success'))
                <div class="wq-alert wq-alert-success">
                    <div class="wq-alert-icon" aria-hidden="true">
                        ✓
                    </div>

                    <div>
                        {{ session('success') }}
                    </div>
                </div>
            @endif

            {{-- ========================================================
                ERROR MESSAGE
            ======================================================== --}}
            @if (session('error'))
                <div class="wq-alert wq-alert-error">
                    <div class="wq-alert-icon" aria-hidden="true">
                        !
                    </div>

                    <div>
                        {{ session('error') }}
                    </div>
                </div>
            @endif

            {{-- ========================================================
                CONTEXT SUMMARY
            ======================================================== --}}
            <section class="wq-card wq-summary">

                <div class="wq-summary-main">
                    <div
                        class="wq-summary-icon"
                        aria-hidden="true">
                        ✍
                    </div>

                    <div class="wq-summary-copy">
                        <p class="wq-summary-label">
                            {{ $isLessonMode
                                ? 'Current Assessment'
                                : 'Current Material' }}
                        </p>

                        <h2 class="wq-summary-title">
                            {{ $contextTitle }}
                        </h2>

                        @if ($isLessonMode && $lesson->description)
                            <p class="wq-summary-description">
                                {{ $lesson->description }}
                            </p>
                        @endif
                    </div>
                </div>

                <div class="wq-summary-badges">
                    @if ($isAssessment)
                        <span class="wq-badge wq-badge-assessment">
                            {{ $assessmentLabel }}
                        </span>
                    @endif

                    <span class="wq-badge wq-badge-skill">
                        Writing
                    </span>

                    <span class="wq-badge wq-badge-count">
                        {{ $questionCount }}

                        {{ \Illuminate\Support\Str::plural(
                            'Question',
                            $questionCount
                        ) }}
                    </span>
                </div>
            </section>

            {{-- ========================================================
                QUESTION LIST
            ======================================================== --}}
            <section class="wq-card wq-list">

                <div class="wq-list-header">
                    <div>
                        <h3 class="wq-list-title">
                            Question List
                        </h3>

                        <p class="wq-list-description">
                            Review, edit, or delete questions for this writing activity.
                        </p>
                    </div>

                    @if ($questionCount)
                        <span class="wq-badge wq-badge-count">
                            {{ $questionCount }}

                            {{ \Illuminate\Support\Str::plural(
                                'Question',
                                $questionCount
                            ) }}
                        </span>
                    @endif
                </div>

                @if ($questions->count())

                    {{-- ====================================================
                        DESKTOP TABLE
                    ==================================================== --}}
                    <div class="wq-table-wrap">
                        <table class="wq-table">
                            <thead>
                                <tr>
                                    <th style="width: 18%;">
                                        Image
                                    </th>

                                    <th style="width: 57%;">
                                        Question
                                    </th>

                                    <th style="width: 25%;">
                                        Actions
                                    </th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($questions as $question)
                                    <tr>

                                        {{-- IMAGE --}}
                                        <td>
                                            @if (!empty($question->image))
                                                <div class="wq-image-wrap">
                                                    <img
                                                        src="{{ asset(
                                                            'storage/' .
                                                            $question->image
                                                        ) }}"
                                                        alt="Writing question image"
                                                        onerror="
                                                            this.classList.add('hidden');
                                                            this.nextElementSibling.classList.remove('hidden');
                                                            this.nextElementSibling.classList.add('flex');
                                                        "
                                                        class="wq-image">

                                                    <div
                                                        class="wq-no-image hidden
                                                               h-full w-full
                                                               items-center justify-center">

                                                        No Image
                                                    </div>
                                                </div>
                                            @else
                                                <div class="wq-image-wrap">
                                                    <span class="wq-no-image">
                                                        No Image
                                                    </span>
                                                </div>
                                            @endif
                                        </td>

                                        {{-- QUESTION --}}
                                        <td>
                                            <p class="wq-question-text">
                                                {{ \Illuminate\Support\Str::limit(
                                                    trim(
                                                        strip_tags(
                                                            $question->question
                                                        )
                                                    ),
                                                    180
                                                ) }}
                                            </p>
                                        </td>

                                        {{-- ACTIONS --}}
                                        <td>
                                            <div class="wq-row-actions">

                                                <a
                                                    href="{{ route(
                                                        'admin.writing-questions.edit',
                                                        $question->id
                                                    ) }}"
                                                    class="wq-action wq-action-edit">

                                                    Edit
                                                </a>

                                                <button
                                                    type="button"
                                                    data-url="{{ route(
                                                        'admin.writing-questions.destroy',
                                                        $question->id
                                                    ) }}"
                                                    data-question="{{ \Illuminate\Support\Str::limit(
                                                        trim(
                                                            strip_tags(
                                                                $question->question
                                                            )
                                                        ),
                                                        80
                                                    ) }}"
                                                    onclick="openDeleteModal(
                                                        this.dataset.url,
                                                        this.dataset.question
                                                    )"
                                                    class="wq-action wq-action-delete">

                                                    Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- ====================================================
                        MOBILE / TABLET CARDS
                    ==================================================== --}}
                    <div class="wq-mobile-list">

                        @foreach ($questions as $question)
                            <article class="wq-mobile-item">

                                <div class="wq-mobile-layout">

                                    {{-- IMAGE --}}
                                    <div class="wq-mobile-image">
                                        @if (!empty($question->image))
                                            <div class="wq-image-wrap">
                                                <img
                                                    src="{{ asset(
                                                        'storage/' .
                                                        $question->image
                                                    ) }}"
                                                    alt="Writing question image"
                                                    onerror="
                                                        this.classList.add('hidden');
                                                        this.nextElementSibling.classList.remove('hidden');
                                                        this.nextElementSibling.classList.add('flex');
                                                    "
                                                    class="wq-image">

                                                <div
                                                    class="wq-no-image hidden
                                                           h-full w-full
                                                           items-center justify-center">

                                                    No Image
                                                </div>
                                            </div>
                                        @else
                                            <div class="wq-image-wrap">
                                                <span class="wq-no-image">
                                                    No Image
                                                </span>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- QUESTION --}}
                                    <div class="wq-mobile-content">
                                        <p class="wq-question-text">
                                            {{ \Illuminate\Support\Str::limit(
                                                trim(
                                                    strip_tags(
                                                        $question->question
                                                    )
                                                ),
                                                180
                                            ) }}
                                        </p>

                                        <div class="wq-mobile-actions">

                                            <a
                                                href="{{ route(
                                                    'admin.writing-questions.edit',
                                                    $question->id
                                                ) }}"
                                                class="wq-action wq-action-edit">

                                                Edit
                                            </a>

                                            <button
                                                type="button"
                                                data-url="{{ route(
                                                    'admin.writing-questions.destroy',
                                                    $question->id
                                                ) }}"
                                                data-question="{{ \Illuminate\Support\Str::limit(
                                                    trim(
                                                        strip_tags(
                                                            $question->question
                                                        )
                                                    ),
                                                    80
                                                ) }}"
                                                onclick="openDeleteModal(
                                                    this.dataset.url,
                                                    this.dataset.question
                                                )"
                                                class="wq-action wq-action-delete">

                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>

                @else

                    {{-- ====================================================
                        EMPTY STATE
                    ==================================================== --}}
                    <div class="wq-empty">

                        <div
                            class="wq-empty-icon"
                            aria-hidden="true">
                            ✍
                        </div>

                        <h3 class="wq-empty-title">
                            No Questions Yet
                        </h3>

                        <p class="wq-empty-text">
                            @if ($isLessonMode)
                                Create the first writing question for this assessment.
                            @else
                                Create the first writing question for this material.
                            @endif
                        </p>

                        <a
                            href="{{ $createRoute }}"
                            class="wq-btn wq-btn-primary">

                            <span aria-hidden="true">+</span>
                            Create First Question
                        </a>
                    </div>
                @endif
            </section>
        </div>
    </div>

    {{-- ================================================================
        DELETE MODAL
    ================================================================= --}}
    <div
        id="deleteModal"
        class="wq-modal"
        aria-hidden="true">

        <div
            id="deleteModalPanel"
            class="wq-modal-panel"
            role="dialog"
            aria-modal="true"
            aria-labelledby="deleteModalTitle">

            <div class="wq-modal-header">
                <h3
                    id="deleteModalTitle"
                    class="wq-modal-title">

                    Delete Writing Question
                </h3>

                <p class="wq-modal-subtitle">
                    Please confirm this action.
                </p>
            </div>

            <div class="wq-modal-body">
                <p class="wq-modal-copy">
                    Are you sure you want to delete this question?
                </p>

                <div
                    id="deleteQuestionText"
                    class="wq-modal-question">

                    Writing question
                </div>

                <div class="wq-modal-warning">
                    This action cannot be undone.
                </div>
            </div>

            <div class="wq-modal-footer">

                <button
                    type="button"
                    onclick="closeDeleteModal()"
                    class="wq-modal-cancel">

                    Cancel
                </button>

                <form
                    id="deleteForm"
                    method="POST">

                    @csrf
                    @method('DELETE')

                    <button
                        type="submit"
                        class="wq-modal-delete">

                        Delete Question
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ================================================================
        JAVASCRIPT
    ================================================================= --}}
    <script>
        function openDeleteModal(
            actionUrl,
            questionText
        ) {
            const modal =
                document.getElementById(
                    'deleteModal'
                );

            const panel =
                document.getElementById(
                    'deleteModalPanel'
                );

            const form =
                document.getElementById(
                    'deleteForm'
                );

            const question =
                document.getElementById(
                    'deleteQuestionText'
                );

            if (
                !modal ||
                !panel ||
                !form ||
                !question
            ) {
                return;
            }

            form.action =
                actionUrl;

            question.textContent =
                questionText ||
                'Writing question';

            modal.classList.add(
                'is-open'
            );

            modal.setAttribute(
                'aria-hidden',
                'false'
            );

            document.body.style.overflow =
                'hidden';

            requestAnimationFrame(
                function () {
                    panel.classList.add(
                        'is-visible'
                    );
                }
            );
        }

        function closeDeleteModal() {
            const modal =
                document.getElementById(
                    'deleteModal'
                );

            const panel =
                document.getElementById(
                    'deleteModalPanel'
                );

            if (
                !modal ||
                !panel ||
                !modal.classList.contains(
                    'is-open'
                )
            ) {
                return;
            }

            panel.classList.remove(
                'is-visible'
            );

            setTimeout(
                function () {
                    modal.classList.remove(
                        'is-open'
                    );

                    modal.setAttribute(
                        'aria-hidden',
                        'true'
                    );

                    document.body.style.overflow =
                        '';
                },
                170
            );
        }

        document.addEventListener(
            'keydown',
            function (event) {
                if (
                    event.key === 'Escape'
                ) {
                    closeDeleteModal();
                }
            }
        );

        document.addEventListener(
            'DOMContentLoaded',
            function () {
                const modal =
                    document.getElementById(
                        'deleteModal'
                    );

                if (!modal) {
                    return;
                }

                modal.addEventListener(
                    'click',
                    function (event) {
                        if (
                            event.target === modal
                        ) {
                            closeDeleteModal();
                        }
                    }
                );
            }
        );
    </script>
@endsection
