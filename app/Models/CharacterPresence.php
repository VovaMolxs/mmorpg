<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CharacterPresence extends Model
{
    protected $table = 'character_presence';

    protected $fillable = [
        'character_id',
        'location_id',
        'is_visible',
        'entered_world_at',
        'last_action_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
            'entered_world_at' => 'datetime',
            'last_action_at' => 'datetime',
            'status' => 'string',
        ];
    }

    /**
     * Персонаж присутствия.
     */
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    /**
     * Локация присутствия.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Установить статус онлайн.
     */
    public function setOnline(): void
    {
        $this->status = 'online';
        $this->is_visible = true;
        $this->entered_world_at = now();
        $this->last_action_at = now();
        $this->save();
    }

    /**
     * Установить статус AFK.
     */
    public function setAfk(): void
    {
        $this->status = 'afk';
        $this->save();
    }

    /**
     * Установить статус оффлайн.
     */
    public function setOffline(): void
    {
        $this->status = 'offline';
        $this->is_visible = false;
        $this->save();
    }

    /**
     * Обновить время последнего действия.
     */
    public function updateLastAction(): void
    {
        $this->last_action_at = now();
        if ($this->status === 'afk') {
            $this->status = 'online';
        }
        $this->save();
    }

    /**
     * Проверить, онлайн ли персонаж.
     */
    public function isOnline(): bool
    {
        return $this->status === 'online';
    }

    /**
     * Проверить, видим ли персонаж.
     */
    public function isVisible(): bool
    {
        return $this->is_visible && $this->isOnline();
    }
}
