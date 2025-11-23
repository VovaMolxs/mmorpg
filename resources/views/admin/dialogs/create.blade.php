@extends('layouts.app')

@section('title', 'Создать диалог')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-semibold mb-2">Создать диалог</h1>
        <a
            href="{{ route('admin.dialogs.index') }}"
            class="text-[#f53003] dark:text-[#FF4433] hover:underline"
        >
            ← Назад к списку
        </a>
    </div>

    <form method="POST" action="{{ route('admin.dialogs.store') }}" class="space-y-6">
        @csrf

        <!-- Основная информация -->
        <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-4 sm:p-6 space-y-4">
            <h2 class="text-xl font-semibold mb-4">Основная информация</h2>

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
                            {{ $npc->name }}
                        </option>
                    @endforeach
                </select>
                @error('npc_id')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="text" class="block text-sm font-medium mb-1">Текст диалога *</label>
                <textarea
                    id="text"
                    name="text"
                    rows="4"
                    required
                    class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                >{{ old('text') }}</textarea>
                @error('text')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="parent_dialog_id" class="block text-sm font-medium mb-1">Родительский диалог</label>
                    <select
                        id="parent_dialog_id"
                        name="parent_dialog_id"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                        <option value="">Нет родительского диалога</option>
                        @foreach($dialogs as $parentDialog)
                            <option value="{{ $parentDialog->id }}" {{ old('parent_dialog_id') == $parentDialog->id ? 'selected' : '' }}>
                                {{ Str::limit($parentDialog->text, 50) }}
                            </option>
                        @endforeach
                    </select>
                </div>

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
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="required_quest_id" class="block text-sm font-medium mb-1">Требуемый квест</label>
                    <select
                        id="required_quest_id"
                        name="required_quest_id"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                        <option value="">Нет требования</option>
                        @foreach($quests as $quest)
                            <option value="{{ $quest->id }}" {{ old('required_quest_id') == $quest->id ? 'selected' : '' }}>
                                {{ $quest->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="required_quest_status" class="block text-sm font-medium mb-1">Статус требуемого квеста</label>
                    <select
                        id="required_quest_status"
                        name="required_quest_status"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                        <option value="">Любой статус</option>
                        @foreach($questStatuses as $statusKey => $statusLabel)
                            <option value="{{ $statusKey }}" {{ old('required_quest_status') === $statusKey ? 'selected' : '' }}>
                                {{ $statusLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex items-center">
                <input
                    type="checkbox"
                    id="is_initial"
                    name="is_initial"
                    value="1"
                    {{ old('is_initial') ? 'checked' : '' }}
                    class="mr-2"
                >
                <label for="is_initial" class="text-sm">Начальный диалог</label>
            </div>
        </div>

        <!-- Ответы -->
        <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-4 sm:p-6 space-y-4">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold">Ответы</h2>
                <button
                    type="button"
                    onclick="addAnswerRow()"
                    class="px-4 py-2 bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded hover:bg-black dark:hover:bg-white text-sm"
                >
                    Добавить ответ
                </button>
            </div>
            <div id="answers-container">
                <!-- Ответы будут добавлены через JavaScript -->
            </div>
        </div>

        <div class="flex justify-end gap-4">
            <a
                href="{{ route('admin.dialogs.index') }}"
                class="px-6 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded hover:bg-gray-50 dark:hover:bg-[#0a0a0a]"
            >
                Отмена
            </a>
            <button
                type="submit"
                class="px-6 py-2 bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded hover:bg-black dark:hover:bg-white"
            >
                Создать диалог
            </button>
        </div>
    </form>
</div>

<script>
let answerIndex = 0;

function addAnswerRow() {
    const container = document.getElementById('answers-container');
    const row = document.createElement('div');
    row.className = 'border border-[#e3e3e0] dark:border-[#3E3E3A] rounded p-4 space-y-4';
    row.id = `answer-row-${answerIndex}`;
    row.innerHTML = `
        <div class="flex justify-between items-center">
            <h3 class="font-medium">Ответ #${answerIndex + 1}</h3>
            <button type="button" onclick="removeAnswerRow(${answerIndex})" class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-sm">Удалить</button>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Текст ответа *</label>
            <input type="text" name="answers[${answerIndex}][text]" required class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a]">
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Следующий диалог</label>
                <select name="answers[${answerIndex}][next_dialog_id]" class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a]">
                    <option value="">Нет следующего диалога</option>
                    @foreach($dialogs as $nextDialog)
                        <option value="{{ $nextDialog->id }}">{{ Str::limit($nextDialog->text, 50) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Запустить квест</label>
                <select name="answers[${answerIndex}][quest_trigger_id]" class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a]">
                    <option value="">Не запускать квест</option>
                    @foreach($quests as $quest)
                        <option value="{{ $quest->id }}">{{ $quest->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Требуемый предмет</label>
                <select name="answers[${answerIndex}][item_required_id]" class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a]">
                    <option value="">Нет требования</option>
                    @foreach($items as $item)
                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-sm font-medium mb-1">Требуемый навык</label>
                    <select name="answers[${answerIndex}][skill_required]" class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a]">
                        <option value="">Нет требования</option>
                        @foreach($skills as $skill)
                            <option value="{{ $skill->id }}">{{ $skill->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Уровень навыка</label>
                    <input type="number" name="answers[${answerIndex}][skill_level_required]" min="1" max="100" class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a]">
                </div>
            </div>
        </div>
    `;
    container.appendChild(row);
    answerIndex++;
}

function removeAnswerRow(index) {
    const row = document.getElementById(`answer-row-${index}`);
    if (row) {
        row.remove();
    }
}
</script>
@endsection

