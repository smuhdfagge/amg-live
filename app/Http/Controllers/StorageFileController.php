<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * Serves uploaded files (news photos, programme photos, resources) when the
 * web server cannot find them itself: the public/storage shortcut is missing,
 * or the host does not allow shortcuts (symlinks). When the shortcut works,
 * the web server sends the file directly and this is never called.
 *
 * The type is decided by the file's extension from a fixed list, never by
 * looking inside the file. A web page disguised as "song.mp3" is therefore
 * sent as audio, and anything not on the list is downloaded, not displayed.
 */
class StorageFileController extends Controller
{
    private const TYPES = [
        'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif', 'webp' => 'image/webp',
        'mp3' => 'audio/mpeg', 'm4a' => 'audio/mp4', 'aac' => 'audio/aac', 'wav' => 'audio/wav', 'ogg' => 'audio/ogg', 'oga' => 'audio/ogg',
        'mp4' => 'video/mp4', 'm4v' => 'video/mp4', 'webm' => 'video/webm', 'mov' => 'video/quicktime',
        'pdf' => 'application/pdf',
    ];

    public function __invoke(string $path): BinaryFileResponse
    {
        $disk = Storage::disk('public');

        abort_if(str_contains($path, '..') || str_contains($path, "\0") || str_starts_with(basename($path), '.'), 404);
        abort_unless($disk->exists($path) && is_file($disk->path($path)), 404);

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $type = self::TYPES[$ext] ?? null;

        $response = response()->file($disk->path($path), [
            'Content-Type' => $type ?? 'application/octet-stream',
            'Cache-Control' => 'public, max-age=2592000',
            'X-Content-Type-Options' => 'nosniff',
            // Even if a browser did treat this as a page, nothing in it could run.
            'Content-Security-Policy' => "default-src 'none'; img-src 'self'; media-src 'self'; style-src 'unsafe-inline'; sandbox",
        ]);

        if (! $type) {
            $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, basename($path));
        }

        return $response;
    }
}
