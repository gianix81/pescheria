<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Log immutabile: si può solo inserire e leggere.
 * Qualsiasi tentativo di update o delete solleva un'eccezione.
 */
class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'action', 'auditable_type', 'auditable_id',
        'payload', 'ip_address', 'user_agent', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function () {
            throw new \RuntimeException('Gli audit log non possono essere modificati.');
        });

        static::deleting(function () {
            throw new \RuntimeException('Gli audit log non possono essere eliminati.');
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
