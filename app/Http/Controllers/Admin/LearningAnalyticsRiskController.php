<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Analytics\SchoolLearningAnalytics;
use App\Services\LearningAnalytics\AccessLog;
use App\Services\LearningAnalytics\Cohort;
use App\Services\LearningAnalytics\Intervention\InterventionService;
use App\Services\LearningAnalytics\Risk\RiskScorer;
use App\Services\LearningAnalytics\Risk\RiskService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

/** Analitik Lanjutan Fase 4: Peringatan Dini, Intervensi & Konten. */
class LearningAnalyticsRiskController extends Controller
{
    public function risk(Request $request): View
    {
        $v = $request->validate([
            'school' => ['nullable', 'string', 'max:150'], 'level' => ['nullable', 'in:tinggi,sedang,rendah,belum_mulai'],
            'urut' => ['nullable', 'in:skor,kelas'], 'page' => ['nullable', 'integer', 'min:1'],
        ]);
        $ready = Schema::hasTable('la_risk_scores') && DB::table('la_risk_scores')->exists();
        $rows = null;
        $counts = [];
        $model = null;
        $schools = [];
        $thr = null;
        if ($ready) {
            $base = fn () => Cohort::whereJoined(DB::table('la_risk_scores as r')->join('users as u', 'u.id', '=', 'r.user_id'));
            $counts = $base()->selectRaw('r.level, COUNT(*) c')->groupBy('r.level')->pluck('c', 'level')->all();
            $schools = DB::table('users')->whereIn('id', DB::table('la_risk_scores')->select('user_id'))->whereNotNull('school')->where('school', '!=', '')->distinct()->orderBy('school')->pluck('school')->all();
            $q = $base();
            if (! empty($v['school'])) {
                $q->where('u.school', $v['school']);
            }
            ! empty($v['level']) ? $q->where('r.level', $v['level']) : $q->whereIn('r.level', ['tinggi', 'sedang']);
            ($v['urut'] ?? 'skor') === 'kelas'
                ? $q->orderBy('u.school')->orderBy('u.grade')->orderBy('u.major')->orderBy('u.parallel')->orderByDesc('r.score')
                : $q->orderByDesc('r.score');
            $rows = $q->simplePaginate(25, ['u.id as uid', 'u.name', 'u.school', 'u.grade', 'u.major', 'u.parallel', 'r.score', 'r.level', 'r.confidence', 'r.factors', 'r.model_prob', 'r.computed_at'])->withQueryString();
            $rows->getCollection()->transform(function ($r) {
                $r->factors = json_decode($r->factors ?? '[]', true) ?: [];

                return $r;
            });
            $run = DB::table('la_model_runs')->where('kind', 'risk_logit')->orderByDesc('id')->first();
            $model = $run ? (json_decode($run->metrics, true) ?: []) + ['computed_at' => $run->computed_at] : null;
            $t = Schema::hasTable('la_settings') ? DB::table('la_settings')->where('key', 'risk_thresholds')->value('value') : null;
            $thr = $t ? json_decode($t, true) : null;
            AccessLog::recordHourly('view_risk_list', $v['school'] ?? 'semua', ['level' => $v['level'] ?? null]);
        }
        $fixed = ['high' => config('learning_analytics.risk.level_high'), 'medium' => config('learning_analytics.risk.level_medium'), 'calibrated' => false];

        return view('admin.learning-analytics.risk', [
            'ready' => $ready, 'rows' => $rows, 'counts' => $counts, 'model' => $model, 'schools' => $schools,
            'filters' => ['school' => $v['school'] ?? null, 'level' => $v['level'] ?? null, 'urut' => $v['urut'] ?? 'skor'],
            'weights' => RiskService::weights(), 'defaults' => config('learning_analytics.risk.weights'), 'labels' => RiskScorer::LABELS,
            'thr' => $thr ?: $fixed, 'thresholds' => ['post' => config('learning_analytics.risk.posttest_threshold')],
            'cohortNote' => ($ex = Cohort::excludedSummary()) ? 'Dikecualikan dari analisis: ' . implode(', ', array_map(fn ($k, $n) => "$n $k", array_keys($ex), $ex)) . '.' : null,
        ]);
    }

