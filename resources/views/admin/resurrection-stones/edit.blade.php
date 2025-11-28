@extends('layouts.app')

@section('title', 'Редактировать камень воскрешения')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-semibold mb-2">Редактировать камень воскрешения</h1>
        <a
            href="{{ route('admin.resurrection-stones.index') }}"
            class="text-[#f53003] dark:text-[#FF4433] hover:underline"
        >
            ← Назад к списку
        </a>
    </div>

    <form method="POST" action="{{ route('admin.resurrection-stones.update', $stone) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-4 sm:p-6 space-y-4">
            <h2 class="text-xl font-semibold mb-4">Основная информация</h2>

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
                        <option value="{{ $location->id }}" {{ (old('location_id', $stone->location_id) == $location->id) ? 'selected' : '' }}>
                            {{ $location->name }}
                        </option>
                    @endforeach
                </select>
                @error('location_id')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="name" class="block text-sm font-medium mb-1">Название *</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name', $stone->name) }}"
                    required
                    maxlength="255"
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
                >{{ old('description', $stone->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="level_required" class="block text-sm font-medium mb-1">Минимальный уровень *</label>
                    <input
                        type="number"
                        id="level_required"
                        name="level_required"
                        value="{{ old('level_required', $stone->level_required) }}"
                        min="1"
                        max="100"
                        required
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                    @error('level_required')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="cooldown_minutes" class="block text-sm font-medium mb-1">Перезарядка (минуты) *</label>
                    <input
                        type="number"
                        id="cooldown_minutes"
                        name="cooldown_minutes"
                        value="{{ old('cooldown_minutes', $stone->cooldown_minutes) }}"
                        min="0"
                        max="1440"
                        required
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                    @error('cooldown_minutes')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">0 = без перезарядки</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="visual_effect" class="block text-sm font-medium mb-1">Визуальный эффект *</label>
                    <select
                        id="visual_effect"
                        name="visual_effect"
                        required
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                        <option value="glow" {{ (old('visual_effect', $stone->visual_effect) == 'glow') ? 'selected' : '' }}>Свечение (glow)</option>
                        <option value="particles" {{ (old('visual_effect', $stone->visual_effect) == 'particles') ? 'selected' : '' }}>Частицы (particles)</option>
                        <option value="aura" {{ (old('visual_effect', $stone->visual_effect) == 'aura') ? 'selected' : '' }}>Аура (aura)</option>
                    </select>
                    @error('visual_effect')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            {{ old('is_active', $stone->is_active) ? 'checked' : '' }}
                            class="w-4 h-4 text-[#f53003] dark:text-[#FF4433] border-[#e3e3e0] dark:border-[#3E3E3A] rounded focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                        >
                        <span class="text-sm font-medium">Активен</span>
                    </label>
                    @error('is_active')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            @if($stone->last_used_at)
                <div class="mt-4 p-3 bg-gray-50 dark:bg-[#0a0a0a] rounded">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        <strong>Последнее использование:</strong> {{ $stone->last_used_at->format('d.m.Y H:i:s') }}
                    </p>
                </div>
            @endif
        </div>

        <div class="flex gap-4">
            <button
                type="submit"
                class="px-6 py-2 bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded hover:bg-black dark:hover:bg-white"
            >
                Сохранить изменения
            </button>
            <a
                href="{{ route('admin.resurrection-stones.index') }}"
                class="px-6 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded hover:bg-gray-50 dark:hover:bg-[#0a0a0a]"
            >
                Отмена
            </a>
        </div>
    </form>
</div>
@endsection

