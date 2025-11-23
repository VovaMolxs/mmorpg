<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreQuestRequest;
use App\Http\Requests\Admin\UpdateQuestRequest;
use App\Models\Item;
use App\Models\Npc;
use App\Models\Quest;
use App\Models\QuestObjective;
use App\Models\QuestReward;
use App\Models\Skill;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class QuestController extends Controller
{
    /**
     * Display a listing of quests.
     */
    public function index(Request $request): View
    {
        $query = Quest::with(['questGiver', 'turnInNpc', 'previousQuest', 'objectives'])->latest();

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->has('quest_giver_npc_id')) {
            $query->where('quest_giver_npc_id', $request->get('quest_giver_npc_id'));
        }

        if ($request->has('min_level')) {
            $query->where('min_level', '>=', $request->get('min_level'));
        }

        $quests = $query->paginate(20);
        $npcs = Npc::orderBy('name')->get();

        return view('admin.quests.index', [
            'quests' => $quests,
            'npcs' => $npcs,
        ]);
    }

    /**
     * Show the form for creating a new quest.
     */
    public function create(): View
    {
        $npcs = Npc::orderBy('name')->get();
        $quests = Quest::orderBy('name')->get();
        $items = Item::orderBy('name')->get();
        $skills = Skill::orderBy('name')->get();

        $objectiveTypes = [
            'kill' => 'Убить',
            'collect' => 'Собрать',
            'talk' => 'Поговорить',
            'explore' => 'Исследовать',
            'craft' => 'Создать',
        ];

        $rewardTypes = [
            'experience' => 'Опыт',
            'gold' => 'Золото',
            'item' => 'Предмет',
            'reputation' => 'Репутация',
            'skill' => 'Навык',
        ];

        return view('admin.quests.create', [
            'npcs' => $npcs,
            'quests' => $quests,
            'items' => $items,
            'skills' => $skills,
            'objectiveTypes' => $objectiveTypes,
            'rewardTypes' => $rewardTypes,
        ]);
    }

    /**
     * Store a newly created quest.
     */
    public function store(StoreQuestRequest $request): RedirectResponse
    {
        try {
            DB::transaction(function () use ($request) {
                $validated = $request->validated();

                // Создаем квест
                $quest = Quest::create([
                    'name' => $validated['name'],
                    'description' => $validated['description'],
                    'min_level' => $validated['min_level'] ?? null,
                    'max_level' => $validated['max_level'] ?? null,
                    'quest_giver_npc_id' => $validated['quest_giver_npc_id'] ?? null,
                    'turn_in_npc_id' => $validated['turn_in_npc_id'] ?? null,
                    'previous_quest_id' => $validated['previous_quest_id'] ?? null,
                    'faction_required_id' => $validated['faction_required_id'] ?? null,
                    'reputation_required' => $validated['reputation_required'] ?? null,
                ]);

                // Создаем цели квеста
                if (isset($validated['objectives']) && is_array($validated['objectives'])) {
                    foreach ($validated['objectives'] as $objectiveData) {
                        QuestObjective::create([
                            'quest_id' => $quest->id,
                            'type' => $objectiveData['type'],
                            'target_id' => $objectiveData['target_id'] ?? null,
                            'target_name' => $objectiveData['target_name'] ?? null,
                            'required_count' => $objectiveData['required_count'],
                            'description' => $objectiveData['description'] ?? null,
                        ]);
                    }
                }

                // Создаем награды
                if (isset($validated['rewards']) && is_array($validated['rewards'])) {
                    foreach ($validated['rewards'] as $rewardData) {
                        QuestReward::create([
                            'quest_id' => $quest->id,
                            'type' => $rewardData['type'],
                            'reward_id' => $rewardData['reward_id'] ?? null,
                            'quantity' => $rewardData['quantity'],
                            'is_choice' => $rewardData['is_choice'] ?? false,
                        ]);
                    }
                }
            });

            return redirect()->route('admin.quests.index')
                ->with('success', 'Квест успешно создан!');
        } catch (\Exception $e) {
            Log::error('Quest creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()?->id,
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'Произошла ошибка при создании квеста: '.$e->getMessage()]);
        }
    }

    /**
     * Show the form for editing the specified quest.
     */
    public function edit(Quest $quest): View
    {
        $quest->load(['objectives', 'rewards', 'questGiver', 'turnInNpc', 'previousQuest']);

        $npcs = Npc::orderBy('name')->get();
        $quests = Quest::where('id', '!=', $quest->id)->orderBy('name')->get();
        $items = Item::orderBy('name')->get();
        $skills = Skill::orderBy('name')->get();

        $objectiveTypes = [
            'kill' => 'Убить',
            'collect' => 'Собрать',
            'talk' => 'Поговорить',
            'explore' => 'Исследовать',
            'craft' => 'Создать',
        ];

        $rewardTypes = [
            'experience' => 'Опыт',
            'gold' => 'Золото',
            'item' => 'Предмет',
            'reputation' => 'Репутация',
            'skill' => 'Навык',
        ];

        return view('admin.quests.edit', [
            'quest' => $quest,
            'npcs' => $npcs,
            'quests' => $quests,
            'items' => $items,
            'skills' => $skills,
            'objectiveTypes' => $objectiveTypes,
            'rewardTypes' => $rewardTypes,
        ]);
    }

    /**
     * Update the specified quest.
     */
    public function update(UpdateQuestRequest $request, Quest $quest): RedirectResponse
    {
        try {
            DB::transaction(function () use ($request, $quest) {
                $validated = $request->validated();

                // Обновляем квест
                $quest->update([
                    'name' => $validated['name'] ?? $quest->name,
                    'description' => $validated['description'] ?? $quest->description,
                    'min_level' => $validated['min_level'] ?? $quest->min_level,
                    'max_level' => $validated['max_level'] ?? $quest->max_level,
                    'quest_giver_npc_id' => $validated['quest_giver_npc_id'] ?? $quest->quest_giver_npc_id,
                    'turn_in_npc_id' => $validated['turn_in_npc_id'] ?? $quest->turn_in_npc_id,
                    'previous_quest_id' => $validated['previous_quest_id'] ?? $quest->previous_quest_id,
                    'faction_required_id' => $validated['faction_required_id'] ?? $quest->faction_required_id,
                    'reputation_required' => $validated['reputation_required'] ?? $quest->reputation_required,
                ]);

                // Обновляем цели квеста
                if (isset($validated['objectives'])) {
                    // Удаляем цели, которых нет в запросе
                    $existingObjectiveIds = collect($validated['objectives'])
                        ->pluck('id')
                        ->filter()
                        ->toArray();
                    $quest->objectives()->whereNotIn('id', $existingObjectiveIds)->delete();

                    // Обновляем или создаем цели
                    foreach ($validated['objectives'] as $objectiveData) {
                        if (isset($objectiveData['id'])) {
                            // Обновляем существующую цель
                            $objective = QuestObjective::find($objectiveData['id']);
                            if ($objective && $objective->quest_id === $quest->id) {
                                $objective->update([
                                    'type' => $objectiveData['type'],
                                    'target_id' => $objectiveData['target_id'] ?? null,
                                    'target_name' => $objectiveData['target_name'] ?? null,
                                    'required_count' => $objectiveData['required_count'],
                                    'description' => $objectiveData['description'] ?? null,
                                ]);
                            }
                        } else {
                            // Создаем новую цель
                            QuestObjective::create([
                                'quest_id' => $quest->id,
                                'type' => $objectiveData['type'],
                                'target_id' => $objectiveData['target_id'] ?? null,
                                'target_name' => $objectiveData['target_name'] ?? null,
                                'required_count' => $objectiveData['required_count'],
                                'description' => $objectiveData['description'] ?? null,
                            ]);
                        }
                    }
                }

                // Обновляем награды
                if (isset($validated['rewards'])) {
                    // Удаляем награды, которых нет в запросе
                    $existingRewardIds = collect($validated['rewards'])
                        ->pluck('id')
                        ->filter()
                        ->toArray();
                    $quest->rewards()->whereNotIn('id', $existingRewardIds)->delete();

                    // Обновляем или создаем награды
                    foreach ($validated['rewards'] as $rewardData) {
                        if (isset($rewardData['id'])) {
                            // Обновляем существующую награду
                            $reward = QuestReward::find($rewardData['id']);
                            if ($reward && $reward->quest_id === $quest->id) {
                                $reward->update([
                                    'type' => $rewardData['type'],
                                    'reward_id' => $rewardData['reward_id'] ?? null,
                                    'quantity' => $rewardData['quantity'],
                                    'is_choice' => $rewardData['is_choice'] ?? false,
                                ]);
                            }
                        } else {
                            // Создаем новую награду
                            QuestReward::create([
                                'quest_id' => $quest->id,
                                'type' => $rewardData['type'],
                                'reward_id' => $rewardData['reward_id'] ?? null,
                                'quantity' => $rewardData['quantity'],
                                'is_choice' => $rewardData['is_choice'] ?? false,
                            ]);
                        }
                    }
                }
            });

            return redirect()->route('admin.quests.index')
                ->with('success', 'Квест успешно обновлен!');
        } catch (\Exception $e) {
            Log::error('Quest update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'quest_id' => $quest->id,
                'user_id' => $request->user()?->id,
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'Произошла ошибка при обновлении квеста: '.$e->getMessage()]);
        }
    }

    /**
     * Remove the specified quest.
     */
    public function destroy(Quest $quest): RedirectResponse
    {
        try {
            $quest->delete();

            return redirect()->route('admin.quests.index')
                ->with('success', 'Квест успешно удален!');
        } catch (\Exception $e) {
            Log::error('Quest deletion failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'quest_id' => $quest->id,
            ]);

            return back()
                ->withErrors(['error' => 'Произошла ошибка при удалении квеста: '.$e->getMessage()]);
        }
    }
}
