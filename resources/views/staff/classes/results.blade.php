@extends('layouts.admin')

@section('content')
    <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <a href="{{ route('staff.classes.index') }}" class="text-sm font-semibold text-slate-500">← Manajemen Kelas</a>
            <h1 class="mt-4 text-3xl font-black">Hasil Belajar</h1>
            <p class="mt-2 text-slate-500">
                {{ $class['school'] }} · {{ $class['major'] }} · {{ $class['grade'] }} {{ $class['parallel'] }}
            </p>
        </div>
        <a href="{{ route('staff.classes.results.export', $class) }}"
            style="display:inline-flex;align-items:center;background:#d97706;color:#fff;font-weight:700;padding:12px 18px;border-radius:14px;text-decoration:none;">
            Export Excel
        </a>
    </div>

    <div class="mb-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        @foreach ([
            ['Siswa', $analysis['total']],
            ['Selesai Semua Unit', $analysis['completed_count'] . ' / ' . $analysis['total']],
            ['Rata-rata XP', $analysis['avg_xp'] ?? '-'],
            ['Pretest', $analysis['avg_pretest'] ?? '-'],
            ['Posttest', $analysis['avg_posttest'] ?? '-'],
        ] as [$label, $value])
            <div class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-semibold text-slate-500">{{ $label }}</p>
                <p class="mt-2 text-3xl font-black">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    <div class="mb-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        @foreach ([
            ['Listening', $analysis['avg_listening']],
            ['Reading', $analysis['avg_reading']],
            ['Writing', $analysis['avg_writing']],
            ['Speaking', $analysis['avg_speaking']],
            ['Kenaikan Pre→Post', $analysis['gain']],
        ] as [$label, $value])
            <div class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-semibold text-slate-500">{{ $label }}</p>
                <p class="mt-2 text-2xl font-black">{{ $value ?? '-' }}</p>
            </div>
        @endforeach
    </div>

    <div class="mb-8 rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="mb-4 text-lg font-black">Analisis kelas</h2>
        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <p class="text-sm text-slate-500">Belum ada nilai</p>
                <p class="text-2xl font-black">{{ $analysis['bands']['belum'] }} siswa</p>
            </div>
            <div>
                <p class="text-sm text-slate-500">Berkembang (&lt; 70)</p>
                <p class="text-2xl font-black">{{ $analysis['bands']['berkembang'] }} siswa</p>
            </div>
            <div>
                <p class="text-sm text-slate-500">Mahir (≥ 70)</p>
                <p class="text-2xl font-black">{{ $analysis['bands']['mahir'] }} siswa</p>
            </div>
        </div>
        <p class="mt-4 text-sm text-slate-500">
            Kategori memakai rata-rata skor listening, reading, writing, dan speaking yang sudah selesai.
            Kenaikan Pre→Post membandingkan rata-rata pretest dan posttest kelas.
        </p>
    </div>

    <div class="mb-8 grid gap-6 lg:grid-cols-2">
        <div class="flex h-[520px] flex-col rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-black">Tren mingguan</h2>
            @if (!empty($analysis['weekly_labels']))
                <div class="relative min-h-0 flex-1">
                    <canvas id="weeklyTrendChart"></canvas>
                </div>
            @else
                <p class="text-sm text-slate-500">Belum ada cukup data bertanggal. Setelah 2–3 minggu submit, grafik akan terisi.</p>
            @endif
        </div>
        @php $remediationItems = array_values($analysis['remediations'] ?? []); @endphp
        <div class="flex h-[520px] flex-col rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-4 flex items-center justify-between gap-2">
                <h2 class="text-lg font-black">Rekomendasi remedial otomatis</h2>
                <span class="text-xs font-semibold text-slate-400">{{ count($remediationItems) }} rekomendasi</span>
            </div>
            <ul id="remediationList" class="min-h-0 flex-1 space-y-3 overflow-y-auto text-sm">
                @foreach ($remediationItems as $item)
                    <li class="remediation-item rounded-2xl bg-slate-50 p-4">
                        <p class="font-bold text-slate-800">{{ $item['title'] }}</p>
                        <p class="mt-1 text-slate-600">{{ $item['action'] }}</p>
                    </li>
                @endforeach
            </ul>
            @if (count($remediationItems) > 5)
                <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3 text-sm">
                    <button type="button" id="remPrev" class="rounded-xl border border-slate-200 px-3 py-1.5 font-bold text-slate-600 hover:bg-slate-50 disabled:opacity-40">← Sebelumnya</button>
                    <span id="remInfo" class="text-xs font-semibold text-slate-500"></span>
                    <button type="button" id="remNext" class="rounded-xl border border-slate-200 px-3 py-1.5 font-bold text-slate-600 hover:bg-slate-50 disabled:opacity-40">Berikutnya →</button>
                </div>
                <script>
                    (function () {
                        const items = Array.from(document.querySelectorAll('#remediationList .remediation-item'));
                        const perPage = 5;
                        const pages = Math.ceil(items.length / perPage);
                        let page = 1;
                        const prev = document.getElementById('remPrev');
                        const next = document.getElementById('remNext');
                        const info = document.getElementById('remInfo');
                        function render() {
                            items.forEach(function (li, i) {
                                li.style.display = (i >= (page - 1) * perPage && i < page * perPage) ? '' : 'none';
                            });
                            info.textContent = 'Hal. ' + page + ' dari ' + pages;
                            prev.disabled = page <= 1;
                            next.disabled = page >= pages;
                            document.getElementById('remediationList').scrollTop = 0;
                        }
                        prev.addEventListener('click', function () { if (page > 1) { page--; render(); } });
                        next.addEventListener('click', function () { if (page < pages) { page++; render(); } });
                        render();
                    })();
                </script>
            @endif
        </div>
    </div>

    @if (!empty($analysis['weekly_labels']))
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
        <script>
            (function () {
                const el = document.getElementById('weeklyTrendChart');
                if (!el || typeof Chart === 'undefined') return;
                new Chart(el, {
                    type: 'line',
                    data: {
                        labels: @json($analysis['weekly_labels']),
                        datasets: [{
                            label: 'Rata-rata skor',
                            data: @json($analysis['weekly_scores']),
                            borderColor: '#0e7490',
                            backgroundColor: 'rgba(14,116,144,.15)',
                            tension: .3,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { y: { suggestedMin: 0, suggestedMax: 100 } }
                    }
                });
            })();
        </script>
    @endif

    <div class="mb-8 rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="mb-1 text-lg font-black">Siswa yang selesai Pre-Test s.d. Post-Test</h2>
        <p class="mb-4 text-sm text-slate-500">
            {{ $analysis['completed_count'] }} dari {{ $analysis['total'] }} siswa sudah menyelesaikan
            seluruh {{ $analysis['total_curriculum_lessons'] }} lesson (Pre-Test, Unit 1–4, Post-Test).
            Diurutkan dari yang tercepat (akun dibuat → Post-Test selesai).
        </p>
        @if (!empty($analysis['fastest_completion']))
            <ol class="space-y-2">
                @foreach ($analysis['fastest_completion'] as $i => $item)
                    <li class="flex items-center justify-between rounded-2xl {{ $i === 0 ? 'bg-amber-50 border border-amber-200' : 'bg-slate-50' }} px-4 py-3">
                        <span class="flex items-center gap-3">
                            <span class="flex h-7 w-7 items-center justify-center rounded-full {{ $i === 0 ? 'bg-amber-500' : 'bg-slate-400' }} text-sm font-black text-white">
                                {{ $i + 1 }}
                            </span>
                            <span class="font-semibold text-slate-800">{{ $item['name'] }}</span>
                            @if ($i === 0)
                                <span class="rounded-full bg-amber-500 px-2 py-0.5 text-xs font-bold text-white">Tercepat</span>
                            @endif
                        </span>
                        <span class="text-sm font-bold text-slate-600">{{ $item['duration_label'] }}</span>
                    </li>
                @endforeach
            </ol>
        @else
            <p class="text-sm text-slate-500">Belum ada siswa yang menyelesaikan seluruh unit (Pre-Test s.d. Post-Test).</p>
        @endif
    </div>

    <div class="mb-8 rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="mb-1 text-lg font-black">Distribusi Jenis Kesalahan (Reading)</h2>
        <p class="mb-4 text-sm text-slate-500">
            Dari jawaban Reading yang SALAH dan sudah diberi label error oleh admin
            (lihat "Error Classification" di halaman kelola soal Reading).
            Soal yang belum dilabel tidak muncul di sini.
        </p>
        @if ($analysis['error_frequency_total'] > 0)
            <div class="space-y-3">
                @foreach ($analysis['error_frequency'] as $code => $count)
                    @php
                        $percent = round($count / $analysis['error_frequency_total'] * 100);
                    @endphp
                    <div>
                        <div class="mb-1 flex items-center justify-between text-sm">
                            <span class="font-semibold text-slate-700">
                                {{ $analysis['error_code_labels'][$code] ?? $code }}
                            </span>
                            <span class="text-slate-500">{{ $count }} ({{ $percent }}%)</span>
                        </div>
                        <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full bg-purple-500" style="width: {{ $percent }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-slate-500">
                Belum ada data. Ini normal kalau soal Reading di kelas ini belum
                diberi label error, atau belum ada siswa yang menjawab salah pada
                soal yang sudah dilabel.
            </p>
        @endif
    </div>

    {{-- MODUL 6 — Teacher HITL Override --}}
    <div class="mb-8 rounded-[28px] border border-amber-200 bg-amber-50 p-6 shadow-sm">
        <h2 class="mb-1 text-lg font-black text-amber-800">⚠️ Perlu Intervensi</h2>
        <p class="mb-4 text-sm text-amber-700">
            Siswa dengan lebih dari {{ $analysis['inferential_threshold'] }}x salah soal inferensial (percobaan pertama),
            atau sudah {{ $analysis['hint_l3_threshold'] }}x atau lebih minta hint level 3 (Socratic).
        </p>
        @if (!empty($analysis['intervention_list']))
            <div class="space-y-2">
                @foreach ($analysis['intervention_list'] as $row)
                    <div class="flex flex-wrap items-center justify-between gap-2 rounded-2xl bg-white px-4 py-3">
                        <div>
                            <span class="font-bold text-slate-800">{{ $row['name'] }}</span>
                            <span class="ml-2 text-xs text-slate-500">
                                @if ($row['inferential_wrong'] > $analysis['inferential_threshold'])
                                    {{ $row['inferential_wrong'] }}x salah inferensial
                                @endif
                                @if ($row['inferential_wrong'] > $analysis['inferential_threshold'] && $row['hint_l3_count'] >= $analysis['hint_l3_threshold'])
                                    ·
                                @endif
                                @if ($row['hint_l3_count'] >= $analysis['hint_l3_threshold'])
                                    {{ $row['hint_l3_count'] }}x hint L3
                                @endif
                            </span>
                        </div>
                        <button type="button"
                            class="text-xs font-bold text-cyan-600 hover:underline"
                            onclick="openOverrideForm({{ $row['id'] }})">
                            Kelola Bantuan AI →
                        </button>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-amber-700">Tidak ada siswa yang perlu intervensi khusus saat ini.</p>
        @endif
    </div>

    <div class="mb-8 rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-lg font-black">Kelola Bantuan AI</h2>
            <button type="button"
                onclick="document.getElementById('overrideFormPanel').classList.toggle('hidden')"
                class="rounded-xl bg-cyan-500 px-4 py-2 text-sm font-bold text-white hover:bg-cyan-600">
                + Tambah Kebijakan
            </button>
        </div>

        <p class="mb-4 text-sm text-slate-500">
            Mengatur level bantuan AI (hint) untuk satu siswa atau seluruh kelas ini.
            Guru tetap punya kendali penuh — AI tidak pernah menggantikan keputusan guru.
        </p>

        @if ($analysis['active_policies']->isEmpty())
            <p class="mb-4 text-sm text-slate-500">Belum ada kebijakan aktif untuk kelas atau siswa di kelas ini.</p>
        @else
            <div class="mb-6 space-y-2">
                @foreach ($analysis['active_policies'] as $policy)
                    <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-200 px-4 py-3">
                        <div class="text-sm">
                            <span class="font-bold text-slate-800">
                                {{ $policy->scope_type === 'class' ? 'Seluruh kelas' : ($policy->student->name ?? 'Siswa dihapus') }}
                            </span>

                            <div class="mt-1 flex flex-wrap gap-2 text-xs">
                                @if ($policy->freeze_level)
                                    <span class="rounded-full bg-red-100 px-2 py-0.5 font-bold text-red-700">Kunci L{{ $policy->freeze_level }}</span>
                                @endif
                                @if ($policy->max_level)
                                    <span class="rounded-full bg-amber-100 px-2 py-0.5 font-bold text-amber-700">Maks L{{ $policy->max_level }}</span>
                                @endif
                                @if ($policy->min_level)
                                    <span class="rounded-full bg-blue-100 px-2 py-0.5 font-bold text-blue-700">Min L{{ $policy->min_level }}</span>
                                @endif
                                @if ($policy->require_hint_before_recheck)
                                    <span class="rounded-full bg-purple-100 px-2 py-0.5 font-bold text-purple-700">Wajib hint sebelum ulang</span>
                                @endif
                                @if ($policy->disable_llm)
                                    <span class="rounded-full bg-slate-200 px-2 py-0.5 font-bold text-slate-700">LLM nonaktif</span>
                                @endif
                                @if ($policy->expires_at)
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-slate-600">Sampai {{ $policy->expires_at->format('d M Y') }}</span>
                                @endif
                            </div>

                            @if ($policy->note)
                                <p class="mt-1 text-xs italic text-slate-500">"{{ $policy->note }}"</p>
                            @endif

                            <p class="mt-1 text-[11px] text-slate-400">Dibuat oleh {{ $policy->teacher->name ?? '-' }}</p>
                        </div>

                        <form action="{{ route('staff.classes.overrides.destroy', $policy) }}" method="POST"
                            onsubmit="return confirm('Hapus kebijakan ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs font-bold text-red-600 hover:underline">
                                Hapus
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif

        <div id="overrideFormPanel" class="hidden rounded-2xl border border-slate-200 bg-slate-50 p-5">
            <form action="{{ route('staff.classes.overrides.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="school" value="{{ $class['school'] }}">
                <input type="hidden" name="major" value="{{ $class['major'] }}">
                <input type="hidden" name="grade" value="{{ $class['grade'] }}">
                <input type="hidden" name="parallel" value="{{ $class['parallel'] }}">

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-bold text-slate-600">Berlaku untuk</label>
                        <select name="scope_type" id="overrideScopeType"
                            class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"
                            onchange="document.getElementById('overrideStudentField').classList.toggle('hidden', this.value !== 'student')">
                            <option value="student">Satu siswa</option>
                            <option value="class">Seluruh kelas ({{ $class['grade'] }}{{ $class['parallel'] }})</option>
                        </select>
                    </div>

                    <div id="overrideStudentField">
                        <label class="mb-1 block text-xs font-bold text-slate-600">Siswa</label>
                        <select name="student_id" id="overrideStudentId"
                            class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                            @foreach ($rows as $row)
                                <option value="{{ $row['id'] }}">{{ $row['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-xs font-bold text-slate-600">Kunci PERSIS ke level</label>
                        <select name="freeze_level" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                            <option value="">Tidak dikunci</option>
                            <option value="1">Level 1</option>
                            <option value="2">Level 2</option>
                            <option value="3">Level 3</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-bold text-slate-600">Batas maksimal level</label>
                        <select name="max_level" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                            <option value="">Tidak dibatasi</option>
                            <option value="1">Maks Level 1</option>
                            <option value="2">Maks Level 2</option>
                            <option value="3">Maks Level 3</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-bold text-slate-600">Berlaku selama (hari)</label>
                        <input type="number" name="expires_in_days" min="1" max="90" value="7"
                            class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                    </div>
                </div>

                <div class="flex flex-wrap gap-4">
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="require_hint_before_recheck" value="1" class="rounded">
                        Wajib minta hint sebelum coba lagi
                    </label>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="disable_llm" value="1" class="rounded">
                        Nonaktifkan LLM (pakai template saja)
                    </label>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold text-slate-600">Catatan (opsional)</label>
                    <textarea name="note" rows="2" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"
                        placeholder="Alasan intervensi ini..."></textarea>
                </div>

                <button type="submit" class="rounded-xl bg-cyan-500 px-5 py-2.5 text-sm font-bold text-white hover:bg-cyan-600">
                    Simpan Kebijakan
                </button>
            </form>
        </div>

        {{-- Saran perbaikan Modul 6 — riwayat kebijakan yang dihapus --}}
        @if ($analysis['deleted_policies']->isNotEmpty())
            <details class="mt-6 rounded-2xl border border-slate-200 p-4">
                <summary class="cursor-pointer text-sm font-bold text-slate-600">
                    Riwayat Kebijakan yang Dihapus ({{ $analysis['deleted_policies']->count() }})
                </summary>

                <div class="mt-3 space-y-2">
                    @foreach ($analysis['deleted_policies'] as $policy)
                        <div class="rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 text-xs text-slate-500">
                            <span class="font-bold text-slate-700">
                                {{ $policy->scope_type === 'class' ? 'Seluruh kelas' : ($policy->student->name ?? 'Siswa dihapus') }}
                            </span>

                            <div class="mt-1 flex flex-wrap gap-2">
                                @if ($policy->freeze_level)
                                    <span class="rounded-full bg-red-100 px-2 py-0.5 font-bold text-red-700">Kunci L{{ $policy->freeze_level }}</span>
                                @endif
                                @if ($policy->max_level)
                                    <span class="rounded-full bg-amber-100 px-2 py-0.5 font-bold text-amber-700">Maks L{{ $policy->max_level }}</span>
                                @endif
                                @if ($policy->min_level)
                                    <span class="rounded-full bg-blue-100 px-2 py-0.5 font-bold text-blue-700">Min L{{ $policy->min_level }}</span>
                                @endif
                                @if ($policy->require_hint_before_recheck)
                                    <span class="rounded-full bg-purple-100 px-2 py-0.5 font-bold text-purple-700">Wajib hint sebelum ulang</span>
                                @endif
                                @if ($policy->disable_llm)
                                    <span class="rounded-full bg-slate-200 px-2 py-0.5 font-bold text-slate-700">LLM nonaktif</span>
                                @endif
                            </div>

                            @if ($policy->note)
                                <p class="mt-1 italic">"{{ $policy->note }}"</p>
                            @endif

                            <p class="mt-1 text-[11px] text-slate-400">
                                Dibuat oleh {{ $policy->teacher->name ?? '-' }}
                                · Dihapus oleh {{ $policy->deletedBy->name ?? '-' }}
                                pada {{ $policy->deleted_at?->format('d M Y, H:i') }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </details>
        @endif
    </div>

    <div class="mb-4 rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-sm font-bold text-slate-700">Sebaran posisi siswa saat ini</p>
        <div class="mt-3 flex flex-wrap gap-2">
            @foreach (($analysis['stage_distribution'] ?? []) as $stageName => $stageCount)
                <span class="rounded-full border px-3 py-1 text-xs font-bold
                    {{ $stageName === 'Selesai semua' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : ($stageName === 'Belum mulai' ? 'border-slate-200 bg-slate-50 text-slate-500' : 'border-cyan-200 bg-cyan-50 text-cyan-700') }}">
                    {{ $stageName }}: {{ $stageCount }} siswa
                </span>
            @endforeach
        </div>
        <p class="mt-3 text-xs text-slate-400">
            Posisi = tahap pertama yang belum lengkap 4 skill (Listening, Reading, Writing, Speaking). "(2/4)" berarti 2 dari 4 skill di tahap itu sudah selesai.
            Kolom L/R/W/S menampilkan skor terbaru dari tahap mana pun.
        </p>
    </div>

    <div class="overflow-x-auto rounded-[28px] border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-slate-50 font-bold text-slate-500">
                <tr>
                    <th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3">Posisi Sekarang</th>
                    <th class="px-4 py-3">Progres per Tahap</th>
                    <th class="px-4 py-3">XP</th>
                    <th class="px-4 py-3" title="Listening (skor terbaru)">L</th>
                    <th class="px-4 py-3" title="Reading (skor terbaru)">R</th>
                    <th class="px-4 py-3" title="Writing (skor terbaru)">W</th>
                    <th class="px-4 py-3" title="Speaking (skor terbaru)">S</th>
                    <th class="px-4 py-3">Pre</th>
                    <th class="px-4 py-3">Post</th>
                    <th class="px-4 py-3">Selesai</th>
                    <th class="px-4 py-3">Waktu Pengerjaan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-3 font-semibold">
                            <a href="{{ route('staff.classes.students.detail', $row['id']) }}" class="text-cyan-600 hover:underline">
                                {{ $row['name'] }}
                            </a>
                        </td>
                        <td class="px-4 py-3">
                            @if ($row['stage_all_done'])
                                <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">Selesai semua</span>
                            @elseif (! $row['stage_started'])
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-500">Belum mulai</span>
                            @else
                                <span class="whitespace-nowrap rounded-full bg-cyan-50 px-3 py-1 text-xs font-bold text-cyan-700">
                                    {{ $row['stage_current'] }} ({{ $row['stage_current_done'] }}/4)
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex gap-1">
                                @foreach ($row['stages'] as $st)
                                    <span title="{{ $st['label'] }}: {{ $st['done'] }}/4 skill selesai{{ $st['skills'] ? ' ('.implode(', ', $st['skills']).')' : '' }}"
                                        class="rounded-md px-1.5 py-0.5 text-[10px] font-bold {{ $st['done'] >= 4 ? 'bg-emerald-100 text-emerald-700' : ($st['done'] > 0 ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-400') }}">
                                        {{ $st['short'] }} {{ $st['done'] }}
                                    </span>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-4 py-3">{{ $row['xp'] }}</td>
                        <td class="px-4 py-3">{{ $row['listening'] ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $row['reading'] ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $row['writing'] ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $row['speaking'] ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $row['pretest_avg'] ?? '-' }}@if ($row['pretest_avg'] !== null && $row['pretest_done'] < 4) <span class="text-[10px] text-amber-600">({{ $row['pretest_done'] }}/4)</span>@endif</td>
                        <td class="px-4 py-3">{{ $row['posttest_avg'] ?? '-' }}@if ($row['posttest_avg'] !== null && $row['posttest_done'] < 4) <span class="text-[10px] text-amber-600">({{ $row['posttest_done'] }}/4)</span>@endif</td>
                        <td class="px-4 py-3">{{ $row['completed_lessons'] }}/{{ $row['total_curriculum_lessons'] }}</td>
                        <td class="px-4 py-3">
                            @if ($row['is_fully_completed'])
                                {{ $row['duration_label'] }}
                            @else
                                <span class="text-slate-400">Belum selesai</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="px-4 py-10 text-center text-slate-500">Belum ada siswa di kelas ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <script>
        // Modul 6b — dipanggil dari tombol "Kelola Bantuan AI →" di
        // panel "Perlu Intervensi": buka form, set scope ke "Satu
        // siswa", dan langsung pilih siswa itu di dropdown.
        function openOverrideForm(studentId) {
            const panel = document.getElementById('overrideFormPanel');
            const scopeSelect = document.getElementById('overrideScopeType');
            const studentField = document.getElementById('overrideStudentField');
            const studentSelect = document.getElementById('overrideStudentId');

            panel.classList.remove('hidden');
            scopeSelect.value = 'student';
            studentField.classList.remove('hidden');
            studentSelect.value = studentId;

            panel.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    </script>
@endsection
