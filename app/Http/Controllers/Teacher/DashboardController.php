<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $teacher = Auth::user();
        abort_unless($teacher && $teacher->role === 'teacher', 403);

        $assignments = collect();
        try {
            if (method_exists($teacher, 'teachingAssignments')) {
                $assignments = $teacher->teachingAssignments()
                    ->orderBy('school')
                    ->orderBy('major')
                    ->orderBy('grade')
                    ->orderBy('parallel')
                    ->get();
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return view('teacher.dashboard', compact('teacher', 'assignments'));
    }
}
