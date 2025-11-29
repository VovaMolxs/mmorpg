<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonsterStat extends Model
{
    protected $fillable = [
        'monster_id',
        'health_max',
        'mana_max',
        'attack_power',
        'magic_power',
        'defense',
        'magic_defense',
        'accuracy',
        'magic_accuracy',
        'dodge',
        'critical_chance',
        'critical_power',
        'experience_reward',
        'attack_type',
    ];

    protected function casts(): array
    {
        return [
            'health_max' => 'integer',
            'mana_max' => 'integer',
            'attack_power' => 'integer',
            'magic_power' => 'integer',
            'defense' => 'integer',
            'magic_defense' => 'integer',
            'accuracy' => 'decimal:2',
            'magic_accuracy' => 'decimal:2',
            'dodge' => 'decimal:2',
            'critical_chance' => 'decimal:2',
            'critical_power' => 'decimal:2',
            'experience_reward' => 'integer',
        ];
    }

    /**
     * Монстр, которому принадлежат эти характеристики.
     */
    public function monster(): BelongsTo
    {
        return $this->belongsTo(Monster::class);
    }

    /**
     * Проверить, использует ли монстр физические атаки.
     */
    public function usesPhysicalAttack(): bool
    {
        return in_array($this->attack_type, ['physical', 'hybrid'], true);
    }

    /**
     * Проверить, использует ли монстр магические атаки.
     */
    public function usesMagicalAttack(): bool
    {
        return in_array($this->attack_type, ['magical', 'hybrid'], true);
    }
}
