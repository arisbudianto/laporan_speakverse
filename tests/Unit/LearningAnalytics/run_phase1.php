<?php
// Uji mandiri (tanpa PHPUnit/Composer): php tests/Unit/LearningAnalytics/run_phase1.php
$b = dirname(__DIR__, 3) . '/app/Services/LearningAnalytics/';
foreach (['Stats/Descriptive', 'Engagement/SessionBuilder', 'Engagement/DailyAggregator', 'Engagement/EngagementMetrics'] as $f) require $b . $f . '.php';
use App\Services\LearningAnalytics\Stats\Descriptive as D;
use App\Services\LearningAnalytics\Engagement\{SessionBuilder as SB, DailyAggregator as DA, EngagementMetrics as EM};
date_default_timezone_set('Asia/Jakarta');
$fail = 0;
function eq($a, $b, $m, $eps = 1e-9) { global $fail; $ok = is_float($a) || is_float($b) ? abs($a - $b) < $eps : $a === $b; echo ($ok ? 'ok   ' : 'FAIL ') . $m . ($ok ? '' : ' => ' . var_export($a, true) . ' != ' . var_export($b, true)) . "\n"; if (!$ok) $fail++; }

// --- Statistik: nilai yang dihitung tangan ---
eq(D::mean([2, 4, 6]), 4.0, 'mean');
eq(D::sd([2, 4, 4, 4, 5, 5, 7, 9]), 2.138089935299395, 'sd sampel (R: sd(c(2,4,4,4,5,5,7,9)))');
eq(D::median([1, 3, 2, 4]), 2.5, 'median genap');
eq(D::quantile([1, 2, 3, 4, 5], 0.25), 2.0, 'Q1 tipe 7');
eq(D::quantile([1, 2, 3, 4], 0.75), 3.25, 'Q3 tipe 7 (R: 3.25)');
eq(D::gini([1, 1, 1, 1]), 0.0, 'gini merata');
eq(D::gini([0, 0, 0, 10]), 0.75, 'gini terkonsentrasi (n=4 maks 0.75)');
eq(D::gini([1, 2, 3, 4]), 0.25, 'gini 1..4');
eq(D::normalizedEntropy([5, 5, 5, 5]), 1.0, 'entropi seragam');
eq(D::normalizedEntropy([10, 0, 0, 0]), 0.0, 'entropi satu kategori');
eq(D::slope([1, 2, 3, 4]), 1.0, 'slope naik');
eq(D::slope([4, 2, 0]), -2.0, 'slope turun');
eq(D::cv([2, 2, 2]), 0.0, 'cv nol');
eq(D::mean([]), null, 'mean kosong');

// --- Sesi ---
$t0 = strtotime('2026-10-20 08:00:00');
$s = SB::build([$t0, $t0 + 60, $t0 + 120, $t0 + 120 + 3600, $t0 + 120 + 3600 + 30]);
eq(count($s), 2, '2 sesi (jeda 1 jam > 30 menit)');
eq($s[0]['seconds'], 60 + 60 + 60, 'sesi1 = 60+60 + tail 60');
eq($s[1]['seconds'], 30 + 60, 'sesi2 = 30 + tail 60');
$s = SB::build([$t0, $t0 + 1500]);   // jeda 25 mnt dalam sesi, dipangkas ke cap 600
eq($s[0]['seconds'], 600 + 60, 'cap idle 600 dtk');
eq(count(SB::build([$t0])), 1, 'sesi tunggal'); eq(SB::build([$t0])[0]['seconds'], 60, 'sesi tunggal = tail');
eq(SB::build([]), [], 'kosong');

// --- Agregasi harian ---
$ts = [strtotime('2026-10-20 08:00:00'), strtotime('2026-10-20 08:01:00'), strtotime('2026-10-21 19:00:00'), strtotime('2026-10-21 19:10:00'), strtotime('2026-10-21 20:30:00')];
$days = DA::days($ts);
eq(array_keys($days), ['2026-10-20', '2026-10-21'], 'hari');
eq($days['2026-10-21']['sessions'], 2, '2 sesi hari ke-2'); eq($days['2026-10-21']['events'], 3, 'events');
eq($days['2026-10-20']['peak_hour'], 8, 'jam puncak 08'); eq($days['2026-10-21']['peak_hour'], 19, 'jam puncak 19');

