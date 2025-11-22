<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Skill extends Model
{
    protected $fillable = [
        'name',
        'description',
        'category',
        'max_level',
        'is_starting_skill',
        'required_level',
        'attribute_requirements',
        'parent_skill_id',
        'mana_cost',
        'cooldown',
    ];

    protected function casts(): array
    {
        return [
            'max_level' => 'integer',
            'is_starting_skill' => 'boolean',
            'required_level' => 'integer',
            'attribute_requirements' => 'array',
            'mana_cost' => 'integer',
            'cooldown' => 'integer',
        ];
    }

    /**
     * Родительский навык.
     */
    public function parentSkill(): BelongsTo
    {
        return $this->belongsTo(Skill::class, 'parent_skill_id');
    }

    /**
     * Дочерние навыки.
     */
    public function childSkills(): HasMany
    {
        return $this->hasMany(Skill::class, 'parent_skill_id');
    }

    /**
     * Проверить, доступен ли навык для персонажа.
     */
    public function isAvailableForCharacter(Character $character): bool
    {
        if ($character->level < $this->required_level) {
            return false;
        }

        if ($this->attribute_requirements) {
            foreach ($this->attribute_requirements as $attribute => $requiredValue) {
                $characterValue = match ($attribute) {
                    'strength' => $character->strength,
                    'agility' => $character->agility,
                    'intelligence' => $character->intelligence,
                    default => 0,
                };

                if ($characterValue < $requiredValue) {
                    return false;
                }
            }
        }

        if ($this->parent_skill_id) {
            $parentSkill = $character->characterSkills()
                ->where('skill_id', $this->parent_skill_id)
                ->where('level', '>=', 1)
                ->exists();

            if (! $parentSkill) {
                return false;
            }
        }

        return true;
    }

    /**
     * Получить стартовые навыки.
     */
    public static function getStartingSkills()
    {
        return self::where('is_starting_skill', true)->get();
    }
}
