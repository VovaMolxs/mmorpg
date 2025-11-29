<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActiveMonster extends Model
{
    protected $fillable = [
        'monster_id',
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
     * Монстр шаблон.
     */
    public function monster(): BelongsTo
    {
        return $this->belongsTo(Monster::class);
    }

    /**
     * Текущая локация.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Спавн, который создал этого монстра.
     */
    public function spawn(): BelongsTo
    {
        return $this->belongsTo(MonsterSpawn::class, 'spawn_id');
    }

    /**
     * Получить статистику монстра.
     */
    public function getStats(): ?MonsterStat
    {
        return $this->monster?->stats;
    }

    /**
     * Применить урон к активному монстру.
     *
     * @param  int  $damage  Количество урона
     * @return bool Возвращает true, если монстр умер
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
     * Восстановить здоровье монстра.
     *
     * @param  int  $healing  Количество восстанавливаемого здоровья
     * @return int Фактически восстановленное количество здоровья
     */
    public function heal(int $healing): int
    {
        if ($healing <= 0 || $this->isDead()) {
            return 0;
        }

        $stats = $this->getStats();
        if (! $stats) {
            return 0;
        }

        $oldHealth = $this->health_current;
        $this->health_current += $healing;

        // Ограничиваем максимумом
        if ($this->health_current > $stats->health_max) {
            $this->health_current = $stats->health_max;
        }

        return $this->health_current - $oldHealth;
    }

    /**
     * Потратить ману.
     *
     * @param  int  $amount  Количество маны для траты
     * @return bool Возвращает true, если маны достаточно
     */
    public function spendMana(int $amount): bool
    {
        if ($amount <= 0) {
            return true;
        }

        if ($this->mana_current < $amount) {
            return false;
        }

        $this->mana_current -= $amount;

        // Убеждаемся, что мана не меньше 0
        if ($this->mana_current < 0) {
            $this->mana_current = 0;
        }

        return true;
    }

    /**
     * Восстановить ману монстра.
     *
     * @param  int  $amount  Количество восстанавливаемой маны
     * @return int Фактически восстановленное количество маны
     */
    public function restoreMana(int $amount): int
    {
        if ($amount <= 0) {
            return 0;
        }

        $stats = $this->getStats();
        if (! $stats) {
            return 0;
        }

        $oldMana = $this->mana_current;
        $this->mana_current += $amount;

        // Ограничиваем максимумом
        if ($this->mana_current > $stats->mana_max) {
            $this->mana_current = $stats->mana_max;
        }

        return $this->mana_current - $oldMana;
    }

    /**
     * Проверить, мертв ли монстр.
     */
    public function isDead(): bool
    {
        return $this->health_current <= 0;
    }

    /**
     * Установить монстра в состояние смерти.
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
