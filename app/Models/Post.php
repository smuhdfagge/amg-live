<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Post extends Model
{
    protected $fillable = [
        'slug', 'title', 'category', 'excerpt', 'body', 'cover_image', 'external_url', 'source', 'published_at',
    ];

    protected function casts(): array
    {
        return ['published_at' => 'datetime'];
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

    public function bodyBlocks(): array
    {
        return \App\Support\Text::blocks($this->body);
    }
}
