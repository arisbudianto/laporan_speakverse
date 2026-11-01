<?php

namespace App\Services\LearningAnalytics\Engagement;

/**
 * Menyusun sesi belajar dari jejak waktu aktivitas (event log).
 *
 * Definisi operasional:
 *  - Sesi baru dimulai jika jeda antar-aktivitas > $gap detik (default 30 menit,
 *    konvensi umum dalam penelitian LA berbasis log).
 *  - Durasi sesi = Σ min(jeda antar-aktivitas berurutan, $cap) + $tail.
 *    $cap memangkas (trim) waktu menganggur yang panjang di tengah sesi;
 *    $tail memberi kredit untuk aktivitas terakhir (waktu membaca/menjawab
 *    yang tidak punya aktivitas penutup). Ini ESTIMASI time-on-task, bukan
 *    waktu layar sebenarnya: sistem tidak mencatat login/page-view.
 */
final class SessionBuilder
{
    /**
     * @param  int[]  $timestamps  detik epoch (boleh tak terurut)
     * @return array<int,array{start:int,end:int,seconds:int,events:int}>
     */
    public static function build(array $timestamps, int $gap = 1800, int $cap = 600, int $tail = 60): array
    {
        if ($timestamps === []) {
            return [];
        }
        sort($timestamps);
        $sessions = [];
        $start = $prev = $timestamps[0];
        $sec = 0;
        $n = 1;
        for ($i = 1, $c = count($timestamps); $i < $c; $i++) {
            $t = $timestamps[$i];
            $d = $t - $prev;
            if ($d > $gap) {
                $sessions[] = ['start' => $start, 'end' => $prev, 'seconds' => $sec + $tail, 'events' => $n];
                $start = $t;
                $sec = 0;
                $n = 0;
            } else {
                $sec += min($d, $cap);
            }
            $prev = $t;
            $n++;
        }
        $sessions[] = ['start' => $start, 'end' => $prev, 'seconds' => $sec + $tail, 'events' => $n];

        return $sessions;
    }
}
