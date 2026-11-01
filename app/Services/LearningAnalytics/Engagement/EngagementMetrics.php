<?php

namespace App\Services\LearningAnalytics\Engagement;

use App\Services\LearningAnalytics\Stats\Descriptive as D;
use DateTimeImmutable;

/**
 * Metrik keterlibatan & konsistensi akses (kelas murni, tanpa DB).
 *
 * Masukan per siswa: peta hari aktif 'Y-m-d' => {sessions, active_seconds, events, peak_hour}.
 * Jendela analisis: 28 hari terakhir (4 minggu, termasuk hari ini).
 *
 * DEFINISI OPERASIONAL
 *  active_days      jumlah hari dengan ≥1 aktivitas dalam jendela
 *  weeks_active     jumlah minggu (dari 4) dengan ≥2 hari aktif
 *  current_streak   hari beruntun aktif berakhir hari ini atau kemarin
 *  gap_cv           koefisien variasi jarak (hari) antar-hari-aktif berurutan; kecil = ritme teratur
 *  dow_entropy      entropi ternormalisasi sebaran hari-aktif pada 7 hari dalam sepekan
 *  concentration    Gini menit-aktif antar HARI AKTIF (hari tanpa aktivitas tidak dihitung, agar jarang-belajar
 *                   bukan otomatis "menumpuk"); tinggi = beban terkumpul di sedikit hari
 *  top_day_share    porsi menit-aktif pada hari tersibuk; ≥ cram_share (0,5) dengan ≥3 hari aktif → "menumpuk"
 *  trend_slope      kemiringan OLS jumlah hari aktif per minggu (4 minggu); negatif = menurun
 *  regularity_index 0–100 = 100·(0,4·weeks_active/4 + 0,3·(1−min(gap_cv,1,5)/1,5) + 0,3·(1−concentration))
 *                   Heuristik transparan, BELUM tervalidasi; bobot di config.
 *  pattern          label deskriptif (lihat classify()); bukan label permanen siswa.
 *
 * Catatan: aktivitas dibaca dari jejak belajar yang tercatat (event kuis, submission,
 * pelajaran selesai, transaksi XP). Login/page-view tidak tercatat, sehingga durasi
 * adalah estimasi.
 */
final class EngagementMetrics
{
    public const WINDOW = 28;

    public const PATTERN_LABELS = [
        'konsisten' => 'Konsisten',
        'sporadis' => 'Sporadis',
        'menumpuk' => 'Belajar menumpuk',
        'menurun' => 'Aktivitas menurun',
        'berhenti' => 'Berhenti belajar',
        'tidak_aktif' => 'Belum mulai',
        'baru' => 'Data belum cukup',
        'hanya_tes' => 'Hanya ikut tes',
    ];

    public function __construct(private array $cfg = [])
    {
        $this->cfg += [
            'stop_days' => 14,        // tak aktif ≥ N hari (setelah pernah aktif) → berhenti
            'cram_share' => 0.50,     // satu hari memuat ≥ separuh menit aktif → menumpuk
            'decline_slope' => -0.75, // hari aktif/minggu per minggu
            'consistent_ri' => 65.0,  // regularity_index ≥ ini → konsisten
            'min_days' => 3,          // hari aktif minimal agar pola dinilai
        ];
    }

    /**
     * Hari aktif BELAJAR: bila kolom pemisahan ada, hanya hari dengan aktivitas latihan/pelajaran (learn_events > 0);
     * hari yang hanya berisi pretest/posttest tidak dihitung. Tanpa kolom itu: perilaku lama (semua jejak).
     */
    public static function isLearningDay(array $row): bool
    {
        return array_key_exists('learn_events', $row) ? $row['learn_events'] > 0 : ($row['events'] > 0 || $row['sessions'] > 0);
    }

