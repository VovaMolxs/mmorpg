<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActiveNpc extends Model
{
    protected $fillable = [
        'npc_id',
        'location_id',
        'spawn_id',
        'health_current',
        'mana_current',
        'spawned_at',
        'died_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'health_current' => 'integer',
            'mana_current' => 'integer',
            'spawned_at' => 'datetime',
            'died_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * NPC шаблон.
     */
    public function npc(): BelongsTo
    {
        return $this->belongsTo(Npc::class);
    }

    /**
     * Текущая локация.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Спавн, который создал этого NPC.
     */
    public function spawn(): BelongsTo
    {
        return $this->belongsTo(NpcSpawn::class, 'spawn_id');
    }

    /**
     * Получить статистику NPC.
     */
    public function getStats(): ?NpcStat
    {
        return $this->npc?->stats;
    }

    /**
     * Применить урон к активному NPC.
     *
     * @param  int  $damage  Количество урона
     * @return bool Возвращает true, если NPC умер
     */
    public function takeDamage(int $damage): bool
    {
        if ($damage <= 0 || ! $this->is_active) {
            return false;
        }

        $this->health_current -= $damage;

        if ($this->health_current < 0) {
            $this->health_current = 0;
        }

        if ($this->isDead()) {
            $this->setDead();
        }

        return $this->isDead();
    }

    /**
     * Проверить, мертв ли NPC.
     */
    public function isDead(): bool
    {
        return $this->health_current <= 0;
    }

    /**
     * Установить NPC в состояние смерти.
     */
    public function setDead(): void
    {
        $this->is_active = false;
        $this->died_at = now();
        $this->health_current = 0;
        $this->mana_current = 0;
    }

    /**
     * Получить процент текущего здоровья.
     *
     * @return float Процент здоровья (0-100)
     */
    public function getHealthPercentage(): float
    {
        $stats = $this->getStats();
        if (! $stats || $stats->health_max <= 0) {
            return 0.0;
        }

        return ($this->health_current / $stats->health_max) * 100;
    }

    /**
     * Получить процент текущей маны.
     *
     * @return float Процент маны (0-100)
     */
    public function getManaPercentage(): float
    {
        $stats = $this->getStats();
        if (! $stats || $stats->mana_max <= 0) {
            return 0.0;
        }

        return ($this->mana_current / $stats->mana_max) * 100;
    }
}
