<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    protected $fillable = [
        'title',
        'subtitle',
        'description',
        'order_number',
        'type',
        'status',
    ];

    /**
     * Seluruh lesson dalam unit.
     */
    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }

    /**
     * Seluruh progress pembelajaran dalam unit.
     */
    public function lessonProgress(): HasMany
    {
        return $this->hasMany(
            UserLessonProgress::class,
            'unit_id'
        );
    }

    /**
     * Seluruh submission assessment dalam unit.
     */
    public function assessmentSubmissions(): HasMany
    {
        return $this->hasMany(
            AssessmentSubmission::class,
            'unit_id'
        );
    }
}