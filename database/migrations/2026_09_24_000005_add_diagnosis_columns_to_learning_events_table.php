<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Modul 2 — Diagnostic Engine.
     *
     * Kolom-kolom ini diisi belakangan (setelah kuis dinilai), bukan
     * saat baris 'select'/'revise' pertama kali dibuat — pada saat
     * itu benar/salah belum diketahui siswa (lihat komentar di
     * StudentReadingController::complete()). Ini mengubah prinsip
     * "append-only" Modul 1 menjadi "insert sekali, enrich sekali"
     * khusus untuk baris select/revise — lihat
     * InteractionLogger::enrichAttempt().
     */
    public function up(): void
    {
        Schema::table('learning_events', function (Blueprint $table) {
            $table->enum('error_code', [
                'lexical',
                'inferential',
                'syntactic',
                'context_misconception',
            ])->nullable()->after('is_correct');

            // 1.0 untuk hasil rule-based (map eksplisit, selalu pasti
            // benar); < 1.0 untuk hasil LLM (belum dipakai di Sprint
            // B ini — lihat DiagnosticEngine).
            $table->decimal('error_confidence', 3, 2)->nullable()->after('error_code');

            $table->enum('classifier', ['rule', 'llm'])->nullable()->after('error_confidence');
        });
    }

    public function down(): void
    {
        Schema::table('learning_events', function (Blueprint $table) {
            $table->dropColumn(['error_code', 'error_confidence', 'classifier']);
        });
    }
};
