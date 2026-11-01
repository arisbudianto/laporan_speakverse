<?php

namespace App\Services\LearningAnalytics\Stats;

/**
 * Regresi kuadrat terkecil (OLS) umum: y = Xβ + ε, X sudah memuat kolom intercept.
 * Galat baku klasik (homoskedastik): Var(β) = σ²(XᵀX)⁻¹, σ² = SSE/(n−p). Inversi Gauss-Jordan berpivot parsial.
 * Dicocokkan dengan numpy.linalg / statsmodels pada fixture.
 */
final class Ols
{
    /**
     * @param float[][] $x baris pengamatan × kolom prediktor (termasuk intercept)
     * @param float[]   $y
     * @return array{beta:float[],se:float[],t:float[],p:float[],ci:array<int,array{0:float,1:float}>,df:int,r2:float,n:int}|null null bila singular / df<1
     */
    public static function fit(array $x, array $y): ?array
    {
        $n = count($y);
        $p = $n ? count($x[0]) : 0;
        if ($n <= $p || $p === 0) {
            return null;
        }
        $xtx = array_fill(0, $p, array_fill(0, $p, 0.0));
        $xty = array_fill(0, $p, 0.0);
        for ($i = 0; $i < $n; $i++) {
            for ($a = 0; $a < $p; $a++) {
                $xty[$a] += $x[$i][$a] * $y[$i];
                for ($b = 0; $b < $p; $b++) {
                    $xtx[$a][$b] += $x[$i][$a] * $x[$i][$b];
                }
            }
        }
        $inv = self::invert($xtx);
        if ($inv === null) {
            return null;
        }
        $beta = [];
        for ($a = 0; $a < $p; $a++) {
            $beta[$a] = 0.0;
            for ($b = 0; $b < $p; $b++) {
                $beta[$a] += $inv[$a][$b] * $xty[$b];
            }
        }
        $sse = 0.0;
        $my = array_sum($y) / $n;
        $sst = 0.0;
        for ($i = 0; $i < $n; $i++) {
            $f = 0.0;
            for ($a = 0; $a < $p; $a++) {
                $f += $x[$i][$a] * $beta[$a];
            }
            $sse += ($y[$i] - $f) ** 2;
            $sst += ($y[$i] - $my) ** 2;
        }
        $df = $n - $p;
        $s2 = $sse / $df;
        $q = Distributions::tQuantile(0.975, $df);
        $se = $t = $pv = $ci = [];
        for ($a = 0; $a < $p; $a++) {
            $se[$a] = sqrt(max(0.0, $s2 * $inv[$a][$a]));
            $t[$a] = $se[$a] > 1e-12 ? $beta[$a] / $se[$a] : 0.0;
            $pv[$a] = $se[$a] > 1e-12 ? min(1.0, 2 * (1 - Distributions::tCdf(abs($t[$a]), $df))) : 1.0;
            $ci[$a] = [$beta[$a] - $q * $se[$a], $beta[$a] + $q * $se[$a]];
        }

        return ['beta' => $beta, 'se' => $se, 't' => $t, 'p' => $pv, 'ci' => $ci, 'df' => $df, 'r2' => $sst > 1e-12 ? 1 - $sse / $sst : 0.0, 'n' => $n];
    }

    /** @param float[][] $m @return float[][]|null */
    public static function invert(array $m): ?array
    {
        $n = count($m);
        $a = [];
        for ($i = 0; $i < $n; $i++) {
            $a[$i] = array_merge($m[$i], array_map(fn ($j) => $i === $j ? 1.0 : 0.0, range(0, $n - 1)));
        }
        for ($c = 0; $c < $n; $c++) {
            $piv = $c;
            for ($r = $c + 1; $r < $n; $r++) {
                if (abs($a[$r][$c]) > abs($a[$piv][$c])) {
                    $piv = $r;
                }
            }
            if (abs($a[$piv][$c]) < 1e-10) {
                return null;
            }
            [$a[$c], $a[$piv]] = [$a[$piv], $a[$c]];
            $d = $a[$c][$c];
            for ($k = 0; $k < 2 * $n; $k++) {
                $a[$c][$k] /= $d;
            }
            for ($r = 0; $r < $n; $r++) {
                if ($r !== $c && $a[$r][$c] != 0.0) {
                    $f = $a[$r][$c];
                    for ($k = 0; $k < 2 * $n; $k++) {
                        $a[$r][$k] -= $f * $a[$c][$k];
                    }
                }
            }
        }

        return array_map(fn ($row) => array_slice($row, $n), $a);
    }
}
