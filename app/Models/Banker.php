<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Banker extends Model
{
    protected $fillable = [
        'npc_id',
        'location_id',
        'storage_slots',
        'base_fee',
        'fee_per_slot',
        'max_upgrade_slots',
    ];

    protected function casts(): array
    {
        return [
            'storage_slots' => 'integer',
            'base_fee' => 'integer',
            'fee_per_slot' => 'integer',
            'max_upgrade_slots' => 'integer',
        ];
    }

    /**
     * NPC-банкир.
     */
    public function npc(): BelongsTo
    {
        return $this->belongsTo(Npc::class);
    }

    /**
     * Локация банкира.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Ячейки хранилища у этого банкира.
     */
    public function storages(): HasMany
    {
        return $this->hasMany(BankStorage::class);
    }

    /**
     * Улучшения хранилища у этого банкира.
     */
    public function upgrades(): HasMany
    {
        return $this->hasMany(BankStorageUpgrade::class);
    }

    /**
     * Получить общее количество доступных слотов для персонажа.
     */
    public function getTotalSlotsForCharacter(Character $character): int
    {
        $baseSlots = $this->storage_slots;

        // Суммируем все активные улучшения персонажа
        $additionalSlots = $this->upgrades()
            ->where('character_id', $character->id)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->sum('additional_slots');

        return $baseSlots + $additionalSlots;
    }

    /**
     * Рассчитать плату за хранение для персонажа.
     */
    public function calculateStorageFee(Character $character, int $usedSlots): int
    {
        $totalSlots = $this->getTotalSlotsForCharacter($character);
        $freeSlots = $this->storage_slots; // Базовые бесплатные слоты

        if ($usedSlots <= $freeSlots) {
            return $this->base_fee;
        }

        $paidSlots = $usedSlots - $freeSlots;

        return $this->base_fee + ($paidSlots * $this->fee_per_slot);
    }
}
