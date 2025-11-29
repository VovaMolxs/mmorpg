<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Monster extends Model
{
    protected $fillable = [
        'name',
        'type',
        'level',
        'rank',
        'faction_id',
        'ai_behavior',
        'respawn_time',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'level' => 'integer',
            'respawn_time' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Статистика монстра.
     */
    public function stats(): HasOne
    {
        return $this->hasOne(MonsterStat::class);
    }

    /**
     * Лут монстра.
     */
    public function loot(): HasMany
    {
        return $this->hasMany(MonsterLoot::class);
    }

    /**
     * Способности монстра.
     */
    public function skills(): HasMany
    {
        return $this->hasMany(MonsterSkill::class);
    }

    /**
     * Настройки спавна этого монстра.
     */
    public function spawns(): HasMany
    {
        return $this->hasMany(MonsterSpawn::class);
    }

    /**
     * Активные экземпляры этого монстра.
     */
    public function activeInstances(): HasMany
    {
        return $this->hasMany(ActiveMonster::class)
            ->where('is_active', true);
    }

    /**
     * Проверить, является ли монстр пассивным.
     */
    public function isPassive(): bool
    {
        return $this->ai_behavior === 'passive';
    }

    /**
     * Проверить, является ли монстр нейтральным.
     */
    public function isNeutral(): bool
    {
        return $this->ai_behavior === 'neutral';
    }

    /**
     * Проверить, является ли монстр агрессивным.
     */
    public function isAggressive(): bool
    {
        return $this->ai_behavior === 'aggressive';
    }

    /**
     * Проверить, является ли монстр боссом.
     */
    public function isBoss(): bool
    {
        return in_array($this->rank, ['boss', 'world_boss'], true);
    }

    /**
     * Проверить, является ли монстр магическим существом.
     */
    public function isMagical(): bool
    {
        return $this->type === 'magical';
    }

    /**
     * Проверить, является ли монстр элементалем.
     */
    public function isElemental(): bool
    {
        return $this->type === 'elemental';
    }

    /**
     * Проверить, является ли монстр нежитью.
     */
    public function isUndead(): bool
    {
        return $this->type === 'undead';
    }

    /**
     * Проверить, является ли монстр демоном.
     */
    public function isDemon(): bool
    {
        return $this->type === 'demon';
    }
}
