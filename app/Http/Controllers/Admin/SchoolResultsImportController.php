<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssessmentSubmission;
use App\Models\Lesson;
use App\Models\Unit;
use App\Models\User;
use App\Support\SimpleXlsxReader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Impor nilai pelengkap dari Excel ke tabel sumber yang dipakai menu
 * "Analisis Hasil Belajar Sekolah" (SchoolResultsController).
 *
 * PRINSIP KESELAMATAN DATA (jangan diubah tanpa sepengetahuan admin):
 *
 *  1. Halaman laporan di admin.school-results TIDAK punya tabel
 *     tersendiri — semua angkanya dihitung ulang setiap saat dari
 *     assessment_submissions, user_lesson_progress, learning_events,
 *     dan users.xp (lihat SchoolLearningAnalytics::load() dan
 *     SchoolLearningMetrics::compute()). Mengubah "tampilan laporan"
 *     berarti mengubah baris di tabel-tabel itu — tidak ada jalan lain.
 *
 *  2. Import ini HANYA mengisi data yang BELUM ADA (kosong):
 *     XP, skor Listening/Reading/Writing/Speaking, Pretest, Posttest.
 *     Kalau siswa sudah punya nilai asli untuk salah satu dari itu,
 *     baris itu DILEWATI, bukan ditimpa. Ini supaya data aktivitas
 *     belajar asli tidak pernah tertindih oleh data pelengkap/dummy.
 *
 *  3. Import ini SENGAJA TIDAK menyentuh user_lesson_progress atau
 *     learning_events (Pelajaran Selesai, Progres, Perlu Intervensi,
 *     Salah Inferensial, Hint Level 3). Dua tabel itu memberi makan
 *     langsung ke sistem risk-scoring dan notifikasi guru
 *     (lihat app/Console/Commands/LaScoreRiskCommand.php dan
 *     NotifyTeachersOfInterventionCommand.php). Mengisinya dengan data
 *     karangan bisa memicu atau menyembunyikan notifikasi intervensi
 *     ke guru berdasarkan aktivitas yang tidak pernah terjadi.
 *
 *  4. Setiap baris assessment_submissions yang dibuat oleh fitur ini
 *     ditandai di kolom `feedback` dengan label "[Impor nilai
 *     pelengkap ...]" supaya bisa dibedakan dari pengerjaan asli siswa
 *     kapan pun diperlukan (audit, koreksi, atau rollback manual).
 */
class SchoolResultsImportController extends Controller
{
    private const SHEET_NAME = 'Hasil Belajar';

    private const MARKER = 'Impor nilai pelengkap';

