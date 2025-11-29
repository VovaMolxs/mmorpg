<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActiveMonster;
use App\Models\Location;
use App\Models\Monster;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MonsterController extends Controller
{
    /**
     * Получить список всех монстров (шаблонов).
     */
    public function index(Request $request): JsonResponse
    {
        $monsters = Monster::with(['stats', 'loot.item', 'skills'])
            ->where('is_active', true)
            ->get();

        return response()->json([
            'monsters' => $monsters->map(function (Monster $monster) {
                return $this->formatMonster($monster);
            }),
        ]);
    }

    /**
     * Получить информацию о конкретном монстре (шаблоне).
     */
    public function show(Request $request, Monster $monster): JsonResponse
    {
        $monster->load(['stats', 'loot.item', 'skills']);

        return response()->json([
            'monster' => $this->formatMonster($monster),
        ]);
    }

    /**
     * Получить активных монстров в локации.
     */
    public function locationMonsters(Request $request, Location $location): JsonResponse
    {
        $monsters = ActiveMonster::where('location_id', $location->id)
            ->where('is_active', true)
            ->with(['monster.stats', 'monster.skills'])
            ->get();

        return response()->json([
            'location' => [
                'id' => $location->id,
                'name' => $location->name,
            ],
            'monsters' => $monsters->map(function (ActiveMonster $activeMonster) {
                return $this->formatActiveMonster($activeMonster);
            }),
            'count' => $monsters->count(),
        ]);
    }

    /**
     * Форматировать данные монстра (шаблона) для ответа.
     */
    private function formatMonster(Monster $monster): array
    {
        $stats = $monster->stats;

        return [
            'id' => $monster->id,
            'name' => $monster->name,
            'description' => $monster->description,
            'type' => $monster->type,
            'level' => $monster->level,
            'rank' => $monster->rank,
            'faction_id' => $monster->faction_id,
            'ai_behavior' => $monster->ai_behavior,
            'respawn_time' => $monster->respawn_time,
            'is_active' => $monster->is_active,
            'stats' => $stats ? [
                'health_max' => $stats->health_max,
                'mana_max' => $stats->mana_max,
                'attack_power' => $stats->attack_power,
                'magic_power' => $stats->magic_power,
                'defense' => $stats->defense,
                'magic_defense' => $stats->magic_defense,
                'accuracy' => $stats->accuracy,
                'magic_accuracy' => $stats->magic_accuracy,
                'dodge' => $stats->dodge,
                'critical_chance' => $stats->critical_chance,
                'critical_power' => $stats->critical_power,
                'experience_reward' => $stats->experience_reward,
                'attack_type' => $stats->attack_type,
            ] : null,
            'loot' => $monster->loot->map(function ($loot) {
                return [
                    'id' => $loot->id,
                    'item_id' => $loot->item_id,
                    'item_name' => $loot->item->name ?? null,
                    'min_quantity' => $loot->min_quantity,
                    'max_quantity' => $loot->max_quantity,
                    'drop_chance' => $loot->drop_chance,
                    'is_guaranteed' => $loot->is_guaranteed,
                ];
            }),
            'skills' => $monster->skills->map(function ($skill) {
                return [
                    'id' => $skill->id,
                    'skill_name' => $skill->skill_name,
                    'skill_type' => $skill->skill_type,
                    'damage_type' => $skill->damage_type,
                    'power' => $skill->power,
                    'mana_cost' => $skill->mana_cost,
                    'cooldown' => $skill->cooldown,
                    'chance_to_use' => $skill->chance_to_use,
                    'description' => $skill->description,
                ];
            }),
        ];
    }

    /**
     * Форматировать данные активного монстра для ответа.
     */
    private function formatActiveMonster(ActiveMonster $activeMonster): array
    {
        $monster = $activeMonster->monster;
        $stats = $monster->stats;

        $healthPercentage = $activeMonster->getHealthPercentage();
        $manaPercentage = $activeMonster->getManaPercentage();

        return [
            'id' => $activeMonster->id,
            'monster_id' => $monster->id,
            'name' => $monster->name,
            'description' => $monster->description,
            'type' => $monster->type,
            'rank' => $monster->rank,
            'level' => $monster->level,
            'ai_behavior' => $monster->ai_behavior,
            'health_current' => $activeMonster->health_current,
            'health_max' => $stats?->health_max ?? 100,
            'health_percentage' => round($healthPercentage, 1),
            'mana_current' => $activeMonster->mana_current,
            'mana_max' => $stats?->mana_max ?? 50,
            'mana_percentage' => round($manaPercentage, 1),
            'attack_type' => $stats?->attack_type ?? 'physical',
            'spawned_at' => $activeMonster->spawned_at?->toIso8601String(),
            'is_dead' => $activeMonster->isDead(),
        ];
    }
}
