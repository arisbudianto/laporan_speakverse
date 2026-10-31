<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Badge extends Model
{
    /**
     * Atribut yang boleh diisi melalui mass assignment.
     */
    protected $fillable = [
        'code',
        'name',
        'description',
        'icon',
        'category',
        'sort_order',
        'is_active',
    ];

    /**
     * Cast atribut model.
     */
    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * User yang sudah memperoleh badge ini.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'user_badges'
        )
            ->withPivot('unlocked_at')
            ->withTimestamps();
    }
}