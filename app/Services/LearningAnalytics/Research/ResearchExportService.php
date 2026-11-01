<?php

namespace App\Services\LearningAnalytics\Research;

use App\Services\Analytics\SchoolLearningAnalytics;
use App\Services\LearningAnalytics\Engagement\EngagementMetrics;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Menyusun baris ekspor riset (pseudonim atau teridentifikasi). Tidak menulis apa pun ke database. */
class ResearchExportService
{
    public function __construct(private readonly SchoolLearningAnalytics $base)
    {
    }

    private ?Pseudonymizer $lastPs = null;

    /** @var int[] */
    private array $lastIds = [];

    /**
     * Format panjang: satu baris per jawaban butir (untuk IRT / model multilevel), hanya siswa pada ekspor terakhir build().
     * @return array{headers:array,rows:array,n:int}
     */
    public function buildLong(): array
    {
        $ps = $this->lastPs;
        $info = DB::table('users')->whereIn('id', $this->lastIds)->get(['id', 'school', 'major', 'grade', 'parallel'])->keyBy('id');
        $size = [];
        foreach ($info as $u) {
            $k = $u->school . '|' . $u->major . '|' . $u->grade . '|' . $u->parallel;
            $size[$k] = ($size[$k] ?? 0) + 1;
        }
        $rows = [];
        foreach (array_chunk($this->lastIds, 500) as $chunk) {
            foreach (DB::table('assessment_answers as a')->join('assessment_submissions as s', 's.id', '=', 'a.assessment_submission_id')
                ->whereIn('s.user_id', $chunk)->whereNotNull('a.is_correct')->orderBy('s.user_id')->orderBy('a.id')
                ->get(['s.user_id', 's.id as sub', 's.type', 's.skill', 's.unit_id', 's.lesson_id', 'a.question_type', 'a.question_id', 'a.selected_option', 'a.is_correct']) as $r) {
                $u = $info[$r->user_id];
                $k = $u->school . '|' . $u->major . '|' . $u->grade . '|' . $u->parallel;
                $rows[] = [$ps->userId((int) $r->user_id), $ps->school($u->school), $ps->classId($u->school, $k, $size[$k], $u->grade), $r->type, $r->skill, $r->unit_id, $r->lesson_id,
                    $r->question_type . ':' . $r->question_id, $r->selected_option, (int) (bool) $r->is_correct, (int) $r->sub];
            }
        }

        return ['headers' => ['pseudo_id', 'school_code', 'class_id', 'test_type', 'skill', 'unit_id', 'lesson_id', 'item', 'selected_option', 'correct', 'submission_no'], 'rows' => $rows, 'n' => count($rows)];
    }

