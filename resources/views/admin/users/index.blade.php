@extends('layouts.admin')

@section('content')
    {{-- ============================================================
        FLASH MESSAGES
    ============================================================ --}}
    @if (session('success'))
        <div
            class="mb-6 rounded-2xl
                   border border-emerald-200
                   bg-emerald-50
                   px-5 py-4
                   font-semibold text-emerald-700
                   shadow-sm
                   dark:border-emerald-500/20
                   dark:bg-emerald-500/10
                   dark:text-emerald-300">

            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div
            class="mb-6 rounded-2xl
                   border border-red-200
                   bg-red-50
                   px-5 py-4
                   font-semibold text-red-700
                   shadow-sm
                   dark:border-red-500/20
                   dark:bg-red-500/10
                   dark:text-red-300">

            {{ session('error') }}
        </div>
    @endif

    {{-- ============================================================
        PAGE HEADER
    ============================================================ --}}
    <div
        class="mb-8 flex flex-col gap-5
               sm:flex-row sm:items-center sm:justify-between">

        <div class="min-w-0">
            <h1
                class="text-3xl font-black leading-tight
                       text-slate-900 dark:text-white
                       lg:text-4xl">
                User Management
            </h1>

            <p class="mt-2 text-slate-500 dark:text-slate-400">
                Manage all registered users
            </p>
        </div>

        <a
            href="{{ route('admin.users.create') }}"
            class="inline-flex w-full items-center justify-center
                   rounded-2xl
                   bg-gradient-to-r from-cyan-500 to-blue-600
                   px-6 py-4
                   text-center font-bold text-white
                   shadow-lg shadow-cyan-500/20
                   transition-all duration-200
                   hover:scale-[1.02]
                   hover:shadow-xl
                   focus:outline-none focus:ring-4 focus:ring-cyan-500/20
                   sm:w-auto">
            <span class="mr-1 text-lg font-black">+</span>
            Add User
        </a>
    </div>

    {{-- ============================================================
        MOBILE USER CARDS
    ============================================================ --}}
    <div class="grid grid-cols-1 gap-5 lg:hidden">

        @forelse ($users as $user)
            <article
                class="rounded-3xl
                       border border-slate-200
                       bg-white
                       p-5
                       shadow-sm
                       dark:border-white/10
                       dark:bg-white/[0.03]">

                <div class="flex items-start justify-between gap-4">
                    <div class="flex min-w-0 items-center gap-4">
                        <div
                            class="flex h-14 w-14 shrink-0
                                   items-center justify-center
                                   rounded-2xl
                                   bg-gradient-to-br from-cyan-400 to-blue-600
                                   text-lg font-black text-white
                                   shadow-lg shadow-cyan-500/10">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>

                        <div class="min-w-0">
                            <h3
                                class="truncate text-lg font-black
                                       text-slate-900 dark:text-white">
                                {{ $user->name }}
                            </h3>

                            <p
                                class="mt-1 truncate text-sm
                                       text-slate-500 dark:text-slate-400">
                                {{ $user->email }}
                            </p>
                        </div>
                    </div>

                    @if ($user->role === 'admin')
                        <span
                            class="shrink-0 rounded-xl
                                   bg-red-500/10
                                   px-3 py-2
                                   text-xs font-bold text-red-500
                                   dark:text-red-400">
                            Admin
                        </span>
                    @else
                        <span
                            class="shrink-0 rounded-xl
                                   bg-cyan-500/10
                                   px-3 py-2
                                   text-xs font-bold text-cyan-600
                                   dark:text-cyan-400">
                            Student
                        </span>
                    @endif
                </div>

                <div
                    class="mt-5 border-t border-slate-200 pt-5
                           dark:border-white/10">

                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Joined
                    </p>

                    <h4
                        class="mt-1 font-semibold
                               text-slate-900 dark:text-white">
                        {{ $user->created_at->format('d M Y') }}
                    </h4>
                </div>

                <div class="mt-5 grid grid-cols-2 gap-3">
                    <a
                        href="{{ route('admin.users.edit', $user) }}"
                        class="rounded-2xl
                               bg-slate-100
                               py-3
                               text-center font-semibold
                               text-slate-900
                               transition-all duration-200
                               hover:bg-slate-200
                               dark:bg-white/5
                               dark:text-white
                               dark:hover:bg-white/10">
                        Edit
                    </a>

                    <form
                        action="{{ route('admin.users.destroy', $user) }}"
                        method="POST"
                        onsubmit="return confirm('Are you sure you want to delete {{ addslashes($user->name) }}?')">

                        @csrf
                        @method('DELETE')

                        <button
                            type="submit"
                            class="w-full rounded-2xl
                                   bg-red-500/10
                                   py-3
                                   font-semibold text-red-600
                                   transition-all duration-200
                                   hover:bg-red-500/20
                                   dark:text-red-400">
                            Delete
                        </button>
                    </form>
                </div>
            </article>
        @empty
            <div
                class="rounded-3xl
                       border border-slate-200
                       bg-white
                       px-6 py-12
                       text-center
                       shadow-sm
                       dark:border-white/10
                       dark:bg-white/[0.03]">

                <p class="font-bold text-slate-900 dark:text-white">
                    No users found
                </p>

                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                    There are no registered users to display.
                </p>
            </div>
        @endforelse
    </div>

    {{-- ============================================================
        DESKTOP USER TABLE
    ============================================================ --}}
    <div
        class="hidden overflow-hidden
               rounded-[32px]
               border border-slate-200
               bg-white
               shadow-sm
               dark:border-white/10
               dark:bg-white/[0.03]
               lg:block">

        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px]">
                <thead
                    class="border-b border-slate-200
                           bg-slate-50
                           dark:border-white/10
                           dark:bg-white/[0.03]">

                    <tr>
                        <th class="px-8 py-6 text-left text-sm font-black text-slate-500 dark:text-slate-400">
                            User
                        </th>
                        <th class="px-8 py-6 text-left text-sm font-black text-slate-500 dark:text-slate-400">
                            Email
                        </th>
                        <th class="px-8 py-6 text-left text-sm font-black text-slate-500 dark:text-slate-400">
                            Role
                        </th>
                        <th class="px-8 py-6 text-left text-sm font-black text-slate-500 dark:text-slate-400">
                            Joined
                        </th>
                        <th class="px-8 py-6 text-right text-sm font-black text-slate-500 dark:text-slate-400">
                            Actions
                        </th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($users as $user)
                        <tr
                            class="border-b border-slate-100
                                   transition-all duration-200
                                   hover:bg-slate-50
                                   dark:border-white/5
                                   dark:hover:bg-white/[0.02]">

                            <td class="px-8 py-6">
                                <div class="flex items-center gap-5">
                                    <div
                                        class="flex h-14 w-14 shrink-0
                                               items-center justify-center
                                               rounded-2xl
                                               bg-gradient-to-br from-cyan-400 to-blue-600
                                               text-lg font-black text-white
                                               shadow-lg shadow-cyan-500/10">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>

                                    <div class="min-w-0">
                                        <h3
                                            class="truncate text-xl font-black
                                                   text-slate-900 dark:text-white">
                                            {{ $user->name }}
                                        </h3>
                                    </div>
                                </div>
                            </td>

                            <td
                                class="px-8 py-6
                                       font-medium
                                       text-slate-600 dark:text-slate-300">
                                {{ $user->email }}
                            </td>

                            <td class="px-8 py-6">
                                @if ($user->role === 'admin')
                                    <span
                                        class="inline-flex rounded-2xl
                                               bg-red-500/10
                                               px-5 py-3
                                               text-sm font-bold text-red-500
                                               dark:text-red-400">
                                        Admin
                                    </span>
                                @else
                                    <span
                                        class="inline-flex rounded-2xl
                                               bg-cyan-500/10
                                               px-5 py-3
                                               text-sm font-bold text-cyan-600
                                               dark:text-cyan-400">
                                        Student
                                    </span>
                                @endif
                            </td>

                            <td
                                class="px-8 py-6
                                       font-medium
                                       text-slate-500 dark:text-slate-400">
                                {{ $user->created_at->format('d M Y') }}
                            </td>

                            <td class="px-8 py-6">
                                <div class="flex items-center justify-end gap-3">
                                    <a
                                        href="{{ route('admin.users.edit', $user) }}"
                                        class="rounded-2xl
                                               bg-slate-100
                                               px-5 py-3
                                               font-semibold
                                               text-slate-900
                                               transition-all duration-200
                                               hover:bg-slate-200
                                               dark:bg-white/5
                                               dark:text-white
                                               dark:hover:bg-white/10">
                                        Edit
                                    </a>

                                    <form
                                        action="{{ route('admin.users.destroy', $user) }}"
                                        method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete {{ addslashes($user->name) }}?')">

                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="rounded-2xl
                                                   bg-red-500/10
                                                   px-5 py-3
                                                   font-semibold text-red-600
                                                   transition-all duration-200
                                                   hover:bg-red-500/20
                                                   dark:text-red-400">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-8 py-14 text-center">
                                <p class="font-bold text-slate-900 dark:text-white">
                                    No users found
                                </p>

                                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                                    There are no registered users to display.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
