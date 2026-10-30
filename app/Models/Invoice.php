<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    protected $fillable = [
        'number', 'type', 'store_id', 'order_id', 'subscription_id', 'amount', 'currency',
        'status', 'payment_reference', 'seller_snapshot', 'buyer_snapshot', 'issued_at',
    ];

    protected $casts = ['seller_snapshot' => 'array', 'buyer_snapshot' => 'array', 'issued_at' => 'datetime'];

    public const ORDER = 'order';
    public const SUBSCRIPTION = 'subscription';

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
