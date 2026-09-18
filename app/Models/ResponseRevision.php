<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResponseRevision extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'response_id', 'user_id', 'action', 'from_status', 'to_status',
        'from_packages', 'to_packages', 'reason', 'ip_address', 'created_at',
    ];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    public function response(): BelongsTo
    {
        return $this->belongsTo(Response::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
