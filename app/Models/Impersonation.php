<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Impersonation extends Model
{
    protected $fillable = ['admin_id', 'user_id', 'reason', 'started_at', 'ended_at', 'actions'];

    protected $casts = ['started_at' => 'datetime', 'ended_at' => 'datetime', 'actions' => 'array'];
}
