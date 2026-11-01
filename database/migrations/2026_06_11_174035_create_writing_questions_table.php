<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('writing_questions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('writing_material_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->text('question');

            $table->string('image')
                ->nullable();

            $table->text('sample_answer')
                ->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('writing_questions');
    }
};
