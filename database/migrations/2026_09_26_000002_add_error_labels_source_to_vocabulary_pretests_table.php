<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Lihat penjelasan lengkap di migration yang sama untuk
     * reading_questions (2026_09_26_000001_...).
     */
    public function up(): void
    {
        Schema::table('vocabulary_pretests', function (Blueprint $table) {
            $table->enum('error_labels_source', ['manual', 'ai_suggested'])
                ->nullable()
                ->after('error_if_wrong');
        });
    }

    public function down(): void
    {
        Schema::table('vocabulary_pretests', function (Blueprint $table) {
            $table->dropColumn('error_labels_source');
        });
    }
};
