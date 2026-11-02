@extends('layouts.admin')

@section('content')
    @if (session('success'))
        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 font-semibold text-emerald-700">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error') || $errors->any())
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 font-semibold text-red-700">
            {{ session('error') ?: $errors->first() }}
        </div>
    @endif

    <div class="mb-8">
        <a href="{{ route('admin.school-results') }}" class="text-sm font-semibold text-slate-500">← Analisis Hasil Belajar Sekolah</a>
        <h1 class="mt-4 text-3xl font-black">Impor Nilai Pelengkap</h1>
        <p class="mt-2 text-slate-500">
            Mengisi nilai yang masih kosong di laporan, bukan menimpa data yang sudah ada.
        </p>
    </div>

    <div class="grid gap-6 lg:grid-cols-[1.1fr_1fr]">
        <div class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-white/[0.03]">
            <h2 class="mb-4 text-lg font-black">Unggah Excel</h2>
            <p class="mb-4 text-sm text-slate-500 dark:text-slate-400">
                File harus punya sheet bernama tepat <strong>"Hasil Belajar"</strong>, dengan kolom
                <code>Email</code> dan salah satu/lebih dari <code>XP</code>, <code>Listening</code>,
                <code>Reading</code>, <code>Writing</code>, <code>Speaking</code>, <code>Pretest</code>,
                <code>Posttest</code>. Kolom lain diabaikan.
            </p>
            <form method="POST" action="{{ route('admin.school-results.import.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <input type="file" name="file" accept=".xlsx" required class="block w-full text-sm">
                <button class="rounded-2xl bg-gradient-to-r from-cyan-500 to-blue-600 px-6 py-3 font-bold text-white">
                    Impor
                </button>
            </form>
        </div>

        <div class="rounded-[28px] border border-amber-200 bg-amber-50 p-6 text-sm text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200">
            <h2 class="mb-3 text-base font-black">Yang perlu diketahui sebelum impor</h2>
            <ul class="list-disc space-y-2 pl-5">
                <li>
                    Siswa dicocokkan lewat <strong>email</strong>. Baris dengan email yang tidak ketemu
                    di data siswa akan dilewati, bukan membuat akun baru.
                </li>
                <li>
                    Kalau siswa <strong>sudah punya nilai asli</strong> untuk suatu kolom (XP, salah satu
                    skill, pretest, atau posttest), nilai dari file <strong>tidak akan menimpanya</strong>.
                    Hanya kolom yang masih kosong yang diisi.
                </li>
                <li>
                    Fitur ini <strong>tidak mengubah</strong> "Pelajaran Selesai", "Progres", "Perlu
                    Intervensi", "Salah Inferensial", atau "Hint Level 3" — angka-angka itu berasal dari
                    jejak aktivitas belajar nyata dan dipakai sistem peringatan dini ke guru, jadi tidak
                    diisi dari Excel.
                </li>
                <li>
                    Setiap nilai yang dibuat dari impor ini ditandai secara internal supaya bisa dibedakan
                    dari pengerjaan asli siswa kapan pun dibutuhkan. Rincian setiap perubahan disimpan ke
                    berkas log di server.
                </li>
            </ul>
        </div>
    </div>
@endsection
