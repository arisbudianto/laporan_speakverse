@extends('layouts.admin')

@section('content')
    @php
        $card = 'rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-white/[0.03]';
        $n = fn ($v, $d = 2) => $v === null ? '–' : (str_contains($__s = number_format((float) $v, $d), '.') ? rtrim(rtrim($__s, '0'), '.') : $__s);
        $pv = fn ($p) => $p === null ? '–' : ($p < 0.001 ? '< 0,001' : number_format($p, 3, ',', ''));
    @endphp

    <div class="space-y-8">
        @include('admin.learning-analytics._tabs')
        <div>
            <h1 class="text-3xl font-black">Analitik Lanjutan · Intervensi &amp; Efektivitas Konten</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Apakah tindak lanjut guru diikuti perubahan aktivitas, dan pelajaran mana yang tampak membantu. Ini asosiasi, bukan bukti sebab-akibat.</p>
        </div>

        @if (! $ready)
            <div class="{{ $card }}"><p class="font-bold">Tabel intervensi belum ada.</p><p class="mt-2 text-sm text-slate-500">Jalankan <code>php artisan migrate</code>.</p></div>
        @else
            @if (isset($due) && $due->isNotEmpty())
                <div class="{{ $card }}">
                    <h2 class="text-lg font-black">Waktunya cek ulang ({{ $due->count() }})</h2>
                    <table class="mt-3 w-full text-left text-sm"><thead class="text-xs uppercase text-slate-500"><tr><th class="py-1">Siswa</th><th>Tindakan</th><th>Kelompok</th><th>Level saat itu</th><th>Level sekarang</th></tr></thead><tbody>
                        @foreach ($due as $d)<tr><td class="py-1 font-bold">{{ $d->name }}<div class="text-xs font-normal text-slate-500">{{ $d->school }}</div></td><td>{{ $d->action }}</td><td>{{ $d->assignment ?? '–' }}</td><td>{{ $d->risk_level ?? '–' }}</td><td>{{ $d->level_now ?? '–' }}</td></tr>@endforeach
                    </tbody></table>
                </div>
            @endif
            <div class="{{ $card }}">
                <h2 class="text-lg font-black">Perubahan aktivitas setelah tindak lanjut</h2>
                <p class="mt-1 text-xs text-slate-500">Hari aktif dalam {{ $eval['window_days'] ?? 14 }} hari sebelum vs sesudah. Pembanding: siswa berisiko yang belum ditindaklanjuti (selisih-dalam-selisih, uji Welch).</p>
                <div class="mt-4 grid gap-3 sm:grid-cols-4 text-sm">
                    <div><b>Ditindaklanjuti</b><br>{{ $eval['followed'] }} siswa · Δ {{ $n($eval['mean_change_followed']) }} hari</div>
                    <div><b>Pembanding</b><br>{{ $eval['not_followed'] }} siswa · Δ {{ $n($eval['mean_change_control']) }} hari</div>
                    @if ($eval['ready'])
                        <div><b>Selisih-dalam-selisih</b><br>{{ $n($eval['did']['diff']) }} hari (CI95 {{ $n($eval['did']['ci'][0]) }} s.d. {{ $n($eval['did']['ci'][1]) }}), p = {{ $pv($eval['did']['p']) }}</div>
                        <div><b>Ukuran efek (Hedges g)</b><br>{{ $n($eval['g']) }}</div>
                    @endif
                </div>
                @unless ($eval['ready'])<p class="mt-4 text-sm text-slate-500">{{ $eval['reason'] }}</p>@endunless
            </div>

            <div class="{{ $card }}">
                <h2 class="text-lg font-black">Tindak lanjut terbaru</h2>
                <div class="mt-3 overflow-x-auto"><table class="w-full min-w-[640px] text-left text-sm">
                    <thead class="text-xs uppercase text-slate-500"><tr><th class="py-2">Siswa</th><th>Guru</th><th>Risiko</th><th>Tindakan</th><th>Catatan</th><th>Waktu</th></tr></thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                    @forelse ($recent as $r)<tr><td class="py-2 font-bold">{{ $r->name }}</td><td>{{ $r->teacher ?? '–' }}</td><td>{{ $r->risk_level ?? '–' }}</td><td>{{ str_replace('_', ' ', $r->action) }}</td><td>{{ $r->note }}</td><td>{{ \Illuminate\Support\Carbon::parse($r->followed_at)->format('d/m/Y H:i') }}</td></tr>
                    @empty<tr><td colspan="6" class="py-4 text-slate-500">Belum ada tindak lanjut tercatat.</td></tr>@endforelse
                    </tbody></table></div>
            </div>

            @php $shown = array_values(array_filter($content, fn ($c) => $showAll || $c['starts'] > 0 || $c['n_exposed'] > 0)); @endphp
            <div class="{{ $card }}">
                <h2 class="text-lg font-black">Efektivitas pelajaran (asosiasi dosis–gain)</h2>
                <p class="mt-1 text-xs text-slate-500">Model per pelajaran: gain = β₀ + β₁·dosis + β₂·pretest. Dosis = jumlah butir (percobaan pertama) yang dijawab; β₁ ditampilkan per +10 butir dengan CI95. Pretest dikendalikan. Hanya asosiasi, bukan bukti sebab-akibat. Syarat: ≥20 siswa berpasangan dan ≥5 yang mengerjakan.</p>
                <p class="mt-1 text-xs text-slate-500">{{ count($shown) }} dari {{ count($content) }} pelajaran punya aktivitas. <a class="font-bold underline" href="{{ route('admin.la.interventions', $showAll ? [] : ['all' => 1]) }}">{{ $showAll ? 'Sembunyikan yang tanpa aktivitas' : 'Tampilkan semua' }}</a></p>
                <div class="mt-3 overflow-x-auto"><table class="w-full min-w-[860px] text-left text-sm">
                    <thead class="text-xs uppercase text-slate-500"><tr><th class="py-2">Pelajaran</th><th>Mulai</th><th>Ditinggalkan</th><th>Akurasi pertama</th><th>Mengerjakan / berpasangan</th><th>Selesai</th><th>Δgain per +10 butir (CI95)</th><th>p</th><th>Penanda</th></tr></thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                    @forelse ($shown as $c)
                        <tr><td class="py-2 font-bold">{{ $c['name'] }}</td><td>{{ $c['starts'] }}</td><td>{{ $c['abandon_rate'] === null ? '–' : round($c['abandon_rate'] * 100) . '%' }}</td>
                            <td>{{ $c['first_try_acc'] === null ? '–' : round($c['first_try_acc'] * 100) . '%' }}</td><td>{{ $c['n_exposed'] }} / {{ $c['n_paired'] }}</td><td>{{ $c['n_completed'] }}</td>
                            <td>@if ($c['per10'] !== null){{ $n($c['per10'], 1) }} ({{ $n($c['per10_ci'][0], 1) }}; {{ $n($c['per10_ci'][1], 1) }})@else<span class="text-xs text-slate-500">tidak dihitung: {{ $c['why_not'] }}</span>@endif</td>
                            <td>{{ $pv($c['p']) }}</td><td>{{ implode(', ', $c['flags']) }}</td></tr>
                    @empty<tr><td colspan="9" class="py-4 text-slate-500">Belum ada data pelajaran.</td></tr>@endforelse
                    </tbody></table></div>
            </div>
        @endif
    </div>
@endsection
