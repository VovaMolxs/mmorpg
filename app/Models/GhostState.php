<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GhostState extends Model
{
    protected $fillable = [
        'character_id',
        'death_id',
        'location_id',
        'is_visible',
    ];

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
        ];
    }

    /**
     * Персонаж-призрак.
     */
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    /**
     * Запись о смерти, связанная с этим призраком.
     */
    public function death(): BelongsTo
    {
        return $this->belongsTo(CharacterDeath::class, 'death_id');
    }

    /**
     * Локация, где находится призрак.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Проверить, является ли персонаж призраком.
     */
    public static function isGhost(Character $character): bool
    {
        return self::where('character_id', $character->id)->exists();
    }
}
