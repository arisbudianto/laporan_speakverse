@extends('layouts.admin')

@section('content')
    @if (session('success'))
        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 font-semibold text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-black lg:text-4xl">Manajemen Guru</h1>
            <p class="mt-2 text-slate-500 dark:text-slate-400">
                Atur guru dan kelas yang diajar (jurusan, tingkat, paralel).
            </p>
        </div>
        <a href="{{ route('admin.users.create') }}"
            class="inline-flex items-center justify-center rounded-2xl bg-gradient-to-r from-cyan-500 to-blue-600 px-6 py-4 font-bold text-white">
            + Tambah User Guru
        </a>
    </div>

    <div class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.03]">
        <table class="min-w-full text-left">
            <thead class="bg-slate-50 text-sm font-bold text-slate-500 dark:bg-white/5">
                <tr>
                    <th class="px-5 py-4">Nama</th>
                    <th class="px-5 py-4">NIP</th>
                    <th class="px-5 py-4">Sekolah</th>
                    <th class="px-5 py-4">Kelas diajar</th>
                    <th class="px-5 py-4"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($teachers as $teacher)
                    <tr class="border-t border-slate-100 dark:border-white/10">
                        <td class="px-5 py-4 font-semibold">{{ $teacher->name }}</td>
                        <td class="px-5 py-4">{{ $teacher->nip ?: '—' }}</td>
                        <td class="px-5 py-4">{{ $teacher->school ?: '—' }}</td>
                        <td class="px-5 py-4">{{ $teacher->teaching_assignments_count }} kelas</td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('admin.teachers.classes', $teacher) }}"
                                class="font-bold text-cyan-600 hover:text-cyan-500">
                                Kelola Kelas
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-10 text-center text-slate-500">
                            Belum ada user dengan role guru. Buat user baru lalu pilih role Teacher.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $teachers->links() }}
    </div>
@endsection
