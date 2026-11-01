@extends('layouts.admin')

@section('content')

<div class="max-w-5xl mx-auto space-y-6">

<div>

    <h1 class="text-3xl font-black text-slate-900 dark:text-white">
        Edit Writing Material
    </h1>

    <p class="mt-1 text-slate-500 dark:text-slate-400">
        Update writing material information
    </p>

</div>

<form
    action="{{ route('admin.writing-materials.update', $material->id) }}"
    method="POST"
    enctype="multipart/form-data"
    class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm p-6 space-y-6">

    @csrf
    @method('PUT')

    <!-- TITLE -->
    <div>

        <label class="block mb-2 font-semibold text-slate-900 dark:text-white">
            Title
        </label>

        <input
            type="text"
            name="title"
            value="{{ old('title', $material->title) }}"
            class="w-full rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white">

        @error('title')
            <p class="text-red-500 text-sm mt-1">
                {{ $message }}
            </p>
        @enderror

    </div>

    <!-- INSTRUCTION -->
    <div>

        <label class="block mb-2 font-semibold text-slate-900 dark:text-white">
            Instruction
        </label>

        <textarea
            name="instruction"
            rows="4"
            class="w-full rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white">{{ old('instruction', $material->instruction) }}</textarea>

        @error('instruction')
            <p class="text-red-500 text-sm mt-1">
                {{ $message }}
            </p>
        @enderror

    </div>

    <!-- PASSAGE -->
    <div>

        <label class="block mb-2 font-semibold text-slate-900 dark:text-white">
            Passage
        </label>

        <textarea
            name="passage"
            rows="12"
            class="w-full rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white">{{ old('passage', $material->passage) }}</textarea>

        @error('passage')
            <p class="text-red-500 text-sm mt-1">
                {{ $message }}
            </p>
        @enderror

    </div>

    <!-- CURRENT IMAGE -->
    <div>

        <label class="block mb-2 font-semibold text-slate-900 dark:text-white">
            Current Image
        </label>

        @if($material->image)

            <img
                src="{{ asset('storage/' . $material->image) }}"
                alt="Writing Image"
                class="w-64 rounded-xl border border-slate-200 dark:border-slate-700">

        @else

            <div class="text-slate-500">
                No image uploaded.
            </div>

        @endif

    </div>

    <!-- NEW IMAGE -->
    <div>

        <label class="block mb-2 font-semibold text-slate-900 dark:text-white">
            Replace Image
        </label>

        <input
            type="file"
            name="image"
            accept="image/*"
            class="w-full rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white p-3">

        <p class="mt-2 text-sm text-slate-500">
            Leave empty if you don't want to change the image.
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
            class="px-6 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold">

            Save Changes

        </button>

        <a
            href="{{ route('admin.writing-materials.index', $material->lesson_id) }}"
            class="px-6 py-3 rounded-xl bg-slate-200 dark:bg-slate-700 text-slate-900 dark:text-white">

            Cancel

        </a>

    </div>

</form>

</div>

@endsection
