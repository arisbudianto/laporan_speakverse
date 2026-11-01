<?php

namespace App\Services\LearningAnalytics\Engagement;

/**
 * Timestamp aktivitas satu siswa → baris agregat per hari.
 * Hari ditentukan oleh zona waktu default PHP (Laravel: config app.timezone).
 */
final class DailyAggregator
{
    /**
     * @param  int[]  $timestamps
     * @return array<string,array{sessions:int,active_seconds:int,events:int,peak_hour:int}> kunci 'Y-m-d'
     */
    public static function days(array $timestamps): array
    {
        $out = [];
        foreach (SessionBuilder::build($timestamps) as $s) {
            // sesi dihitung pada hari mulainya
            $d = date('Y-m-d', $s['start']);
            $out[$d]['sessions'] = ($out[$d]['sessions'] ?? 0) + 1;
            $out[$d]['active_seconds'] = ($out[$d]['active_seconds'] ?? 0) + $s['seconds'];
        }
        $hours = [];
        foreach ($timestamps as $t) {
            $d = date('Y-m-d', $t);
            $h = (int) date('G', $t);
            $hours[$d][$h] = ($hours[$d][$h] ?? 0) + 1;
            $out[$d]['events'] = ($out[$d]['events'] ?? 0) + 1;
        }
        foreach ($out as $d => &$row) {
            $row['sessions'] = $row['sessions'] ?? 0;
            $row['active_seconds'] = $row['active_seconds'] ?? 0;
            arsort($hours[$d]);
            $row['peak_hour'] = (int) array_key_first($hours[$d]);
        }
        unset($row);
        ksort($out);

        return $out;
    }
}
