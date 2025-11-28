<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankStorageUpgrade extends Model
{
    protected $fillable = [
        'character_id',
        'banker_id',
        'additional_slots',
        'purchase_cost',
        'purchased_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'additional_slots' => 'integer',
            'purchase_cost' => 'integer',
            'purchased_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * Владелец улучшения.
     */
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    /**
     * Банкир, у которого куплено улучшение.
     */
    public function banker(): BelongsTo
    {
        return $this->belongsTo(Banker::class);
    }

    /**
     * Проверить, активно ли улучшение.
     */
    public function isActive(): bool
    {
        if ($this->expires_at === null) {
            return true; // Постоянное улучшение
        }

        return $this->expires_at->isFuture();
    }

    /**
     * Проверить, истекло ли улучшение.
     */
    public function isExpired(): bool
    {
        if ($this->expires_at === null) {
            return false; // Постоянное улучшение не истекает
        }

        return $this->expires_at->isPast();
    }
}
