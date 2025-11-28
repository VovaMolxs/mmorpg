<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class CorpseContainer extends Model
{
    protected $fillable = [
        'character_id',
        'death_id',
        'location_id',
        'corpse_type',
        'items_data',
        'expires_at',
        'is_looted',
    ];

    protected function casts(): array
    {
        return [
            'items_data' => 'array',
            'expires_at' => 'datetime',
            'is_looted' => 'boolean',
        ];
    }

    /**
     * Персонаж, которому принадлежит труп.
     */
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    /**
     * Запись о смерти, связанная с этим трупом.
     */
    public function death(): BelongsTo
    {
        return $this->belongsTo(CharacterDeath::class, 'death_id');
    }

    /**
     * Локация, где находится труп.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Проверить, истекло ли время жизни трупа.
     */
    public function isExpired(): bool
    {
        return Carbon::now()->greaterThan($this->expires_at);
    }

    /**
     * Получить оставшееся время до исчезновения в минутах.
     */
    public function getRemainingMinutes(): int
    {
        if ($this->isExpired()) {
            return 0;
        }

        return max(0, (int) Carbon::now()->diffInMinutes($this->expires_at, false));
    }

    /**
     * Проверить, принадлежит ли труп персонажу.
     */
    public function belongsToCharacter(Character $character): bool
    {
        return $this->character_id === $character->id;
    }
}
