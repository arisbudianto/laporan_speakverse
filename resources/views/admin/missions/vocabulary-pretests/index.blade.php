@extends('layouts.admin')

@section('content')
    @if (session('success'))
        <div
            class="mb-6 rounded-2xl
                   border border-emerald-200
                   bg-emerald-50
                   px-5 py-4
                   font-semibold text-emerald-700
                   shadow-sm
                   dark:border-emerald-500/20
                   dark:bg-emerald-500/10
                   dark:text-emerald-300">

            {{ session('success') }}
        </div>
    @endif

    <div
        class="mb-8 flex flex-col gap-5
               sm:flex-row sm:items-center sm:justify-between">

        <div class="min-w-0">
            <h1
                class="text-3xl font-black leading-tight
                       text-slate-900 dark:text-white
                       lg:text-4xl">
                Vocabulary Pretest Questions
            </h1>

            <p class="mt-2 text-slate-500 dark:text-slate-400">
                {{ $pretests->count() }}
                {{ \Illuminate\Support\Str::plural('question', $pretests->count()) }} total ·
                only category "vocabulary" appears in the student pretest
            </p>
        </div>

        <a
            href="{{ route('admin.vocabulary-pretests.create') }}"
            class="inline-flex w-full items-center justify-center
                   rounded-2xl
                   bg-gradient-to-r from-cyan-500 to-blue-600
                   px-6 py-4
                   text-center font-bold text-white
                   shadow-lg shadow-cyan-500/20
                   transition-all duration-200
                   hover:scale-[1.02]
                   hover:shadow-xl
                   focus:outline-none focus:ring-4 focus:ring-cyan-500/20
                   sm:w-auto">
            <span class="mr-1 text-lg font-black">+</span>
            Add Question
        </a>
    </div>

    {{-- MOBILE CARDS --}}
    <div class="grid grid-cols-1 gap-5 lg:hidden">
        @forelse ($pretests as $pretest)
            <article
                class="rounded-3xl
                       border border-slate-200
                       bg-white
                       p-5
                       shadow-sm
                       dark:border-white/10
                       dark:bg-white/[0.03]">

                <div class="flex items-start justify-between gap-4">
                    <p class="min-w-0 flex-1 font-bold text-slate-900 dark:text-white">
                        {{ \Illuminate\Support\Str::limit($pretest->question, 90) }}
                    </p>

                    <span
                        class="shrink-0 rounded-xl
                               bg-cyan-500/10
                               px-3 py-2
                               text-xs font-bold text-cyan-600
                               dark:text-cyan-400">
                        {{ $pretest->category }}
                    </span>
                </div>

                <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">
                    Correct answer:
                    <span class="font-bold text-emerald-600 dark:text-emerald-400">
                        {{ $pretest->correct_answer }}
                    </span>
                </p>

                <p class="mt-1 text-xs">
                    @if (! empty($pretest->error_if_wrong))
                        @if ($pretest->error_labels_source === 'ai_suggested')
                            <span class="font-bold text-amber-600 dark:text-amber-400">🤖 AI Suggested — Review</span>
                        @else
                            <span class="font-bold text-purple-600 dark:text-purple-400">✓ Error-labeled</span>
                        @endif
                    @else
                        <span class="text-slate-400">Not labeled (Modul 2)</span>
                    @endif
                </p>

                <div class="mt-5 grid grid-cols-2 gap-3">
                    <a
                        href="{{ route('admin.vocabulary-pretests.edit', $pretest) }}"
                        class="rounded-2xl
                               bg-slate-100
                               py-3
                               text-center font-semibold
                               text-slate-900
                               transition-all duration-200
                               hover:bg-slate-200
                               dark:bg-white/5
                               dark:text-white
                               dark:hover:bg-white/10">
                        Edit
                    </a>

                    <form
                        action="{{ route('admin.vocabulary-pretests.destroy', $pretest) }}"
                        method="POST"
                        onsubmit="return confirm('Delete this question?')">
                        @csrf
                        @method('DELETE')

                        <button
                            type="submit"
                            class="w-full rounded-2xl
                                   bg-red-500/10
                                   py-3
                                   font-semibold text-red-600
                                   transition-all duration-200
                                   hover:bg-red-500/20
                                   dark:text-red-400">
                            Delete
                        </button>
                    </form>
                </div>
            </article>
        @empty
            <div
                class="rounded-3xl
                       border border-slate-200
                       bg-white
                       px-6 py-12
                       text-center
                       shadow-sm
                       dark:border-white/10
                       dark:bg-white/[0.03]">

                <p class="font-bold text-slate-900 dark:text-white">
                    No questions yet
                </p>

                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                    Add your first Vocabulary Pretest question.
                </p>
            </div>
        @endforelse
    </div>

    {{-- DESKTOP TABLE --}}
    <div
        class="hidden overflow-hidden
               rounded-[32px]
               border border-slate-200
               bg-white
               shadow-sm
               dark:border-white/10
               dark:bg-white/[0.03]
               lg:block">

        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px]">
                <thead
                    class="border-b border-slate-200
                           bg-slate-50
                           dark:border-white/10
                           dark:bg-white/[0.03]">

                    <tr>
                        <th class="px-8 py-6 text-left text-sm font-black text-slate-500 dark:text-slate-400">
                            Question
                        </th>
                        <th class="px-8 py-6 text-left text-sm font-black text-slate-500 dark:text-slate-400">
                            Category
                        </th>
                        <th class="px-8 py-6 text-left text-sm font-black text-slate-500 dark:text-slate-400">
                            Correct
                        </th>
                        <th class="px-8 py-6 text-left text-sm font-black text-slate-500 dark:text-slate-400">
                            Error Label
                        </th>
                        <th class="px-8 py-6 text-right text-sm font-black text-slate-500 dark:text-slate-400">
                            Actions
                        </th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($pretests as $pretest)
                        <tr
                            class="border-b border-slate-100
                                   transition-all duration-200
                                   hover:bg-slate-50
                                   dark:border-white/5
                                   dark:hover:bg-white/[0.02]">

                            <td class="px-8 py-6 font-medium text-slate-900 dark:text-white">
                                {{ \Illuminate\Support\Str::limit($pretest->question, 80) }}
                            </td>

                            <td class="px-8 py-6">
                                <span
                                    class="inline-flex rounded-2xl
                                           bg-cyan-500/10
                                           px-5 py-3
                                           text-sm font-bold text-cyan-600
                                           dark:text-cyan-400">
                                    {{ $pretest->category }}
                                </span>
                            </td>

                            <td class="px-8 py-6 font-bold text-emerald-600 dark:text-emerald-400">
                                {{ $pretest->correct_answer }}
                            </td>

                            <td class="px-8 py-6 text-sm">
                                @if (! empty($pretest->error_if_wrong))
                                    @if ($pretest->error_labels_source === 'ai_suggested')
                                        <span class="font-bold text-amber-600 dark:text-amber-400">🤖 AI Suggested — Review</span>
                                    @else
                                        <span class="font-bold text-purple-600 dark:text-purple-400">✓ Labeled</span>
                                    @endif
                                @else
                                    <span class="text-slate-400">Not labeled</span>
                                @endif
                            </td>

                            <td class="px-8 py-6">
                                <div class="flex items-center justify-end gap-3">
                                    <a
                                        href="{{ route('admin.vocabulary-pretests.edit', $pretest) }}"
                                        class="rounded-2xl
                                               bg-slate-100
                                               px-5 py-3
                                               font-semibold
                                               text-slate-900
                                               transition-all duration-200
                                               hover:bg-slate-200
                                               dark:bg-white/5
                                               dark:text-white
                                               dark:hover:bg-white/10">
                                        Edit
                                    </a>

                                    <form
                                        action="{{ route('admin.vocabulary-pretests.destroy', $pretest) }}"
                                        method="POST"
                                        onsubmit="return confirm('Delete this question?')">
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="rounded-2xl
                                                   bg-red-500/10
                                                   px-5 py-3
                                                   font-semibold text-red-600
                                                   transition-all duration-200
                                                   hover:bg-red-500/20
                                                   dark:text-red-400">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-8 py-14 text-center">
                                <p class="font-bold text-slate-900 dark:text-white">
                                    No questions yet
                                </p>

                                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                                    Add your first Vocabulary Pretest question.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
