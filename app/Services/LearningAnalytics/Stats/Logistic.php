<?php

namespace App\Services\LearningAnalytics\Stats;

/**
 * Regresi logistik L2 (ridge) dengan Newton–Raphson (IRLS).
 * Meminimalkan  −Σ[y·ln p + (1−y)·ln(1−p)] + (λ/2)·Σβ_j²  (intercept TIDAK dikenai penalti),
 * identik dengan sklearn LogisticRegression(C=1/λ, penalty='l2') (kecuali solver).
 * Fitur harus sudah di-standardisasi oleh pemanggil (lihat standardize()).
 */
final class Logistic
{
    /** @return array{b0:float,w:float[],iterations:int,converged:bool} */
    public static function fit(array $x, array $y, float $lambda = 1.0, int $maxIter = 100): array
    {
        $n = count($x);
        $d = count($x[0]);
        $beta = array_fill(0, $d + 1, 0.0);
        $converged = false;
        for ($it = 1; $it <= $maxIter; $it++) {
            $g = array_fill(0, $d + 1, 0.0);
            $H = array_fill(0, $d + 1, array_fill(0, $d + 1, 0.0));
            for ($i = 0; $i < $n; $i++) {
                $z = $beta[0];
                for ($j = 0; $j < $d; $j++) {
                    $z += $beta[$j + 1] * $x[$i][$j];
                }
                $p = 1 / (1 + exp(-max(-35, min(35, $z))));
                $r = $p - $y[$i];
                $wgt = max($p * (1 - $p), 1e-9);
                $xi = $x[$i];
                $g[0] += $r;
                $H[0][0] += $wgt;
                for ($j = 0; $j < $d; $j++) {
                    $g[$j + 1] += $r * $xi[$j];
                    $H[0][$j + 1] += $wgt * $xi[$j];
                    $H[$j + 1][0] = $H[0][$j + 1];
                    for ($k = $j; $k < $d; $k++) {
                        $H[$j + 1][$k + 1] += $wgt * $xi[$j] * $xi[$k];
                    }
                }
            }
            for ($j = 0; $j < $d; $j++) {
                $g[$j + 1] += $lambda * $beta[$j + 1];
                $H[$j + 1][$j + 1] += $lambda;
                for ($k = 0; $k < $j; $k++) {
                    $H[$j + 1][$k + 1] = $H[$k + 1][$j + 1];
                }
            }
            $step = self::solve($H, $g);
            $maxStep = 0.0;
            foreach ($step as $i => $s) {
                $beta[$i] -= $s;
                $maxStep = max($maxStep, abs($s));
            }
            if ($maxStep < 1e-9) {
                $converged = true;
                break;
            }
        }

        return ['b0' => $beta[0], 'w' => array_slice($beta, 1), 'iterations' => $it, 'converged' => $converged];
    }

    public static function predict(array $model, array $row): float
    {
        $z = $model['b0'];
        foreach ($model['w'] as $j => $w) {
            $z += $w * $row[$j];
        }

        return 1 / (1 + exp(-max(-35, min(35, $z))));
    }

    /** @return array{means:float[],sds:float[]} */
    public static function scaler(array $x): array
    {
        $d = count($x[0]);
        $m = $s = [];
        for ($j = 0; $j < $d; $j++) {
            $col = array_column($x, $j);
            $m[$j] = array_sum($col) / count($col);
            $sd = Descriptive::sd($col);
            $s[$j] = ($sd === null || $sd < 1e-9) ? 1.0 : $sd;
        }

        return ['means' => $m, 'sds' => $s];
    }

    public static function apply(array $sc, array $x): array
    {
        return array_map(fn ($r) => array_map(fn ($v, $j) => ($v - $sc['means'][$j]) / $sc['sds'][$j], $r, array_keys($r)), $x);
    }

    /** Gauss-Jordan dengan pivot parsial. */
    private static function solve(array $A, array $b): array
    {
        $n = count($b);
        for ($i = 0; $i < $n; $i++) {
            $A[$i][] = $b[$i];
        }
        for ($c = 0; $c < $n; $c++) {
            $p = $c;
            for ($r = $c + 1; $r < $n; $r++) {
                if (abs($A[$r][$c]) > abs($A[$p][$c])) {
                    $p = $r;
                }
            }
            [$A[$c], $A[$p]] = [$A[$p], $A[$c]];
            $piv = $A[$c][$c];
            if (abs($piv) < 1e-14) {
                $piv = 1e-14;
            }
            for ($k = $c; $k <= $n; $k++) {
                $A[$c][$k] /= $piv;
            }
            for ($r = 0; $r < $n; $r++) {
                if ($r !== $c) {
                    $f = $A[$r][$c];
                    if ($f != 0.0) {
                        for ($k = $c; $k <= $n; $k++) {
                            $A[$r][$k] -= $f * $A[$c][$k];
                        }
                    }
                }
            }
        }

        return array_map(fn ($r) => $r[$n], $A);
    }
}
