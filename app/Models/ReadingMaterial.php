<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReadingMaterial extends Model
{
    protected $fillable = [
        'lesson_id',
        'title',
        'passage',
        'instruction',
        'image',
    ];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(
            Lesson::class,
            'lesson_id'
        );
    }

    public function questions(): HasMany
    {
        return $this->hasMany(
            ReadingQuestion::class,
            'reading_material_id'
        );
    }
}