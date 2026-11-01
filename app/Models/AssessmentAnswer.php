<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssessmentAnswer extends Model
{
    protected $fillable = [
        'assessment_submission_id',
        'question_type',
        'question_id',
        'answer',
        'selected_option',
        'is_correct',
        'orientation_score',
        'complication_score',
        'resolution_score',
        'organization_score',
        'mechanics_score',
        'details_score',
        'fluency_score',
        'pronunciation_score',
        'vocabulary_score',
        'grammar_score',
        'score',
        'max_score',
        'feedback',
    ];

    public function submission()
    {
        return $this->belongsTo(AssessmentSubmission::class);
    }
}