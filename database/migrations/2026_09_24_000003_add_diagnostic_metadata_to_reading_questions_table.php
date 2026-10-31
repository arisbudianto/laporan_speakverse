<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Modul 2 — Diagnostic Engine.
     *
     * 'skill_target' TIDAK ditambahkan di sini karena kolom 'sub_skill'
     * yang sudah ada (main_idea, detail_information, inference,
     * vocabulary_in_context) persis konsep yang sama — menambah
     * kolom baru akan jadi duplikat.
     */
    public function up(): void
    {
        Schema::table('reading_questions', function (Blueprint $table) {
            // Peta opsi salah -> kode error, mis.
            // {"A": "lexical", "C": "inferential"}. Opsi yang benar
            // tidak perlu dipetakan (tidak ada error).
            $table->json('error_if_wrong')->nullable()->after('correct_answer');

            // Kenapa kunci jawaban itu benar — dipakai juga sebagai
            // bahan hint di Modul 3/5 nanti.
            $table->text('rationale')->nullable()->after('error_if_wrong');

            // Kutipan dari passage yang mendukung jawaban benar.
            $table->text('text_span')->nullable()->after('rationale');
        });
    }

    public function down(): void
    {
        Schema::table('reading_questions', function (Blueprint $table) {
            $table->dropColumn(['error_if_wrong', 'rationale', 'text_span']);
        });
    }
};
