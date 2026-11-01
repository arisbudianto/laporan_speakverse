<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lesson extends Model
{
    protected $fillable = [
        'unit_id',
        'skill_type',
        'title',
        'description',
        'order_number',
        'status',
    ];

    /**
     * Unit pemilik lesson.
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Materi reading dalam lesson.
     */
    public function readingMaterials(): HasMany
    {
        return $this->hasMany(ReadingMaterial::class);
    }

    /**
     * Materi speaking dalam lesson.
     */
    public function speakingMaterials(): HasMany
    {
        return $this->hasMany(SpeakingMaterial::class);
    }

    /**
     * Materi writing dalam lesson.
     */
    public function writingMaterials(): HasMany
    {
        return $this->hasMany(WritingMaterial::class);
    }

    /**
     * Materi listening dalam lesson.
     */
    public function listeningMaterials(): HasMany
    {
        return $this->hasMany(ListeningMaterial::class);
    }

    /**
     * Pertanyaan reading langsung pada lesson.
     */
    public function readingQuestions(): HasMany
    {
        return $this->hasMany(ReadingQuestion::class, 'lesson_id')
            ->whereNull('reading_material_id');
    }

    /**
     * Pertanyaan listening langsung pada lesson.
     */
    public function listeningQuestions(): HasMany
    {
        return $this->hasMany(ListeningQuestion::class, 'lesson_id')
            ->whereNull('listening_material_id');
    }

    /**
     * Pertanyaan writing langsung pada lesson.
     */
    public function writingQuestions(): HasMany
    {
        return $this->hasMany(WritingQuestion::class, 'lesson_id')
            ->whereNull('writing_material_id');
    }

    /**
     * Pertanyaan speaking langsung pada lesson.
     */
    public function speakingQuestions(): HasMany
    {
        return $this->hasMany(SpeakingQuestion::class, 'lesson_id')
            ->whereNull('speaking_material_id');
    }

    /**
     * Progress pengguna untuk lesson ini.
     */
    public function progress(): HasMany
    {
        return $this->hasMany(
            UserLessonProgress::class,
            'lesson_id'
        );
    }
}