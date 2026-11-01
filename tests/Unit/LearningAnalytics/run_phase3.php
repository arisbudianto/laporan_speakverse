<?php
// Uji mandiri Fase 3: php tests/Unit/LearningAnalytics/run_phase3.php  (acuan: fixtures/phase3_ref.json dari SciPy/scikit-learn)
$S = dirname(__DIR__, 3) . '/app/Services/LearningAnalytics/';
foreach (['Stats/Descriptive', 'Stats/Distributions', 'Stats/Inference', 'Stats/Multiple', 'Stats/Correlation', 'Stats/KMeans', 'Mastery/BktModel', 'Mastery/MasteryAnalyzer', 'Srl/SrlFeatures', 'Srl/BehaviorClusters'] as $f) require $S . $f . '.php';
use App\Services\LearningAnalytics\Mastery\{BktModel, MasteryAnalyzer};
use App\Services\LearningAnalytics\Srl\{BehaviorClusters, SrlFeatures};
use App\Services\LearningAnalytics\Stats\{Correlation, KMeans};
$bad = 0;
$chk = function ($n, $a, $b, $e = 1e-7) use (&$bad) { if ($a === null || abs($a - $b) > $e) { echo "FAIL $n: " . var_export($a, true) . " vs " . var_export($b, true) . "\n"; $bad++; } else echo "ok   $n\n"; };
$ok = function ($n, $c) use (&$bad) { if (! $c) { echo "FAIL $n\n"; $bad++; } else echo "ok   $n\n"; };
$ref = json_decode(file_get_contents(__DIR__ . '/fixtures/phase3_ref.json'), true);

// Spearman & siluet vs SciPy / scikit-learn
$c = Correlation::spearman($ref['sp']['x'], $ref['sp']['y']);
$chk('Spearman rho (dengan ikatan)', $c['rho'], $ref['sp']['rho'], 1e-9); $chk('Spearman p', $c['p'], $ref['sp']['p'], 1e-6);
$chk('siluet = scikit-learn', KMeans::silhouette($ref['sil']['x'], $ref['sil']['labels']), $ref['sil']['score'], 1e-9);

// k-means memulihkan 3 gumpalan; deterministik
$pts = $ref['sil']['x']; $true = array_merge(array_fill(0, 15, 0), array_fill(0, 15, 1), array_fill(0, 15, 2));
$f1 = KMeans::fit($pts, 3, 10, 42); $f2 = KMeans::fit($pts, 3, 10, 42);
$ok('k-means deterministik', $f1['labels'] === $f2['labels']);
$pure = true; foreach ([0, 15, 30] as $s) { $set = array_unique(array_slice($f1['labels'], $s, 15)); if (count($set) !== 1) $pure = false; }
$ok('k-means memisahkan 3 gumpalan', $pure);
$ok('siluet k=3 tertinggi di antara k=2..5', (function () use ($pts) { $b = null; $bk = 0; foreach ([2, 3, 4, 5] as $k) { $s = KMeans::silhouette($pts, KMeans::fit($pts, $k)['labels']); if ($b === null || $s > $b) { $b = $s; $bk = $k; } } return $bk === 3; })());

// BKT maju: cocok implementasi Python independen
foreach ($ref['bkt']['cases'] as $name => $cs) {
    $r = BktModel::run($cs['obs'], $ref['bkt']['prm']);
    $chk("BKT P(L) '$name'", $r['p'], $cs['p'], 1e-10); $chk("BKT log-lik '$name'", $r['ll'], $cs['ll'], 1e-9);
}
// penguasaan naik pada rangkaian benar, turun pada salah
$p1 = BktModel::run([1, 1, 1], BktModel::DEFAULTS)['p']; $p0 = BktModel::run([0, 0, 0], BktModel::DEFAULTS)['p'];
$ok('benar beruntun → penguasaan tinggi; salah beruntun → rendah', $p1 > 0.9 && $p0 < 0.2);

