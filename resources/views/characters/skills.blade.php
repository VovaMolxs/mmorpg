@extends('layouts.app')

@section('title', 'Навыки: ' . $character->name)

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <h1 class="text-2xl sm:text-3xl font-semibold">Навыки и характеристики</h1>
        <div class="flex flex-wrap gap-2 sm:gap-4 w-full sm:w-auto">
            <a
                href="{{ route('characters.show', $character) }}"
                class="px-3 sm:px-4 py-2 border border-[#19140035] dark:border-[#3E3E3A] rounded hover:border-[#1915014a] dark:hover:border-[#62605b] whitespace-nowrap text-sm sm:text-base"
            >
                Назад к персонажу
            </a>
            <a
                href="{{ route('characters.index') }}"
                class="px-3 sm:px-4 py-2 border border-[#19140035] dark:border-[#3E3E3A] rounded hover:border-[#1915014a] dark:hover:border-[#62605b] whitespace-nowrap text-sm sm:text-base"
            >
                Список персонажей
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4">Основная информация</h2>
                <div class="space-y-2 text-sm">
                    <p><span class="font-medium">Имя:</span> {{ $character->name }}</p>
                    <p><span class="font-medium">Уровень:</span> {{ $character->level }}</p>
                    <p><span class="font-medium">Опыт:</span> {{ $character->experience }}</p>
                    <p><span class="font-medium">Доступно очков:</span> {{ $character->available_points }}</p>
                    <p><span class="font-medium">Здоровье:</span> {{ $character->health_current }} / {{ $character->health_max }}</p>
                    <p><span class="font-medium">Мана:</span> {{ $character->mana_current }} / {{ $character->mana_max }}</p>
                </div>
            </div>

            <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4">Характеристики</h2>
                <div class="space-y-3">
                    <div class="border border-[#e3e3e0] dark:border-[#3E3E3A] rounded p-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-[#0a0a0a] transition-colors attribute-item" data-attribute="strength" data-value="{{ $character->strength }}">
                        <div class="flex justify-between items-center">
                            <span class="font-medium">Сила</span>
                            <span class="text-lg font-bold">{{ $character->strength }}</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">1 Силы = 10 HP</p>
                    </div>

                    <div class="border border-[#e3e3e0] dark:border-[#3E3E3A] rounded p-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-[#0a0a0a] transition-colors attribute-item" data-attribute="agility" data-value="{{ $character->agility }}">
                        <div class="flex justify-between items-center">
                            <span class="font-medium">Ловкость</span>
                            <span class="text-lg font-bold">{{ $character->agility }}</span>
                        </div>
                    </div>

                    <div class="border border-[#e3e3e0] dark:border-[#3E3E3A] rounded p-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-[#0a0a0a] transition-colors attribute-item" data-attribute="intelligence" data-value="{{ $character->intelligence }}">
                        <div class="flex justify-between items-center">
                            <span class="font-medium">Интеллект</span>
                            <span class="text-lg font-bold">{{ $character->intelligence }}</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">1 Интеллекта = 10 MP</p>
                    </div>
                </div>
                <div class="mt-4 p-3 bg-gray-100 dark:bg-[#0a0a0a] rounded">
                    <p class="text-sm">
                        <span class="font-medium">Сумма характеристик:</span> {{ $character->getTotalAttributes() }} / 15
                    </p>
                </div>
            </div>

            @php
                $damage = $character->calculateDamage();
                $physicalDefense = $character->calculatePhysicalDefense();
                $magicDefense = $character->calculateMagicDefense();
                $accuracy = $character->calculateAccuracy();
                $magicAccuracy = $character->calculateMagicAccuracy();
                $rangedAccuracy = $character->calculateRangedAccuracy();
                $criticalChance = $character->calculateCriticalChance();
                $criticalPower = $character->calculateCriticalPower();
                $magicCriticalChance = $character->calculateMagicCriticalChance();
                $magicCriticalPower = $character->calculateMagicCriticalPower();
                $dodge = $character->calculateDodge();
                $magicDodge = $character->calculateMagicDodge();
                $spellSuccessChance = $character->calculateSpellSuccessChance();
                $awareness = $character->calculateAwareness();
                $stealth = $character->calculateStealth();
                $healthRegenerationRate = $character->calculateHealthRegenerationRate();
                $manaRegenerationRate = $character->calculateManaRegenerationRate();
            @endphp

            <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4">Производные характеристики</h2>
                
                <div class="space-y-4">
                    <!-- Боевые характеристики -->
                    <div>
                        <h3 class="text-lg font-medium mb-3 text-[#f53003] dark:text-[#FF4433]">Боевые характеристики</h3>
                        <div class="space-y-2">
                            <div class="border border-[#e3e3e0] dark:border-[#3E3E3A] rounded p-3">
                                <div class="flex justify-between items-center">
                                    <span class="font-medium">Урон</span>
                                    <span class="text-lg font-bold">{{ $damage['min'] }}-{{ $damage['max'] }}</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Зависит от силы, оружия и навыков владения</p>
                            </div>
                            <div class="border border-[#e3e3e0] dark:border-[#3E3E3A] rounded p-3">
                                <div class="flex justify-between items-center">
                                    <span class="font-medium">Физическая защита</span>
                                    <span class="text-lg font-bold">{{ $physicalDefense }}</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Зависит от ловкости, брони и навыка защиты</p>
                            </div>
                            <div class="border border-[#e3e3e0] dark:border-[#3E3E3A] rounded p-3">
                                <div class="flex justify-between items-center">
                                    <span class="font-medium">Магическая защита</span>
                                    <span class="text-lg font-bold">{{ $magicDefense }}</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Зависит от интеллекта и сопротивления магии</p>
                            </div>
                        </div>
                    </div>

                    <!-- Точность и критические удары -->
                    <div>
                        <h3 class="text-lg font-medium mb-3 text-[#f53003] dark:text-[#FF4433]">Точность и критические удары</h3>
                        <div class="space-y-2">
                            <div class="border border-[#e3e3e0] dark:border-[#3E3E3A] rounded p-3">
                                <div class="flex justify-between items-center">
                                    <span class="font-medium">Точность</span>
                                    <span class="text-lg font-bold">{{ number_format($accuracy, 1) }}%</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Шанс попадания физическими атаками</p>
                            </div>
                            <div class="border border-[#e3e3e0] dark:border-[#3E3E3A] rounded p-3">
                                <div class="flex justify-between items-center">
                                    <span class="font-medium">Точность магии</span>
                                    <span class="text-lg font-bold">{{ number_format($magicAccuracy, 1) }}%</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Шанс успешного применения заклинаний</p>
                            </div>
                            <div class="border border-[#e3e3e0] dark:border-[#3E3E3A] rounded p-3">
                                <div class="flex justify-between items-center">
                                    <span class="font-medium">Точность дальнего оружия</span>
                                    <span class="text-lg font-bold">{{ number_format($rangedAccuracy, 1) }}%</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Шанс попадания стрелковым оружием</p>
                            </div>
                            <div class="border border-[#e3e3e0] dark:border-[#3E3E3A] rounded p-3">
                                <div class="flex justify-between items-center">
                                    <span class="font-medium">Шанс критического удара</span>
                                    <span class="text-lg font-bold">{{ number_format($criticalChance, 1) }}%</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Вероятность крита физической атакой</p>
                            </div>
                            <div class="border border-[#e3e3e0] dark:border-[#3E3E3A] rounded p-3">
                                <div class="flex justify-between items-center">
                                    <span class="font-medium">Сила критического удара</span>
                                    <span class="text-lg font-bold">{{ number_format($criticalPower, 2) }}x</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Множитель урона при крите</p>
                            </div>
                            <div class="border border-[#e3e3e0] dark:border-[#3E3E3A] rounded p-3">
                                <div class="flex justify-between items-center">
                                    <span class="font-medium">Шанс магического крита</span>
                                    <span class="text-lg font-bold">{{ number_format($magicCriticalChance, 1) }}%</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Вероятность крита заклинанием</p>
                            </div>
                            <div class="border border-[#e3e3e0] dark:border-[#3E3E3A] rounded p-3">
                                <div class="flex justify-between items-center">
                                    <span class="font-medium">Сила магического крита</span>
                                    <span class="text-lg font-bold">{{ number_format($magicCriticalPower, 2) }}x</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Множитель урона при магическом крите</p>
                            </div>
                        </div>
                    </div>

                    <!-- Защитные характеристики -->
                    <div>
                        <h3 class="text-lg font-medium mb-3 text-[#f53003] dark:text-[#FF4433]">Защитные характеристики</h3>
                        <div class="space-y-2">
                            <div class="border border-[#e3e3e0] dark:border-[#3E3E3A] rounded p-3">
                                <div class="flex justify-between items-center">
                                    <span class="font-medium">Уворот</span>
                                    <span class="text-lg font-bold">{{ number_format($dodge, 1) }}%</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Шанс уклониться от физической атаки</p>
                            </div>
                            <div class="border border-[#e3e3e0] dark:border-[#3E3E3A] rounded p-3">
                                <div class="flex justify-between items-center">
                                    <span class="font-medium">Уворот от магии</span>
                                    <span class="text-lg font-bold">{{ number_format($magicDodge, 1) }}%</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Шанс сопротивления магической атаке</p>
                            </div>
                        </div>
                    </div>

                    <!-- Магические характеристики -->
                    <div>
                        <h3 class="text-lg font-medium mb-3 text-[#f53003] dark:text-[#FF4433]">Магические характеристики</h3>
                        <div class="space-y-2">
                            <div class="border border-[#e3e3e0] dark:border-[#3E3E3A] rounded p-3">
                                <div class="flex justify-between items-center">
                                    <span class="font-medium">Шанс применения магии</span>
                                    <span class="text-lg font-bold">{{ number_format($spellSuccessChance, 1) }}%</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Базовый шанс успеха заклинания</p>
                            </div>
                        </div>
                    </div>

                    <!-- Восстановление -->
                    <div>
                        <h3 class="text-lg font-medium mb-3 text-[#f53003] dark:text-[#FF4433]">Восстановление</h3>
                        <div class="space-y-2">
                            <div class="border border-[#e3e3e0] dark:border-[#3E3E3A] rounded p-3">
                                <div class="flex justify-between items-center">
                                    <span class="font-medium">Регенерация здоровья</span>
                                    <span class="text-lg font-bold">{{ $healthRegenerationRate }} HP</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Восстановление за 15 секунд (базовая: 1, максимум: 8)</p>
                            </div>
                            <div class="border border-[#e3e3e0] dark:border-[#3E3E3A] rounded p-3">
                                <div class="flex justify-between items-center">
                                    <span class="font-medium">Регенерация маны</span>
                                    <span class="text-lg font-bold">{{ $manaRegenerationRate }} MP</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Восстановление за 15 секунд (зависит от интеллекта и навыка, максимум: 12)</p>
                            </div>
                        </div>
                    </div>

                    <!-- Социальные/скрытые характеристики -->
                    <div>
                        <h3 class="text-lg font-medium mb-3 text-[#f53003] dark:text-[#FF4433]">Социальные характеристики</h3>
                        <div class="space-y-2">
                            <div class="border border-[#e3e3e0] dark:border-[#3E3E3A] rounded p-3">
                                <div class="flex justify-between items-center">
                                    <span class="font-medium">Наблюдательность</span>
                                    <span class="text-lg font-bold">{{ $awareness }}</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Защита от краж и скрытных действий</p>
                            </div>
                            <div class="border border-[#e3e3e0] dark:border-[#3E3E3A] rounded p-3">
                                <div class="flex justify-between items-center">
                                    <span class="font-medium">Скрытность</span>
                                    <span class="text-lg font-bold">{{ $stealth }}</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Способность оставаться незамеченным</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4">Изученные навыки</h2>
                @if($learnedSkills->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($learnedSkills as $skill)
                            @php
                                $characterSkill = $character->characterSkills->where('skill_id', $skill->id)->first();
                            @endphp
                            <div class="border border-[#e3e3e0] dark:border-[#3E3E3A] rounded p-4 cursor-pointer hover:bg-gray-50 dark:hover:bg-[#0a0a0a] transition-colors skill-item" data-skill-id="{{ $skill->id }}">
                                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-start gap-4">
                                    <div class="flex-1">
                                        <h3 class="font-medium">{{ $skill->name }}</h3>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $skill->description }}</p>
                                        <p class="text-xs text-gray-500 mt-2">
                                            Категория: <span class="capitalize">{{ $skill->category }}</span>
                                        </p>
                                    </div>
                                    <div class="text-left sm:text-right sm:ml-4">
                                        <p class="font-medium">Уровень {{ $characterSkill->level }} / {{ $skill->max_level }}</p>
                                        <p class="text-xs text-gray-500">Опыт: {{ $characterSkill->experience }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-600 dark:text-gray-400">У персонажа пока нет изученных навыков</p>
                @endif
            </div>

            <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4">Доступные навыки</h2>
                @php
                    $learnedSkillIds = $learnedSkills->pluck('id')->toArray();
                @endphp
                @foreach(['combat', 'magic', 'craft', 'survival', 'social'] as $category)
                    @php
                        $categorySkills = $skillsByCategory[$category]->filter(function ($skill) use ($character, $learnedSkillIds) {
                            return $skill->isAvailableForCharacter($character) && !in_array($skill->id, $learnedSkillIds);
                        });
                    @endphp
                    @if($categorySkills->count() > 0)
                        <div class="mb-6">
                            <h3 class="text-lg font-medium mb-3 capitalize">{{ $category }}</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach($categorySkills as $skill)
                                    <div class="border border-[#e3e3e0] dark:border-[#3E3E3A] rounded p-4 cursor-pointer hover:bg-gray-50 dark:hover:bg-[#0a0a0a] transition-colors skill-item" data-skill-id="{{ $skill->id }}">
                                        <h4 class="font-medium">{{ $skill->name }}</h4>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $skill->description }}</p>
                                        <div class="mt-2 text-xs text-gray-500">
                                            <p>Макс. уровень: {{ $skill->max_level }}</p>
                                            @if($skill->required_level > 1)
                                                <p>Требуется уровень: {{ $skill->required_level }}</p>
                                            @endif
                                            @if($skill->attribute_requirements)
                                                <p>Требования: {{ json_encode($skill->attribute_requirements, JSON_UNESCAPED_UNICODE) }}</p>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach

                @if($availableSkills->count() === 0)
                    <p class="text-gray-600 dark:text-gray-400">Нет доступных навыков для изучения</p>
                @endif
            </div>
        </div>
    </div>
