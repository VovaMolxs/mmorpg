<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AddItemToCharacterRequest;
use App\Http\Requests\Admin\MoveCharacterRequest;
use App\Http\Requests\Admin\RestoreCharacterRequest;
use App\Http\Requests\Admin\UpdateCharacterSkillRequest;
use App\Models\Character;
use App\Models\CharacterPresence;
use App\Models\CharacterSession;
use App\Models\CharacterSkill;
use App\Models\Item;
use App\Models\ItemInstance;
use App\Models\Location;
use App\Models\Skill;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class CharacterController extends Controller
{
    /**
     * Display a listing of characters.
     */
    public function index(Request $request): View
    {
        $query = Character::query()->with('user')->latest();

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('username', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->has('level_min')) {
            $query->where('level', '>=', $request->get('level_min'));
        }

        if ($request->has('level_max')) {
            $query->where('level', '<=', $request->get('level_max'));
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->get('is_active') === '1');
        }

        if ($request->has('is_criminal')) {
            $query->where('is_criminal', $request->get('is_criminal') === '1');
        }

        $characters = $query->paginate(20);

        return view('admin.characters.index', [
            'characters' => $characters,
        ]);
    }

    /**
     * Display the specified character.
     */
    public function show(Character $character): View
    {
        $character->load([
            'user',
            'characterSkills.skill',
            'inventoryItems.item',
            'equipment.itemInstance.item',
            'location',
        ]);

        $allItems = Item::orderBy('type')->orderBy('name')->get();
        $allSkills = Skill::orderBy('category')->orderBy('name')->get();
        $allLocations = Location::orderBy('name')->get();

        return view('admin.characters.show', [
            'character' => $character,
            'allItems' => $allItems,
            'allSkills' => $allSkills,
            'allLocations' => $allLocations,
        ]);
    }

    /**
     * Add item to character inventory.
     */
    public function addItem(AddItemToCharacterRequest $request, Character $character): RedirectResponse
    {
        try {
            DB::transaction(function () use ($request, $character) {
                $item = Item::findOrFail($request->item_id);
                $quantity = $request->quantity ?? 1;

                // Если предмет стакуемый, проверяем наличие в инвентаре
                if ($item->stackable) {
                    $existingInstance = ItemInstance::where('item_id', $item->id)
                        ->where('location_type', 'inventory')
                        ->where('location_id', $character->id)
                        ->first();

                    if ($existingInstance) {
                        // Увеличиваем количество, но не больше максимума
                        $newQuantity = min($existingInstance->quantity + $quantity, $item->max_stack);
                        $existingInstance->quantity = $newQuantity;
                        $existingInstance->save();
                    } else {
                        // Создаем новый экземпляр
                        ItemInstance::create([
                            'item_id' => $item->id,
                            'quantity' => min($quantity, $item->max_stack),
                            'location_type' => 'inventory',
                            'location_id' => $character->id,
                            'durability_current' => $item->armor_data['durability_max'] ?? $item->weapon_data['durability_max'] ?? null,
                        ]);
                    }
                } else {
                    // Для нестакуемых предметов создаем отдельные экземпляры
                    for ($i = 0; $i < $quantity; $i++) {
                        ItemInstance::create([
                            'item_id' => $item->id,
                            'quantity' => 1,
                            'location_type' => 'inventory',
                            'location_id' => $character->id,
                            'durability_current' => $item->armor_data['durability_max'] ?? $item->weapon_data['durability_max'] ?? null,
                        ]);
                    }
                }
            });

            $character->refresh();
            $character->load(['inventoryItems.item']);

            return redirect()->route('admin.characters.show', $character)
                ->with('success', 'Предмет успешно добавлен в инвентарь.');
        } catch (\Exception $e) {
            Log::error('Failed to add item to character', [
                'error' => $e->getMessage(),
                'character_id' => $character->id,
                'item_id' => $request->item_id,
            ]);

            return back()
                ->withErrors(['error' => 'Произошла ошибка при добавлении предмета: '.$e->getMessage()]);
        }
    }

    /**
     * Update character skill.
     */
    public function updateSkill(UpdateCharacterSkillRequest $request, Character $character): RedirectResponse
    {
        try {
            DB::transaction(function () use ($request, $character) {
                $skill = Skill::findOrFail($request->skill_id);

                $characterSkill = CharacterSkill::firstOrNew([
                    'character_id' => $character->id,
                    'skill_id' => $skill->id,
                ]);

                if ($request->has('level')) {
                    $characterSkill->level = min($request->level, $skill->max_level);
                }

                if ($request->has('experience')) {
                    $characterSkill->experience = max(0, $request->experience);
                }

                if ($request->has('is_active')) {
                    $characterSkill->is_active = $request->is_active;
                } else {
                    $characterSkill->is_active = true;
                }

                $characterSkill->save();
            });

            $character->refresh();
            $character->load(['characterSkills.skill']);

            return redirect()->route('admin.characters.show', $character)
                ->with('success', 'Навык успешно обновлен.');
        } catch (\Exception $e) {
            Log::error('Failed to update character skill', [
                'error' => $e->getMessage(),
                'character_id' => $character->id,
                'skill_id' => $request->skill_id,
            ]);

            return back()
                ->withErrors(['error' => 'Произошла ошибка при обновлении навыка: '.$e->getMessage()]);
        }
    }

    /**
     * Display online characters.
     */
    public function online(): View
    {
        // Получаем все активные сессии с персонажами
        $sessions = CharacterSession::where('is_online', true)
            ->whereNull('logout_at')
            ->with([
                'character.user',
                'character.location',
                'location',
            ])
            ->orderBy('last_activity_at', 'desc')
            ->get();

        // Получаем присутствие для всех персонажей в игре
        $characterIds = $sessions->pluck('character_id');
        $presences = CharacterPresence::whereIn('character_id', $characterIds)
            ->where('status', 'online')
            ->with('location')
            ->get()
            ->keyBy('character_id');

        return view('admin.characters.online', [
            'sessions' => $sessions,
            'presences' => $presences,
        ]);
    }

    /**
     * Move character to a different location.
     */
    public function move(MoveCharacterRequest $request, Character $character): RedirectResponse
    {
        try {
            DB::transaction(function () use ($request, $character) {
                $location = Location::findOrFail($request->location_id);

                // Обновляем локацию персонажа
                $character->location_id = $location->id;
                $character->save();

                // Обновляем локацию в активной сессии, если есть
                $session = CharacterSession::where('character_id', $character->id)
                    ->where('is_online', true)
                    ->whereNull('logout_at')
                    ->first();

                if ($session) {
                    $session->location_id = $location->id;
                    $session->save();
                }

                // Обновляем локацию в присутствии, если есть
                $presence = CharacterPresence::where('character_id', $character->id)->first();
                if ($presence) {
                    $presence->location_id = $location->id;
                    $presence->save();
                }
            });

            $character->refresh();
            $character->load('location');

            return redirect()->route('admin.characters.show', $character)
                ->with('success', "Персонаж успешно перемещен в локацию: {$character->location->name}");
        } catch (\Exception $e) {
            Log::error('Failed to move character', [
                'error' => $e->getMessage(),
                'character_id' => $character->id,
                'location_id' => $request->location_id,
            ]);

            return back()
                ->withErrors(['error' => 'Произошла ошибка при перемещении персонажа: '.$e->getMessage()]);
        }
    }

    /**
     * Restore character health and/or mana.
     */
    public function restore(RestoreCharacterRequest $request, Character $character): RedirectResponse
    {
        try {
            DB::transaction(function () use ($request, $character) {
                if ($request->full_restore) {
                    // Полное восстановление
                    $character->health_current = $character->health_max;
                    $character->mana_current = $character->mana_max;
                } else {
                    // Частичное восстановление
                    if ($request->has('health_amount') && $request->health_amount > 0) {
                        $character->heal($request->health_amount);
                    }

                    if ($request->has('mana_amount') && $request->mana_amount > 0) {
                        $character->restoreMana($request->mana_amount);
                    }
                }

                $character->save();
            });

            $character->refresh();

            return redirect()->route('admin.characters.show', $character)
                ->with('success', 'Здоровье и/или мана восстановлены.');
        } catch (\Exception $e) {
            Log::error('Failed to restore character', [
                'error' => $e->getMessage(),
                'character_id' => $character->id,
            ]);

            return back()
                ->withErrors(['error' => 'Произошла ошибка при восстановлении: '.$e->getMessage()]);
        }
    }
}
