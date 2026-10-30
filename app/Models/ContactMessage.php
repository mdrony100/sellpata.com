<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'page_url',
        'message',
        'ip_address',
        'user_agent',
    ];
}
