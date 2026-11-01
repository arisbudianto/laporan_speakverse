<?php

namespace App\Services\LearningAnalytics\Stats;

/**
 * Metrik evaluasi klasifikasi biner.
 *  auc    : statistik Mann–Whitney  AUC = (R₁ − n₁(n₁+1)/2)/(n₁·n₀), R₁ = jumlah peringkat (rata-rata untuk ikatan) skor kelas 1
 *  brier  : rerata (p − y)²
 *  calibration : per kotak peluang (10 kotak) → peluang rerata vs proporsi positif sebenarnya
 *  prf    : presisi, recall, F1 pada ambang tertentu
 *  stratifiedFolds : lipatan k-fold berstrata dan deterministik (round-robin setelah pengurutan stabil)
 */
final class Metrics
{
    public static function auc(array $y, array $score): ?float
    {
        $n1 = array_sum($y);
        $n0 = count($y) - $n1;
        if ($n1 === 0 || $n0 === 0) {
            return null;
        }
        $ranks = Inference::ranks(array_values($score));
        $r1 = 0.0;
        foreach (array_values($y) as $i => $yy) {
            if ($yy) {
                $r1 += $ranks[$i];
            }
        }

        return ($r1 - $n1 * ($n1 + 1) / 2) / ($n1 * $n0);
    }

    public static function brier(array $y, array $p): ?float
    {
        $n = count($y);

        return $n ? array_sum(array_map(fn ($a, $b) => ($b - $a) ** 2, $y, $p)) / $n : null;
    }

    /** @return array<int,array{lo:float,hi:float,n:int,pred:float,obs:float}> */
    public static function calibration(array $y, array $p, int $bins = 10): array
    {
        $out = [];
        for ($b = 0; $b < $bins; $b++) {
            $lo = $b / $bins;
            $hi = ($b + 1) / $bins;
            $idx = array_keys(array_filter($p, fn ($v) => $v >= $lo && ($v < $hi || ($b === $bins - 1 && $v <= 1))));
            if ($idx) {
                $out[] = ['lo' => $lo, 'hi' => $hi, 'n' => count($idx),
                    'pred' => array_sum(array_map(fn ($i) => $p[$i], $idx)) / count($idx),
                    'obs' => array_sum(array_map(fn ($i) => $y[$i], $idx)) / count($idx)];
            }
        }

        return $out;
    }

    /** @return array{precision:?float,recall:?float,f1:?float,tp:int,fp:int,fn:int,tn:int} */
    public static function prf(array $y, array $p, float $thr = 0.5): array
    {
        $tp = $fp = $fn = $tn = 0;
        foreach ($y as $i => $yy) {
            $pred = $p[$i] >= $thr;
            if ($pred && $yy) { $tp++; } elseif ($pred) { $fp++; } elseif ($yy) { $fn++; } else { $tn++; }
        }
        $pr = ($tp + $fp) ? $tp / ($tp + $fp) : null;
        $rc = ($tp + $fn) ? $tp / ($tp + $fn) : null;
        $f1 = ($pr !== null && $rc !== null && ($pr + $rc) > 0) ? 2 * $pr * $rc / ($pr + $rc) : null;

        return compact('tp', 'fp', 'fn', 'tn') + ['precision' => $pr, 'recall' => $rc, 'f1' => $f1];
    }

    /** @return int[] fold index per observasi */
    public static function stratifiedFolds(array $y, int $k): array
    {
        $y = array_values($y);
        $fold = array_fill(0, count($y), 0);
        foreach ([1, 0] as $cls) {
            $i = 0;
            foreach ($y as $idx => $yy) {
                if ((int) $yy === $cls) {
                    $fold[$idx] = $i++ % $k;
                }
            }
        }

        return $fold;
    }
}
