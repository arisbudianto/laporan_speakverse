<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Modul 6 — Teacher HITL Override.
     *
     * Satu baris = satu kebijakan yang guru tetapkan, untuk SATU
     * siswa (scope_type='student') ATAU SATU kelas
     * (scope_type='class', diidentifikasi lewat
     * school+major+grade+parallel — pola yang sama dipakai
     * ClassResultsController). Kalau siswa punya policy sendiri DAN
     * kelasnya juga punya policy, keduanya digabung dengan policy
     * per-siswa menang per kolom (lihat
     * TeacherOverrideService::resolveForStudent()).
     *
     * "Log siapa yang override" dari DoD dipenuhi oleh kolom
     * teacher_id + created_at di baris ini sendiri — tidak perlu
     * tabel log terpisah.
     */
    public function up(): void
    {
        Schema::create('teacher_scaffolding_policies', function (Blueprint $table) {
            $table->id();

            $table->foreignId('teacher_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->enum('scope_type', ['student', 'class']);

            // Diisi kalau scope_type='student'.
            $table->foreignId('student_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnDelete();

            // Diisi kalau scope_type='class' — pola sama dengan
            // identifikasi kelas di Staff\ClassResultsController.
            $table->string('school')->nullable();
            $table->string('major')->nullable();
            $table->string('grade')->nullable();
            $table->string('parallel')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Aksi override (semua opsional — guru isi yang relevan saja)
            |--------------------------------------------------------------------------
            */

            // Kunci PERSIS ke level ini (DoD: "guru mengunci siswa ke
            // L1 only"). Kalau diisi, ini yang menang — lihat
            // AdaptationPolicy::applyTeacherOverride().
            $table->unsignedTinyInteger('freeze_level')->nullable();

            // Batasi level MAKSIMAL (lebih longgar dari freeze —
            // policy tetap boleh memberi level lebih rendah secara
            // alami, cuma tidak boleh melebihi ini).
            $table->unsignedTinyInteger('max_level')->nullable();

            $table->unsignedTinyInteger('min_level')->nullable();

            // "wajib_hint_sebelum_cek": siswa tidak boleh klik "Check
            // Again" tanpa lebih dulu minta hint untuk soal itu.
            $table->boolean('require_hint_before_recheck')->default(false);

            // "nonaktifkan LLM (pakai template saja)": paksa
            // ScaffoldingEngine selalu pakai template rule-based,
            // walau LLM terkonfigurasi.
            $table->boolean('disable_llm')->default(false);

            // Intervensi manual — komentar guru, tidak mengubah
            // perilaku sistem, murni catatan untuk guru lain.
            $table->text('note')->nullable();

            $table->timestamp('expires_at')->nullable();

            $table->timestamps();

            $table->index(['scope_type', 'student_id']);

            // Catatan: index gabungan atas school+major+grade+parallel
            // (semua VARCHAR(255)/utf8mb4) SENGAJA tidak dibuat —
            // gabungan 4 kolom teks itu melebihi batas panjang index
            // MySQL (3072 byte). Tabel ini hanya berisi kebijakan yang
            // dibuat guru (jumlah baris kecil), jadi tidak butuh index
            // untuk performa; index scope_type di atas sudah cukup
            // untuk mempersempit ke baris scope_type='class' sebelum
            // MySQL memfilter kolom lainnya.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_scaffolding_policies');
    }
};
