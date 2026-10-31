<?php

namespace App\Services\Learning;

use App\Models\LearningEvent;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Modul 2 — Diagnostic Engine.
 *
 * Mengklasifikasikan jawaban SALAH ke salah satu dari 4 kode error
 * (taksonomi dari proposal), bukan sekadar benar/salah.
 *
 * SENGAJA RULE-BASED SAJA di tahap ini (Sprint B, sesuai urutan
 * sprint di dokumen rencana: "M2 classifier rule-based ... tanpa
 * LLM. Sudah bisa demo scaffolding deterministik."). Fallback LLM
 * untuk soal yang belum dianotasi BELUM diaktifkan — lihat catatan
 * di classify().
 */
class DiagnosticEngine
{
    /**
     * Taksonomi error tertutup (closed enum) dari proposal:
     * - lexical: salah makna kata, collocation, word form
     * - inferential: gagal menyimpulkan dari teks
     * - syntactic: salah rujukan struktur kalimat/referensi gramatikal
     * - context_misconception: membawa pengetahuan luar yang
     *   bertentangan dengan teks
     */
    public const ERROR_CODES = [
        'lexical',
        'inferential',
        'syntactic',
        'context_misconception',
    ];

    /**
     * Mengklasifikasikan satu jawaban.
     *
     * @param object $question Model dengan atribut `correct_answer`
     *                          dan `error_if_wrong` (ReadingQuestion
     *                          atau VocabularyPretest — keduanya
     *                          punya struktur yang sama untuk ini).
     * @param string $selectedAnswer Opsi yang dipilih siswa (A–E).
     *
     * @return array{error_code: ?string, error_confidence: ?float, classifier: ?string}
     */
    public function classify(object $question, string $selectedAnswer): array
    {
        try {
            if ($selectedAnswer === $question->correct_answer) {
                // Jawaban benar -> tidak ada error untuk diklasifikasi.
                return $this->result(null, null, null);
            }

            $map = $question->error_if_wrong ?? null;

            if (is_array($map) && isset($map[$selectedAnswer])) {
                $code = $map[$selectedAnswer];

                if (in_array($code, self::ERROR_CODES, true)) {
                    // Map eksplisit dari admin = pasti benar, confidence 1.0.
                    return $this->result($code, 1.0, 'rule');
                }

                // Map ada tapi isinya kode yang TIDAK valid (typo admin,
                // dsb). Jangan simpan kode sembarangan — lebih baik
                // "belum terklasifikasi" daripada data yang salah.
                Log::warning('DiagnosticEngine: invalid error_code in error_if_wrong map.', [
                    'question_id' => $question->id ?? null,
                    'selected_answer' => $selectedAnswer,
                    'invalid_code' => $code,
                ]);

                return $this->result(null, null, null);
            }

            /*
            |--------------------------------------------------------------------------
            | Belum ada map (soal belum dianotasi admin)
            |--------------------------------------------------------------------------
            |
            | Sesuai rencana sprint (Sprint B = rule-based, TANPA
            | LLM), fallback LLM SENGAJA belum diaktifkan di sini.
            | Jawaban salah pada soal yang belum dianotasi tetap
            | tercatat is_correct=false di learning_events, tapi
            | error_code-nya null ("belum terklasifikasi") — bukan
            | ditebak asal oleh rule yang tidak punya dasar.
            |
            | Kerangka untuk mengaktifkan fallback LLM di sprint
            | berikutnya: lihat classifyWithLlmStub() di bawah.
            */
            return $this->result(null, null, null);
        } catch (Throwable $exception) {
            // Sama seperti InteractionLogger: kegagalan di sini
            // TIDAK BOLEH menggagalkan submit kuis siswa.
            Log::warning('DiagnosticEngine: classify() failed.', [
                'question_id' => $question->id ?? null,
                'exception' => $exception->getMessage(),
            ]);

            return $this->result(null, null, null);
        }
    }

    private function result(?string $code, ?float $confidence, ?string $classifier): array
    {
        return [
            'error_code' => $code,
            'error_confidence' => $confidence,
            'classifier' => $classifier,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | KERANGKA Sprint berikutnya — BELUM AKTIF, belum dipanggil dari
    | mana pun. Ditinggal sebagai catatan implementasi supaya
    | strukturnya sudah jelas saat mau diaktifkan:
    |--------------------------------------------------------------------------
    |
    | private function classifyWithLlm(object $question, string $selectedAnswer): array
    | {
    |     // 1. Kirim ke Dinoiki/GPT (lihat pola di
    |     //    StudentWritingController::scoreWritingWithAi) dengan
    |     //    system prompt yang MEWAJIBKAN output salah satu dari
    |     //    self::ERROR_CODES persis (tanpa penjelasan tambahan).
    |     // 2. Parse JSON, ambil field error_code.
    |     // 3. WAJIB: in_array($code, self::ERROR_CODES, true) —
    |     //    tolak (return unclassified) kalau LLM mengembalikan
    |     //    apa pun di luar 4 kode itu, walau kelihatannya masuk
    |     //    akal.
    |     // 4. classifier = 'llm', error_confidence = sesuatu < 1.0
    |     //    (mis. dari confidence yang diminta ke model, atau nilai
    |     //    tetap 0.6 kalau modelnya tidak bisa mengembalikan
    |     //    confidence).
    | }
    |
    */

    /**
     * Frekuensi error_code untuk sekumpulan siswa — dipakai laporan
     * guru (DoD Modul 2: "laporan bisa menghitung frekuensi
     * error_code per kelas").
     *
     * @param int[] $userIds
     * @return array<string, int> mis. ['inferential' => 12, 'lexical' => 5, ...]
     *                             Hanya kode dengan count > 0 yang muncul.
     */
    public function errorFrequency(array $userIds, string $skill = 'reading', ?int $lessonId = null): array
    {
        if ($userIds === []) {
            return [];
        }

        try {
            $rows = LearningEvent::query()
                ->whereIn('user_id', $userIds)
                ->where('skill', $skill)
                ->when($lessonId !== null, fn ($query) => $query->where('lesson_id', $lessonId))
                ->whereNotNull('error_code')
                ->selectRaw('error_code, count(*) as total')
                ->groupBy('error_code')
                ->pluck('total', 'error_code');

            return $rows->toArray();
        } catch (Throwable $exception) {
            Log::warning('DiagnosticEngine: errorFrequency() failed.', [
                'exception' => $exception->getMessage(),
            ]);

            return [];
        }
    }
}