    /** Kamus data: kolom, definisi, nilai/satuan, sumber/rumus. */
    public static function codebook(): array
    {
        $d = [
            'pseudo_id' => ['ID siswa pseudonim (HMAC-SHA256, 12 heksa); stabil dalam satu instalasi', 'teks', 'Rahasia server; tidak dapat dibalik tanpa rahasia itu'],
            'school_code' => ['Kode sekolah pseudonim', 'SCH-xxxxxx', 'HMAC kode sekolah'],
            'class_id' => ['Kode kelas pseudonim; kelas < 5 siswa dalam ekspor digeneralisasi (CLS-GEN-…)', 'CLS-xxxxxx', 'HMAC sekolah|jurusan|kelas|paralel'],
            'major' => ['Jurusan', 'teks', 'users.major'], 'grade' => ['Tingkat kelas', 'X/XI/XII', 'users.grade'],
            'research_group' => ['Kelompok penelitian', 'kontrol/perlakuan/kosong', 'Ditetapkan admin per kelas'],
            'consent' => ['Status persetujuan penelitian', 'setuju/tidak setuju/belum menjawab', 'la_consents (siswa, sekolah, atau orang tua)'],
            'listening' => ['Skor keterampilan Listening', '0–100', 'Rerata skor akhir asesmen'], 'reading' => ['Skor Reading', '0–100', 'idem'],
            'writing' => ['Skor Writing', '0–100', 'idem'], 'speaking' => ['Skor Speaking', '0–100', 'idem'],
            'pretest' => ['Skor pretest gabungan', '0–100', 'Rerata skor pretest keterampilan'], 'posttest' => ['Skor posttest gabungan', '0–100', 'Rerata skor posttest keterampilan'],
            'gain' => ['posttest − pretest', 'poin', 'Tidak sebanding bila bentuk tes tidak setara (lihat tab Hasil Belajar)'],
            'xp' => ['Poin pengalaman', 'bilangan bulat', 'users.xp'], 'lesson_progress' => ['Proporsi pelajaran selesai', '0–1', 'user_lesson_progress / total pelajaran'],
            'active_days_28' => ['Hari aktif BELAJAR dalam 28 hari terakhir (tanpa hari yang hanya berisi tes)', 'hari', 'la_daily_student.learn_events > 0'],
            'sessions_28' => ['Jumlah sesi (jeda > 30 menit memulai sesi baru)', 'sesi', 'SessionBuilder'], 'minutes_28' => ['Estimasi menit aktif', 'menit', 'SessionBuilder (estimasi, bukan waktu layar)'],
            'regularity_index' => ['Indeks keteraturan belajar', '0–100', '0,4·minggu aktif/4 + 0,3·(1−CV jeda) + 0,3·(1−Gini menit)'],
            'engagement_pattern' => ['Pola keterlibatan', 'konsisten/sporadis/menumpuk/menurun/berhenti/hanya_tes/tidak_aktif/baru', 'EngagementMetrics::classify'],
            'quiz_items' => ['Jumlah butir latihan percobaan pertama', 'butir', 'learning_events'], 'first_try_acc' => ['Akurasi percobaan pertama', '0–1', 'benar/total'],
            'hint_rate' => ['Proporsi butir yang memakai hint', '0–1', 'learning_events'], 'hint_l3_rate' => ['Proporsi butir dengan hint level 3', '0–1', 'learning_events'],
            'fast_wrong_rate' => ['Proporsi salah cepat (< 3 dtk)', '0–1', 'SrlFeatures'], 'behavior_profile' => ['Profil perilaku (klaster k-means)', 'teks', 'BehaviorClusters; deskriptif, bukan label tetap'],
            'mastered_competencies' => ['Jumlah kompetensi dikuasai (BKT reliabel, P ≥ 0,95)', 'bilangan bulat', 'la_mastery'], 'wheel_spinning_competencies' => ['Jumlah kompetensi berputar-putar', 'bilangan bulat', 'la_mastery'],
            'risk_score' => ['Skor risiko aturan', '0–100', 'RiskScorer; kosong bila belum mulai'], 'risk_level' => ['Tingkat risiko', 'tinggi/sedang/rendah/belum_mulai', 'Ambang terkalibrasi (lihat Peringatan Dini)'],
        ];
        $out = [];
        foreach (Pseudonymizer::COLUMNS as $c) {
            $out[] = [$c, $d[$c][0] ?? '', $d[$c][1] ?? '', $d[$c][2] ?? ''];
        }

        return $out;
    }