// Pemulihan parameter dari data simulasi (150 siswa × 12 soal, parameter sebenarnya L0=.2 T=.2 S=.1 G=.2)
$fit = BktModel::fit($ref['fit']['seqs']);
$tr = $ref['fit']['true']; $llTrue = 0; foreach ($ref['fit']['seqs'] as $s) $llTrue += BktModel::run($s, $tr)['ll'];
$ok('log-lik hasil fit ≥ log-lik parameter sebenarnya (grid)', $fit['ll'] >= $llTrue - 1e-6);
$ok('T terestimasi 0,10–0,30', $fit['params']['t'] >= 0.1 && $fit['params']['t'] <= 0.3);
$ok('G terestimasi 0,10–0,30', $fit['params']['g'] >= 0.1 && $fit['params']['g'] <= 0.30);
echo "     estimasi: " . json_encode($fit['params']) . "\n";

// Kurva belajar: kesalahan = 0.6·k^-0.7 → b = -0.7 (disusun dari proporsi tepat pada 1000 siswa)
$seqs = [];
for ($u = 0; $u < 1000; $u++) { $seqs[$u] = []; }
for ($k = 1; $k <= 10; $k++) { $err = 0.6 * $k ** -0.7; $w = (int) round($err * 1000); for ($u = 0; $u < 1000; $u++) { $seqs[$u][] = $u < $w ? 0 : 1; } }
$cv = MasteryAnalyzer::curve($seqs);
$chk('hukum pangkat: eksponen b', $cv['b'], -0.7, 0.01); $chk('hukum pangkat: a', $cv['a'], 0.6, 0.01); $ok('R² tinggi', $cv['r2'] > 0.99);

// MasteryAnalyzer: wheel spinning & mastery
$obs = ['reading:inference' => ['m' => array_fill(0, 8, 1), 'w' => array_merge([0, 0, 1, 0, 0, 1, 0, 0, 1, 0, 0, 0]), 'k' => [1, 1], 'x1' => [1, 1, 1, 1, 1, 1], 'x2' => [0, 1, 1, 1, 1, 1, 1], 'x3' => [1, 0, 1, 1, 1, 1]]];
$m = (new MasteryAnalyzer())->analyze($obs);
$st = array_column($m['states'], null, 'user');
$ok('siswa 8 benar beruntun = mastered', $st['m']['mastered'] === true);
$ok('12 percobaan campur salah = wheel spinning', $st['w']['wheel_spinning'] === true && $st['w']['mastered'] === false);
$ok('2 percobaan benar belum wheel spinning', $st['k']['wheel_spinning'] === false);
$ok('kompetensi dengan data cukup (41 pengamatan, 6 siswa) di-fit', $m['competencies'][0]['default'] === false);
$m2 = (new MasteryAnalyzer())->analyze(['x' => ['a' => [1, 0, 1], 'b' => [1, 1]]]);
$ok('data sedikit memakai parameter default', $m2['competencies'][0]['default'] === true && $m2['competencies'][0]['params']['l0'] === 0.3);

// SrlFeatures: skenario manual
$ev = function ($u, $s, $q, $type, $c, $att, $h, $ms, $ts) { return ['user' => $u, 'session' => $s, 'q' => $q, 'type' => $type, 'correct' => $c, 'attempt' => $att, 'hint' => $h, 'ms' => $ms, 'ts' => $ts]; };
$rows = [];
// user 1: 10 butir; semuanya benar pertama kali, lambat → mandiri
for ($i = 1; $i <= 10; $i++) { $rows[] = $ev(1, 's', "q$i", 'select', 1, 1, null, 9000, 1000 + $i * 30); }
// user 2: 10 butir; semua salah cepat (1 dtk) lalu hint berantai (selisih 1 dtk), akhirnya benar dengan 3 hint → assisted
for ($i = 1; $i <= 10; $i++) {
    $t = 5000 + $i * 60;
    $rows[] = $ev(2, 's', "q$i", 'select', 0, 1, null, 1000, $t);
    foreach ([1, 2, 3] as $j) { $rows[] = $ev(2, 's', "q$i", 'hint_request', null, 1, $j, null, $t + $j); }
    $rows[] = $ev(2, 's', "q$i", 'revise', 1, 2, null, 4000, $t + 10);
}
$f = (new SrlFeatures())->compute($rows);
$chk('user1 akurasi 1.0', $f[1]['first_try_acc'], 1.0); $chk('user1 hint 0', $f[1]['hint_rate'], 0.0); $ok('user1 tanpa flag gaming', $f[1]['gaming_flag'] === false);
$chk('user2 akurasi 0', $f[2]['first_try_acc'], 0.0); $chk('user2 hint_rate 1', $f[2]['hint_rate'], 1.0); $chk('user2 hint L3 rate 1', $f[2]['hint_l3_rate'], 1.0);
$chk('user2 revisi 1', $f[2]['revision_rate'], 1.0); $chk('user2 salah cepat 1', $f[2]['fast_wrong_rate'], 1.0); $chk('user2 rantai hint 20/30', $f[2]['hint_chain_rate'], 20 / 30, 1e-3);
$ok('user2 kena flag gaming', $f[2]['gaming_flag'] === true);
$chk('user2 assisted 10', $f[2]['struggle']['assisted'], 10, 0); $chk('user2 productive 0', $f[2]['struggle']['productive'], 0, 0);
// unproductive vs no_effort
$r2 = [$ev(3, 's', 'a', 'select', 0, 1, null, 8000, 100), $ev(3, 's', 'a', 'revise', 0, 2, null, 6000, 130),
       $ev(3, 's', 'b', 'select', 0, 1, null, 8000, 200),
       $ev(3, 's', 'c', 'select', 0, 1, null, 8000, 300), $ev(3, 's', 'c', 'hint_request', null, 1, 1, null, 320), $ev(3, 's', 'c', 'revise', 1, 2, null, 5000, 340)];
