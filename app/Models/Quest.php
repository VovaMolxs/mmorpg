<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quest extends Model
{
    protected $fillable = [
        'name',
        'description',
        'min_level',
        'max_level',
        'quest_giver_npc_id',
        'turn_in_npc_id',
        'previous_quest_id',
        'faction_required_id',
        'reputation_required',
    ];

    protected function casts(): array
    {
        return [
            'min_level' => 'integer',
            'max_level' => 'integer',
            'reputation_required' => 'integer',
        ];
    }

    /**
     * NPC, который выдает квест.
     */
    public function questGiver(): BelongsTo
    {
        return $this->belongsTo(Npc::class, 'quest_giver_npc_id');
    }

    /**
     * NPC, которому сдается квест.
     */
    public function turnInNpc(): BelongsTo
    {
        return $this->belongsTo(Npc::class, 'turn_in_npc_id');
    }

    /**
     * Предыдущий квест, который должен быть выполнен.
     */
    public function previousQuest(): BelongsTo
    {
        return $this->belongsTo(Quest::class, 'previous_quest_id');
    }

    /**
     * Следующие квесты, которые требуют этот квест.
     */
    public function nextQuests(): HasMany
    {
        return $this->hasMany(Quest::class, 'previous_quest_id');
    }

    /**
     * Цели квеста.
     */
    public function objectives(): HasMany
    {
        return $this->hasMany(QuestObjective::class);
    }

    /**
     * Награды за квест.
     */
    public function rewards(): HasMany
    {
        return $this->hasMany(QuestReward::class);
    }

    /**
     * Квесты персонажей.
     */
    public function characterQuests(): HasMany
    {
        return $this->hasMany(CharacterQuest::class);
    }

    /**
     * Проверить, доступен ли квест для персонажа.
     */
    public function isAvailableForCharacter(Character $character): bool
    {
        // Проверка минимального уровня
        if ($this->min_level !== null && $character->level < $this->min_level) {
            return false;
        }

        // Проверка максимального уровня
        if ($this->max_level !== null && $character->level > $this->max_level) {
            return false;
        }

        // Проверка предыдущего квеста
        if ($this->previous_quest_id !== null) {
            $previousQuestCompleted = CharacterQuest::where('character_id', $character->id)
                ->where('quest_id', $this->previous_quest_id)
                ->where('status', 'completed')
                ->exists();

            if (! $previousQuestCompleted) {
                return false;
            }
        }

        // Проверка репутации (если система репутации реализована)
        // TODO: Реализовать проверку репутации, когда будет готова система фракций

        // Проверка, не взят ли уже этот квест
        $alreadyActive = CharacterQuest::where('character_id', $character->id)
            ->where('quest_id', $this->id)
            ->where('status', 'active')
            ->exists();

        if ($alreadyActive) {
            return false;
        }

        return true;
    }
}
