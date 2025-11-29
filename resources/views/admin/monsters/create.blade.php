@extends('layouts.app')

@section('title', 'Создать монстра')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-semibold mb-2">Создать монстра</h1>
        <a
            href="{{ route('admin.monsters.index') }}"
            class="text-[#f53003] dark:text-[#FF4433] hover:underline"
        >
            ← Назад к списку
        </a>
    </div>

    <form method="POST" action="{{ route('admin.monsters.store') }}" class="space-y-6">
        @csrf

        <!-- Основная информация -->
        <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-4 sm:p-6 space-y-4">
            <h2 class="text-xl font-semibold mb-4">Основная информация</h2>

            <div>
                <label for="name" class="block text-sm font-medium mb-1">Название *</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
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
                >{{ old('description') }}</textarea>
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
                            <option value="{{ $typeKey }}" {{ old('type') === $typeKey ? 'selected' : '' }}>
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
                            <option value="{{ $rankKey }}" {{ old('rank', 'normal') === $rankKey ? 'selected' : '' }}>
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
                        value="{{ old('level', 1) }}"
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
                            <option value="{{ $behaviorKey }}" {{ old('ai_behavior', 'neutral') === $behaviorKey ? 'selected' : '' }}>
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
                        value="{{ old('respawn_time', 5) }}"
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
                    {{ old('is_active', true) ? 'checked' : '' }}
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
                        value="{{ old('stats.health_max', 100) }}"
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
                        value="{{ old('stats.mana_max', 0) }}"
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
                        value="{{ old('stats.experience_reward', 0) }}"
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
                        value="{{ old('stats.attack_power', 0) }}"
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
                        value="{{ old('stats.magic_power', 0) }}"
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
                            <option value="{{ $attackTypeKey }}" {{ old('stats.attack_type', 'physical') === $attackTypeKey ? 'selected' : '' }}>
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
                        value="{{ old('stats.defense', 0) }}"
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
                        value="{{ old('stats.magic_defense', 0) }}"
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
                        value="{{ old('stats.accuracy', 0) }}"
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
                        value="{{ old('stats.magic_accuracy', 0) }}"
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
                        value="{{ old('stats.dodge', 0) }}"
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
                        value="{{ old('stats.critical_chance', 0) }}"
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
                    value="{{ old('stats.critical_power', 0) }}"
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
                Создать монстра
            </button>
            <a
                href="{{ route('admin.monsters.index') }}"
                class="px-6 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded hover:bg-gray-50 dark:hover:bg-[#0a0a0a]"
            >
                Отмена
            </a>
        </div>
    </form>
</div>
@endsection
