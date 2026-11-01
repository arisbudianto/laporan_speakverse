<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ListeningMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'lesson_id',
        'title',
        'passage',
        'audio_file',
        'instruction',
    ];

    /**
     * Lesson pemilik listening material.
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(
            Lesson::class,
            'lesson_id'
        );
    }

    /**
     * Daftar pertanyaan pada listening material.
     */
    public function questions(): HasMany
    {
        return $this->hasMany(
            ListeningQuestion::class,
            'listening_material_id'
        );
    }
}