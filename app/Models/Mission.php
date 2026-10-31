<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mission extends Model
{
    protected $fillable = [
        'title',
        'category',
        'difficulty',
        'reward_xp',
        'description',
        'deadline',
        'status'
    ];
}