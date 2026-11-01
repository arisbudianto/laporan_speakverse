@extends('layouts.admin')

@section('content')
    @php
        $num = fn ($v, $d = 1) => $v === null ? '–' : (str_contains($__s = number_format((float) $v, $d), '.') ? rtrim(rtrim($__s, '0'), '.') : $__s);
        $o = $e['overview'] ?? null;
        $t = $a['test'] ?? null;
    @endphp
    <style>
        .rpt h2 { font-size: 15px; font-weight: 800; margin: 18px 0 6px; border-bottom: 1px solid #cbd5e1; padding-bottom: 3px; }
        .rpt table { width: 100%; border-collapse: collapse; font-size: 12px; }
        .rpt td, .rpt th { border: 1px solid #e2e8f0; padding: 4px 6px; text-align: left; }
        .rpt .warn { background: #fef3c7; color: #92400e; padding: 8px 10px; border-radius: 8px; font-size: 12px; margin: 6px 0; }
        @media print { .no-print { display: none !important; } body { background: #fff !important; } .rpt { color: #000 !important; } aside, nav, header { display: none !important; } }
    </style>
    <div class="space-y-4">
        <div class="no-print">
            @include('admin.learning-analytics._tabs')
            <form method="GET" action="{{ route('admin.la.report') }}" class="mt-4 flex flex-wrap items-end gap-3 text-sm">
                <label class="font-semibold">Sekolah<br><select name="school" class="rounded-xl border border-slate-300 bg-white px-3 py-2 dark:border-white/10 dark:bg-slate-900"><option value="">Semua</option>@foreach ($schoolOptions as $s)<option value="{{ $s }}" @selected($filters['school'] === $s)>{{ $s }}</option>@endforeach</select></label>
                <label class="font-semibold">Tingkat<br><select name="grade" class="rounded-xl border border-slate-300 bg-white px-3 py-2 dark:border-white/10 dark:bg-slate-900"><option value="">Semua</option>@foreach (['X', 'XI', 'XII'] as $g)<option value="{{ $g }}" @selected($filters['grade'] === $g)>{{ $g }}</option>@endforeach</select></label>
                <button class="rounded-xl bg-slate-900 px-4 py-2 font-bold text-white dark:bg-white dark:text-slate-900">Perbarui</button>
                <button type="button" onclick="window.print()" class="rounded-xl bg-purple-600 px-4 py-2 font-bold text-white">Cetak / Simpan sebagai PDF</button>
            </form>
            <p class="mt-2 text-xs text-slate-500">Di dialog cetak pilih "Simpan sebagai PDF". Laporan memuat angka agregat saja (tanpa nama siswa).</p>
        </div>

        <div class="rpt rounded-[28px] border border-slate-200 bg-white p-6 text-sm shadow-sm dark:border-white/10 dark:bg-white/[0.03]">
            <h1 style="font-size:22px;font-weight:900;margin:0">Laporan Analitik Pembelajaran SpeakVerse</h1>
            <p style="margin:4px 0 0;color:#64748b;font-size:12px">Cakupan: {{ $filters['school'] ?: 'semua sekolah' }}{{ $filters['grade'] ? ' · kelas ' . $filters['grade'] : '' }}@if ($scopeLabel) · filter kohort: {{ $scopeLabel }}@endif · dibuat {{ $generated->format('d/m/Y H:i') }}</p>
            @if ($excluded)<p style="margin:2px 0 0;color:#64748b;font-size:12px">Dikecualikan dari analisis: {{ implode(', ', array_map(fn ($k, $n) => "$n $k", array_keys($excluded), $excluded)) }}.</p>@endif

            <h2>1. Keterlibatan</h2>
            @if ($o && $o['n'] > 0)
                <table><tr><th>Siswa</th><th>Aktif 7 hari</th><th>Rerata hari aktif belajar / 28 hari</th><th>Median menit aktif</th><th>Median indeks keteraturan</th></tr>
                    <tr><td>{{ $o['n'] }}</td><td>{{ $num($o['active7_pct'], 0) }}%</td><td>{{ $num($o['active_days_mean']) }}</td><td>{{ $num($o['minutes_median'], 0) }}</td><td>{{ $num($o['ri_median']) }}</td></tr></table>
            @else
                <p>Belum ada data keterlibatan pada cakupan ini.</p>
            @endif

            <h2>2. Hasil belajar (pretest → posttest)</h2>
            @if (($a['pairs'] ?? 0) === 0)
                <p>Belum ada siswa dengan pretest dan posttest.</p>
            @else
                @if ($eq && ! $eq['comparable'])<div class="warn">{{ $eq['message'] }} Interpretasi gain di bawah harus berhati-hati.</div>@endif
                <table><tr><th>Pasangan (n)</th><th>Rerata pre</th><th>Rerata post</th><th>Uji</th><th>Hedges' g</th><th>Hake &lt;g&gt;</th></tr>
                    <tr><td>{{ $a['pairs'] }}</td><td>{{ $num($a['mean_pre']) }}</td><td>{{ $num($a['mean_post']) }}</td>
                        <td>{{ $t ? ($t['chosen'] === 't' ? 't berpasangan' : 'Wilcoxon') . ', p ' . ($t['p'] < 0.001 ? '<0,001' : $num($t['p'], 3)) : '–' }}</td>
                        <td>{{ $a['effect'] ? $num($a['effect']['g'], 2) . ' (' . $a['effect']['label'] . ')' : '–' }}</td>
                        <td>{{ $a['hake'] ? $num($a['hake']['g'], 2) . ' (' . $a['hake']['category'] . ')' : '–' }}</td></tr></table>
                @if (! empty($a['relative']['rows']))
                    <p style="margin-top:6px;font-size:12px"><b>Perubahan posisi relatif per sekolah (Δz):</b> @foreach ($a['relative']['rows'] as $r){{ $r['school'] }} {{ $num($r['dz'], 2) }} (n={{ $r['n'] }})@if (! $loop->last); @endif @endforeach</p>
                @endif
                @if ($ml['ready'] ?? false)
                    <p style="margin-top:6px;font-size:12px"><b>Model multilevel</b> ({{ $ml['n'] }} siswa, {{ $ml['classes'] }} kelas): pretest {{ $num($ml['m1']['fixed'][1]['b'], 2) }} per poin (p {{ $ml['m1']['fixed'][1]['p'] < 0.001 ? '<0,001' : $num($ml['m1']['fixed'][1]['p'], 3) }}); ICC kelas {{ $num($ml['m1']['icc'], 2) }}.
                        @if ($ml['m2']) Perlakuan − kontrol {{ $num($ml['m2']['fixed'][2]['b'], 2) }} (CI 95% {{ $num($ml['m2']['fixed'][2]['ci'][0], 2) }} s.d. {{ $num($ml['m2']['fixed'][2]['ci'][1], 2) }}).@endif</p>
                @endif
            @endif

            <h2>3. Peringatan dini</h2>
            @if ($risk)
                <table><tr><th>Perlu dicek segera</th><th>Perlu dipantau</th><th>Aman</th><th>Belum mulai belajar</th></tr>
                    <tr><td>{{ $risk['tinggi'] ?? 0 }}</td><td>{{ $risk['sedang'] ?? 0 }}</td><td>{{ $risk['rendah'] ?? 0 }}</td><td>{{ $risk['belum_mulai'] ?? 0 }}</td></tr></table>
                <p style="font-size:11px;color:#64748b">Penandaan relatif dengan ambang dikalibrasi dari sebaran skor; bukan prediksi mutlak.</p>
            @else
                <p>Belum ada skor risiko.</p>
            @endif

            <h2>4. Persetujuan penelitian</h2>
            @if ($consent)
                <p>Setuju {{ (int) $consent->yes }} · Menolak {{ (int) $consent->no }}.</p>
            @else
                <p>Data persetujuan belum tersedia.</p>
            @endif

            <h2>Catatan</h2>
            <p style="font-size:11px;color:#475569">Seluruh angka bersifat deskriptif/asosiatif dan bukan bukti kausal kecuali penugasan kelompok diacak. Rincian metode: tab Metodologi.</p>
        </div>
    </div>
@endsection
