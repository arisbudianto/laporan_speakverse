<?php

namespace App\Services\Analytics;

/**
 * Statistik inferensial untuk data pretest-posttest berpasangan (per siswa
 * yang punya KEDUA nilai): paired-samples t-test, Cohen's dz, dan
 * Normalized Gain (N-Gain / Hake's g).
 *
 * Rumus:
 *  - t = rata-rata selisih / (SD selisih / akar(n)), df = n - 1
 *  - p-value dua-sisi dihitung lewat regularized incomplete beta function
 *    (I_x(df/2, 1/2) dengan x = df/(df+t²)) — pendekatan standar untuk CDF
 *    distribusi-t, diverifikasi manual terhadap nilai kritis tabel-t
 *    (mis. df=10, t=2.228 -> p=0,05; df=10, t=3,169 -> p=0,01).
 *  - Cohen's dz = rata-rata selisih / SD selisih (ukuran efek untuk sampel
 *    berpasangan; beda dengan Cohen's d untuk sampel independen).
 *  - N-Gain per siswa = (post - pre) / (100 - pre), lalu dirata-rata
 *    (metode <g> Hake). Siswa dengan nilai pretest sempurna (100) dilewati
 *    karena pembaginya nol / g tidak terdefinisi.
 */
final class PairedSampleStats
{
    /**
     * @param  float[]  $pre   nilai pretest, sejajar index dengan $post
     * @param  float[]  $post  nilai posttest, siswa yang sama & urutan sama dengan $pre
     */
    public static function compute(array $pre, array $post): array
    {
        $n = count($pre);
        $out = [
            'n' => $n, 'mean_pre' => null, 'mean_post' => null,
            'mean_diff' => null, 'sd_diff' => null,
            't' => null, 'df' => null, 'p' => null, 'sig' => null,
            'cohens_dz' => null, 'cohens_dz_label' => null,
            'ngain' => null, 'ngain_n' => 0, 'ngain_band' => null,
        ];
        if ($n < 2) {
            return $out;
        }

        $diffs = [];
        foreach ($pre as $i => $v) {
            $diffs[] = $post[$i] - $v;
        }
        $meanDiff = array_sum($diffs) / $n;
        $variance = array_sum(array_map(fn ($d) => ($d - $meanDiff) ** 2, $diffs)) / ($n - 1);
        $sd = sqrt($variance);
        $df = $n - 1;

        $t = $sd > 0 ? $meanDiff / ($sd / sqrt($n)) : null;
        $p = $t !== null ? self::tTwoTailedP(abs($t), $df) : null;
        $cohensDz = $sd > 0 ? $meanDiff / $sd : null;

        $ngains = [];
        foreach ($pre as $i => $v) {
            $denom = 100 - $v;
            if ($denom > 0) {
                $ngains[] = ($post[$i] - $v) / $denom;
            }
        }
        $ngainMean = $ngains === [] ? null : array_sum($ngains) / count($ngains);

        $out['mean_pre'] = round(array_sum($pre) / $n, 2);
        $out['mean_post'] = round(array_sum($post) / $n, 2);
        $out['mean_diff'] = round($meanDiff, 2);
        $out['sd_diff'] = round($sd, 2);
        $out['t'] = $t === null ? null : round($t, 3);
        $out['df'] = $df;
        $out['p'] = $p === null ? null : round($p, 4);
        $out['sig'] = $p === null ? null : $p < 0.05;
        $out['cohens_dz'] = $cohensDz === null ? null : round($cohensDz, 3);
        $out['cohens_dz_label'] = self::cohensDLabel($cohensDz);
        $out['ngain'] = $ngainMean === null ? null : round($ngainMean, 3);
        $out['ngain_n'] = count($ngains);
        $out['ngain_band'] = self::ngainBand($ngainMean);

        return $out;
    }

