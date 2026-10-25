<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Serves uploaded files (news photos, programme photos, resources) when the
 * web server cannot find them itself: the public/storage shortcut is missing,
 * or the host does not allow shortcuts (symlinks). When the shortcut works,
 * the web server sends the file directly and this is never called.
 */
class StorageFileController extends Controller
{
    public function __invoke(string $path): BinaryFileResponse
    {
        $disk = Storage::disk('public');

        abort_if(str_contains($path, '..') || str_starts_with(basename($path), '.'), 404);
        abort_unless($disk->exists($path) && is_file($disk->path($path)), 404);

        return response()->file($disk->path($path), [
            'Cache-Control' => 'public, max-age=2592000',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
