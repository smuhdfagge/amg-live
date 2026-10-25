<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Post extends Model
{
    protected $fillable = [
        'slug', 'title', 'category', 'excerpt', 'body', 'cover_image', 'gallery', 'external_url', 'source', 'published_at',
        'legacy_id', 'legacy_path',
    ];

    protected function casts(): array
    {
        return ['published_at' => 'datetime', 'gallery' => 'array'];
    }

    public const CATEGORIES = ['News', 'In the press', 'Field update', 'Announcement'];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now())->orderByDesc('published_at');
    }

    public function isExternal(): bool
    {
        return filled($this->external_url) && blank($this->body);
    }

    public function url(): string
    {
        return $this->isExternal() ? $this->external_url : route('news.show', $this);
    }

    public function coverUrl(): ?string
    {
        return $this->cover_image ? Storage::disk('public')->url($this->cover_image) : null;
    }

    /** @return array<int, string> addresses of the extra photos */
    public function galleryUrls(): array
    {
        return array_map(fn ($path) => Storage::disk('public')->url($path), $this->gallery ?? []);
    }

    /** Short text for news cards: the summary, or the start of the story. */
    public function teaser(int $length = 160): ?string
    {
        if (filled($this->excerpt)) {
            return $this->excerpt;
        }
        $plain = trim(preg_replace('/\s+/', ' ', preg_replace('/^(## |- )/m', '', (string) $this->body)));

        return $plain === '' ? null : \Illuminate\Support\Str::limit($plain, $length);
    }

    public function bodyBlocks(): array
    {
        return \App\Support\Text::blocks($this->body);
    }
}
