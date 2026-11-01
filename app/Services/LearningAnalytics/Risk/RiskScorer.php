<?php

namespace App\Services\LearningAnalytics\Risk;

/**
 * Skor risiko berbasis aturan yang TRANSPARAN (kelas murni).
 *
 * Tiap komponen bernilai 0..1 (null = data tidak ada → komponen dilewati, bobotnya tidak dihitung):
 *  inactivity      min(hari_sejak_aktif / 14, 1); tidak pernah aktif = 1
 *  irregular       1 − indeks_keteraturan/100
 *  decline         clamp(−kemiringan_tren / 2, 0, 1)
 *  hint_dependence min(hint_l3_rate / 0,30, 1)
 *  inferential     min(salah_inferensial / (2 × ambang_intervensi), 1)
 *  lag             clamp((median_progres_kelas − progres) / 0,5, 0, 1)
 *  low_accuracy    1 − akurasi_percobaan_pertama
 *  wheel           min(jumlah_kompetensi_berputar / 2, 1)
 * Skor = 100 × Σ(w·c) / Σ(w pada komponen berdata).  Keyakinan = Σ(w berdata)/Σ(w semua):
 * ≥0,8 "cukup", ≥0,5 "sebagian", selainnya "terbatas".
 * Ini PERKIRAAN untuk membantu guru memilih siapa yang dicek dahulu, bukan diagnosis atau dasar sanksi.
 */
final class RiskScorer
{
    public const LABELS = [
        'inactivity' => 'Lama tidak aktif', 'irregular' => 'Jadwal belajar tidak teratur', 'decline' => 'Aktivitas menurun',
        'hint_dependence' => 'Sering memakai hint level 3', 'inferential' => 'Banyak salah inferensial',
        'lag' => 'Tertinggal dari teman sekelas', 'low_accuracy' => 'Akurasi percobaan pertama rendah', 'wheel' => 'Berputar-putar pada kompetensi',
    ];

    public const ACTIONS = [
        'inactivity' => 'Hubungi siswa, tanyakan kendala akses atau waktu, dan tetapkan target kecil minggu ini.',
        'irregular' => 'Bantu menetapkan jadwal belajar tetap (mis. 3 hari per minggu, 20 menit).',
        'decline' => 'Cek apa yang berubah (materi sulit, jadwal sekolah); beri tugas ringan untuk membangun kembali kebiasaan.',
        'hint_dependence' => 'Atur kebijakan bantuan AI (hint bertahap/wajib hint dulu) dan latih membaca hint sebelum menjawab.',
        'inferential' => 'Beri latihan inferensi terarah (menyimpulkan makna tersirat) dengan contoh bertahap.',
        'lag' => 'Pasangkan dengan teman sebaya atau beri jam pendampingan agar mengejar progres kelas.',
        'low_accuracy' => 'Tinjau prasyarat kosakata/tata bahasa; ulangi materi dasar sebelum lanjut.',
        'wheel' => 'Ganti strategi untuk kompetensi tersebut: contoh terselesaikan (worked example), penjelasan langsung, lalu latihan.',
    ];

    public function __construct(private array $weights, private float $high = 60.0, private float $medium = 35.0)
    {
    }

