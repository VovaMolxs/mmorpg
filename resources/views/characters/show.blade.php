@extends('layouts.app')

@section('title', 'Персонаж: ' . $character->name)

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <h1 class="text-2xl sm:text-3xl font-semibold break-words">{{ $character->name }}</h1>
        <div class="flex flex-wrap gap-2 sm:gap-4 w-full sm:w-auto">
            <a
                href="{{ route('characters.skills', $character) }}"
                class="px-3 sm:px-4 py-2 bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded hover:bg-black dark:hover:bg-white font-medium whitespace-nowrap text-sm sm:text-base"
            >
                Навыки и характеристики
            </a>
            <a
                href="{{ route('characters.inventory', $character) }}"
                class="px-3 sm:px-4 py-2 bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded hover:bg-black dark:hover:bg-white font-medium whitespace-nowrap text-sm sm:text-base"
            >
                Инвентарь
            </a>
            <a
                href="{{ route('characters.index') }}"
                class="px-3 sm:px-4 py-2 border border-[#19140035] dark:border-[#3E3E3A] rounded hover:border-[#1915014a] dark:hover:border-[#62605b] whitespace-nowrap text-sm sm:text-base"
            >
                Назад к списку
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Характеристики</h2>
            <div class="space-y-2 text-sm">
                <p><span class="font-medium">Сила:</span> {{ $character->strength }}</p>
                <p><span class="font-medium">Ловкость:</span> {{ $character->agility }}</p>
                <p><span class="font-medium">Интеллект:</span> {{ $character->intelligence }}</p>
                <p><span class="font-medium">Сумма:</span> {{ $character->getTotalAttributes() }} / 15</p>
            </div>
        </div>

        <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Состояние</h2>
            <div class="space-y-2 text-sm">
                <p><span class="font-medium">Уровень:</span> {{ $character->level }}</p>
                <p><span class="font-medium">Опыт:</span> {{ $character->experience }}</p>
                <p><span class="font-medium">Доступно очков:</span> {{ $character->available_points }}</p>
                <p><span class="font-medium">Здоровье:</span> {{ $character->health_current }} / {{ $character->health_max }}</p>
                <p><span class="font-medium">Мана:</span> {{ $character->mana_current }} / {{ $character->mana_max }}</p>
            </div>
        </div>
    </div>

    @php
        $damage = $character->calculateDamage();
        $physicalDefense = $character->calculatePhysicalDefense();
        $magicDefense = $character->calculateMagicDefense();
        $accuracy = $character->calculateAccuracy();
        $magicAccuracy = $character->calculateMagicAccuracy();
        $criticalChance = $character->calculateCriticalChance();
        $criticalPower = $character->calculateCriticalPower();
        $dodge = $character->calculateDodge();
        $healthRegenerationRate = $character->calculateHealthRegenerationRate();
        $manaRegenerationRate = $character->calculateManaRegenerationRate();
    @endphp

    <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4">Боевые характеристики</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="border border-[#e3e3e0] dark:border-[#3E3E3A] rounded p-3">
                <div class="flex justify-between items-center mb-1">
                    <span class="text-sm font-medium">Урон</span>
                    <span class="font-bold">{{ $damage['min'] }}-{{ $damage['max'] }}</span>
                </div>
                <p class="text-xs text-gray-500">Сила + оружие + мастерство</p>
            </div>
            <div class="border border-[#e3e3e0] dark:border-[#3E3E3A] rounded p-3">
                <div class="flex justify-between items-center mb-1">
                    <span class="text-sm font-medium">Физ. защита</span>
                    <span class="font-bold">{{ $physicalDefense }}</span>
                </div>
                <p class="text-xs text-gray-500">Ловкость + броня + защита</p>
            </div>
            <div class="border border-[#e3e3e0] dark:border-[#3E3E3A] rounded p-3">
                <div class="flex justify-between items-center mb-1">
                    <span class="text-sm font-medium">Маг. защита</span>
                    <span class="font-bold">{{ $magicDefense }}</span>
                </div>
                <p class="text-xs text-gray-500">Интеллект + сопротивление</p>
            </div>
            <div class="border border-[#e3e3e0] dark:border-[#3E3E3A] rounded p-3">
                <div class="flex justify-between items-center mb-1">
                    <span class="text-sm font-medium">Точность</span>
                    <span class="font-bold">{{ number_format($accuracy, 1) }}%</span>
                </div>
                <p class="text-xs text-gray-500">Шанс попадания</p>
            </div>
            <div class="border border-[#e3e3e0] dark:border-[#3E3E3A] rounded p-3">
                <div class="flex justify-between items-center mb-1">
                    <span class="text-sm font-medium">Крит. шанс</span>
                    <span class="font-bold">{{ number_format($criticalChance, 1) }}%</span>
                </div>
                <p class="text-xs text-gray-500">Вероятность крита</p>
            </div>
            <div class="border border-[#e3e3e0] dark:border-[#3E3E3A] rounded p-3">
                <div class="flex justify-between items-center mb-1">
                    <span class="text-sm font-medium">Сила крита</span>
                    <span class="font-bold">{{ number_format($criticalPower, 2) }}x</span>
                </div>
                <p class="text-xs text-gray-500">Множитель урона</p>
            </div>
            <div class="border border-[#e3e3e0] dark:border-[#3E3E3A] rounded p-3">
                <div class="flex justify-between items-center mb-1">
                    <span class="text-sm font-medium">Уворот</span>
                    <span class="font-bold">{{ number_format($dodge, 1) }}%</span>
                </div>
                <p class="text-xs text-gray-500">Шанс уклонения</p>
            </div>
            <div class="border border-[#e3e3e0] dark:border-[#3E3E3A] rounded p-3">
                <div class="flex justify-between items-center mb-1">
                    <span class="text-sm font-medium">Точность магии</span>
                    <span class="font-bold">{{ number_format($magicAccuracy, 1) }}%</span>
                </div>
                <p class="text-xs text-gray-500">Шанс успеха заклинаний</p>
            </div>
            <div class="border border-[#e3e3e0] dark:border-[#3E3E3A] rounded p-3">
                <div class="flex justify-between items-center mb-1">
                    <span class="text-sm font-medium">Регенерация HP</span>
                    <span class="font-bold">{{ $healthRegenerationRate }} / 15 сек</span>
                </div>
                <p class="text-xs text-gray-500">Восстановление здоровья</p>
            </div>
            <div class="border border-[#e3e3e0] dark:border-[#3E3E3A] rounded p-3">
                <div class="flex justify-between items-center mb-1">
                    <span class="text-sm font-medium">Регенерация MP</span>
                    <span class="font-bold">{{ $manaRegenerationRate }} / 15 сек</span>
                </div>
                <p class="text-xs text-gray-500">Восстановление маны</p>
            </div>
        </div>
        <div class="mt-4 text-center">
            <a href="{{ route('characters.skills', $character) }}" class="text-sm text-[#f53003] dark:text-[#FF4433] hover:underline">
                Посмотреть все характеристики →
            </a>
        </div>
    </div>

    @if($character->description)
        <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Описание</h2>
            <p>{{ $character->description }}</p>
        </div>
    @endif

    <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6">
        <h2 class="text-xl font-semibold mb-4">Навыки</h2>
        @if($character->characterSkills->count() > 0)
            <div class="space-y-4">
                @foreach($character->characterSkills as $characterSkill)
                    <div class="border border-[#e3e3e0] dark:border-[#3E3E3A] rounded p-4">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-start gap-4">
                            <div class="flex-1">
                                <h3 class="font-medium">{{ $characterSkill->skill->name }}</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $characterSkill->skill->description }}</p>
                                <p class="text-xs text-gray-500 mt-1">
                                    Категория: {{ $characterSkill->skill->category }}
                                </p>
                            </div>
                            <div class="text-left sm:text-right">
                                <p class="font-medium">Уровень {{ $characterSkill->level }} / {{ $characterSkill->skill->max_level }}</p>
                                <p class="text-xs text-gray-500">Опыт: {{ $characterSkill->experience }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-600 dark:text-gray-400">У персонажа пока нет навыков</p>
        @endif
    </div>
</div>
@endsection

