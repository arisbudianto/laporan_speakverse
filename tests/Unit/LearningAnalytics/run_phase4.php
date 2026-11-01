<?php
// Uji mandiri Fase 4: php tests/Unit/LearningAnalytics/run_phase4.php  (acuan sklearn/SciPy di fixtures/phase4_ref.json)
$S = dirname(__DIR__, 3) . '/app/Services/LearningAnalytics/';
foreach (['Stats/Descriptive', 'Stats/Distributions', 'Stats/Inference', 'Stats/Logistic', 'Stats/Metrics', 'Stats/Ols', 'Risk/RiskScorer', 'Risk/RiskModel', 'Intervention/InterventionEvaluator', 'Content/ContentEffectiveness', 'Research/Pseudonymizer', 'Guidance/RemedialRecommender', 'Guidance/StudentGuidance'] as $f) require $S . $f . '.php';
use App\Services\LearningAnalytics\Content\ContentEffectiveness;
use App\Services\LearningAnalytics\Guidance\{RemedialRecommender, StudentGuidance};
use App\Services\LearningAnalytics\Intervention\InterventionEvaluator;
use App\Services\LearningAnalytics\Research\Pseudonymizer;
use App\Services\LearningAnalytics\Risk\{RiskModel, RiskScorer};
use App\Services\LearningAnalytics\Stats\{Inference as I, Logistic as L, Metrics as M};
$bad = 0;
$chk = function ($n, $a, $b, $e = 1e-6) use (&$bad) { if ($a === null || abs($a - $b) > $e) { echo "FAIL $n: " . var_export($a, true) . " vs " . var_export($b, true) . "\n"; $bad++; } else echo "ok   $n\n"; };
$ok = function ($n, $c) use (&$bad) { if (! $c) { echo "FAIL $n\n"; $bad++; } else echo "ok   $n\n"; };
$r = json_decode(file_get_contents(__DIR__ . '/fixtures/phase4_ref.json'), true);

$sc = L::scaler($r['X']); $xs = L::apply($sc, $r['X']); $m = L::fit($xs, $r['y'], $r['lam']);
$ok('Newton konvergen', $m['converged']);
$chk('intercept = sklearn', $m['b0'], $r['b0'], 1e-6);
foreach ($m['w'] as $j => $w) $chk("koefisien $j = sklearn", $w, $r['w'][$j], 1e-6);
$p = array_map(fn ($row) => L::predict($m, $row), $xs);
$chk('AUC = sklearn', M::auc($r['y'], $p), $r['auc'], 1e-9); $chk('Brier = sklearn', M::brier($r['y'], $p), $r['brier'], 1e-9);
$chk('AUC dengan ikatan = sklearn', M::auc($r['auc_ties']['y'], $r['auc_ties']['s']), $r['auc_ties']['auc'], 1e-9);
$w = I::welch($r['welch']['a'], $r['welch']['b']);
$chk('Welch t', $w['t'], $r['welch']['t'], 1e-7); $chk('Welch p', $w['p'], $r['welch']['p'], 1e-7); $chk('Welch df', $w['df'], $r['welch']['df'], 1e-5);
$chk('Welch CI bawah', $w['ci'][0], $r['welch']['ci'][0], 1e-5); $chk('Welch CI atas', $w['ci'][1], $r['welch']['ci'][1], 1e-5);
$f = M::prf([1, 1, 0, 0, 1, 0], [0.9, 0.4, 0.6, 0.1, 0.8, 0.2], 0.5);
$chk('presisi 2/3', $f['precision'], 2 / 3); $chk('recall 2/3', $f['recall'], 2 / 3);
$folds = M::stratifiedFolds(array_merge(array_fill(0, 20, 1), array_fill(0, 30, 0)), 5);
$ok('lipatan berstrata: tiap lipatan 4 positif & 6 negatif', (function () use ($folds) { for ($k = 0; $k < 5; $k++) { $pos = $neg = 0; foreach ($folds as $i => $f) if ($f === $k) { $i < 20 ? $pos++ : $neg++; } if ($pos !== 4 || $neg !== 6) return false; } return true; })());

