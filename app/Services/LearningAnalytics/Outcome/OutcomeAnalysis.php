<?php

namespace App\Services\LearningAnalytics\Outcome;

use App\Services\LearningAnalytics\Stats\Descriptive as D;
use App\Services\LearningAnalytics\Stats\Inference as I;

/**
 * Analisis hasil belajar tingkat lanjut (kelas murni).
 *
 * Masukan: daftar siswa (keluaran SchoolLearningMetrics::compute()['students']) dengan
 * pre, post, school, major, grade, parallel. Pasangan = siswa yang punya pre DAN post.
 *
 * Keluaran: ringkasan berpasangan (t/Wilcoxon sesuai aturan I::chooseTest, CI 95%, Hedges' g, Hake),
 * per sekolah, histogram, ringkas-lima-angka per sekolah, ICC (sekolah dan kelas), uji kesenjangan
 * (Kruskal–Wallis antar sekolah dan antar jurusan). Tidak ada koreksi perbandingan ganda; p hanya indikatif.
 */
final class OutcomeAnalysis
{
    public function __construct(private int $minPairs = 5, private int $iccMinGroup = 5, private int $iccMinGroups = 3)
    {
    }

    public function analyze(array $students): array
    {
        $pre = $post = [];
        $bySchoolPair = [];
        $postBySchool = [];
        $postByClass = [];
        $gainByClass = [];
        $postByMajor = [];
        $gainBySchool = [];
        $hakeStudent = [];
        foreach ($students as $s) {
            $sch = $s['school'] ?: '(Belum diisi)';
            $cls = $sch . '|' . $s['major'] . '|' . $s['grade'] . '|' . $s['parallel'];
            if ($s['post'] !== null) {
                $postBySchool[$sch][] = (float) $s['post'];
                $postByClass[$cls][] = (float) $s['post'];
                $postByMajor[$s['major'] ?: '(Belum diisi)'][] = (float) $s['post'];
            }
            if ($s['pre'] !== null && $s['post'] !== null) {
                $pre[] = (float) $s['pre'];
                $post[] = (float) $s['post'];
                $bySchoolPair[$sch][0][] = (float) $s['pre'];
                $bySchoolPair[$sch][1][] = (float) $s['post'];
                $gainBySchool[$sch][] = $s['post'] - $s['pre'];
                $gainByClass[$cls][] = (float) ($s['post'] - $s['pre']);
                if (($h = I::hakeStudent((float) $s['pre'], (float) $s['post'])) !== null) {
                    $hakeStudent[] = $h;
                }
            }
        }
        $n = count($pre);
        $test = null;
        $effect = null;
        $hake = null;
        $paired = null;
        if ($n >= 2) {
            $kind = I::chooseTest($pre, $post);
            $paired = I::pairedT($pre, $post);
            $wil = I::wilcoxon($pre, $post);
            $test = [
                'chosen' => $kind,
                'reason' => $kind === 't'
                    ? 'n ≥ 20, selisih tidak miring (|skew|<1) dan kurtosis wajar (|ekses|<2) → t berpasangan'
                    : 'n < 20 atau selisih miring/berekor tebal → Wilcoxon signed-rank',
                't' => $paired, 'wilcoxon' => $wil,
                'p' => $kind === 't' ? ($paired['p'] ?? null) : ($wil['p'] ?? null),
            ];
            $effect = I::effectSize($pre, $post);
            $hake = I::hakeClass($pre, $post);
        }

        $schools = [];
        foreach ($bySchoolPair as $name => [$a, $b]) {
            $np = count($a);
            $h = I::hakeClass($a, $b);
            $e = $np >= $this->minPairs ? I::effectSize($a, $b) : null;
            $w = $np >= $this->minPairs ? I::wilcoxon($a, $b) : null;
            $schools[] = [
                'school' => $name, 'pairs' => $np, 'pre' => round(D::mean($a), 1), 'post' => round(D::mean($b), 1),
                'diff' => round(D::mean($b) - D::mean($a), 1), 'hake' => $h ? round($h['g'], 2) : null, 'hake_cat' => $h['category'] ?? null,
                'g' => $e ? round($e['g'], 2) : null, 'g_label' => $e['label'] ?? null, 'p' => $w ? $w['p'] : null,
                'indicative' => $np < $this->minPairs,
            ];
        }
        usort($schools, fn ($x, $y) => strcmp($x['school'], $y['school']));

        $box = [];
        foreach ($postBySchool as $name => $v) {
            $box[] = ['school' => $name, 'n' => count($v), 'min' => min($v), 'q1' => D::quantile($v, .25), 'median' => D::median($v),
                'q3' => D::quantile($v, .75), 'max' => max($v), 'mean' => D::mean($v), 'sd' => D::sd($v)];
        }
        usort($box, fn ($x, $y) => strcmp($x['school'], $y['school']));

        $filterMin = fn (array $g, int $m) => array_filter($g, fn ($v) => count($v) >= $m);
        $equity = [
            'school' => I::kruskalWallis($filterMin($postBySchool, 3)),
            'major' => I::kruskalWallis($filterMin($postByMajor, 3)),
        ];
        foreach ($equity as $k => $e) {
            if ($e) {
                $equity[$k]['significant'] = $e['p'] < 0.05;
                $equity[$k]['size'] = $e['epsilon2'] < 0.01 ? 'diabaikan' : ($e['epsilon2'] < 0.08 ? 'kecil' : ($e['epsilon2'] < 0.26 ? 'sedang' : 'besar'));
            }
        }
        // ICC hanya layak bila ≥ iccMinGroups kelompok masing-masing ≥ iccMinGroup siswa
        $iccOf = function (array $g) use ($filterMin): ?array {
            $ok = $filterMin($g, $this->iccMinGroup);

            return count($ok) >= $this->iccMinGroups ? self::iccRow(I::icc1($ok)) : null;
        };
        $icc = [
            'post_school' => $iccOf($postBySchool), 'post_class' => $iccOf($postByClass),
            'gain_school' => $iccOf($gainBySchool), 'gain_class' => $iccOf($gainByClass),
        ];
        $icc['min_note'] = "ICC diestimasi bila ≥ {$this->iccMinGroups} kelompok dengan ≥ {$this->iccMinGroup} siswa; selain itu tampil \"tidak dapat diestimasi\".";

        return [
            'relative' => $this->relative($students),
            'pre_post_rho' => $n >= 5 ? (\App\Services\LearningAnalytics\Stats\Correlation::spearman($pre, $post)['rho'] ?? null) : null,
            'pairs' => $n,
            // efek minimal terdeteksi (d dalam SD selisih), uji dua sisi α = 0,05, daya 80% (hampiran t)
            'mde_d' => $n >= 3 ? round((\App\Services\LearningAnalytics\Stats\Distributions::tQuantile(0.975, $n - 1) + \App\Services\LearningAnalytics\Stats\Distributions::tQuantile(0.8, $n - 1)) / sqrt($n), 2) : null,
            'mean_pre' => $n ? round(D::mean($pre), 1) : null,
            'mean_post' => $n ? round(D::mean($post), 1) : null,
            'test' => $test, 'effect' => $effect, 'hake' => $hake,
            'hake_student_mean' => $hakeStudent ? round(D::mean($hakeStudent), 2) : null,
            'hake_student_n' => count($hakeStudent),
            'schools' => $schools, 'box' => $box,
            'hist_pre' => self::hist(array_filter(array_column($students, 'pre'), fn ($v) => $v !== null)),
            'hist_post' => self::hist(array_filter(array_column($students, 'post'), fn ($v) => $v !== null)),
            'equity' => $equity, 'icc' => $icc,
        ];
    }