</div>

<div id="skill-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-[#161615] rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex justify-between items-start mb-4">
                <h2 id="modal-title" class="text-2xl font-semibold"></h2>
                <button id="close-modal" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div id="modal-content" class="space-y-4"></div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('skill-modal');
    const modalTitle = document.getElementById('modal-title');
    const modalContent = document.getElementById('modal-content');
    const closeModal = document.getElementById('close-modal');
    const skillItems = document.querySelectorAll('.skill-item');
    const attributeItems = document.querySelectorAll('.attribute-item');

    const skills = @json($allSkills->keyBy('id'));
    const character = @json($character);
    const characterSkills = @json($character->characterSkills);

    function showModal(title, content) {
        modalTitle.textContent = title;
        modalContent.innerHTML = content;
        modal.classList.remove('hidden');
    }

    function hideModal() {
        modal.classList.add('hidden');
    }

    closeModal.addEventListener('click', hideModal);
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            hideModal();
        }
    });

    skillItems.forEach(item => {
        item.addEventListener('click', function() {
            const skillId = parseInt(this.dataset.skillId);
            const skill = skills[skillId];
            
            if (!skill) return;

            const characterSkill = characterSkills.find(cs => cs.skill_id === skillId);
            const level = characterSkill ? characterSkill.level : 0;

            let content = `
                <div class="space-y-4">
                    <div>
                        <p class="text-gray-600 dark:text-gray-400">${skill.description || 'Нет описания'}</p>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm font-medium">Категория</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400 capitalize">${skill.category}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium">Максимальный уровень</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">${skill.max_level}</p>
                        </div>
                        ${level > 0 ? `
                        <div>
                            <p class="text-sm font-medium">Текущий уровень</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">${level} / ${skill.max_level}</p>
                        </div>
                        ` : ''}
                        ${skill.required_level > 1 ? `
                        <div>
                            <p class="text-sm font-medium">Требуется уровень</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">${skill.required_level}</p>
                        </div>
                        ` : ''}
                        ${skill.mana_cost > 0 ? `
                        <div>
                            <p class="text-sm font-medium">Стоимость маны</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">${skill.mana_cost}</p>
                        </div>
                        ` : ''}
                        ${skill.cooldown > 0 ? `
                        <div>
                            <p class="text-sm font-medium">Перезарядка</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">${skill.cooldown} сек</p>
                        </div>
                        ` : ''}
                    </div>
                    ${skill.attribute_requirements ? `
                    <div>
                        <p class="text-sm font-medium mb-2">Требования к характеристикам:</p>
                        <ul class="list-disc list-inside text-sm text-gray-600 dark:text-gray-400">
                            ${Object.entries(skill.attribute_requirements).map(([attr, value]) => 
                                `<li>${attr === 'strength' ? 'Сила' : attr === 'agility' ? 'Ловкость' : 'Интеллект'}: ${value}</li>`
                            ).join('')}
                        </ul>
                    </div>
                    ` : ''}
                </div>
            `;

            showModal(skill.name, content);
        });
    });

    attributeItems.forEach(item => {
        item.addEventListener('click', function() {
            const attribute = this.dataset.attribute;
            const value = parseInt(this.dataset.value);
            
            const attributeNames = {
                strength: 'Сила',
                agility: 'Ловкость',
                intelligence: 'Интеллект'
            };

            let content = `
                <div class="space-y-4">
                    <div>
                        <p class="text-3xl font-bold">${value}</p>
                        <p class="text-gray-600 dark:text-gray-400 mt-2">${attributeNames[attribute]}</p>
                    </div>
                    ${attribute === 'strength' ? `
                    <div>
                        <p class="text-sm font-medium mb-2">Влияние на характеристики:</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">1 Силы = 10 HP</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Текущее здоровье: ${value * 10} HP</p>
                    </div>
                    ` : ''}
                    ${attribute === 'intelligence' ? `
                    <div>
                        <p class="text-sm font-medium mb-2">Влияние на характеристики:</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">1 Интеллекта = 10 MP</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Текущая мана: ${value * 10} MP</p>
                    </div>
                    ` : ''}
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Характеристики можно улучшить в игре за игровые деньги.</p>
                    </div>
                </div>
            `;

            showModal(attributeNames[attribute], content);
        });
    });
});
</script>
@endsection

