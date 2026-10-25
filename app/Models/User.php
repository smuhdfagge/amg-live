<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /** Same defaults as the database, so new users are right before they are reloaded. */
    protected $attributes = [
        'role' => 'admin',
        'is_active' => true,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public const ROLES = [
        'admin' => 'Administrator',
        'editor' => 'Editor',
    ];

    public const ROLE_HELP = [
        'admin' => 'Everything, including messages, volunteers, subscribers and user accounts.',
        'editor' => 'News, programmes and resources only. Cannot see messages, volunteers or subscribers, or manage users.',
    ];

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function roleLabel(): string
    {
        return self::ROLES[$this->role] ?? ucfirst((string) $this->role);
    }

    /** Is this the only active administrator left? */
    public function isLastActiveAdmin(): bool
    {
        return $this->isAdmin() && $this->is_active
            && self::where('role', 'admin')->where('is_active', true)->where('id', '!=', $this->id)->doesntExist();
    }
}
