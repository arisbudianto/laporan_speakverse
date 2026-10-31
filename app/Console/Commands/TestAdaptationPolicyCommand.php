<?php

namespace App\Console\Commands;

use App\Services\Learning\AdaptationPolicy;
use Illuminate\Console\Command;

/**
 * Modul 4 — Adaptation Policy.
 *
 * Menjalankan skenario uji dari dokumen rencana modul: "diberikan
 * skenario uji (salah-salah-benar), sistem memilih L1 lalu L2 lalu
 * fade; versi kebijakan terekam."
 *
 * Perintah ini memanggil AdaptationPolicy::decideHintLevel() langsung
 * (fungsi murni, TANPA menyentuh database), jadi bisa dijalankan
 * kapan saja tanpa perlu data siswa sungguhan.
 */
class TestAdaptationPolicyCommand extends Command
{
    protected $signature = 'learning:test-adaptation-policy';

    protected $description = 'Modul 4: uji skenario salah-salah-benar pada AdaptationPolicy';

    public function handle(AdaptationPolicy $policy): int
    {
        $failed = false;

        $this->info('=== Skenario 1: salah -> salah -> escalasi level bantuan ===');
        $this->newLine();

        // Langkah 1: siswa salah pertama kali di sebuah soal.
        $step1 = $policy->decideHintLevel([
            'wrong_count' => 1,
            'hints_requested' => 0,
        ]);
        $failed = ! $this->assertLevel('Langkah 1 (salah pertama)', $step1, 1) || $failed;

        // Langkah 2: siswa salah LAGI di soal yang sama.
        $step2 = $policy->decideHintLevel([
            'wrong_count' => 2,
            'hints_requested' => 1,
        ]);
        $failed = ! $this->assertLevel('Langkah 2 (salah kedua kali)', $step2, 2) || $failed;

        // Langkah 3: siswa akhirnya benar (tidak ada keputusan level
        // baru untuk soal ini lagi — soal sudah selesai).
        $this->line('Langkah 3: siswa akhirnya menjawab BENAR — soal ini selesai, tidak ada keputusan level baru.');
        $this->newLine();

        $this->info('=== Skenario 2: fading — 2 soal beruntun benar tanpa hint ===');
        $this->newLine();

        // Setelah 2 soal beruntun benar TANPA hint (fading_active
        // dianggap true), soal berikutnya salah 2x — TANPA fading ini
        // harusnya jadi L2, tapi fading membatasinya ke L1.
        $withoutFading = $policy->decideHintLevel([
            'wrong_count' => 2,
            'fading_active' => false,
        ]);
        $this->line("Tanpa fading, wrong_count=2 -> level {$withoutFading['level']} (baseline, harus 2)");

        $withFading = $policy->decideHintLevel([
            'wrong_count' => 2,
            'fading_active' => true,
        ]);
        $failed = ! $this->assertLevel('Dengan fading aktif, wrong_count=2', $withFading, 1, 'fading_cap') || $failed;

        $this->newLine();
        $this->info('=== Kebijakan guru (Modul 6, dites lewat parameter override) ===');
        $this->newLine();

        $teacherLocked = $policy->decideHintLevel(
            ['wrong_count' => 3, 'hints_requested' => 3],
            ['freeze_level' => 1]
        );
        $failed = ! $this->assertLevel('Guru mengunci ke L1 only, walau sinyal harusnya L3', $teacherLocked, 1, 'teacher_lock') || $failed;

        $this->newLine();
        $this->info('=== Versi kebijakan ===');
        $this->line('policy_version yang tercatat: ' . $step1['policy_version']);
        $failed = ($step1['policy_version'] !== config('learning.policy_version')) || $failed;

        $this->newLine();

        if ($failed) {
            $this->error('SATU ATAU LEBIH SKENARIO GAGAL. Lihat detail di atas.');

            return self::FAILURE;
        }

        $this->info('SEMUA SKENARIO LULUS. AdaptationPolicy bekerja sesuai spesifikasi Modul 4.');

        return self::SUCCESS;
    }

    private function assertLevel(string $label, array $decision, int $expectedLevel, ?string $expectedReason = null): bool
    {
        $pass = $decision['level'] === $expectedLevel;

        if ($expectedReason !== null) {
            $pass = $pass && in_array($expectedReason, $decision['reasons'], true);
        }

        $reasonsText = implode(', ', $decision['reasons']);

        if ($pass) {
            $this->info("✅ {$label}: level {$decision['level']} (alasan: {$reasonsText})");
        } else {
            $this->error("❌ {$label}: level {$decision['level']} (alasan: {$reasonsText}) — DIHARAPKAN level {$expectedLevel}" . ($expectedReason ? " dengan alasan '{$expectedReason}'" : ''));
        }

        return $pass;
    }
}
