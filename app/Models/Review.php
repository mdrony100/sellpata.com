<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = ['author', 'brand', 'body', 'rating', 'is_published'];

    protected $casts = ['is_published' => 'boolean'];
}
