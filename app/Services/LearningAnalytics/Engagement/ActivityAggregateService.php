<?php

namespace App\Services\LearningAnalytics\Engagement;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Loader DB tipis: membaca jejak aktivitas dari tabel yang sudah ada,
 * lalu menulis agregat harian ke la_daily_student (upsert, idempoten).
 *
 * Sumber jejak waktu (hanya yang tersedia di skema):
 *  learning_events.created_at · assessment_submissions.submitted_at (cadangan created_at)
 *  · user_lesson_progress.completed_at · gamification_transactions.created_at
 * Login/page-view TIDAK tercatat (lihat docs/learning-analytics/00-audit.md).
 */
class ActivityAggregateService
{
    private const CHUNK = 200;

    /** @return array{students:int,rows:int} */
    public function run(Carbon $since, ?int $onlyUser = null): array
    {
        $ids = \App\Services\LearningAnalytics\Cohort::students()
            ->when($onlyUser, fn ($q) => $q->where('id', $onlyUser))
            ->pluck('id')->all();

        $rows = 0;
        foreach (array_chunk($ids, self::CHUNK) as $chunk) {
            $ts = array_fill_keys($chunk, []);
            $learn = array_fill_keys($chunk, []);
            $test = array_fill_keys($chunk, []);
            $this->collect($learn, 'learning_events', 'created_at', $chunk, $since);
            $this->collect($learn, 'user_lesson_progress', 'completed_at', $chunk, $since);
            $this->collectSubmissions($learn, $test, $chunk, $since);
            $gam = array_fill_keys($chunk, []);
            $this->collect($gam, 'gamification_transactions', 'created_at', $chunk, $since);
            foreach ($chunk as $uid) {
                $ts[$uid] = array_merge($learn[$uid], $test[$uid], $gam[$uid]);
            }
            $split = Schema::hasColumn('la_daily_student', 'learn_events');
            $quiz = $this->quizCounts($chunk, $since);

            $batch = [];
            $now = now();
            $count = fn (array $l) => array_count_values(array_map(fn ($t) => date('Y-m-d', $t), $l));
            $lc = array_map($count, $learn);
            $tc = array_map($count, $test);
            foreach ($ts as $uid => $list) {
                foreach (DailyAggregator::days($list) as $date => $d) {
                    if ($date < $since->format('Y-m-d')) {
                        continue;
                    }
                    $q = $quiz[$uid][$date] ?? ['a' => 0, 'c' => 0, 'h1' => 0, 'h2' => 0, 'h3' => 0];
                    $batch[] = [
                        'user_id' => $uid, 'date' => $date,
                        'sessions' => $d['sessions'], 'active_seconds' => $d['active_seconds'],
                        'events' => $d['events'], 'peak_hour' => $d['peak_hour'],
                        'learn_events' => $split ? ($lc[$uid][$date] ?? 0) : null, 'test_events' => $split ? ($tc[$uid][$date] ?? 0) : null,
                        'quiz_answers' => $q['a'], 'quiz_correct' => $q['c'],
                        'hint_l1' => $q['h1'], 'hint_l2' => $q['h2'], 'hint_l3' => $q['h3'],
                        'computed_at' => $now,
                    ];
                }
            }
            $upd = ['sessions', 'active_seconds', 'events', 'peak_hour', 'quiz_answers', 'quiz_correct', 'hint_l1', 'hint_l2', 'hint_l3', 'computed_at'];
            if ($split) {
                array_push($upd, 'learn_events', 'test_events');
            } else {
                $batch = array_map(fn ($r) => array_diff_key($r, ['learn_events' => 1, 'test_events' => 1]), $batch);
            }
            foreach (array_chunk($batch, 500) as $b) {
                DB::table('la_daily_student')->upsert($b, ['user_id', 'date'], $upd);
            }
            $rows += count($batch);
            $this->pageViews($chunk, $since);
        }
        $this->prune();

        return ['students' => count($ids), 'rows' => $rows];
    }

