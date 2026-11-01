<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WritingSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'writing_material_id',
        'answer',

        'orientation_score',
        'complication_score',
        'resolution_score',
        'organization_score',
        'mechanics_score',

        'final_score',
        'feedback',
    ];

    // protected $casts = [
    //     'submitted_at' => 'datetime',
    // ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function material()
    {
        return $this->belongsTo(
            WritingMaterial::class,
            'writing_material_id'
        );
    }
}