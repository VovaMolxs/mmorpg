<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateCharacterRequest;
use App\Models\Character;
use App\Models\CharacterPresence;
use App\Models\CharacterSession;
use App\Models\Skill;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class CharacterController extends Controller
{
    /**
     * Display a listing of the user's characters.
     */
    public function index(): View
    {
        $characters = auth()->user()->characters()
            ->with('location')
            ->orderBy('created_at', 'desc')
            ->get();

        // Загружаем информацию о сессиях и присутствии для всех персонажей
        $characterIds = $characters->pluck('id');
        $sessions = collect();
        $presences = collect();

        if ($characterIds->isNotEmpty()) {
            $sessions = CharacterSession::whereIn('character_id', $characterIds)
                ->where('is_online', true)
                ->whereNull('logout_at')
                ->get()
                ->keyBy('character_id');

            $presences = CharacterPresence::whereIn('character_id', $characterIds)
                ->get()
                ->keyBy('character_id');
        }

        return view('characters.index', [
            'characters' => $characters,
            'maxCharacters' => auth()->user()->max_characters,
            'sessions' => $sessions,
            'presences' => $presences,
        ]);
    }

    /**
     * Show the form for creating a new character.
     */
    public function create(): View
    {
        if (! auth()->user()->canCreateCharacter()) {
            abort(403, 'Достигнут лимит персонажей.');
        }

        return view('characters.create');
    }

    /**
     * Store a newly created character.
     */
    public function store(CreateCharacterRequest $request): RedirectResponse
    {
        try {
            $character = DB::transaction(function () use ($request) {
                $user = $request->user();

                $validated = $request->validated();

                $strength = 1;
                $agility = 1;
                $intelligence = 1;
                $healthMax = $strength * 10;
                $manaMax = $intelligence * 10;

                $character = Character::create([
                    'user_id' => $user->id,
                    'name' => $validated['name'],
                    'strength' => $strength,
                    'agility' => $agility,
                    'intelligence' => $intelligence,
                    'level' => 1,
                    'experience' => 0,
                    'total_attributes_spent' => 0,
                    'available_points' => 2,
                    'health_current' => $healthMax,
                    'health_max' => $healthMax,
                    'mana_current' => $manaMax,
                    'mana_max' => $manaMax,
                    'is_criminal' => false,
                    'is_active' => true,
                ]);

                $character->updateTotalAttributesSpent();

                $character->description = $character->generateDescription();

                $character->save();

                return $character;
            });

            return redirect()->route('characters.show', $character)
                ->with('success', 'Персонаж успешно создан!');
        } catch (\Exception $e) {
            Log::error('Character creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()?->id,
                'name' => $request->input('name'),
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'Произошла ошибка при создании персонажа: '.$e->getMessage()]);
        }
    }

    /**
     * Display the specified character.
     */
    public function show(Character $character): View
    {
        if ($character->user_id !== auth()->id()) {
            abort(403, 'Доступ запрещен.');
        }

        $character->load('characterSkills.skill', 'equipment.itemInstance.item', 'location');

        // Проверяем наличие активной сессии
        $session = CharacterSession::where('character_id', $character->id)
            ->where('is_online', true)
            ->whereNull('logout_at')
            ->first();

        // Проверяем присутствие в мире
        $presence = CharacterPresence::where('character_id', $character->id)->first();

        return view('characters.show', [
            'character' => $character,
            'session' => $session,
            'presence' => $presence,
        ]);
    }

    /**
     * Display character skills menu.
     */
    public function skills(Character $character): View
    {
        if ($character->user_id !== auth()->id()) {
            abort(403, 'Доступ запрещен.');
        }

        $character->load('characterSkills.skill', 'equipment.itemInstance.item');

        $learnedSkills = $character->characterSkills()
            ->with('skill')
            ->get()
            ->pluck('skill')
            ->filter();

        $learnedSkillIds = $learnedSkills->pluck('id')->toArray();

        $allSkills = Skill::orderBy('category')->orderBy('name')->get();

        $availableSkills = $allSkills->filter(function ($skill) use ($character, $learnedSkillIds) {
            return $skill->isAvailableForCharacter($character) && ! in_array($skill->id, $learnedSkillIds);
        });

        $skillsByCategory = [
            'combat' => $allSkills->where('category', 'combat'),
            'magic' => $allSkills->where('category', 'magic'),
            'craft' => $allSkills->where('category', 'craft'),
            'survival' => $allSkills->where('category', 'survival'),
            'social' => $allSkills->where('category', 'social'),
        ];

        return view('characters.skills', [
            'character' => $character,
            'learnedSkills' => $learnedSkills,
            'availableSkills' => $availableSkills,
            'skillsByCategory' => $skillsByCategory,
            'allSkills' => $allSkills,
        ]);
    }

    /**
     * Display character inventory.
     */
    public function inventory(Character $character): View
    {
        if ($character->user_id !== auth()->id()) {
            abort(403, 'Доступ запрещен.');
        }

        // Загружаем инвентарь с информацией о предметах
        $inventoryItems = $character->inventoryItems()
            ->with('item')
            ->orderBy('created_at', 'desc')
            ->get();

        // Загружаем экипировку
        $equipment = $character->equipment()
            ->with('itemInstance.item')
            ->get()
            ->keyBy('slot');

        // Определяем слоты экипировки
        $equipmentSlots = [
            'weapon_main' => 'Основное оружие',
            'weapon_offhand' => 'Доп. оружие',
            'head' => 'Голова',
            'chest' => 'Грудь',
            'legs' => 'Ноги',
            'hands' => 'Руки',
            'feet' => 'Ступни',
            'amulet' => 'Амулет',
            'ring1' => 'Кольцо 1',
            'ring2' => 'Кольцо 2',
            'earring' => 'Серьга',
        ];

        return view('characters.inventory', [
            'character' => $character,
            'inventoryItems' => $inventoryItems,
            'equipment' => $equipment,
            'equipmentSlots' => $equipmentSlots,
        ]);
    }
}
