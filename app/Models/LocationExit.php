<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LocationExit extends Model
{
    protected $fillable = [
        'from_location_id',
        'to_location_id',
        'direction',
        'custom_name',
        'is_locked',
        'required_item_id',
        'required_skill',
        'required_skill_level',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'is_locked' => 'boolean',
            'required_skill_level' => 'integer',
        ];
    }

    /**
     * Локация, из которой ведет выход.
     */
    public function fromLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'from_location_id');
    }

    /**
     * Локация, в которую ведет выход.
     */
    public function toLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'to_location_id');
    }

    /**
     * Предмет, требуемый для открытия перехода.
     */
    public function requiredItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'required_item_id');
    }

    /**
     * Проверить, доступен ли переход для персонажа.
     */
    public function isAccessible(Character $character): array
    {
        $errors = [];

        // Проверка закрытости перехода
        if ($this->is_locked) {
            // Проверка наличия требуемого предмета
            if ($this->required_item_id) {
                $hasItem = $character->inventoryItems()
                    ->where('item_id', $this->required_item_id)
                    ->exists();

                if (! $hasItem) {
                    $item = $this->requiredItem;
                    $errors[] = $item ? "Требуется предмет: {$item->name}" : 'Требуется специальный предмет';
                }
            }

            // Проверка требуемого навыка
            if ($this->required_skill && $this->required_skill_level) {
                $skillLevel = $character->getSkillLevel($this->required_skill);
                if ($skillLevel < $this->required_skill_level) {
                    $errors[] = "Требуется навык {$this->required_skill} уровня {$this->required_skill_level}";
                }
            }

            // Если есть ошибки, переход недоступен
            if (! empty($errors)) {
                return ['accessible' => false, 'errors' => $errors];
            }
        }

        // Проверка уровня целевой локации
        $toLocation = $this->toLocation;
        if ($toLocation && ! $toLocation->isAccessibleByLevel($character->level)) {
            $levelRequirement = '';
            if ($toLocation->min_level !== null) {
                $levelRequirement = "минимум {$toLocation->min_level}";
            }
            if ($toLocation->max_level !== null) {
                $levelRequirement .= ($levelRequirement ? ', ' : '')."максимум {$toLocation->max_level}";
            }
            $errors[] = "Требуется уровень: {$levelRequirement}";
        }

        return [
            'accessible' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Найти выход по направлению или кастомному имени.
     */
    public static function findExit(Location $fromLocation, string $directionOrName): ?self
    {
        return self::where('from_location_id', $fromLocation->id)
            ->where(function ($query) use ($directionOrName) {
                $query->where('direction', $directionOrName)
                    ->orWhere('custom_name', $directionOrName);
            })
            ->first();
    }
}
