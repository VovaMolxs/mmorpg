<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonsterSkill extends Model
{
    protected $fillable = [
        'monster_id',
        'skill_name',
        'skill_type',
        'damage_type',
        'power',
        'mana_cost',
        'cooldown',
        'chance_to_use',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'power' => 'integer',
            'mana_cost' => 'integer',
            'cooldown' => 'integer',
            'chance_to_use' => 'integer',
        ];
    }

    /**
     * Монстр, которому принадлежит эта способность.
     */
    public function monster(): BelongsTo
    {
        return $this->belongsTo(Monster::class);
    }

    /**
     * Проверить, является ли способность атакой.
     */
    public function isAttack(): bool
    {
        return $this->skill_type === 'attack';
    }

    /**
     * Проверить, является ли способность исцелением.
     */
    public function isHeal(): bool
    {
        return $this->skill_type === 'heal';
    }

    /**
     * Проверить, является ли способность баффом.
     */
    public function isBuff(): bool
    {
        return $this->skill_type === 'buff';
    }

    /**
     * Проверить, является ли способность дебаффом.
     */
    public function isDebuff(): bool
    {
        return $this->skill_type === 'debuff';
    }

    /**
     * Проверить, является ли способность призывом.
     */
    public function isSummon(): bool
    {
        return $this->skill_type === 'summon';
    }

    /**
     * Проверить, можно ли использовать способность (на основе chance_to_use).
     *
     * @return bool Возвращает true, если способность может быть использована
     */
    public function canUse(): bool
    {
        return rand(1, 100) <= $this->chance_to_use;
    }
}
