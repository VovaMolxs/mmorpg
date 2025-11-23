<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CharacterQuest extends Model
{
    protected $fillable = [
        'character_id',
        'quest_id',
        'status',
        'started_at',
        'completed_at',
        'current_progress',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'current_progress' => 'array',
        ];
    }

    /**
     * Персонаж, которому принадлежит квест.
     */
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    /**
     * Квест.
     */
    public function quest(): BelongsTo
    {
        return $this->belongsTo(Quest::class);
    }

    /**
     * Проверить, выполнены ли все цели квеста.
     */
    public function areAllObjectivesCompleted(): bool
    {
        $objectives = $this->quest->objectives;
        $progress = $this->current_progress ?? [];

        foreach ($objectives as $objective) {
            if (! $objective->isCompleted($progress)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Обновить прогресс цели.
     */
    public function updateObjectiveProgress(int $objectiveId, int $amount = 1): void
    {
        $progress = $this->current_progress ?? [];
        $progress[$objectiveId] = ($progress[$objectiveId] ?? 0) + $amount;
        $this->current_progress = $progress;
        $this->save();
    }

    /**
     * Завершить квест.
     */
    public function complete(): void
    {
        $this->status = 'completed';
        $this->completed_at = now();
        $this->save();
    }

    /**
     * Провалить квест.
     */
    public function fail(): void
    {
        $this->status = 'failed';
        $this->save();
    }

    /**
     * Отменить квест.
     */
    public function abandon(): void
    {
        $this->status = 'abandoned';
        $this->save();
    }

    /**
     * Статический метод для обновления прогресса квестов персонажа.
     * Вызывается из других частей системы (бой, сбор предметов и т.д.).
     */
    public static function updateQuestProgress(Character $character, string $objectiveType, int $targetId, int $amount = 1): void
    {
        $activeQuests = self::where('character_id', $character->id)
            ->where('status', 'active')
            ->with('quest.objectives')
            ->get();

        foreach ($activeQuests as $characterQuest) {
            $objectives = $characterQuest->quest->objectives
                ->where('type', $objectiveType)
                ->where('target_id', $targetId);

            foreach ($objectives as $objective) {
                $characterQuest->updateObjectiveProgress($objective->id, $amount);
            }
        }
    }
}
