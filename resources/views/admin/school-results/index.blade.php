@extends('layouts.admin')

@section('content')
    @php
        $fmt = fn ($v, $d = 1) => $v === null ? '–' : (str_contains($__s = number_format((float) $v, $d), '.') ? rtrim(rtrim($__s, '0'), '.') : $__s);
        $pct = fn ($v) => $v === null ? '–' : number_format((float) $v, 0) . '%';
        $tone = function ($v) {
            if ($v === null) { return 'bg-slate-100 text-slate-500 dark:bg-white/5 dark:text-slate-400'; }
            if ($v >= 85) { return 'bg-green-500/10 text-green-600 dark:text-green-400'; }
            if ($v >= 70) { return 'bg-cyan-500/10 text-cyan-600 dark:text-cyan-400'; }
            if ($v >= 60) { return 'bg-yellow-500/10 text-yellow-600 dark:text-yellow-400'; }
            return 'bg-red-500/10 text-red-500 dark:text-red-400';
        };
        $o = $report['overview'];
        $selSchool = $filters['school'];
        $selGrade = $filters['grade'];
        $card = 'rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-white/[0.03]';
        $maxTrend = 1;
        foreach ($report['trend'] as $t) { $maxTrend = max($maxTrend, (float) ($t['avg'] ?? 0)); }
        $maxErr = 1;
        foreach ($report['errors'] as $e) { $maxErr = max($maxErr, (int) $e['count']); }
    @endphp

    <div class="space-y-8">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-3xl font-black">Analisis Hasil Belajar Sekolah</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Perbandingan hasil belajar seluruh sekolah. Angka dihitung dengan aturan yang sama seperti dasbor guru.
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.school-results.export', ['mode' => 'sekolah', 'school' => $selSchool, 'grade' => $selGrade]) }}"
                   class="rounded-2xl bg-purple-600 px-4 py-2 text-sm font-bold text-white hover:bg-purple-700">
                    Unduh ringkasan (Excel)
                </a>
                <a href="{{ route('admin.school-results.export', ['mode' => 'siswa', 'school' => $selSchool, 'grade' => $selGrade]) }}"
                   class="rounded-2xl border border-slate-300 px-4 py-2 text-sm font-bold hover:bg-slate-100 dark:border-white/10 dark:hover:bg-white/5">
                    Unduh per siswa (Excel)
                </a>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.school-results') }}" class="{{ $card }} flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-xs font-bold uppercase text-slate-500">Sekolah</label>
                <select name="school" class="mt-1 rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-slate-900">
                    <option value="">Semua sekolah</option>
                    @foreach ($report['schools'] as $s)
                        <option value="{{ $s['school'] }}" @if ($selSchool === $s['school']) selected @endif>{{ $s['school'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-slate-500">Kelas</label>
                <select name="grade" class="mt-1 rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-slate-900">
                    <option value="">Semua tingkat</option>
                    @foreach (['X', 'XI', 'XII'] as $g)
                        <option value="{{ $g }}" @if ($selGrade === $g) selected @endif>Kelas {{ $g }}</option>
                    @endforeach
                </select>
            </div>
            <button class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white dark:bg-white dark:text-slate-900">Terapkan</button>
            @if ($selSchool || $selGrade)
                <a href="{{ route('admin.school-results') }}" class="text-sm font-semibold text-purple-500">Reset</a>
            @endif
        </form>

        @if (($o['n'] ?? 0) === 0)
            <div class="{{ $card }} text-center text-slate-500">Belum ada siswa pada cakupan ini.</div>
        @else
            {{-- KPI --}}
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="{{ $card }}">
                    <p class="text-sm font-semibold text-slate-500">Siswa</p>
                    <p class="mt-2 text-4xl font-black">{{ number_format($o['n']) }}</p>
                    <p class="mt-2 text-xs text-slate-500">Sudah mulai: {{ $pct($o['started_pct']) }} · Aktif 7 hari: {{ $pct($o['active_pct']) }}</p>
                </div>
                <div class="{{ $card }}">
                    <p class="text-sm font-semibold text-slate-500">Pretest → Posttest</p>
                    <p class="mt-2 text-4xl font-black">{{ $fmt($o['pre']) }} → {{ $fmt($o['post']) }}</p>
                    <p class="mt-2 text-xs text-slate-500">Kenaikan rata-rata: {{ $o['gain'] === null ? '–' : (($o['gain'] > 0 ? '+' : '') . $fmt($o['gain'])) }} (n = {{ $o['gain_n'] }} siswa lengkap pre &amp; post)</p>
                </div>
                <div class="{{ $card }}">
                    <p class="text-sm font-semibold text-slate-500">Pelajaran selesai</p>
                    <p class="mt-2 text-4xl font-black">{{ $pct($o['progress_pct']) }}</p>
                    <p class="mt-2 text-xs text-slate-500">Rata-rata XP: {{ $fmt($o['avg_xp'], 0) }}</p>
                </div>
                <div class="{{ $card }}">
                    <p class="text-sm font-semibold text-slate-500">Perlu intervensi</p>
                    <p class="mt-2 text-4xl font-black text-red-500">{{ number_format($o['intervention']) }}</p>
                    <p class="mt-2 text-xs text-slate-500">{{ $pct($o['intervention_pct']) }} dari siswa · Mahir: {{ $pct($o['mahir_pct']) }}</p>
                </div>
            </div>

            {{-- Insight --}}
            @if (count($report['insights']))
                <div class="{{ $card }}">
                    <h2 class="text-lg font-black">Temuan utama</h2>
                    <ul class="mt-3 list-disc space-y-2 pl-5 text-sm">
                        @foreach ($report['insights'] as $line)
                            <li>{{ $line }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Sekolah --}}
            <div class="{{ $card }}">
                <h2 class="text-lg font-black">Perbandingan antar sekolah</h2>
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full min-w-[900px] text-left text-sm">
                        <thead class="text-xs uppercase text-slate-500">
                            <tr>
                                <th class="py-2 pr-3">Sekolah</th>
                                <th class="px-2">Siswa</th>
                                <th class="px-2">Mulai</th>
                                <th class="px-2">Aktif 7h</th>
                                <th class="px-2">XP</th>
                                <th class="px-2">Pre</th>
                                <th class="px-2">Post</th>
                                <th class="px-2">Naik (n)</th>
                                <th class="px-2">L</th>
                                <th class="px-2">R</th>
                                <th class="px-2">W</th>
                                <th class="px-2">S</th>
                                <th class="px-2">Progres</th>
                                <th class="px-2">Intervensi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                            @foreach ($report['schools'] as $s)
                                <tr>
                                    <td class="py-2 pr-3 font-bold">
                                        <a class="text-purple-500 hover:underline" href="{{ route('admin.school-results', ['school' => $s['school'], 'grade' => $selGrade]) }}">{{ $s['school'] }}</a>
                                    </td>
                                    <td class="px-2">{{ $s['n'] }}</td>
                                    <td class="px-2">{{ $pct($s['started_pct']) }}</td>
                                    <td class="px-2">{{ $pct($s['active_pct']) }}</td>
                                    <td class="px-2">{{ $fmt($s['avg_xp'], 0) }}</td>
                                    <td class="px-2"><span class="rounded-lg px-2 py-0.5 {{ $tone($s['pre']) }}">{{ $fmt($s['pre']) }}</span></td>
                                    <td class="px-2"><span class="rounded-lg px-2 py-0.5 {{ $tone($s['post']) }}">{{ $fmt($s['post']) }}</span></td>
                                    <td class="px-2">{{ $s['gain'] === null ? '–' : (($s['gain'] > 0 ? '+' : '') . $fmt($s['gain'])) }} ({{ $s['gain_n'] }})</td>
                                    @foreach (['listening', 'reading', 'writing', 'speaking'] as $k)
                                        <td class="px-2"><span class="rounded-lg px-2 py-0.5 {{ $tone($s['skills'][$k]) }}">{{ $fmt($s['skills'][$k]) }}</span></td>
                                    @endforeach
                                    <td class="px-2">
                                        <div class="h-2 w-20 rounded-full bg-slate-200 dark:bg-white/10">
                                            <div class="h-2 rounded-full bg-purple-500" style="width: {{ min(100, max(0, (float) $s['progress_pct'])) }}%"></div>
                                        </div>
                                        <span class="text-xs text-slate-500">{{ $pct($s['progress_pct']) }}</span>
                                    </td>
                                    <td class="px-2 font-bold {{ $s['intervention'] > 0 ? 'text-red-500' : '' }}">{{ $s['intervention'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="mt-3 text-xs text-slate-500">L/R/W/S = rata-rata skor terbaru per keterampilan. Kolom “Naik” hanya dari siswa yang punya pretest dan posttest (n).</p>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                {{-- Keterampilan --}}
                <div class="{{ $card }}">
                    <h2 class="text-lg font-black">Rata-rata per keterampilan</h2>
                    <div class="mt-4 space-y-4">
                        @foreach ($skillLabels as $k => $label)
                            @php $v = $o['skills'][$k] ?? null; @endphp
                            <div>
                                <div class="flex justify-between text-sm"><span class="font-semibold">{{ $label }}</span><span>{{ $fmt($v) }}</span></div>
                                <div class="mt-1 h-3 rounded-full bg-slate-200 dark:bg-white/10">
                                    <div class="h-3 rounded-full bg-cyan-500" style="width: {{ min(100, max(0, (float) $v)) }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Tren --}}
                <div class="{{ $card }}">
                    <h2 class="text-lg font-black">Tren skor 8 minggu terakhir</h2>
                    <div class="mt-4 flex h-40 items-end gap-2">
                        @foreach ($report['trend'] as $t)
                            <div class="flex flex-1 flex-col items-center justify-end">
                                <span class="text-[10px] text-slate-500">{{ $t['avg'] === null ? '' : $fmt($t['avg']) }}</span>
                                <div class="w-full rounded-t-lg bg-purple-500/70" style="height: {{ $t['avg'] === null ? 2 : max(2, round(((float) $t['avg'] / 100) * 120)) }}px"></div>
                                <span class="mt-1 text-[10px] text-slate-500">{{ $t['week'] }}</span>
                                <span class="text-[10px] text-slate-400">n={{ $t['n'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Kesalahan --}}
            <div class="{{ $card }}">
                <h2 class="text-lg font-black">Sebaran kesalahan Reading</h2>
                @if (count($report['errors']))
                    <div class="mt-4 space-y-3">
                        @foreach ($report['errors'] as $e)
                            <div>
                                <div class="flex justify-between text-sm"><span class="font-semibold">{{ $e['label'] }}</span><span>{{ $e['count'] }} ({{ $fmt($e['pct'], 0) }}%)</span></div>
                                <div class="mt-1 h-3 rounded-full bg-slate-200 dark:bg-white/10">
                                    <div class="h-3 rounded-full bg-red-400" style="width: {{ round(($e['count'] / $maxErr) * 100) }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="mt-3 text-sm text-slate-500">Belum ada data kesalahan.</p>
                @endif
            </div>

            {{-- Kelas --}}
            @if ($selSchool && count($report['classes']))
                <div class="{{ $card }}">
                    <h2 class="text-lg font-black">Kelas di {{ $selSchool }}</h2>
                    <div class="mt-4 overflow-x-auto">
                        <table class="w-full min-w-[700px] text-left text-sm">
                            <thead class="text-xs uppercase text-slate-500">
                                <tr><th class="py-2">Kelas</th><th>Siswa</th><th>Pre</th><th>Post</th><th>Progres</th><th>Intervensi</th><th></th></tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                                @foreach ($report['classes'] as $c)
                                    <tr>
                                        <td class="py-2 font-bold">{{ $c['label'] }}</td>
                                        <td>{{ $c['n'] }}</td>
                                        <td>{{ $fmt($c['pre']) }}</td>
                                        <td>{{ $fmt($c['post']) }}</td>
                                        <td>{{ $pct($c['progress_pct']) }}</td>
                                        <td class="{{ $c['intervention'] > 0 ? 'font-bold text-red-500' : '' }}">{{ $c['intervention'] }}</td>
                                        <td>
                                            <a class="text-purple-500 hover:underline" href="{{ route('staff.classes.results', [$selSchool, $c['major'], $c['grade'], $c['parallel']]) }}">Lihat detail</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- Perhatian --}}
            <div class="{{ $card }}">
                <h2 class="text-lg font-black">Siswa yang perlu perhatian (maks. 15)</h2>
                @if (count($report['attention']))
                    <div class="mt-4 overflow-x-auto">
                        <table class="w-full min-w-[640px] text-left text-sm">
                            <thead class="text-xs uppercase text-slate-500">
                                <tr><th class="py-2">Nama</th><th>Sekolah</th><th>Kelas</th><th>Progres</th><th>Salah inferensial</th><th>Hint L3</th></tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                                @foreach ($report['attention'] as $p)
                                    <tr>
                                        <td class="py-2 font-bold">{{ $p['name'] }}</td>
                                        <td>{{ $p['school'] }}</td>
                                        <td>{{ $p['grade'] }} {{ $p['major'] }} {{ $p['parallel'] }}</td>
                                        <td>{{ $pct($p['progress'] * 100) }}</td>
                                        <td>{{ $p['infer'] }}</td>
                                        <td>{{ $p['l3'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="mt-3 text-sm text-slate-500">Tidak ada siswa yang memenuhi kriteria intervensi.</p>
                @endif
                <p class="mt-3 text-xs text-slate-500">
                    Kriteria: salah inferensial percobaan pertama &gt; {{ $report['thresholds']['inferential'] ?? '–' }} atau hint level 3 ≥ {{ $report['thresholds']['hint_l3'] ?? '–' }}.
                </p>
            </div>
        @endif
    </div>
@endsection
