<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['slug', 'label', 'sort_order', 'is_visible'];

    protected $casts = ['is_visible' => 'boolean'];

    public function templates(): HasMany
    {
        return $this->hasMany(Template::class);
    }
}
