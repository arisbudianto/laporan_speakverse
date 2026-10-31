<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpeakingMaterial extends Model
{
    protected $fillable = [
        'lesson_id',
        'title',
        'instruction',
        'scenario',
        'role_a',
        'role_b',
        'discussion_points',
        'min_duration',
        'max_duration',
        'is_pair_work',
        'ai_evaluation_enabled',
        'passage',
        'image',
    ];

    protected $casts = [
        'discussion_points' => 'array',
        'min_duration' => 'integer',
        'max_duration' => 'integer',
        'is_pair_work' => 'boolean',
        'ai_evaluation_enabled' => 'boolean',
    ];

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    public function submissions()
    {
        return $this->hasMany(SpeakingSubmission::class);
    }

    /**
     * Relasi lama masih dipertahankan untuk kompatibilitas.
     */
    public function questions()
    {
        return $this->hasMany(
            SpeakingQuestion::class,
            'speaking_material_id'
        );
    }
}