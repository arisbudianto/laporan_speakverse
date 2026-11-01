<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_lesson_progress', function (Blueprint $table) {
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

            $table->string('skill_type');

            $table->string('status')->default('completed');

            $table->integer('score')->nullable();

            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->unique([
                'user_id',
                'lesson_id',
                'skill_type'
            ], 'user_lesson_progress_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_lesson_progress');
    }
};