<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'store_id', 'page_id', 'reference', 'customer_name', 'customer_phone', 'address', 'city', 'area',
        'note', 'subtotal', 'discount', 'delivery_charge', 'total', 'payment_method', 'payment_status',
        'status', 'snapshot', 'ip', 'paid_at',
    ];

    protected $casts = ['snapshot' => 'array', 'paid_at' => 'datetime'];

    public const STATUSES = ['new', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'returned'];
    public const PAYMENT_STATUSES = ['unpaid', 'paid', 'refunded'];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function invoice(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
