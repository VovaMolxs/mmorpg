<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestObjective extends Model
{
    protected $fillable = [
        'quest_id',
        'type',
        'target_id',
        'target_name',
        'required_count',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'required_count' => 'integer',
        ];
    }

    /**
     * Квест, к которому относится цель.
     */
    public function quest(): BelongsTo
    {
        return $this->belongsTo(Quest::class);
    }

    /**
     * Проверить, выполнена ли цель.
     */
    public function isCompleted(array $progress): bool
    {
        $currentCount = $progress[$this->id] ?? 0;

        return $currentCount >= $this->required_count;
    }

    /**
     * Получить текущий прогресс цели.
     */
    public function getCurrentProgress(array $progress): int
    {
        return $progress[$this->id] ?? 0;
    }
}
