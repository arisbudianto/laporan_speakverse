@extends('layouts.admin')

@section('content')
    @php
        $card = 'rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-white/[0.03]';
        $input = 'rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-slate-900';
    @endphp
    <div class="space-y-8">
        @include('admin.learning-analytics._tabs')
        <div>
            <h1 class="text-3xl font-black">Gabung Akun Ganda</h1>
            <p class="mt-1 text-sm text-slate-500">Riwayat belajar akun lama dipindahkan ke akun utama. Tidak ada yang dihapus; akun lama tetap ada tetapi dikecualikan dari analisis. Dapat dibatalkan.</p>
        </div>
        @if (session('status'))<div class="rounded-2xl bg-green-50 p-4 text-sm font-semibold text-green-800">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="rounded-2xl bg-red-50 p-4 text-sm font-semibold text-red-800">{{ $errors->first() }}</div>@endif

        <form method="GET" action="{{ route('admin.la.cohort.merge') }}" class="{{ $card }}">
            <div class="flex flex-wrap items-end gap-3">
                <label class="text-sm font-semibold">ID akun lama (dipindahkan dari)<br><input type="number" name="source" value="{{ $source }}" class="{{ $input }} w-40"></label>
                <label class="text-sm font-semibold">ID akun utama (dipindahkan ke)<br><input type="number" name="target" value="{{ $target }}" class="{{ $input }} w-40"></label>
                <button class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white dark:bg-white dark:text-slate-900">Lihat pratinjau</button>
                <a class="text-sm font-semibold text-purple-500 underline" href="{{ route('admin.la.cohort') }}">Kembali ke Kohort</a>
            </div>
        </form>

        @if ($err)
            <div class="rounded-2xl bg-red-50 p-4 text-sm font-semibold text-red-800">{{ $err }}</div>
        @elseif ($plan)
            <div class="{{ $card }}">
                <h2 class="text-lg font-black">Pratinjau</h2>
                <div class="mt-3 grid gap-3 text-sm sm:grid-cols-2">
                    @foreach ([['Akun lama (dikecualikan setelah digabung)', $source], ['Akun utama (tetap dianalisis)', $target]] as [$lb, $id])
                        @php $u = $users[$id] ?? null; @endphp
                        <div class="rounded-2xl border border-slate-200 p-3 dark:border-white/10"><p class="text-xs font-bold uppercase text-slate-500">{{ $lb }}</p>
                            <p class="font-bold">#{{ $id }} · {{ $u->name ?? '–' }}</p><p class="text-slate-500">{{ $u->email ?? '' }} · {{ $u->school ?? '' }} · {{ $u->grade ?? '' }} {{ $u->major ?? '' }} {{ $u->parallel ?? '' }}</p></div>
                    @endforeach
                </div>
                <table class="mt-4 w-full text-left text-sm"><thead class="text-xs uppercase text-slate-500"><tr><th class="py-1">Tabel</th><th>Baris di akun lama</th><th>Akan dipindah</th><th>Tetap (bentrok)</th></tr></thead><tbody>
                    @forelse ($plan['counts'] as $t => $c)<tr class="border-t border-slate-100"><td class="py-1 font-mono text-xs">{{ $t }}</td><td>{{ $c['rows'] }}</td><td>{{ $c['moved'] }}</td><td>{{ $c['kept'] }}</td></tr>
                    @empty<tr><td colspan="4" class="py-3 text-slate-500">Akun lama tidak punya riwayat untuk dipindahkan.</td></tr>@endforelse
                </tbody></table>
                @if ($plan['irreversible'])<p class="mt-3 text-xs" style="color:#b45309">Tabel ini tidak punya kolom id sehingga tidak dapat dikembalikan saat dibatalkan: {{ implode(', ', $plan['irreversible']) }}.</p>@endif
                <p class="mt-3 text-xs text-slate-500">Baris "bentrok" (mis. progres pelajaran yang sama di kedua akun) tidak dipindahkan agar data akun utama tidak tertimpa. Saldo XP/koin tidak digabung.</p>
                <form method="POST" action="{{ route('admin.la.cohort.merge.do') }}" class="mt-4">
                    @csrf
                    <input type="hidden" name="source" value="{{ $source }}"><input type="hidden" name="target" value="{{ $target }}">
                    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="confirm" value="1"> Saya sudah memeriksa bahwa kedua akun adalah siswa yang sama.</label>
                    <button class="mt-3 rounded-xl px-4 py-2 text-sm font-bold text-white" style="background:#b91c1c">Gabungkan sekarang</button>
                </form>
            </div>
        @endif

        <div class="{{ $card }}">
            <h2 class="text-lg font-black">Riwayat penggabungan</h2>
            <table class="mt-3 w-full text-left text-sm"><thead class="text-xs uppercase text-slate-500"><tr><th class="py-1">#</th><th>Dari → ke</th><th>Baris</th><th>Oleh</th><th>Waktu</th><th></th></tr></thead><tbody>
                @forelse ($history as $h)
                    @php $moved = array_sum(array_column(json_decode($h->counts, true) ?: [], 'moved')); @endphp
                    <tr class="border-t border-slate-100"><td class="py-1">{{ $h->id }}</td><td>#{{ $h->source_id }} → #{{ $h->target_id }}</td><td>{{ $moved }}</td><td>{{ $h->actor ?? '–' }}</td><td>{{ \Illuminate\Support\Carbon::parse($h->created_at)->format('d/m/Y H:i') }}</td>
                        <td>@if ($h->undone_at)<span class="text-xs text-slate-500">dibatalkan</span>@else<form method="POST" action="{{ route('admin.la.cohort.merge.undo', $h->id) }}">@csrf<button class="text-xs font-bold text-red-600">Batalkan</button></form>@endif</td></tr>
                @empty<tr><td colspan="6" class="py-3 text-slate-500">Belum ada.</td></tr>@endforelse
            </tbody></table>
        </div>
    </div>
@endsection
