<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migration.
     */
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Reading Questions
        |--------------------------------------------------------------------------
        */

        Schema::table('reading_questions', function (Blueprint $table) {
            $table
                ->string('sub_skill', 80)
                ->nullable()
                ->after('score');
        });

        /*
        |--------------------------------------------------------------------------
        | Listening Questions
        |--------------------------------------------------------------------------
        */

        Schema::table('listening_questions', function (Blueprint $table) {
            $table
                ->string('sub_skill', 80)
                ->nullable()
                ->after('score');
        });
    }

    /**
     * Rollback migration.
     */
    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Reading Questions
        |--------------------------------------------------------------------------
        */

        Schema::table('reading_questions', function (Blueprint $table) {
            $table->dropColumn('sub_skill');
        });

        /*
        |--------------------------------------------------------------------------
        | Listening Questions
        |--------------------------------------------------------------------------
        */

        Schema::table('listening_questions', function (Blueprint $table) {
            $table->dropColumn('sub_skill');
        });
    }
};