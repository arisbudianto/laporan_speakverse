<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Modul 5 — In-Quiz Tutor UX (Vocabulary Pretest).
     *
     * Menyamakan vocabulary_pretest_results dengan assessment_submissions
     * (Reading) yang sudah punya kolom feedback — dipakai untuk
     * menyimpan feedback dinamis dari buildDynamicFeedback().
     */
    public function up(): void
    {
        Schema::table('vocabulary_pretest_results', function (Blueprint $table) {
            $table->text('feedback')->nullable()->after('score');
        });
    }

    public function down(): void
    {
        Schema::table('vocabulary_pretest_results', function (Blueprint $table) {
            $table->dropColumn('feedback');
        });
    }
};
