<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Saran perbaikan Modul 6 — riwayat kebijakan yang dihapus.
     *
     * Memakai soft delete (Laravel bawaan) bukan tabel audit
     * terpisah — kebijakan yang dihapus TETAP ADA di database
     * (deleted_at terisi), cuma disembunyikan dari query normal
     * (termasuk yang dipakai TeacherOverrideService — kebijakan yang
     * dihapus otomatis TIDAK BERLAKU lagi, tanpa perubahan kode di
     * sana sama sekali, karena SoftDeletes bawaan Laravel menambah
     * scope global itu sendiri).
     *
     * deleted_by mencatat SIAPA yang menghapus, supaya guru lain bisa
     * lihat "kebijakan ini dulu ada, dihapus oleh X pada tanggal Y".
     */
    public function up(): void
    {
        Schema::table('teacher_scaffolding_policies', function (Blueprint $table) {
            $table->softDeletes();

            $table->foreignId('deleted_by')
                ->nullable()
                ->after('deleted_at')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('teacher_scaffolding_policies', function (Blueprint $table) {
            $table->dropConstrainedForeignId('deleted_by');
            $table->dropSoftDeletes();
        });
    }
};
