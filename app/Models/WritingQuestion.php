<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WritingQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'lesson_id',
        'writing_material_id',
        'question',
        'image',
        'sample_answer',
    ];

    public function material()
    {
        return $this->belongsTo(
            WritingMaterial::class,
            'writing_material_id'
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
