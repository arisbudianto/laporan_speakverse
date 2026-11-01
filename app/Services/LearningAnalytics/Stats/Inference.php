<?php

namespace App\Services\LearningAnalytics\Stats;

use App\Services\LearningAnalytics\Stats\Descriptive as D;

/**
 * Inferensi statistik murni.
 *
 *  pairedT        t berpasangan: t = d̄ / (s_d/√n), df = n−1; CI = d̄ ± t(0,975;df)·s_d/√n
 *  cohenDz        d_z = d̄ / s_d (efek untuk desain berpasangan); Hedges' g = J·d_z, J = 1 − 3/(4·df − 1)
 *                 SE(d_z) ≈ √(1/n + d_z²/(2n)) (aproksimasi besar-sampel; Lakens 2013)
 *  wilcoxon       Wilcoxon signed-rank; selisih nol dibuang; peringkat rata-rata untuk ikatan;
 *                 eksak (DP atas peringkat×2) bila n_efektif ≤ 25, selainnya normal dengan koreksi ikatan & kontinuitas
 *  hakeClass      <g> = (post̄ − prē)/(100 − prē) (gain kelompok Hake 1998); kategori ≥0,7 tinggi, ≥0,3 sedang, selainnya rendah
 *  kruskalWallis  H dengan koreksi ikatan; p dari χ²(k−1); ε² = H/(N−1)
 *  icc1           ICC(1) anova satu arah: (MSB−MSW)/(MSB+(k0−1)MSW), k0 = (N − Σn_i²/N)/(a−1) untuk data tak seimbang
 *  skewKurt       skewness & excess kurtosis (momen sampel) untuk memilih uji t vs Wilcoxon
 */
final class Inference
{
    /** @return array{n:int,mean:float,sd:float,t:float,df:int,p:float,ci:array{0:float,1:float}}|null */
    public static function pairedT(array $pre, array $post): ?array
    {
        $d = self::diffs($pre, $post);
        $n = count($d);
        if ($n < 2) {
            return null;
        }
        $m = D::mean($d);
        $sd = D::sd($d);
        if ($sd < 1e-12) {
            return ['n' => $n, 'mean' => $m, 'sd' => 0.0, 't' => null, 'df' => $n - 1, 'p' => abs($m) < 1e-12 ? 1.0 : 0.0, 'ci' => [$m, $m]];
        }
        $se = $sd / sqrt($n);
        $t = $m / $se;
        $df = $n - 1;
        $p = 2 * (1 - Distributions::tCdf(abs($t), $df));
        $q = Distributions::tQuantile(0.975, $df);

        return ['n' => $n, 'mean' => $m, 'sd' => $sd, 't' => $t, 'df' => $df, 'p' => min(1.0, $p), 'ci' => [$m - $q * $se, $m + $q * $se]];
    }

    /** @return array{dz:float,g:float,ci:array{0:float,1:float},label:string}|null */
    public static function effectSize(array $pre, array $post): ?array
    {
        $d = self::diffs($pre, $post);
        $n = count($d);
        $sd = D::sd($d);
        if ($n < 2 || $sd === null || $sd < 1e-12) {
            return null;
        }
        $dz = D::mean($d) / $sd;
        $df = $n - 1;
        $j = 1 - 3 / (4 * $df - 1);
        $g = $j * $dz;
        $se = sqrt(1 / $n + $dz ** 2 / (2 * $n));
        $abs = abs($g);
        $label = $abs < 0.2 ? 'sangat kecil' : ($abs < 0.5 ? 'kecil' : ($abs < 0.8 ? 'sedang' : 'besar'));

        return ['dz' => $dz, 'g' => $g, 'ci' => [$g - 1.96 * $se * $j, $g + 1.96 * $se * $j], 'label' => $label];
    }

    /** @return array{n:int,w_plus:float,p:float,method:string}|null */
    public static function wilcoxon(array $pre, array $post): ?array
    {
        $d = array_values(array_filter(self::diffs($pre, $post), fn ($x) => abs($x) > 1e-12));
        $n = count($d);
        if ($n < 1) {
            return null;
        }
        $abs = array_map('abs', $d);
        $ranks = self::ranks($abs);
        $wPlus = 0.0;
        foreach ($d as $i => $x) {
            if ($x > 0) {
                $wPlus += $ranks[$i];
            }
        }
        if ($n <= 25) {
            // eksak: peringkat×2 bilangan bulat, DP jumlah bagian
            $r2 = array_map(fn ($r) => (int) round($r * 2), $ranks);
            $tot = array_sum($r2);
            $dp = array_fill(0, $tot + 1, 0.0);
            $dp[0] = 1.0;
            foreach ($r2 as $r) {
                for ($s = $tot; $s >= $r; $s--) {
                    $dp[$s] += $dp[$s - $r];
                }
            }
            $all = 2 ** $n;
            $obs = (int) round($wPlus * 2);
            $lo = 0.0;
            for ($s = 0; $s <= $obs; $s++) {
                $lo += $dp[$s];
            }
            $hi = 0.0;
            for ($s = $obs; $s <= $tot; $s++) {
                $hi += $dp[$s];
            }
            $p = min(1.0, 2 * min($lo, $hi) / $all);

            return ['n' => $n, 'w_plus' => $wPlus, 'p' => $p, 'method' => 'eksak'];
        }
        $mu = $n * ($n + 1) / 4;
        $ties = array_count_values(array_map(fn ($v) => (string) round($v, 9), $abs));
        $tc = 0.0;
        foreach ($ties as $t) {
            $tc += $t ** 3 - $t;
        }
        $var = $n * ($n + 1) * (2 * $n + 1) / 24 - $tc / 48;
        $z = (abs($wPlus - $mu) - 0.5) / sqrt($var);
        $p = min(1.0, 2 * (1 - Distributions::normalCdf($z)));

        return ['n' => $n, 'w_plus' => $wPlus, 'p' => $p, 'method' => 'normal'];
    }

