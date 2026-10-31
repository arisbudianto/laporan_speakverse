<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Modul 5 (perluasan ke Listening) — listening_questions belum
     * pernah punya kolom diagnostik Modul 2 sama sekali. Menambahkan
     * kolom yang SAMA PERSIS dengan reading_questions (lihat
     * 2026_09_24_000003_... dan 2026_09_26_000001_...) supaya
     * DiagnosticEngine, ScaffoldingEngine, dan alat bulk-labeling bisa
     * dipakai untuk Listening tanpa perubahan sama sekali di service
     * layer — semuanya sudah skill-agnostic sejak awal dibangun.
     */
    public function up(): void
    {
        Schema::table('listening_questions', function (Blueprint $table) {
            $table->json('error_if_wrong')->nullable()->after('correct_answer');
            $table->enum('error_labels_source', ['manual', 'ai_suggested'])->nullable()->after('error_if_wrong');
            $table->text('rationale')->nullable()->after('error_labels_source');
            $table->text('text_span')->nullable()->after('rationale');
        });
    }

    public function down(): void
    {
        Schema::table('listening_questions', function (Blueprint $table) {
            $table->dropColumn(['error_if_wrong', 'error_labels_source', 'rationale', 'text_span']);
        });
    }
};
