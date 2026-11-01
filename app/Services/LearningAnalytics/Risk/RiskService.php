<?php

namespace App\Services\LearningAnalytics\Risk;

use App\Models\User;
use App\Services\Analytics\SchoolLearningAnalytics;
use App\Services\LearningAnalytics\Engagement\DaysLoader;
use App\Services\LearningAnalytics\Engagement\EngagementMetrics;
use App\Services\LearningAnalytics\Stats\Descriptive as D;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Loader DB untuk skor risiko aturan + model logit pendamping. Menulis la_risk_scores, la_risk_history, la_model_runs. */
class RiskService
{
    public function __construct(private readonly SchoolLearningAnalytics $base)
    {
    }

    public static function weights(): array
    {
        $w = config('learning_analytics.risk.weights', []);
        if (Schema::hasTable('la_settings')) {
            $row = DB::table('la_settings')->where('key', 'risk_weights')->value('value');
            if ($row) {
                $over = json_decode($row, true);
                if (is_array($over)) {
                    $w = array_replace($w, array_intersect_key(array_map('floatval', $over), $w));
                }
            }
        }

        return $w;
    }

    public function run(): array
    {
        $cfg = config('learning_analytics.risk');
        $today = new DateTimeImmutable('today');
        $students = $this->base->report(null, null)['students'];
        $ids = array_column($students, 'id');

        $days = DaysLoader::load($ids, $today->modify('-90 days')->format('Y-m-d'));
        $srl = Schema::hasTable('la_srl_student') ? DB::table('la_srl_student')->get()->keyBy('user_id') : collect();
        $wheel = Schema::hasTable('la_mastery') ? DB::table('la_mastery')->where('wheel_spinning', true)->selectRaw('user_id, COUNT(*) c')->groupBy('user_id')->pluck('c', 'user_id') : collect();

        $em = new EngagementMetrics();
        $progByClass = [];
        foreach ($students as $s) {
            $progByClass[$s['school'] . '|' . $s['major'] . '|' . $s['grade'] . '|' . $s['parallel']][] = $s['progress'];
        }
        $median = array_map(fn ($v) => D::median($v) ?? 0.0, $progByClass);
        $scorer = new RiskScorer(self::weights(), (float) $cfg['level_high'], (float) $cfg['level_medium']);
        $thr = (float) config('learning.intervention.inferential_wrong_threshold', 2);

        $out = [];
        $rowsModel = [];
        foreach ($students as $s) {
            $eng = $em->student($days[$s['id']] ?? [], $today);
            $sr = $srl[$s['id']] ?? null;
            $enough = $sr && $sr->enough;
            $feat = [
                'days_since_last' => $eng['days_since_last'], 'regularity_index' => $eng['regularity_index'], 'trend_slope' => $eng['trend_slope'],
                'hint_l3_rate' => $enough ? (float) $sr->hint_l3_rate : null, 'infer' => $s['infer'], 'progress' => (float) $s['progress'],
                'first_try_acc' => $enough ? (float) $sr->first_try_acc : null, 'wheel_n' => $enough || $wheel->has($s['id']) ? (int) ($wheel[$s['id']] ?? 0) : null,
            ];
            $key = $s['school'] . '|' . $s['major'] . '|' . $s['grade'] . '|' . $s['parallel'];
            $feat['started'] = $eng['days_since_last'] !== null || ($sr && (int) $sr->items >= (int) $cfg['started_min_items']);
            $r = $scorer->score($feat, $thr, $median[$key] ?? 0.0);
            $out[$s['id']] = $r + ['features' => $feat];
            if ($s['post'] !== null && $feat['started']) {
                $rowsModel[] = ['features' => $feat, 'y' => $s['post'] < $cfg['posttest_threshold'] ? 1 : 0, 'school' => $s['school'] ?: '(Belum diisi)'];
            }
        }

        // kalibrasi ambang pada siswa yang sudah mulai; level dihitung ulang dari skor
        $startedScores = array_values(array_map(fn ($r) => $r['score'], array_filter($out, fn ($r) => $r['level'] !== 'belum_mulai')));
        $thr2 = RiskScorer::calibrate($startedScores, $cfg['calibration'], (float) $cfg['level_high'], (float) $cfg['level_medium']);
        foreach ($out as $uid => $r) {
            if ($r['level'] !== 'belum_mulai') {
                $out[$uid]['level'] = RiskScorer::levelFor($r['score'], $thr2['high'], $thr2['medium']);
            }
        }
        $hist = array_fill(0, 10, 0);
        foreach ($startedScores as $sc) {
            $hist[min(9, (int) floor($sc / 10))]++;
        }
        if (Schema::hasTable('la_settings')) {
            DB::table('la_settings')->updateOrInsert(['key' => 'risk_thresholds'], ['value' => json_encode($thr2 + ['hist' => $hist, 'computed_at' => now()->toDateTimeString()]), 'updated_by' => null, 'updated_at' => now()]);
        }

        $model = new RiskModel((int) $cfg['min_students_model'], (int) $cfg['min_class_model'], (float) $cfg['min_auc_to_show']);
        $eval = $model->evaluate($rowsModel);
        $now = now();
        DB::transaction(function () use ($out, $eval, $model, $now, $today) {
            DB::table('la_risk_scores')->delete();
            $batch = [];
            foreach ($out as $uid => $r) {
                $batch[] = ['user_id' => $uid, 'score' => $r['score'], 'level' => $r['level'], 'confidence' => $r['confidence'], 'coverage' => $r['coverage'],
                    'factors' => json_encode($r['factors']), 'components' => json_encode($r['components']),
                    'model_prob' => ($eval['ready'] ?? false) ? round($model->predict($eval['model'], $r['features']), 4) : null, 'computed_at' => $now];
            }
            foreach (array_chunk($batch, 500) as $c) {
                DB::table('la_risk_scores')->insert($c);
            }
            $hist = array_map(fn ($b) => ['user_id' => $b['user_id'], 'date' => $today->format('Y-m-d'), 'score' => $b['score'], 'level' => $b['level']], $batch);
            foreach (array_chunk($hist, 500) as $c) {
                DB::table('la_risk_history')->upsert($c, ['user_id', 'date'], ['score', 'level']);
            }
            $metrics = $eval;
            unset($metrics['model']);
            DB::table('la_model_runs')->insert(['kind' => 'risk_logit', 'version' => 'v1', 'params' => json_encode(['features' => RiskModel::FEATURES, 'lambda' => 1.0]),
                'metrics' => json_encode($metrics), 'n' => $eval['n'], 'computed_at' => $now]);
        });
        $lv = array_count_values(array_column($out, 'level'));

        return ['students' => count($out), 'belum_mulai' => $lv['belum_mulai'] ?? 0, 'thresholds' => $thr2, 'tinggi' => $lv['tinggi'] ?? 0, 'sedang' => $lv['sedang'] ?? 0, 'rendah' => $lv['rendah'] ?? 0, 'model_ready' => (bool) ($eval['ready'] ?? false), 'auc' => $eval['auc'] ?? null];
    }
}
