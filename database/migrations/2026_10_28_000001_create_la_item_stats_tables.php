<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Learning Analytics Fase 2 — hasil analisis butir (Teori Tes Klasik). Diisi `la:item-analysis`. */
    public function up(): void
    {
        Schema::create('la_form_stats', function (Blueprint $table) {
            $table->id();
            $table->string('form', 120)->unique();   // type|skill|unit_id|lesson_id
            $table->unsignedInteger('n');
            $table->unsignedSmallInteger('k');
            $table->decimal('mean', 8, 2)->nullable();
            $table->decimal('sd', 8, 2)->nullable();
            $table->decimal('alpha', 6, 3)->nullable();
            $table->decimal('sem', 8, 2)->nullable();
            $table->boolean('indicative')->default(false);
            $table->timestamp('computed_at')->nullable();
        });

        Schema::create('la_item_stats', function (Blueprint $table) {
            $table->id();
            $table->string('form', 120)->index();
            $table->string('item', 60);               // question_type:question_id
            $table->unsignedInteger('n');
            $table->decimal('p', 5, 3);
            $table->decimal('r_pb', 6, 3)->nullable();
            $table->decimal('d_index', 6, 3)->nullable();
            $table->string('key_option', 10)->nullable();
            $table->json('options')->nullable();
            $table->json('flags')->nullable();
            $table->boolean('indicative')->default(false);
            $table->timestamp('computed_at')->nullable();
            $table->unique(['form', 'item']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('la_item_stats');
        Schema::dropIfExists('la_form_stats');
    }
};
