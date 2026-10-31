@extends('layouts.admin')

@section('content')
    <div class="mb-8">
        <p class="text-sm font-bold uppercase tracking-wide text-cyan-500">Panel Guru</p>
        <h1 class="mt-2 text-3xl font-black lg:text-4xl">Halo, {{ $teacher->name }}</h1>
        <p class="mt-2 text-slate-500 dark:text-slate-400">
            Kelola materi dan soal untuk kelas yang Anda ampu. Satu guru dapat mengajar beberapa jurusan dan kelas berbeda.
        </p>
    </div>

    <div class="mb-8 grid gap-4 sm:grid-cols-2">
        @if (\Illuminate\Support\Facades\Route::has('staff.classes.index'))
        <a href="{{ route('staff.classes.index') }}"
            class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm transition hover:border-cyan-400 dark:border-white/10 dark:bg-white/[0.03]">
            <div class="text-3xl">🏫</div>
            <h2 class="mt-4 text-xl font-black">Manajemen Kelas</h2>
            <p class="mt-2 text-sm text-slate-500">Tambah siswa lewat Excel, edit, atau hapus data siswa per kelas yang Anda ampu.</p>
        </a>
        @endif
        <a href="{{ route('admin.learning') }}"
            class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm transition hover:border-cyan-400 dark:border-white/10 dark:bg-white/[0.03]">
            <div class="text-3xl">📚</div>
            <h2 class="mt-4 text-xl font-black">Materi & Soal</h2>
            <p class="mt-2 text-sm text-slate-500">Tambah, ubah, atau hapus materi listening, reading, writing, dan speaking.</p>
        </a>
        <a href="{{ route('profile.edit') }}"
            class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm transition hover:border-cyan-400 dark:border-white/10 dark:bg-white/[0.03]">
            <div class="text-3xl">👤</div>
            <h2 class="mt-4 text-xl font-black">Profil</h2>
            <p class="mt-2 text-sm text-slate-500">Perbarui data akun. Kelas ampuan diatur terpisah, bukan di profil.</p>
        </a>
    </div>

    <div class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.03]">
        <div class="border-b border-slate-100 px-6 py-4 dark:border-white/10">
            <h2 class="text-lg font-black">Kelas yang diampu</h2>
        </div>
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
                @forelse ($assignments as $row)
                    <tr class="border-t border-slate-100 dark:border-white/10">
                        <td class="px-5 py-4">{{ $row->school }}</td>
                        <td class="px-5 py-4">{{ $row->major }}</td>
                        <td class="px-5 py-4 font-semibold">{{ $row->grade }}</td>
                        <td class="px-5 py-4 font-semibold">{{ $row->parallel }}</td>
                        <td class="px-5 py-4 text-right">
                            @if (\Illuminate\Support\Facades\Route::has('staff.classes.show'))
                            <a href="{{ route('staff.classes.show', [
                                    'school' => $row->school,
                                    'major' => $row->major,
                                    'grade' => $row->grade,
                                    'parallel' => $row->parallel,
                                ]) }}"
                                style="display:inline-flex;align-items:center;background:#059669;color:#fff;font-weight:700;font-size:13px;padding:8px 14px;border-radius:12px;text-decoration:none;">
                                Kelola Siswa
                            </a>
                            <a href="{{ route('staff.classes.results', [
                                    'school' => $row->school,
                                    'major' => $row->major,
                                    'grade' => $row->grade,
                                    'parallel' => $row->parallel,
                                ]) }}"
                                style="display:inline-flex;align-items:center;background:#7c3aed;color:#fff;font-weight:700;font-size:13px;padding:8px 14px;border-radius:12px;text-decoration:none;margin-left:8px;">
                                Hasil Belajar
                            </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-10 text-center text-slate-500">
                            Belum ada kelas yang diampu. Minta admin menambahkan lewat menu Guru.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
