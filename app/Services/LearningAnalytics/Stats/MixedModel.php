<?php

namespace App\Services\LearningAnalytics\Stats;

/**
 * Model linear campuran dengan satu intersep acak per kelompok (siswa tersarang dalam sekolah/kelas):
 *   y_ij = x_ij'β + u_j + e_ij,  u_j ~ N(0, σ²_u),  e_ij ~ N(0, σ²_e)
 * Diestimasi dengan REML. Dengan λ = σ²_u/σ²_e, σ²_e dan β dapat dihitung tertutup (GLS), sehingga hanya λ yang dicari
 * (pemindaian log-λ lalu golden-section). Dicocokkan dengan optimasi REML matriks penuh (SciPy) pada fixture.
 *
 * Inferensi: SE dari σ²·(X'V⁻¹X)⁻¹ (mengabaikan ketidakpastian λ → sedikit terlalu optimistis untuk kelompok sedikit);
 * df t = N − p untuk prediktor tingkat-siswa dan G − q − 1 untuk prediktor tingkat-kelompok (q = jumlah prediktor kelompok).
 * Uji σ²_u = 0: rasio kemungkinan REML dengan campuran ½χ²₁ (batas parameter).
 */
final class MixedModel
{
    /**
     * @param float[][] $x baris × prediktor (kolom pertama intersep)
     * @param float[]   $y
     * @param array<int,string|int> $group label kelompok per baris
     * @param int[]     $groupLevelCols indeks kolom X yang konstan dalam kelompok (mis. perlakuan)
     * @return array<string,mixed>|null null bila data tidak cukup
     */
    public static function fit(array $x, array $y, array $group, array $groupLevelCols = []): ?array
    {
        $n = count($y);
        $p = $n ? count($x[0]) : 0;
        $idx = [];
        foreach ($group as $g) {
            $idx[(string) $g] ??= count($idx);
        }
        $G = count($idx);
        if ($n <= $p + 1 || $G < 3) {
            return null;
        }
        $nj = array_fill(0, $G, 0);
        $sj = array_fill(0, $G, array_fill(0, $p, 0.0));
        $tj = array_fill(0, $G, 0.0);
        $xtx = array_fill(0, $p, array_fill(0, $p, 0.0));
        $xty = array_fill(0, $p, 0.0);
        $yty = 0.0;
        foreach ($y as $i => $yi) {
            $j = $idx[(string) $group[$i]];
            $nj[$j]++;
            $tj[$j] += $yi;
            $yty += $yi * $yi;
            for ($a = 0; $a < $p; $a++) {
                $sj[$j][$a] += $x[$i][$a];
                $xty[$a] += $x[$i][$a] * $yi;
                for ($b = 0; $b < $p; $b++) {
                    $xtx[$a][$b] += $x[$i][$a] * $x[$i][$b];
                }
            }
        }
        $eval = function (float $lam) use ($p, $G, $n, $nj, $sj, $tj, $xtx, $xty, $yty) {
            $A = $xtx;
            $b = $xty;
            $ywy = $yty;
            $logdetV = 0.0;
            for ($j = 0; $j < $G; $j++) {
                $c = $lam / (1 + $nj[$j] * $lam);
                $logdetV += log(1 + $nj[$j] * $lam);
                $ywy -= $c * $tj[$j] ** 2;
                for ($a = 0; $a < $p; $a++) {
                    $b[$a] -= $c * $sj[$j][$a] * $tj[$j];
                    for ($k = 0; $k < $p; $k++) {
                        $A[$a][$k] -= $c * $sj[$j][$a] * $sj[$j][$k];
                    }
                }
            }
            $inv = Ols::invert($A);
            $det = self::logDet($A);
            if ($inv === null || $det === null) {
                return null;
            }
            $beta = [];
            for ($a = 0; $a < $p; $a++) {
                $beta[$a] = 0.0;
                for ($k = 0; $k < $p; $k++) {
                    $beta[$a] += $inv[$a][$k] * $b[$k];
                }
            }
            $rss = $ywy;
            for ($a = 0; $a < $p; $a++) {
                $rss -= $beta[$a] * $b[$a];
            }
            $rss = max($rss, 1e-12);
            $s2 = $rss / ($n - $p);
            $reml = -0.5 * (($n - $p) * log($s2) + $logdetV + $det);

            return ['reml' => $reml, 's2' => $s2, 'beta' => $beta, 'inv' => $inv, 'lam' => $lam];
        };

        // pemindaian kasar pada log λ, lalu penghalusan
        $best = $eval(0.0);
        if ($best === null) {
            return null;
        }
        $reml0 = $best['reml'];
        $grid = [];
        for ($k = 0; $k <= 70; $k++) {
            $grid[] = exp(log(1e-4) + $k * (log(1e3) - log(1e-4)) / 70);
        }
        $bi = -1;
        foreach ($grid as $i => $lam) {
            $r = $eval($lam);
            if ($r !== null && $r['reml'] > $best['reml']) {
                $best = $r;
                $bi = $i;
            }
        }
        $boundary = null;
        if ($bi === -1) {
            $boundary = 'sigma_u = 0';
        } elseif ($bi === count($grid) - 1) {
            $boundary = 'lambda di batas atas';
        } else {
            $lo = log($grid[max(0, $bi - 1)]);
            $hi = log($grid[min(count($grid) - 1, $bi + 1)]);
            $phi = (sqrt(5) - 1) / 2;
            $c = $hi - $phi * ($hi - $lo);
            $d = $lo + $phi * ($hi - $lo);
            $fc = $eval(exp($c))['reml'] ?? -INF;
            $fd = $eval(exp($d))['reml'] ?? -INF;
            for ($it = 0; $it < 60; $it++) {
                if ($fc > $fd) {
                    $hi = $d;
                    $d = $c;
                    $fd = $fc;
                    $c = $hi - $phi * ($hi - $lo);
                    $fc = $eval(exp($c))['reml'] ?? -INF;
                } else {
                    $lo = $c;
                    $c = $d;
                    $fc = $fd;
                    $d = $lo + $phi * ($hi - $lo);
                    $fd = $eval(exp($d))['reml'] ?? -INF;
                }
            }
            $cand = $eval(exp(($lo + $hi) / 2));
            if ($cand !== null && $cand['reml'] >= $best['reml']) {
                $best = $cand;
            }
        }
        $s2 = $best['s2'];
        $su = $best['lam'] * $s2;
        if ($boundary === 'sigma_u = 0') {
            $su = 0.0;
        }
        $q = count($groupLevelCols);
        $se = $t = $pv = $df = $ci = [];
        for ($a = 0; $a < $p; $a++) {
            $se[$a] = sqrt(max(0.0, $s2 * $best['inv'][$a][$a]));
            $df[$a] = in_array($a, $groupLevelCols, true) ? max(1, $G - $q - 1) : max(1, $n - $p);
            $t[$a] = $se[$a] > 1e-12 ? $best['beta'][$a] / $se[$a] : 0.0;
            $pv[$a] = $se[$a] > 1e-12 ? min(1.0, 2 * (1 - Distributions::tCdf(abs($t[$a]), $df[$a]))) : 1.0;
            $qt = Distributions::tQuantile(0.975, $df[$a]);
            $ci[$a] = [$best['beta'][$a] - $qt * $se[$a], $best['beta'][$a] + $qt * $se[$a]];
        }
        $lrt = max(0.0, 2 * ($best['reml'] - $reml0));
        $tot = $su + $s2;

        return [
            'beta' => $best['beta'], 'se' => $se, 't' => $t, 'p' => $pv, 'df' => $df, 'ci' => $ci,
            'sigma2_e' => $s2, 'sigma2_u' => $su, 'icc' => $tot > 0 ? $su / $tot : null, 'lambda' => $best['lam'],
            'reml' => $best['reml'], 'lrt' => $lrt, 'lrt_p' => $lrt > 0 ? 0.5 * (1 - Distributions::chiSqCdf($lrt, 1)) : 1.0,
            'boundary' => $boundary, 'n' => $n, 'groups' => $G, 'p_params' => $p,
        ];
    }

    /** ln|A| lewat eliminasi Gauss dengan pivot sebagian; null bila singular / tak positif. */
    private static function logDet(array $m): ?float
    {
        $n = count($m);
        $ld = 0.0;
        for ($c = 0; $c < $n; $c++) {
            $piv = $c;
            for ($r = $c + 1; $r < $n; $r++) {
                if (abs($m[$r][$c]) > abs($m[$piv][$c])) {
                    $piv = $r;
                }
            }
            if (abs($m[$piv][$c]) < 1e-12) {
                return null;
            }
            if ($piv !== $c) {
                [$m[$piv], $m[$c]] = [$m[$c], $m[$piv]];
            }
            $ld += log(abs($m[$c][$c]));
            for ($r = $c + 1; $r < $n; $r++) {
                $f = $m[$r][$c] / $m[$c][$c];
                for ($k = $c; $k < $n; $k++) {
                    $m[$r][$k] -= $f * $m[$c][$k];
                }
            }
        }

        return $ld;
    }
}