    public function saveWeights(Request $request): RedirectResponse
    {
        $keys = array_keys(config('learning_analytics.risk.weights'));
        $rules = [];
        foreach ($keys as $k) {
            $rules['w.' . $k] = ['required', 'numeric', 'min:0', 'max:50'];
        }
        $data = $request->validate($rules)['w'];
        abort_if(array_sum($data) <= 0, 422, 'Minimal satu bobot harus lebih dari 0.');
        DB::table('la_settings')->updateOrInsert(['key' => 'risk_weights'], ['value' => json_encode(array_map('floatval', $data)), 'updated_by' => auth()->id(), 'updated_at' => now()]);
        AccessLog::record('update_risk_weights', null, $data);

        return redirect()->route('admin.la.risk')->with('status', 'Bobot disimpan. Berlaku pada perhitungan berikutnya (php artisan la:score-risk atau jadwal harian).');
    }

    public function interventions(Request $request, SchoolLearningAnalytics $base, InterventionService $svc): View
    {
        $gain = $pre = [];
        foreach ($base->report(null, null)['students'] as $s) {
            if ($s['gain'] !== null && $s['pre'] !== null) {
                $gain[$s['id']] = (float) $s['gain'];
                $pre[$s['id']] = (float) $s['pre'];
            }
        }
        $ready = Schema::hasTable('la_interventions');
        $eval = $ready ? $svc->interventions() : null;
        $recent = $ready ? DB::table('la_interventions as i')->join('users as u', 'u.id', '=', 'i.user_id')->leftJoin('users as t', 't.id', '=', 'i.actor_id')
            ->orderByDesc('i.followed_at')->limit(30)->get(['u.name', 'u.school', 't.name as teacher', 'i.risk_level', 'i.action', 'i.note', 'i.followed_at']) : collect();

        return view('admin.learning-analytics.interventions', ['ready' => $ready, 'eval' => $eval, 'recent' => $recent, 'content' => $ready ? $svc->content($gain, $pre) : [], 'due' => ($ready && Schema::hasColumn('la_interventions', 'recheck_at')) ? DB::table('la_interventions as i')->join('users as u', 'u.id', '=', 'i.user_id')->leftJoin('la_risk_scores as r', 'r.user_id', '=', 'i.user_id')->whereNotNull('i.recheck_at')->where('i.recheck_at', '<=', now())->orderBy('i.recheck_at')->limit(30)->get(['u.name', 'u.school', 'i.action', 'i.assignment', 'i.risk_level', 'r.level as level_now', 'i.recheck_at']) : collect(), 'showAll' => $request->query('all') === '1']);
    }

    /** Catat tindak lanjut untuk banyak siswa sekaligus; opsi acak 50% 'tunda' menyediakan kelompok pembanding. */
    public function bulkIntervention(Request $request): RedirectResponse
    {
        abort_unless(Schema::hasTable('la_interventions'), 404);
        $v = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:200'], 'ids.*' => ['integer', 'exists:users,id'],
            'action' => ['required', 'in:hubungi_siswa,jadwal_belajar,atur_bantuan_ai,remedial,pendampingan,lainnya'],
            'note' => ['nullable', 'string', 'max:500'], 'recheck_days' => ['nullable', 'integer', 'min:3', 'max:60'], 'randomize' => ['nullable', 'boolean'],
        ], ['ids.required' => 'Centang minimal satu siswa.']);
        $ids = Cohort::students()->whereIn('id', $v['ids'])->pluck('id')->all();
        $levels = DB::table('la_risk_scores')->whereIn('user_id', $ids)->pluck('level', 'user_id');
        $hasCols = Schema::hasColumn('la_interventions', 'assignment');
        $rand = (bool) ($v['randomize'] ?? false);
        $now = now();
        $direct = $delayed = 0;
        foreach ($ids as $id) {
            $assign = $rand ? (random_int(0, 1) ? 'langsung' : 'tunda') : 'langsung';
            $assign === 'langsung' ? $direct++ : $delayed++;
            $row = ['user_id' => $id, 'actor_id' => auth()->id(), 'risk_level' => $levels[$id] ?? null, 'action' => $v['action'], 'note' => $v['note'] ?? null,
                'followed_at' => $now, 'created_at' => $now, 'updated_at' => $now];
            if ($hasCols) {
                $row += ['assignment' => $rand ? $assign : null, 'recheck_at' => $now->copy()->addDays((int) ($v['recheck_days'] ?? 14))];
            }
            DB::table('la_interventions')->insert($row);
        }
        AccessLog::record('bulk_followed_up', null, ['n' => count($ids), 'action' => $v['action'], 'randomized' => $rand, 'direct' => $direct, 'delayed' => $delayed]);

        return back()->with('status', count($ids) . ' tindak lanjut dicatat' . ($rand ? " ($direct langsung, $delayed ditunda sebagai pembanding)" : '') . '.');
    }
}
