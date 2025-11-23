<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AcceptQuestRequest;
use App\Http\Requests\CompleteQuestRequest;
use App\Models\Character;
use App\Models\CharacterQuest;
use App\Models\ItemInstance;
use App\Models\Quest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QuestController extends Controller
{
    /**
     * Принять квест.
     */
    public function accept(AcceptQuestRequest $request): JsonResponse
    {
        try {
            $character = $this->getActiveCharacter($request);

            if (! $character) {
                return response()->json([
                    'error' => 'Активный персонаж не найден',
                ], 404);
            }

            $questId = $request->input('quest_id');
            $quest = Quest::with('objectives')->find($questId);

            if (! $quest) {
                return response()->json([
                    'error' => 'Квест не найден',
                ], 404);
            }

            // Проверка доступности квеста
            if (! $quest->isAvailableForCharacter($character)) {
                return response()->json([
                    'error' => 'Квест недоступен для вашего персонажа',
                ], 403);
            }

            // Проверка, находится ли персонаж рядом с NPC, который выдает квест
            if ($quest->quest_giver_npc_id !== null) {
                $questGiver = $quest->questGiver;
                if ($questGiver && $questGiver->location_id !== $character->location_id) {
                    return response()->json([
                        'error' => 'Вы должны находиться рядом с NPC, который выдает этот квест',
                    ], 403);
                }
            }

            DB::transaction(function () use ($character, $quest) {
                // Инициализация прогресса
                $progress = [];
                foreach ($quest->objectives as $objective) {
                    $progress[$objective->id] = 0;
                }

                CharacterQuest::create([
                    'character_id' => $character->id,
                    'quest_id' => $quest->id,
                    'status' => 'active',
                    'started_at' => now(),
                    'current_progress' => $progress,
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Квест принят',
                'quest' => [
                    'id' => $quest->id,
                    'name' => $quest->name,
                    'description' => $quest->description,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Quest accept failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()?->id,
            ]);

            return response()->json([
                'error' => 'Произошла ошибка при принятии квеста: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Завершить квест.
     */
    public function complete(CompleteQuestRequest $request): JsonResponse
    {
        try {
            $character = $this->getActiveCharacter($request);

            if (! $character) {
                return response()->json([
                    'error' => 'Активный персонаж не найден',
                ], 404);
            }

            $characterQuestId = $request->input('character_quest_id');
            $characterQuest = CharacterQuest::with('quest.objectives', 'quest.rewards')->find($characterQuestId);

            if (! $characterQuest) {
                return response()->json([
                    'error' => 'Квест персонажа не найден',
                ], 404);
            }

            // Проверка, что квест принадлежит персонажу
            if ($characterQuest->character_id !== $character->id) {
                return response()->json([
                    'error' => 'Этот квест не принадлежит вашему персонажу',
                ], 403);
            }

            // Проверка статуса
            if ($characterQuest->status !== 'active') {
                return response()->json([
                    'error' => 'Квест уже завершен или отменен',
                ], 403);
            }

            // Проверка выполнения всех целей
            if (! $characterQuest->areAllObjectivesCompleted()) {
                return response()->json([
                    'error' => 'Не все цели квеста выполнены',
                ], 403);
            }

            // Проверка, находится ли персонаж рядом с NPC, которому сдается квест
            $quest = $characterQuest->quest;
            if ($quest->turn_in_npc_id !== null) {
                $turnInNpc = $quest->turnInNpc;
                if ($turnInNpc && $turnInNpc->location_id !== $character->location_id) {
                    return response()->json([
                        'error' => 'Вы должны находиться рядом с NPC, которому нужно сдать квест',
                    ], 403);
                }
            }

            DB::transaction(function () use ($characterQuest, $character, $request) {
                // Завершение квеста
                $characterQuest->complete();

                // Выдача наград
                $selectedRewards = $request->input('selected_rewards', []);
                $this->giveQuestRewards($character, $characterQuest->quest, $selectedRewards);
            });

            return response()->json([
                'success' => true,
                'message' => 'Квест завершен',
                'quest' => [
                    'id' => $quest->id,
                    'name' => $quest->name,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Quest complete failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()?->id,
            ]);

            return response()->json([
                'error' => 'Произошла ошибка при завершении квеста: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Получить список активных квестов персонажа.
     */
    public function active(\Illuminate\Http\Request $request): JsonResponse
    {
        try {
            $character = $this->getActiveCharacter($request);

            if (! $character) {
                return response()->json([
                    'error' => 'Активный персонаж не найден',
                ], 404);
            }

            $activeQuests = CharacterQuest::where('character_id', $character->id)
                ->where('status', 'active')
                ->with('quest.objectives')
                ->get()
                ->map(function ($characterQuest) {
                    $quest = $characterQuest->quest;
                    $progress = $characterQuest->current_progress ?? [];

                    $objectives = $quest->objectives->map(function ($objective) use ($progress) {
                        return [
                            'id' => $objective->id,
                            'type' => $objective->type,
                            'description' => $objective->description,
                            'target_name' => $objective->target_name,
                            'required_count' => $objective->required_count,
                            'current_count' => $progress[$objective->id] ?? 0,
                            'completed' => $objective->isCompleted($progress),
                        ];
                    });

                    return [
                        'id' => $characterQuest->id,
                        'quest_id' => $quest->id,
                        'name' => $quest->name,
                        'description' => $quest->description,
                        'objectives' => $objectives,
                        'started_at' => $characterQuest->started_at?->toIso8601String(),
                    ];
                });

            return response()->json([
                'success' => true,
                'quests' => $activeQuests,
            ]);
        } catch (\Exception $e) {
            Log::error('Get active quests failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()?->id,
            ]);

            return response()->json([
                'error' => 'Произошла ошибка при получении списка квестов: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Выдать награды за квест.
     */
    private function giveQuestRewards(Character $character, Quest $quest, array $selectedRewards = []): void
    {
        $rewards = $quest->rewards;

        // Если есть награды с выбором, обрабатываем только выбранные
        $choiceRewards = $rewards->where('is_choice', true);
        if ($choiceRewards->isNotEmpty() && ! empty($selectedRewards)) {
            $rewards = $rewards->where(function ($reward) use ($selectedRewards) {
                return ! $reward->is_choice || in_array($reward->id, $selectedRewards);
            });
        } elseif ($choiceRewards->isNotEmpty() && empty($selectedRewards)) {
            // Если есть награды с выбором, но ничего не выбрано, пропускаем их
            $rewards = $rewards->where('is_choice', false);
        }

        foreach ($rewards as $reward) {
            match ($reward->type) {
                'experience' => $this->giveExperience($character, $reward->quantity),
                'gold' => $this->giveGold($character, $reward->quantity),
                'item' => $this->giveItem($character, $reward->reward_id, $reward->quantity),
                'reputation' => $this->giveReputation($character, $reward->reward_id, $reward->quantity),
                'skill' => $this->giveSkill($character, $reward->reward_id, $reward->quantity),
                default => null,
            };
        }
    }

    /**
     * Выдать опыт персонажу.
     */
    private function giveExperience(Character $character, int $amount): void
    {
        $character->experience += $amount;
        $character->save();

        // TODO: Проверка повышения уровня
    }

    /**
     * Выдать золото персонажу.
     */
    private function giveGold(Character $character, int $amount): void
    {
        // TODO: Реализовать систему золота, когда будет готова
    }

    /**
     * Выдать предмет персонажу.
     */
    private function giveItem(Character $character, int $itemId, int $quantity): void
    {
        // Создаем экземпляр предмета в инвентаре персонажа
        ItemInstance::create([
            'item_id' => $itemId,
            'owner_id' => $character->id,
            'location_type' => 'inventory',
            'location_id' => $character->id,
            'quantity' => $quantity,
        ]);
    }

    /**
     * Выдать репутацию персонажу.
     */
    private function giveReputation(Character $character, int $factionId, int $amount): void
    {
        // TODO: Реализовать систему репутации, когда будет готова
    }

    /**
     * Выдать навык персонажу.
     */
    private function giveSkill(Character $character, int $skillId, int $experience): void
    {
        // TODO: Реализовать выдачу опыта навыка, когда будет готова система навыков
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
