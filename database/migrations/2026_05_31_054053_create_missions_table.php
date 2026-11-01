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
        Schema::create('missions', function (Blueprint $table) {

            $table->id();

            $table->string('title');

            $table->enum('category', [
                'speaking',
                'reading',
                'vocabulary'
            ]);

            $table->enum('difficulty', [
                'easy',
                'medium',
                'hard'
            ]);

            $table->integer('reward_xp');

            $table->text('description');

            $table->date('deadline')->nullable();

            $table->enum('status', [
                'active',
                'draft'
            ])->default('active');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('missions');
    }
};
