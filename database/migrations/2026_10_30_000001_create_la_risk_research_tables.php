<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Learning Analytics Fase 4–5: skor risiko + riwayat, tindak lanjut guru, pengaturan, persetujuan riset,
     * log akses data siswa, dan penanda kelompok riset. ADDITIVE dan reversible.
     */
    public function up(): void
    {
        Schema::create('la_settings', function (Blueprint $table) {
            $table->string('key', 60)->primary();
            $table->json('value');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('la_risk_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('score', 5, 1);
            $table->string('level', 10);
            $table->string('confidence', 12);
            $table->decimal('coverage', 4, 2);
            $table->json('factors')->nullable();
            $table->json('components')->nullable();
            $table->decimal('model_prob', 5, 4)->nullable();
            $table->timestamp('computed_at')->nullable();
            $table->index('level');
        });

        Schema::create('la_risk_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->decimal('score', 5, 1);
            $table->string('level', 10);
            $table->unique(['user_id', 'date']);
            $table->index('date');
        });

        Schema::create('la_interventions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();           // siswa
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete(); // guru/admin
            $table->string('risk_level', 10)->nullable();
            $table->string('action', 60)->nullable();                                 // jenis tindakan
            $table->text('note')->nullable();
            $table->timestamp('followed_at');
            $table->timestamps();
            $table->index(['user_id', 'followed_at']);
        });

        Schema::create('la_consents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('version', 20);
            $table->boolean('accepted');
            $table->timestamp('answered_at');
        });

        Schema::create('la_access_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 60);
            $table->string('target', 120)->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['action', 'created_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'research_group')) {
                $table->string('research_group', 30)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'research_group')) {
                $table->dropColumn('research_group');
            }
        });
        Schema::dropIfExists('la_access_log');
        Schema::dropIfExists('la_consents');
        Schema::dropIfExists('la_interventions');
        Schema::dropIfExists('la_risk_history');
        Schema::dropIfExists('la_risk_scores');
        Schema::dropIfExists('la_settings');
    }
};
