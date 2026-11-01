<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Learning Analytics Fase 1 — agregat harian per siswa.
     * Diisi oleh `php artisan la:aggregate` (idempoten). Tabel ADDITIVE: tidak menyentuh tabel lain.
     */
    public function up(): void
    {
        Schema::create('la_daily_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->unsignedSmallInteger('sessions')->default(0);
            $table->unsignedInteger('active_seconds')->default(0);   // estimasi time-on-task (lihat SessionBuilder)
            $table->unsignedInteger('events')->default(0);            // semua jejak aktivitas
            $table->unsignedTinyInteger('peak_hour')->default(0);
            $table->unsignedSmallInteger('quiz_answers')->default(0); // jawaban percobaan ke-1 pada learning_events
            $table->unsignedSmallInteger('quiz_correct')->default(0);
            $table->unsignedSmallInteger('hint_l1')->default(0);
            $table->unsignedSmallInteger('hint_l2')->default(0);
            $table->unsignedSmallInteger('hint_l3')->default(0);
            $table->timestamp('computed_at')->nullable();

            $table->unique(['user_id', 'date']);
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('la_daily_student');
    }
};
