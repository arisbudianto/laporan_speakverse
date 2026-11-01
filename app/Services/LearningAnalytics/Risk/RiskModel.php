<?php

namespace App\Services\LearningAnalytics\Risk;

use App\Services\LearningAnalytics\Stats\Descriptive as D;
use App\Services\LearningAnalytics\Stats\Logistic;
use App\Services\LearningAnalytics\Stats\Metrics;

/**
 * Model statistik pendamping (regresi logistik ridge) untuk memperkirakan "posttest di bawah ambang".
 * Evaluasi: k-fold berstrata (standardisasi dan imputasi dihitung di data latih tiap lipatan), AUC, presisi/recall/F1 pada 0,5,
 * Brier, kalibrasi 10 kotak, dan KEADILAN per sekolah (AUC dan recall per sekolah dengan ≥ minGroup siswa dan kedua kelas ada).
 * Model hanya DITAMPILKAN bila n ≥ minStudents, tiap kelas ≥ minClass, dan AUC-CV ≥ minAuc.
 * CATATAN KETERBATASAN: fitur diambil dari keadaan terbaru, bukan potret sebelum posttest, sehingga ada risiko kebocoran
 * waktu; evaluasi pemisahan-waktu belum dapat dilakukan sampai potret berkala disimpan.
 */
final class RiskModel
{
    public const FEATURES = ['days_since_last', 'regularity_index', 'trend_slope', 'hint_l3_rate', 'infer', 'progress', 'first_try_acc', 'wheel_n'];

    public function __construct(
        private int $minStudents = 60, private int $minClass = 15, private float $minAuc = 0.70,
        private int $folds = 5, private float $lambda = 1.0, private int $minGroup = 10,
    ) {
    }

    /**
     * @param array<int,array{features:array,y:int,school:string}> $rows
     */
    public function evaluate(array $rows): array
    {
        $n = count($rows);
        $pos = array_sum(array_column($rows, 'y'));
        $res = ['n' => $n, 'positives' => $pos, 'ready' => false, 'reason' => null, 'auc' => null];
        if ($n < $this->minStudents || min($pos, $n - $pos) < $this->minClass) {
            $res['reason'] = "Data belum cukup (butuh ≥{$this->minStudents} siswa dan ≥{$this->minClass} per kelas; ada n=$n, positif=$pos).";

            return $res;
        }
        $rows = array_values($rows);
        $y = array_column($rows, 'y');
        $fold = Metrics::stratifiedFolds($y, $this->folds);
        $oof = array_fill(0, $n, null);
        for ($f = 0; $f < $this->folds; $f++) {
            $tr = $te = [];
            foreach ($fold as $i => $fi) {
                if ($fi === $f) {
                    $te[] = $i;
                } else {
                    $tr[] = $i;
                }
            }
            [$xtr, $imp] = $this->matrix(array_map(fn ($i) => $rows[$i]['features'], $tr));
            $sc = Logistic::scaler($xtr);
            $m = Logistic::fit(Logistic::apply($sc, $xtr), array_map(fn ($i) => $y[$i], $tr), $this->lambda);
            foreach ($te as $i) {
                $x = Logistic::apply($sc, [$this->vector($rows[$i]['features'], $imp)])[0];
                $oof[$i] = Logistic::predict($m, $x);
            }
        }
        $auc = Metrics::auc($y, $oof);
        $res += ['auc_cv' => $auc, 'brier' => Metrics::brier($y, $oof), 'prf' => Metrics::prf($y, $oof, 0.5), 'calibration' => Metrics::calibration($y, $oof), 'folds' => $this->folds];
        $res['auc'] = $auc;

        $bySchool = [];
        foreach ($rows as $i => $r) {
            $bySchool[$r['school']][] = $i;
        }
        $fair = [];
        foreach ($bySchool as $school => $idx) {
            $ys = array_map(fn ($i) => $y[$i], $idx);
            $ps = array_map(fn ($i) => $oof[$i], $idx);
            $np = array_sum($ys);
            $ok = count($idx) >= $this->minGroup && $np > 0 && $np < count($idx);
            $prf = $ok ? Metrics::prf($ys, $ps, 0.5) : null;
            $fair[] = ['school' => $school, 'n' => count($idx), 'positives' => $np, 'auc' => $ok ? Metrics::auc($ys, $ps) : null,
                'recall' => $prf['recall'] ?? null, 'precision' => $prf['precision'] ?? null];
        }
        usort($fair, fn ($a, $b) => strcmp($a['school'], $b['school']));
        $res['fairness'] = $fair;
        $aucs = array_filter(array_column($fair, 'auc'), fn ($v) => $v !== null);
        $res['fairness_gap'] = count($aucs) >= 2 ? max($aucs) - min($aucs) : null;

        [$xall, $imp] = $this->matrix(array_column($rows, 'features'));
        $sc = Logistic::scaler($xall);
        $model = Logistic::fit(Logistic::apply($sc, $xall), $y, $this->lambda);
        $res['coefficients'] = array_combine(self::FEATURES, array_map(fn ($w) => round($w, 3), $model['w']));
        $res['intercept'] = round($model['b0'], 3);
        $res['model'] = ['b0' => $model['b0'], 'w' => $model['w'], 'scaler' => $sc, 'impute' => $imp];
        $res['ready'] = $auc !== null && $auc >= $this->minAuc;
        if (! $res['ready']) {
            $res['reason'] = 'AUC validasi silang ' . ($auc === null ? '–' : round($auc, 2)) . ' di bawah ' . $this->minAuc . ': model tidak ditampilkan.';
        }

        return $res;
    }

    /** Peluang dari model yang sudah dilatih. */
    public function predict(array $trained, array $features): float
    {
        $x = Logistic::apply($trained['scaler'], [$this->vector($features, $trained['impute'])])[0];

        return Logistic::predict($trained, $x);
    }

    /** @return array{0:float[][],1:float[]} matriks terimputasi + rerata imputasi */
    private function matrix(array $feats): array
    {
        $imp = [];
        foreach (self::FEATURES as $j => $name) {
            $vals = array_filter(array_map(fn ($f) => $f[$name] ?? null, $feats), fn ($v) => $v !== null);
            $imp[$j] = $vals ? D::mean(array_values($vals)) : 0.0;
        }

        return [array_map(fn ($f) => $this->vector($f, $imp), $feats), $imp];
    }

    private function vector(array $f, array $imp): array
    {
        $v = [];
        foreach (self::FEATURES as $j => $name) {
            $x = $f[$name] ?? null;
            $v[] = $x === null ? $imp[$j] : (float) $x;
        }

        return $v;
    }
}
