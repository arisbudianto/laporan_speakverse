<?php

namespace App\Services\LearningAnalytics\Research;

/**
 * Pseudonimisasi ekspor riset: ID = HMAC-SHA256(user_id, rahasia) dipotong 12 heksa; kode sekolah = 'SCH-' + 6 heksa.
 * Rahasia berasal dari app key aplikasi (tidak ikut diekspor), sehingga:
 *  - ID stabil antar ekspor (memungkinkan analisis longitudinal), tetapi tidak dapat dibalik tanpa kunci;
 *  - nama, email, kelas paralel, dan ID asli TIDAK pernah ada di baris ekspor.
 * Catatan: pseudonim bukan anonim — gabungan sekolah+jurusan+tingkat pada sekolah kecil dapat mengidentifikasi; baca bagian Etika.
 */
final class Pseudonymizer
{
    public function __construct(private string $secret)
    {
    }

    public function userId(int $id): string
    {
        return substr(hash_hmac('sha256', 'user:' . $id, $this->secret), 0, 12);
    }

    public function school(?string $school): string
    {
        return 'SCH-' . substr(hash_hmac('sha256', 'school:' . ($school ?? ''), $this->secret), 0, 6);
    }

    public const COLUMNS = ['pseudo_id', 'school_code', 'class_id', 'major', 'grade', 'research_group', 'consent',
        'listening', 'reading', 'writing', 'speaking', 'pretest', 'posttest', 'gain', 'xp', 'lesson_progress',
        'active_days_28', 'sessions_28', 'minutes_28', 'regularity_index', 'engagement_pattern',
        'quiz_items', 'first_try_acc', 'hint_rate', 'hint_l3_rate', 'fast_wrong_rate', 'behavior_profile',
        'mastered_competencies', 'wheel_spinning_competencies', 'risk_score', 'risk_level'];

    /**
     * Kode kelas pseudonim. Kelas dengan < $minSize siswa di dalam ekspor digeneralisasi menjadi kode tingkat sekolah-tingkat kelas
     * agar kelompok kecil tidak mudah dikenali kembali (k-anonimitas sederhana).
     */
    public function classId(?string $school, string $classKey, int $size, ?string $grade, int $minSize = 5): string
    {
        if ($size >= $minSize) {
            return 'CLS-' . substr(hash_hmac('sha256', 'class:' . $classKey, $this->secret), 0, 6);
        }

        return 'CLS-GEN-' . substr(hash_hmac('sha256', 'classgen:' . ($school ?? '') . '|' . ($grade ?? ''), $this->secret), 0, 4);
    }

    /** @param array<string,mixed> $r nilai bernama sesuai COLUMNS selain pseudo_id & school_code */
    public function row(int $userId, ?string $school, array $r): array
    {
        $row = ['pseudo_id' => $this->userId($userId), 'school_code' => $this->school($school)];
        $row['class_id'] = $r['class_id'] ?? null;
        foreach (self::COLUMNS as $c) {
            if (! isset($row[$c])) {
                $row[$c] = $r[$c] ?? null;
            }
        }

        return $row;
    }
}
