@php
    $__u = auth()->user();
    $__show = false;
    $__text = null;
    if ($__u && ! in_array($__u->role, ['admin', 'teacher'], true) && ! request()->routeIs('progress.*') && \Illuminate\Support\Facades\Schema::hasTable('la_consents')) {
        $__snooze = (int) session('la_consent_snooze_until', 0);
        if ($__snooze < now()->timestamp) {
            $__row = \Illuminate\Support\Facades\DB::table('la_consents')->where('user_id', $__u->id)->first(['version']);
            $__show = ! $__row || $__row->version !== \App\Http\Controllers\StudentProgressController::CONSENT_VERSION;
            if ($__show) {
                $__meta = \App\Http\Controllers\Admin\LearningAnalyticsResearchController::meta();
            }
        }
    }
@endphp
@if ($__show)
    <div style="position:fixed;inset:0;z-index:9999;background:rgba(15,23,42,.6);display:flex;align-items:center;justify-content:center;padding:16px">
        <div style="max-width:520px;width:100%;background:#fff;color:#0f172a;border-radius:24px;padding:24px;box-shadow:0 20px 50px rgba(0,0,0,.3)">
            <h2 style="font-size:20px;font-weight:900;margin:0">Izin penggunaan data untuk penelitian</h2>
            <p style="margin:12px 0 0;font-size:14px;line-height:1.5">Data belajarmu di SpeakVerse dapat dipakai untuk penelitian pembelajaran bahasa Inggris <b>tanpa nama dan identitas</b> (memakai kode acak), termasuk jejak halaman yang kamu buka (nama halaman dan waktu saja, tanpa isi). Menolak <b>tidak memengaruhi</b> nilai atau akses belajarmu, dan kamu bisa mengubah pilihan kapan saja di halaman Progres Saya.</p>
            @if (! empty($__meta['ethics_no']))<p style="margin:8px 0 0;font-size:12px;color:#64748b">Persetujuan etik: {{ $__meta['ethics_no'] }}</p>@endif
            @if (! empty($__meta['contact']))<p style="margin:4px 0 0;font-size:12px;color:#64748b">Pertanyaan: {{ $__meta['contact'] }}</p>@endif
            <form method="POST" action="{{ route('progress.consent') }}" style="margin-top:16px;display:flex;flex-wrap:wrap;gap:8px">
                @csrf
                <button name="accepted" value="1" style="background:#0f172a;color:#fff;border-radius:12px;padding:8px 16px;font-weight:700;font-size:14px;border:0;cursor:pointer">Saya setuju</button>
                <button name="accepted" value="0" style="background:#fff;color:#0f172a;border:1px solid #cbd5e1;border-radius:12px;padding:8px 16px;font-weight:700;font-size:14px;cursor:pointer">Saya tidak setuju</button>
                <button name="accepted" value="later" style="background:transparent;color:#64748b;border:0;padding:8px 8px;font-size:13px;cursor:pointer">Nanti saja</button>
            </form>
        </div>
    </div>
@endif
