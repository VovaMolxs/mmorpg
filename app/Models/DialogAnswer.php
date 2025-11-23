<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DialogAnswer extends Model
{
    protected $fillable = [
        'dialog_id',
        'text',
        'next_dialog_id',
        'quest_trigger_id',
        'item_required_id',
        'skill_required',
        'skill_level_required',
    ];

    protected function casts(): array
    {
        return [
            'skill_level_required' => 'integer',
        ];
    }

    /**
     * Диалог, к которому относится ответ.
     */
    public function dialog(): BelongsTo
    {
        return $this->belongsTo(Dialog::class);
    }

    /**
     * Следующий диалог после выбора этого ответа.
     */
    public function nextDialog(): BelongsTo
    {
        return $this->belongsTo(Dialog::class, 'next_dialog_id');
    }

    /**
     * Квест, который запускается при выборе этого ответа.
     */
    public function questTrigger(): BelongsTo
    {
        return $this->belongsTo(Quest::class, 'quest_trigger_id');
    }

    /**
     * Требуемый предмет для выбора этого ответа.
     */
    public function itemRequired(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_required_id');
    }

    /**
     * Требуемый навык для выбора этого ответа.
     */
    public function skillRequired(): BelongsTo
    {
        return $this->belongsTo(Skill::class, 'skill_required');
    }

    /**
     * Проверить, доступен ли ответ для персонажа.
     */
    public function isAvailableForCharacter(Character $character): bool
    {
        // Проверка требуемого предмета
        if ($this->item_required_id !== null) {
            $hasItem = $character->inventoryItems()
                ->where('item_id', $this->item_required_id)
                ->exists();

            if (! $hasItem) {
                return false;
            }
        }

        // Проверка требуемого навыка
        if ($this->skill_required !== null && $this->skill_level_required !== null) {
            $skillLevel = $character->getSkillLevel($this->skillRequired->name);

            if ($skillLevel < $this->skill_level_required) {
                return false;
            }
        }

        return true;
    }
}
