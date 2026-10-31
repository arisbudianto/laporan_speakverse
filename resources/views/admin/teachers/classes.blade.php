@extends('layouts.admin')

@section('content')
    @if (session('success'))
        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 font-semibold text-emerald-700">
            {{ session('success') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 font-semibold text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="mb-8">
        <a href="{{ route('admin.teachers.index') }}" class="text-sm font-semibold text-slate-500 hover:text-cyan-500">
            ← Kembali ke daftar guru
        </a>
        <h1 class="mt-4 text-3xl font-black">Kelas Diajar</h1>
        <p class="mt-2 text-slate-500">
            {{ $teacher->name }}
            @if ($teacher->nip)
                · NIP {{ $teacher->nip }}
            @endif
        </p>
    </div>

    <div class="mb-8 rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-white/[0.03]">
        <h2 class="mb-5 text-xl font-black">Tambah kelas</h2>
        <form method="POST" action="{{ route('admin.teachers.classes.store', $teacher) }}" class="grid gap-4 md:grid-cols-2">
            @csrf
            @include('partials.school-major-grade-fields', [
                'schools' => $schools,
                'selectedSchool' => old('school', $teacher->school),
                'selectedMajor' => old('major'),
                'selectedGrade' => old('grade'),
                'selectedParallel' => old('parallel'),
                'required' => true,
            ])
            <div class="md:col-span-2">
                <button class="rounded-2xl bg-gradient-to-r from-cyan-500 to-blue-600 px-6 py-4 font-bold text-white">
                    Tambah Kelas
                </button>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.03]">
        <table class="min-w-full text-left">
            <thead class="bg-slate-50 text-sm font-bold text-slate-500 dark:bg-white/5">
                <tr>
                    <th class="px-5 py-4">Sekolah</th>
                    <th class="px-5 py-4">Jurusan</th>
                    <th class="px-5 py-4">Kelas</th>
                    <th class="px-5 py-4">Paralel</th>
                    <th class="px-5 py-4"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($assignments as $assignment)
                    <tr class="border-t border-slate-100 dark:border-white/10">
                        <td class="px-5 py-4">{{ $assignment->school }}</td>
                        <td class="px-5 py-4">{{ $assignment->major }}</td>
                        <td class="px-5 py-4 font-semibold">{{ $assignment->grade }}</td>
                        <td class="px-5 py-4 font-semibold">{{ $assignment->parallel }}</td>
                        <td class="px-5 py-4 text-right">
                            <div class="flex justify-end gap-4">
                                <a href="{{ route('admin.teachers.classes.edit', [$teacher, $assignment]) }}"
                                    class="font-bold text-cyan-600 hover:text-cyan-500">Edit</a>
                                <form method="POST"
                                    action="{{ route('admin.teachers.classes.destroy', [$teacher, $assignment]) }}"
                                    onsubmit="return confirm('Hapus kelas ini dari daftar mengajar?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="font-bold text-red-500 hover:text-red-400">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-10 text-center text-slate-500">
                            Guru ini belum memiliki kelas mengajar.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
