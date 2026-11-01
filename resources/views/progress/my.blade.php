<x-app-layout>
    @include('admin.learning-analytics._colors')
    @php
        $card = 'rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-white/5';
        $colors = ['green' => 'bg-green-500', 'cyan' => 'bg-cyan-400', 'yellow' => 'bg-yellow-400', 'orange' => 'bg-orange-400'];
    @endphp
    <div class="mx-auto max-w-4xl space-y-6 px-4 py-8">
        <div>
            <a href="{{ route('progress') }}" class="text-sm font-semibold text-slate-500">← Progres</a>
            <h1 class="mt-2 text-3xl font-black">Kemajuan Saya</h1>
            <p class="mt-1 text-sm text-slate-500">Hanya kamu yang melihat halaman ini. Tidak ada peringkat atau perbandingan dengan teman.</p>
        </div>

        @if (session('status'))<div class="rounded-2xl bg-green-50 p-4 text-sm font-semibold text-green-800">{{ session('status') }}</div>@endif

        <div class="{{ $card }}">
            <h2 class="text-lg font-black">Langkah berikutnya</h2>
            <p class="mt-2">{{ $next['text'] }}</p>
        </div>

        <div class="{{ $card }}">
            <h2 class="text-lg font-black">Target minggu ini</h2>
            <p class="mt-2">Hari belajar dalam 7 hari terakhir: <b>{{ $weekly['active'] }}</b> dari target {{ $weekly['target'] }}.
                {{ $weekly['done'] ? 'Target tercapai, bagus sekali!' : 'Tinggal ' . $weekly['remaining'] . ' hari lagi. Sesi singkat 15–20 menit sudah cukup.' }}</p>
            <div class="mt-4 grid grid-cols-7 gap-2" role="img" aria-label="Kalender belajar 28 hari terakhir">
                @foreach ($cal as $d)
                    <div class="flex h-9 items-center justify-center rounded-lg text-xs font-bold {{ $d['active'] ? 'bg-green-500 text-white' : 'bg-slate-100 text-slate-400 dark:bg-white/10' }}" title="{{ $d['date'] }}">{{ $d['day'] }}</div>
                @endforeach
            </div>
            <p class="mt-2 text-xs text-slate-500">Hijau = hari kamu belajar (28 hari terakhir).</p>
        </div>

        <div class="{{ $card }}">
            <h2 class="text-lg font-black">Penguasaan kompetensi</h2>
            @if ($states->isEmpty())
                <p class="mt-2 text-sm text-slate-500">Belum ada data. Kerjakan beberapa latihan, nanti peta kemajuanmu muncul di sini.</p>
            @else
                <div class="mt-4 space-y-3">
                    @foreach ($states as $s)
                        @php [$label, $c] = \App\Services\LearningAnalytics\Guidance\StudentGuidance::masteryLabel((float) $s->p_mastery); @endphp
                        <div>
                            <div class="flex justify-between text-sm"><b>{{ $s->competency }}</b><span class="text-slate-500">{{ $label }}</span></div>
                            <div class="mt-1 h-3 overflow-hidden rounded-full bg-slate-200 dark:bg-white/10"><div class="h-full {{ $colors[$c] ?? 'bg-slate-400' }}" style="width: {{ round($s->p_mastery * 100) }}%"></div></div>
                        </div>
                    @endforeach
                </div>
                <p class="mt-3 text-xs text-slate-500">Ini perkiraan berdasarkan jawabanmu, bukan nilai rapor.</p>
            @endif
        </div>

        <div class="{{ $card }}">
            <h2 class="text-lg font-black">Izin penggunaan data untuk penelitian</h2>
            <p class="mt-2 text-sm">Data belajarmu dapat dipakai untuk penelitian pembelajaran bahasa Inggris tanpa nama dan identitas (memakai kode acak). Menolak tidak memengaruhi nilai atau akses belajarmu.</p>
            <p class="mt-2 text-sm text-slate-500">Status saat ini: <b>{{ $consent === null ? 'belum dijawab' : ($consent->accepted ? 'setuju' : 'tidak setuju') }}</b></p>
            <form method="POST" action="{{ route('progress.consent') }}" class="mt-3 flex gap-2">
                @csrf
                <button name="accepted" value="1" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white dark:bg-white dark:text-slate-900">Saya setuju</button>
                <button name="accepted" value="0" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-bold dark:border-white/20">Saya tidak setuju</button>
            </form>
        </div>
    </div>
</x-app-layout>
