<?php

namespace App\Services\LearningAnalytics\Items;

use App\Services\LearningAnalytics\Stats\Descriptive as D;

/**
 * Analisis butir Teori Tes Klasik (CTT), kelas murni.
 *
 * Masukan: baris jawaban pilihan ganda
 *   {sub:int, user:int, form:string, item:string, option:?string, correct:0|1}
 * 'form' = kumpulan soal yang diujikan bersama (mis. pretest|reading|unit|lesson).
 *
 * Aturan:
 *  - Satu jawaban per siswa per form (submission paling awal) agar pengulangan tidak menggembungkan n.
 *  - Soal yang dianalisis: yang muncul pada ≥80% submission form; submission harus memuat semua soal tsb.
 *  - p (kesukaran) = proporsi benar. r_pb = korelasi point-biserial soal vs SKOR SISA (total − soal), mencegah inflasi.
 *  - D = p(kelompok atas 27%) − p(kelompok bawah 27%) berdasar skor total.
 *  - Reliabilitas: Cronbach α (= KR-20 untuk soal 0/1, varians sampel): α = k/(k−1)·(1 − Σ s²_i / s²_total).
 *    SEM = s_total·√(1−α).
 *  - Penanda: terlalu mudah (p>0,90), terlalu sulit (p<0,20), daya beda rendah (r_pb<0,20), negatif (r_pb<0),
 *    distraktor mati (<5% pemilih, n≥30), kunci diduga salah (r_pb<0 dan pada kelompok atas ada opsi non-kunci lebih populer dari kunci).
 *  - n < 30: hasil ditandai "indikatif".
 */
final class ItemAnalyzer
{
    public function __construct(private int $minN = 30)
    {
    }

