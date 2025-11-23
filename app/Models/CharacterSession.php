<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CharacterSession extends Model
{
    protected $fillable = [
        'character_id',
        'session_token',
        'location_id',
        'ip_address',
        'user_agent',
        'login_at',
        'last_activity_at',
        'logout_at',
        'is_online',
        'session_duration',
    ];

    protected function casts(): array
    {
        return [
            'login_at' => 'datetime',
            'last_activity_at' => 'datetime',
            'logout_at' => 'datetime',
            'is_online' => 'boolean',
            'session_duration' => 'integer',
        ];
    }

    /**
     * Персонаж сессии.
     */
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    /**
     * Локация сессии.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Генерировать уникальный токен сессии.
     */
    public static function generateSessionToken(): string
    {
        do {
            $token = Str::random(60);
        } while (self::where('session_token', $token)->exists());

        return $token;
    }

    /**
     * Найти активную сессию по токену.
     */
    public static function findActiveByToken(string $token): ?self
    {
        return self::where('session_token', $token)
            ->where('is_online', true)
            ->whereNull('logout_at')
            ->first();
    }

    /**
     * Проверить, истекла ли сессия по таймауту бездействия.
     */
    public function isExpired(int $timeoutMinutes = 15): bool
    {
        if (! $this->last_activity_at) {
            return true;
        }

        return $this->last_activity_at->copy()->addMinutes($timeoutMinutes)->isPast();
    }

    /**
     * Получить оставшееся время до истечения сессии в секундах.
     */
    public function getRemainingTime(int $timeoutMinutes = 15): int
    {
        if (! $this->last_activity_at) {
            return 0;
        }

        $expiresAt = $this->last_activity_at->copy()->addMinutes($timeoutMinutes);
        $remaining = now()->diffInSeconds($expiresAt, false);

        return max(0, $remaining);
    }

    /**
     * Обновить время последней активности.
     */
    public function updateActivity(): void
    {
        $this->last_activity_at = now();
        $this->save();
    }

    /**
     * Завершить сессию.
     */
    public function endSession(): void
    {
        $this->logout_at = now();
        $this->is_online = false;

        if ($this->login_at) {
            $this->session_duration = $this->login_at->diffInSeconds($this->logout_at);
        }

        $this->save();
    }

    /**
     * Проверить, активна ли сессия.
     */
    public function isActive(): bool
    {
        return $this->is_online && is_null($this->logout_at);
    }
}
