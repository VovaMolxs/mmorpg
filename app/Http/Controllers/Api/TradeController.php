<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BuyItemRequest;
use App\Http\Requests\SellItemRequest;
use App\Models\ActiveNpc;
use App\Models\Character;
use App\Models\ItemInstance;
use App\Models\MerchantInventory;
use App\Models\Npc;
use App\Models\TradeTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TradeController extends Controller
{
    /**
     * Купить предмет у торговца.
     */
    public function buy(BuyItemRequest $request): JsonResponse
    {
        try {
            $character = $this->getActiveCharacter($request);

            if (! $character) {
                return response()->json([
                    'error' => 'Активный персонаж не найден',
                ], 404);
            }

            $npcId = $request->input('npc_id');
            $merchantInventoryId = $request->input('merchant_inventory_id');
            $quantity = $request->input('quantity');

            // Проверка активного спавна NPC
            $activeNpc = ActiveNpc::where('npc_id', $npcId)
                ->where('is_active', true)
                ->where('location_id', $character->location_id)
                ->first();

            if (! $activeNpc) {
                return response()->json([
                    'error' => 'NPC не находится в вашей локации или не заспавнен',
                ], 403);
            }

            $npc = Npc::find($npcId);

            if (! $npc || ! $npc->is_merchant) {
                return response()->json([
                    'error' => 'Этот NPC не является торговцем',
                ], 403);
            }

            $merchantInventory = MerchantInventory::with('item')
                ->where('id', $merchantInventoryId)
                ->where('npc_id', $npcId)
                ->first();

            if (! $merchantInventory) {
                return response()->json([
                    'error' => 'Предмет не найден в ассортименте торговца',
                ], 404);
            }

            if (! $merchantInventory->isAvailableForPurchase()) {
                return response()->json([
                    'error' => 'Предмет недоступен для покупки',
                ], 403);
            }

            // Проверка лимита количества
            if ($merchantInventory->max_quantity > 0 && $merchantInventory->quantity < $quantity) {
                return response()->json([
                    'error' => 'Недостаточно предметов у торговца. Доступно: '.$merchantInventory->quantity,
                ], 403);
            }

            // Проверка лимита покупок в день
            if ($merchantInventory->purchase_limit_per_day !== null) {
                $todayPurchases = TradeTransaction::where('character_id', $character->id)
                    ->where('npc_id', $npcId)
                    ->where('item_id', $merchantInventory->item_id)
                    ->where('type', 'buy')
                    ->whereDate('created_at', today())
                    ->sum('quantity');

                if ($todayPurchases + $quantity > $merchantInventory->purchase_limit_per_day) {
                    $remaining = $merchantInventory->purchase_limit_per_day - $todayPurchases;

                    return response()->json([
                        'error' => 'Превышен лимит покупок в день. Осталось: '.max(0, $remaining),
                    ], 403);
                }
            }

            $unitPrice = $merchantInventory->getCurrentBuyPrice();
            $totalPrice = $unitPrice * $quantity;

            // Проверка достаточности золота
            $characterGold = $character->getGold();
            if (! $character->hasEnoughGold($totalPrice)) {
                return response()->json([
                    'error' => 'Недостаточно золота. Требуется: '.$totalPrice.', у вас: '.$characterGold,
                ], 403);
            }

            // Проверка места в инвентаре
            $inventoryCheck = $character->canAddItemsToInventory($merchantInventory->item, $quantity);
            if (! $inventoryCheck['can_add']) {
                return response()->json([
                    'error' => $inventoryCheck['reason'],
                ], 403);
            }

            return DB::transaction(function () use ($character, $npc, $merchantInventory, $quantity, $unitPrice, $totalPrice) {
                $characterGoldBefore = $character->getGold();

                // Списываем золото
                if (! $character->spendGold($totalPrice)) {
                    throw new \Exception('Недостаточно золота');
                }

                // Уменьшаем количество у торговца
                $merchantInventory->decreaseQuantity($quantity);
                $merchantInventory->save();

                // Добавляем предметы в инвентарь персонажа
                $item = $merchantInventory->item;
                $remainingQuantity = $quantity;

                // Если предмет стакуемый, пытаемся добавить к существующему стаку
                if ($item->stackable) {
                    $existingStack = ItemInstance::where('item_id', $item->id)
                        ->where('location_type', 'inventory')
                        ->where('location_id', $character->id)
                        ->first();

                    if ($existingStack) {
                        $availableSpace = $item->max_stack - $existingStack->quantity;
                        $addToStack = min($remainingQuantity, $availableSpace);

                        if ($addToStack > 0) {
                            $existingStack->quantity += $addToStack;
                            $existingStack->save();
                            $remainingQuantity -= $addToStack;
                        }
                    }
                }

                // Создаем новые экземпляры для оставшегося количества
                while ($remainingQuantity > 0) {
                    $stackSize = $item->stackable ? min($remainingQuantity, $item->max_stack) : 1;

                    ItemInstance::create([
                        'item_id' => $item->id,
                        'owner_id' => null,
                        'location_type' => 'inventory',
                        'location_id' => $character->id,
                        'quantity' => $stackSize,
                    ]);

                    $remainingQuantity -= $stackSize;
                }

                // Записываем транзакцию
                TradeTransaction::create([
                    'character_id' => $character->id,
                    'npc_id' => $npc->id,
                    'item_id' => $item->id,
                    'type' => 'buy',
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                    'character_gold_before' => $characterGoldBefore,
                    'character_gold_after' => $character->getGold(),
                    'metadata' => [
                        'merchant_inventory_id' => $merchantInventory->id,
                    ],
                ]);

                // Обновляем множитель цены (небольшое увеличение после покупки)
                $merchantInventory->updatePriceMultiplier(1.01);

                return response()->json([
                    'success' => true,
                    'message' => 'Предмет успешно куплен',
                    'item' => [
                        'id' => $item->id,
                        'name' => $item->name,
                    ],
                    'quantity' => $quantity,
                    'total_price' => $totalPrice,
                    'character_gold' => $character->getGold(),
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Trade buy failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()?->id,
            ]);

            return response()->json([
                'error' => 'Произошла ошибка при покупке: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Продать предмет торговцу.
     */
    public function sell(SellItemRequest $request): JsonResponse
    {
        try {
            $character = $this->getActiveCharacter($request);

            if (! $character) {
                return response()->json([
                    'error' => 'Активный персонаж не найден',
                ], 404);
            }

            $npcId = $request->input('npc_id');
            $itemInstanceId = $request->input('item_instance_id');
            $quantity = $request->input('quantity');

            // Проверка активного спавна NPC
            $activeNpc = ActiveNpc::where('npc_id', $npcId)
                ->where('is_active', true)
                ->where('location_id', $character->location_id)
                ->first();

            if (! $activeNpc) {
                return response()->json([
                    'error' => 'NPC не находится в вашей локации или не заспавнен',
                ], 403);
            }

            $npc = Npc::find($npcId);

            if (! $npc || ! $npc->is_merchant) {
                return response()->json([
                    'error' => 'Этот NPC не является торговцем',
                ], 403);
            }

            $itemInstance = ItemInstance::with('item')
                ->where('id', $itemInstanceId)
                ->whereIn('location_type', ['inventory', 'equipped'])
                ->where('location_id', $character->id)
                ->first();

            if (! $itemInstance) {
                return response()->json([
                    'error' => 'Предмет не найден в вашем инвентаре или экипировке',
                ], 404);
            }

            // Если предмет экипирован, нужно сначала снять его с экипировки
            if ($itemInstance->location_type === 'equipped') {
                $equipment = \App\Models\CharacterEquipment::where('character_id', $character->id)
                    ->where('item_instance_id', $itemInstance->id)
                    ->first();

                if ($equipment) {
                    $equipment->delete();
                }

                // Перемещаем предмет в инвентарь перед продажей
                $itemInstance->location_type = 'inventory';
                $itemInstance->save();
            }

            if ($itemInstance->quantity < $quantity) {
                return response()->json([
                    'error' => 'Недостаточно предметов. У вас: '.$itemInstance->quantity,
                ], 403);
            }

            $item = $itemInstance->item;

            // Проверка, можно ли продать предмет (не квестовый и т.д.)
            if ($itemInstance->isQuestItem()) {
                return response()->json([
                    'error' => 'Квестовые предметы нельзя продать',
                ], 403);
            }

            // Проверка типа предмета - покупает ли торговец этот тип
            if (! $npc->canBuyItemType($item->type)) {
                return response()->json([
                    'error' => 'Этот торговец не покупает предметы типа: '.$item->type,
                ], 403);
            }

            // Находим запись в ассортименте торговца (если есть)
            $merchantInventory = MerchantInventory::where('npc_id', $npcId)
                ->where('item_id', $item->id)
                ->first();

            // Определяем цену покупки
            if ($merchantInventory && $merchantInventory->canBuyFromPlayer()) {
                // Если предмет есть в ассортименте и торговец его покупает, используем цену из ассортимента
                $unitPrice = $merchantInventory->getCurrentSellPrice();
            } else {
                // Если предмета нет в ассортименте, используем базовую цену (30% от стоимости)
                $unitPrice = (int) ($item->value * 0.3);
            }

            $totalPrice = $unitPrice * $quantity;

            // TODO: Временно отключено добавление проданных предметов в инвентарь торговца
            // // Находим или создаем запись в ассортименте торговца для разрешенных типов
            // $merchantInventory = MerchantInventory::firstOrCreate(
            //     [
            //         'npc_id' => $npcId,
            //         'item_id' => $item->id,
            //     ],
            //     [
            //         'quantity' => 0,
            //         'max_quantity' => 0, // Безлимит для купленных у игроков
            //         'base_price' => 0, // Торговец не продает этот предмет игрокам
            //         'base_sell_price' => (int) ($item->value * 0.3), // Цена покупки у игрока = 30% от стоимости
            //         'price_multiplier' => 1.0,
            //         'is_available' => true,
            //     ]
            // );
            //
            // if (! $merchantInventory->canBuyFromPlayer()) {
            //     return response()->json([
            //         'error' => 'Этот торговец не покупает данный предмет (не установлена цена покупки)',
            //     ], 403);
            // }
            //
            // $unitPrice = $merchantInventory->getCurrentSellPrice();
            // $totalPrice = $unitPrice * $quantity;

            return DB::transaction(function () use ($character, $npc, $itemInstance, $item, $merchantInventory, $quantity, $unitPrice, $totalPrice) {
                $characterGoldBefore = $character->getGold();

                // Удаляем предметы из инвентаря
                if ($itemInstance->quantity === $quantity) {
                    $itemInstance->delete();
                } else {
                    $itemInstance->quantity -= $quantity;
                    $itemInstance->save();
                }

                // Добавляем золото персонажу
                $character->addGold($totalPrice);

                // TODO: Временно отключено увеличение количества у торговца
                // // Увеличиваем количество у торговца
                // if ($merchantInventory) {
                //     $merchantInventory->increaseQuantity($quantity);
                //     $merchantInventory->save();
                // }

                // Записываем транзакцию
                TradeTransaction::create([
                    'character_id' => $character->id,
                    'npc_id' => $npc->id,
                    'item_id' => $item->id,
                    'type' => 'sell',
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                    'character_gold_before' => $characterGoldBefore,
                    'character_gold_after' => $character->getGold(),
                    'metadata' => [
                        'item_instance_id' => $itemInstance->id,
                        'merchant_inventory_id' => $merchantInventory?->id,
                    ],
                ]);

                // TODO: Временно отключено обновление множителя цены
                // // Обновляем множитель цены (небольшое уменьшение после продажи)
                // if ($merchantInventory) {
                //     $merchantInventory->updatePriceMultiplier(0.99);
                // }

                return response()->json([
                    'success' => true,
                    'message' => 'Предмет успешно продан',
                    'item' => [
                        'id' => $item->id,
                        'name' => $item->name,
                    ],
                    'quantity' => $quantity,
                    'total_price' => $totalPrice,
                    'character_gold' => $character->getGold(),
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Trade sell failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()?->id,
            ]);

            return response()->json([
                'error' => 'Произошла ошибка при продаже: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Получить активного персонажа пользователя.
     */
    private function getActiveCharacter($request): ?Character
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
