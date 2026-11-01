{{-- resources/views/admin/writing-materials/create-with-questions.blade.php --}}

@extends('layouts.admin')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <div>
        <h1 class="text-3xl font-black text-slate-900 dark:text-white">
            Create Writing Material with Questions
        </h1>

        <p class="text-slate-500 dark:text-slate-400 mt-1">
            Create a new writing material with multiple questions at once.
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
            action="{{ route('admin.writing-materials.store-with-questions', $lesson->id) }}"
            method="POST"
            enctype="multipart/form-data"
            class="space-y-6">

            @csrf

            <!-- WRITING MATERIAL SECTION -->
            <div class="border-b border-slate-200 dark:border-slate-700 pb-6">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">
                    Writing Material
                </h3>

                <!-- TITLE -->
                <div class="mb-4">
                    <label class="block font-semibold mb-2">
                        Title <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="title"
                        value="{{ old('title') }}"
                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white">
                    @error('title')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- INSTRUCTION -->
                <div class="mb-4">
                    <label class="block font-semibold mb-2">
                        Reading Instruction
                    </label>
                    <textarea
                        name="instruction"
                        rows="3"
                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white">{{ old('instruction') }}</textarea>
                    @error('instruction')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- PASSAGE -->
                <div class="mb-4">
                    <label class="block font-semibold mb-2">
                        Passage
                    </label>
                    <textarea
                        name="passage"
                        rows="8"
                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white">{{ old('passage') }}</textarea>
                    @error('passage')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- MATERIAL IMAGE -->
                <div>
                    <label class="block font-semibold mb-2">
                        Material Image (Optional)
                    </label>
                    <input
                        type="file"
                        name="image"
                        accept="image/*"
                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white p-3">
                    @error('image')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- QUESTIONS SECTION -->
            <div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">
                    Questions
                </h3>

                <!-- QUESTION INSTRUCTION -->
                <div class="mb-4">
                    <label class="block font-semibold mb-2">
                        Question Instruction (for all questions)
                        <span class="text-slate-400 font-normal text-sm">(Optional)</span>
                    </label>
                    <textarea
                        name="question_instruction"
                        rows="3"
                        placeholder="Contoh: Perhatikan mind map di bawah ini, lalu jawab pertanyaan berikut..."
                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white">{{ old('question_instruction') }}</textarea>
                    @error('question_instruction')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- DYNAMIC QUESTIONS -->
                <div id="questions-container">
                    <div class="question-item bg-slate-50 dark:bg-slate-900/50 rounded-xl p-4 mb-4 border border-slate-200 dark:border-slate-700">
                        <div class="flex justify-between items-center mb-3">
                            <h4 class="font-semibold text-slate-900 dark:text-white">
                                Question 1
                            </h4>
                            <button type="button" onclick="removeQuestion(this)"
                                    class="text-red-500 hover:text-red-700 text-sm font-semibold hidden">
                                <i class="fas fa-trash"></i> Remove
                            </button>
                        </div>

                        <div class="mb-3">
                            <label class="block text-sm font-medium mb-1">
                                Question Text <span class="text-red-500">*</span>
                            </label>
                            <textarea name="questions[]" rows="3"
                                      class="w-full rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white"
                                      required>{{ old('questions.0') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="block text-sm font-medium mb-1">
                                Mind Map / Image (Optional)
                            </label>
                            <input type="file" name="question_images[]" accept="image/*"
                                   class="w-full rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white p-2">
                            <p class="text-xs text-slate-500 mt-1">
                                Upload mind map atau gambar untuk pertanyaan ini. Maks 2MB.
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">
                                Sample Answer (Optional)
                            </label>
                            <textarea name="sample_answers[]" rows="3"
                                      class="w-full rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white">{{ old('sample_answers.0') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- ADD QUESTION BUTTON -->
                <button type="button" onclick="addQuestion()"
                        class="mt-2 px-5 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-semibold transition">
                    <i class="fas fa-plus mr-2"></i>
                    Add Question
                </button>
            </div>

            <!-- BUTTONS -->
            <div class="flex gap-3 pt-4 border-t border-slate-200 dark:border-slate-700">
                <button type="submit" class="px-6 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold">
                    <i class="fas fa-save mr-2"></i>
                    Save All
                </button>

                <a href="{{ route('admin.writing-materials.index', $lesson->id) }}"
                   class="px-6 py-3 rounded-xl bg-slate-200 dark:bg-slate-700 text-slate-900 dark:text-white hover:bg-slate-300 dark:hover:bg-slate-600 transition">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    let questionCount = 1;

    function addQuestion() {
        questionCount++;

        const container = document.getElementById('questions-container');
        const template = `
            <div class="question-item bg-slate-50 dark:bg-slate-900/50 rounded-xl p-4 mb-4 border border-slate-200 dark:border-slate-700">
                <div class="flex justify-between items-center mb-3">
                    <h4 class="font-semibold text-slate-900 dark:text-white">
                        Question ${questionCount}
                    </h4>
                    <button type="button" onclick="removeQuestion(this)"
                            class="text-red-500 hover:text-red-700 text-sm font-semibold">
                        <i class="fas fa-trash"></i> Remove
                    </button>
                </div>

                <div class="mb-3">
                    <label class="block text-sm font-medium mb-1">
                        Question Text <span class="text-red-500">*</span>
                    </label>
                    <textarea name="questions[]" rows="3"
                              class="w-full rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white"
                              required></textarea>
                </div>

                <div class="mb-3">
                    <label class="block text-sm font-medium mb-1">
                        Mind Map / Image (Optional)
                    </label>
                    <input type="file" name="question_images[]" accept="image/*"
                           class="w-full rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white p-2">
                    <p class="text-xs text-slate-500 mt-1">
                        Upload mind map atau gambar untuk pertanyaan ini. Maks 2MB.
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">
                        Sample Answer (Optional)
                    </label>
                    <textarea name="sample_answers[]" rows="3"
                              class="w-full rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white"></textarea>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', template);

        // Show remove button for all questions except first
        document.querySelectorAll('.question-item .text-red-500').forEach(btn => {
            btn.classList.remove('hidden');
        });
    }

    function removeQuestion(button) {
        const questionItem = button.closest('.question-item');
        if (document.querySelectorAll('.question-item').length > 1) {
            questionItem.remove();
            // Renumber remaining questions
            document.querySelectorAll('.question-item h4').forEach((h4, index) => {
                h4.textContent = `Question ${index + 1}`;
            });
        } else {
            alert('Minimal 1 question required.');
        }
    }

    // Initialize - hide remove button for first question
    document.addEventListener('DOMContentLoaded', function() {
        const firstRemoveBtn = document.querySelector('.question-item .text-red-500');
        if (firstRemoveBtn) {
            firstRemoveBtn.classList.add('hidden');
        }
    });
</script>
@endpush
@endsection