<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonsterLoot extends Model
{
    protected $table = 'monster_loot';

    protected $fillable = [
        'monster_id',
        'item_id',
        'min_quantity',
        'max_quantity',
        'drop_chance',
        'is_guaranteed',
    ];

    protected function casts(): array
    {
        return [
            'min_quantity' => 'integer',
            'max_quantity' => 'integer',
            'drop_chance' => 'integer',
            'is_guaranteed' => 'boolean',
        ];
    }

    /**
     * Монстр, которому принадлежит этот лут.
     */
    public function monster(): BelongsTo
    {
        return $this->belongsTo(Monster::class);
    }

    /**
     * Предмет в луте.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Проверить, выпал ли предмет (на основе drop_chance или is_guaranteed).
     *
     * @return bool Возвращает true, если предмет выпал
     */
    public function shouldDrop(): bool
    {
        if ($this->is_guaranteed) {
            return true;
        }

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
