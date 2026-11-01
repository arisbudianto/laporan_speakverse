<x-app-layout>

<div class="space-y-8">

<!-- HEADER -->
<section
    class="relative overflow-hidden rounded-[32px]
    border border-slate-200 dark:border-white/10
    bg-white/70 dark:bg-white/5
    backdrop-blur-2xl
    p-6 md:p-8 lg:p-10">

    <div
        class="absolute top-[-100px] right-[-100px]
        w-[250px] h-[250px]
        bg-purple-400/10 rounded-full blur-3xl">
    </div>

    <div class="relative z-10">

        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8">

            <div class="flex-1">

                <div
                    class="inline-flex items-center gap-2
                    px-4 py-2 rounded-full
                    bg-purple-500/10 border border-purple-400/20
                    text-purple-400 text-sm font-medium mb-5">

                    🎧 Listening Mission

                </div>

                <h1
                    class="text-3xl md:text-4xl lg:text-5xl
                    font-black leading-tight">

                    Unit 1 Listening
                    <br>
                    Listen & Learn 🚀

                </h1>

                <p
                    class="mt-5 text-slate-600 dark:text-slate-400
                    text-base md:text-lg
                    max-w-2xl leading-relaxed">

                    Listen carefully to the audio and understand the main ideas before starting the exercise.

                </p>

            </div>

            <!-- RIGHT STATS -->
            <div
                class="grid grid-cols-2 gap-4
                w-full lg:w-[340px]">

                <div
                    class="rounded-3xl
                    border border-slate-200 dark:border-white/10
                    bg-slate-50 dark:bg-white/5
                    p-5">

                    <p class="text-sm text-slate-500 mb-2">
                        Questions
                    </p>

                    <h3 class="text-3xl font-black text-purple-400">
                        {{ $material->questions->count() }}
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
                        10m
                    </h3>

                </div>

                <div
                    class="rounded-3xl
                    border border-slate-200 dark:border-white/10
                    bg-slate-50 dark:bg-white/5
                    p-5">

                    <p class="text-sm text-slate-500 mb-2">
                        Level
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

<!-- LISTENING MATERIAL -->
<section
    class="rounded-[32px]
    border border-slate-200 dark:border-white/10
    bg-white/70 dark:bg-white/5
    backdrop-blur-xl
    p-8">

    <div class="flex items-center gap-3 mb-6">

        <div
            class="w-14 h-14 rounded-2xl
            bg-purple-500/10
            flex items-center justify-center">

            <span class="text-3xl">
                🎧
            </span>

        </div>

        <div>

            <h2 class="text-2xl font-bold">
                Listening Material
            </h2>

            <p class="text-slate-500">
                Listen to the audio below carefully.
            </p>

        </div>

    </div>

    <h3 class="text-2xl font-bold mb-4">
        {{ $material->title }}
    </h3>

    @if($material->instruction)

        <div class="mb-6">

            <p class="text-purple-400 font-medium">
                {{ $material->instruction }}
            </p>

        </div>

    @endif

    @if($material->audio_file)

        <div
            class="rounded-2xl
            border border-slate-200 dark:border-white/10
            p-6">

            <audio controls class="w-full">

                <source
                    src="{{ asset('storage/'.$material->audio_file) }}"
                    type="audio/mpeg">

                Your browser does not support audio.

            </audio>

        </div>

    @else

        <div
            class="rounded-2xl
            border border-red-300
            bg-red-50
            p-6 text-red-600">

            Audio file not available.

        </div>

    @endif

</section>

<!-- START EXERCISE -->
<section
    class="rounded-[32px]
    border border-slate-200 dark:border-white/10
    bg-white/70 dark:bg-white/5
    backdrop-blur-xl
    p-8">

    <h2 class="text-2xl font-bold">
        Ready for the Exercise?
    </h2>

    <p class="text-slate-500 mt-2">
        Once you finish listening to the audio, continue to answer the questions.
    </p>

    <a
        href="{{ route('student.listening.quiz', $lesson) }}"
        class="mt-6 inline-flex items-center justify-center
        w-full py-4 rounded-2xl

        bg-gradient-to-r
        from-purple-500
        to-indigo-600

        text-white font-semibold

        hover:scale-[1.02]
        transition-all duration-300
        shadow-lg shadow-purple-500/20">

        Start Exercise

    </a>

</section>


</div>

</x-app-layout>
