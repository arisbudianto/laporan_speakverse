<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WritingMaterial;
use App\Models\Lesson;

class WritingMaterialController extends Controller
{

    public function index(Lesson $lesson)
    {
        $materials = WritingMaterial::where(
            'lesson_id',
            $lesson->id
        )
        ->latest()
        ->get();

        return view(
            'admin.writing-materials.index',
            compact(
                'lesson',
                'materials'
            )
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Lesson $lesson)
    {
        return view(
            'admin.writing-materials.create',
            compact('lesson')
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(
        Request $request,
        Lesson $lesson
    )
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'instruction' => 'nullable',
            'passage' => 'nullable',
            'image' => 'nullable|image|max:2048',
        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {

            $imagePath = $request
                ->file('image')
                ->store(
                    'writing',
                    'public'
                );
        }

        WritingMaterial::create([
            'lesson_id' => $lesson->id,
            'title' => $validated['title'],
            'instruction' => $validated['instruction'] ?? null,
            'passage' => $validated['passage'] ?? null,
            'image' => $imagePath,
        ]);

        return redirect()
            ->route(
                'admin.writing-materials.index',
                $lesson->id
            )
            ->with(
                'success',
                'Writing material created successfully.'
            );
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(
        WritingMaterial $material
    )
    {
        return view(
            'admin.writing-materials.edit',
            compact('material')
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        Request $request,
        WritingMaterial $material
    )
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'instruction' => 'nullable',
            'passage' => 'nullable',
            'image' => 'nullable|image|max:2048',
        ]);

        $imagePath = $material->image;

        if ($request->hasFile('image')) {

            $imagePath = $request
                ->file('image')
                ->store(
                    'writing',
                    'public'
                );
        }

        $material->update([
            'title' => $validated['title'],
            'instruction' => $validated['instruction'] ?? null,
            'passage' => $validated['passage'] ?? null,
            'image' => $imagePath,
        ]);

        return redirect()
            ->route(
                'admin.writing-materials.index',
                $material->lesson_id
            )
            ->with(
                'success',
                'Writing material updated successfully.'
            );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(
        WritingMaterial $material
    )
    {
        $lessonId = $material->lesson_id;

        $material->delete();

        return redirect()
            ->route(
                'admin.writing-materials.index',
                $lessonId
            );
    }
}
