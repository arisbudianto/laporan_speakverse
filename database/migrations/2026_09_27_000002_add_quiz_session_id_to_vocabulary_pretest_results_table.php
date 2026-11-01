<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Lihat penjelasan lengkap di migration yang sama untuk
     * assessment_submissions (2026_09_27_000001_...).
     */
    public function up(): void
    {
        Schema::table('vocabulary_pretest_results', function (Blueprint $table) {
            $table->string('quiz_session_id', 36)->nullable()->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('vocabulary_pretest_results', function (Blueprint $table) {
            $table->dropColumn('quiz_session_id');
        });
    }
};
