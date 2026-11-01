<?php
// Uji mandiri Fase 2 (tanpa PHPUnit): php tests/Unit/LearningAnalytics/run_phase2.php
// Nilai acuan dihasilkan SciPy/NumPy (fixtures/gen_*.py) dan disimpan sebagai JSON agar uji bisa diulang tanpa Python.
$S = dirname(__DIR__, 3) . '/app/Services/LearningAnalytics/';
$F = __DIR__ . '/fixtures/';
foreach (['Stats/Descriptive', 'Stats/Correlation', 'Stats/Distributions', 'Stats/Inference', 'Items/ItemAnalyzer', 'Outcome/OutcomeAnalysis'] as $f) require $S . $f . '.php';
use App\Services\LearningAnalytics\Items\ItemAnalyzer;
use App\Services\LearningAnalytics\Outcome\OutcomeAnalysis;
use App\Services\LearningAnalytics\Stats\{Distributions as X, Inference as I};
$bad = 0;
$chk = function ($n, $a, $b, $e = 1e-7) use (&$bad) {
    if ($a === null || abs($a - $b) > $e) { echo "FAIL $n: " . var_export($a, true) . " vs $b\n"; $bad++; } else { echo "ok   $n\n"; }
};
$j = fn ($f) => json_decode(file_get_contents($F . $f), true);

$r = $j('scipy_dist.json');
foreach ($r['t'] as [$t, $df, $v]) $chk("t cdf($t,$df)", X::tCdf($t, $df), $v, 1e-8);
foreach ($r['tq'] as [$p, $df, $v]) $chk("t kuantil($p,$df)", X::tQuantile($p, $df), $v, 1e-7);
foreach ($r['chi'] as [$x, $df, $v]) $chk("chi2($x,$df)", X::chiSqCdf($x, $df), $v, 1e-8);
foreach ($r['f'] as [$f, $a, $b, $v]) $chk("F($f)", X::fCdf($f, $a, $b), $v, 1e-8);
foreach ($r['n'] as [$z, $v]) $chk("normal($z)", X::normalCdf($z), $v, 1e-8);
foreach ($r['lg'] as [$x, $v]) $chk("lgamma($x)", X::lgamma($x), $v, 1e-8);

$r = $j('scipy_inference.json');
$t = I::pairedT($r['t']['pre'], $r['t']['post']);
$chk('t berpasangan', $t['t'], $r['t']['t']); $chk('p t', $t['p'], $r['t']['p']);
$chk('CI bawah', $t['ci'][0], $r['t']['ci'][0]); $chk('CI atas', $t['ci'][1], $r['t']['ci'][1]);
$e = I::effectSize($r['t']['pre'], $r['t']['post']);
$chk('d_z', $e['dz'], $r['t']['dz']); $chk("Hedges g", $e['g'], $r['t']['g']);
$chk('Wilcoxon eksak (SciPy)', I::wilcoxon($r['w1']['pre'], $r['w1']['post'])['p'], $r['w1']['p']);
$chk('Wilcoxon eksak ikatan+nol (brute force)', I::wilcoxon($r['w2']['pre'], $r['w2']['post'])['p'], $r['w2']['p']);
$chk('Wilcoxon normal (n=40)', I::wilcoxon($r['w3']['pre'], $r['w3']['post'])['p'], $r['w3']['p'], 1e-6);
$k = I::kruskalWallis($r['kw']['groups']);
$chk('Kruskal-Wallis H', $k['h'], $r['kw']['h']); $chk('Kruskal-Wallis p', $k['p'], $r['kw']['p']);
$chk('ICC(1) tak seimbang', I::icc1($r['icc']['groups'])['icc'], $r['icc']['icc']);
$chk('Hake kelompok', I::hakeClass([40, 50, 60], [70, 75, 80])['g'], 0.5);
$chk('Hake siswa', I::hakeStudent(60, 80), 0.5);
echo I::hakeStudent(100, 100) === null ? "ok   pre=100 dilewati\n" : "FAIL pre=100\n";

$r = $j('numpy_items.json');
$out = (new ItemAnalyzer())->analyze($r['rows']);
$chk('alpha Cronbach/KR-20', $out['forms'][0]['alpha'], $r['alpha'], 1e-3);
$chk('SEM', $out['forms'][0]['sem'], $r['sem'], 0.01);
foreach ($out['items'] as $i => $it) {
    $chk("p butir $i", $it['p'], $r['p'][$i], 1e-3);
    $chk("r_pb sisa butir $i", $it['r_pb'], $r['rpb'][$i], 1e-3);
    $chk("D 27% butir $i", $it['d_index'], $r['D_note'][$i], 1e-3);
}
$fl = array_column($out['items'], 'flags', 'item');
echo in_array('terlalu mudah', $fl['i0'], true) ? "ok   penanda terlalu mudah\n" : "FAIL penanda mudah\n";
echo in_array('terlalu sulit', $fl['i7'], true) ? "ok   penanda terlalu sulit\n" : "FAIL penanda sulit\n";
// kunci terbalik: butir dengan korelasi negatif harus bertanda
$rows = [];
mt_srand(11);
for ($u = 1; $u <= 60; $u++) {
    $ab = mt_rand(0, 1);
    foreach (['a', 'b', 'c', 'd'] as $it) { $rows[] = ['sub' => $u, 'user' => $u, 'form' => 'G', 'item' => $it, 'option' => $ab ? 'A' : 'B', 'correct' => $ab]; }
    // butir "bad": benar justru untuk yang lemah
    $rows[] = ['sub' => $u, 'user' => $u, 'form' => 'G', 'item' => 'bad', 'option' => $ab ? 'B' : 'A', 'correct' => $ab ? 0 : 1];
}
$o2 = (new ItemAnalyzer())->analyze($rows);
$bad2 = array_values(array_filter($o2['items'], fn ($x) => $x['item'] === 'bad'))[0];
echo ($bad2['r_pb'] < 0 && in_array('daya beda negatif', $bad2['flags'], true)) ? "ok   butir berkorelasi negatif ditandai\n" : "FAIL butir negatif\n";

$st = json_decode(file_get_contents(__DIR__ . '/fixtures/outcome_students.json'), true);
$o = (new OutcomeAnalysis())->analyze($st);
$exp = $j('outcome_expected.json');
$chk('pasangan', $o['pairs'], $exp['pairs'], 0); $chk('p t pre-post', $o['test']['p'], $exp['p'], 1e-12);
$chk('Hedges g', $o['effect']['g'], $exp['g']); $chk('Hake <g>', $o['hake']['g'], $exp['hake']);
$chk('ICC post antar sekolah', $o['icc']['post_school']['icc'], $exp['icc']); $chk('KW antar sekolah p', $o['equity']['school']['p'], $exp['kw'], 1e-10);
echo $bad ? "\n$bad GAGAL\n" : "\nSEMUA LULUS\n";
exit($bad ? 1 : 0);
