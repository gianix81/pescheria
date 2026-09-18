<?php

namespace App\Jobs;

use App\Models\OpportunityMedia;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Punto di estensione per la scansione antivirus asincrona (assunzione A12).
 *
 * L'MVP non include un motore antivirus: qui va innestata la chiamata a ClamAV
 * (clamdscan sul file temporaneo) o a un servizio esterno. Finché
 * MEDIA_ANTIVIRUS_ENABLED è false il job non viene nemmeno accodato.
 */
class ScanUploadedMedia implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $mediaId) {}

    public function handle(): void
    {
        $media = OpportunityMedia::find($this->mediaId);

        if (! $media) {
            return;
        }

        Log::info('Scansione antivirus da implementare', [
            'media_id' => $media->id,
            'disk' => $media->disk,
            'path' => $media->path,
        ]);

        // TODO produzione: eseguire la scansione e impostare CLEAN / INFECTED.
        $media->forceFill(['scan_status' => 'CLEAN'])->save();
    }
}