    /** @return array{forms:array<int,array>,items:array<int,array>} */
    public function analyze(array $rows): array
    {
        $byForm = [];
        foreach ($rows as $r) {
            $byForm[$r['form']][$r['user']][$r['sub']][$r['item']] = $r;
        }
        $forms = [];
        $items = [];
        foreach ($byForm as $form => $users) {
            // submission paling awal per siswa
            $subs = [];
            foreach ($users as $uid => $bySub) {
                ksort($bySub);
                $subs[] = reset($bySub);
            }
            $nSubs = count($subs);
            $freq = [];
            foreach ($subs as $s) {
                foreach (array_keys($s) as $it) {
                    $freq[$it] = ($freq[$it] ?? 0) + 1;
                }
            }
            $keep = array_keys(array_filter($freq, fn ($c) => $c >= 0.8 * $nSubs));
            sort($keep);
            $k = count($keep);
            $mat = [];
            foreach ($subs as $s) {
                $ok = true;
                foreach ($keep as $it) {
                    if (! isset($s[$it])) {
                        $ok = false;
                        break;
                    }
                }
                if ($ok) {
                    $mat[] = $s;
                }
            }
            $n = count($mat);
            if ($k < 2 || $n < 2) {
                continue;
            }
            $totals = [];
            foreach ($mat as $i => $s) {
                $t = 0;
                foreach ($keep as $it) {
                    $t += (int) $s[$it]['correct'];
                }
                $totals[$i] = $t;
            }
            $sdT = D::sd($totals);
            $varItems = 0.0;
            $itemStats = [];
            $vec = [];
            // kelompok atas/bawah 27%
            $order = array_keys($totals);
            usort($order, fn ($a, $b) => $totals[$a] <=> $totals[$b]);
            $g = max(1, (int) round(0.27 * $n));
            $low = array_slice($order, 0, $g);
            $high = array_slice($order, -$g);

            foreach ($keep as $it) {
                $x = [];
                foreach ($mat as $i => $s) {
                    $x[$i] = (int) $s[$it]['correct'];
                }
                $p = array_sum($x) / $n;
                $vec[$it] = $x;
                $varItems += (D::sd($x) ?? 0) ** 2;
                $rest = [];
                foreach ($x as $i => $v) {
                    $rest[$i] = $totals[$i] - $v;
                }
                $r = self::corr(array_values($x), array_values($rest));
                $pHi = array_sum(array_map(fn ($i) => $x[$i], $high)) / $g;
                $pLo = array_sum(array_map(fn ($i) => $x[$i], $low)) / $g;
                // opsi
                $opt = [];
                $optHi = [];
                $key = null;
                $keyCount = [];
                foreach ($mat as $i => $s) {
                    $o = $s[$it]['option'];
                    if ($o !== null && $o !== '') {
                        $opt[$o] = ($opt[$o] ?? 0) + 1;
                        if ($s[$it]['correct']) {
                            $keyCount[$o] = ($keyCount[$o] ?? 0) + 1;
                        }
                        if (in_array($i, $high, true)) {
                            $optHi[$o] = ($optHi[$o] ?? 0) + 1;
                        }
                    }
                }
                if ($keyCount) {
                    arsort($keyCount);
                    $key = (string) array_key_first($keyCount);
                }
                $flags = [];
                if ($p > 0.90) {
                    $flags[] = 'terlalu mudah';
                }
                if ($p < 0.20) {
                    $flags[] = 'terlalu sulit';
                }
                if ($r !== null && $r < 0.20) {
                    $flags[] = $r < 0 ? 'daya beda negatif' : 'daya beda rendah';
                }
                if ($key !== null && $n >= $this->minN) {
                    foreach ($opt as $o => $c) {
                        if ((string) $o !== $key && $c / $n < 0.05) {
                            $flags[] = "distraktor $o hampir tak dipilih";
                        }
                    }
                }
                if ($key !== null && $r !== null && $r < 0 && $optHi) {
                    $keyHi = $optHi[$key] ?? 0;
                    foreach ($optHi as $o => $c) {
                        if ((string) $o !== $key && $c > $keyHi) {
                            $flags[] = 'kunci diduga salah';
                            break;
                        }
                    }
                }
                ksort($opt);
                $itemStats[] = [
                    'form' => $form, 'item' => $it, 'n' => $n, 'p' => round($p, 3),
                    'r_pb' => $r === null ? null : round($r, 3), 'd_index' => round($pHi - $pLo, 3),
                    'key' => $key, 'options' => $opt, 'flags' => array_values(array_unique($flags)),
                    'indicative' => $n < $this->minN,
                ];
            }
            // α bila butir bermasalah berat (kunci diduga salah / daya beda negatif) dibuang
            $bad = [];
            foreach ($itemStats as $st) {
                if (array_intersect($st['flags'], ['kunci diduga salah', 'daya beda negatif'])) {
                    $bad[] = $st['item'];
                }
            }
            $alphaWo = null;
            $rest = array_values(array_diff($keep, $bad));
            if ($bad && count($rest) >= 2) {
                $tot2 = [];
                foreach ($mat as $i => $_) {
                    $tot2[$i] = array_sum(array_map(fn ($it) => $vec[$it][$i], $rest));
                }
                $sd2 = D::sd($tot2);
                $vi2 = array_sum(array_map(fn ($it) => (D::sd($vec[$it]) ?? 0) ** 2, $rest));
                $alphaWo = ($sd2 !== null && $sd2 > 1e-6) ? round((count($rest) / (count($rest) - 1)) * (1 - $vi2 / ($sd2 ** 2)), 3) : null;
            }
            $varT = $sdT === null ? 0.0 : $sdT ** 2;
            $alpha = ($k > 1 && $varT > 1e-12) ? ($k / ($k - 1)) * (1 - $varItems / $varT) : null;
            $forms[] = [
                'form' => $form, 'n' => $n, 'k' => $k, 'mean' => round(D::mean($totals), 2), 'sd' => $sdT === null ? null : round($sdT, 2),
                'alpha' => $alpha === null ? null : round($alpha, 3),
                'sem' => ($alpha === null || $sdT === null) ? null : round($sdT * sqrt(max(0.0, 1 - $alpha)), 2),
                'indicative' => $n < $this->minN, 'dropped_items' => count($freq) - $k,
                'n_flagged_severe' => count($bad), 'alpha_without_flagged' => $alphaWo,
                'n_flagged' => count(array_filter($itemStats, fn ($st) => count($st['flags']) > 0)),
            ];
            array_push($items, ...$itemStats);
        }

        return ['forms' => $forms, 'items' => $items];
    }

    public static function corr(array $x, array $y): ?float
    {
        $n = count($x);
        if ($n < 3) {
            return null;
        }
        $mx = array_sum($x) / $n;
        $my = array_sum($y) / $n;
        $sxy = $sxx = $syy = 0.0;
        for ($i = 0; $i < $n; $i++) {
            $sxy += ($x[$i] - $mx) * ($y[$i] - $my);
            $sxx += ($x[$i] - $mx) ** 2;
            $syy += ($y[$i] - $my) ** 2;
        }

        return ($sxx < 1e-12 || $syy < 1e-12) ? null : $sxy / sqrt($sxx * $syy);
    }
}
