<?php

namespace App\Console\Commands;

use App\Models\LearningEvent;
use App\Models\TeacherClassAssignment;
use App\Models\User;
use App\Notifications\StudentNeedsInterventionNotification;
use Illuminate\Console\Command;

/**
 * Saran perbaikan Modul 6 — notifikasi otomatis ke guru.
 *
 * Mencari SEMUA siswa yang masuk kriteria "Perlu Intervensi" (sama
 * persis dengan logika di ClassResultsController::buildReport()),
 * lalu mengirim notifikasi database ke guru yang mengampu kelas
 * siswa itu (lewat TeacherClassAssignment).
 *
 * ANTI-SPAM: satu guru tidak akan dinotifikasi ulang untuk SISWA
 * YANG SAMA dalam --cooldown-days hari terakhir, walau command ini
 * dijalankan berkali-kali (mis. dijadwalkan harian).
 *
 * Dijadwalkan otomatis lewat routes/console.php (lihat komentar di
 * sana) — BEDA dengan learning:archive-events yang sengaja manual,
 * karena mengirim notifikasi tidak berisiko menghapus/merusak data
 * apa pun.
 */
class NotifyTeachersOfInterventionCommand extends Command
{
    protected $signature = 'learning:notify-teachers
        {--cooldown-days=7 : Jangan kirim notifikasi ulang untuk siswa yang sama dalam N hari terakhir}';

    protected $description = 'Modul 6: kirim notifikasi ke guru untuk siswa yang masuk daftar Perlu Intervensi.';

    public function handle(): int
    {
        $inferentialThreshold = (int) config('learning.intervention.inferential_wrong_threshold', 2);
        $hintL3Threshold = (int) config('learning.intervention.hint_l3_threshold', 2);
        $cooldownDays = (int) $this->option('cooldown-days');

        $inferentialWrongByStudent = LearningEvent::query()
            ->whereIn('skill', ['reading', 'vocabulary', 'listening'])
            ->whereIn('event_type', ['select', 'revise'])
            ->where('attempt_no', 1)
            ->where('is_correct', false)
            ->where('error_code', 'inferential')
            ->selectRaw('user_id, count(*) as total')
            ->groupBy('user_id')
            ->pluck('total', 'user_id');

        $hintL3ByStudent = LearningEvent::query()
            ->where('event_type', 'hint_request')
            ->where('hint_level_shown', 3)
            ->selectRaw('user_id, count(*) as total')
            ->groupBy('user_id')
            ->pluck('total', 'user_id');

        $candidateUserIds = $inferentialWrongByStudent->keys()
            ->merge($hintL3ByStudent->keys())
            ->unique();

        if ($candidateUserIds->isEmpty()) {
            $this->info('Tidak ada siswa yang memenuhi ambang batas intervensi saat ini.');

            return self::SUCCESS;
        }

        $students = User::query()
            ->whereIn('id', $candidateUserIds)
            ->where('role', 'student')
            ->get();

        $notifiedCount = 0;
        $skippedCooldown = 0;
        $skippedNoTeacher = 0;
        $skippedNotQualified = 0;

        foreach ($students as $student) {
            $inferentialWrong = (int) ($inferentialWrongByStudent[$student->id] ?? 0);
            $hintL3Count = (int) ($hintL3ByStudent[$student->id] ?? 0);

            $needsIntervention = $inferentialWrong > $inferentialThreshold
                || $hintL3Count >= $hintL3Threshold;

            if (! $needsIntervention) {
                $skippedNotQualified++;

                continue;
            }

            if (
                blank($student->school) || blank($student->major)
                || blank($student->grade) || blank($student->parallel)
            ) {
                $skippedNoTeacher++;

                continue;
            }

            $teachers = TeacherClassAssignment::query()
                ->where('school', $student->school)
                ->where('major', $student->major)
                ->where('grade', $student->grade)
                ->where('parallel', $student->parallel)
                ->with('teacher')
                ->get()
                ->pluck('teacher')
                ->filter();

            if ($teachers->isEmpty()) {
                $skippedNoTeacher++;

                continue;
            }

            foreach ($teachers as $teacher) {
                $recentlyNotified = $teacher->notifications()
                    ->where('type', StudentNeedsInterventionNotification::class)
                    ->where('created_at', '>=', now()->subDays($cooldownDays))
                    ->get()
                    ->contains(fn ($notification) => ($notification->data['student_id'] ?? null) === $student->id);

                if ($recentlyNotified) {
                    $skippedCooldown++;

                    continue;
                }

                $teacher->notify(new StudentNeedsInterventionNotification(
                    $student->id,
                    $student->name,
                    (string) $student->school,
                    (string) $student->major,
                    (string) $student->grade,
                    (string) $student->parallel,
                    $inferentialWrong,
                    $hintL3Count
                ));

                $notifiedCount++;
            }
        }

        $this->info(
            "Selesai: {$notifiedCount} notifikasi terkirim, "
            . "{$skippedCooldown} dilewati (masih cooldown), "
            . "{$skippedNoTeacher} dilewati (tidak ada guru terdaftar di kelasnya atau data kelas kosong)."
        );

        return self::SUCCESS;
    }
}
