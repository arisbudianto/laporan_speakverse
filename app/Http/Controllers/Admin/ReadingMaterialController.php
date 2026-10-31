<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\ReadingMaterial;
use Illuminate\Http\Request;

class ReadingMaterialController extends Controller
{
    /**
     * Display all reading materials for a lesson.
     */
    public function index(Lesson $lesson)
    {
        $lesson->load('unit');

        $materials = ReadingMaterial::withCount('questions')
            ->where('lesson_id', $lesson->id)
            ->latest()
            ->get();

        return view(
            'admin.reading-materials.index',
            compact('lesson', 'materials')
        );
    }

    /**
     * Display the create reading material form.
     */
    public function create(Lesson $lesson)
    {
        $lesson->load('unit');

        return view(
            'admin.reading-materials.create',
            compact('lesson')
        );
    }

    /**
     * Store a new reading material.
     */
    public function store(
        Request $request,
        Lesson $lesson
    ) {
        $validated = $this->validateMaterial($request);

        ReadingMaterial::create([
            'lesson_id' => $lesson->id,
            'title' => $validated['title'],
            'instruction' => $validated['instruction'] ?? null,
            'passage' => $validated['passage'],
        ]);

        return redirect()
            ->route(
                'admin.reading-materials.index',
                $lesson->id
            )
            ->with(
                'success',
                'Reading Material added successfully.'
            );
    }

    /**
     * Display the edit reading material form.
     */
    public function edit(ReadingMaterial $material)
    {
        $material->load('lesson.unit');

        return view(
            'admin.reading-materials.edit',
            compact('material')
        );
    }

    /**
     * Update an existing reading material.
     */
    public function update(
        Request $request,
        ReadingMaterial $material
    ) {
        $validated = $this->validateMaterial($request);

        $material->update([
            'title' => $validated['title'],
            'instruction' => $validated['instruction'] ?? null,
            'passage' => $validated['passage'],
        ]);

        return redirect()
            ->route(
                'admin.reading-materials.index',
                $material->lesson_id
            )
            ->with(
                'success',
                'Reading Material updated successfully.'
            );
    }

    /**
     * Delete a reading material and its related questions.
     */
    public function destroy(ReadingMaterial $material)
    {
        $lessonId = $material->lesson_id;

        $material->delete();

        return redirect()
            ->route(
                'admin.reading-materials.index',
                $lessonId
            )
            ->with(
                'success',
                'Reading Material and its questions were deleted successfully.'
            );
    }

    /**
     * Validate reading material input.
     */
    private function validateMaterial(
        Request $request
    ): array {
        return $request->validate(
            [
                'title' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'instruction' => [
                    'nullable',
                    'string',
                ],

                'passage' => [
                    'required',
                    'string',
                ],
            ],
            [
                'title.required' =>
                    'The title field is required.',

                'title.string' =>
                    'The title must be valid text.',

                'title.max' =>
                    'The title may not be longer than 255 characters.',

                'instruction.string' =>
                    'The instruction must be valid text.',

                'passage.required' =>
                    'The reading passage field is required.',

                'passage.string' =>
                    'The reading passage must be valid text.',
            ]
        );
    }
}
