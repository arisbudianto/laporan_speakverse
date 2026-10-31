<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Modul 1 — Interaction Logger.
     *
     * Tabel log interaksi siswa per soal (reading & vocabulary untuk
     * tahap awal): kapan sesi kuis dibuka, jawaban apa yang dipilih,
     * berapa kali diganti (revisi), berapa lama merespons, dan kapan
     * kuis dikirim. Ini fondasi untuk Modul 2 (Diagnostic Engine),
     * Modul 3 (LLM Scaffolding Engine), dan Modul 4 (Adaptation
     * Policy) — modul ini sendiri TIDAK mengubah logika skor apa pun,
     * murni observabilitas (append-only log).
     */
    public function up(): void
    {
        Schema::create('learning_events', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // Mengelompokkan seluruh event dalam satu kali pengerjaan
            // kuis (dari dibuka sampai dikirim/ditinggalkan).
            $table->uuid('quiz_session_id');

            $table->foreignId('lesson_id')
                ->constrained()
                ->cascadeOnDelete();

            // Sengaja TANPA foreign key: question_id bisa merujuk ke
            // reading_questions atau (nanti) item vocabulary, dua
            // tabel berbeda tergantung 'skill'. Null untuk event yang
            // tidak terikat satu soal (start, submit, abandon).
            $table->unsignedBigInteger('question_id')->nullable();

            $table->enum('skill', ['reading', 'vocabulary'])
                ->default('reading');

            $table->enum('event_type', [
                'start',
                'select',
                'revise',
                'hint_request',
                'submit',
                'abandon',
            ]);

            $table->string('selected_answer', 10)->nullable();
            $table->boolean('is_correct')->nullable();

            // Diisi mulai Modul 3 (LLM Scaffolding Engine); kolom
            // disiapkan dari awal supaya migrasi lanjutan tidak perlu
            // ALTER TABLE lagi.
            $table->unsignedTinyInteger('hint_level_shown')->nullable();

            // Waktu dari soal pertama kali tampil sampai aksi ini,
            // dikirim oleh klien (lihat catatan di InteractionLogger
            // soal keandalan timestamp klien).
            $table->unsignedInteger('response_ms')->nullable();

            // Urutan percobaan ke berapa untuk soal ini dalam sesi
            // yang sama (1 = pilihan pertama, 2+ = revisi).
            $table->unsignedSmallInteger('attempt_no')->default(1);

            // Data mentah tambahan (mis. daftar semua opsi yang
            // sempat dipilih sebelum final) untuk analisis lanjutan
            // tanpa perlu migrasi kolom baru setiap kali.
            $table->json('payload')->nullable();

            // Log ini hanya ditambah (append-only), tidak pernah
            // diedit, jadi cukup created_at — tanpa updated_at.
            $table->timestamp('created_at')->useCurrent();

            $table->index(['lesson_id', 'skill', 'question_id'], 'learning_events_lesson_skill_question_idx');
            $table->index('quiz_session_id');
            $table->index(['user_id', 'lesson_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_events');
    }
};
