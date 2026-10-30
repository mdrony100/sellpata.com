<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = ['slug', 'name', 'price_monthly', 'price_yearly', 'currency', 'max_pages', 'features', 'is_active', 'sort_order'];

    protected $casts = ['features' => 'array', 'is_active' => 'boolean'];

    public const FREE = 'free';

    public static function free(): self
    {
        return static::where('slug', self::FREE)->firstOrFail();
    }

    public function has(string $feature): bool
    {
        return (bool) ($this->features[$feature] ?? false);
    }

    /** Paid plans are only sellable once an administrator sets a price. */
    public function isPurchasable(): bool
    {
        return $this->slug !== self::FREE && $this->price_monthly !== null;
    }
}
