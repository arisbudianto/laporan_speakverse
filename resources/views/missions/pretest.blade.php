<x-app-layout>

    <div class="space-y-8">

        <!-- HEADER -->
        <section
            class="relative overflow-hidden rounded-[32px]
        border border-slate-200 dark:border-white/10
        bg-white/70 dark:bg-white/5
        backdrop-blur-2xl
        p-6 md:p-8 lg:p-10">

            <!-- GLOW -->
            <div
                class="absolute top-[-100px] right-[-100px]
            w-[250px] h-[250px]
            bg-cyan-400/10 rounded-full blur-3xl">
            </div>

            <div class="relative z-10">

                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8">

                    <!-- LEFT -->
                    <div class="flex-1">

                        <div
                            class="inline-flex items-center gap-2
                        px-4 py-2 rounded-full
                        bg-cyan-500/10 border border-cyan-400/20
                        text-cyan-400 text-sm font-medium mb-5">

                            📝 English Placement Test

                        </div>

                        <h1 class="text-3xl md:text-4xl lg:text-5xl
                        font-black leading-tight">

                            Pre-Test Assessment
                            <br>
                            Measure Your Skills 🚀

                        </h1>

                        <p
                            class="mt-5 text-slate-600 dark:text-slate-400
                        text-base md:text-lg
                        max-w-2xl leading-relaxed">

                            Complete the Listening, Reading, Writing, and Speaking
                            assessments to determine your English proficiency level
                            before starting your learning journey.

                        </p>

                    </div>

                    <!-- RIGHT STATS -->
                    <div class="grid grid-cols-2 gap-4
                    w-full lg:w-[340px]">

                        <div
                            class="rounded-3xl
                        border border-slate-200 dark:border-white/10
                        bg-slate-50 dark:bg-white/5
                        p-5">

                            <p class="text-sm text-slate-500 mb-2">
                                Total Sections
                            </p>

                            <h3 class="text-3xl font-black text-cyan-400">
                                4
                            </h3>

                        </div>

                        <div
                            class="rounded-3xl
                        border border-slate-200 dark:border-white/10
                        bg-slate-50 dark:bg-white/5
                        p-5">

                            <p class="text-sm text-slate-500 mb-2">
                                Duration
                            </p>

                            <h3 class="text-3xl font-black text-blue-400">
                                30m
                            </h3>

                        </div>

                        <div
                            class="rounded-3xl
                        border border-slate-200 dark:border-white/10
                        bg-slate-50 dark:bg-white/5
                        p-5">

                            <p class="text-sm text-slate-500 mb-2">
                                Difficulty
                            </p>

                            <h3 class="text-2xl font-black text-orange-400">
                                Basic
                            </h3>

                        </div>

                        <div
                            class="rounded-3xl
                        border border-slate-200 dark:border-white/10
                        bg-slate-50 dark:bg-white/5
                        p-5">

                            <p class="text-sm text-slate-500 mb-2">
                                Status
                            </p>

                            <h3 class="text-2xl font-black text-green-400">
                                Ready
                            </h3>

                        </div>

                    </div>

                </div>

            </div>

        </section>

        <!-- SKILLS -->
        <section>

            <div class="mb-6">

                <h2 class="text-2xl font-bold">
                    Assessment Sections
                </h2>

                <p class="text-slate-500 mt-2">
                    Complete all four sections to receive your English proficiency result.
                </p>

            </div>

            <div
                class="grid
            grid-cols-1
            sm:grid-cols-2
            xl:grid-cols-4
            gap-6">

                <!-- LISTENING -->
                <div
                    class="rounded-[28px]
                border border-slate-200 dark:border-white/10
                bg-white/70 dark:bg-white/5
                backdrop-blur-xl
                p-6">

                    <div
                        class="w-14 h-14 rounded-2xl
                    bg-cyan-500/10
                    flex items-center justify-center
                    mb-5">

                        <x-heroicon-o-musical-note class="w-7 h-7 text-cyan-400" />

                    </div>

                    <h3 class="text-2xl font-bold">
                        Listening
                    </h3>

                    <p class="mt-3 text-slate-500 min-h-[72px]">
                        Listen to audio recordings and answer comprehension questions.
                    </p>

                    <a href="{{ route('student.assessment.show', ['type' => 'pretest', 'skill' => 'listening']) }}"
                        class="mt-6 inline-flex items-center justify-center
                    w-full py-3 rounded-2xl

                    bg-gradient-to-r from-cyan-500 to-blue-600 text-white font-semibold hover:scale-[1.02] transition-all duration-300 shadow-lg shadow-cyan-500/20">

                        Start Listening

                    </a>

                </div>

                <!-- READING -->
                <div
                    class="rounded-[28px]
                border border-slate-200 dark:border-white/10
                bg-white/70 dark:bg-white/5
                backdrop-blur-xl
                p-6">

                    <div
                        class="w-14 h-14 rounded-2xl
                    bg-cyan-500/10
                    flex items-center justify-center
                    mb-5">

                        <x-heroicon-o-book-open class="w-7 h-7 text-cyan-400" />

                    </div>

                    <h3 class="text-2xl font-bold">
                        Reading
                    </h3>

                    <p class="mt-3 text-slate-500 min-h-[72px]">
                        Read passages and identify key information and meaning.
                    </p>

                    <a href="{{ route('student.assessment.show', ['type' => 'pretest', 'skill' => 'reading']) }}"
                        class="mt-6 inline-flex items-center justify-center
                    w-full py-3 rounded-2xl

                    bg-gradient-to-r from-cyan-500 to-blue-600 text-white font-semibold hover:scale-[1.02] transition-all duration-300 shadow-lg shadow-cyan-500/20">

                        Start Reading

                    </a>

                </div>

                <!-- WRITING -->
                <div
                    class="rounded-[28px]
                border border-slate-200 dark:border-white/10
                bg-white/70 dark:bg-white/5
                backdrop-blur-xl
                p-6">

                    <div
                        class="w-14 h-14 rounded-2xl
                    bg-cyan-500/10
                    flex items-center justify-center
                    mb-5">

                        <x-heroicon-o-pencil-square class="w-7 h-7 text-cyan-400" />

                    </div>

                    <h3 class="text-2xl font-bold">
                        Writing
                    </h3>

                    <p class="mt-3 text-slate-500 min-h-[72px]">
                        Write responses and demonstrate your writing skills.
                    </p>

                    <a href="{{ route('student.assessment.show', ['type' => 'pretest', 'skill' => 'writing']) }}"
                        class="mt-6 inline-flex items-center justify-center
                    w-full py-3 rounded-2xl

                    bg-gradient-to-r from-cyan-500 to-blue-600 text-white font-semibold hover:scale-[1.02] transition-all duration-300 shadow-lg shadow-cyan-500/20">

                        Start Writing

                    </a>

                </div>

                <!-- SPEAKING -->
                <div
                    class="rounded-[28px]
                border border-slate-200 dark:border-white/10
                bg-white/70 dark:bg-white/5
                backdrop-blur-xl
                p-6">

                    <div
                        class="w-14 h-14 rounded-2xl
                    bg-cyan-500/10
                    flex items-center justify-center
                    mb-5">

                        <x-heroicon-o-microphone class="w-7 h-7 text-cyan-400" />

                    </div>

                    <h3 class="text-2xl font-bold">
                        Speaking
                    </h3>

                    <p class="mt-3 text-slate-500 min-h-[72px]">
                        Record your voice and evaluate pronunciation skills.
                    </p>

                    <a href="{{ route('student.assessment.show', ['type' => 'pretest', 'skill' => 'speaking']) }}"
                        class="mt-6 inline-flex items-center justify-center
                    w-full py-3 rounded-2xl

                    bg-gradient-to-r from-cyan-500 to-blue-600 text-white font-semibold hover:scale-[1.02] transition-all duration-300 shadow-lg shadow-cyan-500/20">

                        Start Speaking

                    </a>

                </div>

            </div>

        </section>

    </div>

</x-app-layout>
