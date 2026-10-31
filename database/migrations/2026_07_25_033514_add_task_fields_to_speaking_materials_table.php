<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('speaking_materials', function (Blueprint $table) {
            $table->longText('scenario')
                ->nullable()
                ->after('instruction');

            $table->longText('role_a')
                ->nullable()
                ->after('scenario');

            $table->longText('role_b')
                ->nullable()
                ->after('role_a');

            $table->json('discussion_points')
                ->nullable()
                ->after('role_b');

            $table->unsignedInteger('min_duration')
                ->nullable()
                ->after('discussion_points');

            $table->unsignedInteger('max_duration')
                ->nullable()
                ->after('min_duration');

            $table->boolean('is_pair_work')
                ->default(true)
                ->after('max_duration');

            $table->boolean('ai_evaluation_enabled')
                ->default(true)
                ->after('is_pair_work');
        });
    }

    public function down(): void
    {
        Schema::table('speaking_materials', function (Blueprint $table) {
            $table->dropColumn([
                'scenario',
                'role_a',
                'role_b',
                'discussion_points',
                'min_duration',
                'max_duration',
                'is_pair_work',
                'ai_evaluation_enabled',
            ]);
        });
    }
};