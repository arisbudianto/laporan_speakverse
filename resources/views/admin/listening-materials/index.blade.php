@extends('layouts.admin')

@section('content')
    @php
        $isAssessment = in_array(
            $lesson->unit?->type,
            ['pretest', 'posttest'],
            true
        );

        $assessmentLabel = $isAssessment
            ? strtoupper($lesson->unit->type)
            : null;

        $totalMaterials = $materials->count();

        $totalQuestions = $materials->sum(function ($material) {
            return $material->questions->count();
        });
    @endphp

    <style>
        /* ============================================================
           LISTENING MATERIALS PAGE
           Scoped styles so global admin styles do not break this page.
        ============================================================ */
        .svl-page {
            --svl-bg: #ffffff;
            --svl-bg-soft: #f8fafc;
            --svl-bg-muted: #f1f5f9;
            --svl-border: #e2e8f0;
            --svl-border-strong: #cbd5e1;
            --svl-text: #0f172a;
            --svl-text-soft: #475569;
            --svl-text-muted: #64748b;
            --svl-shadow: 0 1px 2px rgba(15, 23, 42, .04), 0 12px 35px rgba(15, 23, 42, .06);

            width: 100%;
            max-width: 1500px;
            margin: 0 auto;
            color: var(--svl-text);
        }

        .dark .svl-page {
            --svl-bg: #111827;
            --svl-bg-soft: #172033;
            --svl-bg-muted: #1e293b;
            --svl-border: #334155;
            --svl-border-strong: #475569;
            --svl-text: #f8fafc;
            --svl-text-soft: #cbd5e1;
            --svl-text-muted: #94a3b8;
            --svl-shadow: 0 1px 2px rgba(0, 0, 0, .20), 0 14px 34px rgba(0, 0, 0, .18);
        }

        .svl-stack > * + * {
            margin-top: 22px;
        }

        .svl-card {
            background: var(--svl-bg) !important;
            border: 1px solid var(--svl-border) !important;
            border-radius: 24px;
            box-shadow: var(--svl-shadow);
        }

        .svl-page-header {
            position: relative;
            overflow: hidden;
            padding: 28px;
        }

        .svl-page-header::after {
            content: "";
            position: absolute;
            right: -90px;
            top: -100px;
            width: 260px;
            height: 260px;
            border-radius: 9999px;
            background: rgba(37, 99, 235, .08);
            filter: blur(18px);
            pointer-events: none;
        }

        .dark .svl-page-header::after {
            background: rgba(59, 130, 246, .10);
        }

        .svl-page-header-inner {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 28px;
        }

        .svl-heading {
            min-width: 0;
        }

        .svl-back {
            display: inline-flex !important;
            align-items: center;
            gap: 8px;
            margin-bottom: 18px;
            padding: 9px 13px;
            border-radius: 11px;
            border: 1px solid var(--svl-border) !important;
            background: var(--svl-bg-soft) !important;
            color: var(--svl-text-soft) !important;
            font-size: 13px;
            font-weight: 800;
            text-decoration: none !important;
            transition: .18s ease;
        }

        .svl-back:hover {
            background: var(--svl-bg-muted) !important;
            color: var(--svl-text) !important;
            border-color: var(--svl-border-strong) !important;
        }

        .svl-kicker {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
            padding: 7px 12px;
            border-radius: 999px;
            border: 1px solid #fde68a;
            background: #fffbeb !important;
            color: #b45309 !important;
            font-size: 12px;
            line-height: 1;
            font-weight: 900;
            letter-spacing: .03em;
            text-transform: uppercase;
        }

        .dark .svl-kicker {
            border-color: rgba(245, 158, 11, .30);
            background: rgba(245, 158, 11, .11) !important;
            color: #fcd34d !important;
        }

        .svl-title {
            margin: 0;
            color: var(--svl-text) !important;
            font-size: clamp(28px, 3vw, 42px);
            line-height: 1.08;
            font-weight: 950;
            letter-spacing: -.035em;
        }

        .svl-description {
            max-width: 720px;
            margin: 12px 0 0;
            color: var(--svl-text-soft) !important;
            font-size: 15px;
            line-height: 1.7;
        }

        /* Primary button deliberately uses direct CSS + !important
           because this project has global button/link styling. */
        .svl-primary {
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-height: 48px;
            padding: 12px 18px;
            border: 1px solid #2563eb !important;
            border-radius: 14px;
            background: #2563eb !important;
            color: #ffffff !important;
            font-size: 14px;
            font-weight: 900;
            line-height: 1;
            text-decoration: none !important;
            box-shadow: 0 10px 24px rgba(37, 99, 235, .24) !important;
            transition: transform .18s ease, background .18s ease, box-shadow .18s ease;
            white-space: nowrap;
        }

        .svl-primary:hover {
            transform: translateY(-1px);
            background: #1d4ed8 !important;
            border-color: #1d4ed8 !important;
            color: #ffffff !important;
            box-shadow: 0 13px 28px rgba(37, 99, 235, .30) !important;
        }

        .dark .svl-primary {
            background: #3b82f6 !important;
            border-color: #3b82f6 !important;
            color: #ffffff !important;
        }

        .dark .svl-primary:hover {
            background: #2563eb !important;
            border-color: #2563eb !important;
        }

        .svl-plus {
            display: inline-flex;
            width: 26px;
            height: 26px;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background: rgba(255, 255, 255, .17) !important;
            color: #fff !important;
            font-size: 19px;
            font-weight: 900;
        }

        /* ============================================================
           CURRENT LESSON
        ============================================================ */
        .svl-lesson {
            padding: 24px 26px;
        }

        .svl-lesson-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
        }

        .svl-lesson-info {
            display: flex;
            align-items: center;
            gap: 16px;
            min-width: 0;
        }

        .svl-icon {
            display: flex;
            width: 58px;
            height: 58px;
            flex: 0 0 58px;
            align-items: center;
            justify-content: center;
            border-radius: 18px;
            border: 1px solid #bfdbfe !important;
            background: #eff6ff !important;
            color: #2563eb !important;
            font-size: 25px;
        }

        .dark .svl-icon {
            border-color: rgba(59, 130, 246, .35) !important;
            background: rgba(59, 130, 246, .10) !important;
        }

        .svl-eyebrow {
            margin: 0;
            color: var(--svl-text-muted) !important;
            font-size: 12px;
            font-weight: 900;
            letter-spacing: .055em;
            text-transform: uppercase;
        }

        .svl-lesson-title {
            margin: 4px 0 0;
            color: var(--svl-text) !important;
            font-size: 21px;
            line-height: 1.25;
            font-weight: 950;
        }

        .svl-lesson-description {
            margin: 6px 0 0;
            color: var(--svl-text-soft) !important;
            font-size: 14px;
            line-height: 1.55;
        }

        .svl-badges {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 8px;
        }

        .svl-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 30px;
            padding: 5px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 900;
            line-height: 1;
            white-space: nowrap;
        }

        .svl-badge-assessment {
            border: 1px solid #fde68a !important;
            background: #fffbeb !important;
            color: #a16207 !important;
        }

        .dark .svl-badge-assessment {
            border-color: rgba(245, 158, 11, .30) !important;
            background: rgba(245, 158, 11, .10) !important;
            color: #fcd34d !important;
        }

        .svl-badge-blue {
            border: 1px solid #bfdbfe !important;
            background: #eff6ff !important;
            color: #1d4ed8 !important;
        }

        .dark .svl-badge-blue {
            border-color: rgba(59, 130, 246, .30) !important;
            background: rgba(59, 130, 246, .10) !important;
            color: #93c5fd !important;
        }

        .svl-badge-slate {
            border: 1px solid #e2e8f0 !important;
            background: #f1f5f9 !important;
            color: #334155 !important;
        }

        .dark .svl-badge-slate {
            border-color: #475569 !important;
            background: #1e293b !important;
            color: #e2e8f0 !important;
        }

        .svl-badge-green {
            border: 1px solid #a7f3d0 !important;
            background: #ecfdf5 !important;
            color: #047857 !important;
        }

        .dark .svl-badge-green {
            border-color: rgba(16, 185, 129, .30) !important;
            background: rgba(16, 185, 129, .10) !important;
            color: #6ee7b7 !important;
        }

        /* ============================================================
           MATERIAL LIST
        ============================================================ */
        .svl-list {
            overflow: hidden;
        }

        .svl-list-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            padding: 22px 26px;
            border-bottom: 1px solid var(--svl-border);
        }

        .svl-list-title {
            margin: 0;
            color: var(--svl-text) !important;
            font-size: 20px;
            line-height: 1.3;
            font-weight: 950;
        }

        .svl-list-description {
            margin: 5px 0 0;
            color: var(--svl-text-soft) !important;
            font-size: 14px;
            line-height: 1.5;
        }

        .svl-table-wrap {
            width: 100%;
            overflow-x: auto;
        }

        .svl-table {
            width: 100%;
            min-width: 900px;
            border-collapse: collapse;
        }

        .svl-table th {
            padding: 15px 26px;
            background: #f8fafc !important;
            color: #64748b !important;
            font-size: 12px;
            font-weight: 900;
            letter-spacing: .045em;
            text-align: left;
            text-transform: uppercase;
        }

        .dark .svl-table th {
            background: #0f172a !important;
            color: #94a3b8 !important;
        }

        .svl-table th:last-child {
            text-align: right;
        }

        .svl-table td {
            padding: 19px 26px;
            border-top: 1px solid var(--svl-border);
            vertical-align: middle;
        }

        .svl-table tbody tr {
            background: var(--svl-bg) !important;
            transition: background .18s ease;
        }

        .svl-table tbody tr:hover {
            background: var(--svl-bg-soft) !important;
        }

        .svl-material-cell {
            display: flex;
            align-items: center;
            gap: 13px;
            min-width: 260px;
        }

        .svl-material-icon {
            display: flex;
            width: 44px;
            height: 44px;
            flex: 0 0 44px;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            background: #fef3c7 !important;
            color: #92400e !important;
            font-size: 20px;
        }

        .dark .svl-material-icon {
            background: rgba(245, 158, 11, .10) !important;
            color: #fcd34d !important;
        }

        .svl-material-name {
            margin: 0;
            color: var(--svl-text) !important;
            font-size: 15px;
            font-weight: 950;
            line-height: 1.35;
        }

        .svl-material-meta {
            margin: 4px 0 0;
            color: var(--svl-text-muted) !important;
            font-size: 12px;
            font-weight: 700;
        }

        .svl-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            flex-wrap: wrap;
            gap: 8px;
        }

        .svl-action {
            display: inline-flex !important;
            min-height: 38px;
            align-items: center;
            justify-content: center;
            padding: 8px 13px;
            border-radius: 11px;
            font-size: 13px;
            font-weight: 900;
            line-height: 1;
            text-decoration: none !important;
            transition: .18s ease;
            cursor: pointer;
            white-space: nowrap;
        }

        .svl-action-question {
            border: 1px solid #a7f3d0 !important;
            background: #ecfdf5 !important;
            color: #047857 !important;
        }

        .svl-action-question:hover {
            border-color: #059669 !important;
            background: #059669 !important;
            color: #ffffff !important;
        }

        .dark .svl-action-question {
            border-color: rgba(16, 185, 129, .35) !important;
            background: rgba(16, 185, 129, .12) !important;
            color: #6ee7b7 !important;
        }

        .dark .svl-action-question:hover {
            border-color: #059669 !important;
            background: #059669 !important;
            color: #ffffff !important;
        }

        .svl-action-edit {
            border: 1px solid #bfdbfe !important;
            background: #eff6ff !important;
            color: #1d4ed8 !important;
        }

        .svl-action-edit:hover {
            border-color: #2563eb !important;
            background: #2563eb !important;
            color: #ffffff !important;
        }

        .dark .svl-action-edit {
            border-color: rgba(59, 130, 246, .35) !important;
            background: rgba(59, 130, 246, .12) !important;
            color: #93c5fd !important;
        }

        .dark .svl-action-edit:hover {
            border-color: #2563eb !important;
            background: #2563eb !important;
            color: #ffffff !important;
        }

        .svl-action-delete {
            border: 1px solid #fecaca !important;
            background: #fef2f2 !important;
            color: #dc2626 !important;
        }

        .svl-action-delete:hover {
            border-color: #dc2626 !important;
            background: #dc2626 !important;
            color: #ffffff !important;
        }

        .dark .svl-action-delete {
            border-color: rgba(239, 68, 68, .35) !important;
            background: rgba(239, 68, 68, .12) !important;
            color: #fca5a5 !important;
        }

        .dark .svl-action-delete:hover {
            border-color: #dc2626 !important;
            background: #dc2626 !important;
            color: #ffffff !important;
        }

        /* ============================================================
           MOBILE CARDS
        ============================================================ */
        .svl-mobile-list {
            display: none;
        }

        .svl-mobile-item {
            padding: 18px;
            border-top: 1px solid var(--svl-border);
        }

        .svl-mobile-item:first-child {
            border-top: 0;
        }

        .svl-mobile-top {
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .svl-mobile-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
            margin-top: 15px;
        }

        .svl-mobile-stat {
            min-width: 0;
            padding: 12px;
            border: 1px solid var(--svl-border);
            border-radius: 14px;
            background: var(--svl-bg-soft) !important;
        }

        .svl-mobile-label {
            display: block;
            margin-bottom: 8px;
            color: var(--svl-text-muted) !important;
            font-size: 10px;
            font-weight: 900;
            letter-spacing: .05em;
            text-transform: uppercase;
        }

        .svl-mobile-actions {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 8px;
            margin-top: 14px;
        }

        /* ============================================================
           EMPTY STATE
        ============================================================ */
        .svl-empty {
            padding: 58px 24px;
            text-align: center;
        }

        .svl-empty-icon {
            display: flex;
            width: 72px;
            height: 72px;
            margin: 0 auto;
            align-items: center;
            justify-content: center;
            border-radius: 22px;
            background: #fef3c7 !important;
            font-size: 32px;
        }

        .dark .svl-empty-icon {
            background: rgba(245, 158, 11, .10) !important;
        }

        .svl-empty h3 {
            margin: 20px 0 0;
            color: var(--svl-text) !important;
            font-size: 22px;
            font-weight: 950;
        }

        .svl-empty p {
            max-width: 500px;
            margin: 9px auto 0;
            color: var(--svl-text-soft) !important;
            font-size: 14px;
            line-height: 1.7;
        }

        .svl-empty .svl-primary {
            margin-top: 22px;
        }

        /* ============================================================
           SUCCESS
        ============================================================ */
        .svl-success {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 14px 16px;
            border: 1px solid #a7f3d0;
            border-radius: 16px;
            background: #ecfdf5 !important;
            color: #065f46 !important;
        }

        .dark .svl-success {
            border-color: rgba(16, 185, 129, .30);
            background: rgba(16, 185, 129, .10) !important;
            color: #a7f3d0 !important;
        }

        .svl-success-icon {
            display: flex;
            width: 36px;
            height: 36px;
            flex: 0 0 36px;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            background: rgba(16, 185, 129, .14) !important;
            font-weight: 950;
        }

        .svl-success-title {
            margin: 0;
            font-size: 14px;
            font-weight: 950;
        }

        .svl-success-message {
            margin: 3px 0 0;
            font-size: 13px;
            line-height: 1.5;
        }

        /* ============================================================
           MODAL
        ============================================================ */
        .svl-modal {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 16px;
            background: rgba(2, 6, 23, .72) !important;
            backdrop-filter: blur(5px);
        }

        .svl-modal.is-open {
            display: flex;
        }

        .svl-modal-card {
            width: 100%;
            max-width: 440px;
            overflow: hidden;
            border: 1px solid var(--svl-border);
            border-radius: 22px;
            background: var(--svl-bg) !important;
            box-shadow: 0 28px 70px rgba(0, 0, 0, .30);
        }

        .svl-modal-head,
        .svl-modal-body,
        .svl-modal-foot {
            padding: 20px 22px;
        }

        .svl-modal-head {
            border-bottom: 1px solid var(--svl-border);
        }

        .svl-modal-title-row {
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .svl-warning-icon {
            display: flex;
            width: 42px;
            height: 42px;
            flex: 0 0 42px;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: #fef2f2 !important;
            font-size: 19px;
        }

        .dark .svl-warning-icon {
            background: rgba(239, 68, 68, .12) !important;
        }

        .svl-modal-title {
            margin: 0;
            color: var(--svl-text) !important;
            font-size: 18px;
            font-weight: 950;
        }

        .svl-modal-subtitle {
            margin: 4px 0 0;
            color: var(--svl-text-muted) !important;
            font-size: 13px;
        }

        .svl-modal-copy {
            margin: 0;
            color: var(--svl-text-soft) !important;
            font-size: 14px;
            line-height: 1.65;
        }

        .svl-danger-note {
            margin-top: 14px;
            padding: 13px 14px;
            border: 1px solid #fecaca;
            border-radius: 13px;
            background: #fef2f2 !important;
            color: #b91c1c !important;
            font-size: 13px;
            font-weight: 750;
            line-height: 1.55;
        }

        .dark .svl-danger-note {
            border-color: rgba(239, 68, 68, .30);
            background: rgba(239, 68, 68, .10) !important;
            color: #fca5a5 !important;
        }

        .svl-modal-foot {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            border-top: 1px solid var(--svl-border);
            background: var(--svl-bg-soft) !important;
        }

        .svl-modal-cancel,
        .svl-modal-delete {
            display: inline-flex !important;
            min-height: 42px;
            align-items: center;
            justify-content: center;
            padding: 9px 16px;
            border-radius: 11px;
            font-size: 13px;
            font-weight: 900;
            transition: .18s ease;
        }

        .svl-modal-cancel {
            border: 1px solid var(--svl-border-strong) !important;
            background: var(--svl-bg) !important;
            color: var(--svl-text-soft) !important;
        }

        .svl-modal-cancel:hover {
            background: var(--svl-bg-muted) !important;
            color: var(--svl-text) !important;
        }

        .svl-modal-delete {
            border: 1px solid #dc2626 !important;
            background: #dc2626 !important;
            color: #ffffff !important;
        }

        .svl-modal-delete:hover {
            background: #b91c1c !important;
            border-color: #b91c1c !important;
            color: #ffffff !important;
        }

        /* ============================================================
           RESPONSIVE
        ============================================================ */
        @media (max-width: 1023px) {
            .svl-page-header-inner {
                align-items: stretch;
                flex-direction: column;
                gap: 20px;
            }

            .svl-primary {
                width: 100%;
            }

            .svl-lesson-inner {
                align-items: flex-start;
                flex-direction: column;
            }

            .svl-badges {
                justify-content: flex-start;
            }
        }

        @media (max-width: 767px) {
            .svl-stack > * + * {
                margin-top: 16px;
            }

            .svl-page-header,
            .svl-lesson {
                padding: 18px;
                border-radius: 20px;
            }

            .svl-title {
                font-size: 30px;
            }

            .svl-description {
                font-size: 14px;
                line-height: 1.6;
            }

            .svl-lesson-info {
                align-items: flex-start;
            }

            .svl-icon {
                width: 50px;
                height: 50px;
                flex-basis: 50px;
                border-radius: 15px;
                font-size: 21px;
            }

            .svl-list {
                border-radius: 20px;
            }

            .svl-list-head {
                align-items: flex-start;
                padding: 18px;
                flex-direction: column;
            }

            .svl-table-wrap {
                display: none;
            }

            .svl-mobile-list {
                display: block;
            }

            .svl-mobile-actions .svl-action {
                width: 100%;
                padding-inline: 8px;
                font-size: 12px;
            }

            .svl-modal-foot {
                flex-direction: column-reverse;
            }

            .svl-modal-cancel,
            .svl-modal-delete,
            .svl-modal-foot form {
                width: 100%;
            }
        }

        @media (max-width: 430px) {
            .svl-page-header,
            .svl-lesson,
            .svl-list-head,
            .svl-mobile-item {
                padding-left: 15px;
                padding-right: 15px;
            }

            .svl-title {
                font-size: 27px;
            }

            .svl-mobile-grid {
                grid-template-columns: 1fr;
            }

            .svl-mobile-actions {
                grid-template-columns: 1fr;
            }

            .svl-badge {
                padding-inline: 10px;
            }
        }
    </style>

    <div class="svl-page">
        <div class="svl-stack">

            @if (session('success'))
                <div class="svl-success">
                    <div class="svl-success-icon">✓</div>

                    <div>
                        <p class="svl-success-title">Success</p>
                        <p class="svl-success-message">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            {{-- ========================================================
                PAGE HEADER
            ======================================================== --}}
            <section class="svl-card svl-page-header">
                <div class="svl-page-header-inner">

                    <div class="svl-heading">
                        <a href="{{ route('admin.learning') }}" class="svl-back">
                            <span>←</span>
                            <span>Back to Learning</span>
                        </a>

                        <div class="svl-kicker">
                            <span>🎧</span>

                            @if ($isAssessment)
                                <span>{{ $assessmentLabel }} Listening Assessment</span>
                            @else
                                <span>Listening Lesson</span>
                            @endif
                        </div>

                        <h1 class="svl-title">Listening Materials</h1>

                        <p class="svl-description">
                            Manage listening audio materials and their related assessment questions for this lesson.
                        </p>
                    </div>

                    <div>
                        <a
                            href="{{ route('admin.listening-materials.create', $lesson->id) }}"
                            class="svl-primary">
                            <span class="svl-plus">+</span>
                            <span>Add Listening Material</span>
                        </a>
                    </div>

                </div>
            </section>

            {{-- ========================================================
                CURRENT LESSON
            ======================================================== --}}
            <section class="svl-card svl-lesson">
                <div class="svl-lesson-inner">

                    <div class="svl-lesson-info">
                        <div class="svl-icon">🎧</div>

                        <div style="min-width: 0;">
                            <p class="svl-eyebrow">Current Lesson</p>

                            <h2 class="svl-lesson-title">
                                {{ $lesson->title }}
                            </h2>

                            @if ($lesson->description)
                                <p class="svl-lesson-description">
                                    {{ $lesson->description }}
                                </p>
                            @endif
                        </div>
                    </div>

                    <div class="svl-badges">
                        @if ($isAssessment)
                            <span class="svl-badge svl-badge-assessment">
                                {{ $assessmentLabel }}
                            </span>
                        @endif

                        <span class="svl-badge svl-badge-blue">
                            Listening
                        </span>

                        <span class="svl-badge svl-badge-slate">
                            {{ $totalMaterials }}
                            {{ Str::plural('Material', $totalMaterials) }}
                        </span>

                        <span class="svl-badge svl-badge-green">
                            {{ $totalQuestions }}
                            {{ Str::plural('Question', $totalQuestions) }}
                        </span>
                    </div>

                </div>
            </section>

            {{-- ========================================================
                MATERIAL LIST
            ======================================================== --}}
            <section class="svl-card svl-list">

                <div class="svl-list-head">
                    <div>
                        <h3 class="svl-list-title">
                            Listening Material List
                        </h3>

                        <p class="svl-list-description">
                            Audio materials and questions available for this lesson.
                        </p>
                    </div>

                    @if ($totalMaterials)
                        <span class="svl-badge svl-badge-slate">
                            {{ $totalMaterials }}
                            {{ Str::plural('Material', $totalMaterials) }}
                        </span>
                    @endif
                </div>

                @if ($materials->count())

                    {{-- DESKTOP --}}
                    <div class="svl-table-wrap">
                        <table class="svl-table">
                            <thead>
                                <tr>
                                    <th>Material</th>
                                    <th>Questions</th>
                                    <th>Audio</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($materials as $material)
                                    <tr>
                                        <td>
                                            <div class="svl-material-cell">
                                                <div class="svl-material-icon">
                                                    🎧
                                                </div>

                                                <div>
                                                    <p class="svl-material-name">
                                                        {{ $material->title }}
                                                    </p>

                                                    <p class="svl-material-meta">
                                                        Listening Audio Material
                                                    </p>
                                                </div>
                                            </div>
                                        </td>

                                        <td>
                                            <span class="svl-badge svl-badge-green">
                                                {{ $material->questions->count() }}
                                                {{ Str::plural(
                                                    'Question',
                                                    $material->questions->count()
                                                ) }}
                                            </span>
                                        </td>

                                        <td>
                                            @if (!empty($material->audio_file))
                                                <span class="svl-badge svl-badge-blue">
                                                    🎧 Available
                                                </span>
                                            @else
                                                <span class="svl-badge svl-badge-slate">
                                                    No Audio
                                                </span>
                                            @endif
                                        </td>

                                        <td>
                                            <div class="svl-actions">
                                                <a
                                                    href="{{ route(
                                                        'admin.listening-questions.index',
                                                        $material->id
                                                    ) }}"
                                                    class="svl-action svl-action-question">
                                                    Questions
                                                </a>

                                                <a
                                                    href="{{ route(
                                                        'admin.listening-materials.edit',
                                                        $material->id
                                                    ) }}"
                                                    class="svl-action svl-action-edit">
                                                    Edit
                                                </a>

                                                <button
                                                    type="button"
                                                    data-url="{{ route(
                                                        'admin.listening-materials.destroy',
                                                        $material->id
                                                    ) }}"
                                                    onclick="openDeleteModal(this.dataset.url)"
                                                    class="svl-action svl-action-delete">
                                                    Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- MOBILE --}}
                    <div class="svl-mobile-list">
                        @foreach ($materials as $material)
                            <article class="svl-mobile-item">

                                <div class="svl-mobile-top">
                                    <div class="svl-material-icon">
                                        🎧
                                    </div>

                                    <div style="min-width: 0;">
                                        <p class="svl-material-name">
                                            {{ $material->title }}
                                        </p>

                                        <p class="svl-material-meta">
                                            Listening Audio Material
                                        </p>
                                    </div>
                                </div>

                                <div class="svl-mobile-grid">
                                    <div class="svl-mobile-stat">
                                        <span class="svl-mobile-label">
                                            Questions
                                        </span>

                                        <span class="svl-badge svl-badge-green">
                                            {{ $material->questions->count() }}
                                            {{ Str::plural(
                                                'Question',
                                                $material->questions->count()
                                            ) }}
                                        </span>
                                    </div>

                                    <div class="svl-mobile-stat">
                                        <span class="svl-mobile-label">
                                            Audio
                                        </span>

                                        @if (!empty($material->audio_file))
                                            <span class="svl-badge svl-badge-blue">
                                                🎧 Available
                                            </span>
                                        @else
                                            <span class="svl-badge svl-badge-slate">
                                                No Audio
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="svl-mobile-actions">
                                    <a
                                        href="{{ route(
                                            'admin.listening-questions.index',
                                            $material->id
                                        ) }}"
                                        class="svl-action svl-action-question">
                                        Questions
                                    </a>

                                    <a
                                        href="{{ route(
                                            'admin.listening-materials.edit',
                                            $material->id
                                        ) }}"
                                        class="svl-action svl-action-edit">
                                        Edit
                                    </a>

                                    <button
                                        type="button"
                                        data-url="{{ route(
                                            'admin.listening-materials.destroy',
                                            $material->id
                                        ) }}"
                                        onclick="openDeleteModal(this.dataset.url)"
                                        class="svl-action svl-action-delete">
                                        Delete
                                    </button>
                                </div>

                            </article>
                        @endforeach
                    </div>

                @else
                    <div class="svl-empty">
                        <div class="svl-empty-icon">🎧</div>

                        <h3>No Listening Materials Yet</h3>

                        <p>
                            Create the first listening audio material for this lesson,
                            then add the related questions students must answer.
                        </p>

                        <a
                            href="{{ route(
                                'admin.listening-materials.create',
                                $lesson->id
                            ) }}"
                            class="svl-primary">
                            <span class="svl-plus">+</span>
                            <span>Create First Material</span>
                        </a>
                    </div>
                @endif

            </section>

        </div>
    </div>

    {{-- ================================================================
        DELETE MODAL
    ================================================================= --}}
    <div id="deleteModal" class="svl-modal">
        <div
            class="svl-page svl-modal-card"
            role="dialog"
            aria-modal="true"
            aria-labelledby="deleteModalTitle">

            <div class="svl-modal-head">
                <div class="svl-modal-title-row">
                    <div class="svl-warning-icon">⚠️</div>

                    <div>
                        <h3 id="deleteModalTitle" class="svl-modal-title">
                            Delete Listening Material
                        </h3>

                        <p class="svl-modal-subtitle">
                            Please confirm this action.
                        </p>
                    </div>
                </div>
            </div>

            <div class="svl-modal-body">
                <p class="svl-modal-copy">
                    Are you sure you want to delete this listening material?
                </p>

                <div class="svl-danger-note">
                    The audio file and related questions may also be deleted.
                    This action cannot be undone.
                </div>
            </div>

            <div class="svl-modal-foot">
                <button
                    type="button"
                    onclick="closeDeleteModal()"
                    class="svl-modal-cancel">
                    Cancel
                </button>

                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')

                    <button
                        type="submit"
                        class="svl-modal-delete">
                        Delete Material
                    </button>
                </form>
            </div>

        </div>
    </div>

    <script>
        function openDeleteModal(actionUrl) {
            const modal = document.getElementById('deleteModal');
            const form = document.getElementById('deleteForm');

            if (!modal || !form) {
                return;
            }

            form.action = actionUrl;
            modal.classList.add('is-open');
            document.body.style.overflow = 'hidden';
        }

        function closeDeleteModal() {
            const modal = document.getElementById('deleteModal');

            if (!modal) {
                return;
            }

            modal.classList.remove('is-open');
            document.body.style.overflow = '';
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeDeleteModal();
            }
        });

        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('deleteModal');

            if (!modal) {
                return;
            }

            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    closeDeleteModal();
                }
            });
        });
    </script>
@endsection
