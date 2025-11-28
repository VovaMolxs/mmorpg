<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankStorage extends Model
{
    protected $fillable = [
        'character_id',
        'banker_id',
        'slot_number',
        'item_instance_id',
        'is_locked',
        'deposited_at',
        'withdrawn_at',
    ];

    protected function casts(): array
    {
        return [
            'slot_number' => 'integer',
            'is_locked' => 'boolean',
            'deposited_at' => 'datetime',
            'withdrawn_at' => 'datetime',
        ];
    }

    /**
     * Владелец ячейки.
     */
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    /**
     * Банкир, у которого хранится предмет.
     */
    public function banker(): BelongsTo
    {
        return $this->belongsTo(Banker::class);
    }

    /**
     * Экземпляр предмета в ячейке.
     */
    public function itemInstance(): BelongsTo
    {
        return $this->belongsTo(ItemInstance::class);
    }

    /**
     * Проверить, свободна ли ячейка.
     */
    public function isEmpty(): bool
    {
        return $this->item_instance_id === null;
    }

    /**
     * Проверить, занята ли ячейка.
     */
    public function isOccupied(): bool
    {
        return $this->item_instance_id !== null;
    }

    /**
     * Проверить, можно ли изъять предмет из ячейки.
     */
    public function canWithdraw(): bool
    {
        return $this->isOccupied() && ! $this->is_locked;
    }
}
