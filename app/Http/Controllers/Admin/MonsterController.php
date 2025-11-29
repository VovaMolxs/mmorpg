<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMonsterRequest;
use App\Http\Requests\Admin\UpdateMonsterRequest;
use App\Models\Item;
use App\Models\Location;
use App\Models\Monster;
use App\Models\MonsterLoot;
use App\Models\MonsterSkill;
use App\Models\MonsterStat;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class MonsterController extends Controller
{
    /**
     * Display a listing of monsters.
     */
    public function index(Request $request): View
    {
        $query = Monster::with('stats')->latest();

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->has('type')) {
            $query->where('type', $request->get('type'));
        }

        if ($request->has('rank')) {
            $query->where('rank', $request->get('rank'));
        }

        $monsters = $query->paginate(20);

        $types = [
            'beast' => 'Зверь',
            'humanoid' => 'Гуманоид',
            'undead' => 'Нежить',
            'elemental' => 'Элементаль',
            'demon' => 'Демон',
            'magical' => 'Магический',
        ];

        $ranks = [
            'normal' => 'Обычный',
            'elite' => 'Элитный',
            'boss' => 'Босс',
            'world_boss' => 'Мировой босс',
        ];

        $aiBehaviors = [
            'passive' => 'Пассивный',
            'neutral' => 'Нейтральный',
            'aggressive' => 'Агрессивный',
        ];

        return view('admin.monsters.index', [
            'monsters' => $monsters,
            'types' => $types,
            'ranks' => $ranks,
            'aiBehaviors' => $aiBehaviors,
        ]);
    }

    /**
     * Show the form for creating a new monster.
     */
    public function create(): View
    {
        $types = [
            'beast' => 'Зверь',
            'humanoid' => 'Гуманоид',
            'undead' => 'Нежить',
            'elemental' => 'Элементаль',
            'demon' => 'Демон',
            'magical' => 'Магический',
        ];

        $ranks = [
            'normal' => 'Обычный',
            'elite' => 'Элитный',
            'boss' => 'Босс',
            'world_boss' => 'Мировой босс',
        ];

        $aiBehaviors = [
            'passive' => 'Пассивный',
            'neutral' => 'Нейтральный',
            'aggressive' => 'Агрессивный',
        ];

        $attackTypes = [
            'physical' => 'Физическая',
            'magical' => 'Магическая',
            'hybrid' => 'Гибридная',
        ];

        return view('admin.monsters.create', [
            'types' => $types,
            'ranks' => $ranks,
            'aiBehaviors' => $aiBehaviors,
            'attackTypes' => $attackTypes,
        ]);
    }

    /**
     * Store a newly created monster.
     */
    public function store(StoreMonsterRequest $request): RedirectResponse
    {
        try {
            DB::transaction(function () use ($request) {
                $validated = $request->validated();

                // Создаем монстра
                $monster = Monster::create([
                    'name' => $validated['name'],
                    'type' => $validated['type'],
                    'level' => $validated['level'],
                    'rank' => $validated['rank'],
                    'faction_id' => $validated['faction_id'] ?? null,
                    'ai_behavior' => $validated['ai_behavior'],
                    'respawn_time' => $validated['respawn_time'] ?? 5,
                    'description' => $validated['description'] ?? null,
                    'is_active' => $validated['is_active'] ?? true,
                ]);

                // Создаем статистику
                $stats = $validated['stats'];
                MonsterStat::create([
                    'monster_id' => $monster->id,
                    'health_max' => $stats['health_max'],
                    'mana_max' => $stats['mana_max'],
                    'attack_power' => $stats['attack_power'],
                    'magic_power' => $stats['magic_power'],
                    'defense' => $stats['defense'],
                    'magic_defense' => $stats['magic_defense'],
                    'accuracy' => $stats['accuracy'] ?? 0,
                    'magic_accuracy' => $stats['magic_accuracy'] ?? 0,
                    'dodge' => $stats['dodge'] ?? 0,
                    'critical_chance' => $stats['critical_chance'] ?? 0,
                    'critical_power' => $stats['critical_power'] ?? 0,
                    'experience_reward' => $stats['experience_reward'],
                    'attack_type' => $stats['attack_type'],
                ]);
            });

            return redirect()->route('admin.monsters.index')
                ->with('success', 'Монстр успешно создан!');
        } catch (\Exception $e) {
            Log::error('Monster creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()?->id,
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'Произошла ошибка при создании монстра: '.$e->getMessage()]);
        }
    }

    /**
     * Show the form for editing the specified monster.
     */
    public function edit(Monster $monster): View
    {
        // Перезагружаем модель с отношениями, чтобы убедиться, что лут загружен
        $monster = Monster::with(['stats', 'loot.item', 'skills'])->findOrFail($monster->id);

        $types = [
            'beast' => 'Зверь',
            'humanoid' => 'Гуманоид',
            'undead' => 'Нежить',
            'elemental' => 'Элементаль',
            'demon' => 'Демон',
            'magical' => 'Магический',
        ];

        $ranks = [
            'normal' => 'Обычный',
            'elite' => 'Элитный',
            'boss' => 'Босс',
            'world_boss' => 'Мировой босс',
        ];

        $aiBehaviors = [
            'passive' => 'Пассивный',
            'neutral' => 'Нейтральный',
            'aggressive' => 'Агрессивный',
        ];

        $attackTypes = [
            'physical' => 'Физическая',
            'magical' => 'Магическая',
            'hybrid' => 'Гибридная',
        ];

        $items = Item::orderBy('name')->get();
        $locations = Location::orderBy('name')->get();

        return view('admin.monsters.edit', [
            'monster' => $monster,
            'types' => $types,
            'ranks' => $ranks,
            'aiBehaviors' => $aiBehaviors,
            'attackTypes' => $attackTypes,
            'items' => $items,
            'locations' => $locations,
        ]);
    }

    /**
     * Update the specified monster.
     */
    public function update(UpdateMonsterRequest $request, Monster $monster): RedirectResponse
    {
        try {
            DB::transaction(function () use ($request, $monster) {
                $validated = $request->validated();

                // Извлекаем статистику из валидированных данных
                $stats = $validated['stats'] ?? null;
                unset($validated['stats']);

                // Обновляем основную информацию монстра
                $monster->update($validated);

                // Обновляем статистику, если она передана
                if ($stats) {
                    $monsterStat = $monster->stats;

                    if ($monsterStat) {
                        $monsterStat->update([
                            'health_max' => $stats['health_max'],
                            'mana_max' => $stats['mana_max'],
                            'attack_power' => $stats['attack_power'],
                            'magic_power' => $stats['magic_power'],
                            'defense' => $stats['defense'],
                            'magic_defense' => $stats['magic_defense'],
                            'accuracy' => $stats['accuracy'] ?? 0,
                            'magic_accuracy' => $stats['magic_accuracy'] ?? 0,
                            'dodge' => $stats['dodge'] ?? 0,
                            'critical_chance' => $stats['critical_chance'] ?? 0,
                            'critical_power' => $stats['critical_power'] ?? 0,
                            'experience_reward' => $stats['experience_reward'],
                            'attack_type' => $stats['attack_type'],
                        ]);
                    } else {
                        // Создаем статистику, если её нет
                        MonsterStat::create([
                            'monster_id' => $monster->id,
                            'health_max' => $stats['health_max'],
                            'mana_max' => $stats['mana_max'],
                            'attack_power' => $stats['attack_power'],
                            'magic_power' => $stats['magic_power'],
                            'defense' => $stats['defense'],
                            'magic_defense' => $stats['magic_defense'],
                            'accuracy' => $stats['accuracy'] ?? 0,
                            'magic_accuracy' => $stats['magic_accuracy'] ?? 0,
                            'dodge' => $stats['dodge'] ?? 0,
                            'critical_chance' => $stats['critical_chance'] ?? 0,
                            'critical_power' => $stats['critical_power'] ?? 0,
                            'experience_reward' => $stats['experience_reward'],
                            'attack_type' => $stats['attack_type'],
                        ]);
                    }
                }
            });

            return redirect()->route('admin.monsters.index')
                ->with('success', 'Монстр успешно обновлен!');
        } catch (\Exception $e) {
            Log::error('Monster update failed', [
                'error' => $e->getMessage(),
                'monster_id' => $monster->id,
                'user_id' => $request->user()?->id,
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'Произошла ошибка при обновлении монстра: '.$e->getMessage()]);
        }
    }

    /**
     * Remove the specified monster.
     */
    public function destroy(Monster $monster): RedirectResponse
    {
        try {
            $monster->delete();

            return redirect()->route('admin.monsters.index')
                ->with('success', 'Монстр успешно удален!');
        } catch (\Exception $e) {
            Log::error('Monster deletion failed', [
                'error' => $e->getMessage(),
                'monster_id' => $monster->id,
            ]);

            return back()
                ->withErrors(['error' => 'Произошла ошибка при удалении монстра: '.$e->getMessage()]);
        }
    }

    /**
     * Add loot to monster.
     */
    public function addLoot(Request $request, Monster $monster): RedirectResponse
    {
        $request->validate([
            'item_id' => 'required|exists:items,id',
            'min_quantity' => 'required|integer|min:1',
            'max_quantity' => 'required|integer|min:1|gte:min_quantity',
            'drop_chance' => 'required|integer|min:1|max:100',
            'is_guaranteed' => 'nullable|boolean',
        ]);

        try {
            $loot = MonsterLoot::create([
                'monster_id' => $monster->id,
                'item_id' => $request->item_id,
                'min_quantity' => $request->min_quantity,
                'max_quantity' => $request->max_quantity,
                'drop_chance' => $request->drop_chance,
                'is_guaranteed' => $request->boolean('is_guaranteed'),
            ]);

            Log::info('Loot added to monster', [
                'monster_id' => $monster->id,
                'monster_name' => $monster->name,
                'loot_id' => $loot->id,
                'item_id' => $request->item_id,
            ]);

            return redirect()->route('admin.monsters.edit', $monster)
                ->with('success', 'Лут успешно добавлен!');
        } catch (\Exception $e) {
            Log::error('Failed to add loot to monster', [
                'error' => $e->getMessage(),
                'monster_id' => $monster->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('admin.monsters.edit', $monster)
                ->withInput()
                ->withErrors(['error' => 'Произошла ошибка при добавлении лута: '.$e->getMessage()]);
        }
    }

    /**
     * Remove loot from monster.
     */
    public function removeLoot(Monster $monster, MonsterLoot $loot): RedirectResponse
    {
        try {
            if ($loot->monster_id !== $monster->id) {
                return redirect()->route('admin.monsters.edit', $monster)
                    ->withErrors(['error' => 'Лут не принадлежит этому монстру']);
            }

            $loot->delete();

            return redirect()->route('admin.monsters.edit', $monster)
                ->with('success', 'Лут успешно удален!');
        } catch (\Exception $e) {
            Log::error('Failed to remove loot from monster', [
                'error' => $e->getMessage(),
                'monster_id' => $monster->id,
                'loot_id' => $loot->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('admin.monsters.edit', $monster)
                ->withErrors(['error' => 'Произошла ошибка при удалении лута: '.$e->getMessage()]);
        }
    }

    /**
     * Add skill to monster.
     */
    public function addSkill(Request $request, Monster $monster): RedirectResponse
    {
        $request->validate([
            'skill_name' => 'required|string|max:255',
            'skill_type' => 'required|in:attack,heal,buff,debuff,summon',
            'damage_type' => 'nullable|in:physical,fire,ice,lightning,poison',
            'power' => 'required|integer|min:0',
            'mana_cost' => 'required|integer|min:0',
            'cooldown' => 'required|integer|min:0',
            'chance_to_use' => 'required|integer|min:1|max:100',
            'description' => 'nullable|string',
        ]);

        try {
            MonsterSkill::create([
                'monster_id' => $monster->id,
                'skill_name' => $request->skill_name,
                'skill_type' => $request->skill_type,
                'damage_type' => $request->damage_type,
                'power' => $request->power,
                'mana_cost' => $request->mana_cost,
                'cooldown' => $request->cooldown,
                'chance_to_use' => $request->chance_to_use,
                'description' => $request->description,
            ]);

            return back()->with('success', 'Способность успешно добавлена!');
        } catch (\Exception $e) {
            Log::error('Failed to add skill to monster', [
                'error' => $e->getMessage(),
                'monster_id' => $monster->id,
            ]);

            return back()->withErrors(['error' => 'Произошла ошибка при добавлении способности: '.$e->getMessage()]);
        }
    }

    /**
     * Remove skill from monster.
     */
    public function removeSkill(Monster $monster, MonsterSkill $skill): RedirectResponse
    {
        try {
            if ($skill->monster_id !== $monster->id) {
                return back()->withErrors(['error' => 'Способность не принадлежит этому монстру']);
            }

            $skill->delete();

            return back()->with('success', 'Способность успешно удалена!');
        } catch (\Exception $e) {
            Log::error('Failed to remove skill from monster', [
                'error' => $e->getMessage(),
                'monster_id' => $monster->id,
                'skill_id' => $skill->id,
            ]);

            return back()->withErrors(['error' => 'Произошла ошибка при удалении способности: '.$e->getMessage()]);
        }
    }

    /**
     * Force spawn monster.
     */
    public function forceSpawn(Request $request, Monster $monster): RedirectResponse
    {
        $request->validate([
            'location_id' => 'required|exists:locations,id',
        ]);

        try {
            if (! $monster->stats) {
                return back()->withErrors(['error' => 'У монстра отсутствует статистика']);
            }

            \App\Models\ActiveMonster::create([
                'monster_id' => $monster->id,
                'location_id' => $request->location_id,
                'spawn_id' => null,
                'health_current' => $monster->stats->health_max,
                'mana_current' => $monster->stats->mana_max,
                'spawned_at' => now(),
                'is_active' => true,
            ]);

            return back()->with('success', 'Монстр успешно заспавнен!');
        } catch (\Exception $e) {
            Log::error('Failed to force spawn monster', [
                'error' => $e->getMessage(),
                'monster_id' => $monster->id,
            ]);

            return back()->withErrors(['error' => 'Произошла ошибка при спавне монстра: '.$e->getMessage()]);
        }
    }
}
