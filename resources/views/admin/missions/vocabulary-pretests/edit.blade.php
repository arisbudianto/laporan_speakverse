@extends('layouts.admin')

@section('content')
    <div class="max-w-3xl mx-auto">

        <div class="mb-8">

            <a href="{{ route('admin.vocabulary-pretests.index') }}"
                class="inline-flex items-center gap-2
                text-sm font-semibold
                text-slate-500 dark:text-slate-400
                hover:text-cyan-500 transition">

                ← Back to Vocabulary Pretest Questions

            </a>

            <h1 class="mt-4 text-3xl lg:text-4xl font-black">
                Edit Vocabulary Question
            </h1>

            <p class="mt-2 text-slate-500 dark:text-slate-400">
                Question #{{ $pretest->id }}
            </p>

        </div>

        <form action="{{ route('admin.vocabulary-pretests.update', $pretest) }}" method="POST"
            class="space-y-6 rounded-[32px]
            bg-white dark:bg-white/[0.03]
            border border-slate-200 dark:border-white/10
            p-6 lg:p-8 shadow-sm">

            @csrf
            @method('PUT')

            @include('admin.missions.vocabulary-pretests.partials.form')

            <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pt-2">

                <a href="{{ route('admin.vocabulary-pretests.index') }}"
                    class="px-6 py-4 rounded-2xl
                    bg-slate-100 dark:bg-white/5
                    font-bold text-center
                    hover:bg-slate-200 dark:hover:bg-white/10
                    transition">

                    Cancel

                </a>

                <button type="submit"
                    class="px-6 py-4 rounded-2xl
                    bg-gradient-to-r from-cyan-500 to-blue-600
                    text-white font-bold
                    shadow-lg shadow-cyan-500/20
                    hover:scale-[1.02]
                    transition">

                    Update Question

                </button>

            </div>

        </form>

    </div>
@endsection
