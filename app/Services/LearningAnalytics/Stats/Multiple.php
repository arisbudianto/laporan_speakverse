<?php

namespace App\Services\LearningAnalytics\Stats;

/** Koreksi perbandingan ganda: Holm (kendali FWER) dan Benjamini–Hochberg (kendali FDR). Dicocokkan dengan statsmodels.multipletests. */
final class Multiple
{
    /** @param array<int|string,?float> $p kunci dipertahankan; null diabaikan @return array<int|string,?float> */
    public static function holm(array $p): array
    {
        $valid = array_filter($p, fn ($v) => $v !== null);
        asort($valid);
        $m = count($valid);
        $out = array_fill_keys(array_keys($p), null);
        $running = 0.0;
        $i = 0;
        foreach ($valid as $k => $v) {
            $adj = min(1.0, ($m - $i) * $v);
            $running = max($running, $adj);
            $out[$k] = $running;
            $i++;
        }

        return $out;
    }

    /** @param array<int|string,?float> $p @return array<int|string,?float> */
    public static function benjaminiHochberg(array $p): array
    {
        $valid = array_filter($p, fn ($v) => $v !== null);
        arsort($valid);   // terbesar → terkecil
        $m = count($valid);
        $out = array_fill_keys(array_keys($p), null);
        $prev = 1.0;
        $rank = $m;
        foreach ($valid as $k => $v) {
            $adj = min($prev, $v * $m / $rank);
            $out[$k] = min(1.0, $adj);
            $prev = $adj;
            $rank--;
        }

        return $out;
    }
}
