<?php

namespace App\Services\Learning;

use App\Models\AssessmentSubmission;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Saran perbaikan Modul 3 — LLM Scaffolding Engine.
 *
 * Mengalirkan cefr_band (yang sebelumnya SELALU null di setiap
 * pemanggilan ScaffoldingEngine::generate(), lihat StudentReadingController
 * dan VocabularyPretestController sebelum perbaikan ini) supaya bahasa
 * hint benar-benar menyesuaikan level siswa, bukan cuma janji di
 * proposal yang tidak pernah tersambung ke kode.
 *
 * CATATAN JUJUR: ini BUKAN asesmen CEFR resmi/tervalidasi — cuma
 * proksi sederhana dari skor rata-rata Pre-Test (assessment_submissions
 * type='pretest', lintas skill) yang sudah ada di sistem, dipetakan ke
 * rentang band lewat ambang batas yang bisa diatur di
 * config('learning.cefr.thresholds'). Cukup untuk menyesuaikan
 * kompleksitas bahasa hint (A1/A2 dapat glosarium Indonesia singkat,
 * lihat ScaffoldingEngine), TIDAK untuk pelaporan CEFR formal ke pihak
 * mana pun.
 */
class CefrBandResolver
{
    /**
     * @return string|null 'A1'|'A2'|'B1'|'B2'|'C1', atau null kalau
     *         siswa belum punya submission Pre-Test sama sekali
     *         (mis. baru daftar, belum mengerjakan apa-apa).
     */
    public function resolveForStudent(User $student): ?string
    {
        try {
            $avgScore = AssessmentSubmission::query()
                ->where('user_id', $student->id)
                ->where('type', 'pretest')
                ->avg('final_score');

            if ($avgScore === null) {
                return null;
            }

            return $this->scoreToBand((float) $avgScore);
        } catch (Throwable $exception) {
            Log::warning('CefrBandResolver: failed to resolve band.', [
                'student_id' => $student->id,
                'exception' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    private function scoreToBand(float $score): string
    {
        $thresholds = config('learning.cefr.thresholds', [
            'A1' => 0,
            'A2' => 40,
            'B1' => 60,
            'B2' => 75,
            'C1' => 90,
        ]);

        // Diurutkan dari ambang TERTINGGI ke terendah secara
        // terprogram (bukan bergantung urutan penulisan di config),
        // supaya pemetaannya benar walau config ditulis urutan apa
        // pun.
        arsort($thresholds);

        foreach ($thresholds as $band => $minScore) {
            if ($score >= $minScore) {
                return (string) $band;
            }
        }

        // Fallback kalau config kosong total — seharusnya tidak
        // pernah terjadi karena default di atas selalu ada 'A1' => 0.
        return 'A1';
    }
}
