@extends('layouts.admin')

@section('content')

<div class="max-w-5xl mx-auto space-y-6">

<div>

    <h1 class="text-3xl font-black text-slate-900 dark:text-white">
        Add Writing Material
    </h1>

    <p class="text-slate-500 dark:text-slate-400 mt-1">
        Create a new writing material for this lesson.
    </p>

</div>

<div class="bg-white dark:bg-slate-800 rounded-2xl shadow p-6">

    <div class="mb-6">

        <h2 class="font-bold text-xl text-slate-900 dark:text-white">
            {{ $lesson->title }}
        </h2>

        <p class="text-slate-500 dark:text-slate-400">
            Lesson ID : {{ $lesson->id }}
        </p>

    </div>

    <form
        action="{{ route('admin.writing-materials.store', $lesson->id) }}"
        method="POST"
        enctype="multipart/form-data"
        class="space-y-6">

        @csrf

        <!-- TITLE -->
        <div>

            <label class="block font-semibold mb-2">
                Title
            </label>

            <input
                type="text"
                name="title"
                value="{{ old('title') }}"
                class="w-full rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white">

            @error('title')
                <p class="text-red-500 text-sm mt-1">
                    {{ $message }}
                </p>
            @enderror

        </div>

        <!-- INSTRUCTION -->
        <div>

            <label class="block font-semibold mb-2">
                Instruction
            </label>

            <textarea
                name="instruction"
                rows="4"
                class="w-full rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white">{{ old('instruction') }}</textarea>

            @error('instruction')
                <p class="text-red-500 text-sm mt-1">
                    {{ $message }}
                </p>
            @enderror

        </div>

        <!-- PASSAGE -->
        <div>

            <label class="block font-semibold mb-2">
                Passage
            </label>

            <textarea
                name="passage"
                rows="12"
                class="w-full rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white">{{ old('passage') }}</textarea>

            @error('passage')
                <p class="text-red-500 text-sm mt-1">
                    {{ $message }}
                </p>
            @enderror

        </div>

        <!-- QUESTION INSTRUCTION -->
        <div>

            <label class="block font-semibold mb-2">
                Question Instruction
            </label>

            <textarea
                name="question_instruction"
                rows="4"
                class="w-full rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white">{{ old('question_instruction') }}</textarea>

            <p class="mt-2 text-sm text-slate-500">
                Example: Look at the mind map below and write an essay based on it.
            </p>

            @error('question_instruction')
                <p class="text-red-500 text-sm mt-1">
                    {{ $message }}
                </p>
            @enderror

        </div>

        <!-- IMAGE -->
        <div>

            <label class="block font-semibold mb-2">
                Writing Image
            </label>

            <input
                type="file"
                name="image"
                accept="image/*"
                class="w-full rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white p-3">

            <p class="mt-2 text-sm text-slate-500">
                Upload an image related to the writing passage.
            </p>

            @error('image')
                <p class="text-red-500 text-sm mt-1">
                    {{ $message }}
                </p>
            @enderror

        </div>

        <!-- BUTTON -->
        <div class="flex gap-3">

            <button
                type="submit"
                class="px-6 py-3 rounded-xl bg-blue-600 text-white font-semibold hover:bg-blue-700">

                Save Material

            </button>

            <a
                href="{{ route('admin.writing-materials.index', $lesson->id) }}"
                class="px-6 py-3 rounded-xl bg-slate-200 dark:bg-slate-700 text-slate-900 dark:text-white">

                Cancel

            </a>

        </div>

    </form>

</div>
```

</div>

@endsection