    /** @return array<string,mixed> */
    public function student(array $days, DateTimeImmutable $today): array
    {
        $today = $today->setTime(0, 0);
        $series = [];   // 28 hari, tertua → terbaru: menit aktif
        $activeIdx = [];
        $weekDays = [0, 0, 0, 0];
        $dow = array_fill(0, 7, 0);
        $sessions = 0;
        $seconds = 0;
        $events = 0;
        $weekend = 0;
        $hours = [];
        for ($i = self::WINDOW - 1; $i >= 0; $i--) {
            $d = $today->modify("-{$i} days");
            $key = $d->format('Y-m-d');
            $row = $days[$key] ?? null;
            $pos = self::WINDOW - 1 - $i;
            $series[] = $row ? $row['active_seconds'] / 60 : 0.0;
            if ($row && self::isLearningDay($row)) {
                $activeIdx[] = $pos;
                $weekDays[intdiv($pos, 7)]++;
                $dow[(int) $d->format('N') - 1]++;
                $weekend += ((int) $d->format('N') >= 6) ? 1 : 0;
                $sessions += $row['sessions'];
                $seconds += $row['active_seconds'];
                $events += $row['events'];
                $hours[$row['peak_hour']] = ($hours[$row['peak_hour']] ?? 0) + 1;
            }
        }
        $activeDays = count($activeIdx);

        // hari sejak aktif terakhir: cari di SELURUH data (bisa di luar jendela)
        $last = null;
        $testOnly = 0;
        $visitOnly = 0;
        foreach ($days as $k => $r) {
            if (! self::isLearningDay($r)) {
                $visitOnly += ($r['page_views'] ?? 0) > 0 ? 1 : 0;
                $testOnly += ($r['test_events'] ?? 0) > 0 ? 1 : 0;
                continue;
            }
            $last = ($last === null || $k > $last) ? $k : $last;
        }
        $daysSince = $last === null ? null : (int) $today->diff(new DateTimeImmutable($last))->days;

        // streak
        $set = array_flip($activeIdx);
        $longest = $run = 0;
        for ($p = 0; $p < self::WINDOW; $p++) {
            $run = isset($set[$p]) ? $run + 1 : 0;
            $longest = max($longest, $run);
        }
        $cur = 0;
        $p = isset($set[self::WINDOW - 1]) ? self::WINDOW - 1 : (isset($set[self::WINDOW - 2]) ? self::WINDOW - 2 : -1);
        while ($p >= 0 && isset($set[$p])) {
            $cur++;
            $p--;
        }

        $gaps = [];
        for ($i = 1; $i < $activeDays; $i++) {
            $gaps[] = $activeIdx[$i] - $activeIdx[$i - 1];
        }
        $gapCv = count($gaps) >= 2 ? D::cv($gaps) : null;
        $activeMin = array_values(array_filter($series, fn ($x) => $x > 0));
        $conc = count($activeMin) >= 2 ? D::gini($activeMin) : null;
        $tot = array_sum($activeMin);
        $topShare = ($tot > 0 && count($activeMin) >= 2) ? round(max($activeMin) / $tot, 3) : null;
        $slope = D::slope($weekDays);
        $weeksActive = count(array_filter($weekDays, fn ($c) => $c >= 2));

        $ri = null;
        if ($activeDays >= $this->cfg['min_days'] && $gapCv !== null && $conc !== null) {
            $ri = round(100 * (0.4 * $weeksActive / 4
                + 0.3 * (1 - min($gapCv, 1.5) / 1.5)
                + 0.3 * (1 - min($conc, 1.0))), 1);
        }
        arsort($hours);

        $out = [
            'active_days' => $activeDays,
            'week_days' => $weekDays,
            'weeks_active' => $weeksActive,
            'sessions' => $sessions,
            'minutes' => (int) round($seconds / 60),
            'events' => $events,
            'avg_session_min' => $sessions > 0 ? round($seconds / 60 / $sessions, 1) : null,
            'days_since_last' => $daysSince,
            'current_streak' => $cur,
            'longest_streak' => $longest,
            'gap_cv' => $gapCv === null ? null : round($gapCv, 2),
            'dow_entropy' => ($e = D::normalizedEntropy($dow)) === null || $activeDays < 3 ? null : round($e, 2),
            'weekend_share' => $activeDays > 0 ? round($weekend / $activeDays, 2) : null,
            'concentration' => $conc === null ? null : round($conc, 2),
            'top_day_share' => $topShare,
            'trend_slope' => $slope === null ? null : round($slope, 2),
            'peak_hour' => $hours === [] ? null : (int) array_key_first($hours),
            'regularity_index' => $ri,
            'test_only_days' => $testOnly,
            'visit_only_days' => $visitOnly,
        ];
        $out['pattern'] = $this->classify($out);

        return $out;
    }

