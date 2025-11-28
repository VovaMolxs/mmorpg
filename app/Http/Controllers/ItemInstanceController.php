<?php

namespace App\Http\Controllers;

use App\Http\Requests\DropItemRequest;
use App\Http\Requests\EquipItemRequest;
use App\Http\Requests\MoveItemRequest;
use App\Http\Requests\PickUpItemRequest;
use App\Http\Requests\UseItemRequest;
use App\Models\Character;
use App\Models\CharacterEquipment;
use App\Models\ItemInstance;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ItemInstanceController extends Controller
{
    /**
     * Получить информацию об экземпляре предмета.
     */
    public function show(ItemInstance $itemInstance): JsonResponse
    {
        // Проверка доступа
        if ($itemInstance->location_type === 'inventory' || $itemInstance->location_type === 'equipped') {
            // Проверяем, принадлежит ли предмет персонажу пользователя
            $character = auth()->user()->characters()->find($itemInstance->location_id);
            if (! $character) {
                return response()->json(['error' => 'Доступ запрещен.'], 403);
            }
        }

        // Для предметов на земле загружаем информацию о локации
        if ($itemInstance->location_type === 'ground') {
            $itemInstance->load('item');
        } else {
            $itemInstance->load('item');
        }

        return response()->json($itemInstance);
    }

    /**
     * Получить инвентарь персонажа.
     */
    public function inventory(Character $character): JsonResponse
    {
        if ($character->user_id !== auth()->id()) {
            return response()->json(['error' => 'Доступ запрещен.'], 403);
        }

        $items = $character->inventoryItems()
            ->with('item')
            ->get()
            ->map(function ($itemInstance) {
                $item = $itemInstance->item;

                return [
                    'id' => $itemInstance->id,
                    'item_id' => $item->id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'type' => $item->type,
                    'subtype' => $item->subtype,
                    'rarity' => $item->rarity,
                    'quantity' => $itemInstance->quantity,
                    'durability_current' => $itemInstance->durability_current,
                ];
            })
            ->values()
            ->toArray();

        return response()->json(['items' => $items]);
    }

    /**
     * Получить экипировку персонажа.
     */
    public function equipment(Character $character): JsonResponse
    {
        if ($character->user_id !== auth()->id()) {
            return response()->json(['error' => 'Доступ запрещен.'], 403);
        }

        $equipment = $character->equipment()
            ->with('itemInstance.item')
            ->get()
            ->keyBy('slot');

        return response()->json($equipment);
    }

    /**
     * Экипировать предмет.
     */
    public function equip(EquipItemRequest $request, Character $character): JsonResponse
    {
        if ($character->user_id !== auth()->id()) {
            return response()->json(['error' => 'Доступ запрещен.'], 403);
        }

        try {
            return DB::transaction(function () use ($request, $character) {
                $itemInstance = ItemInstance::findOrFail($request->item_instance_id);

                // Проверка, что предмет в инвентаре персонажа
                if ($itemInstance->location_type !== 'inventory' || $itemInstance->location_id !== $character->id) {
                    return response()->json(['error' => 'Предмет не находится в инвентаре персонажа.'], 400);
                }

                $item = $itemInstance->item;

                // Проверка требований
                $requirementsErrors = $item->checkRequirements($character);
                if (! empty($requirementsErrors)) {
                    return response()->json(['error' => 'Требования не выполнены.', 'details' => $requirementsErrors], 400);
                }

                // Определение слота экипировки
                $slot = $request->slot ?? $item->getEquipmentSlot();
                if ($slot === null) {
                    return response()->json(['error' => 'Этот предмет нельзя экипировать.'], 400);
                }

                // Проверка, занят ли слот
                $existingEquipment = CharacterEquipment::where('character_id', $character->id)
                    ->where('slot', $slot)
                    ->first();

                if ($existingEquipment) {
                    // Снимаем предмет из слота
                    $existingItemInstance = $existingEquipment->itemInstance;
                    $existingItemInstance->moveToInventory($character->id);
                    $existingEquipment->delete();
                }

                // Экипируем предмет
                $itemInstance->location_type = 'equipped';
                $itemInstance->location_id = $character->id;
                $itemInstance->save();

                CharacterEquipment::create([
                    'character_id' => $character->id,
                    'item_instance_id' => $itemInstance->id,
                    'slot' => $slot,
                ]);

                return response()->json([
                    'message' => 'Предмет успешно экипирован.',
                    'equipment' => $character->equipment()->with('itemInstance.item')->get(),
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Item equip failed', [
                'error' => $e->getMessage(),
                'character_id' => $character->id,
                'item_instance_id' => $request->item_instance_id,
            ]);

            return response()->json(['error' => 'Произошла ошибка при экипировке предмета.'], 500);
        }
    }

    /**
     * Снять предмет с экипировки.
     */
    public function unequip(Character $character, ItemInstance $itemInstance): JsonResponse
    {
        if ($character->user_id !== auth()->id()) {
            return response()->json(['error' => 'Доступ запрещен.'], 403);
        }

        try {
            return DB::transaction(function () use ($character, $itemInstance) {
                $equipment = CharacterEquipment::where('character_id', $character->id)
                    ->where('item_instance_id', $itemInstance->id)
                    ->first();

                if (! $equipment) {
                    return response()->json(['error' => 'Предмет не экипирован.'], 400);
                }

                $itemInstance->moveToInventory($character->id);
                $equipment->delete();

                return response()->json([
                    'message' => 'Предмет успешно снят с экипировки.',
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Item unequip failed', [
                'error' => $e->getMessage(),
                'character_id' => $character->id,
                'item_instance_id' => $itemInstance->id,
            ]);

            return response()->json(['error' => 'Произошла ошибка при снятии предмета.'], 500);
        }
    }

    /**
     * Выбросить предмет на землю.
     */
    public function drop(DropItemRequest $request, Character $character): JsonResponse
    {
        if ($character->user_id !== auth()->id()) {
            return response()->json(['error' => 'Доступ запрещен.'], 403);
        }

        try {
            return DB::transaction(function () use ($request, $character) {
                $itemInstance = ItemInstance::findOrFail($request->item_instance_id);

                // Проверка, что предмет в инвентаре персонажа
                if ($itemInstance->location_type !== 'inventory' || $itemInstance->location_id !== $character->id) {
                    return response()->json(['error' => 'Предмет не находится в инвентаре персонажа.'], 400);
                }

                // Получаем текущую локацию персонажа
                $location = $character->location;
                if (! $location) {
                    return response()->json(['error' => 'Персонаж не находится ни в одной локации.'], 400);
                }

                $locationId = $location->id;
                $positionX = $request->position_x ?? 0;
                $positionY = $request->position_y ?? 0;

                // Если указано количество и предмет стакуемый, разделяем стак
                if ($request->has('quantity') && $itemInstance->item->stackable) {
                    $quantity = $request->quantity;
                    if ($quantity >= $itemInstance->quantity) {
                        return response()->json(['error' => 'Количество не может быть больше или равно количеству в стаке.'], 400);
                    }

                    $droppedInstance = $itemInstance->splitStack($quantity);
                    $droppedInstance->dropOnGround($locationId, $positionX, $positionY);
                } else {
                    // Выбрасываем весь предмет
                    $itemInstance->dropOnGround($locationId, $positionX, $positionY);
                }

                return response()->json([
                    'message' => 'Предмет успешно выброшен на землю.',
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Item drop failed', [
                'error' => $e->getMessage(),
                'character_id' => $character->id,
                'item_instance_id' => $request->item_instance_id,
            ]);

            return response()->json(['error' => 'Произошла ошибка при выбрасывании предмета.'], 500);
        }
    }

    /**
     * Поднять предмет с земли.
     */
    public function pickUp(PickUpItemRequest $request, Character $character): JsonResponse
    {
        if ($character->user_id !== auth()->id()) {
            return response()->json(['error' => 'Доступ запрещен.'], 403);
        }

        try {
            return DB::transaction(function () use ($request, $character) {
                $itemInstance = ItemInstance::findOrFail($request->item_instance_id);

                // Проверка, что предмет на земле
                if ($itemInstance->location_type !== 'ground') {
                    return response()->json(['error' => 'Предмет не находится на земле.'], 400);
                }

                // Проверка, что предмет не истек
                if ($itemInstance->isExpired()) {
                    return response()->json(['error' => 'Предмет уже исчез.'], 400);
                }

                // Проверка, что предмет находится в текущей локации персонажа
                $characterLocation = $character->location;
                if (! $characterLocation || $itemInstance->location_id !== $characterLocation->id) {
                    return response()->json(['error' => 'Предмет находится в другой локации.'], 400);
                }

                $item = $itemInstance->item;

                // Если указано количество и предмет стакуемый, разделяем стак
                if ($request->has('quantity') && $item->stackable && $itemInstance->quantity > 1) {
                    $quantity = $request->quantity;
                    if ($quantity > $itemInstance->quantity) {
                        return response()->json(['error' => 'Количество не может быть больше количества в стаке.'], 400);
                    }

                    // Создаем новый экземпляр для поднятия
                    $pickedUpInstance = $itemInstance->splitStack($quantity);
                    $pickedUpInstance->moveToInventory($character->id);

                    return response()->json([
                        'message' => "Поднято {$quantity} шт. предмета.",
                        'item_instance' => $pickedUpInstance->load('item'),
                    ]);
                } else {
                    // Поднимаем весь предмет
                    $itemInstance->moveToInventory($character->id);

                    return response()->json([
                        'message' => 'Предмет успешно поднят.',
                        'item_instance' => $itemInstance->load('item'),
                    ]);
                }
            });
        } catch (\Exception $e) {
            Log::error('Item pick up failed', [
                'error' => $e->getMessage(),
                'character_id' => $character->id,
                'item_instance_id' => $request->item_instance_id,
            ]);

            return response()->json(['error' => 'Произошла ошибка при поднятии предмета.'], 500);
        }
    }

    /**
     * Переместить предмет.
     */
    public function move(MoveItemRequest $request, Character $character): JsonResponse
    {
        if ($character->user_id !== auth()->id()) {
            return response()->json(['error' => 'Доступ запрещен.'], 403);
        }

        try {
            return DB::transaction(function () use ($request, $character) {
                $itemInstance = ItemInstance::findOrFail($request->item_instance_id);

                // Проверка прав доступа
                if ($itemInstance->location_type === 'inventory' && $itemInstance->location_id !== $character->id) {
                    return response()->json(['error' => 'Нет доступа к этому предмету.'], 403);
                }

                $targetLocationType = $request->target_location_type;
                $targetLocationId = $request->target_location_id ?? $character->id;

                // Перемещение в инвентарь
                if ($targetLocationType === 'inventory') {
                    $itemInstance->moveToInventory($targetLocationId, $request->position_x, $request->position_y);
                } elseif ($targetLocationType === 'ground') {
                    // Получаем текущую локацию персонажа для выброса на землю
                    $location = $character->location;
                    $locationId = $location?->id;
                    $itemInstance->dropOnGround($locationId, $request->position_x ?? 0, $request->position_y ?? 0);
                } else {
                    // Для других типов расположения
                    $itemInstance->location_type = $targetLocationType;
                    $itemInstance->location_id = $targetLocationId;
                    $itemInstance->position_x = $request->position_x;
                    $itemInstance->position_y = $request->position_y;
                    $itemInstance->save();
                }

                return response()->json([
                    'message' => 'Предмет успешно перемещен.',
                    'item_instance' => $itemInstance->load('item'),
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Item move failed', [
                'error' => $e->getMessage(),
                'character_id' => $character->id,
                'item_instance_id' => $request->item_instance_id,
            ]);

            return response()->json(['error' => 'Произошла ошибка при перемещении предмета.'], 500);
        }
    }

    /**
     * Использовать предмет.
     */
    public function use(UseItemRequest $request, Character $character): JsonResponse
    {
        if ($character->user_id !== auth()->id()) {
            return response()->json(['error' => 'Доступ запрещен.'], 403);
        }

        try {
            return DB::transaction(function () use ($request, $character) {
                $itemInstance = ItemInstance::findOrFail($request->item_instance_id);

                // Проверка, что предмет в инвентаре или экипирован
                if ($itemInstance->location_type !== 'inventory' && $itemInstance->location_type !== 'equipped') {
                    return response()->json(['error' => 'Предмет недоступен для использования.'], 400);
                }

                if ($itemInstance->location_type === 'inventory' && $itemInstance->location_id !== $character->id) {
                    return response()->json(['error' => 'Предмет не находится в инвентаре персонажа.'], 400);
                }

                $item = $itemInstance->item;

                // Использование зелья
                if ($item->isPotion()) {
                    return $this->usePotion($itemInstance, $character);
                }

                // Использование свитка
                if ($item->isScroll()) {
                    return $this->useScroll($itemInstance, $character);
                }

                // Использование руны
                if ($item->isRune()) {
                    return $this->useRune($itemInstance, $character);
                }

                return response()->json(['error' => 'Этот предмет нельзя использовать.'], 400);
            });
        } catch (\Exception $e) {
            Log::error('Item use failed', [
                'error' => $e->getMessage(),
                'character_id' => $character->id,
                'item_instance_id' => $request->item_instance_id,
            ]);

            return response()->json(['error' => 'Произошла ошибка при использовании предмета.'], 500);
        }
    }

    /**
     * Использовать зелье.
     */
    protected function usePotion(ItemInstance $itemInstance, Character $character): JsonResponse
    {
        $potionData = $itemInstance->item->getPotionData();

        if (! $potionData) {
            return response()->json(['error' => 'Некорректные данные зелья.'], 400);
        }

        $effectType = $potionData['effect_type'] ?? null;
        $effectPower = $potionData['effect_power'] ?? 0;

        switch ($effectType) {
            case 'health':
                $healed = $character->heal($effectPower);
                $itemInstance->quantity--;
                if ($itemInstance->quantity <= 0) {
                    $itemInstance->delete();
                } else {
                    $itemInstance->save();
                }

                return response()->json([
                    'message' => "Восстановлено {$healed} здоровья.",
                    'character' => $character->fresh(),
                ]);

            case 'mana':
                $restored = $character->restoreMana($effectPower);
                $itemInstance->quantity--;
                if ($itemInstance->quantity <= 0) {
                    $itemInstance->delete();
                } else {
                    $itemInstance->save();
                }

                return response()->json([
                    'message' => "Восстановлено {$restored} маны.",
                    'character' => $character->fresh(),
                ]);

            default:
                return response()->json(['error' => 'Неизвестный тип эффекта зелья.'], 400);
        }
    }

    /**
     * Использовать свиток.
     */
    protected function useScroll(ItemInstance $itemInstance, Character $character): JsonResponse
    {
        $scrollData = $itemInstance->item->getScrollData();

        if (! $scrollData) {
            return response()->json(['error' => 'Некорректные данные свитка.'], 400);
        }

        // Проверка навыка прочтения (если требуется)
        if (isset($scrollData['skill_required'])) {
            $skillName = $scrollData['skill_required'];
            $requiredLevel = $scrollData['skill_level_required'] ?? 1;

            $characterSkill = $character->skills()->where('name', $skillName)->first();
            $skillLevel = $characterSkill?->pivot->level ?? 0;

            if ($skillLevel < $requiredLevel) {
                return response()->json([
                    'error' => "Требуется навык {$skillName} уровня {$requiredLevel}.",
                ], 400);
            }
        }

        // Здесь должна быть логика применения заклинания из свитка
        // Пока просто удаляем свиток, если он одноразовый
        if ($scrollData['is_consumable'] ?? true) {
            $itemInstance->quantity--;
            if ($itemInstance->quantity <= 0) {
                $itemInstance->delete();
            } else {
                $itemInstance->save();
            }
        }

        return response()->json([
            'message' => 'Свиток использован.',
            'spell_id' => $scrollData['spell_id'] ?? null,
        ]);
    }

    /**
     * Использовать руну.
     */
    protected function useRune(ItemInstance $itemInstance, Character $character): JsonResponse
    {
        $runeData = $itemInstance->item->getRuneData();

        if (! $runeData) {
            return response()->json(['error' => 'Некорректные данные руны.'], 400);
        }

        $manaCost = $runeData['mana_cost_per_use'] ?? 0;

        // Проверка маны
        if (! $character->spendMana($manaCost)) {
            return response()->json(['error' => 'Недостаточно маны для использования руны.'], 400);
        }

        // Здесь должна быть логика применения заклинания из руны
        // Руны не расходуются при использовании

        return response()->json([
            'message' => 'Руна использована.',
            'spell_id' => $runeData['spell_id'] ?? null,
            'character' => $character->fresh(),
        ]);
    }
}
