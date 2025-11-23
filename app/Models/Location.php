<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    protected $fillable = [
        'name',
        'description',
        'type',
        'is_safe_zone',
        'min_level',
        'max_level',
        'coordinate_x',
        'coordinate_y',
        'image_url',
        'background_music',
    ];

    protected function casts(): array
    {
        return [
            'is_safe_zone' => 'boolean',
            'min_level' => 'integer',
            'max_level' => 'integer',
            'coordinate_x' => 'integer',
            'coordinate_y' => 'integer',
        ];
    }

    /**
     * Выходы из этой локации.
     */
    public function exits(): HasMany
    {
        return $this->hasMany(LocationExit::class, 'from_location_id');
    }

    /**
     * Входы в эту локацию.
     */
    public function entrances(): HasMany
    {
        return $this->hasMany(LocationExit::class, 'to_location_id');
    }

    /**
     * Персонажи в этой локации.
     */
    public function characters(): HasMany
    {
        return $this->hasMany(Character::class);
    }

    /**
     * Проверить, доступна ли локация для персонажа по уровню.
     */
    public function isAccessibleByLevel(int $level): bool
    {
        if ($this->min_level !== null && $level < $this->min_level) {
            return false;
        }

        if ($this->max_level !== null && $level > $this->max_level) {
            return false;
        }

        return true;
    }

    /**
     * Получить локацию по координатам.
     */
    public static function findByCoordinates(int $x, int $y): ?self
    {
        return self::where('coordinate_x', $x)
            ->where('coordinate_y', $y)
            ->first();
    }

    /**
     * Получить соседние локации в радиусе.
     */
    public function getNearbyLocations(int $radius = 2): \Illuminate\Database\Eloquent\Collection
    {
        return self::whereBetween('coordinate_x', [$this->coordinate_x - $radius, $this->coordinate_x + $radius])
            ->whereBetween('coordinate_y', [$this->coordinate_y - $radius, $this->coordinate_y + $radius])
            ->where('id', '!=', $this->id)
            ->get();
    }
}
