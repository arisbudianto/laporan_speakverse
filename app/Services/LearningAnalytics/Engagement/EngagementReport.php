<?php

namespace App\Services\LearningAnalytics\Engagement;

use App\Models\User;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Membaca la_daily_student → laporan keterlibatan per sekolah (perhitungan di EngagementMetrics). */
class EngagementReport
{
    public function __construct(private readonly EngagementMetrics $metrics)
    {
    }

    public function build(?string $school, ?string $grade, ?DateTimeImmutable $today = null): array
    {
        $today = ($today ?? new DateTimeImmutable('today'))->setTime(0, 0);
        if (! Schema::hasTable('la_daily_student')) {
            return ['ready' => false];
        }
        $students = \App\Services\LearningAnalytics\Cohort::students()
            ->when($grade, fn ($q) => $q->where('grade', $grade))
            ->get(['id', 'name', 'school', 'major', 'grade', 'parallel'])
            ->map(fn ($u) => ['id' => $u->id, 'name' => $u->name, 'school' => $u->school ?: '(Belum diisi)',
                'major' => $u->major, 'grade' => $u->grade, 'parallel' => $u->parallel])->all();

        $ids = array_column($students, 'id');
        $days = DaysLoader::load($ids, $today->modify('-90 days')->format('Y-m-d'));
        $computed = DB::table('la_daily_student')->max('computed_at');

        return $this->assemble($students, $days, $school, $today) + ['ready' => true, 'computed_at' => $computed];
    }

    /** Murni (diuji): students[] + days[uid][date] → laporan. */
    public function assemble(array $students, array $days, ?string $school, DateTimeImmutable $today): array
    {
        $per = [];
        foreach ($students as $s) {
            $per[$s['id']] = $this->metrics->student($days[$s['id']] ?? [], $today) + ['student' => $s];
        }
        $bySchool = [];
        foreach ($per as $p) {
            $bySchool[$p['student']['school']][] = $p;
        }
        ksort($bySchool);
        $schools = [];
        foreach ($bySchool as $name => $list) {
            $schools[] = ['school' => $name] + $this->metrics->cohort($list);
        }
        $scope = $school === null ? array_values($per) : ($bySchool[$school] ?? []);
        $scopeDays = [];
        foreach ($scope as $p) {
            $scopeDays[] = $days[$p['student']['id']] ?? [];
        }
        $attention = array_values(array_filter($scope, fn ($p) => in_array($p['pattern'], ['berhenti', 'menurun', 'menumpuk'], true)));
        usort($attention, fn ($a, $b) => [$b['days_since_last'] ?? 0] <=> [$a['days_since_last'] ?? 0]);

        return [
            'scope_school' => $school,
            'overview' => $this->metrics->cohort($scope),
            'schools' => $schools,
            'weekly' => $this->metrics->weeklyActive($scopeDays, $today, 8),
            'attention' => array_slice($attention, 0, 20),
            'students' => $scope,
        ];
    }
}
