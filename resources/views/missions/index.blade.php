<x-app-layout>

    @php
        $stageIcons = [
            'pretest' => 'clipboard',
            'posttest' => 'clipboard',
            'unit' => 'book',
        ];

        $stageGradients = [
            'pretest' => 'from-orange-500 to-red-500',
            'posttest' => 'from-orange-500 to-red-500',
            'unit' => 'from-cyan-400 to-blue-600',
        ];

        $skillIcons = [
            'listening' => '🎧',
            'reading' => '📖',
            'writing' => '✍️',
            'speaking' => '🎙️',
        ];

        $skillDescriptions = [
            'listening' => 'Listen carefully and identify important information.',
            'reading' => 'Read the text and understand the main ideas.',
            'writing' => 'Organize and write your ideas effectively.',
            'speaking' => 'Practice expressing ideas confidently.',
        ];

        function missionSkillRoute($unit, $skill)
        {
            // Pretest
            if ($unit->type === 'pretest') {
                return route('student.assessment.show', [
                    'type' => 'pretest',
                    'skill' => $skill,
                ]);
            }

            // Posttest
            if ($unit->type === 'posttest') {
                return route('student.assessment.show', [
                    'type' => 'posttest',
                    'skill' => $skill,
                ]);
            }

            // Cari lesson sesuai skill pada unit
            $lesson = $unit->lessons
                ->firstWhere('skill_type', $skill);

            if (!$lesson) {
                return '#';
            }

            return match ($skill) {

                'listening' => route('student.listening', [
                    'lesson' => $lesson
                ]),

                'reading' => route('student.reading', [
                    'lesson' => $lesson
                ]),

                'writing' => route('student.writing', [
                    'lesson' => $lesson
                ]),

                'speaking' => route('student.speaking', [
                    'lesson' => $lesson
                ]),

                default => '#',
            };
        }

    @endphp

    <div class="space-y-10">

        <!-- HEADER -->
        <section
            class="relative overflow-hidden rounded-[40px]
            border border-slate-200 dark:border-white/10
            bg-white/80 dark:bg-white/5
            backdrop-blur-2xl
            p-8 lg:p-12">

            <div class="absolute -top-28 -right-28 w-80 h-80 bg-cyan-400/20 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-28 -left-28 w-80 h-80 bg-purple-500/10 rounded-full blur-3xl"></div>

            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8">

                <div>
                    <div
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-full
                        bg-cyan-500/10 text-cyan-500 font-bold text-sm mb-5">
                        🎯 Learning Path
                    </div>

                    <h1 class="text-4xl lg:text-6xl font-black leading-tight">
                        Complete Missions
                        <br>
                        Unlock Your Journey 🚀
                    </h1>

                    <p class="mt-5 text-slate-500 max-w-2xl text-base lg:text-lg leading-relaxed">
                        Complete each stage step by step. Finish all Pre-Test skills first to unlock Unit 1, then
                        continue until the Post-Test.
                    </p>
                </div>

                <div
                    class="rounded-[32px] bg-slate-100 dark:bg-white/5
                    border border-slate-200 dark:border-white/10 p-6 min-w-[260px]">

                    <p class="text-sm text-slate-500">
                        Overall Progress
                    </p>

                    <h2 class="mt-2 text-6xl font-black text-cyan-400">
                        {{ $overallProgress }}%
                    </h2>

                    <p class="mt-2 text-sm text-slate-500">
                        {{ $completedTotal }} of {{ $totalStages }} stages completed
                    </p>

                    <div class="mt-5 w-full h-3 rounded-full bg-slate-200 dark:bg-white/10 overflow-hidden">
                        <div class="h-full rounded-full bg-gradient-to-r from-cyan-400 to-blue-600"
                            style="width: {{ $overallProgress }}%">
                        </div>
                    </div>

                </div>

            </div>

        </section>

        <!-- TIMELINE -->
        <section>

            <div class="mb-8">
                <h2 class="text-2xl font-black">
                    Mission Roadmap
                </h2>

                <p class="mt-1 text-slate-500">
                    Unlock every mission by completing the previous stage.
                </p>
            </div>

            <div class="relative">

                <div
                    class="absolute left-7 lg:left-8 top-8 bottom-8 w-[4px]
                    bg-slate-300 dark:bg-white/10 rounded-full">
                </div>

                <div class="space-y-14">

                    @foreach ($units as $unit)
                        @php
                            $isUnlocked = $unlockMap[$unit->id] ?? false;
                            $progress = $completedMap[$unit->id] ?? [
                                'completed_skills' => [],
                                'completed_count' => 0,
                                'is_completed' => false,
                            ];

                            $completedSkills = $progress['completed_skills'];
                            $isStageCompleted = $progress['is_completed'];
                            $completedCount = $progress['completed_count'];

                            $stageTitle =
                                $unit->type === 'unit'
                                    ? $unit->title
                                    : strtoupper(str_replace('test', '-TEST', $unit->type));

                            $gradient = $stageGradients[$unit->type] ?? 'from-cyan-400 to-blue-600';

                            $stageProgress = count($skills) > 0 ? round(($completedCount / count($skills)) * 100) : 0;
                        @endphp

                        <div class="relative flex gap-6">

                            <!-- STAGE ICON -->
                            <div
                                class="relative z-10 w-16 h-16 shrink-0 rounded-2xl
                                bg-gradient-to-r {{ $isUnlocked ? $gradient : 'from-slate-500 to-slate-700' }}
                                flex items-center justify-center text-white text-3xl
                                shadow-lg {{ $isUnlocked ? 'shadow-cyan-500/20' : '' }}">

                                @if ($isStageCompleted)
                                    ✅
                                @elseif (!$isUnlocked)
                                    🔒
                                @elseif ($unit->type === 'pretest' || $unit->type === 'posttest')
                                    📋
                                @else
                                    📘
                                @endif

                            </div>

                            <div class="flex-1 min-w-0">

                                <!-- STAGE HEADER -->
                                <div class="mb-6 flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">

                                    <div>
                                        <div class="flex flex-wrap items-center gap-3 mb-2">
                                            <h3 class="text-3xl font-black">
                                                {{ $stageTitle }}
                                            </h3>

                                            @if ($isStageCompleted)
                                                <span
                                                    class="px-3 py-1 rounded-full text-xs font-black
                                                    bg-green-500/10 text-green-500">
                                                    Completed
                                                </span>
                                            @elseif ($isUnlocked)
                                                <span
                                                    class="px-3 py-1 rounded-full text-xs font-black
                                                    bg-cyan-500/10 text-cyan-400">
                                                    Available
                                                </span>
                                            @else
                                                <span
                                                    class="px-3 py-1 rounded-full text-xs font-black
                                                    bg-slate-500/10 text-slate-400">
                                                    Locked
                                                </span>
                                            @endif
                                        </div>

                                        <p class="text-slate-500">
                                            {{ $unit->subtitle }}
                                        </p>
                                    </div>

                                    <div class="w-full lg:w-64">
                                        <div class="flex justify-between mb-2 text-sm">
                                            <span class="text-slate-500">
                                                Progress
                                            </span>

                                            <span class="font-black text-cyan-400">
                                                {{ $completedCount }}/{{ count($skills) }}
                                            </span>
                                        </div>

                                        <div
                                            class="w-full h-3 rounded-full bg-slate-200 dark:bg-white/10 overflow-hidden">
                                            <div class="h-full rounded-full bg-gradient-to-r from-cyan-400 to-blue-600"
                                                style="width: {{ $stageProgress }}%">
                                            </div>
                                        </div>
                                    </div>

                                </div>

                                <!-- PRETEST / POSTTEST -->
                                @if ($unit->type === 'pretest' || $unit->type === 'posttest')
                                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">

                                        @foreach ($skills as $skill)
                                            @php
                                                $skillCompleted = in_array($skill, $completedSkills);
                                                $href = missionSkillRoute($unit, $skill);
                                            @endphp

                                            @if ($isUnlocked)
                                                <a href="{{ $href }}"
                                                    class="group relative block rounded-[28px]
                                                    border border-slate-200 dark:border-white/10
                                                    bg-white/80 dark:bg-white/5
                                                    backdrop-blur-xl p-6
                                                    hover:-translate-y-1 hover:border-cyan-400/40
                                                    hover:shadow-xl hover:shadow-cyan-500/10
                                                    active:scale-[0.98]
                                                    transition-all duration-300">

                                                    <div
                                                        class="w-12 h-12 rounded-2xl
                                                        bg-cyan-500/10 border border-cyan-500/20
                                                        flex items-center justify-center text-2xl mb-5">
                                                        {{ $skillIcons[$skill] }}
                                                    </div>

                                                    <h4 class="text-2xl font-black capitalize">
                                                        {{ $skill }}
                                                    </h4>

                                                    <p class="mt-2 text-slate-500">
                                                        {{ ucfirst($skill) }} assessment.
                                                    </p>

                                                    <div class="mt-5 flex items-center justify-between">

                                                        @if ($skillCompleted)
                                                            <span
                                                                class="px-3 py-1 rounded-full text-xs font-black
                                                                bg-green-500/10 text-green-500">
                                                                Completed
                                                            </span>
                                                        @else
                                                            <span
                                                                class="px-3 py-1 rounded-full text-xs font-black
                                                                bg-cyan-500/10 text-cyan-400">
                                                                Start
                                                            </span>
                                                        @endif

                                                        <span
                                                            class="text-slate-400 group-hover:text-cyan-400 transition">
                                                            →
                                                        </span>

                                                    </div>

                                                </a>
                                            @else
                                                <div
                                                    class="relative rounded-[28px]
                                                    border border-slate-200 dark:border-white/10
                                                    bg-slate-100/80 dark:bg-white/[0.03]
                                                    backdrop-blur-xl p-6 opacity-70">

                                                    <div class="absolute top-5 right-5 text-slate-400">
                                                        🔒
                                                    </div>

                                                    <div
                                                        class="w-12 h-12 rounded-2xl
                                                        bg-slate-500/10 border border-slate-500/20
                                                        flex items-center justify-center text-2xl mb-5 grayscale">
                                                        {{ $skillIcons[$skill] }}
                                                    </div>

                                                    <h4 class="text-2xl font-black capitalize text-slate-500">
                                                        {{ $skill }}
                                                    </h4>

                                                    <p class="mt-2 text-slate-500">
                                                        Complete previous stage to unlock.
                                                    </p>

                                                    <span
                                                        class="inline-flex mt-5 px-3 py-1 rounded-full text-xs font-black
                                                        bg-slate-500/10 text-slate-400">
                                                        Locked
                                                    </span>

                                                </div>
                                            @endif
                                        @endforeach

                                    </div>

                                    <!-- UNIT -->
                                @else
                                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">

                                        @foreach ($skills as $skill)
                                            @php
                                                $skillCompleted = in_array($skill, $completedSkills);
                                                $href = missionSkillRoute($unit, $skill);
                                                $hasRoute = $href !== '#';
                                            @endphp

                                            @if ($isUnlocked && $hasRoute)
                                                <a href="{{ $href }}"
                                                    class="group relative block rounded-[28px]
                                                    border border-slate-200 dark:border-white/10
                                                    bg-white/80 dark:bg-white/5
                                                    backdrop-blur-xl p-6
                                                    hover:-translate-y-1 hover:border-cyan-400/40
                                                    hover:shadow-xl hover:shadow-cyan-500/10
                                                    active:scale-[0.98]
                                                    transition-all duration-300">

                                                    <div
                                                        class="w-12 h-12 rounded-2xl
                                                        bg-cyan-500/10 border border-cyan-500/20
                                                        flex items-center justify-center text-2xl mb-5">
                                                        {{ $skillIcons[$skill] }}
                                                    </div>

                                                    <h4 class="text-2xl font-black capitalize">
                                                        {{ $skill }}
                                                    </h4>

                                                    <p class="mt-2 text-slate-500">
                                                        {{ $skillDescriptions[$skill] }}
                                                    </p>

                                                    <div class="mt-5 flex items-center justify-between">
                                                        @if ($skillCompleted)
                                                            <span
                                                                class="px-3 py-1 rounded-full text-xs font-black
                                                                bg-green-500/10 text-green-500">
                                                                Completed
                                                            </span>
                                                        @else
                                                            <span
                                                                class="px-3 py-1 rounded-full text-xs font-black
                                                                bg-cyan-500/10 text-cyan-400">
                                                                Open
                                                            </span>
                                                        @endif

                                                        <span
                                                            class="text-slate-400 group-hover:text-cyan-400 transition">
                                                            →
                                                        </span>
                                                    </div>

                                                </a>
                                            @else
                                                <div
                                                    class="relative rounded-[28px]
                                                    border border-slate-200 dark:border-white/10
                                                    bg-slate-100/80 dark:bg-white/[0.03]
                                                    backdrop-blur-xl p-6 opacity-70">

                                                    <div class="absolute top-5 right-5 text-slate-400">
                                                        🔒
                                                    </div>

                                                    <div
                                                        class="w-12 h-12 rounded-2xl
                                                        bg-slate-500/10 border border-slate-500/20
                                                        flex items-center justify-center text-2xl mb-5 grayscale">
                                                        {{ $skillIcons[$skill] }}
                                                    </div>

                                                    <h4 class="text-2xl font-black capitalize text-slate-500">
                                                        {{ $skill }}
                                                    </h4>

                                                    <p class="mt-2 text-slate-500">
                                                        @if (!$isUnlocked)
                                                            Complete previous stage to unlock this activity.
                                                        @else
                                                            This unit activity route has not been connected yet.
                                                        @endif
                                                    </p>

                                                    <span
                                                        class="inline-flex mt-5 px-3 py-1 rounded-full text-xs font-black
                                                        bg-slate-500/10 text-slate-400">
                                                        Locked
                                                    </span>

                                                </div>
                                            @endif
                                        @endforeach

                                    </div>
                                @endif

                            </div>

                        </div>
                    @endforeach

                </div>

            </div>

        </section>

    </div>

</x-app-layout>