    private static function cohensDLabel(?float $d): ?string
    {
        if ($d === null) {
            return null;
        }
        $a = abs($d);
        if ($a < 0.2) {
            return 'Dapat diabaikan';
        }
        if ($a < 0.5) {
            return 'Kecil';
        }
        if ($a < 0.8) {
            return 'Sedang';
        }

        return 'Besar';
    }

    private static function ngainBand(?float $g): ?string
    {
        if ($g === null) {
            return null;
        }
        if ($g < 0.3) {
            return 'Rendah';
        }
        if ($g < 0.7) {
            return 'Sedang';
        }

        return 'Tinggi';
    }

    /** P-value dua-sisi distribusi-t: P(|T| > |t|) dengan df derajat bebas. */
    private static function tTwoTailedP(float $absT, int $df): float
    {
        if ($df <= 0) {
            return 1.0;
        }
        $x = $df / ($df + $absT * $absT);

        return self::regularizedIncompleteBeta($x, $df / 2.0, 0.5);
    }

    private static function regularizedIncompleteBeta(float $x, float $a, float $b): float
    {
        if ($x <= 0.0) {
            return 0.0;
        }
        if ($x >= 1.0) {
            return 1.0;
        }
        $bt = exp(
            self::logGamma($a + $b) - self::logGamma($a) - self::logGamma($b)
            + $a * log($x) + $b * log(1.0 - $x)
        );
        if ($x < ($a + 1.0) / ($a + $b + 2.0)) {
            return $bt * self::betacf($x, $a, $b) / $a;
        }

        return 1.0 - $bt * self::betacf(1.0 - $x, $b, $a) / $b;
    }

    /** Continued fraction untuk incomplete beta (Lentz's algorithm). */
    private static function betacf(float $x, float $a, float $b): float
    {
        $maxit = 200;
        $eps = 3.0e-9;
        $fpmin = 1.0e-30;
        $qab = $a + $b;
        $qap = $a + 1.0;
        $qam = $a - 1.0;
        $c = 1.0;
        $d = 1.0 - $qab * $x / $qap;
        if (abs($d) < $fpmin) {
            $d = $fpmin;
        }
        $d = 1.0 / $d;
        $h = $d;
        for ($m = 1; $m <= $maxit; $m++) {
            $m2 = 2 * $m;
            $aa = $m * ($b - $m) * $x / (($qam + $m2) * ($a + $m2));
            $d = 1.0 + $aa * $d;
            if (abs($d) < $fpmin) {
                $d = $fpmin;
            }
            $c = 1.0 + $aa / $c;
            if (abs($c) < $fpmin) {
                $c = $fpmin;
            }
            $d = 1.0 / $d;
            $h *= $d * $c;
            $aa = -($a + $m) * ($qab + $m) * $x / (($a + $m2) * ($qap + $m2));
            $d = 1.0 + $aa * $d;
            if (abs($d) < $fpmin) {
                $d = $fpmin;
            }
            $c = 1.0 + $aa / $c;
            if (abs($c) < $fpmin) {
                $c = $fpmin;
            }
            $d = 1.0 / $d;
            $del = $d * $c;
            $h *= $del;
            if (abs($del - 1.0) < $eps) {
                break;
            }
        }

        return $h;
    }

    /** Lanczos approximation untuk ln(Gamma(x)). */
    private static function logGamma(float $x): float
    {
        static $g = [
            676.5203681218851, -1259.1392167224028, 771.32342877765313,
            -176.61502916214059, 12.507343278686905, -0.13857109526572012,
            9.9843695780195716e-6, 1.5056327351493116e-7,
        ];
        if ($x < 0.5) {
            return log(M_PI / sin(M_PI * $x)) - self::logGamma(1 - $x);
        }
        $x -= 1;
        $t = $x + 7.5;
        $a = 0.99999999999980993;
        foreach ($g as $i => $c) {
            $a += $c / ($x + $i + 1);
        }

        return 0.5 * log(2 * M_PI) + ($x + 0.5) * log($t) - $t + log($a);
    }
}
