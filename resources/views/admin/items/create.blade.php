@extends('layouts.app')

@section('title', 'Создать предмет')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-semibold mb-2">Создать предмет</h1>
        <a
            href="{{ route('admin.items.index') }}"
            class="text-[#f53003] dark:text-[#FF4433] hover:underline"
        >
            ← Назад к списку
        </a>
    </div>

    <form method="POST" action="{{ route('admin.items.store') }}" class="space-y-6">
        @csrf

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
                        @foreach($types as $typeKey => $typeData)
                            <option value="{{ $typeKey }}" {{ old('type') === $typeKey ? 'selected' : '' }}>
                                {{ $typeData['label'] }}
                            </option>
                        @endforeach
                    </select>
                    @error('type')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div id="subtype-container">
                    <label for="subtype" class="block text-sm font-medium mb-1">Подтип</label>
                    <select
                        id="subtype"
                        name="subtype"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                        <option value="">Выберите подтип</option>
                    </select>
                    @error('subtype')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="rarity" class="block text-sm font-medium mb-1">Редкость *</label>
                    <select
                        id="rarity"
                        name="rarity"
                        required
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                        @foreach($rarities as $rarityKey => $rarityLabel)
                            <option value="{{ $rarityKey }}" {{ old('rarity', 'common') === $rarityKey ? 'selected' : '' }}>
                                {{ $rarityLabel }}
                            </option>
                        @endforeach
                    </select>
                    @error('rarity')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="level_required" class="block text-sm font-medium mb-1">Требуемый уровень</label>
                    <input
                        type="number"
                        id="level_required"
                        name="level_required"
                        value="{{ old('level_required', 1) }}"
                        min="1"
                        max="100"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                    @error('level_required')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="flex items-center">
                        <input
                            type="checkbox"
                            name="stackable"
                            value="1"
                            {{ old('stackable') ? 'checked' : '' }}
                            class="mr-2"
                        >
                        <span class="text-sm">Стакуемый</span>
                    </label>
                </div>
                <div>
                    <label for="max_stack" class="block text-sm font-medium mb-1">Макс. стак</label>
                    <input
                        type="number"
                        id="max_stack"
                        name="max_stack"
                        value="{{ old('max_stack', 1) }}"
                        min="1"
                        max="9999"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                </div>
                <div>
                    <label for="weight" class="block text-sm font-medium mb-1">Вес</label>
                    <input
                        type="number"
                        id="weight"
                        name="weight"
                        value="{{ old('weight', 0) }}"
                        step="0.01"
                        min="0"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                </div>
            </div>

            <div>
                <label for="value" class="block text-sm font-medium mb-1">Стоимость</label>
                <input
                    type="number"
                    id="value"
                    name="value"
                    value="{{ old('value', 0) }}"
                    min="0"
                    class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                >
                @error('value')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Требования -->
        <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-4 sm:p-6 space-y-4">
            <h2 class="text-xl font-semibold mb-4">Требования</h2>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div>
                    <label for="requirements_level" class="block text-sm font-medium mb-1">Уровень</label>
                    <input
                        type="number"
                        id="requirements_level"
                        name="requirements[level]"
                        value="{{ old('requirements.level') }}"
                        min="1"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                </div>
                <div>
                    <label for="requirements_strength" class="block text-sm font-medium mb-1">Сила</label>
                    <input
                        type="number"
                        id="requirements_strength"
                        name="requirements[attributes][strength]"
                        value="{{ old('requirements.attributes.strength') }}"
                        min="1"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                </div>
                <div>
                    <label for="requirements_agility" class="block text-sm font-medium mb-1">Ловкость</label>
                    <input
                        type="number"
                        id="requirements_agility"
                        name="requirements[attributes][agility]"
                        value="{{ old('requirements.attributes.agility') }}"
                        min="1"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                </div>
                <div>
                    <label for="requirements_intelligence" class="block text-sm font-medium mb-1">Интеллект</label>
                    <input
                        type="number"
                        id="requirements_intelligence"
                        name="requirements[attributes][intelligence]"
                        value="{{ old('requirements.attributes.intelligence') }}"
                        min="1"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                </div>
            </div>
        </div>

        <!-- Специфичные данные по типу -->
        <div id="type-specific-data" class="space-y-4">
            <!-- Данные будут добавлены через JavaScript -->
        </div>

        <div class="flex justify-end gap-4">
            <a
                href="{{ route('admin.items.index') }}"
                class="px-6 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded hover:bg-gray-50 dark:hover:bg-[#0a0a0a]"
            >
                Отмена
            </a>
            <button
                type="submit"
                class="px-6 py-2 bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded hover:bg-black dark:hover:bg-white"
            >
                Создать предмет
            </button>
        </div>
    </form>
</div>

<script>
const types = @json($types);
const oldInput = @json(old());

// Вспомогательная функция для безопасного доступа к вложенным значениям
function getOldValue(path, defaultValue = '') {
    const keys = path.split('.');
    let value = oldInput;
    for (const key of keys) {
        if (value && typeof value === 'object' && key in value) {
            value = value[key];
        } else {
            return defaultValue;
        }
    }
    return value !== null && value !== undefined ? value : defaultValue;
}

// Обновление подтипов при изменении типа
document.getElementById('type').addEventListener('change', function() {
    const type = this.value;
    const subtypeSelect = document.getElementById('subtype');
    subtypeSelect.innerHTML = '<option value="">Выберите подтип</option>';

    if (type && types[type] && types[type].subtypes) {
        Object.entries(types[type].subtypes).forEach(([key, label]) => {
            const option = document.createElement('option');
            option.value = key;
            option.textContent = label;
            if (getOldValue('subtype') === key) {
                option.selected = true;
            }
            subtypeSelect.appendChild(option);
        });
    }

    updateTypeSpecificFields(type);
});

