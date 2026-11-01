<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\ListeningMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ListeningMaterialController extends Controller
{
    public function index(Lesson $lesson)
    {
        $materials = ListeningMaterial::with('questions')
            ->where('lesson_id', $lesson->id)
            ->latest()
            ->get();

        return view(
            'admin.listening-materials.index',
            compact(
                'lesson',
                'materials'
            )
        );
    }

    public function create(Lesson $lesson)
    {
        return view(
            'admin.listening-materials.create',
            compact('lesson')
        );
    }

    public function store(
        Request $request,
        Lesson $lesson
    )
    {
        $validated = $request->validate([
    'title' => 'required|max:255',
    'instruction' => 'nullable',
    'passage' => 'required',
    'audio_file' => 'required|file|mimes:mp3,wav,mpeg,mpga,m4a,ogg|max:20480'
]);

        $audioPath = null;

        if ($request->hasFile('audio_file')) {

            $audioPath = $request
                ->file('audio_file')
                ->store(
                    'listening',
                    'public'
                );
        }

        ListeningMaterial::create([
    'lesson_id' => $lesson->id,
    'title' => $validated['title'],
    'instruction' => $validated['instruction'] ?? null,
    'passage' => $validated['passage'],
    'audio_file' => $audioPath,
]);
        return redirect()
            ->route(
                'admin.listening-materials.index',
                $lesson->id
            )
            ->with(
                'success',
                'Listening Material berhasil ditambahkan.'
            );
    }

    public function edit(
        ListeningMaterial $material
    )
    {
        return view(
            'admin.listening-materials.edit',
            compact('material')
        );
    }

    public function update(
        Request $request,
        ListeningMaterial $material
    )
    {
        $validated = $request->validate([
    'title' => 'required|max:255',
    'instruction' => 'nullable',
    'passage' => 'required',
    'audio_file' => 'nullable|mimes:mp3,wav,mpeg'
]);
        $audioPath = $material->audio_file;

        if ($request->hasFile('audio_file')) {

            if (
                $material->audio_file &&
                Storage::disk('public')->exists(
                    $material->audio_file
                )
            ) {
                Storage::disk('public')->delete(
                    $material->audio_file
                );
            }

            $audioPath = $request
                ->file('audio_file')
                ->store(
                    'listening',
                    'public'
                );
        }

        $material->update([
    'title' => $validated['title'],
    'instruction' => $validated['instruction'] ?? null,
    'passage' => $validated['passage'],
    'audio_file' => $audioPath,
]);

        return redirect()
            ->route(
                'admin.listening-materials.index',
                $material->lesson_id
            )
            ->with(
                'success',
                'Listening Material berhasil diperbarui.'
            );
    }

    public function destroy(
        ListeningMaterial $material
    )
    {
        $lessonId = $material->lesson_id;

        if (
            $material->audio_file &&
            Storage::disk('public')->exists(
                $material->audio_file
            )
        ) {
            Storage::disk('public')->delete(
                $material->audio_file
            );
        }

        $material->delete();

        return redirect()
            ->route(
                'admin.listening-materials.index',
                $lessonId
            )
            ->with(
                'success',
                'Listening Material berhasil dihapus.'
            );
    }
}