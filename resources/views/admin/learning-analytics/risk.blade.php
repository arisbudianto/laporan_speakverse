@extends('layouts.admin')

@section('content')
    @php
        $card = 'rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-white/[0.03]';
        $input = 'rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-slate-900';
        $badge = ['tinggi' => 'bg-red-100 text-red-700', 'sedang' => 'bg-yellow-100 text-yellow-800', 'rendah' => 'bg-green-100 text-green-700', 'belum_mulai' => 'bg-slate-200 text-slate-600'];
        $pc = fn ($v) => $v === null ? '–' : round($v * 100) . '%';
    @endphp

    <div class="space-y-8">
        @include('admin.learning-analytics._tabs')
        <div>
            <h1 class="text-3xl font-black">Analitik Lanjutan · Peringatan Dini</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Daftar siswa yang <b>perlu dicek</b> beserta alasannya. Skor adalah penanda untuk memulai percakapan, bukan label atau penilaian siswa.</p>
        </div>

        @if (session('status'))<div class="rounded-2xl bg-green-50 p-4 text-sm font-semibold text-green-800">{{ session('status') }}</div>@endif

        @if (! $ready)
            <div class="{{ $card }}">
                <p class="font-bold">Skor risiko belum dihitung.</p>
                <p class="mt-2 text-sm text-slate-500">Jalankan <code>php artisan migrate</code> lalu <code>php artisan la:score-risk</code>.</p>
            </div>
        @else
            @if ($cohortNote)<p class="text-xs text-slate-500">{{ $cohortNote }}</p>@endif
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach (['tinggi' => ['Perlu dicek segera', 'skor ≥ ' . $thr['high']], 'sedang' => ['Perlu dipantau', 'skor ' . $thr['medium'] . '–' . ($thr['high'] - 0.1)], 'rendah' => ['Aman', 'skor < ' . $thr['medium']], 'belum_mulai' => ['Belum mulai belajar', 'tidak dinilai risikonya']] as $lv => [$lb, $sub])
                    <div class="{{ $card }}"><p class="text-sm font-semibold text-slate-500">{{ $lb }}</p><p class="mt-2 text-4xl font-black">{{ (int) ($counts[$lv] ?? 0) }}</p><p class="mt-2 text-xs text-slate-500">{{ $sub }}</p></div>
                @endforeach
            </div>
            <p class="text-xs text-slate-500">Ambang {{ $thr['calibrated'] ? 'dikalibrasi dari sebaran skor siswa yang sudah mulai belajar (n = ' . $thr['n'] . '; sasaran ≈ 8% tinggi, ≈ 17% sedang, dengan lantai skor)' : 'tetap (data belum cukup untuk kalibrasi)' }}. Akun yang belum pernah mengerjakan latihan/pelajaran tidak dinilai risikonya: untuk mereka tindakan yang tepat adalah mengajak mulai, bukan intervensi akademik.</p>
            @if (! empty($thr['hist']))
                @php $hm = max(1, max($thr['hist'])); @endphp
                <div class="{{ $card }}">
                    <h2 class="text-lg font-black">Sebaran skor (siswa yang sudah mulai)</h2>
                    <div style="display:flex;align-items:flex-end;gap:6px;height:120px;margin-top:12px" role="img" aria-label="Histogram skor risiko">
                        @foreach ($thr['hist'] as $i => $c)
                            <div style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:flex-end;height:100%">
                                <span class="text-[10px] text-slate-500">{{ $c }}</span>
                                <div style="width:100%;height:{{ max(2, round($c / $hm * 90)) }}px;background:{{ $i * 10 >= $thr['high'] ? '#ef4444' : ($i * 10 >= $thr['medium'] ? '#eab308' : '#22c55e') }};border-radius:6px 6px 0 0" title="{{ $i * 10 }}–{{ $i * 10 + 9 }}: {{ $c }} siswa"></div>
                                <span class="text-[10px] text-slate-500">{{ $i * 10 }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <form method="GET" class="flex flex-wrap items-end gap-3">
                <label class="text-sm font-semibold">Sekolah<br>
                    <select name="school" class="{{ $input }}"><option value="">Semua</option>@foreach ($schools as $s)<option value="{{ $s }}" @selected($filters['school'] === $s)>{{ $s }}</option>@endforeach</select></label>
                <label class="text-sm font-semibold">Tingkat<br>
                    <select name="level" class="{{ $input }}"><option value="">Tinggi &amp; sedang</option>@foreach (['tinggi' => 'Tinggi', 'sedang' => 'Sedang', 'rendah' => 'Rendah', 'belum_mulai' => 'Belum mulai'] as $l => $ll)<option value="{{ $l }}" @selected($filters['level'] === $l)>{{ $ll }}</option>@endforeach</select></label>
                <label class="text-sm font-semibold">Urut<br>
                    <select name="urut" class="{{ $input }}"><option value="skor" @selected($filters['urut'] === 'skor')>Skor tertinggi</option><option value="kelas" @selected($filters['urut'] === 'kelas')>Per kelas</option></select></label>
                <button class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white dark:bg-white dark:text-slate-900">Terapkan</button>
            </form>

            <div class="{{ $card }}">
                <h2 class="text-lg font-black">{{ ($filters['level'] ?? '') === 'belum_mulai' ? 'Siswa yang belum mulai belajar' : 'Siswa yang perlu dicek' }} (halaman {{ $rows->currentPage() }})</h2>
                <form method="POST" action="{{ route('admin.la.interventions.bulk') }}">
                @csrf
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full min-w-[820px] text-left text-sm">
                        <thead class="text-xs uppercase text-slate-500"><tr><th class="py-2"></th><th>Siswa</th><th>Kelas</th><th>Skor</th><th>Keyakinan data</th><th>Alasan utama &amp; saran</th></tr></thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                            @forelse ($rows as $r)
                                <tr class="align-top">
                                    <td class="py-3 pr-2"><input type="checkbox" name="ids[]" value="{{ $r->uid }}"></td>
                                    <td class="py-3 font-bold">{{ $r->name }}<div class="text-xs font-normal text-slate-500">{{ $r->school }}</div></td>
                                    <td>{{ $r->grade }} {{ $r->major }} {{ $r->parallel }}</td>
                                    <td><span class="rounded-full px-3 py-1 text-xs font-bold {{ $badge[$r->level] ?? '' }}">{{ $r->level === 'belum_mulai' ? 'belum mulai' : round($r->score) . ' · ' . $r->level }}</span></td>
                                    <td>{{ $r->confidence }}</td>
                                    <td>
                                        @foreach (array_slice($r->factors, 0, 2) as $f)
                                            <div class="mb-1"><b>{{ $f['label'] }}</b> <span class="text-slate-500">— {{ $f['detail'] }}</span></div>
                                        @endforeach
                                        @if (count($r->factors))
                                            <details class="text-xs text-slate-500"><summary class="cursor-pointer font-bold">Rincian &amp; saran</summary>
                                                @foreach ($r->factors as $f)<div class="mt-1"><b>{{ $f['label'] }}</b>: {{ $f['detail'] }}<br>→ {{ $f['action'] }}</div>@endforeach
                                            </details>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="py-6 text-center text-slate-500">Tidak ada siswa pada filter ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 rounded-2xl border border-slate-200 p-4 text-sm dark:border-white/10">
                    <p class="font-bold">Catat tindak lanjut untuk siswa yang dicentang</p>
                    <div class="mt-2 flex flex-wrap items-end gap-3">
                        <label class="text-xs font-semibold">Tindakan<br><select name="action" class="{{ $input }}">@foreach (['hubungi_siswa' => 'Hubungi siswa', 'jadwal_belajar' => 'Atur jadwal belajar', 'atur_bantuan_ai' => 'Atur bantuan AI', 'remedial' => 'Remedial', 'pendampingan' => 'Pendampingan', 'lainnya' => 'Lainnya'] as $k => $lb)<option value="{{ $k }}">{{ $lb }}</option>@endforeach</select></label>
                        <label class="text-xs font-semibold">Cek ulang (hari)<br><input type="number" name="recheck_days" value="14" min="3" max="60" class="{{ $input }} w-24"></label>
                        <label class="text-xs font-semibold">Catatan<br><input name="note" maxlength="500" class="{{ $input }} w-64"></label>
                        <label class="flex items-center gap-2 text-xs font-semibold"><input type="checkbox" name="randomize" value="1"> Acak 50% ke "tunda" (untuk menguji dampak, tercatat sebagai kelompok pembanding)</label>
                        <button class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white dark:bg-white dark:text-slate-900">Catat</button>
                    </div>
                    <details class="mt-3 text-xs text-slate-500"><summary class="cursor-pointer font-bold">Templat pesan (salin)</summary>
                        <p class="mt-2 select-all rounded-xl bg-slate-50 p-3 dark:bg-white/5">Halo [nama], ini guru bahasa Inggrismu. Aku lihat kamu belum belajar di SpeakVerse beberapa hari terakhir. Ada kendala? Yuk mulai 10 menit saja hari ini; kalau butuh bantuan, kabari aku ya.</p>
                        <p class="mt-2 select-all rounded-xl bg-slate-50 p-3 dark:bg-white/5">Halo [nama], kemajuanmu sudah bagus. Ayo lanjutkan pelajaran berikutnya minggu ini supaya tidak ketinggalan. Semangat!</p>
                    </details>
                </div>
                </form>
                <div class="mt-4 flex gap-3 text-sm font-bold">
                    @if ($rows->previousPageUrl())<a href="{{ $rows->previousPageUrl() }}" class="rounded-xl border border-slate-300 px-3 py-1">← Sebelumnya</a>@endif
                    @if ($rows->nextPageUrl())<a href="{{ $rows->nextPageUrl() }}" class="rounded-xl border border-slate-300 px-3 py-1">Berikutnya →</a>@endif
                </div>
            </div>

            <div class="{{ $card }}">
                <h2 class="text-lg font-black">Model prediksi (validasi silang 5-lipat)</h2>
                @if ($model && ! empty($model['ready']))
                    <div class="mt-3 grid gap-3 sm:grid-cols-4 text-sm">
                        <div><b>AUC</b><br>{{ number_format($model['auc'], 2) }}</div>
                        <div><b>Presisi / Recall</b><br>{{ $pc($model['prf']['precision'] ?? null) }} / {{ $pc($model['prf']['recall'] ?? null) }}</div>
                        <div><b>Brier</b><br>{{ number_format($model['brier'], 3) }}</div>
                        <div><b>n (positif)</b><br>{{ $model['n'] }} ({{ $model['positives'] }})</div>
                    </div>
                    <p class="mt-3 text-xs text-slate-500">Target: skor posttest &lt; {{ $thresholds['post'] }}. Probabilitas model tampil sebagai pelengkap skor aturan.</p>
                    @if (! empty($model['fairness']))
                        <h3 class="mt-5 font-bold">Pemeriksaan keadilan antar sekolah</h3>
                        <table class="mt-2 w-full text-left text-sm"><thead class="text-xs uppercase text-slate-500"><tr><th>Sekolah</th><th>n</th><th>AUC</th><th>Recall</th></tr></thead>
                            <tbody>@foreach ($model['fairness'] as $f)<tr><td class="py-1">{{ $f['school'] }}</td><td>{{ $f['n'] }}</td><td>{{ $f['auc'] === null ? '–' : number_format($f['auc'], 2) }}</td><td>{{ $pc($f['recall']) }}</td></tr>@endforeach</tbody></table>
                        @if (($model['fairness_gap'] ?? null) !== null && $model['fairness_gap'] > 0.1)<p class="mt-2 text-sm font-semibold text-orange-600">Selisih AUC antar sekolah {{ number_format($model['fairness_gap'], 2) }}: gunakan hati-hati.</p>@endif
                    @endif
                @else
                    <p class="mt-3 text-sm text-slate-500">{{ $model['reason'] ?? 'Model belum dilatih (belum ada data posttest yang cukup). Skor berbasis aturan tetap dipakai.' }}</p>
                @endif
            </div>

            <form method="POST" action="{{ route('admin.la.risk.weights') }}" class="{{ $card }}">
                @csrf
                <h2 class="text-lg font-black">Bobot skor aturan</h2>
                <p class="mt-1 text-sm text-slate-500">Bobot relatif (0–50). Skor dinormalkan ke 0–100 hanya dari komponen yang punya data.</p>
                <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($weights as $k => $w)
                        <label class="text-sm font-semibold">{{ $labels[$k] ?? $k }}<br><input type="number" step="0.5" min="0" max="50" name="w[{{ $k }}]" value="{{ old('w.' . $k, $w) }}" class="{{ $input }} w-24"> <span class="text-xs font-normal text-slate-500">bawaan {{ $defaults[$k] ?? '' }}</span></label>
                    @endforeach
                </div>
                @if ($errors->any())<p class="mt-3 text-sm text-red-600">{{ $errors->first() }}</p>@endif
                <button class="mt-4 rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white dark:bg-white dark:text-slate-900">Simpan bobot</button>
            </form>
        @endif
    </div>
@endsection
