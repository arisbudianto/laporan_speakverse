<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reading_questions', function (Blueprint $table) {
            $table->enum('category', [
                'main_idea',
                'detail',
                'inference',
                'vocabulary',
            ])
            ->default('detail')
            ->after('question');
        });

        Schema::table('listening_questions', function (Blueprint $table) {
            $table->enum('category', [
                'main_idea',
                'detail',
                'interpretation',
                'vocabulary',
            ])
            ->default('detail')
            ->after('question');
        });
    }

    public function down(): void
    {
        Schema::table('reading_questions', function (Blueprint $table) {
            $table->dropColumn('category');
        });

        Schema::table('listening_questions', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
};