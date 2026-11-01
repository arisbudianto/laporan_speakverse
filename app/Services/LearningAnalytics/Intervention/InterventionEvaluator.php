<?php

namespace App\Services\LearningAnalytics\Intervention;

use App\Services\LearningAnalytics\Stats\Descriptive as D;
use App\Services\LearningAnalytics\Stats\Inference as I;

/**
 * Evaluasi dampak tindak lanjut guru pada keterlibatan (kelas murni).
 *
 * Satuan: siswa yang ditandai "perlu dicek". Hasil = perubahan jumlah hari aktif per 14 hari (sesudah − sebelum).
 *   - Dalam kelompok ditindaklanjuti: t berpasangan sebelum vs sesudah.
 *   - Difference-in-differences sederhana: selisih rerata perubahan (ditindaklanjuti − tidak ditindaklanjuti),
 *     uji Welch, CI 95%, Hedges' g.  Kelompok pembanding = siswa bertanda yang tidak ditindaklanjuti pada periode sama.
 * KETERBATASAN: penugasan tidak acak (guru memilih siapa yang ditindaklanjuti → bias seleksi), regresi ke rerata
 * (siswa dipilih saat aktivitasnya rendah dan cenderung naik sendiri), n kecil. Hasil bersifat indikatif, bukan bukti kausal.
 *
 * @param array<int,array{user:int,followed:bool,before:float,after:float}> $rows
 */
final class InterventionEvaluator
{
    public function __construct(private int $minGroup = 5)
    {
    }

    public function evaluate(array $rows): array
    {
        $f = $c = [];
        $fb = $fa = [];
        foreach ($rows as $r) {
            $d = $r['after'] - $r['before'];
            if ($r['followed']) {
                $f[] = $d;
                $fb[] = $r['before'];
                $fa[] = $r['after'];
            } else {
                $c[] = $d;
            }
        }
        $out = ['followed' => count($f), 'not_followed' => count($c), 'ready' => false, 'reason' => null,
            'mean_change_followed' => $f ? round(D::mean($f), 2) : null, 'mean_change_control' => $c ? round(D::mean($c), 2) : null];
        if (count($f) < $this->minGroup || count($c) < $this->minGroup) {
            $out['reason'] = "Butuh ≥{$this->minGroup} siswa pada tiap kelompok (ditindaklanjuti: " . count($f) . ', pembanding: ' . count($c) . ').';

            return $out;
        }
        $out['ready'] = true;
        $out['within'] = I::pairedT($fb, $fa);
        $out['did'] = I::welch($f, $c);
        $out['g'] = I::hedgesGIndependent($f, $c);

        return $out;
    }
}
