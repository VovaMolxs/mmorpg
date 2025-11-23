<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EnterWorldRequest;
use App\Http\Requests\LeaveWorldRequest;
use App\Models\CharacterPresence;
use App\Models\CharacterSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GameSessionController extends Controller
{
    /**
     * Вход в игровой мир.
     */
    public function enterWorld(EnterWorldRequest $request): JsonResponse
    {
        try {
            $character = $request->getCharacter();
            $user = $request->user();

            // Проверка, что персонаж не уже в игре
            $activeSession = CharacterSession::where('character_id', $character->id)
                ->where('is_online', true)
                ->whereNull('logout_at')
                ->first();

            if ($activeSession) {
                return response()->json([
                    'error' => 'Персонаж уже находится в игре',
                    'session_token' => $activeSession->session_token,
                ], 409);
            }

            // Проверка присутствия
            $presence = CharacterPresence::where('character_id', $character->id)->first();
            if ($presence && $presence->isOnline()) {
                return response()->json([
                    'error' => 'Персонаж уже находится в мире',
                ], 409);
            }

            return DB::transaction(function () use ($character, $request) {
                // Создание новой сессии
                $sessionToken = CharacterSession::generateSessionToken();
                $location = $character->location;

                $session = CharacterSession::create([
                    'character_id' => $character->id,
                    'session_token' => $sessionToken,
                    'location_id' => $location?->id,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'login_at' => now(),
                    'last_activity_at' => now(),
                    'is_online' => true,
                ]);

                // Создание или обновление присутствия
                $presence = CharacterPresence::firstOrCreate(
                    ['character_id' => $character->id],
                    [
                        'location_id' => $location?->id,
                        'is_visible' => true,
                        'entered_world_at' => now(),
                        'last_action_at' => now(),
                        'status' => 'online',
                    ]
                );

                if ($presence->wasRecentlyCreated === false) {
                    $presence->location_id = $location?->id;
                    $presence->setOnline();
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Вы успешно вошли в игровой мир',
                    'session' => [
                        'token' => $sessionToken,
                        'login_at' => $session->login_at->toIso8601String(),
                        'last_activity_at' => $session->last_activity_at->toIso8601String(),
                    ],
                    'character' => [
                        'id' => $character->id,
                        'name' => $character->name,
                    ],
                    'location' => $location ? [
                        'id' => $location->id,
                        'name' => $location->name,
                        'description' => $location->description,
                        'type' => $location->type,
                    ] : null,
                ], 201);
            });
        } catch (\Exception $e) {
            Log::error('Enter world failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()?->id,
                'character_id' => $request->input('character_id'),
            ]);

            return response()->json([
                'error' => 'Произошла ошибка при входе в мир: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Выход из игрового мира.
     */
    public function leaveWorld(LeaveWorldRequest $request): JsonResponse
    {
        try {
            $session = $this->getActiveSession($request);

            if (! $session) {
                return response()->json([
                    'error' => 'Активная сессия не найдена',
                ], 404);
            }

            return DB::transaction(function () use ($session) {
                $character = $session->character;
                $presence = $character->presence;

                // Завершение сессии
                $session->endSession();

                // Обновление присутствия
                if ($presence) {
                    $presence->setOffline();
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Вы успешно вышли из игрового мира',
                    'session' => [
                        'duration' => $session->session_duration,
                        'logout_at' => $session->logout_at->toIso8601String(),
                    ],
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Leave world failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()?->id,
            ]);

            return response()->json([
                'error' => 'Произошла ошибка при выходе из мира: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Обновление времени активности (keep-alive).
     */
    public function keepAlive(Request $request): JsonResponse
    {
        try {
            $session = $this->getActiveSession($request);

            if (! $session) {
                return response()->json([
                    'error' => 'Активная сессия не найдена',
                ], 404);
            }

            // Проверка таймаута
            $timeoutMinutes = 15;
            if ($session->isExpired($timeoutMinutes)) {
                // Автоматический выход
                $character = $session->character;
                $presence = $character->presence;

                $session->endSession();
                if ($presence) {
                    $presence->setOffline();
                }

                return response()->json([
                    'error' => 'Сессия истекла из-за бездействия',
                    'expired' => true,
                ], 401);
            }

            // Обновление активности
            $session->updateActivity();
            $presence = $session->character->presence;
            if ($presence) {
                $presence->updateLastAction();
            }

            $remainingTime = $session->getRemainingTime($timeoutMinutes);
            $warningThreshold = 5 * 60; // 5 минут в секундах

            return response()->json([
                'success' => true,
                'last_activity_at' => $session->last_activity_at->toIso8601String(),
                'remaining_time' => $remainingTime,
                'warning' => $remainingTime <= $warningThreshold,
            ]);
        } catch (\Exception $e) {
            Log::error('Keep alive failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()?->id,
            ]);

            return response()->json([
                'error' => 'Произошла ошибка при обновлении активности: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Получить статус текущей сессии.
     */
    public function sessionStatus(Request $request): JsonResponse
    {
        try {
            $session = $this->getActiveSession($request);

            if (! $session) {
                return response()->json([
                    'has_session' => false,
                ]);
            }

            $timeoutMinutes = 15;
            $remainingTime = $session->getRemainingTime($timeoutMinutes);
            $warningThreshold = 5 * 60;

            $presence = $session->character->presence;

            return response()->json([
                'has_session' => true,
                'session' => [
                    'token' => $session->session_token,
                    'login_at' => $session->login_at->toIso8601String(),
                    'last_activity_at' => $session->last_activity_at->toIso8601String(),
                    'remaining_time' => $remainingTime,
                    'warning' => $remainingTime <= $warningThreshold,
                ],
                'presence' => $presence ? [
                    'status' => $presence->status,
                    'is_visible' => $presence->is_visible,
                    'location_id' => $presence->location_id,
                    'last_action_at' => $presence->last_action_at?->toIso8601String(),
                ] : null,
                'character' => [
                    'id' => $session->character->id,
                    'name' => $session->character->name,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Session status failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()?->id,
            ]);

            return response()->json([
                'error' => 'Произошла ошибка при получении статуса сессии: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Получить активную сессию из запроса.
     */
    private function getActiveSession(Request $request): ?CharacterSession
    {
        $sessionToken = $request->header('X-Session-Token') ?? $request->input('session_token');

        if (! $sessionToken) {
            return null;
        }

        $session = CharacterSession::findActiveByToken($sessionToken);

        if (! $session) {
            return null;
        }

        // Проверка прав доступа
        $user = $request->user();
        if ($user && $session->character->user_id !== $user->id) {
            return null;
        }

        return $session;
    }
}
