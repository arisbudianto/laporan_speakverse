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
        Schema::create('assessment_submissions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('unit_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('lesson_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->enum('type', [
                'pretest',
                'posttest',
            ]);

            $table->enum('skill', [
                'listening',
                'reading',
                'writing',
                'speaking',
            ]);

            $table->integer('final_score')
                ->nullable();

            $table->enum('status', [
                'pending',
                'completed',
                'failed',
            ])->default('pending');

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
        Schema::dropIfExists('assessment_submissions');
    }
};
