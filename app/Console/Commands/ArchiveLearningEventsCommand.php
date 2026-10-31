<?php

namespace App\Console\Commands;

use App\Models\LearningEvent;
use Illuminate\Console\Command;

/**
 * Saran perbaikan Modul 1 — strategi retensi data learning_events.
 *
 * PRINSIP UTAMA: data di learning_events adalah DATA PENELITIAN
 * (bahan DBR/thesis Anda), BUKAN cuma log operasional biasa yang
 * aman dihapus otomatis. Karena itu perintah ini:
 * - TIDAK PERNAH dijadwalkan otomatis (tidak didaftarkan di
 *   routes/console.php sebagai scheduled task) — harus dijalankan
 *   manual, sadar, oleh Anda.
 * - Defaultnya HANYA melaporkan (tidak mengekspor, tidak menghapus).
 * - Untuk mengekspor, WAJIB isi --export-path secara eksplisit.
 * - Untuk menghapus, WAJIB tambah --delete DAN mengekspor lebih
 *   dulu di panggilan yang sama, DAN mengonfirmasi lewat prompt.
 *
 * Contoh pemakaian bertahap yang disarankan:
 *   php artisan learning:archive-events --older-than=180
 *     (lihat dulu berapa banyak & berapa persen dari total)
 *   php artisan learning:archive-events --older-than=180 --export-path=storage/app/archive/events-2026-h1.json
 *     (ekspor ke file, TIDAK menghapus apa pun)
 *   php artisan learning:archive-events --older-than=180 --export-path=... --delete
 *     (ekspor ULANG ke file yang sama, lalu baru minta konfirmasi hapus)
 */
class ArchiveLearningEventsCommand extends Command
{
    protected $signature = 'learning:archive-events
        {--older-than=180 : Ambil event yang lebih tua dari N hari}
        {--export-path= : Path file JSON untuk menyimpan salinan. Tanpa ini, perintah HANYA melaporkan, tidak mengekspor atau menghapus apa pun.}
        {--delete : WAJIB disertakan secara eksplisit untuk menghapus dari database SETELAH ekspor berhasil dan Anda konfirmasi. Tanpa flag ini, data lama tidak pernah dihapus.}';

    protected $description = 'Modul 1: laporkan/ekspor/(opsional, dengan konfirmasi) hapus learning_events lama. Selalu manual, tidak pernah otomatis.';

    public function handle(): int
    {
        $olderThanDays = (int) $this->option('older-than');
        $cutoff = now()->subDays($olderThanDays);

        $matchingCount = LearningEvent::where('created_at', '<', $cutoff)->count();
        $totalCount = LearningEvent::count();

        $this->info("Ambang batas: event sebelum {$cutoff->format('Y-m-d H:i')} ({$olderThanDays} hari yang lalu).");
        $this->line("Cocok: {$matchingCount} baris, dari total {$totalCount} baris di tabel learning_events.");

        if ($matchingCount === 0) {
            $this->info('Tidak ada yang perlu diekspor/dihapus.');

            return self::SUCCESS;
        }

        $percentage = $totalCount > 0 ? round($matchingCount / $totalCount * 100, 1) : 0;
        $this->line("Itu {$percentage}% dari seluruh data.");

        $exportPath = $this->option('export-path');

        if (! $exportPath) {
            $this->newLine();
            $this->warn('Ini HANYA laporan — tidak ada file yang dibuat, tidak ada yang dihapus.');
            $this->line('Untuk mengekspor ke file (langkah wajib sebelum bisa menghapus), tambahkan:');
            $this->line('  --export-path=storage/app/archive/events-' . now()->format('Y-m-d') . '.json');

            return self::SUCCESS;
        }

        $this->newLine();
        $this->info("Mengekspor {$matchingCount} baris ke {$exportPath} ...");

        $directory = dirname($exportPath);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $handle = fopen($exportPath, 'w');

        if ($handle === false) {
            $this->error("Tidak bisa menulis ke {$exportPath}. Cek izin folder.");

            return self::FAILURE;
        }

        fwrite($handle, "[\n");
        $isFirstRow = true;
        $exportedCount = 0;

        LearningEvent::where('created_at', '<', $cutoff)
            ->orderBy('id')
            ->chunkById(500, function ($rows) use ($handle, &$isFirstRow, &$exportedCount) {
                foreach ($rows as $row) {
                    if (! $isFirstRow) {
                        fwrite($handle, ",\n");
                    }
                    fwrite($handle, json_encode($row->toArray(), JSON_UNESCAPED_UNICODE));
                    $isFirstRow = false;
                    $exportedCount++;
                }
            });

        fwrite($handle, "\n]\n");
        fclose($handle);

        $this->info("Ekspor selesai: {$exportedCount} baris tersimpan di {$exportPath}.");

        if (! $this->option('delete')) {
            $this->newLine();
            $this->warn('Data BELUM dihapus dari database (flag --delete tidak disertakan) — aman, tidak ada perubahan di database.');
            $this->line('Setelah Anda memeriksa file ekspor di atas dan yakin sudah tercadangkan dengan baik, jalankan ulang perintah yang sama dengan tambahan --delete untuk menghapusnya dari database.');

            return self::SUCCESS;
        }

        $this->newLine();

        if (! $this->confirm("File ekspor sudah dibuat. Anda YAKIN mau menghapus {$matchingCount} baris ini dari database? Tindakan ini TIDAK BISA dibatalkan.", false)) {
            $this->info('Dibatalkan — tidak ada yang dihapus dari database. File ekspor tetap tersimpan.');

            return self::SUCCESS;
        }

        $deletedCount = LearningEvent::where('created_at', '<', $cutoff)->delete();

        $this->info("Dihapus {$deletedCount} baris dari database. Salinannya tetap aman di {$exportPath}.");

        return self::SUCCESS;
    }
}
