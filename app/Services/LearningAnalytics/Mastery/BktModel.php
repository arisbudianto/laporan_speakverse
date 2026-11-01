<?php

namespace App\Services\LearningAnalytics\Mastery;

/**
 * Bayesian Knowledge Tracing (Corbett & Anderson, 1995), satu keterampilan.
 *
 * Parameter: L0 = P(sudah menguasai di awal), T = P(belajar per kesempatan),
 *            S = P(slip: salah padahal menguasai), G = P(guess: benar padahal belum).
 * Pembaruan per pengamatan (benar/salah):
 *   benar : P(L|obs) = P(L)(1−S) / [P(L)(1−S) + (1−P(L))G]
 *   salah : P(L|obs) = P(L)S     / [P(L)S + (1−P(L))(1−G)]
 *   lalu  : P(L_next) = P(L|obs) + (1 − P(L|obs))·T
 * Log-likelihood = Σ log P(obs). Estimasi parameter: pencarian grid (deterministik) memaksimalkan
 * log-likelihood gabungan semua siswa, dengan batas S ≤ 0,30 dan G ≤ 0,30 (identifiabilitas).
 */
final class BktModel
{
    public const DEFAULTS = ['l0' => 0.30, 't' => 0.10, 's' => 0.10, 'g' => 0.20];

    /** @param int[] $obs 1 = benar, 0 = salah  @return array{p:float,ll:float,trace:float[]} */
    public static function run(array $obs, array $prm): array
    {
        $p = $prm['l0'];
        $ll = 0.0;
        $trace = [];
        foreach ($obs as $o) {
            if ($o) {
                $d = $p * (1 - $prm['s']) + (1 - $p) * $prm['g'];
                $post = $p * (1 - $prm['s']) / $d;
            } else {
                $d = $p * $prm['s'] + (1 - $p) * (1 - $prm['g']);
                $post = $p * $prm['s'] / $d;
            }
            $ll += log($d);
            $p = $post + (1 - $post) * $prm['t'];
            $trace[] = $p;
        }

        return ['p' => $p, 'll' => $ll, 'trace' => $trace];
    }

    /** Batas grid: parameter yang jatuh tepat di batas menandakan model kurang teridentifikasi. */
    public const GRID = ['l0' => [0.05, 0.7], 't' => [0.02, 0.4], 's' => [0.02, 0.3], 'g' => [0.05, 0.3]];

    /** @return string[] nama parameter yang menempel pada batas grid */
    public static function atBound(array $prm): array
    {
        $out = [];
        foreach (self::GRID as $k => [$lo, $hi]) {
            if (abs($prm[$k] - $lo) < 1e-9 || abs($prm[$k] - $hi) < 1e-9) {
                $out[] = $k;
            }
        }

        return $out;
    }

    /** Log-likelihood model pembanding: peluang benar konstan = rerata (1 parameter). */
    public static function baselineLl(array $sequences): float
    {
        $n = array_sum(array_map('count', $sequences));
        if ($n === 0) {
            return 0.0;
        }
        $c = array_sum(array_map('array_sum', $sequences));
        $p = min(0.999, max(0.001, $c / $n));

        return $c * log($p) + ($n - $c) * log(1 - $p);
    }

    /**
     * @param array<int,int[]> $sequences urutan pengamatan per siswa
     * @return array{params:array,ll:float,n_obs:int,n_seq:int}
     */
    public static function fit(array $sequences): array
    {
        $nObs = array_sum(array_map('count', $sequences));
        $best = null;
        foreach ([0.05, 0.1, 0.2, 0.3, 0.4, 0.5, 0.6, 0.7] as $l0) {
            foreach ([0.02, 0.05, 0.1, 0.15, 0.2, 0.3, 0.4] as $t) {
                foreach ([0.02, 0.05, 0.1, 0.15, 0.2, 0.3] as $s) {
                    foreach ([0.05, 0.1, 0.15, 0.2, 0.25, 0.3] as $g) {
                        $prm = ['l0' => $l0, 't' => $t, 's' => $s, 'g' => $g];
                        $ll = 0.0;
                        foreach ($sequences as $seq) {
                            $ll += self::run($seq, $prm)['ll'];
                        }
                        if ($best === null || $ll > $best['ll'] + 1e-9) {
                            $best = ['params' => $prm, 'll' => $ll];
                        }
                    }
                }
            }
        }

        return $best + ['n_obs' => $nObs, 'n_seq' => count($sequences)];
    }
}
