@extends('layouts.admin')

@section('content')
    @if ($errors->any())
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 font-semibold text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="mb-8">
        <a href="{{ route('staff.classes.index') }}" class="text-sm font-semibold text-slate-500">← Manajemen Kelas</a>
        <h1 class="mt-4 text-3xl font-black">Edit Kelas Ampuan</h1>
    </div>

    <div class="max-w-3xl rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('staff.classes.assignments.update', $assignment) }}" class="grid gap-4">
            @csrf
            @method('PUT')
            @include('partials.school-major-grade-fields', [
                'schools' => $schools ?? config('schools', []),
                'selectedSchool' => old('school', $assignment->school),
                'selectedMajor' => old('major', $assignment->major),
                'selectedGrade' => old('grade', $assignment->grade),
                'selectedParallel' => old('parallel', $assignment->parallel),
                'required' => true,
            ])
            <div>
                <button style="background:#0e7490;color:#fff;font-weight:700;padding:12px 20px;border-radius:14px;border:0;">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
@endsection
