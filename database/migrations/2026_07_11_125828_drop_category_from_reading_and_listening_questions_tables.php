<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            Schema::hasTable('reading_questions') &&
            Schema::hasColumn('reading_questions', 'category')
        ) {
            Schema::table('reading_questions', function (Blueprint $table) {
                $table->dropColumn('category');
            });
        }

        if (
            Schema::hasTable('listening_questions') &&
            Schema::hasColumn('listening_questions', 'category')
        ) {
            Schema::table('listening_questions', function (Blueprint $table) {
                $table->dropColumn('category');
            });
        }
    }

    public function down(): void
    {
        if (
            Schema::hasTable('reading_questions') &&
            !Schema::hasColumn('reading_questions', 'category')
        ) {
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
        }

        if (
            Schema::hasTable('listening_questions') &&
            !Schema::hasColumn('listening_questions', 'category')
        ) {
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
    }
};