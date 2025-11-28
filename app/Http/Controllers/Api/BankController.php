<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DepositItemRequest;
use App\Http\Requests\UpgradeStorageRequest;
use App\Http\Requests\WithdrawItemRequest;
use App\Models\ActiveNpc;
use App\Models\Banker;
use App\Models\BankStorage;
use App\Models\BankStorageUpgrade;
use App\Models\Character;
use App\Models\ItemInstance;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BankController extends Controller
{
    /**
     * Получить информацию о хранилище персонажа у банкира.
     */
    public function getStorage(int $bankerId): JsonResponse
    {
        try {
            $character = $this->getActiveCharacter(request());

            if (! $character) {
                return response()->json([
                    'error' => 'Активный персонаж не найден',
                ], 404);
            }

            $banker = Banker::with('npc')->find($bankerId);

            if (! $banker) {
                return response()->json([
                    'error' => 'Банкир не найден',
                ], 404);
            }

            // Проверка, что банкир находится в локации персонажа
            if ($banker->location_id !== $character->location_id) {
                return response()->json([
                    'error' => 'Банкир не находится в вашей локации',
                ], 403);
            }

            // Проверка активного спавна NPC
            $activeNpc = ActiveNpc::where('npc_id', $banker->npc_id)
                ->where('is_active', true)
                ->where('location_id', $character->location_id)
                ->first();

            if (! $activeNpc) {
                return response()->json([
                    'error' => 'Банкир не заспавнен в вашей локации',
                ], 403);
            }

            // Получаем все ячейки хранилища персонажа у этого банкира
            $storages = BankStorage::where('character_id', $character->id)
                ->where('banker_id', $bankerId)
                ->with(['itemInstance.item'])
                ->orderBy('slot_number')
                ->get();

            $totalSlots = $banker->getTotalSlotsForCharacter($character);
            $usedSlots = $storages->where('item_instance_id', '!=', null)->count();
            $freeSlots = $totalSlots - $usedSlots;

            // Получаем активные улучшения
            $upgrades = BankStorageUpgrade::where('character_id', $character->id)
                ->where('banker_id', $bankerId)
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })
                ->get();

            $storageFee = $banker->calculateStorageFee($character, $usedSlots);

            return response()->json([
                'success' => true,
                'banker' => [
                    'id' => $banker->id,
                    'npc_name' => $banker->npc->name,
                    'base_slots' => $banker->storage_slots,
                    'base_fee' => $banker->base_fee,
                    'fee_per_slot' => $banker->fee_per_slot,
                ],
                'storage' => [
                    'total_slots' => $totalSlots,
                    'used_slots' => $usedSlots,
                    'free_slots' => $freeSlots,
                    'current_fee' => $storageFee,
                ],
                'slots' => $storages->map(function ($storage) {
                    return [
                        'id' => $storage->id,
                        'slot_number' => $storage->slot_number,
                        'item_instance' => $storage->itemInstance ? [
                            'id' => $storage->itemInstance->id,
                            'item' => [
                                'id' => $storage->itemInstance->item->id,
                                'name' => $storage->itemInstance->item->name,
                                'type' => $storage->itemInstance->item->type,
                            ],
                            'quantity' => $storage->itemInstance->quantity,
                        ] : null,
                        'is_locked' => $storage->is_locked,
                        'deposited_at' => $storage->deposited_at?->toIso8601String(),
                    ];
                }),
                'upgrades' => $upgrades->map(function ($upgrade) {
                    return [
                        'id' => $upgrade->id,
                        'additional_slots' => $upgrade->additional_slots,
                        'purchase_cost' => $upgrade->purchase_cost,
                        'purchased_at' => $upgrade->purchased_at->toIso8601String(),
                        'expires_at' => $upgrade->expires_at?->toIso8601String(),
                        'is_permanent' => $upgrade->expires_at === null,
                    ];
                }),
            ]);
        } catch (\Exception $e) {
            Log::error('Bank getStorage failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => request()->user()?->id,
            ]);

            return response()->json([
                'error' => 'Произошла ошибка при получении информации о хранилище: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Депозит предмета в банк.
     */
    public function deposit(DepositItemRequest $request): JsonResponse
    {
        try {
            $character = $this->getActiveCharacter($request);

            if (! $character) {
                return response()->json([
                    'error' => 'Активный персонаж не найден',
                ], 404);
            }

            $bankerId = $request->input('banker_id');
            $itemInstanceId = $request->input('item_instance_id');
            $quantity = $request->input('quantity');
            $slotNumber = $request->input('slot_number');

            $banker = Banker::with('npc')->find($bankerId);

            if (! $banker) {
                return response()->json([
                    'error' => 'Банкир не найден',
                ], 404);
            }

            // Проверка локации
            if ($banker->location_id !== $character->location_id) {
                return response()->json([
                    'error' => 'Банкир не находится в вашей локации',
                ], 403);
            }

            // Проверка активного спавна NPC
            $activeNpc = ActiveNpc::where('npc_id', $banker->npc_id)
                ->where('is_active', true)
                ->where('location_id', $character->location_id)
                ->first();

            if (! $activeNpc) {
                return response()->json([
                    'error' => 'Банкир не заспавнен в вашей локации',
                ], 403);
            }

            // Получаем экземпляр предмета из инвентаря
            $itemInstance = ItemInstance::with('item')
                ->where('id', $itemInstanceId)
                ->where('location_type', 'inventory')
                ->where('location_id', $character->id)
                ->first();

            if (! $itemInstance) {
                return response()->json([
                    'error' => 'Предмет не найден в вашем инвентаре',
                ], 404);
            }

            $item = $itemInstance->item;

            // Проверка возможности хранения предмета
            $canStoreCheck = $this->canStoreItem($itemInstance);
            if (! $canStoreCheck['can_store']) {
                return response()->json([
                    'error' => $canStoreCheck['reason'],
                ], 403);
            }

            // Определяем количество для депозита
            $depositQuantity = $quantity ?? $itemInstance->quantity;
            if ($depositQuantity > $itemInstance->quantity) {
                $depositQuantity = $itemInstance->quantity;
            }

            // Получаем общее количество доступных слотов
            $totalSlots = $banker->getTotalSlotsForCharacter($character);

            // Получаем занятые слоты
            $usedSlots = BankStorage::where('character_id', $character->id)
                ->where('banker_id', $bankerId)
                ->whereNotNull('item_instance_id')
                ->count();

            // Проверка наличия свободных слотов
            if ($usedSlots >= $totalSlots) {
                return response()->json([
                    'error' => 'Нет свободных слотов в хранилище',
                ], 403);
            }

            return DB::transaction(function () use ($character, $banker, $itemInstance, $item, $depositQuantity, $slotNumber, $bankerId, $usedSlots) {
                // Если предмет стакуемый и есть частичный депозит
                if ($item->stackable && $depositQuantity < $itemInstance->quantity) {
                    // Разделяем стак
                    $remainingInstance = $itemInstance->splitStack($depositQuantity);
                    if (! $remainingInstance) {
                        throw new \Exception('Не удалось разделить стак предметов');
                    }
                    $depositInstance = $itemInstance;
                } else {
                    $depositInstance = $itemInstance;
                }

                // Находим свободную ячейку или используем указанную
                if ($slotNumber) {
                    $storage = BankStorage::firstOrCreate(
                        [
                            'character_id' => $character->id,
                            'banker_id' => $bankerId,
                            'slot_number' => $slotNumber,
                        ],
                        [
                            'item_instance_id' => null,
                            'is_locked' => false,
                        ]
                    );

                    if ($storage->isOccupied()) {
                        throw new \Exception('Ячейка уже занята');
                    }
                } else {
                    // Ищем первую свободную ячейку
                    $storage = BankStorage::where('character_id', $character->id)
                        ->where('banker_id', $bankerId)
                        ->whereNull('item_instance_id')
                        ->orderBy('slot_number')
                        ->first();

                    if (! $storage) {
                        // Создаем новую ячейку с минимальным свободным номером
                        $maxSlot = BankStorage::where('character_id', $character->id)
                            ->where('banker_id', $bankerId)
                            ->max('slot_number') ?? 0;

                        $storage = BankStorage::create([
                            'character_id' => $character->id,
                            'banker_id' => $bankerId,
                            'slot_number' => $maxSlot + 1,
                            'item_instance_id' => null,
                            'is_locked' => false,
                        ]);
                    }
                }

                // Перемещаем предмет в банковскую ячейку
                // Используем 'container' как location_type, а location_id указывает на BankStorage
                $depositInstance->location_type = 'container';
                $depositInstance->location_id = $storage->id;
                $depositInstance->save();

                // Обновляем ячейку хранилища
                $storage->item_instance_id = $depositInstance->id;
                $storage->deposited_at = now();
                $storage->withdrawn_at = null;
                $storage->save();

                // Рассчитываем и взимаем плату
                $newUsedSlots = $usedSlots + 1;
                $storageFee = $banker->calculateStorageFee($character, $newUsedSlots);

                if ($storageFee > 0) {
                    if (! $character->hasEnoughGold($storageFee)) {
                        throw new \Exception('Недостаточно золота для оплаты хранения');
                    }
                    $character->spendGold($storageFee);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Предмет успешно помещен в хранилище',
                    'storage' => [
                        'id' => $storage->id,
                        'slot_number' => $storage->slot_number,
                    ],
                    'item' => [
                        'id' => $item->id,
                        'name' => $item->name,
                    ],
                    'quantity' => $depositQuantity,
                    'storage_fee' => $storageFee,
                    'character_gold' => $character->getGold(),
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Bank deposit failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()?->id,
            ]);

            return response()->json([
                'error' => 'Произошла ошибка при депозите: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Изъятие предмета из банка.
     */
    public function withdraw(WithdrawItemRequest $request): JsonResponse
    {
        try {
            $character = $this->getActiveCharacter($request);

            if (! $character) {
                return response()->json([
                    'error' => 'Активный персонаж не найден',
                ], 404);
            }

            $bankerId = $request->input('banker_id');
            $storageId = $request->input('storage_id');
            $quantity = $request->input('quantity');

            $banker = Banker::with('npc')->find($bankerId);

            if (! $banker) {
                return response()->json([
                    'error' => 'Банкир не найден',
                ], 404);
            }

            // Проверка локации
            if ($banker->location_id !== $character->location_id) {
                return response()->json([
                    'error' => 'Банкир не находится в вашей локации',
                ], 403);
            }

            // Проверка активного спавна NPC
            $activeNpc = ActiveNpc::where('npc_id', $banker->npc_id)
                ->where('is_active', true)
                ->where('location_id', $character->location_id)
                ->first();

            if (! $activeNpc) {
                return response()->json([
                    'error' => 'Банкир не заспавнен в вашей локации',
                ], 403);
            }

            // Получаем ячейку хранилища
            $storage = BankStorage::with('itemInstance.item')
                ->where('id', $storageId)
                ->where('character_id', $character->id)
                ->where('banker_id', $bankerId)
                ->first();

            if (! $storage) {
                return response()->json([
                    'error' => 'Ячейка хранилища не найдена',
                ], 404);
            }

            if (! $storage->canWithdraw()) {
                return response()->json([
                    'error' => 'Ячейка заблокирована или пуста',
                ], 403);
            }

            $itemInstance = $storage->itemInstance;
            $item = $itemInstance->item;

            // Определяем количество для изъятия
            $withdrawQuantity = $quantity ?? $itemInstance->quantity;
            if ($withdrawQuantity > $itemInstance->quantity) {
                $withdrawQuantity = $itemInstance->quantity;
            }

            // Проверка места в инвентаре
            $inventoryCheck = $character->canAddItemsToInventory($item, $withdrawQuantity);
            if (! $inventoryCheck['can_add']) {
                return response()->json([
                    'error' => $inventoryCheck['reason'],
                ], 403);
            }

            return DB::transaction(function () use ($character, $storage, $itemInstance, $item, $withdrawQuantity) {
                $isPartialWithdraw = $item->stackable && $withdrawQuantity < $itemInstance->quantity;

                // Если частичное изъятие стакуемого предмета
                if ($isPartialWithdraw) {
                    $withdrawInstance = $itemInstance->splitStack($withdrawQuantity);
                    if (! $withdrawInstance) {
                        throw new \Exception('Не удалось разделить стак предметов');
                    }
                    // Оригинальный экземпляр остается в банке с уменьшенным количеством
                } else {
                    $withdrawInstance = $itemInstance;
                }

                // Перемещаем предмет в инвентарь
                $withdrawInstance->location_type = 'inventory';
                $withdrawInstance->location_id = $character->id;
                $withdrawInstance->save();

                // Очищаем ячейку хранилища только если изымается весь предмет
                if (! $isPartialWithdraw) {
                    $storage->item_instance_id = null;
                    $storage->withdrawn_at = now();
                    $storage->save();
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Предмет успешно изъят из хранилища',
                    'item' => [
                        'id' => $item->id,
                        'name' => $item->name,
                    ],
                    'quantity' => $withdrawQuantity,
                    'character_gold' => $character->getGold(),
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Bank withdraw failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()?->id,
            ]);

            return response()->json([
                'error' => 'Произошла ошибка при изъятии: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Покупка улучшения хранилища.
     */
    public function upgrade(UpgradeStorageRequest $request): JsonResponse
    {
        try {
            $character = $this->getActiveCharacter($request);

            if (! $character) {
                return response()->json([
                    'error' => 'Активный персонаж не найден',
                ], 404);
            }

            $bankerId = $request->input('banker_id');
            $additionalSlots = $request->input('additional_slots');

            $banker = Banker::with('npc')->find($bankerId);

            if (! $banker) {
                return response()->json([
                    'error' => 'Банкир не найден',
                ], 404);
            }

            // Проверка локации
            if ($banker->location_id !== $character->location_id) {
                return response()->json([
                    'error' => 'Банкир не находится в вашей локации',
                ], 403);
            }

            // Проверка активного спавна NPC
            $activeNpc = ActiveNpc::where('npc_id', $banker->npc_id)
                ->where('is_active', true)
                ->where('location_id', $character->location_id)
                ->first();

            if (! $activeNpc) {
                return response()->json([
                    'error' => 'Банкир не заспавнен в вашей локации',
                ], 403);
            }

            // Получаем текущее количество улучшенных слотов
            $currentUpgradedSlots = BankStorageUpgrade::where('character_id', $character->id)
                ->where('banker_id', $bankerId)
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })
                ->sum('additional_slots');

            // Проверка лимита улучшений
            if ($currentUpgradedSlots + $additionalSlots > $banker->max_upgrade_slots) {
                return response()->json([
                    'error' => "Превышен лимит улучшений. Максимум: {$banker->max_upgrade_slots}, текущее: {$currentUpgradedSlots}",
                ], 403);
            }

            // Рассчитываем стоимость улучшения (базовая стоимость * количество слотов)
            $baseUpgradeCost = 1000; // Базовая стоимость за слот
            $upgradeCost = $baseUpgradeCost * $additionalSlots;

            // Проверка достаточности золота
            if (! $character->hasEnoughGold($upgradeCost)) {
                return response()->json([
                    'error' => "Недостаточно золота. Требуется: {$upgradeCost}, у вас: {$character->getGold()}",
                ], 403);
            }

            return DB::transaction(function () use ($character, $banker, $additionalSlots, $upgradeCost) {
                // Списываем золото
                if (! $character->spendGold($upgradeCost)) {
                    throw new \Exception('Недостаточно золота');
                }

                // Создаем улучшение (по умолчанию постоянное)
                $upgrade = BankStorageUpgrade::create([
                    'character_id' => $character->id,
                    'banker_id' => $banker->id,
                    'additional_slots' => $additionalSlots,
                    'purchase_cost' => $upgradeCost,
                    'purchased_at' => now(),
                    'expires_at' => null, // Постоянное улучшение
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Улучшение хранилища успешно приобретено',
                    'upgrade' => [
                        'id' => $upgrade->id,
                        'additional_slots' => $upgrade->additional_slots,
                        'purchase_cost' => $upgrade->purchase_cost,
                    ],
                    'character_gold' => $character->getGold(),
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Bank upgrade failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()?->id,
            ]);

            return response()->json([
                'error' => 'Произошла ошибка при покупке улучшения: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Проверить, можно ли хранить предмет в банке.
     */
    private function canStoreItem(ItemInstance $itemInstance): array
    {
        $item = $itemInstance->item;

        // Квестовые предметы нельзя хранить
        if ($itemInstance->isQuestItem()) {
            return [
                'can_store' => false,
                'reason' => 'Квестовые предметы нельзя хранить в банке',
            ];
        }

        // Предметы с истекшим сроком годности нельзя хранить
        if ($itemInstance->isExpired()) {
            return [
                'can_store' => false,
                'reason' => 'Предметы с истекшим сроком годности нельзя хранить',
            ];
        }

        // Экипированные предметы нужно сначала снять
        if ($itemInstance->isEquipped()) {
            return [
                'can_store' => false,
                'reason' => 'Сначала снимите предмет с экипировки',
            ];
        }

        // Проверка типа предмета (можно добавить дополнительные ограничения)
        // Например, некоторые типы предметов могут быть нехранимыми

        return ['can_store' => true, 'reason' => null];
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
