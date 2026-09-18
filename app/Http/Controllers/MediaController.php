<?php

namespace App\Http\Controllers;

use App\Models\OpportunityMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * I media non sono mai pubblici: l'URL è firmato e a scadenza, e qui si riverifica
 * comunque il diritto di vedere l'opportunità collegata.
 */
class MediaController extends Controller
{
    public function show(Request $request, OpportunityMedia $media)
    {
        $this->authorizeMedia($request, $media);

        abort_unless(Storage::disk($media->disk)->exists($media->path), 404);

        return Storage::disk($media->disk)->response($media->path, null, [
            'Content-Type' => $media->mime,
            'Cache-Control' => 'private, max-age=600',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Disposition' => 'inline',
        ]);
    }

    public function poster(Request $request, OpportunityMedia $media)
    {
        $this->authorizeMedia($request, $media);

        abort_unless($media->poster_path && Storage::disk($media->disk)->exists($media->poster_path), 404);

        return Storage::disk($media->disk)->response($media->poster_path, null, [
            'Cache-Control' => 'private, max-age=600',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function authorizeMedia(Request $request, OpportunityMedia $media): void
    {
        abort_unless($request->user()?->can('view', $media->opportunity), 403);
    }
}
