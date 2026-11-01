<?php
// Uji mandiri Tahap 2: php tests/Unit/LearningAnalytics/run_phase5.php  (acuan OLS: numpy/SciPy di fixtures/phase5_ols.json)
$S = dirname(__DIR__, 3) . '/app/Services/LearningAnalytics/';
foreach (['Stats/Descriptive', 'Stats/Distributions', 'Stats/Inference', 'Stats/Ols', 'Stats/Multiple', 'Outcome/FormComparability', 'Risk/RiskScorer', 'Mastery/BktModel', 'Mastery/MasteryAnalyzer', 'Engagement/EngagementMetrics', 'Research/Pseudonymizer'] as $f) require $S . $f . '.php';
use App\Services\LearningAnalytics\Engagement\EngagementMetrics;
use App\Services\LearningAnalytics\Mastery\{BktModel, MasteryAnalyzer};
use App\Services\LearningAnalytics\Outcome\FormComparability;
use App\Services\LearningAnalytics\Research\Pseudonymizer;
use App\Services\LearningAnalytics\Risk\RiskScorer;
use App\Services\LearningAnalytics\Stats\{Multiple, Ols};
$bad = 0;
$chk = function ($n, $a, $b, $e = 1e-6) use (&$bad) { if ($a === null || abs($a - $b) > $e) { echo "FAIL $n: " . var_export($a, true) . " vs " . var_export($b, true) . "\n"; $bad++; } else echo "ok   $n\n"; };
$ok = function ($n, $c) use (&$bad) { if (! $c) { echo "FAIL $n\n"; $bad++; } else echo "ok   $n\n"; };

// OLS vs numpy
$r = json_decode(file_get_contents(__DIR__ . '/fixtures/phase5_ols.json'), true);
$o = Ols::fit($r['X'], $r['y']);
foreach ($r['beta'] as $j => $b) { $chk("OLS beta$j", $o['beta'][$j], $b, 1e-8); $chk("OLS se$j", $o['se'][$j], $r['se'][$j], 1e-8); $chk("OLS p$j", $o['p'][$j], $r['p'][$j], 1e-7); }
$chk('OLS R2', $o['r2'], $r['r2'], 1e-9);
$ok('OLS singular → null', Ols::fit([[1, 2], [1, 2], [1, 2]], [1, 2, 3]) === null);

// Holm / BH (hitung tangan: p=[.01,.04,.03,.005])
$p = [0.01, 0.04, 0.03, 0.005];
foreach (Multiple::holm($p) as $i => $v) $chk("Holm $i", $v, [0.03, 0.06, 0.06, 0.02][$i], 1e-12);
foreach (Multiple::benjaminiHochberg($p) as $i => $v) $chk("BH $i", $v, [0.02, 0.04, 0.04, 0.02][$i], 1e-12);
$h = Multiple::holm(['a' => 0.01, 'b' => null, 'c' => 0.02]);
$ok('Holm null dipertahankan', $h['b'] === null && abs($h['a'] - 0.02) < 1e-12 && abs($h['c'] - 0.02) < 1e-12);

// Kesetaraan bentuk tes
$items = [];
foreach ([1, 2, 3, 4] as $i) { $items[] = ['form' => 'pretest|reading|0|0', 'item' => "mc:$i", 'p' => 0.70, 'n' => 30]; $items[] = ['form' => 'posttest|reading|0|0', 'item' => "mc:" . ($i + 10), 'p' => 0.45, 'n' => 30]; }
$fc = (new FormComparability())->analyze($items);
$ok('bentuk tidak sebanding (Δp -0,25)', $fc['comparable'] === false && ! $fc['has_anchors'] && str_contains($fc['message'], 'tidak sebanding'));
foreach ($items as &$it) { if ($it['form'][0] === 'p' && str_starts_with($it['form'], 'posttest')) $it['p'] = 0.72; } unset($it);
$ok('bentuk sebanding (Δp +0,02)', (new FormComparability())->analyze($items)['comparable'] === true);
$anch = [['form' => 'pretest|reading|0|0', 'item' => 'mc:1', 'p' => 0.5, 'n' => 30], ['form' => 'posttest|reading|0|0', 'item' => 'mc:1', 'p' => 0.6, 'n' => 30]];
$fa = (new FormComparability())->analyze($anch);
$chk('butir jangkar Δ', $fa['rows'][0]['anchor_delta'], 0.1, 1e-9);

