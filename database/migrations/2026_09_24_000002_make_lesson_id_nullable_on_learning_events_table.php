<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Vocabulary Pretest (App\Http\Controllers\VocabularyPretestController)
     * TIDAK terikat ke satu Lesson/Unit — berbeda dari Reading, Listening,
     * Writing, dan Speaking. Supaya Modul 1 (Interaction Logger) juga bisa
     * mencatat sesi Vocabulary Pretest, lesson_id di learning_events harus
     * boleh NULL.
     *
     * Ditulis dengan raw SQL (bukan Schema::table(...)->change()) supaya
     * tidak bergantung paket doctrine/dbal yang belum tentu ter-install.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement(
                'ALTER TABLE `learning_events` DROP FOREIGN KEY `learning_events_lesson_id_foreign`'
            );

            DB::statement(
                'ALTER TABLE `learning_events` MODIFY `lesson_id` BIGINT UNSIGNED NULL'
            );

            DB::statement(
                'ALTER TABLE `learning_events` '
                . 'ADD CONSTRAINT `learning_events_lesson_id_foreign` '
                . 'FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) '
                . 'ON DELETE CASCADE'
            );

            return;
        }

        // Fallback untuk driver lain (mis. sqlite saat testing lokal).
        Schema::table('learning_events', function (Blueprint $table) {
            $table->unsignedBigInteger('lesson_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement(
                'ALTER TABLE `learning_events` DROP FOREIGN KEY `learning_events_lesson_id_foreign`'
            );

            // Catatan: rollback ini akan gagal kalau sudah ada baris
            // vocabulary dengan lesson_id NULL — itu memang konsekuensi
            // yang wajar dari migrasi maju (forward migration), bukan bug.
            DB::statement(
                'ALTER TABLE `learning_events` MODIFY `lesson_id` BIGINT UNSIGNED NOT NULL'
            );

            DB::statement(
                'ALTER TABLE `learning_events` '
                . 'ADD CONSTRAINT `learning_events_lesson_id_foreign` '
                . 'FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) '
                . 'ON DELETE CASCADE'
            );

            return;
        }

        Schema::table('learning_events', function (Blueprint $table) {
            $table->unsignedBigInteger('lesson_id')->nullable(false)->change();
        });
    }
};
