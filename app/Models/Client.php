<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = ['brand', 'category', 'url', 'image_path', 'sort_order', 'is_published'];

    protected $casts = ['is_published' => 'boolean'];
}
