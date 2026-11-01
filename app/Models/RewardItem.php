<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class RewardItem extends Model
{
    /**
     * Atribut yang boleh diisi melalui mass assignment.
     */
    protected $fillable = [
        'code',
        'name',
        'description',
        'icon',
        'type',
        'price_coins',
        'metadata',
        'sort_order',
        'is_active',
    ];

    /**
     * Cast atribut model.
     */
    protected $casts = [
        'price_coins' => 'integer',
        'metadata' => 'array',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * User yang sudah memiliki reward ini.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'user_reward_items'
        )
            ->withPivot([
                'quantity',
                'acquired_at',
            ])
            ->withTimestamps();
    }
}