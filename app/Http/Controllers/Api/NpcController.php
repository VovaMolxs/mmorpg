<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActiveNpc;
use App\Models\Banker;
use App\Models\BankStorage;
use App\Models\BankStorageUpgrade;
use App\Models\Character;
use App\Models\MerchantInventory;
use App\Models\Npc;
use App\Models\Skill;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NpcController extends Controller
{
    /**
     * Получить информацию о NPC для взаимодействия.
     */
    public function show(Request $request, int $npcId): JsonResponse
    {
        try {
            $character = $this->getActiveCharacter($request);

            if (! $character) {
                return response()->json([
                    'error' => 'Активный персонаж не найден',
                ], 404);
            }

            $npc = Npc::with(['stats', 'location', 'dialogs.answers', 'dialogs.answers.itemRequired', 'dialogs.answers.skillRequired', 'questsGiven.objectives', 'questsGiven.rewards'])
                ->find($npcId);

            if (! $npc) {
                return response()->json([
                    'error' => 'NPC не найден',
                ], 404);
            }

            // Проверка: NPC должен быть заспавнен в той же локации, что и персонаж
            $activeNpc = ActiveNpc::where('npc_id', $npcId)
                ->where('is_active', true)
                ->where('location_id', $character->location_id)
                ->first();

            if (! $activeNpc) {
                // Проверяем, существует ли активный спавн этого NPC вообще
                $anyActiveNpc = ActiveNpc::where('npc_id', $npcId)
                    ->where('is_active', true)
                    ->first();

                if (! $anyActiveNpc) {
                    return response()->json([
                        'error' => 'NPC не заспавнен в мире',
                    ], 403);
                }

                return response()->json([
                    'error' => 'NPC не находится в вашей локации. Вы находитесь в локации ID: '.$character->location_id.', а NPC в локации ID: '.$anyActiveNpc->location_id,
                ], 403);
            }

            // Получаем доступные диалоги
            $availableDialogs = $npc->dialogs()
                ->where('is_initial', true)
                ->get()
                ->filter(function ($dialog) use ($character) {
                    return $dialog->isAvailableForCharacter($character);
                })
                ->map(function ($dialog) use ($character) {
                    $answers = $dialog->answers()
                        ->get()
                        ->filter(function ($answer) use ($character) {
                            return $answer->isAvailableForCharacter($character);
                        })
                        ->map(function ($answer) {
                            return [
                                'id' => $answer->id,
                                'text' => $answer->text,
                                'next_dialog_id' => $answer->next_dialog_id,
                                'item_required' => ($answer->item_required_id && $answer->itemRequired) ? [
                                    'id' => $answer->itemRequired->id,
                                    'name' => $answer->itemRequired->name,
                                ] : null,
                                'skill_required' => ($answer->skill_required && $answer->skillRequired) ? [
                                    'id' => $answer->skillRequired->id,
                                    'name' => $answer->skillRequired->name,
                                    'level_required' => $answer->skill_level_required,
                                ] : null,
                            ];
                        })
                        ->values();

                    return [
                        'id' => $dialog->id,
                        'text' => $dialog->text,
                        'answers' => $answers,
                    ];
                })
                ->values();

            // Получаем доступные квесты
            $availableQuests = $npc->questsGiven()
                ->get()
                ->filter(function ($quest) use ($character) {
                    return $quest->isAvailableForCharacter($character);
                })
                ->map(function ($quest) use ($character) {
                    $characterQuest = $character->quests()
                        ->where('quest_id', $quest->id)
                        ->first();

                    return [
                        'id' => $quest->id,
                        'name' => $quest->name,
                        'description' => $quest->description,
                        'min_level' => $quest->min_level,
                        'max_level' => $quest->max_level,
                        'objectives' => $quest->objectives->map(function ($objective) {
                            return [
                                'id' => $objective->id,
                                'type' => $objective->type,
                                'description' => $objective->description,
                                'target_name' => $objective->target_name,
                                'required_count' => $objective->required_count,
                            ];
                        }),
                        'rewards' => $quest->rewards->map(function ($reward) {
                            return [
                                'id' => $reward->id,
                                'type' => $reward->type,
                                'quantity' => $reward->quantity,
                                'is_choice' => $reward->is_choice,
                            ];
                        }),
                        'status' => $characterQuest ? $characterQuest->status : null,
                        'progress' => $characterQuest ? $characterQuest->current_progress : null,
                    ];
                })
                ->values();

            // Получаем квесты для сдачи
            $questsToTurnIn = $character->activeQuests()
                ->whereHas('quest', function ($query) use ($npcId) {
                    $query->where('turn_in_npc_id', $npcId);
                })
                ->with('quest.objectives', 'quest.rewards')
                ->get()
                ->map(function ($characterQuest) {
                    $quest = $characterQuest->quest;
                    $progress = $characterQuest->current_progress ?? [];

                    return [
                        'character_quest_id' => $characterQuest->id,
                        'quest_id' => $quest->id,
                        'name' => $quest->name,
                        'description' => $quest->description,
                        'objectives' => $quest->objectives->map(function ($objective) use ($progress) {
                            return [
                                'id' => $objective->id,
                                'type' => $objective->type,
                                'description' => $objective->description,
                                'target_name' => $objective->target_name,
                                'required_count' => $objective->required_count,
                                'current_count' => $progress[$objective->id] ?? 0,
                                'completed' => $objective->isCompleted($progress),
                            ];
                        }),
                        'rewards' => $quest->rewards->map(function ($reward) {
                            return [
                                'id' => $reward->id,
                                'type' => $reward->type,
                                'quantity' => $reward->quantity,
                                'is_choice' => $reward->is_choice,
                            ];
                        }),
                    ];
                })
                ->values();

            // Получаем доступные навыки для обучения (если NPC учитель)
            $teachableSkills = [];
            if ($npc->is_teacher) {
                $teachableSkills = Skill::all()
                    ->filter(function ($skill) use ($character) {
                        return $skill->isAvailableForCharacter($character);
                    })
                    ->map(function ($skill) use ($character) {
                        $characterSkill = $character->characterSkills()
                            ->where('skill_id', $skill->id)
                            ->first();

                        return [
                            'id' => $skill->id,
                            'name' => $skill->name,
                            'description' => $skill->description,
                            'category' => $skill->category,
                            'max_level' => $skill->max_level,
                            'required_level' => $skill->required_level,
                            'attribute_requirements' => $skill->attribute_requirements,
                            'current_level' => $characterSkill ? $characterSkill->level : 0,
                            'current_experience' => $characterSkill ? $characterSkill->experience : 0,
                            'can_learn' => $characterSkill ? $characterSkill->level < $skill->max_level : true,
                        ];
                    })
                    ->values();
            }

            // Получаем товары торговца
            $merchantItems = [];
            if ($npc->is_merchant) {
                $merchantItems = MerchantInventory::where('npc_id', $npcId)
                    ->where('is_available', true)
                    ->with('item')
                    ->get()
                    ->filter(function ($inventory) {
                        return $inventory->isAvailableForPurchase();
                    })
                    ->map(function ($inventory) {
                        $item = $inventory->item;

                        return [
                            'id' => $inventory->id,
                            'item_id' => $item->id,
                            'name' => $item->name,
                            'description' => $item->description,
                            'type' => $item->type,
                            'subtype' => $item->subtype,
                            'rarity' => $item->rarity,
                            'level_required' => $item->level_required,
                            'value' => $item->value,
                            'weight' => $item->weight,
                            'stackable' => $item->stackable,
                            'max_stack' => $item->max_stack,
                            'requirements' => $item->requirements,
                            'weapon_data' => $item->weapon_data,
                            'armor_data' => $item->armor_data,
                            'jewelry_data' => $item->jewelry_data,
                            'potion_data' => $item->potion_data,
                            'rune_data' => $item->rune_data,
                            'scroll_data' => $item->scroll_data,
                            'resource_data' => $item->resource_data,
                            'quantity' => $inventory->quantity,
                            'max_quantity' => $inventory->max_quantity,
                            'base_price' => $inventory->base_price,
                            'current_price' => $inventory->getCurrentBuyPrice(),
                            'sell_price' => $inventory->getCurrentSellPrice(),
                            'can_buy_from_player' => $inventory->canBuyFromPlayer(),
                            'purchase_limit_per_day' => $inventory->purchase_limit_per_day,
                        ];
                    })
                    ->values();
            }

            // Получаем информацию о банкире, если NPC является банкиром
            $bankerInfo = null;
            $banker = Banker::where('npc_id', $npcId)->first();
            if ($banker) {
                // Получаем информацию о хранилище персонажа
                $storages = BankStorage::where('character_id', $character->id)
                    ->where('banker_id', $banker->id)
                    ->with(['itemInstance.item'])
                    ->orderBy('slot_number')
                    ->get();

                $totalSlots = $banker->getTotalSlotsForCharacter($character);
                $usedSlots = $storages->where('item_instance_id', '!=', null)->count();
                $freeSlots = $totalSlots - $usedSlots;

                // Получаем активные улучшения
                $upgrades = BankStorageUpgrade::where('character_id', $character->id)
                    ->where('banker_id', $banker->id)
                    ->where(function ($query) {
                        $query->whereNull('expires_at')
                            ->orWhere('expires_at', '>', now());
                    })
                    ->get();

                $storageFee = $banker->calculateStorageFee($character, $usedSlots);

                $bankerInfo = [
                    'id' => $banker->id,
                    'storage_slots' => $banker->storage_slots,
                    'base_fee' => $banker->base_fee,
                    'fee_per_slot' => $banker->fee_per_slot,
                    'max_upgrade_slots' => $banker->max_upgrade_slots,
                    'total_slots' => $totalSlots,
                    'used_slots' => $usedSlots,
                    'free_slots' => $freeSlots,
                    'current_fee' => $storageFee,
                    'storages' => $storages->map(function ($storage) {
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
                ];
            }

            return response()->json([
                'success' => true,
                'npc' => [
                    'id' => $npc->id,
                    'name' => $npc->name,
                    'description' => $npc->description,
                    'type' => $npc->type,
                    'is_merchant' => $npc->is_merchant,
                    'is_teacher' => $npc->is_teacher,
                    'is_quest_giver' => $npc->is_quest_giver,
                    'is_hostile' => $npc->is_hostile,
                    'ai_behavior' => $npc->ai_behavior,
                    'faction_id' => $npc->faction_id,
                    'merchant_buy_types' => $npc->merchant_buy_types,
                    'is_banker' => $banker !== null,
                    'stats' => $npc->stats ? [
                        'level' => $npc->stats->level,
                        'health_current' => $npc->stats->health_current,
                        'health_max' => $npc->stats->health_max,
                        'mana_current' => $npc->stats->mana_current,
                        'mana_max' => $npc->stats->mana_max,
                        'strength' => $npc->stats->strength,
                        'agility' => $npc->stats->agility,
                        'intelligence' => $npc->stats->intelligence,
                    ] : null,
                ],
                'dialogs' => $availableDialogs,
                'available_quests' => $availableQuests,
                'quests_to_turn_in' => $questsToTurnIn,
                'teachable_skills' => $teachableSkills,
                'merchant_items' => $merchantItems,
                'banker' => $bankerInfo,
                'character' => [
                    'gold' => $character->getGold(),
                    'level' => $character->level,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('NPC show failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()?->id,
                'npc_id' => $npcId,
            ]);

            return response()->json([
                'error' => 'Произошла ошибка при получении информации о NPC: '.$e->getMessage(),
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
