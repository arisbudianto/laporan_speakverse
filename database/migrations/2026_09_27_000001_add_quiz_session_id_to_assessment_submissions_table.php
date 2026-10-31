<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Saran perbaikan Modul 5 — Halaman Pembahasan.
     *
     * Menyimpan quiz_session_id langsung di assessment_submissions
     * supaya halaman pembahasan bisa menelusuri balik ke
     * learning_events (Modul 1) untuk tahu jawaban PERSIS apa yang
     * dipilih siswa di percobaan pertama tiap soal, tanpa perlu
     * membuat tabel jawaban terpisah.
     */
    public function up(): void
    {
        Schema::table('assessment_submissions', function (Blueprint $table) {
            $table->string('quiz_session_id', 36)->nullable()->after('lesson_id');
        });
    }

    public function down(): void
    {
        Schema::table('assessment_submissions', function (Blueprint $table) {
            $table->dropColumn('quiz_session_id');
        });
    }
};
