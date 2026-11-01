<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssessmentAnswer;
use App\Models\Lesson;
use App\Models\ListeningMaterial;
use App\Models\ListeningQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ListeningQuestionController extends Controller
{
    /**
     * Kategori sub-skill Listening.
     */
    private const SUB_SKILLS = [
        'main_idea' => 'Main Idea',
        'detail_information' => 'Detail Information',
        'inference' => 'Inference',
        'specific_information' => 'Specific Information',
    ];

    /**
     * Daftar question dalam satu Listening Material.
     */
    public function index(
        ListeningMaterial $material
    ) {
        $material->load(
            'lesson.unit'
        );

        $questions = $material
            ->questions()
            ->orderBy('id')
            ->get();

        $subSkillOptions =
            self::SUB_SKILLS;

        /*
        |--------------------------------------------------------------------------
        | Assessment Usage
        |--------------------------------------------------------------------------
        */

        $questionIds = $questions
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values();

        $usedQuestionCounts = $questionIds->isNotEmpty()
            ? AssessmentAnswer::query()
                ->where(
                    'question_type',
                    'listening'
                )
                ->whereIn(
                    'question_id',
                    $questionIds
                )
                ->selectRaw(
                    'question_id, COUNT(*) as total'
                )
                ->groupBy(
                    'question_id'
                )
                ->pluck(
                    'total',
                    'question_id'
                )
            : collect();

        return view(
            'admin.listening-questions.index',
            compact(
                'material',
                'questions',
                'subSkillOptions',
                'usedQuestionCounts'
            )
        );
    }

    /**
     * Form create.
     */
    public function create(
        ListeningMaterial $material
    ) {
        $material->load(
            'lesson.unit'
        );

        $subSkillOptions =
            self::SUB_SKILLS;

        return view(
            'admin.listening-questions.create',
            compact(
                'material',
                'subSkillOptions'
            )
        );
    }

    /**
     * Store multiple questions.
     */
    public function store(
        Request $request,
        ListeningMaterial $material
    ) {
        $validated = $request->validate(
            [
                'questions' => [
                    'required',
                    'array',
                    'min:1',
                ],

                'questions.*.instruction' => [
                    'nullable',
                    'string',
                ],

                'questions.*.question' => [
                    'required',
                    'string',
                ],

                'questions.*.sub_skill' => [
                    'required',
                    'string',
                    Rule::in(
                        array_keys(
                            self::SUB_SKILLS
                        )
                    ),
                ],

                'questions.*.option_a' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'questions.*.option_b' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'questions.*.option_c' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'questions.*.option_d' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'questions.*.option_e' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'questions.*.correct_answer' => [
                    'required',
                    Rule::in([
                        'A',
                        'B',
                        'C',
                        'D',
                        'E',
                    ]),
                ],

                'questions.*.score' => [
                    'required',
                    'integer',
                    'min:1',
                    'max:100',
                ],
            ],
            [
                'questions.required' =>
                    'Please add at least one listening question.',

                'questions.*.question.required' =>
                    'Each question is required.',

                'questions.*.sub_skill.required' =>
                    'Please select a sub-skill for each question.',

                'questions.*.sub_skill.in' =>
                    'One or more selected sub-skill categories are invalid.',

                'questions.*.option_a.required' =>
                    'Option A is required for each question.',

                'questions.*.option_b.required' =>
                    'Option B is required for each question.',

                'questions.*.correct_answer.required' =>
                    'Please select the correct answer for each question.',

                'questions.*.correct_answer.in' =>
                    'The correct answer must be A, B, C, D, or E.',

                'questions.*.score.required' =>
                    'A score is required for each question.',

                'questions.*.score.integer' =>
                    'The score must be a whole number.',

                'questions.*.score.min' =>
                    'The score must be at least 1 point.',

                'questions.*.score.max' =>
                    'The score may not be greater than 100 points.',
            ]
        );

        DB::transaction(
            function () use (
                $validated,
                $material
            ) {
                foreach (
                    $validated['questions']
                    as $questionData
                ) {
                    ListeningQuestion::create([
                        'lesson_id' =>
                            $material->lesson_id,

                        'listening_material_id' =>
                            $material->id,

                        'instruction' =>
                            $questionData['instruction']
                            ?? null,

                        'question' =>
                            $questionData['question'],

                        'audio_file' =>
                            null,

                        'sub_skill' =>
                            $questionData['sub_skill'],

                        'option_a' =>
                            $questionData['option_a'],

                        'option_b' =>
                            $questionData['option_b'],

                        'option_c' =>
                            $questionData['option_c']
                            ?? null,

                        'option_d' =>
                            $questionData['option_d']
                            ?? null,

                        'option_e' =>
                            $questionData['option_e']
                            ?? null,

                        'correct_answer' =>
                            $questionData['correct_answer'],

                        'score' =>
                            $questionData['score'],
                    ]);
                }
            }
        );

        return redirect()
            ->route(
                'admin.listening-questions.index',
                $material->id
            )
            ->with(
                'success',
                'All listening questions were added successfully.'
            );
    }

    /**
     * Bulk update sub-skills.
     */
    public function bulkUpdateSubSkills(
        Request $request,
        ListeningMaterial $material
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

        $questions = ListeningQuestion::query()
            ->where(
                'listening_material_id',
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
                'admin.listening-questions.index',
                $material->id
            )
            ->with(
                'success',
                $updatedCount > 0
                    ? "{$updatedCount} Listening sub-skill(s) updated successfully."
                    : 'No Listening sub-skill changes were needed.'
            );
    }

    /**
     * Legacy lesson index.
     */
    public function lessonIndex(
        Lesson $lesson
    ) {
        return redirect()
            ->route(
                'admin.listening-materials.index',
                $lesson->id
            )
            ->with(
                'success',
                'Listening Pre-test and Post-test now use materials.'
            );
    }

    /**
     * Legacy lesson create.
     */
    public function lessonCreate(
        Lesson $lesson
    ) {
        return redirect()
            ->route(
                'admin.listening-materials.create',
                $lesson->id
            )
            ->with(
                'success',
                'Create a Listening Material first.'
            );
    }

    /**
     * Legacy lesson store.
     */
    public function lessonStore(
        Request $request,
        Lesson $lesson
    ) {
        return redirect()
            ->route(
                'admin.listening-materials.create',
                $lesson->id
            )
            ->with(
                'error',
                'Saving questions directly to a lesson is no longer supported.'
            );
    }

    /**
     * Edit.
     */
    public function edit(
        ListeningQuestion $question
    ) {
        $question->load([
            'material.lesson.unit',
            'lesson.unit',
        ]);

        $subSkillOptions =
            self::SUB_SKILLS;

        return view(
            'admin.listening-questions.edit',
            compact(
                'question',
                'subSkillOptions'
            )
        );
    }

    /**
     * Update.
     */
    public function update(
        Request $request,
        ListeningQuestion $question
    ) {
        $isLegacyLessonMode =
            is_null(
                $question->listening_material_id
            );

        $rules = [
            'instruction' => [
                'nullable',
                'string',
            ],

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
                'nullable',
                'string',
                'max:255',
            ],

            'option_d' => [
                'nullable',
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
        ];

        if ($isLegacyLessonMode) {
            $rules['audio_file'] = [
                'nullable',
                'file',
                'mimes:mp3,wav,mpeg,mpga,m4a,ogg',
                'max:10240',
            ];

            $rules['remove_audio'] = [
                'nullable',
                'boolean',
            ];
        }

        $messages = [
            'question.required' =>
                'The question is required.',

            'sub_skill.required' =>
                'Please select a sub-skill category.',

            'sub_skill.in' =>
                'The selected sub-skill category is invalid.',

            'option_a.required' =>
                'Option A is required.',

            'option_b.required' =>
                'Option B is required.',

            'correct_answer.required' =>
                'Please select the correct answer.',

            'correct_answer.in' =>
                'The correct answer must be A, B, C, D, or E.',

            'score.required' =>
                'The score is required.',

            'score.integer' =>
                'The score must be a whole number.',

            'score.min' =>
                'The score must be at least 1 point.',

            'score.max' =>
                'The score may not be greater than 100 points.',

            'audio_file.file' =>
                'The uploaded audio must be a valid file.',

            'audio_file.mimes' =>
                'The audio file must be an MP3, WAV, MPEG, MPGA, M4A, or OGG file.',

            'audio_file.max' =>
                'The audio file may not be larger than 10 MB.',

            'remove_audio.boolean' =>
                'The remove audio value must be true or false.',
        ];

        $validated =
            $request->validate(
                $rules,
                $messages
            );

        $audioPath =
            $question->audio_file;

        if (
            $isLegacyLessonMode
            && $request->boolean(
                'remove_audio'
            )
            && $audioPath
        ) {
            $this->deleteLegacyAudioIfUnused(
                $audioPath,
                $question->id
            );

            $audioPath = null;
        }

        if (
            $isLegacyLessonMode
            && $request->hasFile(
                'audio_file'
            )
        ) {
            if ($audioPath) {
                $this->deleteLegacyAudioIfUnused(
                    $audioPath,
                    $question->id
                );
            }

            $audioPath = $request
                ->file('audio_file')
                ->store(
                    'listening-question-audios',
                    'public'
                );
        }

        $question->update([
            'instruction' =>
                $validated['instruction']
                ?? null,

            'question' =>
                $validated['question'],

            'audio_file' =>
                $isLegacyLessonMode
                    ? $audioPath
                    : null,

            'sub_skill' =>
                $validated['sub_skill'],

            'option_a' =>
                $validated['option_a'],

            'option_b' =>
                $validated['option_b'],

            'option_c' =>
                $validated['option_c']
                ?? null,

            'option_d' =>
                $validated['option_d']
                ?? null,

            'option_e' =>
                $validated['option_e']
                ?? null,

            'correct_answer' =>
                $validated['correct_answer'],

            'score' =>
                $validated['score'],
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
     * Delete.
     */
    public function destroy(
        ListeningQuestion $question
    ) {
        $redirect =
            $this->redirectAfterAction(
                $question
            );

        if (
            is_null(
                $question->listening_material_id
            )
            && $question->audio_file
        ) {
            $this->deleteLegacyAudioIfUnused(
                $question->audio_file,
                $question->id
            );
        }

        $question->delete();

        return $redirect->with(
            'success',
            'Question deleted successfully.'
        );
    }

    /**
     * Redirect helper.
     */
    private function redirectAfterAction(
        ListeningQuestion $question
    ) {
        if (
            $question->listening_material_id
        ) {
            return redirect()->route(
                'admin.listening-questions.index',
                $question->listening_material_id
            );
        }

        return redirect()->route(
            'admin.listening-materials.index',
            $question->lesson_id
        );
    }

    /**
     * Legacy audio helper.
     */
    private function deleteLegacyAudioIfUnused(
        string $audioPath,
        int $exceptQuestionId
    ): void {
        $stillUsed =
            ListeningQuestion::query()
                ->where(
                    'audio_file',
                    $audioPath
                )
                ->where(
                    'id',
                    '!=',
                    $exceptQuestionId
                )
                ->exists();

        if (!$stillUsed) {
            Storage::disk(
                'public'
            )->delete(
                $audioPath
            );
        }
    }
}