// RiskScorer: dihitung tangan
$W = ['inactivity' => 20, 'irregular' => 10, 'decline' => 10, 'hint_dependence' => 10, 'inferential' => 15, 'lag' => 15, 'low_accuracy' => 12, 'wheel' => 8];
$sr = new RiskScorer($W);
$o = $sr->score(['days_since_last' => 7, 'regularity_index' => 40, 'trend_slope' => -1, 'hint_l3_rate' => 0.15, 'infer' => 2, 'progress' => 0.2, 'first_try_acc' => 0.5, 'wheel_n' => 1], 2, 0.6);
$chk('skor risiko (hitung tangan 55,5)', $o['score'], 55.5, 1e-9); $ok('level sedang, keyakinan cukup', $o['level'] === 'sedang' && $o['confidence'] === 'cukup');
$ok('3 faktor teratas: lag, inactivity, inferential', array_column($o['factors'], 'key') === ['lag', 'inactivity', 'inferential']);
$o2 = $sr->score(['days_since_last' => null, 'regularity_index' => null, 'trend_slope' => null, 'hint_l3_rate' => null, 'infer' => 0, 'progress' => null, 'first_try_acc' => null, 'wheel_n' => null], 2, 0.5);
$chk('data minim: 100·20/35', $o2['score'], round(100 * 20 / 35, 1), 1e-9); $ok('keyakinan terbatas', $o2['confidence'] === 'terbatas' && $o2['level'] === 'sedang');
$o3 = $sr->score(['days_since_last' => 0, 'regularity_index' => 90, 'trend_slope' => 1, 'hint_l3_rate' => 0, 'infer' => 0, 'progress' => 0.9, 'first_try_acc' => 0.9, 'wheel_n' => 0], 2, 0.6);
$ok('siswa sehat → rendah tanpa faktor', $o3['level'] === 'rendah' && $o3['factors'] === []);

// RiskModel: sinyal nyata vs derau
mt_srand(17);
$gen = function (int $n, bool $signal) { $rows = []; for ($i = 0; $i < $n; $i++) {
    $ds = mt_rand(0, 20); $reg = mt_rand(10, 90); $acc = mt_rand(20, 95) / 100;
    $z = $signal ? (-1.5 + 0.45 * ($ds - 10) - 0.05 * ($reg - 50) - 5.0 * ($acc - 0.5)) : 0;
    $y = $signal ? (mt_rand() / mt_getrandmax() < 1 / (1 + exp(-$z)) ? 1 : 0) : mt_rand(0, 1);
    $rows[] = ['features' => ['days_since_last' => $ds, 'regularity_index' => $reg, 'trend_slope' => mt_rand(-20, 20) / 10, 'hint_l3_rate' => mt_rand(0, 30) / 100, 'infer' => mt_rand(0, 5), 'progress' => mt_rand(0, 100) / 100, 'first_try_acc' => $acc, 'wheel_n' => mt_rand(0, 2)],
        'y' => $y, 'school' => 'S' . ($i % 3)]; } return $rows; };
