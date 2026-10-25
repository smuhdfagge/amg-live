<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Resource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ResourceController extends Controller
{
    public function index(Request $request): View
    {
        $type = array_key_exists($request->query('type'), Resource::TYPES) ? $request->query('type') : null;

        return view('admin.resources.index', [
            'resources' => Resource::when($type, fn ($q) => $q->where('type', $type))
                ->orderByDesc('published_at')->orderByDesc('created_at')->paginate(20)->withQueryString(),
            'type' => $type,
            'counts' => Resource::selectRaw('type, count(*) as total')->groupBy('type')->pluck('total', 'type'),
        ]);
    }

    public function create(Request $request): View
    {
        $type = array_key_exists($request->query('type'), Resource::TYPES) ? $request->query('type') : 'radio';

        return view('admin.resources.form', [
            'resource' => new Resource(['type' => $type, 'published_at' => now(), 'allow_download' => true]),
            'limits' => self::uploadLimits(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $resource = Resource::create($this->validated($request));

        return redirect()->route('admin.resources.index')->with('status', '"'.$resource->title.'" was saved.');
    }

    public function edit(Resource $resource): View
    {
        return view('admin.resources.form', ['resource' => $resource, 'limits' => self::uploadLimits()]);
    }

    public function update(Request $request, Resource $resource): RedirectResponse
    {
        $resource->update($this->validated($request, $resource));

        return redirect()->route('admin.resources.index')->with('status', '"'.$resource->title.'" was updated.');
    }

    public function destroy(Resource $resource): RedirectResponse
    {
        Storage::disk('public')->delete(array_filter([$resource->media_file, $resource->thumbnail]));
        $resource->delete();

        return redirect()->route('admin.resources.index')->with('status', 'The resource was deleted.');
    }

    /** Upload limits in MB: the lower of our setting and what PHP on this server accepts. */
    public static function uploadLimits(): array
    {
        $php = min(self::iniBytes(ini_get('upload_max_filesize')), self::iniBytes(ini_get('post_max_size'))) / 1048576;

        return [
            'audio' => (int) floor(min(config('amg.uploads.audio_mb'), $php ?: PHP_INT_MAX)),
            'video' => (int) floor(min(config('amg.uploads.video_mb'), $php ?: PHP_INT_MAX)),
            'php' => (int) floor($php),
        ];
    }

    /**
     * Look inside the file, not just at its name: a web page or script saved as
     * "song.mp3" is refused, so it can never be served from this site.
     */
    public static function notAWebPage(): \Closure
    {
        return function (string $attribute, mixed $file, \Closure $fail) {
            if (! $file instanceof \Illuminate\Http\UploadedFile || ! $file->isValid()) {
                return;
            }
            $mime = strtolower((string) (new \finfo(FILEINFO_MIME_TYPE))->file($file->getRealPath()));
            $head = strtolower((string) file_get_contents($file->getRealPath(), false, null, 0, 1024));
            $looksLikePage = str_starts_with($mime, 'text/') || preg_match('/html|xml|javascript|ecmascript|php|svg|x-sh/', $mime)
                || preg_match('/<\s*(script|html|body|svg|iframe|\?php)\b/', $head);
            if ($looksLikePage) {
                $fail('This file is not a real audio or video file. Choose the original recording.');
            }
        };
    }

    private static function iniBytes(string|false $value): int
    {
        $value = trim((string) $value);
        if ($value === '' || $value === '0' || $value === '-1') {
            return 0;
        }
        $n = (int) $value;

        return match (strtolower(substr($value, -1))) {
            'g' => $n * 1073741824,
            'm' => $n * 1048576,
            'k' => $n * 1024,
            default => $n,
        };
    }

    private function validated(Request $request, ?Resource $resource = null): array
    {
        $type = $request->input('type');
        $isVideo = $type === 'video';
        $exts = $isVideo ? Resource::VIDEO_EXTENSIONS : Resource::AUDIO_EXTENSIONS;
        $limitMb = self::uploadLimits()[$isVideo ? 'video' : 'audio'];

        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(Resource::TYPES))],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:2000'],
            'language' => ['nullable', Rule::in(Resource::LANGUAGES)],
            'duration' => ['nullable', 'string', 'max:20'],
            'media_url' => ['nullable', 'max:500', ...\App\Support\SafeUrl::rule()],
            'media' => ['nullable', 'file', 'extensions:'.implode(',', $exts), 'max:'.($limitMb * 1024), self::notAWebPage()],
            'remove_media' => ['nullable', 'boolean'],
            'thumb' => ['nullable', 'image', 'max:4096'],
            'remove_thumb' => ['nullable', 'boolean'],
            'allow_download' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
        ], [
            'media.extensions' => 'Upload a '.implode(', ', $exts).' file.',
            'media.max' => 'The file is too big. The most this server accepts is '.$limitMb.' MB. For bigger videos, upload to YouTube and paste the link instead.',
            'media.uploaded' => 'The file could not be uploaded. It may be bigger than the server allows ('.$limitMb.' MB).',
        ]);

        $disk = Storage::disk('public');
        $data['allow_download'] = $request->boolean('allow_download');

        // Media file: remove, replace or keep.
        $currentFile = $resource?->media_file;
        if ($request->boolean('remove_media') && $currentFile) {
            $disk->delete($currentFile);
            $currentFile = null;
            $data['media_file'] = null;
        }
        if ($request->hasFile('media')) {
            if ($currentFile) {
                $disk->delete($currentFile);
            }
            $currentFile = $request->file('media')->store('resources/'.$data['type'], 'public');
            $data['media_file'] = $currentFile;
        }

        if (blank($currentFile) && blank($data['media_url'] ?? null)) {
            throw ValidationException::withMessages([
                'media' => $isVideo
                    ? 'Upload the video, or paste a YouTube or Vimeo link.'
                    : 'Upload the audio file, or paste a link to where it can be heard.',
            ]);
        }

        // Thumbnail: remove, replace or keep.
        if ($request->boolean('remove_thumb') && $resource?->thumbnail) {
            $disk->delete($resource->thumbnail);
            $data['thumbnail'] = null;
        }
        if ($request->hasFile('thumb')) {
            if ($resource?->thumbnail) {
                $disk->delete($resource->thumbnail);
            }
            $data['thumbnail'] = $request->file('thumb')->store('resources/thumbnails', 'public');
        }

        unset($data['media'], $data['remove_media'], $data['thumb'], $data['remove_thumb']);

        return $data;
    }
}
