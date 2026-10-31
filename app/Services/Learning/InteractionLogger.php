<?php

namespace App\Services\Learning;

use App\Models\LearningEvent;
use App\Models\Lesson;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Modul 1 — Interaction Logger.
 *
 * Satu-satunya pintu masuk untuk menulis ke tabel learning_events.
 * Tidak mengubah logika skor apa pun (lihat StudentReadingController)
 * — modul ini murni observabilitas.
 *
 * PRINSIP KEANDALAN: logging TIDAK BOLEH pernah menggagalkan alur
 * utama siswa (buka kuis / jawab / kirim). Setiap method di sini
 * menangkap error sendiri, mencatat Log::warning, dan mengembalikan
 * null alih-alih melempar exception — supaya controller pemanggil
 * tidak perlu membungkus setiap panggilan dengan try/catch.
 *
 * CATATAN JUJUR soal response_ms: pada tahap ini (belum ada endpoint
 * live "Check"/"hint" — itu baru datang di Modul 5), response_ms
 * dihitung dari timestamp yang dikirim KLIEN (browser), bukan server.
 * Ini rentan dimanipulasi siswa yang mau ("mempercepat" jam
 * browsernya) dan bisa meleset kalau tab di-minimize lama. Untuk
 * kebutuhan Modul 1 (observabilitas, bukan penilaian) ini cukup;
 * kalau nanti dipakai sebagai sinyal keras di Modul 4 (Adaptation
 * Policy), perlu diperketat (mis. dibatasi rentang wajar, atau
 * dipindah ke pengukuran server-side per soal via endpoint live).
 */
class InteractionLogger
{
    /**
     * Membuka sesi kuis baru dan mencatat event 'start'.
     *
     * @return string|null UUID sesi, atau null jika logging gagal
     *                      (lihat catatan keandalan di atas class).
     */
    public function startSession(int $userId, ?Lesson $lesson, string $skill): ?string
    {
        $sessionId = (string) Str::uuid();

        $logged = $this->write([
            'user_id' => $userId,
            'quiz_session_id' => $sessionId,
            'lesson_id' => $lesson?->id,
            'question_id' => null,
            'skill' => $skill,
            'event_type' => 'start',
            'attempt_no' => 1,
        ]);

        return $logged ? $sessionId : null;
    }

    /**
     * Mencatat siswa memilih/mengganti jawaban untuk satu soal.
     *
     * Menentukan sendiri apakah ini pilihan pertama ('select') atau
     * revisi ('revise') dengan mengecek riwayat event pada
     * (sessionId, questionId) yang sama — pemanggil (controller/JS)
     * tidak perlu (dan tidak boleh) menentukan ini sendiri, supaya
     * definisi "revisi" konsisten dan bisa diquery ulang.
     */
    public function logSelection(
        string $sessionId,
        int $userId,
        ?Lesson $lesson,
        string $skill,
        int $questionId,
        ?string $selectedAnswer,
        ?bool $isCorrect,
        ?int $responseMs,
        array $payload = []
    ): void {
        $priorAttempts = $this->safeCount(function () use ($sessionId, $questionId) {
            return LearningEvent::query()
                ->where('quiz_session_id', $sessionId)
                ->where('question_id', $questionId)
                ->whereIn('event_type', ['select', 'revise'])
                ->count();
        });

        $this->write([
            'user_id' => $userId,
            'quiz_session_id' => $sessionId,
            'lesson_id' => $lesson?->id,
            'question_id' => $questionId,
            'skill' => $skill,
            'event_type' => $priorAttempts > 0 ? 'revise' : 'select',
            'selected_answer' => $selectedAnswer,
            'is_correct' => $isCorrect,
            'response_ms' => $this->sanitizeResponseMs($responseMs),
            'attempt_no' => $priorAttempts + 1,
            'payload' => $payload === [] ? null : $payload,
        ]);
    }

    /**
     * Mencatat siswa meminta hint untuk satu soal (dipakai mulai
     * Modul 3/5; disiapkan sekarang supaya kontrak datanya stabil).
     */
    public function logHintRequest(
        string $sessionId,
        int $userId,
        ?Lesson $lesson,
        string $skill,
        int $questionId,
        int $hintLevel,
        array $payload = []
    ): void {
        $this->write([
            'user_id' => $userId,
            'quiz_session_id' => $sessionId,
            'lesson_id' => $lesson?->id,
            'question_id' => $questionId,
            'skill' => $skill,
            'event_type' => 'hint_request',
            'hint_level_shown' => $hintLevel,
            'payload' => $payload === [] ? null : $payload,
        ]);
    }

