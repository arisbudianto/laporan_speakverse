<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('speaking_submissions', function (Blueprint $table) {
            $table->foreignId('partner_user_id')
                ->nullable()
                ->after('user_id')
                ->constrained('users')
                ->nullOnDelete();

            $table->string('submitter_role', 1)
                ->nullable()
                ->after('partner_user_id');

            $table->unsignedInteger('audio_duration')
                ->nullable()
                ->after('audio_file');

            $table->longText('strengths')
                ->nullable()
                ->after('feedback');

            $table->longText('improvements')
                ->nullable()
                ->after('strengths');

            $table->json('grammar_errors')
                ->nullable()
                ->after('improvements');

            $table->json('raw_ai_response')
                ->nullable()
                ->after('grammar_errors');

            $table->timestamp('evaluated_at')
                ->nullable()
                ->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('speaking_submissions', function (Blueprint $table) {
            $table->dropForeign([
                'partner_user_id',
            ]);

            $table->dropColumn([
                'partner_user_id',
                'submitter_role',
                'audio_duration',
                'strengths',
                'improvements',
                'grammar_errors',
                'raw_ai_response',
                'evaluated_at',
            ]);
        });
    }
};