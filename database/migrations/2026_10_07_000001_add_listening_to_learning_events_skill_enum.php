<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Kolom learning_events.skill awalnya hanya menerima 'reading' dan
     * 'vocabulary'. Listening (Modul 5) memakai skill 'listening', jadi
     * pencatatan sesi ditolak database -> quiz_session_id kosong ->
     * tombol Check di Listening gagal ("The quiz session id field is required").
     */
    public function up(): void
    {
        DB::statement(
            "ALTER TABLE learning_events MODIFY skill ENUM('reading','vocabulary','listening') NOT NULL DEFAULT 'reading'"
        );
    }

    public function down(): void
    {
        DB::statement(
            "ALTER TABLE learning_events MODIFY skill ENUM('reading','vocabulary') NOT NULL DEFAULT 'reading'"
        );
    }
};
