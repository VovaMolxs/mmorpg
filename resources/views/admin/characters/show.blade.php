@extends('layouts.app')

@section('title', 'Персонаж: ' . $character->name)

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <h1 class="text-2xl sm:text-3xl font-semibold break-words">Персонаж: {{ $character->name }}</h1>
        <a
            href="{{ route('admin.characters.index') }}"
            class="px-3 sm:px-4 py-2 border border-[#19140035] dark:border-[#3E3E3A] rounded hover:border-[#1915014a] dark:hover:border-[#62605b] whitespace-nowrap text-sm sm:text-base w-full sm:w-auto text-center"
        >
            Назад к списку
        </a>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-300 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 p-4 bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-300 rounded-lg">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Основная информация</h2>
            <div class="space-y-2 text-sm">
                <p><span class="font-medium">ID:</span> {{ $character->id }}</p>
                <p><span class="font-medium">Имя:</span> {{ $character->name }}</p>
                <p><span class="font-medium">Владелец:</span> 
                    <a href="{{ route('admin.users.show', $character->user) }}" class="text-[#f53003] dark:text-[#FF4433] hover:underline">
                        {{ $character->user->username }}
                    </a>
                </p>
                <p><span class="font-medium">Уровень:</span> {{ $character->level }}</p>
                <p><span class="font-medium">Опыт:</span> {{ number_format($character->experience, 0, ',', ' ') }}</p>
                <p><span class="font-medium">Доступно очков:</span> {{ $character->available_points }}</p>
                <p><span class="font-medium">Дата создания:</span> {{ $character->created_at->format('d.m.Y H:i') }}</p>
                <p><span class="font-medium">Последнее обновление:</span> {{ $character->updated_at->format('d.m.Y H:i') }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Характеристики</h2>
            <div class="space-y-2 text-sm">
                <p><span class="font-medium">Сила:</span> {{ $character->strength }}</p>
                <p><span class="font-medium">Ловкость:</span> {{ $character->agility }}</p>
                <p><span class="font-medium">Интеллект:</span> {{ $character->intelligence }}</p>
                <p><span class="font-medium">Сумма:</span> {{ $character->getTotalAttributes() }} / 15</p>
                <p><span class="font-medium">Потрачено очков:</span> {{ $character->total_attributes_spent }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Состояние</h2>
            <div class="space-y-2 text-sm">
                <p><span class="font-medium">Здоровье:</span> {{ $character->health_current }} / {{ $character->health_max }}</p>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 mb-2">
                    <div class="bg-red-600 h-2 rounded-full" style="width: {{ $character->getHealthPercentage() }}%"></div>
                </div>
                <p><span class="font-medium">Мана:</span> {{ $character->mana_current }} / {{ $character->mana_max }}</p>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $character->getManaPercentage() }}%"></div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Статус</h2>
            <div class="space-y-2 text-sm">
                <p>
                    <span class="font-medium">Активен:</span> 
                    @if($character->is_active)
                        <span class="text-green-600 dark:text-green-400">Да</span>
                    @else
                        <span class="text-gray-500">Нет</span>
                    @endif
                </p>
                <p>
                    <span class="font-medium">Преступник:</span> 
                    @if($character->is_criminal)
                        <span class="text-red-600 dark:text-red-400">Да</span>
                        @if($character->criminal_until)
                            <span class="text-xs text-gray-500">до {{ $character->criminal_until->format('d.m.Y H:i') }}</span>
                        @endif
                    @else
                        <span class="text-gray-500">Нет</span>
                    @endif
                </p>
                @if($character->location)
                    <p><span class="font-medium">Локация:</span> {{ $character->location->name }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $character->location->type }}
                        @if($character->location->is_safe_zone)
                            <span class="text-green-600 dark:text-green-400">(Безопасная зона)</span>
                        @endif
                    </p>
                    @if($character->location->coordinate_x !== null && $character->location->coordinate_y !== null)
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Координаты: ({{ $character->location->coordinate_x }}, {{ $character->location->coordinate_y }})
                        </p>
                    @endif
                @else
                    <p><span class="font-medium">Локация:</span> <span class="text-gray-500">Не указана</span></p>
                @endif
            </div>
        </div>
    </div>

    <!-- Перемещение персонажа -->
    <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4">Перемещение персонажа</h2>
        <form method="POST" action="{{ route('admin.characters.move', $character) }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label for="location_id" class="block text-sm font-medium mb-1">Локация *</label>
                    <select
                        id="location_id"
                        name="location_id"
                        required
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                        <option value="">Выберите локацию</option>
                        @foreach($allLocations as $location)
                            <option value="{{ $location->id }}" {{ $character->location_id === $location->id ? 'selected' : '' }}>
                                {{ $location->name }}
                                ({{ $location->type }})
                                @if($location->is_safe_zone)
                                    - Безопасная зона
                                @endif
                                @if($location->coordinate_x !== null && $location->coordinate_y !== null)
                                    - ({{ $location->coordinate_x }}, {{ $location->coordinate_y }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('location_id')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex items-end">
                    <button
                        type="submit"
                        class="w-full px-4 py-2 bg-blue-600 dark:bg-blue-700 text-white rounded hover:bg-blue-700 dark:hover:bg-blue-600"
                    >
                        Переместить
                    </button>
                </div>
            </div>
        </form>
    </div>

    @if($character->description)
        <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Описание</h2>
            <p>{{ $character->description }}</p>
        </div>
    @endif

    <!-- Восстановление здоровья и маны -->
    <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4">Восстановление</h2>
        <form method="POST" action="{{ route('admin.characters.restore', $character) }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="health_amount" class="block text-sm font-medium mb-1">Здоровье</label>
                    <input
                        type="number"
                        id="health_amount"
                        name="health_amount"
                        min="0"
                        max="{{ $character->health_max }}"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                        placeholder="Количество"
                    >
                </div>
                <div>
                    <label for="mana_amount" class="block text-sm font-medium mb-1">Мана</label>
                    <input
                        type="number"
                        id="mana_amount"
                        name="mana_amount"
                        min="0"
                        max="{{ $character->mana_max }}"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                        placeholder="Количество"
                    >
                </div>
                <div class="flex items-end">
                    <div class="w-full">
                        <label class="flex items-center mb-2">
                            <input
                                type="checkbox"
                                name="full_restore"
                                value="1"
                                class="mr-2"
                            >
                            <span class="text-sm">Полное восстановление</span>
                        </label>
                        <button
                            type="submit"
                            class="w-full px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
                        >
                            Восстановить
                        </button>
                    </div>
                </div>
            </div>
            @error('health_amount')
                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
            @error('mana_amount')
                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </form>
    </div>

    <!-- Добавление предмета в инвентарь -->
    <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4">Добавить предмет в инвентарь</h2>
        <form method="POST" action="{{ route('admin.characters.add-item', $character) }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label for="item_id" class="block text-sm font-medium mb-1">Предмет *</label>
                    <select
                        id="item_id"
                        name="item_id"
                        required
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                        <option value="">Выберите предмет</option>
                        @foreach($allItems as $item)
                            <option value="{{ $item->id }}">
                                {{ $item->name }} ({{ $item->type }}{{ $item->subtype ? ' - ' . $item->subtype : '' }})
                            </option>
                        @endforeach
                    </select>
                    @error('item_id')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="quantity" class="block text-sm font-medium mb-1">Количество</label>
                    <input
                        type="number"
                        id="quantity"
                        name="quantity"
                        value="1"
                        min="1"
                        max="9999"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                    @error('quantity')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <button
                type="submit"
                class="px-4 py-2 bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded hover:bg-black dark:hover:bg-white"
            >
                Добавить предмет
            </button>
        </form>
    </div>

    <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4">Навыки</h2>
        
        <!-- Форма добавления/изменения навыка -->
        <div class="mb-6 p-4 bg-gray-50 dark:bg-[#0a0a0a] rounded-lg">
            <h3 class="text-lg font-medium mb-3">Добавить/Изменить навык</h3>
            <form method="POST" action="{{ route('admin.characters.update-skill', $character) }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div class="sm:col-span-2">
                        <label for="skill_id" class="block text-sm font-medium mb-1">Навык *</label>
                        <select
                            id="skill_id"
                            name="skill_id"
                            required
                            class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                        >
                            <option value="">Выберите навык</option>
                            @foreach($allSkills as $skill)
                                <option value="{{ $skill->id }}">
                                    {{ $skill->name }} ({{ $skill->category }})
                                </option>
                            @endforeach
                        </select>
                        @error('skill_id')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="skill_level" class="block text-sm font-medium mb-1">Уровень</label>
                        <input
                            type="number"
                            id="skill_level"
                            name="level"
                            min="1"
                            max="100"
                            class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                            placeholder="1"
                        >
                        @error('level')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="skill_experience" class="block text-sm font-medium mb-1">Опыт</label>
                        <input
                            type="number"
                            id="skill_experience"
                            name="experience"
                            min="0"
                            class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                            placeholder="0"
                        >
                        @error('experience')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <label class="flex items-center">
                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            checked
                            class="mr-2"
                        >
                        <span class="text-sm">Активен</span>
                    </label>
                    <button
                        type="submit"
                        class="px-4 py-2 bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded hover:bg-black dark:hover:bg-white"
                    >
                        Сохранить навык
                    </button>
                </div>
            </form>
        </div>
        @if($character->characterSkills->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($character->characterSkills as $characterSkill)
                    <div class="border border-[#e3e3e0] dark:border-[#3E3E3A] rounded p-4">
                        <h3 class="font-medium">{{ $characterSkill->skill->name }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $characterSkill->skill->description }}</p>
                        <p class="text-xs text-gray-500 mt-2">
                            Уровень: {{ $characterSkill->level }} / {{ $characterSkill->skill->max_level }} | 
                            Опыт: {{ $characterSkill->experience }} | 
                            Категория: {{ $characterSkill->skill->category }}
                        </p>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-600 dark:text-gray-400">У персонажа нет навыков</p>
        @endif
    </div>

    <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4">Экипировка</h2>
        @if($character->equipment->count() > 0)
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                @foreach($character->equipment as $equipment)
                    @php
                        $item = $equipment->itemInstance->item;
                    @endphp
                    <div class="border border-[#e3e3e0] dark:border-[#3E3E3A] rounded p-3">
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ $equipment->slot }}</div>
                        <div class="font-medium text-sm">{{ $item->name }}</div>
                        @if($item->subtype)
                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $item->subtype }}</div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-600 dark:text-gray-400">Персонаж не экипирован</p>
        @endif
    </div>

    <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6">
        <h2 class="text-xl font-semibold mb-4">Инвентарь</h2>
        @if($character->inventoryItems->count() > 0)
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                @foreach($character->inventoryItems as $itemInstance)
                    @php
                        $item = $itemInstance->item;
                    @endphp
                    <div class="border border-[#e3e3e0] dark:border-[#3E3E3A] rounded p-3">
                        <div class="font-medium text-sm">{{ $item->name }}</div>
                        @if($item->subtype)
                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $item->subtype }}</div>
                        @endif
                        @if($itemInstance->quantity > 1)
                            <div class="text-xs text-gray-500 dark:text-gray-400">x{{ $itemInstance->quantity }}</div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-600 dark:text-gray-400">Инвентарь пуст</p>
        @endif
    </div>
</div>
@endsection

