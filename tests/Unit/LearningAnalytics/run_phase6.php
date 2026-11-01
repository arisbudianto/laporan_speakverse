<?php
// Uji mandiri Tahap 3: php tests/Unit/LearningAnalytics/run_phase6.php  (acuan REML matriks penuh SciPy: fixtures/phase6_mixed.json)
$S = dirname(__DIR__, 3) . '/app/Services/LearningAnalytics/';
foreach (['Stats/Descriptive', 'Stats/Distributions', 'Stats/Ols', 'Stats/MixedModel'] as $f) require $S . $f . '.php';
use App\Services\LearningAnalytics\Stats\MixedModel;
$bad = 0;
$chk = function ($n, $a, $b, $e = 1e-6) use (&$bad) { if ($a === null || abs($a - $b) > $e) { echo "FAIL $n: " . var_export($a, true) . " vs " . var_export($b, true) . "\n"; $bad++; } else echo "ok   $n\n"; };
$ok = function ($n, $c) use (&$bad) { if (! $c) { echo "FAIL $n\n"; $bad++; } else echo "ok   $n\n"; };

$r = json_decode(file_get_contents(__DIR__ . '/fixtures/phase6_mixed.json'), true);
$m = MixedModel::fit($r['X'], $r['y'], $r['group'], [2]);
foreach ($r['beta'] as $j => $b) { $chk("beta$j", $m['beta'][$j], $b, 1e-5); $chk("se$j", $m['se'][$j], $r['se'][$j], 1e-5); }
$chk('sigma2_e', $m['sigma2_e'], $r['s2e'], 1e-3);
$chk('sigma2_u', $m['sigma2_u'], $r['s2u'], 1e-3);
$chk('REML', $m['reml'], -$r['neg_reml'], 1e-6);
$ok('df tingkat-kelompok = G − q − 1', $m['df'][2] === 12 - 1 - 1 && $m['df'][0] === count($r['y']) - 3);
$ok('LRT σ²_u>0 signifikan', $m['lrt'] > 3.84 && $m['lrt_p'] < 0.05);

// tanpa efek kelompok → σ²_u ≈ 0 dan ICC kecil
mt_srand(3);
$x = $y = $g = [];
for ($i = 0; $i < 240; $i++) { $x[] = [1.0]; $y[] = 50 + 8 * (mt_rand() / mt_getrandmax() - 0.5) * 2; $g[] = $i % 8; }
$m0 = MixedModel::fit($x, $y, $g);
$ok('tanpa struktur kelompok: ICC < 0,1', $m0['icc'] < 0.1);
$ok('kelompok < 3 → null', MixedModel::fit([[1.0], [1.0], [1.0], [1.0], [1.0]], [1, 2, 3, 4, 5], [0, 0, 1, 1, 1]) === null);
echo "\n" . ($bad ? "$bad GAGAL" : 'SEMUA LULUS') . "\n";
exit($bad ? 1 : 0);
