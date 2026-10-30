<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageRedirect extends Model
{
    protected $fillable = ['store_id', 'slug', 'page_id'];
}
