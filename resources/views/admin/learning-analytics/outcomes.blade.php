@extends('layouts.admin')

@section('content')
    @php
        $card = 'rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-white/[0.03]';
        $num = fn ($v, $d = 1) => $v === null ? '–' : (str_contains($__s = number_format((float) $v, $d), '.') ? rtrim(rtrim($__s, '0'), '.') : $__s);
        $pv = fn ($p) => $p === null ? '–' : ($p < 0.001 ? '< 0,001' : str_replace('.', ',', number_format($p, 3)));
        $sig = fn ($p) => $p !== null && $p < 0.05;
        $t = $a['test'];
        $maxH = max(1, max($a['hist_pre']), max($a['hist_post']));
    @endphp

    <div class="space-y-8">
        @include('admin.learning-analytics._tabs')
        <div>
            <h1 class="text-3xl font-black">Analitik Lanjutan · Hasil Belajar</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Inferensi pretest → posttest, gain ternormalisasi, ukuran efek, sebaran antar sekolah.</p>
        </div>

        <form method="GET" action="{{ route('admin.la.outcomes') }}" class="{{ $card }} flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-xs font-bold uppercase text-slate-500">Sekolah</label>
                <select name="school" class="mt-1 rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-slate-900">
                    <option value="">Semua sekolah</option>
                    @foreach ($schoolOptions as $s)
                        <option value="{{ $s }}" @if ($filters['school'] === $s) selected @endif>{{ $s }}</option>
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
        </form>

        @if ($a['pairs'] < 2)
            <div class="{{ $card }} text-center text-slate-500">Belum cukup siswa dengan pretest dan posttest (n = {{ $a['pairs'] }}).</div>
        @else
            @if (! empty($cohortNote))<p class="text-xs text-slate-500">{{ $cohortNote }}</p>@endif
            @if ($eq && ! $eq['comparable'])
                <div class="rounded-2xl border border-orange-300 bg-orange-50 p-4 text-sm text-orange-900" role="alert">
                    <b>Peringatan: bentuk tes tidak sebanding.</b> {{ $eq['message'] }}
                    @if (! $eq['has_anchors']) <span>Tidak ada butir jangkar (butir yang sama di pre dan post): tambahkan 5–8 butir jangkar pada bentuk berikutnya agar skor dapat diekuatkan.</span>@endif
                </div>
            @endif
            @if ($coverage)
                <p class="text-xs text-slate-500">Cakupan: {{ $a['pairs'] }} siswa berpasangan dari {{ $coverage['students'] }} siswa terdaftar ({{ $coverage['pct'] }}%)@if ($coverage['pct'] < 50). Siswa yang mengerjakan posttest mungkin bukan sampel acak: waspadai bias seleksi.@endif</p>
            @endif
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="{{ $card }}">
                    <p class="text-sm font-semibold text-slate-500">Pretest → Posttest</p>
                    <p class="mt-2 text-3xl font-black">{{ $num($a['mean_pre']) }} → {{ $num($a['mean_post']) }}</p>
                    <p class="mt-2 text-xs text-slate-500">n = {{ $a['pairs'] }} siswa berpasangan</p>
                </div>
                <div class="{{ $card }}">
                    <p class="text-sm font-semibold text-slate-500">Uji {{ $t['chosen'] === 't' ? 't berpasangan' : 'Wilcoxon' }}</p>
                    <p class="mt-2 text-3xl font-black {{ $sig($t['p']) ? 'text-green-600' : '' }}">p {{ $pv($t['p']) }}</p>
                    <p class="mt-2 text-xs text-slate-500">
                        @if ($t['chosen'] === 't' && $t['t'] && $t['t']['t'] !== null)
                            t({{ $t['t']['df'] }}) = {{ $num($t['t']['t'], 2) }} · selisih {{ $num($t['t']['mean']) }} (CI 95% {{ $num($t['t']['ci'][0]) }} s/d {{ $num($t['t']['ci'][1]) }})
                        @elseif ($t['wilcoxon'])
                            W+ = {{ $num($t['wilcoxon']['w_plus'], 1) }}, n efektif {{ $t['wilcoxon']['n'] }} ({{ $t['wilcoxon']['method'] }})
                        @endif
                    </p>
                </div>
                <div class="{{ $card }}">
                    <p class="text-sm font-semibold text-slate-500">Ukuran efek (Hedges' g)</p>
                    <p class="mt-2 text-3xl font-black">{{ $a['effect'] ? $num($a['effect']['g'], 2) : '–' }}</p>
                    <p class="mt-2 text-xs text-slate-500">
                        @if ($a['effect']) {{ $a['effect']['label'] }} · CI 95% {{ $num($a['effect']['ci'][0], 2) }} s/d {{ $num($a['effect']['ci'][1], 2) }} @else selisih tidak bervariasi @endif
                    </p>
                    <p class="mt-1 text-xs text-slate-500">Efek minimal yang terdeteksi (daya 80%): d ≈ {{ $num($a['mde_d'], 2) }} pada n = {{ $a['pairs'] }}.</p>
                </div>
                <div class="{{ $card }}">
                    <p class="text-sm font-semibold text-slate-500">Gain ternormalisasi Hake &lt;g&gt;</p>
                    <p class="mt-2 text-3xl font-black">{{ $a['hake'] ? $num($a['hake']['g'], 2) : '–' }}</p>
                    <p class="mt-2 text-xs text-slate-500">{{ $a['hake'] ? 'kategori ' . $a['hake']['category'] : '' }} · rerata g per siswa {{ $num($a['hake_student_mean'], 2) }} (n={{ $a['hake_student_n'] }})</p>
                </div>
            </div>
            <p class="text-xs text-slate-500">Pemilihan uji: {{ $t['reason'] }}.</p>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="{{ $card }}">
                    <h2 class="text-lg font-black">Distribusi skor</h2>
                    <div class="mt-4 flex h-44 items-end gap-2">
                        @foreach ($a['hist_pre'] as $i => $c)
                            <div class="flex flex-1 flex-col items-center justify-end gap-1">
                                <div class="flex h-36 w-full items-end gap-[2px]">
                                    <div class="w-1/2 rounded-t bg-slate-400/70" style="height: {{ max(1, round($c / $maxH * 130)) }}px" title="Pretest {{ $i * 10 }}–{{ $i * 10 + 9 }}: {{ $c }}"></div>
                                    <div class="w-1/2 rounded-t bg-purple-500/80" style="height: {{ max(1, round($a['hist_post'][$i] / $maxH * 130)) }}px" title="Posttest {{ $i * 10 }}–{{ $i * 10 + 9 }}: {{ $a['hist_post'][$i] }}"></div>
                                </div>
                                <span class="text-[10px] text-slate-500">{{ $i * 10 }}</span>
                            </div>
                        @endforeach
                    </div>
                    <p class="mt-2 text-xs text-slate-500"><span class="inline-block h-2 w-2 bg-slate-400"></span> Pretest · <span class="inline-block h-2 w-2 bg-purple-500"></span> Posttest (jumlah siswa per rentang 10 poin)</p>
                </div>

                <div class="{{ $card }}">
                    <h2 class="text-lg font-black">Sebaran posttest per sekolah (boxplot)</h2>
                    @php $rowH = 34; $h = max(60, count($a['box']) * $rowH + 24); @endphp
                    <svg viewBox="0 0 420 {{ $h }}" class="mt-4 w-full" role="img" aria-label="Boxplot skor posttest per sekolah">
                        @foreach ([0, 25, 50, 75, 100] as $tick)
                            <line x1="{{ 110 + $tick * 2.9 }}" y1="0" x2="{{ 110 + $tick * 2.9 }}" y2="{{ $h - 16 }}" stroke="currentColor" stroke-opacity="0.12" />
                            <text x="{{ 110 + $tick * 2.9 }}" y="{{ $h - 3 }}" font-size="9" text-anchor="middle" fill="currentColor" fill-opacity="0.6">{{ $tick }}</text>
                        @endforeach
                        @foreach ($a['box'] as $i => $b)
                            @php $y = 12 + $i * $rowH; @endphp
                            <text x="104" y="{{ $y + 4 }}" font-size="9" text-anchor="end" fill="currentColor">{{ \Illuminate\Support\Str::limit($b['school'], 16) }} (n={{ $b['n'] }})</text>
                            <line x1="{{ 110 + $b['min'] * 2.9 }}" y1="{{ $y }}" x2="{{ 110 + $b['max'] * 2.9 }}" y2="{{ $y }}" stroke="#8b5cf6" stroke-width="1.5" />
                            <rect x="{{ 110 + $b['q1'] * 2.9 }}" y="{{ $y - 8 }}" width="{{ max(1, ($b['q3'] - $b['q1']) * 2.9) }}" height="16" fill="#8b5cf6" fill-opacity="0.35" stroke="#8b5cf6" />
                            <line x1="{{ 110 + $b['median'] * 2.9 }}" y1="{{ $y - 8 }}" x2="{{ 110 + $b['median'] * 2.9 }}" y2="{{ $y + 8 }}" stroke="#8b5cf6" stroke-width="2.5" />
                        @endforeach
                    </svg>
                    <p class="mt-2 text-xs text-slate-500">Garis tebal = median, kotak = kuartil 1–3, garis = min–maks.</p>
                </div>
            </div>

            <div class="{{ $card }}">
                <h2 class="text-lg font-black">Perubahan per sekolah</h2>
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full min-w-[760px] text-left text-sm">
                        <thead class="text-xs uppercase text-slate-500">
                            <tr><th class="py-2">Sekolah</th><th>Pasangan (n)</th><th>Pre</th><th>Post</th><th>Selisih</th><th>Hake &lt;g&gt;</th><th>Hedges g</th><th>p (Wilcoxon)</th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                            @foreach ($a['schools'] as $s)
                                <tr>
                                    <td class="py-2 font-bold">{{ $s['school'] }}</td>
                                    <td>{{ $s['pairs'] }}@if ($s['indicative']) <span class="text-xs text-yellow-600">(n kecil)</span>@endif</td>
                                    <td>{{ $num($s['pre']) }}</td>
                                    <td>{{ $num($s['post']) }}</td>
                                    <td>{{ ($s['diff'] > 0 ? '+' : '') . $num($s['diff']) }}</td>
                                    <td>{{ $num($s['hake'], 2) }} {{ $s['hake_cat'] ? '(' . $s['hake_cat'] . ')' : '' }}</td>
                                    <td>{{ $num($s['g'], 2) }} {{ $s['g_label'] ? '(' . $s['g_label'] . ')' : '' }}</td>
                                    <td class="{{ $sig($s['p']) ? 'font-bold text-green-600' : '' }}">{{ $pv($s['p']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($eq && count($eq['rows']))
                <div class="{{ $card }}">
                    <h2 class="text-lg font-black">Kesetaraan bentuk tes (kesukaran butir)</h2>
                    <table class="mt-3 w-full text-left text-sm"><thead class="text-xs uppercase text-slate-500"><tr><th class="py-1">Keterampilan</th><th>Butir pre / post</th><th>Rerata p pre</th><th>Rerata p post</th><th>Selisih</th><th>Jangkar</th><th>Selisih pada jangkar</th><th>Sebanding?</th></tr></thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                        @foreach ($eq['rows'] as $r)
                            <tr><td class="py-1 font-bold">{{ $r['skill'] }}</td><td>{{ $r['items_pre'] }} / {{ $r['items_post'] }}</td><td>{{ $num($r['p_pre'], 2) }}</td><td>{{ $num($r['p_post'], 2) }}</td><td>{{ $num($r['delta'], 2) }}</td><td>{{ $r['anchors'] }}</td><td>{{ $num($r['anchor_delta'], 2) }}</td><td class="{{ $r['comparable'] ? 'text-green-600' : 'text-red-600' }} font-bold">{{ $r['comparable'] ? 'ya' : 'tidak' }}</td></tr>
                        @endforeach
                        </tbody></table>
                    <p class="mt-2 text-xs text-slate-500">Ambang: |Δp| ≤ {{ $eq['delta_limit'] }}. Butir dengan n &lt; 15 diabaikan. Korelasi pre–post (Spearman): {{ $num($a['pre_post_rho'], 2) }}.</p>
                </div>
            @endif
            @if (count($a['relative']['rows']))
                <div class="{{ $card }}">
                    <h2 class="text-lg font-black">Perubahan posisi relatif per sekolah (alternatif yang sah bila bentuk tes tak setara)</h2>
                    <table class="mt-3 w-full text-left text-sm"><thead class="text-xs uppercase text-slate-500"><tr><th class="py-1">Sekolah</th><th>n</th><th>Δz rerata</th><th>Galat baku</th></tr></thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-white/5">@foreach ($a['relative']['rows'] as $r)<tr><td class="py-1">{{ $r['school'] }}</td><td>{{ $r['n'] }}</td><td>{{ $num($r['dz'], 2) }}</td><td>{{ $num($r['se'], 2) }}</td></tr>@endforeach</tbody></table>
                    <p class="mt-2 text-xs text-slate-500">{{ $a['relative']['note'] }}</p>
                </div>
            @endif

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="{{ $card }}">
                    <h2 class="text-lg font-black">Variasi bersarang (ICC)</h2>
                    <table class="mt-3 w-full text-left text-sm">
                        <thead class="text-xs uppercase text-slate-500"><tr><th class="py-1">Ukuran</th><th>ICC(1)</th><th>Kategori</th><th>Design effect</th></tr></thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                            @foreach (['post_school' => 'Posttest · antar sekolah', 'post_class' => 'Posttest · antar kelas', 'gain_school' => 'Selisih · antar sekolah', 'gain_class' => 'Selisih · antar kelas'] as $k => $label)
                                <tr>
                                    <td class="py-1">{{ $label }}</td>
                                    @if ($a['icc'][$k])
                                        <td>{{ $num($a['icc'][$k]['icc'], 2) }}</td><td>{{ $a['icc'][$k]['label'] }}</td><td>{{ $num($a['icc'][$k]['design_effect'], 2) }}</td>
                                    @else
                                        <td colspan="3" class="text-slate-500">tidak dapat diestimasi</td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <p class="mt-3 text-xs text-slate-500">{{ $a['icc']['min_note'] ?? '' }}</p>
                    <p class="mt-3 text-xs text-slate-500">ICC = porsi variasi skor yang berasal dari perbedaan kelompok. ICC besar berarti siswa dalam satu sekolah/kelas mirip; analisis inferensial riset sebaiknya memakai model multilevel (ekspor per siswa tersedia di menu Hasil Sekolah).</p>
                </div>

                <div class="{{ $card }}">
                    <h2 class="text-lg font-black">Kesenjangan capaian posttest</h2>
                    <div class="mt-3 space-y-3 text-sm">
                        @foreach (['school' => 'Antar sekolah', 'major' => 'Antar jurusan'] as $k => $label)
                            <div>
                                <p class="font-bold">{{ $label }}</p>
                                @if ($a['equity'][$k])
                                    <p class="{{ $a['equity'][$k]['significant'] ? 'text-orange-600' : 'text-slate-600 dark:text-slate-300' }}">
                                        Kruskal–Wallis H({{ $a['equity'][$k]['df'] }}) = {{ $num($a['equity'][$k]['h'], 2) }}, p {{ $pv($a['equity'][$k]['p']) }}, ε² = {{ $num($a['equity'][$k]['epsilon2'], 2) }} ({{ $a['equity'][$k]['size'] }}).
                                        {{ $a['equity'][$k]['significant'] ? 'Ada perbedaan bermakna antar kelompok.' : 'Tidak ada perbedaan bermakna yang terdeteksi.' }}
                                    </p>
                                @else
                                    <p class="text-slate-500">Butuh ≥2 kelompok dengan ≥3 siswa.</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <p class="mt-3 text-xs text-slate-500">Jenis kelamin belum tercatat sehingga tidak dianalisis. Tanpa koreksi perbandingan ganda; p bersifat indikatif.</p>
                </div>
            </div>

            <div class="{{ $card }}">
                <h2 class="text-lg font-black">Model multilevel (siswa dalam kelas)</h2>
                @if (! ($ml['ready'] ?? false))
                    <p class="mt-2 text-sm text-slate-500">{{ $ml['reason'] ?? 'Tidak tersedia.' }}</p>
                @else
                    <p class="mt-1 text-xs text-slate-500">{{ $ml['n'] }} siswa dalam {{ $ml['classes'] }} kelas. Variabel hasil: gain (post − pre); pretest dipusatkan pada rerata {{ $num($ml['pre_mean'], 1) }}.</p>
                    @foreach (['m1' => 'Model 1: pretest', 'm2' => 'Model 2: pretest + kelompok penelitian'] as $mk => $mt)
                        @if ($ml[$mk])
                            <h3 class="mt-4 text-sm font-black">{{ $mt }}</h3>
                            <table class="mt-1 w-full text-left text-sm"><thead class="text-xs uppercase text-slate-500"><tr><th class="py-1">Efek tetap</th><th>b</th><th>SE</th><th>CI 95%</th><th>p</th></tr></thead><tbody>
                                @foreach ($ml[$mk]['fixed'] as $f)<tr class="border-t border-slate-100"><td class="py-1">{{ $f['label'] }}</td><td>{{ $num($f['b'], 2) }}</td><td>{{ $num($f['se'], 2) }}</td><td>{{ $num($f['ci'][0], 2) }} s.d. {{ $num($f['ci'][1], 2) }}</td><td>{{ $f['p'] < 0.001 ? '<0,001' : $num($f['p'], 3) }}</td></tr>@endforeach
                            </tbody></table>
                            <p class="mt-1 text-xs text-slate-500">Varians antar-kelas {{ $num($ml[$mk]['sigma2_u'], 2) }} · varians sisa {{ $num($ml[$mk]['sigma2_e'], 2) }} · ICC {{ $num($ml[$mk]['icc'], 2) }} · uji varians kelas (LRT) {{ $num($ml[$mk]['lrt'], 2) }}, p {{ $ml[$mk]['lrt_p'] < 0.001 ? '<0,001' : $num($ml[$mk]['lrt_p'], 3) }}</p>
                        @endif
                    @endforeach
                    @foreach ($ml['notes'] as $n)<p class="mt-2 text-xs text-slate-500">{{ $n }}</p>@endforeach
                @endif
            </div>

            <details class="{{ $card }}">
                <summary class="cursor-pointer text-lg font-black">Definisi, rumus, dan keterbatasan</summary>
                <div class="mt-4 space-y-2 text-sm text-slate-600 dark:text-slate-300">
                    <p><b>Berpasangan</b>: hanya siswa dengan pretest dan posttest. <b>Uji</b>: t berpasangan bila n ≥ 20 dan selisih tidak miring (|skew| &lt; 1, |ekses kurtosis| &lt; 2); selainnya Wilcoxon signed-rank (eksak bila n ≤ 25, selainnya aproksimasi normal dengan koreksi ikatan dan kontinuitas).</p>
                    <p><b>Hedges' g</b> = J × d_z, d_z = rerata selisih / SD selisih, J = 1 − 3/(4·df − 1). Ambang: &lt;0,2 sangat kecil, &lt;0,5 kecil, &lt;0,8 sedang, selainnya besar (Cohen, 1988).</p>
                    <p><b>Hake &lt;g&gt;</b> = (post̄ − prē)/(100 − prē); ≥0,7 tinggi, 0,3–0,7 sedang, &lt;0,3 rendah (Hake, 1998).</p>
                    <p><b>ICC(1)</b> = (MSB − MSW)/(MSB + (k₀ − 1)·MSW), k₀ disesuaikan untuk kelompok tak seimbang (Raudenbush &amp; Bryk, 2002). <b>Design effect</b> = 1 + (rerata ukuran kelompok − 1)·ICC.</p>
                    <p>Keterbatasan: desain tanpa kelompok kontrol, jadi kenaikan skor tidak dapat dipastikan disebabkan oleh SpeakVerse; siswa tidak diacak; pretest dan posttest memakai instrumen yang sama sehingga efek latihan mungkin ikut menaikkan skor.</p>
                </div>
            </details>
        @endif
    </div>
@endsection
