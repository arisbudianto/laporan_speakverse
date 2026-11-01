<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpeakingSubmission extends Model
{
    protected $fillable = [
        'user_id',
        'partner_user_id',
        'speaking_material_id',
        'submitter_role',
        'audio_file',
        'audio_duration',
        'transcript',
        'details_score',
        'fluency_score',
        'pronunciation_score',
        'vocabulary_score',
        'grammar_score',
        'total_score',
        'feedback',
        'strengths',
        'improvements',
        'grammar_errors',
        'raw_ai_response',
        'status',
        'evaluated_at',
    ];

    protected $casts = [
        'partner_user_id' => 'integer',
        'audio_duration' => 'integer',
        'details_score' => 'integer',
        'fluency_score' => 'integer',
        'pronunciation_score' => 'integer',
        'vocabulary_score' => 'integer',
        'grammar_score' => 'integer',
        'total_score' => 'integer',
        'grammar_errors' => 'array',
        'raw_ai_response' => 'array',
        'evaluated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function partner()
    {
        return $this->belongsTo(
            User::class,
            'partner_user_id'
        );
    }

    public function material()
    {
        return $this->belongsTo(
            SpeakingMaterial::class,
            'speaking_material_id'
        );
    }
}