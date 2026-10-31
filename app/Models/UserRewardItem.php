<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class UserRewardItem extends Pivot
{
    /**
     * Nama tabel pivot reward user.
     */
    protected $table = 'user_reward_items';

    /**
     * Atribut yang boleh diisi melalui mass assignment.
     */
    protected $fillable = [
        'user_id',
        'reward_item_id',
        'quantity',
        'acquired_at',
    ];

    /**
     * Cast atribut model.
     */
    protected $casts = [
        'quantity' => 'integer',
        'acquired_at' => 'datetime',
    ];

    /**
     * User pemilik reward.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class
        );
    }

    /**
     * Reward item yang dimiliki user.
     */
    public function rewardItem(): BelongsTo
    {
        return $this->belongsTo(
            RewardItem::class
        );
    }
}