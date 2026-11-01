<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Modul 2 — Diagnostic Engine.
     *
     * vocabulary_pretests tidak punya kolom setara 'sub_skill' seperti
     * reading_questions, jadi 'skill_target' ditambahkan di sini
     * sebagai kolom baru (beda dari migration reading_questions).
     */
    public function up(): void
    {
        Schema::table('vocabulary_pretests', function (Blueprint $table) {
            // Contoh isi: word_meaning, collocation, word_form.
            $table->string('skill_target')->nullable()->after('category');

            $table->json('error_if_wrong')->nullable()->after('correct_answer');
            $table->text('rationale')->nullable()->after('error_if_wrong');

            // Untuk vocabulary biasanya diisi kalimat contoh
            // pemakaian kata, bukan kutipan passage seperti reading.
            $table->text('text_span')->nullable()->after('rationale');
        });
    }

    public function down(): void
    {
        Schema::table('vocabulary_pretests', function (Blueprint $table) {
            $table->dropColumn(['skill_target', 'error_if_wrong', 'rationale', 'text_span']);
        });
    }
};
