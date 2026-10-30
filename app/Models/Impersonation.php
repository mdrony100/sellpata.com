<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Impersonation extends Model
{
    protected $fillable = ['admin_id', 'user_id', 'reason', 'started_at', 'ended_at', 'actions'];

    public function admin(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected $casts = ['started_at' => 'datetime', 'ended_at' => 'datetime', 'actions' => 'array'];
}
