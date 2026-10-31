{{--
    Dipakai bersama oleh create.blade.php dan edit.blade.php.
    Saat edit, variabel $pretest tersedia (dari controller); saat
    create, $pretest tidak ada, jadi semua default fallback ke
    string kosong / 'vocabulary'.
--}}

<div>
    <label for="category" class="block mb-2 font-bold">
        Category
    </label>

    <input id="category" type="text" name="category"
        value="{{ old('category', $pretest->category ?? 'vocabulary') }}" required
        class="w-full rounded-2xl
        border-slate-200 dark:border-white/10
        bg-slate-50 dark:bg-white/5
        px-5 py-4
        focus:border-cyan-500 focus:ring-cyan-500">

    <p class="mt-2 text-xs text-slate-400">
        Only questions with category exactly "vocabulary" currently
        appear in the student Vocabulary Pretest.
    </p>

    @error('category')
        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="question" class="block mb-2 font-bold">
        Question
    </label>

    <textarea id="question" name="question" rows="3" required
        class="w-full rounded-2xl
        border-slate-200 dark:border-white/10
        bg-slate-50 dark:bg-white/5
        px-5 py-4
        focus:border-cyan-500 focus:ring-cyan-500">{{ old('question', $pretest->question ?? '') }}</textarea>

    @error('question')
        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
    @enderror
</div>

@foreach (['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D', 'e' => 'E'] as $key => $label)
    <div>
        <label for="option_{{ $key }}" class="block mb-2 font-bold">
            Option {{ $label }}
        </label>

        <input id="option_{{ $key }}" type="text" name="option_{{ $key }}"
            value="{{ old('option_' . $key, $pretest->{'option_' . $key} ?? '') }}" required
            class="w-full rounded-2xl
            border-slate-200 dark:border-white/10
            bg-slate-50 dark:bg-white/5
            px-5 py-4
            focus:border-cyan-500 focus:ring-cyan-500">

        @error('option_' . $key)
            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
        @enderror
    </div>
@endforeach

<div>
    <label for="correct_answer" class="block mb-2 font-bold">
        Correct Answer
    </label>

    <select id="correct_answer" name="correct_answer" required
        class="w-full rounded-2xl
        border-slate-200 dark:border-white/10
        bg-slate-50 dark:bg-[#081120]
        px-5 py-4
        focus:border-cyan-500 focus:ring-cyan-500">

        <option value="">Select the correct option</option>

        @foreach (['A', 'B', 'C', 'D', 'E'] as $option)
            <option value="{{ $option }}"
                @selected(old('correct_answer', $pretest->correct_answer ?? '') === $option)>
                {{ $option }}
            </option>
        @endforeach
    </select>

    @error('correct_answer')
        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
    @enderror
</div>

{{--
    ============================================================
    Modul 2 — Diagnostic Engine
    ============================================================
    Field-field di bawah ini opsional. Kalau diisi, jawaban salah
    siswa untuk pertanyaan ini akan otomatis diberi label error
    (bukan cuma "salah") di dashboard guru dan bahan hint nanti.
--}}
<div class="pt-4 mt-2 border-t border-slate-200 dark:border-white/10">
    <p class="font-black text-purple-600 dark:text-purple-400">
        Error Classification (optional)
    </p>
    <p class="mt-1 text-xs text-slate-400">
        For each WRONG option, pick why a student who picks it is
        wrong. Leave "— not labeled —" if you don't know yet; those
        answers just won't get an error label.
    </p>
</div>

<div>
    <label for="skill_target" class="block mb-2 font-bold">
        Skill Target
    </label>

    <input id="skill_target" type="text" name="skill_target"
        value="{{ old('skill_target', $pretest->skill_target ?? '') }}"
        placeholder="e.g. word_meaning, collocation, word_form"
        class="w-full rounded-2xl
        border-slate-200 dark:border-white/10
        bg-slate-50 dark:bg-white/5
        px-5 py-4
        focus:border-cyan-500 focus:ring-cyan-500">

    @error('skill_target')
        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
    @enderror
</div>

@php
    $errorCodeLabels = [
        'lexical' => 'Lexical (wrong word meaning/collocation/form)',
        'inferential' => 'Inferential (failed to infer from context)',
        'syntactic' => 'Syntactic (wrong grammatical structure)',
        'context_misconception' => 'Context Misconception (outside knowledge conflicts with context)',
    ];
    $existingMap = old('error_if_wrong', $pretest->error_if_wrong ?? []);
@endphp

@foreach (['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D', 'e' => 'E'] as $key => $label)
    <div>
        <label for="error_code_{{ $key }}" class="block mb-2 font-bold">
            If student picks "{{ $label }}", it's an error of type…
        </label>

        <select id="error_code_{{ $key }}" name="error_code_{{ $key }}"
            class="w-full rounded-2xl
            border-slate-200 dark:border-white/10
            bg-slate-50 dark:bg-[#081120]
            px-5 py-4
            focus:border-cyan-500 focus:ring-cyan-500">

            <option value="">— not labeled —</option>

            @foreach ($errorCodeLabels as $code => $codeLabel)
                <option value="{{ $code }}"
                    @selected(($existingMap[$label] ?? null) === $code)>
                    {{ $codeLabel }}
                </option>
            @endforeach
        </select>
    </div>
@endforeach

<div>
    <label for="rationale" class="block mb-2 font-bold">
        Rationale (why the correct answer is correct)
    </label>

    <textarea id="rationale" name="rationale" rows="2"
        class="w-full rounded-2xl
        border-slate-200 dark:border-white/10
        bg-slate-50 dark:bg-white/5
        px-5 py-4
        focus:border-cyan-500 focus:ring-cyan-500">{{ old('rationale', $pretest->rationale ?? '') }}</textarea>
</div>

<div>
    <label for="text_span" class="block mb-2 font-bold">
        Example Sentence / Context (optional)
    </label>

    <textarea id="text_span" name="text_span" rows="2"
        class="w-full rounded-2xl
        border-slate-200 dark:border-white/10
        bg-slate-50 dark:bg-white/5
        px-5 py-4
        focus:border-cyan-500 focus:ring-cyan-500">{{ old('text_span', $pretest->text_span ?? '') }}</textarea>
</div>
