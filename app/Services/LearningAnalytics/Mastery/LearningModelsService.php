<?php

namespace App\Services\LearningAnalytics\Mastery;

use App\Models\User;
use App\Services\Analytics\SchoolLearningAnalytics;
use App\Services\LearningAnalytics\Engagement\DaysLoader;
use App\Services\LearningAnalytics\Engagement\EngagementMetrics;
use App\Services\LearningAnalytics\Srl\BehaviorClusters;
use App\Services\LearningAnalytics\Srl\SrlFeatures;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Loader DB untuk Fase 3: membaca learning_events → BKT (penguasaan), fitur SRL, klaster profil.
 * Hasil ditulis ke la_bkt_params, la_mastery, la_srl_student, la_model_runs (diganti seluruhnya tiap run).
 */
class LearningModelsService
{
    public const VERSION = 'v1';

    private const CHUNK = 300;

    public function __construct(private readonly SchoolLearningAnalytics $base)
    {
    }

    public function run(): array
    {
        $ids = \App\Services\LearningAnalytics\Cohort::students()->pluck('id')->all();
        $comp = $this->competencyMap();
        $lessonName = DB::table('lessons')->pluck('title', 'id')->map(fn ($t) => trim((string) $t))->all();

        $obs = [];      // [kompetensi][user] => [0/1,...]
        $srl = [];
        $events = 0;
        $feat = new SrlFeatures();
        foreach (array_chunk($ids, self::CHUNK) as $chunk) {
            $rows = [];
            foreach (DB::table('learning_events')
                ->whereIn('user_id', $chunk)
                ->whereIn('event_type', ['select', 'revise', 'hint_request'])
                ->whereNotNull('question_id')
                ->orderBy('id')
                ->get(['id', 'user_id', 'quiz_session_id', 'skill', 'question_id', 'lesson_id', 'event_type', 'is_correct', 'attempt_no', 'hint_level_shown', 'response_ms', 'created_at']) as $e) {
                $events++;
                $q = $e->skill . ':' . $e->question_id;
                $rows[] = [
                    'user' => (int) $e->user_id, 'session' => $e->quiz_session_id, 'q' => $q, 'type' => $e->event_type,
                    'correct' => $e->is_correct === null ? null : (int) (bool) $e->is_correct, 'attempt' => (int) $e->attempt_no,
                    'hint' => $e->hint_level_shown === null ? null : (int) $e->hint_level_shown,
                    'ms' => $e->response_ms === null ? null : (int) $e->response_ms, 'ts' => strtotime((string) $e->created_at),
                ];
                if ($e->event_type !== 'hint_request' && (int) $e->attempt_no === 1 && $e->is_correct !== null) {
                    $obs[$this->competencyOf($comp[$q] ?? null, $e->skill, $e->lesson_id ? ($lessonName[(int) $e->lesson_id] ?? null) : null)][(int) $e->user_id][] = (int) (bool) $e->is_correct;
                }
            }
            $srl += $feat->compute($rows);
        }

        $mastery = (new MasteryAnalyzer())->analyze($obs);

        $gain = [];
        foreach ($this->base->report(null, null)['students'] as $s) {
            $gain[$s['id']] = $s['gain'];
        }
        $clusters = (new BehaviorClusters())->run($srl, $this->regularity($ids), $gain);

        $now = now();
        DB::transaction(function () use ($mastery, $srl, $clusters, $now, $events) {
            DB::table('la_mastery')->delete();
            DB::table('la_bkt_params')->delete();
            DB::table('la_srl_student')->delete();
            foreach ($mastery['competencies'] as $c) {
                DB::table('la_bkt_params')->insert([
                    'competency' => $c['competency'], 'l0' => $c['params']['l0'], 't' => $c['params']['t'], 's' => $c['params']['s'], 'g' => $c['params']['g'],
                    'log_likelihood' => $c['ll'], 'll_baseline' => $c['ll_baseline'], 'reliable' => $c['reliable'], 'at_bound' => json_encode($c['at_bound']), 'students' => $c['students'], 'n_obs' => $c['n_obs'], 'is_default' => $c['default'],
                    'mastered' => $c['mastered'], 'wheel_spinning' => $c['wheel_spinning'], 'curve' => json_encode($c['curve']), 'computed_at' => $now,
                ]);
            }
            foreach (array_chunk($mastery['states'], 500) as $chunk) {
                DB::table('la_mastery')->insert(array_map(fn ($s) => [
                    'user_id' => $s['user'], 'competency' => $s['competency'], 'p_mastery' => $s['p'], 'n_obs' => $s['n_obs'],
                    'mastered' => $s['mastered'], 'wheel_spinning' => $s['wheel_spinning'], 'reliable' => $s['reliable'],
                    'opportunities_to_mastery' => $s['opportunities_to_mastery'], 'computed_at' => $now], $chunk));
            }
            $labels = [];
            foreach ($clusters['clusters'] as $c) {
                $labels[$c['id']] = $c['label'];
            }
            $rows = [];
            foreach ($srl as $uid => $f) {
                $rows[] = [
                    'user_id' => $uid, 'items' => $f['items'], 'first_try_acc' => $f['first_try_acc'], 'revision_rate' => $f['revision_rate'],
                    'hint_rate' => $f['hint_rate'], 'hint_l3_rate' => $f['hint_l3_rate'], 'median_ms' => $f['median_ms'],
                    'fast_wrong_rate' => $f['fast_wrong_rate'], 'hint_chain_rate' => $f['hint_chain_rate'],
                    'struggle_productive' => $f['struggle']['productive'], 'struggle_assisted' => $f['struggle']['assisted'],
                    'struggle_unproductive' => $f['struggle']['unproductive'], 'struggle_no_effort' => $f['struggle']['no_effort'],
                    'gaming_flag' => $f['gaming_flag'], 'enough' => $f['enough'],
                    'profile' => isset($clusters['assign'][$uid]) ? ($labels[$clusters['assign'][$uid]] ?? null) : null, 'computed_at' => $now,
                ];
            }
            foreach (array_chunk($rows, 500) as $chunk) {
                DB::table('la_srl_student')->insert($chunk);
            }
            DB::table('la_model_runs')->insert([
                ['kind' => 'bkt', 'version' => self::VERSION, 'params' => json_encode(['mastery_threshold' => 0.95, 'wheel_attempts' => 10, 'grid' => 'l0,t,s,g']),
                    'metrics' => json_encode(['competencies' => count($mastery['competencies']), 'events' => $events]), 'n' => count($mastery['states']), 'computed_at' => $now],
                ['kind' => 'cluster', 'version' => self::VERSION, 'params' => json_encode(['features' => $clusters['features']]),
                    'metrics' => json_encode(['k' => $clusters['k'], 'silhouette' => $clusters['silhouette'], 'clusters' => $clusters['clusters'], 'correlations' => $clusters['correlations']]),
                    'n' => $clusters['n'], 'computed_at' => $now],
            ]);
        });

        return ['events' => $events, 'competencies' => count($mastery['competencies']), 'states' => count($mastery['states']),
            'srl_students' => count($srl), 'clustered' => $clusters['n'], 'k' => $clusters['k']];
    }

