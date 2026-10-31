<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WritingMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'lesson_id',
        'title',
        'instruction',
        'passage',
        'image',
    ];

    public function questions()
    {
        return $this->hasMany(
            WritingQuestion::class
        );
    }

    public function lesson()
    {
        return $this->belongsTo(
            Lesson::class
        );
    }
}