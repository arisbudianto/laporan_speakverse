<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeacherClassAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TeacherController extends Controller
{
    public function index()
    {
        $teachers = User::query()
            ->where('role', 'teacher')
            ->withCount('teachingAssignments')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.teachers.index', compact('teachers'));
    }

    public function classes(User $user)
    {
        abort_unless($user->role === 'teacher', 404);

        $assignments = $user->teachingAssignments()
            ->orderBy('school')
            ->orderBy('major')
            ->orderBy('grade')
            ->orderBy('parallel')
            ->get();

        return view('admin.teachers.classes', [
            'teacher' => $user,
            'assignments' => $assignments,
            'schools' => config('schools', []),
        ]);
    }

    public function storeClass(Request $request, User $user)
    {
        abort_unless($user->role === 'teacher', 404);

        $validated = $this->validateAssignment($request);

        TeacherClassAssignment::query()->firstOrCreate(
            [
                'user_id' => $user->id,
                'school' => $validated['school'],
                'major' => $validated['major'],
                'grade' => $validated['grade'],
                'parallel' => $validated['parallel'],
            ]
        );

        return back()->with('success', 'Kelas mengajar berhasil ditambahkan.');
    }

    public function editClass(User $user, TeacherClassAssignment $assignment)
    {
        abort_unless($user->role === 'teacher', 404);
        abort_unless((int) $assignment->user_id === (int) $user->id, 404);

        return view('admin.teachers.class-edit', [
            'teacher' => $user,
            'assignment' => $assignment,
            'schools' => config('schools', []),
        ]);
    }

    public function updateClass(Request $request, User $user, TeacherClassAssignment $assignment)
    {
        abort_unless($user->role === 'teacher', 404);
        abort_unless((int) $assignment->user_id === (int) $user->id, 404);

        $validated = $this->validateAssignment($request, $assignment->id);

        $assignment->update($validated);

        return back()->with('success', 'Kelas mengajar berhasil diperbarui.');
    }

    public function destroyClass(User $user, TeacherClassAssignment $assignment)
    {
        abort_unless($user->role === 'teacher', 404);
        abort_unless((int) $assignment->user_id === (int) $user->id, 404);

        $assignment->delete();

        return back()->with('success', 'Kelas mengajar berhasil dihapus.');
    }

    private function validateAssignment(Request $request, ?int $ignoreId = null): array
    {
        $schools = config('schools', []);
        $majors = $schools[$request->school] ?? [];

        return $request->validate([
            'school' => ['required', 'string', Rule::in(array_keys($schools))],
            'major' => ['required', 'string', Rule::in($majors)],
            'grade' => ['required', 'string', Rule::in(['X', 'XI', 'XII', 'S1'])],
            'parallel' => ['required', 'string', Rule::in(['A', 'B', 'C', 'D'])],
        ]);
    }
}
