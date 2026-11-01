<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class VocabularyPretest extends Model
{
    protected $fillable = [
        'category',
        'question',
        'option_a',
        'option_b',
        'option_c',
        'option_d',
        'option_e',
        'correct_answer',

        /*
        |--------------------------------------------------------------------------
        | Modul 2 — Diagnostic Engine
        |--------------------------------------------------------------------------
        */
        'skill_target',
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

}
