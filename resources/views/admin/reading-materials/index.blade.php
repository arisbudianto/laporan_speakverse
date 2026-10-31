@extends('layouts.admin')

@section('content')
    @php
        $lessonTitle = $lesson->title ?? 'Reading';

        $unitTitle = $lesson->unit->title ?? null;

        $contextTitle = $unitTitle
            ? $unitTitle . ' — ' . $lessonTitle
            : $lessonTitle;
    @endphp

    <style>
        /*
        |--------------------------------------------------------------------------
        | Reading Materials Page
        |--------------------------------------------------------------------------
        | Scoped styles are used for flash messages so global admin styles
        | cannot create bright borders in dark mode.
        |--------------------------------------------------------------------------
        */

        .reading-flash {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            border-radius: 1rem;
            padding: 1rem 1.25rem;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        }

        .reading-flash-success {
            border: 1px solid #bbf7d0 !important;
            background: #f0fdf4 !important;
            color: #047857 !important;
        }

        .reading-flash-info {
            border: 1px solid #bfdbfe !important;
            background: #eff6ff !important;
            color: #1d4ed8 !important;
        }

        .reading-flash-icon {
            display: flex;
            width: 2rem;
            height: 2rem;
            flex: 0 0 2rem;
            align-items: center;
            justify-content: center;
            border-radius: 9999px;
            font-weight: 900;
        }

        .reading-flash-success .reading-flash-icon {
            background: #d1fae5 !important;
            color: #047857 !important;
        }

        .reading-flash-info .reading-flash-icon {
            background: #dbeafe !important;
            color: #1d4ed8 !important;
        }

        .dark .reading-flash-success {
            border-color: rgba(16, 185, 129, 0.18) !important;
            background: rgba(16, 185, 129, 0.08) !important;
            color: #6ee7b7 !important;
            box-shadow: none !important;
        }

        .dark .reading-flash-info {
            border-color: rgba(59, 130, 246, 0.18) !important;
            background: rgba(59, 130, 246, 0.08) !important;
            color: #93c5fd !important;
            box-shadow: none !important;
        }

        .dark .reading-flash-success .reading-flash-icon {
            background: rgba(16, 185, 129, 0.14) !important;
            color: #6ee7b7 !important;
        }

        .dark .reading-flash-info .reading-flash-icon {
            background: rgba(59, 130, 246, 0.14) !important;
            color: #93c5fd !important;
        }

        @media (max-width: 640px) {
            .reading-flash {
                padding: 0.9rem 1rem;
            }
        }

        /* ============================================================
           DELETE MODAL
           Scoped CSS prevents global admin styles from breaking the
           light/dark appearance of the modal.
        ============================================================ */
        .reading-delete-modal {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            background: rgba(2, 6, 23, 0.72) !important;
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }

        .reading-delete-modal.is-open {
            display: flex;
        }

        .reading-delete-panel {
            width: 100%;
            max-width: 31rem;
            overflow: hidden;
            border: 1px solid #e2e8f0 !important;
            border-radius: 1.35rem;
            background: #ffffff !important;
            box-shadow:
                0 32px 80px rgba(2, 6, 23, 0.24),
                0 8px 28px rgba(2, 6, 23, 0.10) !important;
            opacity: 0;
            transform: translateY(10px) scale(0.975);
            transition:
                opacity 180ms ease,
                transform 180ms ease;
        }

        .reading-delete-panel.is-visible {
            opacity: 1;
            transform: translateY(0) scale(1);
        }

        .reading-delete-header {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 1.4rem 1.5rem;
            border-bottom: 1px solid #e2e8f0 !important;
            background: #ffffff !important;
        }

        .reading-delete-icon {
            display: flex;
            width: 3rem;
            height: 3rem;
            flex: 0 0 3rem;
            align-items: center;
            justify-content: center;
            border-radius: 1rem;
            border: 1px solid #fecaca !important;
            background: #fef2f2 !important;
            color: #dc2626 !important;
            font-size: 1.25rem;
            font-weight: 950;
        }

        .reading-delete-title {
            margin: 0;
            color: #0f172a !important;
            font-size: 1.25rem;
            line-height: 1.4;
            font-weight: 950;
            letter-spacing: -0.02em;
        }

        .reading-delete-subtitle {
            margin: 0.3rem 0 0;
            color: #64748b !important;
            font-size: 0.875rem;
            line-height: 1.5;
        }

        .reading-delete-body {
            padding: 1.5rem;
            background: #ffffff !important;
        }

        .reading-delete-copy {
            margin: 0;
            color: #475569 !important;
            font-size: 0.95rem;
            line-height: 1.65;
        }

        .reading-delete-material {
            margin: 0.4rem 0 0;
            color: #0f172a !important;
            font-size: 1rem;
            line-height: 1.5;
            font-weight: 950;
            overflow-wrap: anywhere;
        }

        .reading-delete-warning {
            margin-top: 1.25rem;
            padding: 0.9rem 1rem;
            border: 1px solid #fecaca !important;
            border-radius: 0.9rem;
            background: #fff7f7 !important;
            color: #b91c1c !important;
            font-size: 0.875rem;
            line-height: 1.6;
            font-weight: 700;
        }

        .reading-delete-footer {
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
            padding: 1rem 1.5rem 1.25rem;
            border-top: 1px solid #e2e8f0 !important;
            background: #f8fafc !important;
        }

        .reading-delete-cancel,
        .reading-delete-confirm {
            display: inline-flex !important;
            min-height: 2.75rem;
            align-items: center;
            justify-content: center;
            border-radius: 0.8rem;
            padding: 0.7rem 1.15rem;
            font-size: 0.875rem;
            font-weight: 900;
            line-height: 1;
            text-decoration: none !important;
            transition:
                background 160ms ease,
                border-color 160ms ease,
                color 160ms ease,
                transform 160ms ease,
                box-shadow 160ms ease;
        }

        .reading-delete-cancel {
            border: 1px solid #cbd5e1 !important;
            background: #ffffff !important;
            color: #334155 !important;
        }

        .reading-delete-cancel:hover {
            border-color: #94a3b8 !important;
            background: #f1f5f9 !important;
            color: #0f172a !important;
        }

        .reading-delete-confirm {
            border: 1px solid #dc2626 !important;
            background: #dc2626 !important;
            color: #ffffff !important;
            box-shadow: 0 8px 18px rgba(220, 38, 38, 0.18) !important;
        }

        .reading-delete-confirm:hover {
            transform: translateY(-1px);
            border-color: #b91c1c !important;
            background: #b91c1c !important;
            color: #ffffff !important;
            box-shadow: 0 10px 22px rgba(220, 38, 38, 0.24) !important;
        }

        .dark .reading-delete-panel {
            border-color: #273449 !important;
            background: #111827 !important;
            box-shadow:
                0 34px 90px rgba(0, 0, 0, 0.55),
                0 10px 32px rgba(0, 0, 0, 0.34) !important;
        }

        .dark .reading-delete-header {
            border-bottom-color: #273449 !important;
            background: #111827 !important;
        }

        .dark .reading-delete-icon {
            border-color: rgba(248, 113, 113, 0.18) !important;
            background: rgba(239, 68, 68, 0.10) !important;
            color: #f87171 !important;
        }

        .dark .reading-delete-title {
            color: #f8fafc !important;
        }

        .dark .reading-delete-subtitle {
            color: #94a3b8 !important;
        }

        .dark .reading-delete-body {
            background: #111827 !important;
        }

        .dark .reading-delete-copy {
            color: #cbd5e1 !important;
        }

        .dark .reading-delete-material {
            color: #ffffff !important;
        }

        .dark .reading-delete-warning {
            border-color: rgba(248, 113, 113, 0.16) !important;
            background: rgba(239, 68, 68, 0.075) !important;
            color: #fca5a5 !important;
        }

        .dark .reading-delete-footer {
            border-top-color: #273449 !important;
            background: #0d1422 !important;
        }

        .dark .reading-delete-cancel {
            border-color: #334155 !important;
            background: #1e293b !important;
            color: #e2e8f0 !important;
        }

        .dark .reading-delete-cancel:hover {
            border-color: #475569 !important;
            background: #273449 !important;
            color: #ffffff !important;
        }

        .dark .reading-delete-confirm {
            border-color: #ef4444 !important;
            background: #ef4444 !important;
            color: #ffffff !important;
            box-shadow: 0 8px 20px rgba(239, 68, 68, 0.16) !important;
        }

        .dark .reading-delete-confirm:hover {
            border-color: #dc2626 !important;
            background: #dc2626 !important;
            color: #ffffff !important;
        }

        @media (max-width: 640px) {
            .reading-delete-modal {
                align-items: flex-end;
                padding: 0.75rem;
            }

            .reading-delete-panel {
                max-width: none;
                border-radius: 1.25rem;
            }

            .reading-delete-header,
            .reading-delete-body {
                padding-left: 1.1rem;
                padding-right: 1.1rem;
            }

            .reading-delete-footer {
                flex-direction: column-reverse;
                padding: 0.9rem 1.1rem 1.1rem;
            }

            .reading-delete-footer form,
            .reading-delete-cancel,
            .reading-delete-confirm {
                width: 100%;
            }
        }

    </style>

    <div class="mx-auto max-w-[1500px] space-y-6">

        {{-- ============================================================
            PAGE HEADER
        ============================================================ --}}
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="min-w-0">
                <h1 class="text-3xl font-black text-slate-900 dark:text-white">
                    Reading Materials
                </h1>

                <p class="mt-1 text-slate-500 dark:text-slate-400">
                    Manage reading passages and questions for this lesson.
                </p>
            </div>

            <a
                href="{{ route('admin.reading-materials.create', $lesson->id) }}"
                class="inline-flex self-start items-center justify-center
                       rounded-xl bg-blue-600 px-5 py-3
                       text-sm font-bold text-white
                       shadow-sm transition
                       hover:bg-blue-700
                       focus:outline-none focus:ring-2
                       focus:ring-blue-500 focus:ring-offset-2
                       md:self-auto
                       dark:focus:ring-offset-slate-900">

                <span class="mr-2 text-lg leading-none">
                    +
                </span>

                Add Reading Passage
            </a>
        </div>

        {{-- ============================================================
            FLASH MESSAGES
        ============================================================ --}}
        @if (session('success'))
            <div class="reading-flash reading-flash-success">
                <div class="reading-flash-icon" aria-hidden="true">
                    ✓
                </div>

                <p class="pt-1 font-semibold leading-6">
                    {{ session('success') }}
                </p>
            </div>
        @endif

        @if (session('info'))
            <div class="reading-flash reading-flash-info">
                <div class="reading-flash-icon" aria-hidden="true">
                    i
                </div>

                <p class="pt-1 font-semibold leading-6">
                    {{ session('info') }}
                </p>
            </div>
        @endif

        {{-- ============================================================
            LESSON INFORMATION
        ============================================================ --}}
        <section
            class="overflow-hidden rounded-2xl
                   border border-slate-200
                   bg-white shadow-sm
                   dark:border-slate-700
                   dark:bg-slate-800">

            <div
                class="flex flex-col gap-4
                       px-5 py-5
                       sm:flex-row sm:items-center sm:justify-between
                       md:px-6">

                <div class="min-w-0">
                    <p
                        class="text-xs font-black
                               uppercase tracking-wider
                               text-blue-600
                               dark:text-blue-400">

                        Reading Lesson
                    </p>

                    <h2
                        class="mt-1 break-words text-xl font-black
                               text-slate-900 dark:text-white">

                        {{ $contextTitle }}
                    </h2>
                </div>

                <span
                    class="inline-flex self-start
                           items-center justify-center
                           whitespace-nowrap rounded-full
                           bg-slate-100 px-4 py-2
                           text-sm font-bold text-slate-700
                           sm:self-auto
                           dark:bg-slate-700
                           dark:text-slate-200">

                    {{ $materials->count() }}

                    {{ \Illuminate\Support\Str::plural(
                        'Passage',
                        $materials->count()
                    ) }}
                </span>
            </div>
        </section>

        {{-- ============================================================
            PASSAGE LIST
        ============================================================ --}}
        <section
            class="overflow-hidden rounded-2xl
                   border border-slate-200
                   bg-white shadow-sm
                   dark:border-slate-700
                   dark:bg-slate-800">

            {{-- LIST HEADER --}}
            <div
                class="border-b border-slate-200
                       px-5 py-5
                       dark:border-slate-700
                       md:px-6">

                <div
                    class="flex flex-col gap-3
                           sm:flex-row sm:items-center sm:justify-between">

                    <div>
                        <h3
                            class="text-xl font-black
                                   text-slate-900 dark:text-white">

                            Passage List
                        </h3>

                        <p
                            class="mt-1 text-sm
                                   text-slate-500 dark:text-slate-400">

                            Each passage can contain multiple questions.
                        </p>
                    </div>
                </div>
            </div>

            @if ($materials->count())

                {{-- ========================================================
                    DESKTOP TABLE
                ======================================================== --}}
                <div class="hidden overflow-x-auto xl:block">

                    <table class="w-full min-w-[1000px]">

                        <thead>
                            <tr
                                class="bg-slate-50 text-left
                                       text-sm font-bold text-slate-600
                                       dark:bg-slate-900/60
                                       dark:text-slate-300">

                                <th class="w-[50%] px-6 py-4">
                                    Passage
                                </th>

                                <th class="w-[15%] px-6 py-4 text-center">
                                    Questions
                                </th>

                                <th class="w-[35%] px-6 py-4 text-center">
                                    Actions
                                </th>
                            </tr>
                        </thead>

                        <tbody
                            class="divide-y divide-slate-200
                                   dark:divide-slate-700">

                            @foreach ($materials as $material)
                                @php
                                    $questionCount = $material->questions_count
                                        ?? $material->questions->count();
                                @endphp

                                <tr
                                    class="transition
                                           hover:bg-slate-50/80
                                           dark:hover:bg-slate-700/30">

                                    {{-- PASSAGE --}}
                                    <td class="px-6 py-5 align-middle">

                                        <div class="flex items-start gap-4">

                                            <div
                                                class="flex h-11 w-11 shrink-0
                                                       items-center justify-center
                                                       rounded-xl bg-blue-50
                                                       text-xl
                                                       dark:bg-blue-500/10">

                                                📖
                                            </div>

                                            <div class="min-w-0">

                                                <h4
                                                    class="text-base font-black
                                                           text-slate-900
                                                           dark:text-white">

                                                    {{ $material->title }}
                                                </h4>

                                                @if (!empty($material->instruction))
                                                    <p
                                                        class="mt-1 max-w-2xl
                                                               text-sm leading-6
                                                               text-slate-500
                                                               dark:text-slate-400">

                                                        {{ \Illuminate\Support\Str::limit(
                                                            trim($material->instruction),
                                                            120
                                                        ) }}
                                                    </p>
                                                @else
                                                    <p
                                                        class="mt-1 text-sm italic
                                                               text-slate-400
                                                               dark:text-slate-500">

                                                        No instruction provided.
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    {{-- QUESTION COUNT --}}
                                    <td class="px-6 py-5 text-center align-middle">

                                        <span
                                            class="inline-flex min-w-[110px]
                                                   items-center justify-center
                                                   whitespace-nowrap rounded-full
                                                   bg-emerald-100 px-4 py-2
                                                   text-sm font-bold
                                                   text-emerald-700
                                                   dark:bg-emerald-500/10
                                                   dark:text-emerald-300">

                                            {{ $questionCount }}

                                            {{ \Illuminate\Support\Str::plural(
                                                'Question',
                                                $questionCount
                                            ) }}
                                        </span>
                                    </td>

                                    {{-- ACTIONS --}}
                                    <td class="px-6 py-5 align-middle">

                                        <div
                                            class="flex items-center
                                                   justify-center gap-2">

                                            <a
                                                href="{{ route(
                                                    'admin.reading-questions.index',
                                                    $material->id
                                                ) }}"
                                                class="inline-flex h-10
                                                       items-center justify-center
                                                       whitespace-nowrap rounded-lg
                                                       bg-emerald-600 px-4
                                                       text-sm font-bold text-white
                                                       shadow-sm transition
                                                       hover:bg-emerald-700
                                                       focus:outline-none
                                                       focus:ring-2
                                                       focus:ring-emerald-500
                                                       focus:ring-offset-2
                                                       dark:focus:ring-offset-slate-800">

                                                Questions
                                            </a>

                                            <a
                                                href="{{ route(
                                                    'admin.reading-materials.edit',
                                                    $material->id
                                                ) }}"
                                                class="inline-flex h-10
                                                       items-center justify-center
                                                       whitespace-nowrap rounded-lg
                                                       bg-blue-600 px-4
                                                       text-sm font-bold text-white
                                                       shadow-sm transition
                                                       hover:bg-blue-700
                                                       focus:outline-none
                                                       focus:ring-2
                                                       focus:ring-blue-500
                                                       focus:ring-offset-2
                                                       dark:focus:ring-offset-slate-800">

                                                Edit
                                            </a>

                                            <button
                                                type="button"
                                                data-url="{{ route(
                                                    'admin.reading-materials.destroy',
                                                    $material->id
                                                ) }}"
                                                data-title="{{ $material->title }}"
                                                onclick="openDeleteModal(
                                                    this.dataset.url,
                                                    this.dataset.title
                                                )"
                                                class="inline-flex h-10
                                                       items-center justify-center
                                                       whitespace-nowrap rounded-lg
                                                       bg-red-600 px-4
                                                       text-sm font-bold text-white
                                                       transition
                                                       hover:bg-red-700
                                                       focus:outline-none
                                                       focus:ring-2
                                                       focus:ring-red-500
                                                       focus:ring-offset-2
                                                       dark:focus:ring-offset-slate-800">

                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- ========================================================
                    MOBILE AND TABLET CARDS
                ======================================================== --}}
                <div
                    class="divide-y divide-slate-200
                           xl:hidden
                           dark:divide-slate-700">

                    @foreach ($materials as $material)
                        @php
                            $questionCount = $material->questions_count
                                ?? $material->questions->count();
                        @endphp

                        <article class="p-5 md:p-6">

                            <div
                                class="flex flex-col gap-5
                                       lg:flex-row
                                       lg:items-center
                                       lg:justify-between">

                                {{-- PASSAGE DETAIL --}}
                                <div class="flex min-w-0 items-start gap-4">

                                    <div
                                        class="flex h-11 w-11 shrink-0
                                               items-center justify-center
                                               rounded-xl bg-blue-50
                                               text-xl
                                               dark:bg-blue-500/10">

                                        📖
                                    </div>

                                    <div class="min-w-0">

                                        <h4
                                            class="text-lg font-black
                                                   text-slate-900
                                                   dark:text-white">

                                            {{ $material->title }}
                                        </h4>

                                        @if (!empty($material->instruction))
                                            <p
                                                class="mt-1 max-w-2xl
                                                       text-sm leading-6
                                                       text-slate-500
                                                       dark:text-slate-400">

                                                {{ \Illuminate\Support\Str::limit(
                                                    trim($material->instruction),
                                                    140
                                                ) }}
                                            </p>
                                        @else
                                            <p
                                                class="mt-1 text-sm italic
                                                       text-slate-400
                                                       dark:text-slate-500">

                                                No instruction provided.
                                            </p>
                                        @endif

                                        <span
                                            class="mt-3 inline-flex
                                                   items-center whitespace-nowrap
                                                   rounded-full bg-emerald-100
                                                   px-3 py-1.5
                                                   text-xs font-bold
                                                   text-emerald-700
                                                   dark:bg-emerald-500/10
                                                   dark:text-emerald-300">

                                            {{ $questionCount }}

                                            {{ \Illuminate\Support\Str::plural(
                                                'Question',
                                                $questionCount
                                            ) }}
                                        </span>
                                    </div>
                                </div>

                                {{-- ACTIONS --}}
                                <div
                                    class="grid grid-cols-1 gap-2
                                           sm:grid-cols-3
                                           lg:flex lg:shrink-0
                                           lg:items-center">

                                    <a
                                        href="{{ route(
                                            'admin.reading-questions.index',
                                            $material->id
                                        ) }}"
                                        class="inline-flex h-10
                                               items-center justify-center
                                               whitespace-nowrap rounded-lg
                                               bg-emerald-600 px-4
                                               text-sm font-bold text-white
                                               transition hover:bg-emerald-700">

                                        Questions
                                    </a>

                                    <a
                                        href="{{ route(
                                            'admin.reading-materials.edit',
                                            $material->id
                                        ) }}"
                                        class="inline-flex h-10
                                               items-center justify-center
                                               whitespace-nowrap rounded-lg
                                               bg-blue-600 px-4
                                               text-sm font-bold text-white
                                               transition hover:bg-blue-700">

                                        Edit
                                    </a>

                                    <button
                                        type="button"
                                        data-url="{{ route(
                                            'admin.reading-materials.destroy',
                                            $material->id
                                        ) }}"
                                        data-title="{{ $material->title }}"
                                        onclick="openDeleteModal(
                                            this.dataset.url,
                                            this.dataset.title
                                        )"
                                        class="inline-flex h-10
                                               items-center justify-center
                                               whitespace-nowrap rounded-lg
                                               bg-red-600 px-4
                                               text-sm font-bold text-white
                                               transition hover:bg-red-700">

                                        Delete
                                    </button>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

            @else

                {{-- ========================================================
                    EMPTY STATE
                ======================================================== --}}
                <div class="px-6 py-16 text-center">

                    <div
                        class="mx-auto flex h-16 w-16
                               items-center justify-center
                               rounded-2xl bg-blue-50
                               text-3xl
                               dark:bg-blue-500/10">

                        📖
                    </div>

                    <h3
                        class="mt-5 text-xl font-black
                               text-slate-900 dark:text-white">

                        No Reading Passages Yet
                    </h3>

                    <p
                        class="mx-auto mt-2 max-w-md
                               text-sm leading-6
                               text-slate-500 dark:text-slate-400">

                        Create a reading passage first, then add questions
                        connected to the passage.
                    </p>

                    <a
                        href="{{ route(
                            'admin.reading-materials.create',
                            $lesson->id
                        ) }}"
                        class="mt-6 inline-flex
                               items-center justify-center
                               rounded-xl bg-blue-600
                               px-5 py-3
                               text-sm font-bold text-white
                               transition hover:bg-blue-700">

                        <span class="mr-2 text-lg leading-none">
                            +
                        </span>

                        Create First Passage
                    </a>
                </div>
            @endif
        </section>
    </div>

    {{-- ================================================================
        DELETE MODAL
    ================================================================= --}}
    <div
        id="deleteModal"
        class="reading-delete-modal"
        aria-hidden="true">

        <div
            id="deleteModalPanel"
            class="reading-delete-panel"
            role="dialog"
            aria-modal="true"
            aria-labelledby="deleteModalTitle"
            aria-describedby="deleteModalDescription">

            {{-- MODAL HEADER --}}
            <div class="reading-delete-header">

                <div
                    class="reading-delete-icon"
                    aria-hidden="true">
                    !
                </div>

                <div>
                    <h3
                        id="deleteModalTitle"
                        class="reading-delete-title">
                        Delete Reading Passage
                    </h3>

                    <p
                        id="deleteModalDescription"
                        class="reading-delete-subtitle">
                        This action cannot be undone.
                    </p>
                </div>
            </div>

            {{-- MODAL BODY --}}
            <div class="reading-delete-body">

                <p class="reading-delete-copy">
                    Are you sure you want to delete:
                </p>

                <p
                    id="deleteMaterialTitle"
                    class="reading-delete-material">
                    Reading passage
                </p>

                <div class="reading-delete-warning">
                    All questions connected to this passage will also be deleted.
                </div>
            </div>

            {{-- MODAL ACTIONS --}}
            <div class="reading-delete-footer">

                <button
                    type="button"
                    onclick="closeDeleteModal()"
                    class="reading-delete-cancel">
                    Cancel
                </button>

                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')

                    <button
                        type="submit"
                        class="reading-delete-confirm">
                        Delete Passage
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openDeleteModal(actionUrl, materialTitle) {
            const modal = document.getElementById('deleteModal');
            const panel = document.getElementById('deleteModalPanel');
            const form = document.getElementById('deleteForm');
            const title = document.getElementById('deleteMaterialTitle');

            if (!modal || !panel || !form || !title) {
                return;
            }

            form.action = actionUrl;
            title.textContent = materialTitle || 'Reading passage';

            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');

            document.body.style.overflow = 'hidden';

            requestAnimationFrame(function () {
                panel.classList.add('is-visible');
            });
        }

        function closeDeleteModal() {
            const modal = document.getElementById('deleteModal');
            const panel = document.getElementById('deleteModalPanel');

            if (!modal || !panel || !modal.classList.contains('is-open')) {
                return;
            }

            panel.classList.remove('is-visible');

            setTimeout(function () {
                modal.classList.remove('is-open');
                modal.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
            }, 180);
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
