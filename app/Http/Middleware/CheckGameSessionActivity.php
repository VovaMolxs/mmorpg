<?php

namespace App\Http\Middleware;

use App\Models\CharacterSession;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckGameSessionActivity
{
    /**
     * Таймаут бездействия в минутах.
     */
    private const TIMEOUT_MINUTES = 15;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $sessionToken = $request->header('X-Session-Token') ?? $request->input('session_token');

        // Если нет токена сессии, пропускаем запрос (может быть не игровой запрос)
        if (! $sessionToken) {
            return $next($request);
        }

        $session = CharacterSession::findActiveByToken($sessionToken);

        // Если сессия не найдена, пропускаем (валидация будет в контроллере)
        if (! $session) {
            return $next($request);
        }

        // Проверка таймаута бездействия
        if ($session->isExpired(self::TIMEOUT_MINUTES)) {
            try {
                DB::transaction(function () use ($session) {
                    $character = $session->character;
                    $presence = $character->presence;

                    // Завершение сессии
                    $session->endSession();

                    // Обновление присутствия
                    if ($presence) {
                        $presence->setOffline();
                    }
                });

                Log::info('Session expired due to inactivity', [
                    'session_id' => $session->id,
                    'character_id' => $session->character_id,
                ]);

                return response()->json([
                    'error' => 'Сессия истекла из-за бездействия',
                    'expired' => true,
                ], 401);
            } catch (\Exception $e) {
                Log::error('Failed to expire session', [
                    'error' => $e->getMessage(),
                    'session_id' => $session->id,
                ]);
            }
        }

        // Обновление времени последней активности
        try {
            $session->updateActivity();
            $presence = $session->character->presence;
            if ($presence) {
                $presence->updateLastAction();
            }
        } catch (\Exception $e) {
            Log::error('Failed to update session activity', [
                'error' => $e->getMessage(),
                'session_id' => $session->id,
            ]);
        }

        // Добавляем сессию в запрос для удобства доступа в контроллерах
        $request->merge(['game_session' => $session]);

        return $next($request);
    }
}
