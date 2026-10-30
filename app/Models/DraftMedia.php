<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DraftMedia extends Model
{
    protected $table = 'draft_media';

    protected $fillable = ['draft_id', 'field', 'path', 'original_name', 'mime', 'size'];

    public function draft(): BelongsTo
    {
        return $this->belongsTo(Draft::class);
    }
}
