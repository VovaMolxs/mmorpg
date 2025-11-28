<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CharacterDeath extends Model
{
    protected $fillable = [
        'character_id',
        'location_id',
        'killed_by_id',
        'killed_by_type',
        'death_cause',
        'died_at',
    ];

    protected function casts(): array
    {
        return [
            'died_at' => 'datetime',
        ];
    }

    /**
     * Персонаж, который умер.
     */
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    /**
     * Локация, где произошла смерть.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Труп-контейнер, связанный с этой смертью.
     */
    public function corpse(): HasOne
    {
        return $this->hasOne(CorpseContainer::class, 'death_id');
    }

    /**
     * Состояние призрака, связанное с этой смертью.
     */
    public function ghostState(): HasOne
    {
        return $this->hasOne(GhostState::class, 'death_id');
    }
}