    /** @return array{pre:float,post:float,g:float,category:string}|null */
    public static function hakeClass(array $pre, array $post): ?array
    {
        $pairs = self::pairs($pre, $post);
        if (! $pairs) {
            return null;
        }
        $mp = D::mean(array_column($pairs, 0));
        $mq = D::mean(array_column($pairs, 1));
        if ($mp >= 100) {
            return null;
        }
        $g = ($mq - $mp) / (100 - $mp);

        return ['pre' => $mp, 'post' => $mq, 'g' => $g, 'category' => $g < 0 ? 'menurun' : ($g >= 0.7 ? 'tinggi' : ($g >= 0.3 ? 'sedang' : 'rendah'))];
    }

    /** Gain ternormalisasi per siswa; pre = 100 dilewati. */
    public static function hakeStudent(float $pre, float $post): ?float
    {
        return $pre >= 100 ? null : ($post - $pre) / (100 - $pre);
    }

    /** @param array<string,float[]> $groups @return array{h:float,df:int,p:float,epsilon2:float,n:int}|null */
    public static function kruskalWallis(array $groups): ?array
    {
        $groups = array_filter($groups, fn ($g) => count($g) >= 1);
        $k = count($groups);
        $all = [];
        foreach ($groups as $g) {
            foreach ($g as $v) {
                $all[] = $v;
            }
        }
        $n = count($all);
        if ($k < 2 || $n <= $k) {
            return null;
        }
        $ranks = self::ranks($all);
        $i = 0;
        $s = 0.0;
        foreach ($groups as $g) {
            $sum = 0.0;
            foreach ($g as $_) {
                $sum += $ranks[$i++];
            }
            $s += $sum ** 2 / count($g);
        }
        $h = 12 / ($n * ($n + 1)) * $s - 3 * ($n + 1);
        $cnt = array_count_values(array_map(fn ($v) => (string) round($v, 9), $all));
        $tc = 0.0;
        foreach ($cnt as $t) {
            $tc += $t ** 3 - $t;
        }
        $corr = 1 - $tc / ($n ** 3 - $n);
        if ($corr <= 0) {
            return null;
        }
        $h /= $corr;
        $df = $k - 1;

        return ['h' => $h, 'df' => $df, 'p' => 1 - Distributions::chiSqCdf($h, $df), 'epsilon2' => $h / ($n - 1), 'n' => $n];
    }

    /** @param array<string,float[]> $groups @return array{icc:float,design_effect:float,groups:int,n:int,k0:float,msb:float,msw:float}|null */
    public static function icc1(array $groups): ?array
    {
        $groups = array_filter($groups, fn ($g) => count($g) >= 1);
        $a = count($groups);
        $n = 0;
        $sumSq = 0.0;
        $tot = 0.0;
        foreach ($groups as $g) {
            $n += count($g);
            $sumSq += count($g) ** 2;
            $tot += array_sum($g);
        }
        if ($a < 2 || $n <= $a) {
            return null;
        }
        $grand = $tot / $n;
        $ssb = 0.0;
        $ssw = 0.0;
        foreach ($groups as $g) {
            $m = array_sum($g) / count($g);
            $ssb += count($g) * ($m - $grand) ** 2;
            foreach ($g as $v) {
                $ssw += ($v - $m) ** 2;
            }
        }
        $msb = $ssb / ($a - 1);
        $msw = $ssw / ($n - $a);
        $k0 = ($n - $sumSq / $n) / ($a - 1);
        $den = $msb + ($k0 - 1) * $msw;
        if ($den <= 0) {
            return null;
        }
        $icc = ($msb - $msw) / $den;

        return ['icc' => $icc, 'design_effect' => 1 + ($n / $a - 1) * max(0.0, $icc), 'groups' => $a, 'n' => $n, 'k0' => $k0, 'msb' => $msb, 'msw' => $msw];
    }

