<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MonsterSpawn extends Model
{
    protected $fillable = [
        'monster_id',
        'location_id',
        'min_instances',
        'max_instances',
        'respawn_time_min',
        'respawn_time_max',
        'spawn_chance',
        'spawn_radius',
        'spawn_schedule',
        'is_active',
        'last_spawn_at',
    ];

    protected function casts(): array
    {
        return [
            'min_instances' => 'integer',
            'max_instances' => 'integer',
            'respawn_time_min' => 'integer',
            'respawn_time_max' => 'integer',
            'spawn_chance' => 'integer',
            'spawn_radius' => 'integer',
            'is_active' => 'boolean',
            'last_spawn_at' => 'datetime',
            'spawn_schedule' => 'array',
        ];
    }

    /**
     * Монстр шаблон для спавна.
     */
    public function monster(): BelongsTo
    {
        return $this->belongsTo(Monster::class);
    }

    /**
     * Локация спавна.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Активные монстры, созданные этим спавном.
     */
    public function activeMonsters(): HasMany
    {
        return $this->hasMany(ActiveMonster::class, 'spawn_id')
            ->where('is_active', true);
    }

    /**
     * Все монстры, созданные этим спавном.
     */
    public function allActiveMonsters(): HasMany
    {
        return $this->hasMany(ActiveMonster::class, 'spawn_id');
    }

    /**
     * Получить количество активных монстров в локации.
     */
    public function getActiveCount(): int
    {
        return $this->activeMonsters()->count();
    }

    /**
     * Проверить, нужно ли спавнить монстра.
     */
    public function shouldSpawn(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $activeCount = $this->getActiveCount();

        // Если меньше минимума - обязательно спавним
        if ($activeCount < $this->min_instances) {
            return true;
        }

        // Если меньше максимума и прошло время респавна - проверяем шанс
        if ($activeCount < $this->max_instances) {
            if ($this->canRespawn()) {
                return $this->checkSpawnChance();
            }
        }

        return false;
    }

    /**
     * Проверить, можно ли респавнить (прошло ли время респавна).
     */
    public function canRespawn(): bool
    {
        if (! $this->last_spawn_at) {
            return true;
        }

        $minRespawnTime = now()->subMinutes($this->respawn_time_min);

        // Проверяем, прошло ли минимальное время респавна
        if ($this->last_spawn_at->greaterThan($minRespawnTime)) {
            return false;
        }

        return true;
    }

    /**
     * Проверить шанс спавна.
     */
    public function checkSpawnChance(): bool
    {
        return rand(1, 100) <= $this->spawn_chance;
    }

    /**
     * Получить случайное время респавна в минутах.
     */
    public function getRandomRespawnTime(): int
    {
        if ($this->respawn_time_min >= $this->respawn_time_max) {
            return $this->respawn_time_min;
        }

        return rand($this->respawn_time_min, $this->respawn_time_max);
    }

    /**
     * Проверить, соответствует ли текущее время расписанию спавна.
     */
    public function isWithinSchedule(): bool
    {
        if (! $this->spawn_schedule) {
            return true; // Если расписание не задано - круглосуточно
        }

        $schedule = $this->spawn_schedule;
        $currentHour = (int) now()->format('H');

        // Проверка типа расписания
        if (isset($schedule['type'])) {
            return match ($schedule['type']) {
                'always' => true,
                'day' => $currentHour >= 6 && $currentHour < 22, // 6:00 - 22:00
                'night' => $currentHour >= 22 || $currentHour < 6, // 22:00 - 6:00
                'custom' => $this->checkCustomSchedule($currentHour, $schedule),
                default => true,
            };
        }

        return true;
    }

    /**
     * Проверить кастомное расписание.
     */
    protected function checkCustomSchedule(int $currentHour, array $schedule): bool
    {
        if (isset($schedule['hours']) && is_array($schedule['hours'])) {
            return in_array($currentHour, $schedule['hours']);
        }

        return true;
    }
}
