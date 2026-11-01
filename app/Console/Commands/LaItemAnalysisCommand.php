<?php

namespace App\Console\Commands;

use App\Services\LearningAnalytics\Items\ItemAnalysisService;
use Illuminate\Console\Command;

class LaItemAnalysisCommand extends Command
{
    protected $signature = 'la:item-analysis';

    protected $description = 'Learning Analytics: analisis butir (CTT) dari assessment_answers ke la_item_stats / la_form_stats.';

    public function handle(ItemAnalysisService $svc): int
    {
        $t = microtime(true);
        $r = $svc->run();
        $this->info(sprintf('Selesai: %d jawaban, %d form, %d butir (%.1f dtk).', $r['answers'], $r['forms'], $r['items'], microtime(true) - $t));

        return self::SUCCESS;
    }
}
