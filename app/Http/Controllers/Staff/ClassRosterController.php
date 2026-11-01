<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\TeacherClassAssignment;
use App\Models\User;
use App\Support\SimpleXlsxReader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ClassRosterController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $classes = $this->visibleClasses($user);

        return view('staff.classes.index', [
            'classes' => $classes,
            'schools' => config('schools', []),
            'canManageOwnClasses' => in_array($user->role, ['teacher', 'admin'], true),
        ]);
    }

    public function storeAssignment(Request $request): RedirectResponse
    {
        $user = Auth::user();
        abort_unless(in_array($user->role, ['teacher', 'admin'], true), 403);

        $validated = $this->validatedClass($request);

        TeacherClassAssignment::query()->firstOrCreate(
            [
                'user_id' => $user->id,
                'school' => $validated['school'],
                'major' => $validated['major'],
                'grade' => $validated['grade'],
                'parallel' => $validated['parallel'],
            ]
        );

        return back()->with('success', 'Kelas ampuan ditambahkan.');
    }

    public function editAssignment(TeacherClassAssignment $assignment): View
    {
        $this->authorizeAssignment($assignment);

        return view('staff.classes.edit-assignment', [
            'assignment' => $assignment,
            'schools' => config('schools', []),
        ]);
    }

    public function updateAssignment(Request $request, TeacherClassAssignment $assignment): RedirectResponse
    {
        $this->authorizeAssignment($assignment);
        $assignment->update($this->validatedClass($request));

        return redirect()
            ->route('staff.classes.index')
            ->with('success', 'Kelas ampuan diperbarui.');
    }

    public function destroyAssignment(TeacherClassAssignment $assignment): RedirectResponse
    {
        $this->authorizeAssignment($assignment);
        $assignment->delete();

        return back()->with('success', 'Kelas ampuan dihapus. Data siswa tidak ikut terhapus.');
    }

    private function authorizeAssignment(TeacherClassAssignment $assignment): void
    {
        $user = Auth::user();
        abort_unless(
            $user->role === 'admin' || (int) $assignment->user_id === (int) $user->id,
            403
        );
    }

    public function show(Request $request): View
    {
        $class = $this->validatedClass($request);
        $this->authorizeClass($class);

        $students = User::query()
            ->where('role', 'student')
            ->where('school', $class['school'])
            ->where('major', $class['major'])
            ->where('grade', $class['grade'])
            ->where('parallel', $class['parallel'])
            ->orderBy('name')
            ->get();

        return view('staff.classes.show', [
            'class' => $class,
            'students' => $students,
            'schools' => config('schools', []),
        ]);
    }

    public function template(): BinaryFileResponse
    {
        $path = public_path('templates/template-unggah-siswa.xlsx');
        abort_unless(is_file($path), 404);

        return response()->download($path, 'template-unggah-siswa.xlsx');
    }

    public function import(Request $request): RedirectResponse
    {
        $class = $this->validatedClass($request);
        $this->authorizeClass($class);

        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx', 'max:5120'],
        ]);

        $rows = SimpleXlsxReader::rows($request->file('file')->getRealPath());
        if ($rows === []) {
            return back()->with('error', 'File Excel kosong.');
        }

        $header = array_map(fn ($v) => strtolower(trim((string) $v)), $rows[0]);
        $map = [];
        foreach ($header as $i => $name) {
            $map[$name] = $i;
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach (array_slice($rows, 1) as $row) {
            $name = trim((string) ($row[$map['nama'] ?? 0] ?? ''));
            $email = strtolower(trim((string) ($row[$map['email'] ?? 1] ?? '')));
            if ($name === '' || $email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $skipped++;
                continue;
            }

            $password = trim((string) ($row[$map['password'] ?? 2] ?? ''));
            $school = trim((string) ($row[$map['sekolah'] ?? 3] ?? '')) ?: $class['school'];
            $major = trim((string) ($row[$map['jurusan'] ?? 4] ?? '')) ?: $class['major'];
            $grade = strtoupper(trim((string) ($row[$map['kelas'] ?? 5] ?? '')) ?: $class['grade']);
            $parallel = strtoupper(trim((string) ($row[$map['paralel'] ?? 6] ?? '')) ?: $class['parallel']);

            if (! $this->isAllowedMajor($school, $major) || ! in_array($grade, ['X', 'XI', 'XII', 'S1'], true) || ! in_array($parallel, ['A', 'B', 'C', 'D'], true)) {
                $skipped++;
                continue;
            }

            if (! $this->canManageClass([
                'school' => $school,
                'major' => $major,
                'grade' => $grade,
                'parallel' => $parallel,
            ])) {
                $skipped++;
                continue;
            }

            $student = User::query()->where('email', $email)->first();
            // 'role' bukan mass-assignable (lihat App\Models\User),
            // jadi tidak dimasukkan ke $payload dan diset secara
            // eksplisit ke 'student' di bawah setelah fill()/new().
            $payload = [
                'name' => $name,
                'school' => $school,
                'major' => $major,
                'grade' => $grade,
                'parallel' => $parallel,
                'email_verified_at' => now(),
            ];

            if ($student) {
                if (! in_array($student->role, ['student', 'user', null], true)) {
                    $skipped++;
                    continue;
                }
                if ($password !== '') {
                    $payload['password'] = Hash::make($password);
                }
                $student->fill($payload);
                $student->role = 'student';
                $student->save();
                $updated++;
                continue;
            }

            $payload['email'] = $email;
            $payload['password'] = Hash::make($password !== '' ? $password : 'Siswa2026!');
            $newStudent = new User($payload);
            $newStudent->role = 'student';
            $newStudent->save();
            $created++;
        }

        return back()->with(
            'success',
            "Unggah selesai. Baru: {$created}, diperbarui: {$updated}, dilewati: {$skipped}."
        );
    }

    public function storeStudent(Request $request): RedirectResponse
    {
        $class = $this->validatedClass($request);
        $this->authorizeClass($class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        // 'role' diset eksplisit setelah new() karena tidak lagi
        // mass-assignable (lihat App\Models\User).
        $student = new User([
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'password' => Hash::make($validated['password'] ?? 'Siswa2026!'),
            'school' => $class['school'],
            'major' => $class['major'],
            'grade' => $class['grade'],
            'parallel' => $class['parallel'],
            'email_verified_at' => now(),
        ]);
        $student->role = 'student';
        $student->save();

        return back()->with('success', 'Siswa ditambahkan.');
    }

    public function updateStudent(Request $request, User $user): RedirectResponse
    {
        abort_unless(in_array($user->role, ['student', 'user'], true), 404);

        $schools = config('schools', []);
        $majors = $schools[$request->school] ?? [];

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'school' => ['required', Rule::in(array_keys($schools))],
            'major' => ['required', Rule::in($majors)],
            'grade' => ['required', Rule::in(['X', 'XI', 'XII', 'S1'])],
            'parallel' => ['required', Rule::in(['A', 'B', 'C', 'D'])],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $this->authorizeClass([
            'school' => $validated['school'],
            'major' => $validated['major'],
            'grade' => $validated['grade'],
            'parallel' => $validated['parallel'],
        ]);

        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return back()->with('success', 'Data siswa diperbarui.');
    }

    public function destroyStudent(User $user): RedirectResponse
    {
        abort_unless(in_array($user->role, ['student', 'user'], true), 404);

        $this->authorizeClass([
            'school' => $user->school,
            'major' => $user->major,
            'grade' => $user->grade,
            'parallel' => $user->parallel,
        ]);

        $user->delete();

        return back()->with('success', 'Siswa dihapus.');
    }

    private function visibleClasses(User $user): array
    {
        if ($user->role === 'admin') {
            $fromAssignments = TeacherClassAssignment::query()
                ->get(['school', 'major', 'grade', 'parallel']);
            $fromStudents = User::query()
                ->where('role', 'student')
                ->whereNotNull('school')
                ->whereNotNull('major')
                ->whereNotNull('grade')
                ->whereNotNull('parallel')
                ->get(['school', 'major', 'grade', 'parallel']);

            return $fromAssignments
                ->concat($fromStudents)
                ->unique(fn ($row) => implode('|', [$row->school, $row->major, $row->grade, $row->parallel]))
                ->sortBy(fn ($row) => $row->school.$row->major.$row->grade.$row->parallel)
                ->values()
                ->all();
        }

        return $user->teachingAssignments()
            ->orderBy('school')
            ->orderBy('major')
            ->orderBy('grade')
            ->orderBy('parallel')
            ->get()
            ->all();
    }

    private function validatedClass(Request $request): array
    {
        $schools = config('schools', []);
        $majors = $schools[$request->school] ?? [];

        return $request->validate([
            'school' => ['required', Rule::in(array_keys($schools))],
            'major' => ['required', Rule::in($majors)],
            'grade' => ['required', Rule::in(['X', 'XI', 'XII', 'S1'])],
            'parallel' => ['required', Rule::in(['A', 'B', 'C', 'D'])],
        ]);
    }

    private function authorizeClass(array $class): void
    {
        abort_unless($this->canManageClass($class), 403);
    }

    private function canManageClass(array $class): bool
    {
        $user = Auth::user();
        if ($user->role === 'admin') {
            return true;
        }

        return $user->teachingAssignments()
            ->where('school', $class['school'])
            ->where('major', $class['major'])
            ->where('grade', $class['grade'])
            ->where('parallel', $class['parallel'])
            ->exists();
    }

    private function isAllowedMajor(string $school, string $major): bool
    {
        return in_array($major, config('schools', [])[$school] ?? [], true);
    }
}
