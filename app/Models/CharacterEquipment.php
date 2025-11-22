<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CharacterEquipment extends Model
{
    protected $fillable = [
        'character_id',
        'item_instance_id',
        'slot',
        'equipped_at',
    ];

    protected function casts(): array
    {
        return [
            'equipped_at' => 'datetime',
        ];
    }

    /**
     * Персонаж, которому принадлежит экипировка.
     */
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    /**
     * Экземпляр предмета.
     */
    public function itemInstance(): BelongsTo
    {
        return $this->belongsTo(ItemInstance::class);
    }

    /**
     * Получить слоты экипировки для типа предмета.
     */
    public static function getSlotsForItemType(string $type, ?string $subtype = null): array
    {
        return match ($type) {
            'weapon' => ['weapon_main', 'weapon_offhand'],
            'armor' => match ($subtype) {
                'helmet' => ['head'],
                'chest' => ['chest'],
                'legs' => ['legs'],
                'hands' => ['hands'],
                'feet' => ['feet'],
                default => [],
            },
            'jewelry' => match ($subtype) {
                'amulet' => ['amulet'],
                'ring' => ['ring1', 'ring2'],
                'earring' => ['earring'],
                default => [],
            },
            'rune' => ['amulet'],
            default => [],
        };
    }
}
