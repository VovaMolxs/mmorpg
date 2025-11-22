<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Character extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'strength',
        'agility',
        'intelligence',
        'level',
        'experience',
        'total_attributes_spent',
        'available_points',
        'health_current',
        'health_max',
        'mana_current',
        'mana_max',
        'is_criminal',
        'criminal_until',
        'is_active',
        'location_id',
    ];

    protected function casts(): array
    {
        return [
            'strength' => 'integer',
            'agility' => 'integer',
            'intelligence' => 'integer',
            'level' => 'integer',
            'experience' => 'integer',
            'total_attributes_spent' => 'integer',
            'available_points' => 'integer',
            'health_current' => 'integer',
            'health_max' => 'integer',
            'mana_current' => 'integer',
            'mana_max' => 'integer',
            'is_criminal' => 'boolean',
            'criminal_until' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Владелец персонажа.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Навыки персонажа.
     */
    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'character_skills')
            ->withPivot('level', 'experience', 'is_active')
            ->withTimestamps();
    }

    /**
     * Навыки персонажа через промежуточную таблицу.
     */
    public function characterSkills()
    {
        return $this->hasMany(CharacterSkill::class);
    }

    /**
     * Предметы в инвентаре персонажа.
     */
    public function inventoryItems(): HasMany
    {
        return $this->hasMany(ItemInstance::class, 'location_id')
            ->where('location_type', 'inventory');
    }

    /**
     * Экипировка персонажа.
     */
    public function equipment(): HasMany
    {
        return $this->hasMany(CharacterEquipment::class);
    }

    /**
     * Экипированные предметы.
     */
    public function equippedItems(): HasMany
    {
        return $this->hasMany(ItemInstance::class, 'location_id')
            ->where('location_type', 'equipped');
    }

    /**
     * Квестовые предметы персонажа.
     */
    public function questItems(): HasMany
    {
        return $this->hasMany(ItemInstance::class, 'owner_id');
    }

    /**
     * Вычислить максимальное здоровье на основе силы.
     */
    public function calculateHealthMax(): int
    {
        return $this->strength * 10;
    }

    /**
     * Вычислить максимальную ману на основе интеллекта.
     */
    public function calculateManaMax(): int
    {
        return $this->intelligence * 10;
    }

    /**
     * Вычислить сумму потраченных очков на характеристики.
     */
    public function calculateTotalAttributesSpent(): int
    {
        return ($this->strength - 1) + ($this->agility - 1) + ($this->intelligence - 1);
    }

    /**
     * Вычислить сумму всех характеристик.
     */
    public function getTotalAttributes(): int
    {
        return $this->strength + $this->agility + $this->intelligence;
    }

    /**
     * Обновить здоровье и ману на основе характеристик.
     */
    public function updateHealthAndMana(): void
    {
        $this->health_max = $this->calculateHealthMax();
        $this->mana_max = $this->calculateManaMax();

        // Ограничиваем текущие значения максимумами
        if ($this->health_current > $this->health_max) {
            $this->health_current = $this->health_max;
        }

        if ($this->mana_current > $this->mana_max) {
            $this->mana_current = $this->mana_max;
        }

        // Убеждаемся, что значения не меньше 0
        if ($this->health_current < 0) {
            $this->health_current = 0;
        }

        if ($this->mana_current < 0) {
            $this->mana_current = 0;
        }
    }

    /**
     * Применить урон к персонажу.
     *
     * @param  int  $damage  Количество урона
     * @return bool Возвращает true, если персонаж умер
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
     * Восстановить здоровье персонажа.
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
     * Восстановить ману персонажа.
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
     * Проверить, мертв ли персонаж.
     */
    public function isDead(): bool
    {
        return $this->health_current <= 0;
    }

    /**
     * Установить персонажа в состояние смерти (HP = 0, Mana = 0).
     */
    public function setDead(): void
    {
        $this->health_current = 0;
        $this->mana_current = 0;
    }

    /**
     * Воскресить персонажа (восстановить HP и Mana до максимума).
     */
    public function resurrect(): void
    {
        $this->health_current = 1;
        $this->mana_current = 1;
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
     * Обновить сумму потраченных очков.
     */
    public function updateTotalAttributesSpent(): void
    {
        $this->total_attributes_spent = $this->calculateTotalAttributesSpent();
    }

    /**
     * Генерировать описание персонажа.
     */
    public function generateDescription(): string
    {
        $descriptions = [
            'Странник, ищущий приключений в этом мире.',
            'Отважный искатель, готовый к любым испытаниям.',
            'Загадочный персонаж с необычными способностями.',
        ];

        $primaryAttribute = match (true) {
            $this->strength >= $this->agility && $this->strength >= $this->intelligence => 'сильный',
            $this->agility >= $this->intelligence => 'ловкий',
            default => 'умный',
        };

        return "{$descriptions[array_rand($descriptions)]} Особенно {$primaryAttribute}.";
    }

    /**
     * Проверить валидность характеристик.
     */
    public function validateAttributes(): array
    {
        $errors = [];

        if ($this->strength < 1 || $this->strength > 10) {
            $errors[] = 'Сила должна быть от 1 до 10';
        }

        if ($this->agility < 1 || $this->agility > 10) {
            $errors[] = 'Ловкость должна быть от 1 до 10';
        }

        if ($this->intelligence < 1 || $this->intelligence > 10) {
            $errors[] = 'Интеллект должен быть от 1 до 10';
        }

        if ($this->getTotalAttributes() > 15) {
            $errors[] = 'Сумма характеристик не может превышать 15';
        }

        return $errors;
    }
}
