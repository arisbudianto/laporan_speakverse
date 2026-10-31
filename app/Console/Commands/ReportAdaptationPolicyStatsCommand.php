<?php

namespace App\Console\Commands;

use App\Models\LearningEvent;
use Illuminate\Console\Command;

/**
 * Saran perbaikan Modul 4 — bahan untuk tuning ambang batas.
 *
 * Perintah ini TIDAK mengubah/menentukan angka apa pun — cuma
 * MELAPORKAN distribusi data pemakaian nyata (response_ms, jumlah
 * revisi, seberapa sering tiap level bantuan tercapai) berdampingan
 * dengan angka yang SEDANG dipakai di config/learning.php, supaya
 * Anda bisa menilai sendiri apakah ambang batas itu masih masuk akal
 * atau perlu diubah — dengan data sungguhan, bukan tebakan.
 *
 * Disarankan dijalankan berkala (mis. tiap beberapa minggu) begitu
 * cukup banyak siswa sudah memakai sistem, BUKAN sekali lalu
 * dianggap final.
 */
class ReportAdaptationPolicyStatsCommand extends Command
{
    protected $signature = 'learning:policy-stats
        {--skill=reading : reading atau vocabulary}
        {--days=30 : Ambil data dari N hari terakhir}';

    protected $description = 'Modul 4: laporkan distribusi data nyata (response_ms, revisi, dsb) untuk bahan tuning ambang batas — tidak mengubah apa pun.';

    public function handle(): int
    {
        $skill = (string) $this->option('skill');
        $days = (int) $this->option('days');

        if (! in_array($skill, ['reading', 'vocabulary'], true)) {
            $this->error("--skill harus 'reading' atau 'vocabulary', bukan '{$skill}'.");

            return self::FAILURE;
        }

        $since = now()->subDays($days);

        $this->info("=== Statistik Adaptation Policy — skill={$skill}, {$days} hari terakhir ===");
        $this->newLine();

        $this->reportResponseMs($skill, $since);
        $this->newLine();
        $this->reportRevisionCount($skill, $since);
        $this->newLine();
        $this->reportHintLevels($skill, $since);
        $this->newLine();
        $this->reportFading($skill, $since);

        return self::SUCCESS;
    }

    private function reportResponseMs(string $skill, $since): void
    {
        $values = LearningEvent::query()
            ->where('skill', $skill)
            ->whereIn('event_type', ['select', 'revise'])
            ->where('created_at', '>=', $since)
            ->whereNotNull('response_ms')
            ->orderBy('response_ms')
            ->pluck('response_ms')
            ->all();

        $this->line('--- response_ms (percobaan jawab) ---');

        if ($values === []) {
            $this->line('Belum ada data response_ms sama sekali untuk skill ini di rentang waktu ini.');

            return;
        }

        $count = count($values);
        $median = $this->percentile($values, 50);
        $p75 = $this->percentile($values, 75);
        $p90 = $this->percentile($values, 90);

        $this->line("Jumlah data: {$count}");
        $this->line('Min: ' . $values[0] . ' ms');
        $this->line("Median (p50): {$median} ms");
        $this->line("p75: {$p75} ms");
        $this->line("p90: {$p90} ms");
        $this->line('Max: ' . end($values) . ' ms');

        $configuredMultiplier = config('learning.adaptation.slow_response_multiplier', 1.5);
        $impliedThreshold = $median > 0 ? round($median * $configuredMultiplier) : 0;

        $this->newLine();
        $this->line("Config saat ini: slow_response_multiplier = {$configuredMultiplier}");
        $this->line("-> Dengan median di atas, 'respons lambat' berarti > {$impliedThreshold} ms untuk soal median.");

        if ($p75 > 0) {
            $actualRatio = round($p75 / max(1, $median), 2);
            $this->line("Rasio p75/median SEBENARNYA di data ini: {$actualRatio}x (config mengasumsikan {$configuredMultiplier}x).");

            if (abs($actualRatio - $configuredMultiplier) > 0.5) {
                $this->warn("Selisihnya cukup besar — mungkin waktu mempertimbangkan menyesuaikan slow_response_multiplier ke sekitar {$actualRatio}.");
            }
        }
    }

    private function reportRevisionCount(string $skill, $since): void
    {
        $counts = LearningEvent::query()
            ->where('skill', $skill)
            ->where('event_type', 'revise')
            ->where('created_at', '>=', $since)
            ->selectRaw('quiz_session_id, question_id, count(*) as total')
            ->groupBy('quiz_session_id', 'question_id')
            ->pluck('total')
            ->all();

        $this->line('--- Jumlah revisi per soal (yang direvisi minimal 1x) ---');

        if ($counts === []) {
            $this->line('Belum ada revisi sama sekali di rentang waktu ini.');

            return;
        }

        sort($counts);
        $median = $this->percentile($counts, 50);
        $max = end($counts);

        $this->line('Jumlah soal yang pernah direvisi: ' . count($counts));
        $this->line("Median jumlah revisi: {$median}");
        $this->line("Maksimum: {$max}");

        $configuredThreshold = config('learning.adaptation.high_revision_threshold', 2);
        $atOrAbove = count(array_filter($counts, fn ($c) => $c >= $configuredThreshold));
        $percentage = round($atOrAbove / count($counts) * 100, 1);

        $this->newLine();
        $this->line("Config saat ini: high_revision_threshold = {$configuredThreshold}");
        $this->line("-> {$atOrAbove} dari " . count($counts) . " soal yang direvisi ({$percentage}%) mencapai ambang ini.");
    }

    private function reportHintLevels(string $skill, $since): void
    {
        $this->line('--- Level hint yang diminta ---');

        $counts = LearningEvent::query()
            ->where('skill', $skill)
            ->where('event_type', 'hint_request')
            ->where('created_at', '>=', $since)
            ->whereNotNull('hint_level_shown')
            ->selectRaw('hint_level_shown, count(*) as total')
            ->groupBy('hint_level_shown')
            ->pluck('total', 'hint_level_shown');

        if ($counts->isEmpty()) {
            $this->line('Belum ada permintaan hint sama sekali di rentang waktu ini.');

            return;
        }

        foreach ([1, 2, 3] as $level) {
            $this->line("Level {$level}: " . ($counts[$level] ?? 0) . 'x diminta');
        }
    }

    private function reportFading(string $skill, $since): void
    {
        $this->line('--- Fading (siswa konsisten bagus, bantuan dibatasi) ---');

        $events = LearningEvent::query()
            ->where('skill', $skill)
            ->where('event_type', 'hint_request')
            ->where('created_at', '>=', $since)
            ->whereNotNull('payload')
            ->get();

        $fadingCount = $events->filter(function ($event) {
            $reasons = $event->payload['reasons'] ?? [];

            return is_array($reasons) && in_array('fading_cap', $reasons, true);
        })->count();

        $this->line("Jumlah keputusan hint yang dibatasi karena fading: {$fadingCount}");

        if ($fadingCount === 0) {
            $this->line('(Wajar kalau masih 0 — fading baru relevan setelah siswa punya cukup riwayat jawaban benar beruntun.)');
        }
    }

    private function percentile(array $sortedValues, int $percentile): int
    {
        $count = count($sortedValues);

        if ($count === 0) {
            return 0;
        }

        $index = (int) ceil($percentile / 100 * $count) - 1;
        $index = max(0, min($count - 1, $index));

        return (int) $sortedValues[$index];
    }
}
