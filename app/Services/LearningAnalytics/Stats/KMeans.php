<?php

namespace App\Services\LearningAnalytics\Stats;

/**
 * k-means (Lloyd) dengan inisialisasi k-means++ yang DETERMINISTIK (PRNG bersemai sendiri,
 * tidak memakai mt_rand global) + beberapa kali ulang, plus skor siluet (Rousseeuw, 1987).
 */
final class KMeans
{
    /** @param float[][] $x @return array{labels:int[],centroids:float[][],inertia:float} */
    public static function fit(array $x, int $k, int $restarts = 10, int $seed = 42): array
    {
        $best = null;
        for ($r = 0; $r < $restarts; $r++) {
            $rng = new Lcg($seed + $r * 7919);
            $res = self::once($x, $k, $rng);
            if ($best === null || $res['inertia'] < $best['inertia'] - 1e-12) {
                $best = $res;
            }
        }

        return $best;
    }

    private static function once(array $x, int $k, Lcg $rng): array
    {
        $n = count($x);
        $c = [$x[$rng->int($n)]];
        while (count($c) < $k) {
            $d = [];
            $sum = 0.0;
            foreach ($x as $i => $p) {
                $m = INF;
                foreach ($c as $q) {
                    $m = min($m, self::d2($p, $q));
                }
                $d[$i] = $m;
                $sum += $m;
            }
            if ($sum <= 0) {
                $c[] = $x[$rng->int($n)];
                continue;
            }
            $t = $rng->float() * $sum;
            $acc = 0.0;
            foreach ($d as $i => $v) {
                $acc += $v;
                if ($acc >= $t) {
                    $c[] = $x[$i];
                    break;
                }
            }
        }
        $labels = array_fill(0, $n, -1);
        for ($iter = 0; $iter < 100; $iter++) {
            $changed = false;
            foreach ($x as $i => $p) {
                $b = 0;
                $bd = INF;
                foreach ($c as $j => $q) {
                    $dd = self::d2($p, $q);
                    if ($dd < $bd) {
                        $bd = $dd;
                        $b = $j;
                    }
                }
                if ($labels[$i] !== $b) {
                    $labels[$i] = $b;
                    $changed = true;
                }
            }
            $dim = count($x[0]);
            $sums = array_fill(0, $k, array_fill(0, $dim, 0.0));
            $cnt = array_fill(0, $k, 0);
            foreach ($x as $i => $p) {
                $cnt[$labels[$i]]++;
                foreach ($p as $d => $v) {
                    $sums[$labels[$i]][$d] += $v;
                }
            }
            for ($j = 0; $j < $k; $j++) {
                if ($cnt[$j] > 0) {
                    foreach ($sums[$j] as $d => $v) {
                        $c[$j][$d] = $v / $cnt[$j];
                    }
                }
            }
            if (! $changed) {
                break;
            }
        }
        $in = 0.0;
        foreach ($x as $i => $p) {
            $in += self::d2($p, $c[$labels[$i]]);
        }

        return ['labels' => $labels, 'centroids' => $c, 'inertia' => $in];
    }

    /** Rerata siluet (−1..1); klaster berukuran 1 mendapat s = 0. */
    public static function silhouette(array $x, array $labels): ?float
    {
        $n = count($x);
        $groups = [];
        foreach ($labels as $i => $l) {
            $groups[$l][] = $i;
        }
        if (count($groups) < 2 || $n < 3) {
            return null;
        }
        $tot = 0.0;
        foreach ($x as $i => $p) {
            $own = $groups[$labels[$i]];
            if (count($own) === 1) {
                continue;
            }
            $a = 0.0;
            foreach ($own as $j) {
                if ($j !== $i) {
                    $a += sqrt(self::d2($p, $x[$j]));
                }
            }
            $a /= count($own) - 1;
            $b = INF;
            foreach ($groups as $l => $idx) {
                if ($l === $labels[$i]) {
                    continue;
                }
                $s = 0.0;
                foreach ($idx as $j) {
                    $s += sqrt(self::d2($p, $x[$j]));
                }
                $b = min($b, $s / count($idx));
            }
            $m = max($a, $b);
            $tot += $m > 0 ? ($b - $a) / $m : 0.0;
        }

        return $tot / $n;
    }

    private static function d2(array $a, array $b): float
    {
        $s = 0.0;
        foreach ($a as $i => $v) {
            $s += ($v - $b[$i]) ** 2;
        }

        return $s;
    }
}

/** PRNG LCG kecil (Numerical Recipes) agar hasil klaster dapat diulang di semua versi PHP. */
final class Lcg
{
    private int $s;

    public function __construct(int $seed)
    {
        $this->s = $seed & 0x7fffffff;
    }

    public function float(): float
    {
        $this->s = ($this->s * 1103515245 + 12345) & 0x7fffffff;

        return $this->s / 0x7fffffff;
    }

    public function int(int $n): int
    {
        return (int) floor($this->float() * $n) % $n;
    }
}
