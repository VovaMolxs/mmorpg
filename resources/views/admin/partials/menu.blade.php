@php
    $currentRoute = request()->route()->getName();
@endphp

<div class="flex flex-wrap gap-2">
    <a
        href="{{ route('admin.characters.online') }}"
        class="px-4 py-2 bg-green-600 dark:bg-green-700 text-white rounded hover:bg-green-700 dark:hover:bg-green-600 whitespace-nowrap"
    >
        Онлайн персонажи
    </a>
    <a
        href="{{ route('admin.users.index') }}"
        class="px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded hover:bg-gray-50 dark:hover:bg-[#0a0a0a] whitespace-nowrap {{ str_starts_with($currentRoute, 'admin.users') ? 'bg-gray-100 dark:bg-[#0a0a0a]' : '' }}"
    >
        Пользователи
    </a>
    <a
        href="{{ route('admin.characters.index') }}"
        class="px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded hover:bg-gray-50 dark:hover:bg-[#0a0a0a] whitespace-nowrap {{ str_starts_with($currentRoute, 'admin.characters') && !str_starts_with($currentRoute, 'admin.characters.online') ? 'bg-gray-100 dark:bg-[#0a0a0a]' : '' }}"
    >
        Персонажи
    </a>
    <a
        href="{{ route('admin.bank-storages.index') }}"
        class="px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded hover:bg-gray-50 dark:hover:bg-[#0a0a0a] whitespace-nowrap {{ str_starts_with($currentRoute, 'admin.bank-storages') ? 'bg-gray-100 dark:bg-[#0a0a0a]' : '' }}"
    >
        Склады персонажей
    </a>
    <a
        href="{{ route('admin.bankers.index') }}"
        class="px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded hover:bg-gray-50 dark:hover:bg-[#0a0a0a] whitespace-nowrap {{ str_starts_with($currentRoute, 'admin.bankers') ? 'bg-gray-100 dark:bg-[#0a0a0a]' : '' }}"
    >
        Банкиры
    </a>
    <a
        href="{{ route('admin.items.index') }}"
        class="px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded hover:bg-gray-50 dark:hover:bg-[#0a0a0a] whitespace-nowrap {{ str_starts_with($currentRoute, 'admin.items') ? 'bg-gray-100 dark:bg-[#0a0a0a]' : '' }}"
    >
        Предметы
    </a>
    <a
        href="{{ route('admin.world-map.index') }}"
        class="px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded hover:bg-gray-50 dark:hover:bg-[#0a0a0a] whitespace-nowrap {{ str_starts_with($currentRoute, 'admin.world-map') ? 'bg-gray-100 dark:bg-[#0a0a0a]' : '' }}"
    >
        Карта мира
    </a>
    <a
        href="{{ route('admin.item-spawns.index') }}"
        class="px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded hover:bg-gray-50 dark:hover:bg-[#0a0a0a] whitespace-nowrap {{ str_starts_with($currentRoute, 'admin.item-spawns') ? 'bg-gray-100 dark:bg-[#0a0a0a]' : '' }}"
    >
        Спавн предметов
    </a>
    <a
        href="{{ route('admin.npcs.index') }}"
        class="px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded hover:bg-gray-50 dark:hover:bg-[#0a0a0a] whitespace-nowrap {{ str_starts_with($currentRoute, 'admin.npcs') && !str_starts_with($currentRoute, 'admin.npc-spawns') ? 'bg-gray-100 dark:bg-[#0a0a0a]' : '' }}"
    >
        NPC
    </a>
    <a
        href="{{ route('admin.npc-spawns.index') }}"
        class="px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded hover:bg-gray-50 dark:hover:bg-[#0a0a0a] whitespace-nowrap {{ str_starts_with($currentRoute, 'admin.npc-spawns') ? 'bg-gray-100 dark:bg-[#0a0a0a]' : '' }}"
    >
        Спавн NPC
    </a>
    <a
        href="{{ route('admin.dialogs.index') }}"
        class="px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded hover:bg-gray-50 dark:hover:bg-[#0a0a0a] whitespace-nowrap {{ str_starts_with($currentRoute, 'admin.dialogs') ? 'bg-gray-100 dark:bg-[#0a0a0a]' : '' }}"
    >
        Диалоги
    </a>
    <a
        href="{{ route('admin.quests.index') }}"
        class="px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded hover:bg-gray-50 dark:hover:bg-[#0a0a0a] whitespace-nowrap {{ str_starts_with($currentRoute, 'admin.quests') ? 'bg-gray-100 dark:bg-[#0a0a0a]' : '' }}"
    >
        Квесты
    </a>
    @isset($additionalButtons)
        @foreach($additionalButtons as $button)
            <a
                href="{{ $button['route'] }}"
                class="px-4 py-2 bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded hover:bg-black dark:hover:bg-white whitespace-nowrap"
            >
                {{ $button['label'] }}
            </a>
        @endforeach
    @endisset
</div>