    /** @return array{skew:float,kurt:float}|null */
    public static function skewKurt(array $v): ?array
    {
        $n = count($v);
        if ($n < 3) {
            return null;
        }
        $m = array_sum($v) / $n;
        $m2 = $m3 = $m4 = 0.0;
        foreach ($v as $x) {
            $m2 += ($x - $m) ** 2;
            $m3 += ($x - $m) ** 3;
            $m4 += ($x - $m) ** 4;
        }
        $m2 /= $n;
        $m3 /= $n;
        $m4 /= $n;
        if ($m2 < 1e-12) {
            return ['skew' => 0.0, 'kurt' => 0.0];
        }

        return ['skew' => $m3 / $m2 ** 1.5, 'kurt' => $m4 / $m2 ** 2 - 3];
    }

    /**
     * Pilih uji: t berpasangan bila n ≥ 20 dan |skew| < 1 dan |ekses kurtosis| < 2 pada selisih; selainnya Wilcoxon.
     * Aturan sederhana (bukan uji normalitas formal); selalu dicantumkan di laporan.
     */
    public static function chooseTest(array $pre, array $post): string
    {
        $d = self::diffs($pre, $post);
        $sk = self::skewKurt($d);

        return (count($d) >= 20 && $sk && abs($sk['skew']) < 1 && abs($sk['kurt']) < 2) ? 't' : 'wilcoxon';
    }

    /**
     * Uji t Welch dua sampel independen (varians tak sama). df Welch–Satterthwaite; CI 95% selisih rerata (a − b).
     * @return array{n1:int,n2:int,diff:float,t:float,df:float,p:float,ci:array{0:float,1:float}}|null
     */
    public static function welch(array $a, array $b): ?array
    {
        $n1 = count($a);
        $n2 = count($b);
        if ($n1 < 2 || $n2 < 2) {
            return null;
        }
        $v1 = D::sd($a) ** 2 / $n1;
        $v2 = D::sd($b) ** 2 / $n2;
        $se = sqrt($v1 + $v2);
        $diff = D::mean($a) - D::mean($b);
        if ($se < 1e-12) {
            return ['n1' => $n1, 'n2' => $n2, 'diff' => $diff, 't' => 0.0, 'df' => (float) ($n1 + $n2 - 2), 'p' => abs($diff) < 1e-12 ? 1.0 : 0.0, 'ci' => [$diff, $diff]];
        }
        $t = $diff / $se;
        $df = ($v1 + $v2) ** 2 / ($v1 ** 2 / ($n1 - 1) + $v2 ** 2 / ($n2 - 1));
        $q = Distributions::tQuantile(0.975, $df);

        return ['n1' => $n1, 'n2' => $n2, 'diff' => $diff, 't' => $t, 'df' => $df, 'p' => min(1.0, 2 * (1 - Distributions::tCdf(abs($t), $df))), 'ci' => [$diff - $q * $se, $diff + $q * $se]];
    }

    /** Hedges' g dua sampel independen (SD gabungan). */
    public static function hedgesGIndependent(array $a, array $b): ?float
    {
        $n1 = count($a);
        $n2 = count($b);
        if ($n1 < 2 || $n2 < 2) {
            return null;
        }
        $sp = sqrt((($n1 - 1) * D::sd($a) ** 2 + ($n2 - 1) * D::sd($b) ** 2) / ($n1 + $n2 - 2));
        if ($sp < 1e-12) {
            return null;
        }
        $j = 1 - 3 / (4 * ($n1 + $n2 - 2) - 1);

        return $j * (D::mean($a) - D::mean($b)) / $sp;
    }

    /** @return float[] peringkat rata-rata (1..n) dalam urutan masukan */
    public static function ranks(array $v): array
    {
        $idx = array_keys($v);
        usort($idx, fn ($a, $b) => $v[$a] <=> $v[$b]);
        $r = [];
        $n = count($idx);
        for ($i = 0; $i < $n;) {
            $j = $i;
            while ($j + 1 < $n && abs($v[$idx[$j + 1]] - $v[$idx[$i]]) < 1e-9) {
                $j++;
            }
            $avg = ($i + $j) / 2 + 1;
            for ($k = $i; $k <= $j; $k++) {
                $r[$idx[$k]] = $avg;
            }
            $i = $j + 1;
        }
        ksort($r);

        return array_values($r);
    }

    private static function pairs(array $pre, array $post): array
    {
        $out = [];
        foreach ($pre as $k => $a) {
            if (isset($post[$k]) && $a !== null && $post[$k] !== null) {
                $out[] = [(float) $a, (float) $post[$k]];
            }
        }

        return $out;
    }

    private static function diffs(array $pre, array $post): array
    {
        return array_map(fn ($p) => $p[1] - $p[0], self::pairs($pre, $post));
    }
}
