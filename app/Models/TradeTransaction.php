<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TradeTransaction extends Model
{
    protected $fillable = [
        'character_id',
        'npc_id',
        'item_id',
        'type',
        'quantity',
        'unit_price',
        'total_price',
        'character_gold_before',
        'character_gold_after',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'integer',
            'total_price' => 'integer',
            'character_gold_before' => 'integer',
            'character_gold_after' => 'integer',
            'metadata' => 'array',
        ];
    }

    /**
     * Персонаж, совершивший сделку.
     */
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    /**
     * NPC торговец.
     */
    public function npc(): BelongsTo
    {
        return $this->belongsTo(Npc::class);
    }

    /**
     * Предмет в сделке.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Проверить, является ли сделка покупкой.
     */
    public function isBuy(): bool
    {
        return $this->type === 'buy';
    }

    /**
     * Проверить, является ли сделка продажей.
     */
    public function isSell(): bool
    {
        return $this->type === 'sell';
    }
}
