<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NpcEquipment extends Model
{
    protected $fillable = [
        'npc_id',
        'item_id',
        'slot',
    ];

    /**
     * NPC, которому принадлежит эта экипировка.
     */
    public function npc(): BelongsTo
    {
        return $this->belongsTo(Npc::class);
    }

    /**
     * Предмет экипировки.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
