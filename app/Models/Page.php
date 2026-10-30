<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Page extends Model
{
    protected $fillable = [
        'store_id', 'draft_id', 'template_id', 'title', 'slug', 'status', 'is_primary',
        'content', 'published_content', 'revision', 'published_at',
    ];

    protected $casts = [
        'content' => 'array',
        'published_content' => 'array',
        'is_primary' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(BuilderTemplate::class, 'template_id');
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    /** Visitors only ever see the published snapshot, never working edits. */
    public function liveContent(): ?array
    {
        return $this->published_content ?? null;
    }
}
