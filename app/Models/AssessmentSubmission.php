<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssessmentSubmission extends Model
{
    protected $fillable = [
        'user_id',
        'unit_id',
        'lesson_id',
        'quiz_session_id',
        'type',
        'skill',
        'final_score',
        'criteria_scores',
        'status',
        'feedback',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'criteria_scores' => 'array',
    ];

    public function answers()
    {
        return $this->hasMany(AssessmentAnswer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }
}