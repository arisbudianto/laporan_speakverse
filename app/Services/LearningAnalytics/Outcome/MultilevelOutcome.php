<?php

namespace App\Services\LearningAnalytics\Outcome;

use App\Services\LearningAnalytics\Stats\MixedModel;

/**
 * Model multilevel untuk gain (post − pre): siswa tersarang dalam kelas.
 *   Model 1: gain ~ 1 + pretest (tengah rata-rata)           intersep acak per kelas
 *   Model 2: + perlakuan (kelompok penelitian, tingkat-kelas) bila ≥ 2 kelas di tiap kelompok
 * Kelas yang dipakai: ≥ minClass siswa berpasangan; butuh ≥ 3 kelas. Hasil bersifat observasional kecuali penugasan kelompok diacak.
 */
final class MultilevelOutcome
{
    public function __construct(private int $minClass = 5, private int $minClasses = 3)
    {
    }

    /**
     * @param array<int,array{id:int,school:?string,major:?string,grade:?string,parallel:?string,pre:?float,gain:?float}> $students
     * @param array<int,?string> $groupByUser research_group per user id
     */
    public function analyze(array $students, array $groupByUser = []): array
    {
        $rows = [];
        foreach ($students as $s) {
            if ($s['pre'] === null || $s['gain'] === null) {
                continue;
            }
            $rows[] = ['cls' => ($s['school'] ?: '(Belum diisi)') . '|' . $s['major'] . '|' . $s['grade'] . '|' . $s['parallel'], 'pre' => (float) $s['pre'], 'gain' => (float) $s['gain'], 'grp' => $groupByUser[$s['id']] ?? null];
        }
        $sizes = array_count_values(array_column($rows, 'cls'));
        $rows = array_values(array_filter($rows, fn ($r) => $sizes[$r['cls']] >= $this->minClass));
        $nClasses = count(array_unique(array_column($rows, 'cls')));
        $out = ['ready' => false, 'n' => count($rows), 'classes' => $nClasses, 'reason' => null, 'm1' => null, 'm2' => null, 'notes' => []];
        if ($nClasses < $this->minClasses) {
            $out['reason'] = "Butuh ≥ {$this->minClasses} kelas dengan ≥ {$this->minClass} siswa berpasangan pre-post (saat ini $nClasses kelas).";

            return $out;
        }
        $mp = array_sum(array_column($rows, 'pre')) / count($rows);
        $y = array_column($rows, 'gain');
        $g = array_column($rows, 'cls');
        $x1 = array_map(fn ($r) => [1.0, $r['pre'] - $mp], $rows);
        $m1 = MixedModel::fit($x1, $y, $g);
        if ($m1 === null) {
            $out['reason'] = 'Model tidak dapat diestimasi dari data ini.';

            return $out;
        }
        $out['ready'] = true;
        $out['pre_mean'] = round($mp, 2);
        $out['m1'] = $this->pack($m1, ['Intersep (gain rata-rata, pretest rata-rata)', 'Pretest (per poin)']);

        $classGroup = [];
        foreach ($rows as $r) {
            if ($r['grp'] === 'kontrol' || $r['grp'] === 'perlakuan') {
                $classGroup[$r['cls']][$r['grp']] = true;
            }
        }
        $kontrol = $perlakuan = 0;
        $clean = true;
        foreach ($classGroup as $c => $gs) {
            if (count($gs) > 1) {
                $clean = false;
            }
            isset($gs['kontrol']) && $kontrol++;
            isset($gs['perlakuan']) && $perlakuan++;
        }
        if ($kontrol >= 2 && $perlakuan >= 2 && $clean) {
            $rows2 = array_values(array_filter($rows, fn ($r) => isset($classGroup[$r['cls']])));
            $x2 = array_map(fn ($r) => [1.0, $r['pre'] - $mp, $r['grp'] === 'perlakuan' ? 1.0 : 0.0], $rows2);
            $m2 = MixedModel::fit($x2, array_column($rows2, 'gain'), array_column($rows2, 'cls'), [2]);
            if ($m2) {
                $out['m2'] = $this->pack($m2, ['Intersep (kontrol)', 'Pretest (per poin)', 'Perlakuan − kontrol']);
                $out['m2']['classes_kontrol'] = $kontrol;
                $out['m2']['classes_perlakuan'] = $perlakuan;
            }
        } else {
            $out['notes'][] = 'Efek perlakuan tidak diestimasi: butuh ≥ 2 kelas kontrol dan ≥ 2 kelas perlakuan, tiap kelas hanya satu kelompok (saat ini kontrol ' . $kontrol . ', perlakuan ' . $perlakuan . ').';
        }
        $out['notes'][] = 'Intersep acak per kelas; SE mengabaikan ketidakpastian varians kelas (agak optimistis bila kelas < 10). Penugasan kelompok tidak diacak → bukan bukti kausal.';
        if ($m1['boundary'] === 'sigma_u = 0') {
            $out['notes'][] = 'Varians antar-kelas diestimasi 0 (tidak ada bukti perbedaan antar kelas setelah memperhitungkan pretest).';
        }

        return $out;
    }

    private function pack(array $m, array $labels): array
    {
        $fixed = [];
        foreach ($labels as $i => $lb) {
            $fixed[] = ['label' => $lb, 'b' => round($m['beta'][$i], 3), 'se' => round($m['se'][$i], 3), 'ci' => [round($m['ci'][$i][0], 3), round($m['ci'][$i][1], 3)], 'p' => $m['p'][$i], 'df' => $m['df'][$i]];
        }

        return ['fixed' => $fixed, 'sigma2_u' => round($m['sigma2_u'], 3), 'sigma2_e' => round($m['sigma2_e'], 3), 'icc' => $m['icc'] === null ? null : round($m['icc'], 3),
            'lrt' => round($m['lrt'], 2), 'lrt_p' => $m['lrt_p'], 'groups' => $m['groups'], 'n' => $m['n']];
    }
}
