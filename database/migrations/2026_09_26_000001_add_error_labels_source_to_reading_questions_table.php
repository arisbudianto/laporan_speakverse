<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Saran perbaikan Modul 2: mempercepat pengisian error_if_wrong
     * lewat usulan LLM (App\Services\Learning\ErrorLabelSuggester +
     * `php artisan learning:suggest-error-labels`). Kolom ini melacak
     * apakah label sebuah soal berasal dari usulan AI yang BELUM
     * ditinjau guru/admin, atau sudah manual/ditinjau — supaya UI
     * bisa menandai mana yang masih perlu dicek, tanpa mengubah
     * perilaku DiagnosticEngine sama sekali (ia tetap memakai
     * error_if_wrong apa adanya, tidak peduli sumbernya).
     */
    public function up(): void
    {
        Schema::table('reading_questions', function (Blueprint $table) {
            $table->enum('error_labels_source', ['manual', 'ai_suggested'])
                ->nullable()
                ->after('error_if_wrong');
        });
    }

    public function down(): void
    {
        Schema::table('reading_questions', function (Blueprint $table) {
            $table->dropColumn('error_labels_source');
        });
    }
};
