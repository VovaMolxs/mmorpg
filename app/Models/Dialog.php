<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dialog extends Model
{
    protected $fillable = [
        'npc_id',
        'parent_dialog_id',
        'text',
        'is_initial',
        'min_level',
        'required_quest_id',
        'required_quest_status',
    ];

    protected function casts(): array
    {
        return [
            'is_initial' => 'boolean',
            'min_level' => 'integer',
        ];
    }

    /**
     * NPC, которому принадлежит диалог.
     */
    public function npc(): BelongsTo
    {
        return $this->belongsTo(Npc::class);
    }

    /**
     * Родительский диалог.
     */
    public function parentDialog(): BelongsTo
    {
        return $this->belongsTo(Dialog::class, 'parent_dialog_id');
    }

    /**
     * Дочерние диалоги.
     */
    public function childDialogs(): HasMany
    {
        return $this->hasMany(Dialog::class, 'parent_dialog_id');
    }

    /**
     * Требуемый квест для показа диалога.
     */
    public function requiredQuest(): BelongsTo
    {
        return $this->belongsTo(Quest::class, 'required_quest_id');
    }

    /**
     * Ответы на этот диалог.
     */
    public function answers(): HasMany
    {
        return $this->hasMany(DialogAnswer::class);
    }

    /**
     * Проверить, доступен ли диалог для персонажа.
     */
    public function isAvailableForCharacter(Character $character): bool
    {
        // Проверка минимального уровня
        if ($this->min_level !== null && $character->level < $this->min_level) {
            return false;
        }

        // Проверка требуемого квеста
        if ($this->required_quest_id !== null) {
            $characterQuest = CharacterQuest::where('character_id', $character->id)
                ->where('quest_id', $this->required_quest_id)
                ->first();

            if (! $characterQuest) {
                return false;
            }

            if ($this->required_quest_status !== null && $characterQuest->status !== $this->required_quest_status) {
                return false;
            }
        }

        return true;
    }
}
