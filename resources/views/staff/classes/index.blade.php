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

    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-black">Manajemen Kelas</h1>
            <p class="mt-2 text-slate-500">
                Tambah, ubah, atau hapus kelas yang Anda ampu. Lalu kelola siswa di masing-masing kelas.
            </p>
        </div>
        <a href="{{ route('staff.classes.template') }}"
            style="display:inline-flex;align-items:center;justify-content:center;background:#d97706;color:#fff;font-weight:700;padding:14px 22px;border-radius:16px;text-decoration:none;">
            Unduh Template Excel
        </a>
    </div>

    @if (!empty($canManageOwnClasses))
        <div class="mb-8 rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-black">Tambah kelas ampuan</h2>
            <form method="POST" action="{{ route('staff.classes.assignments.store') }}" class="grid gap-4 md:grid-cols-2">
                @csrf
                @include('partials.school-major-grade-fields', [
                    'schools' => $schools ?? config('schools', []),
                    'selectedSchool' => old('school'),
                    'selectedMajor' => old('major'),
                    'selectedGrade' => old('grade'),
                    'selectedParallel' => old('parallel'),
                    'required' => true,
                ])
                <div class="md:col-span-2">
                    <button style="background:#0e7490;color:#fff;font-weight:700;padding:12px 20px;border-radius:14px;border:0;">
                        Tambah Kelas
                    </button>
                </div>
            </form>
        </div>
    @endif

    <div class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full text-left">
            <thead class="bg-slate-50 text-sm font-bold text-slate-500">
                <tr>
                    <th class="px-5 py-4">Sekolah</th>
                    <th class="px-5 py-4">Jurusan</th>
                    <th class="px-5 py-4">Kelas</th>
                    <th class="px-5 py-4"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($classes as $class)
                    <tr class="border-t border-slate-100">
                        <td class="px-5 py-4">{{ $class->school }}</td>
                        <td class="px-5 py-4">{{ $class->major }}</td>
                        <td class="px-5 py-4 font-semibold">{{ $class->grade }} {{ $class->parallel }}</td>
                        <td class="px-5 py-4">
                            <div style="display:flex;gap:8px;justify-content:flex-end;flex-wrap:wrap;">
                                <a href="{{ route('staff.classes.results', [
                                        'school' => $class->school,
                                        'major' => $class->major,
                                        'grade' => $class->grade,
                                        'parallel' => $class->parallel,
                                    ]) }}"
                                    style="display:inline-flex;align-items:center;background:#7c3aed;color:#fff;font-weight:700;font-size:13px;padding:8px 14px;border-radius:12px;text-decoration:none;">
                                    Hasil Belajar
                                </a>
                                <a href="{{ route('staff.classes.show', [
                                        'school' => $class->school,
                                        'major' => $class->major,
                                        'grade' => $class->grade,
                                        'parallel' => $class->parallel,
                                    ]) }}"
                                    style="display:inline-flex;align-items:center;background:#059669;color:#fff;font-weight:700;font-size:13px;padding:8px 14px;border-radius:12px;text-decoration:none;">
                                    Kelola Siswa
                                </a>
                                @if (!empty($canManageOwnClasses) && !empty($class->id))
                                    <a href="{{ route('staff.classes.assignments.edit', $class->id) }}"
                                        style="display:inline-flex;align-items:center;background:#2563eb;color:#fff;font-weight:700;font-size:13px;padding:8px 14px;border-radius:12px;text-decoration:none;">
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('staff.classes.assignments.destroy', $class->id) }}"
                                        onsubmit="return confirm('Hapus kelas ini dari daftar ampuan? Siswa tidak terhapus.')">
                                        @csrf
                                        @method('DELETE')
                                        <button style="background:#dc2626;color:#fff;font-weight:700;font-size:13px;padding:8px 14px;border-radius:12px;border:0;">
                                            Hapus
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-5 py-10 text-center text-slate-500">
                            Belum ada kelas. Tambahkan kelas ampuan di formulir atas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
