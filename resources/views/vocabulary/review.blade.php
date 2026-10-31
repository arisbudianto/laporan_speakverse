<x-app-layout>

    <div class="space-y-8 max-w-3xl mx-auto">

        {{-- HEADER --}}
        <section
            class="relative overflow-hidden rounded-[32px]
            border border-slate-200 dark:border-white/10
            bg-white/70 dark:bg-white/5
            backdrop-blur-2xl
            p-6 md:p-8">

            <div
                class="absolute top-[-100px] right-[-100px]
                w-[250px] h-[250px]
                bg-cyan-400/10 rounded-full blur-3xl">
            </div>

            <div class="relative z-10">
                <div
                    class="inline-flex items-center gap-2
                    px-4 py-2 rounded-full
                    bg-cyan-500/10 border border-cyan-400/20
                    text-cyan-400 text-sm font-medium mb-4">
                    📖 Vocabulary Pretest — Pembahasan
                </div>

                <h1 class="text-3xl md:text-4xl font-black leading-tight">
                    Hasil Pretest
                </h1>

                <p class="mt-3 text-slate-600 dark:text-slate-400">
                    Skor akhir:
                    <span class="font-black text-cyan-500">{{ (int) $result->score }}</span>
                    · Dikerjakan {{ $result->created_at?->format('d M Y, H:i') }}
                </p>
            </div>
        </section>

        @if (! $hasSessionData)
            {{-- Hasil dari SEBELUM perbaikan ini dibuat — tidak ada data untuk direkonstruksi --}}
            <section
                class="rounded-[32px] border border-slate-200 dark:border-white/10
                bg-white/70 dark:bg-white/5 backdrop-blur-xl
                p-10 text-center">

                <h2 class="text-xl font-black">Pembahasan Tidak Tersedia</h2>

                <p class="mt-2 text-slate-500 dark:text-slate-400">
                    Hasil ini dikerjakan sebelum fitur pembahasan dibuat, jadi
                    rincian jawabannya tidak bisa ditampilkan. Ini hanya
                    berlaku untuk hasil lama — pretest yang dikerjakan
                    setelah ini akan punya pembahasan lengkap.
                </p>

                <a href="{{ route('missions') }}"
                    class="mt-6 inline-flex items-center justify-center
                    px-6 py-3 rounded-2xl
                    bg-gradient-to-r from-cyan-500 to-blue-600
                    text-white font-bold">
                    Kembali ke Missions
                </a>
            </section>
        @else
            {{-- QUESTIONS --}}
            <div class="space-y-5">
                @foreach ($items as $index => $item)
                    @php
                        $question = $item['question'];
                        $selected = $item['selected'];
                        $isCorrect = $item['is_correct'];
                    @endphp

                    <section
                        class="rounded-[28px] border
                        {{ $isCorrect ? 'border-emerald-200 dark:border-emerald-500/20' : 'border-red-200 dark:border-red-500/20' }}
                        bg-white/70 dark:bg-white/5 backdrop-blur-xl
                        p-6 md:p-7">

                        <div class="flex items-start gap-3">
                            <span
                                class="flex h-9 w-9 shrink-0 items-center justify-center
                                rounded-xl font-black text-sm
                                {{ $isCorrect ? 'bg-emerald-500/10 text-emerald-500' : 'bg-red-500/10 text-red-500' }}">
                                {{ $isCorrect ? '✓' : '✗' }}
                            </span>

                            <div class="min-w-0">
                                <p class="text-xs font-bold text-slate-400 mb-1">Soal {{ $index + 1 }}</p>
                                <h2 class="text-lg font-bold leading-snug">
                                    {{ $question->question }}
                                </h2>
                            </div>
                        </div>

                        <div class="mt-4 grid gap-2 pl-12">
                            @foreach (['A', 'B', 'C', 'D', 'E'] as $option)
                                @php
                                    $field = 'option_' . strtolower($option);
                                    $optionText = $question->$field;
                                @endphp

                                @if ($optionText)
                                    @php
                                        $isTheCorrectOption = $option === $question->correct_answer;
                                        $isTheSelectedOption = $option === $selected;
                                    @endphp

                                    <div
                                        class="flex items-center gap-3 rounded-2xl border px-4 py-3 text-sm
                                        {{ $isTheCorrectOption
                                            ? 'border-emerald-400 bg-emerald-500/10 font-bold text-emerald-700 dark:text-emerald-400'
                                            : ($isTheSelectedOption
                                                ? 'border-red-400 bg-red-500/10 font-bold text-red-700 dark:text-red-400'
                                                : 'border-slate-200 dark:border-white/10 text-slate-600 dark:text-slate-400') }}">

                                        <span>{{ $optionText }}</span>

                                        @if ($isTheCorrectOption)
                                            <span class="ml-auto text-xs font-black">✓ Jawaban benar</span>
                                        @elseif ($isTheSelectedOption)
                                            <span class="ml-auto text-xs font-black">Pilihanmu</span>
                                        @endif
                                    </div>
                                @endif
                            @endforeach
                        </div>

                        @if ($question->rationale)
                            <div class="mt-4 ml-12 rounded-2xl bg-cyan-50 dark:bg-cyan-500/10 px-4 py-3">
                                <p class="text-xs font-bold text-cyan-700 dark:text-cyan-400 mb-1">Kenapa jawabannya begitu?</p>
                                <p class="text-sm text-slate-600 dark:text-slate-300">{{ $question->rationale }}</p>
                            </div>
                        @endif

                        @if (! $isCorrect && $item['error_label'])
                            <p class="mt-3 ml-12 text-xs font-semibold text-amber-600 dark:text-amber-400">
                                💡 {{ $item['error_label'] }}
                            </p>
                        @endif

                        @if (! $selected)
                            <p class="mt-3 ml-12 text-xs text-slate-400 italic">
                                Kamu tidak menjawab soal ini.
                            </p>
                        @endif
                    </section>
                @endforeach
            </div>

            <div class="flex justify-center pb-8">
                <a href="{{ route('missions') }}"
                    class="inline-flex items-center justify-center
                    px-8 py-4 rounded-2xl
                    bg-gradient-to-r from-cyan-500 to-blue-600
                    text-white font-bold shadow-lg shadow-cyan-500/20
                    hover:scale-[1.02] transition-all duration-200">
                    Kembali ke Missions
                </a>
            </div>
        @endif

    </div>

</x-app-layout>
