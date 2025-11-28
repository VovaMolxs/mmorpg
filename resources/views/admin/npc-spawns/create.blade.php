@extends('layouts.app')

@section('title', 'Создать спавн NPC')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-semibold mb-2">Создать спавн NPC</h1>
        <a
            href="{{ route('admin.npc-spawns.index') }}"
            class="text-[#f53003] dark:text-[#FF4433] hover:underline"
        >
            ← Назад к списку
        </a>
    </div>

    <form method="POST" action="{{ route('admin.npc-spawns.store') }}" class="space-y-6">
        @csrf

        <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-4 sm:p-6 space-y-4">
            <h2 class="text-xl font-semibold mb-4">Основная информация</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="npc_id" class="block text-sm font-medium mb-1">NPC *</label>
                    <select
                        id="npc_id"
                        name="npc_id"
                        required
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                        <option value="">Выберите NPC</option>
                        @foreach($npcs as $npc)
                            <option value="{{ $npc->id }}" {{ old('npc_id') == $npc->id ? 'selected' : '' }}>
                                {{ $npc->name }} (Уровень: {{ $npc->stats?->level ?? 'N/A' }})
                            </option>
                        @endforeach
                    </select>
                    @error('npc_id')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="location_id" class="block text-sm font-medium mb-1">Локация *</label>
                    <select
                        id="location_id"
                        name="location_id"
                        required
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                        <option value="">Выберите локацию</option>
                        @foreach($locations as $location)
                            <option value="{{ $location->id }}" {{ old('location_id') == $location->id ? 'selected' : '' }}>
                                {{ $location->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('location_id')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="min_instances" class="block text-sm font-medium mb-1">Мин. экземпляров *</label>
                    <input
                        type="number"
                        id="min_instances"
                        name="min_instances"
                        value="{{ old('min_instances', 0) }}"
                        required
                        min="0"
                        max="100"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                    @error('min_instances')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="max_instances" class="block text-sm font-medium mb-1">Макс. экземпляров *</label>
                    <input
                        type="number"
                        id="max_instances"
                        name="max_instances"
                        value="{{ old('max_instances', 3) }}"
                        required
                        min="1"
                        max="100"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                    @error('max_instances')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="respawn_time_min" class="block text-sm font-medium mb-1">Мин. время респавна (мин) *</label>
                    <input
                        type="number"
                        id="respawn_time_min"
                        name="respawn_time_min"
                        value="{{ old('respawn_time_min', 5) }}"
                        required
                        min="1"
                        max="1440"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                    @error('respawn_time_min')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="respawn_time_max" class="block text-sm font-medium mb-1">Макс. время респавна (мин) *</label>
                    <input
                        type="number"
                        id="respawn_time_max"
                        name="respawn_time_max"
                        value="{{ old('respawn_time_max', 15) }}"
                        required
                        min="1"
                        max="1440"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                    @error('respawn_time_max')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="spawn_chance" class="block text-sm font-medium mb-1">Шанс спавна (%) *</label>
                    <input
                        type="number"
                        id="spawn_chance"
                        name="spawn_chance"
                        value="{{ old('spawn_chance', 100) }}"
                        required
                        min="1"
                        max="100"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                    @error('spawn_chance')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="spawn_radius" class="block text-sm font-medium mb-1">Радиус спавна</label>
                    <input
                        type="number"
                        id="spawn_radius"
                        name="spawn_radius"
                        value="{{ old('spawn_radius', 0) }}"
                        min="0"
                        max="1000"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                    @error('spawn_radius')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label class="flex items-center">
                    <input
                        type="checkbox"
                        name="is_active"
                        value="1"
                        {{ old('is_active', true) ? 'checked' : '' }}
                        class="mr-2"
                    >
                    <span class="text-sm">Активен</span>
                </label>
            </div>
        </div>

        <!-- Расписание спавна -->
        <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-4 sm:p-6 space-y-4">
            <h2 class="text-xl font-semibold mb-4">Расписание спавна</h2>

            <div>
                <label for="spawn_schedule_type" class="block text-sm font-medium mb-1">Тип расписания</label>
                <select
                    id="spawn_schedule_type"
                    name="spawn_schedule[type]"
                    onchange="toggleScheduleHours()"
                    class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                >
                    <option value="">Круглосуточно</option>
                    <option value="always" {{ old('spawn_schedule.type') === 'always' ? 'selected' : '' }}>Всегда</option>
                    <option value="day" {{ old('spawn_schedule.type') === 'day' ? 'selected' : '' }}>Только день (6:00 - 22:00)</option>
                    <option value="night" {{ old('spawn_schedule.type') === 'night' ? 'selected' : '' }}>Только ночь (22:00 - 6:00)</option>
                    <option value="custom" {{ old('spawn_schedule.type') === 'custom' ? 'selected' : '' }}>Кастомное</option>
                </select>
            </div>

            <div id="schedule_hours_container" style="display: none;">
                <label class="block text-sm font-medium mb-2">Часы спавна (0-23)</label>
                <div class="grid grid-cols-6 sm:grid-cols-12 gap-2">
                    @for($i = 0; $i < 24; $i++)
                        <label class="flex items-center">
                            <input
                                type="checkbox"
                                name="spawn_schedule[hours][]"
                                value="{{ $i }}"
                                {{ in_array($i, old('spawn_schedule.hours', [])) ? 'checked' : '' }}
                                class="mr-1"
                            >
                            <span class="text-xs">{{ $i }}</span>
                        </label>
                    @endfor
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-4">
            <a
                href="{{ route('admin.npc-spawns.index') }}"
                class="px-6 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded hover:bg-gray-50 dark:hover:bg-[#0a0a0a]"
            >
                Отмена
            </a>
            <button
                type="submit"
                class="px-6 py-2 bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded hover:bg-black dark:hover:bg-white"
            >
                Создать спавн
            </button>
        </div>
    </form>
</div>

<script>
function toggleScheduleHours() {
    const type = document.getElementById('spawn_schedule_type').value;
    const container = document.getElementById('schedule_hours_container');
    container.style.display = type === 'custom' ? 'block' : 'none';
}

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    toggleScheduleHours();
});
</script>
@endsection



