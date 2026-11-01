<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GamificationTransaction extends Model
{
    /**
     * Atribut yang boleh diisi melalui mass assignment.
     */
    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'reason',
        'event_key',
        'metadata',
    ];

    /**
     * Cast atribut model.
     */
    protected $casts = [
        'amount' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * User pemilik transaksi gamifikasi.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class
        );
    }
}