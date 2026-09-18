<?php

namespace App\Services;

use App\Enums\MediaType;
use App\Exceptions\DomainException;
use App\Jobs\ScanUploadedMedia;
use App\Models\Opportunity;
use App\Models\OpportunityMedia;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Upload dei media dell'opportunità.
 *
 * Nome file sempre casuale e non eseguibile, MIME verificato lato server
 * (non ci si fida dell'estensione dichiarata dal client), disco privato.
 */
class MediaService
{
    public function store(Opportunity $opportunity, UploadedFile $file, User $user): OpportunityMedia
    {
        $mime = $file->getMimeType() ?: $file->getClientMimeType();
        $type = $this->detectType($mime);
        $this->assertSize($file, $type);

        $disk = config('pescheria.media.disk');
        $extension = $this->safeExtension($file, $mime);
        $name = Str::uuid()->toString().'.'.$extension;
        $directory = "opportunita/{$opportunity->id}";
        $path = "{$directory}/{$name}";

        // storeAs() funziona sia con un file caricato sul server sia con un file
        // temporaneo già su S3 (upload diretto dal browser): in quel caso Livewire
        // esegue una copia S3→S3 senza farlo passare dall'applicazione. È ciò che
        // permette video da 100 MB anche dove la richiesta HTTP è limitata.
        $file->storeAs($directory, $name, ['disk' => $disk]);

        $media = $opportunity->media()->create([
            'type' => $type,
            'disk' => $disk,
            'path' => $path,
            'original_name' => substr((string) $file->getClientOriginalName(), 0, 190),
            'mime' => $mime,
            'size' => $file->getSize(),
            'sort_order' => (int) $opportunity->media()->max('sort_order') + 1,
            'scan_status' => config('pescheria.media.antivirus_enabled') ? 'PENDING' : 'SKIPPED',
            'uploaded_by' => $user->id,
        ]);

        if (config('pescheria.media.antivirus_enabled')) {
            ScanUploadedMedia::dispatch($media->id);
        }

        return $media;
    }

    public function delete(OpportunityMedia $media): void
    {
        Storage::disk($media->disk)->delete($media->path);

        if ($media->poster_path) {
            Storage::disk($media->disk)->delete($media->poster_path);
        }

        $media->delete();
    }

    public function detectType(string $mime): MediaType
    {
        if (in_array($mime, config('pescheria.media.image_mimes'), true)) {
            return MediaType::IMAGE;
        }

        if (in_array($mime, config('pescheria.media.video_mimes'), true)) {
            return MediaType::VIDEO;
        }

        throw new DomainException("Tipo di file non ammesso ({$mime}). Sono accettate foto JPEG/PNG/WebP e video MP4/MOV/WebM.");
    }

    private function assertSize(UploadedFile $file, MediaType $type): void
    {
        $maxMb = $type === MediaType::IMAGE
            ? config('pescheria.media.max_image_mb')
            : config('pescheria.media.max_video_mb');

        if ($file->getSize() > $maxMb * 1024 * 1024) {
            throw new DomainException('File troppo grande: il limite per '.strtolower($type->label())." è di {$maxMb} MB.");
        }
    }

    /** Estensione ricavata dal MIME verificato, mai dall'input dell'utente. */
    private function safeExtension(UploadedFile $file, string $mime): string
    {
        return match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/heic', 'image/heif' => 'heic',
            'video/mp4' => 'mp4',
            'video/quicktime' => 'mov',
            'video/webm' => 'webm',
            'video/x-m4v' => 'm4v',
            default => 'bin',
        };
    }
}
