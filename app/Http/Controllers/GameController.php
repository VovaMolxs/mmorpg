<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\CharacterSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GameController extends Controller
{
    /**
     * Display the game interface.
     */
    public function index(): View|RedirectResponse
    {
        $user = auth()->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Получаем активного персонажа с локацией
        $character = Character::where('user_id', $user->id)
            ->where('is_active', true)
            ->with('location')
            ->first();

        if (! $character) {
            return redirect()->route('characters.index')
                ->with('error', 'У вас нет активного персонажа');
        }

        // Проверяем наличие активной сессии
        $session = CharacterSession::where('character_id', $character->id)
            ->where('is_online', true)
            ->whereNull('logout_at')
            ->first();

        return view('game.index', [
            'character' => $character,
            'session' => $session,
        ]);
    }
}