    /**
     * Urutan prioritas (aturan pertama yang cocok):
     * tidak_aktif → berhenti → baru → menumpuk → menurun → konsisten → sporadis
     */
    public function classify(array $m): string
    {
        $c = $this->cfg;
        if ($m['days_since_last'] === null) {
            return ($m['test_only_days'] ?? 0) > 0 ? 'hanya_tes' : 'tidak_aktif';
        }
        if ($m['days_since_last'] >= $c['stop_days']) {
            return 'berhenti';
        }
        if ($m['active_days'] < $c['min_days']) {
            return 'baru';
        }
        if ($m['top_day_share'] !== null && $m['top_day_share'] >= $c['cram_share']) {
            return 'menumpuk';
        }
        if ($m['trend_slope'] !== null && $m['trend_slope'] <= $c['decline_slope']) {
            return 'menurun';
        }
        if ($m['regularity_index'] !== null && $m['regularity_index'] >= $c['consistent_ri']) {
            return 'konsisten';
        }

        return 'sporadis';
    }

    /**
     * Ringkasan kelompok dari daftar hasil student().
     * @param  array<int,array> $students
     */
    public function cohort(array $students): array
    {
        $n = count($students);
        $labels = array_fill_keys(array_keys(self::PATTERN_LABELS), 0);
        $ri = [];
        $ad = [];
        $mins = [];
        $hours = array_fill(0, 24, 0);
        foreach ($students as $s) {
            $labels[$s['pattern']]++;
            if ($s['regularity_index'] !== null) {
                $ri[] = $s['regularity_index'];
            }
            $ad[] = $s['active_days'];
            if ($s['active_days'] > 0) {
                $mins[] = $s['minutes'];   // median menit hanya pada siswa yang pernah belajar pada jendela
            }
            if ($s['peak_hour'] !== null) {
                $hours[$s['peak_hour']]++;
            }
        }

        return [
            'n' => $n,
            'patterns' => $labels,
            'pattern_pct' => array_map(fn ($c) => $n ? round($c * 100 / $n, 1) : 0.0, $labels),
            'ri_mean' => ($v = D::mean($ri)) === null ? null : round($v, 1),
            'ri_median' => ($v = D::median($ri)) === null ? null : round($v, 1),
            'ri_n' => count($ri),
            'active_days_mean' => ($v = D::mean($ad)) === null ? null : round($v, 1),
            'minutes_median' => ($v = D::median($mins)) === null ? null : round($v, 0),
            'active7_pct' => $n ? round(100 * count(array_filter($students, fn ($s) => $s['days_since_last'] !== null && $s['days_since_last'] <= 6)) / $n, 1) : 0.0,
            'peak_hours' => $hours,
        ];
    }

    /**
     * Persentase siswa aktif (≥1 hari aktif) per minggu kalender-rolling untuk $weeks minggu terakhir.
     * @param array<int,array<string,array>> $allDays  daftar peta hari aktif per siswa
     * @return array<int,array{week_start:string,active:int,pct:float}>
     */
    public function weeklyActive(array $allDays, DateTimeImmutable $today, int $weeks = 8): array
    {
        $today = $today->setTime(0, 0);
        $n = count($allDays);
        $out = [];
        for ($w = $weeks - 1; $w >= 0; $w--) {
            $end = $today->modify('-' . ($w * 7) . ' days');
            $start = $end->modify('-6 days');
            $act = 0;
            foreach ($allDays as $days) {
                for ($i = 0; $i < 7; $i++) {
                    if (isset($days[$start->modify("+{$i} days")->format('Y-m-d')])) {
                        $act++;
                        break;
                    }
                }
            }
            $out[] = ['week_start' => $start->format('Y-m-d'), 'active' => $act, 'pct' => $n ? round($act * 100 / $n, 1) : 0.0];
        }

        return $out;
    }
}
