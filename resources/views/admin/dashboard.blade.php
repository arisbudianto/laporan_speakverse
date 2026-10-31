@extends('layouts.admin')

@section('content')

    <style>
        .dashboard-card {
            background-color: #ffffff;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
        }

        html.dark .dashboard-card {
            background-color: #0f172a;
            box-shadow: none;
        }

        .dashboard-soft-card {
            background-color: #f8fafc;
        }

        html.dark .dashboard-soft-card {
            background-color: #1e293b;
        }

        .recent-user-row {
            background-color: transparent;
            transition:
                background-color 180ms ease,
                transform 180ms ease;
        }

        html:not(.dark) .recent-user-row:hover {
            background-color: #f8fafc;
        }

        html.dark .recent-user-row:hover {
            background-color: #1e293b;
        }

        /*
        |--------------------------------------------------------------------------
        | Role badges
        |--------------------------------------------------------------------------
        */

        .role-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            min-width: 72px;
            padding: 6px 12px;
            border-radius: 10px;
            font-size: 12px;
            line-height: 16px;
            font-weight: 700;
            white-space: nowrap;
        }

        .role-badge-student {
            color: #047857;
            background-color: #ecfdf5;
        }

        html.dark .role-badge-student {
            color: #6ee7b7;
            background-color: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(52, 211, 153, 0.14);
        }

        .role-badge-admin {
            color: #6d28d9;
            background-color: #f5f3ff;
        }

        html.dark .role-badge-admin {
            color: #c4b5fd;
            background-color: rgba(139, 92, 246, 0.1);
            border: 1px solid rgba(167, 139, 250, 0.14);
        }

        .role-badge-default {
            color: #475569;
            background-color: #f1f5f9;
        }

        html.dark .role-badge-default {
            color: #cbd5e1;
            background-color: rgba(148, 163, 184, 0.1);
            border: 1px solid rgba(148, 163, 184, 0.14);
        }
    </style>


    <div class="mx-auto max-w-screen-2xl space-y-6">

        {{-- =========================================================
            STATISTICS
        ========================================================== --}}
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

            {{-- TOTAL USERS --}}
            <div class="dashboard-card rounded-3xl p-5 sm:p-6">

                <div class="flex items-start justify-between gap-4">

                    <div class="min-w-0">

                        <p class="text-sm font-bold text-slate-500 dark:text-slate-400">
                            Total Users
                        </p>

                        <h3
                            class="mt-4 text-4xl font-black tracking-tight
                            text-slate-900 dark:text-white">

                            {{ number_format($totalUsers) }}

                        </h3>

                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                            Registered accounts
                        </p>

                    </div>

                    <div
                        class="flex h-14 w-14 shrink-0 items-center justify-center
                        rounded-2xl bg-cyan-50 text-cyan-600
                        dark:bg-slate-800 dark:text-cyan-400">

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            class="h-7 w-7">

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M16 21v-2a4 4 0 0 0-4-4H6
                                a4 4 0 0 0-4 4v2
                                M9 11a4 4 0 1 0 0-8
                                4 4 0 0 0 0 8
                                M22 21v-2a4 4 0 0 0-3-3.87
                                M16 3.13a4 4 0 0 1 0 7.75" />

                        </svg>

                    </div>

                </div>

            </div>


            {{-- TOTAL QUESTIONS --}}
            <div class="dashboard-card rounded-3xl p-5 sm:p-6">

                <div class="flex items-start justify-between gap-4">

                    <div class="min-w-0">

                        <p class="text-sm font-bold text-slate-500 dark:text-slate-400">
                            Total Questions
                        </p>

                        <h3
                            class="mt-4 text-4xl font-black tracking-tight
                            text-slate-900 dark:text-white">

                            {{ number_format($totalQuestions) }}

                        </h3>

                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                            Vocabulary questions
                        </p>

                    </div>

                    <div
                        class="flex h-14 w-14 shrink-0 items-center justify-center
                        rounded-2xl bg-pink-50 text-pink-600
                        dark:bg-slate-800 dark:text-pink-400">

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            class="h-7 w-7">

                            <circle cx="12" cy="12" r="9"></circle>

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M9.5 9a2.5 2.5 0 1 1 4.8 1
                                c-.7 1.2-2.3 1.4-2.3 3
                                M12 17h.01" />

                        </svg>

                    </div>

                </div>

            </div>


            {{-- TOTAL ATTEMPTS --}}
            <div class="dashboard-card rounded-3xl p-5 sm:p-6">

                <div class="flex items-start justify-between gap-4">

                    <div class="min-w-0">

                        <p class="text-sm font-bold text-slate-500 dark:text-slate-400">
                            Total Attempts
                        </p>

                        <h3
                            class="mt-4 text-4xl font-black tracking-tight
                            text-slate-900 dark:text-white">

                            {{ number_format($totalAttempts) }}

                        </h3>

                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                            Recorded pretests
                        </p>

                    </div>

                    <div
                        class="flex h-14 w-14 shrink-0 items-center justify-center
                        rounded-2xl bg-violet-50 text-violet-600
                        dark:bg-slate-800 dark:text-violet-400">

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            class="h-7 w-7">

                            <circle cx="12" cy="12" r="9"></circle>

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="m8 12 2.5 2.5L16 9" />

                        </svg>

                    </div>

                </div>

            </div>


            {{-- AVERAGE SCORE --}}
            <div class="dashboard-card rounded-3xl p-5 sm:p-6">

                <div class="flex items-start justify-between gap-4">

                    <div class="min-w-0">

                        <p class="text-sm font-bold text-slate-500 dark:text-slate-400">
                            Average Score
                        </p>

                        <h3
                            class="mt-4 text-4xl font-black tracking-tight
                            text-emerald-500">

                            {{ number_format($averageScore, 1) }}

                        </h3>

                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                            Overall performance
                        </p>

                    </div>

                    <div
                        class="flex h-14 w-14 shrink-0 items-center justify-center
                        rounded-2xl bg-emerald-50 text-emerald-600
                        dark:bg-slate-800 dark:text-emerald-400">

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            class="h-7 w-7">

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M4 20V10
                                M10 20V4
                                M16 20v-7
                                M22 20H2" />

                        </svg>

                    </div>

                </div>

            </div>

        </section>


        {{-- =========================================================
            RECENT USERS AND SYSTEM INFORMATION
        ========================================================== --}}
        <section class="grid grid-cols-1 items-start gap-6 xl:grid-cols-3">

            {{-- RECENT USERS --}}
            <div
                class="dashboard-card overflow-hidden rounded-3xl
                xl:col-span-2">

                {{-- HEADER --}}
                <div
                    class="flex flex-col gap-4 px-5 pb-3 pt-5
                    sm:flex-row sm:items-center sm:justify-between
                    sm:px-6 sm:pb-4 sm:pt-6">

                    <div>

                        <h3
                            class="text-xl font-black
                            text-slate-900 dark:text-white">

                            Recent Users

                        </h3>

                        <p
                            class="mt-1 text-sm
                            text-slate-500 dark:text-slate-400">

                            Latest registered accounts

                        </p>

                    </div>

                    <a
                        href="{{ route('admin.users.index') }}"
                        class="inline-flex items-center gap-2
                        text-sm font-bold text-cyan-600
                        transition-colors hover:text-cyan-700
                        dark:text-cyan-400 dark:hover:text-cyan-300">

                        <span>View all</span>

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="h-4 w-4">

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="m9 18 6-6-6-6" />

                        </svg>

                    </a>

                </div>


                {{-- USER LIST --}}
                <div class="px-3 pb-4 sm:px-4 sm:pb-5">

                    <div class="space-y-1">

                        @forelse($recentUsers as $user)

                            @php
                                $roleLabel = ucwords(
                                    str_replace('_', ' ', $user->role)
                                );

                                $roleClass = match ($user->role) {
                                    'student' => 'role-badge-student',
                                    'admin' => 'role-badge-admin',
                                    default => 'role-badge-default',
                                };
                            @endphp

                            <div
                                class="recent-user-row flex items-center
                                justify-between gap-3 rounded-2xl
                                px-3 py-3 sm:px-4">

                                {{-- LEFT --}}
                                <div class="flex min-w-0 items-center gap-3 sm:gap-4">

                                    {{-- AVATAR --}}
                                    <div
                                        class="flex h-11 w-11 shrink-0
                                        items-center justify-center rounded-2xl
                                        bg-gradient-to-br from-cyan-400 to-blue-600
                                        font-black text-white">

                                        {{ strtoupper(substr($user->name, 0, 1)) }}

                                    </div>


                                    {{-- USER INFORMATION --}}
                                    <div class="min-w-0">

                                        <h4
                                            class="truncate font-bold
                                            text-slate-900 dark:text-slate-100">

                                            {{ $user->name }}

                                        </h4>

                                        <p
                                            class="mt-0.5 truncate text-sm
                                            text-slate-500 dark:text-slate-400">

                                            {{ $user->email }}

                                        </p>

                                    </div>

                                </div>


                                {{-- ROLE BADGE --}}
                                <span class="role-badge {{ $roleClass }}">

                                    {{ $roleLabel }}

                                </span>

                            </div>

                        @empty

                            <div
                                class="dashboard-soft-card rounded-2xl
                                px-6 py-12 text-center">

                                <div
                                    class="mx-auto flex h-12 w-12
                                    items-center justify-center rounded-2xl
                                    bg-slate-200 text-slate-500
                                    dark:bg-slate-700 dark:text-slate-400">

                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        class="h-6 w-6">

                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M16 21v-2a4 4 0 0 0-4-4H6
                                            a4 4 0 0 0-4 4v2
                                            M9 11a4 4 0 1 0 0-8
                                            4 4 0 0 0 0 8" />

                                    </svg>

                                </div>

                                <p
                                    class="mt-4 font-bold
                                    text-slate-700 dark:text-slate-200">

                                    No users found

                                </p>

                                <p
                                    class="mt-1 text-sm
                                    text-slate-500 dark:text-slate-400">

                                    Registered users will appear here.

                                </p>

                            </div>

                        @endforelse

                    </div>

                </div>

            </div>


            {{-- SYSTEM INFORMATION --}}
            <aside
                class="dashboard-card self-start rounded-3xl
                p-5 sm:p-6">

                {{-- HEADER --}}
                <div>

                    <h3
                        class="text-xl font-black
                        text-slate-900 dark:text-white">

                        System Information

                    </h3>

                    <p
                        class="mt-1 text-sm
                        text-slate-500 dark:text-slate-400">

                        Platform data overview

                    </p>

                </div>


                {{-- INFORMATION --}}
                <div class="mt-5 space-y-3">

                    {{-- REGISTERED USERS --}}
                    <div
                        class="dashboard-soft-card flex items-center gap-3
                        rounded-2xl p-4">

                        <div
                            class="flex h-10 w-10 shrink-0
                            items-center justify-center rounded-xl
                            bg-cyan-100 text-cyan-600
                            dark:bg-slate-700 dark:text-cyan-400">

                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                class="h-5 w-5">

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M16 21v-2a4 4 0 0 0-4-4H6
                                    a4 4 0 0 0-4 4v2
                                    M9 11a4 4 0 1 0 0-8
                                    4 4 0 0 0 0 8" />

                            </svg>

                        </div>

                        <div class="min-w-0 flex-1">

                            <p
                                class="truncate text-sm font-bold
                                text-slate-900 dark:text-white">

                                Registered Users

                            </p>

                            <p
                                class="truncate text-xs
                                text-slate-500 dark:text-slate-400">

                                Users in database

                            </p>

                        </div>

                        <span
                            class="shrink-0 text-lg font-black
                            text-slate-900 dark:text-white">

                            {{ number_format($totalUsers) }}

                        </span>

                    </div>


                    {{-- VOCABULARY QUESTIONS --}}
                    <div
                        class="dashboard-soft-card flex items-center gap-3
                        rounded-2xl p-4">

                        <div
                            class="flex h-10 w-10 shrink-0
                            items-center justify-center rounded-xl
                            bg-pink-100 text-pink-600
                            dark:bg-slate-700 dark:text-pink-400">

                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                class="h-5 w-5">

                                <circle cx="12" cy="12" r="9"></circle>

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M9.5 9a2.5 2.5 0 1 1 4.8 1
                                    c-.7 1.2-2.3 1.4-2.3 3
                                    M12 17h.01" />

                            </svg>

                        </div>

                        <div class="min-w-0 flex-1">

                            <p
                                class="truncate text-sm font-bold
                                text-slate-900 dark:text-white">

                                Vocabulary Questions

                            </p>

                            <p
                                class="truncate text-xs
                                text-slate-500 dark:text-slate-400">

                                Questions available

                            </p>

                        </div>

                        <span
                            class="shrink-0 text-lg font-black
                            text-slate-900 dark:text-white">

                            {{ number_format($totalQuestions) }}

                        </span>

                    </div>


                    {{-- PRETEST ATTEMPTS --}}
                    <div
                        class="dashboard-soft-card flex items-center gap-3
                        rounded-2xl p-4">

                        <div
                            class="flex h-10 w-10 shrink-0
                            items-center justify-center rounded-xl
                            bg-emerald-100 text-emerald-600
                            dark:bg-slate-700 dark:text-emerald-400">

                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                class="h-5 w-5">

                                <circle cx="12" cy="12" r="9"></circle>

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="m8 12 2.5 2.5L16 9" />

                            </svg>

                        </div>

                        <div class="min-w-0 flex-1">

                            <p
                                class="truncate text-sm font-bold
                                text-slate-900 dark:text-white">

                                Pretest Attempts

                            </p>

                            <p
                                class="truncate text-xs
                                text-slate-500 dark:text-slate-400">

                                Attempts recorded

                            </p>

                        </div>

                        <span
                            class="shrink-0 text-lg font-black
                            text-slate-900 dark:text-white">

                            {{ number_format($totalAttempts) }}

                        </span>

                    </div>

                </div>

            </aside>

        </section>

    </div>

@endsection