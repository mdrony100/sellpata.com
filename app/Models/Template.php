<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Template extends Model
{
    protected $fillable = [
        'category_id', 'brand', 'headline', 'subline', 'product_name', 'price',
        'old_price', 'badge', 'art', 'palette', 'sort_order', 'is_published',
    ];

    protected $casts = [
        'palette' => 'array',
        'is_published' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
