<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\LearningAnalytics\AccessLog;
use App\Services\LearningAnalytics\Cohort;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

/** Kohort penelitian: tandai akun uji / dikecualikan, tinjau kandidat duplikat dan akun tanpa sekolah. Tidak ada penggabungan otomatis. */
class LearningAnalyticsCohortController extends Controller
{
    private const TEST_PATTERN = '(uji|test|demo|dummy|coba|trial)';

    public function index(Request $request): View
    {
        $ready = Cohort::hasExclusion();
        $v = $request->validate(['tampil' => ['nullable', 'in:semua,tanpa_sekolah,mirip_uji,dikecualikan'], 'school' => ['nullable', 'string', 'max:150'], 'q' => ['nullable', 'string', 'max:80']]);
        $tampil = $v['tampil'] ?? 'semua';
        $rows = null;
        $dups = collect();
        $summary = [];
        $schools = [];
        if ($ready) {
            $base = fn () => User::query()->where(function ($q) {
                $q->where('role', 'student')->orWhereNull('role')->orWhere('role', 'user');
            });
            $q = $base();
            match ($tampil) {
                'tanpa_sekolah' => $q->where(fn ($w) => $w->whereNull('school')->orWhere('school', '')),
                'mirip_uji' => $q->where(fn ($w) => $w->whereRaw('LOWER(name) REGEXP ?', [self::TEST_PATTERN])->orWhereRaw('LOWER(email) REGEXP ?', [self::TEST_PATTERN])),
                'dikecualikan' => $q->where(fn ($w) => $w->where('is_test', true)->orWhere('exclude_from_analytics', true)),
                default => null,
            };
            if (! empty($v['school'])) {
                $v['school'] === '(Belum diisi)' ? $q->where(fn ($w) => $w->whereNull('school')->orWhere('school', '')) : $q->where('school', $v['school']);
            }
            if (! empty($v['q'])) {
                $q->where(fn ($w) => $w->where('name', 'like', '%' . $v['q'] . '%')->orWhere('email', 'like', '%' . $v['q'] . '%'));
            }
            $rows = $q->orderBy('name')->simplePaginate(50, ['id', 'name', 'email', 'school', 'grade', 'major', 'parallel', 'is_test', 'exclude_from_analytics', 'exclude_reason'])->withQueryString();
            $summary = [
                'total' => $base()->count(),
                'dikecualikan' => $base()->where(fn ($w) => $w->where('is_test', true)->orWhere('exclude_from_analytics', true))->count(),
                'tanpa_sekolah' => $base()->where(fn ($w) => $w->whereNull('school')->orWhere('school', ''))->count(),
                'mirip_uji' => $base()->where(fn ($w) => $w->whereRaw('LOWER(name) REGEXP ?', [self::TEST_PATTERN])->orWhereRaw('LOWER(email) REGEXP ?', [self::TEST_PATTERN]))->count(),
            ];
            $schools = array_merge(['(Belum diisi)'], $base()->whereNotNull('school')->where('school', '!=', '')->distinct()->orderBy('school')->pluck('school')->all());
            // kandidat duplikat: nama sama setelah dinormalisasi (huruf kecil, spasi ganda dirapikan)
            $dupNames = DB::table('users')->where(fn ($w) => $w->where('role', 'student')->orWhereNull('role')->orWhere('role', 'user'))
                ->selectRaw("LOWER(TRIM(REPLACE(REPLACE(name, '  ', ' '), '  ', ' '))) n, COUNT(*) c")->groupBy('n')->having('c', '>', 1)->orderByDesc('c')->limit(30)->pluck('c', 'n');
            foreach ($dupNames as $n => $c) {
                $dups->push(['name' => $n, 'users' => User::query()->whereRaw("LOWER(TRIM(REPLACE(REPLACE(name, '  ', ' '), '  ', ' '))) = ?", [$n])
                    ->get(['id', 'name', 'email', 'school', 'grade', 'major', 'parallel', 'is_test', 'exclude_from_analytics', 'created_at'])]);
            }
            AccessLog::recordHourly('view_cohort', $tampil);
        }

        return view('admin.learning-analytics.cohort', compact('ready', 'rows', 'dups', 'summary', 'schools', 'tampil') + ['filters' => $v]);
    }

