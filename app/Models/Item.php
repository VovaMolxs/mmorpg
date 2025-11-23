<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    protected $fillable = [
        'name',
        'description',
        'type',
        'subtype',
        'rarity',
        'level_required',
        'stackable',
        'max_stack',
        'weight',
        'value',
        'requirements',
        'weapon_data',
        'armor_data',
        'jewelry_data',
        'potion_data',
        'rune_data',
        'scroll_data',
        'resource_data',
    ];

    protected function casts(): array
    {
        return [
            'level_required' => 'integer',
            'stackable' => 'boolean',
            'max_stack' => 'integer',
            'weight' => 'decimal:2',
            'value' => 'integer',
            'requirements' => 'array',
            'weapon_data' => 'array',
            'armor_data' => 'array',
            'jewelry_data' => 'array',
            'potion_data' => 'array',
            'rune_data' => 'array',
            'scroll_data' => 'array',
            'resource_data' => 'array',
        ];
    }

    /**
     * Экземпляры этого предмета.
     */
    public function instances(): HasMany
    {
        return $this->hasMany(ItemInstance::class);
    }

    /**
     * Настройки спавна этого предмета в локациях.
     */
    public function spawns(): HasMany
    {
        return $this->hasMany(LocationItemSpawn::class);
    }

    /**
     * Проверить, является ли предмет оружием.
     */
    public function isWeapon(): bool
    {
        return $this->type === 'weapon';
    }

    /**
     * Проверить, является ли предмет броней.
     */
    public function isArmor(): bool
    {
        return $this->type === 'armor';
    }

    /**
     * Проверить, является ли предмет бижутерией.
     */
    public function isJewelry(): bool
    {
        return $this->type === 'jewelry';
    }

    /**
     * Проверить, является ли предмет руной.
     */
    public function isRune(): bool
    {
        return $this->type === 'rune';
    }

    /**
     * Проверить, является ли предмет свитком.
     */
    public function isScroll(): bool
    {
        return $this->type === 'scroll';
    }

    /**
     * Проверить, является ли предмет зельем.
     */
    public function isPotion(): bool
    {
        return $this->type === 'potion';
    }

    /**
     * Проверить, является ли предмет ресурсом.
     */
    public function isResource(): bool
    {
        return $this->type === 'resource';
    }

    /**
     * Получить данные оружия.
     */
    public function getWeaponData(): ?array
    {
        return $this->weapon_data;
    }

    /**
     * Получить данные брони.
     */
    public function getArmorData(): ?array
    {
        return $this->armor_data;
    }

    /**
     * Получить данные бижутерии.
     */
    public function getJewelryData(): ?array
    {
        return $this->jewelry_data;
    }

    /**
     * Получить данные зелья.
     */
    public function getPotionData(): ?array
    {
        return $this->potion_data;
    }

    /**
     * Получить данные руны.
     */
    public function getRuneData(): ?array
    {
        return $this->rune_data;
    }

    /**
     * Получить данные свитка.
     */
    public function getScrollData(): ?array
    {
        return $this->scroll_data;
    }

    /**
     * Получить данные ресурса.
     */
    public function getResourceData(): ?array
    {
        return $this->resource_data;
    }

    /**
     * Получить требования к использованию предмета.
     */
    public function getRequirements(): array
    {
        return $this->requirements ?? [];
    }

    /**
     * Проверить, соответствует ли персонаж требованиям предмета.
     */
    public function checkRequirements(Character $character): array
    {
        $errors = [];
        $requirements = $this->getRequirements();

        // Проверка уровня
        if (isset($requirements['level']) && $character->level < $requirements['level']) {
            $errors[] = "Требуется уровень {$requirements['level']}";
        }

        // Проверка характеристик
        if (isset($requirements['attributes'])) {
            foreach ($requirements['attributes'] as $attribute => $requiredValue) {
                $characterValue = match ($attribute) {
                    'strength' => $character->strength,
                    'agility' => $character->agility,
                    'intelligence' => $character->intelligence,
                    default => 0,
                };

                if ($characterValue < $requiredValue) {
                    $attributeName = match ($attribute) {
                        'strength' => 'Сила',
                        'agility' => 'Ловкость',
                        'intelligence' => 'Интеллект',
                        default => $attribute,
                    };
                    $errors[] = "Требуется {$attributeName}: {$requiredValue}";
                }
            }
        }

        // Проверка навыков
        if (isset($requirements['skills'])) {
            foreach ($requirements['skills'] as $skillName => $requiredLevel) {
                $characterSkill = $character->skills()->where('name', $skillName)->first();
                $skillLevel = $characterSkill?->pivot->level ?? 0;

                if ($skillLevel < $requiredLevel) {
                    $errors[] = "Требуется навык {$skillName} уровня {$requiredLevel}";
                }
            }
        }

        return $errors;
    }

    /**
     * Получить слот экипировки для предмета.
     */
    public function getEquipmentSlot(): ?string
    {
        return match ($this->type) {
            'weapon' => match ($this->subtype) {
                'sword', 'axe', 'dagger', 'bow', 'crossbow', 'staff' => 'weapon_main',
                default => null,
            },
            'armor' => match ($this->subtype) {
                'helmet' => 'head',
                'chest' => 'chest',
                'legs' => 'legs',
                'hands' => 'hands',
                'feet' => 'feet',
                default => null,
            },
            'jewelry' => match ($this->subtype) {
                'amulet' => 'amulet',
                'ring' => 'ring1', // По умолчанию первый слот кольца
                'earring' => 'earring',
                default => null,
            },
            'rune' => 'amulet', // Руны занимают слот амулета
            default => null,
        };
    }
}
