<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResurrectionStone extends Model
{
    protected $fillable = [
        'location_id',
        'name',
        'description',
        'level_required',
        'is_active',
        'cooldown_minutes',
        'last_used_at',
        'visual_effect',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'level_required' => 'integer',
            'cooldown_minutes' => 'integer',
            'last_used_at' => 'datetime',
        ];
    }

    /**
     * Локация, где находится камень воскрешения.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Получить все активные камни воскрешения.
     */
    public static function getActiveStones()
    {
        return self::where('is_active', true)
            ->with('location')
            ->get();
    }

    /**
     * Получить камень воскрешения в локации.
     */
    public static function getStoneInLocation(int $locationId): ?self
    {
        return self::where('location_id', $locationId)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Проверить, доступен ли камень для использования (не на перезарядке).
     */
    public function isAvailable(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        // Если перезарядка не установлена (0), камень всегда доступен
        if ($this->cooldown_minutes <= 0) {
            return true;
        }

        // Если камень еще не использовался, он доступен
        if (! $this->last_used_at) {
            return true;
        }

        // Проверяем, прошло ли время перезарядки
        $cooldownEnd = $this->last_used_at->copy()->addMinutes($this->cooldown_minutes);

        return now()->greaterThanOrEqualTo($cooldownEnd);
    }

    /**
     * Получить оставшееся время перезарядки в минутах.
     */
    public function getRemainingCooldownMinutes(): int
    {
        if ($this->cooldown_minutes <= 0 || ! $this->last_used_at) {
            return 0;
        }

        $cooldownEnd = $this->last_used_at->copy()->addMinutes($this->cooldown_minutes);
        $remaining = now()->diffInMinutes($cooldownEnd, false);

        return max(0, $remaining);
    }

    /**
     * Проверить, может ли персонаж использовать камень (по уровню).
     */
    public function canBeUsedByLevel(int $level): bool
    {
        return $level >= $this->level_required;
    }

    /**
     * Обновить время последнего использования.
     */
    public function markAsUsed(): void
    {
        $this->last_used_at = now();
        $this->save();
    }
}
