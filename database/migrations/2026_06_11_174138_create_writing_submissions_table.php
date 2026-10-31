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
        Schema::create('writing_submissions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('writing_question_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->longText('answer');

            $table->integer('orientation_score')
                ->nullable();

            $table->integer('complication_score')
                ->nullable();

            $table->integer('resolution_score')
                ->nullable();

            $table->integer('organization_score')
                ->nullable();

            $table->integer('mechanics_score')
                ->nullable();

            $table->integer('final_score')
                ->nullable();

            $table->longText('feedback')
                ->nullable();

            $table->timestamp('submitted_at')
                ->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('writing_submissions');
    }
};
