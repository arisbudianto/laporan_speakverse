<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\SpeakingMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SpeakingMaterialController extends Controller
{
    /**
     * Menampilkan daftar Speaking Task pada sebuah lesson.
     */
    public function index(Lesson $lesson)
    {
        $lesson->loadMissing('unit');

        $materials = SpeakingMaterial::query()
            ->where('lesson_id', $lesson->id)
            ->latest()
            ->get();

        return view(
            'admin.speaking-materials.index',
            compact('lesson', 'materials')
        );
    }

    /**
     * Menampilkan form tambah Speaking Task.
     */
    public function create(Lesson $lesson)
    {
        $lesson->loadMissing('unit');

        return view(
            'admin.speaking-materials.create',
            compact('lesson')
        );
    }

    /**
     * Menyimpan Speaking Task baru.
     */
    public function store(Request $request, Lesson $lesson)
    {
        $lesson->loadMissing('unit');

        $isAssessment = $this->isAssessmentLesson($lesson);
        $validated = $this->validateMaterial(
            $request,
            $isAssessment
        );

        $discussionPoints = $this->cleanDiscussionPoints(
            $validated['discussion_points']
        );

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request
                ->file('image')
                ->store('speaking', 'public');
        }

        SpeakingMaterial::create([
            'lesson_id' => $lesson->id,
            'title' => $validated['title'],
            'instruction' => $validated['instruction'],
            'scenario' => $validated['scenario'] ?? null,

            /*
            |--------------------------------------------------------------------------
            | PRETEST / POSTTEST adalah individual speaking
            |--------------------------------------------------------------------------
            */
            'role_a' => $isAssessment
                ? null
                : ($validated['role_a'] ?? null),

            'role_b' => $isAssessment
                ? null
                : ($validated['role_b'] ?? null),

            'discussion_points' => $discussionPoints,

            /*
            |--------------------------------------------------------------------------
            | Durasi disimpan dalam detik
            |--------------------------------------------------------------------------
            |
            | Assessment selalu 2–3 menit. Unit 1–4 tetap mengikuti input admin.
            |
            */
            'min_duration' => $isAssessment
                ? 120
                : ((int) $validated['min_duration_minutes'] * 60),

            'max_duration' => $isAssessment
                ? 180
                : ((int) $validated['max_duration_minutes'] * 60),

            'is_pair_work' => $isAssessment
                ? false
                : $request->boolean('is_pair_work'),

            'ai_evaluation_enabled' => $isAssessment
                ? true
                : $request->boolean('ai_evaluation_enabled'),

            'passage' => $validated['passage'] ?? null,
            'image' => $imagePath,
        ]);

        return redirect()
            ->route(
                'admin.speaking-materials.index',
                $lesson->id
            )
            ->with(
                'success',
                $isAssessment
                    ? 'Individual speaking assessment created successfully.'
                    : 'Speaking task created successfully.'
            );
    }

    /**
     * Menampilkan form edit Speaking Task.
     */
    public function edit(SpeakingMaterial $material)
    {
        $material->loadMissing('lesson.unit');

        return view(
            'admin.speaking-materials.edit',
            compact('material')
        );
    }

    /**
     * Memperbarui Speaking Task.
     */
    public function update(
        Request $request,
        SpeakingMaterial $material
    ) {
        $material->loadMissing('lesson.unit');

        $isAssessment = $this->isAssessmentLesson(
            $material->lesson
        );

        $validated = $this->validateMaterial(
            $request,
            $isAssessment
        );

        $discussionPoints = $this->cleanDiscussionPoints(
            $validated['discussion_points']
        );

        $imagePath = $material->image;

        if ($request->hasFile('image')) {
            if (
                $material->image &&
                Storage::disk('public')->exists($material->image)
            ) {
                Storage::disk('public')->delete($material->image);
            }

            $imagePath = $request
                ->file('image')
                ->store('speaking', 'public');
        }

        $material->update([
            'title' => $validated['title'],
            'instruction' => $validated['instruction'],
            'scenario' => $validated['scenario'] ?? null,

            'role_a' => $isAssessment
                ? null
                : ($validated['role_a'] ?? null),

            'role_b' => $isAssessment
                ? null
                : ($validated['role_b'] ?? null),

            'discussion_points' => $discussionPoints,

            'min_duration' => $isAssessment
                ? 120
                : ((int) $validated['min_duration_minutes'] * 60),

            'max_duration' => $isAssessment
                ? 180
                : ((int) $validated['max_duration_minutes'] * 60),

            'is_pair_work' => $isAssessment
                ? false
                : $request->boolean('is_pair_work'),

            'ai_evaluation_enabled' => $isAssessment
                ? true
                : $request->boolean('ai_evaluation_enabled'),

            'passage' => $validated['passage'] ?? null,
            'image' => $imagePath,
        ]);

        return redirect()
            ->route(
                'admin.speaking-materials.index',
                $material->lesson_id
            )
            ->with(
                'success',
                $isAssessment
                    ? 'Individual speaking assessment updated successfully.'
                    : 'Speaking task updated successfully.'
            );
    }

    /**
     * Menghapus Speaking Task.
     */
    public function destroy(SpeakingMaterial $material)
    {
        $lessonId = $material->lesson_id;

        if (
            $material->image &&
            Storage::disk('public')->exists($material->image)
        ) {
            Storage::disk('public')->delete($material->image);
        }

        $material->delete();

        return redirect()
            ->route(
                'admin.speaking-materials.index',
                $lessonId
            )
            ->with(
                'success',
                'Speaking task deleted successfully.'
            );
    }

    /**
     * Validasi form Speaking Task.
     */
    private function validateMaterial(
        Request $request,
        bool $isAssessment
    ): array {
        $scenarioRules = $isAssessment
            ? ['nullable', 'string']
            : ['required', 'string'];

        $roleRules = $isAssessment
            ? ['nullable', 'string']
            : ['required', 'string'];

        $booleanRules = $isAssessment
            ? [
                'nullable',
                Rule::in(['0', '1', 0, 1]),
            ]
            : [
                'required',
                Rule::in(['0', '1', 0, 1]),
            ];

        return $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'instruction' => [
                'required',
                'string',
            ],

            'scenario' => $scenarioRules,
            'role_a' => $roleRules,
            'role_b' => $roleRules,

            'discussion_points' => [
                'required',
                'array',
                'min:1',
            ],

            'discussion_points.*' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'min_duration_minutes' => [
                'required',
                'integer',
                'min:1',
                'max:60',
            ],

            'max_duration_minutes' => [
                'required',
                'integer',
                'min:1',
                'max:60',
                'gte:min_duration_minutes',
            ],

            'is_pair_work' => $booleanRules,
            'ai_evaluation_enabled' => $booleanRules,

            'passage' => [
                'nullable',
                'string',
            ],

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ]);
    }

    /**
     * Menentukan apakah lesson berada pada PRETEST / POSTTEST.
     */
    private function isAssessmentLesson(?Lesson $lesson): bool
    {
        if (!$lesson) {
            return false;
        }

        $lesson->loadMissing('unit');

        return in_array(
            $lesson->unit?->type,
            ['pretest', 'posttest'],
            true
        );
    }

    /**
     * Menghapus discussion point kosong.
     */
    private function cleanDiscussionPoints(array $points): array
    {
        return collect($points)
            ->map(function ($point) {
                return trim((string) $point);
            })
            ->filter(function ($point) {
                return $point !== '';
            })
            ->values()
            ->all();
    }
}
