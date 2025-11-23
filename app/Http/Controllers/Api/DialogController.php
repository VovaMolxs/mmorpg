<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AnswerDialogRequest;
use App\Http\Requests\StartDialogRequest;
use App\Models\Character;
use App\Models\CharacterQuest;
use App\Models\Dialog;
use App\Models\DialogAnswer;
use App\Models\Npc;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DialogController extends Controller
{
    /**
     * Начать диалог с NPC.
     */
    public function start(StartDialogRequest $request): JsonResponse
    {
        try {
            $character = $this->getActiveCharacter($request);

            if (! $character) {
                return response()->json([
                    'error' => 'Активный персонаж не найден',
                ], 404);
            }

            $npcId = $request->input('npc_id');
            $npc = Npc::find($npcId);

            if (! $npc) {
                return response()->json([
                    'error' => 'NPC не найден',
                ], 404);
            }

            // Проверка, находится ли NPC в той же локации, что и персонаж
            if ($npc->location_id !== $character->location_id) {
                return response()->json([
                    'error' => 'NPC не находится в вашей локации',
                ], 403);
            }

            // Поиск начального диалога для NPC с учетом уровня персонажа
            $initialDialog = Dialog::where('npc_id', $npcId)
                ->where('is_initial', true)
                ->where(function ($query) use ($character) {
                    $query->whereNull('min_level')
                        ->orWhere('min_level', '<=', $character->level);
                })
                ->get()
                ->first(function ($dialog) use ($character) {
                    return $dialog->isAvailableForCharacter($character);
                });

            if (! $initialDialog) {
                return response()->json([
                    'error' => 'Нет доступных диалогов для этого NPC',
                ], 404);
            }

            // Загружаем ответы с проверкой доступности
            $answers = $initialDialog->answers()
                ->get()
                ->filter(function ($answer) use ($character) {
                    return $answer->isAvailableForCharacter($character);
                })
                ->map(function ($answer) {
                    return [
                        'id' => $answer->id,
                        'text' => $answer->text,
                    ];
                })
                ->values();

            return response()->json([
                'success' => true,
                'npc' => [
                    'id' => $npc->id,
                    'name' => $npc->name,
                ],
                'dialog' => [
                    'id' => $initialDialog->id,
                    'text' => $initialDialog->text,
                ],
                'answers' => $answers,
            ]);
        } catch (\Exception $e) {
            Log::error('Dialog start failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()?->id,
            ]);

            return response()->json([
                'error' => 'Произошла ошибка при начале диалога: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Ответить на диалог.
     */
    public function answer(AnswerDialogRequest $request): JsonResponse
    {
        try {
            $character = $this->getActiveCharacter($request);

            if (! $character) {
                return response()->json([
                    'error' => 'Активный персонаж не найден',
                ], 404);
            }

            $dialogId = $request->input('dialog_id');
            $answerId = $request->input('answer_id');

            $dialog = Dialog::find($dialogId);
            $answer = DialogAnswer::find($answerId);

            if (! $dialog || ! $answer) {
                return response()->json([
                    'error' => 'Диалог или ответ не найдены',
                ], 404);
            }

            // Проверка, что ответ принадлежит диалогу
            if ($answer->dialog_id !== $dialog->id) {
                return response()->json([
                    'error' => 'Ответ не принадлежит указанному диалогу',
                ], 403);
            }

            // Проверка доступности ответа
            if (! $answer->isAvailableForCharacter($character)) {
                return response()->json([
                    'error' => 'Ответ недоступен (не выполнены требования)',
                ], 403);
            }

            DB::transaction(function () use ($answer, $character) {
                // Запуск квеста, если он указан в ответе
                if ($answer->quest_trigger_id !== null) {
                    $quest = $answer->questTrigger;

                    if ($quest && $quest->isAvailableForCharacter($character)) {
                        CharacterQuest::create([
                            'character_id' => $character->id,
                            'quest_id' => $quest->id,
                            'status' => 'active',
                            'started_at' => now(),
                            'current_progress' => $this->initializeQuestProgress($quest),
                        ]);
                    }
                }
            });

            // Получаем следующий диалог или завершаем диалог
            if ($answer->next_dialog_id !== null) {
                $nextDialog = Dialog::find($answer->next_dialog_id);

                if ($nextDialog && $nextDialog->isAvailableForCharacter($character)) {
                    $answers = $nextDialog->answers()
                        ->get()
                        ->filter(function ($ans) use ($character) {
                            return $ans->isAvailableForCharacter($character);
                        })
                        ->map(function ($ans) {
                            return [
                                'id' => $ans->id,
                                'text' => $ans->text,
                            ];
                        })
                        ->values();

                    return response()->json([
                        'success' => true,
                        'dialog' => [
                            'id' => $nextDialog->id,
                            'text' => $nextDialog->text,
                        ],
                        'answers' => $answers,
                        'quest_started' => $answer->quest_trigger_id !== null,
                    ]);
                }
            }

            // Диалог завершен
            return response()->json([
                'success' => true,
                'dialog_completed' => true,
                'quest_started' => $answer->quest_trigger_id !== null,
            ]);
        } catch (\Exception $e) {
            Log::error('Dialog answer failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()?->id,
            ]);

            return response()->json([
                'error' => 'Произошла ошибка при обработке ответа: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Инициализировать прогресс квеста.
     */
    private function initializeQuestProgress($quest): array
    {
        $progress = [];

        foreach ($quest->objectives as $objective) {
            $progress[$objective->id] = 0;
        }

        return $progress;
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
