<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{
    sidebar: false,
    darkMode: localStorage.getItem('darkMode') === 'true',
    profileOpen: false
}" x-init="$watch('darkMode', value => {
    localStorage.setItem('darkMode', value)
    document.documentElement.classList.toggle('dark', value)
});

document.documentElement.classList.toggle('dark', darkMode)"
    :class="{ 'dark': darkMode }">

<head>

    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>SpeakVerse Admin</title>

    <!-- FONT -->
    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>

<body
    class="font-[Outfit]
    bg-slate-100
    dark:bg-[#050816]
    text-slate-900
    dark:text-white
    overflow-x-hidden
    transition-colors duration-300">

    <!-- BACKGROUND -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none z-0">

        <div
            class="absolute top-[-200px] left-[-200px]
            w-[420px] h-[420px]
            bg-cyan-500/10 rounded-full blur-3xl">
        </div>

        <div
            class="absolute bottom-[-200px] right-[-200px]
            w-[420px] h-[420px]
            bg-purple-500/10 rounded-full blur-3xl">
        </div>

    </div>

    <!-- APP -->
    <div class="relative z-10 flex min-h-screen">

        <!-- OVERLAY -->
        <div x-show="sidebar" x-transition.opacity @click="sidebar = false"
            class="fixed inset-0 bg-black/50 z-40 lg:hidden">
        </div>

        <!-- SIDEBAR -->
        <aside
            class="fixed lg:sticky top-0 left-0 z-50
            w-[280px] h-screen shrink-0
            flex flex-col
            bg-white/95 dark:bg-[#081120]/95
            backdrop-blur-xl
            border-r border-slate-200 dark:border-white/10
            transition-transform duration-300
            shadow-2xl lg:shadow-none"
            :class="sidebar ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">

            <!-- LOGO -->
            <div
                class="h-20 px-6
                flex items-center justify-between
                border-b border-slate-200 dark:border-white/10">

                <div class="flex items-center gap-4">

                    <div
                        class="w-12 h-12 rounded-2xl
                        bg-gradient-to-br from-cyan-400 to-blue-600
                        flex items-center justify-center
                        text-white text-xl font-black
                        shadow-lg shadow-cyan-500/20">

                        S

                    </div>

                    <div>

                        <h1 class="text-xl font-black">
                            SpeakVerse
                        </h1>

                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            {{ auth()->user()?->role === 'teacher' ? 'Menu Guru' : 'Admin Panel' }}
                        </p>

                    </div>

                </div>

                <!-- CLOSE -->
                <button @click="sidebar = false"
                    class="lg:hidden w-10 h-10 rounded-xl
                    bg-slate-100 dark:bg-white/10">

                    ✕

                </button>

            </div>

            <!-- MENU -->
            <div class="flex-1 overflow-y-auto p-5">

                <nav class="space-y-2">

                    @if (auth()->user()?->role === 'teacher')
                    <a href="{{ route('teacher.dashboard') }}"
                        class="flex items-center gap-4 px-4 py-3 rounded-2xl transition-all duration-200 font-semibold
                        {{ request()->routeIs('teacher.dashboard')
                            ? 'bg-cyan-500/10 text-cyan-400 shadow-lg shadow-cyan-500/5'
                            : 'hover:bg-slate-100 dark:hover:bg-white/5 text-slate-700 dark:text-slate-300' }}">
                        <span class="text-xl">🏠</span>
                        <span>Dashboard</span>
                    </a>
                    @else
                    <a href="{{ route('admin.dashboard') }}"
                        class="flex items-center gap-4 px-4 py-3 rounded-2xl transition-all duration-200 font-semibold
                        {{ request()->routeIs('admin.dashboard')
                            ? 'bg-cyan-500/10 text-cyan-400 shadow-lg shadow-cyan-500/5'
                            : 'hover:bg-slate-100 dark:hover:bg-white/5 text-slate-700 dark:text-slate-300' }}">
                        <span class="text-xl">📊</span>
                        <span>Dashboard</span>
                    </a>
                    <a href="{{ route('admin.users.index') }}"
                        class="flex items-center gap-4 px-4 py-3 rounded-2xl transition-all duration-200 font-semibold
                        {{ request()->routeIs('admin.users.*')
                            ? 'bg-cyan-500/10 text-cyan-400 shadow-lg shadow-cyan-500/5'
                            : 'hover:bg-slate-100 dark:hover:bg-white/5 text-slate-700 dark:text-slate-300' }}">
                        <span class="text-xl">👥</span>
                        <span>Users</span>
                    </a>
                    <a href="{{ route('admin.teachers.index') }}"
                        class="flex items-center gap-4 px-4 py-3 rounded-2xl transition-all duration-200 font-semibold
                        {{ request()->routeIs('admin.teachers.*')
                            ? 'bg-cyan-500/10 text-cyan-400 shadow-lg shadow-cyan-500/5'
                            : 'hover:bg-slate-100 dark:hover:bg-white/5 text-slate-700 dark:text-slate-300' }}">
                        <span class="text-xl">👩‍🏫</span>
                        <span>Guru</span>
                    </a>
                    @endif

                    @if (\Illuminate\Support\Facades\Route::has('staff.classes.index'))
                    <a href="{{ route('staff.classes.index') }}"
                        class="flex items-center gap-4 px-4 py-3 rounded-2xl transition-all duration-200 font-semibold
                        {{ request()->routeIs('staff.classes.*')
                            ? 'bg-cyan-500/10 text-cyan-400 shadow-lg shadow-cyan-500/5'
                            : 'hover:bg-slate-100 dark:hover:bg-white/5 text-slate-700 dark:text-slate-300' }}">
                        <span class="text-xl">🏫</span>
                        <span>Manajemen Kelas</span>
                    </a>
                    @endif

                    <!-- LEARNING -->
                    <a href="{{ route('admin.learning') }}"
                        class="flex items-center gap-4 px-4 py-3 rounded-2xl transition-all duration-200 font-semibold
                        {{ request()->routeIs('admin.learning') ||
                        request()->routeIs('admin.reading-materials.*') ||
                        request()->routeIs('admin.reading-questions.*') ||
                        request()->routeIs('admin.listening-materials.*') ||
                        request()->routeIs('admin.listening-questions.*') ||
                        request()->routeIs('admin.speaking-materials.*') ||
                        request()->routeIs('admin.speaking-questions.*') ||
                        request()->routeIs('admin.writing-materials.*') ||
                        request()->routeIs('admin.writing-questions.*')
                            ? 'bg-cyan-500/10 text-cyan-400 shadow-lg shadow-cyan-500/5'
                            : 'hover:bg-slate-100 dark:hover:bg-white/5 text-slate-700 dark:text-slate-300' }}">

                        <span class="text-xl">📚</span>

                        <span>Learning</span>

                    </a>

                    @if (auth()->user()?->role === 'admin')
                    <!-- ANALYTICS -->
                    <a href="{{ route('admin.analytics') }}"
                        class="flex items-center gap-4 px-4 py-3 rounded-2xl transition-all duration-200 font-semibold
                        {{ request()->routeIs('admin.analytics')
                            ? 'bg-cyan-500/10 text-cyan-400 shadow-lg shadow-cyan-500/5'
                            : 'hover:bg-slate-100 dark:hover:bg-white/5 text-slate-700 dark:text-slate-300' }}">

                        <span class="text-xl">📈</span>

                        <span>Analytics</span>

                    </a>

                    <!-- HASIL BELAJAR SEMUA SEKOLAH -->
                    <a href="{{ route('admin.school-results') }}"
                        class="flex items-center gap-4 px-4 py-3 rounded-2xl transition-all duration-200 font-semibold
                        {{ request()->routeIs('admin.school-results*')
                            ? 'bg-cyan-500/10 text-cyan-400 shadow-lg shadow-cyan-500/5'
                            : 'hover:bg-slate-100 dark:hover:bg-white/5 text-slate-700 dark:text-slate-300' }}">

                        <span class="text-xl">🏫</span>

                        <span>Hasil Sekolah</span>

                    </a>

                    <!-- LEARNING ANALYTICS: KETERLIBATAN -->
                    <a href="{{ route('admin.la.engagement') }}"
                        class="flex items-center gap-4 px-4 py-3 rounded-2xl transition-all duration-200 font-semibold
                        {{ request()->routeIs('admin.la.*')
                            ? 'bg-cyan-500/10 text-cyan-400 shadow-lg shadow-cyan-500/5'
                            : 'hover:bg-slate-100 dark:hover:bg-white/5 text-slate-700 dark:text-slate-300' }}">

                        <span class="text-xl">🧭</span>

                        <span>Analitik Lanjutan</span>

                    </a>
                    @endif

                </nav>

            </div>

            <!-- FOOTER -->
            <div
                class="p-5
                border-t border-slate-200 dark:border-white/10
                bg-white/80 dark:bg-[#081120]/80">

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button
                        class="w-full py-3 rounded-2xl
                        bg-red-500/10
                        text-red-400
                        hover:bg-red-500/20
                        transition-all duration-200
                        font-semibold">

                        Logout

                    </button>

                </form>

            </div>

        </aside>

        <!-- MAIN -->
        <div class="flex-1 min-w-0 flex flex-col">

            <!-- TOPBAR -->
            <header
                class="sticky top-0 z-30
                h-20
                px-5 lg:px-8
                flex items-center justify-between
                bg-white/80 dark:bg-[#050816]/80
                backdrop-blur-xl
                border-b border-slate-200 dark:border-white/10">

                <!-- LEFT -->
                <div class="flex items-center gap-4">

                    <!-- MOBILE BUTTON -->
                    <button @click="sidebar = true"
                        class="lg:hidden
                        w-12 h-12 rounded-2xl
                        bg-white dark:bg-white/5
                        border border-slate-200 dark:border-white/10">

                        ☰

                    </button>

                    <div>

                        <h2 class="text-2xl lg:text-4xl font-black leading-none">
                            {{ auth()->user()?->role === 'teacher' ? 'Dashboard Guru' : 'Admin Dashboard' }}
                        </h2>

                        <p class="hidden sm:block mt-2 text-sm text-slate-500 dark:text-slate-400">
                            {{ auth()->user()?->role === 'teacher' ? 'Kelola kelas, siswa, materi, dan soal' : 'Manage SpeakVerse Platform' }}
                        </p>

                    </div>

                </div>

                <!-- RIGHT -->
                <div class="flex items-center gap-3">

                    <!-- SARAN PERBAIKAN MODUL 6 — NOTIFIKASI -->
                    <a href="{{ route('staff.notifications.index') }}"
                        class="relative w-12 h-12 rounded-2xl
                        border border-slate-200 dark:border-white/10
                        bg-white dark:bg-white/5
                        hover:scale-105 transition-all duration-200
                        flex items-center justify-center">

                        🔔

                        @php
                            $unreadNotificationCount = auth()->user()?->unreadNotifications()->count() ?? 0;
                        @endphp

                        @if ($unreadNotificationCount > 0)
                            <span class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center
                                rounded-full bg-red-500 text-[10px] font-black text-white">
                                {{ $unreadNotificationCount > 9 ? '9+' : $unreadNotificationCount }}
                            </span>
                        @endif
                    </a>

                    <!-- DARK MODE -->
                    <button @click="darkMode = !darkMode"
                        class="w-12 h-12 rounded-2xl
                        border border-slate-200 dark:border-white/10
                        bg-white dark:bg-white/5
                        hover:scale-105 transition-all duration-200">

                        <span x-show="darkMode">☀️</span>
                        <span x-show="!darkMode">🌙</span>

                    </button>

                    <!-- PROFILE -->
                    <div class="relative">

                        <!-- BUTTON -->
                        <button @click="profileOpen = !profileOpen"
                            class="flex items-center gap-3
                            px-3 py-2 rounded-2xl
                            bg-white dark:bg-white/5
                            border border-slate-200 dark:border-white/10
                            hover:shadow-lg
                            transition-all duration-200">

                            <div class="hidden sm:block text-right">

                                <h3 class="font-bold leading-none">
                                    {{ Auth::user()->name }}
                                </h3>

                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                    {{ auth()->user()?->role === 'teacher' ? 'Guru' : 'Administrator' }}
                                </p>

                            </div>

                            <div
                                class="w-12 h-12 rounded-2xl
                                bg-gradient-to-br from-cyan-400 to-blue-600
                                flex items-center justify-center
                                text-white font-bold
                                shadow-lg shadow-cyan-500/20">

                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}

                            </div>

                        </button>

                        <!-- DROPDOWN -->
                        <div x-show="profileOpen" @click.away="profileOpen = false" x-transition
                            class="absolute right-0 top-20
                            w-[320px]
                            rounded-[32px]
                            overflow-hidden
                            bg-white dark:bg-[#081120]
                            border border-slate-200 dark:border-white/10
                            shadow-2xl z-50">

                            <!-- TOP -->
                            <div class="p-6 flex items-center gap-4">

                                <div
                                    class="w-20 h-20 rounded-3xl
                                    bg-gradient-to-br from-cyan-400 to-blue-600
                                    flex items-center justify-center
                                    text-white text-3xl font-black
                                    shadow-lg shadow-cyan-500/20">

                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}

                                </div>

                                <div>

                                    <h3 class="text-2xl font-black">
                                        {{ Auth::user()->name }}
                                    </h3>

                                    <p class="text-slate-500 dark:text-slate-400 mt-1">
                                        {{ Auth::user()->email }}
                                    </p>

                                    <div
                                        class="mt-3 inline-flex items-center
                                        px-3 py-1 rounded-full
                                        bg-cyan-500/10
                                        text-cyan-400
                                        text-xs font-bold">

                                        {{ auth()->user()?->role === 'teacher' ? 'Guru' : 'Administrator' }}

                                    </div>

                                </div>

                            </div>

                            <!-- MENU -->
                            <div class="border-t border-slate-200 dark:border-white/10 p-4">

                                <!-- PROFILE SETTINGS -->
                                <a href="{{ auth()->user()?->role === 'teacher' ? route('profile.edit') : route('admin.profile.edit') }}"
                                    class="flex items-center gap-4
                                    px-4 py-4 rounded-2xl
                                    hover:bg-slate-100 dark:hover:bg-white/5
                                    transition-all duration-200">

                                    <div
                                        class="w-12 h-12 rounded-2xl
                                        bg-slate-100 dark:bg-white/5
                                        flex items-center justify-center">

                                        ⚙️

                                    </div>

                                    <div>

                                        <h4 class="font-bold">
                                            Profile Settings
                                        </h4>

                                        <p class="text-sm text-slate-500 dark:text-slate-400">
                                            Manage your account settings
                                        </p>

                                    </div>

                                </a>

                            </div>

                        </div>

                    </div>

                </div>

            </header>

            <!-- CONTENT -->
            <main class="flex-1 p-5 lg:p-8">

                @yield('content')

            </main>

        </div>

    </div>

</body>

</html>
