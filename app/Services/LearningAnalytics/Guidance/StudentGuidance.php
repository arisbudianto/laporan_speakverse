<?php

namespace App\Services\LearningAnalytics\Guidance;

/**
 * Teks dan ringkasan untuk halaman siswa "Kemajuan Saya" (bahasa suportif, tanpa peringkat).
 *  - Label penguasaan: P ≥ 0,95 "Sudah dikuasai"; ≥ 0,60 "Hampir"; ≥ 0,30 "Sedang berkembang"; selainnya "Perlu dilatih".
 *  - Target mingguan: 3 hari aktif per minggu (7 hari terakhir termasuk hari ini).
 *  - Saran berikutnya: kompetensi belum dikuasai dengan P terendah yang sudah pernah dicoba; bila semua dikuasai, beri apresiasi.
 */
final class StudentGuidance
{
    public static function masteryLabel(float $p): array
    {
        return match (true) {
            $p >= 0.95 => ['Sudah dikuasai', 'green'],
            $p >= 0.60 => ['Hampir', 'cyan'],
            $p >= 0.30 => ['Sedang berkembang', 'yellow'],
            default => ['Perlu dilatih', 'orange'],
        };
    }

    /** @param array<string,true> $activeDates 'Y-m-d' hari aktif */
    public static function weekly(array $activeDates, \DateTimeImmutable $today, int $target = 3): array
    {
        $n = 0;
        for ($i = 0; $i < 7; $i++) {
            $n += isset($activeDates[$today->modify("-$i days")->format('Y-m-d')]) ? 1 : 0;
        }

        return ['active' => $n, 'target' => $target, 'done' => $n >= $target, 'remaining' => max(0, $target - $n)];
    }

    /** @param array<int,array{competency:string,p:float,n_obs:int}> $states */
    public static function nextStep(array $states): array
    {
        $open = array_values(array_filter($states, fn ($s) => $s['p'] < 0.95 && $s['n_obs'] > 0));
        if (! $states) {
            return ['text' => 'Mulai dengan satu pelajaran hari ini, 15–20 menit sudah cukup.', 'competency' => null];
        }
        if (! $open) {
            return ['text' => 'Kerja bagus! Semua kompetensi yang sudah dicoba telah kamu kuasai. Lanjutkan ke pelajaran berikutnya.', 'competency' => null];
        }
        usort($open, fn ($a, $b) => $a['p'] <=> $b['p']);

        return ['text' => 'Langkah berikutnya: latihan ' . $open[0]['competency'] . ' beberapa soal lagi, pakai hint secara bertahap bila perlu.', 'competency' => $open[0]['competency']];
    }
}
