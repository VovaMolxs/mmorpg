<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NpcSkill extends Model
{
    protected $fillable = [
        'npc_id',
        'skill_id',
        'level',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'level' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * NPC, которому принадлежит этот навык.
     */
    public function npc(): BelongsTo
    {
        return $this->belongsTo(Npc::class);
    }

    /**
     * Навык.
     */
    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }

    /**
     * Проверить, активен ли навык.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }
}
