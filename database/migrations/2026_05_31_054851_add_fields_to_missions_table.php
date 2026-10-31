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
        Schema::table('missions', function (Blueprint $table) {

            $table->string('title')->after('id');

            $table->enum('category', [
                'speaking',
                'reading',
                'vocabulary'
            ])->after('title');

            $table->enum('difficulty', [
                'easy',
                'medium',
                'hard'
            ])->after('category');

            $table->integer('reward_xp')->after('difficulty');

            $table->text('description')->after('reward_xp');

            $table->date('deadline')
                ->nullable()
                ->after('description');

            $table->enum('status', [
                'active',
                'draft'
            ])
            ->default('active')
            ->after('deadline');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('missions', function (Blueprint $table) {

            $table->dropColumn([
                'title',
                'category',
                'difficulty',
                'reward_xp',
                'description',
                'deadline',
                'status'
            ]);

        });
    }
};