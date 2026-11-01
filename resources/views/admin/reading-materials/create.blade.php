@extends('layouts.admin')

@section('content')
    @php
        $lessonTitle = $lesson->title ?? 'Reading';

        $unitTitle = optional($lesson->unit)->title;

        $contextTitle = $unitTitle
            ? $unitTitle . ' — ' . $lessonTitle
            : $lessonTitle;

        $storeRoute = route(
            'admin.reading-materials.store',
            $lesson->id
        );

        $backRoute = route(
            'admin.reading-materials.index',
            $lesson->id
        );
    @endphp

    <div class="mx-auto max-w-5xl space-y-6">

        {{-- HEADER --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            <div>
                <h1 class="text-3xl font-black text-slate-900 dark:text-white">
                    Add Reading Material
                </h1>

                <p class="mt-1 text-slate-500 dark:text-slate-400">
                    Create a new reading passage for this lesson.
                </p>
            </div>

            <a
                href="{{ $backRoute }}"
                class="inline-flex self-start items-center justify-center
                rounded-xl bg-slate-200 px-5 py-3
                text-sm font-bold text-slate-800
                transition hover:bg-slate-300
                sm:self-auto
                dark:bg-slate-700
                dark:text-white
                dark:hover:bg-slate-600">

                Back
            </a>
        </div>

        {{-- VALIDATION ERROR SUMMARY --}}
        @if ($errors->any())
            <div
                class="rounded-2xl
                border border-red-200
                bg-red-50 px-5 py-4
                text-red-700
                dark:border-red-500/20
                dark:bg-red-500/10
                dark:text-red-300">

                <h2 class="font-bold">
                    Please check the form again.
                </h2>

                <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>
                            {{ $error }}
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- LESSON INFORMATION --}}
        <section
            class="overflow-hidden rounded-2xl
            border border-slate-200
            bg-white shadow-sm
            dark:border-white/10
            dark:bg-slate-800">

            <div class="px-6 py-5">

                <p
                    class="text-xs font-black uppercase tracking-wider
                    text-blue-600 dark:text-blue-400">

                    Reading Lesson
                </p>

                <h2
                    class="mt-1 text-xl font-black
                    text-slate-900 dark:text-white">

                    {{ $contextTitle }}
                </h2>
            </div>
        </section>

        {{-- FORM --}}
        <form
            action="{{ $storeRoute }}"
            method="POST"
            class="space-y-6 rounded-2xl
            border border-slate-200
            bg-white p-6 shadow-sm
            dark:border-white/10
            dark:bg-slate-800">

            @csrf

            {{-- TITLE --}}
            <div>
                <label
                    for="title"
                    class="mb-2 block font-bold
                    text-slate-900 dark:text-white">

                    Title
                    <span class="text-red-500">*</span>
                </label>

                <input
                    id="title"
                    type="text"
                    name="title"
                    value="{{ old('title') }}"
                    required
                    placeholder="Example: The Story of the Clever Deer"
                    class="w-full rounded-xl
                    border border-slate-300
                    bg-white px-4 py-3
                    text-slate-900
                    outline-none transition
                    placeholder:text-slate-400
                    focus:border-blue-500
                    focus:ring-2 focus:ring-blue-500/20
                    dark:border-slate-600
                    dark:bg-slate-900
                    dark:text-white
                    @error('title') border-red-500 @enderror">

                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                    Enter a short and clear title for the reading passage.
                </p>

                @error('title')
                    <p class="mt-2 text-sm font-semibold text-red-500">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- INSTRUCTION --}}
            <div>
                <label
                    for="instruction"
                    class="mb-2 block font-bold
                    text-slate-900 dark:text-white">

                    Instruction

                    <span class="font-normal text-slate-400">
                        (Optional)
                    </span>
                </label>

                <textarea
                    id="instruction"
                    name="instruction"
                    rows="4"
                    placeholder="Example: Read the passage carefully, then answer the questions."
                    class="w-full rounded-xl
                    border border-slate-300
                    bg-white px-4 py-3
                    text-slate-900
                    outline-none transition
                    placeholder:text-slate-400
                    focus:border-blue-500
                    focus:ring-2 focus:ring-blue-500/20
                    dark:border-slate-600
                    dark:bg-slate-900
                    dark:text-white
                    @error('instruction') border-red-500 @enderror">{{ old('instruction') }}</textarea>

                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                    Explain what students should do before answering the questions.
                </p>

                @error('instruction')
                    <p class="mt-2 text-sm font-semibold text-red-500">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- PASSAGE --}}
            <div>
                <label
                    for="passage"
                    class="mb-2 block font-bold
                    text-slate-900 dark:text-white">

                    Passage
                    <span class="text-red-500">*</span>
                </label>

                <textarea
                    id="passage"
                    name="passage"
                    rows="14"
                    required
                    placeholder="Enter the complete reading passage..."
                    class="w-full rounded-xl
                    border border-slate-300
                    bg-white px-4 py-3
                    text-slate-900
                    outline-none transition
                    placeholder:text-slate-400
                    focus:border-blue-500
                    focus:ring-2 focus:ring-blue-500/20
                    dark:border-slate-600
                    dark:bg-slate-900
                    dark:text-white
                    @error('passage') border-red-500 @enderror">{{ old('passage') }}</textarea>

                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                    Add the full text that students will read.
                </p>

                @error('passage')
                    <p class="mt-2 text-sm font-semibold text-red-500">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- ACTIONS --}}
            <div
                class="flex flex-col-reverse gap-3
                border-t border-slate-200 pt-6
                sm:flex-row sm:justify-end
                dark:border-white/10">

                <a
                    href="{{ $backRoute }}"
                    class="inline-flex items-center justify-center
                    rounded-xl bg-slate-200
                    px-6 py-3
                    font-bold text-slate-800
                    transition hover:bg-slate-300
                    dark:bg-slate-700
                    dark:text-white
                    dark:hover:bg-slate-600">

                    Cancel
                </a>

                <button
                    type="submit"
                    class="inline-flex items-center justify-center
                    rounded-xl bg-blue-600
                    px-6 py-3
                    font-bold text-white
                    shadow-sm transition
                    hover:bg-blue-700
                    focus:outline-none
                    focus:ring-2 focus:ring-blue-500
                    focus:ring-offset-2
                    dark:focus:ring-offset-slate-800">

                    Save Material
                </button>
            </div>
        </form>
    </div>
@endsection