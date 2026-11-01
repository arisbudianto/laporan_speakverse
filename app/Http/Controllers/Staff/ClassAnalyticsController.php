<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Learning\DiagnosticEngine;
use App\Services\LearningAnalytics\AccessLog;
use App\Services\LearningAnalytics\Engagement\EngagementMetrics;
use App\Services\LearningAnalytics\Guidance\RemedialRecommender;
use DateTimeImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Guru: analitik kelas (peta penguasaan, perlu dicek + alasan, profil perilaku, remedial, tindak lanjut). */
class ClassAnalyticsController extends Controller
{
    public function show(Request $request, DiagnosticEngine $diagnostic): View
    {
        $class = $this->validatedClass($request);
        $this->authorizeClass($class);
        $ready = Schema::hasTable('la_risk_scores') && Schema::hasTable('la_mastery');
        $students = \App\Services\LearningAnalytics\Cohort::students()->where('school', $class['school'])->where('major', $class['major'])->where('grade', $class['grade'])->where('parallel', $class['parallel'])
            ->orderBy('name')->get(['id', 'name']);
        $ids = $students->pluck('id')->all();
        $data = ['class' => $class, 'ready' => $ready, 'students' => $students];
        if ($ready && $ids) {
            $comps = DB::table('la_bkt_params')->orderBy('competency')->pluck('competency')->all();
            $mastery = [];
            foreach (DB::table('la_mastery')->whereIn('user_id', $ids)->when(Schema::hasColumn('la_mastery', 'reliable'), fn ($q) => $q->where('reliable', true))->get(['user_id', 'competency', 'p_mastery', 'wheel_spinning']) as $m) {
                $mastery[$m->user_id][$m->competency] = ['p' => (float) $m->p_mastery, 'wheel' => (bool) $m->wheel_spinning];
            }
            $compStats = [];
            foreach ($comps as $c) {
                $ps = [];
                $mast = 0;
                foreach ($mastery as $row) {
                    if (isset($row[$c])) {
                        $ps[] = $row[$c]['p'];
                        $mast += $row[$c]['p'] >= 0.95 ? 1 : 0;
                    }
                }
                if ($ps) {
                    $compStats[] = ['competency' => $c, 'students' => count($ps), 'mean_p' => array_sum($ps) / count($ps), 'mastered_pct' => 100 * $mast / count($ps)];
                }
            }
            $risk = DB::table('la_risk_scores')->whereIn('user_id', $ids)->get()->keyBy('user_id')->map(function ($r) {
                $r->factors = json_decode($r->factors ?? '[]', true) ?: [];

                return $r;
            });
            $srl = DB::table('la_srl_student')->whereIn('user_id', $ids)->get()->keyBy('user_id');
            $today = new DateTimeImmutable('today');
            $em = new EngagementMetrics();
            $days = \App\Services\LearningAnalytics\Engagement\DaysLoader::load($ids, $today->modify('-40 days')->format('Y-m-d'));
            $eng = [];
            foreach ($ids as $id) {
                $eng[$id] = $em->student($days[$id] ?? [], $today);
            }
            $followed = DB::table('la_interventions as i')->leftJoin('users as t', 't.id', '=', 'i.actor_id')->whereIn('i.user_id', $ids)->orderByDesc('i.followed_at')
                ->get(['i.user_id', 'i.action', 'i.note', 'i.followed_at', 't.name as teacher'])->groupBy('user_id');
            $errors = $diagnostic->errorFrequency($ids, 'reading');
            $data += [
                'comps' => $comps, 'mastery' => $mastery, 'risk' => $risk, 'srl' => $srl, 'eng' => $eng, 'followed' => $followed,
                'remedial' => (new RemedialRecommender())->recommend($compStats, $errors), 'patternLabels' => EngagementMetrics::PATTERN_LABELS,
            ];
            AccessLog::recordHourly('view_class_analytics', implode('|', $class), ['students' => count($ids)]);
        }

        return view('staff.classes.analytics', $data);
    }

    public function storeIntervention(Request $request): RedirectResponse
    {
        $v = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'], 'action' => ['required', Rule::in(['hubungi_siswa', 'jadwal_belajar', 'atur_bantuan_ai', 'remedial', 'pendampingan', 'lainnya'])],
            'note' => ['nullable', 'string', 'max:500'],
        ]);
        $student = User::findOrFail($v['user_id']);
        abort_unless(in_array($student->role, ['student', null, 'user'], true), 404);
        $this->authorizeClass(['school' => $student->school, 'major' => $student->major, 'grade' => $student->grade, 'parallel' => $student->parallel]);
        $level = DB::table('la_risk_scores')->where('user_id', $student->id)->value('level');
        DB::table('la_interventions')->insert(['user_id' => $student->id, 'actor_id' => Auth::id(), 'risk_level' => $level, 'action' => $v['action'], 'note' => $v['note'] ?? null,
            'followed_at' => now(), 'created_at' => now(), 'updated_at' => now()]);
        AccessLog::record('mark_followed_up', (string) $student->id, ['action' => $v['action']]);

        return back()->with('status', 'Tindak lanjut untuk ' . $student->name . ' dicatat.');
    }

    private function validatedClass(Request $request): array
    {
        $schools = config('schools', []);

        return $request->validate([
            'school' => ['required', Rule::in(array_keys($schools))],
            'major' => ['required', Rule::in($schools[$request->school] ?? [])],
            'grade' => ['required', Rule::in(['X', 'XI', 'XII'])],
            'parallel' => ['required', Rule::in(['A', 'B', 'C', 'D'])],
        ]);
    }

    private function authorizeClass(array $class): void
    {
        $user = Auth::user();
        if ($user->role === 'admin') {
            return;
        }
        abort_unless($user->teachingAssignments()->where('school', $class['school'])->where('major', $class['major'])
            ->where('grade', $class['grade'])->where('parallel', $class['parallel'])->exists(), 403);
    }
}
