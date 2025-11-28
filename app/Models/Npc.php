<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Npc extends Model
{
    protected $fillable = [
        'name',
        'type',
        'location_id',
        'description',
        'is_merchant',
        'is_teacher',
        'is_quest_giver',
        'is_hostile',
        'faction_id',
        'ai_behavior',
        'respawn_time',
        'merchant_buy_types',
    ];

    protected function casts(): array
    {
        return [
            'is_merchant' => 'boolean',
            'is_teacher' => 'boolean',
            'is_quest_giver' => 'boolean',
            'is_hostile' => 'boolean',
            'respawn_time' => 'integer',
            'merchant_buy_types' => 'array',
        ];
    }

    /**
     * Локация NPC.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Статистика NPC.
     */
    public function stats(): HasOne
    {
        return $this->hasOne(NpcStat::class);
    }

    /**
     * Лут NPC.
     */
    public function loot(): HasMany
    {
        return $this->hasMany(NpcLoot::class);
    }

    /**
     * Навыки NPC.
     */
    public function skills(): HasMany
    {
        return $this->hasMany(NpcSkill::class);
    }

    /**
     * Экипировка NPC.
     */
    public function equipment(): HasMany
    {
        return $this->hasMany(NpcEquipment::class);
    }

    /**
     * Настройки спавна этого NPC.
     */
    public function spawns(): HasMany
    {
        return $this->hasMany(NpcSpawn::class);
    }

    /**
     * Активные экземпляры этого NPC.
     */
    public function activeInstances(): HasMany
    {
        return $this->hasMany(ActiveNpc::class)
            ->where('is_active', true);
    }

    /**
     * Проверить, является ли NPC торговцем.
     */
    public function isMerchant(): bool
    {
        return $this->is_merchant;
    }

    /**
     * Проверить, является ли NPC учителем.
     */
    public function isTeacher(): bool
    {
        return $this->is_teacher;
    }

    /**
     * Проверить, является ли NPC квестодателем.
     */
    public function isQuestGiver(): bool
    {
        return $this->is_quest_giver;
    }

    /**
     * Проверить, является ли NPC враждебным.
     */
    public function isHostile(): bool
    {
        return $this->is_hostile;
    }

    /**
     * Проверить, является ли NPC пассивным.
     */
    public function isPassive(): bool
    {
        return $this->ai_behavior === 'passive';
    }

    /**
     * Проверить, является ли NPC нейтральным.
     */
    public function isNeutral(): bool
    {
        return $this->ai_behavior === 'neutral';
    }

    /**
     * Проверить, является ли NPC агрессивным.
     */
    public function isAggressive(): bool
    {
        return $this->ai_behavior === 'aggressive';
    }

    /**
     * Диалоги NPC.
     */
    public function dialogs(): HasMany
    {
        return $this->hasMany(Dialog::class);
    }

    /**
     * Начальные диалоги NPC.
     */
    public function initialDialogs(): HasMany
    {
        return $this->hasMany(Dialog::class)->where('is_initial', true);
    }

    /**
     * Квесты, которые выдает этот NPC.
     */
    public function questsGiven(): HasMany
    {
        return $this->hasMany(Quest::class, 'quest_giver_npc_id');
    }

    /**
     * Квесты, которые сдаются этому NPC.
     */
    public function questsTurnedIn(): HasMany
    {
        return $this->hasMany(Quest::class, 'turn_in_npc_id');
    }

    /**
     * Ассортимент торговца.
     */
    public function merchantInventory(): HasMany
    {
        return $this->hasMany(MerchantInventory::class);
    }

    /**
     * Торговые транзакции с этим NPC.
     */
    public function tradeTransactions(): HasMany
    {
        return $this->hasMany(TradeTransaction::class);
    }

    /**
     * Банкир (если NPC является банкиром).
     */
    public function banker(): HasOne
    {
        return $this->hasOne(Banker::class);
    }

    /**
     * Проверить, является ли NPC банкиром.
     */
    public function isBanker(): bool
    {
        return $this->banker !== null;
    }

    /**
     * Проверить, покупает ли торговец предметы указанного типа.
     */
    public function canBuyItemType(string $itemType): bool
    {
        if (! $this->is_merchant) {
            return false;
        }

        // Если список типов не указан, торговец покупает все типы
        if (empty($this->merchant_buy_types)) {
            return true;
        }

        return in_array($itemType, $this->merchant_buy_types, true);
    }
}
