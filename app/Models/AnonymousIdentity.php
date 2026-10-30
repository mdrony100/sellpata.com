<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnonymousIdentity extends Model
{
    protected $fillable = ['token_hash', 'expires_at', 'last_seen_at'];

    protected $casts = ['expires_at' => 'datetime', 'last_seen_at' => 'datetime'];

    public function drafts(): HasMany
    {
        return $this->hasMany(Draft::class);
    }
}
