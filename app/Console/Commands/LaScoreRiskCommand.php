<?php

namespace App\Console\Commands;

use App\Services\LearningAnalytics\Risk\RiskService;
use Illuminate\Console\Command;

class LaScoreRiskCommand extends Command
{
    protected $signature = 'la:score-risk';

    protected $description = 'Learning Analytics: skor risiko (aturan transparan + model logit pendamping) dan riwayat harian.';

    public function handle(RiskService $svc): int
    {
        $r = $svc->run();
        $this->info(sprintf('Selesai: %d siswa (belum mulai %d; tinggi %d, sedang %d, rendah %d; ambang %s/%s%s). Model: %s.', $r['students'], $r['belum_mulai'], $r['tinggi'], $r['sedang'], $r['rendah'],
            $r['thresholds']['high'], $r['thresholds']['medium'], $r['thresholds']['calibrated'] ? ', terkalibrasi' : ', tetap',
            $r['model_ready'] ? 'tampil, AUC-CV ' . round($r['auc'], 2) : 'tidak ditampilkan' . ($r['auc'] !== null ? ' (AUC-CV ' . round($r['auc'], 2) . ')' : ' (data belum cukup)')));

        return self::SUCCESS;
    }
}
