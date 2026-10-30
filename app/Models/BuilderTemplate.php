<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BuilderTemplate extends Model
{
    protected $fillable = [
        'category_id', 'name', 'slug', 'design', 'theme_version', 'tagline', 'suitable_for',
        'content', 'schema', 'thumbnail', 'preview', 'is_published', 'sort_order',
    ];

    protected $casts = [
        'content' => 'array',
        'schema' => 'array',
        'suitable_for' => 'array',
        'is_published' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function drafts(): HasMany
    {
        return $this->hasMany(Draft::class);
    }
}
