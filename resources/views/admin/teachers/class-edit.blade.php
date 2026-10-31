@extends('layouts.admin')

@section('content')
    @if ($errors->any())
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 font-semibold text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="mb-8">
        <a href="{{ route('admin.teachers.classes', $teacher) }}" class="text-sm font-semibold text-slate-500 hover:text-cyan-500">
            ← Kembali
        </a>
        <h1 class="mt-4 text-3xl font-black">Edit Kelas Mengajar</h1>
        <p class="mt-2 text-slate-500">{{ $teacher->name }}</p>
    </div>

    <div class="max-w-3xl rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-white/[0.03]">
        <form method="POST" action="{{ route('admin.teachers.classes.update', [$teacher, $assignment]) }}" class="grid gap-4">
            @csrf
            @method('PUT')
            @include('partials.school-major-grade-fields', [
                'schools' => $schools,
                'selectedSchool' => old('school', $assignment->school),
                'selectedMajor' => old('major', $assignment->major),
                'selectedGrade' => old('grade', $assignment->grade),
                'selectedParallel' => old('parallel', $assignment->parallel),
                'required' => true,
            ])
            <div>
                <button class="rounded-2xl bg-gradient-to-r from-cyan-500 to-blue-600 px-6 py-4 font-bold text-white">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
@endsection
