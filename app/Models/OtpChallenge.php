<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OtpChallenge extends Model
{
    protected $fillable = [
        'phone', 'purpose', 'code_hash', 'draft_id', 'anonymous_identity_id',
        'attempts', 'max_attempts', 'sends', 'expires_at', 'consumed_at', 'last_sent_at', 'ip',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'consumed_at' => 'datetime',
        'last_sent_at' => 'datetime',
        'attempts' => 'integer',
        'max_attempts' => 'integer',
        'sends' => 'integer',
    ];

    public function draft(): BelongsTo
    {
        return $this->belongsTo(Draft::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isUsable(): bool
    {
        return $this->consumed_at === null
            && ! $this->isExpired()
            && $this->attempts < $this->max_attempts;
    }
}
