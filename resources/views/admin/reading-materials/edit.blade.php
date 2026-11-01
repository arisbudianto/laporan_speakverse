@extends('layouts.admin')

@section('content')
    <div class="max-w-5xl mx-auto space-y-6">

        <div>

            <h1 class="text-3xl font-black text-slate-900 dark:text-white">
                Edit Reading Material
            </h1>

            <p class="mt-1 text-slate-500 dark:text-slate-400">
                Update reading material information
            </p>

        </div>

        <form action="{{ route('admin.reading-materials.update', $material->id) }}" method="POST"
            class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm p-6 space-y-6">

            @csrf
            @method('PUT')

            <div>

                <label class="block mb-2 font-semibold text-slate-900 dark:text-white">
                    Title
                </label>

                <input type="text" name="title" value="{{ old('title', $material->title) }}"
                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white">

            </div>

            <div>

                <label class="block mb-2 font-semibold text-slate-900 dark:text-white">
                    Instruction
                </label>

                <textarea name="instruction" rows="3"
                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white">{{ old('instruction', $material->instruction) }}</textarea>

            </div>

            <div>

                <label class="block mb-2 font-semibold text-slate-900 dark:text-white">
                    Passage
                </label>

                <textarea name="passage" rows="12"
                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white">{{ old('passage', $material->passage) }}</textarea>

            </div>

            <div class="flex gap-3">

                <button type="submit" class="px-6 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold">

                    Save Changes

                </button>

                <a href="{{ route('admin.reading-materials.index', $material->lesson_id) }}"
                    class="px-6 py-3 rounded-xl bg-slate-200 dark:bg-slate-700 text-slate-900 dark:text-white">

                    Cancel

                </a>

            </div>

        </form>

    </div>
@endsection
