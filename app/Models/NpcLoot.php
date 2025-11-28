<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NpcLoot extends Model
{
    protected $table = 'npc_loot';

    protected $fillable = [
        'npc_id',
        'item_id',
        'min_quantity',
        'max_quantity',
        'drop_chance',
    ];

    protected function casts(): array
    {
        return [
            'min_quantity' => 'integer',
            'max_quantity' => 'integer',
            'drop_chance' => 'integer',
        ];
    }

    /**
     * NPC, которому принадлежит этот лут.
     */
    public function npc(): BelongsTo
    {
        return $this->belongsTo(Npc::class);
    }

    /**
     * Предмет в луте.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Проверить, выпал ли предмет (на основе drop_chance).
     *
     * @return bool Возвращает true, если предмет выпал
     */
    public function shouldDrop(): bool
    {
        return rand(1, 100) <= $this->drop_chance;
    }

    /**
     * Получить случайное количество предмета.
     *
     * @return int Количество предмета
     */
    public function getRandomQuantity(): int
    {
        if ($this->min_quantity >= $this->max_quantity) {
            return $this->min_quantity;
        }

        return rand($this->min_quantity, $this->max_quantity);
    }
}
