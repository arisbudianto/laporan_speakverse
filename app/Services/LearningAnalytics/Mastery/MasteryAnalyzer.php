<?php

namespace App\Services\LearningAnalytics\Mastery;

/**
 * Penguasaan per kompetensi (kelas murni).
 *
 * Masukan: obs[competency][user] = urutan benar(1)/salah(0) pada PERCOBAAN PERTAMA tiap soal, urut waktu.
 *
 * - Parameter BKT dicocokkan per kompetensi bila ≥ minObs pengamatan dan ≥ minSeq siswa; bila tidak,
 *   dipakai DEFAULTS (ditandai 'default' = true; interpretasi lebih lemah).
 * - mastered   : P(L) ≥ masteryThreshold (default 0,95).
 * - wheel spinning (Beck & Gong, 2013): ≥ wheelAttempts pengamatan (default 10), belum menguasai, DAN P(L) naik < wheelMinGain pada wheelWindow pengamatan terakhir.
 * - reliable: BKT hanya dipercaya bila hasil fit (kompetensi cukup data) mengungguli pembanding konstan (log-likelihood lebih besar > 3).
 *   Bila tidak reliable, status 'dikuasai' dan 'berputar-putar' tidak diberikan; parameter yang menempel batas grid ditandai (at_bound).
 * - Kurva belajar: tingkat kesalahan pada kesempatan ke-k (gabungan siswa), dicocokkan hukum pangkat
 *   e(k) = a·k^b lewat OLS pada ln e vs ln k (hanya titik dengan e > 0 dan ≥ 5 pengamatan). b < 0 → belajar.
 */
final class MasteryAnalyzer
{
    public function __construct(
        private float $masteryThreshold = 0.95,
        private int $wheelAttempts = 10,
        private int $minObs = 30,
        private int $minSeq = 5,
        private float $wheelMinGain = 0.05,
        private int $wheelWindow = 5,
    ) {
    }

    public function analyze(array $obs): array
    {
        $comps = [];
        $states = [];
        ksort($obs);
        foreach ($obs as $comp => $byUser) {
            $seqs = array_values(array_filter($byUser, fn ($s) => count($s) > 0));
            if (! $seqs) {
                continue;
            }
            $nObs = array_sum(array_map('count', $seqs));
            $fitted = ($nObs >= $this->minObs && count($seqs) >= $this->minSeq);
            if ($fitted) {
                $fit = BktModel::fit($seqs);
                $prm = $fit['params'];
                $ll = $fit['ll'];
            } else {
                $prm = BktModel::DEFAULTS;
                $ll = array_sum(array_map(fn ($s) => BktModel::run($s, $prm)['ll'], $seqs));
            }
            // diagnostik kecocokan: BKT harus lebih baik daripada pembanding konstan (selisih ll > selisih jumlah parameter)
            $llBase = BktModel::baselineLl($seqs);
            $bound = $fitted ? BktModel::atBound($prm) : [];
            $reliable = $fitted && ($ll - $llBase) > 3.0;
            $mastered = 0;
            $wheel = 0;
            foreach ($byUser as $uid => $seq) {
                if (! $seq) {
                    continue;
                }
                $r = BktModel::run($seq, $prm);
                $isM = $reliable && $r['p'] >= $this->masteryThreshold;
                $nn = count($r['trace']);
                $stagnant = $nn <= $this->wheelWindow || ($r['trace'][$nn - 1] - $r['trace'][$nn - 1 - $this->wheelWindow]) < $this->wheelMinGain;
                $isW = $reliable && ! $isM && $r['p'] < $this->masteryThreshold && count($seq) >= $this->wheelAttempts && $stagnant;
                $first = null;
                foreach ($r['trace'] as $i => $pp) {
                    if ($pp >= $this->masteryThreshold) {
                        $first = $i + 1;
                        break;
                    }
                }
                $states[] = ['user' => $uid, 'competency' => $comp, 'p' => round($r['p'], 4), 'n_obs' => count($seq),
                    'mastered' => $isM, 'wheel_spinning' => $isW, 'opportunities_to_mastery' => $first, 'reliable' => $reliable];
                $mastered += $isM ? 1 : 0;
                $wheel += $isW ? 1 : 0;
            }
            $comps[] = [
                'competency' => $comp, 'students' => count($seqs), 'n_obs' => $nObs, 'default' => ! $fitted,
                'params' => array_map(fn ($v) => round($v, 3), $prm), 'll' => round($ll, 2),
                'reliable' => $reliable, 'at_bound' => $bound, 'll_baseline' => round($llBase, 2),
                'mastered' => $mastered, 'wheel_spinning' => $wheel,
                'mastered_pct' => round(100 * $mastered / count($seqs), 1),
                'curve' => self::curve($seqs),
            ];
        }

        return ['competencies' => $comps, 'states' => $states];
    }

    /** @return array{points:array<int,array{k:int,error:float,n:int}>,a:?float,b:?float,r2:?float} */
    public static function curve(array $seqs, int $maxK = 20): array
    {
        $pts = [];
        for ($k = 1; $k <= $maxK; $k++) {
            $n = $w = 0;
            foreach ($seqs as $s) {
                if (isset($s[$k - 1])) {
                    $n++;
                    $w += $s[$k - 1] ? 0 : 1;
                }
            }
            if ($n > 0) {
                $pts[] = ['k' => $k, 'error' => round($w / $n, 4), 'n' => $n];
            }
        }
        $use = array_values(array_filter($pts, fn ($p) => $p['n'] >= 5 && $p['error'] > 0));
        $a = $b = $r2 = null;
        if (count($use) >= 3) {
            $x = array_map(fn ($p) => log($p['k']), $use);
            $y = array_map(fn ($p) => log($p['error']), $use);
            $n = count($use);
            $mx = array_sum($x) / $n;
            $my = array_sum($y) / $n;
            $sxy = $sxx = $syy = 0.0;
            for ($i = 0; $i < $n; $i++) {
                $sxy += ($x[$i] - $mx) * ($y[$i] - $my);
                $sxx += ($x[$i] - $mx) ** 2;
                $syy += ($y[$i] - $my) ** 2;
            }
            if ($sxx > 1e-12) {
                $b = $sxy / $sxx;
                $a = exp($my - $b * $mx);
                $r2 = $syy > 1e-12 ? ($sxy ** 2) / ($sxx * $syy) : null;
            }
        }

        return ['points' => $pts, 'a' => $a === null ? null : round($a, 4), 'b' => $b === null ? null : round($b, 4), 'r2' => $r2 === null ? null : round($r2, 3)];
    }
}
