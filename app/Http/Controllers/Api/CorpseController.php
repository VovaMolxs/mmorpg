<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Character;
use App\Models\CorpseContainer;
use App\Models\ItemInstance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CorpseController extends Controller
{
    /**
     * Просмотр предметов в трупе.
     * GET /api/corpse/{id}/items
     */
    public function getItems(Request $request, int $corpseId): JsonResponse
    {
        try {
            $character = $this->getActiveCharacter($request);

            if (! $character) {
                return response()->json([
                    'error' => 'Активный персонаж не найден',
                ], 404);
            }

            $corpse = CorpseContainer::with('character', 'location')
                ->find($corpseId);

            if (! $corpse) {
                return response()->json([
                    'error' => 'Труп не найден',
                ], 404);
            }

            // Проверка, что труп не исчез
            if ($corpse->isExpired()) {
                return response()->json([
                    'error' => 'Труп уже исчез',
                ], 400);
            }

            // Проверка, что персонаж находится в той же локации
            if ($character->location_id !== $corpse->location_id) {
                return response()->json([
                    'error' => 'Труп находится в другой локации',
                ], 403);
            }

            $itemsData = $corpse->items_data ?? [];

            return response()->json([
                'success' => true,
                'corpse' => [
                    'id' => $corpse->id,
                    'character_name' => $corpse->character->name,
                    'corpse_type' => $corpse->corpse_type,
                    'expires_at' => $corpse->expires_at->toIso8601String(),
                    'remaining_minutes' => $corpse->getRemainingMinutes(),
                    'is_looted' => $corpse->is_looted,
                    'belongs_to_me' => $corpse->belongsToCharacter($character),
                ],
                'items' => [
                    'inventory' => $itemsData['inventory'] ?? [],
                    'equipment' => $itemsData['equipment'] ?? [],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Get corpse items failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()?->id,
                'corpse_id' => $corpseId,
            ]);

            return response()->json([
                'error' => 'Произошла ошибка при получении предметов: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Забрать предметы с трупа.
     * POST /api/corpse/{id}/loot
     */
    public function loot(Request $request, int $corpseId): JsonResponse
    {
        try {
            $character = $this->getActiveCharacter($request);

            if (! $character) {
                return response()->json([
                    'error' => 'Активный персонаж не найден',
                ], 404);
            }

            $corpse = CorpseContainer::with('character', 'location')
                ->find($corpseId);

            if (! $corpse) {
                return response()->json([
                    'error' => 'Труп не найден',
                ], 404);
            }

            // Проверка, что труп не исчез
            if ($corpse->isExpired()) {
                return response()->json([
                    'error' => 'Труп уже исчез',
                ], 400);
            }

            // Проверка, что персонаж находится в той же локации
            if ($character->location_id !== $corpse->location_id) {
                return response()->json([
                    'error' => 'Труп находится в другой локации',
                ], 403);
            }

            // Проверка, что персонаж не является призраком
            if ($character->isGhost()) {
                return response()->json([
                    'error' => 'Призраки не могут забирать предметы из трупов',
                ], 403);
            }

            $itemInstanceId = $request->input('item_instance_id');
            if (! $itemInstanceId) {
                return response()->json([
                    'error' => 'Не указан ID предмета',
                ], 400);
            }

            $itemsData = $corpse->items_data ?? [];
            $allItems = array_merge($itemsData['inventory'] ?? [], $itemsData['equipment'] ?? []);

            // Находим предмет в данных трупа
            $itemData = null;
            $itemKey = null;
            $itemSource = null;

            foreach ($allItems as $key => $item) {
                if (($item['item_instance_id'] ?? null) == $itemInstanceId) {
                    $itemData = $item;
                    $itemKey = $key;
                    $itemSource = isset($itemsData['inventory'][$key]) ? 'inventory' : 'equipment';
                    break;
                }
            }

            if (! $itemData) {
                return response()->json([
                    'error' => 'Предмет не найден в трупе',
                ], 404);
            }

            DB::beginTransaction();

            try {
                // Получаем шаблон предмета
                $item = \App\Models\Item::find($itemData['item_id']);
                if (! $item) {
                    throw new \Exception('Шаблон предмета не найден');
                }

                // Проверяем, может ли персонаж добавить предмет в инвентарь
                $canAdd = $character->canAddItemsToInventory($item, $itemData['quantity']);
                if (! $canAdd['can_add']) {
                    return response()->json([
                        'error' => $canAdd['reason'],
                    ], 400);
                }

                // Создаем экземпляр предмета в инвентаре персонажа
                $itemInstance = ItemInstance::create([
                    'item_id' => $item->id,
                    'owner_id' => $character->id,
                    'location_type' => 'inventory',
                    'location_id' => $character->id,
                    'quantity' => $itemData['quantity'],
                    'durability_current' => $itemData['durability_current'] ?? null,
                ]);

                // Удаляем предмет из данных трупа
                if ($itemSource === 'inventory') {
                    unset($itemsData['inventory'][$itemKey]);
                    $itemsData['inventory'] = array_values($itemsData['inventory']); // Переиндексация
                } else {
                    unset($itemsData['equipment'][$itemKey]);
                    $itemsData['equipment'] = array_values($itemsData['equipment']); // Переиндексация
                }

                // Проверяем, остались ли предметы в трупе
                $hasItems = ! empty($itemsData['inventory']) || ! empty($itemsData['equipment']);

                $corpse->items_data = $itemsData;
                if (! $hasItems) {
                    $corpse->is_looted = true;
                }
                $corpse->save();

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Предмет успешно подобран',
                    'item' => [
                        'id' => $itemInstance->id,
                        'name' => $item->name,
                        'quantity' => $itemInstance->quantity,
                    ],
                    'corpse' => [
                        'is_looted' => $corpse->is_looted,
                        'remaining_items' => count($itemsData['inventory'] ?? []) + count($itemsData['equipment'] ?? []),
                    ],
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Loot corpse failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()?->id,
                'corpse_id' => $corpseId,
            ]);

            return response()->json([
                'error' => 'Произошла ошибка при подборе предмета: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Забрать все предметы с трупа.
     * POST /api/corpse/{id}/loot-all
     */
    public function lootAll(Request $request, int $corpseId): JsonResponse
    {
        try {
            $character = $this->getActiveCharacter($request);

            if (! $character) {
                return response()->json([
                    'error' => 'Активный персонаж не найден',
                ], 404);
            }

            $corpse = CorpseContainer::with('character', 'location')
                ->find($corpseId);

            if (! $corpse) {
                return response()->json([
                    'error' => 'Труп не найден',
                ], 404);
            }

            // Проверка, что труп не исчез
            if ($corpse->isExpired()) {
                return response()->json([
                    'error' => 'Труп уже исчез',
                ], 400);
            }

            // Проверка, что персонаж находится в той же локации
            if ($character->location_id !== $corpse->location_id) {
                return response()->json([
                    'error' => 'Труп находится в другой локации',
                ], 403);
            }

            $itemsData = $corpse->items_data ?? [];
            $allItems = array_merge($itemsData['inventory'] ?? [], $itemsData['equipment'] ?? []);

            if (empty($allItems)) {
                return response()->json([
                    'error' => 'В трупе нет предметов',
                ], 400);
            }

            DB::beginTransaction();

            try {
                $lootedItems = [];
                $failedItems = [];

                foreach ($allItems as $itemData) {
                    try {
                        // Получаем шаблон предмета
                        $item = \App\Models\Item::find($itemData['item_id']);
                        if (! $item) {
                            $failedItems[] = $itemData['item_instance_id'] ?? 'unknown';

                            continue;
                        }

                        // Проверяем, может ли персонаж добавить предмет в инвентарь
                        $canAdd = $character->canAddItemsToInventory($item, $itemData['quantity']);
                        if (! $canAdd['can_add']) {
                            $failedItems[] = $itemData['item_instance_id'] ?? 'unknown';

                            continue;
                        }

                        // Создаем экземпляр предмета в инвентаре персонажа
                        $itemInstance = ItemInstance::create([
                            'item_id' => $item->id,
                            'owner_id' => $character->id,
                            'location_type' => 'inventory',
                            'location_id' => $character->id,
                            'quantity' => $itemData['quantity'],
                            'durability_current' => $itemData['durability_current'] ?? null,
                        ]);

                        $lootedItems[] = [
                            'id' => $itemInstance->id,
                            'name' => $item->name,
                            'quantity' => $itemInstance->quantity,
                        ];
                    } catch (\Exception $e) {
                        $failedItems[] = $itemData['item_instance_id'] ?? 'unknown';
                        Log::warning('Failed to loot item from corpse', [
                            'item_data' => $itemData,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

                // Очищаем данные трупа
                $corpse->items_data = [
                    'inventory' => [],
                    'equipment' => [],
                ];
                $corpse->is_looted = true;
                $corpse->save();

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Предметы успешно подобраны',
                    'looted_items' => $lootedItems,
                    'failed_items_count' => count($failedItems),
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Loot all from corpse failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()?->id,
                'corpse_id' => $corpseId,
            ]);

            return response()->json([
                'error' => 'Произошла ошибка при подборе предметов: '.$e->getMessage(),
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
