<?php

namespace App\Models;

use App\Enums\ResponseStatus;
use Database\Factories\ResponseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Response extends Model
{
    /** @use HasFactory<ResponseFactory> */
    use HasFactory;

    protected $fillable = [
        'opportunity_id', 'store_id', 'status', 'packages', 'kg', 'committed_packages',
        'refusal_reason', 'submitted_at', 'last_actor_id',
        'reopened_until', 'reopened_by', 'reopen_reason',
    ];

    protected function casts(): array
    {
        return [
            'status' => ResponseStatus::class,
            'packages' => 'integer',
            'committed_packages' => 'integer',
            'kg' => 'decimal:3',
            'submitted_at' => 'datetime',
            'reopened_until' => 'datetime',
        ];
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(ResponseRevision::class)->latest();
    }

    public function lastActor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_actor_id');
    }

    public function scopeSubmitted(Builder $query): Builder
    {
        return $query->whereIn('status', [ResponseStatus::INVIATA_ACQUISTO, ResponseStatus::INVIATA_RIFIUTO]);
    }

    public function isSubmitted(): bool
    {
        return $this->status->isSubmitted();
    }

    /** Riapertura concessa dal Tecnico ancora valida. */
    public function hasActiveReopening(): bool
    {
        return $this->reopened_until !== null && $this->reopened_until->greaterThan(now());
    }
}