    /** Kunjungan halaman per hari → la_daily_student.page_views (membuat baris kosong untuk hari yang hanya berisi kunjungan). */
    private function pageViews(array $ids, Carbon $since): void
    {
        if (! Schema::hasTable('la_page_views') || ! Schema::hasColumn('la_daily_student', 'page_views')) {
            return;
        }
        $rows = DB::table('la_page_views')->whereIn('user_id', $ids)->where('created_at', '>=', $since)
            ->selectRaw('user_id, DATE(created_at) d, COUNT(*) c')->groupBy('user_id', 'd')->get();
        // hari yang tidak lagi punya kunjungan: nolkan agar idempoten setelah pemangkasan
        DB::table('la_daily_student')->whereIn('user_id', $ids)->where('date', '>=', $since->format('Y-m-d'))->where('page_views', '>', 0)->update(['page_views' => 0]);
        $now = now();
        $batch = $rows->map(fn ($r) => ['user_id' => (int) $r->user_id, 'date' => $r->d, 'sessions' => 0, 'active_seconds' => 0, 'events' => 0, 'peak_hour' => 0,
            'learn_events' => 0, 'test_events' => 0, 'page_views' => min(65535, (int) $r->c), 'computed_at' => $now])->all();
        $cols = ['page_views'];
        if (! Schema::hasColumn('la_daily_student', 'learn_events')) {
            $batch = array_map(fn ($r) => array_diff_key($r, ['learn_events' => 1, 'test_events' => 1]), $batch);
        }
        foreach (array_chunk($batch, 500) as $b) {
            DB::table('la_daily_student')->upsert($b, ['user_id', 'date'], $cols);
        }
    }

    private function prune(): void
    {
        if (Schema::hasTable('la_page_views')) {
            DB::table('la_page_views')->where('created_at', '<', now()->subDays((int) config('learning_analytics.page_log.retention_days', 180)))->delete();
        }
    }

    /** assessment_submissions: pretest/posttest → $test; type 'unit' → $learn. */
    private function collectSubmissions(array &$learn, array &$test, array $ids, Carbon $since): void
    {
        if (! Schema::hasTable('assessment_submissions')) {
            return;
        }
        $from = $since->copy()->subDay();
        $q = DB::table('assessment_submissions')->whereIn('user_id', $ids)->whereRaw('COALESCE(submitted_at, created_at) >= ?', [$from]);
        foreach ($q->select('user_id', 'type', DB::raw('COALESCE(submitted_at, created_at) as ts'))->cursor() as $r) {
            $t = strtotime((string) $r->ts);
            if (! $t) {
                continue;
            }
            if (in_array($r->type, ['pretest', 'posttest'], true)) {
                $test[$r->user_id][] = $t;
            } else {
                $learn[$r->user_id][] = $t;
            }
        }
    }

    /** @param array<int,int[]> $ts */
    private function collect(array &$ts, string $table, $col, array $ids, Carbon $since): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }
        $isRaw = $col instanceof \Illuminate\Contracts\Database\Query\Expression;
        if (! $isRaw && ! Schema::hasColumn($table, $col)) {
            return;
        }
        // mundur 1 hari agar sesi lintas tengah malam tidak terpotong di batas $since
        $from = $since->copy()->subDay();
        $q = DB::table($table)->whereIn('user_id', $ids);
        $isRaw ? $q->whereRaw('COALESCE(submitted_at, created_at) >= ?', [$from]) : $q->where($col, '>=', $from)->whereNotNull($col);
        foreach ($q->select('user_id', DB::raw(($isRaw ? 'COALESCE(submitted_at, created_at)' : $col) . ' as ts'))->cursor() as $r) {
            $t = strtotime((string) $r->ts);
            if ($t) {
                $ts[$r->user_id][] = $t;
            }
        }
    }

    /** @return array<int,array<string,array{a:int,c:int,h1:int,h2:int,h3:int}>> */
    private function quizCounts(array $ids, Carbon $since): array
    {
        $out = [];
        if (! Schema::hasTable('learning_events')) {
            return $out;
        }
        $rows = DB::table('learning_events')
            ->whereIn('user_id', $ids)->where('created_at', '>=', $since)
            ->selectRaw("user_id, DATE(created_at) as d,
                SUM(CASE WHEN event_type IN ('select','revise') AND attempt_no = 1 THEN 1 ELSE 0 END) as a,
                SUM(CASE WHEN event_type IN ('select','revise') AND attempt_no = 1 AND is_correct = 1 THEN 1 ELSE 0 END) as c,
                SUM(CASE WHEN event_type = 'hint_request' AND hint_level_shown = 1 THEN 1 ELSE 0 END) as h1,
                SUM(CASE WHEN event_type = 'hint_request' AND hint_level_shown = 2 THEN 1 ELSE 0 END) as h2,
                SUM(CASE WHEN event_type = 'hint_request' AND hint_level_shown = 3 THEN 1 ELSE 0 END) as h3")
            ->groupBy('user_id', 'd')->get();
        foreach ($rows as $r) {
            $out[$r->user_id][(string) $r->d] = ['a' => (int) $r->a, 'c' => (int) $r->c, 'h1' => (int) $r->h1, 'h2' => (int) $r->h2, 'h3' => (int) $r->h3];
        }

        return $out;
    }
}
