<?php

namespace App\Models;

use App\Enums\MediaType;
use Database\Factories\OpportunityMediaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class OpportunityMedia extends Model
{
    /** @use HasFactory<OpportunityMediaFactory> */
    use HasFactory;

    protected $table = 'opportunity_media';

    protected $fillable = [
        'opportunity_id', 'type', 'disk', 'path', 'poster_path',
        'original_name', 'mime', 'size', 'sort_order', 'scan_status', 'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'type' => MediaType::class,
            'size' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }

    /**
     * Il file è ancora al suo posto?
     *
     * Su una piattaforma senza disco persistente il contenuto sparisce a ogni
     * pubblicazione mentre la riga in tabella resta: senza questo controllo si
     * vedrebbero immagini rotte e il motivo non sarebbe chiaro a nessuno.
     */
    public function esiste(): bool
    {
        return Storage::disk($this->disk)->exists($this->path);
    }

    public function isVideo(): bool
    {
        return $this->type === MediaType::VIDEO;
    }

    /** URL firmato e temporaneo: i media non sono mai pubblici. */
    public function temporaryUrl(): string
    {
        return URL::temporarySignedRoute(
            'media.show',
            now()->addMinutes(config('pescheria.media.signed_url_minutes')),
            ['media' => $this->id],
        );
    }

    public function posterUrl(): ?string
    {
        if (! $this->poster_path) {
            return null;
        }

        return URL::temporarySignedRoute(
            'media.poster',
            now()->addMinutes(config('pescheria.media.signed_url_minutes')),
            ['media' => $this->id],
        );
    }
}
