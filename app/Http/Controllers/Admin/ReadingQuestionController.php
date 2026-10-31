<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssessmentAnswer;
use App\Models\Lesson;
use App\Models\ReadingMaterial;
use App\Models\ReadingQuestion;
use App\Services\Learning\DiagnosticEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ReadingQuestionController extends Controller
{
    /**
     * Kategori sub-skill Reading.
     */
    private const SUB_SKILLS = [
        'main_idea' => 'Main Idea',
        'detail_information' => 'Detail Information',
        'inference' => 'Inference',
        'vocabulary_in_context' => 'Vocabulary in Context',
    ];

    /**
     * Display all questions for a specific reading material.
     */
    public function index(ReadingMaterial $material)
    {
        $material->load([
            'lesson.unit',
        ]);

        $questions = $material
            ->questions()
            ->orderBy('id')
            ->get();

        $subSkillOptions = self::SUB_SKILLS;

        /*
        |--------------------------------------------------------------------------
        | Assessment Usage
        |--------------------------------------------------------------------------
        |
        | Digunakan untuk menandai pertanyaan yang sudah pernah digunakan
        | pada AssessmentAnswer.
        |
        | Ini penting karena pertanyaan lama yang sudah pernah dikerjakan
        | akan langsung memengaruhi Sub-Skill Performance di /progress.
        |--------------------------------------------------------------------------
        */

        $questionIds = $questions
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values();

        $usedQuestionCounts = $questionIds->isNotEmpty()
            ? AssessmentAnswer::query()
                ->where('question_type', 'reading')
                ->whereIn('question_id', $questionIds)
                ->selectRaw('question_id, COUNT(*) as total')
                ->groupBy('question_id')
                ->pluck('total', 'question_id')
            : collect();

        return view(
            'admin.reading-questions.index',
            compact(
                'material',
                'questions',
                'subSkillOptions',
                'usedQuestionCounts'
            )
        );
    }

    /**
     * Display the create question form.
     */
    public function create(ReadingMaterial $material)
    {
        $material->load([
            'lesson.unit',
        ]);

        $subSkillOptions = self::SUB_SKILLS;

        return view(
            'admin.reading-questions.create',
            compact(
                'material',
                'subSkillOptions'
            )
        );
    }

    /**
     * Store a new question.
     */
    public function store(
        Request $request,
        ReadingMaterial $material
    ) {
        $validated = $this->validateQuestion(
            $request
        );

        ReadingQuestion::create([
            'lesson_id' =>
                $material->lesson_id,

            'reading_material_id' =>
                $material->id,

            'question' =>
                $validated['question'],

            'option_a' =>
                $validated['option_a'],

            'option_b' =>
                $validated['option_b'],

            'option_c' =>
                $validated['option_c'],

            'option_d' =>
                $validated['option_d'],

            'option_e' =>
                $validated['option_e']
                ?? null,

            'correct_answer' =>
                $validated['correct_answer'],

            'score' =>
                $validated['score'],

            'sub_skill' =>
                $validated['sub_skill'],

            'error_if_wrong' =>
                $this->buildErrorIfWrongMap($validated),

            // Saran guru menyimpan lewat form ini = ditinjau manusia,
            // walau sebelumnya berasal dari usulan AI.
            'error_labels_source' =>
                'manual',

            'rationale' =>
                $validated['rationale'] ?? null,

            'text_span' =>
                $validated['text_span'] ?? null,
        ]);

        return redirect()
            ->route(
                'admin.reading-questions.index',
                $material->id
            )
            ->with(
                'success',
                'Question added successfully.'
            );
    }

    /**
     * Bulk update sub-skill untuk seluruh question pada material.
     */
    public function bulkUpdateSubSkills(
        Request $request,
        ReadingMaterial $material
    ) {
        $validated = $request->validate(
            [
                'sub_skills' => [
                    'required',
                    'array',
                    'min:1',
                ],

                'sub_skills.*' => [
                    'nullable',
                    'string',
                    Rule::in(
                        array_keys(
                            self::SUB_SKILLS
                        )
                    ),
                ],
            ],
            [
                'sub_skills.required' =>
                    'No sub-skill data was submitted.',

                'sub_skills.array' =>
                    'The submitted sub-skill data is invalid.',

                'sub_skills.min' =>
                    'No sub-skill data was submitted.',

                'sub_skills.*.in' =>
                    'One or more selected sub-skill categories are invalid.',
            ]
        );

        $submittedQuestionIds = collect(
            array_keys(
                $validated['sub_skills']
            )
        )
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        $questions = ReadingQuestion::query()
            ->where(
                'reading_material_id',
                $material->id
            )
            ->whereIn(
                'id',
                $submittedQuestionIds
            )
            ->get();

        $updatedCount = 0;

        DB::transaction(
            function () use (
                $questions,
                $validated,
                &$updatedCount
            ) {
                foreach ($questions as $question) {
                    $value =
                        $validated['sub_skills'][$question->id]
                        ?? null;

                    /*
                     * Dropdown kosong tidak menghapus nilai lama.
                     */
                    if (
                        $value === null
                        || trim((string) $value) === ''
                    ) {
                        continue;
                    }

                    if ($question->sub_skill === $value) {
                        continue;
                    }

                    $question->update([
                        'sub_skill' => $value,
                    ]);

                    $updatedCount++;
                }
            }
        );

        return redirect()
            ->route(
                'admin.reading-questions.index',
                $material->id
            )
            ->with(
                'success',
                $updatedCount > 0
                    ? "{$updatedCount} Reading sub-skill(s) updated successfully."
                    : 'No Reading sub-skill changes were needed.'
            );
    }

    /**
     * Legacy Pre-test/Post-test route.
     */
    public function lessonIndex(Lesson $lesson)
    {
        return redirect()
            ->route(
                'admin.reading-materials.index',
                $lesson->id
            );
    }

    /**
     * Legacy create route.
     */
    public function lessonCreate(Lesson $lesson)
    {
        return redirect()
            ->route(
                'admin.reading-materials.create',
                $lesson->id
            )
            ->with(
                'info',
                'Create a reading passage first, then add one or more questions to it.'
            );
    }

    /**
     * Legacy store route.
     */
    public function lessonStore(
        Request $request,
        Lesson $lesson
    ) {
        return redirect()
            ->route(
                'admin.reading-materials.index',
                $lesson->id
            )
            ->with(
                'error',
                'Reading questions must be created through a reading material.'
            );
    }

    /**
     * Display edit form.
     */
    public function edit(ReadingQuestion $question)
    {
        $question->load([
            'material.lesson.unit',
            'lesson.unit',
        ]);

        $subSkillOptions = self::SUB_SKILLS;

        return view(
            'admin.reading-questions.edit',
            compact(
                'question',
                'subSkillOptions'
            )
        );
    }

    /**
     * Update question.
     */
    public function update(
        Request $request,
        ReadingQuestion $question
    ) {
        $validated = $this->validateQuestion(
            $request
        );

        $question->update([
            'question' =>
                $validated['question'],

            'option_a' =>
                $validated['option_a'],

            'option_b' =>
                $validated['option_b'],

            'option_c' =>
                $validated['option_c'],

            'option_d' =>
                $validated['option_d'],

            'option_e' =>
                $validated['option_e']
                ?? null,

            'correct_answer' =>
                $validated['correct_answer'],

            'score' =>
                $validated['score'],

            'sub_skill' =>
                $validated['sub_skill'],

            'error_if_wrong' =>
                $this->buildErrorIfWrongMap($validated),

            // Saran guru menyimpan lewat form ini = ditinjau manusia,
            // walau sebelumnya berasal dari usulan AI.
            'error_labels_source' =>
                'manual',

            'rationale' =>
                $validated['rationale'] ?? null,

            'text_span' =>
                $validated['text_span'] ?? null,
        ]);

        return $this
            ->redirectAfterAction(
                $question
            )
            ->with(
                'success',
                'Question updated successfully.'
            );
    }

    /**
     * Delete question.
     */
    public function destroy(
        ReadingQuestion $question
    ) {
        $redirect =
            $this->redirectAfterAction(
                $question
            );

        $question->delete();

        return $redirect->with(
            'success',
            'Question deleted successfully.'
        );
    }

    /**
     * Validation.
     */
    private function validateQuestion(
        Request $request
    ): array {
        return $request->validate(
            [
                'question' => [
                    'required',
                    'string',
                ],

                'sub_skill' => [
                    'required',
                    'string',
                    Rule::in(
                        array_keys(
                            self::SUB_SKILLS
                        )
                    ),
                ],

                'option_a' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'option_b' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'option_c' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'option_d' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'option_e' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'correct_answer' => [
                    'required',
                    Rule::in([
                        'A',
                        'B',
                        'C',
                        'D',
                        'E',
                    ]),
                ],

                'score' => [
                    'required',
                    'integer',
                    'min:1',
                    'max:100',
                ],

                /*
                |--------------------------------------------------------------------------
                | Modul 2 — Diagnostic Engine (opsional)
                |--------------------------------------------------------------------------
                */

                'rationale' => [
                    'nullable',
                    'string',
                ],

                'text_span' => [
                    'nullable',
                    'string',
                ],

                'error_code_a' => [
                    'nullable',
                    Rule::in(DiagnosticEngine::ERROR_CODES),
                ],

                'error_code_b' => [
                    'nullable',
                    Rule::in(DiagnosticEngine::ERROR_CODES),
                ],

                'error_code_c' => [
                    'nullable',
                    Rule::in(DiagnosticEngine::ERROR_CODES),
                ],

                'error_code_d' => [
                    'nullable',
                    Rule::in(DiagnosticEngine::ERROR_CODES),
                ],

                'error_code_e' => [
                    'nullable',
                    Rule::in(DiagnosticEngine::ERROR_CODES),
                ],
            ],
            [
                'question.required' =>
                    'The question field is required.',

                'question.string' =>
                    'The question must be valid text.',

                'sub_skill.required' =>
                    'Please select a sub-skill category.',

                'sub_skill.string' =>
                    'The sub-skill category must be valid.',

                'sub_skill.in' =>
                    'The selected sub-skill category is invalid.',

                'option_a.required' =>
                    'Option A is required.',

                'option_b.required' =>
                    'Option B is required.',

                'option_c.required' =>
                    'Option C is required.',

                'option_d.required' =>
                    'Option D is required.',

                'correct_answer.required' =>
                    'Please select the correct answer.',

                'correct_answer.in' =>
                    'The correct answer must be A, B, C, D, or E.',

                'score.required' =>
                    'The score field is required.',

                'score.integer' =>
                    'The score must be a whole number.',

                'score.min' =>
                    'The score must be at least 1 point.',

                'score.max' =>
                    'The score may not be greater than 100 points.',
            ]
        );
    }

    /**
     * Redirect helper.
     */
    private function redirectAfterAction(
        ReadingQuestion $question
    ) {
        if ($question->reading_material_id) {
            return redirect()->route(
                'admin.reading-questions.index',
                $question->reading_material_id
            );
        }

        return redirect()->route(
            'admin.reading-materials.index',
            $question->lesson_id
        );
    }

    /**
     * Modul 2 — Diagnostic Engine: gabungkan error_code_a s.d.
     * error_code_e dari form (UX per-opsi, lebih mudah daripada minta
     * admin mengetik JSON manual) menjadi kolom error_if_wrong (JSON
     * map opsi -> kode error). Opsi yang sama dengan correct_answer
     * diabaikan (tidak masuk akal melabeli jawaban benar sebagai
     * error).
     */
    private function buildErrorIfWrongMap(array $validated): ?array
    {
        $map = [];

        foreach (['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D', 'e' => 'E'] as $key => $label) {
            $code = $validated['error_code_' . $key] ?? null;

            if ($code && $label !== $validated['correct_answer']) {
                $map[$label] = $code;
            }
        }

        return $map === [] ? null : $map;
    }
}