$rm = new RiskModel(); $e = $rm->evaluate($gen(240, true));
$ok('sinyal nyata: AUC-CV > 0,75 dan model tampil', $e['auc'] > 0.75 && $e['ready']); echo "     AUC-CV " . round($e['auc'], 3) . ", Brier " . round($e['brier'], 3) . ", selisih AUC antar sekolah " . round($e['fairness_gap'], 3) . "\n";
$ok('koefisien days_since_last positif, first_try_acc negatif', $e['coefficients']['days_since_last'] > 0 && $e['coefficients']['first_try_acc'] < 0);
$ok('keadilan: 3 sekolah dilaporkan', count($e['fairness']) === 3 && $e['fairness'][0]['auc'] !== null);
$ok('prediksi model lebih tinggi untuk profil berisiko', $rm->predict($e['model'], ['days_since_last' => 20, 'regularity_index' => 10, 'first_try_acc' => 0.2]) > $rm->predict($e['model'], ['days_since_last' => 0, 'regularity_index' => 90, 'first_try_acc' => 0.95]));
$en = $rm->evaluate($gen(240, false)); $ok('derau: AUC-CV ≈ 0,5 dan model TIDAK ditampilkan', abs($en['auc'] - 0.5) < 0.12 && ! $en['ready']); echo "     AUC derau " . round($en['auc'], 3) . "\n";
$es = $rm->evaluate($gen(30, true)); $ok('data kurang → alasan, tanpa model', ! $es['ready'] && str_contains($es['reason'], 'belum cukup'));

// InterventionEvaluator: efek +2 hari aktif yang ditanam
mt_srand(5); $rows = [];
for ($i = 0; $i < 30; $i++) { $b = mt_rand(0, 4); $rows[] = ['user' => $i, 'followed' => true, 'before' => $b, 'after' => $b + 2 + mt_rand(-1, 1)]; }
for ($i = 30; $i < 60; $i++) { $b = mt_rand(0, 4); $rows[] = ['user' => $i, 'followed' => false, 'before' => $b, 'after' => $b + mt_rand(-1, 1)]; }
$ev = (new InterventionEvaluator())->evaluate($rows);
$ok('DiD ≈ +2 dan signifikan', $ev['ready'] && abs($ev['did']['diff'] - 2) < 0.6 && $ev['did']['p'] < 0.001 && $ev['g'] > 1);
$ok('sebelum–sesudah kelompok ditindaklanjuti signifikan', $ev['within']['p'] < 0.001);
$ok('kelompok < 5 → tidak dievaluasi', ! (new InterventionEvaluator())->evaluate(array_slice($rows, 0, 3))['ready']);

// ContentEffectiveness (dosis–gain, kendali pretest). gain = 2 + 0,5·dosis − 0,1·pretest + derau kecil deterministik
$gain = $pre = $d1 = [];
for ($u = 1; $u <= 40; $u++) { $pre[$u] = 40 + ($u * 7) % 30; $d1[$u] = $u <= 25 ? ($u % 10) + 1 : 0; $gain[$u] = 2 + 0.5 * $d1[$u] - 0.1 * $pre[$u] + (($u * 13) % 7 - 3) * 0.2; }
$rank = (new ContentEffectiveness())->rank([['lesson' => 1, 'name' => 'L1', 'starts' => 30, 'submits' => 20, 'abandons' => 10, 'first_try_acc' => 0.6], ['lesson' => 2, 'name' => 'L2', 'starts' => 5, 'submits' => 5, 'abandons' => 0, 'first_try_acc' => 0.8]], [1 => $d1, 2 => [1 => 3, 2 => 4]], $gain, $pre, [1 => [1, 2, 3]]);
$l1 = array_values(array_filter($rank, fn ($x) => $x['lesson'] === 1))[0];
$ok('L1: β dosis ≈ +5 per 10 butir', $l1['per10'] !== null && abs($l1['per10'] - 5.0) < 0.6);
$ok('L1: bertanda terkait kenaikan skor + sering ditinggalkan', in_array('terkait kenaikan skor', $l1['flags'], true) && in_array('sering ditinggalkan', $l1['flags'], true));
$l2 = array_values(array_filter($rank, fn ($x) => $x['lesson'] === 2))[0]; $ok('L2: terpapar < 5 → tidak dihitung dengan alasan', $l2['per10'] === null && $l2['why_not'] !== null && $l2['flags'] === []);
$ok('n selesai tidak menggembung', $l1['n_completed'] === 3);

