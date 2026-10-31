@extends('layouts.admin')

@section('content')
    @php
        $formatScore = function ($score) {
            if ($score === null) {
                return '-';
            }

            return rtrim(
                rtrim(number_format((float) $score, 1), '0'),
                '.'
            );
        };

        $scoreClass = function ($score) {
            if ($score === null) {
                return 'bg-slate-100 text-slate-500 dark:bg-white/5 dark:text-slate-400';
            }

            if ($score >= 85) {
                return 'bg-green-500/10 text-green-500 dark:text-green-400';
            }

            if ($score >= 70) {
                return 'bg-cyan-500/10 text-cyan-500 dark:text-cyan-400';
            }

            if ($score >= 60) {
                return 'bg-yellow-500/10 text-yellow-600 dark:text-yellow-400';
            }

            return 'bg-red-500/10 text-red-500 dark:text-red-400';
        };

        $maxActivity = max(
            $weeklyActivity->max('count') ?? 0,
            1
        );
    @endphp

    {{-- HEADER --}}
    <div
        class="flex flex-col gap-5 mb-8
        lg:flex-row lg:items-center lg:justify-between"
    >
        <div>
            <h1 class="text-3xl lg:text-4xl font-black leading-tight">
                Analytics
            </h1>

            <p class="mt-2 text-slate-500 dark:text-slate-400">
                Monitor nilai pretest, posttest, dan perkembangan setiap siswa.
            </p>
        </div>

        <form
            action="{{ route('admin.analytics') }}"
            method="GET"
            class="flex flex-col sm:flex-row gap-3"
        >
            <div class="relative">
                <svg
                    class="absolute left-4 top-1/2 -translate-y-1/2
                    w-5 h-5 text-slate-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="m21 21-4.35-4.35m1.35-5.65
                        a7 7 0 1 1-14 0
                        a7 7 0 0 1 14 0Z"
                    />
                </svg>

                <input
                    type="text"
                    name="q"
                    value="{{ $search }}"
                    placeholder="Cari nama atau email..."
                    class="w-full sm:w-72
                    rounded-2xl
                    border border-slate-200
                    bg-white
                    py-3 pl-12 pr-4
                    text-sm font-semibold
                    text-slate-700
                    outline-none
                    transition
                    focus:border-cyan-400
                    focus:ring-4 focus:ring-cyan-500/10
                    dark:border-white/10
                    dark:bg-white/[0.03]
                    dark:text-white"
                >
            </div>

            <button
                type="submit"
                class="rounded-2xl
                bg-cyan-500
                px-6 py-3
                text-sm font-black
                text-white
                transition
                hover:bg-cyan-600"
            >
                Cari
            </button>

            @if ($search !== '')
                <a
                    href="{{ route('admin.analytics') }}"
                    class="flex items-center justify-center
                    rounded-2xl
                    border border-slate-200
                    px-5 py-3
                    text-sm font-bold
                    text-slate-600
                    transition
                    hover:bg-slate-100
                    dark:border-white/10
                    dark:text-slate-300
                    dark:hover:bg-white/5"
                >
                    Reset
                </a>
            @endif
        </form>
    </div>

    {{-- STATISTICS --}}
    <div
        class="grid grid-cols-1 gap-6 mb-8
        sm:grid-cols-2 xl:grid-cols-5"
    >
        {{-- TOTAL STUDENTS --}}
        <div
            class="relative overflow-hidden
            rounded-[28px]
            border border-slate-200
            bg-white
            p-6 shadow-sm
            dark:border-white/10
            dark:bg-white/[0.03]"
        >
            <div
                class="absolute -right-10 -top-10
                h-32 w-32 rounded-full
                bg-purple-500/10 blur-3xl"
            ></div>

            <div class="relative">
                <div
                    class="mb-5 flex h-14 w-14
                    items-center justify-center
                    rounded-2xl
                    bg-purple-500/10
                    text-2xl"
                >
                    👥
                </div>

                <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">
                    Total Students
                </p>

                <h2 class="mt-3 text-4xl font-black">
                    {{ number_format($totalStudents) }}
                </h2>

                <p class="mt-3 text-sm font-semibold text-purple-500">
                    Registered students
                </p>
            </div>
        </div>

        {{-- COMPLETED TESTS --}}
        <div
            class="relative overflow-hidden
            rounded-[28px]
            border border-slate-200
            bg-white
            p-6 shadow-sm
            dark:border-white/10
            dark:bg-white/[0.03]"
        >
            <div
                class="absolute -right-10 -top-10
                h-32 w-32 rounded-full
                bg-cyan-500/10 blur-3xl"
            ></div>

            <div class="relative">
                <div
                    class="mb-5 flex h-14 w-14
                    items-center justify-center
                    rounded-2xl
                    bg-cyan-500/10
                    text-2xl"
                >
                    ✅
                </div>

                <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">
                    Tests Completed
                </p>

                <h2 class="mt-3 text-4xl font-black text-cyan-500">
                    {{ number_format($totalCompletedTests) }}
                </h2>

                <p class="mt-3 text-sm font-semibold text-cyan-500">
                    All completed submissions
                </p>
            </div>
        </div>

        {{-- AVERAGE PRETEST --}}
        <div
            class="relative overflow-hidden
            rounded-[28px]
            border border-slate-200
            bg-white
            p-6 shadow-sm
            dark:border-white/10
            dark:bg-white/[0.03]"
        >
            <div
                class="absolute -right-10 -top-10
                h-32 w-32 rounded-full
                bg-yellow-500/10 blur-3xl"
            ></div>

            <div class="relative">
                <div
                    class="mb-5 flex h-14 w-14
                    items-center justify-center
                    rounded-2xl
                    bg-yellow-500/10
                    text-2xl"
                >
                    📝
                </div>

                <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">
                    Average Pretest
                </p>

                <h2 class="mt-3 text-4xl font-black text-yellow-500">
                    {{ $formatScore($averagePretest) }}
                </h2>

                <p class="mt-3 text-sm font-semibold text-yellow-500">
                    Listening, Reading, Writing, Speaking
                </p>
            </div>
        </div>

        {{-- AVERAGE POSTTEST --}}
        <div
            class="relative overflow-hidden
            rounded-[28px]
            border border-slate-200
            bg-white
            p-6 shadow-sm
            dark:border-white/10
            dark:bg-white/[0.03]"
        >
            <div
                class="absolute -right-10 -top-10
                h-32 w-32 rounded-full
                bg-green-500/10 blur-3xl"
            ></div>

            <div class="relative">
                <div
                    class="mb-5 flex h-14 w-14
                    items-center justify-center
                    rounded-2xl
                    bg-green-500/10
                    text-2xl"
                >
                    🎯
                </div>

                <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">
                    Average Posttest
                </p>

                <h2 class="mt-3 text-4xl font-black text-green-500">
                    {{ $formatScore($averagePosttest) }}
                </h2>

                <p class="mt-3 text-sm font-semibold text-green-500">
                    Completed posttest scores
                </p>
            </div>
        </div>

        {{-- IMPROVEMENT --}}
        <div
            class="relative overflow-hidden
            rounded-[28px]
            border border-slate-200
            bg-white
            p-6 shadow-sm
            dark:border-white/10
            dark:bg-white/[0.03]"
        >
            <div
                class="absolute -right-10 -top-10
                h-32 w-32 rounded-full
                bg-blue-500/10 blur-3xl"
            ></div>

            <div class="relative">
                <div
                    class="mb-5 flex h-14 w-14
                    items-center justify-center
                    rounded-2xl
                    bg-blue-500/10
                    text-2xl"
                >
                    📈
                </div>

                <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">
                    Average Improvement
                </p>

                <h2
                    class="mt-3 text-4xl font-black
                    {{ $averageImprovement >= 0
                        ? 'text-green-500'
                        : 'text-red-500' }}"
                >
                    {{ $averageImprovement > 0 ? '+' : '' }}
                    {{ $formatScore($averageImprovement) }}
                </h2>

                <p class="mt-3 text-sm font-semibold text-blue-500">
                    Posttest minus pretest
                </p>
            </div>
        </div>
    </div>

    {{-- ACTIVITY AND SKILL PERFORMANCE --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
        {{-- WEEKLY ACTIVITY --}}
        <div
            class="xl:col-span-2
            rounded-[32px]
            border border-slate-200
            bg-white
            p-6 shadow-sm
            dark:border-white/10
            dark:bg-white/[0.03]
            lg:p-8"
        >
            <div
                class="flex flex-col gap-4 mb-8
                sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h2 class="text-2xl font-black">
                        Student Activity
                    </h2>

                    <p class="mt-2 text-slate-500 dark:text-slate-400">
                        Completed assessments during the last seven days.
                    </p>
                </div>

                <div
                    class="flex items-center gap-2
                    self-start
                    rounded-2xl
                    bg-cyan-500/10
                    px-4 py-2
                    text-sm font-bold
                    text-cyan-500"
                >
                    <span class="h-2 w-2 rounded-full bg-cyan-500"></span>
                    Assessment Activity
                </div>
            </div>

            <div
                class="flex h-[320px]
                items-end justify-between
                gap-3"
            >
                @foreach ($weeklyActivity as $activity)
                    @php
                        $heightPercentage = $activity['count'] > 0
                            ? max(
                                ($activity['count'] / $maxActivity) * 100,
                                10
                            )
                            : 3;
                    @endphp

                    <div
                        class="flex h-full flex-1
                        flex-col items-center justify-end"
                        title="{{ $activity['full_label'] }}: {{ $activity['count'] }} aktivitas"
                    >
                        <span
                            class="mb-3 text-sm font-black
                            text-slate-700 dark:text-slate-200"
                        >
                            {{ $activity['count'] }}
                        </span>

                        <div
                            class="w-full max-w-[64px]
                            rounded-t-[20px]
                            bg-gradient-to-t
                            from-cyan-600 to-cyan-300
                            shadow-lg shadow-cyan-500/20
                            transition-all duration-300"
                            style="height: {{ $heightPercentage }}%;"
                        ></div>

                        <span
                            class="mt-4 text-sm font-semibold
                            text-slate-500 dark:text-slate-400"
                        >
                            {{ $activity['label'] }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- SKILL PERFORMANCE --}}
        <div
            class="rounded-[32px]
            border border-slate-200
            bg-white
            p-6 shadow-sm
            dark:border-white/10
            dark:bg-white/[0.03]
            lg:p-8"
        >
            <div class="mb-8">
                <h2 class="text-2xl font-black">
                    Skill Performance
                </h2>

                <p class="mt-2 text-slate-500 dark:text-slate-400">
                    Average pretest and posttest score.
                </p>
            </div>

            <div class="space-y-7">
                @foreach ($skillPerformance as $performance)
                    <div>
                        <div class="mb-3 flex items-center justify-between">
                            <div>
                                <p class="font-black">
                                    {{ $performance['label'] }}
                                </p>

                                <p
                                    class="mt-1 text-xs font-semibold
                                    text-slate-500 dark:text-slate-400"
                                >
                                    Pre:
                                    {{ $formatScore($performance['pretest']) }}

                                    <span class="mx-1">•</span>

                                    Post:
                                    {{ $formatScore($performance['posttest']) }}
                                </p>
                            </div>

                            <span
                                class="text-lg font-black text-cyan-500"
                            >
                                {{ $formatScore($performance['posttest']) }}
                            </span>
                        </div>

                        <div
                            class="h-3 w-full overflow-hidden
                            rounded-full
                            bg-slate-100 dark:bg-white/5"
                        >
                            <div
                                class="h-full rounded-full
                                bg-gradient-to-r
                                from-cyan-400 to-cyan-600"
                                style="width: {{ min($performance['posttest'], 100) }}%;"
                            ></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- STUDENT SCORE TABLE --}}
    <div
        class="overflow-hidden
        rounded-[32px]
        border border-slate-200
        bg-white shadow-sm
        dark:border-white/10
        dark:bg-white/[0.03]"
    >
        <div
            class="flex flex-col gap-4
            border-b border-slate-200
            px-6 py-6
            dark:border-white/10
            lg:flex-row lg:items-center lg:justify-between
            lg:px-8"
        >
            <div>
                <h2 class="text-2xl font-black">
                    Student Assessment Scores
                </h2>

                <p class="mt-2 text-slate-500 dark:text-slate-400">
                    Scores for every student from pretest to posttest.
                </p>
            </div>

            <div
                class="self-start rounded-2xl
                bg-slate-100
                px-4 py-2
                text-sm font-bold
                text-slate-600
                dark:bg-white/5
                dark:text-slate-300"
            >
                {{ $students->total() }} Students
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[1900px]">
                <thead
                    class="border-b border-slate-200
                    bg-slate-50
                    dark:border-white/10
                    dark:bg-white/[0.03]"
                >
                    <tr>
                        <th
                            rowspan="2"
                            class="sticky left-0 z-20
                            min-w-[270px]
                            border-r border-slate-200
                            bg-slate-50
                            px-6 py-5
                            text-left text-sm font-black
                            text-slate-500
                            dark:border-white/10
                            dark:bg-[#0c1220]
                            dark:text-slate-400"
                        >
                            Student
                        </th>

                        <th
                            rowspan="2"
                            class="px-4 py-5 text-center
                            text-sm font-black text-slate-500
                            dark:text-slate-400"
                        >
                            Vocabulary
                        </th>

                        <th
                            colspan="2"
                            class="border-l border-slate-200
                            px-4 py-4 text-center
                            text-sm font-black
                            text-slate-500
                            dark:border-white/10
                            dark:text-slate-400"
                        >
                            Listening
                        </th>

                        <th
                            colspan="2"
                            class="border-l border-slate-200
                            px-4 py-4 text-center
                            text-sm font-black
                            text-slate-500
                            dark:border-white/10
                            dark:text-slate-400"
                        >
                            Reading
                        </th>

                        <th
                            colspan="2"
                            class="border-l border-slate-200
                            px-4 py-4 text-center
                            text-sm font-black
                            text-slate-500
                            dark:border-white/10
                            dark:text-slate-400"
                        >
                            Writing
                        </th>

                        <th
                            colspan="2"
                            class="border-l border-slate-200
                            px-4 py-4 text-center
                            text-sm font-black
                            text-slate-500
                            dark:border-white/10
                            dark:text-slate-400"
                        >
                            Speaking
                        </th>

                        <th
                            rowspan="2"
                            class="border-l border-slate-200
                            px-4 py-5 text-center
                            text-sm font-black
                            text-slate-500
                            dark:border-white/10
                            dark:text-slate-400"
                        >
                            Avg. Pre
                        </th>

                        <th
                            rowspan="2"
                            class="px-4 py-5 text-center
                            text-sm font-black text-slate-500
                            dark:text-slate-400"
                        >
                            Avg. Post
                        </th>

                        <th
                            rowspan="2"
                            class="px-4 py-5 text-center
                            text-sm font-black text-slate-500
                            dark:text-slate-400"
                        >
                            Overall
                        </th>

                        <th
                            rowspan="2"
                            class="px-4 py-5 text-center
                            text-sm font-black text-slate-500
                            dark:text-slate-400"
                        >
                            Improvement
                        </th>

                        <th
                            rowspan="2"
                            class="min-w-[190px]
                            px-6 py-5 text-left
                            text-sm font-black text-slate-500
                            dark:text-slate-400"
                        >
                            Completion
                        </th>
                    </tr>

                    <tr
                        class="border-t border-slate-200
                        dark:border-white/10"
                    >
                        @foreach (range(1, 4) as $index)
                            <th
                                class="border-l border-slate-200
                                px-4 py-3 text-center
                                text-xs font-black uppercase
                                text-yellow-600
                                dark:border-white/10
                                dark:text-yellow-400"
                            >
                                Pre
                            </th>

                            <th
                                class="px-4 py-3 text-center
                                text-xs font-black uppercase
                                text-green-600
                                dark:text-green-400"
                            >
                                Post
                            </th>
                        @endforeach
                    </tr>
                </thead>

                <tbody>
                    @forelse ($students as $student)
                        <tr
                            class="border-b border-slate-100
                            transition
                            hover:bg-slate-50
                            dark:border-white/5
                            dark:hover:bg-white/[0.02]"
                        >
                            {{-- STUDENT --}}
                            <td
                                class="sticky left-0 z-10
                                border-r border-slate-200
                                bg-white
                                px-6 py-5
                                dark:border-white/10
                                dark:bg-[#080d18]"
                            >
                                <div class="flex items-center gap-4">
                                    <div
                                        class="flex h-13 w-13
                                        min-w-[52px]
                                        items-center justify-center
                                        rounded-2xl
                                        bg-gradient-to-br
                                        from-cyan-400 to-blue-600
                                        text-lg font-black text-white
                                        shadow-lg shadow-cyan-500/20"
                                    >
                                        {{ $student['initial'] }}
                                    </div>

                                    <div class="min-w-0">
                                        <h3
                                            class="truncate font-black
                                            text-slate-900 dark:text-white"
                                        >
                                            {{ $student['name'] }}
                                        </h3>

                                        <p
                                            class="mt-1 truncate text-sm
                                            text-slate-500
                                            dark:text-slate-400"
                                        >
                                            {{ $student['email'] }}
                                        </p>

                                        <p
                                            class="mt-1 text-xs font-semibold
                                            text-cyan-500"
                                        >
                                            {{ $student['completed_lessons'] }}
                                            lessons completed
                                        </p>
                                    </div>
                                </div>
                            </td>

                            {{-- VOCABULARY --}}
                            <td class="px-4 py-5 text-center">
                                <span
                                    class="inline-flex min-w-[52px]
                                    justify-center rounded-xl
                                    px-3 py-2 text-sm font-black
                                    {{ $scoreClass($student['vocabulary']) }}"
                                >
                                    {{ $formatScore($student['vocabulary']) }}
                                </span>
                            </td>

                            {{-- LISTENING --}}
                            <td class="border-l border-slate-100 px-4 py-5 text-center dark:border-white/5">
                                <span class="inline-flex min-w-[52px] justify-center rounded-xl px-3 py-2 text-sm font-black {{ $scoreClass($student['scores']['listening']['pretest']) }}">
                                    {{ $formatScore($student['scores']['listening']['pretest']) }}
                                </span>
                            </td>

                            <td class="px-4 py-5 text-center">
                                <span class="inline-flex min-w-[52px] justify-center rounded-xl px-3 py-2 text-sm font-black {{ $scoreClass($student['scores']['listening']['posttest']) }}">
                                    {{ $formatScore($student['scores']['listening']['posttest']) }}
                                </span>
                            </td>

                            {{-- READING --}}
                            <td class="border-l border-slate-100 px-4 py-5 text-center dark:border-white/5">
                                <span class="inline-flex min-w-[52px] justify-center rounded-xl px-3 py-2 text-sm font-black {{ $scoreClass($student['scores']['reading']['pretest']) }}">
                                    {{ $formatScore($student['scores']['reading']['pretest']) }}
                                </span>
                            </td>

                            <td class="px-4 py-5 text-center">
                                <span class="inline-flex min-w-[52px] justify-center rounded-xl px-3 py-2 text-sm font-black {{ $scoreClass($student['scores']['reading']['posttest']) }}">
                                    {{ $formatScore($student['scores']['reading']['posttest']) }}
                                </span>
                            </td>

                            {{-- WRITING --}}
                            <td class="border-l border-slate-100 px-4 py-5 text-center dark:border-white/5">
                                <span class="inline-flex min-w-[52px] justify-center rounded-xl px-3 py-2 text-sm font-black {{ $scoreClass($student['scores']['writing']['pretest']) }}">
                                    {{ $formatScore($student['scores']['writing']['pretest']) }}
                                </span>
                            </td>

                            <td class="px-4 py-5 text-center">
                                <span class="inline-flex min-w-[52px] justify-center rounded-xl px-3 py-2 text-sm font-black {{ $scoreClass($student['scores']['writing']['posttest']) }}">
                                    {{ $formatScore($student['scores']['writing']['posttest']) }}
                                </span>
                            </td>

                            {{-- SPEAKING --}}
                            <td class="border-l border-slate-100 px-4 py-5 text-center dark:border-white/5">
                                <span class="inline-flex min-w-[52px] justify-center rounded-xl px-3 py-2 text-sm font-black {{ $scoreClass($student['scores']['speaking']['pretest']) }}">
                                    {{ $formatScore($student['scores']['speaking']['pretest']) }}
                                </span>
                            </td>

                            <td class="px-4 py-5 text-center">
                                <span class="inline-flex min-w-[52px] justify-center rounded-xl px-3 py-2 text-sm font-black {{ $scoreClass($student['scores']['speaking']['posttest']) }}">
                                    {{ $formatScore($student['scores']['speaking']['posttest']) }}
                                </span>
                            </td>

                            {{-- AVERAGE PRETEST --}}
                            <td class="border-l border-slate-100 px-4 py-5 text-center dark:border-white/5">
                                <span class="font-black text-yellow-600 dark:text-yellow-400">
                                    {{ $formatScore($student['average_pretest']) }}
                                </span>
                            </td>

                            {{-- AVERAGE POSTTEST --}}
                            <td class="px-4 py-5 text-center">
                                <span class="font-black text-green-600 dark:text-green-400">
                                    {{ $formatScore($student['average_posttest']) }}
                                </span>
                            </td>

                            {{-- OVERALL --}}
                            <td class="px-4 py-5 text-center">
                                <span class="inline-flex min-w-[58px] justify-center rounded-xl px-3 py-2 text-sm font-black {{ $scoreClass($student['average_score']) }}">
                                    {{ $formatScore($student['average_score']) }}
                                </span>
                            </td>

                            {{-- IMPROVEMENT --}}
                            <td class="px-4 py-5 text-center">
                                @if ($student['improvement'] === null)
                                    <span class="text-sm font-bold text-slate-400">
                                        -
                                    </span>
                                @else
                                    <span
                                        class="inline-flex min-w-[64px]
                                        justify-center rounded-xl
                                        px-3 py-2 text-sm font-black
                                        {{ $student['improvement'] >= 0
                                            ? 'bg-green-500/10 text-green-500'
                                            : 'bg-red-500/10 text-red-500' }}"
                                    >
                                        {{ $student['improvement'] > 0 ? '+' : '' }}
                                        {{ $formatScore($student['improvement']) }}
                                    </span>
                                @endif
                            </td>

                            {{-- COMPLETION --}}
                            <td class="px-6 py-5">
                                <div class="mb-2 flex items-center justify-between">
                                    <span
                                        class="text-xs font-bold
                                        text-slate-500 dark:text-slate-400"
                                    >
                                        {{ $student['completed_assessments'] }}
                                        /
                                        {{ $student['total_required_assessments'] }}
                                    </span>

                                    <span
                                        class="text-xs font-black text-cyan-500"
                                    >
                                        {{ $student['completion_percentage'] }}%
                                    </span>
                                </div>

                                <div
                                    class="h-2.5 w-full overflow-hidden
                                    rounded-full
                                    bg-slate-100 dark:bg-white/5"
                                >
                                    <div
                                        class="h-full rounded-full
                                        bg-gradient-to-r
                                        from-cyan-400 to-cyan-600"
                                        style="width: {{ $student['completion_percentage'] }}%;"
                                    ></div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="15"
                                class="px-8 py-16 text-center"
                            >
                                <div
                                    class="mx-auto flex h-20 w-20
                                    items-center justify-center
                                    rounded-3xl
                                    bg-slate-100
                                    text-4xl
                                    dark:bg-white/5"
                                >
                                    🔍
                                </div>

                                <h3 class="mt-5 text-xl font-black">
                                    Student data not found
                                </h3>

                                <p
                                    class="mt-2 text-slate-500
                                    dark:text-slate-400"
                                >
                                    No students match the selected search.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($students->hasPages())
            <div
                class="border-t border-slate-200
                px-6 py-5
                dark:border-white/10
                lg:px-8"
            >
                {{ $students->links() }}
            </div>
        @endif
    </div>
@endsection