<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLessonProgress extends Model
{
    protected $table = 'user_lesson_progress';

    protected $fillable = [
        'user_id',
        'unit_id',
        'lesson_id',
        'skill_type',
        'status',
        'score',
        'completed_at',
    ];

    protected $casts = [
        'score' => 'integer',
        'completed_at' => 'datetime',
    ];

    /**
     * Pengguna pemilik progress.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Unit yang sedang atau sudah dikerjakan.
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Lesson yang sedang atau sudah dikerjakan.
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}