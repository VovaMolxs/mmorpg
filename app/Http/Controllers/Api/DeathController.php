<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Character;
use App\Models\CharacterDeath;
use App\Models\CorpseContainer;
use App\Models\GhostState;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeathController extends Controller
{
    /**
     * Обработка смерти персонажа.
     * POST /api/character/die
     */
    public function die(Request $request): JsonResponse
    {
        try {
            $character = $this->getActiveCharacter($request);

            if (! $character) {
                return response()->json([
                    'error' => 'Активный персонаж не найден',
                ], 404);
            }

            // Проверка, что персонаж действительно мертв
            if (! $character->isDead()) {
                return response()->json([
                    'error' => 'Персонаж не мертв',
                ], 400);
            }

            // Проверка, что персонаж еще не является призраком
            if ($character->isGhost()) {
                return response()->json([
                    'error' => 'Персонаж уже является призраком',
                ], 400);
            }

            DB::beginTransaction();

            try {
                // Создаем запись о смерти
                $death = CharacterDeath::create([
                    'character_id' => $character->id,
                    'location_id' => $character->location_id,
                    'killed_by_id' => $request->input('killed_by_id'),
                    'killed_by_type' => $request->input('killed_by_type', 'environment'), // npc, player, environment
                    'death_cause' => $request->input('death_cause', 'combat'), // combat, fall, poison, etc.
                    'died_at' => now(),
                ]);

                // Собираем все предметы персонажа (инвентарь + экипировка)
                $inventoryItems = $character->inventoryItems()->with('item')->get();
                $equippedItems = $character->equippedItems()->with('item')->get();

                $itemsData = [
                    'inventory' => $inventoryItems->map(function ($item) {
                        return [
                            'item_instance_id' => $item->id,
                            'item_id' => $item->item_id,
                            'quantity' => $item->quantity,
                            'durability_current' => $item->durability_current,
                            'item_data' => [
                                'name' => $item->item->name,
                                'type' => $item->item->type,
                                'subtype' => $item->item->subtype,
                                'rarity' => $item->item->rarity,
                            ],
                        ];
                    })->toArray(),
                    'equipment' => $equippedItems->map(function ($item) {
                        return [
                            'item_instance_id' => $item->id,
                            'item_id' => $item->item_id,
                            'quantity' => $item->quantity,
                            'durability_current' => $item->durability_current,
                            'item_data' => [
                                'name' => $item->item->name,
                                'type' => $item->item->type,
                                'subtype' => $item->item->subtype,
                                'rarity' => $item->item->rarity,
                            ],
                        ];
                    })->toArray(),
                ];

                // Определяем тип трупа на основе статуса персонажа
                $corpseType = $character->is_criminal ? 'criminal' : 'innocent';

                // Создаем труп-контейнер
                $corpse = CorpseContainer::create([
                    'character_id' => $character->id,
                    'death_id' => $death->id,
                    'location_id' => $character->location_id,
                    'corpse_type' => $corpseType,
                    'items_data' => $itemsData,
                    'expires_at' => now()->addMinutes(60), // 60 минут до исчезновения
                    'is_looted' => false,
                ]);

                // Удаляем предметы из инвентаря и экипировки (они теперь в трупе)
                $inventoryItems->each(function ($item) {
                    $item->delete();
                });
                $equippedItems->each(function ($item) {
                    $item->delete();
                });

                // Создаем состояние призрака
                $ghostState = GhostState::create([
                    'character_id' => $character->id,
                    'death_id' => $death->id,
                    'location_id' => $character->location_id,
                    'is_visible' => true,
                ]);

                // Устанавливаем персонажа в состояние смерти
                $character->setDead();
                $character->save();

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Персонаж умер и стал призраком',
                    'death' => [
                        'id' => $death->id,
                        'died_at' => $death->died_at->toIso8601String(),
                        'death_cause' => $death->death_cause,
                    ],
                    'corpse' => [
                        'id' => $corpse->id,
                        'location_id' => $corpse->location_id,
                        'corpse_type' => $corpse->corpse_type,
                        'expires_at' => $corpse->expires_at->toIso8601String(),
                        'remaining_minutes' => $corpse->getRemainingMinutes(),
                    ],
                    'ghost_state' => [
                        'id' => $ghostState->id,
                        'is_visible' => $ghostState->is_visible,
                    ],
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Character death failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()?->id,
            ]);

            return response()->json([
                'error' => 'Произошла ошибка при обработке смерти: '.$e->getMessage(),
            ], 500);
        }
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
