<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\SpeakingMaterial;
use App\Models\SpeakingQuestion;
use Illuminate\Http\Request;

class SpeakingQuestionController extends Controller
{
    public function index(SpeakingMaterial $material)
    {
        $questions = SpeakingQuestion::where('speaking_material_id', $material->id)
            ->latest()
            ->get();

        return view('admin.speaking-questions.index', compact('material', 'questions'));
    }

    public function create(SpeakingMaterial $material)
    {
        return view('admin.speaking-questions.create', compact('material'));
    }

    public function store(Request $request, SpeakingMaterial $material)
    {
        $validated = $this->validateQuestion($request);

        $imagePath = $this->uploadImage($request);

        SpeakingQuestion::create([
            'lesson_id' => $material->lesson_id,
            'speaking_material_id' => $material->id,
            'question' => $validated['question'],
            'image' => $imagePath,
        ]);

        return redirect()
            ->route('admin.speaking-questions.index', $material->id)
            ->with('success', 'Speaking Question created successfully.');
    }

    public function lessonIndex(Lesson $lesson)
    {
        $questions = SpeakingQuestion::where('lesson_id', $lesson->id)
            ->whereNull('speaking_material_id')
            ->latest()
            ->get();

        return view('admin.speaking-questions.index', compact('lesson', 'questions'));
    }

    public function lessonCreate(Lesson $lesson)
    {
        return view('admin.speaking-questions.create', compact('lesson'));
    }

    public function lessonStore(Request $request, Lesson $lesson)
    {
        $validated = $this->validateQuestion($request);

        $imagePath = $this->uploadImage($request);

        SpeakingQuestion::create([
            'lesson_id' => $lesson->id,
            'speaking_material_id' => null,
            'question' => $validated['question'],
            'image' => $imagePath,
        ]);

        return redirect()
            ->route('admin.speaking-lesson-questions.index', $lesson->id)
            ->with('success', 'Pre/Post-test speaking question berhasil ditambahkan.');
    }

    public function edit(SpeakingQuestion $question)
    {
        return view('admin.speaking-questions.edit', compact('question'));
    }

    public function update(Request $request, SpeakingQuestion $question)
    {
        $validated = $this->validateQuestion($request);

        $imagePath = $question->image;

        if ($request->hasFile('image')) {
            $imagePath = $this->uploadImage($request);
        }

        $question->update([
            'question' => $validated['question'],
            'image' => $imagePath,
        ]);

        return $this->redirectAfterAction($question)
            ->with('success', 'Speaking Question updated successfully.');
    }

    public function destroy(SpeakingQuestion $question)
    {
        $redirect = $this->redirectAfterAction($question);

        $question->delete();

        return $redirect->with('success', 'Speaking Question deleted successfully.');
    }

    private function validateQuestion(Request $request): array
    {
        return $request->validate([
            'question' => 'required',
            'image' => 'nullable|image|max:2048',
        ]);
    }

    private function uploadImage(Request $request): ?string
    {
        if (!$request->hasFile('image')) {
            return null;
        }

        return $request
            ->file('image')
            ->store('speaking-questions', 'public');
    }

    private function redirectAfterAction(SpeakingQuestion $question)
    {
        if ($question->speaking_material_id) {
            return redirect()->route(
                'admin.speaking-questions.index',
                $question->speaking_material_id
            );
        }

        return redirect()->route(
            'admin.speaking-lesson-questions.index',
            $question->lesson_id
        );
    }
}