    /** Kompetensi: sub-keterampilan butir bila ada; jika kosong ('umum'), pakai judul pelajaran; terakhir 'skill:umum'. */
    private function competencyOf(?string $mapped, string $skill, ?string $lesson): string
    {
        if ($mapped !== null && ! str_ends_with($mapped, ':umum')) {
            return $mapped;
        }

        return $lesson ? mb_substr($skill . ':' . $lesson, 0, 100) : ($mapped ?? $skill . ':umum');
    }

    /** @return array<string,string> 'skill:question_id' => 'skill:sub_skill' */
    private function competencyMap(): array
    {
        $map = [];
        foreach (['reading' => 'reading_questions', 'listening' => 'listening_questions'] as $skill => $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'sub_skill')) {
                foreach (DB::table($table)->get(['id', 'sub_skill']) as $r) {
                    $map[$skill . ':' . $r->id] = $skill . ':' . (trim((string) $r->sub_skill) ?: 'umum');
                }
            }
        }
        if (Schema::hasTable('vocabulary_pretests') && Schema::hasColumn('vocabulary_pretests', 'category')) {
            foreach (DB::table('vocabulary_pretests')->get(['id', 'category']) as $r) {
                $map['vocabulary:' . $r->id] = 'vocabulary:' . (trim((string) $r->category) ?: 'umum');
            }
        }

        return $map;
    }

    /** @return array<int,?float> user => indeks keteraturan (dari la_daily_student bila ada) */
    private function regularity(array $ids): array
    {
        if (! Schema::hasTable('la_daily_student')) {
            return [];
        }
        $today = new DateTimeImmutable('today');
        $em = new EngagementMetrics();
        $out = [];
        foreach (DaysLoader::load($ids, $today->modify('-40 days')->format('Y-m-d')) as $uid => $d) {
            $out[$uid] = $em->student($d, $today)['regularity_index'];
        }

        return $out;
    }
}