$f3 = (new SrlFeatures())->compute($r2)[3];
$chk('unproductive 1 (2 percobaan, tetap salah)', $f3['struggle']['unproductive'], 1, 0); $chk('no_effort 1', $f3['struggle']['no_effort'], 1, 0); $chk('productive 1 (benar dengan 1 hint)', $f3['struggle']['productive'], 1, 0);
$ok('butir < 10 → enough=false & tanpa flag', $f3['enough'] === false && $f3['gaming_flag'] === false);

// BehaviorClusters: tiga persona sintetis
mt_srand(3);
$srl = []; $reg = []; $gain = []; $uid = 0;
$persona = [['mandiri', 0.85, 0.05, 0.10, 9000, 0.02, 80, 22], ['hint', 0.40, 0.80, 0.30, 7000, 0.05, 55, 6], ['tergesa', 0.35, 0.10, 0.05, 1500, 0.55, 40, -2]];
foreach ($persona as [$nm, $acc, $hint, $rev, $ms, $fw, $ri, $g]) {
    for ($i = 0; $i < 12; $i++) {
        $uid++; $j = fn ($v, $a) => max(0, $v + (mt_rand(-100, 100) / 100) * $a);
        $srl[$uid] = ['enough' => true, 'first_try_acc' => min(1, $j($acc, .05)), 'hint_rate' => min(1, $j($hint, .05)), 'revision_rate' => min(1, $j($rev, .04)), 'median_ms' => (int) $j($ms, $ms * .1), 'fast_wrong_rate' => min(1, $j($fw, .03))];
        $reg[$uid] = $ri + mt_rand(-5, 5); $gain[$uid] = $g + mt_rand(-4, 4);
    }
}
$bc = (new BehaviorClusters())->run($srl, $reg, $gain);
$ok('klaster siap & k=3', $bc['ready'] && $bc['k'] === 3);
$pure = true; foreach ([[1, 12], [13, 24], [25, 36]] as [$a, $b]) { $set = []; for ($u = $a; $u <= $b; $u++) $set[$bc['assign'][$u]] = 1; if (count($set) !== 1) $pure = false; }
$ok('persona terpisah sempurna', $pure);
$labels = array_column($bc['clusters'], 'label');
$ok('nama profil: Tergesa-gesa, Bergantung hint, Konsisten-mandiri', in_array('Tergesa-gesa', $labels, true) && in_array('Bergantung hint', $labels, true) && in_array('Konsisten-mandiri', $labels, true));
echo "     profil: " . implode(', ', $labels) . "; siluet " . $bc['silhouette'] . "\n";
$ok('korelasi akurasi-gain positif signifikan', isset($bc['correlations']['first_try_acc']) && $bc['correlations']['first_try_acc']['rho'] > 0.5 && $bc['correlations']['first_try_acc']['p'] < 0.01);
$small = (new BehaviorClusters())->run(array_slice($srl, 0, 10, true), $reg, $gain);
$ok('n < 20 tidak diklaster', $small['ready'] === false);

echo $bad ? "\n$bad GAGAL\n" : "\nSEMUA LULUS\n";
exit($bad ? 1 : 0);
