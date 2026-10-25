<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Resource extends Model
{
    protected $fillable = [
        'type', 'title', 'description', 'language', 'duration', 'media_file', 'media_url',
        'thumbnail', 'allow_download', 'published_at',
    ];

    protected function casts(): array
    {
        return ['published_at' => 'datetime', 'allow_download' => 'boolean'];
    }

    /** slug => [singular, plural, icon] */
    public const TYPES = [
        'radio' => ['Radio programme', 'Radio programmes', 'radio'],
        'jingle' => ['Audio jingle', 'Audio jingles', 'music'],
        'video' => ['Video', 'Videos', 'video'],
    ];

    public const LANGUAGES = ['Hausa', 'English', 'Hausa and English', 'Pidgin', 'Fulfulde', 'Other'];

    public const AUDIO_EXTENSIONS = ['mp3', 'm4a', 'aac', 'wav', 'ogg', 'oga'];

    public const VIDEO_EXTENSIONS = ['mp4', 'webm', 'mov', 'm4v'];

    public static function typeOptions(bool $plural = false): array
    {
        return collect(self::TYPES)->map(fn ($t) => $t[$plural ? 1 : 0])->all();
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now())->orderByDesc('published_at');
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type][0] ?? ucfirst($this->type);
    }

    public function typeIcon(): string
    {
        return self::TYPES[$this->type][2] ?? 'document';
    }

    public function isVideo(): bool
    {
        return $this->type === 'video';
    }

    public function fileUrl(): ?string
    {
        return $this->media_file ? Storage::disk('public')->url($this->media_file) : null;
    }

    public function thumbnailUrl(): ?string
    {
        if ($this->thumbnail) {
            return Storage::disk('public')->url($this->thumbnail);
        }

        return ($id = $this->youtubeId()) ? 'https://i.ytimg.com/vi/'.$id.'/hqdefault.jpg' : null;
    }

    public function youtubeId(): ?string
    {
        if (\App\Support\SafeUrl::web($this->media_url) === null) {
            return null;
        }
        $pattern = '~(?:youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/)|youtu\.be/)([A-Za-z0-9_-]{11})~';

        return preg_match($pattern, $this->media_url, $m) ? $m[1] : null;
    }

    public function vimeoId(): ?string
    {
        return \App\Support\SafeUrl::web($this->media_url) && preg_match('~vimeo\.com/(?:video/)?(\d+)~', $this->media_url, $m) ? $m[1] : null;
    }

    /** Address for an <iframe> player, when the link is YouTube or Vimeo. */
    public function embedUrl(): ?string
    {
        if ($id = $this->youtubeId()) {
            return 'https://www.youtube-nocookie.com/embed/'.$id.'?rel=0';
        }
        if ($id = $this->vimeoId()) {
            return 'https://player.vimeo.com/video/'.$id.'?dnt=1';
        }

        return null;
    }

    /** Address the browser can play directly with <audio> or <video>. */
    public function playableUrl(): ?string
    {
        if ($url = $this->fileUrl()) {
            return $url;
        }
        if (\App\Support\SafeUrl::web($this->media_url) === null) {
            return null;
        }
        $path = strtolower((string) parse_url((string) $this->media_url, PHP_URL_PATH));
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $allowed = $this->isVideo() ? self::VIDEO_EXTENSIONS : self::AUDIO_EXTENSIONS;

        return in_array($ext, $allowed, true) ? $this->media_url : null;
    }

    /** A link to listen or watch elsewhere, when we cannot play it on the page. */
    public function externalUrl(): ?string
    {
        $url = \App\Support\SafeUrl::web($this->media_url);

        return $url && ! $this->embedUrl() && ! $this->playableUrl() ? $url : null;
    }

    public function anchor(): string
    {
        return 'resource-'.$this->id;
    }
}