// Fase 5: pseudonimisasi
$ps = new Pseudonymizer('rahasia-uji'); $ps2 = new Pseudonymizer('rahasia-lain');
$ok('ID pseudonim stabil', $ps->userId(42) === $ps->userId(42)); $ok('ID berbeda per siswa', $ps->userId(42) !== $ps->userId(43));
$ok('ID berbeda per rahasia', $ps->userId(42) !== $ps2->userId(42)); $ok('format 12 heksa', (bool) preg_match('/^[0-9a-f]{12}$/', $ps->userId(42)));
$row = $ps->row(42, 'SMKN 1 Contoh', ['major' => 'TKJ', 'grade' => 'X', 'pretest' => 55, 'posttest' => 70, 'name' => 'Budi', 'email' => 'budi@x.id']);
$flat = json_encode($row);
$ok('baris tidak memuat nama/email/ID asli/nama sekolah', ! str_contains($flat, 'Budi') && ! str_contains($flat, 'budi@x.id') && ! str_contains($flat, 'SMKN') && ! preg_match('/"user_id"|"id":42/', $flat));
$ok('kolom persis sesuai skema', array_keys($row) === Pseudonymizer::COLUMNS); $ok('kode sekolah konsisten', $ps->school('A') === $ps->school('A') && $ps->school('A') !== $ps->school('B'));
// Fase 5: rekomendasi dan panduan siswa
$rr = new RemedialRecommender();
$rec = $rr->recommend([['competency' => 'reading:inference', 'students' => 20, 'mean_p' => 0.35, 'mastered_pct' => 10], ['competency' => 'reading:main-idea', 'students' => 20, 'mean_p' => 0.9, 'mastered_pct' => 80], ['competency' => 'listening:detail', 'students' => 3, 'mean_p' => 0.1, 'mastered_pct' => 0]], ['inferential' => 20, 'lexical' => 8]);
$ok('remedial: kompetensi lemah dipilih, yang kuat/n kecil tidak', count(array_filter($rec, fn ($x) => $x['type'] === 'kompetensi')) === 1 && $rec[0]['title'] === 'reading:inference');
$ok('remedial: kesalahan dominan inferensial', $rec[1]['type'] === 'kesalahan' && $rec[1]['title'] === 'inferential');
$ok('remedial: kesalahan tersebar → tak ada saran', count($rr->recommend([], ['inferential' => 5, 'lexical' => 5, 'syntactic' => 5, 'context_misconception' => 5])) === 0);
$ok('label penguasaan', StudentGuidance::masteryLabel(0.97)[0] === 'Sudah dikuasai' && StudentGuidance::masteryLabel(0.1)[0] === 'Perlu dilatih' && StudentGuidance::masteryLabel(0.5)[0] === 'Sedang berkembang');
$today = new DateTimeImmutable('2026-10-26'); $act = ['2026-10-26' => true, '2026-10-24' => true, '2026-10-19' => true, '2026-10-18' => true];
$wk = StudentGuidance::weekly($act, $today); $ok('target mingguan: 2 aktif (26,24; 19 di luar 7 hari), sisa 1', $wk['active'] === 2 && $wk['remaining'] === 1 && ! $wk['done']);
$ns = StudentGuidance::nextStep([['competency' => 'a', 'p' => 0.5, 'n_obs' => 5], ['competency' => 'b', 'p' => 0.2, 'n_obs' => 5], ['competency' => 'c', 'p' => 0.99, 'n_obs' => 5]]);
$ok('saran: kompetensi terendah', $ns['competency'] === 'b'); $ok('saran: semua dikuasai → apresiasi', StudentGuidance::nextStep([['competency' => 'c', 'p' => 0.99, 'n_obs' => 5]])['competency'] === null);

echo $bad ? "\n$bad GAGAL\n" : "\nSEMUA LULUS\n"; exit($bad ? 1 : 0);
