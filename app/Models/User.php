<?php

namespace App\Models;

use App\AccountStatus;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'account_status',
        'is_admin',
        'max_characters',
        'preferred_language',
        'game_settings',
        'last_login_at',
        'time_played_total',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
            'account_status' => AccountStatus::class,
            'is_admin' => 'boolean',
            'game_settings' => 'array',
            'last_login_at' => 'datetime',
            'time_played_total' => 'integer',
        ];
    }

    /**
     * Get default game settings.
     *
     * @return array<string, mixed>
     */
    public static function getDefaultGameSettings(): array
    {
        return [
            'theme' => 'dark',
            'sound_enabled' => true,
            'notifications_enabled' => true,
            'auto_save' => true,
        ];
    }

    /**
     * Check if account is active.
     */
    public function isActive(): bool
    {
        return $this->account_status === AccountStatus::Active;
    }

    /**
     * Check if account is banned.
     */
    public function isBanned(): bool
    {
        return $this->account_status === AccountStatus::Banned;
    }

    /**
     * Check if account is verified.
     */
    public function isVerified(): bool
    {
        return $this->email_verified_at !== null;
    }

    /**
     * Update last login timestamp.
     */
    public function updateLastLogin(): void
    {
        $this->update(['last_login_at' => now()]);
    }

    /**
     * Add time played to total.
     */
    public function addTimePlayed(int $seconds): void
    {
        $this->increment('time_played_total', $seconds);
    }

    /**
     * Get the characters for the user.
     */
    public function characters(): HasMany
    {
        return $this->hasMany(Character::class);
    }

    /**
     * Check if user can create more characters.
     */
    public function canCreateCharacter(): bool
    {
        return $this->characters()->count() < $this->max_characters;
    }
}
