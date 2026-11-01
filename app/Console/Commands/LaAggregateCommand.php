<?php

namespace App\Console\Commands;

use App\Services\LearningAnalytics\Engagement\ActivityAggregateService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class LaAggregateCommand extends Command
{
    protected $signature = 'la:aggregate
        {--since= : Tanggal mulai (Y-m-d). Default: 60 hari lalu}
        {--user= : Hanya satu user_id (untuk uji)}';

    protected $description = 'Learning Analytics: hitung agregat harian aktivitas siswa ke la_daily_student (idempoten).';

    public function handle(ActivityAggregateService $svc): int
    {
        $since = $this->option('since') ? Carbon::parse($this->option('since'))->startOfDay() : now()->subDays(60)->startOfDay();
        $t = microtime(true);
        $r = $svc->run($since, $this->option('user') ? (int) $this->option('user') : null);
        $this->info(sprintf('Selesai: %d siswa, %d baris harian (sejak %s) dalam %.1f dtk.', $r['students'], $r['rows'], $since->format('Y-m-d'), microtime(true) - $t));

        return self::SUCCESS;
    }
}
