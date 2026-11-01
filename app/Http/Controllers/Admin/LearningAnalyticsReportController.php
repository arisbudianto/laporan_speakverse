<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Analytics\SchoolLearningAnalytics;
use App\Services\LearningAnalytics\AccessLog;
use App\Services\LearningAnalytics\Cohort;
use App\Services\LearningAnalytics\Engagement\EngagementReport;
use App\Services\LearningAnalytics\Outcome\FormComparability;
use App\Services\LearningAnalytics\Outcome\MultilevelOutcome;
use App\Services\LearningAnalytics\Outcome\OutcomeAnalysis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

/** Laporan ringkas satu halaman (siap cetak / simpan sebagai PDF lewat dialog cetak peramban). */
class LearningAnalyticsReportController extends Controller
{
    public function show(Request $request, SchoolLearningAnalytics $base, OutcomeAnalysis $analysis, EngagementReport $eng): View
    {
        $v = $request->validate(['school' => ['nullable', 'string', 'max:150'], 'grade' => ['nullable', 'in:X,XI,XII']]);
        $school = ($v['school'] ?? null) ?: null;
        $grade = ($v['grade'] ?? null) ?: null;
        $rep = $base->report($school, $grade);
        $a = $analysis->analyze($rep['students']);
        $e = $eng->build($school, $grade);

        $eq = null;
        if (Schema::hasTable('la_item_stats')) {
            $eq = (new FormComparability((float) config('learning_analytics.outcomes.nonequivalence_delta_p')))->analyze(
                DB::table('la_item_stats')->get(['form', 'item', 'p', 'n'])->map(fn ($i) => ['form' => $i->form, 'item' => $i->item, 'p' => (float) $i->p, 'n' => (int) $i->n])->all());
        }
        $grp = Schema::hasColumn('users', 'research_group') ? DB::table('users')->whereIn('id', array_column($rep['students'], 'id'))->pluck('research_group', 'id')->all() : [];
        $ml = (new MultilevelOutcome())->analyze($rep['students'], $grp);

        $risk = [];
        if (Schema::hasTable('la_risk_scores')) {
            $q = Cohort::whereJoined(DB::table('la_risk_scores as r')->join('users as u', 'u.id', '=', 'r.user_id'));
            $school && $q->where('u.school', $school);
            $grade && $q->where('u.grade', $grade);
            $risk = $q->selectRaw('r.level, COUNT(*) c')->groupBy('r.level')->pluck('c', 'level')->all();
        }
        $consent = null;
        if (Schema::hasTable('la_consents')) {
            $q = Cohort::whereJoined(DB::table('la_consents as c')->join('users as u', 'u.id', '=', 'c.user_id'));
            $school && $q->where('u.school', $school);
            $grade && $q->where('u.grade', $grade);
            $consent = $q->selectRaw('SUM(c.accepted=1) yes, SUM(c.accepted=0) no')->first();
        }
        AccessLog::recordHourly('view_report', $school);

        return view('admin.learning-analytics.report', [
            'a' => $a, 'e' => $e, 'eq' => $eq, 'ml' => $ml, 'risk' => $risk, 'consent' => $consent,
            'filters' => ['school' => $school, 'grade' => $grade], 'schoolOptions' => array_column($rep['schools'], 'school'),
            'scopeLabel' => Cohort::scopeLabel(), 'excluded' => Cohort::excludedSummary(), 'generated' => now(),
        ]);
    }
}
