@extends('layouts.admin')

@section('content')
    @php
        $card = 'rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-white/[0.03]';
        $num = fn ($v, $d = 2) => $v === null ? '–' : (str_contains($__s = number_format((float) $v, $d), '.') ? rtrim(rtrim($__s, '0'), '.') : $__s);
    @endphp

    <div class="space-y-8">
        @include('admin.learning-analytics._tabs')
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-3xl font-black">Analitik Lanjutan · Kualitas Soal</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Analisis butir Teori Tes Klasik: kesukaran, daya beda, distraktor, reliabilitas.</p>
            </div>
            @if ($ready)
                <a href="{{ route('admin.la.items.export') }}" class="rounded-2xl bg-purple-600 px-4 py-2 text-sm font-bold text-white hover:bg-purple-700">Unduh (Excel)</a>
            @endif
        </div>

        @if (! $ready || $forms->isEmpty())
            <div class="{{ $card }}">
                <p class="font-bold">Belum ada hasil analisis butir.</p>
                <p class="mt-2 text-sm text-slate-500">Jalankan <code>php artisan migrate</code> lalu <code>php artisan la:item-analysis</code>. Butuh jawaban pilihan ganda (Reading/Listening/Vocabulary) yang sudah dinilai.</p>
            </div>
        @else
            <div class="{{ $card }}">
                <h2 class="text-lg font-black">Reliabilitas per form tes</h2>
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full min-w-[640px] text-left text-sm">
                        <thead class="text-xs uppercase text-slate-500"><tr><th class="py-2">Form (tipe|skill|unit|pelajaran)</th><th>n</th><th>Soal</th><th>Rerata</th><th>SD</th><th>α (KR-20)</th><th>SEM</th><th></th><th>Butir bermasalah</th><th>α tanpa butir berat</th></tr></thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                            @foreach ($forms as $f)
                                <tr>
                                    <td class="py-2 font-bold"><a class="text-purple-500 hover:underline" href="{{ route('admin.la.items', ['form' => $f->form]) }}">{{ $f->form }}</a></td>
                                    <td>{{ $f->n }}@if ($f->indicative) <span class="text-xs text-yellow-600">(n kecil)</span>@endif</td>
                                    <td>{{ $f->k }}</td>
                                    <td>{{ $num($f->mean) }}</td>
                                    <td>{{ $num($f->sd) }}</td>
                                    <td class="{{ $f->alpha !== null && $f->alpha < 0.6 ? 'font-bold text-red-500' : '' }}">{{ $num($f->alpha, 3) }}</td>
                                    <td>{{ $num($f->sem) }}</td>
                                    <td class="text-xs text-slate-500">{{ $f->alpha === null ? '' : ($f->alpha >= 0.8 ? 'baik' : ($f->alpha >= 0.7 ? 'cukup' : ($f->alpha >= 0.6 ? 'rendah' : 'kurang'))) }}</td>
                                    <td>{{ $f->n_flagged ?? '–' }}@if (isset($f->n_flagged) && $f->k) ({{ round($f->n_flagged * 100 / $f->k) }}%)@endif</td>
                                    <td>@if (! empty($f->n_flagged_severe)){{ $num($f->alpha_without_flagged, 3) }} <span class="text-xs text-slate-500">(−{{ $f->n_flagged_severe }} butir)</span>@else –@endif</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="mt-3 text-xs text-slate-500">Dihitung: {{ $computed }}. α &lt; 0,6 untuk tes pendek wajar, tetapi perlu ditinjau bila dipakai untuk keputusan penting.</p>
            </div>

            @if (session('status'))<div class="rounded-2xl bg-green-50 p-4 text-sm font-semibold text-green-800">{{ session('status') }}</div>@endif
            <div class="{{ $card }}">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-lg font-black">{{ $only ? 'Antrean tinjauan butir' : 'Semua butir' }}{{ $form ? ' · ' . $form : '' }}</h2>
                    <div class="flex flex-wrap gap-3 text-sm">
                        <a class="font-semibold text-purple-500" href="{{ route('admin.la.items', array_filter(['form' => $form, 'all' => $only ? 1 : null])) }}">{{ $only ? 'Tampilkan semua butir' : 'Hanya yang bertanda' }}</a>
                        @if ($form)<a class="font-semibold text-slate-500" href="{{ route('admin.la.items') }}">Semua form</a>@endif
                    </div>
                </div>
                <div class="mt-3 flex flex-wrap gap-2 text-xs">
                    @foreach (['semua' => 'Semua', 'belum' => 'Belum ditinjau', 'ditinjau' => 'Ditinjau', 'diperbaiki' => 'Diperbaiki', 'dibuang' => 'Dibuang'] as $k => $lb)
                        <a href="{{ route('admin.la.items', array_filter(['form' => $form, 'all' => $only ? null : 1, 'status' => $k])) }}" class="rounded-full border border-slate-300 px-3 py-1 font-bold {{ $status === $k ? 'bg-slate-900 text-white' : '' }}">{{ $lb }}@if ($k !== 'semua') ({{ $summary[$k] ?? 0 }})@endif</a>
                    @endforeach
                </div>
                <p class="mt-2 text-xs text-slate-500">Urut tingkat keparahan: kunci diduga salah &gt; daya beda negatif &gt; daya beda rendah &gt; distraktor mati &gt; terlalu mudah/sulit.</p>
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full min-w-[860px] text-left text-sm">
                        <thead class="text-xs uppercase text-slate-500"><tr><th class="py-2">Butir</th><th>n</th><th>p</th><th>r_pb</th><th>D 27%</th><th>Kunci</th><th>Pilihan</th><th>Penanda</th><th>Tinjauan</th></tr></thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                            @forelse ($items as $i)
                                <tr class="align-top">
                                    <td class="py-2 font-bold">{{ $i->item }}<div class="text-[10px] font-normal text-slate-400">{{ $i->form }}</div>@if ($i->link)<a class="text-[11px] font-semibold text-purple-500 underline" href="{{ $i->link }}">Buka soal pelajaran</a>@endif</td>
                                    <td>{{ $i->n }}</td>
                                    <td>{{ $num($i->p) }}</td>
                                    <td class="{{ $i->r_pb !== null && $i->r_pb < 0.2 ? 'font-bold text-red-500' : '' }}">{{ $num($i->r_pb) }}</td>
                                    <td>{{ $num($i->d_index) }}</td>
                                    <td>{{ $i->key_option ?? '–' }}</td>
                                    <td class="text-xs text-slate-500">@foreach ($i->options as $o => $c){{ $o }}:{{ $c }} @endforeach</td>
                                    <td class="text-xs text-orange-600">{{ implode('; ', $i->flags) }}@if ($i->indicative) <span class="text-yellow-600">(n kecil)</span>@endif</td>
                                    <td>
                                        @if ($hasReviews)
                                            <form method="POST" action="{{ route('admin.la.items.review') }}" class="flex flex-col gap-1">
                                                @csrf
                                                <input type="hidden" name="form" value="{{ $i->form }}"><input type="hidden" name="item" value="{{ $i->item }}">
                                                <select name="status" class="rounded-lg border border-slate-300 bg-white px-2 py-1 text-xs dark:border-white/10 dark:bg-slate-900">@foreach (['belum' => 'Belum ditinjau', 'ditinjau' => 'Ditinjau', 'diperbaiki' => 'Diperbaiki', 'dibuang' => 'Dibuang'] as $k => $lb)<option value="{{ $k }}" @selected($i->review_status === $k)>{{ $lb }}</option>@endforeach</select>
                                                <input name="note" value="{{ $i->review_note }}" maxlength="500" placeholder="Catatan" class="rounded-lg border border-slate-300 bg-white px-2 py-1 text-xs dark:border-white/10 dark:bg-slate-900">
                                                <button class="rounded-lg bg-slate-900 px-2 py-1 text-xs font-bold text-white">Simpan</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="py-6 text-center text-slate-500">Tidak ada butir pada filter ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 flex gap-3 text-sm font-bold">
                    @if ($items->previousPageUrl())<a href="{{ $items->previousPageUrl() }}" class="rounded-xl border border-slate-300 px-3 py-1">← Sebelumnya</a>@endif
                    @if ($items->nextPageUrl())<a href="{{ $items->nextPageUrl() }}" class="rounded-xl border border-slate-300 px-3 py-1">Berikutnya →</a>@endif
                    <span class="text-xs font-normal text-slate-500">{{ $items->total() }} butir · 30 per halaman. Ekspor Excel memuat semuanya.</span>
                </div>
            </div>

            <details class="{{ $card }}">
                <summary class="cursor-pointer text-lg font-black">Definisi dan keterbatasan</summary>
                <div class="mt-4 space-y-2 text-sm text-slate-600 dark:text-slate-300">
                    <p><b>p</b> = proporsi benar (terlalu mudah &gt; 0,90; terlalu sulit &lt; 0,20). <b>r_pb</b> = korelasi point-biserial antara butir dan skor sisa (total tanpa butir itu); &lt; 0,20 daya beda rendah, &lt; 0 negatif (periksa kunci). <b>D</b> = p kelompok atas 27% − p kelompok bawah 27%.</p>
                    <p><b>α</b> = k/(k−1)·(1 − Σs²ᵢ/s²_total), sama dengan KR-20 untuk butir 0/1. <b>SEM</b> = SD·√(1−α). Hanya jawaban pertama tiap siswa pada tiap form yang dihitung, dan hanya butir yang muncul pada ≥80% pengerjaan.</p>
                    <p>Tes dengan sedikit soal atau n &lt; 30 menghasilkan estimasi yang tidak stabil; tanda "n kecil" berarti indikatif. Writing dan Speaking tidak dianalisis karena tidak berskor benar/salah.</p>
                    <p>Rujukan: Crocker &amp; Algina (1986); Cronbach (1951).</p>
                </div>
            </details>
        @endif
    </div>
@endsection
