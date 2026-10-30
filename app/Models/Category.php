<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['slug', 'label', 'keywords', 'parent_id', 'sort_order', 'is_visible'];

    protected $casts = ['is_visible' => 'boolean'];

    public function templates(): HasMany
    {
        return $this->hasMany(Template::class);
    }

    public function builderTemplates(): HasMany
    {
        return $this->hasMany(BuilderTemplate::class);
    }

    /** Sub-categories shown as chips on the discovery page. */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }
}
