@extends('layouts.admin')

@section('content')
    @php
        $isAssessment = in_array(
            $lesson->unit?->type,
            [
                'pretest',
                'posttest',
            ],
            true
        );

        $assessmentLabel = $isAssessment
            ? strtoupper($lesson->unit->type)
            : null;
    @endphp

    <div class="mx-auto max-w-5xl space-y-6">

        {{-- HEADER --}}
        <div>
            <a
                href="{{ route(
                    'admin.listening-materials.index',
                    $lesson->id
                ) }}"
                class="mb-4 inline-flex items-center gap-2
                text-sm font-semibold text-slate-500
                transition hover:text-slate-900
                dark:text-slate-400
                dark:hover:text-white">

                <span>←</span>
                Back to Listening Materials
            </a>

            <div class="flex flex-wrap items-center gap-2">

                @if ($isAssessment)
                    <span
                        class="inline-flex rounded-full
                        bg-orange-100 px-3 py-1.5
                        text-xs font-bold text-orange-700
                        dark:bg-orange-500/10
                        dark:text-orange-300">

                        {{ $assessmentLabel }}
                    </span>
                @endif

                <span
                    class="inline-flex rounded-full
                    bg-blue-100 px-3 py-1.5
                    text-xs font-bold text-blue-700
                    dark:bg-blue-500/10
                    dark:text-blue-300">

                    Listening
                </span>
            </div>

            <h1
                class="mt-3 text-3xl font-black
                text-slate-900 dark:text-white">

                Add Listening Material
            </h1>

            <p
                class="mt-2 max-w-2xl
                text-slate-500 dark:text-slate-400">

                Add the audio, transcript or passage,
                and instructions students need for this listening activity.
            </p>
        </div>


        {{-- LESSON CONTEXT --}}
        <section
            class="flex flex-col gap-4
            rounded-2xl
            border border-slate-200
            bg-white p-5 shadow-sm
            dark:border-slate-700
            dark:bg-slate-800
            sm:flex-row
            sm:items-center
            sm:justify-between">

            <div class="flex items-center gap-4">

                <div
                    class="flex h-12 w-12
                    shrink-0 items-center
                    justify-center
                    rounded-xl
                    bg-orange-100
                    text-2xl
                    dark:bg-orange-500/10">

                    🎧
                </div>

                <div>

                    <p
                        class="text-xs font-bold
                        uppercase tracking-wide
                        text-slate-400">

                        Current Lesson
                    </p>

                    <h2
                        class="mt-1 text-lg font-black
                        text-slate-900
                        dark:text-white">

                        {{ $lesson->title }}
                    </h2>

                    @if ($lesson->description)
                        <p
                            class="mt-1 text-sm
                            text-slate-500
                            dark:text-slate-400">

                            {{ $lesson->description }}
                        </p>
                    @endif

                </div>

            </div>

            <span
                class="inline-flex w-fit
                rounded-full
                bg-orange-50
                px-4 py-2
                text-sm font-bold
                text-orange-700
                dark:bg-orange-500/10
                dark:text-orange-300">

                🎧 Listening Material
            </span>

        </section>


        {{-- FORM --}}
        <form
            action="{{ route(
                'admin.listening-materials.store',
                $lesson->id
            ) }}"
            method="POST"
            enctype="multipart/form-data"
            class="space-y-6">

            @csrf


            {{-- MATERIAL INFORMATION --}}
            <section
                class="rounded-2xl
                border border-slate-200
                bg-white p-6 shadow-sm
                dark:border-slate-700
                dark:bg-slate-800">

                <div
                    class="mb-6
                    border-b border-slate-100
                    pb-4
                    dark:border-slate-700">

                    <h2
                        class="text-lg font-black
                        text-slate-900
                        dark:text-white">

                        Material Information
                    </h2>

                    <p
                        class="mt-1 text-sm
                        text-slate-500
                        dark:text-slate-400">

                        Give the listening activity
                        a clear title and simple instructions.
                    </p>

                </div>

                <div class="space-y-6">

                    {{-- TITLE --}}
                    <div>

                        <label
                            for="title"
                            class="mb-2 block
                            text-sm font-bold
                            text-slate-800
                            dark:text-slate-200">

                            Title
                        </label>

                        <input
                            id="title"
                            type="text"
                            name="title"
                            value="{{ old('title') }}"
                            required
                            placeholder="Example: A Conversation at the Hotel"
                            class="w-full rounded-xl
                            border border-slate-300
                            bg-white
                            px-4 py-3
                            text-slate-900
                            placeholder:text-slate-400
                            focus:border-blue-500
                            focus:ring-blue-500
                            dark:border-slate-600
                            dark:bg-slate-900
                            dark:text-white
                            dark:placeholder:text-slate-500">

                        @error('title')
                            <p
                                class="mt-2
                                text-sm font-semibold
                                text-red-500">

                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- INSTRUCTION --}}
                    <div>

                        <label
                            for="instruction"
                            class="mb-2 block
                            text-sm font-bold
                            text-slate-800
                            dark:text-slate-200">

                            Instruction
                        </label>

                        <textarea
                            id="instruction"
                            name="instruction"
                            rows="4"
                            placeholder="Example: Listen to the audio carefully, then answer all questions."
                            class="w-full rounded-xl
                            border border-slate-300
                            bg-white p-4
                            text-slate-900
                            placeholder:text-slate-400
                            focus:border-blue-500
                            focus:ring-blue-500
                            dark:border-slate-600
                            dark:bg-slate-900
                            dark:text-white
                            dark:placeholder:text-slate-500">{{ old('instruction') }}</textarea>

                        <p
                            class="mt-2 text-xs
                            text-slate-400
                            dark:text-slate-500">

                            Keep the instruction short
                            and easy for students to follow.
                        </p>

                        @error('instruction')
                            <p
                                class="mt-2
                                text-sm font-semibold
                                text-red-500">

                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                </div>

            </section>


            {{-- LISTENING PASSAGE --}}
            <section
                class="rounded-2xl
                border border-slate-200
                bg-white p-6 shadow-sm
                dark:border-slate-700
                dark:bg-slate-800">

                <div
                    class="mb-5
                    border-b border-slate-100
                    pb-4
                    dark:border-slate-700">

                    <h2
                        class="text-lg font-black
                        text-slate-900
                        dark:text-white">

                        Listening Passage
                    </h2>

                    <p
                        class="mt-1 text-sm
                        text-slate-500
                        dark:text-slate-400">

                        Add the transcript or source text
                        used in the listening material.
                    </p>

                </div>

                <label
                    for="passage"
                    class="mb-2 block
                    text-sm font-bold
                    text-slate-800
                    dark:text-slate-200">

                    Passage / Transcript
                </label>

                <textarea
                    id="passage"
                    name="passage"
                    rows="12"
                    placeholder="Enter the complete listening transcript here..."
                    class="w-full rounded-xl
                    border border-slate-300
                    bg-white p-4
                    text-slate-900
                    placeholder:text-slate-400
                    focus:border-blue-500
                    focus:ring-blue-500
                    dark:border-slate-600
                    dark:bg-slate-900
                    dark:text-white
                    dark:placeholder:text-slate-500">{{ old('passage') }}</textarea>

                @error('passage')
                    <p
                        class="mt-2
                        text-sm font-semibold
                        text-red-500">

                        {{ $message }}
                    </p>
                @enderror

            </section>


            {{-- AUDIO FILE --}}
            <section
                class="rounded-2xl
                border border-slate-200
                bg-white p-6 shadow-sm
                dark:border-slate-700
                dark:bg-slate-800">

                <div
                    class="mb-5
                    border-b border-slate-100
                    pb-4
                    dark:border-slate-700">

                    <h2
                        class="text-lg font-black
                        text-slate-900
                        dark:text-white">

                        Audio File
                    </h2>

                    <p
                        class="mt-1 text-sm
                        text-slate-500
                        dark:text-slate-400">

                        Upload the audio
                        students will listen to.
                    </p>

                </div>


                {{-- UPLOAD BOX --}}
                <div
                    class="rounded-2xl
                    border border-dashed
                    border-slate-300
                    bg-slate-50
                    p-5
                    dark:border-slate-600
                    dark:bg-slate-900">

                    <div
                        class="flex flex-col gap-4
                        sm:flex-row
                        sm:items-start">

                        {{-- ICON --}}
                        <div
                            class="flex h-11 w-11
                            shrink-0 items-center
                            justify-center
                            rounded-xl
                            bg-orange-100
                            text-xl
                            dark:bg-orange-500/10">

                            🎧
                        </div>


                        {{-- FILE INPUT --}}
                        <div class="min-w-0 flex-1">

                            <label
                                for="audio_file"
                                class="mb-2 block
                                text-sm font-bold
                                text-slate-800
                                dark:text-slate-200">

                                Select Audio
                            </label>

                            <input
                                id="audio_file"
                                type="file"
                                name="audio_file"
                                accept=".mp3,.wav,.mpeg,audio/mpeg,audio/wav"
                                class="block w-full
                                cursor-pointer
                                rounded-xl
                                border border-slate-300
                                bg-white
                                text-sm
                                text-slate-600
                                transition
                                file:mr-4
                                file:border-0
                                file:bg-slate-100
                                file:px-4
                                file:py-3
                                file:font-bold
                                file:text-slate-700
                                hover:file:bg-slate-200
                                focus:outline-none
                                dark:border-slate-600
                                dark:bg-slate-950
                                dark:text-slate-300
                                dark:file:bg-slate-700
                                dark:file:text-white
                                dark:hover:file:bg-slate-600">

                            <p
                                class="mt-2
                                text-xs leading-5
                                text-slate-400
                                dark:text-slate-500">

                                Supported formats:
                                MP3 and WAV.
                            </p>

                            @error('audio_file')
                                <p
                                    class="mt-2
                                    text-sm font-semibold
                                    text-red-500">

                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                    </div>

                </div>

            </section>


            {{-- ACTIONS --}}
            <div
                class="flex flex-col-reverse
                gap-3
                sm:flex-row
                sm:items-center
                sm:justify-end">

                <a
                    href="{{ route(
                        'admin.listening-materials.index',
                        $lesson->id
                    ) }}"
                    class="inline-flex
                    items-center justify-center
                    rounded-xl
                    bg-slate-200
                    px-6 py-3
                    font-bold
                    text-slate-700
                    transition
                    hover:bg-slate-300
                    dark:bg-slate-700
                    dark:text-white
                    dark:hover:bg-slate-600">

                    Cancel
                </a>

                <button
                    type="submit"
                    class="inline-flex
                    items-center justify-center
                    rounded-xl
                    bg-blue-600
                    px-6 py-3
                    font-bold
                    text-white
                    shadow-sm
                    transition
                    hover:bg-blue-700">

                    Save Material
                </button>

            </div>

        </form>

    </div>
@endsection