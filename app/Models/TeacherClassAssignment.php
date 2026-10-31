<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherClassAssignment extends Model
{
    protected $fillable = [
        'user_id',
        'school',
        'major',
        'grade',
        'parallel',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function classLabel(): string
    {
        return trim($this->grade.' '.$this->parallel);
    }
}
