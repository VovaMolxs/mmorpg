<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CharacterSkill extends Model
{
    protected $fillable = [
        'character_id',
        'skill_id',
        'level',
        'experience',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'level' => 'integer',
            'experience' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Персонаж.
     */
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    /**
     * Навык.
     */
    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }
}
