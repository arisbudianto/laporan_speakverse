<?php

namespace App\Services\Analytics;

use App\Models\LearningEvent;
use App\Models\Lesson;
use App\Models\User;
use App\Services\Learning\DiagnosticEngine;
use Illuminate\Support\Facades\DB;

/**
 * Memuat data dari database lalu menyerahkan perhitungan ke
 * SchoolLearningMetrics. Semua query bersifat "set-based" (per kelompok
 * id, bukan per siswa) supaya tetap ringan untuk banyak sekolah.
 */
class SchoolLearningAnalytics
{
    private const CHUNK = 800;

    public function __construct(
        private readonly SchoolLearningMetrics $metrics,
        private readonly DiagnosticEngine $diagnosticEngine,
    ) {
    }

    public function report(?string $school = null, ?string $grade = null): array
    {
        return $this->metrics->compute($this->load(), $school, $grade);
    }

    public function load(): array
    {
        $students = \App\Services\LearningAnalytics\Cohort::students()
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'school', 'major', 'grade', 'parallel', 'xp', 'last_activity_date'])
            ->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'school' => $u->school,
                'major' => $u->major,
                'grade' => $u->grade,
                'parallel' => $u->parallel,
                'xp' => (int) $u->xp,
                'last_activity' => $u->last_activity_date?->format('Y-m-d'),
            ])
            ->all();

        $ids = array_column($students, 'id');

        $subs = [];
        $completed = [];
        $infer = [];
        $l3 = [];

        foreach (array_chunk($ids, self::CHUNK) as $chunk) {
            foreach (DB::table('assessment_submissions')
                ->whereIn('user_id', $chunk)
                ->orderBy('id')
                ->get(['id', 'user_id', 'type', 'skill', 'final_score', 'submitted_at', 'created_at']) as $row) {
                $subs[] = [
                    'id' => $row->id,
                    'user_id' => $row->user_id,
                    'type' => $row->type,
                    'skill' => $row->skill,
                    'final_score' => $row->final_score,
                    'when' => $row->submitted_at ?? $row->created_at,
                ];
            }

            foreach (DB::table('user_lesson_progress')
                ->whereIn('user_id', $chunk)
                ->where(function ($q) {
                    $q->whereNull('status')->orWhere('status', '')->orWhere('status', 'completed');
                })
                ->selectRaw('user_id, count(*) as total')
                ->groupBy('user_id')
                ->pluck('total', 'user_id') as $uid => $total) {
                $completed[$uid] = (int) $total;
            }

            // Kriteria sama dengan dasbor guru (ClassResultsController::buildReport).
            foreach (LearningEvent::query()
                ->whereIn('user_id', $chunk)
                ->whereIn('skill', ['reading', 'vocabulary', 'listening'])
                ->whereIn('event_type', ['select', 'revise'])
                ->where('attempt_no', 1)
                ->where('is_correct', false)
                ->where('error_code', 'inferential')
                ->selectRaw('user_id, count(*) as total')
                ->groupBy('user_id')
                ->pluck('total', 'user_id') as $uid => $total) {
                $infer[$uid] = (int) $total;
            }

            foreach (LearningEvent::query()
                ->whereIn('user_id', $chunk)
                ->where('event_type', 'hint_request')
                ->where('hint_level_shown', 3)
                ->selectRaw('user_id, count(*) as total')
                ->groupBy('user_id')
                ->pluck('total', 'user_id') as $uid => $total) {
                $l3[$uid] = (int) $total;
            }
        }

        return [
            'students' => $students,
            'subs' => $subs,
            'completed' => $completed,
            'total_lessons' => Lesson::query()->count(),
            'infer' => $infer,
            'l3' => $l3,
            'inferential_threshold' => (int) config('learning.intervention.inferential_wrong_threshold', 2),
            'hint_l3_threshold' => (int) config('learning.intervention.hint_l3_threshold', 2),
            'school_names' => array_keys(config('schools', [])),
            'error_loader' => function (array $userIds): array {
                $total = [];
                foreach (array_chunk($userIds, self::CHUNK) as $chunk) {
                    foreach ($this->diagnosticEngine->errorFrequency($chunk, 'reading') as $code => $n) {
                        $total[$code] = ($total[$code] ?? 0) + (int) $n;
                    }
                }

                return $total;
            },
        ];
    }
}
