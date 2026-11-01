@extends('layouts.admin')

@section('content')
    @php
        $card = 'rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-white/[0.03]';
        $pct = fn ($v) => $v === null ? '–' : rtrim(rtrim(number_format((float) $v, 1), '0'), '.') . '%';
        $num = fn ($v, $d = 1) => $v === null ? '–' : (str_contains($__s = number_format((float) $v, $d), '.') ? rtrim(rtrim($__s, '0'), '.') : $__s);
        $ready = $report['ready'] ?? false;
        $tone = [
            'konsisten' => 'bg-green-500', 'sporadis' => 'bg-yellow-500', 'menumpuk' => 'bg-orange-500',
            'menurun' => 'bg-red-400', 'berhenti' => 'bg-red-600', 'tidak_aktif' => 'bg-slate-400', 'baru' => 'bg-cyan-500', 'hanya_tes' => 'bg-indigo-400',
        ];
        $badge = [
            'konsisten' => 'bg-green-500/10 text-green-600 dark:text-green-400',
            'sporadis' => 'bg-yellow-500/10 text-yellow-600 dark:text-yellow-400',
            'menumpuk' => 'bg-orange-500/10 text-orange-600 dark:text-orange-400',
            'menurun' => 'bg-red-500/10 text-red-500',
            'berhenti' => 'bg-red-500/10 text-red-600',
            'tidak_aktif' => 'bg-slate-100 text-slate-500 dark:bg-white/5',
            'baru' => 'bg-cyan-500/10 text-cyan-600',
            'hanya_tes' => 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400',
        ];
    @endphp

    <div class="space-y-8">
        @include('admin.learning-analytics._tabs')
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-3xl font-black">Analitik Lanjutan · Keterlibatan &amp; Konsistensi</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Pola akses belajar 28 hari terakhir. Dibaca dari jejak belajar yang tercatat (kuis, tes, pelajaran selesai, XP).
                </p>
            </div>
            @if ($ready)
                <a href="{{ route('admin.la.engagement.export', ['school' => $filters['school'], 'grade' => $filters['grade']]) }}"
                   class="rounded-2xl bg-purple-600 px-4 py-2 text-sm font-bold text-white hover:bg-purple-700">Unduh per siswa (Excel)</a>
            @endif
        </div>

        @if (! $ready)
            <div class="{{ $card }}">
                <p class="font-bold">Tabel agregat belum ada.</p>
                <p class="mt-2 text-sm text-slate-500">Jalankan <code>php artisan migrate</code> lalu <code>php artisan la:aggregate --since={{ now()->subDays(60)->format('Y-m-d') }}</code>.</p>
            </div>
        @else
            <form method="GET" action="{{ route('admin.la.engagement') }}" class="{{ $card }} flex flex-wrap items-end gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500">Sekolah</label>
                    <select name="school" class="mt-1 rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-slate-900">
                        <option value="">Semua sekolah</option>
                        @foreach ($report['schools'] as $s)
                            <option value="{{ $s['school'] }}" @if ($filters['school'] === $s['school']) selected @endif>{{ $s['school'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500">Kelas</label>
                    <select name="grade" class="mt-1 rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-slate-900">
                        <option value="">Semua tingkat</option>
                        @foreach (['X', 'XI', 'XII'] as $g)
                            <option value="{{ $g }}" @if ($filters['grade'] === $g) selected @endif>Kelas {{ $g }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white dark:bg-white dark:text-slate-900">Terapkan</button>
                @if ($filters['school'] || $filters['grade'])
                    <a href="{{ route('admin.la.engagement') }}" class="text-sm font-semibold text-purple-500">Reset</a>
                @endif
                <p class="ml-auto text-xs text-slate-500">Agregat terakhir dihitung: {{ $report['computed_at'] ?? 'belum pernah' }}</p>
            </form>

            @php $o = $report['overview']; @endphp
            @if ($o['n'] === 0)
                <div class="{{ $card }} text-center text-slate-500">Belum ada siswa pada cakupan ini.</div>
            @else
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="{{ $card }}">
                        <p class="text-sm font-semibold text-slate-500">Siswa</p>
                        <p class="mt-2 text-4xl font-black">{{ number_format($o['n']) }}</p>
                        <p class="mt-2 text-xs text-slate-500">Aktif 7 hari terakhir: {{ $pct($o['active7_pct']) }}</p>
                    </div>
                    <div class="{{ $card }}">
                        <p class="text-sm font-semibold text-slate-500">Rata-rata hari aktif / 28 hari</p>
                        <p class="mt-2 text-4xl font-black">{{ $num($o['active_days_mean']) }}</p>
                        <p class="mt-2 text-xs text-slate-500">Median menit aktif: {{ $num($o['minutes_median'], 0) }}</p>
                    </div>
                    <div class="{{ $card }}">
                        <p class="text-sm font-semibold text-slate-500">Indeks keteraturan (median)</p>
                        <p class="mt-2 text-4xl font-black">{{ $num($o['ri_median']) }}</p>
                        <p class="mt-2 text-xs text-slate-500">0–100 · dihitung untuk {{ $o['ri_n'] }} siswa (≥3 hari aktif)</p>
                    </div>
                    <div class="{{ $card }}">
                        <p class="text-sm font-semibold text-slate-500">Berhenti / menurun</p>
                        <p class="mt-2 text-4xl font-black text-red-500">{{ $o['patterns']['berhenti'] + $o['patterns']['menurun'] }}</p>
                        <p class="mt-2 text-xs text-slate-500">Tidak aktif sama sekali: {{ $o['patterns']['tidak_aktif'] }}</p>
                    </div>
                </div>

                {{-- Pola --}}
                <div class="{{ $card }}">
                    <h2 class="text-lg font-black">Sebaran pola akses</h2>
                    <div class="mt-4 flex h-5 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-white/10" role="img"
                         aria-label="Sebaran pola akses siswa">
                        @foreach ($labels as $code => $label)
                            @if ($o['pattern_pct'][$code] > 0)
                                <div class="{{ $tone[$code] }}" style="width: {{ $o['pattern_pct'][$code] }}%" title="{{ $label }}: {{ $o['patterns'][$code] }}"></div>
                            @endif
                        @endforeach
                    </div>
                    <div class="mt-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ($labels as $code => $label)
                            <div class="flex items-center gap-2 text-sm">
                                <span class="inline-block h-3 w-3 rounded-full {{ $tone[$code] }}"></span>
                                <span class="font-semibold">{{ $label }}</span>
                                <span class="text-slate-500">{{ $o['patterns'][$code] }} ({{ $pct($o['pattern_pct'][$code]) }})</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <div class="{{ $card }}">
                        <h2 class="text-lg font-black">Siswa aktif per minggu (8 minggu)</h2>
                        <div class="mt-4 flex h-40 items-end gap-2">
                            @foreach ($report['weekly'] as $w)
                                <div class="flex flex-1 flex-col items-center justify-end">
                                    <span class="text-[10px] text-slate-500">{{ $pct($w['pct']) }}</span>
                                    <div class="w-full rounded-t-lg bg-cyan-500/70" style="height: {{ max(2, round($w['pct'] * 1.2)) }}px"></div>
                                    <span class="mt-1 text-[10px] text-slate-500">{{ substr($w['week_start'], 5) }}</span>
                                </div>
                            @endforeach
                        </div>
                        <p class="mt-2 text-xs text-slate-500">Persentase siswa dengan ≥1 hari aktif pada jendela 7 hari yang berakhir pada minggu itu.</p>
                    </div>
                    <div class="{{ $card }}">
                        <h2 class="text-lg font-black">Jam belajar dominan</h2>
                        @php $maxH = max(1, max($o['peak_hours'])); @endphp
                        <div class="mt-4 flex h-40 items-end gap-[3px]">
                            @foreach ($o['peak_hours'] as $h => $c)
                                <div class="flex flex-1 flex-col items-center justify-end" title="{{ sprintf('%02d:00', $h) }} · {{ $c }} siswa">
                                    <div class="w-full rounded-t bg-purple-500/70" style="height: {{ $c > 0 ? max(3, round($c / $maxH * 120)) : 1 }}px"></div>
                                    @if ($h % 3 === 0)<span class="mt-1 text-[10px] text-slate-500">{{ sprintf('%02d', $h) }}</span>@else<span class="mt-1 text-[10px]">&nbsp;</span>@endif
                                </div>
                            @endforeach
                        </div>
                        <p class="mt-2 text-xs text-slate-500">Jumlah siswa menurut jam tersibuk mereka (waktu server).</p>
                    </div>
                </div>

                {{-- Sekolah --}}
                <div class="{{ $card }}">
                    <h2 class="text-lg font-black">Perbandingan antar sekolah</h2>
                    <div class="mt-4 overflow-x-auto">
                        <table class="w-full min-w-[820px] text-left text-sm">
                            <thead class="text-xs uppercase text-slate-500">
                                <tr><th class="py-2 pr-3">Sekolah</th><th>Siswa</th><th>Aktif 7h</th><th>Hari aktif</th><th>Keteraturan</th><th>Konsisten</th><th>Menumpuk</th><th>Menurun</th><th>Berhenti</th><th>Tak aktif</th></tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                                @foreach ($report['schools'] as $s)
                                    <tr>
                                        <td class="py-2 pr-3 font-bold"><a class="text-purple-500 hover:underline" href="{{ route('admin.la.engagement', ['school' => $s['school'], 'grade' => $filters['grade']]) }}">{{ $s['school'] }}</a></td>
                                        <td>{{ $s['n'] }}</td>
                                        <td>{{ $pct($s['active7_pct']) }}</td>
                                        <td>{{ $num($s['active_days_mean']) }}</td>
                                        <td>{{ $num($s['ri_median']) }}</td>
                                        <td>{{ $s['patterns']['konsisten'] }}</td>
                                        <td>{{ $s['patterns']['menumpuk'] }}</td>
                                        <td>{{ $s['patterns']['menurun'] }}</td>
                                        <td class="{{ $s['patterns']['berhenti'] > 0 ? 'font-bold text-red-500' : '' }}">{{ $s['patterns']['berhenti'] }}</td>
                                        <td>{{ $s['patterns']['tidak_aktif'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Perlu dicek --}}
                <div class="{{ $card }}">
                    <h2 class="text-lg font-black">Perlu dicek guru (maks. 20)</h2>
                    @if (count($report['attention']))
                        <div class="mt-4 overflow-x-auto">
                            <table class="w-full min-w-[720px] text-left text-sm">
                                <thead class="text-xs uppercase text-slate-500">
                                    <tr><th class="py-2">Nama</th><th>Sekolah</th><th>Kelas</th><th>Pola</th><th>Hari sejak aktif</th><th>Hari aktif</th><th>Tren</th><th>Kunjungan tanpa belajar</th></tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                                    @foreach ($report['attention'] as $p)
                                        <tr>
                                            <td class="py-2 font-bold">{{ $p['student']['name'] }}</td>
                                            <td>{{ $p['student']['school'] }}</td>
                                            <td>{{ $p['student']['grade'] }} {{ $p['student']['major'] }} {{ $p['student']['parallel'] }}</td>
                                            <td><span class="rounded-lg px-2 py-0.5 {{ $badge[$p['pattern']] }}">{{ $labels[$p['pattern']] }}</span></td>
                                            <td>{{ $p['days_since_last'] ?? '–' }}</td>
                                            <td>{{ $p['active_days'] }}</td>
                                            <td>{{ $num($p['trend_slope']) }}</td>
                                            <td>{{ $p['visit_only_days'] ?? '–' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="mt-3 text-sm text-slate-500">Tidak ada siswa dengan pola berhenti, menurun, atau menumpuk.</p>
                    @endif
                    <p class="mt-3 text-xs text-slate-500">Label ini gambaran perilaku 28 hari terakhir, bukan penilaian permanen terhadap siswa.</p>
                </div>

                {{-- Metodologi --}}
                <details class="{{ $card }}">
                    <summary class="cursor-pointer text-lg font-black">Definisi &amp; keterbatasan</summary>
                    <div class="mt-4 space-y-2 text-sm text-slate-600 dark:text-slate-300">
                        <p><b>Sesi</b>: rangkaian aktivitas dengan jeda ≤ 30 menit. <b>Menit aktif</b> = jumlah jeda antar-aktivitas (dipangkas maks. 10 menit) + 1 menit penutup; ini estimasi, bukan waktu layar.</p>
                        <p><b>Indeks keteraturan</b> = 100 × (0,4 × minggu aktif/4 + 0,3 × (1 − min(CV jeda, 1,5)/1,5) + 0,3 × (1 − Gini menit antar hari aktif)). Heuristik transparan, belum divalidasi.</p>
                        <p><b>Pola</b> (urutan aturan): tidak aktif → berhenti (≥14 hari tak aktif) → data belum cukup (&lt;3 hari aktif) → menumpuk (satu hari ≥50% menit) → menurun (kemiringan hari-aktif/minggu ≤ −0,75) → konsisten (indeks ≥ 65) → sporadis.</p>
                        <p>Keterbatasan: login dan halaman yang dibuka tidak tercatat, hanya aktivitas yang menghasilkan jejak. Belajar menjelang tes tidak dapat dipastikan karena jadwal tes belum tersimpan. Pola adalah korelasi perilaku, bukan sebab.</p>
                        <p>Rujukan: Kizilcec dkk. (2017) <i>Computers &amp; Education</i>; Macfadyen &amp; Dawson (2010) <i>Computers &amp; Education</i>; Baker &amp; Yacef (2009) <i>JEDM</i>.</p>
                    </div>
                </details>
            @endif
        @endif
    </div>
@endsection
