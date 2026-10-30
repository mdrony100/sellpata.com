<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Benefit extends Model
{
    protected $fillable = ['title', 'body', 'icon', 'feature_key', 'sort_order', 'is_published'];

    protected $casts = ['is_published' => 'boolean'];
}
