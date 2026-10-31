<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

/**
 * Saran perbaikan Modul 6 — notifikasi otomatis ke guru.
 *
 * Dikirim ke guru yang mengampu kelas seorang siswa, begitu siswa
 * itu masuk daftar "Perlu Intervensi" (lihat
 * ClassResultsController & command NotifyTeachersOfInterventionCommand).
 *
 * Cuma channel 'database' — TIDAK memakai email, karena konfigurasi
 * SMTP di server ini belum tentu ada/valid, dan notifikasi di dalam
 * aplikasi sudah cukup untuk kebutuhan ini tanpa risiko gagal kirim
 * yang bisa menghentikan proses command.
 */
class StudentNeedsInterventionNotification extends Notification
{
    public function __construct(
        public int $studentId,
        public string $studentName,
        public string $school,
        public string $major,
        public string $grade,
        public string $parallel,
        public int $inferentialWrong,
        public int $hintL3Count,
    ) {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $reasons = [];

        if ($this->inferentialWrong > 0) {
            $reasons[] = "{$this->inferentialWrong}x salah soal inferensial";
        }

        if ($this->hintL3Count > 0) {
            $reasons[] = "{$this->hintL3Count}x minta hint level 3";
        }

        $reasonText = implode(', ', $reasons);

        return [
            'student_id' => $this->studentId,
            'student_name' => $this->studentName,
            'school' => $this->school,
            'major' => $this->major,
            'grade' => $this->grade,
            'parallel' => $this->parallel,
            'inferential_wrong' => $this->inferentialWrong,
            'hint_l3_count' => $this->hintL3Count,
            'message' => "{$this->studentName} ({$this->grade}{$this->parallel}) perlu perhatian: {$reasonText}.",
        ];
    }
}
