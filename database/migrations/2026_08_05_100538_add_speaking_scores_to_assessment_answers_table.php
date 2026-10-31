<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan skor rubric Speaking ke assessment_answers.
     */
    public function up(): void
    {
        $hasDetailsScore =
            Schema::hasColumn(
                'assessment_answers',
                'details_score'
            );

        $hasFluencyScore =
            Schema::hasColumn(
                'assessment_answers',
                'fluency_score'
            );

        $hasPronunciationScore =
            Schema::hasColumn(
                'assessment_answers',
                'pronunciation_score'
            );

        $hasVocabularyScore =
            Schema::hasColumn(
                'assessment_answers',
                'vocabulary_score'
            );

        $hasGrammarScore =
            Schema::hasColumn(
                'assessment_answers',
                'grammar_score'
            );

        Schema::table(
            'assessment_answers',
            function (Blueprint $table) use (
                $hasDetailsScore,
                $hasFluencyScore,
                $hasPronunciationScore,
                $hasVocabularyScore,
                $hasGrammarScore
            ) {
                /*
                |--------------------------------------------------------------------------
                | Content / Story Details
                |--------------------------------------------------------------------------
                |
                | Skor 1–4 untuk kelengkapan dan relevansi isi presentasi.
                |
                */
                if (!$hasDetailsScore) {
                    $table
                        ->tinyInteger('details_score')
                        ->nullable()
                        ->after('mechanics_score');
                }

                /*
                |--------------------------------------------------------------------------
                | Fluency
                |--------------------------------------------------------------------------
                */
                if (!$hasFluencyScore) {
                    $table
                        ->tinyInteger('fluency_score')
                        ->nullable()
                        ->after('details_score');
                }

                /*
                |--------------------------------------------------------------------------
                | Pronunciation
                |--------------------------------------------------------------------------
                */
                if (!$hasPronunciationScore) {
                    $table
                        ->tinyInteger('pronunciation_score')
                        ->nullable()
                        ->after('fluency_score');
                }

                /*
                |--------------------------------------------------------------------------
                | Vocabulary
                |--------------------------------------------------------------------------
                */
                if (!$hasVocabularyScore) {
                    $table
                        ->tinyInteger('vocabulary_score')
                        ->nullable()
                        ->after('pronunciation_score');
                }

                /*
                |--------------------------------------------------------------------------
                | Grammar
                |--------------------------------------------------------------------------
                */
                if (!$hasGrammarScore) {
                    $table
                        ->tinyInteger('grammar_score')
                        ->nullable()
                        ->after('vocabulary_score');
                }
            }
        );
    }

    /**
     * Hapus kolom skor Speaking.
     */
    public function down(): void
    {
        $columns = [];

        if (
            Schema::hasColumn(
                'assessment_answers',
                'details_score'
            )
        ) {
            $columns[] =
                'details_score';
        }

        if (
            Schema::hasColumn(
                'assessment_answers',
                'fluency_score'
            )
        ) {
            $columns[] =
                'fluency_score';
        }

        if (
            Schema::hasColumn(
                'assessment_answers',
                'pronunciation_score'
            )
        ) {
            $columns[] =
                'pronunciation_score';
        }

        if (
            Schema::hasColumn(
                'assessment_answers',
                'vocabulary_score'
            )
        ) {
            $columns[] =
                'vocabulary_score';
        }

        if (
            Schema::hasColumn(
                'assessment_answers',
                'grammar_score'
            )
        ) {
            $columns[] =
                'grammar_score';
        }

        if (count($columns) === 0) {
            return;
        }

        Schema::table(
            'assessment_answers',
            function (Blueprint $table) use (
                $columns
            ) {
                $table->dropColumn(
                    $columns
                );
            }
        );
    }
};