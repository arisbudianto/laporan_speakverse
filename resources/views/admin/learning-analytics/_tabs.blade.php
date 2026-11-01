@include('admin.learning-analytics._colors')
@php
    $tabs = [
        'admin.la.engagement' => 'Keterlibatan',
        'admin.la.outcomes' => 'Hasil Belajar',
        'admin.la.mastery' => 'Penguasaan',
        'admin.la.srl' => 'Strategi & SRL',
        'admin.la.items' => 'Kualitas Soal',
        'admin.la.risk' => 'Peringatan Dini',
        'admin.la.interventions' => 'Intervensi & Konten',
        'admin.la.cohort' => 'Kohort',
        'admin.la.research' => 'Ekspor Riset',
        'admin.la.report' => 'Laporan',
        'admin.la.method' => 'Metodologi',
    ];
@endphp
<div class="flex flex-wrap gap-2">
    @foreach ($tabs as $r => $label)
        <a href="{{ route($r) }}"
           class="rounded-2xl px-4 py-2 text-sm font-bold {{ request()->routeIs($r . '*') ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' : 'border border-slate-300 hover:bg-slate-100 dark:border-white/10 dark:hover:bg-white/5' }}">{{ $label }}</a>
    @endforeach
</div>
@php
    $__scope = \App\Services\LearningAnalytics\Cohort::scope();
    $__schools = \Illuminate\Support\Facades\Cache::remember('la:schools', 600, fn () => \Illuminate\Support\Facades\DB::table('users')->whereNotNull('school')->where('school', '!=', '')->distinct()->orderBy('school')->pluck('school')->all());
@endphp
<form method="POST" action="{{ route('admin.la.cohort.scope') }}" class="mt-3 flex flex-wrap items-center gap-2 text-xs">
    @csrf
    <span class="font-bold text-slate-500">Kohort:</span>
    <select name="school" class="rounded-lg border border-slate-300 bg-white px-2 py-1 dark:border-white/10 dark:bg-slate-900"><option value="">Semua sekolah</option>@foreach ($__schools as $__s)<option value="{{ $__s }}" @selected($__scope['school'] === $__s)>{{ $__s }}</option>@endforeach</select>
    <select name="group" class="rounded-lg border border-slate-300 bg-white px-2 py-1 dark:border-white/10 dark:bg-slate-900"><option value="">Semua kelompok</option><option value="kontrol" @selected($__scope['group'] === 'kontrol')>Kontrol</option><option value="perlakuan" @selected($__scope['group'] === 'perlakuan')>Perlakuan</option></select>
    <button class="rounded-lg bg-slate-900 px-3 py-1 font-bold text-white dark:bg-white dark:text-slate-900">Terapkan</button>
    @if ($__scope['school'] || $__scope['group'])<span class="rounded-full px-2 py-1 font-bold" style="background:#fef3c7;color:#92400e">Filter aktif: {{ \App\Services\LearningAnalytics\Cohort::scopeLabel() }}</span>@endif
    <span class="text-slate-400">Berlaku untuk tabel per siswa; analisis butir &amp; parameter BKT dihitung dari seluruh kohort.</span>
</form>
