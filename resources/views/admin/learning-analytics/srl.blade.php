@extends('layouts.admin')

@section('content')
    @php
        $card = 'rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-white/[0.03]';
        $num = fn ($v, $d = 2) => $v === null ? '–' : (str_contains($__s = number_format((float) $v, $d), '.') ? rtrim(rtrim($__s, '0'), '.') : $__s);
        $pc = fn ($v) => $v === null ? '–' : round($v * 100) . '%';
    @endphp

    <div class="space-y-8">
        @include('admin.learning-analytics._tabs')
        <div>
            <h1 class="text-3xl font-black">Analitik Lanjutan · Strategi Belajar &amp; Regulasi Diri</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Pola penggunaan hint, ketergesaan menjawab, usaha menghadapi kesulitan, dan profil perilaku.</p>
        </div>

        @if (! $ready)
            <div class="{{ $card }}">
                <p class="font-bold">Fitur perilaku belum dihitung.</p>
                <p class="mt-2 text-sm text-slate-500">Jalankan <code>php artisan migrate</code> lalu <code>php artisan la:fit-models</code>.</p>
            </div>
        @else
            @php $n = (int) ($agg->n ?? 0); $st = ($agg->sp ?? 0) + ($agg->sa ?? 0) + ($agg->su ?? 0) + ($agg->sn ?? 0); @endphp
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="{{ $card }}"><p class="text-sm font-semibold text-slate-500">Siswa dianalisis</p><p class="mt-2 text-4xl font-black">{{ $n }}</p><p class="mt-2 text-xs text-slate-500">≥10 butir terjawab</p></div>
                <div class="{{ $card }}"><p class="text-sm font-semibold text-slate-500">Akurasi percobaan pertama</p><p class="mt-2 text-4xl font-black">{{ $pc($agg->acc ?? null) }}</p><p class="mt-2 text-xs text-slate-500">rerata antar siswa</p></div>
                <div class="{{ $card }}"><p class="text-sm font-semibold text-slate-500">Memakai hint</p><p class="mt-2 text-4xl font-black">{{ $pc($agg->hint ?? null) }}</p><p class="mt-2 text-xs text-slate-500">level 3: {{ $pc($agg->hint3 ?? null) }} dari butir</p></div>
                <div class="{{ $card }}"><p class="text-sm font-semibold text-slate-500">Pola menjawab tergesa</p><p class="mt-2 text-4xl font-black {{ ($agg->gaming ?? 0) > 0 ? 'text-orange-500' : '' }}">{{ (int) ($agg->gaming ?? 0) }}</p><p class="mt-2 text-xs text-slate-500">siswa bertanda (perlu dicek)</p></div>
            </div>

            <div class="{{ $card }}">
                <h2 class="text-lg font-black">Menghadapi kesulitan (butir yang salah pada percobaan pertama)</h2>
                @if ($st > 0)
                    @php
                        $parts = [['Produktif', $agg->sp, 'bg-green-500'], ['Dibantu banyak hint', $agg->sa, 'bg-yellow-500'], ['Tidak produktif', $agg->su, 'bg-red-500'], ['Tanpa usaha lanjutan', $agg->sn, 'bg-slate-400']];
                    @endphp
                    <div class="mt-4 flex h-5 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-white/10" role="img" aria-label="Proporsi jenis kesulitan">
                        @foreach ($parts as [$label, $v, $color])<div class="{{ $color }}" style="width: {{ $v * 100 / $st }}%" title="{{ $label }}: {{ $v }}"></div>@endforeach
                    </div>
                    <div class="mt-3 grid gap-2 text-sm sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ($parts as [$label, $v, $color])<div class="flex items-center gap-2"><span class="inline-block h-3 w-3 rounded-full {{ $color }}"></span><b>{{ $label }}</b><span class="text-slate-500">{{ $v }} ({{ round($v * 100 / $st) }}%)</span></div>@endforeach
                    </div>
                    <p class="mt-3 text-xs text-slate-500">Produktif = akhirnya benar dengan ≤2 permintaan hint. Dibantu = benar tetapi >2 hint. Tidak produktif = tetap salah setelah ≥2 percobaan atau ≥2 hint. Tanpa usaha lanjutan = salah, 1 percobaan, &lt;2 hint.</p>
                @else
                    <p class="mt-3 text-sm text-slate-500">Belum ada data.</p>
                @endif
            </div>

            <div class="{{ $card }}">
                <h2 class="text-lg font-black">Profil perilaku (klaster){{ $k ? ' · k = ' . $k . ', siluet ' . $num($silhouette, 2) : '' }}</h2>
                @if (count($clusters))
                    <div class="mt-4 overflow-x-auto">
                        <table class="w-full min-w-[720px] text-left text-sm">
                            <thead class="text-xs uppercase text-slate-500"><tr><th class="py-2">Profil</th><th>Siswa</th>@foreach ($clusters[0]['means'] as $f => $_)<th>{{ $featureLabels[$f] ?? $f }}</th>@endforeach</tr></thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                                @foreach ($clusters as $c)
                                    <tr>
                                        <td class="py-2 font-bold">{{ $c['label'] }}</td><td>{{ $c['n'] }}</td>
                                        @foreach ($c['means'] as $f => $v)<td>{{ $f === 'log_ms' ? ($v === null ? '–' : round(exp($v) / 1000, 1) . ' dtk') : $num($v) }}</td>@endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p class="mt-3 text-xs text-slate-500">Siluet: &lt;0,25 klaster lemah, 0,25–0,5 wajar, &gt;0,5 jelas. Profil meringkas kelompok pada periode ini; siswa dapat berpindah profil dan tidak boleh dijadikan label tetap. Dihitung: {{ $computed }}.</p>
                @else
                    <p class="mt-3 text-sm text-slate-500">Butuh ≥20 siswa dengan ≥10 butir untuk membentuk profil.</p>
                @endif
            </div>

            @if (count($correlations))
                <div class="{{ $card }}">
                    <h2 class="text-lg font-black">Hubungan perilaku dengan kenaikan skor (Spearman)</h2>
                    <table class="mt-3 w-full text-left text-sm">
                        <thead class="text-xs uppercase text-slate-500"><tr><th class="py-1">Fitur</th><th>ρ</th><th>p</th><th>p Holm</th><th>q BH</th><th>n</th></tr></thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                            @foreach ($correlations as $f => $c)
                                <tr><td class="py-1">{{ $featureLabels[$f] ?? $f }}</td><td>{{ $num($c['rho']) }}</td><td class="{{ $c['p'] !== null && $c['p'] < 0.05 ? 'font-bold text-green-600' : '' }}">{{ $c['p'] === null ? '–' : ($c['p'] < 0.001 ? '< 0,001' : $num($c['p'], 3)) }}</td><td class="{{ ($c['p_holm'] ?? null) !== null && $c['p_holm'] < 0.05 ? 'font-bold text-green-600' : '' }}">{{ ($c['p_holm'] ?? null) === null ? '–' : ($c['p_holm'] < 0.001 ? '< 0,001' : $num($c['p_holm'], 3)) }}</td><td>{{ ($c['p_bh'] ?? null) === null ? '–' : ($c['p_bh'] < 0.001 ? '< 0,001' : $num($c['p_bh'], 3)) }}</td><td>{{ $c['n'] }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                    <p class="mt-3 text-xs text-slate-500">Korelasi tidak berarti sebab-akibat: siswa yang sudah kuat memang cenderung lebih sedikit memakai hint. Hijau tebal = signifikan (p &lt; 0,05); p Holm mengendalikan galat keluarga, q BH mengendalikan laju temuan palsu. Seluruh analisis ini eksploratori. Bila bentuk pre/post tidak setara, gunakan korelasi hanya sebagai urutan relatif siswa.</p>
                </div>
            @endif

            <div class="{{ $card }}">
                <h2 class="text-lg font-black">Per sekolah</h2>
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full min-w-[520px] text-left text-sm">
                        <thead class="text-xs uppercase text-slate-500"><tr><th class="py-2">Sekolah</th><th>Siswa</th><th>Akurasi pertama</th><th>Pakai hint</th><th>Bertanda tergesa</th></tr></thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                            @foreach ($bySchool as $s)
                                <tr><td class="py-2 font-bold">{{ $s->school }}</td><td>{{ $s->n }}</td><td>{{ $pc($s->acc) }}</td><td>{{ $pc($s->hint) }}</td><td>{{ (int) $s->gaming }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="{{ $card }}">
                <h2 class="text-lg font-black">Perlu dicek guru: pola menjawab tergesa / hint berantai</h2>
                @if ($gaming->isEmpty())
                    <p class="mt-3 text-sm text-slate-500">Tidak ada siswa bertanda.</p>
                @else
                    <div class="mt-4 overflow-x-auto">
                        <table class="w-full min-w-[640px] text-left text-sm">
                            <thead class="text-xs uppercase text-slate-500"><tr><th class="py-2">Nama</th><th>Sekolah</th><th>Kelas</th><th>Butir</th><th>Salah cepat</th><th>Hint berantai</th><th>Pakai hint</th></tr></thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                                @foreach ($gaming as $g)
                                    <tr><td class="py-2 font-bold">{{ $g->name }}</td><td>{{ $g->school }}</td><td>{{ $g->grade }} {{ $g->major }} {{ $g->parallel }}</td><td>{{ $g->items }}</td><td>{{ $pc($g->fast_wrong_rate) }}</td><td>{{ $pc($g->hint_chain_rate) }}</td><td>{{ $pc($g->hint_rate) }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
                <p class="mt-3 text-xs text-slate-500">Penanda kasar: ≥30% jawaban pertama salah dan dijawab &lt;3 detik, atau ≥50% hint diminta ≤2 detik setelah hint sebelumnya (≥5 hint). Siswa yang cepat memang bisa benar-benar paham; gunakan sebagai petunjuk untuk bertanya, bukan sanksi.</p>
            </div>

            <details class="{{ $card }}">
                <summary class="cursor-pointer text-lg font-black">Definisi dan keterbatasan</summary>
                <div class="mt-4 space-y-2 text-sm text-slate-600 dark:text-slate-300">
                    <p>Fitur dihitung dari log kuis Reading, Listening, Vocabulary. Waktu respons memakai jam server. Klaster: k-means pada fitur ter-standardisasi (akurasi pertama, hint, revisi, ln waktu respons, salah cepat, indeks keteraturan); k dipilih 2–5 dengan siluet tertinggi.</p>
                    <p>Rujukan: Aleven dkk. (2006) <i>Review of Educational Research</i>; Baker dkk. (2004) <i>ITS</i>; Zimmerman (2002) <i>Theory Into Practice</i>; Rousseeuw (1987) <i>J. Comput. Appl. Math</i>.</p>
                </div>
            </details>
        @endif
    </div>
@endsection