    public function mark(Request $request): RedirectResponse
    {
        abort_unless(Cohort::hasExclusion(), 404);
        $v = $request->validate([
            'mode' => ['required', 'in:ids,school,emails'], 'ids' => ['nullable', 'array', 'max:2000'], 'ids.*' => ['integer'],
            'school' => ['nullable', 'string', 'max:150'], 'emails' => ['nullable', 'string', 'max:20000'],
            'action' => ['required', 'in:test,exclude,include'], 'reason' => ['nullable', 'string', 'max:120'],
        ]);
        if ($v['action'] !== 'include' && mb_strlen(trim($v['reason'] ?? '')) < 3) {
            return back()->withErrors(['reason' => 'Alasan wajib diisi (≥3 karakter) saat mengecualikan akun.'])->withInput();
        }
        $q = User::query()->where(function ($w) {
            $w->where('role', 'student')->orWhereNull('role')->orWhere('role', 'user');
        });
        if ($v['mode'] === 'ids') {
            $q->whereIn('id', $v['ids'] ?? []);
        } elseif ($v['mode'] === 'school') {
            abort_if(empty($v['school']), 422);
            $v['school'] === '(Belum diisi)' ? $q->where(fn ($w) => $w->whereNull('school')->orWhere('school', '')) : $q->where('school', $v['school']);
        } else {
            $emails = array_values(array_filter(array_map(fn ($e) => mb_strtolower(trim($e)), preg_split('/[\s,;]+/', (string) ($v['emails'] ?? '')))));
            $q->whereIn(DB::raw('LOWER(email)'), $emails);
        }
        $set = match ($v['action']) {
            'test' => ['is_test' => true, 'exclude_from_analytics' => true, 'exclude_reason' => $v['reason']],
            'exclude' => ['is_test' => false, 'exclude_from_analytics' => true, 'exclude_reason' => $v['reason']],
            default => ['is_test' => false, 'exclude_from_analytics' => false, 'exclude_reason' => null],
        };
        $n = $q->update($set);
        AccessLog::record('cohort_' . $v['action'], $v['mode'] === 'school' ? $v['school'] : $v['mode'], ['affected' => $n, 'reason' => $v['reason'] ?? null]);

        return back()->with('status', "$n akun diperbarui. Angka akan berubah setelah perhitungan ulang (php artisan la:aggregate, la:fit-models, la:score-risk) atau jadwal dini hari.");
    }

    /** Atur filter kohort global (sekolah / kelompok) untuk seluruh tab Analitik Lanjutan pada sesi ini. */
    public function scope(Request $request): RedirectResponse
    {
        $v = $request->validate(['school' => ['nullable', 'string', 'max:150'], 'group' => ['nullable', 'in:kontrol,perlakuan']]);
        if (! empty($v['school']) && ! DB::table('users')->where('school', $v['school'])->exists()) {
            return back()->withErrors(['school' => 'Sekolah tidak ditemukan.']);
        }
        Cohort::setScope($v['school'] ?? null, $v['group'] ?? null);

        return back()->with('status', Cohort::scopeLabel() ? 'Filter kohort aktif: ' . Cohort::scopeLabel() . '.' : 'Filter kohort dimatikan (semua siswa).');
    }

    /** Pratinjau penggabungan (dijalankan lalu dibatalkan di dalam transaksi). */
    public function mergePreview(Request $request, \App\Services\LearningAnalytics\AccountMerger $merger): View
    {
        abort_unless(Schema::hasTable('la_account_merges'), 404);
        $v = $request->validate(['source' => ['nullable', 'integer'], 'target' => ['nullable', 'integer']]);
        $err = $plan = $users = null;
        if (! empty($v['source']) && ! empty($v['target'])) {
            $err = $merger->validate((int) $v['source'], (int) $v['target']);
            $users = User::whereIn('id', [$v['source'], $v['target']])->get(['id', 'name', 'email', 'school', 'grade', 'major', 'parallel', 'created_at'])->keyBy('id');
            if (! $err) {
                $plan = $merger->run((int) $v['source'], (int) $v['target'], false);
            }
        }
        $history = DB::table('la_account_merges as m')->leftJoin('users as a', 'a.id', '=', 'm.actor_id')->orderByDesc('m.id')->limit(20)
            ->get(['m.id', 'm.source_id', 'm.target_id', 'm.counts', 'm.undone_at', 'm.created_at', 'a.name as actor']);

        return view('admin.learning-analytics.merge', ['source' => $v['source'] ?? null, 'target' => $v['target'] ?? null, 'err' => $err, 'plan' => $plan, 'users' => $users, 'history' => $history]);
    }

    public function merge(Request $request, \App\Services\LearningAnalytics\AccountMerger $merger): RedirectResponse
    {
        abort_unless(Schema::hasTable('la_account_merges'), 404);
        $v = $request->validate(['source' => ['required', 'integer'], 'target' => ['required', 'integer'], 'confirm' => ['accepted']], ['confirm.accepted' => 'Centang konfirmasi terlebih dulu.']);
        if ($err = $merger->validate((int) $v['source'], (int) $v['target'])) {
            return back()->withErrors(['merge' => $err]);
        }
        $r = $merger->run((int) $v['source'], (int) $v['target'], true);
        $moved = array_sum(array_column($r['counts'], 'moved'));
        AccessLog::record('merge_accounts', $v['source'] . '→' . $v['target'], ['moved' => $moved, 'merge_id' => $r['merge_id']]);

        return redirect()->route('admin.la.cohort.merge')->with('status', "Akun #{$v['source']} digabung ke #{$v['target']}: $moved baris dipindahkan. Jalankan la:aggregate, la:fit-models, la:score-risk (atau tunggu jadwal malam) agar analitik diperbarui.");
    }

    public function mergeUndo(int $id, \App\Services\LearningAnalytics\AccountMerger $merger): RedirectResponse
    {
        abort_unless(Schema::hasTable('la_account_merges'), 404);
        $r = $merger->undo($id);
        AccessLog::record('merge_undo', (string) $id, $r);

        return back()->with('status', "Penggabungan #$id dibatalkan: {$r['restored']} baris dikembalikan." . ($r['irreversible'] ? ' Tabel tanpa kolom id tidak dapat dikembalikan: ' . implode(', ', $r['irreversible']) . '.' : ''));
    }
}
