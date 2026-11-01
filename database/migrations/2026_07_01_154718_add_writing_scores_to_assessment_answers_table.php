<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assessment_answers', function (Blueprint $table) {

            $table->tinyInteger('orientation_score')->nullable()->after('is_correct');
            $table->tinyInteger('complication_score')->nullable()->after('orientation_score');
            $table->tinyInteger('resolution_score')->nullable()->after('complication_score');
            $table->tinyInteger('organization_score')->nullable()->after('resolution_score');
            $table->tinyInteger('mechanics_score')->nullable()->after('organization_score');

        });
    }

    public function down(): void
    {
        Schema::table('assessment_answers', function (Blueprint $table) {

            $table->dropColumn([
                'orientation_score',
                'complication_score',
                'resolution_score',
                'organization_score',
                'mechanics_score',
            ]);

        });
    }
};