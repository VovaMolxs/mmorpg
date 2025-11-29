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
     * Присутствие персонажей в этой локации.
     */
    public function presence(): HasMany
    {
        return $this->hasMany(CharacterPresence::class);
    }

    /**
     * Онлайн персонажи в этой локации.
     */
    public function onlinePlayers(): HasMany
    {
        return $this->hasMany(CharacterPresence::class)
            ->where('status', 'online')
            ->where('is_visible', true);
    }

    /**
     * Предметы на земле в этой локации.
     */
    public function itemsOnGround(): HasMany
    {
        return $this->hasMany(ItemInstance::class, 'location_id')
            ->where('location_type', 'ground')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Настройки спавна предметов в этой локации.
     */
    public function itemSpawns(): HasMany
    {
        return $this->hasMany(LocationItemSpawn::class);
    }

    /**
     * NPC в этой локации.
     */
    public function npcs(): HasMany
    {
        return $this->hasMany(Npc::class);
    }

    /**
     * Настройки спавна NPC в этой локации.
     */
    public function npcSpawns(): HasMany
    {
        return $this->hasMany(NpcSpawn::class);
    }

    /**
     * Активные NPC в этой локации.
     */
    public function activeNpcs(): HasMany
    {
        return $this->hasMany(ActiveNpc::class)
            ->where('is_active', true);
    }

    /**
     * Трупы в этой локации.
     */
    public function corpses(): HasMany
    {
        return $this->hasMany(CorpseContainer::class)
            ->where('expires_at', '>', now())
            ->where('is_looted', false);
    }

    /**
     * Призраки в этой локации.
     */
    public function ghosts(): HasMany
    {
        return $this->hasMany(GhostState::class)
            ->where('is_visible', true);
    }

    /**
     * Камни воскрешения в этой локации.
     */
    public function resurrectionStones(): HasMany
    {
        return $this->hasMany(ResurrectionStone::class)
            ->where('is_active', true);
    }

    /**
     * Настройки спавна монстров в этой локации.
     */
    public function monsterSpawns(): HasMany
    {
        return $this->hasMany(MonsterSpawn::class);
    }

    /**
     * Активные монстры в этой локации.
     */
    public function activeMonsters(): HasMany
    {
        return $this->hasMany(ActiveMonster::class)
            ->where('is_active', true);
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
