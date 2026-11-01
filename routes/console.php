<?php

use App\Console\Commands\NotifyTeachersOfInterventionCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Saran perbaikan Modul 6 — notifikasi otomatis ke guru
|--------------------------------------------------------------------------
|
| BEDA dengan learning:archive-events (sengaja manual karena
| berisiko menghapus data), command ini AMAN dijadwalkan otomatis —
| cuma mengirim notifikasi, tidak mengubah/menghapus apa pun, dan
| sudah punya cooldown anti-spam bawaan (lihat command-nya).
|
| PENTING — supaya baris di bawah ini benar-benar jalan, server
| perlu satu cron job (biasanya diatur lewat cPanel > Cron Jobs)
| yang menjalankan ini setiap menit:
|
|   * * * * * cd /path/ke/laravel && php artisan schedule:run >> /dev/null 2>&1
|
| Tanpa cron itu, jadwal di bawah TIDAK akan pernah berjalan sendiri
| — Laravel tidak punya "daemon" bawaan, hanya menunggu dipanggil.
*/
Schedule::command(NotifyTeachersOfInterventionCommand::class)->daily();

// Learning Analytics: agregat harian aktivitas siswa (dini hari).
Schedule::command('la:aggregate')->dailyAt('02:30')->withoutOverlapping();

// Learning Analytics: analisis butir (mingguan, Minggu dini hari).
Schedule::command('la:item-analysis')->weeklyOn(0, '03:15')->withoutOverlapping();

// Learning Analytics: BKT, fitur SRL, klaster profil (harian, setelah agregat).
Schedule::command('la:fit-models')->dailyAt('03:45')->withoutOverlapping();

// Learning Analytics: skor peringatan dini (setelah model penguasaan).
Schedule::command('la:score-risk')->dailyAt('04:00')->withoutOverlapping();
