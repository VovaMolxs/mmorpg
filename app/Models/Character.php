<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
     * Текущая локация персонажа.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
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
     * Сессии персонажа.
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(CharacterSession::class);
    }

    /**
     * Активная сессия персонажа.
     */
    public function activeSession(): HasMany
    {
        return $this->hasMany(CharacterSession::class)
            ->where('is_online', true)
            ->whereNull('logout_at');
    }

    /**
     * Присутствие персонажа в мире.
     */
    public function presence(): HasOne
    {
        return $this->hasOne(CharacterPresence::class);
    }

    /**
     * Квесты персонажа.
     */
    public function quests(): HasMany
    {
        return $this->hasMany(CharacterQuest::class);
    }

    /**
     * Активные квесты персонажа.
     */
    public function activeQuests(): HasMany
    {
        return $this->hasMany(CharacterQuest::class)->where('status', 'active');
    }

    /**
     * Завершенные квесты персонажа.
     */
    public function completedQuests(): HasMany
    {
        return $this->hasMany(CharacterQuest::class)->where('status', 'completed');
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

    /**
     * Получить уровень навыка по имени.
     */
    public function getSkillLevel(string $skillName): int
    {
        $characterSkill = $this->characterSkills()
            ->whereHas('skill', function ($query) use ($skillName) {
                $query->where('name', $skillName);
            })
            ->first();

        return $characterSkill?->level ?? 0;
    }

    /**
     * Получить экипированное оружие.
     */
    public function getEquippedWeapon(): ?ItemInstance
    {
        $equipment = $this->equipment()
            ->where('slot', 'weapon_main')
            ->with('itemInstance.item')
            ->first();

        return $equipment?->itemInstance;
    }

    /**
     * Получить сумму защиты от всей экипированной брони.
     */
    public function getTotalArmorDefense(): int
    {
        $armorSlots = ['head', 'chest', 'legs', 'hands', 'feet'];
        $totalDefense = 0;

        foreach ($armorSlots as $slot) {
            $equipment = $this->equipment()
                ->where('slot', $slot)
                ->with('itemInstance.item')
                ->first();

            if ($equipment) {
                $itemInstance = $equipment->itemInstance;
                if ($itemInstance && $itemInstance->item) {
                    $armorData = $itemInstance->item->armor_data;
                    if ($armorData && isset($armorData['defense'])) {
                        $totalDefense += (int) $armorData['defense'];
                    }
                }
            }
        }

        return $totalDefense;
    }

    /**
     * Получить тип экипированного оружия.
     */
    public function getEquippedWeaponType(): ?string
    {
        $weapon = $this->getEquippedWeapon();
        if (! $weapon || ! $weapon->item) {
            return null;
        }

        return $weapon->item->subtype;
    }

    /**
     * Получить уровень мастерства для текущего оружия.
     */
    public function getWeaponMasteryLevel(): int
    {
        $weaponType = $this->getEquippedWeaponType();
        if (! $weaponType) {
            return 0;
        }

        $skillMap = [
            'sword' => 'sword_mastery',
            'axe' => 'axe_mastery',
            'dagger' => 'dagger_mastery',
            'staff' => 'staff_mastery',
            'bow' => 'archery',
            'crossbow' => 'crossbow_mastery',
        ];

        $skillName = $skillMap[$weaponType] ?? null;
        if (! $skillName) {
            return 0;
        }

        return $this->getSkillLevel($skillName);
    }

    /**
     * Рассчитать урон (Damage).
     */
    public function calculateDamage(): array
    {
        $baseDamage = $this->strength * 2;
        $weapon = $this->getEquippedWeapon();
        $weaponMasteryLevel = $this->getWeaponMasteryLevel();

        $minDamage = $baseDamage;
        $maxDamage = $baseDamage;

        if ($weapon && $weapon->item && $weapon->item->weapon_data) {
            $weaponData = $weapon->item->weapon_data;
            $minDamage += $weaponData['damage_min'] ?? 0;
            $maxDamage += $weaponData['damage_max'] ?? 0;
        }

        // Бонус от мастерства оружия: +5% урона за уровень
        $masteryBonus = 1 + ($weaponMasteryLevel * 0.05);
        $minDamage = (int) ($minDamage * $masteryBonus);
        $maxDamage = (int) ($maxDamage * $masteryBonus);

        return [
            'min' => $minDamage,
            'max' => $maxDamage,
        ];
    }

    /**
     * Рассчитать физическую защиту (Physical Defense).
     */
    public function calculatePhysicalDefense(): int
    {
        $baseDefense = $this->agility;
        $armorDefense = $this->getTotalArmorDefense();
        $defenseSkillLevel = $this->getSkillLevel('defense');

        // Базовая защита от ловкости + защита от брони
        $totalDefense = $baseDefense + $armorDefense;

        // Бонус от навыка защиты: +2 защиты за уровень
        $totalDefense += $defenseSkillLevel * 2;

        return $totalDefense;
    }

    /**
     * Рассчитать магическую защиту (Magic Defense).
     */
    public function calculateMagicDefense(): int
    {
        $baseDefense = $this->intelligence;
        $magicResistanceLevel = $this->getSkillLevel('magic_resistance');

        // Базовая защита от интеллекта
        $totalDefense = $baseDefense;

        // Бонус от навыка сопротивления магии: +3 защиты за уровень
        $totalDefense += $magicResistanceLevel * 3;

        return $totalDefense;
    }

    /**
     * Рассчитать точность (Accuracy).
     */
    public function calculateAccuracy(): float
    {
        $baseAccuracy = 20.0; // Базовая точность 50%
        $agilityBonus = $this->agility * 3; // +3% за единицу ловкости
        $accuracySkillLevel = $this->getSkillLevel('accuracy');

        $totalAccuracy = $baseAccuracy + $agilityBonus;

        // Бонус от навыка точности: +5% за уровень
        $totalAccuracy += $accuracySkillLevel * 5;

        return min($totalAccuracy, 95.0); // Максимум 95%
    }

    /**
     * Рассчитать точность магии (Magic Accuracy).
     */
    public function calculateMagicAccuracy(): float
    {
        $baseAccuracy = 20.0; // Базовая точность магии 60%
        $intelligenceBonus = $this->intelligence * 4; // +4% за единицу интеллекта
        $spellcastingLevel = $this->getSkillLevel('spellcasting');

        $totalAccuracy = $baseAccuracy + $intelligenceBonus;

        // Бонус от навыка колдовства: +4% за уровень
        $totalAccuracy += $spellcastingLevel * 4;

        return min($totalAccuracy, 95.0); // Максимум 95%
    }

    /**
     * Рассчитать точность дальнего оружия (Ranged Accuracy).
     */
    public function calculateRangedAccuracy(): float
    {
        $baseAccuracy = 35.0; // Базовая точность дальнего оружия 45%
        $agilityBonus = $this->agility * 4; // +4% за единицу ловкости
        $archeryLevel = $this->getSkillLevel('archery');
        $crossbowLevel = $this->getSkillLevel('crossbow_mastery');
        $accuracySkillLevel = $this->getSkillLevel('accuracy');

        $totalAccuracy = $baseAccuracy + $agilityBonus;

        // Бонус от навыков стрельбы (берем максимальный)
        $rangedSkillLevel = max($archeryLevel, $crossbowLevel);
        $totalAccuracy += $rangedSkillLevel * 6;

        // Бонус от навыка точности: +3% за уровень
        $totalAccuracy += $accuracySkillLevel * 3;

        return min($totalAccuracy, 95.0); // Максимум 95%
    }

    /**
     * Рассчитать шанс критического удара (Critical Chance).
     */
    public function calculateCriticalChance(): float
    {
        $baseChance = 5.0; // Базовая вероятность 5%
        $agilityBonus = $this->agility * 0.5; // +0.5% за единицу ловкости
        $criticalStrikeLevel = $this->getSkillLevel('critical_strike');

        $totalChance = $baseChance + $agilityBonus;

        // Бонус от навыка критических ударов: +2% за уровень
        $totalChance += $criticalStrikeLevel * 2;

        return min($totalChance, 50.0); // Максимум 50%
    }

    /**
     * Рассчитать силу критического удара (Critical Power).
     */
    public function calculateCriticalPower(): float
    {
        $basePower = 1.5; // Базовый множитель 1.5x
        $strengthBonus = $this->strength * 0.05; // +0.05x за единицу силы
        $criticalStrikeLevel = $this->getSkillLevel('critical_strike');

        $totalPower = $basePower + $strengthBonus;

        // Бонус от навыка критических ударов: +0.1x за уровень
        $totalPower += $criticalStrikeLevel * 0.1;

        return min($totalPower, 3.0); // Максимум 3.0x
    }

    /**
     * Рассчитать шанс магического крита (Magic Critical Chance).
     */
    public function calculateMagicCriticalChance(): float
    {
        $baseChance = 3.0; // Базовая вероятность 3%
        $intelligenceBonus = $this->intelligence * 0.5; // +0.5% за единицу интеллекта
        $magicCriticalLevel = $this->getSkillLevel('magic_critical');

        $totalChance = $baseChance + $intelligenceBonus;

        // Бонус от навыка магических критических ударов: +2% за уровень
        $totalChance += $magicCriticalLevel * 2;

        return min($totalChance, 40.0); // Максимум 40%
    }

    /**
     * Рассчитать силу магического крита (Magic Critical Power).
     */
    public function calculateMagicCriticalPower(): float
    {
        $basePower = 1.5; // Базовый множитель 1.5x
        $intelligenceBonus = $this->intelligence * 0.05; // +0.05x за единицу интеллекта
        $magicCriticalLevel = $this->getSkillLevel('magic_critical');

        $totalPower = $basePower + $intelligenceBonus;

        // Бонус от навыка магических критических ударов: +0.1x за уровень
        $totalPower += $magicCriticalLevel * 0.1;

        return min($totalPower, 2.5); // Максимум 2.5x
    }

    /**
     * Рассчитать уворот (Dodge).
     */
    public function calculateDodge(): float
    {
        $baseDodge = 5.0; // Базовая вероятность 5%
        $agilityBonus = $this->agility * 2; // +2% за единицу ловкости
        $dodgeSkillLevel = $this->getSkillLevel('Уклонение'); // Используем существующий навык

        $totalDodge = $baseDodge + $agilityBonus;

        // Бонус от навыка уклонения: +3% за уровень
        $totalDodge += $dodgeSkillLevel * 3;

        return min($totalDodge, 50.0); // Максимум 50%
    }

    /**
     * Рассчитать уворот от магии (Magic Dodge).
     */
    public function calculateMagicDodge(): float
    {
        $baseDodge = 3.0; // Базовая вероятность 3%
        $intelligenceBonus = $this->intelligence * 1.5; // +1.5% за единицу интеллекта
        $magicDodgeLevel = $this->getSkillLevel('magic_dodge');

        $totalDodge = $baseDodge + $intelligenceBonus;

        // Бонус от навыка уворота от магии: +2% за уровень
        $totalDodge += $magicDodgeLevel * 2;

        return min($totalDodge, 40.0); // Максимум 40%
    }

    /**
     * Рассчитать шанс применения магии (Spell Success Chance).
     */
    public function calculateSpellSuccessChance(): float
    {
        $baseChance = 70.0; // Базовая вероятность 70%
        $intelligenceBonus = $this->intelligence * 3; // +3% за единицу интеллекта
        $spellcastingLevel = $this->getSkillLevel('spellcasting');

        $totalChance = $baseChance + $intelligenceBonus;

        // Бонус от навыка колдовства: +3% за уровень
        $totalChance += $spellcastingLevel * 3;

        return min($totalChance, 95.0); // Максимум 95%
    }

    /**
     * Рассчитать наблюдательность (Awareness).
     */
    public function calculateAwareness(): int
    {
        $baseAwareness = 40.0;
        $awarenessSkillLevel = $this->getSkillLevel('awareness');

        $totalAwareness = $baseAwareness;

        // Бонус от навыка наблюдательности: +2 за уровень
        $totalAwareness += $awarenessSkillLevel * 0.5;

        return $totalAwareness;
    }

    /**
     * Рассчитать скрытность (Stealth).
     */
    public function calculateStealth(): int
    {
        $baseStealth = 20.0;
        $stealthSkillLevel = $this->getSkillLevel('stealth');

        // Базовая скрытность от ловкости
        $totalStealth = $baseStealth * $this->agility * 0.5;

        // Бонус от навыка скрытности: +2 за уровень
        $totalStealth += $stealthSkillLevel * 2;

        return $totalStealth;
    }

    /**
     * Рассчитать шанс украсть (Steal Chance) против цели.
     */
    public function calculateStealChance(Character $target): float
    {
        $baseChance = 20.0; // Базовая вероятность 20%
        $agilityBonus = $this->agility * 2; // +2% за единицу ловкости
        $stealingSkillLevel = $this->getSkillLevel('stealing');
        $targetAwareness = $target->calculateAwareness();

        $totalChance = $baseChance + $agilityBonus;

        // Бонус от навыка воровства: +5% за уровень
        $totalChance += $stealingSkillLevel * 5;

        // Штраф от наблюдательности цели: -3% за единицу наблюдательности
        $totalChance -= $targetAwareness * 3;

        return max(min($totalChance, 80.0), 5.0); // От 5% до 80%
    }

    /**
     * Рассчитать скорость регенерации здоровья за 15 секунд.
     * Базовая скорость: 1 единица за 15 секунд.
     * На максимальном уровне навыка (10): 8 единиц за 15 секунд.
     */
    public function calculateHealthRegenerationRate(): int
    {
        $baseRate = 1; // Базовая скорость: 1 единица за 15 секунд
        $healthRegenerationLevel = $this->getSkillLevel('health_regeneration');

        // Каждый уровень навыка добавляет 0.7 единицы регенерации
        // На уровне 10: 1 + round(10 * 0.7) = 1 + 7 = 8 единиц
        // Используем round для правильного округления
        $skillBonus = (int) round($healthRegenerationLevel * 0.7);

        return $baseRate + $skillBonus;
    }

    /**
     * Рассчитать скорость регенерации маны за 15 секунд.
     * Базовая скорость: 1 единица за 15 секунд при интеллекте 1 и навыке 0.
     * При интеллекте 10 и навыке 10: 12 единиц за 15 секунд.
     */
    public function calculateManaRegenerationRate(): int
    {
        $baseRate = 1; // Базовая скорость: 1 единица за 15 секунд
        $manaRegenerationLevel = $this->getSkillLevel('mana_regeneration');

        // Интеллект добавляет (intelligence - 1) * 0.5 единицы регенерации
        // При интеллекте 1: 0, при интеллекте 10: 4.5
        $intelligenceBonus = ($this->intelligence - 1) * 0.5;

        // Навык добавляет skill_level * 0.65 единицы регенерации
        // При навыке 0: 0, при навыке 10: 6.5
        $skillBonus = $manaRegenerationLevel * 0.65;

        // Итого: 1 + 4.5 + 6.5 = 12 при интеллекте 10 и навыке 10
        $totalRate = $baseRate + $intelligenceBonus + $skillBonus;

        return (int) round($totalRate);
    }

    /**
     * Торговые транзакции персонажа.
     */
    public function tradeTransactions(): HasMany
    {
        return $this->hasMany(TradeTransaction::class);
    }

    /**
     * Банковские ячейки персонажа.
     */
    public function bankStorages(): HasMany
    {
        return $this->hasMany(BankStorage::class);
    }

    /**
     * Улучшения банковского хранилища персонажа.
     */
    public function bankStorageUpgrades(): HasMany
    {
        return $this->hasMany(BankStorageUpgrade::class);
    }

    /**
     * Получить ID предмета золота (золотая монета).
     */
    private function getGoldItemId(): int
    {
        // ID золотой монеты из базы данных
        // Можно получить динамически или использовать константу
        static $goldItemId = null;

        if ($goldItemId === null) {
            $goldItem = Item::where('type', 'currency')
                ->where('subtype', 'coin')
                ->where('name', 'LIKE', '%золот%монет%')
                ->first();

            $goldItemId = $goldItem ? $goldItem->id : 80; // Fallback на известный ID
        }

        return $goldItemId;
    }

    /**
     * Получить количество золота у персонажа из инвентаря.
     */
    public function getGold(): int
    {
        $goldItemId = $this->getGoldItemId();
        $goldItem = $this->inventoryItems()
            ->where('item_id', $goldItemId)
            ->first();

        return $goldItem ? $goldItem->quantity : 0;
    }

    /**
     * Добавить золото персонажу.
     */
    public function addGold(int $amount): void
    {
        if ($amount <= 0) {
            return;
        }

        $goldItemId = $this->getGoldItemId();
        $goldItem = $this->inventoryItems()
            ->where('item_id', $goldItemId)
            ->first();

        if ($goldItem) {
            $goldItem->quantity += $amount;
            $goldItem->save();
        } else {
            ItemInstance::create([
                'item_id' => $goldItemId,
                'owner_id' => $this->id,
                'location_type' => 'inventory',
                'location_id' => $this->id,
                'quantity' => $amount,
            ]);
        }
    }

    /**
     * Потратить золото персонажа.
     *
     * @return bool Возвращает true, если золота достаточно и операция успешна
     */
    public function spendGold(int $amount): bool
    {
        if ($amount <= 0) {
            return true;
        }

        $goldItemId = $this->getGoldItemId();
        $goldItem = $this->inventoryItems()
            ->where('item_id', $goldItemId)
            ->first();

        if (! $goldItem || $goldItem->quantity < $amount) {
            return false;
        }

        $goldItem->quantity -= $amount;

        if ($goldItem->quantity <= 0) {
            $goldItem->delete();
        } else {
            $goldItem->save();
        }

        return true;
    }

    /**
     * Проверить, достаточно ли золота у персонажа.
     */
    public function hasEnoughGold(int $amount): bool
    {
        return $this->getGold() >= $amount;
    }

    /**
     * Проверить, может ли персонаж добавить предметы в инвентарь.
     *
     * @param  \App\Models\Item  $item  Предмет для добавления
     * @param  int  $quantity  Количество предметов
     * @return array ['can_add' => bool, 'reason' => string|null]
     */
    public function canAddItemsToInventory(Item $item, int $quantity): array
    {
        // Максимальное количество слотов в инвентаре (можно сделать настраиваемым)
        $maxInventorySlots = 50;

        // Если предмет стакуемый, проверяем существующие стаки
        if ($item->stackable) {
            $existingStack = $this->inventoryItems()
                ->where('item_id', $item->id)
                ->first();

            if ($existingStack) {
                // Есть существующий стак, проверяем, можно ли добавить к нему
                $availableSpace = $item->max_stack - $existingStack->quantity;
                if ($availableSpace >= $quantity) {
                    return ['can_add' => true, 'reason' => null];
                }

                // Часть можно добавить к существующему стаку, остальное - новые слоты
                $remainingQuantity = $quantity - $availableSpace;
                $neededSlots = (int) ceil($remainingQuantity / $item->max_stack);
            } else {
                // Новый стак
                $neededSlots = (int) ceil($quantity / $item->max_stack);
            }
        } else {
            // Нестакуемый предмет - каждый экземпляр занимает отдельный слот
            $neededSlots = $quantity;
        }

        // Подсчитываем занятые слоты (каждый ItemInstance = 1 слот)
        $occupiedSlots = $this->inventoryItems()->count();

        if ($occupiedSlots + $neededSlots > $maxInventorySlots) {
            return [
                'can_add' => false,
                'reason' => "Недостаточно места в инвентаре. Занято: {$occupiedSlots}/{$maxInventorySlots}, требуется: {$neededSlots}",
            ];
        }

        return ['can_add' => true, 'reason' => null];
    }
}