// Обновление специфичных полей для типа
function updateTypeSpecificFields(type) {
    const container = document.getElementById('type-specific-data');
    container.innerHTML = '';

    if (!type) return;

    const section = document.createElement('div');
    section.className = 'bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-4 sm:p-6 space-y-4';
    
    let html = `<h2 class="text-xl font-semibold mb-4">Данные для ${types[type]?.label || type}</h2>`;

    switch(type) {
        case 'weapon':
            html += `
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Минимальный урон *</label>
                        <input type="number" name="weapon_data[damage_min]" value="${getOldValue('weapon_data.damage_min')}" min="1" required class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Максимальный урон *</label>
                        <input type="number" name="weapon_data[damage_max]" value="${getOldValue('weapon_data.damage_max')}" min="1" required class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Скорость атаки</label>
                        <input type="number" name="weapon_data[attack_speed]" value="${getOldValue('weapon_data.attack_speed', '1.0')}" step="0.1" min="0.1" class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Дальность</label>
                        <input type="number" name="weapon_data[range]" value="${getOldValue('weapon_data.range', '1')}" min="1" class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Макс. прочность</label>
                        <input type="number" name="weapon_data[durability_max]" value="${getOldValue('weapon_data.durability_max', '100')}" min="1" class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]">
                    </div>
                </div>
            `;
            break;
        case 'armor':
            html += `
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Защита *</label>
                        <input type="number" name="armor_data[defense]" value="${getOldValue('armor_data.defense')}" min="0" required class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Макс. прочность</label>
                        <input type="number" name="armor_data[durability_max]" value="${getOldValue('armor_data.durability_max', '100')}" min="1" class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]">
                    </div>
                </div>
            `;
            break;
        case 'jewelry':
            html += `
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Бонус к силе</label>
                        <input type="number" name="jewelry_data[attributes_bonus][strength]" value="${getOldValue('jewelry_data.attributes_bonus.strength')}" min="1" class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Бонус к ловкости</label>
                        <input type="number" name="jewelry_data[attributes_bonus][agility]" value="${getOldValue('jewelry_data.attributes_bonus.agility')}" min="1" class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Бонус к интеллекту</label>
                        <input type="number" name="jewelry_data[attributes_bonus][intelligence]" value="${getOldValue('jewelry_data.attributes_bonus.intelligence')}" min="1" class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]">
                    </div>
                </div>
            `;
            break;
        case 'potion':
            html += `
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Тип эффекта *</label>
                        <select name="potion_data[effect_type]" required class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]">
                            <option value="">Выберите тип</option>
                            <option value="health" ${getOldValue('potion_data.effect_type') === 'health' ? 'selected' : ''}>Здоровье</option>
                            <option value="mana" ${getOldValue('potion_data.effect_type') === 'mana' ? 'selected' : ''}>Мана</option>
                            <option value="buff" ${getOldValue('potion_data.effect_type') === 'buff' ? 'selected' : ''}>Усиление</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Сила эффекта *</label>
                        <input type="number" name="potion_data[effect_power]" value="${getOldValue('potion_data.effect_power')}" min="1" required class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Длительность (сек)</label>
                        <input type="number" name="potion_data[duration]" value="${getOldValue('potion_data.duration', '0')}" min="0" class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Перезарядка (сек)</label>
                        <input type="number" name="potion_data[cooldown]" value="${getOldValue('potion_data.cooldown', '0')}" min="0" class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]">
                    </div>
                </div>
            `;
            break;
        case 'rune':
            html += `
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">ID заклинания</label>
                        <input type="number" name="rune_data[spell_id]" value="${getOldValue('rune_data.spell_id')}" class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Заряды</label>
                        <input type="number" name="rune_data[charges]" value="${getOldValue('rune_data.charges')}" min="1" class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Время перезарядки (мин)</label>
                        <input type="number" name="rune_data[recharge_time]" value="${getOldValue('rune_data.recharge_time')}" min="0" class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Стоимость маны</label>
                        <input type="number" name="rune_data[mana_cost_per_use]" value="${getOldValue('rune_data.mana_cost_per_use')}" min="0" class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]">
                    </div>
                </div>
            `;
            break;
        case 'scroll':
            html += `
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">ID заклинания</label>
                        <input type="number" name="scroll_data[spell_id]" value="${oldInput['scroll_data.spell_id'] || ''}" class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]">
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" name="scroll_data[is_consumable]" value="1" ${getOldValue('scroll_data.is_consumable') || getOldValue('scroll_data.is_consumable') === '1' ? 'checked' : 'checked'} class="mr-2">
                            <span class="text-sm">Одноразовый</span>
                        </label>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Требуемый навык</label>
                        <input type="text" name="scroll_data[skill_required]" value="${getOldValue('scroll_data.skill_required')}" class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Уровень навыка</label>
                        <input type="number" name="scroll_data[skill_level_required]" value="${getOldValue('scroll_data.skill_level_required')}" min="1" class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]">
                    </div>
                </div>
            `;
            break;
        case 'resource':
            html += `
                <div>
                    <label class="block text-sm font-medium mb-1">Качество (1-10)</label>
                    <input type="number" name="resource_data[quality]" value="${getOldValue('resource_data.quality', '1')}" min="1" max="10" class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]">
                </div>
            `;
            break;
    }

    section.innerHTML = html;
    container.appendChild(section);
}

// Инициализация при загрузке страницы
if (getOldValue('type')) {
    document.getElementById('type').dispatchEvent(new Event('change'));
}
</script>
@endsection

