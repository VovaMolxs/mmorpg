<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class LocationItemSpawn extends Model
{
    protected $fillable = [
        'location_id',
        'item_id',
        'min_quantity',
        'max_quantity',
        'respawn_time_min',
        'respawn_time_max',
        'max_instances',
        'spawn_chance',
        'is_active',
        'last_spawn_at',
    ];

    protected function casts(): array
    {
        return [
            'min_quantity' => 'integer',
            'max_quantity' => 'integer',
            'respawn_time_min' => 'integer',
            'respawn_time_max' => 'integer',
            'max_instances' => 'integer',
            'spawn_chance' => 'integer',
            'is_active' => 'boolean',
            'last_spawn_at' => 'datetime',
        ];
    }

    /**
     * Локация спавна.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Предмет для спавна.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Проверить, готов ли спавн к созданию нового экземпляра.
     */
    public function isReadyToSpawn(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        // Если еще не было спавна, можно спавнить
        if ($this->last_spawn_at === null) {
            return true;
        }

        // Вычисляем случайное время респавна
        $respawnTimeMinutes = rand($this->respawn_time_min, $this->respawn_time_max);
        $nextSpawnAt = $this->last_spawn_at->copy()->addMinutes($respawnTimeMinutes);

        return Carbon::now()->greaterThanOrEqualTo($nextSpawnAt);
    }

    /**
     * Получить текущее количество экземпляров в локации.
     */
    public function getCurrentInstancesCount(): int
    {
        return ItemInstance::where('location_type', 'ground')
            ->where('location_id', $this->location_id)
            ->where('item_id', $this->item_id)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->count();
    }

    /**
     * Проверить, можно ли создать новый экземпляр.
     */
    public function canSpawn(): bool
    {
        if (! $this->isReadyToSpawn()) {
            return false;
        }

        // Проверяем шанс спавна
        if (rand(1, 100) > $this->spawn_chance) {
            return false;
        }

        // Проверяем максимальное количество экземпляров
        if ($this->getCurrentInstancesCount() >= $this->max_instances) {
            return false;
        }

        return true;
    }

    /**
     * Создать экземпляр предмета.
     */
    public function spawn(): ?ItemInstance
    {
        if (! $this->canSpawn()) {
            return null;
        }

        $quantity = rand($this->min_quantity, $this->max_quantity);

        $instance = ItemInstance::create([
            'item_id' => $this->item_id,
            'quantity' => $quantity,
            'location_type' => 'ground',
            'location_id' => $this->location_id,
            'position_x' => rand(1, 100),
            'position_y' => rand(1, 100),
            'expires_at' => now()->addHours(2),
        ]);

        $this->last_spawn_at = now();
        $this->save();

        return $instance;
    }
}
