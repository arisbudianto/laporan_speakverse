@extends('layouts.admin')

@section('content')
    @include('admin.learning-analytics._colors')
    @php
        $card = 'rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-white/[0.03]';
        $badge = ['tinggi' => 'bg-red-100 text-red-700', 'sedang' => 'bg-yellow-100 text-yellow-800', 'rendah' => 'bg-green-100 text-green-700'];
        $cell = fn ($p) => $p >= 0.95 ? 'bg-green-500 text-white' : ($p >= 0.6 ? 'bg-cyan-400 text-slate-900' : ($p >= 0.3 ? 'bg-yellow-300 text-slate-900' : 'bg-orange-400 text-slate-900'));
        $actions = ['hubungi_siswa' => 'Hubungi siswa', 'jadwal_belajar' => 'Atur jadwal belajar', 'atur_bantuan_ai' => 'Atur bantuan AI', 'remedial' => 'Remedial', 'pendampingan' => 'Pendampingan teman/guru', 'lainnya' => 'Lainnya'];
    @endphp

    <div class="space-y-8">
        <div>
            <a href="{{ route('staff.classes.results', $class) }}" class="text-sm font-semibold text-slate-500">← Hasil Belajar</a>
            <h1 class="mt-3 text-3xl font-black">Analitik Kelas</h1>
            <p class="mt-1 text-slate-500">{{ $class['school'] }} · {{ $class['major'] }} · {{ $class['grade'] }} {{ $class['parallel'] }} · {{ $students->count() }} siswa</p>
            <p class="mt-1 text-xs text-slate-500">Penanda untuk membantu guru memutuskan siapa yang perlu didekati, bukan penilaian atas siswa.</p>
        </div>

        @if (session('status'))<div class="rounded-2xl bg-green-50 p-4 text-sm font-semibold text-green-800">{{ session('status') }}</div>@endif

        @if (! $ready || $students->isEmpty())
            <div class="{{ $card }}"><p class="font-bold">{{ $students->isEmpty() ? 'Belum ada siswa pada kelas ini.' : 'Analitik belum dihitung.' }}</p>
                @unless ($ready)<p class="mt-2 text-sm text-slate-500">Minta admin menjalankan <code>php artisan la:fit-models</code> dan <code>php artisan la:score-risk</code>.</p>@endunless</div>
        @else
            @if (count($remedial))
                <div class="{{ $card }}">
                    <h2 class="text-lg font-black">Saran remedial kelas</h2>
                    <ul class="mt-3 space-y-2 text-sm">@foreach ($remedial as $r)<li><b>{{ $r['title'] }}</b>: {{ $r['text'] }}</li>@endforeach</ul>
                </div>
            @endif

            <div class="{{ $card }}">
                <h2 class="text-lg font-black">Perlu dicek</h2>
                @php $flagged = $students->filter(fn ($s) => in_array($risk[$s->id]->level ?? null, ['tinggi', 'sedang']))->sortByDesc(fn ($s) => $risk[$s->id]->score); @endphp
                <div class="mt-4 space-y-4">
                    @forelse ($flagged as $s)
                        @php $r = $risk[$s->id]; $fu = $followed[$s->id] ?? collect(); @endphp
                        <div class="rounded-2xl border border-slate-200 p-4 dark:border-white/10">
                            <div class="flex flex-wrap items-center gap-3">
                                <b>{{ $s->name }}</b>
                                <span class="rounded-full px-3 py-1 text-xs font-bold {{ $badge[$r->level] }}">{{ round($r->score) }} · {{ $r->level }}</span>
                                <span class="text-xs text-slate-500">data: {{ $r->confidence }}</span>
                                @if (isset($eng[$s->id]['pattern']))<span class="text-xs text-slate-500">pola: {{ $patternLabels[$eng[$s->id]['pattern']] ?? $eng[$s->id]['pattern'] }}</span>@endif
                                @if (! empty($srl[$s->id]->profile))<span class="text-xs text-slate-500">profil: {{ $srl[$s->id]->profile }}</span>@endif
                            </div>
                            <div class="mt-2 text-sm">
                                @foreach ($r->factors as $f)<div><b>{{ $f['label'] }}</b> <span class="text-slate-500">— {{ $f['detail'] }}</span> <span class="text-xs text-slate-500">→ {{ $f['action'] }}</span></div>@endforeach
                            </div>
                            @if ($fu->count())<p class="mt-2 text-xs text-slate-500">Terakhir ditindaklanjuti {{ \Illuminate\Support\Carbon::parse($fu->first()->followed_at)->format('d/m/Y') }} ({{ str_replace('_', ' ', $fu->first()->action) }}, {{ $fu->first()->teacher ?? 'guru' }}).</p>@endif
                            <form method="POST" action="{{ route('staff.classes.analytics.interventions') }}" class="mt-3 flex flex-wrap items-center gap-2">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $s->id }}">
                                <select name="action" class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-slate-900">@foreach ($actions as $k => $l)<option value="{{ $k }}">{{ $l }}</option>@endforeach</select>
                                <input name="note" maxlength="500" placeholder="Catatan singkat (opsional)" class="min-w-[220px] flex-1 rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-slate-900">
                                <button class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white dark:bg-white dark:text-slate-900">Catat tindak lanjut</button>
                            </form>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">Tidak ada siswa bertanda saat ini.</p>
                    @endforelse
                </div>
            </div>

            <div class="{{ $card }}">
                <h2 class="text-lg font-black">Peta penguasaan kompetensi</h2>
                <p class="mt-1 text-xs text-slate-500">Hijau ≥ 95% dikuasai · biru ≥ 60% hampir · kuning ≥ 30% berkembang · oranye perlu dilatih · kosong = belum ada data. ⟳ = berputar-putar.</p>
                @if (count($comps))
                    <div class="mt-4 overflow-x-auto">
                        <table class="text-center text-xs">
                            <thead><tr><th class="px-2 py-2 text-left">Siswa</th>@foreach ($comps as $c)<th class="px-2 py-2 font-semibold" style="max-width:90px">{{ $c }}</th>@endforeach</tr></thead>
                            <tbody>
                                @foreach ($students as $s)
                                    <tr><td class="whitespace-nowrap px-2 py-1 text-left font-semibold">{{ $s->name }}</td>
                                        @foreach ($comps as $c)
                                            @php $m = $mastery[$s->id][$c] ?? null; @endphp
                                            <td class="px-1 py-1">@if ($m)<div class="rounded-lg px-2 py-1 font-bold {{ $cell($m['p']) }}" title="{{ round($m['p'] * 100) }}%">{{ round($m['p'] * 100) }}{{ $m['wheel'] ? '⟳' : '' }}</div>@else<div class="rounded-lg bg-slate-100 px-2 py-1 text-slate-400 dark:bg-white/5">–</div>@endif</td>
                                        @endforeach</tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="mt-3 text-sm text-slate-500">Belum ada kompetensi dengan parameter BKT.</p>
                @endif
            </div>

            <div class="{{ $card }}">
                <h2 class="text-lg font-black">Pola belajar 28 hari</h2>
                <div class="mt-3 overflow-x-auto"><table class="w-full min-w-[560px] text-left text-sm">
                    <thead class="text-xs uppercase text-slate-500"><tr><th class="py-2">Siswa</th><th>Hari aktif</th><th>Keteraturan</th><th>Pola</th><th>Profil perilaku</th></tr></thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                    @foreach ($students as $s)
                        @php $e = $eng[$s->id] ?? []; @endphp
                        <tr><td class="py-2 font-semibold">{{ $s->name }}</td><td>{{ $e['active_days'] ?? '–' }}</td><td>{{ isset($e['regularity_index']) ? round($e['regularity_index']) : '–' }}</td><td>{{ $patternLabels[$e['pattern'] ?? ''] ?? '–' }}</td><td>{{ $srl[$s->id]->profile ?? '–' }}</td></tr>
                    @endforeach
                    </tbody></table></div>
            </div>
        @endif
    </div>
@endsection
