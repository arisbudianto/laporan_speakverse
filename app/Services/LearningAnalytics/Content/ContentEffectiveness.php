<?php

namespace App\Services\LearningAnalytics\Content;

use App\Services\LearningAnalytics\Stats\Ols;

/**
 * Efektivitas pelajaran sebagai ASOSIASI dosis–gain (bukan sebab-akibat), kelas murni.
 *
 * Per pelajaran, untuk siswa berpasangan (punya gain dan pretest):
 *   gain = β0 + β1·dosis + β2·pretest + ε,  dosis = jumlah butir (percobaan pertama) yang dijawab pada pelajaran itu.
 * β1 dilaporkan per 10 butir tambahan, CI95, p (OLS klasik). Syarat: ≥ minN siswa berpasangan, ≥ minExposed siswa
 * dengan dosis > 0, dan dosis bervariasi. Pretest dikendalikan agar siswa berskor awal tinggi (gain kecil karena langit-langit)
 * tidak mengacaukan. Penanda: 'terkait kenaikan skor' (β1 > 0, p < 0,05); 'sering ditinggalkan' (abandon ≥ 30% dari ≥ 10 percobaan).
 *
 * @param array<int,array{lesson:int,name:string,starts:int,submits:int,abandons:int,first_try_acc:?float}> $lessons
 * @param array<int,array<int,int>> $dose  lesson => [user => butir]
 * @param array<int,float> $gain  user => post − pre
 * @param array<int,float> $pre   user => pretest
 */
final class ContentEffectiveness
{
    public function __construct(private int $minN = 20, private int $minExposed = 5)
    {
    }

    public function rank(array $lessons, array $dose, array $gain, array $pre, array $completed = []): array
    {
        $out = [];
        foreach ($lessons as $l) {
            $d = $dose[$l['lesson']] ?? [];
            $att = $l['submits'] + $l['abandons'];
            $abandon = $att > 0 ? $l['abandons'] / $att : null;
            $users = array_values(array_filter(array_keys($gain), fn ($u) => isset($pre[$u])));
            $exposed = count(array_filter($users, fn ($u) => ($d[$u] ?? 0) > 0));
            $x = $y = [];
            foreach ($users as $u) {
                $x[] = [1.0, (float) ($d[$u] ?? 0), (float) $pre[$u]];
                $y[] = (float) $gain[$u];
            }
            $fit = null;
            $why = null;
            if (count($users) < $this->minN) {
                $why = 'siswa berpasangan < ' . $this->minN;
            } elseif ($exposed < $this->minExposed) {
                $why = 'siswa yang mengerjakan < ' . $this->minExposed;
            } elseif (count(array_unique(array_column($x, 1))) < 3) {
                $why = 'dosis tidak bervariasi';
            } else {
                $fit = Ols::fit($x, $y);
                $why = $fit ? null : 'model tidak dapat diestimasi';
            }
            $flags = [];
            if ($fit && $fit['beta'][1] > 0 && $fit['p'][1] < 0.05) {
                $flags[] = 'terkait kenaikan skor';
            }
            if ($abandon !== null && $att >= 10 && $abandon >= 0.30) {
                $flags[] = 'sering ditinggalkan';
            }
            $out[] = $l + [
                'abandon_rate' => $abandon === null ? null : round($abandon, 3),
                'n_paired' => count($users), 'n_exposed' => $exposed, 'n_completed' => count($completed[$l['lesson']] ?? []),
                'per10' => $fit ? round($fit['beta'][1] * 10, 2) : null,
                'per10_ci' => $fit ? [round($fit['ci'][1][0] * 10, 2), round($fit['ci'][1][1] * 10, 2)] : null,
                'p' => $fit ? $fit['p'][1] : null, 'why_not' => $why, 'flags' => $flags,
            ];
        }
        usort($out, fn ($a, $b) => [$b['starts'], $b['n_exposed']] <=> [$a['starts'], $a['n_exposed']]);

        return $out;
    }
}
