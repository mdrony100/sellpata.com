<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = ['actor_id', 'action', 'subject_type', 'subject_id', 'meta', 'ip'];

    protected $casts = ['meta' => 'array'];

    public static function record(string $action, ?Model $subject = null, array $meta = [], ?int $actorId = null): self
    {
        return static::create([
            'actor_id' => $actorId ?? auth()->id(),
            'action' => $action,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'meta' => $meta,
            'ip' => request()->ip(),
        ]);
    }
}
