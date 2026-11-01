@extends('layouts.admin')

@section('content')
    <a href="{{ url()->previous() }}" class="mb-4 inline-flex items-center gap-1 text-sm font-bold text-slate-500 hover:text-slate-700">
        ← Kembali
    </a>

    <div class="mb-8 rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="text-2xl font-black">{{ $student->name }}</h1>
        <p class="mt-1 text-sm text-slate-500">
            {{ $student->email }} · {{ $student->school }} · {{ $student->major }} · {{ $student->grade }}{{ $student->parallel }}
        </p>

        @if ($cefrBand)
            <span class="mt-3 inline-block rounded-full bg-cyan-100 px-3 py-1 text-xs font-bold text-cyan-700">
                Perkiraan Band CEFR: {{ $cefrBand }}
            </span>
        @endif
    </div>

    {{-- SKOR PER TAHAP KURIKULUM --}}
    <div class="mb-8 rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="mb-1 text-lg font-black">Hasil Belajar per Tahap</h2>
        <p class="mb-4 text-sm text-slate-500">
            Skor terbaru siswa di setiap tahap kurikulum (Pretest → Unit 1-4 → Posttest), per kemampuan.
        </p>

        <div class="overflow-x-auto rounded-2xl border border-slate-200">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-slate-50 font-bold text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Tahap</th>
                        <th class="px-4 py-3">Listening</th>
                        <th class="px-4 py-3">Reading</th>
                        <th class="px-4 py-3">Writing</th>
                        <th class="px-4 py-3">Speaking</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($scoreGrid as $row)
                        <tr>
                            <td class="px-4 py-3 font-bold">{{ $row['unit']->title }}</td>
                            @foreach ($skills as $skill)
                                <td class="px-4 py-3">
                                    @if ($row['scores'][$skill] !== null)
                                        <span class="font-bold {{ $row['scores'][$skill] >= 70 ? 'text-emerald-600' : 'text-amber-600' }}">
                                            {{ $row['scores'][$skill] }}
                                        </span>
                                    @else
                                        <span class="text-slate-300">—</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-slate-400">Belum ada tahap kurikulum yang terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ANALISIS BELAJAR --}}
    <div class="mb-8 rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="mb-1 text-lg font-black">Analisis Belajar</h2>
        <p class="mb-4 text-sm text-slate-500">
            Diambil dari data interaksi siswa (Modul 1-4) — bukan cuma skor akhir, tapi bagaimana siswa sampai ke jawabannya.
        </p>

        <div class="mb-6 grid gap-4 sm:grid-cols-3">
            <div class="rounded-2xl bg-slate-50 p-4">
                <p class="text-xs font-bold text-slate-500">Akurasi percobaan pertama</p>
                <p class="mt-1 text-2xl font-black">
                    {{ $firstAttemptAccuracy !== null ? $firstAttemptAccuracy . '%' : '—' }}
                </p>
                <p class="mt-1 text-xs text-slate-400">
                    {{ $totalCorrectFirstAttempt }} benar dari {{ $totalAttempted }} soal dicoba
                </p>
            </div>

            <div class="rounded-2xl bg-slate-50 p-4">
                <p class="text-xs font-bold text-slate-500">Hint Level 1</p>
                <p class="mt-1 text-2xl font-black">{{ (int) ($hintCountsByLevel[1] ?? 0) }}x</p>
            </div>

            <div class="rounded-2xl bg-slate-50 p-4">
                <p class="text-xs font-bold text-slate-500">Hint Level 3 (Socratic)</p>
                <p class="mt-1 text-2xl font-black">{{ (int) ($hintCountsByLevel[3] ?? 0) }}x</p>
            </div>
        </div>

        <h3 class="mb-2 text-sm font-bold text-slate-600">Distribusi Jenis Kesalahan</h3>
        <p class="mb-3 text-xs text-slate-400">
            Dari jawaban Reading/Vocabulary/Listening yang SALAH dan sudah diberi label error oleh admin.
        </p>

        @if ($errorFrequencyTotal > 0)
            <div class="space-y-3">
                @foreach ($errorFrequency as $code => $count)
                    @php
                        $percent = round($count / $errorFrequencyTotal * 100);
                    @endphp
                    <div>
                        <div class="mb-1 flex items-center justify-between text-sm">
                            <span class="font-semibold text-slate-700">{{ $errorCodeLabels[$code] ?? $code }}</span>
                            <span class="text-slate-500">{{ $count }} ({{ $percent }}%)</span>
                        </div>
                        <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full bg-purple-500" style="width: {{ $percent }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-slate-500">
                Belum ada data — soal yang dikerjakan siswa ini belum diberi label error, atau belum ada jawaban salah pada soal yang sudah dilabel.
            </p>
        @endif
    </div>
@endsection
