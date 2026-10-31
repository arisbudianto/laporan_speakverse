<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListeningQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'lesson_id',
        'listening_material_id',
        'instruction',
        'question',
        'audio_file',
        'option_a',
        'option_b',
        'option_c',
        'option_d',
        'option_e',
        'correct_answer',
        'score',

        /*
         * Contoh:
         * main_idea, detail_information,
         * inference, vocabulary_in_context
         */
        'sub_skill',

        // Modul 5 (perluasan ke Listening) — kolom diagnostik Modul 2.
        'error_if_wrong',
        'error_labels_source',
        'rationale',
        'text_span',
    ];

    protected function casts(): array
    {
        return [
            'error_if_wrong' => 'array',
        ];
    }

    public function material()
    {
        return $this->belongsTo(
            ListeningMaterial::class,
            'listening_material_id'
        );
    }

    public function lesson()
    {
        return $this->belongsTo(
            Lesson::class,
            'lesson_id'
        );
    }
}
