@extends('layouts.admin')

@section('content')
    @php
        $card = 'rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-white/[0.03]';
        $input = 'rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-slate-900';
    @endphp

    <div class="space-y-8">
        @include('admin.learning-analytics._tabs')
        <div>
            <h1 class="text-3xl font-black">Analitik Lanjutan · Ekspor Riset</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Data dipseudonimkan (ID acak, tanpa nama/email) dan hanya siswa yang menyetujui. Setiap ekspor dan perubahan kelompok tercatat.</p>
        </div>

        @if ($errors->has('export') || $errors->has('reason'))<div class="rounded-2xl bg-red-50 p-4 text-sm font-semibold text-red-800">{{ $errors->first('export') ?: $errors->first('reason') }}</div>@endif
        @if ($cohortNote)<p class="text-xs text-slate-500">{{ $cohortNote }}</p>@endif
        @if (session('status'))<div class="rounded-2xl bg-green-50 p-4 text-sm font-semibold text-green-800">{{ session('status') }}</div>@endif

        @if (! $ready)
            <div class="{{ $card }}"><p class="font-bold">Tabel persetujuan belum ada.</p><p class="mt-2 text-sm text-slate-500">Jalankan <code>php artisan migrate</code>.</p></div>
        @else
            <div class="grid gap-4 sm:grid-cols-4">
                <div class="{{ $card }}"><p class="text-sm font-semibold text-slate-500">Siswa</p><p class="mt-2 text-4xl font-black">{{ $total }}</p></div>
                <div class="{{ $card }}"><p class="text-sm font-semibold text-slate-500">Setuju</p><p class="mt-2 text-4xl font-black text-green-600">{{ $yes }}</p></div>
                <div class="{{ $card }}"><p class="text-sm font-semibold text-slate-500">Menolak</p><p class="mt-2 text-4xl font-black text-orange-500">{{ $no }}</p></div>
                <div class="{{ $card }}"><p class="text-sm font-semibold text-slate-500">Belum menjawab</p><p class="mt-2 text-4xl font-black">{{ max(0, $total - $yes - $no) }}</p></div>
            </div>

            <form method="POST" action="{{ route('admin.la.research.export') }}" class="{{ $card }}">
                @csrf
                <h2 class="text-lg font-black">Unduh dataset</h2>
                <label class="mt-3 block text-sm font-semibold">Format<br><select name="format" class="{{ $input }}"><option value="xlsx">Excel (data + kamus data + metadata)</option><option value="csv">CSV (satu baris per siswa)</option><option value="long">CSV format panjang (satu baris per butir)</option></select></label>
                <a href="{{ route('admin.la.research.codebook') }}" class="mt-2 inline-block text-xs font-semibold text-purple-500 underline">Unduh kamus data saja</a>
                <p class="mt-1 text-sm text-slate-500">Bawaan: pseudonim, hanya siswa yang setuju. Opsi di bawah memerlukan alasan dan dicatat di log akses.</p>
                <label class="mt-4 flex items-center gap-2 text-sm"><input type="checkbox" name="identified" value="1"> Sertakan identitas (nama, email)</label>
                <label class="mt-2 flex items-center gap-2 text-sm"><input type="checkbox" name="include_unconsented" value="1"> Sertakan siswa yang belum/tidak menyetujui</label>
                <label class="mt-3 block text-sm font-semibold">Alasan (wajib untuk opsi di atas)<br><input name="reason" value="{{ old('reason') }}" maxlength="300" class="{{ $input }} w-full max-w-xl"></label>
                @error('reason')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                <button class="mt-4 rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white dark:bg-white dark:text-slate-900">Unduh</button>
            </form>

            <div class="{{ $card }}">
                <h2 class="text-lg font-black">Persetujuan per kelas</h2>
                <p class="mt-1 text-sm text-slate-500">Target: {{ $meta['target_pct'] }}% menjawab setuju.</p>
                <div class="mt-3 overflow-x-auto"><table class="w-full min-w-[640px] text-left text-sm">
                    <thead class="text-xs uppercase text-slate-500"><tr><th class="py-2">Kelas</th><th>Siswa</th><th>Setuju</th><th>Menolak</th><th>Belum</th><th>% setuju</th></tr></thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                    @forelse ($classes as $c)
                        @php $pct = $c['n'] ? round(100 * $c['yes'] / $c['n']) : 0; @endphp
                        <tr><td class="py-2 font-bold">{{ $c['school'] }} · {{ $c['grade'] }} {{ $c['major'] }} {{ $c['parallel'] }}</td><td>{{ $c['n'] }}</td><td>{{ $c['yes'] }}</td><td>{{ $c['no'] }}</td><td>{{ $c['n'] - $c['yes'] - $c['no'] }}</td>
                            <td style="font-weight:700;color:{{ $pct >= $meta['target_pct'] ? '#16a34a' : '#ea580c' }}">{{ $pct }}%</td></tr>
                    @empty<tr><td colspan="6" class="py-4 text-slate-500">Belum ada siswa.</td></tr>@endforelse
                    </tbody></table></div>
            </div>

            <form method="POST" action="{{ route('admin.la.research.consent_batch') }}" class="{{ $card }}">
                @csrf
                <h2 class="text-lg font-black">Persetujuan kolektif (sekolah / orang tua)</h2>
                <p class="mt-1 text-sm text-slate-500">Dicatat atas dasar dokumen resmi. Siswa yang sudah menjawab sendiri tidak ditimpa. Dapat ditarik kembali per batch.</p>
                <div class="mt-4 flex flex-wrap items-end gap-3">
                    <label class="text-sm font-semibold">Sekolah<br><select name="school" class="{{ $input }}">@foreach ($schools as $s)<option>{{ $s }}</option>@endforeach</select></label>
                    <label class="text-sm font-semibold">Tingkat<br><select name="grade" class="{{ $input }}"><option value="">Semua</option><option>X</option><option>XI</option><option>XII</option></select></label>
                    <label class="text-sm font-semibold">Paralel<br><select name="parallel" class="{{ $input }}"><option value="">Semua</option><option>A</option><option>B</option><option>C</option><option>D</option></select></label>
                    <label class="text-sm font-semibold">Dasar<br><select name="basis" class="{{ $input }}"><option value="sekolah">Izin sekolah</option><option value="orangtua">Izin orang tua</option></select></label>
                    <label class="text-sm font-semibold">Keputusan<br><select name="decision" class="{{ $input }}"><option value="setuju">Setuju</option><option value="tolak">Tolak</option></select></label>
                    <label class="text-sm font-semibold">No. dokumen<br><input name="document_no" maxlength="120" required class="{{ $input }}"></label>
                    <label class="text-sm font-semibold">Catatan<br><input name="note" maxlength="500" class="{{ $input }}"></label>
                    <button class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white dark:bg-white dark:text-slate-900">Catat</button>
                </div>
                @if ($batches->isNotEmpty())
                    <table class="mt-4 w-full text-left text-sm"><thead class="text-xs uppercase text-slate-500"><tr><th class="py-1">#</th><th>Sekolah/kelas</th><th>Dasar</th><th>Keputusan</th><th>Dokumen</th><th>Siswa</th><th></th></tr></thead><tbody>
                    @foreach ($batches as $b)
                        <tr class="border-t border-slate-100"><td class="py-1">{{ $b->id }}</td><td>{{ $b->school }} {{ $b->grade }} {{ $b->parallel }}</td><td>{{ $b->basis }}</td><td>{{ $b->decision }}</td><td>{{ $b->document_no }}</td><td>{{ $b->affected }}</td>
                            <td>@if ($b->revoked_at)<span class="text-xs text-slate-500">ditarik</span>@else<button formnovalidate formaction="{{ route('admin.la.research.consent_revoke', $b->id) }}" class="text-xs font-bold text-red-600">Tarik</button>@endif</td></tr>
                    @endforeach
                    </tbody></table>
                @endif
            </form>

            <form method="POST" action="{{ route('admin.la.research.meta') }}" class="{{ $card }}">
                @csrf
                <h2 class="text-lg font-black">Keterangan penelitian (tampil di modal persetujuan siswa)</h2>
                <div class="mt-3 flex flex-wrap items-end gap-3">
                    <label class="text-sm font-semibold">No. persetujuan etik<br><input name="ethics_no" value="{{ $meta['ethics_no'] }}" maxlength="120" class="{{ $input }}"></label>
                    <label class="text-sm font-semibold">Kontak peneliti<br><input name="contact" value="{{ $meta['contact'] }}" maxlength="200" class="{{ $input }}"></label>
                    <label class="text-sm font-semibold">Target setuju (%)<br><input type="number" name="target_pct" value="{{ $meta['target_pct'] }}" min="1" max="100" class="{{ $input }} w-24"></label>
                    <button class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white dark:bg-white dark:text-slate-900">Simpan</button>
                </div>
            </form>

            <form method="POST" action="{{ route('admin.la.research.group') }}" class="{{ $card }}">
                @csrf
                <h2 class="text-lg font-black">Kelompok penelitian</h2>
                <p class="mt-1 text-sm text-slate-500">Tetapkan kontrol/perlakuan per kelas untuk desain kuasi-eksperimen. Saat ini: @forelse ($groups as $g => $c)<b>{{ $g }}</b> {{ $c }}@if (! $loop->last), @endif @empty belum ada @endforelse.</p>
                <div class="mt-4 flex flex-wrap items-end gap-3">
                    <label class="text-sm font-semibold">Sekolah<br><select name="school" class="{{ $input }}">@foreach ($schools as $s)<option>{{ $s }}</option>@endforeach</select></label>
                    <label class="text-sm font-semibold">Tingkat<br><select name="grade" class="{{ $input }}"><option value="">Semua</option><option>X</option><option>XI</option><option>XII</option></select></label>
                    <label class="text-sm font-semibold">Paralel<br><select name="parallel" class="{{ $input }}"><option value="">Semua</option><option>A</option><option>B</option><option>C</option><option>D</option></select></label>
                    <label class="text-sm font-semibold">Kelompok<br><select name="group" class="{{ $input }}"><option value="perlakuan">Perlakuan</option><option value="kontrol">Kontrol</option><option value="">(kosongkan)</option></select></label>
                    <button class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white dark:bg-white dark:text-slate-900">Terapkan</button>
                </div>
            </form>

            <div class="{{ $card }}">
                <h2 class="text-lg font-black">Log akses terbaru</h2>
                <div class="mt-3 overflow-x-auto"><table class="w-full min-w-[640px] text-left text-sm">
                    <thead class="text-xs uppercase text-slate-500"><tr><th class="py-2">Waktu</th><th>Pengguna</th><th>Aksi</th><th>Target</th></tr></thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                    @forelse ($log as $l)<tr><td class="py-2">{{ \Illuminate\Support\Carbon::parse($l->created_at)->format('d/m/Y H:i') }}</td><td>{{ $l->name ?? '–' }}</td><td>{{ $l->action }}</td><td>{{ $l->target }}</td></tr>
                    @empty<tr><td colspan="4" class="py-4 text-slate-500">Belum ada catatan.</td></tr>@endforelse
                    </tbody></table></div>
            </div>
        @endif
    </div>
@endsection
