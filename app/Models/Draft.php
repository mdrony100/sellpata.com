<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Draft extends Model
{
    protected $fillable = [
        'public_id', 'builder_template_id', 'user_id', 'anonymous_identity_id',
        'title', 'content', 'revision', 'status', 'claimed_at', 'last_saved_at', 'page_id',
    ];

    protected $casts = [
        'content' => 'array',
        'revision' => 'integer',
        'claimed_at' => 'datetime',
        'last_saved_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $draft) {
            $draft->public_id ??= (string) Str::uuid();
        });
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(BuilderTemplate::class, 'builder_template_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function identity(): BelongsTo
    {
        return $this->belongsTo(AnonymousIdentity::class, 'anonymous_identity_id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(DraftMedia::class);
    }

    public function isClaimed(): bool
    {
        return $this->user_id !== null;
    }
}
