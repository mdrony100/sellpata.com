<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Store extends Model
{
    protected $fillable = [
        'user_id', 'username', 'username_lower', 'display_name', 'logo_path', 'contact_phone',
        'contact_email', 'delivery_charge_enabled', 'delivery_charge', 'status', 'suspended_reason',
        'username_changed_at',
    ];

    protected $casts = ['delivery_charge_enabled' => 'boolean', 'username_changed_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pages(): HasMany
    {
        return $this->hasMany(Page::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription(): ?Subscription
    {
        return $this->subscriptions()
            ->where('status', 'active')
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()))
            ->latest('started_at')
            ->first();
    }

    public function plan(): Plan
    {
        return $this->activeSubscription()?->plan ?? Plan::free();
    }

    public function primaryPage(): ?Page
    {
        return $this->pages()->where('is_primary', true)->where('status', 'published')->first();
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function publicUrl(?Page $page = null): string
    {
        $base = \App\Services\Platform\PlatformSettings::get('platform.base_domain', 'sellpata.com');
        $host = $this->username_lower.'.'.$base;

        return $page && ! $page->is_primary
            ? "https://{$host}/{$page->slug}"
            : "https://{$host}";
    }
}
