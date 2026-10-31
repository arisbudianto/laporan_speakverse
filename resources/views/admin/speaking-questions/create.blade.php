@extends('layouts.admin')

@section('content')
    @php
        $isLessonMode = isset($lesson);

        $contextTitle = $isLessonMode ? $lesson->unit->title . ' - ' . ucfirst($lesson->skill_type) : $material->title;

        $formAction = $isLessonMode
            ? route('admin.speaking-lesson-questions.store', $lesson->id)
            : route('admin.speaking-questions.store', $material->id);

        $backRoute = $isLessonMode
            ? route('admin.speaking-lesson-questions.index', $lesson->id)
            : route('admin.speaking-questions.index', $material->id);
    @endphp

    <div class="max-w-5xl mx-auto space-y-6">

        <div>

            <h1 class="text-3xl font-black text-slate-900 dark:text-white">
                Add Speaking Question
            </h1>

            <p class="mt-1 text-slate-500 dark:text-slate-400">
                {{ $isLessonMode ? 'Create a new speaking question for pre-test/post-test' : 'Create a new question for this speaking material' }}
            </p>

        </div>

        <div
            class="bg-white dark:bg-slate-800
            border border-slate-200 dark:border-slate-700
            rounded-2xl shadow-sm p-6">

            <h2 class="font-bold text-lg text-slate-900 dark:text-white">
                {{ $contextTitle }}
            </h2>

            <p class="text-slate-500 dark:text-slate-400">
                {{ $isLessonMode ? 'Lesson ID' : 'Material ID' }} :
                {{ $isLessonMode ? $lesson->id : $material->id }}
            </p>

        </div>

        <form action="{{ $formAction }}" method="POST" enctype="multipart/form-data"
            class="bg-white dark:bg-slate-800
                border border-slate-200 dark:border-slate-700
                rounded-2xl shadow-sm p-6 space-y-6">

            @csrf

            <div>

                <label class="block mb-2 font-semibold text-slate-900 dark:text-white">
                    Question
                </label>

                <textarea name="question" rows="5" required
                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white">{{ old('question') }}</textarea>

                @error('question')
                    <p class="text-red-500 text-sm mt-1">
                        {{ $message }}
                    </p>
                @enderror

            </div>

            <div>

                <label class="block mb-2 font-semibold text-slate-900 dark:text-white">
                    Upload Image (Optional)
                </label>

                <input type="file" name="image" accept="image/*"
                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white p-3">

                @error('image')
                    <p class="text-red-500 text-sm mt-1">
                        {{ $message }}
                    </p>
                @enderror

            </div>

            <div class="flex gap-3">

                <button type="submit" class="px-6 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold">
                    Save Question
                </button>

                <a href="{{ $backRoute }}"
                    class="px-6 py-3 rounded-xl bg-slate-200 dark:bg-slate-700 text-slate-900 dark:text-white">
                    Back
                </a>

            </div>

        </form>

    </div>
@endsection
