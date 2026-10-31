@extends('layouts.admin')

@section('content')
    @php
        /*
        |--------------------------------------------------------------------------
        | Lesson Context
        |--------------------------------------------------------------------------
        |
        | PRETEST / POSTTEST:
        | - Individual Speaking
        | - 1 student
        | - Durasi tetap 2–3 menit
        |
        | Unit 1–4:
        | - Pair Speaking
        | - Student A dan Student B
        |
        */
        $isAssessment = in_array(
            $lesson->unit?->type,
            [
                'pretest',
                'posttest',
            ],
            true
        );

        $assessmentLabel = $isAssessment
            ? strtoupper($lesson->unit->type)
            : null;

        $taskCount = $materials->count();
    @endphp

    <style>
        /*
        |--------------------------------------------------------------------------
        | Speaking Materials Index
        |--------------------------------------------------------------------------
        |
        | Style dibuat scoped agar tidak bentrok dengan style global admin.
        |
        */
        .spk-index,
        .spk-index * {
            box-sizing: border-box;
        }

        .spk-index {
            --spk-bg: #ffffff;
            --spk-bg-soft: #f8fafc;
            --spk-bg-muted: #f1f5f9;
            --spk-border: #e2e8f0;
            --spk-border-soft: #edf2f7;
            --spk-text: #0f172a;
            --spk-text-soft: #475569;
            --spk-text-muted: #64748b;

            width: 100%;
            max-width: 1500px;
            margin: 0 auto;
            padding: 12px 0 32px;
            color: var(--spk-text);
        }

        .dark .spk-index {
            --spk-bg: #111827;
            --spk-bg-soft: #172033;
            --spk-bg-muted: #1e293b;
            --spk-border: #29364b;
            --spk-border-soft: #202c3f;
            --spk-text: #f8fafc;
            --spk-text-soft: #cbd5e1;
            --spk-text-muted: #94a3b8;
        }

        .spk-index-stack > * + * {
            margin-top: 20px;
        }

        /*
        |--------------------------------------------------------------------------
        | Header
        |--------------------------------------------------------------------------
        */
        .spk-index-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 24px;
        }

        .spk-index-header-copy {
            min-width: 0;
        }

        .spk-index-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 9px;
            color: #7c3aed !important;
            font-size: 11px;
            font-weight: 850;
            letter-spacing: 0.09em;
            text-transform: uppercase;
        }

        .dark .spk-index-eyebrow {
            color: #c4b5fd !important;
        }

        .spk-index-eyebrow-dot {
            width: 7px;
            height: 7px;
            border-radius: 999px;
            background: currentColor;
        }

        .spk-index-title {
            margin: 0;
            color: var(--spk-text) !important;
            font-size: 31px;
            line-height: 1.15;
            font-weight: 850;
            letter-spacing: -0.03em;
        }

        .spk-index-subtitle {
            max-width: 800px;
            margin: 9px 0 0;
            color: var(--spk-text-muted) !important;
            font-size: 14px;
            line-height: 1.7;
        }

        .spk-index-header-actions {
            display: flex;
            flex: 0 0 auto;
            align-items: center;
            gap: 10px;
        }

        /*
        |--------------------------------------------------------------------------
        | Buttons
        |--------------------------------------------------------------------------
        */
        .spk-index-btn {
            display: inline-flex !important;
            min-height: 44px;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 16px;
            border-radius: 11px;
            font-size: 14px;
            line-height: 1;
            font-weight: 750;
            text-decoration: none !important;
            cursor: pointer;
            transition:
                transform 150ms ease,
                border-color 150ms ease,
                background 150ms ease,
                color 150ms ease,
                box-shadow 150ms ease;
        }

        .spk-index-btn:hover {
            text-decoration: none !important;
        }

        .spk-index-btn:active,
        .spk-index-action:active {
            transform: scale(0.985);
        }

        .spk-index-btn:focus-visible,
        .spk-index-action:focus-visible,
        .spk-modal-cancel:focus-visible,
        .spk-modal-delete:focus-visible {
            outline: none !important;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.18) !important;
        }

        .spk-index-btn-secondary {
            border: 1px solid #cbd5e1 !important;
            background: #ffffff !important;
            color: #475569 !important;
        }

        .spk-index-btn-secondary:hover {
            border-color: #94a3b8 !important;
            background: #f8fafc !important;
            color: #0f172a !important;
        }

        .dark .spk-index-btn-secondary {
            border-color: #334155 !important;
            background: #1e293b !important;
            color: #e2e8f0 !important;
        }

        .dark .spk-index-btn-secondary:hover {
            border-color: #475569 !important;
            background: #273449 !important;
            color: #ffffff !important;
        }

        .spk-index-btn-primary {
            border: 1px solid #2563eb !important;
            background: #2563eb !important;
            color: #ffffff !important;
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.18) !important;
        }

        .spk-index-btn-primary:hover {
            border-color: #1d4ed8 !important;
            background: #1d4ed8 !important;
            color: #ffffff !important;
            transform: translateY(-1px);
            box-shadow: 0 11px 25px rgba(37, 99, 235, 0.23) !important;
        }

        .dark .spk-index-btn-primary {
            border-color: #3b82f6 !important;
            background: #3b82f6 !important;
        }

        .dark .spk-index-btn-primary:hover {
            border-color: #2563eb !important;
            background: #2563eb !important;
        }

        /*
        |--------------------------------------------------------------------------
        | Alerts
        |--------------------------------------------------------------------------
        */
        .spk-index-alert {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 13px 15px;
            border-radius: 13px;
            font-size: 14px;
            line-height: 1.55;
            font-weight: 650;
        }

        .spk-index-alert-icon {
            display: flex;
            width: 32px;
            height: 32px;
            flex: 0 0 32px;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            font-size: 14px;
            font-weight: 900;
        }

        .spk-index-alert-success {
            border: 1px solid #bbf7d0 !important;
            background: #f0fdf4 !important;
            color: #047857 !important;
        }

        .spk-index-alert-success .spk-index-alert-icon {
            background: #d1fae5 !important;
            color: #047857 !important;
        }

        .dark .spk-index-alert-success {
            border-color: rgba(16, 185, 129, 0.22) !important;
            background: rgba(16, 185, 129, 0.09) !important;
            color: #6ee7b7 !important;
        }

        .dark .spk-index-alert-success .spk-index-alert-icon {
            background: rgba(16, 185, 129, 0.15) !important;
            color: #6ee7b7 !important;
        }

        .spk-index-alert-error {
            border: 1px solid #fecaca !important;
            background: #fef2f2 !important;
            color: #b91c1c !important;
        }

        .spk-index-alert-error .spk-index-alert-icon {
            background: #fee2e2 !important;
            color: #dc2626 !important;
        }

        .dark .spk-index-alert-error {
            border-color: rgba(239, 68, 68, 0.22) !important;
            background: rgba(239, 68, 68, 0.09) !important;
            color: #fca5a5 !important;
        }

        .dark .spk-index-alert-error .spk-index-alert-icon {
            background: rgba(239, 68, 68, 0.15) !important;
            color: #fca5a5 !important;
        }

        /*
        |--------------------------------------------------------------------------
        | Base Card
        |--------------------------------------------------------------------------
        */
        .spk-index-card {
            overflow: hidden;
            border: 1px solid var(--spk-border) !important;
            border-radius: 20px;
            background: var(--spk-bg) !important;
            box-shadow: 0 2px 7px rgba(15, 23, 42, 0.035);
        }

        .dark .spk-index-card {
            box-shadow: none !important;
        }

        /*
        |--------------------------------------------------------------------------
        | Lesson Summary
        |--------------------------------------------------------------------------
        */
        .spk-index-summary {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 22px;
            padding: 21px 23px;
        }

        .spk-index-summary-main {
            display: flex;
            min-width: 0;
            align-items: center;
            gap: 15px;
        }

        .spk-index-summary-icon {
            display: flex;
            width: 48px;
            height: 48px;
            flex: 0 0 48px;
            align-items: center;
            justify-content: center;
            border: 1px solid #ede9fe !important;
            border-radius: 14px;
            background: #f5f3ff !important;
            color: #7c3aed !important;
        }

        .dark .spk-index-summary-icon {
            border-color: rgba(139, 92, 246, 0.22) !important;
            background: rgba(139, 92, 246, 0.11) !important;
            color: #c4b5fd !important;
        }

        .spk-index-summary-icon svg {
            width: 23px;
            height: 23px;
        }

        .spk-index-summary-copy {
            min-width: 0;
        }

        .spk-index-summary-label {
            margin: 0;
            color: var(--spk-text-muted) !important;
            font-size: 12px;
            font-weight: 750;
        }

        .spk-index-summary-title {
            margin: 4px 0 0;
            color: var(--spk-text) !important;
            font-size: 19px;
            line-height: 1.4;
            font-weight: 800;
            overflow-wrap: anywhere;
        }

        .spk-index-summary-description {
            max-width: 760px;
            margin: 5px 0 0;
            color: var(--spk-text-muted) !important;
            font-size: 13px;
            line-height: 1.6;
        }

        .spk-index-summary-badges {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 8px;
        }

        /*
        |--------------------------------------------------------------------------
        | Badges
        |--------------------------------------------------------------------------
        */
        .spk-index-badge {
            display: inline-flex;
            min-height: 29px;
            align-items: center;
            justify-content: center;
            gap: 5px;
            padding: 6px 11px;
            border-radius: 999px;
            font-size: 11px;
            line-height: 1;
            font-weight: 800;
            white-space: nowrap;
        }

        .spk-index-badge-purple {
            border: 1px solid #ddd6fe !important;
            background: #f5f3ff !important;
            color: #7c3aed !important;
        }

        .dark .spk-index-badge-purple {
            border-color: rgba(139, 92, 246, 0.22) !important;
            background: rgba(139, 92, 246, 0.11) !important;
            color: #c4b5fd !important;
        }

        .spk-index-badge-blue {
            border: 1px solid #bfdbfe !important;
            background: #eff6ff !important;
            color: #1d4ed8 !important;
        }

        .dark .spk-index-badge-blue {
            border-color: rgba(59, 130, 246, 0.22) !important;
            background: rgba(59, 130, 246, 0.11) !important;
            color: #93c5fd !important;
        }

        .spk-index-badge-green {
            border: 1px solid #a7f3d0 !important;
            background: #ecfdf5 !important;
            color: #047857 !important;
        }

        .dark .spk-index-badge-green {
            border-color: rgba(16, 185, 129, 0.22) !important;
            background: rgba(16, 185, 129, 0.1) !important;
            color: #6ee7b7 !important;
        }

        .spk-index-badge-red {
            border: 1px solid #fecaca !important;
            background: #fef2f2 !important;
            color: #b91c1c !important;
        }

        .dark .spk-index-badge-red {
            border-color: rgba(239, 68, 68, 0.2) !important;
            background: rgba(239, 68, 68, 0.08) !important;
            color: #fca5a5 !important;
        }

        .spk-index-badge-neutral {
            border: 1px solid #e2e8f0 !important;
            background: #f8fafc !important;
            color: #475569 !important;
        }

        .dark .spk-index-badge-neutral {
            border-color: #334155 !important;
            background: #1e293b !important;
            color: #cbd5e1 !important;
        }

        /*
        |--------------------------------------------------------------------------
        | List Header
        |--------------------------------------------------------------------------
        */
        .spk-index-list-head {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 18px;
            padding: 19px 23px;
            border-bottom: 1px solid var(--spk-border) !important;
            background: var(--spk-bg) !important;
        }

        .spk-index-list-title {
            margin: 0;
            color: var(--spk-text) !important;
            font-size: 18px;
            font-weight: 800;
        }

        .spk-index-list-description {
            margin: 5px 0 0;
            color: var(--spk-text-muted) !important;
            font-size: 13px;
            line-height: 1.6;
        }

        /*
        |--------------------------------------------------------------------------
        | Desktop Table
        |--------------------------------------------------------------------------
        */
        .spk-index-table-wrap {
            width: 100%;
            overflow-x: auto;
        }

        .spk-index-table {
            width: 100%;
            min-width: 1050px;
            border-collapse: collapse !important;
            border-spacing: 0 !important;
        }

        .spk-index-table thead,
        .spk-index-table thead tr,
        .spk-index-table thead th {
            background: var(--spk-bg-soft) !important;
        }

        .spk-index-table th {
            padding: 13px 18px;
            border: 0 !important;
            border-bottom: 1px solid var(--spk-border) !important;
            color: var(--spk-text-muted) !important;
            font-size: 11px;
            font-weight: 850;
            letter-spacing: 0.055em;
            text-align: left;
            text-transform: uppercase;
        }

        .spk-index-table th:last-child {
            text-align: right;
        }

        .spk-index-table tbody,
        .spk-index-table tbody tr,
        .spk-index-table tbody td {
            background: var(--spk-bg) !important;
        }

        .spk-index-table tbody tr {
            transition: background 150ms ease;
        }

        .spk-index-table tbody tr:hover,
        .spk-index-table tbody tr:hover td {
            background: var(--spk-bg-soft) !important;
        }

        .spk-index-table td {
            padding: 17px 18px;
            border: 0 !important;
            border-bottom: 1px solid var(--spk-border-soft) !important;
            vertical-align: middle;
        }

        .spk-index-table tbody tr:last-child td {
            border-bottom: 0 !important;
        }

        /*
        |--------------------------------------------------------------------------
        | Task Information
        |--------------------------------------------------------------------------
        */
        .spk-index-task {
            display: flex;
            min-width: 220px;
            align-items: center;
            gap: 12px;
        }

        .spk-index-task-image,
        .spk-index-task-placeholder {
            width: 52px;
            height: 52px;
            flex: 0 0 52px;
            overflow: hidden;
            border-radius: 12px;
        }

        .spk-index-task-image {
            border: 1px solid var(--spk-border) !important;
            background: var(--spk-bg-soft) !important;
        }

        .spk-index-task-image img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .spk-index-task-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #ede9fe !important;
            background: #f5f3ff !important;
            color: #7c3aed !important;
        }

        .dark .spk-index-task-placeholder {
            border-color: rgba(139, 92, 246, 0.22) !important;
            background: rgba(139, 92, 246, 0.11) !important;
            color: #c4b5fd !important;
        }

        .spk-index-task-placeholder svg {
            width: 22px;
            height: 22px;
        }

        .spk-index-task-copy {
            min-width: 0;
        }

        .spk-index-task-title {
            margin: 0;
            color: var(--spk-text) !important;
            font-size: 14px;
            line-height: 1.45;
            font-weight: 800;
            overflow-wrap: anywhere;
        }

        .spk-index-task-type {
            margin: 4px 0 0;
            color: var(--spk-text-muted) !important;
            font-size: 11px;
            font-weight: 650;
        }

        .spk-index-instruction {
            max-width: 390px;
            color: var(--spk-text-soft) !important;
            font-size: 13px;
            line-height: 1.65;
            overflow-wrap: anywhere;
        }

        /*
        |--------------------------------------------------------------------------
        | Row Actions
        |--------------------------------------------------------------------------
        */
        .spk-index-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
        }

        .spk-index-action {
            display: inline-flex !important;
            min-height: 37px;
            align-items: center;
            justify-content: center;
            padding: 8px 13px;
            border-radius: 9px;
            font-size: 12px;
            line-height: 1;
            font-weight: 750;
            text-decoration: none !important;
            cursor: pointer;
            transition:
                transform 150ms ease,
                border-color 150ms ease,
                background 150ms ease,
                color 150ms ease;
        }

        .spk-index-action-edit {
            border: 1px solid #bfdbfe !important;
            background: #eff6ff !important;
            color: #1d4ed8 !important;
        }

        .spk-index-action-edit:hover {
            background: #dbeafe !important;
            color: #1d4ed8 !important;
        }

        .dark .spk-index-action-edit {
            border-color: rgba(59, 130, 246, 0.22) !important;
            background: rgba(59, 130, 246, 0.11) !important;
            color: #93c5fd !important;
        }

        .dark .spk-index-action-edit:hover {
            background: rgba(59, 130, 246, 0.17) !important;
        }

        .spk-index-action-delete {
            border: 1px solid #fecaca !important;
            background: #fef2f2 !important;
            color: #b91c1c !important;
        }

        .spk-index-action-delete:hover {
            background: #fee2e2 !important;
            color: #b91c1c !important;
        }

        .dark .spk-index-action-delete {
            border-color: rgba(239, 68, 68, 0.22) !important;
            background: rgba(239, 68, 68, 0.09) !important;
            color: #fca5a5 !important;
        }

        .dark .spk-index-action-delete:hover {
            background: rgba(239, 68, 68, 0.15) !important;
        }

        /*
        |--------------------------------------------------------------------------
        | Mobile Cards
        |--------------------------------------------------------------------------
        */
        .spk-index-mobile-list {
            display: none;
        }

        .spk-index-mobile-item {
            padding: 18px;
            border-bottom: 1px solid var(--spk-border-soft) !important;
            background: var(--spk-bg) !important;
        }

        .spk-index-mobile-item:last-child {
            border-bottom: 0 !important;
        }

        .spk-index-mobile-head {
            display: flex;
            min-width: 0;
            align-items: center;
            gap: 12px;
        }

        .spk-index-mobile-content {
            min-width: 0;
            flex: 1;
        }

        .spk-index-mobile-instruction {
            margin-top: 14px;
            color: var(--spk-text-soft) !important;
            font-size: 13px;
            line-height: 1.65;
            overflow-wrap: anywhere;
        }

        .spk-index-mobile-meta {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 9px;
            margin-top: 15px;
        }

        .spk-index-mobile-meta-box {
            min-width: 0;
            padding: 11px;
            border: 1px solid var(--spk-border) !important;
            border-radius: 11px;
            background: var(--spk-bg-soft) !important;
        }

        .spk-index-mobile-meta-label {
            margin: 0 0 8px;
            color: var(--spk-text-muted) !important;
            font-size: 10px;
            line-height: 1;
            font-weight: 850;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .spk-index-mobile-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-top: 15px;
        }

        .spk-index-mobile-actions .spk-index-action {
            width: 100%;
            min-height: 41px;
        }

        /*
        |--------------------------------------------------------------------------
        | Empty State
        |--------------------------------------------------------------------------
        */
        .spk-index-empty {
            padding: 58px 22px;
            text-align: center;
            background: var(--spk-bg) !important;
        }

        .spk-index-empty-icon {
            display: flex;
            width: 58px;
            height: 58px;
            margin: 0 auto;
            align-items: center;
            justify-content: center;
            border: 1px solid #ede9fe !important;
            border-radius: 16px;
            background: #f5f3ff !important;
            color: #7c3aed !important;
        }

        .dark .spk-index-empty-icon {
            border-color: rgba(139, 92, 246, 0.22) !important;
            background: rgba(139, 92, 246, 0.11) !important;
            color: #c4b5fd !important;
        }

        .spk-index-empty-icon svg {
            width: 27px;
            height: 27px;
        }

        .spk-index-empty-title {
            margin: 17px 0 0;
            color: var(--spk-text) !important;
            font-size: 19px;
            font-weight: 800;
        }

        .spk-index-empty-text {
            max-width: 550px;
            margin: 8px auto 0;
            color: var(--spk-text-muted) !important;
            font-size: 14px;
            line-height: 1.7;
        }

        .spk-index-empty .spk-index-btn {
            margin-top: 21px;
        }

        /*
        |--------------------------------------------------------------------------
        | Delete Modal
        |--------------------------------------------------------------------------
        */
        .spk-modal {
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

        .spk-modal.is-open {
            display: flex;
        }

        .spk-modal-panel {
            width: 100%;
            max-width: 440px;
            overflow: hidden;
            border: 1px solid #e2e8f0 !important;
            border-radius: 19px;
            background: #ffffff !important;
            box-shadow: 0 25px 75px rgba(2, 6, 23, 0.28);
            opacity: 0;
            transform: translateY(10px) scale(0.98);
            transition:
                opacity 170ms ease,
                transform 170ms ease;
        }

        .spk-modal-panel.is-visible {
            opacity: 1;
            transform: translateY(0) scale(1);
        }

        .dark .spk-modal-panel {
            border-color: #29364b !important;
            background: #111827 !important;
            box-shadow: 0 30px 85px rgba(0, 0, 0, 0.5);
        }

        .spk-modal-header {
            padding: 20px 21px;
            border-bottom: 1px solid #e2e8f0 !important;
            background: #ffffff !important;
        }

        .dark .spk-modal-header {
            border-bottom-color: #29364b !important;
            background: #111827 !important;
        }

        .spk-modal-title {
            margin: 0;
            color: #0f172a !important;
            font-size: 18px;
            font-weight: 850;
        }

        .dark .spk-modal-title {
            color: #f8fafc !important;
        }

        .spk-modal-subtitle {
            margin: 5px 0 0;
            color: #64748b !important;
            font-size: 13px;
        }

        .dark .spk-modal-subtitle {
            color: #94a3b8 !important;
        }

        .spk-modal-body {
            padding: 21px;
            background: #ffffff !important;
        }

        .dark .spk-modal-body {
            background: #111827 !important;
        }

        .spk-modal-copy {
            margin: 0;
            color: #475569 !important;
            font-size: 14px;
            line-height: 1.65;
        }

        .dark .spk-modal-copy {
            color: #cbd5e1 !important;
        }

        .spk-modal-warning {
            margin-top: 13px;
            padding: 12px 14px;
            border: 1px solid #fecaca !important;
            border-radius: 11px;
            background: #fef2f2 !important;
            color: #b91c1c !important;
            font-size: 12px;
            line-height: 1.6;
            font-weight: 650;
        }

        .dark .spk-modal-warning {
            border-color: rgba(239, 68, 68, 0.2) !important;
            background: rgba(239, 68, 68, 0.08) !important;
            color: #fca5a5 !important;
        }

        .spk-modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 9px;
            padding: 15px 21px;
            border-top: 1px solid #e2e8f0 !important;
            background: #f8fafc !important;
        }

        .dark .spk-modal-footer {
            border-top-color: #29364b !important;
            background: #0f172a !important;
        }

        .spk-modal-cancel,
        .spk-modal-delete {
            display: inline-flex !important;
            min-height: 41px;
            align-items: center;
            justify-content: center;
            padding: 9px 16px;
            border-radius: 9px;
            font-size: 13px;
            font-weight: 750;
            cursor: pointer;
            transition:
                border-color 150ms ease,
                background 150ms ease,
                color 150ms ease;
        }

        .spk-modal-cancel {
            border: 1px solid #cbd5e1 !important;
            background: #ffffff !important;
            color: #475569 !important;
        }

        .spk-modal-cancel:hover {
            background: #f1f5f9 !important;
        }

        .dark .spk-modal-cancel {
            border-color: #334155 !important;
            background: #1e293b !important;
            color: #e2e8f0 !important;
        }

        .dark .spk-modal-cancel:hover {
            background: #273449 !important;
        }

        .spk-modal-delete {
            border: 1px solid #dc2626 !important;
            background: #dc2626 !important;
            color: #ffffff !important;
        }

        .spk-modal-delete:hover {
            border-color: #b91c1c !important;
            background: #b91c1c !important;
        }

        /*
        |--------------------------------------------------------------------------
        | Responsive
        |--------------------------------------------------------------------------
        */
        @media (max-width: 1199px) {
            .spk-index-table-wrap {
                display: none;
            }

            .spk-index-mobile-list {
                display: block;
            }
        }

        @media (max-width: 767px) {
            .spk-index {
                padding-top: 7px;
                padding-bottom: 24px;
            }

            .spk-index-stack > * + * {
                margin-top: 16px;
            }

            .spk-index-header {
                align-items: stretch;
                flex-direction: column;
            }

            .spk-index-header-actions {
                display: grid;
                grid-template-columns: 1fr;
            }

            .spk-index-title {
                font-size: 27px;
            }

            .spk-index-summary {
                align-items: flex-start;
                flex-direction: column;
                padding: 18px;
            }

            .spk-index-summary-main {
                align-items: flex-start;
            }

            .spk-index-summary-badges {
                justify-content: flex-start;
            }

            .spk-index-list-head {
                align-items: flex-start;
                flex-direction: column;
                padding: 18px;
            }

            .spk-index-mobile-meta {
                grid-template-columns: 1fr;
            }

            .spk-modal {
                align-items: flex-end;
                padding: 10px;
            }

            .spk-modal-panel {
                max-width: none;
            }

            .spk-modal-footer {
                flex-direction: column-reverse;
            }

            .spk-modal-footer form,
            .spk-modal-cancel,
            .spk-modal-delete {
                width: 100%;
            }
        }
    </style>

    <div class="spk-index">
        <div class="spk-index-stack">

            {{-- ========================================================
                PAGE HEADER
            ======================================================== --}}
            <header class="spk-index-header">

                <div class="spk-index-header-copy">

                    <div class="spk-index-eyebrow">
                        <span class="spk-index-eyebrow-dot"></span>

                        @if ($isAssessment)
                            {{ $assessmentLabel }}
                            Speaking Assessment
                        @else
                            Speaking Lesson
                        @endif
                    </div>

                    <h1 class="spk-index-title">
                        Speaking Tasks
                    </h1>

                    <p class="spk-index-subtitle">
                        @if ($isAssessment)
                            Manage an individual fable or short-story
                            presentation for one student, with a fixed
                            duration of 2–3 minutes and AI evaluation.
                        @else
                            Manage pair speaking activities, Student A and
                            Student B roles, conversation duration,
                            supporting content, and AI evaluation.
                        @endif
                    </p>
                </div>

                <div class="spk-index-header-actions">

                    <a
                        href="{{ route('admin.learning') }}"
                        class="spk-index-btn spk-index-btn-secondary">

                        <span aria-hidden="true">
                            ←
                        </span>

                        Back to Learning
                    </a>

                    <a
                        href="{{ route(
                            'admin.speaking-materials.create',
                            $lesson->id
                        ) }}"
                        class="spk-index-btn spk-index-btn-primary">

                        <span aria-hidden="true">
                            +
                        </span>

                        @if ($isAssessment)
                            Add Individual Assessment
                        @else
                            Add Speaking Task
                        @endif
                    </a>
                </div>
            </header>

            {{-- ========================================================
                SUCCESS MESSAGE
            ======================================================== --}}
            @if (session('success'))
                <div
                    class="spk-index-alert
                    spk-index-alert-success">

                    <div
                        class="spk-index-alert-icon"
                        aria-hidden="true">

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
                <div
                    class="spk-index-alert
                    spk-index-alert-error">

                    <div
                        class="spk-index-alert-icon"
                        aria-hidden="true">

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
            <section
                class="spk-index-card
                spk-index-summary">

                <div class="spk-index-summary-main">

                    <div
                        class="spk-index-summary-icon"
                        aria-hidden="true">

                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.9">

                            <rect
                                x="9"
                                y="2"
                                width="6"
                                height="11"
                                rx="3">
                            </rect>

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M5 10a7 7 0 0 0 14 0M12 17v4M9 21h6">
                            </path>
                        </svg>
                    </div>

                    <div class="spk-index-summary-copy">

                        <p class="spk-index-summary-label">
                            Current Lesson
                        </p>

                        <h2 class="spk-index-summary-title">
                            {{ $lesson->title }}
                        </h2>

                        @if ($lesson->description)
                            <p class="spk-index-summary-description">
                                {{ $lesson->description }}
                            </p>
                        @endif
                    </div>
                </div>

                <div class="spk-index-summary-badges">

                    @if ($isAssessment)
                        <span
                            class="spk-index-badge
                            spk-index-badge-purple">

                            {{ $assessmentLabel }}
                        </span>

                        <span
                            class="spk-index-badge
                            spk-index-badge-green">

                            Individual
                        </span>

                        <span
                            class="spk-index-badge
                            spk-index-badge-neutral">

                            1 Student
                        </span>
                    @else
                        <span
                            class="spk-index-badge
                            spk-index-badge-purple">

                            Pair Work
                        </span>

                        <span
                            class="spk-index-badge
                            spk-index-badge-neutral">

                            2 Students
                        </span>
                    @endif

                    <span
                        class="spk-index-badge
                        spk-index-badge-blue">

                        Speaking
                    </span>

                    <span
                        class="spk-index-badge
                        spk-index-badge-neutral">

                        {{ $taskCount }}

                        {{ $taskCount === 1
                            ? 'Task'
                            : 'Tasks' }}
                    </span>
                </div>
            </section>

            {{-- ========================================================
                TASK LIST
            ======================================================== --}}
            <section class="spk-index-card">

                <div class="spk-index-list-head">

                    <div>
                        <h3 class="spk-index-list-title">
                            Speaking Task List
                        </h3>

                        <p class="spk-index-list-description">
                            @if ($isAssessment)
                                Individual speaking assessments available
                                for this lesson.
                            @else
                                Pair speaking activities available
                                for this lesson.
                            @endif
                        </p>
                    </div>

                    @if ($taskCount > 0)
                        <span
                            class="spk-index-badge
                            spk-index-badge-neutral">

                            {{ $taskCount }}

                            {{ $taskCount === 1
                                ? 'Task'
                                : 'Tasks' }}
                        </span>
                    @endif
                </div>

                @if ($materials->isNotEmpty())

                    {{-- ====================================================
                        DESKTOP TABLE
                    ==================================================== --}}
                    <div class="spk-index-table-wrap">

                        <table class="spk-index-table">

                            <thead>
                                <tr>
                                    <th style="width: 23%;">
                                        Task
                                    </th>

                                    <th style="width: 29%;">
                                        Instruction
                                    </th>

                                    <th style="width: 12%;">
                                        Duration
                                    </th>

                                    <th style="width: 12%;">
                                        Mode
                                    </th>

                                    <th style="width: 12%;">
                                        AI Evaluation
                                    </th>

                                    <th style="width: 12%;">
                                        Actions
                                    </th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($materials as $material)
                                    @php
                                        /*
                                        |--------------------------------------------------------------------------
                                        | Material Display Context
                                        |--------------------------------------------------------------------------
                                        |
                                        | Assessment lama mungkin masih tersimpan
                                        | sebagai pair dan berdurasi selain 2–3 menit.
                                        | Pada halaman ini tetap ditampilkan sebagai
                                        | individual 2–3 menit.
                                        |
                                        */
                                        $minimumMinutes = $isAssessment
                                            ? 2
                                            : (
                                                $material->min_duration
                                                    ? (int) ceil(
                                                        $material->min_duration / 60
                                                    )
                                                    : null
                                            );

                                        $maximumMinutes = $isAssessment
                                            ? 3
                                            : (
                                                $material->max_duration
                                                    ? (int) ceil(
                                                        $material->max_duration / 60
                                                    )
                                                    : null
                                            );

                                        $isIndividual =
                                            $isAssessment ||
                                            !$material->is_pair_work;

                                        $aiEnabled =
                                            $isAssessment ||
                                            $material->ai_evaluation_enabled;
                                    @endphp

                                    <tr>

                                        {{-- TASK --}}
                                        <td>
                                            <div class="spk-index-task">

                                                @if ($material->image)
                                                    <div
                                                        class="spk-index-task-image">

                                                        <img
                                                            src="{{ asset(
                                                                'storage/' .
                                                                $material->image
                                                            ) }}"
                                                            alt="{{ $material->title }}">
                                                    </div>
                                                @else
                                                    <div
                                                        class="spk-index-task-placeholder"
                                                        aria-hidden="true">

                                                        <svg
                                                            viewBox="0 0 24 24"
                                                            fill="none"
                                                            stroke="currentColor"
                                                            stroke-width="1.9">

                                                            <rect
                                                                x="9"
                                                                y="2"
                                                                width="6"
                                                                height="11"
                                                                rx="3">
                                                            </rect>

                                                            <path
                                                                stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                d="M5 10a7 7 0 0 0 14 0M12 17v4M9 21h6">
                                                            </path>
                                                        </svg>
                                                    </div>
                                                @endif

                                                <div class="spk-index-task-copy">

                                                    <h4 class="spk-index-task-title">
                                                        {{ $material->title }}
                                                    </h4>

                                                    <p class="spk-index-task-type">
                                                        @if ($isAssessment)
                                                            Individual Speaking Assessment
                                                        @elseif ($material->is_pair_work)
                                                            Pair Speaking Activity
                                                        @else
                                                            Individual Speaking Activity
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- INSTRUCTION --}}
                                        <td>
                                            <div class="spk-index-instruction">
                                                {{ \Illuminate\Support\Str::limit(
                                                    $material->instruction,
                                                    135
                                                ) }}
                                            </div>
                                        </td>

                                        {{-- DURATION --}}
                                        <td>
                                            @if (
                                                $minimumMinutes &&
                                                $maximumMinutes
                                            )
                                                <span
                                                    class="spk-index-badge
                                                    spk-index-badge-blue">

                                                    {{ $minimumMinutes }}
                                                    –
                                                    {{ $maximumMinutes }}
                                                    min
                                                </span>
                                            @elseif ($minimumMinutes)
                                                <span
                                                    class="spk-index-badge
                                                    spk-index-badge-blue">

                                                    Min.
                                                    {{ $minimumMinutes }}
                                                    min
                                                </span>
                                            @elseif ($maximumMinutes)
                                                <span
                                                    class="spk-index-badge
                                                    spk-index-badge-blue">

                                                    Max.
                                                    {{ $maximumMinutes }}
                                                    min
                                                </span>
                                            @else
                                                <span
                                                    class="spk-index-badge
                                                    spk-index-badge-neutral">

                                                    Flexible
                                                </span>
                                            @endif
                                        </td>

                                        {{-- MODE --}}
                                        <td>
                                            @if ($isIndividual)
                                                <span
                                                    class="spk-index-badge
                                                    spk-index-badge-green">

                                                    1 Student
                                                </span>
                                            @else
                                                <span
                                                    class="spk-index-badge
                                                    spk-index-badge-purple">

                                                    2 Students
                                                </span>
                                            @endif
                                        </td>

                                        {{-- AI EVALUATION --}}
                                        <td>
                                            @if ($aiEnabled)
                                                <span
                                                    class="spk-index-badge
                                                    spk-index-badge-green">

                                                    ✓ Enabled
                                                </span>
                                            @else
                                                <span
                                                    class="spk-index-badge
                                                    spk-index-badge-red">

                                                    ✕ Disabled
                                                </span>
                                            @endif
                                        </td>

                                        {{-- ACTIONS --}}
                                        <td>
                                            <div class="spk-index-actions">

                                                <a
                                                    href="{{ route(
                                                        'admin.speaking-materials.edit',
                                                        $material->id
                                                    ) }}"
                                                    class="spk-index-action
                                                    spk-index-action-edit">

                                                    Edit
                                                </a>

                                                <button
                                                    type="button"
                                                    data-delete-url="{{ route(
                                                        'admin.speaking-materials.destroy',
                                                        $material->id
                                                    ) }}"
                                                    data-task-title="{{ $material->title }}"
                                                    onclick="openSpeakingDeleteModal(this)"
                                                    class="spk-index-action
                                                    spk-index-action-delete">

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
                    <div class="spk-index-mobile-list">

                        @foreach ($materials as $material)
                            @php
                                $minimumMinutes = $isAssessment
                                    ? 2
                                    : (
                                        $material->min_duration
                                            ? (int) ceil(
                                                $material->min_duration / 60
                                            )
                                            : null
                                    );

                                $maximumMinutes = $isAssessment
                                    ? 3
                                    : (
                                        $material->max_duration
                                            ? (int) ceil(
                                                $material->max_duration / 60
                                            )
                                            : null
                                    );

                                $isIndividual =
                                    $isAssessment ||
                                    !$material->is_pair_work;

                                $aiEnabled =
                                    $isAssessment ||
                                    $material->ai_evaluation_enabled;
                            @endphp

                            <article class="spk-index-mobile-item">

                                <div class="spk-index-mobile-head">

                                    @if ($material->image)
                                        <div class="spk-index-task-image">
                                            <img
                                                src="{{ asset(
                                                    'storage/' .
                                                    $material->image
                                                ) }}"
                                                alt="{{ $material->title }}">
                                        </div>
                                    @else
                                        <div
                                            class="spk-index-task-placeholder"
                                            aria-hidden="true">

                                            <svg
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.9">

                                                <rect
                                                    x="9"
                                                    y="2"
                                                    width="6"
                                                    height="11"
                                                    rx="3">
                                                </rect>

                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="M5 10a7 7 0 0 0 14 0M12 17v4M9 21h6">
                                                </path>
                                            </svg>
                                        </div>
                                    @endif

                                    <div class="spk-index-mobile-content">

                                        <h4 class="spk-index-task-title">
                                            {{ $material->title }}
                                        </h4>

                                        <p class="spk-index-task-type">
                                            @if ($isAssessment)
                                                Individual Speaking Assessment
                                            @elseif ($material->is_pair_work)
                                                Pair Speaking Activity
                                            @else
                                                Individual Speaking Activity
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                <div class="spk-index-mobile-instruction">
                                    {{ \Illuminate\Support\Str::limit(
                                        $material->instruction,
                                        190
                                    ) }}
                                </div>

                                <div class="spk-index-mobile-meta">

                                    {{-- DURATION --}}
                                    <div class="spk-index-mobile-meta-box">

                                        <p class="spk-index-mobile-meta-label">
                                            Duration
                                        </p>

                                        @if (
                                            $minimumMinutes &&
                                            $maximumMinutes
                                        )
                                            <span
                                                class="spk-index-badge
                                                spk-index-badge-blue">

                                                {{ $minimumMinutes }}
                                                –
                                                {{ $maximumMinutes }}
                                                min
                                            </span>
                                        @elseif ($minimumMinutes)
                                            <span
                                                class="spk-index-badge
                                                spk-index-badge-blue">

                                                Min.
                                                {{ $minimumMinutes }}
                                                min
                                            </span>
                                        @elseif ($maximumMinutes)
                                            <span
                                                class="spk-index-badge
                                                spk-index-badge-blue">

                                                Max.
                                                {{ $maximumMinutes }}
                                                min
                                            </span>
                                        @else
                                            <span
                                                class="spk-index-badge
                                                spk-index-badge-neutral">

                                                Flexible
                                            </span>
                                        @endif
                                    </div>

                                    {{-- MODE --}}
                                    <div class="spk-index-mobile-meta-box">

                                        <p class="spk-index-mobile-meta-label">
                                            Mode
                                        </p>

                                        @if ($isIndividual)
                                            <span
                                                class="spk-index-badge
                                                spk-index-badge-green">

                                                1 Student
                                            </span>
                                        @else
                                            <span
                                                class="spk-index-badge
                                                spk-index-badge-purple">

                                                2 Students
                                            </span>
                                        @endif
                                    </div>

                                    {{-- AI --}}
                                    <div class="spk-index-mobile-meta-box">

                                        <p class="spk-index-mobile-meta-label">
                                            AI Evaluation
                                        </p>

                                        @if ($aiEnabled)
                                            <span
                                                class="spk-index-badge
                                                spk-index-badge-green">

                                                ✓ Enabled
                                            </span>
                                        @else
                                            <span
                                                class="spk-index-badge
                                                spk-index-badge-red">

                                                ✕ Disabled
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="spk-index-mobile-actions">

                                    <a
                                        href="{{ route(
                                            'admin.speaking-materials.edit',
                                            $material->id
                                        ) }}"
                                        class="spk-index-action
                                        spk-index-action-edit">

                                        Edit
                                    </a>

                                    <button
                                        type="button"
                                        data-delete-url="{{ route(
                                            'admin.speaking-materials.destroy',
                                            $material->id
                                        ) }}"
                                        data-task-title="{{ $material->title }}"
                                        onclick="openSpeakingDeleteModal(this)"
                                        class="spk-index-action
                                        spk-index-action-delete">

                                        Delete
                                    </button>
                                </div>
                            </article>
                        @endforeach
                    </div>

                @else

                    {{-- ====================================================
                        EMPTY STATE
                    ==================================================== --}}
                    <div class="spk-index-empty">

                        <div
                            class="spk-index-empty-icon"
                            aria-hidden="true">

                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.9">

                                <rect
                                    x="9"
                                    y="2"
                                    width="6"
                                    height="11"
                                    rx="3">
                                </rect>

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M5 10a7 7 0 0 0 14 0M12 17v4M9 21h6">
                                </path>
                            </svg>
                        </div>

                        <h3 class="spk-index-empty-title">
                            @if ($isAssessment)
                                No Individual Assessment Yet
                            @else
                                No Speaking Tasks Yet
                            @endif
                        </h3>

                        <p class="spk-index-empty-text">
                            @if ($isAssessment)
                                Create the first individual fable or
                                short-story presentation for one student
                                with a fixed duration of 2–3 minutes and
                                AI evaluation.
                            @else
                                Create the first pair speaking activity
                                with Student A and Student B roles,
                                discussion points, duration, supporting
                                content, and AI evaluation.
                            @endif
                        </p>

                        <a
                            href="{{ route(
                                'admin.speaking-materials.create',
                                $lesson->id
                            ) }}"
                            class="spk-index-btn
                            spk-index-btn-primary">

                            <span aria-hidden="true">
                                +
                            </span>

                            @if ($isAssessment)
                                Create Individual Assessment
                            @else
                                Create First Speaking Task
                            @endif
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
        id="speakingDeleteModal"
        class="spk-modal"
        aria-hidden="true">

        <div
            id="speakingDeleteModalPanel"
            class="spk-modal-panel"
            role="dialog"
            aria-modal="true"
            aria-labelledby="speakingDeleteModalTitle">

            <div class="spk-modal-header">

                <h3
                    id="speakingDeleteModalTitle"
                    class="spk-modal-title">

                    Delete Speaking Task
                </h3>

                <p class="spk-modal-subtitle">
                    Please confirm this action.
                </p>
            </div>

            <div class="spk-modal-body">

                <p class="spk-modal-copy">
                    Are you sure you want to delete

                    <strong id="speakingDeleteTaskTitle">
                        this speaking task
                    </strong>?
                </p>

                <div class="spk-modal-warning">
                    Related submissions and uploaded audio may also be
                    deleted. This action cannot be undone.
                </div>
            </div>

            <div class="spk-modal-footer">

                <button
                    type="button"
                    onclick="closeSpeakingDeleteModal()"
                    class="spk-modal-cancel">

                    Cancel
                </button>

                <form
                    id="speakingDeleteForm"
                    method="POST">

                    @csrf
                    @method('DELETE')

                    <button
                        type="submit"
                        class="spk-modal-delete">

                        Delete Task
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ================================================================
        JAVASCRIPT
    ================================================================= --}}
    <script>
        function openSpeakingDeleteModal(button) {
            const modal =
                document.getElementById(
                    'speakingDeleteModal'
                );

            const panel =
                document.getElementById(
                    'speakingDeleteModalPanel'
                );

            const form =
                document.getElementById(
                    'speakingDeleteForm'
                );

            const taskTitle =
                document.getElementById(
                    'speakingDeleteTaskTitle'
                );

            if (
                !modal ||
                !panel ||
                !form
            ) {
                return;
            }

            const deleteUrl =
                button.dataset.deleteUrl;

            const title =
                button.dataset.taskTitle
                || 'this speaking task';

            form.action =
                deleteUrl;

            if (taskTitle) {
                taskTitle.textContent =
                    '"' + title + '"';
            }

            modal.classList.add(
                'is-open'
            );

            modal.setAttribute(
                'aria-hidden',
                'false'
            );

            document.body.style.overflow =
                'hidden';

            window.requestAnimationFrame(
                function () {
                    panel.classList.add(
                        'is-visible'
                    );
                }
            );
        }

        function closeSpeakingDeleteModal() {
            const modal =
                document.getElementById(
                    'speakingDeleteModal'
                );

            const panel =
                document.getElementById(
                    'speakingDeleteModalPanel'
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

            window.setTimeout(
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
                    closeSpeakingDeleteModal();
                }
            }
        );

        document.addEventListener(
            'DOMContentLoaded',
            function () {
                const modal =
                    document.getElementById(
                        'speakingDeleteModal'
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
                            closeSpeakingDeleteModal();
                        }
                    }
                );
            }
        );
    </script>
@endsection