// Risiko: belum mulai, lag, kalibrasi
$w = ['inactivity' => 20, 'irregular' => 10, 'decline' => 10, 'hint_dependence' => 10, 'inferential' => 15, 'lag' => 10, 'low_accuracy' => 15, 'wheel' => 10];
$sc = new RiskScorer($w);
$f = ['started' => false, 'days_since_last' => null, 'regularity_index' => null, 'trend_slope' => null, 'hint_l3_rate' => null, 'infer' => 0, 'progress' => 0.0, 'first_try_acc' => null, 'wheel_n' => null, 'active_days' => 0, 'items' => 0];
$res = $sc->score($f, 55, 0.5);
$ok('belum_mulai: level & skor 0', $res['level'] === 'belum_mulai' && $res['score'] == 0.0);
$f2 = ['started' => true, 'days_since_last' => 2, 'regularity_index' => 70, 'trend_slope' => 0, 'hint_l3_rate' => 0.0, 'infer' => 60, 'progress' => 0.0, 'first_try_acc' => 0.8, 'wheel_n' => 0, 'active_days' => 5, 'items' => 30];
$ok('lag null bila median kelas < 0,10', $sc->score($f2, 55, 0.05)['components']['lag'] === null);
$ok('lag terhitung bila median ≥ 0,10', $sc->score($f2, 55, 0.4)['components']['lag'] !== null);
$cfg = ['min_n' => 30, 'pct_high' => 8, 'pct_medium' => 17, 'floor_high' => 45, 'floor_medium' => 25];
$sm = RiskScorer::calibrate(range(1, 10), $cfg, 60, 35);
$ok('kalibrasi n kecil → ambang tetap', ! $sm['calibrated'] && $sm['high'] == 60 && $sm['medium'] == 35);
$low = array_map(fn ($i) => $i * 0.3, range(1, 100));      // skor 0,3..30 → lantai berlaku
$cl = RiskScorer::calibrate($low, $cfg, 60, 35);
$ok('kalibrasi: lantai mencegah ambang rendah', $cl['calibrated'] && $cl['high'] >= 45 && $cl['medium'] >= 25);
$hi = range(1, 100);                                       // persentil 92 ≈ 92,08
$ch = RiskScorer::calibrate($hi, $cfg, 60, 35);
$ok('kalibrasi: persentil di atas lantai', abs($ch['high'] - 92.08) < 0.5 && $ch['high'] > $ch['medium']);

// BKT: batas grid, pembanding, keandalan
$ok('atBound mendeteksi batas', BktModel::atBound(['l0' => 0.05, 't' => 0.2, 's' => 0.3, 'g' => 0.1]) === ['l0', 's']);
$chk('baseline ll konstan', BktModel::baselineLl([[1, 1, 0, 0]]), 4 * log(0.5), 1e-9);
mt_srand(5);
$obsRandom = [];
for ($u = 0; $u < 12; $u++) { $obsRandom[$u] = array_map(fn () => mt_rand(0, 100) < 60 ? 1 : 0, range(1, 12)); }
$ma = (new MasteryAnalyzer())->analyze(['reading:acak' => $obsRandom]);
$ok('data acak: tidak reliable → tidak ada penguasaan/putar-putar', ! $ma['competencies'][0]['reliable'] && array_sum(array_column($ma['states'], 'mastered')) === 0 && array_sum(array_column($ma['states'], 'wheel_spinning')) === 0);
$obsLearn = [];
for ($u = 0; $u < 15; $u++) { $s = []; $k = false; $learnAt = mt_rand(2, 5); for ($i = 0; $i < 14; $i++) { if ($i >= $learnAt) $k = true; $s[] = $k ? (mt_rand(0, 100) < 92 ? 1 : 0) : (mt_rand(0, 100) < 30 ? 1 : 0); } $obsLearn[$u] = $s; }
$ml = (new MasteryAnalyzer())->analyze(['reading:belajar' => $obsLearn]);
$ok('data ada kurva belajar: reliable & ada yang menguasai', $ml['competencies'][0]['reliable'] && $ml['competencies'][0]['mastered'] > 0);

// Keterlibatan: hari belajar vs tes
$ok('hari hanya-tes bukan hari belajar', ! EngagementMetrics::isLearningDay(['events' => 5, 'sessions' => 1, 'learn_events' => 0, 'test_events' => 5]));
$ok('hari belajar dengan learn_events', EngagementMetrics::isLearningDay(['events' => 5, 'sessions' => 1, 'learn_events' => 3, 'test_events' => 2]));
$ok('tanpa kolom: perilaku lama', EngagementMetrics::isLearningDay(['events' => 2, 'sessions' => 0]));

// Pseudonim kelas: k-anonimitas
$ps = new Pseudonymizer('rahasia-uji');
$big = $ps->classId('SMA A', 'SMA A|IPA|X|A', 30, 'X');
$small1 = $ps->classId('SMA A', 'SMA A|IPA|X|A', 3, 'X');
$small2 = $ps->classId('SMA A', 'SMA A|IPS|X|B', 4, 'X');
$ok('kelas besar punya kode sendiri', str_starts_with($big, 'CLS-') && ! str_starts_with($big, 'CLS-GEN'));
$ok('kelas kecil digabung (kode generik sama)', str_starts_with($small1, 'CLS-GEN-') && $small1 === $small2);
$ok('kode kelas deterministik', $big === $ps->classId('SMA A', 'SMA A|IPA|X|A', 30, 'X'));

echo "\n" . ($bad ? "$bad GAGAL" : 'SEMUA LULUS') . "\n";
exit($bad ? 1 : 0);
