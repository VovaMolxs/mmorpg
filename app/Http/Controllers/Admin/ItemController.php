<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreItemRequest;
use App\Models\Item;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ItemController extends Controller
{
    /**
     * Display a listing of items.
     */
    public function index(Request $request): View
    {
        $query = Item::query()->latest();

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

        if ($request->has('rarity')) {
            $query->where('rarity', $request->get('rarity'));
        }

        $items = $query->paginate(20);

        $types = [
            'weapon' => 'Оружие',
            'armor' => 'Броня',
            'jewelry' => 'Бижутерия',
            'resource' => 'Ресурс',
            'potion' => 'Зелье',
            'consumable' => 'Расходник',
            'currency' => 'Валюта',
            'rune' => 'Руна',
            'scroll' => 'Свиток',
        ];

        $rarities = [
            'common' => 'Обычный',
            'uncommon' => 'Необычный',
            'rare' => 'Редкий',
            'epic' => 'Эпический',
            'legendary' => 'Легендарный',
        ];

        return view('admin.items.index', [
            'items' => $items,
            'types' => $types,
            'rarities' => $rarities,
        ]);
    }

    /**
     * Show the form for creating a new item.
     */
    public function create(): View
    {
        $types = [
            'weapon' => [
                'label' => 'Оружие',
                'subtypes' => [
                    'sword' => 'Меч',
                    'axe' => 'Топор',
                    'dagger' => 'Кинжал',
                    'bow' => 'Лук',
                    'crossbow' => 'Арбалет',
                    'staff' => 'Посох',
                ],
            ],
            'armor' => [
                'label' => 'Броня',
                'subtypes' => [
                    'helmet' => 'Шлем',
                    'chest' => 'Нагрудник',
                    'legs' => 'Поножи',
                    'hands' => 'Перчатки',
                    'feet' => 'Ботинки',
                ],
            ],
            'jewelry' => [
                'label' => 'Бижутерия',
                'subtypes' => [
                    'amulet' => 'Амулет',
                    'ring' => 'Кольцо',
                    'earring' => 'Серьга',
                ],
            ],
            'resource' => [
                'label' => 'Ресурс',
                'subtypes' => [
                    'herb' => 'Трава',
                    'ore' => 'Руда',
                    'gem' => 'Драгоценный камень',
                    'leather' => 'Кожа',
                ],
            ],
            'potion' => [
                'label' => 'Зелье',
                'subtypes' => [
                    'health' => 'Здоровье',
                    'mana' => 'Мана',
                    'buff' => 'Усиление',
                ],
            ],
            'consumable' => [
                'label' => 'Расходник',
                'subtypes' => [
                    'arrow' => 'Стрела',
                    'bolt' => 'Болт',
                ],
            ],
            'currency' => [
                'label' => 'Валюта',
                'subtypes' => [
                    'money' => 'Деньги',
                ],
            ],
            'rune' => [
                'label' => 'Руна',
                'subtypes' => [
                    'offensive' => 'Атакующая',
                    'defensive' => 'Защитная',
                    'utility' => 'Утилитарная',
                    'summoning' => 'Призывающая',
                ],
            ],
            'scroll' => [
                'label' => 'Свиток',
                'subtypes' => [
                    'offensive' => 'Атакующий',
                    'defensive' => 'Защитный',
                    'utility' => 'Утилитарный',
                    'teleport' => 'Телепорт',
                ],
            ],
        ];

        $rarities = [
            'common' => 'Обычный',
            'uncommon' => 'Необычный',
            'rare' => 'Редкий',
            'epic' => 'Эпический',
            'legendary' => 'Легендарный',
        ];

        return view('admin.items.create', [
            'types' => $types,
            'rarities' => $rarities,
        ]);
    }

    /**
     * Store a newly created item.
     */
    public function store(StoreItemRequest $request): RedirectResponse
    {
        try {
            $item = DB::transaction(function () use ($request) {
                $validated = $request->validated();

                // Подготовка JSON данных
                $data = [
                    'name' => $validated['name'],
                    'description' => $validated['description'] ?? null,
                    'type' => $validated['type'],
                    'subtype' => $validated['subtype'] ?? null,
                    'rarity' => $validated['rarity'],
                    'level_required' => $validated['level_required'] ?? 1,
                    'stackable' => $validated['stackable'] ?? false,
                    'max_stack' => $validated['max_stack'] ?? 1,
                    'weight' => $validated['weight'] ?? 0,
                    'value' => $validated['value'] ?? 0,
                ];

                // Требования
                if (isset($validated['requirements'])) {
                    $data['requirements'] = $this->prepareRequirements($validated['requirements']);
                }

                // Специфичные данные в зависимости от типа
                $type = $validated['type'];
                if (isset($validated["{$type}_data"])) {
                    $data["{$type}_data"] = $this->prepareTypeData($type, $validated["{$type}_data"]);
                }

                return Item::create($data);
            });

            return redirect()->route('admin.items.index')
                ->with('success', 'Предмет успешно создан!');
        } catch (\Exception $e) {
            Log::error('Item creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()?->id,
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'Произошла ошибка при создании предмета: '.$e->getMessage()]);
        }
    }

    /**
     * Подготовить данные требований.
     */
    protected function prepareRequirements(array $requirements): array
    {
        $result = [];

        if (isset($requirements['level'])) {
            $result['level'] = (int) $requirements['level'];
        }

        if (isset($requirements['attributes'])) {
            $result['attributes'] = [];
            foreach (['strength', 'agility', 'intelligence'] as $attr) {
                if (isset($requirements['attributes'][$attr]) && $requirements['attributes'][$attr] > 0) {
                    $result['attributes'][$attr] = (int) $requirements['attributes'][$attr];
                }
            }
        }

        if (isset($requirements['skills']) && is_array($requirements['skills'])) {
            $result['skills'] = [];
            foreach ($requirements['skills'] as $skillName => $level) {
                if ($level > 0) {
                    $result['skills'][$skillName] = (int) $level;
                }
            }
        }

        return $result;
    }

    /**
     * Подготовить специфичные данные для типа предмета.
     */
    protected function prepareTypeData(string $type, array $data): array
    {
        return match ($type) {
            'weapon' => [
                'damage_min' => (int) ($data['damage_min'] ?? 0),
                'damage_max' => (int) ($data['damage_max'] ?? 0),
                'attack_speed' => (float) ($data['attack_speed'] ?? 1.0),
                'weapon_type' => $data['weapon_type'] ?? null,
                'range' => (int) ($data['range'] ?? 1),
                'durability_max' => (int) ($data['durability_max'] ?? 100),
            ],
            'armor' => [
                'defense' => (int) ($data['defense'] ?? 0),
                'durability_max' => (int) ($data['durability_max'] ?? 100),
                'armor_type' => $data['armor_type'] ?? null,
            ],
            'jewelry' => [
                'attributes_bonus' => array_filter([
                    'strength' => isset($data['attributes_bonus']['strength']) ? (int) $data['attributes_bonus']['strength'] : null,
                    'agility' => isset($data['attributes_bonus']['agility']) ? (int) $data['attributes_bonus']['agility'] : null,
                    'intelligence' => isset($data['attributes_bonus']['intelligence']) ? (int) $data['attributes_bonus']['intelligence'] : null,
                ], fn ($value) => $value !== null && $value > 0),
            ],
            'potion' => [
                'effect_type' => $data['effect_type'] ?? null,
                'effect_power' => (int) ($data['effect_power'] ?? 0),
                'duration' => (int) ($data['duration'] ?? 0),
                'cooldown' => (int) ($data['cooldown'] ?? 0),
            ],
            'rune' => [
                'spell_id' => isset($data['spell_id']) ? (int) $data['spell_id'] : null,
                'charges' => (int) ($data['charges'] ?? 0),
                'recharge_time' => (int) ($data['recharge_time'] ?? 0),
                'mana_cost_per_use' => (int) ($data['mana_cost_per_use'] ?? 0),
            ],
            'scroll' => [
                'spell_id' => isset($data['spell_id']) ? (int) $data['spell_id'] : null,
                'is_consumable' => (bool) ($data['is_consumable'] ?? true),
                'skill_required' => $data['skill_required'] ?? null,
                'skill_level_required' => isset($data['skill_level_required']) ? (int) $data['skill_level_required'] : null,
            ],
            'resource' => [
                'resource_type' => $data['resource_type'] ?? null,
                'quality' => (int) ($data['quality'] ?? 1),
            ],
            default => [],
        };
    }
}