    /**
     * @param bool $identified  true = sertakan nama/email/sekolah asli (di luar skema pseudonim)
     * @param bool $includeUnconsented  true = ikutkan siswa yang belum menyetujui (wajib alasan; dicatat)
     * @return array{headers:array,rows:array,n:int,excluded:int}
     */
    public function build(bool $identified, bool $includeUnconsented): array
    {
        $today = new DateTimeImmutable('today');
        $students = $this->base->report(null, null)['students'];
        $ids = array_column($students, 'id');
        $consent = Schema::hasTable('la_consents') ? DB::table('la_consents')->pluck('accepted', 'user_id') : collect();
        $group = DB::table('users')->whereIn('id', $ids)->pluck('research_group', 'id');
        $srl = Schema::hasTable('la_srl_student') ? DB::table('la_srl_student')->get()->keyBy('user_id') : collect();
        $risk = Schema::hasTable('la_risk_scores') ? DB::table('la_risk_scores')->get()->keyBy('user_id') : collect();
        $mastered = Schema::hasTable('la_mastery') ? DB::table('la_mastery')->where('mastered', true)->selectRaw('user_id, COUNT(*) c')->groupBy('user_id')->pluck('c', 'user_id') : collect();
        $wheel = Schema::hasTable('la_mastery') ? DB::table('la_mastery')->where('wheel_spinning', true)->selectRaw('user_id, COUNT(*) c')->groupBy('user_id')->pluck('c', 'user_id') : collect();
        $days = \App\Services\LearningAnalytics\Engagement\DaysLoader::load($ids, $today->modify('-40 days')->format('Y-m-d'));
        $em = new EngagementMetrics();
        $ps = new Pseudonymizer(hash('sha256', 'la-research|' . config('app.key')));
        $rows = [];
        $excluded = 0;
        $selected = [];
        $classSize = [];
        foreach ($students as $s) {
            $accepted = $consent->has($s['id']) ? (bool) $consent[$s['id']] : null;
            if ($accepted !== true && ! $includeUnconsented) {
                $excluded++;
                continue;
            }
            $selected[] = [$s, $accepted];
            $key = $s['school'] . '|' . $s['major'] . '|' . $s['grade'] . '|' . $s['parallel'];
            $classSize[$key] = ($classSize[$key] ?? 0) + 1;
        }
        foreach ($selected as [$s, $accepted]) {
            $id = $s['id'];
            $ckey = $s['school'] . '|' . $s['major'] . '|' . $s['grade'] . '|' . $s['parallel'];
            $eng = $em->student($days[$id] ?? [], $today);
            $sr = $srl[$id] ?? null;
            $enough = $sr && $sr->enough;
            $vals = [
                'class_id' => $ps->classId($s['school'], $ckey, $classSize[$ckey], $s['grade']),
                'major' => $s['major'], 'grade' => $s['grade'], 'research_group' => $group[$id] ?? null,
                'consent' => $accepted === null ? 'belum menjawab' : ($accepted ? 'setuju' : 'tidak setuju'),
                'listening' => $s['skills']['listening'], 'reading' => $s['skills']['reading'], 'writing' => $s['skills']['writing'], 'speaking' => $s['skills']['speaking'],
                'pretest' => $s['pre'], 'posttest' => $s['post'], 'gain' => $s['gain'], 'xp' => $s['xp'], 'lesson_progress' => round($s['progress'], 3),
                'active_days_28' => $eng['active_days'], 'sessions_28' => $eng['sessions'], 'minutes_28' => $eng['minutes'], 'regularity_index' => $eng['regularity_index'],
                'engagement_pattern' => $eng['pattern'],
                'quiz_items' => $enough ? $sr->items : null, 'first_try_acc' => $enough ? (float) $sr->first_try_acc : null, 'hint_rate' => $enough ? (float) $sr->hint_rate : null,
                'hint_l3_rate' => $enough ? (float) $sr->hint_l3_rate : null, 'fast_wrong_rate' => $enough && $sr->fast_wrong_rate !== null ? (float) $sr->fast_wrong_rate : null,
                'behavior_profile' => $enough ? $sr->profile : null, 'mastered_competencies' => (int) ($mastered[$id] ?? 0), 'wheel_spinning_competencies' => (int) ($wheel[$id] ?? 0),
                'risk_score' => isset($risk[$id]) ? (float) $risk[$id]->score : null, 'risk_level' => $risk[$id]->level ?? null,
            ];
            $row = $ps->row($id, $s['school'], $vals);
            if ($identified) {
                $row = ['nama' => $s['name'], 'email' => $s['email'], 'sekolah' => $s['school'], 'paralel' => $s['parallel']] + $row;
            }
            $rows[] = array_values($row);
        }
        $this->lastPs = $ps;
        $this->lastIds = array_column(array_column($selected, 0), 'id');
        $headers = array_keys($identified ? (['nama' => 1, 'email' => 1, 'sekolah' => 1, 'paralel' => 1] + array_flip(Pseudonymizer::COLUMNS)) : array_flip(Pseudonymizer::COLUMNS));

        return ['headers' => $headers, 'rows' => $rows, 'n' => count($rows), 'excluded' => $excluded];
    }
}
