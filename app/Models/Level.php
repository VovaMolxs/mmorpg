<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    protected $fillable = [
        'level',
        'experience_required',
        'reward_points',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'level' => 'integer',
            'experience_required' => 'integer',
            'reward_points' => 'integer',
        ];
    }

    /**
     * Получить уровень по опыту.
     */
    public static function getLevelByExperience(int $experience): ?self
    {
        return self::where('experience_required', '<=', $experience)
            ->orderBy('level', 'desc')
            ->first();
    }

    /**
     * Получить следующий уровень.
     */
    public function getNextLevel(): ?self
    {
        return self::where('level', '>', $this->level)
            ->orderBy('level', 'asc')
            ->first();
    }
}
