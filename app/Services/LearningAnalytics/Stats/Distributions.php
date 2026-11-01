<?php

namespace App\Services\LearningAnalytics\Stats;

/**
 * Fungsi distribusi untuk uji statistik (tanpa dependensi).
 * Implementasi: Lanczos (ln Γ), fraksi lanjutan Lentz untuk beta tak-lengkap,
 * deret/fraksi lanjutan untuk gamma tak-lengkap (Numerical Recipes, Press dkk.).
 * Ketelitian ≈ 1e-10; diuji terhadap SciPy.
 */
final class Distributions
{
    public static function lgamma(float $x): float
    {
        if ($x < 0.5) {   // refleksi
            return log(M_PI / abs(sin(M_PI * $x))) - self::lgamma(1 - $x);
        }
        $x -= 1;
        // Lanczos g=7, n=9
        static $g = [0.99999999999980993, 676.5203681218851, -1259.1392167224028, 771.32342877765313,
            -176.61502916214059, 12.507343278686905, -0.13857109526572012, 9.9843695780195716e-6, 1.5056327351493116e-7];
        $a = $g[0];
        $t = $x + 7.5;
        for ($i = 1; $i < 9; $i++) {
            $a += $g[$i] / ($x + $i);
        }

        return 0.5 * log(2 * M_PI) + ($x + 0.5) * log($t) - $t + log($a);
    }

    /** Beta tak-lengkap teregularisasi I_x(a,b). */
    public static function regIncBeta(float $x, float $a, float $b): float
    {
        if ($x <= 0) {
            return 0.0;
        }
        if ($x >= 1) {
            return 1.0;
        }
        $bt = exp(self::lgamma($a + $b) - self::lgamma($a) - self::lgamma($b) + $a * log($x) + $b * log(1 - $x));
        if ($x < ($a + 1) / ($a + $b + 2)) {
            return $bt * self::betacf($x, $a, $b) / $a;
        }

        return 1 - $bt * self::betacf(1 - $x, $b, $a) / $b;
    }

    private static function betacf(float $x, float $a, float $b): float
    {
        $tiny = 1e-300;
        $qab = $a + $b;
        $qap = $a + 1;
        $qam = $a - 1;
        $c = 1.0;
        $d = 1 - $qab * $x / $qap;
        $d = abs($d) < $tiny ? $tiny : $d;
        $d = 1 / $d;
        $h = $d;
        for ($m = 1; $m <= 300; $m++) {
            $m2 = 2 * $m;
            $aa = $m * ($b - $m) * $x / (($qam + $m2) * ($a + $m2));
            $d = 1 + $aa * $d;
            $d = abs($d) < $tiny ? $tiny : $d;
            $c = 1 + $aa / $c;
            $c = abs($c) < $tiny ? $tiny : $c;
            $d = 1 / $d;
            $h *= $d * $c;
            $aa = -($a + $m) * ($qab + $m) * $x / (($a + $m2) * ($qap + $m2));
            $d = 1 + $aa * $d;
            $d = abs($d) < $tiny ? $tiny : $d;
            $c = 1 + $aa / $c;
            $c = abs($c) < $tiny ? $tiny : $c;
            $d = 1 / $d;
            $del = $d * $c;
            $h *= $del;
            if (abs($del - 1) < 1e-14) {
                break;
            }
        }

        return $h;
    }

    /** Gamma tak-lengkap bawah teregularisasi P(a,x). */
    public static function regLowerGamma(float $a, float $x): float
    {
        if ($x <= 0) {
            return 0.0;
        }
        $gln = self::lgamma($a);
        if ($x < $a + 1) {
            $ap = $a;
            $sum = $del = 1 / $a;
            for ($n = 0; $n < 500; $n++) {
                $ap++;
                $del *= $x / $ap;
                $sum += $del;
                if (abs($del) < abs($sum) * 1e-15) {
                    break;
                }
            }

            return $sum * exp(-$x + $a * log($x) - $gln);
        }
        $tiny = 1e-300;
        $b = $x + 1 - $a;
        $c = 1 / $tiny;
        $d = 1 / $b;
        $h = $d;
        for ($i = 1; $i < 500; $i++) {
            $an = -$i * ($i - $a);
            $b += 2;
            $d = $an * $d + $b;
            $d = abs($d) < $tiny ? $tiny : $d;
            $c = $b + $an / $c;
            $c = abs($c) < $tiny ? $tiny : $c;
            $d = 1 / $d;
            $del = $d * $c;
            $h *= $del;
            if (abs($del - 1) < 1e-15) {
                break;
            }
        }

        return 1 - exp(-$x + $a * log($x) - $gln) * $h;
    }

    public static function normalCdf(float $z): float
    {
        return 0.5 * (1 + self::erf($z / M_SQRT2));
    }

    public static function erf(float $x): float
    {
        // erf(x) = P(1/2, x²) · sign(x)
        $p = self::regLowerGamma(0.5, $x * $x);

        return $x >= 0 ? $p : -$p;
    }

    /** P(T ≤ t), derajat bebas $df. */
    public static function tCdf(float $t, float $df): float
    {
        $x = $df / ($df + $t * $t);
        $p = 0.5 * self::regIncBeta($x, $df / 2, 0.5);

        return $t > 0 ? 1 - $p : $p;
    }

    /** Kuantil t (invers CDF) lewat bisection. */
    public static function tQuantile(float $p, float $df): float
    {
        $lo = -1000.0;
        $hi = 1000.0;
        for ($i = 0; $i < 200; $i++) {
            $mid = ($lo + $hi) / 2;
            if (self::tCdf($mid, $df) < $p) {
                $lo = $mid;
            } else {
                $hi = $mid;
            }
        }

        return ($lo + $hi) / 2;
    }

    public static function chiSqCdf(float $x, float $df): float
    {
        return self::regLowerGamma($df / 2, $x / 2);
    }

    public static function fCdf(float $f, float $d1, float $d2): float
    {
        return $f <= 0 ? 0.0 : self::regIncBeta($d1 * $f / ($d1 * $f + $d2), $d1 / 2, $d2 / 2);
    }
}
