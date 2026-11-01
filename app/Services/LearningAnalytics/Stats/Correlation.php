<?php

namespace App\Services\LearningAnalytics\Stats;

/**
 * Korelasi peringkat Spearman: ρ = Pearson atas peringkat rata-rata (menangani ikatan).
 * Uji: t = ρ·√((n−2)/(1−ρ²)), df = n−2 (aproksimasi yang lazim untuk n ≥ 10).
 */
final class Correlation
{
    /** @return array{rho:float,n:int,t:?float,p:?float}|null */
    public static function spearman(array $x, array $y): ?array
    {
        $px = $py = [];
        foreach ($x as $k => $a) {
            if ($a !== null && isset($y[$k]) && $y[$k] !== null) {
                $px[] = (float) $a;
                $py[] = (float) $y[$k];
            }
        }
        $n = count($px);
        if ($n < 4) {
            return null;
        }
        $rx = Inference::ranks($px);
        $ry = Inference::ranks($py);
        $mx = array_sum($rx) / $n;
        $my = array_sum($ry) / $n;
        $sxy = $sxx = $syy = 0.0;
        for ($i = 0; $i < $n; $i++) {
            $sxy += ($rx[$i] - $mx) * ($ry[$i] - $my);
            $sxx += ($rx[$i] - $mx) ** 2;
            $syy += ($ry[$i] - $my) ** 2;
        }
        if ($sxx < 1e-12 || $syy < 1e-12) {
            return null;
        }
        $rho = $sxy / sqrt($sxx * $syy);
        if (abs($rho) >= 1 - 1e-12) {
            return ['rho' => $rho, 'n' => $n, 't' => null, 'p' => 0.0];
        }
        $t = $rho * sqrt(($n - 2) / (1 - $rho ** 2));

        return ['rho' => $rho, 'n' => $n, 't' => $t, 'p' => min(1.0, 2 * (1 - Distributions::tCdf(abs($t), $n - 2)))];
    }
}