    /**
     * @param array $f days_since_last,regularity_index,trend_slope,hint_l3_rate,infer,progress,first_try_acc,wheel_n,active_days,items
     * @return array{score:float,level:string,confidence:string,coverage:float,factors:array,components:array}
     */
    public function score(array $f, float $inferThreshold, float $classMedianProgress): array
    {
        if (! ($f['started'] ?? true)) {
            return ['score' => 0.0, 'level' => 'belum_mulai', 'confidence' => 'terbatas', 'coverage' => 0.0,
                'factors' => [['key' => 'not_started', 'label' => 'Belum mulai belajar', 'value' => 1.0, 'detail' => 'belum ada aktivitas latihan/pelajaran', 'action' => 'Ajak siswa memulai satu pelajaran singkat dan pastikan akun/kelas sudah benar.']],
                'components' => array_fill_keys(array_keys(self::LABELS), null)];
        }
        $c = [
            'inactivity' => $f['days_since_last'] === null ? 1.0 : min(1.0, $f['days_since_last'] / 14),
            'irregular' => $f['regularity_index'] === null ? null : 1 - $f['regularity_index'] / 100,
            'decline' => $f['trend_slope'] === null ? null : max(0.0, min(1.0, -$f['trend_slope'] / 2)),
            'hint_dependence' => $f['hint_l3_rate'] === null ? null : min(1.0, $f['hint_l3_rate'] / 0.30),
            'inferential' => min(1.0, ($f['infer'] ?? 0) / max(1.0, 2 * $inferThreshold)),
            // kelas yang seluruhnya masih rendah (median < 10%) tidak bermakna sebagai pembanding
            'lag' => ($f['progress'] === null || $classMedianProgress < 0.10) ? null : max(0.0, min(1.0, ($classMedianProgress - $f['progress']) / 0.5)),
            'low_accuracy' => $f['first_try_acc'] === null ? null : 1 - $f['first_try_acc'],
            'wheel' => $f['wheel_n'] === null ? null : min(1.0, $f['wheel_n'] / 2),
        ];
        $num = $den = $all = 0.0;
        $contrib = [];
        foreach ($c as $k => $v) {
            $w = (float) ($this->weights[$k] ?? 0);
            $all += $w;
            if ($v !== null) {
                $num += $w * $v;
                $den += $w;
                $contrib[$k] = $w * $v;
            }
        }
        $score = $den > 0 ? round(100 * $num / $den, 1) : 0.0;
        $coverage = $all > 0 ? $den / $all : 0.0;
        arsort($contrib);
        $factors = [];
        foreach ($contrib as $k => $v) {
            if ($c[$k] >= 0.2 && count($factors) < 3) {
                $factors[] = ['key' => $k, 'label' => self::LABELS[$k], 'value' => round($c[$k], 2), 'detail' => $this->detail($k, $f), 'action' => self::ACTIONS[$k]];
            }
        }

        return [
            'score' => $score,
            'level' => self::levelFor($score, $this->high, $this->medium),
            'confidence' => $coverage >= 0.8 ? 'cukup' : ($coverage >= 0.5 ? 'sebagian' : 'terbatas'),
            'coverage' => round($coverage, 2),
            'factors' => $factors,
            'components' => array_map(fn ($v) => $v === null ? null : round($v, 3), $c),
        ];
    }

    public static function levelFor(float $score, float $high, float $medium): string
    {
        return $score >= $high ? 'tinggi' : ($score >= $medium ? 'sedang' : 'rendah');
    }

    /**
     * Kalibrasi ambang berbasis persentil pada siswa yang sudah mulai belajar:
     * tinggi = max(floorHigh, persentil (100-pctHigh)), sedang = max(floorMed, persentil (100-pctHigh-pctMed)).
     * Bila siswa < $minN, pakai ambang tetap. Lantai mencegah menandai skor yang sebenarnya rendah.
     *
     * @param float[] $scores
     * @return array{high:float,medium:float,calibrated:bool,n:int}
     */
    public static function calibrate(array $scores, array $cfg, float $fixedHigh, float $fixedMedium): array
    {
        $n = count($scores);
        if ($n < (int) ($cfg['min_n'] ?? 30)) {
            return ['high' => $fixedHigh, 'medium' => $fixedMedium, 'calibrated' => false, 'n' => $n];
        }
        sort($scores);
        $q = function (float $p) use ($scores, $n): float {
            $pos = $p / 100 * ($n - 1);
            $lo = (int) floor($pos);
            $hi = (int) ceil($pos);

            return $scores[$lo] + ($scores[$hi] - $scores[$lo]) * ($pos - $lo);
        };
        $pH = (float) ($cfg['pct_high'] ?? 8);
        $pM = (float) ($cfg['pct_medium'] ?? 17);
        $high = max((float) ($cfg['floor_high'] ?? 45), round($q(100 - $pH), 1));
        $med = max((float) ($cfg['floor_medium'] ?? 25), round($q(100 - $pH - $pM), 1));
        $med = min($med, $high);

        return ['high' => $high, 'medium' => $med, 'calibrated' => true, 'n' => $n];
    }

    private function detail(string $k, array $f): string
    {
        return match ($k) {
            'inactivity' => $f['days_since_last'] === null ? 'belum pernah aktif' : $f['days_since_last'] . ' hari sejak aktif terakhir',
            'irregular' => 'indeks keteraturan ' . round($f['regularity_index']),
            'decline' => 'tren ' . round($f['trend_slope'], 1) . ' hari aktif/minggu',
            'hint_dependence' => 'hint level 3 pada ' . round($f['hint_l3_rate'] * 100) . '% butir',
            'inferential' => ($f['infer'] ?? 0) . ' salah inferensial',
            'lag' => 'progres ' . round($f['progress'] * 100) . '% (kelas lebih tinggi)',
            'low_accuracy' => 'akurasi ' . round($f['first_try_acc'] * 100) . '%',
            'wheel' => $f['wheel_n'] . ' kompetensi berputar-putar',
            default => '',
        };
    }
}
