<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

class ItemInstance extends Model
{
    protected $fillable = [
        'item_id',
        'quantity',
        'durability_current',
        'location_type',
        'location_id',
        'position_x',
        'position_y',
        'expires_at',
        'owner_id',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'durability_current' => 'integer',
            'position_x' => 'integer',
            'position_y' => 'integer',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * Шаблон предмета.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Владелец (для квестовых предметов).
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'owner_id');
    }

    /**
     * Экипировка персонажа (если предмет экипирован).
     */
    public function equipment(): HasOne
    {
        return $this->hasOne(CharacterEquipment::class);
    }

    /**
     * Проверить, находится ли предмет в инвентаре.
     */
    public function isInInventory(): bool
    {
        return $this->location_type === 'inventory';
    }

    /**
     * Проверить, экипирован ли предмет.
     */
    public function isEquipped(): bool
    {
        return $this->location_type === 'equipped';
    }

    /**
     * Проверить, находится ли предмет на земле.
     */
    public function isOnGround(): bool
    {
        return $this->location_type === 'ground';
    }

    /**
     * Проверить, является ли предмет квестовым (не исчезает).
     */
    public function isQuestItem(): bool
    {
        return $this->owner_id !== null;
    }

    /**
     * Проверить, истекло ли время жизни предмета.
     */
    public function isExpired(): bool
    {
        if ($this->expires_at === null) {
            return false;
        }

        return Carbon::now()->greaterThan($this->expires_at);
    }

    /**
     * Проверить, должен ли предмет исчезнуть.
     */
    public function shouldExpire(): bool
    {
        // Квестовые предметы не исчезают
        if ($this->isQuestItem()) {
            return false;
        }

        // Предметы на земле исчезают через 30 минут
        if ($this->isOnGround() && $this->expires_at !== null) {
            return $this->isExpired();
        }

        return false;
    }

    /**
     * Установить предмет на землю с временем жизни 30 минут.
     */
    public function dropOnGround(int $x, int $y): void
    {
        $this->location_type = 'ground';
        $this->location_id = null;
        $this->position_x = $x;
        $this->position_y = $y;
        $this->expires_at = Carbon::now()->addMinutes(30);
        $this->save();
    }

    /**
     * Переместить предмет в инвентарь персонажа.
     */
    public function moveToInventory(int $characterId, ?int $x = null, ?int $y = null): void
    {
        $this->location_type = 'inventory';
        $this->location_id = $characterId;
        $this->position_x = $x;
        $this->position_y = $y;
        $this->expires_at = null;
        $this->save();
    }

    /**
     * Разделить стак предметов.
     */
    public function splitStack(int $quantity): ?ItemInstance
    {
        $item = $this->item ?? $this->item()->first();
        if (! $item || ! $item->stackable) {
            return null;
        }

        if ($quantity >= $this->quantity) {
            return null;
        }

        $newInstance = $this->replicate();
        $newInstance->quantity = $quantity;
        $newInstance->save();

        $this->quantity -= $quantity;
        $this->save();

        return $newInstance;
    }

    /**
     * Объединить стак с другим экземпляром.
     */
    public function mergeStack(ItemInstance $otherInstance): bool
    {
        if ($this->item_id !== $otherInstance->item_id) {
            return false;
        }

        $item = $this->item ?? $this->item()->first();
        if (! $item || ! $item->stackable) {
            return false;
        }

        $totalQuantity = $this->quantity + $otherInstance->quantity;
        $maxStack = $item->max_stack;

        if ($totalQuantity <= $maxStack) {
            $this->quantity = $totalQuantity;
            $this->save();
            $otherInstance->delete();

            return true;
        }

        // Если превышает максимум, заполняем до максимума
        $this->quantity = $maxStack;
        $otherInstance->quantity = $totalQuantity - $maxStack;
        $this->save();
        $otherInstance->save();

        return true;
    }

    /**
     * Уменьшить прочность предмета.
     */
    public function reduceDurability(int $amount = 1): bool
    {
        if ($this->durability_current === null) {
            return false;
        }

        $this->durability_current = max(0, $this->durability_current - $amount);

        if ($this->durability_current === 0) {
            // Предмет сломан
            return false;
        }

        $this->save();

        return true;
    }

    /**
     * Восстановить прочность предмета.
     */
    public function repairDurability(?int $amount = null): void
    {
        if ($this->durability_current === null || $this->item->armor_data === null) {
            return;
        }

        $maxDurability = $this->item->armor_data['durability_max'] ?? $this->item->weapon_data['durability_max'] ?? null;

        if ($maxDurability === null) {
            return;
        }

        if ($amount === null) {
            $this->durability_current = $maxDurability;
        } else {
            $this->durability_current = min($maxDurability, $this->durability_current + $amount);
        }

        $this->save();
    }
}