    /**
     * Perubahan posisi relatif: z-skor pre dan post dihitung dalam kelompok berpasangan; Δz rerata per sekolah.
     * Sah walau bentuk tes pre dan post tidak setara (rerata Δz seluruh kohort = 0 menurut konstruksi).
     * @return array{rows:array<int,array{school:string,n:int,dz:float,se:?float}>,note:string}
     */
    public function relative(array $students): array
    {
        $pairs = [];
        foreach ($students as $s) {
            if ($s['pre'] !== null && $s['post'] !== null) {
                $pairs[] = [$s['school'] ?: '(Belum diisi)', (float) $s['pre'], (float) $s['post']];
            }
        }
        $note = 'Δz = perubahan posisi relatif (z-skor pre → post) di dalam kohort berpasangan; rerata seluruh kohort selalu 0.';
        if (count($pairs) < 10) {
            return ['rows' => [], 'note' => $note . ' Butuh ≥10 pasangan.'];
        }
        $pre = array_column($pairs, 1);
        $post = array_column($pairs, 2);
        $mp = D::mean($pre);
        $mq = D::mean($post);
        $sp = sqrt(array_sum(array_map(fn ($v) => ($v - $mp) ** 2, $pre)) / count($pre));
        $sq = sqrt(array_sum(array_map(fn ($v) => ($v - $mq) ** 2, $post)) / count($post));
        if ($sp < 1e-9 || $sq < 1e-9) {
            return ['rows' => [], 'note' => $note . ' Sebaran skor nol.'];
        }
        $by = [];
        foreach ($pairs as [$sch, $a, $b]) {
            $by[$sch][] = ($b - $mq) / $sq - ($a - $mp) / $sp;
        }
        $rows = [];
        foreach ($by as $sch => $d) {
            if (count($d) >= $this->minPairs) {
                $sd = D::sd($d);
                $rows[] = ['school' => $sch, 'n' => count($d), 'dz' => round(D::mean($d), 2), 'se' => $sd === null ? null : round($sd / sqrt(count($d)), 2)];
            }
        }
        usort($rows, fn ($x, $y) => strcmp($x['school'], $y['school']));

        return ['rows' => $rows, 'note' => $note];
    }

    private static function iccRow(?array $i): ?array
    {
        if (! $i) {
            return null;
        }
        $v = max(0.0, $i['icc']);

        return $i + ['label' => $v < 0.05 ? 'kecil' : ($v < 0.15 ? 'sedang' : 'besar')];
    }

    /** @return int[] 10 kotak: 0–9,10–19,…,90–100 */
    public static function hist(array $v): array
    {
        $h = array_fill(0, 10, 0);
        foreach ($v as $x) {
            $h[(int) min(9, max(0, floor($x / 10)))]++;
        }

        return $h;
    }
}
