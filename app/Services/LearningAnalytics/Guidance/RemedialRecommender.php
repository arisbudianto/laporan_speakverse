<?php

namespace App\Services\LearningAnalytics\Guidance;

/**
 * Rekomendasi remedial kelas (aturan eksplisit, bukan model):
 *  - Kompetensi dengan rerata P(menguasai) terendah (siswa berdata ≥ minStudents) dan persentase menguasai < 60% → "ulang/perkuat".
 *  - Jenis kesalahan Reading dominan (≥ 35% dari total, total ≥ 10) → strategi pengajaran terkait.
 *
 * @param array<int,array{competency:string,students:int,mean_p:float,mastered_pct:float}> $comps
 * @param array<string,int> $errors  kode galat → jumlah
 */
final class RemedialRecommender
{
    private const ERROR_ADVICE = [
        'inferential' => 'Banyak salah inferensial: latih menyimpulkan makna tersirat (think-aloud, tanya "apa petunjuk di teks?").',
        'lexical' => 'Banyak salah leksikal: perkuat kosakata dalam konteks (kata kunci, sinonim, tebak makna dari kalimat sekitar).',
        'syntactic' => 'Banyak salah sintaksis: latih struktur kalimat (subjek-predikat-objek, klausa, kata hubung).',
        'context_misconception' => 'Banyak salah paham konteks: bahas ulang tujuan dan situasi teks sebelum menjawab.',
    ];

    public function recommend(array $comps, array $errors, int $minStudents = 5, int $max = 3): array
    {
        $out = [];
        $cand = array_values(array_filter($comps, fn ($c) => $c['students'] >= $minStudents && $c['mastered_pct'] < 60));
        usort($cand, fn ($a, $b) => $a['mean_p'] <=> $b['mean_p']);
        foreach (array_slice($cand, 0, $max) as $c) {
            $out[] = ['type' => 'kompetensi', 'title' => $c['competency'],
                'text' => sprintf('Rerata penguasaan %d%% dan hanya %d%% siswa menguasai. Ulang/perkuat kompetensi ini sebelum lanjut.', round($c['mean_p'] * 100), round($c['mastered_pct']))];
        }
        $total = array_sum($errors);
        if ($total >= 10) {
            arsort($errors);
            $top = array_key_first($errors);
            if ($errors[$top] / $total >= 0.35 && isset(self::ERROR_ADVICE[$top])) {
                $out[] = ['type' => 'kesalahan', 'title' => $top, 'text' => self::ERROR_ADVICE[$top] . sprintf(' (%d%% dari %d kesalahan).', round($errors[$top] * 100 / $total), $total)];
            }
        }

        return $out;
    }
}
