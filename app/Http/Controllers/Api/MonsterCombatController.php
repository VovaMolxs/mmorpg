<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActiveMonster;
use App\Models\Character;
use App\Models\ItemInstance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MonsterCombatController extends Controller
{
    /**
     * Атака монстра персонажем.
     */
    public function attack(Request $request, ActiveMonster $monster): JsonResponse
    {
        try {
            $character = $this->getActiveCharacter($request);

            if (! $character) {
                return response()->json([
                    'error' => 'Активный персонаж не найден',
                ], 404);
            }

            // Проверяем, что персонаж и монстр в одной локации
            if ($character->location_id !== $monster->location_id) {
                return response()->json([
                    'error' => 'Монстр находится в другой локации',
                ], 403);
            }

            // Проверяем, что монстр жив
            if ($monster->isDead() || ! $monster->is_active) {
                return response()->json([
                    'error' => 'Монстр уже мертв',
                ], 400);
            }

            // Проверяем, что персонаж жив
            if ($character->isDead()) {
                return response()->json([
                    'error' => 'Вы мертвы и не можете атаковать',
                ], 400);
            }

            return DB::transaction(function () use ($character, $monster) {
                // Вычисляем урон (упрощенная формула)
                $damage = $this->calculateDamage($character, $monster);

                // Применяем урон к монстру
                $isDead = $monster->takeDamage($damage);
                $monster->save();

                $result = [
                    'success' => true,
                    'damage' => $damage,
                    'monster' => [
                        'id' => $monster->id,
                        'health_current' => $monster->health_current,
                        'health_max' => $monster->getStats()?->health_max ?? 100,
                        'health_percentage' => round($monster->getHealthPercentage(), 1),
                        'is_dead' => $isDead,
                    ],
                ];

                // Если монстр убит, генерируем лут и даем опыт
                if ($isDead) {
                    $loot = $this->generateLoot($monster);
                    $experience = $monster->getStats()?->experience_reward ?? 0;

                    // Добавляем опыт персонажу (упрощенная версия)
                    // В реальной системе здесь должна быть более сложная логика

                    $result['monster_killed'] = true;
                    $result['experience_gained'] = $experience;
                    $result['loot'] = $loot;
                }

                return response()->json($result);
            });
        } catch (\Exception $e) {
            Log::error('Monster attack failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'monster_id' => $monster->id,
                'character_id' => $character->id ?? null,
            ]);

            return response()->json([
                'error' => 'Произошла ошибка при атаке: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Получить способности монстра.
     */
    public function skills(Request $request, ActiveMonster $monster): JsonResponse
    {
        $monsterTemplate = $monster->monster;

        if (! $monsterTemplate) {
            return response()->json([
                'error' => 'Шаблон монстра не найден',
            ], 404);
        }

        $skills = $monsterTemplate->skills;

        return response()->json([
            'monster' => [
                'id' => $monster->id,
                'name' => $monsterTemplate->name,
            ],
            'skills' => $skills->map(function ($skill) {
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
        ]);
    }

    /**
     * Собрать лут с убитого монстра.
     */
    public function loot(Request $request, ActiveMonster $monster): JsonResponse
    {
        try {
            $character = $this->getActiveCharacter($request);

            if (! $character) {
                return response()->json([
                    'error' => 'Активный персонаж не найден',
                ], 404);
            }

            // Проверяем, что персонаж и монстр в одной локации
            if ($character->location_id !== $monster->location_id) {
                return response()->json([
                    'error' => 'Монстр находится в другой локации',
                ], 403);
            }

            // Проверяем, что монстр мертв
            if (! $monster->isDead() || $monster->is_active) {
                return response()->json([
                    'error' => 'Монстр еще жив',
                ], 400);
            }

            return DB::transaction(function () use ($monster) {
                // Генерируем лут
                $loot = $this->generateLoot($monster);

                // Создаем экземпляры предметов на земле
                $createdItems = [];
                foreach ($loot as $lootItem) {
                    $itemInstance = ItemInstance::create([
                        'item_id' => $lootItem['item_id'],
                        'quantity' => $lootItem['quantity'],
                        'location_type' => 'ground',
                        'location_id' => $monster->location_id,
                        'position_x' => rand(0, 100), // Случайная позиция
                        'position_y' => rand(0, 100),
                    ]);

                    $createdItems[] = [
                        'id' => $itemInstance->id,
                        'item_id' => $lootItem['item_id'],
                        'item_name' => $lootItem['item_name'],
                        'quantity' => $lootItem['quantity'],
                    ];
                }

                // Помечаем монстра как залученного (можно добавить флаг в таблицу)
                // Для простоты просто удаляем активного монстра или помечаем как неактивного

                return response()->json([
                    'success' => true,
                    'message' => 'Лут собран',
                    'items' => $createdItems,
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Monster loot failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'monster_id' => $monster->id,
                'character_id' => $character->id ?? null,
            ]);

            return response()->json([
                'error' => 'Произошла ошибка при сборе лута: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Вычислить урон от персонажа к монстру.
     */
    private function calculateDamage(Character $character, ActiveMonster $monster): int
    {
        // Упрощенная формула урона
        // В реальной системе здесь должна быть более сложная логика с учетом оружия, навыков и т.д.
        $baseDamage = $character->strength * 2 + $character->level * 5;
        $monsterDefense = $monster->getStats()?->defense ?? 0;

        $damage = max(1, $baseDamage - ($monsterDefense / 2));

        // Добавляем случайность ±20%
        $variation = rand(80, 120);
        $damage = (int) ($damage * $variation / 100);

        return max(1, $damage);
    }

    /**
     * Генерировать лут с монстра.
     */
    private function generateLoot(ActiveMonster $monster): array
    {
        $monsterTemplate = $monster->monster;

        if (! $monsterTemplate) {
            return [];
        }

        $lootItems = [];
        $lootConfigs = $monsterTemplate->loot;

        foreach ($lootConfigs as $lootConfig) {
            // Проверяем, должен ли предмет выпасть
            if ($lootConfig->shouldDrop()) {
                $quantity = $lootConfig->getRandomQuantity();
                $item = $lootConfig->item;

                if ($item) {
                    $lootItems[] = [
                        'item_id' => $item->id,
                        'item_name' => $item->name,
                        'quantity' => $quantity,
                    ];
                }
            }
        }

        return $lootItems;
    }

    /**
     * Получить активного персонажа пользователя.
     */
    private function getActiveCharacter(Request $request): ?Character
    {
        $user = $request->user();
        if (! $user) {
            return null;
        }

        return Character::where('user_id', $user->id)
            ->where('is_active', true)
            ->first();
    }
}
