<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestReward extends Model
{
    protected $fillable = [
        'quest_id',
        'type',
        'reward_id',
        'quantity',
        'is_choice',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'is_choice' => 'boolean',
        ];
    }

    /**
     * Квест, к которому относится награда.
     */
    public function quest(): BelongsTo
    {
        return $this->belongsTo(Quest::class);
    }

    /**
     * Получить предмет награды (если тип - item).
     */
    public function rewardItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'reward_id');
    }

    /**
     * Получить навык награды (если тип - skill).
     */
    public function rewardSkill(): BelongsTo
    {
        return $this->belongsTo(Skill::class, 'reward_id');
    }
}
