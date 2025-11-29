@extends('layouts.app')

@section('title', 'Редактировать монстра')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-semibold mb-2">Редактировать монстра: {{ $monster->name }}</h1>
        <a
            href="{{ route('admin.monsters.index') }}"
            class="text-[#f53003] dark:text-[#FF4433] hover:underline"
        >
            ← Назад к списку
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-300 rounded">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.monsters.update', $monster) }}" class="space-y-6">
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
                    value="{{ old('name', $monster->name) }}"
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
                >{{ old('description', $monster->description) }}</textarea>
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
                        @foreach($types as $typeKey => $typeLabel)
                            <option value="{{ $typeKey }}" {{ old('type', $monster->type) === $typeKey ? 'selected' : '' }}>
                                {{ $typeLabel }}
                            </option>
                        @endforeach
                    </select>
                    @error('type')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="rank" class="block text-sm font-medium mb-1">Ранг *</label>
                    <select
                        id="rank"
                        name="rank"
                        required
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                        @foreach($ranks as $rankKey => $rankLabel)
                            <option value="{{ $rankKey }}" {{ old('rank', $monster->rank) === $rankKey ? 'selected' : '' }}>
                                {{ $rankLabel }}
                            </option>
                        @endforeach
                    </select>
                    @error('rank')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="level" class="block text-sm font-medium mb-1">Уровень *</label>
                    <input
                        type="number"
                        id="level"
                        name="level"
                        value="{{ old('level', $monster->level) }}"
                        required
                        min="1"
                        max="100"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                    @error('level')
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
                            <option value="{{ $behaviorKey }}" {{ old('ai_behavior', $monster->ai_behavior) === $behaviorKey ? 'selected' : '' }}>
                                {{ $behaviorLabel }}
                            </option>
                        @endforeach
                    </select>
                    @error('ai_behavior')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="respawn_time" class="block text-sm font-medium mb-1">Время респавна (минуты)</label>
                    <input
                        type="number"
                        id="respawn_time"
                        name="respawn_time"
                        value="{{ old('respawn_time', $monster->respawn_time) }}"
                        min="1"
                        max="1440"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                    @error('respawn_time')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center">
                <input
                    type="checkbox"
                    id="is_active"
                    name="is_active"
                    value="1"
                    {{ old('is_active', $monster->is_active) ? 'checked' : '' }}
                    class="mr-2"
                >
                <label for="is_active" class="text-sm">Активен</label>
            </div>
        </div>

        <!-- Статистика -->
        <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-4 sm:p-6 space-y-4">
            <h2 class="text-xl font-semibold mb-4">Статистика</h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="stats_health_max" class="block text-sm font-medium mb-1">Макс. здоровье *</label>
                    <input
                        type="number"
                        id="stats_health_max"
                        name="stats[health_max]"
                        value="{{ old('stats.health_max', $monster->stats->health_max ?? 100) }}"
                        required
                        min="1"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                    @error('stats.health_max')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="stats_mana_max" class="block text-sm font-medium mb-1">Макс. мана *</label>
                    <input
                        type="number"
                        id="stats_mana_max"
                        name="stats[mana_max]"
                        value="{{ old('stats.mana_max', $monster->stats->mana_max ?? 0) }}"
                        required
                        min="0"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                    @error('stats.mana_max')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="stats_experience_reward" class="block text-sm font-medium mb-1">Опыт за убийство *</label>
                    <input
                        type="number"
                        id="stats_experience_reward"
                        name="stats[experience_reward]"
                        value="{{ old('stats.experience_reward', $monster->stats->experience_reward ?? 0) }}"
                        required
                        min="0"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                    @error('stats.experience_reward')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="stats_attack_power" class="block text-sm font-medium mb-1">Сила атаки *</label>
                    <input
                        type="number"
                        id="stats_attack_power"
                        name="stats[attack_power]"
                        value="{{ old('stats.attack_power', $monster->stats->attack_power ?? 0) }}"
                        required
                        min="0"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                    @error('stats.attack_power')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="stats_magic_power" class="block text-sm font-medium mb-1">Сила магии *</label>
                    <input
                        type="number"
                        id="stats_magic_power"
                        name="stats[magic_power]"
                        value="{{ old('stats.magic_power', $monster->stats->magic_power ?? 0) }}"
                        required
                        min="0"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                    @error('stats.magic_power')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="stats_attack_type" class="block text-sm font-medium mb-1">Тип атаки *</label>
                    <select
                        id="stats_attack_type"
                        name="stats[attack_type]"
                        required
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                        @foreach($attackTypes as $attackTypeKey => $attackTypeLabel)
                            <option value="{{ $attackTypeKey }}" {{ old('stats.attack_type', $monster->stats->attack_type ?? 'physical') === $attackTypeKey ? 'selected' : '' }}>
                                {{ $attackTypeLabel }}
                            </option>
                        @endforeach
                    </select>
                    @error('stats.attack_type')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="stats_defense" class="block text-sm font-medium mb-1">Защита *</label>
                    <input
                        type="number"
                        id="stats_defense"
                        name="stats[defense]"
                        value="{{ old('stats.defense', $monster->stats->defense ?? 0) }}"
                        required
                        min="0"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                    @error('stats.defense')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="stats_magic_defense" class="block text-sm font-medium mb-1">Маг. защита *</label>
                    <input
                        type="number"
                        id="stats_magic_defense"
                        name="stats[magic_defense]"
                        value="{{ old('stats.magic_defense', $monster->stats->magic_defense ?? 0) }}"
                        required
                        min="0"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                    @error('stats.magic_defense')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div>
                    <label for="stats_accuracy" class="block text-sm font-medium mb-1">Точность (%)</label>
                    <input
                        type="number"
                        id="stats_accuracy"
                        name="stats[accuracy]"
                        value="{{ old('stats.accuracy', $monster->stats->accuracy ?? 0) }}"
                        min="0"
                        max="100"
                        step="0.01"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                </div>
                <div>
                    <label for="stats_magic_accuracy" class="block text-sm font-medium mb-1">Маг. точность (%)</label>
                    <input
                        type="number"
                        id="stats_magic_accuracy"
                        name="stats[magic_accuracy]"
                        value="{{ old('stats.magic_accuracy', $monster->stats->magic_accuracy ?? 0) }}"
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
                        value="{{ old('stats.dodge', $monster->stats->dodge ?? 0) }}"
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
                        value="{{ old('stats.critical_chance', $monster->stats->critical_chance ?? 0) }}"
                        min="0"
                        max="100"
                        step="0.01"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                </div>
            </div>

            <div>
                <label for="stats_critical_power" class="block text-sm font-medium mb-1">Сила крита</label>
                <input
                    type="number"
                    id="stats_critical_power"
                    name="stats[critical_power]"
                    value="{{ old('stats.critical_power', $monster->stats->critical_power ?? 0) }}"
                    min="0"
                    step="0.01"
                    class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                >
            </div>
        </div>

        <div class="flex gap-4">
            <button
                type="submit"
                class="px-6 py-2 bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded hover:bg-black dark:hover:bg-white"
            >
                Сохранить изменения
            </button>
            <a
                href="{{ route('admin.monsters.index') }}"
                class="px-6 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded hover:bg-gray-50 dark:hover:bg-[#0a0a0a]"
            >
                Отмена
            </a>
        </div>
    </form>

    <!-- Лут -->
    <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-4 sm:p-6 space-y-4 mt-6">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold">Лут</h2>
        </div>

        @if($monster->loot && $monster->loot->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]">
                    <thead class="bg-gray-50 dark:bg-[#0a0a0a]">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Предмет</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Количество</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Шанс</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Гарантированный</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-[#161615] divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]">
                        @foreach($monster->loot as $loot)
                            <tr>
                                <td class="px-4 py-3 text-sm">{{ $loot->item ? $loot->item->name : 'N/A' }}</td>
                                <td class="px-4 py-3 text-sm">{{ $loot->min_quantity }}-{{ $loot->max_quantity }}</td>
                                <td class="px-4 py-3 text-sm">{{ $loot->drop_chance }}%</td>
                                <td class="px-4 py-3 text-sm">{{ $loot->is_guaranteed ? 'Да' : 'Нет' }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <form
                                        method="POST"
                                        action="{{ route('admin.monsters.remove-loot', [$monster, $loot]) }}"
                                        class="inline"
                                        onsubmit="return confirm('Вы уверены, что хотите удалить этот лут?');"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="text-red-600 dark:text-red-400 hover:underline"
                                        >
                                            Удалить
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-sm text-gray-500 dark:text-gray-400">Лут не настроен</p>
        @endif

        <div class="border-t border-[#e3e3e0] dark:border-[#3E3E3A] pt-4">
            <h3 class="text-lg font-semibold mb-4">Добавить лут</h3>
            <form method="POST" action="{{ route('admin.monsters.add-loot', $monster) }}" class="grid grid-cols-1 sm:grid-cols-5 gap-4">
                @csrf
                <div>
                    <label for="loot_item_id" class="block text-sm font-medium mb-1">Предмет *</label>
                    <select
                        id="loot_item_id"
                        name="item_id"
                        required
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                        <option value="">Выберите предмет</option>
                        @foreach($items as $item)
                            <option value="{{ $item->id }}" {{ old('item_id') == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                        @endforeach
                    </select>
                    @error('item_id')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="loot_min_quantity" class="block text-sm font-medium mb-1">Мин. кол-во *</label>
                    <input
                        type="number"
                        id="loot_min_quantity"
                        name="min_quantity"
                        value="{{ old('min_quantity', 1) }}"
                        required
                        min="1"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                    @error('min_quantity')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="loot_max_quantity" class="block text-sm font-medium mb-1">Макс. кол-во *</label>
                    <input
                        type="number"
                        id="loot_max_quantity"
                        name="max_quantity"
                        value="{{ old('max_quantity', 1) }}"
                        required
                        min="1"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                    @error('max_quantity')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="loot_drop_chance" class="block text-sm font-medium mb-1">Шанс (%) *</label>
                    <input
                        type="number"
                        id="loot_drop_chance"
                        name="drop_chance"
                        value="{{ old('drop_chance', 100) }}"
                        required
                        min="1"
                        max="100"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                    @error('drop_chance')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex items-end">
                    <div class="flex-1">
                        <div class="flex items-center mb-2">
                            <input
                                type="checkbox"
                                id="loot_is_guaranteed"
                                name="is_guaranteed"
                                value="1"
                                {{ old('is_guaranteed') ? 'checked' : '' }}
                                class="mr-2"
                            >
                            <label for="loot_is_guaranteed" class="text-sm">Гарантированный</label>
                        </div>
                        <button
                            type="submit"
                            class="w-full px-4 py-2 bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded hover:bg-black dark:hover:bg-white"
                        >
                            Добавить
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Способности -->
    <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-4 sm:p-6 space-y-4 mt-6">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold">Способности</h2>
        </div>

        @if($monster->skills->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]">
                    <thead class="bg-gray-50 dark:bg-[#0a0a0a]">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Название</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Тип</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Сила</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Мана</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Шанс</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-[#161615] divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]">
                        @foreach($monster->skills as $skill)
                            <tr>
                                <td class="px-4 py-3 text-sm">{{ $skill->skill_name }}</td>
                                <td class="px-4 py-3 text-sm">{{ $skill->skill_type }}</td>
                                <td class="px-4 py-3 text-sm">{{ $skill->power }}</td>
                                <td class="px-4 py-3 text-sm">{{ $skill->mana_cost }}</td>
                                <td class="px-4 py-3 text-sm">{{ $skill->chance_to_use }}%</td>
                                <td class="px-4 py-3 text-sm">
                                    <form
                                        method="POST"
                                        action="{{ route('admin.monsters.remove-skill', [$monster, $skill]) }}"
                                        class="inline"
                                        onsubmit="return confirm('Вы уверены, что хотите удалить эту способность?');"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="text-red-600 dark:text-red-400 hover:underline"
                                        >
                                            Удалить
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-sm text-gray-500 dark:text-gray-400">Способности не настроены</p>
        @endif

        <div class="border-t border-[#e3e3e0] dark:border-[#3E3E3A] pt-4">
            <h3 class="text-lg font-semibold mb-4">Добавить способность</h3>
            <form method="POST" action="{{ route('admin.monsters.add-skill', $monster) }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="skill_name" class="block text-sm font-medium mb-1">Название *</label>
                        <input
                            type="text"
                            id="skill_name"
                            name="skill_name"
                            required
                            class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                        >
                    </div>
                    <div>
                        <label for="skill_type" class="block text-sm font-medium mb-1">Тип *</label>
                        <select
                            id="skill_type"
                            name="skill_type"
                            required
                            class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                        >
                            <option value="attack">Атака</option>
                            <option value="heal">Исцеление</option>
                            <option value="buff">Бафф</option>
                            <option value="debuff">Дебафф</option>
                            <option value="summon">Призыв</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div>
                        <label for="skill_power" class="block text-sm font-medium mb-1">Сила *</label>
                        <input
                            type="number"
                            id="skill_power"
                            name="power"
                            value="0"
                            required
                            min="0"
                            class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                        >
                    </div>
                    <div>
                        <label for="skill_mana_cost" class="block text-sm font-medium mb-1">Стоимость маны *</label>
                        <input
                            type="number"
                            id="skill_mana_cost"
                            name="mana_cost"
                            value="0"
                            required
                            min="0"
                            class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                        >
                    </div>
                    <div>
                        <label for="skill_cooldown" class="block text-sm font-medium mb-1">Перезарядка (сек) *</label>
                        <input
                            type="number"
                            id="skill_cooldown"
                            name="cooldown"
                            value="0"
                            required
                            min="0"
                            class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                        >
                    </div>
                    <div>
                        <label for="skill_chance_to_use" class="block text-sm font-medium mb-1">Шанс (%) *</label>
                        <input
                            type="number"
                            id="skill_chance_to_use"
                            name="chance_to_use"
                            value="100"
                            required
                            min="1"
                            max="100"
                            class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                        >
                    </div>
                </div>
                <div>
                    <label for="skill_damage_type" class="block text-sm font-medium mb-1">Тип урона</label>
                    <select
                        id="skill_damage_type"
                        name="damage_type"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                        <option value="">Нет</option>
                        <option value="physical">Физический</option>
                        <option value="fire">Огонь</option>
                        <option value="ice">Лед</option>
                        <option value="lightning">Молния</option>
                        <option value="poison">Яд</option>
                    </select>
                </div>
                <div>
                    <label for="skill_description" class="block text-sm font-medium mb-1">Описание</label>
                    <textarea
                        id="skill_description"
                        name="description"
                        rows="2"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    ></textarea>
                </div>
                <button
                    type="submit"
                    class="px-4 py-2 bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded hover:bg-black dark:hover:bg-white"
                >
                    Добавить способность
                </button>
            </form>
        </div>
    </div>

    <!-- Принудительный спавн -->
    <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-4 sm:p-6 space-y-4 mt-6">
        <h2 class="text-xl font-semibold mb-4">Принудительный спавн</h2>
        <form method="POST" action="{{ route('admin.monsters.force-spawn', $monster) }}" class="flex gap-4">
            @csrf
            <select
                name="location_id"
                required
                class="flex-1 px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
            >
                <option value="">Выберите локацию</option>
                @foreach($locations as $location)
                    <option value="{{ $location->id }}">{{ $location->name }}</option>
                @endforeach
            </select>
            <button
                type="submit"
                class="px-4 py-2 bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded hover:bg-black dark:hover:bg-white"
            >
                Заспавнить
            </button>
        </form>
    </div>
</div>
@endsection

