<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Project extends Model
{
    protected $fillable = [
        'slug', 'title', 'short_title', 'partner', 'partner_role', 'period', 'start_year', 'end_year',
        'status', 'location', 'theme', 'summary', 'body', 'reach_figure', 'reach_label',
        'cover_image', 'is_featured', 'sort_order',
    ];

    protected function casts(): array
    {
        return ['is_featured' => 'boolean'];
    }

    public const STATUSES = [
        'ongoing' => 'Ongoing',
        'recent' => 'Recent',
        'completed' => 'Completed',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderByDesc('start_year');
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function displayName(): string
    {
        return $this->short_title ?: $this->title;
    }

    public function coverUrl(): ?string
    {
        return $this->cover_image ? Storage::disk('public')->url($this->cover_image) : null;
    }

    /** Body text split into paragraphs; lines starting with "- " become list items. */
    public function bodyBlocks(): array
    {
        return \App\Support\Text::blocks($this->body);
    }
}
