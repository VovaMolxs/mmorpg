<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreNpcRequest;
use App\Http\Requests\Admin\UpdateNpcRequest;
use App\Models\Item;
use App\Models\Location;
use App\Models\Npc;
use App\Models\NpcEquipment;
use App\Models\NpcLoot;
use App\Models\NpcSkill;
use App\Models\NpcStat;
use App\Models\Skill;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class NpcController extends Controller
{
    /**
     * Display a listing of NPCs.
     */
    public function index(Request $request): View
    {
        $query = Npc::with(['location', 'stats'])->latest();

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

        if ($request->has('is_hostile')) {
            $query->where('is_hostile', $request->boolean('is_hostile'));
        }

        if ($request->has('location_id')) {
            $query->where('location_id', $request->get('location_id'));
        }

        $npcs = $query->paginate(20);

        $types = [
            'trader' => 'Торговец',
            'teacher' => 'Учитель',
            'quest_giver' => 'Квестодатель',
            'simple' => 'Обычный',
            'hostile' => 'Враждебный',
        ];

        $aiBehaviors = [
            'passive' => 'Пассивный',
            'neutral' => 'Нейтральный',
            'aggressive' => 'Агрессивный',
        ];

        $locations = Location::orderBy('name')->get();

        return view('admin.npcs.index', [
            'npcs' => $npcs,
            'types' => $types,
            'aiBehaviors' => $aiBehaviors,
            'locations' => $locations,
        ]);
    }

    /**
     * Show the form for creating a new NPC.
     */
    public function create(): View
    {
        $types = [
            'trader' => 'Торговец',
            'teacher' => 'Учитель',
            'quest_giver' => 'Квестодатель',
            'simple' => 'Обычный',
            'hostile' => 'Враждебный',
        ];

        $aiBehaviors = [
            'passive' => 'Пассивный',
            'neutral' => 'Нейтральный',
            'aggressive' => 'Агрессивный',
        ];

        $locations = Location::orderBy('name')->get();
        $items = Item::orderBy('name')->get();
        $skills = Skill::orderBy('name')->get();

        $equipmentSlots = [
            'weapon' => 'Оружие',
            'head' => 'Голова',
            'chest' => 'Грудь',
            'legs' => 'Ноги',
            'hands' => 'Руки',
            'feet' => 'Ноги',
            'amulet' => 'Амулет',
            'ring' => 'Кольцо',
            'earring' => 'Серьга',
        ];

        return view('admin.npcs.create', [
            'types' => $types,
            'aiBehaviors' => $aiBehaviors,
            'locations' => $locations,
            'items' => $items,
            'skills' => $skills,
            'equipmentSlots' => $equipmentSlots,
        ]);
    }

    /**
     * Store a newly created NPC.
     */
    public function store(StoreNpcRequest $request): RedirectResponse
    {
        try {
            DB::transaction(function () use ($request) {
                $validated = $request->validated();

                // Создаем NPC
                $npc = Npc::create([
                    'name' => $validated['name'],
                    'type' => $validated['type'],
                    'location_id' => $validated['location_id'] ?? null,
                    'description' => $validated['description'] ?? null,
                    'is_merchant' => $validated['is_merchant'] ?? false,
                    'is_teacher' => $validated['is_teacher'] ?? false,
                    'is_quest_giver' => $validated['is_quest_giver'] ?? false,
                    'is_hostile' => $validated['is_hostile'] ?? false,
                    'faction_id' => $validated['faction_id'] ?? null,
                    'ai_behavior' => $validated['ai_behavior'],
                    'respawn_time' => $validated['respawn_time'] ?? 5,
                    'merchant_buy_types' => $validated['merchant_buy_types'] ?? null,
                ]);

                // Создаем статистику
                if (isset($validated['stats'])) {
                    $stats = $validated['stats'];
                    NpcStat::create([
                        'npc_id' => $npc->id,
                        'level' => $stats['level'],
                        'health_max' => $stats['health_max'],
                        'health_current' => $stats['health_current'] ?? $stats['health_max'],
                        'mana_max' => $stats['mana_max'] ?? 0,
                        'mana_current' => $stats['mana_current'] ?? ($stats['mana_max'] ?? 0),
                        'strength' => $stats['strength'],
                        'agility' => $stats['agility'],
                        'intelligence' => $stats['intelligence'],
                        'attack_power' => $stats['attack_power'] ?? 0,
                        'defense' => $stats['defense'] ?? 0,
                        'magic_defense' => $stats['magic_defense'] ?? 0,
                        'accuracy' => $stats['accuracy'] ?? 50.00,
                        'dodge' => $stats['dodge'] ?? 0.00,
                        'critical_chance' => $stats['critical_chance'] ?? 5.00,
                        'critical_power' => $stats['critical_power'] ?? 1.50,
                        'experience_reward' => $stats['experience_reward'] ?? 0,
                        'gold_reward_min' => $stats['gold_reward_min'] ?? 0,
                        'gold_reward_max' => $stats['gold_reward_max'] ?? 0,
                    ]);
                }

                // Создаем лут
                if (isset($validated['loot']) && is_array($validated['loot'])) {
                    foreach ($validated['loot'] as $lootData) {
                        if (! empty($lootData['item_id'])) {
                            NpcLoot::create([
                                'npc_id' => $npc->id,
                                'item_id' => $lootData['item_id'],
                                'min_quantity' => $lootData['min_quantity'] ?? 1,
                                'max_quantity' => $lootData['max_quantity'] ?? 1,
                                'drop_chance' => $lootData['drop_chance'] ?? 100,
                            ]);
                        }
                    }
                }

                // Создаем навыки
                if (isset($validated['skills']) && is_array($validated['skills'])) {
                    foreach ($validated['skills'] as $skillData) {
                        if (! empty($skillData['skill_id'])) {
                            NpcSkill::create([
                                'npc_id' => $npc->id,
                                'skill_id' => $skillData['skill_id'],
                                'level' => $skillData['level'] ?? 1,
                                'is_active' => $skillData['is_active'] ?? true,
                            ]);
                        }
                    }
                }

                // Создаем экипировку
                if (isset($validated['equipment']) && is_array($validated['equipment'])) {
                    foreach ($validated['equipment'] as $equipmentData) {
                        if (! empty($equipmentData['item_id']) && ! empty($equipmentData['slot'])) {
                            NpcEquipment::create([
                                'npc_id' => $npc->id,
                                'item_id' => $equipmentData['item_id'],
                                'slot' => $equipmentData['slot'],
                            ]);
                        }
                    }
                }
            });

            return redirect()->route('admin.npcs.index')
                ->with('success', 'NPC успешно создан!');
        } catch (\Exception $e) {
            Log::error('NPC creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()?->id,
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'Произошла ошибка при создании NPC: '.$e->getMessage()]);
        }
    }

    /**
     * Show the form for editing the specified NPC.
     */
    public function edit(Npc $npc): View
    {
        $npc->load(['stats', 'loot.item', 'skills.skill', 'equipment.item']);

        $types = [
            'trader' => 'Торговец',
            'teacher' => 'Учитель',
            'quest_giver' => 'Квестодатель',
            'simple' => 'Обычный',
            'hostile' => 'Враждебный',
        ];

        $aiBehaviors = [
            'passive' => 'Пассивный',
            'neutral' => 'Нейтральный',
            'aggressive' => 'Агрессивный',
        ];

        $locations = Location::orderBy('name')->get();
        $items = Item::orderBy('name')->get();
        $skills = Skill::orderBy('name')->get();

        $equipmentSlots = [
            'weapon' => 'Оружие',
            'head' => 'Голова',
            'chest' => 'Грудь',
            'legs' => 'Ноги',
            'hands' => 'Руки',
            'feet' => 'Ноги',
            'amulet' => 'Амулет',
            'ring' => 'Кольцо',
            'earring' => 'Серьга',
        ];

        return view('admin.npcs.edit', [
            'npc' => $npc,
            'types' => $types,
            'aiBehaviors' => $aiBehaviors,
            'locations' => $locations,
            'items' => $items,
            'skills' => $skills,
            'equipmentSlots' => $equipmentSlots,
        ]);
    }

    /**
     * Update the specified NPC.
     */
    public function update(UpdateNpcRequest $request, Npc $npc): RedirectResponse
    {
        try {
            DB::transaction(function () use ($request, $npc) {
                $validated = $request->validated();

                // Обновляем NPC
                $updateData = [
                    'name' => $validated['name'] ?? $npc->name,
                    'type' => $validated['type'] ?? $npc->type,
                    'location_id' => $validated['location_id'] ?? $npc->location_id,
                    'description' => $validated['description'] ?? $npc->description,
                    'is_merchant' => $validated['is_merchant'] ?? $npc->is_merchant,
                    'is_teacher' => $validated['is_teacher'] ?? $npc->is_teacher,
                    'is_quest_giver' => $validated['is_quest_giver'] ?? $npc->is_quest_giver,
                    'is_hostile' => $validated['is_hostile'] ?? $npc->is_hostile,
                    'faction_id' => $validated['faction_id'] ?? $npc->faction_id,
                    'ai_behavior' => $validated['ai_behavior'] ?? $npc->ai_behavior,
                    'respawn_time' => $validated['respawn_time'] ?? $npc->respawn_time,
                ];

                // Обновляем merchant_buy_types только если оно передано
                if (isset($validated['merchant_buy_types'])) {
                    $updateData['merchant_buy_types'] = empty($validated['merchant_buy_types']) ? null : $validated['merchant_buy_types'];
                }

                $npc->update($updateData);

                // Обновляем статистику
                if (isset($validated['stats'])) {
                    $stats = $validated['stats'];
                    $npcStat = $npc->stats;
                    if ($npcStat) {
                        $npcStat->update([
                            'level' => $stats['level'] ?? $npcStat->level,
                            'health_max' => $stats['health_max'] ?? $npcStat->health_max,
                            'health_current' => $stats['health_current'] ?? $npcStat->health_current,
                            'mana_max' => $stats['mana_max'] ?? $npcStat->mana_max,
                            'mana_current' => $stats['mana_current'] ?? $npcStat->mana_current,
                            'strength' => $stats['strength'] ?? $npcStat->strength,
                            'agility' => $stats['agility'] ?? $npcStat->agility,
                            'intelligence' => $stats['intelligence'] ?? $npcStat->intelligence,
                            'attack_power' => $stats['attack_power'] ?? $npcStat->attack_power,
                            'defense' => $stats['defense'] ?? $npcStat->defense,
                            'magic_defense' => $stats['magic_defense'] ?? $npcStat->magic_defense,
                            'accuracy' => $stats['accuracy'] ?? $npcStat->accuracy,
                            'dodge' => $stats['dodge'] ?? $npcStat->dodge,
                            'critical_chance' => $stats['critical_chance'] ?? $npcStat->critical_chance,
                            'critical_power' => $stats['critical_power'] ?? $npcStat->critical_power,
                            'experience_reward' => $stats['experience_reward'] ?? $npcStat->experience_reward,
                            'gold_reward_min' => $stats['gold_reward_min'] ?? $npcStat->gold_reward_min,
                            'gold_reward_max' => $stats['gold_reward_max'] ?? $npcStat->gold_reward_max,
                        ]);
                    }
                }

                // Обновляем лут (удаляем старый и создаем новый)
                if (isset($validated['loot'])) {
                    $npc->loot()->delete();
                    if (is_array($validated['loot'])) {
                        foreach ($validated['loot'] as $lootData) {
                            if (! empty($lootData['item_id'])) {
                                NpcLoot::create([
                                    'npc_id' => $npc->id,
                                    'item_id' => $lootData['item_id'],
                                    'min_quantity' => $lootData['min_quantity'] ?? 1,
                                    'max_quantity' => $lootData['max_quantity'] ?? 1,
                                    'drop_chance' => $lootData['drop_chance'] ?? 100,
                                ]);
                            }
                        }
                    }
                }

                // Обновляем навыки
                if (isset($validated['skills'])) {
                    $npc->skills()->delete();
                    if (is_array($validated['skills'])) {
                        foreach ($validated['skills'] as $skillData) {
                            if (! empty($skillData['skill_id'])) {
                                NpcSkill::create([
                                    'npc_id' => $npc->id,
                                    'skill_id' => $skillData['skill_id'],
                                    'level' => $skillData['level'] ?? 1,
                                    'is_active' => $skillData['is_active'] ?? true,
                                ]);
                            }
                        }
                    }
                }

                // Обновляем экипировку
                if (isset($validated['equipment'])) {
                    $npc->equipment()->delete();
                    if (is_array($validated['equipment'])) {
                        foreach ($validated['equipment'] as $equipmentData) {
                            if (! empty($equipmentData['item_id']) && ! empty($equipmentData['slot'])) {
                                NpcEquipment::create([
                                    'npc_id' => $npc->id,
                                    'item_id' => $equipmentData['item_id'],
                                    'slot' => $equipmentData['slot'],
                                ]);
                            }
                        }
                    }
                }
            });

            return redirect()->route('admin.npcs.index')
                ->with('success', 'NPC успешно обновлен!');
        } catch (\Exception $e) {
            Log::error('NPC update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'npc_id' => $npc->id,
                'user_id' => $request->user()?->id,
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'Произошла ошибка при обновлении NPC: '.$e->getMessage()]);
        }
    }

    /**
     * Remove the specified NPC.
     */
    public function destroy(Npc $npc): RedirectResponse
    {
        try {
            $npc->delete();

            return redirect()->route('admin.npcs.index')
                ->with('success', 'NPC успешно удален!');
        } catch (\Exception $e) {
            Log::error('NPC deletion failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'npc_id' => $npc->id,
            ]);

            return back()
                ->withErrors(['error' => 'Произошла ошибка при удалении NPC: '.$e->getMessage()]);
        }
    }
}
