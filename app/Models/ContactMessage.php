<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $fillable = ['type', 'name', 'email', 'phone', 'organisation', 'subject', 'message', 'read_at'];

    protected function casts(): array
    {
        return ['read_at' => 'datetime'];
    }

    public const TYPES = [
        'general' => 'General enquiry',
        'partnership' => 'Partnership',
        'support' => 'Support or donation',
        'media' => 'Media',
    ];

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }
}
