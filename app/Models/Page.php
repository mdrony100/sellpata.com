<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Page extends Model
{
    protected $fillable = [
        'store_id', 'draft_id', 'template_id', 'published_template_id', 'title', 'slug', 'status', 'is_primary',
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

    public function publishedTemplate(): BelongsTo
    {
        return $this->belongsTo(BuilderTemplate::class, 'published_template_id');
    }

    /** Visitors keep the theme that was live at publish time until the next publish. */
    public function liveDesign(): string
    {
        if ($this->status === 'published' && $this->published_template_id) {
            $this->loadMissing('publishedTemplate');

            if ($this->publishedTemplate) {
                return $this->publishedTemplate->design;
            }
        }

        $this->loadMissing('template');

        return $this->template?->design ?? 'spotlight';
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
