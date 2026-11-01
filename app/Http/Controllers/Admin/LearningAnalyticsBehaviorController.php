<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\LearningAnalytics\Srl\BehaviorClusters;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

/** Analitik Lanjutan Fase 3: tab Penguasaan (BKT) dan Strategi & SRL. Membaca tabel hasil `la:fit-models`. */
class LearningAnalyticsBehaviorController extends Controller
{
    public function mastery(): View
    {
        $ready = Schema::hasTable('la_bkt_params') && DB::table('la_bkt_params')->exists();
        $comps = collect();
        $wheel = collect();
        $computed = null;
        if ($ready) {
            $comps = DB::table('la_bkt_params')->orderBy('competency')->get()->map(function ($c) {
                $c->curve = json_decode($c->curve ?? '{}', true) ?: [];

                return $c;
            });
            $hasRel = Schema::hasColumn('la_mastery', 'reliable');
            $rows = \App\Services\LearningAnalytics\Cohort::whereJoined(DB::table('la_mastery as m')->join('users as u', 'u.id', '=', 'm.user_id'))
                ->where('m.wheel_spinning', true)->orderByDesc('m.n_obs')
                ->get(['u.id as uid', 'u.name', 'u.school', 'u.grade', 'u.major', 'u.parallel', 'm.competency', 'm.p_mastery', 'm.n_obs']);
            // satu baris per siswa; kompetensi sebagai daftar
            $grouped = [];
            foreach ($rows as $r) {
                $g = $grouped[$r->uid] ??= (object) ['name' => $r->name, 'school' => $r->school, 'grade' => $r->grade, 'major' => $r->major, 'parallel' => $r->parallel, 'items' => [], 'max' => 0];
                $g->items[] = ['competency' => $r->competency, 'p' => (float) $r->p_mastery, 'n' => (int) $r->n_obs];
                $g->max = max($g->max, (int) $r->n_obs);
            }
            usort($grouped, fn ($a, $b) => [count($b->items), $b->max] <=> [count($a->items), $a->max]);
            $wheelTotal = count($grouped);
            $wheel = collect(array_slice($grouped, 0, 50));
            $computed = DB::table('la_bkt_params')->max('computed_at');
        }

        return view('admin.learning-analytics.mastery', compact('ready', 'comps', 'wheel', 'computed') + ['wheelTotal' => $wheelTotal ?? 0]);
    }

    public function srl(): View
    {
        $ready = Schema::hasTable('la_srl_student') && DB::table('la_srl_student')->exists();
        $data = ['ready' => $ready];
        if ($ready) {
            $run = DB::table('la_model_runs')->where('kind', 'cluster')->orderByDesc('id')->first();
            $metrics = $run ? (json_decode($run->metrics, true) ?: []) : [];
            $agg = DB::table('la_srl_student')->where('enough', true)->selectRaw(
                'COUNT(*) n, AVG(first_try_acc) acc, AVG(hint_rate) hint, AVG(hint_l3_rate) hint3, AVG(revision_rate) rev,
                 SUM(gaming_flag) gaming, SUM(struggle_productive) sp, SUM(struggle_assisted) sa, SUM(struggle_unproductive) su, SUM(struggle_no_effort) sn'
            )->first();
            $data += [
                'metrics' => $metrics,
                'clusters' => $metrics['clusters'] ?? [],
                'correlations' => $metrics['correlations'] ?? [],
                'k' => $metrics['k'] ?? null,
                'silhouette' => $metrics['silhouette'] ?? null,
                'agg' => $agg,
                'bySchool' => DB::table('la_srl_student as s')->join('users as u', 'u.id', '=', 's.user_id')->where('s.enough', true)
                    ->groupBy('u.school')->selectRaw("COALESCE(NULLIF(u.school, ''), '(Belum diisi)') as school, COUNT(*) n, AVG(s.first_try_acc) acc, AVG(s.hint_rate) hint, SUM(s.gaming_flag) gaming")
                    ->orderBy('school')->get(),
                'gaming' => DB::table('la_srl_student as s')->join('users as u', 'u.id', '=', 's.user_id')->where('s.gaming_flag', true)
                    ->orderByDesc('s.fast_wrong_rate')->limit(30)
                    ->get(['u.name', 'u.school', 'u.grade', 'u.major', 'u.parallel', 's.items', 's.fast_wrong_rate', 's.hint_chain_rate', 's.hint_rate']),
                'computed' => $run->computed_at ?? null,
                'featureLabels' => BehaviorClusters::FEATURE_LABELS,
            ];
        }

        return view('admin.learning-analytics.srl', $data);
    }
}
