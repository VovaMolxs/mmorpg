<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NpcStat extends Model
{
    protected $fillable = [
        'npc_id',
        'level',
        'health_max',
        'health_current',
        'mana_max',
        'mana_current',
        'strength',
        'agility',
        'intelligence',
        'attack_power',
        'defense',
        'magic_defense',
        'accuracy',
        'dodge',
        'critical_chance',
        'critical_power',
        'experience_reward',
        'gold_reward_min',
        'gold_reward_max',
    ];

    protected function casts(): array
    {
        return [
            'level' => 'integer',
            'health_max' => 'integer',
            'health_current' => 'integer',
            'mana_max' => 'integer',
            'mana_current' => 'integer',
            'strength' => 'integer',
            'agility' => 'integer',
            'intelligence' => 'integer',
            'attack_power' => 'integer',
            'defense' => 'integer',
            'magic_defense' => 'integer',
            'accuracy' => 'decimal:2',
            'dodge' => 'decimal:2',
            'critical_chance' => 'decimal:2',
            'critical_power' => 'decimal:2',
            'experience_reward' => 'integer',
            'gold_reward_min' => 'integer',
            'gold_reward_max' => 'integer',
        ];
    }

    /**
     * NPC, которому принадлежат эти характеристики.
     */
    public function npc(): BelongsTo
    {
        return $this->belongsTo(Npc::class);
    }

    /**
     * Применить урон к NPC.
     *
     * @param  int  $damage  Количество урона
     * @return bool Возвращает true, если NPC умер
     */
    public function takeDamage(int $damage): bool
    {
        if ($damage <= 0) {
            return false;
        }

        $this->health_current -= $damage;

        // Убеждаемся, что здоровье не меньше 0
        if ($this->health_current < 0) {
            $this->health_current = 0;
        }

        return $this->isDead();
    }

    /**
     * Восстановить здоровье NPC.
     *
     * @param  int  $healing  Количество восстанавливаемого здоровья
     * @return int Фактически восстановленное количество здоровья
     */
    public function heal(int $healing): int
    {
        if ($healing <= 0 || $this->isDead()) {
            return 0;
        }

        $oldHealth = $this->health_current;
        $this->health_current += $healing;

        // Ограничиваем максимумом
        if ($this->health_current > $this->health_max) {
            $this->health_current = $this->health_max;
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
     * Восстановить ману NPC.
     *
     * @param  int  $amount  Количество восстанавливаемой маны
     * @return int Фактически восстановленное количество маны
     */
    public function restoreMana(int $amount): int
    {
        if ($amount <= 0) {
            return 0;
        }

        $oldMana = $this->mana_current;
        $this->mana_current += $amount;

        // Ограничиваем максимумом
        if ($this->mana_current > $this->mana_max) {
            $this->mana_current = $this->mana_max;
        }

        return $this->mana_current - $oldMana;
    }

    /**
     * Проверить, мертв ли NPC.
     */
    public function isDead(): bool
    {
        return $this->health_current <= 0;
    }

    /**
     * Установить NPC в состояние смерти (HP = 0, Mana = 0).
     */
    public function setDead(): void
    {
        $this->health_current = 0;
        $this->mana_current = 0;
    }

    /**
     * Воскресить NPC (восстановить HP и Mana до максимума).
     */
    public function resurrect(): void
    {
        $this->health_current = $this->health_max;
        $this->mana_current = $this->mana_max;
    }

    /**
     * Получить процент текущего здоровья.
     *
     * @return float Процент здоровья (0-100)
     */
    public function getHealthPercentage(): float
    {
        if ($this->health_max <= 0) {
            return 0.0;
        }

        return ($this->health_current / $this->health_max) * 100;
    }

    /**
     * Получить процент текущей маны.
     *
     * @return float Процент маны (0-100)
     */
    public function getManaPercentage(): float
    {
        if ($this->mana_max <= 0) {
            return 0.0;
        }

        return ($this->mana_current / $this->mana_max) * 100;
    }

    /**
     * Получить случайное количество золота за убийство.
     *
     * @return int Количество золота
     */
    public function getRandomGoldReward(): int
    {
        if ($this->gold_reward_min >= $this->gold_reward_max) {
            return $this->gold_reward_min;
        }

        return rand($this->gold_reward_min, $this->gold_reward_max);
    }
}
