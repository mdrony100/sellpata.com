<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    protected $fillable = ['store_id', 'plan_id', 'status', 'source', 'billing_period', 'started_at', 'ends_at', 'assigned_by', 'note'];

    protected $casts = ['started_at' => 'datetime', 'ends_at' => 'datetime'];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
