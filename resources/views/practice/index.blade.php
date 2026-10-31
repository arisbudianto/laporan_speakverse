<x-app-layout>

    <div class="space-y-8">

        <!-- HERO -->
        <section
            class="relative overflow-hidden rounded-[32px]
            border border-slate-200 dark:border-white/10
            bg-white/70 dark:bg-white/5
            backdrop-blur-2xl
            p-8 lg:p-10">

            <!-- GLOW -->
            <div
                class="absolute top-[-120px] right-[-120px]
                w-[280px] h-[280px]
                bg-cyan-400/10 rounded-full blur-3xl">
            </div>

            <div class="relative z-10">

                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-10">

                    <!-- LEFT -->
                    <div>

                        <div
                            class="inline-flex items-center gap-2
                            px-4 py-2 rounded-full
                            bg-cyan-500/10 border border-cyan-400/20
                            text-cyan-400 text-sm font-medium mb-5">

                            🎤 AI Speaking Practice

                        </div>

                        <h1 class="text-4xl lg:text-5xl font-black leading-tight">

                            Practice Your
                            <br>
                            English Speaking 🚀

                        </h1>

                        <p
                            class="mt-5 text-slate-600 dark:text-slate-400
                            text-lg max-w-2xl leading-relaxed">

                            Improve pronunciation, fluency, confidence,
                            and speaking accuracy using AI-powered
                            speaking exercises and real conversation simulations.

                        </p>

                        <!-- BUTTONS -->
                        <div class="flex flex-wrap gap-4 mt-8">

                            <button
                                class="px-6 py-4 rounded-2xl
                                bg-gradient-to-r from-cyan-500 to-blue-600
                                text-white font-semibold
                                hover:scale-[1.02]
                                transition-all duration-300
                                shadow-lg shadow-cyan-500/20">

                                Start Speaking

                            </button>

                            <button
                                class="px-6 py-4 rounded-2xl
                                border border-slate-300 dark:border-white/10
                                bg-white dark:bg-white/5
                                text-slate-700 dark:text-white
                                font-semibold
                                hover:bg-slate-100 dark:hover:bg-white/10
                                transition">

                                View History

                            </button>

                        </div>

                    </div>

                    <!-- RIGHT -->
                    <div class="grid grid-cols-2 gap-5 min-w-[320px]">

                        <!-- SCORE -->
                        <div
                            class="rounded-3xl border border-slate-200 dark:border-white/10
                            bg-slate-50 dark:bg-white/5 p-6">

                            <p class="text-sm text-slate-500 dark:text-slate-400 mb-2">
                                Pronunciation
                            </p>

                            <h3 class="text-4xl font-black text-cyan-400">
                                92%
                            </h3>

                        </div>

                        <!-- FLUENCY -->
                        <div
                            class="rounded-3xl border border-slate-200 dark:border-white/10
                            bg-slate-50 dark:bg-white/5 p-6">

                            <p class="text-sm text-slate-500 dark:text-slate-400 mb-2">
                                Fluency
                            </p>

                            <h3 class="text-4xl font-black text-green-400">
                                88%
                            </h3>

                        </div>

                        <!-- STREAK -->
                        <div
                            class="rounded-3xl border border-slate-200 dark:border-white/10
                            bg-slate-50 dark:bg-white/5 p-6">

                            <p class="text-sm text-slate-500 dark:text-slate-400 mb-2">
                                Practice Streak
                            </p>

                            <h3 class="text-4xl font-black text-orange-400">
                                16🔥
                            </h3>

                        </div>

                        <!-- LEVEL -->
                        <div
                            class="rounded-3xl border border-slate-200 dark:border-white/10
                            bg-slate-50 dark:bg-white/5 p-6">

                            <p class="text-sm text-slate-500 dark:text-slate-400 mb-2">
                                Speaking Level
                            </p>

                            <h3 class="text-3xl font-black">
                                Advanced
                            </h3>

                        </div>

                    </div>

                </div>

            </div>

        </section>

        <!-- PRACTICE MODES -->
        <section>

            <div class="mb-6">

                <h2 class="text-2xl font-bold">
                    Practice Modes
                </h2>

                <p class="text-slate-500 dark:text-slate-400 mt-1">
                    Choose your preferred speaking practice session.
                </p>

            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">

                <!-- CARD -->
                <div
                    class="rounded-[30px]
                    border border-slate-200 dark:border-white/10
                    bg-white/70 dark:bg-white/5
                    backdrop-blur-xl
                    p-7
                    hover:scale-[1.02]
                    transition-all duration-300">

                    <div
                        class="w-16 h-16 rounded-3xl
                        bg-cyan-500/10
                        flex items-center justify-center
                        text-3xl mb-6">

                        🎙️

                    </div>

                    <h3 class="text-2xl font-black mb-3">
                        Pronunciation Test
                    </h3>

                    <p class="text-slate-500 dark:text-slate-400 leading-relaxed">

                        Train pronunciation accuracy using AI speech recognition.

                    </p>

                    <button
                        class="mt-8 w-full py-4 rounded-2xl
                        bg-gradient-to-r from-cyan-500 to-blue-600
                        text-white font-semibold
                        hover:scale-[1.01]
                        transition-all duration-300">

                        Start Practice

                    </button>

                </div>

                <!-- CARD -->
                <div
                    class="rounded-[30px]
                    border border-slate-200 dark:border-white/10
                    bg-white/70 dark:bg-white/5
                    backdrop-blur-xl
                    p-7
                    hover:scale-[1.02]
                    transition-all duration-300">

                    <div
                        class="w-16 h-16 rounded-3xl
                        bg-purple-500/10
                        flex items-center justify-center
                        text-3xl mb-6">

                        🧠

                    </div>

                    <h3 class="text-2xl font-black mb-3">
                        AI Conversation
                    </h3>

                    <p class="text-slate-500 dark:text-slate-400 leading-relaxed">

                        Practice real-time English conversation with AI assistant.

                    </p>

                    <button
                        class="mt-8 w-full py-4 rounded-2xl
                        border border-slate-300 dark:border-white/10
                        bg-white dark:bg-white/5
                        font-semibold
                        hover:bg-slate-100 dark:hover:bg-white/10
                        transition-all duration-300">

                        Start Chat

                    </button>

                </div>

                <!-- CARD -->
                <div
                    class="rounded-[30px]
                    border border-slate-200 dark:border-white/10
                    bg-white/70 dark:bg-white/5
                    backdrop-blur-xl
                    p-7
                    hover:scale-[1.02]
                    transition-all duration-300">

                    <div
                        class="w-16 h-16 rounded-3xl
                        bg-orange-500/10
                        flex items-center justify-center
                        text-3xl mb-6">

                        📚

                    </div>

                    <h3 class="text-2xl font-black mb-3">
                        Vocabulary Speaking
                    </h3>

                    <p class="text-slate-500 dark:text-slate-400 leading-relaxed">

                        Learn vocabulary and practice sentence speaking naturally.

                    </p>

                    <button
                        class="mt-8 w-full py-4 rounded-2xl
                        border border-slate-300 dark:border-white/10
                        bg-white dark:bg-white/5
                        font-semibold
                        hover:bg-slate-100 dark:hover:bg-white/10
                        transition-all duration-300">

                        Practice Now

                    </button>

                </div>

            </div>

        </section>

        <!-- RECENT PRACTICE -->
        <section>

            <div class="mb-6">

                <h2 class="text-2xl font-bold">
                    Recent Practice
                </h2>

                <p class="text-slate-500 dark:text-slate-400 mt-1">
                    Your latest speaking sessions and AI evaluations.
                </p>

            </div>

            <div class="space-y-4">

                <!-- ITEM -->
                <div
                    class="rounded-3xl
                    border border-slate-200 dark:border-white/10
                    bg-white/70 dark:bg-white/5
                    backdrop-blur-xl
                    p-6
                    flex flex-col lg:flex-row
                    lg:items-center lg:justify-between gap-5">

                    <div class="flex items-center gap-5">

                        <div
                            class="w-16 h-16 rounded-2xl
                            bg-cyan-500/10
                            flex items-center justify-center
                            text-3xl">

                            🎤

                        </div>

                        <div>

                            <h3 class="text-xl font-bold">
                                Pronunciation Session
                            </h3>

                            <p class="text-slate-500 dark:text-slate-400 mt-1">
                                AI evaluated your pronunciation and fluency.
                            </p>

                        </div>

                    </div>

                    <div class="flex items-center gap-4">

                        <span
                            class="px-4 py-2 rounded-full
                            bg-green-500/10 text-green-400
                            text-sm font-medium">

                            Score: 92%

                        </span>

                        <button
                            class="px-5 py-3 rounded-2xl
                            bg-gradient-to-r from-cyan-500 to-blue-600
                            text-white font-medium">

                            View Result

                        </button>

                    </div>

                </div>

            </div>

        </section>

    </div>

</x-app-layout>
