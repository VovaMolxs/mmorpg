<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MerchantInventory extends Model
{
    protected $fillable = [
        'npc_id',
        'item_id',
        'quantity',
        'max_quantity',
        'base_price',
        'base_sell_price',
        'price_multiplier',
        'purchase_limit_per_day',
        'is_available',
        'restocked_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'max_quantity' => 'integer',
            'base_price' => 'integer',
            'base_sell_price' => 'integer',
            'price_multiplier' => 'decimal:2',
            'purchase_limit_per_day' => 'integer',
            'is_available' => 'boolean',
            'restocked_at' => 'datetime',
        ];
    }

    /**
     * NPC торговец.
     */
    public function npc(): BelongsTo
    {
        return $this->belongsTo(Npc::class);
    }

    /**
     * Предмет в ассортименте.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Получить текущую цену покупки с учетом множителя.
     */
    public function getCurrentBuyPrice(): int
    {
        return (int) round($this->base_price * $this->price_multiplier);
    }

    /**
     * Получить текущую цену продажи с учетом множителя.
     */
    public function getCurrentSellPrice(): int
    {
        return (int) round($this->base_sell_price * $this->price_multiplier);
    }

    /**
     * Проверить, доступен ли предмет для покупки.
     */
    public function isAvailableForPurchase(): bool
    {
        if (! $this->is_available) {
            return false;
        }

        if ($this->max_quantity > 0 && $this->quantity <= 0) {
            return false;
        }

        return true;
    }

    /**
     * Проверить, может ли торговец купить этот предмет.
     */
    public function canBuyFromPlayer(): bool
    {
        return $this->base_sell_price > 0;
    }

    /**
     * Уменьшить количество предмета.
     */
    public function decreaseQuantity(int $amount): bool
    {
        if ($this->max_quantity > 0 && $this->quantity < $amount) {
            return false;
        }

        if ($this->max_quantity > 0) {
            $this->quantity = max(0, $this->quantity - $amount);
        }

        return true;
    }

    /**
     * Увеличить количество предмета.
     */
    public function increaseQuantity(int $amount): void
    {
        if ($this->max_quantity > 0) {
            $this->quantity = min($this->max_quantity, $this->quantity + $amount);
        }
    }

    /**
     * Обновить множитель цены на основе количества сделок.
     */
    public function updatePriceMultiplier(float $factor): void
    {
        $this->price_multiplier = max(0.1, min(5.0, $this->price_multiplier * $factor));
    }
}
