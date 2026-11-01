<?php

namespace App\Services\LearningAnalytics\Stats;

/**
 * Statistik deskriptif murni (tanpa DB, tanpa dependensi).
 *
 * Rumus:
 *  - sd        : simpangan baku sampel (pembagi n-1)
 *  - quantile  : Hyndman & Fan tipe 7 (default R/Excel)
 *  - cv        : sd / mean
 *  - gini      : G = (2·Σ i·x(i)) / (n·Σx) − (n+1)/n, x diurutkan naik, i = 1..n
 *  - entropy   : H = −Σ p·ln p, dinormalisasi oleh ln(k) → 0..1
 *  - slope     : kemiringan regresi kuadrat terkecil (OLS) y terhadap x = 0..n-1
 */
final class Descriptive
{
    public static function mean(array $v): ?float
    {
        return $v === [] ? null : array_sum($v) / count($v);
    }

    public static function median(array $v): ?float
    {
        return self::quantile($v, 0.5);
    }

    public static function sd(array $v): ?float
    {
        $n = count($v);
        if ($n < 2) {
            return null;
        }
        $m = array_sum($v) / $n;
        $ss = 0.0;
        foreach ($v as $x) {
            $ss += ($x - $m) ** 2;
        }

        return sqrt($ss / ($n - 1));
    }

    public static function quantile(array $v, float $p): ?float
    {
        $n = count($v);
        if ($n === 0) {
            return null;
        }
        sort($v);
        $h = ($n - 1) * $p;
        $lo = (int) floor($h);
        $hi = (int) ceil($h);

        return $v[$lo] + ($h - $lo) * ($v[$hi] - $v[$lo]);
    }

    public static function cv(array $v): ?float
    {
        $m = self::mean($v);
        $s = self::sd($v);

        return ($m === null || $s === null || abs($m) < 1e-12) ? null : $s / $m;
    }

    /** 0 = merata sempurna, mendekati 1 = terkonsentrasi pada sedikit titik. */
    public static function gini(array $v): ?float
    {
        $n = count($v);
        $sum = array_sum($v);
        if ($n === 0 || $sum <= 0) {
            return null;
        }
        sort($v);
        $w = 0.0;
        foreach ($v as $i => $x) {
            $w += ($i + 1) * $x;
        }

        return max(0.0, (2 * $w) / ($n * $sum) - ($n + 1) / $n);
    }

    /** Entropi Shannon ternormalisasi (0..1) dari hitungan kategori. */
    public static function normalizedEntropy(array $counts): ?float
    {
        $k = count($counts);
        $sum = array_sum($counts);
        if ($k < 2 || $sum <= 0) {
            return null;
        }
        $h = 0.0;
        foreach ($counts as $c) {
            if ($c > 0) {
                $p = $c / $sum;
                $h -= $p * log($p);
            }
        }

        return $h / log($k);
    }

    public static function slope(array $y): ?float
    {
        $n = count($y);
        if ($n < 2) {
            return null;
        }
        $y = array_values($y);
        $mx = ($n - 1) / 2;
        $my = array_sum($y) / $n;
        $num = 0.0;
        $den = 0.0;
        foreach ($y as $i => $v) {
            $num += ($i - $mx) * ($v - $my);
            $den += ($i - $mx) ** 2;
        }

        return $num / $den;
    }
}
