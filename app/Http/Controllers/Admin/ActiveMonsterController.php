<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActiveMonster;
use App\Models\Location;
use App\Models\Monster;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ActiveMonsterController extends Controller
{
    /**
     * Display a listing of active monsters.
     */
    public function index(Request $request): View
    {
        $query = ActiveMonster::with(['monster.stats', 'location', 'spawn'])
            ->latest('spawned_at');

        if ($request->has('location_id')) {
            $query->where('location_id', $request->get('location_id'));
        }

        if ($request->has('monster_id')) {
            $query->where('monster_id', $request->get('monster_id'));
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        } else {
            // По умолчанию показываем только активных
            $query->where('is_active', true);
        }

        $activeMonsters = $query->paginate(20);

        $locations = Location::orderBy('name')->get();
        $monsters = Monster::with('stats')->orderBy('name')->get();

        return view('admin.active-monsters.index', [
            'activeMonsters' => $activeMonsters,
            'locations' => $locations,
            'monsters' => $monsters,
        ]);
    }

    /**
     * Remove the specified active monster.
     */
    public function destroy(ActiveMonster $activeMonster): RedirectResponse
    {
        try {
            $monsterName = $activeMonster->monster->name ?? 'Unknown';
            $locationName = $activeMonster->location->name ?? 'Unknown';

            $activeMonster->setDead();
            $activeMonster->save();

            return redirect()->route('admin.active-monsters.index')
                ->with('success', "Монстр {$monsterName} в локации {$locationName} успешно удален!");
        } catch (\Exception $e) {
            Log::error('Active monster deletion failed', [
                'error' => $e->getMessage(),
                'active_monster_id' => $activeMonster->id,
            ]);

            return back()
                ->withErrors(['error' => 'Произошла ошибка при удалении монстра: '.$e->getMessage()]);
        }
    }

    /**
     * Kill the specified active monster (set as dead).
     */
    public function kill(ActiveMonster $activeMonster): RedirectResponse
    {
        try {
            if ($activeMonster->isDead()) {
                return back()->withErrors(['error' => 'Монстр уже мертв']);
            }

            $monsterName = $activeMonster->monster->name ?? 'Unknown';
            $locationName = $activeMonster->location->name ?? 'Unknown';

            $activeMonster->setDead();
            $activeMonster->save();

            return redirect()->route('admin.active-monsters.index')
                ->with('success', "Монстр {$monsterName} в локации {$locationName} успешно убит!");
        } catch (\Exception $e) {
            Log::error('Active monster kill failed', [
                'error' => $e->getMessage(),
                'active_monster_id' => $activeMonster->id,
            ]);

            return back()
                ->withErrors(['error' => 'Произошла ошибка при убийстве монстра: '.$e->getMessage()]);
        }
    }

    /**
     * Heal the specified active monster.
     */
    public function heal(ActiveMonster $activeMonster): RedirectResponse
    {
        try {
            if ($activeMonster->isDead()) {
                return back()->withErrors(['error' => 'Монстр мертв и не может быть исцелен']);
            }

            $stats = $activeMonster->getStats();
            if (! $stats) {
                return back()->withErrors(['error' => 'Статистика монстра не найдена']);
            }

            $healed = $activeMonster->heal($stats->health_max);
            $activeMonster->save();

            $monsterName = $activeMonster->monster->name ?? 'Unknown';

            return redirect()->route('admin.active-monsters.index')
                ->with('success', "Монстр {$monsterName} исцелен на {$healed} HP!");
        } catch (\Exception $e) {
            Log::error('Active monster heal failed', [
                'error' => $e->getMessage(),
                'active_monster_id' => $activeMonster->id,
            ]);

            return back()
                ->withErrors(['error' => 'Произошла ошибка при исцелении монстра: '.$e->getMessage()]);
        }
    }
}