// --- Pola siswa (tanggal "hari ini" = 2026-10-26, Senin) ---
$today = new DateTimeImmutable('2026-10-26');
$mk = function (array $offsets, int $secs = 600, int $hour = 16) use ($today) {
    $o = [];
    foreach ($offsets as $off) { $o[$today->modify("-{$off} days")->format('Y-m-d')] = ['sessions' => 1, 'active_seconds' => $secs, 'events' => 10, 'peak_hour' => $hour]; }
    return $o;
};
$em = new EM();
// A: belajar tiap 2 hari selama 4 minggu → konsisten
$A = $em->student($mk(range(0, 26, 2)), $today);
eq($A['pattern'], 'konsisten', 'A konsisten'); eq($A['gap_cv'], 0.0, 'A gap_cv 0'); eq($A['weeks_active'], 4, 'A 4 minggu aktif');
eq($A['active_days'], 14, 'A 14 hari aktif'); eq($A['current_streak'], 1, 'A streak 1'); eq($A['days_since_last'], 0, 'A baru aktif hari ini');
// B: hanya 3 hari beruntun yang lalu (H-27..H-25) lalu hilang → berhenti
$B = $em->student($mk([27, 26, 25]), $today);
eq($B['pattern'], 'berhenti', 'B berhenti'); eq($B['days_since_last'], 25, 'B 25 hari sejak aktif');
// C: tak pernah aktif
$C = $em->student([], $today); eq($C['pattern'], 'tidak_aktif', 'C tidak aktif'); eq($C['days_since_last'], null, 'C null');
// D: aktif 2 hari saja, baru → data belum cukup
$D2 = $em->student($mk([0, 1]), $today); eq($D2['pattern'], 'baru', 'D baru');
// E: menumpuk — 6 hari aktif berdekatan di 7 hari terakhir dengan satu hari sangat panjang, sebelumnya nihil
$E = $em->student(array_replace($mk([0, 1, 2], 300), $mk([0], 40000)), $today);
eq($E['pattern'], 'menumpuk', 'E menumpuk'); eq($E['top_day_share'] >= 0.5, true, 'E satu hari ≥ 50% menit');
// F: aktif intens minggu 1, menurun hingga sedikit di minggu terakhir (hari aktif/minggu 6,4,2,1... hari ini aktif)
$F = $em->student($mk([0, 8, 9, 10, 11, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27]), $today);
// pos 0-6 (H-27..H-21): 27,26,25,24,23,22,21 → 7 hari; pos 7-13: 20..14 → 20,19,18,17,16,15 =6; pos14-20: 11,10,9,8 = 4 ; pos21-27: 0 = 1
eq($F['week_days'], [7, 6, 4, 1], 'F hari aktif per minggu'); eq($F['pattern'], 'menurun', 'F menurun'); eq($F['trend_slope'] < -0.75, true, 'F slope');
// G: sporadis — jarak tak teratur
$G = $em->student($mk([0, 1, 9, 10, 11, 19, 26]), $today); eq($G['pattern'], 'sporadis', 'G sporadis (ritme tak teratur, beban merata)'); echo "     G: {$G['pattern']} RI={$G['regularity_index']} cv={$G['gap_cv']} gini={$G['concentration']}\n";
$H = $em->student($mk([0, 7, 14, 21]), $today); eq($H['pattern'] !== 'menumpuk', true, 'H seminggu sekali merata ≠ menumpuk'); eq($H['top_day_share'], 0.25, 'H top day 25%');
// Akhir pekan: 26 Okt 2026 = Senin; H-1 Minggu, H-2 Sabtu
$W = $em->student($mk([1, 2, 8, 9]), $today); eq($W['weekend_share'], 1.0, 'semua akhir pekan (H-1 Min, H-2 Sab, H-8 Min, H-9 Sab)');
eq($A['peak_hour'], 16, 'jam puncak 16');

// --- Kohort & mingguan ---
$c = $em->cohort([$A, $B, $C, $D2]);
eq($c['n'], 4, 'kohort n'); eq($c['patterns']['konsisten'], 1, 'kohort 1 konsisten'); eq($c['pattern_pct']['berhenti'], 25.0, 'kohort 25% berhenti');
eq($c['active7_pct'], 50.0, 'aktif 7 hari: A & D2 = 2 dari 4');
$wk = $em->weeklyActive([$mk([0]), $mk([30]), $mk([8])], $today, 3);
eq(array_column($wk, 'active'), [0, 1, 1], 'aktif per minggu (rolling 7 hari)');   // w-2: t-20..t-14 kosong; w-1: t-13..t-7 memuat H-8; w0: t-6..t memuat H-0
echo $fail ? "\n$fail GAGAL\n" : "\nSEMUA LULUS\n"; exit($fail ? 1 : 0);
