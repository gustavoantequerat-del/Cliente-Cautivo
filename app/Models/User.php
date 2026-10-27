<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_CLIENT_ADMIN = 'client_admin';
    public const ROLE_CLIENT = 'client';

    protected $fillable = [
        'institution_id', 'name', 'email', 'password', 'role', 'area', 'position', 'phone',
        'booking_url', 'is_active', 'credential_number',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'credential_issued_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if ($user->role !== self::ROLE_ADMIN && ! $user->credential_code) {
                $user->credential_code = (string) Str::uuid();
                $user->credential_issued_at = now();
            }
        });
        static::created(function (User $user) {
            if ($user->credential_code && ! $user->credential_number) {
                $user->credential_number = str_pad((string) $user->id, 4, '0', STR_PAD_LEFT);
                $user->saveQuietly();
            }
        });
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isClientAdmin(): bool
    {
        return $this->role === self::ROLE_CLIENT_ADMIN;
    }

    public function initials(): string
    {
        $parts = preg_split('/\s+/', trim($this->name));

        return Str::upper(Str::substr($parts[0] ?? '', 0, 1).Str::substr($parts[1] ?? '', 0, 1));
    }
}