    /**
     * Mencatat kuis dikirim (akhir sesi).
     */
    public function logSubmit(
        string $sessionId,
        int $userId,
        ?Lesson $lesson,
        string $skill,
        array $payload = []
    ): void {
        $this->write([
            'user_id' => $userId,
            'quiz_session_id' => $sessionId,
            'lesson_id' => $lesson?->id,
            'question_id' => null,
            'skill' => $skill,
            'event_type' => 'submit',
            'payload' => $payload === [] ? null : $payload,
        ]);
    }

    /**
     * Modul 2 — Diagnostic Engine: melengkapi attempt (baris
     * select/revise TERAKHIR untuk satu soal dalam satu sesi) dengan
     * hasil penilaian: benar/salah, dan kalau salah, kode error dari
     * DiagnosticEngine::classify().
     *
     * Ini SATU-SATUNYA tempat yang boleh meng-UPDATE baris
     * learning_events setelah insert (lihat docblock LearningEvent).
     * Dipanggil dari controller (StudentReadingController,
     * VocabularyPretestController) tepat setelah skor final
     * dihitung, sama sekali tidak mengubah logika skor itu sendiri.
     */
    public function enrichAttempt(
        string $sessionId,
        int $questionId,
        ?bool $isCorrect,
        ?string $errorCode,
        ?float $errorConfidence,
        ?string $classifier
    ): void {
        try {
            $attempt = LearningEvent::query()
                ->where('quiz_session_id', $sessionId)
                ->where('question_id', $questionId)
                ->whereIn('event_type', ['select', 'revise'])
                ->latest('id')
                ->first();

            if (! $attempt) {
                // Wajar terjadi kalau siswa tidak pernah benar-benar
                // memilih jawaban untuk soal ini (mis. events dari
                // klien gagal terkirim) — tidak ada yang perlu
                // di-enrich, bukan error.
                return;
            }

            $attempt->update([
                'is_correct' => $isCorrect,
                'error_code' => $errorCode,
                'error_confidence' => $errorConfidence,
                'classifier' => $classifier,
            ]);
        } catch (Throwable $exception) {
            Log::warning('InteractionLogger: failed to enrich attempt.', [
                'quiz_session_id' => $sessionId,
                'question_id' => $questionId,
                'exception' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * Jumlah revisi (percobaan kedua dst.) untuk satu soal dalam satu
     * sesi. Dihitung dari data, bukan ditebak di UI — sesuai prinsip
     * Modul 1.
     */
    public function revisionCount(string $sessionId, int $questionId): int
    {
        return $this->safeCount(function () use ($sessionId, $questionId) {
            return LearningEvent::query()
                ->where('quiz_session_id', $sessionId)
                ->where('question_id', $questionId)
                ->where('event_type', 'revise')
                ->count();
        });
    }

    /**
     * Median response_ms untuk satu soal (semua siswa, semua sesi).
     * lessonId null berarti soal tanpa lesson (mis. Vocabulary
     * Pretest). Dihitung di PHP (bukan SQL) supaya sama persis di
     * MySQL maupun SQLite (dipakai saat testing) tanpa bergantung
     * fungsi percentile khusus versi database.
     */
    public function medianResponseMsForQuestion(?int $lessonId, string $skill, int $questionId): ?float
    {
        try {
            $values = LearningEvent::query()
                ->when(
                    $lessonId !== null,
                    fn ($query) => $query->where('lesson_id', $lessonId),
                    fn ($query) => $query->whereNull('lesson_id')
                )
                ->where('skill', $skill)
                ->where('question_id', $questionId)
                ->whereIn('event_type', ['select', 'revise'])
                ->whereNotNull('response_ms')
                ->orderBy('response_ms')
                ->pluck('response_ms')
                ->all();

            $count = count($values);
            if ($count === 0) {
                return null;
            }

            $middle = intdiv($count, 2);
            if ($count % 2 === 1) {
                return (float) $values[$middle];
            }

            return ($values[$middle - 1] + $values[$middle]) / 2;
        } catch (Throwable $exception) {
            Log::warning('InteractionLogger: failed to compute median response_ms.', [
                'lesson_id' => $lessonId,
                'skill' => $skill,
                'question_id' => $questionId,
                'exception' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Saran perbaikan Modul 1 — response_ms dari jam SERVER.
     *
     * Dipanggil dari klien begitu satu soal tampil di layar (lihat
     * endpoint markShown() di controller). Server mencatat waktunya
     * SENDIRI (bukan menerima angka dari klien) — siswa tidak bisa
     * lagi mengirim response_ms palsu langsung, cuma bisa memilih
     * KAPAN ping ini dikirim (yang tetap dibatasi wajar oleh
     * sanitizeResponseMs() di resolveResponseMs() di bawah).
     *
     * Disimpan di cache (bukan learning_events) karena ini murni
     * penanda waktu sementara, bukan event yang perlu dianalisis.
     */
    public function markQuestionShown(string $sessionId, int $questionId): void
    {
        try {
            Cache::put(
                $this->shownCacheKey($sessionId, $questionId),
                now()->getTimestampMs(),
                now()->addHours(4)
            );
        } catch (Throwable $exception) {
            Log::warning('InteractionLogger: markQuestionShown failed.', [
                'quiz_session_id' => $sessionId,
                'question_id' => $questionId,
                'exception' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * Dipanggil dari check() menggantikan "percaya begitu saja pada
     * response_ms dari body request". Kalau markQuestionShown() sudah
     * pernah dipanggil untuk soal ini (kasus normal), response_ms
     * dihitung dari SELISIH JAM SERVER — otoritatif, tidak bisa
     * dipalsukan langsung oleh klien.
     *
     * $clientResponseMs tetap dipakai sebagai FALLBACK kalau
     * penanda server tidak ada (mis. ping markShown gagal terkirim,
     * koneksi lambat, dsb) — lebih baik data kasar daripada
     * response_ms kosong sama sekali.
     */
    public function resolveResponseMs(string $sessionId, int $questionId, ?int $clientResponseMs): ?int
    {
        try {
            $shownAtMs = Cache::get($this->shownCacheKey($sessionId, $questionId));

            if ($shownAtMs !== null) {
                $serverResponseMs = now()->getTimestampMs() - (int) $shownAtMs;

                return $this->sanitizeResponseMs(max(0, $serverResponseMs));
            }
        } catch (Throwable $exception) {
            Log::warning('InteractionLogger: resolveResponseMs failed, falling back to client value.', [
                'quiz_session_id' => $sessionId,
                'question_id' => $questionId,
                'exception' => $exception->getMessage(),
            ]);
        }

        return $this->sanitizeResponseMs($clientResponseMs);
    }

    private function shownCacheKey(string $sessionId, int $questionId): string
    {
        return "question_shown:{$sessionId}:{$questionId}";
    }

    /**
     * Saran perbaikan Modul 1 — event 'abandon'.
     *
     * Dipanggil lewat navigator.sendBeacon() saat siswa menutup
     * tab/pindah halaman DI TENGAH kuis (belum sempat Finish). Lihat
     * endpoint abandon() di controller dan pemasangan
     * 'pagehide' listener di JS.
     */
    public function logAbandon(
        string $sessionId,
        int $userId,
        ?Lesson $lesson,
        string $skill,
        array $payload = []
    ): void {
        $this->write([
            'quiz_session_id' => $sessionId,
            'user_id' => $userId,
            'lesson_id' => $lesson?->id,
            'question_id' => null,
            'skill' => $skill,
            'event_type' => 'abandon',
            'payload' => $payload === [] ? null : $payload,
        ]);
    }

    /**
     * response_ms datang dari klien (lihat catatan class di atas).
     * Dibatasi ke rentang wajar (0 – 30 menit) supaya nilai yang
     * jelas-jelas rusak (negatif, atau tab dibiarkan terbuka
     * berjam-jam) tidak merusak perhitungan median.
     */
    private function sanitizeResponseMs(?int $responseMs): ?int
    {
        if ($responseMs === null) {
            return null;
        }

        if ($responseMs < 0 || $responseMs > 30 * 60 * 1000) {
            return null;
        }

        return $responseMs;
    }

    private function write(array $attributes): bool
    {
        try {
            LearningEvent::query()->create($attributes);

            return true;
        } catch (Throwable $exception) {
            // Sengaja tidak dilempar ulang: kegagalan logging tidak
            // boleh menggagalkan siswa membuka/mengerjakan/mengirim
            // kuis. Lihat catatan keandalan di atas class.
            Log::warning('InteractionLogger: failed to write learning event.', [
                'attributes' => $attributes,
                'exception' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    private function safeCount(callable $query): int
    {
        try {
            return (int) $query();
        } catch (Throwable $exception) {
            Log::warning('InteractionLogger: failed to count prior events.', [
                'exception' => $exception->getMessage(),
            ]);

            return 0;
        }
    }
}
