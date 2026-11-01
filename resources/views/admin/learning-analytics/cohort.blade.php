@extends('layouts.admin')

@section('content')
    @php
        $card = 'rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-white/[0.03]';
        $input = 'rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-slate-900';
        $btn = 'rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white dark:bg-white dark:text-slate-900';
    @endphp
    <div class="space-y-8">
        @include('admin.learning-analytics._tabs')
        <div>
            <h1 class="text-3xl font-black">Analitik Lanjutan · Kohort Penelitian</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Tandai akun uji atau yang bukan populasi penelitian agar tidak memengaruhi angka. Akun tidak dihapus, hanya dikecualikan dari analitik, ekspor, dan daftar guru.</p>
        </div>

        @if (session('status'))<div class="rounded-2xl bg-green-50 p-4 text-sm font-semibold text-green-800">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="rounded-2xl bg-red-50 p-4 text-sm font-semibold text-red-700">{{ $errors->first() }}</div>@endif

        @if (! $ready)
            <div class="{{ $card }}"><p class="font-bold">Kolom kohort belum ada.</p><p class="mt-2 text-sm text-slate-500">Jalankan <code>php artisan migrate</code>.</p></div>
        @else
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="{{ $card }}"><p class="text-sm font-semibold text-slate-500">Akun siswa</p><p class="mt-2 text-4xl font-black">{{ $summary['total'] }}</p></div>
                <div class="{{ $card }}"><p class="text-sm font-semibold text-slate-500">Dikecualikan</p><p class="mt-2 text-4xl font-black">{{ $summary['dikecualikan'] }}</p></div>
                <div class="{{ $card }}"><p class="text-sm font-semibold text-slate-500">Tanpa sekolah</p><p class="mt-2 text-4xl font-black">{{ $summary['tanpa_sekolah'] }}</p></div>
                <div class="{{ $card }}"><p class="text-sm font-semibold text-slate-500">Nama/email mirip akun uji</p><p class="mt-2 text-4xl font-black">{{ $summary['mirip_uji'] }}</p></div>
            </div>

            <form method="GET" class="flex flex-wrap items-end gap-3">
                <label class="text-sm font-semibold">Tampilkan<br><select name="tampil" class="{{ $input }}">@foreach (['semua' => 'Semua', 'tanpa_sekolah' => 'Tanpa sekolah', 'mirip_uji' => 'Mirip akun uji', 'dikecualikan' => 'Sudah dikecualikan'] as $k => $l)<option value="{{ $k }}" @selected($tampil === $k)>{{ $l }}</option>@endforeach</select></label>
                <label class="text-sm font-semibold">Sekolah<br><select name="school" class="{{ $input }}"><option value="">Semua</option>@foreach ($schools as $s)<option value="{{ $s }}" @selected(($filters['school'] ?? null) === $s)>{{ $s }}</option>@endforeach</select></label>
                <label class="text-sm font-semibold">Cari<br><input name="q" value="{{ $filters['q'] ?? '' }}" class="{{ $input }}" placeholder="nama / email"></label>
                <button class="{{ $btn }}">Terapkan</button>
            </form>

            <form method="POST" action="{{ route('admin.la.cohort.mark') }}" class="{{ $card }}">
                @csrf
                <input type="hidden" name="mode" value="ids">
                <h2 class="text-lg font-black">Akun ({{ $rows->count() }} pada halaman ini)</h2>
                <div class="mt-4 overflow-x-auto"><table class="w-full min-w-[760px] text-left text-sm">
                    <thead class="text-xs uppercase text-slate-500"><tr><th class="py-2">Pilih</th><th>Nama</th><th>Email</th><th>Sekolah</th><th>Kelas</th><th>Status</th></tr></thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                    @forelse ($rows as $u)
                        <tr><td class="py-2"><input type="checkbox" name="ids[]" value="{{ $u->id }}"></td><td class="font-bold">{{ $u->name }}</td><td>{{ $u->email }}</td><td>{{ $u->school ?: '(Belum diisi)' }}</td><td>{{ $u->grade }} {{ $u->major }} {{ $u->parallel }}</td>
                            <td class="text-xs">@if ($u->is_test)<span class="font-bold text-orange-600">akun uji</span>@elseif ($u->exclude_from_analytics)<span class="font-bold text-orange-600">dikecualikan: {{ $u->exclude_reason }}</span>@else <span class="text-slate-500">termasuk</span>@endif</td></tr>
                    @empty<tr><td colspan="6" class="py-4 text-slate-500">Tidak ada akun.</td></tr>@endforelse
                    </tbody></table></div>
                <div class="mt-3 flex gap-3 text-sm font-bold">
                    @if ($rows->previousPageUrl())<a href="{{ $rows->previousPageUrl() }}" class="rounded-xl border border-slate-300 px-3 py-1">← Sebelumnya</a>@endif
                    @if ($rows->nextPageUrl())<a href="{{ $rows->nextPageUrl() }}" class="rounded-xl border border-slate-300 px-3 py-1">Berikutnya →</a>@endif
                </div>
                <div class="mt-4 flex flex-wrap items-end gap-3">
                    <label class="text-sm font-semibold">Tindakan<br><select name="action" class="{{ $input }}"><option value="test">Tandai akun uji</option><option value="exclude">Kecualikan dari analitik</option><option value="include">Sertakan kembali</option></select></label>
                    <label class="text-sm font-semibold">Alasan<br><input name="reason" maxlength="120" class="{{ $input }} w-64" placeholder="mis. akun uji guru"></label>
                    <button class="{{ $btn }}">Terapkan ke yang dipilih</button>
                </div>
            </form>

            <div class="grid gap-6 lg:grid-cols-2">
                <form method="POST" action="{{ route('admin.la.cohort.mark') }}" class="{{ $card }}">
                    @csrf<input type="hidden" name="mode" value="school">
                    <h2 class="text-lg font-black">Kecualikan satu sekolah/institusi</h2>
                    <p class="mt-1 text-xs text-slate-500">Mis. institusi yang bukan populasi SMK. Dapat dibatalkan dengan tindakan "Sertakan kembali".</p>
                    <div class="mt-3 flex flex-wrap items-end gap-3">
                        <select name="school" class="{{ $input }}">@foreach ($schools as $s)<option>{{ $s }}</option>@endforeach</select>
                        <select name="action" class="{{ $input }}"><option value="exclude">Kecualikan</option><option value="include">Sertakan kembali</option></select>
                        <input name="reason" maxlength="120" class="{{ $input }}" placeholder="Alasan">
                        <button class="{{ $btn }}">Terapkan</button>
                    </div>
                </form>
                <form method="POST" action="{{ route('admin.la.cohort.mark') }}" class="{{ $card }}">
                    @csrf<input type="hidden" name="mode" value="emails">
                    <h2 class="text-lg font-black">Impor daftar email</h2>
                    <p class="mt-1 text-xs text-slate-500">Satu email per baris (atau dipisah koma).</p>
                    <textarea name="emails" rows="3" class="{{ $input }} mt-3 w-full"></textarea>
                    <div class="mt-3 flex flex-wrap items-end gap-3">
                        <select name="action" class="{{ $input }}"><option value="test">Tandai akun uji</option><option value="exclude">Kecualikan</option><option value="include">Sertakan kembali</option></select>
                        <input name="reason" maxlength="120" class="{{ $input }}" placeholder="Alasan">
                        <button class="{{ $btn }}">Terapkan</button>
                    </div>
                </form>
            </div>

            <div class="{{ $card }}">
                <h2 class="text-lg font-black">Kandidat duplikat (nama sama)</h2>
                <p class="mt-1 text-xs text-slate-500">Tinjau satu per satu: bisa orang yang berbeda dengan nama sama. Untuk akun ganda, gunakan "Gabungkan…" (riwayat belajar dipindahkan ke akun utama, tidak ada yang dihapus, dapat dibatalkan) atau cukup kecualikan akun yang tidak dipakai.</p>
                @forelse ($dups as $d)
                    <div class="mt-4 rounded-2xl border border-slate-200 p-3 dark:border-white/10">
                        <p class="font-bold">{{ $d['name'] }} <a class="ml-2 text-xs font-semibold text-purple-500 underline" href="{{ route('admin.la.cohort.merge', ['source' => $d['users'][0]->id, 'target' => $d['users'][1]->id]) }}">Gabungkan…</a></p>
                        @foreach ($d['users'] as $u)<p class="text-sm text-slate-600">#{{ $u->id }} · {{ $u->email }} · {{ $u->school ?: '(Belum diisi)' }} · {{ $u->grade }} {{ $u->major }} {{ $u->parallel }} · dibuat {{ \Illuminate\Support\Carbon::parse($u->created_at)->format('d/m/Y') }}@if ($u->exclude_from_analytics) · <b class="text-orange-600">dikecualikan</b>@endif</p>@endforeach
                    </div>
                @empty<p class="mt-3 text-sm text-slate-500">Tidak ditemukan.</p>@endforelse
            </div>
        @endif
    </div>
@endsection
