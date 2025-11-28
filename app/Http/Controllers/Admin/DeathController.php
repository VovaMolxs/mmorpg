<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CharacterDeath;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeathController extends Controller
{
    /**
     * Display a listing of character deaths.
     */
    public function index(Request $request): View
    {
        $query = CharacterDeath::query()
            ->with(['character.user', 'location', 'corpse', 'ghostState'])
            ->latest('died_at');

        // Поиск по имени персонажа
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->whereHas('character', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        // Фильтр по типу убийцы
        if ($request->has('killed_by_type')) {
            $query->where('killed_by_type', $request->get('killed_by_type'));
        }

        // Фильтр по причине смерти
        if ($request->has('death_cause')) {
            $query->where('death_cause', $request->get('death_cause'));
        }

        // Фильтр по дате (от)
        if ($request->has('date_from')) {
            $query->whereDate('died_at', '>=', $request->get('date_from'));
        }

        // Фильтр по дате (до)
        if ($request->has('date_to')) {
            $query->whereDate('died_at', '<=', $request->get('date_to'));
        }

        $deaths = $query->paginate(50);

        // Статистика
        $totalDeaths = CharacterDeath::count();
        $todayDeaths = CharacterDeath::whereDate('died_at', today())->count();
        $thisWeekDeaths = CharacterDeath::whereBetween('died_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $thisMonthDeaths = CharacterDeath::whereMonth('died_at', now()->month)
            ->whereYear('died_at', now()->year)
            ->count();

        // Статистика по типам убийц
        $killedByStats = CharacterDeath::selectRaw('killed_by_type, COUNT(*) as count')
            ->groupBy('killed_by_type')
            ->get()
            ->pluck('count', 'killed_by_type');

        // Статистика по причинам смерти
        $deathCauseStats = CharacterDeath::selectRaw('death_cause, COUNT(*) as count')
            ->groupBy('death_cause')
            ->get()
            ->pluck('count', 'death_cause');

        return view('admin.deaths.index', [
            'deaths' => $deaths,
            'totalDeaths' => $totalDeaths,
            'todayDeaths' => $todayDeaths,
            'thisWeekDeaths' => $thisWeekDeaths,
            'thisMonthDeaths' => $thisMonthDeaths,
            'killedByStats' => $killedByStats,
            'deathCauseStats' => $deathCauseStats,
        ]);
    }

    /**
     * Display the specified death.
     */
    public function show(CharacterDeath $death): View
    {
        $death->load([
            'character.user',
            'location',
            'corpse.character',
            'ghostState.character',
        ]);

        return view('admin.deaths.show', [
            'death' => $death,
        ]);
    }
}
