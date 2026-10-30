<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Step extends Model
{
    protected $fillable = ['number_label', 'title', 'body', 'sort_order', 'is_published'];

    protected $casts = ['is_published' => 'boolean'];
}