    public function create(): View
    {
        return view('admin.school-results.import');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx', 'max:10240'],
        ]);

        $path = $request->file('file')->getRealPath();

        try {
            $rows = SimpleXlsxReader::rowsFromSheet($path, self::SHEET_NAME);
        } catch (\RuntimeException $e) {
            return back()->with(
                'error',
                'Gagal membaca file: ' . $e->getMessage() . ' Pastikan file punya sheet bernama "' . self::SHEET_NAME . '".'
            );
        }

        if ($rows === []) {
            return back()->with('error', 'Sheet "' . self::SHEET_NAME . '" kosong.');
        }

        $header = array_map(fn ($v) => strtolower(trim((string) $v)), $rows[0]);
        $map = [];
        foreach ($header as $i => $name) {
            $map[$name] = $i;
        }

        if (! isset($map['email'])) {
            return back()->with('error', 'Kolom "Email" tidak ditemukan di sheet "' . self::SHEET_NAME . '".');
        }

        $numericCol = fn (array $row, string $key): ?float => $this->numericValue($row[$map[$key] ?? -1] ?? null);

        $skills = ['listening', 'reading', 'writing', 'speaking'];

        // Lesson "penampung" untuk submission yang dibuat fitur ini.
        // Dicari sekali di awal, bukan per baris, supaya tidak query berulang.
        //
        // PENTING soal kolom 'skill' pada submission pretest/posttest:
        // SchoolLearningMetrics::compute() menghitung Listening/Reading/
        // Writing/Speaking dari SUBMISSION TERAKHIR (id tertinggi) yang
        // punya skill itu — TANPA peduli type-nya pretest/posttest/unit.
        // Jadi satu baris pretest/posttest yang kita buat untuk mengisi
        // rata-rata, kalau asal pilih skill, bisa membajak tampilan skor
        // skill lain (karena baris baru selalu id tertinggi = selalu
        // "terbaru"). Makanya pemilihan skill untuk baris pretest/
        // posttest filler dilakukan PER SISWA di dalam loop (lihat
        // $safeFillerSkill di bawah), bukan sekali di sini — dan hanya
        // boleh jatuh ke skill yang masih benar-benar kosong untuk siswa
        // itu. Di sini kita hanya siapkan lesson per (unit pretest/
        // posttest x skill) supaya tinggal dipilih sesuai skill aman itu.
        $skillLesson = [];
        $pretestLessonBySkill = [];
        $posttestLessonBySkill = [];
        foreach ($skills as $sk) {
            $skillLesson[$sk] = Lesson::query()
                ->where('skill_type', $sk)
                ->whereHas('unit', fn ($q) => $q->where('type', 'unit'))
                ->orderBy('id')
                ->first();

            $pretestLessonBySkill[$sk] = Lesson::query()
                ->where('skill_type', $sk)
                ->whereHas('unit', fn ($q) => $q->where('type', 'pretest'))
                ->orderBy('id')
                ->first();

            $posttestLessonBySkill[$sk] = Lesson::query()
                ->where('skill_type', $sk)
                ->whereHas('unit', fn ($q) => $q->where('type', 'posttest'))
                ->orderBy('id')
                ->first();
        }

        $stamp = now()->format('Y-m-d H:i');
        $marker = '[' . self::MARKER . ' — data dummy/pelengkap, bukan pengerjaan siswa. Diimpor oleh '
            . (Auth::user()->name ?? 'admin') . ' pada ' . $stamp . ']';

        $summary = [
            'rows' => count($rows) - 1,
            'not_found' => 0,
            'invalid_email' => 0,
            'xp_filled' => 0,
            'skill_filled' => array_fill_keys($skills, 0),
            'pretest_filled' => 0,
            'posttest_filled' => 0,
            'skipped_has_data' => 0,
            'skipped_no_lesson' => 0,
            'skipped_no_safe_skill' => 0,
        ];

        $auditLog = [];

        foreach (array_slice($rows, 1) as $row) {
            $email = strtolower(trim((string) ($row[$map['email']] ?? '')));
            if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $summary['invalid_email']++;
                continue;
            }

            $student = User::query()->where('email', $email)->where('role', 'student')->first();
            if (! $student) {
                $summary['not_found']++;
                continue;
            }

            DB::transaction(function () use (
                $row, $map, $numericCol, $student, $skills, $skillLesson,
                $pretestLessonBySkill, $posttestLessonBySkill, $marker, &$summary, &$auditLog
            ) {
                // ---- XP ----
                // Hanya diisi kalau XP saat ini masih 0 (belum pernah
                // dapat reward apa pun) DAN file punya nilai > 0.
                $fileXp = $numericCol($row, 'xp');
                if ($fileXp !== null && $fileXp > 0 && (int) $student->xp === 0) {
                    $old = $student->xp;
                    $student->xp = (int) round($fileXp);
                    $student->save();
                    $summary['xp_filled']++;
                    $auditLog[] = [
                        'email' => $student->email, 'field' => 'xp',
                        'old' => $old, 'new' => $student->xp,
                    ];
                } elseif ($fileXp !== null && $fileXp > 0) {
                    $summary['skipped_has_data']++;
                }

                // ---- Tahap 1: baca kondisi SEBELUM menulis apa pun ----
                //
                // $hasRealSkill[sk]   = siswa sudah punya skor asli utk skill itu
                // $toFillSkill[sk]    = file punya nilai DAN skill itu masih kosong
                //                       -> ini skill yang akan benar-benar diisi
                $hasRealSkill = [];
                $toFillSkill = [];
                foreach ($skills as $sk) {
                    $hasRealSkill[$sk] = AssessmentSubmission::query()
                        ->where('user_id', $student->id)
                        ->where('skill', $sk)
                        ->whereNotNull('final_score')
                        ->exists();

                    $fileVal = $numericCol($row, $sk);
                    if ($fileVal === null) {
                        continue;
                    }
                    if ($hasRealSkill[$sk]) {
                        $summary['skipped_has_data']++;
                        continue;
                    }
                    $toFillSkill[$sk] = $fileVal;
                }

                $hasRealPretest = AssessmentSubmission::query()
                    ->where('user_id', $student->id)->where('type', 'pretest')
                    ->whereNotNull('final_score')->exists();
                $hasRealPosttest = AssessmentSubmission::query()
                    ->where('user_id', $student->id)->where('type', 'posttest')
                    ->whereNotNull('final_score')->exists();

                $filePretest = $numericCol($row, 'pretest');
                $filePosttest = $numericCol($row, 'posttest');

                // ---- Tahap 2: pilih skill "aman" untuk filler pretest/posttest ----
                //
                // WAJIB diambil dari $toFillSkill (skill yang MEMANG akan
                // diisi baris ini) — bukan sembarang skill. Alasannya:
                // baris filler pretest/posttest ini akan kita INSERT lebih
                // dulu (id lebih rendah), lalu skill fill-nya di Tahap 3
                // di-insert SESUDAHNYA (id lebih tinggi) khusus untuk skill
                // yang sama — supaya skill fill itu yang menang sebagai
                // "submission terbaru" dan filler pretest/posttest-nya
                // otomatis tertimpa secara tampilan (tanpa merusak nilai
                // skill yang sebenarnya). Kalau tidak ada skill yang akan
                // diisi baris ini (karena ke-4 skill sudah punya data asli
                // semua, atau file tidak punya nilai skill), TIDAK ADA
                // tempat aman untuk filler ini -> dilewati, Pretest/
                // Posttest tetap kosong daripada merusak skill lain.
                // $toFillSkill mengikuti urutan $skills (listening lebih
                // dulu), jadi array_keys-nya sudah terurut wajar.
                $available = array_keys($toFillSkill);

                $pretestSkill = ($filePretest !== null && ! $hasRealPretest && $available !== [])
                    ? $available[0]
                    : null;

                // Untuk posttest, pakai skill LAIN kalau ada lebih dari
                // satu pilihan (supaya tidak keduanya menimpa skill yang
                // sama persis) — kalau cuma ada 1 skill yang akan diisi,
                // berbagi skill yang sama dengan pretest tidak masalah,
                // karena keduanya tetap tertimpa dengan benar oleh skill
                // fill asli di Tahap 3.
                $posttestSkill = null;
                if ($filePosttest !== null && ! $hasRealPosttest && $available !== []) {
                    $posttestSkill = (count($available) > 1 && $available[1] !== $pretestSkill)
                        ? $available[1]
                        : (($available[0] !== $pretestSkill) ? $available[0] : ($available[1] ?? $available[0]));
                }

                // ---- Tahap 3: INSERT — filler pretest/posttest dulu, baru skill fill ----
                if ($pretestSkill !== null) {
                    $lesson = $pretestLessonBySkill[$pretestSkill] ?? null;
                    if ($lesson) {
                        $sub = AssessmentSubmission::create([
                            'user_id' => $student->id,
                            'unit_id' => $lesson->unit_id,
                            'lesson_id' => $lesson->id,
                            'type' => 'pretest',
                            'skill' => $pretestSkill,
                            'final_score' => (int) round($filePretest),
                            'status' => 'completed',
                            'feedback' => $marker,
                            'submitted_at' => now(),
                        ]);
                        $summary['pretest_filled']++;
                        $auditLog[] = [
                            'email' => $student->email, 'field' => 'pretest',
                            'old' => null, 'new' => $sub->final_score,
                            'assessment_submission_id' => $sub->id,
                        ];
                    } else {
                        $summary['skipped_no_lesson']++;
                    }
                } elseif ($filePretest !== null && ! $hasRealPretest) {
                    $summary['skipped_no_safe_skill']++;
                } elseif ($filePretest !== null && $hasRealPretest) {
                    $summary['skipped_has_data']++;
                }

                if ($posttestSkill !== null) {
                    $lesson = $posttestLessonBySkill[$posttestSkill] ?? null;
                    if ($lesson) {
                        $sub = AssessmentSubmission::create([
                            'user_id' => $student->id,
                            'unit_id' => $lesson->unit_id,
                            'lesson_id' => $lesson->id,
                            'type' => 'posttest',
                            'skill' => $posttestSkill,
                            'final_score' => (int) round($filePosttest),
                            'status' => 'completed',
                            'feedback' => $marker,
                            'submitted_at' => now(),
                        ]);
                        $summary['posttest_filled']++;
                        $auditLog[] = [
                            'email' => $student->email, 'field' => 'posttest',
                            'old' => null, 'new' => $sub->final_score,
                            'assessment_submission_id' => $sub->id,
                        ];
                    } else {
                        $summary['skipped_no_lesson']++;
                    }
                } elseif ($filePosttest !== null && ! $hasRealPosttest) {
                    $summary['skipped_no_safe_skill']++;
                } elseif ($filePosttest !== null && $hasRealPosttest) {
                    $summary['skipped_has_data']++;
                }

                // Sekarang insert skill fill sesungguhnya — SELALU setelah
                // filler pretest/posttest di atas, supaya id-nya lebih
                // tinggi dan menang sebagai "submission terbaru" untuk
                // skill itu (lihat penjelasan Tahap 2).
                foreach ($toFillSkill as $sk => $fileVal) {
                    $lesson = $skillLesson[$sk];
                    if (! $lesson) {
                        $summary['skipped_no_lesson']++;
                        continue;
                    }

                    $sub = AssessmentSubmission::create([
                        'user_id' => $student->id,
                        'unit_id' => $lesson->unit_id,
                        'lesson_id' => $lesson->id,
                        'type' => 'unit',
                        'skill' => $sk,
                        'final_score' => (int) round($fileVal),
                        'status' => 'completed',
                        'feedback' => $marker,
                        'submitted_at' => now(),
                    ]);
                    $summary['skill_filled'][$sk]++;
                    $auditLog[] = [
                        'email' => $student->email, 'field' => $sk,
                        'old' => null, 'new' => $sub->final_score,
                        'assessment_submission_id' => $sub->id,
                    ];
                }
            });
        }

        $logPath = null;
        if ($auditLog !== []) {
            $logName = 'import-logs/school-results-' . now()->format('Ymd-His') . '.json';
            Storage::disk('local')->put($logName, json_encode([
                'imported_by' => Auth::user()->email,
                'imported_at' => now()->toIso8601String(),
                'summary' => $summary,
                'changes' => $auditLog,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $logPath = $logName;
        }

        $msg = sprintf(
            'Impor selesai. Baris diproses: %d. Terisi — XP: %d, Listening: %d, Reading: %d, Writing: %d, Speaking: %d, Pretest: %d, Posttest: %d. '
            . 'Dilewati (sudah ada data asli): %d. Dilewati (tidak ada slot skill kosong yang aman untuk Pretest/Posttest): %d. '
            . 'Dilewati (tidak ketemu lesson penampung): %d. Email tidak ditemukan/bukan siswa: %d. Email tidak valid: %d.',
            $summary['rows'],
            $summary['xp_filled'],
            $summary['skill_filled']['listening'],
            $summary['skill_filled']['reading'],
            $summary['skill_filled']['writing'],
            $summary['skill_filled']['speaking'],
            $summary['pretest_filled'],
            $summary['posttest_filled'],
            $summary['skipped_has_data'],
            $summary['skipped_no_safe_skill'],
            $summary['skipped_no_lesson'],
            $summary['not_found'],
            $summary['invalid_email'],
        );

        if ($logPath) {
            $msg .= ' Rincian perubahan tersimpan di storage/app/' . $logPath . '.';
        }

        return back()->with('success', $msg);
    }

    private function numericValue(mixed $raw): ?float
    {
        if ($raw === null) {
            return null;
        }
        $s = trim((string) $raw);
        if ($s === '') {
            return null;
        }
        // Terima koma sebagai desimal juga, seperti export Excel lokal.
        $s = str_replace(',', '.', $s);
        if (! is_numeric($s)) {
            return null;
        }

        return (float) $s;
    }
}
