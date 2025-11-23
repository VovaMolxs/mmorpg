@extends('layouts.app')

@section('title', 'Редактировать NPC')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-semibold mb-2">Редактировать NPC: {{ $npc->name }}</h1>
        <a
            href="{{ route('admin.npcs.index') }}"
            class="text-[#f53003] dark:text-[#FF4433] hover:underline"
        >
            ← Назад к списку
        </a>
    </div>

    <form method="POST" action="{{ route('admin.npcs.update', $npc) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Основная информация -->
        <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-4 sm:p-6 space-y-4">
            <h2 class="text-xl font-semibold mb-4">Основная информация</h2>

            <div>
                <label for="name" class="block text-sm font-medium mb-1">Название *</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name', $npc->name) }}"
                    required
                    class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                >
                @error('name')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium mb-1">Описание</label>
                <textarea
                    id="description"
                    name="description"
                    rows="3"
                    class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                >{{ old('description', $npc->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="type" class="block text-sm font-medium mb-1">Тип *</label>
                    <select
                        id="type"
                        name="type"
                        required
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                        <option value="">Выберите тип</option>
                        @foreach($types as $typeKey => $typeLabel)
                            <option value="{{ $typeKey }}" {{ old('type', $npc->type) === $typeKey ? 'selected' : '' }}>
                                {{ $typeLabel }}
                            </option>
                        @endforeach
                    </select>
                    @error('type')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="ai_behavior" class="block text-sm font-medium mb-1">Поведение AI *</label>
                    <select
                        id="ai_behavior"
                        name="ai_behavior"
                        required
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                        @foreach($aiBehaviors as $behaviorKey => $behaviorLabel)
                            <option value="{{ $behaviorKey }}" {{ old('ai_behavior', $npc->ai_behavior) === $behaviorKey ? 'selected' : '' }}>
                                {{ $behaviorLabel }}
                            </option>
                        @endforeach
                    </select>
                    @error('ai_behavior')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="location_id" class="block text-sm font-medium mb-1">Локация</label>
                    <select
                        id="location_id"
                        name="location_id"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                        <option value="">Без локации</option>
                        @foreach($locations as $location)
                            <option value="{{ $location->id }}" {{ old('location_id', $npc->location_id) == $location->id ? 'selected' : '' }}>
                                {{ $location->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('location_id')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="respawn_time" class="block text-sm font-medium mb-1">Время респавна (минуты)</label>
                    <input
                        type="number"
                        id="respawn_time"
                        name="respawn_time"
                        value="{{ old('respawn_time', $npc->respawn_time) }}"
                        min="1"
                        max="1440"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                    @error('respawn_time')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="flex items-center">
                    <input
                        type="checkbox"
                        id="is_merchant"
                        name="is_merchant"
                        value="1"
                        {{ old('is_merchant', $npc->is_merchant) ? 'checked' : '' }}
                        class="mr-2"
                    >
                    <label for="is_merchant" class="text-sm">Торговец</label>
                </div>
                <div class="flex items-center">
                    <input
                        type="checkbox"
                        id="is_teacher"
                        name="is_teacher"
                        value="1"
                        {{ old('is_teacher', $npc->is_teacher) ? 'checked' : '' }}
                        class="mr-2"
                    >
                    <label for="is_teacher" class="text-sm">Учитель</label>
                </div>
                <div class="flex items-center">
                    <input
                        type="checkbox"
                        id="is_quest_giver"
                        name="is_quest_giver"
                        value="1"
                        {{ old('is_quest_giver', $npc->is_quest_giver) ? 'checked' : '' }}
                        class="mr-2"
                    >
                    <label for="is_quest_giver" class="text-sm">Квестодатель</label>
                </div>
                <div class="flex items-center">
                    <input
                        type="checkbox"
                        id="is_hostile"
                        name="is_hostile"
                        value="1"
                        {{ old('is_hostile', $npc->is_hostile) ? 'checked' : '' }}
                        class="mr-2"
                    >
                    <label for="is_hostile" class="text-sm">Враждебный</label>
                </div>
            </div>
        </div>

        <!-- Статистика -->
        <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-4 sm:p-6 space-y-4">
            <h2 class="text-xl font-semibold mb-4">Статистика</h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="stats_level" class="block text-sm font-medium mb-1">Уровень *</label>
                    <input
                        type="number"
                        id="stats_level"
                        name="stats[level]"
                        value="{{ old('stats.level', $npc->stats?->level ?? 1) }}"
                        required
                        min="1"
                        max="100"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                    @error('stats.level')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="stats_health_max" class="block text-sm font-medium mb-1">Макс. здоровье *</label>
                    <input
                        type="number"
                        id="stats_health_max"
                        name="stats[health_max]"
                        value="{{ old('stats.health_max', $npc->stats?->health_max ?? 100) }}"
                        required
                        min="1"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                    @error('stats.health_max')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="stats_mana_max" class="block text-sm font-medium mb-1">Макс. мана</label>
                    <input
                        type="number"
                        id="stats_mana_max"
                        name="stats[mana_max]"
                        value="{{ old('stats.mana_max', $npc->stats?->mana_max ?? 0) }}"
                        min="0"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="stats_strength" class="block text-sm font-medium mb-1">Сила *</label>
                    <input
                        type="number"
                        id="stats_strength"
                        name="stats[strength]"
                        value="{{ old('stats.strength', $npc->stats?->strength ?? 1) }}"
                        required
                        min="1"
                        max="100"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                </div>
                <div>
                    <label for="stats_agility" class="block text-sm font-medium mb-1">Ловкость *</label>
                    <input
                        type="number"
                        id="stats_agility"
                        name="stats[agility]"
                        value="{{ old('stats.agility', $npc->stats?->agility ?? 1) }}"
                        required
                        min="1"
                        max="100"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                </div>
                <div>
                    <label for="stats_intelligence" class="block text-sm font-medium mb-1">Интеллект *</label>
                    <input
                        type="number"
                        id="stats_intelligence"
                        name="stats[intelligence]"
                        value="{{ old('stats.intelligence', $npc->stats?->intelligence ?? 1) }}"
                        required
                        min="1"
                        max="100"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="stats_attack_power" class="block text-sm font-medium mb-1">Сила атаки</label>
                    <input
                        type="number"
                        id="stats_attack_power"
                        name="stats[attack_power]"
                        value="{{ old('stats.attack_power', $npc->stats?->attack_power ?? 0) }}"
                        min="0"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                </div>
                <div>
                    <label for="stats_defense" class="block text-sm font-medium mb-1">Защита</label>
                    <input
                        type="number"
                        id="stats_defense"
                        name="stats[defense]"
                        value="{{ old('stats.defense', $npc->stats?->defense ?? 0) }}"
                        min="0"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                </div>
                <div>
                    <label for="stats_magic_defense" class="block text-sm font-medium mb-1">Маг. защита</label>
                    <input
                        type="number"
                        id="stats_magic_defense"
                        name="stats[magic_defense]"
                        value="{{ old('stats.magic_defense', $npc->stats?->magic_defense ?? 0) }}"
                        min="0"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div>
                    <label for="stats_accuracy" class="block text-sm font-medium mb-1">Точность (%)</label>
                    <input
                        type="number"
                        id="stats_accuracy"
                        name="stats[accuracy]"
                        value="{{ old('stats.accuracy', $npc->stats?->accuracy ?? 50) }}"
                        min="0"
                        max="100"
                        step="0.01"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                </div>
                <div>
                    <label for="stats_dodge" class="block text-sm font-medium mb-1">Уворот (%)</label>
                    <input
                        type="number"
                        id="stats_dodge"
                        name="stats[dodge]"
                        value="{{ old('stats.dodge', $npc->stats?->dodge ?? 0) }}"
                        min="0"
                        max="100"
                        step="0.01"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                </div>
                <div>
                    <label for="stats_critical_chance" class="block text-sm font-medium mb-1">Шанс крита (%)</label>
                    <input
                        type="number"
                        id="stats_critical_chance"
                        name="stats[critical_chance]"
                        value="{{ old('stats.critical_chance', $npc->stats?->critical_chance ?? 5) }}"
                        min="0"
                        max="100"
                        step="0.01"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                </div>
                <div>
                    <label for="stats_critical_power" class="block text-sm font-medium mb-1">Сила крита (x)</label>
                    <input
                        type="number"
                        id="stats_critical_power"
                        name="stats[critical_power]"
                        value="{{ old('stats.critical_power', $npc->stats?->critical_power ?? 1.5) }}"
                        min="1"
                        max="10"
                        step="0.01"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="stats_experience_reward" class="block text-sm font-medium mb-1">Опыт за убийство</label>
                    <input
                        type="number"
                        id="stats_experience_reward"
                        name="stats[experience_reward]"
                        value="{{ old('stats.experience_reward', $npc->stats?->experience_reward ?? 0) }}"
                        min="0"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                </div>
                <div>
                    <label for="stats_gold_reward_min" class="block text-sm font-medium mb-1">Мин. золото</label>
                    <input
                        type="number"
                        id="stats_gold_reward_min"
                        name="stats[gold_reward_min]"
                        value="{{ old('stats.gold_reward_min', $npc->stats?->gold_reward_min ?? 0) }}"
                        min="0"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                </div>
                <div>
                    <label for="stats_gold_reward_max" class="block text-sm font-medium mb-1">Макс. золото</label>
                    <input
                        type="number"
                        id="stats_gold_reward_max"
                        name="stats[gold_reward_max]"
                        value="{{ old('stats.gold_reward_max', $npc->stats?->gold_reward_max ?? 0) }}"
                        min="0"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                </div>
            </div>
        </div>

        <!-- Лут -->
        <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-4 sm:p-6 space-y-4">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold">Лут</h2>
                <button
                    type="button"
                    onclick="addLootRow()"
                    class="px-4 py-2 bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded hover:bg-black dark:hover:bg-white text-sm"
                >
                    Добавить предмет
                </button>
            </div>
            <div id="loot-container">
                @if($npc->loot && $npc->loot->count() > 0)
                    @foreach($npc->loot as $loot)
                        <div class="grid grid-cols-1 sm:grid-cols-5 gap-4 items-end mb-4" id="loot-row-{{ $loop->index }}">
                            <div>
                                <label class="block text-sm font-medium mb-1">Предмет</label>
                                <select name="loot[{{ $loop->index }}][item_id]" class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a]">
                                    <option value="">Выберите предмет</option>
                                    @foreach($items as $item)
                                        <option value="{{ $item->id }}" {{ $loot->item_id == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Мин. кол-во</label>
                                <input type="number" name="loot[{{ $loop->index }}][min_quantity]" value="{{ $loot->min_quantity }}" min="1" class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a]">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Макс. кол-во</label>
                                <input type="number" name="loot[{{ $loop->index }}][max_quantity]" value="{{ $loot->max_quantity }}" min="1" class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a]">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Шанс (%)</label>
                                <input type="number" name="loot[{{ $loop->index }}][drop_chance]" value="{{ $loot->drop_chance }}" min="1" max="100" class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a]">
                            </div>
                            <div>
                                <button type="button" onclick="removeLootRow({{ $loop->index }})" class="w-full px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Удалить</button>
                            </div>
                        </div>
                    @endforeach
                    @php $lootIndex = $npc->loot->count(); @endphp
                @else
                    @php $lootIndex = 0; @endphp
                @endif
            </div>
        </div>

        <!-- Навыки -->
        <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-4 sm:p-6 space-y-4">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold">Навыки</h2>
                <button
                    type="button"
                    onclick="addSkillRow()"
                    class="px-4 py-2 bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded hover:bg-black dark:hover:bg-white text-sm"
                >
                    Добавить навык
                </button>
            </div>
            <div id="skills-container">
                @if($npc->skills && $npc->skills->count() > 0)
                    @foreach($npc->skills as $skill)
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end mb-4" id="skill-row-{{ $loop->index }}">
                            <div>
                                <label class="block text-sm font-medium mb-1">Навык</label>
                                <select name="skills[{{ $loop->index }}][skill_id]" class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a]">
                                    <option value="">Выберите навык</option>
                                    @foreach($skills as $skillItem)
                                        <option value="{{ $skillItem->id }}" {{ $skill->skill_id == $skillItem->id ? 'selected' : '' }}>{{ $skillItem->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Уровень</label>
                                <input type="number" name="skills[{{ $loop->index }}][level]" value="{{ $skill->level }}" min="1" max="100" class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a]">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Активен</label>
                                <input type="checkbox" name="skills[{{ $loop->index }}][is_active]" value="1" {{ $skill->is_active ? 'checked' : '' }} class="w-full">
                            </div>
                            <div>
                                <button type="button" onclick="removeSkillRow({{ $loop->index }})" class="w-full px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Удалить</button>
                            </div>
                        </div>
                    @endforeach
                    @php $skillIndex = $npc->skills->count(); @endphp
                @else
                    @php $skillIndex = 0; @endphp
                @endif
            </div>
        </div>

        <!-- Экипировка -->
        <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-4 sm:p-6 space-y-4">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold">Экипировка</h2>
                <button
                    type="button"
                    onclick="addEquipmentRow()"
                    class="px-4 py-2 bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded hover:bg-black dark:hover:bg-white text-sm"
                >
                    Добавить предмет
                </button>
            </div>
            <div id="equipment-container">
                @if($npc->equipment && $npc->equipment->count() > 0)
                    @foreach($npc->equipment as $equipment)
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end mb-4" id="equipment-row-{{ $loop->index }}">
                            <div>
                                <label class="block text-sm font-medium mb-1">Предмет</label>
                                <select name="equipment[{{ $loop->index }}][item_id]" class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a]">
                                    <option value="">Выберите предмет</option>
                                    @foreach($items as $item)
                                        <option value="{{ $item->id }}" {{ $equipment->item_id == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Слот</label>
                                <select name="equipment[{{ $loop->index }}][slot]" class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a]">
                                    <option value="">Выберите слот</option>
                                    @foreach($equipmentSlots as $slotKey => $slotLabel)
                                        <option value="{{ $slotKey }}" {{ $equipment->slot == $slotKey ? 'selected' : '' }}>{{ $slotLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <button type="button" onclick="removeEquipmentRow({{ $loop->index }})" class="w-full px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Удалить</button>
                            </div>
                        </div>
                    @endforeach
                    @php $equipmentIndex = $npc->equipment->count(); @endphp
                @else
                    @php $equipmentIndex = 0; @endphp
                @endif
            </div>
        </div>

        <div class="flex justify-end gap-4">
            <a
                href="{{ route('admin.npcs.index') }}"
                class="px-6 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded hover:bg-gray-50 dark:hover:bg-[#0a0a0a]"
            >
                Отмена
            </a>
            <button
                type="submit"
                class="px-6 py-2 bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded hover:bg-black dark:hover:bg-white"
            >
                Обновить NPC
            </button>
        </div>
    </form>
</div>

<script>
let lootIndex = {{ $lootIndex ?? 0 }};
let skillIndex = {{ $skillIndex ?? 0 }};
let equipmentIndex = {{ $equipmentIndex ?? 0 }};

function addLootRow() {
    const container = document.getElementById('loot-container');
    const row = document.createElement('div');
    row.className = 'grid grid-cols-1 sm:grid-cols-5 gap-4 items-end';
    row.id = `loot-row-${lootIndex}`;
    row.innerHTML = `
        <div>
            <label class="block text-sm font-medium mb-1">Предмет</label>
            <select name="loot[${lootIndex}][item_id]" class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a]">
                <option value="">Выберите предмет</option>
                @foreach($items as $item)
                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Мин. кол-во</label>
            <input type="number" name="loot[${lootIndex}][min_quantity]" value="1" min="1" class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a]">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Макс. кол-во</label>
            <input type="number" name="loot[${lootIndex}][max_quantity]" value="1" min="1" class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a]">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Шанс (%)</label>
            <input type="number" name="loot[${lootIndex}][drop_chance]" value="100" min="1" max="100" class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a]">
        </div>
        <div>
            <button type="button" onclick="removeLootRow(${lootIndex})" class="w-full px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Удалить</button>
        </div>
    `;
    container.appendChild(row);
    lootIndex++;
}

function removeLootRow(index) {
    const row = document.getElementById(`loot-row-${index}`);
    if (row) {
        row.remove();
    }
}

function addSkillRow() {
    const container = document.getElementById('skills-container');
    const row = document.createElement('div');
    row.className = 'grid grid-cols-1 sm:grid-cols-4 gap-4 items-end';
    row.id = `skill-row-${skillIndex}`;
    row.innerHTML = `
        <div>
            <label class="block text-sm font-medium mb-1">Навык</label>
            <select name="skills[${skillIndex}][skill_id]" class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a]">
                <option value="">Выберите навык</option>
                @foreach($skills as $skill)
                    <option value="{{ $skill->id }}">{{ $skill->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Уровень</label>
            <input type="number" name="skills[${skillIndex}][level]" value="1" min="1" max="100" class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a]">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Активен</label>
            <input type="checkbox" name="skills[${skillIndex}][is_active]" value="1" checked class="w-full">
        </div>
        <div>
            <button type="button" onclick="removeSkillRow(${skillIndex})" class="w-full px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Удалить</button>
        </div>
    `;
    container.appendChild(row);
    skillIndex++;
}

function removeSkillRow(index) {
    const row = document.getElementById(`skill-row-${index}`);
    if (row) {
        row.remove();
    }
}

function addEquipmentRow() {
    const container = document.getElementById('equipment-container');
    const row = document.createElement('div');
    row.className = 'grid grid-cols-1 sm:grid-cols-3 gap-4 items-end';
    row.id = `equipment-row-${equipmentIndex}`;
    row.innerHTML = `
        <div>
            <label class="block text-sm font-medium mb-1">Предмет</label>
            <select name="equipment[${equipmentIndex}][item_id]" class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a]">
                <option value="">Выберите предмет</option>
                @foreach($items as $item)
                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Слот</label>
            <select name="equipment[${equipmentIndex}][slot]" class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a]">
                <option value="">Выберите слот</option>
                @foreach($equipmentSlots as $slotKey => $slotLabel)
                    <option value="{{ $slotKey }}">{{ $slotLabel }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <button type="button" onclick="removeEquipmentRow(${equipmentIndex})" class="w-full px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Удалить</button>
        </div>
    `;
    container.appendChild(row);
    equipmentIndex++;
}

function removeEquipmentRow(index) {
    const row = document.getElementById(`equipment-row-${index}`);
    if (row) {
        row.remove();
    }
}
</script>
@endsection

