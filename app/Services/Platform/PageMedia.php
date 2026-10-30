<?php

namespace App\Services\Platform;

use App\Models\Draft;
use Illuminate\Support\Facades\Storage;

/**
 * Copies a draft's private uploads onto the public page so published
 * images keep working after the short-lived preview URL expires.
 */
class PageMedia
{
    public static function mergeIntoContent(array $content, ?Draft $draft, int $pageId): array
    {
        if ($draft === null) {
            return $content;
        }

        $draft->loadMissing('media');

        foreach ($draft->media as $media) {
            if (! Storage::disk('local')->exists($media->path)) {
                continue;
            }

            $ext = strtolower(pathinfo($media->path, PATHINFO_EXTENSION) ?: 'jpg');
            if (! in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                continue;
            }

            $safe = trim(preg_replace('/[^a-z0-9._-]+/i', '-', (string) $media->field) ?? '', '-');
            if ($safe === '') {
                continue;
            }

            $relative = "media/pages/{$pageId}/{$safe}.{$ext}";
            $dest = public_path($relative);
            if (! is_dir(dirname($dest))) {
                mkdir(dirname($dest), 0755, true);
            }

            copy(Storage::disk('local')->path($media->path), $dest);
            data_set($content, $media->field, '/'.$relative);
        }

        return $content;
    }
}
