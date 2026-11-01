<?php

namespace App\Console\Commands;

use App\Services\LearningAnalytics\Mastery\LearningModelsService;
use Illuminate\Console\Command;

class LaFitModelsCommand extends Command
{
    protected $signature = 'la:fit-models';

    protected $description = 'Learning Analytics: cocokkan BKT (penguasaan), hitung fitur SRL dan klaster profil perilaku.';

    public function handle(LearningModelsService $svc): int
    {
        $t = microtime(true);
        $r = $svc->run();
        $this->info(sprintf('Selesai: %d event, %d kompetensi, %d status penguasaan, %d siswa SRL, %d diklaster (k=%s) dalam %.1f dtk.',
            $r['events'], $r['competencies'], $r['states'], $r['srl_students'], $r['clustered'], $r['k'] ?? '–', microtime(true) - $t));

        return self::SUCCESS;
    }
}
