@extends('layouts.admin')

@section('content')
    @php
        $lessonTitle = $lesson->title ?? 'Writing';
        $unitTitle = optional($lesson->unit)->title;

        $contextTitle = $unitTitle
            ? $unitTitle . ' — ' . $lessonTitle
            : $lessonTitle;

        $materialCount = $materials->count();
    @endphp

    <style>
        /* ============================================================
           WRITING MATERIALS PAGE
           Scoped styles keep light/dark mode consistent even when the
           admin layout has global table, card, or button styles.
        ============================================================ */
        .wm-page {
            --wm-card: #ffffff;
            --wm-card-soft: #f8fafc;
            --wm-card-muted: #f1f5f9;
            --wm-border: #e2e8f0;
            --wm-border-soft: #edf2f7;
            --wm-text: #0f172a;
            --wm-text-soft: #475569;
            --wm-text-muted: #64748b;

            width: 100%;
            max-width: 1500px;
            margin: 0 auto;
            padding-top: 18px;
            padding-bottom: 32px;
            color: var(--wm-text);
        }

        .dark .wm-page {
            --wm-card: #111827;
            --wm-card-soft: #172033;
            --wm-card-muted: #1e293b;
            --wm-border: #263449;
            --wm-border-soft: #1e2a3b;
            --wm-text: #f1f5f9;
            --wm-text-soft: #cbd5e1;
            --wm-text-muted: #94a3b8;
        }

        .wm-stack > * + * {
            margin-top: 20px;
        }

        /* ============================================================
           PAGE HEADER
        ============================================================ */
        .wm-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 24px;
        }

        .wm-header-copy {
            min-width: 0;
        }

        .wm-context-label {
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

        .dark .wm-context-label {
            color: #c4b5fd !important;
        }

        .wm-context-dot {
            width: 6px;
            height: 6px;
            border-radius: 999px;
            background: currentColor;
        }

        .wm-title {
            margin: 0;
            color: var(--wm-text) !important;
            font-size: 30px;
            line-height: 1.15;
            font-weight: 800;
            letter-spacing: -0.025em;
        }

        .wm-subtitle {
            margin: 8px 0 0;
            max-width: 680px;
            color: var(--wm-text-muted) !important;
            font-size: 14px;
            line-height: 1.65;
        }

        .wm-header-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 0 0 auto;
        }

        /* ============================================================
           BUTTONS
        ============================================================ */
        .wm-btn,
        .wm-action,
        .wm-modal-cancel,
        .wm-modal-delete {
            font-family: inherit;
        }

        .wm-btn {
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
            cursor: pointer;
            transition:
                background 150ms ease,
                border-color 150ms ease,
                color 150ms ease,
                transform 150ms ease,
                box-shadow 150ms ease;
        }

        .wm-btn:active,
        .wm-action:active,
        .wm-modal-cancel:active,
        .wm-modal-delete:active {
            transform: scale(0.985);
        }

        .wm-btn:focus-visible,
        .wm-action:focus-visible,
        .wm-modal-cancel:focus-visible,
        .wm-modal-delete:focus-visible {
            outline: none;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.18) !important;
        }

        .wm-btn-primary {
            border: 1px solid #2563eb !important;
            background: #2563eb !important;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.14) !important;
        }

        .wm-btn-primary:hover {
            border-color: #1d4ed8 !important;
            background: #1d4ed8 !important;
            color: #ffffff !important;
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.18) !important;
        }

        .dark .wm-btn-primary {
            border-color: #3b82f6 !important;
            background: #3b82f6 !important;
            color: #ffffff !important;
        }

        .dark .wm-btn-primary:hover {
            border-color: #2563eb !important;
            background: #2563eb !important;
        }

        /* ============================================================
           FLASH MESSAGES
        ============================================================ */
        .wm-alert {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 11px 14px;
            border-radius: 12px;
            font-size: 14px;
            line-height: 1.5;
            font-weight: 650;
        }

        .wm-alert-icon {
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

        .wm-alert-success {
            border: 1px solid #bbf7d0 !important;
            background: #f0fdf4 !important;
            color: #047857 !important;
        }

        .wm-alert-success .wm-alert-icon {
            background: #d1fae5 !important;
            color: #047857 !important;
        }

        .dark .wm-alert-success {
            border-color: rgba(16, 185, 129, 0.18) !important;
            background: rgba(16, 185, 129, 0.08) !important;
            color: #6ee7b7 !important;
        }

        .dark .wm-alert-success .wm-alert-icon {
            background: rgba(16, 185, 129, 0.14) !important;
            color: #6ee7b7 !important;
        }

        .wm-alert-error {
            border: 1px solid #fecaca !important;
            background: #fef2f2 !important;
            color: #b91c1c !important;
        }

        .wm-alert-error .wm-alert-icon {
            background: #fee2e2 !important;
            color: #dc2626 !important;
        }

        .dark .wm-alert-error {
            border-color: rgba(239, 68, 68, 0.18) !important;
            background: rgba(239, 68, 68, 0.08) !important;
            color: #fca5a5 !important;
        }

        .dark .wm-alert-error .wm-alert-icon {
            background: rgba(239, 68, 68, 0.14) !important;
            color: #fca5a5 !important;
        }

        /* ============================================================
           CARDS
        ============================================================ */
        .wm-card {
            overflow: hidden;
            border: 1px solid var(--wm-border) !important;
            border-radius: 18px;
            background: var(--wm-card) !important;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.035);
        }

        .dark .wm-card {
            box-shadow: none !important;
        }

        /* ============================================================
           LESSON SUMMARY
        ============================================================ */
        .wm-summary {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            padding: 20px 22px;
        }

        .wm-summary-main {
            display: flex;
            min-width: 0;
            align-items: center;
            gap: 14px;
        }

        .wm-summary-icon {
            display: flex;
            width: 44px;
            height: 44px;
            flex: 0 0 44px;
            align-items: center;
            justify-content: center;
            border: 1px solid #ede9fe !important;
            border-radius: 12px;
            background: #f5f3ff !important;
            color: #7c3aed !important;
        }

        .wm-summary-icon svg {
            width: 21px;
            height: 21px;
        }

        .dark .wm-summary-icon {
            border-color: rgba(139, 92, 246, 0.16) !important;
            background: rgba(139, 92, 246, 0.09) !important;
            color: #c4b5fd !important;
        }

        .wm-summary-copy {
            min-width: 0;
        }

        .wm-summary-label {
            margin: 0;
            color: var(--wm-text-muted) !important;
            font-size: 12px;
            font-weight: 700;
        }

        .wm-summary-title {
            margin: 4px 0 0;
            color: var(--wm-text) !important;
            font-size: 18px;
            line-height: 1.4;
            font-weight: 750;
            overflow-wrap: anywhere;
        }

        .wm-badge {
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

        .wm-badge-count {
            border: 1px solid #e2e8f0 !important;
            background: #f8fafc !important;
            color: #475569 !important;
        }

        .dark .wm-badge-count {
            border-color: #334155 !important;
            background: #1e293b !important;
            color: #cbd5e1 !important;
        }

        .wm-badge-question {
            border: 1px solid #ddd6fe !important;
            background: #f5f3ff !important;
            color: #7c3aed !important;
        }

        .dark .wm-badge-question {
            border-color: rgba(139, 92, 246, 0.18) !important;
            background: rgba(139, 92, 246, 0.09) !important;
            color: #c4b5fd !important;
        }

        /* ============================================================
           MATERIAL LIST HEADER
        ============================================================ */
        .wm-list-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 16px;
            padding: 18px 22px;
            border-bottom: 1px solid var(--wm-border) !important;
            background: var(--wm-card) !important;
        }

        .wm-list-title {
            margin: 0;
            color: var(--wm-text) !important;
            font-size: 18px;
            font-weight: 750;
        }

        .wm-list-description {
            margin: 5px 0 0;
            color: var(--wm-text-muted) !important;
            font-size: 13px;
            line-height: 1.55;
        }

        /* ============================================================
           DESKTOP TABLE
        ============================================================ */
        .wm-table-wrap {
            display: block;
            overflow-x: auto;
            background: var(--wm-card) !important;
        }

        .wm-table {
            width: 100%;
            min-width: 1050px;
            border-collapse: collapse !important;
            border-spacing: 0 !important;
            background: var(--wm-card) !important;
        }

        .wm-table thead,
        .wm-table thead tr,
        .wm-table thead th {
            background: #f8fafc !important;
        }

        .dark .wm-table thead,
        .dark .wm-table thead tr,
        .dark .wm-table thead th {
            background: #172033 !important;
        }

        .wm-table th {
            padding: 12px 20px;
            border: 0 !important;
            border-bottom: 1px solid var(--wm-border) !important;
            color: #64748b !important;
            font-size: 11px;
            line-height: 1;
            font-weight: 800;
            letter-spacing: 0.055em;
            text-align: left;
            text-transform: uppercase;
        }

        .dark .wm-table th {
            color: #94a3b8 !important;
        }

        .wm-table th:nth-child(4),
        .wm-table th:last-child {
            text-align: right;
        }

        .wm-table tbody,
        .wm-table tbody tr,
        .wm-table tbody td {
            background: var(--wm-card) !important;
        }

        .wm-table tbody tr {
            transition: background 150ms ease;
        }

        .wm-table tbody tr:hover,
        .wm-table tbody tr:hover td {
            background: var(--wm-card-soft) !important;
        }

        .wm-table td {
            padding: 17px 20px;
            border: 0 !important;
            border-bottom: 1px solid var(--wm-border-soft) !important;
            vertical-align: middle;
        }

        .wm-table tbody tr:last-child td {
            border-bottom: 0 !important;
        }

        .wm-image-wrap {
            position: relative;
            display: flex;
            width: 104px;
            height: 78px;
            overflow: hidden;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--wm-border) !important;
            border-radius: 11px;
            background: var(--wm-card-soft) !important;
        }

        .wm-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .wm-image-fallback {
            display: flex;
            width: 100%;
            height: 100%;
            align-items: center;
            justify-content: center;
            color: var(--wm-text-muted) !important;
            font-size: 11px;
            font-weight: 700;
            text-align: center;
        }

        .wm-image-fallback.is-hidden {
            display: none;
        }

        .wm-material-title {
            margin: 0;
            color: var(--wm-text) !important;
            font-size: 14px;
            line-height: 1.5;
            font-weight: 750;
            overflow-wrap: anywhere;
        }

        .wm-passage {
            margin: 0;
            max-width: 520px;
            color: var(--wm-text-soft) !important;
            font-size: 13px;
            line-height: 1.65;
            font-weight: 500;
            overflow-wrap: anywhere;
        }

        .wm-passage-empty {
            margin: 0;
            color: var(--wm-text-muted) !important;
            font-size: 13px;
            line-height: 1.55;
            font-style: italic;
        }

        .wm-question-cell {
            text-align: right;
        }

        .wm-row-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 7px;
        }

        .wm-action {
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
                color 150ms ease,
                transform 150ms ease;
        }

        .wm-action-question {
            border: 1px solid #ddd6fe !important;
            background: #f5f3ff !important;
            color: #7c3aed !important;
        }

        .wm-action-question:hover {
            background: #ede9fe !important;
        }

        .dark .wm-action-question {
            border-color: rgba(139, 92, 246, 0.18) !important;
            background: rgba(139, 92, 246, 0.09) !important;
            color: #c4b5fd !important;
        }

        .dark .wm-action-question:hover {
            background: rgba(139, 92, 246, 0.14) !important;
        }

        .wm-action-edit {
            border: 1px solid #bfdbfe !important;
            background: #eff6ff !important;
            color: #1d4ed8 !important;
        }

        .wm-action-edit:hover {
            background: #dbeafe !important;
        }

        .dark .wm-action-edit {
            border-color: rgba(59, 130, 246, 0.18) !important;
            background: rgba(59, 130, 246, 0.09) !important;
            color: #93c5fd !important;
        }

        .dark .wm-action-edit:hover {
            background: rgba(59, 130, 246, 0.14) !important;
        }

        .wm-action-delete {
            border: 1px solid #fecaca !important;
            background: #fef2f2 !important;
            color: #b91c1c !important;
        }

        .wm-action-delete:hover {
            background: #fee2e2 !important;
        }

        .dark .wm-action-delete {
            border-color: rgba(239, 68, 68, 0.18) !important;
            background: rgba(239, 68, 68, 0.08) !important;
            color: #fca5a5 !important;
        }

        .dark .wm-action-delete:hover {
            background: rgba(239, 68, 68, 0.13) !important;
        }

        /* ============================================================
           MOBILE / TABLET MATERIAL CARDS
        ============================================================ */
        .wm-mobile-list {
            display: none;
            background: var(--wm-card) !important;
        }

        .wm-mobile-item {
            padding: 17px;
            border-bottom: 1px solid var(--wm-border-soft) !important;
            background: var(--wm-card) !important;
        }

        .wm-mobile-item:last-child {
            border-bottom: 0 !important;
        }

        .wm-mobile-layout {
            display: flex;
            gap: 14px;
        }

        .wm-mobile-image {
            width: 126px;
            flex: 0 0 126px;
        }

        .wm-mobile-content {
            min-width: 0;
            flex: 1;
        }

        .wm-mobile-title-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
        }

        .wm-mobile-passage {
            margin-top: 9px;
        }

        .wm-mobile-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
            margin-top: 14px;
        }

        /* ============================================================
           EMPTY STATE
        ============================================================ */
        .wm-empty {
            padding: 54px 20px;
            text-align: center;
            background: var(--wm-card) !important;
        }

        .wm-empty-icon {
            display: flex;
            width: 52px;
            height: 52px;
            margin: 0 auto;
            align-items: center;
            justify-content: center;
            border: 1px solid #ede9fe !important;
            border-radius: 14px;
            background: #f5f3ff !important;
            color: #7c3aed !important;
        }

        .wm-empty-icon svg {
            width: 23px;
            height: 23px;
        }

        .dark .wm-empty-icon {
            border-color: rgba(139, 92, 246, 0.16) !important;
            background: rgba(139, 92, 246, 0.09) !important;
            color: #c4b5fd !important;
        }

        .wm-empty-title {
            margin: 16px 0 0;
            color: var(--wm-text) !important;
            font-size: 18px;
            font-weight: 750;
        }

        .wm-empty-text {
            margin: 7px auto 0;
            max-width: 430px;
            color: var(--wm-text-muted) !important;
            font-size: 14px;
            line-height: 1.6;
        }

        .wm-empty .wm-btn-primary {
            margin-top: 20px;
        }

        /* ============================================================
           DELETE MODAL
        ============================================================ */
        .wm-modal {
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

        .wm-modal.is-open {
            display: flex;
        }

        .wm-modal-panel {
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

        .wm-modal-panel.is-visible {
            opacity: 1;
            transform: translateY(0) scale(1);
        }

        .dark .wm-modal-panel {
            border-color: #253247 !important;
            background: #111827 !important;
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.48);
        }

        .wm-modal-header {
            padding: 19px 20px;
            border-bottom: 1px solid #e2e8f0 !important;
            background: #ffffff !important;
        }

        .dark .wm-modal-header {
            border-bottom-color: #253247 !important;
            background: #111827 !important;
        }

        .wm-modal-title {
            margin: 0;
            color: #0f172a !important;
            font-size: 18px;
            font-weight: 800;
        }

        .dark .wm-modal-title {
            color: #f1f5f9 !important;
        }

        .wm-modal-subtitle {
            margin: 5px 0 0;
            color: #64748b !important;
            font-size: 13px;
        }

        .dark .wm-modal-subtitle {
            color: #94a3b8 !important;
        }

        .wm-modal-body {
            padding: 20px;
            background: #ffffff !important;
        }

        .dark .wm-modal-body {
            background: #111827 !important;
        }

        .wm-modal-copy {
            margin: 0;
            color: #475569 !important;
            font-size: 14px;
            line-height: 1.6;
        }

        .dark .wm-modal-copy {
            color: #cbd5e1 !important;
        }

        .wm-modal-material {
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

        .dark .wm-modal-material {
            border-color: #253247 !important;
            background: #182235 !important;
            color: #e2e8f0 !important;
        }

        .wm-modal-warning {
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

        .dark .wm-modal-warning {
            border-color: rgba(239, 68, 68, 0.16) !important;
            background: rgba(239, 68, 68, 0.07) !important;
            color: #fca5a5 !important;
        }

        .wm-modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 9px;
            padding: 14px 20px;
            border-top: 1px solid #e2e8f0 !important;
            background: #f8fafc !important;
        }

        .dark .wm-modal-footer {
            border-top-color: #253247 !important;
            background: #0f172a !important;
        }

        .wm-modal-cancel,
        .wm-modal-delete {
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
                color 150ms ease,
                transform 150ms ease;
        }

        .wm-modal-cancel {
            border: 1px solid #cbd5e1 !important;
            background: #ffffff !important;
            color: #475569 !important;
        }

        .wm-modal-cancel:hover {
            background: #f1f5f9 !important;
        }

        .dark .wm-modal-cancel {
            border-color: #334155 !important;
            background: #1e293b !important;
            color: #e2e8f0 !important;
        }

        .dark .wm-modal-cancel:hover {
            background: #273449 !important;
        }

        .wm-modal-delete {
            border: 1px solid #dc2626 !important;
            background: #dc2626 !important;
            color: #ffffff !important;
        }

        .wm-modal-delete:hover {
            border-color: #b91c1c !important;
            background: #b91c1c !important;
        }

        /* ============================================================
           RESPONSIVE
        ============================================================ */
        @media (max-width: 1023px) {
            .wm-table-wrap {
                display: none;
            }

            .wm-mobile-list {
                display: block;
            }
        }

        @media (max-width: 767px) {
            .wm-page {
                padding-top: 12px;
                padding-bottom: 24px;
            }

            .wm-stack > * + * {
                margin-top: 16px;
            }

            .wm-header {
                align-items: stretch;
                flex-direction: column;
            }

            .wm-header-actions {
                display: grid;
                grid-template-columns: 1fr;
            }

            .wm-btn {
                width: 100%;
            }

            .wm-title {
                font-size: 26px;
            }

            .wm-summary {
                align-items: flex-start;
                flex-direction: column;
                padding: 17px;
            }

            .wm-summary-main {
                align-items: flex-start;
            }

            .wm-list-header {
                align-items: flex-start;
                flex-direction: column;
                padding: 17px;
            }

            .wm-mobile-layout {
                flex-direction: column;
            }

            .wm-mobile-image {
                width: 100%;
                flex-basis: auto;
            }

            .wm-mobile-image .wm-image-wrap {
                width: 100%;
                height: 180px;
            }

            .wm-mobile-title-row {
                flex-direction: column;
            }

            .wm-mobile-actions {
                display: grid;
                grid-template-columns: 1fr 1fr;
            }

            .wm-mobile-actions .wm-action {
                width: 100%;
                min-height: 40px;
            }

            .wm-mobile-actions .wm-action-question {
                grid-column: 1 / -1;
            }

            .wm-modal {
                align-items: flex-end;
                padding: 10px;
            }

            .wm-modal-panel {
                max-width: none;
            }

            .wm-modal-footer {
                flex-direction: column-reverse;
            }

            .wm-modal-footer form,
            .wm-modal-cancel,
            .wm-modal-delete {
                width: 100%;
            }
        }

        @media (max-width: 420px) {
            .wm-mobile-actions {
                grid-template-columns: 1fr;
            }

            .wm-mobile-actions .wm-action-question {
                grid-column: auto;
            }
        }
    </style>

    <div class="wm-page">
        <div class="wm-stack">

            {{-- ========================================================
                PAGE HEADER
            ======================================================== --}}
            <header class="wm-header">
                <div class="wm-header-copy">
                    <div class="wm-context-label">
                        <span class="wm-context-dot"></span>
                        Writing Lesson
                    </div>

                    <h1 class="wm-title">
                        Writing Materials
                    </h1>

                    <p class="wm-subtitle">
                        Manage writing materials and their questions for this lesson.
                    </p>
                </div>

                <div class="wm-header-actions">
                    <a
                        href="{{ route(
                            'admin.writing-materials.create',
                            $lesson->id
                        ) }}"
                        class="wm-btn wm-btn-primary">

                        <span aria-hidden="true">+</span>
                        Add Material
                    </a>
                </div>
            </header>

            {{-- ========================================================
                SUCCESS MESSAGE
            ======================================================== --}}
            @if (session('success'))
                <div class="wm-alert wm-alert-success">
                    <div class="wm-alert-icon" aria-hidden="true">
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
                <div class="wm-alert wm-alert-error">
                    <div class="wm-alert-icon" aria-hidden="true">
                        !
                    </div>

                    <div>
                        {{ session('error') }}
                    </div>
                </div>
            @endif

            {{-- ========================================================
                LESSON SUMMARY
            ======================================================== --}}
            <section class="wm-card wm-summary">
                <div class="wm-summary-main">
                    <div class="wm-summary-icon" aria-hidden="true">
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round">

                            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
                            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z" />
                            <path d="M8 7h8" />
                            <path d="M8 11h6" />
                        </svg>
                    </div>

                    <div class="wm-summary-copy">
                        <p class="wm-summary-label">
                            Current Lesson
                        </p>

                        <h2 class="wm-summary-title">
                            {{ $contextTitle }}
                        </h2>
                    </div>
                </div>

                <span class="wm-badge wm-badge-count">
                    {{ $materialCount }}

                    {{ \Illuminate\Support\Str::plural(
                        'Material',
                        $materialCount
                    ) }}
                </span>
            </section>

            {{-- ========================================================
                MATERIAL LIST
            ======================================================== --}}
            <section class="wm-card">
                <div class="wm-list-header">
                    <div>
                        <h3 class="wm-list-title">
                            Material List
                        </h3>

                        <p class="wm-list-description">
                            Review, edit, delete, or manage questions for each material.
                        </p>
                    </div>

                    @if ($materialCount)
                        <span class="wm-badge wm-badge-count">
                            {{ $materialCount }}

                            {{ \Illuminate\Support\Str::plural(
                                'Material',
                                $materialCount
                            ) }}
                        </span>
                    @endif
                </div>

                @if ($materials->count())

                    {{-- ====================================================
                        DESKTOP TABLE
                    ==================================================== --}}
                    <div class="wm-table-wrap">
                        <table class="wm-table">
                            <thead>
                                <tr>
                                    <th style="width: 14%;">
                                        Image
                                    </th>

                                    <th style="width: 19%;">
                                        Title
                                    </th>

                                    <th style="width: 31%;">
                                        Passage
                                    </th>

                                    <th style="width: 14%;">
                                        Questions
                                    </th>

                                    <th style="width: 22%;">
                                        Actions
                                    </th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($materials as $material)
                                    @php
                                        $questionCount = isset($material->questions_count)
                                            ? $material->questions_count
                                            : $material->questions->count();

                                        $cleanPassage = !empty($material->passage)
                                            ? trim(strip_tags($material->passage))
                                            : null;
                                    @endphp

                                    <tr>
                                        {{-- IMAGE --}}
                                        <td>
                                            <div class="wm-image-wrap">
                                                @if (!empty($material->image))
                                                    <img
                                                        src="{{ asset(
                                                            'storage/' .
                                                            $material->image
                                                        ) }}"
                                                        alt="{{ $material->title }}"
                                                        class="wm-image"
                                                        onerror="
                                                            this.style.display = 'none';
                                                            this.nextElementSibling.classList.remove('is-hidden');
                                                        ">

                                                    <div class="wm-image-fallback is-hidden">
                                                        No Image
                                                    </div>
                                                @else
                                                    <div class="wm-image-fallback">
                                                        No Image
                                                    </div>
                                                @endif
                                            </div>
                                        </td>

                                        {{-- TITLE --}}
                                        <td>
                                            <h4 class="wm-material-title">
                                                {{ $material->title }}
                                            </h4>
                                        </td>

                                        {{-- PASSAGE --}}
                                        <td>
                                            @if ($cleanPassage)
                                                <p class="wm-passage">
                                                    {{ \Illuminate\Support\Str::limit(
                                                        $cleanPassage,
                                                        160
                                                    ) }}
                                                </p>
                                            @else
                                                <p class="wm-passage-empty">
                                                    No passage provided.
                                                </p>
                                            @endif
                                        </td>

                                        {{-- QUESTION COUNT --}}
                                        <td class="wm-question-cell">
                                            <span class="wm-badge wm-badge-question">
                                                {{ $questionCount }}

                                                {{ \Illuminate\Support\Str::plural(
                                                    'Question',
                                                    $questionCount
                                                ) }}
                                            </span>
                                        </td>

                                        {{-- ACTIONS --}}
                                        <td>
                                            <div class="wm-row-actions">
                                                <a
                                                    href="{{ route(
                                                        'admin.writing-questions.index',
                                                        $material->id
                                                    ) }}"
                                                    class="wm-action wm-action-question">

                                                    Questions
                                                </a>

                                                <a
                                                    href="{{ route(
                                                        'admin.writing-materials.edit',
                                                        $material->id
                                                    ) }}"
                                                    class="wm-action wm-action-edit">

                                                    Edit
                                                </a>

                                                <button
                                                    type="button"
                                                    data-url="{{ route(
                                                        'admin.writing-materials.destroy',
                                                        $material->id
                                                    ) }}"
                                                    data-title="{{ $material->title }}"
                                                    onclick="openDeleteModal(
                                                        this.dataset.url,
                                                        this.dataset.title
                                                    )"
                                                    class="wm-action wm-action-delete">

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
                    <div class="wm-mobile-list">
                        @foreach ($materials as $material)
                            @php
                                $questionCount = isset($material->questions_count)
                                    ? $material->questions_count
                                    : $material->questions->count();

                                $cleanPassage = !empty($material->passage)
                                    ? trim(strip_tags($material->passage))
                                    : null;
                            @endphp

                            <article class="wm-mobile-item">
                                <div class="wm-mobile-layout">

                                    {{-- IMAGE --}}
                                    <div class="wm-mobile-image">
                                        <div class="wm-image-wrap">
                                            @if (!empty($material->image))
                                                <img
                                                    src="{{ asset(
                                                        'storage/' .
                                                        $material->image
                                                    ) }}"
                                                    alt="{{ $material->title }}"
                                                    class="wm-image"
                                                    onerror="
                                                        this.style.display = 'none';
                                                        this.nextElementSibling.classList.remove('is-hidden');
                                                    ">

                                                <div class="wm-image-fallback is-hidden">
                                                    No Image
                                                </div>
                                            @else
                                                <div class="wm-image-fallback">
                                                    No Image
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- CONTENT --}}
                                    <div class="wm-mobile-content">
                                        <div class="wm-mobile-title-row">
                                            <h4 class="wm-material-title">
                                                {{ $material->title }}
                                            </h4>

                                            <span class="wm-badge wm-badge-question">
                                                {{ $questionCount }}

                                                {{ \Illuminate\Support\Str::plural(
                                                    'Question',
                                                    $questionCount
                                                ) }}
                                            </span>
                                        </div>

                                        <div class="wm-mobile-passage">
                                            @if ($cleanPassage)
                                                <p class="wm-passage">
                                                    {{ \Illuminate\Support\Str::limit(
                                                        $cleanPassage,
                                                        180
                                                    ) }}
                                                </p>
                                            @else
                                                <p class="wm-passage-empty">
                                                    No passage provided.
                                                </p>
                                            @endif
                                        </div>

                                        <div class="wm-mobile-actions">
                                            <a
                                                href="{{ route(
                                                    'admin.writing-questions.index',
                                                    $material->id
                                                ) }}"
                                                class="wm-action wm-action-question">

                                                Questions
                                            </a>

                                            <a
                                                href="{{ route(
                                                    'admin.writing-materials.edit',
                                                    $material->id
                                                ) }}"
                                                class="wm-action wm-action-edit">

                                                Edit
                                            </a>

                                            <button
                                                type="button"
                                                data-url="{{ route(
                                                    'admin.writing-materials.destroy',
                                                    $material->id
                                                ) }}"
                                                data-title="{{ $material->title }}"
                                                onclick="openDeleteModal(
                                                    this.dataset.url,
                                                    this.dataset.title
                                                )"
                                                class="wm-action wm-action-delete">

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
                    <div class="wm-empty">
                        <div class="wm-empty-icon" aria-hidden="true">
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round">

                                <path d="M12 20h9" />
                                <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z" />
                            </svg>
                        </div>

                        <h3 class="wm-empty-title">
                            No Writing Materials Yet
                        </h3>

                        <p class="wm-empty-text">
                            Create the first writing material for this lesson.
                        </p>

                        <a
                            href="{{ route(
                                'admin.writing-materials.create',
                                $lesson->id
                            ) }}"
                            class="wm-btn wm-btn-primary">

                            <span aria-hidden="true">+</span>
                            Create First Material
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
        class="wm-modal"
        aria-hidden="true">

        <div
            id="deleteModalPanel"
            class="wm-modal-panel"
            role="dialog"
            aria-modal="true"
            aria-labelledby="deleteModalTitle">

            <div class="wm-modal-header">
                <h3
                    id="deleteModalTitle"
                    class="wm-modal-title">

                    Delete Writing Material
                </h3>

                <p class="wm-modal-subtitle">
                    Please confirm this action.
                </p>
            </div>

            <div class="wm-modal-body">
                <p class="wm-modal-copy">
                    Are you sure you want to delete this material?
                </p>

                <div
                    id="deleteMaterialTitle"
                    class="wm-modal-material">

                    Writing material
                </div>

                <div class="wm-modal-warning">
                    This action cannot be undone. Questions connected to this
                    material may also be deleted.
                </div>
            </div>

            <div class="wm-modal-footer">
                <button
                    type="button"
                    onclick="closeDeleteModal()"
                    class="wm-modal-cancel">

                    Cancel
                </button>

                <form
                    id="deleteForm"
                    method="POST">

                    @csrf
                    @method('DELETE')

                    <button
                        type="submit"
                        class="wm-modal-delete">

                        Delete Material
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
            materialTitle
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

            const title =
                document.getElementById(
                    'deleteMaterialTitle'
                );

            if (
                !modal ||
                !panel ||
                !form ||
                !title
            ) {
                return;
            }

            form.action =
                actionUrl;

            title.textContent =
                materialTitle ||
                'Writing material';

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
