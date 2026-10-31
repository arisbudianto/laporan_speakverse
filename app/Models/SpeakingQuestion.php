<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpeakingQuestion extends Model
{
    protected $fillable = [
        'lesson_id',
        'speaking_material_id',
        'question',
        'image',
    ];

    public function material()
    {
        return $this->belongsTo(
            SpeakingMaterial::class,
            'speaking_material_id'
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