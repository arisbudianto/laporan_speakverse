<?php

namespace App\Services\LearningAnalytics\Srl;

use App\Services\LearningAnalytics\Stats\Correlation;
use App\Services\LearningAnalytics\Stats\Multiple;
use App\Services\LearningAnalytics\Stats\Descriptive as D;
use App\Services\LearningAnalytics\Stats\KMeans;

/**
 * Profil perilaku belajar lewat k-means pada fitur ter-standardisasi (z-score) + korelasi Spearman fitur-gain.
 *
 * Fitur: first_try_acc, hint_rate, revision_rate, ln(median_ms), fast_wrong_rate, regularity_index (bila tersedia).
 * Nilai kosong diisi rerata (z = 0). Fitur dengan >50% kosong dibuang. Hanya siswa 'enough' (butir ≥ minItems).
 * k dipilih 2..kMax dengan skor siluet tertinggi; syarat n ≥ minStudents (default 20).
 * Nama profil diberikan oleh aturan pada pusat klaster (z): lihat nameFor(). Profil adalah RINGKASAN DESKRIPTIF kelompok,
 * bukan label permanen individu.
 */
final class BehaviorClusters
{
    public const FEATURES = ['first_try_acc', 'hint_rate', 'revision_rate', 'log_ms', 'fast_wrong_rate', 'regularity_index'];

    public const FEATURE_LABELS = [
        'first_try_acc' => 'Akurasi percobaan pertama', 'hint_rate' => 'Penggunaan hint', 'revision_rate' => 'Tingkat revisi jawaban',
        'log_ms' => 'Waktu respons (ln ms)', 'fast_wrong_rate' => 'Salah cepat (<3 dtk)', 'regularity_index' => 'Indeks keteraturan',
    ];

    public function __construct(private int $minStudents = 20, private int $kMax = 5, private int $seed = 42)
    {
    }

    /**
     * @param array<int,array> $srl   keluaran SrlFeatures::compute
     * @param array<int,?float> $regularity user => indeks keteraturan
     * @param array<int,?float> $gain       user => selisih post-pre
     */
    public function run(array $srl, array $regularity = [], array $gain = []): array
    {
        $raw = [];
        foreach ($srl as $uid => $f) {
            if (! $f['enough']) {
                continue;
            }
            $raw[$uid] = [
                'first_try_acc' => $f['first_try_acc'], 'hint_rate' => $f['hint_rate'], 'revision_rate' => $f['revision_rate'],
                'log_ms' => $f['median_ms'] ? log($f['median_ms']) : null, 'fast_wrong_rate' => $f['fast_wrong_rate'],
                'regularity_index' => $regularity[$uid] ?? null,
            ];
        }
        $out = ['n' => count($raw), 'ready' => false, 'features' => [], 'clusters' => [], 'assign' => [], 'silhouette' => null, 'k' => null, 'correlations' => []];
        $out['correlations'] = $this->correlations($raw, $gain);
        if (count($raw) < $this->minStudents) {
            return $out;
        }
        $feat = [];
        foreach (self::FEATURES as $name) {
            $vals = array_filter(array_column($raw, $name), fn ($v) => $v !== null);
            if (count($vals) >= 0.5 * count($raw) && (D::sd($vals) ?? 0) > 1e-9) {
                $feat[$name] = ['mean' => D::mean($vals), 'sd' => D::sd($vals)];
            }
        }
        if (count($feat) < 3) {
            return $out;
        }
        $ids = array_keys($raw);
        $x = [];
        foreach ($ids as $uid) {
            $row = [];
            foreach ($feat as $name => $p) {
                $v = $raw[$uid][$name];
                $row[] = $v === null ? 0.0 : ($v - $p['mean']) / $p['sd'];
            }
            $x[] = $row;
        }
        $best = null;
        for ($k = 2; $k <= min($this->kMax, intdiv(count($ids), 5)); $k++) {
            $fit = KMeans::fit($x, $k, 10, $this->seed);
            $sil = KMeans::silhouette($x, $fit['labels']);
            if ($sil !== null && ($best === null || $sil > $best['sil'] + 1e-9)) {
                $best = ['k' => $k, 'fit' => $fit, 'sil' => $sil];
            }
        }
        if (! $best) {
            return $out;
        }
        $names = array_keys($feat);
        $clusters = [];
        $used = [];
        foreach ($best['fit']['centroids'] as $j => $c) {
            $z = array_combine($names, $c);
            $members = array_keys(array_filter($best['fit']['labels'], fn ($l) => $l === $j));
            $means = [];
            foreach ($names as $nm) {
                $vals = array_filter(array_map(fn ($i) => $raw[$ids[$i]][$nm], $members), fn ($v) => $v !== null);
                $means[$nm] = $vals ? round(D::mean($vals), 3) : null;
            }
            $label = $this->nameFor($z);
            $used[$label] = ($used[$label] ?? 0) + 1;
            $clusters[$j] = ['id' => $j, 'label' => $label . ($used[$label] > 1 ? ' ' . $used[$label] : ''), 'n' => count($members), 'z' => array_map(fn ($v) => round($v, 2), $z), 'means' => $means];
        }
        foreach ($ids as $i => $uid) {
            $out['assign'][$uid] = $best['fit']['labels'][$i];
        }
        $out['ready'] = true;
        $out['features'] = $names;
        $out['clusters'] = array_values($clusters);
        $out['silhouette'] = round($best['sil'], 3);
        $out['k'] = $best['k'];

        return $out;
    }

    /** @param array<string,float> $z pusat klaster dalam z-score */
    public function nameFor(array $z): string
    {
        $g = fn ($k) => $z[$k] ?? 0.0;
        if ($g('fast_wrong_rate') > 0.5) {
            return 'Tergesa-gesa';
        }
        if ($g('hint_rate') > 0.5 && $g('first_try_acc') < 0.25) {
            return 'Bergantung hint';
        }
        if ($g('regularity_index') < -0.5) {
            return 'Sporadis';
        }
        if ($g('first_try_acc') > 0.25 && $g('hint_rate') < 0.25 && $g('regularity_index') >= -0.25) {
            return 'Konsisten-mandiri';
        }
        if ($g('revision_rate') > 0.5) {
            return 'Teliti-merevisi';
        }

        return 'Campuran';
    }

    /** Spearman fitur-gain (peringatan: korelasi ≠ sebab). */
    private function correlations(array $raw, array $gain): array
    {
        $res = [];
        foreach (self::FEATURES as $name) {
            $x = $y = [];
            foreach ($raw as $uid => $f) {
                if ($f[$name] !== null && isset($gain[$uid]) && $gain[$uid] !== null) {
                    $x[] = $f[$name];
                    $y[] = $gain[$uid];
                }
            }
            $c = Correlation::spearman($x, $y);
            if ($c && $c['n'] >= 10) {
                $res[$name] = ['rho' => round($c['rho'], 3), 'p' => $c['p'] === null ? null : round($c['p'], 4), 'n' => $c['n']];
            }
        }

        $holm = Multiple::holm(array_map(fn ($r) => $r['p'], $res));
        $bh = Multiple::benjaminiHochberg(array_map(fn ($r) => $r['p'], $res));
        foreach ($res as $k => $r) {
            $res[$k]['p_holm'] = $holm[$k] === null ? null : round($holm[$k], 4);
            $res[$k]['p_bh'] = $bh[$k] === null ? null : round($bh[$k], 4);
        }

        return $res;
    }
}
