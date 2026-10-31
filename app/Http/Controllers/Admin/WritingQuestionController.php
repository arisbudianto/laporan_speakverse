<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\WritingMaterial;
use App\Models\WritingQuestion;
use Illuminate\Http\Request;

class WritingQuestionController extends Controller
{
    /**
     * Display all questions for a specific writing material.
     */
    public function index(WritingMaterial $material)
    {
        $questions = WritingQuestion::where(
            'writing_material_id',
            $material->id
        )
            ->latest()
            ->get();

        return view(
            'admin.writing-questions.index',
            compact(
                'material',
                'questions'
            )
        );
    }

    /**
     * Display the create question form for a writing material.
     */
    public function create(WritingMaterial $material)
    {
        return view(
            'admin.writing-questions.create',
            compact('material')
        );
    }

    /**
     * Store a new question for a writing material.
     */
    public function store(
        Request $request,
        WritingMaterial $material
    ) {
        $validated = $this->validateQuestion($request);

        $imagePath = $this->uploadImage($request);

        WritingQuestion::create([
            'lesson_id' => $material->lesson_id,
            'writing_material_id' => $material->id,
            'question' => $validated['question'],
            'sample_answer' => $validated['sample_answer'] ?? null,
            'image' => $imagePath,
        ]);

        return redirect()
            ->route(
                'admin.writing-questions.index',
                $material->id
            )
            ->with(
                'success',
                'Writing question created successfully.'
            );
    }

    /**
     * Display Pre-test/Post-test writing questions for a lesson.
     */
    public function lessonIndex(Lesson $lesson)
    {
        $questions = WritingQuestion::where(
            'lesson_id',
            $lesson->id
        )
            ->whereNull('writing_material_id')
            ->latest()
            ->get();

        return view(
            'admin.writing-questions.index',
            compact(
                'lesson',
                'questions'
            )
        );
    }

    /**
     * Display the create question form for a Pre-test/Post-test lesson.
     */
    public function lessonCreate(Lesson $lesson)
    {
        return view(
            'admin.writing-questions.create',
            compact('lesson')
        );
    }

    /**
     * Store a new Pre-test/Post-test writing question.
     */
    public function lessonStore(
        Request $request,
        Lesson $lesson
    ) {
        $validated = $this->validateQuestion($request);

        $imagePath = $this->uploadImage($request);

        WritingQuestion::create([
            'lesson_id' => $lesson->id,
            'writing_material_id' => null,
            'question' => $validated['question'],
            'sample_answer' => $validated['sample_answer'] ?? null,
            'image' => $imagePath,
        ]);

        return redirect()
            ->route(
                'admin.writing-lesson-questions.index',
                $lesson->id
            )
            ->with(
                'success',
                'Pre/Post-test writing question added successfully.'
            );
    }

    /**
     * Display the edit question form.
     */
    public function edit(WritingQuestion $question)
    {
        return view(
            'admin.writing-questions.edit',
            compact('question')
        );
    }

    /**
     * Update an existing writing question.
     */
    public function update(
        Request $request,
        WritingQuestion $question
    ) {
        $validated = $this->validateQuestion($request);

        $imagePath = $question->image;

        if ($request->hasFile('image')) {
            $imagePath = $this->uploadImage($request);
        }

        $question->update([
            'question' => $validated['question'],
            'sample_answer' => $validated['sample_answer'] ?? null,
            'image' => $imagePath,
        ]);

        return $this
            ->redirectAfterAction($question)
            ->with(
                'success',
                'Writing question updated successfully.'
            );
    }

    /**
     * Delete a writing question.
     */
    public function destroy(WritingQuestion $question)
    {
        $redirect = $this->redirectAfterAction($question);

        $question->delete();

        return $redirect->with(
            'success',
            'Writing question deleted successfully.'
        );
    }

    /**
     * Validate writing question input.
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

                'sample_answer' => [
                    'nullable',
                    'string',
                ],

                'image' => [
                    'nullable',
                    'image',
                    'max:2048',
                ],
            ],
            [
                'question.required' =>
                    'The question field is required.',

                'question.string' =>
                    'The question must be valid text.',

                'sample_answer.string' =>
                    'The sample answer must be valid text.',

                'image.image' =>
                    'The uploaded file must be a valid image.',

                'image.max' =>
                    'The image may not be larger than 2 MB.',
            ]
        );
    }

    /**
     * Upload a writing question image when one is provided.
     */
    private function uploadImage(
        Request $request
    ): ?string {
        if (!$request->hasFile('image')) {
            return null;
        }

        return $request
            ->file('image')
            ->store(
                'writing-questions',
                'public'
            );
    }

    /**
     * Determine the redirect destination after updating or deleting a question.
     */
    private function redirectAfterAction(
        WritingQuestion $question
    ) {
        if ($question->writing_material_id) {
            return redirect()->route(
                'admin.writing-questions.index',
                $question->writing_material_id
            );
        }

        return redirect()->route(
            'admin.writing-lesson-questions.index',
            $question->lesson_id
        );
    }
}
