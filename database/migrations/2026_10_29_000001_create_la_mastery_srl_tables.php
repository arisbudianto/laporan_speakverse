<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Learning Analytics Fase 3 — penguasaan (BKT), fitur perilaku/SRL, profil klaster, catatan model. Diisi `la:fit-models`. */
    public function up(): void
    {
        Schema::create('la_model_runs', function (Blueprint $table) {
            $table->id();
            $table->string('kind', 30);                 // bkt | cluster
            $table->string('version', 30);
            $table->json('params')->nullable();
            $table->json('metrics')->nullable();
            $table->unsignedInteger('n')->default(0);
            $table->timestamp('computed_at')->nullable();
            $table->index(['kind', 'computed_at']);
        });

        Schema::create('la_bkt_params', function (Blueprint $table) {
            $table->id();
            $table->string('competency', 100)->unique();
            $table->decimal('l0', 5, 3);
            $table->decimal('t', 5, 3);
            $table->decimal('s', 5, 3);
            $table->decimal('g', 5, 3);
            $table->decimal('log_likelihood', 12, 2)->nullable();
            $table->unsignedInteger('students');
            $table->unsignedInteger('n_obs');
            $table->boolean('is_default')->default(false);
            $table->unsignedInteger('mastered')->default(0);
            $table->unsignedInteger('wheel_spinning')->default(0);
            $table->json('curve')->nullable();
            $table->timestamp('computed_at')->nullable();
        });

        Schema::create('la_mastery', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('competency', 100);
            $table->decimal('p_mastery', 5, 4);
            $table->unsignedSmallInteger('n_obs');
            $table->boolean('mastered')->default(false);
            $table->boolean('wheel_spinning')->default(false);
            $table->unsignedSmallInteger('opportunities_to_mastery')->nullable();
            $table->timestamp('computed_at')->nullable();
            $table->unique(['user_id', 'competency']);
            $table->index('wheel_spinning');
        });

        Schema::create('la_srl_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedInteger('items');
            $table->decimal('first_try_acc', 5, 3);
            $table->decimal('revision_rate', 5, 3);
            $table->decimal('hint_rate', 5, 3);
            $table->decimal('hint_l3_rate', 5, 3);
            $table->unsignedInteger('median_ms')->nullable();
            $table->decimal('fast_wrong_rate', 5, 3)->nullable();
            $table->decimal('hint_chain_rate', 5, 3)->nullable();
            $table->unsignedSmallInteger('struggle_productive')->default(0);
            $table->unsignedSmallInteger('struggle_assisted')->default(0);
            $table->unsignedSmallInteger('struggle_unproductive')->default(0);
            $table->unsignedSmallInteger('struggle_no_effort')->default(0);
            $table->boolean('gaming_flag')->default(false);
            $table->boolean('enough')->default(false);
            $table->string('profile', 60)->nullable();
            $table->timestamp('computed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('la_srl_student');
        Schema::dropIfExists('la_mastery');
        Schema::dropIfExists('la_bkt_params');
        Schema::dropIfExists('la_model_runs');
    }
};
