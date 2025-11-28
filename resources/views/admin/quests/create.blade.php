@extends('layouts.app')

@section('title', 'Создать квест')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-semibold mb-2">Создать квест</h1>
        <a
            href="{{ route('admin.quests.index') }}"
            class="text-[#f53003] dark:text-[#FF4433] hover:underline"
        >
            ← Назад к списку
        </a>
    </div>

    <form method="POST" action="{{ route('admin.quests.store') }}" class="space-y-6">
        @csrf

        <!-- Основная информация -->
        <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-4 sm:p-6 space-y-4">
            <h2 class="text-xl font-semibold mb-4">Основная информация</h2>

            <div>
                <label for="name" class="block text-sm font-medium mb-1">Название квеста *</label>
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
                <label for="description" class="block text-sm font-medium mb-1">Описание *</label>
                <textarea
                    id="description"
                    name="description"
                    rows="4"
                    required
                    class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                >{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="min_level" class="block text-sm font-medium mb-1">Минимальный уровень</label>
                    <input
                        type="number"
                        id="min_level"
                        name="min_level"
                        value="{{ old('min_level') }}"
                        min="1"
                        max="100"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                </div>
                <div>
                    <label for="max_level" class="block text-sm font-medium mb-1">Максимальный уровень</label>
                    <input
                        type="number"
                        id="max_level"
                        name="max_level"
                        value="{{ old('max_level') }}"
                        min="1"
                        max="100"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="quest_giver_npc_id" class="block text-sm font-medium mb-1">NPC, выдающий квест</label>
                    <select
                        id="quest_giver_npc_id"
                        name="quest_giver_npc_id"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                        <option value="">Нет NPC</option>
                        @foreach($npcs as $npc)
                            <option value="{{ $npc->id }}" {{ old('quest_giver_npc_id') == $npc->id ? 'selected' : '' }}>
                                {{ $npc->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="turn_in_npc_id" class="block text-sm font-medium mb-1">NPC, которому сдается квест</label>
                    <select
                        id="turn_in_npc_id"
                        name="turn_in_npc_id"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                        <option value="">Нет NPC</option>
                        @foreach($npcs as $npc)
                            <option value="{{ $npc->id }}" {{ old('turn_in_npc_id') == $npc->id ? 'selected' : '' }}>
                                {{ $npc->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="previous_quest_id" class="block text-sm font-medium mb-1">Предыдущий квест</label>
                    <select
                        id="previous_quest_id"
                        name="previous_quest_id"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                        <option value="">Нет требования</option>
                        @foreach($quests as $prevQuest)
                            <option value="{{ $prevQuest->id }}" {{ old('previous_quest_id') == $prevQuest->id ? 'selected' : '' }}>
                                {{ $prevQuest->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="reputation_required" class="block text-sm font-medium mb-1">Требуемая репутация</label>
                    <input
                        type="number"
                        id="reputation_required"
                        name="reputation_required"
                        value="{{ old('reputation_required') }}"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                </div>
            </div>
        </div>

        <!-- Цели квеста -->
        <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-4 sm:p-6 space-y-4">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold">Цели квеста *</h2>
                <button
                    type="button"
                    onclick="addObjectiveRow()"
                    class="px-4 py-2 bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded hover:bg-black dark:hover:bg-white text-sm"
                >
                    Добавить цель
                </button>
            </div>
            <div id="objectives-container">
                <!-- Цели будут добавлены через JavaScript -->
            </div>
        </div>

        <!-- Награды -->
        <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-4 sm:p-6 space-y-4">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold">Награды</h2>
                <button
                    type="button"
                    onclick="addRewardRow()"
                    class="px-4 py-2 bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded hover:bg-black dark:hover:bg-white text-sm"
                >
                    Добавить награду
                </button>
            </div>
            <div id="rewards-container">
                <!-- Награды будут добавлены через JavaScript -->
            </div>
        </div>

        <div class="flex justify-end gap-4">
            <a
                href="{{ route('admin.quests.index') }}"
                class="px-6 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded hover:bg-gray-50 dark:hover:bg-[#0a0a0a]"
            >
                Отмена
            </a>
            <button
                type="submit"
                class="px-6 py-2 bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded hover:bg-black dark:hover:bg-white"
            >
                Создать квест
            </button>
        </div>
    </form>
</div>

<script>
let objectiveIndex = 0;
let rewardIndex = 0;

function addObjectiveRow() {
    const container = document.getElementById('objectives-container');
    const row = document.createElement('div');
    row.className = 'border border-[#e3e3e0] dark:border-[#3E3E3A] rounded p-4 space-y-4';
    row.id = `objective-row-${objectiveIndex}`;
    row.innerHTML = `
        <div class="flex justify-between items-center">
            <h3 class="font-medium">Цель #${objectiveIndex + 1}</h3>
            <button type="button" onclick="removeObjectiveRow(${objectiveIndex})" class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-sm">Удалить</button>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Тип цели *</label>
                <select name="objectives[${objectiveIndex}][type]" required class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a]">
                    @foreach($objectiveTypes as $typeKey => $typeLabel)
                        <option value="{{ $typeKey }}">{{ $typeLabel }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Требуемое количество *</label>
                <input type="number" name="objectives[${objectiveIndex}][required_count]" value="1" min="1" required class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a]">
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">ID цели</label>
                <input type="number" name="objectives[${objectiveIndex}][target_id]" class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a]">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Название цели</label>
                <input type="text" name="objectives[${objectiveIndex}][target_name]" class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a]">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Описание</label>
            <textarea name="objectives[${objectiveIndex}][description]" rows="2" class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a]"></textarea>
        </div>
    `;
    container.appendChild(row);
    objectiveIndex++;
}

function removeObjectiveRow(index) {
    const row = document.getElementById(`objective-row-${index}`);
    if (row) {
        row.remove();
    }
}

function addRewardRow() {
    const container = document.getElementById('rewards-container');
    const row = document.createElement('div');
    row.className = 'border border-[#e3e3e0] dark:border-[#3E3E3A] rounded p-4 space-y-4';
    row.id = `reward-row-${rewardIndex}`;
    row.innerHTML = `
        <div class="flex justify-between items-center">
            <h3 class="font-medium">Награда #${rewardIndex + 1}</h3>
            <button type="button" onclick="removeRewardRow(${rewardIndex})" class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-sm">Удалить</button>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Тип награды *</label>
                <select name="rewards[${rewardIndex}][type]" required class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a]" onchange="updateRewardFields(${rewardIndex})">
                    @foreach($rewardTypes as $typeKey => $typeLabel)
                        <option value="{{ $typeKey }}">{{ $typeLabel }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Количество *</label>
                <input type="number" name="rewards[${rewardIndex}][quantity]" value="1" min="1" required class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a]">
            </div>
        </div>
        <div id="reward-${rewardIndex}-item" style="display: none;">
            <label class="block text-sm font-medium mb-1">Предмет</label>
            <select name="rewards[${rewardIndex}][reward_id]" class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a]">
                <option value="">Выберите предмет</option>
                @foreach($items as $item)
                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                @endforeach
            </select>
        </div>
        <div id="reward-${rewardIndex}-skill" style="display: none;">
            <label class="block text-sm font-medium mb-1">Навык</label>
            <select name="rewards[${rewardIndex}][reward_id]" class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a]">
                <option value="">Выберите навык</option>
                @foreach($skills as $skill)
                    <option value="{{ $skill->id }}">{{ $skill->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-center">
            <input type="checkbox" id="rewards[${rewardIndex}][is_choice]" name="rewards[${rewardIndex}][is_choice]" value="1" class="mr-2">
            <label for="rewards[${rewardIndex}][is_choice]" class="text-sm">Награда на выбор</label>
        </div>
    `;
    container.appendChild(row);
    rewardIndex++;
}

function removeRewardRow(index) {
    const row = document.getElementById(`reward-row-${index}`);
    if (row) {
        row.remove();
    }
}

function updateRewardFields(index) {
    const typeSelect = document.querySelector(`#reward-row-${index} select[name="rewards[${index}][type]"]`);
    const itemDiv = document.getElementById(`reward-${index}-item`);
    const skillDiv = document.getElementById(`reward-${index}-skill`);
    const type = typeSelect.value;

    itemDiv.style.display = type === 'item' ? 'block' : 'none';
    skillDiv.style.display = type === 'skill' ? 'block' : 'none';
}
</script>
@endsection



