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
        Schema::create('assessment_answers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('assessment_submission_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('question_type');

            $table->unsignedBigInteger('question_id');

            $table->longText('answer')
                ->nullable();

            $table->string('selected_option')
                ->nullable();

            $table->boolean('is_correct')
                ->nullable();

            $table->integer('score')
                ->default(0);

            $table->integer('max_score')
                ->default(0);

            $table->longText('feedback')
                ->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_answers');
    }
};
