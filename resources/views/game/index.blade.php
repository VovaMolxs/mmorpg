@extends('layouts.app')

@section('title', 'Игровой мир')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <h1 class="text-2xl sm:text-3xl font-semibold">Игровой мир</h1>
        <div class="flex flex-wrap gap-2 sm:gap-4 w-full sm:w-auto">
            <button
                onclick="leaveWorld()"
                class="px-3 sm:px-4 py-2 bg-red-600 dark:bg-red-700 text-white rounded hover:bg-red-700 dark:hover:bg-red-600 font-medium whitespace-nowrap text-sm sm:text-base"
                id="leave-world-btn"
            >
                Выйти из игры
            </button>
            <a
                href="{{ route('characters.show', $character) }}"
                class="px-3 sm:px-4 py-2 border border-[#19140035] dark:border-[#3E3E3A] rounded hover:border-[#1915014a] dark:hover:border-[#62605b] whitespace-nowrap text-sm sm:text-base"
            >
                Профиль персонажа
            </a>
        </div>
    </div>

    @if($session)
        <div class="bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-700 rounded-lg p-4 mb-6">
            <p class="text-sm text-green-800 dark:text-green-200">
                Вы в игре. Сессия активна с {{ $session->login_at->format('d.m.Y H:i') }}
            </p>
        </div>
    @else
        <div class="bg-yellow-100 dark:bg-yellow-900 border border-yellow-400 dark:border-yellow-700 rounded-lg p-4 mb-6">
            <p class="text-sm text-yellow-800 dark:text-yellow-200">
                У вас нет активной игровой сессии. Пожалуйста, войдите в игру.
            </p>
            <button
                onclick="enterWorld({{ $character->id }})"
                class="mt-2 px-4 py-2 bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded hover:bg-black dark:hover:bg-white font-medium"
            >
                Войти в игру
            </button>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Информация о персонаже -->
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4">{{ $character->name }}</h2>
                <div class="space-y-2 text-sm">
                    <p><span class="font-medium">Уровень:</span> {{ $character->level }}</p>
                    <p><span class="font-medium">Здоровье:</span> {{ $character->health_current }} / {{ $character->health_max }}</p>
                    <p><span class="font-medium">Мана:</span> {{ $character->mana_current }} / {{ $character->mana_max }}</p>
                    @if($character->location)
                        <p><span class="font-medium">Локация:</span> {{ $character->location->name }}</p>
                    @endif
                </div>
            </div>

            <!-- Статус сессии -->
            @if($session)
                <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6">
                    <h2 class="text-xl font-semibold mb-4">Статус сессии</h2>
                    <div class="space-y-2 text-sm">
                        <p><span class="font-medium">Вход:</span> {{ $session->login_at->format('H:i:s') }}</p>
                        <p><span class="font-medium">Последняя активность:</span> <span id="last-activity">{{ $session->last_activity_at->format('H:i:s') }}</span></p>
                        <p><span class="font-medium">Осталось времени:</span> <span id="remaining-time">--:--</span></p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Игровая область -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Текущая локация -->
            <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6">
                <div id="location-content">
                    <div class="flex items-center justify-center py-8">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900 dark:border-gray-100"></div>
                    </div>
                </div>
            </div>

            <!-- Направления перемещения -->
            <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4">Направления перемещения</h2>
                <div id="exits-content">
                    <p class="text-gray-600 dark:text-gray-400">Загрузка...</p>
                </div>
            </div>

            <!-- Камни воскрешения в локации -->
            <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4">Камни воскрешения</h2>
                <div id="resurrection-stones-content">
                    <p class="text-gray-600 dark:text-gray-400">Загрузка...</p>
                </div>
            </div>

            <!-- NPC в локации -->
            <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4">NPC в локации</h2>
                <div id="npcs-content">
                    <p class="text-gray-600 dark:text-gray-400">Загрузка...</p>
                </div>
            </div>

            <!-- Предметы в локации -->
            <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4">Предметы в локации</h2>
                <div id="items-content">
                    <p class="text-gray-600 dark:text-gray-400">Загрузка...</p>
                </div>
            </div>

            <!-- Трупы в локации -->
            <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4">Трупы в локации</h2>
                <div id="corpses-content">
                    <p class="text-gray-600 dark:text-gray-400">Загрузка...</p>
                </div>
            </div>

            <!-- Игроки и призраки в локации -->
            <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4">Игроки и призраки</h2>
                <div id="players-content">
                    <p class="text-gray-600 dark:text-gray-400">Загрузка...</p>
                </div>
                <div id="ghosts-content" class="mt-4">
                    <p class="text-gray-600 dark:text-gray-400">Загрузка...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для взаимодействия с NPC -->
@include('game.npc-interaction')

<!-- Модальное окно для осмотра предметов в трупе -->
<div id="corpseItemsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" onclick="if(event.target === this) closeCorpseItemsModal()">
    <div class="bg-white dark:bg-[#161615] rounded-lg p-6 max-w-2xl w-full max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
        <div class="flex justify-between items-center mb-4">
            <h3 id="corpseItemsModalTitle" class="text-xl font-semibold">Предметы в трупе</h3>
            <button
                onclick="closeCorpseItemsModal()"
                class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div id="corpseItemsModalContent" class="space-y-3">
            <!-- Контент будет загружен динамически -->
        </div>
    </div>
</div>

<!-- Модальное окно для осмотра предмета на земле -->
<div id="groundItemModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-[#161615] rounded-lg p-6 max-w-md w-full max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
            <h3 id="groundItemModalTitle" class="text-xl font-semibold"></h3>
            <button onclick="closeGroundItemModal()" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">✕</button>
        </div>
        <div id="groundItemModalContent" class="space-y-4"></div>
    </div>
</div>

@push('scripts')
<script>
    const sessionToken = localStorage.getItem('game_session_token');
    let keepAliveInterval = null;

    // Функция входа в игру
    async function enterWorld(characterId) {
        try {
            const response = await fetch('{{ route("api.game.enter-world") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    character_id: characterId
                })
            });
            
            const data = await response.json();
            
            if (response.ok) {
                if (data.session && data.session.token) {
                    localStorage.setItem('game_session_token', data.session.token);
                }
                location.reload();
            } else {
                alert(data.error || 'Ошибка при входе в игру');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Произошла ошибка при входе в игру');
        }
    }

    // Функция выхода из игры
    async function leaveWorld() {
        const btn = document.getElementById('leave-world-btn');
        const originalText = btn.textContent;
        
        btn.disabled = true;
        btn.textContent = 'Выход...';
        
        try {
            const response = await fetch('{{ route("api.game.leave-world") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Session-Token': sessionToken || '',
                    'Accept': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (response.ok) {
                localStorage.removeItem('game_session_token');
                window.location.href = '{{ route("characters.index") }}';
            } else {
                alert(data.error || 'Ошибка при выходе из игры');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Произошла ошибка при выходе из игры');
        } finally {
            btn.disabled = false;
            btn.textContent = originalText;
        }
    }

    // Функция обновления активности (keep-alive)
    async function keepAlive() {
        if (!sessionToken) {
            return;
        }

        try {
            const response = await fetch('{{ route("api.game.keep-alive") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Session-Token': sessionToken,
                    'Accept': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (response.ok) {
                // Обновляем время последней активности
                if (data.last_activity_at) {
                    const date = new Date(data.last_activity_at);
                    document.getElementById('last-activity').textContent = date.toLocaleTimeString('ru-RU');
                }
                
                // Обновляем оставшееся время
                if (data.remaining_time !== undefined) {
                    const minutes = Math.floor(data.remaining_time / 60);
                    const seconds = data.remaining_time % 60;
                    document.getElementById('remaining-time').textContent = 
                        `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                    
                    // Предупреждение за 5 минут
                    if (data.warning) {
                        document.getElementById('remaining-time').classList.add('text-yellow-600', 'dark:text-yellow-400');
                    } else {
                        document.getElementById('remaining-time').classList.remove('text-yellow-600', 'dark:text-yellow-400');
                    }
                }
                
                // Если сессия истекла, перенаправляем
                if (data.expired) {
                    localStorage.removeItem('game_session_token');
                    alert('Сессия истекла из-за бездействия');
                    window.location.href = '{{ route("characters.index") }}';
                }
            } else if (response.status === 401) {
                // Сессия истекла
                localStorage.removeItem('game_session_token');
                if (keepAliveInterval) {
                    clearInterval(keepAliveInterval);
                }
            }
        } catch (error) {
            console.error('Keep alive error:', error);
        }
    }

    // Запускаем keep-alive каждые 30 секунд, если есть активная сессия
    @if($session)
        keepAlive();
        keepAliveInterval = setInterval(keepAlive, 30000); // Каждые 30 секунд
    @endif

    // Функция загрузки текущей локации
    async function loadCurrentLocation() {
        try {
            const response = await fetch('{{ route("api.location.current") }}', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (response.ok) {
                // Устанавливаем уровень персонажа
                window.currentCharacterLevel = {{ $character->level }};
                
                // Проверяем, является ли текущий персонаж призраком
                const currentCharacterId = {{ $character->id }};
                const isGhost = (data.ghosts || []).some(ghost => ghost.character_id === currentCharacterId);
                window.currentCharacterIsGhost = isGhost;
                
                renderLocation(data.location);
                renderExits(data.exits || []);
                renderItems(data.items || []);
                renderResurrectionStones(data.resurrection_stones || []);
                renderNpcs(data.npcs || []);
                renderCorpses(data.corpses || []);
                renderPlayers(data.players || []);
                renderGhosts(data.ghosts || []);
            } else {
                document.getElementById('location-content').innerHTML = 
                    '<p class="text-red-600 dark:text-red-400">Ошибка загрузки локации: ' + (data.error || 'Неизвестная ошибка') + '</p>';
            }
        } catch (error) {
            console.error('Error loading location:', error);
            document.getElementById('location-content').innerHTML = 
                '<p class="text-red-600 dark:text-red-400">Произошла ошибка при загрузке локации</p>';
        }
    }

    // Функция отображения локации
    function renderLocation(location) {
        if (!location) {
            return;
        }

        const safeZoneBadge = location.is_safe_zone 
            ? '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Безопасная зона</span>'
            : '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">Опасная зона</span>';

        const imageHtml = location.image_url 
            ? `<img src="${location.image_url}" alt="${location.name}" class="w-full h-64 object-cover rounded-lg mb-4">`
            : '';

        const html = `
            <div>
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h2 class="text-2xl font-bold mb-2">${escapeHtml(location.name)}</h2>
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-sm text-gray-600 dark:text-gray-400">${escapeHtml(location.type)}</span>
                            ${safeZoneBadge}
                        </div>
                        ${location.coordinate_x !== null && location.coordinate_y !== null 
                            ? `<p class="text-sm text-gray-500 dark:text-gray-500">Координаты: (${location.coordinate_x}, ${location.coordinate_y})</p>`
                            : ''}
                    </div>
                </div>
                ${imageHtml}
                <div class="prose dark:prose-invert max-w-none">
                    <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">${escapeHtml(location.description || '')}</p>
                </div>
            </div>
        `;

        document.getElementById('location-content').innerHTML = html;
    }

    // Функция отображения выходов
    function renderExits(exits) {
        if (!exits || exits.length === 0) {
            document.getElementById('exits-content').innerHTML = 
                '<p class="text-gray-600 dark:text-gray-400">Нет доступных направлений</p>';
            return;
        }

        const exitsHtml = exits.map(exit => {
            const displayName = exit.custom_name || getDirectionName(exit.direction) || 'Неизвестное направление';
            const toLocationName = exit.to_location ? escapeHtml(exit.to_location.name) : 'Неизвестная локация';
            
            const isAccessible = exit.is_accessible;
            const isLocked = exit.is_locked;
            
            let buttonClass = 'w-full text-left px-4 py-3 rounded-lg border transition-colors ';
            let statusBadge = '';
            
            if (!isAccessible || isLocked) {
                buttonClass += 'border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400 cursor-not-allowed';
                statusBadge = '<span class="ml-2 text-xs text-red-600 dark:text-red-400">(Закрыто)</span>';
            } else {
                buttonClass += 'border-blue-300 dark:border-blue-700 bg-blue-50 dark:bg-blue-900/30 hover:bg-blue-100 dark:hover:bg-blue-900/50 cursor-pointer';
            }

            const errorsHtml = exit.accessibility_errors && exit.accessibility_errors.length > 0
                ? `<div class="mt-2 text-xs text-red-600 dark:text-red-400">
                    ${exit.accessibility_errors.map(err => escapeHtml(err)).join('<br>')}
                   </div>`
                : '';

            const descriptionHtml = exit.description 
                ? `<p class="text-sm text-gray-600 dark:text-gray-400 mt-1">${escapeHtml(exit.description)}</p>`
                : '';

            return `
                <div class="mb-3">
                    <button 
                        onclick="${isAccessible && !isLocked ? `moveToLocation('${exit.direction || ''}', '${exit.custom_name || ''}')` : ''}"
                        class="${buttonClass}"
                        ${!isAccessible || isLocked ? 'disabled' : ''}
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="font-medium">${escapeHtml(displayName)} ${statusBadge}</div>
                                ${descriptionHtml}
                                <p class="text-sm text-gray-500 dark:text-gray-500 mt-1">→ ${toLocationName}</p>
                            </div>
                            ${isAccessible && !isLocked ? '<span class="text-blue-600 dark:text-blue-400">→</span>' : ''}
                        </div>
                        ${errorsHtml}
                    </button>
                </div>
            `;
        }).join('');

        document.getElementById('exits-content').innerHTML = exitsHtml;
    }

    // Функция отображения NPC
    function renderNpcs(npcs) {
        if (!npcs || npcs.length === 0) {
            document.getElementById('npcs-content').innerHTML = 
                '<p class="text-gray-600 dark:text-gray-400">В локации нет NPC</p>';
            return;
        }

        const npcsHtml = npcs.map(npc => {
            // Определяем цвет здоровья
            let healthColor = 'bg-green-600';
            if (npc.health_percentage < 25) {
                healthColor = 'bg-red-600';
            } else if (npc.health_percentage < 50) {
                healthColor = 'bg-yellow-600';
            } else if (npc.health_percentage < 75) {
                healthColor = 'bg-orange-600';
            }

            // Определяем цвет маны
            const manaColor = 'bg-blue-600';

            // Бейджи для типа NPC
            const typeBadges = [];
            if (npc.is_hostile) {
                typeBadges.push('<span class="px-2 py-1 text-xs rounded bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">Враждебный</span>');
            }
            if (npc.is_merchant) {
                typeBadges.push('<span class="px-2 py-1 text-xs rounded bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Торговец</span>');
            }
            if (npc.is_teacher) {
                typeBadges.push('<span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">Учитель</span>');
            }
            if (npc.is_quest_giver) {
                typeBadges.push('<span class="px-2 py-1 text-xs rounded bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">Квестодатель</span>');
            }

            // Поведение AI
            const behaviorNames = {
                'passive': 'Пассивный',
                'neutral': 'Нейтральный',
                'aggressive': 'Агрессивный'
            };
            const behaviorName = behaviorNames[npc.ai_behavior] || npc.ai_behavior;

            return `
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-3">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <h3 class="font-medium text-lg">${escapeHtml(npc.name)}</h3>
                                <span class="text-sm text-gray-600 dark:text-gray-400">Ур. ${npc.level}</span>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">${escapeHtml(npc.description || '')}</p>
                            <div class="flex flex-wrap gap-2 mb-3">
                                ${typeBadges.join('')}
                                <span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200">
                                    ${escapeHtml(behaviorName)}
                                </span>
                            </div>
                            
                            <!-- Здоровье -->
                            <div class="mb-2">
                                <div class="flex items-center justify-between text-sm mb-1">
                                    <span class="text-gray-600 dark:text-gray-400">Здоровье</span>
                                    <span class="font-medium">${npc.health_current} / ${npc.health_max}</span>
                                </div>
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                    <div 
                                        class="${healthColor} h-2 rounded-full transition-all duration-300" 
                                        style="width: ${Math.max(0, Math.min(100, npc.health_percentage))}%"
                                    ></div>
                                </div>
                            </div>

                            <!-- Мана -->
                            <div class="mb-3">
                                <div class="flex items-center justify-between text-sm mb-1">
                                    <span class="text-gray-600 dark:text-gray-400">Мана</span>
                                    <span class="font-medium">${npc.mana_current} / ${npc.mana_max}</span>
                                </div>
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                    <div 
                                        class="${manaColor} h-2 rounded-full transition-all duration-300" 
                                        style="width: ${Math.max(0, Math.min(100, npc.mana_percentage))}%"
                                    ></div>
                                </div>
                            </div>

                            <!-- Кнопки действий -->
                            <div class="flex gap-2 mt-3">
                                <button
                                    onclick="interactWithNpc(${npc.npc_id}, '${escapeHtml(npc.name)}')"
                                    class="flex-1 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm font-medium"
                                >
                                    Взаимодействовать
                                </button>
                                ${npc.is_hostile ? `
                                    <button
                                        onclick="attackNpc(${npc.npc_id}, '${escapeHtml(npc.name)}')"
                                        class="flex-1 px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 text-sm font-medium"
                                    >
                                        Атаковать
                                    </button>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        document.getElementById('npcs-content').innerHTML = npcsHtml;
    }

    // Переменные для работы с NPC
    let currentNpcId = null;
    let currentNpcData = null;
    let currentDialogHistory = [];
    let currentDialogId = null;

    // Функция взаимодействия с NPC
    async function interactWithNpc(npcId, npcName) {
        try {
            currentNpcId = npcId;
            const response = await fetch(`{{ route('api.npcs.show', ['npc' => '__NPC_ID__']) }}`.replace('__NPC_ID__', npcId), {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (response.ok) {
                currentNpcData = data;
                openNpcInteractionModal(data);
            } else {
                alert(data.error || 'Ошибка при загрузке информации о NPC');
            }
        } catch (error) {
            console.error('Error loading NPC:', error);
            alert('Произошла ошибка при загрузке информации о NPC');
        }
    }

    // Открыть модальное окно взаимодействия с NPC
    function openNpcInteractionModal(data) {
        const modal = document.getElementById('npcInteractionModal');
        const npc = data.npc;

        // Заполняем заголовок
        document.getElementById('npcModalName').textContent = npc.name;
        
        // Бейджи
        const badgesContainer = document.getElementById('npcModalBadges');
        badgesContainer.innerHTML = '';
        
        if (npc.is_merchant) {
            badgesContainer.innerHTML += '<span class="px-2 py-1 text-xs rounded bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Торговец</span>';
        }
        if (npc.is_teacher) {
            badgesContainer.innerHTML += '<span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">Учитель</span>';
        }
        if (npc.is_quest_giver) {
            badgesContainer.innerHTML += '<span class="px-2 py-1 text-xs rounded bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">Квестодатель</span>';
        }
        if (npc.is_hostile) {
            badgesContainer.innerHTML += '<span class="px-2 py-1 text-xs rounded bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">Враждебный</span>';
        }

        // Скрываем вкладки, которые недоступны
        const tabs = document.querySelectorAll('.npc-tab');
        tabs.forEach(tab => {
            const tabName = tab.dataset.tab;
            if (tabName === 'trade' && !npc.is_merchant) {
                tab.style.display = 'none';
            } else if (tabName === 'training' && !npc.is_teacher) {
                tab.style.display = 'none';
            } else if (tabName === 'quests' && !npc.is_quest_giver) {
                tab.style.display = 'none';
            } else if (tabName === 'bank' && !npc.is_banker) {
                tab.style.display = 'none';
            } else {
                tab.style.display = 'block';
            }
        });

        // Заполняем данные для всех вкладок
        populateNpcOverview(data);
        populateNpcDialog(data);
        populateNpcQuests(data);
        populateNpcTrade(data);
        populateNpcTraining(data);
        populateNpcBank(data);

        // Показываем модальное окно и переключаемся на первую доступную вкладку
        modal.classList.remove('hidden');
        switchNpcTab('overview');
    }

    // Закрыть модальное окно
    function closeNpcInteractionModal() {
        document.getElementById('npcInteractionModal').classList.add('hidden');
        currentNpcId = null;
        currentNpcData = null;
        currentDialogHistory = [];
        currentDialogId = null;
    }

    // Переключение вкладок
    function switchNpcTab(tabName) {
        // Обновляем стили вкладок
        document.querySelectorAll('.npc-tab').forEach(tab => {
            if (tab.dataset.tab === tabName) {
                tab.classList.add('border-blue-600', 'text-blue-600', 'dark:text-blue-400', 'font-medium');
                tab.classList.remove('border-transparent', 'text-gray-600', 'dark:text-gray-400');
            } else {
                tab.classList.remove('border-blue-600', 'text-blue-600', 'dark:text-blue-400', 'font-medium');
                tab.classList.add('border-transparent', 'text-gray-600', 'dark:text-gray-400');
            }
        });

        // Показываем/скрываем содержимое вкладок
        document.querySelectorAll('.npc-tab-content').forEach(content => {
            content.classList.add('hidden');
        });
        document.getElementById(`npcTab${tabName.charAt(0).toUpperCase() + tabName.slice(1)}`).classList.remove('hidden');

        // Если открывается вкладка банка, инициализируем подвкладку депозита
        if (tabName === 'bank' && window.currentBankerData) {
            switchBankSubTab('deposit');
        }
    }

    // Заполнить вкладку "Обзор"
    function populateNpcOverview(data) {
        const npc = data.npc;
        
        document.getElementById('npcOverviewDescription').textContent = npc.description || 'Нет описания';
        
        if (npc.stats) {
            document.getElementById('npcOverviewStats').classList.remove('hidden');
            document.getElementById('npcOverviewLevel').textContent = npc.stats.level;
            document.getElementById('npcOverviewStrength').textContent = npc.stats.strength;
            document.getElementById('npcOverviewAgility').textContent = npc.stats.agility;
            document.getElementById('npcOverviewIntelligence').textContent = npc.stats.intelligence;
            
            const healthPercent = (npc.stats.health_current / npc.stats.health_max) * 100;
            document.getElementById('npcOverviewHealthText').textContent = `${npc.stats.health_current} / ${npc.stats.health_max}`;
            document.getElementById('npcOverviewHealthBar').style.width = `${healthPercent}%`;
            
            const manaPercent = (npc.stats.mana_current / npc.stats.mana_max) * 100;
            document.getElementById('npcOverviewManaText').textContent = `${npc.stats.mana_current} / ${npc.stats.mana_max}`;
            document.getElementById('npcOverviewManaBar').style.width = `${manaPercent}%`;
        } else {
            document.getElementById('npcOverviewStats').classList.add('hidden');
        }

        const behaviorNames = {
            'passive': 'Пассивный',
            'neutral': 'Нейтральный',
            'aggressive': 'Агрессивный'
        };
        document.getElementById('npcOverviewBehavior').textContent = behaviorNames[npc.ai_behavior] || npc.ai_behavior;
    }

    // Заполнить вкладку "Диалог"
    function populateNpcDialog(data) {
        const dialogs = data.dialogs || [];
        
        if (dialogs.length === 0) {
            document.getElementById('npcDialogCurrent').classList.add('hidden');
            document.getElementById('npcDialogNoDialogs').classList.remove('hidden');
            document.getElementById('npcDialogHistory').innerHTML = '';
            return;
        }

        document.getElementById('npcDialogNoDialogs').classList.add('hidden');
        document.getElementById('npcDialogCurrent').classList.remove('hidden');
        
        // Очищаем историю при первом открытии
        currentDialogHistory = [];
        
        // Показываем первый доступный диалог
        if (dialogs.length > 0) {
            showDialog(dialogs[0]);
        }
    }

    // Показать диалог
    function showDialog(dialog) {
        if (!dialog) return;
        
        currentDialogId = dialog.id;
        document.getElementById('npcDialogText').textContent = dialog.text || '';
        
        // Добавляем в историю только если это новый диалог
        const isNewDialog = !currentDialogHistory.some(h => h.dialogId === dialog.id);
        if (isNewDialog) {
            currentDialogHistory.push({
                type: 'npc',
                text: dialog.text || '',
                dialogId: dialog.id
            });
            updateDialogHistory();
        }

        // Показываем ответы
        const answersContainer = document.getElementById('npcDialogAnswers');
        answersContainer.innerHTML = '';
        
        if (dialog.answers && dialog.answers.length > 0) {
            dialog.answers.forEach(answer => {
                const answerDiv = document.createElement('div');
                let answerHtml = `<button onclick="selectDialogAnswer(${answer.id}, ${answer.next_dialog_id || 'null'})" class="w-full text-left px-4 py-3 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors mb-2">`;
                
                answerHtml += `<div class="font-medium">${escapeHtml(answer.text || '')}</div>`;
                
                if (answer.item_required) {
                    answerHtml += `<div class="text-xs text-yellow-600 dark:text-yellow-400 mt-1">Требуется: ${escapeHtml(answer.item_required.name || '')}</div>`;
                }
                if (answer.skill_required) {
                    answerHtml += `<div class="text-xs text-blue-600 dark:text-blue-400 mt-1">Требуется навык: ${escapeHtml(answer.skill_required.name || '')} (ур. ${answer.skill_required.level_required || 0})</div>`;
                }
                
                answerHtml += `</button>`;
                answerDiv.innerHTML = answerHtml;
                answersContainer.appendChild(answerDiv);
            });
        } else {
            answersContainer.innerHTML = '<p class="text-gray-500 dark:text-gray-400 text-center py-4">Диалог завершен</p>';
        }
    }

    // Выбрать ответ в диалоге
    async function selectDialogAnswer(answerId, nextDialogId) {
        try {
            const response = await fetch('{{ route("api.dialogs.answer") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    dialog_id: currentDialogId,
                    answer_id: answerId
                })
            });

            const data = await response.json();

            if (response.ok) {
                // Добавляем ответ в историю
                const answer = currentNpcData.dialogs
                    .flatMap(d => (d.answers || []))
                    .find(a => a.id === answerId);
                
                if (answer) {
                    currentDialogHistory.push({
                        type: 'player',
                        text: answer.text || ''
                    });
                    updateDialogHistory();
                }

                if (data.dialog && data.dialog.id) {
                    // Показываем следующий диалог - нужно загрузить полные данные
                    const nextDialog = {
                        id: data.dialog.id,
                        text: data.dialog.text,
                        answers: data.answers || []
                    };
                    showDialog(nextDialog);
                } else if (data.dialog_completed) {
                    // Диалог завершен
                    document.getElementById('npcDialogAnswers').innerHTML = '<p class="text-gray-500 dark:text-gray-400 text-center py-4">Диалог завершен</p>';
                    
                    if (data.quest_started) {
                        alert('Квест принят!');
                        // Обновляем данные NPC
                        await interactWithNpc(currentNpcId, currentNpcData.npc.name);
                    }
                }
            } else {
                alert(data.error || 'Ошибка при обработке ответа');
            }
        } catch (error) {
            console.error('Error answering dialog:', error);
            alert('Произошла ошибка при обработке ответа');
        }
    }

    // Обновить историю диалога
    function updateDialogHistory() {
        const historyContainer = document.getElementById('npcDialogHistory');
        historyContainer.innerHTML = '';
        
        currentDialogHistory.forEach(entry => {
            const entryDiv = document.createElement('div');
            entryDiv.className = entry.type === 'npc' 
                ? 'bg-blue-50 dark:bg-blue-900/30 p-3 rounded-lg'
                : 'bg-gray-100 dark:bg-gray-700 p-3 rounded-lg ml-8';
            entryDiv.innerHTML = `<div class="text-xs text-gray-500 dark:text-gray-400 mb-1">${entry.type === 'npc' ? currentNpcData.npc.name : 'Вы'}</div><div>${escapeHtml(entry.text)}</div>`;
            historyContainer.appendChild(entryDiv);
        });
        
        historyContainer.scrollTop = historyContainer.scrollHeight;
    }

    // Заполнить вкладку "Квесты"
    function populateNpcQuests(data) {
        const questsToTurnIn = data.quests_to_turn_in || [];
        const availableQuests = data.available_quests || [];
        
        if (questsToTurnIn.length === 0 && availableQuests.length === 0) {
            document.getElementById('npcQuestsNone').classList.remove('hidden');
            document.getElementById('npcQuestsToTurnIn').classList.add('hidden');
            document.getElementById('npcQuestsAvailable').classList.add('hidden');
            return;
        }

        document.getElementById('npcQuestsNone').classList.add('hidden');

        // Квесты для сдачи
        if (questsToTurnIn.length > 0) {
            document.getElementById('npcQuestsToTurnIn').classList.remove('hidden');
            const container = document.getElementById('npcQuestsToTurnInList');
            container.innerHTML = '';
            
            questsToTurnIn.forEach(quest => {
                const questDiv = document.createElement('div');
                questDiv.className = 'border border-gray-200 dark:border-gray-700 rounded-lg p-4';
                let html = `<h4 class="font-semibold text-lg mb-2">${escapeHtml(quest.name)}</h4>`;
                html += `<p class="text-sm text-gray-600 dark:text-gray-400 mb-3">${escapeHtml(quest.description)}</p>`;
                
                html += `<div class="mb-3"><strong class="text-sm">Цели:</strong><ul class="list-disc list-inside mt-1 text-sm">`;
                quest.objectives.forEach(objective => {
                    const completed = objective.completed ? '✓' : '';
                    html += `<li class="${objective.completed ? 'text-green-600 dark:text-green-400' : ''}">${completed} ${escapeHtml(objective.description)} (${objective.current_count}/${objective.required_count})</li>`;
                });
                html += `</ul></div>`;
                
                const allCompleted = quest.objectives.every(o => o.completed);
                if (allCompleted) {
                    html += `<button onclick="completeQuest(${quest.character_quest_id})" class="w-full px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 font-medium">Сдать квест</button>`;
                } else {
                    html += `<button disabled class="w-full px-4 py-2 bg-gray-400 text-white rounded cursor-not-allowed font-medium">Не все цели выполнены</button>`;
                }
                
                questDiv.innerHTML = html;
                container.appendChild(questDiv);
            });
        } else {
            document.getElementById('npcQuestsToTurnIn').classList.add('hidden');
        }

        // Доступные квесты
        if (availableQuests.length > 0) {
            document.getElementById('npcQuestsAvailable').classList.remove('hidden');
            const container = document.getElementById('npcQuestsAvailableList');
            container.innerHTML = '';
            
            availableQuests.forEach(quest => {
                const questDiv = document.createElement('div');
                questDiv.className = 'border border-gray-200 dark:border-gray-700 rounded-lg p-4';
                let html = `<h4 class="font-semibold text-lg mb-2">${escapeHtml(quest.name)}</h4>`;
                html += `<p class="text-sm text-gray-600 dark:text-gray-400 mb-3">${escapeHtml(quest.description)}</p>`;
                
                if (quest.min_level || quest.max_level) {
                    html += `<p class="text-xs text-gray-500 dark:text-gray-500 mb-2">Уровень: ${quest.min_level || 1} - ${quest.max_level || '∞'}</p>`;
                }
                
                html += `<div class="mb-3"><strong class="text-sm">Цели:</strong><ul class="list-disc list-inside mt-1 text-sm">`;
                quest.objectives.forEach(objective => {
                    html += `<li>${escapeHtml(objective.description)} (${objective.required_count})</li>`;
                });
                html += `</ul></div>`;
                
                html += `<button onclick="acceptQuest(${quest.id})" class="w-full px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-medium">Принять квест</button>`;
                
                questDiv.innerHTML = html;
                container.appendChild(questDiv);
            });
        } else {
            document.getElementById('npcQuestsAvailable').classList.add('hidden');
        }
    }

    // Принять квест
    async function acceptQuest(questId) {
        try {
            const response = await fetch('{{ route("api.quests.accept") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    quest_id: questId
                })
            });

            const data = await response.json();

            if (response.ok) {
                alert('Квест принят!');
                // Обновляем данные NPC
                await interactWithNpc(currentNpcId, currentNpcData.npc.name);
            } else {
                alert(data.error || 'Ошибка при принятии квеста');
            }
        } catch (error) {
            console.error('Error accepting quest:', error);
            alert('Произошла ошибка при принятии квеста');
        }
    }

    // Завершить квест
    async function completeQuest(characterQuestId) {
        try {
            const response = await fetch('{{ route("api.quests.complete") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    character_quest_id: characterQuestId
                })
            });

            const data = await response.json();

            if (response.ok) {
                alert('Квест завершен!');
                // Обновляем данные NPC
                await interactWithNpc(currentNpcId, currentNpcData.npc.name);
            } else {
                alert(data.error || 'Ошибка при завершении квеста');
            }
        } catch (error) {
            console.error('Error completing quest:', error);
            alert('Произошла ошибка при завершении квеста');
        }
    }

    // Заполнить вкладку "Торговля"
    function populateNpcTrade(data) {
        document.getElementById('npcTradePlayerGold').textContent = data.character.gold || 0;
        
        const items = data.merchant_items || [];
        
        if (items.length === 0) {
            document.getElementById('npcTradeNoItems').classList.remove('hidden');
            document.getElementById('npcTradeItems').innerHTML = '';
        } else {
            document.getElementById('npcTradeNoItems').classList.add('hidden');
            // Сохраняем все товары для фильтрации
            window.npcTradeAllItems = items;
            renderNpcTradeItems(items);
        }

        // Сохраняем данные NPC для использования в функциях продажи
        if (!window.currentNpcData) {
            window.currentNpcData = data;
        } else {
            // Обновляем данные, сохраняя merchant_items
            window.currentNpcData = data;
        }

        // Загружаем инвентарь игрока для продажи только если открыта вкладка продажи
        const sellTabContent = document.getElementById('tradeSubTabSellContent');
        if (sellTabContent && !sellTabContent.classList.contains('hidden')) {
            window.playerInventoryLoaded = false;
            loadPlayerInventoryForSell();
        }
    }

    // Обновить только данные торговли без перезагрузки всего модального окна
    async function refreshNpcTradeData(npcId) {
        try {
            const response = await fetch(`{{ route('api.npcs.show', ['npc' => '__NPC_ID__']) }}`.replace('__NPC_ID__', npcId), {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (response.ok) {
                // Обновляем только данные торговли
                populateNpcTrade(data);
            }
        } catch (error) {
            console.error('Error refreshing trade data:', error);
        }
    }

    // Переключение подвкладок Купить/Продать
    function switchTradeSubTab(subTab) {
        // Обновляем стили кнопок
        const buyBtn = document.getElementById('tradeSubTabBuy');
        const sellBtn = document.getElementById('tradeSubTabSell');
        
        if (subTab === 'buy') {
            buyBtn.classList.add('border-blue-600', 'text-blue-600', 'dark:text-blue-400', 'font-medium');
            buyBtn.classList.remove('border-transparent', 'text-gray-600', 'dark:text-gray-400');
            sellBtn.classList.remove('border-blue-600', 'text-blue-600', 'dark:text-blue-400', 'font-medium');
            sellBtn.classList.add('border-transparent', 'text-gray-600', 'dark:text-gray-400');
            
            document.getElementById('tradeSubTabBuyContent').classList.remove('hidden');
            document.getElementById('tradeSubTabSellContent').classList.add('hidden');
        } else {
            sellBtn.classList.add('border-blue-600', 'text-blue-600', 'dark:text-blue-400', 'font-medium');
            sellBtn.classList.remove('border-transparent', 'text-gray-600', 'dark:text-gray-400');
            buyBtn.classList.remove('border-blue-600', 'text-blue-600', 'dark:text-blue-400', 'font-medium');
            buyBtn.classList.add('border-transparent', 'text-gray-600', 'dark:text-gray-400');
            
            document.getElementById('tradeSubTabSellContent').classList.remove('hidden');
            document.getElementById('tradeSubTabBuyContent').classList.add('hidden');
            
            // Загружаем инвентарь при переключении на вкладку продажи
            if (!window.playerInventoryLoaded) {
                loadPlayerInventoryForSell();
            }
        }
    }

    // Загрузить инвентарь игрока для продажи
    async function loadPlayerInventoryForSell() {
        try {
            // Загружаем инвентарь
            const inventoryResponse = await fetch(`{{ route('api.characters.inventory', ['character' => $character->id]) }}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });

            // Загружаем экипировку
            const equipmentResponse = await fetch(`{{ route('api.characters.equipment', ['character' => $character->id]) }}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });

            const inventoryData = await inventoryResponse.json();
            const equipmentData = await equipmentResponse.json();

            if (inventoryResponse.ok && equipmentResponse.ok) {
                window.playerInventoryLoaded = true;
                
                // Объединяем предметы из инвентаря и экипировки
                const inventoryItems = Array.isArray(inventoryData) ? inventoryData : [];
                const equipmentItems = [];
                
                // Преобразуем экипировку в формат itemInstance
                // equipmentData - это объект, где ключи - слоты, значения - CharacterEquipment
                if (equipmentData && typeof equipmentData === 'object') {
                    Object.values(equipmentData).forEach(equipment => {
                        if (equipment && equipment.item_instance) {
                            const itemInstance = equipment.item_instance;
                            if (itemInstance && itemInstance.item) {
                                equipmentItems.push({
                                    id: itemInstance.id,
                                    quantity: itemInstance.quantity || 1,
                                    item: itemInstance.item,
                                    location_type: 'equipped',
                                    slot: equipment.slot
                                });
                            }
                        }
                    });
                }
                
                // Объединяем все предметы
                const allItems = [
                    ...inventoryItems.map(item => ({
                        ...item,
                        location_type: 'inventory'
                    })),
                    ...equipmentItems
                ];
                
                window.playerInventoryAllItems = allItems;
                renderPlayerInventoryForSell(allItems);
            } else {
                document.getElementById('npcSellNoItems').textContent = 'Ошибка загрузки инвентаря';
            }
        } catch (error) {
            console.error('Error loading inventory:', error);
            document.getElementById('npcSellNoItems').textContent = 'Ошибка загрузки инвентаря';
        }
    }

    // Отобразить предметы инвентаря для продажи
    function renderPlayerInventoryForSell(items) {
        const container = document.getElementById('npcSellItems');
        container.innerHTML = '';
        
        if (items.length === 0) {
            document.getElementById('npcSellNoItems').classList.remove('hidden');
            document.getElementById('npcSellNoItems').textContent = 'В вашем инвентаре нет предметов для продажи';
            return;
        }

        document.getElementById('npcSellNoItems').classList.add('hidden');
        
        items.forEach(itemInstance => {
            const item = itemInstance.item;
            const itemDiv = document.createElement('div');
            itemDiv.className = 'border border-gray-200 dark:border-gray-700 rounded-lg p-4';
            itemDiv.dataset.itemType = item.type || '';
            itemDiv.dataset.itemName = (item.name || '').toLowerCase();
            
            const rarityColors = {
                'common': 'text-gray-600 dark:text-gray-400',
                'uncommon': 'text-green-600 dark:text-green-400',
                'rare': 'text-blue-600 dark:text-blue-400',
                'epic': 'text-purple-600 dark:text-purple-400',
                'legendary': 'text-orange-600 dark:text-orange-400',
            };
            const rarityColor = rarityColors[item.rarity] || rarityColors.common;
            
            // Получаем цену продажи от торговца
            const sellPrice = getSellPriceForItem(item, itemInstance.quantity || 1);
            
            const isEquipped = itemInstance.location_type === 'equipped';
            const equippedBadge = isEquipped ? '<span class="text-xs px-2 py-1 rounded bg-purple-100 dark:bg-purple-800">Экипировано</span>' : '';
            
            let html = `<div class="flex justify-between items-start mb-2">`;
            html += `<div class="flex-1">`;
            html += `<h4 class="font-semibold ${rarityColor}">${escapeHtml(item.name)}</h4>`;
            html += `<p class="text-sm text-gray-600 dark:text-gray-400">${escapeHtml(item.description || '')}</p>`;
            html += `<div class="flex gap-2 mt-2">`;
            html += `<span class="text-xs px-2 py-1 rounded bg-gray-100 dark:bg-gray-800">${escapeHtml(item.type)}</span>`;
            if (equippedBadge) {
                html += equippedBadge;
            }
            if (itemInstance.quantity > 1) {
                html += `<span class="text-xs px-2 py-1 rounded bg-blue-100 dark:bg-blue-800">Количество: ${itemInstance.quantity}</span>`;
            }
            html += `</div>`;
            html += `</div>`;
            html += `<div class="text-right">`;
            if (sellPrice > 0) {
                html += `<div class="text-lg font-bold text-green-600 dark:text-green-400">${sellPrice} золота</div>`;
                if (itemInstance.quantity > 1) {
                    const totalPrice = sellPrice * itemInstance.quantity;
                    html += `<div class="text-xs text-gray-500">Всего: ${totalPrice} золота</div>`;
                }
                html += `<div class="flex gap-2 mt-2">`;
                html += `<button onclick="showTradeItemDetailsFromInventory(${itemInstance.id})" class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-medium">Осмотреть</button>`;
                html += `<button onclick="sellItemToMerchant(${itemInstance.id}, ${currentNpcId}, ${itemInstance.quantity || 1})" class="px-3 py-1 bg-green-600 hover:bg-green-700 text-white rounded text-xs font-medium">Продать${itemInstance.quantity > 1 ? ' все' : ''}</button>`;
                html += `</div>`;
            } else {
                html += `<div class="text-sm text-red-600 dark:text-red-400 mb-2">Торговец не покупает</div>`;
                html += `<button onclick="showTradeItemDetailsFromInventory(${itemInstance.id})" class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-medium">Осмотреть</button>`;
            }
            html += `</div>`;
            html += `</div>`;
            
            itemDiv.innerHTML = html;
            container.appendChild(itemDiv);
        });
    }

    // Получить цену продажи предмета торговцу
    function getSellPriceForItem(item, quantity) {
        if (!currentNpcData || !currentNpcData.npc.is_merchant) {
            return 0;
        }
        
        // Проверяем, покупает ли торговец этот тип
        const npc = currentNpcData.npc;
        if (npc.merchant_buy_types && npc.merchant_buy_types.length > 0) {
            if (!npc.merchant_buy_types.includes(item.type)) {
                return 0;
            }
        }
        
        // Ищем цену в merchant_items (если торговец уже покупал этот предмет)
        if (currentNpcData.merchant_items) {
            const merchantItem = currentNpcData.merchant_items.find(mi => mi.item_id === item.id);
            if (merchantItem && merchantItem.sell_price > 0) {
                return merchantItem.sell_price;
            }
        }
        
        // Если тип разрешен, но предмета еще нет в ассортименте, используем базовую цену (30% от стоимости)
        // Запись будет создана автоматически при продаже
        return Math.floor((item.value || 0) * 0.3);
    }

    // Фильтрация предметов инвентаря для продажи
    function filterPlayerInventoryItems() {
        const search = document.getElementById('npcSellSearch').value.toLowerCase();
        const filter = document.getElementById('npcSellFilter').value;
        const allItems = window.playerInventoryAllItems || [];
        
        let filtered = allItems.filter(itemInstance => {
            const item = itemInstance.item;
            const matchesSearch = !search || (item.name || '').toLowerCase().includes(search);
            const matchesFilter = !filter || item.type === filter;
            return matchesSearch && matchesFilter;
        });
        
        renderPlayerInventoryForSell(filtered);
    }

    // Продать предмет торговцу
    async function sellItemToMerchant(itemInstanceId, npcId, maxQuantity = 1) {
        try {
            let quantity = maxQuantity;
            
            // Если предмет стакуемый и количество больше 1, спрашиваем сколько продать
            if (maxQuantity > 1) {
                const input = prompt(`Сколько предметов продать? (доступно: ${maxQuantity})`, maxQuantity.toString());
                if (input === null) {
                    return; // Пользователь отменил
                }
                quantity = parseInt(input);
                if (isNaN(quantity) || quantity < 1 || quantity > maxQuantity) {
                    alert('Укажите корректное количество (от 1 до ' + maxQuantity + ')');
                    return;
                }
            }

            const response = await fetch('{{ route("api.trade.sell") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    npc_id: npcId,
                    item_instance_id: itemInstanceId,
                    quantity: quantity
                })
            });

            const data = await response.json();

            if (response.ok) {
                alert(`Предмет успешно продан! Получено: ${data.total_price} золота`);
                
                // Обновляем золото игрока
                if (data.character_gold !== undefined) {
                    document.getElementById('npcTradePlayerGold').textContent = data.character_gold;
                    // Обновляем данные в currentNpcData
                    if (currentNpcData && currentNpcData.character) {
                        currentNpcData.character.gold = data.character_gold;
                    }
                }
                
                // Обновляем только инвентарь для продажи, не закрывая окно
                window.playerInventoryLoaded = false;
                await loadPlayerInventoryForSell();
            } else {
                alert(data.error || 'Ошибка при продаже предмета');
            }
        } catch (error) {
            console.error('Error selling item:', error);
            alert('Произошла ошибка при продаже предмета');
        }
    }

    // Отобразить товары торговца
    function renderNpcTradeItems(items) {
        const container = document.getElementById('npcTradeItems');
        container.innerHTML = '';
        
        items.forEach(item => {
            const itemDiv = document.createElement('div');
            itemDiv.className = 'border border-gray-200 dark:border-gray-700 rounded-lg p-4';
            itemDiv.dataset.itemType = item.type || '';
            itemDiv.dataset.itemName = (item.name || '').toLowerCase();
            
            const rarityColors = {
                'common': 'text-gray-600 dark:text-gray-400',
                'uncommon': 'text-green-600 dark:text-green-400',
                'rare': 'text-blue-600 dark:text-blue-400',
                'epic': 'text-purple-600 dark:text-purple-400',
                'legendary': 'text-orange-600 dark:text-orange-400',
            };
            const rarityColor = rarityColors[item.rarity] || rarityColors.common;
            
            let html = `<div class="flex justify-between items-start mb-2">`;
            html += `<div class="flex-1">`;
            html += `<h4 class="font-semibold ${rarityColor}">${escapeHtml(item.name)}</h4>`;
            html += `<p class="text-sm text-gray-600 dark:text-gray-400">${escapeHtml(item.description || '')}</p>`;
            html += `<div class="flex gap-2 mt-2">`;
            html += `<span class="text-xs px-2 py-1 rounded bg-gray-100 dark:bg-gray-800">${escapeHtml(item.type)}</span>`;
            if (item.quantity !== undefined && item.max_quantity > 0) {
                html += `<span class="text-xs px-2 py-1 rounded bg-blue-100 dark:bg-blue-800">В наличии: ${item.quantity}</span>`;
            }
            html += `</div>`;
            html += `</div>`;
            html += `<div class="text-right">`;
            html += `<div class="text-lg font-bold text-yellow-600 dark:text-yellow-400">${item.current_price || 0} золота</div>`;
            if (item.base_price !== item.current_price) {
                html += `<div class="text-xs text-gray-500 line-through">${item.base_price} золота</div>`;
            }
            html += `<div class="flex gap-2 mt-2">`;
            html += `<button onclick="showTradeItemDetailsFromMerchant(${item.item_id || item.id})" class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-medium">Осмотреть</button>`;
            const canBuy = item.quantity === undefined || item.quantity > 0;
            html += `<button onclick="buyItem(${item.id}, ${currentNpcId}, 1)" ${!canBuy ? 'disabled' : ''} class="px-3 py-1 ${canBuy ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-400 cursor-not-allowed'} text-white rounded text-xs font-medium">${canBuy ? 'Купить' : 'Нет'}</button>`;
            html += `</div>`;
            html += `</div>`;
            html += `</div>`;
            
            itemDiv.innerHTML = html;
            container.appendChild(itemDiv);
        });
    }

    // Фильтрация товаров
    function filterNpcTradeItems() {
        const search = document.getElementById('npcTradeSearch').value.toLowerCase();
        const filter = document.getElementById('npcTradeFilter').value;
        const allItems = window.npcTradeAllItems || [];
        
        let filtered = allItems.filter(item => {
            const matchesSearch = !search || (item.name || '').toLowerCase().includes(search);
            const matchesFilter = !filter || item.type === filter;
            return matchesSearch && matchesFilter;
        });
        
        renderNpcTradeItems(filtered);
    }

    // Купить предмет
    async function buyItem(merchantInventoryId, npcId, quantity = 1) {
        try {
            const response = await fetch('{{ route("api.trade.buy") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    npc_id: npcId,
                    merchant_inventory_id: merchantInventoryId,
                    quantity: quantity
                })
            });

            const data = await response.json();

            if (response.ok) {
                alert(`Предмет успешно куплен! Потрачено: ${data.total_price} золота`);
                // Обновляем золото игрока
                if (data.character_gold !== undefined) {
                    document.getElementById('npcTradePlayerGold').textContent = data.character_gold;
                    // Обновляем данные в currentNpcData
                    if (currentNpcData && currentNpcData.character) {
                        currentNpcData.character.gold = data.character_gold;
                    }
                }
                
                // Обновляем только данные торговли без перезагрузки всего окна
                await refreshNpcTradeData(npcId);
            } else {
                alert(data.error || 'Ошибка при покупке предмета');
            }
        } catch (error) {
            console.error('Error buying item:', error);
            alert('Произошла ошибка при покупке предмета');
        }
    }

    // Заполнить вкладку "Обучение"
    function populateNpcTraining(data) {
        const skills = data.teachable_skills || [];
        
        if (skills.length === 0) {
            document.getElementById('npcTrainingNoSkills').classList.remove('hidden');
            document.getElementById('npcTrainingSkills').innerHTML = '';
            return;
        }

        document.getElementById('npcTrainingNoSkills').classList.add('hidden');
        const container = document.getElementById('npcTrainingSkills');
        container.innerHTML = '';
        
        skills.forEach(skill => {
            const skillDiv = document.createElement('div');
            skillDiv.className = 'border border-gray-200 dark:border-gray-700 rounded-lg p-4';
            
            let html = `<h4 class="font-semibold text-lg mb-2">${escapeHtml(skill.name)}</h4>`;
            html += `<p class="text-sm text-gray-600 dark:text-gray-400 mb-3">${escapeHtml(skill.description || '')}</p>`;
            
            html += `<div class="grid grid-cols-2 gap-4 mb-3 text-sm">`;
            html += `<div><strong>Категория:</strong> ${escapeHtml(skill.category || 'Общее')}</div>`;
            html += `<div><strong>Текущий уровень:</strong> ${skill.current_level} / ${skill.max_level}</div>`;
            html += `<div><strong>Требуемый уровень персонажа:</strong> ${skill.required_level || 1}</div>`;
            if (skill.attribute_requirements) {
                const reqs = Object.entries(skill.attribute_requirements).map(([attr, val]) => {
                    const attrNames = {
                        'strength': 'Сила',
                        'agility': 'Ловкость',
                        'intelligence': 'Интеллект'
                    };
                    return `${attrNames[attr] || attr}: ${val}`;
                }).join(', ');
                html += `<div><strong>Требования:</strong> ${reqs}</div>`;
            }
            html += `</div>`;
            
            if (skill.can_learn) {
                html += `<button onclick="learnSkill(${skill.id})" class="w-full px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-medium">Изучить / Улучшить</button>`;
            } else {
                html += `<button disabled class="w-full px-4 py-2 bg-gray-400 text-white rounded cursor-not-allowed font-medium">Достигнут максимальный уровень</button>`;
            }
            
            skillDiv.innerHTML = html;
            container.appendChild(skillDiv);
        });
    }

    // Изучить навык
    async function learnSkill(skillId) {
        // TODO: Реализовать изучение навыков
        alert(`Изучение навыка (ID: ${skillId}) - будет реализовано позже`);
    }

    // Заполнить вкладку "Банк"
    function populateNpcBank(data) {
        const banker = data.banker;
        
        if (!banker) {
            return;
        }

        // Обновляем информацию о хранилище
        document.getElementById('npcBankPlayerGold').textContent = data.character.gold || 0;
        document.getElementById('npcBankUsedSlots').textContent = banker.used_slots || 0;
        document.getElementById('npcBankFreeSlots').textContent = banker.free_slots || 0;
        document.getElementById('npcBankTotalSlots').textContent = banker.total_slots || 0;
        document.getElementById('npcBankStorageFee').textContent = `${banker.current_fee || 0} золота`;

        // Сохраняем данные банкира
        window.currentBankerData = banker;

        // Сбрасываем флаг загрузки инвентаря при обновлении данных банкира
        // Инвентарь будет загружен при открытии подвкладки депозита
        window.bankInventoryLoaded = false;

        // Загружаем хранилище для изъятия, если открыта вкладка изъятия
        const withdrawTabContent = document.getElementById('bankSubTabWithdrawContent');
        if (withdrawTabContent && !withdrawTabContent.classList.contains('hidden')) {
            renderBankStorage(banker.storages || []);
        }

        // Обновляем информацию об улучшениях
        populateBankUpgrades(banker);
    }

    // Переключение подвкладок банка
    function switchBankSubTab(subTab) {
        const depositBtn = document.getElementById('bankSubTabDeposit');
        const withdrawBtn = document.getElementById('bankSubTabWithdraw');
        const upgradeBtn = document.getElementById('bankSubTabUpgrade');
        
        // Сбрасываем стили всех кнопок
        [depositBtn, withdrawBtn, upgradeBtn].forEach(btn => {
            btn.classList.remove('border-blue-600', 'text-blue-600', 'dark:text-blue-400', 'font-medium');
            btn.classList.add('border-transparent', 'text-gray-600', 'dark:text-gray-400');
        });

        // Скрываем все подвкладки
        document.getElementById('bankSubTabDepositContent').classList.add('hidden');
        document.getElementById('bankSubTabWithdrawContent').classList.add('hidden');
        document.getElementById('bankSubTabUpgradeContent').classList.add('hidden');

        if (subTab === 'deposit') {
            depositBtn.classList.add('border-blue-600', 'text-blue-600', 'dark:text-blue-400', 'font-medium');
            depositBtn.classList.remove('border-transparent', 'text-gray-600', 'dark:text-gray-400');
            document.getElementById('bankSubTabDepositContent').classList.remove('hidden');
            
            // Проверяем, нужно ли загрузить инвентарь
            const inventoryContainer = document.getElementById('npcBankDepositInventory');
            const needsLoad = !window.bankInventoryLoaded || 
                            !inventoryContainer || 
                            inventoryContainer.innerHTML.includes('Загрузка') ||
                            inventoryContainer.innerHTML.trim() === '';
            
            if (needsLoad) {
                window.bankInventoryLoaded = false;
                loadPlayerInventoryForDeposit();
            }
        } else if (subTab === 'withdraw') {
            withdrawBtn.classList.add('border-blue-600', 'text-blue-600', 'dark:text-blue-400', 'font-medium');
            withdrawBtn.classList.remove('border-transparent', 'text-gray-600', 'dark:text-gray-400');
            document.getElementById('bankSubTabWithdrawContent').classList.remove('hidden');
            
            if (window.currentBankerData) {
                renderBankStorage(window.currentBankerData.storages || []);
            }
        } else if (subTab === 'upgrade') {
            upgradeBtn.classList.add('border-blue-600', 'text-blue-600', 'dark:text-blue-400', 'font-medium');
            upgradeBtn.classList.remove('border-transparent', 'text-gray-600', 'dark:text-gray-400');
            document.getElementById('bankSubTabUpgradeContent').classList.remove('hidden');
            
            if (window.currentBankerData) {
                populateBankUpgrades(window.currentBankerData);
            }
        }
    }

    // Загрузить инвентарь игрока для депозита
    async function loadPlayerInventoryForDeposit() {
        const container = document.getElementById('npcBankDepositInventory');
        if (!container) {
            console.error('Bank deposit inventory container not found');
            return;
        }

        // Показываем индикатор загрузки
        container.innerHTML = '<p class="text-center py-8 text-gray-500 dark:text-gray-400">Загрузка инвентаря...</p>';

        try {
            const response = await fetch(`{{ route('api.characters.inventory', ['character' => $character->id]) }}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (response.ok) {
                window.bankInventoryLoaded = true;
                renderPlayerInventoryForDeposit(data.items || []);
            } else {
                window.bankInventoryLoaded = false;
                container.innerHTML = 
                    '<p class="text-center py-8 text-red-500">Ошибка загрузки инвентаря: ' + (data.error || 'Неизвестная ошибка') + '</p>';
            }
        } catch (error) {
            window.bankInventoryLoaded = false;
            console.error('Error loading inventory for deposit:', error);
            container.innerHTML = 
                '<p class="text-center py-8 text-red-500">Ошибка загрузки инвентаря: ' + error.message + '</p>';
        }
    }

    // Отобразить инвентарь для депозита
    function renderPlayerInventoryForDeposit(items) {
        const container = document.getElementById('npcBankDepositInventory');
        
        if (!items || items.length === 0) {
            container.innerHTML = '<p class="text-center py-8 text-gray-500 dark:text-gray-400">Инвентарь пуст</p>';
            return;
        }

        container.innerHTML = items.map(item => {
            const rarityColors = {
                'common': 'text-gray-600 dark:text-gray-400',
                'uncommon': 'text-green-600 dark:text-green-400',
                'rare': 'text-blue-600 dark:text-blue-400',
                'epic': 'text-purple-600 dark:text-purple-400',
                'legendary': 'text-orange-600 dark:text-orange-400',
            };
            const rarityColor = rarityColors[item.rarity] || rarityColors.common;
            const quantityHtml = item.quantity > 1 ? ` x${item.quantity}` : '';

            return `
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 flex justify-between items-center">
                    <div class="flex-1">
                        <h4 class="font-semibold ${rarityColor}">${escapeHtml(item.name)}${quantityHtml}</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">${escapeHtml(item.description || '')}</p>
                    </div>
                    <button
                        onclick="depositItem(${item.id}, ${item.quantity || 1})"
                        class="ml-4 px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 font-medium"
                    >
                        Депозит
                    </button>
                </div>
            `;
        }).join('');
    }

    // Депозит предмета в банк
    async function depositItem(itemInstanceId, maxQuantity = 1) {
        try {
            if (!window.currentBankerData) {
                alert('Ошибка: данные банкира не загружены');
                return;
            }

            let quantity = maxQuantity;
            
            if (maxQuantity > 1) {
                const input = prompt(`Сколько предметов поместить в банк? (доступно: ${maxQuantity})`, maxQuantity.toString());
                if (input === null) return;
                quantity = parseInt(input);
                if (isNaN(quantity) || quantity < 1 || quantity > maxQuantity) {
                    alert('Укажите корректное количество (от 1 до ' + maxQuantity + ')');
                    return;
                }
            }

            const response = await fetch('{{ route("api.bank.deposit") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    banker_id: window.currentBankerData.id,
                    item_instance_id: itemInstanceId,
                    quantity: quantity
                })
            });

            const data = await response.json();

            if (response.ok) {
                alert(`Предмет успешно помещен в банк! Плата за хранение: ${data.storage_fee || 0} золота`);
                
                // Обновляем данные
                await refreshBankData();
            } else {
                alert(data.error || 'Ошибка при депозите предмета');
            }
        } catch (error) {
            console.error('Error depositing item:', error);
            alert('Произошла ошибка при депозите предмета');
        }
    }

    // Отобразить хранилище банка
    function renderBankStorage(storages) {
        const container = document.getElementById('npcBankStorageSlots');
        
        if (!storages || storages.length === 0) {
            container.innerHTML = '<p class="text-center py-8 text-gray-500 dark:text-gray-400 col-span-full">Хранилище пусто</p>';
            return;
        }

        // Создаем сетку слотов
        const maxSlot = Math.max(...storages.map(s => s.slot_number || 0), 0);
        const slots = Array.from({ length: Math.max(maxSlot, 20) }, (_, i) => {
            const slotNumber = i + 1;
            const storage = storages.find(s => s.slot_number === slotNumber);
            return storage || { slot_number: slotNumber, item_instance: null, is_locked: false };
        });

        container.innerHTML = slots.map(storage => {
            if (storage.item_instance) {
                const item = storage.item_instance.item;
                return `
                    <div class="border ${storage.is_locked ? 'border-yellow-500 bg-yellow-50 dark:bg-yellow-900/20' : 'border-gray-300 dark:border-gray-700'} rounded p-2 text-center">
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">#${storage.slot_number}</div>
                        <div class="text-xs font-medium mb-1">${escapeHtml(item.name)}</div>
                        <div class="text-xs text-gray-600 dark:text-gray-400 mb-2">x${storage.item_instance.quantity}</div>
                        ${storage.is_locked ? '<div class="text-xs text-yellow-600 dark:text-yellow-400 mb-2">Заблокировано</div>' : ''}
                        ${!storage.is_locked ? `<button onclick="withdrawItem(${storage.id})" class="w-full text-xs px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700">Изъять</button>` : ''}
                    </div>
                `;
            } else {
                return `
                    <div class="border border-gray-300 dark:border-gray-700 rounded p-2 text-center">
                        <div class="text-xs text-gray-500 dark:text-gray-400">#${storage.slot_number}</div>
                        <div class="text-xs text-gray-400 mt-2">Пусто</div>
                    </div>
                `;
            }
        }).join('');
    }

    // Изъять предмет из банка
    async function withdrawItem(storageId) {
        try {
            if (!window.currentBankerData) {
                alert('Ошибка: данные банкира не загружены');
                return;
            }

            const storage = (window.currentBankerData.storages || []).find(s => s.id === storageId);
            if (!storage || !storage.item_instance) {
                alert('Предмет не найден');
                return;
            }

            let quantity = storage.item_instance.quantity;
            if (quantity > 1) {
                const input = prompt(`Сколько предметов изъять? (доступно: ${quantity})`, quantity.toString());
                if (input === null) return;
                quantity = parseInt(input);
                if (isNaN(quantity) || quantity < 1 || quantity > storage.item_instance.quantity) {
                    alert('Укажите корректное количество (от 1 до ' + storage.item_instance.quantity + ')');
                    return;
                }
            }

            const response = await fetch('{{ route("api.bank.withdraw") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    banker_id: window.currentBankerData.id,
                    storage_id: storageId,
                    quantity: quantity
                })
            });

            const data = await response.json();

            if (response.ok) {
                alert(`Предмет успешно изъят из банка!`);
                
                // Обновляем данные
                await refreshBankData();
            } else {
                alert(data.error || 'Ошибка при изъятии предмета');
            }
        } catch (error) {
            console.error('Error withdrawing item:', error);
            alert('Произошла ошибка при изъятии предмета');
        }
    }

    // Заполнить информацию об улучшениях
    function populateBankUpgrades(banker) {
        const container = document.getElementById('npcBankUpgradesInfo');
        const upgrades = banker.upgrades || [];
        
        if (upgrades.length === 0) {
            container.innerHTML = '<p class="text-gray-600 dark:text-gray-400">У вас нет активных улучшений</p>';
        } else {
            container.innerHTML = upgrades.map(upgrade => {
                const expiresText = upgrade.is_permanent 
                    ? 'Постоянное' 
                    : `Истекает: ${new Date(upgrade.expires_at).toLocaleDateString()}`;
                return `
                    <div class="flex justify-between items-center p-2 bg-white dark:bg-gray-700 rounded">
                        <span>+${upgrade.additional_slots} слотов</span>
                        <span class="text-xs text-gray-500">${expiresText}</span>
                    </div>
                `;
            }).join('');
        }
    }

    // Рассчитать стоимость улучшения
    function calculateUpgradeCost() {
        const slots = parseInt(document.getElementById('bankUpgradeSlots').value) || 1;
        const baseCost = 1000; // Базовая стоимость за слот
        const totalCost = baseCost * slots;
        
        document.getElementById('bankUpgradeCost').innerHTML = 
            `<strong>Стоимость улучшения: ${totalCost} золота</strong>`;
    }

    // Купить улучшение хранилища
    async function purchaseBankUpgrade() {
        try {
            if (!window.currentBankerData) {
                alert('Ошибка: данные банкира не загружены');
                return;
            }

            const slots = parseInt(document.getElementById('bankUpgradeSlots').value) || 1;
            const baseCost = 1000;
            const totalCost = baseCost * slots;

            if (!confirm(`Купить ${slots} дополнительных слотов за ${totalCost} золота?`)) {
                return;
            }

            const response = await fetch('{{ route("api.bank.upgrade") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    banker_id: window.currentBankerData.id,
                    additional_slots: slots
                })
            });

            const data = await response.json();

            if (response.ok) {
                alert(`Улучшение успешно приобретено!`);
                
                // Обновляем данные
                await refreshBankData();
            } else {
                alert(data.error || 'Ошибка при покупке улучшения');
            }
        } catch (error) {
            console.error('Error purchasing upgrade:', error);
            alert('Произошла ошибка при покупке улучшения');
        }
    }

    // Обновить данные банка
    async function refreshBankData() {
        try {
            if (!currentNpcId) return;

            const response = await fetch(`{{ route('api.npcs.show', ['npc' => '__NPC_ID__']) }}`.replace('__NPC_ID__', currentNpcId), {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (response.ok) {
                // Обновляем данные банка
                populateNpcBank(data);
                
                // Обновляем золото
                if (data.character && data.character.gold !== undefined) {
                    document.getElementById('npcBankPlayerGold').textContent = data.character.gold;
                }

                // Обновляем текущие данные
                if (currentNpcData) {
                    currentNpcData.character = data.character;
                    currentNpcData.banker = data.banker;
                }
                window.currentBankerData = data.banker;

                // Перезагружаем инвентарь для депозита
                window.bankInventoryLoaded = false;
                if (document.getElementById('bankSubTabDepositContent') && 
                    !document.getElementById('bankSubTabDepositContent').classList.contains('hidden')) {
                    await loadPlayerInventoryForDeposit();
                }

                // Обновляем хранилище для изъятия
                if (document.getElementById('bankSubTabWithdrawContent') && 
                    !document.getElementById('bankSubTabWithdrawContent').classList.contains('hidden')) {
                    renderBankStorage(data.banker?.storages || []);
                }
            }
        } catch (error) {
            console.error('Error refreshing bank data:', error);
        }
    }

    // Закрытие модального окна по клику вне его
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('npcInteractionModal');
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeNpcInteractionModal();
                }
            });
        }
    });

    // Функция атаки на NPC
    function attackNpc(npcId, npcName) {
        // TODO: Реализовать атаку на NPC
        alert(`Атака на ${npcName} (ID: ${npcId}) - будет реализовано позже`);
    }

    // Функция отображения предметов
    function renderItems(items) {
        if (!items || items.length === 0) {
            document.getElementById('items-content').innerHTML = 
                '<p class="text-gray-600 dark:text-gray-400">В локации нет предметов</p>';
            return;
        }

        const itemsHtml = items.map(item => {
            const rarityColors = {
                'common': 'text-gray-600 dark:text-gray-400',
                'uncommon': 'text-green-600 dark:text-green-400',
                'rare': 'text-blue-600 dark:text-blue-400',
                'epic': 'text-purple-600 dark:text-purple-400',
                'legendary': 'text-orange-600 dark:text-orange-400',
            };

            const rarityColor = rarityColors[item.rarity] || rarityColors.common;
            const rarityName = getRarityName(item.rarity);

            const expiresHtml = item.expires_at 
                ? `<p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                    Исчезнет через: ${formatTime(item.expires_in)}
                   </p>`
                : '';

            const quantityHtml = item.quantity > 1 
                ? `<span class="ml-2 text-sm text-gray-600 dark:text-gray-400">x${item.quantity}</span>`
                : '';

            const buttonsHtml = `
                <div class="flex gap-2 mt-3">
                    <button
                        onclick="showGroundItemDetails(${item.id})"
                        class="flex-1 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm font-medium"
                    >
                        Осмотреть
                    </button>
                    <button
                        onclick="pickUpItem(${item.id}, ${item.quantity}, ${item.stackable ? 'true' : 'false'})"
                        class="flex-1 px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm font-medium"
                    >
                        Поднять
                    </button>
                </div>
            `;

            return `
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-3">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h3 class="font-medium ${rarityColor}">${escapeHtml(item.name)} ${quantityHtml}</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">${escapeHtml(item.description || '')}</p>
                            <div class="flex items-center gap-2 mt-2">
                                <span class="text-xs px-2 py-1 rounded bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400">
                                    ${escapeHtml(item.type)}${item.subtype ? ' / ' + escapeHtml(item.subtype) : ''}
                                </span>
                                <span class="text-xs px-2 py-1 rounded ${rarityColor} bg-opacity-10">
                                    ${rarityName}
                                </span>
                            </div>
                            ${expiresHtml}
                            ${buttonsHtml}
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        document.getElementById('items-content').innerHTML = itemsHtml;
    }

    // Функция отображения трупов
    function renderCorpses(corpses) {
        if (!corpses || corpses.length === 0) {
            document.getElementById('corpses-content').innerHTML = 
                '<p class="text-gray-600 dark:text-gray-400">В локации нет трупов</p>';
            return;
        }

        const corpsesHtml = corpses.map(corpse => {
            const corpseTypeColors = {
                'innocent': 'text-blue-600 dark:text-blue-400',
                'criminal': 'text-red-600 dark:text-red-400',
                'monster': 'text-purple-600 dark:text-purple-400',
                'npc': 'text-gray-600 dark:text-gray-400',
            };

            const corpseTypeColor = corpseTypeColors[corpse.corpse_type] || corpseTypeColors.npc;
            const corpseTypeNames = {
                'innocent': 'Мирный',
                'criminal': 'Преступник',
                'monster': 'Монстр',
                'npc': 'NPC',
            };

            const corpseTypeName = corpseTypeNames[corpse.corpse_type] || 'Неизвестно';
            const belongsToMeBadge = corpse.belongs_to_me 
                ? '<span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Ваш труп</span>'
                : '';
            const lootedBadge = corpse.is_looted 
                ? '<span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200">Разграблен</span>'
                : '';

            // Проверяем, является ли текущий персонаж призраком
            const isGhost = window.currentCharacterIsGhost || false;
            
            const buttonsHtml = `
                <div class="flex gap-2 mt-3">
                    <button
                        onclick="showCorpseItems(${corpse.id})"
                        class="flex-1 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm font-medium"
                    >
                        Осмотреть
                    </button>
                    ${!corpse.is_looted && !isGhost ? `
                        <button
                            onclick="lootAllFromCorpse(${corpse.id})"
                            class="flex-1 px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm font-medium"
                        >
                            Забрать все
                        </button>
                    ` : ''}
                    ${isGhost ? `
                        <span class="flex-1 px-4 py-2 bg-gray-400 text-white rounded text-sm font-medium text-center cursor-not-allowed">
                            Призраки не могут забирать предметы
                        </span>
                    ` : ''}
                </div>
            `;

            return `
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-3">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <h3 class="font-medium ${corpseTypeColor}">Труп ${escapeHtml(corpse.character_name)}</h3>
                                ${belongsToMeBadge}
                                ${lootedBadge}
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Тип: ${corpseTypeName} | Предметов: ${corpse.total_items} | Исчезнет через: ${corpse.remaining_minutes} мин.
                            </p>
                            ${buttonsHtml}
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        document.getElementById('corpses-content').innerHTML = corpsesHtml;
    }

    // Функция отображения обычных игроков
    function renderPlayers(players) {
        if (!players || players.length === 0) {
            document.getElementById('players-content').innerHTML = 
                '<p class="text-gray-600 dark:text-gray-400">В локации нет других игроков</p>';
            return;
        }

        const playersHtml = players.map(player => {
            return `
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-3">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <h3 class="font-medium">${escapeHtml(player.name)}</h3>
                                <span class="text-xs px-2 py-1 rounded bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
                                    Уровень ${player.level}
                                </span>
                                <span class="text-xs px-2 py-1 rounded bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                                    Онлайн
                                </span>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Статус: ${player.status || 'активен'}
                            </p>
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        document.getElementById('players-content').innerHTML = playersHtml;
    }

    // Функция отображения призраков
    function renderGhosts(ghosts) {
        if (!ghosts || ghosts.length === 0) {
            document.getElementById('ghosts-content').innerHTML = 
                '<p class="text-gray-600 dark:text-gray-400">В локации нет других игроков или призраков</p>';
            return;
        }

        const ghostsHtml = ghosts.map(ghost => {
            return `
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-3 opacity-75">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <h3 class="font-medium text-purple-600 dark:text-purple-400">👻 Призрак ${escapeHtml(ghost.character_name)}</h3>
                                <span class="text-xs px-2 py-1 rounded bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200">
                                    Уровень ${ghost.level}
                                </span>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Полупрозрачная фигура бродит по локации...
                            </p>
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        document.getElementById('ghosts-content').innerHTML = ghostsHtml;
    }

    // Функция отображения камней воскрешения
    function renderResurrectionStones(stones) {
        if (!stones || stones.length === 0) {
            document.getElementById('resurrection-stones-content').innerHTML = 
                '<p class="text-gray-600 dark:text-gray-400">В локации нет камней воскрешения</p>';
            return;
        }

        const isGhost = window.currentCharacterIsGhost || false;
        const currentLevel = window.currentCharacterLevel || 1;

        const stonesHtml = stones.map(stone => {
            // Определяем иконку и цвет в зависимости от визуального эффекта
            const effectIcons = {
                'glow': '✨',
                'particles': '🌟',
                'aura': '💫',
            };
            const effectIcon = effectIcons[stone.visual_effect] || '💎';

            // Статус камня
            let statusBadge = '';
            if (!stone.is_active) {
                statusBadge = '<span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200">Неактивен</span>';
            } else if (!stone.is_available && stone.remaining_cooldown_minutes > 0) {
                statusBadge = `<span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">Перезарядка: ${stone.remaining_cooldown_minutes} мин</span>`;
            } else {
                statusBadge = '<span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Доступен</span>';
            }

            // Проверка возможности использования
            const canUse = isGhost && stone.is_active && stone.is_available && stone.can_use && currentLevel >= stone.level_required;
            const levelRequirementMet = currentLevel >= stone.level_required;

            // Кнопка взаимодействия
            let buttonHtml = '';
            if (isGhost) {
                if (canUse) {
                    buttonHtml = `
                        <button
                            onclick="interactWithResurrectionStone(${stone.id}, '${escapeHtml(stone.name)}')"
                            class="mt-3 w-full px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700 text-sm font-medium"
                        >
                            ${effectIcon} Взаимодействовать с камнем
                        </button>
                    `;
                } else if (!stone.is_active) {
                    buttonHtml = `
                        <div class="mt-3 px-4 py-2 bg-gray-400 text-white rounded text-sm font-medium text-center cursor-not-allowed">
                            Камень неактивен
                        </div>
                    `;
                } else if (!stone.is_available && stone.remaining_cooldown_minutes > 0) {
                    buttonHtml = `
                        <div class="mt-3 px-4 py-2 bg-yellow-400 text-white rounded text-sm font-medium text-center cursor-not-allowed">
                            Камень на перезарядке (${stone.remaining_cooldown_minutes} мин)
                        </div>
                    `;
                } else if (!levelRequirementMet) {
                    buttonHtml = `
                        <div class="mt-3 px-4 py-2 bg-gray-400 text-white rounded text-sm font-medium text-center cursor-not-allowed">
                            Требуется уровень ${stone.level_required}
                        </div>
                    `;
                } else {
                    buttonHtml = `
                        <div class="mt-3 px-4 py-2 bg-gray-400 text-white rounded text-sm font-medium text-center cursor-not-allowed">
                            Камень недоступен
                        </div>
                    `;
                }
            } else {
                buttonHtml = `
                    <div class="mt-3 px-4 py-2 bg-gray-400 text-white rounded text-sm font-medium text-center cursor-not-allowed">
                        Только призраки могут использовать камни воскрешения
                    </div>
                `;
            }

            // Информация о перезарядке
            const cooldownInfo = stone.cooldown_minutes > 0 
                ? `<p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Перезарядка: ${stone.cooldown_minutes} минут</p>`
                : '<p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Без перезарядки</p>';

            return `
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-3 ${isGhost && canUse ? 'border-purple-400 dark:border-purple-600 bg-purple-50 dark:bg-purple-900/20' : ''}">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-2xl">${effectIcon}</span>
                                <h3 class="font-medium text-lg">${escapeHtml(stone.name)}</h3>
                                ${statusBadge}
                            </div>
                            ${stone.description ? `<p class="text-sm text-gray-600 dark:text-gray-400 mb-2">${escapeHtml(stone.description)}</p>` : ''}
                            <div class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                                <p><span class="font-medium">Требуемый уровень:</span> ${stone.level_required}</p>
                                ${cooldownInfo}
                            </div>
                            ${buttonHtml}
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        document.getElementById('resurrection-stones-content').innerHTML = stonesHtml;
    }

    // Функция взаимодействия с камнем воскрешения
    async function interactWithResurrectionStone(stoneId, stoneName) {
        if (!confirm(`Вы уверены, что хотите использовать камень "${stoneName}" для само-воскрешения?`)) {
            return;
        }

        try {
            const response = await fetch(`/api/ghost/resurrection-stones/${stoneId}/interact`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (response.ok) {
                alert(data.message || 'Вы были успешно воскрешены!');
                // Обновляем страницу для отображения изменений
                window.location.reload();
            } else {
                alert(data.error || 'Произошла ошибка при воскрешении');
            }
        } catch (error) {
            console.error('Error interacting with resurrection stone:', error);
            alert('Произошла ошибка при взаимодействии с камнем воскрешения');
        }
    }

    // Функция показа предметов в трупе
    async function showCorpseItems(corpseId) {
        try {
            const response = await fetch(`/api/corpse/${corpseId}/items`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (response.ok) {
                const inventoryItems = data.items.inventory || [];
                const equipmentItems = data.items.equipment || [];
                const allItems = [...inventoryItems, ...equipmentItems];
                
                // Проверяем, является ли текущий персонаж призраком
                const isGhost = window.currentCharacterIsGhost || false;
                
                // Устанавливаем заголовок модального окна
                document.getElementById('corpseItemsModalTitle').textContent = 
                    `Предметы в трупе ${escapeHtml(data.corpse.character_name)}`;
                
                let itemsHtml = '';
                
                if (allItems.length === 0) {
                    itemsHtml = '<p class="text-gray-600 dark:text-gray-400 text-center py-4">В трупе нет предметов</p>';
                } else {
                    // Информация о трупе
                    const corpseInfoHtml = `
                        <div class="bg-gray-100 dark:bg-gray-800 rounded-lg p-3 mb-4">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Тип трупа:</span>
                                <span class="font-medium ${data.corpse.corpse_type === 'innocent' ? 'text-blue-600 dark:text-blue-400' : 'text-red-600 dark:text-red-400'}">
                                    ${data.corpse.corpse_type === 'innocent' ? 'Мирный' : data.corpse.corpse_type === 'criminal' ? 'Преступник' : data.corpse.corpse_type}
                                </span>
                            </div>
                            <div class="flex items-center justify-between text-sm mt-2">
                                <span class="text-gray-600 dark:text-gray-400">Исчезнет через:</span>
                                <span class="font-medium">${data.corpse.remaining_minutes} минут</span>
                            </div>
                            ${data.corpse.belongs_to_me ? `
                                <div class="mt-2 text-xs text-green-600 dark:text-green-400">
                                    ✓ Это ваш труп
                                </div>
                            ` : ''}
                        </div>
                    `;
                    
                    // Предметы из инвентаря
                    let inventorySectionHtml = '';
                    if (inventoryItems.length > 0) {
                        inventorySectionHtml = `
                            <div class="mb-4">
                                <h4 class="font-semibold mb-2 text-gray-700 dark:text-gray-300">Инвентарь (${inventoryItems.length})</h4>
                                ${inventoryItems.map(item => {
                                    const rarityColors = {
                                        'common': 'text-gray-600 dark:text-gray-400',
                                        'uncommon': 'text-green-600 dark:text-green-400',
                                        'rare': 'text-blue-600 dark:text-blue-400',
                                        'epic': 'text-purple-600 dark:text-purple-400',
                                        'legendary': 'text-orange-600 dark:text-orange-400',
                                    };
                                    const rarityColor = rarityColors[item.item_data.rarity] || rarityColors.common;
                                    
                                    return `
                                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-3 mb-2">
                                            <div class="flex items-start justify-between">
                                                <div class="flex-1">
                                                    <h4 class="font-medium ${rarityColor}">${escapeHtml(item.item_data.name)}</h4>
                                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                                        Количество: ${item.quantity} | Тип: ${escapeHtml(item.item_data.type)}${item.item_data.subtype ? ' / ' + escapeHtml(item.item_data.subtype) : ''}
                                                    </p>
                                                    ${item.durability_current !== null ? `
                                                        <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                                                            Прочность: ${item.durability_current}
                                                        </p>
                                                    ` : ''}
                                                </div>
                                                ${!isGhost && !data.corpse.is_looted ? `
                                                    <button
                                                        onclick="lootItemFromCorpse(${corpseId}, ${item.item_instance_id})"
                                                        class="ml-3 px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700 text-sm whitespace-nowrap"
                                                    >
                                                        Забрать
                                                    </button>
                                                ` : ''}
                                            </div>
                                        </div>
                                    `;
                                }).join('')}
                            </div>
                        `;
                    }
                    
                    // Предметы из экипировки
                    let equipmentSectionHtml = '';
                    if (equipmentItems.length > 0) {
                        equipmentSectionHtml = `
                            <div class="mb-4">
                                <h4 class="font-semibold mb-2 text-gray-700 dark:text-gray-300">Экипировка (${equipmentItems.length})</h4>
                                ${equipmentItems.map(item => {
                                    const rarityColors = {
                                        'common': 'text-gray-600 dark:text-gray-400',
                                        'uncommon': 'text-green-600 dark:text-green-400',
                                        'rare': 'text-blue-600 dark:text-blue-400',
                                        'epic': 'text-purple-600 dark:text-purple-400',
                                        'legendary': 'text-orange-600 dark:text-orange-400',
                                    };
                                    const rarityColor = rarityColors[item.item_data.rarity] || rarityColors.common;
                                    
                                    return `
                                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-3 mb-2">
                                            <div class="flex items-start justify-between">
                                                <div class="flex-1">
                                                    <h4 class="font-medium ${rarityColor}">${escapeHtml(item.item_data.name)}</h4>
                                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                                        Количество: ${item.quantity} | Тип: ${escapeHtml(item.item_data.type)}${item.item_data.subtype ? ' / ' + escapeHtml(item.item_data.subtype) : ''}
                                                    </p>
                                                    ${item.durability_current !== null ? `
                                                        <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                                                            Прочность: ${item.durability_current}
                                                        </p>
                                                    ` : ''}
                                                </div>
                                                ${!isGhost && !data.corpse.is_looted ? `
                                                    <button
                                                        onclick="lootItemFromCorpse(${corpseId}, ${item.item_instance_id})"
                                                        class="ml-3 px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700 text-sm whitespace-nowrap"
                                                    >
                                                        Забрать
                                                    </button>
                                                ` : ''}
                                            </div>
                                        </div>
                                    `;
                                }).join('')}
                            </div>
                        `;
                    }
                    
                    itemsHtml = corpseInfoHtml + inventorySectionHtml + equipmentSectionHtml;
                    
                    if (isGhost) {
                        itemsHtml += `
                            <div class="bg-yellow-100 dark:bg-yellow-900 border border-yellow-400 dark:border-yellow-700 rounded-lg p-3 mt-4">
                                <p class="text-sm text-yellow-800 dark:text-yellow-200">
                                    ⚠ Призраки не могут забирать предметы из трупов
                                </p>
                            </div>
                        `;
                    }
                }
                
                document.getElementById('corpseItemsModalContent').innerHTML = itemsHtml;
                document.getElementById('corpseItemsModal').classList.remove('hidden');
            } else {
                alert(data.error || 'Ошибка при загрузке предметов');
            }
        } catch (error) {
            console.error('Error loading corpse items:', error);
            alert('Произошла ошибка при загрузке предметов');
        }
    }
    
    // Функция закрытия модального окна трупа
    function closeCorpseItemsModal() {
        document.getElementById('corpseItemsModal').classList.add('hidden');
        document.getElementById('corpseItemsModalContent').innerHTML = '';
    }
    
    // Закрытие модального окна по ESC
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const modal = document.getElementById('corpseItemsModal');
            if (modal && !modal.classList.contains('hidden')) {
                closeCorpseItemsModal();
            }
        }
    });

    // Функция подбора предмета с трупа
    async function lootItemFromCorpse(corpseId, itemInstanceId) {
        // Проверка, что персонаж не призрак
        if (window.currentCharacterIsGhost) {
            alert('Призраки не могут забирать предметы из трупов');
            return;
        }
        
        try {
            const response = await fetch(`/api/corpse/${corpseId}/loot`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    item_instance_id: itemInstanceId
                })
            });

            let data;
            try {
                const text = await response.text();
                data = text ? JSON.parse(text) : {};
            } catch (parseError) {
                console.error('Error parsing response:', parseError);
                alert('Ошибка при обработке ответа сервера');
                return;
            }

            if (response.ok && data.success) {
                const itemName = data.item?.name || 'предмет';
                alert(`Предмет "${itemName}" успешно подобран!`);
                try {
                    closeCorpseItemsModal(); // Закрываем модальное окно
                } catch (e) {
                    console.error('Error closing modal:', e);
                }
                try {
                    loadLocation(); // Перезагружаем локацию
                } catch (e) {
                    console.error('Error reloading location:', e);
                }
            } else {
                alert(data.error || 'Ошибка при подборе предмета');
            }
        } catch (error) {
            console.error('Error looting item:', error);
            alert('Произошла ошибка при подборе предмета: ' + error.message);
        }
    }

    // Функция подбора всех предметов с трупа
    async function lootAllFromCorpse(corpseId) {
        // Проверка, что персонаж не призрак
        if (window.currentCharacterIsGhost) {
            alert('Призраки не могут забирать предметы из трупов');
            return;
        }
        
        if (!confirm('Забрать все предметы с трупа?')) {
            return;
        }

        try {
            const response = await fetch(`/api/corpse/${corpseId}/loot-all`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });

            let data;
            try {
                const text = await response.text();
                data = text ? JSON.parse(text) : {};
            } catch (parseError) {
                console.error('Error parsing response:', parseError);
                alert('Ошибка при обработке ответа сервера');
                return;
            }

            if (response.ok && data.success) {
                const lootedCount = data.looted_items?.length || 0;
                alert(`Подобрано предметов: ${lootedCount}`);
                try {
                    closeCorpseItemsModal(); // Закрываем модальное окно, если открыто
                } catch (e) {
                    console.error('Error closing modal:', e);
                }
                try {
                    loadLocation(); // Перезагружаем локацию
                } catch (e) {
                    console.error('Error reloading location:', e);
                }
            } else {
                alert(data.error || 'Ошибка при подборе предметов');
            }
        } catch (error) {
            console.error('Error looting all items:', error);
            alert('Произошла ошибка при подборе предметов: ' + error.message);
        }
    }

    // Функция перемещения в локацию
    async function moveToLocation(direction, customName) {
        try {
            const response = await fetch('{{ route("api.location.move") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    direction: direction || null,
                    custom_name: customName || null
                })
            });

            const data = await response.json();

            if (response.ok) {
                // Устанавливаем уровень персонажа
                window.currentCharacterLevel = {{ $character->level }};
                
                // Проверяем, является ли текущий персонаж призраком
                const currentCharacterId = {{ $character->id }};
                const isGhost = (data.ghosts || []).some(ghost => ghost.character_id === currentCharacterId);
                window.currentCharacterIsGhost = isGhost;
                
                // Обновляем локацию после перемещения
                renderLocation(data.location);
                renderExits(data.exits || []);
                renderItems(data.items || []);
                renderResurrectionStones(data.resurrection_stones || []);
                renderNpcs(data.npcs || []);
                renderCorpses(data.corpses || []);
                renderPlayers(data.players || []);
                renderGhosts(data.ghosts || []);
            } else {
                alert(data.error || 'Ошибка при перемещении');
                if (data.errors && Array.isArray(data.errors)) {
                    alert(data.errors.join('\n'));
                }
            }
        } catch (error) {
            console.error('Error moving:', error);
            alert('Произошла ошибка при перемещении');
        }
    }

    // Вспомогательные функции
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function getDirectionName(direction) {
        const directions = {
            'north': 'Север',
            'south': 'Юг',
            'east': 'Восток',
            'west': 'Запад',
        };
        return directions[direction] || direction;
    }

    function getRarityName(rarity) {
        const rarities = {
            'common': 'Обычный',
            'uncommon': 'Необычный',
            'rare': 'Редкий',
            'epic': 'Эпический',
            'legendary': 'Легендарный',
        };
        return rarities[rarity] || rarity;
    }

    function formatTime(seconds) {
        if (!seconds || seconds < 0) {
            return '--:--';
        }
        const minutes = Math.floor(seconds / 60);
        const secs = seconds % 60;
        return `${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    }

    // Переменные для поднятия предмета из модального окна
    let currentPickUpItemId = null;
    let currentPickUpMaxQuantity = 1;
    let currentPickUpIsStackable = false;

    // Функция показа деталей предмета на земле
    async function showGroundItemDetails(itemInstanceId) {
        try {
            const response = await fetch(`/api/item-instances/${itemInstanceId}`);
            const itemInstance = await response.json();

            if (!response.ok) {
                alert('Не удалось загрузить информацию о предмете');
                return;
            }

            const item = itemInstance.item;
            const modal = document.getElementById('groundItemModal');
            const title = document.getElementById('groundItemModalTitle');
            const content = document.getElementById('groundItemModalContent');

            // Сохраняем данные для поднятия
            currentPickUpItemId = itemInstanceId;
            currentPickUpMaxQuantity = itemInstance.quantity || 1;
            currentPickUpIsStackable = item.stackable || false;

            title.textContent = item.name;

            const rarityColors = {
                'common': 'text-gray-600 dark:text-gray-400',
                'uncommon': 'text-green-600 dark:text-green-400',
                'rare': 'text-blue-600 dark:text-blue-400',
                'epic': 'text-purple-600 dark:text-purple-400',
                'legendary': 'text-orange-600 dark:text-orange-400',
            };
            const rarityColor = rarityColors[item.rarity] || rarityColors.common;
            const rarityName = getRarityName(item.rarity);

            let html = `
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">${escapeHtml(item.description || 'Нет описания')}</p>
                <div class="space-y-2 text-sm border-t border-gray-200 dark:border-gray-700 pt-4">
                    <div><strong>Тип:</strong> ${escapeHtml(item.type)}${item.subtype ? ' (' + escapeHtml(item.subtype) + ')' : ''}</div>
                    <div><strong>Редкость:</strong> <span class="${rarityColor} capitalize">${rarityName}</span></div>
                    <div><strong>Требуемый уровень:</strong> ${item.level_required || 1}</div>
                    ${item.value > 0 ? `<div><strong>Стоимость:</strong> ${item.value}</div>` : ''}
                    ${item.weight > 0 ? `<div><strong>Вес:</strong> ${item.weight}</div>` : ''}
                    ${itemInstance.quantity > 1 ? `<div><strong>Количество на земле:</strong> ${itemInstance.quantity}</div>` : ''}
                    ${item.stackable ? `<div><strong>Стакуемый:</strong> Да (макс. ${item.max_stack || 'неограничено'})</div>` : ''}
            `;

            // Специфичные данные предмета
            if (item.weapon_data) {
                html += `<div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700"><strong>Характеристики оружия:</strong></div>`;
                html += `<div>Урон: ${item.weapon_data.damage_min || 0}-${item.weapon_data.damage_max || 0}</div>`;
                if (item.weapon_data.attack_speed) {
                    html += `<div>Скорость атаки: ${item.weapon_data.attack_speed}</div>`;
                }
                if (item.weapon_data.durability_max) {
                    html += `<div>Прочность: ${itemInstance.durability_current || item.weapon_data.durability_max} / ${item.weapon_data.durability_max}</div>`;
                }
            }

            if (item.armor_data) {
                html += `<div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700"><strong>Характеристики брони:</strong></div>`;
                html += `<div>Защита: ${item.armor_data.defense || 0}</div>`;
                if (item.armor_data.durability_max) {
                    html += `<div>Прочность: ${itemInstance.durability_current || item.armor_data.durability_max} / ${item.armor_data.durability_max}</div>`;
                }
            }

            if (item.potion_data) {
                html += `<div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700"><strong>Эффект зелья:</strong></div>`;
                html += `<div>Тип: ${item.potion_data.effect_type || 'Неизвестно'}</div>`;
                html += `<div>Сила: ${item.potion_data.effect_power || 0}</div>`;
            }

            if (item.scroll_data) {
                html += `<div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700"><strong>Данные свитка:</strong></div>`;
                html += `<div>Заклинание ID: ${item.scroll_data.spell_id || 'Неизвестно'}</div>`;
                html += `<div>Одноразовый: ${item.scroll_data.is_consumable !== false ? 'Да' : 'Нет'}</div>`;
            }

            if (item.rune_data) {
                html += `<div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700"><strong>Данные руны:</strong></div>`;
                html += `<div>Заклинание ID: ${item.rune_data.spell_id || 'Неизвестно'}</div>`;
                html += `<div>Стоимость маны: ${item.rune_data.mana_cost_per_use || 0}</div>`;
            }

            // Время исчезновения
            if (itemInstance.expires_at) {
                const expiresIn = itemInstance.expires_in || 0;
                html += `<div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700"><strong>Время исчезновения:</strong> ${formatTime(expiresIn)}</div>`;
            }

            html += `</div>`;

            // Кнопки действий
            html += `<div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700 space-y-3">`;
            
            if (currentPickUpIsStackable && currentPickUpMaxQuantity > 1) {
                html += `
                    <div>
                        <label for="pickUpQuantity" class="block text-sm font-medium mb-2">Количество для поднятия:</label>
                        <div class="flex gap-2">
                            <input
                                type="number"
                                id="pickUpQuantity"
                                min="1"
                                max="${currentPickUpMaxQuantity}"
                                value="${currentPickUpMaxQuantity}"
                                class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-[#1C1C1A] text-gray-900 dark:text-gray-100"
                            />
                            <button
                                onclick="setPickUpQuantity(${currentPickUpMaxQuantity})"
                                class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 text-sm"
                            >
                                Все
                            </button>
                        </div>
                    </div>
                `;
            }

            html += `
                <div class="flex gap-2">
                    <button
                        onclick="pickUpItemFromModal()"
                        class="flex-1 px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 font-medium"
                    >
                        Поднять${currentPickUpIsStackable && currentPickUpMaxQuantity > 1 ? ' выбранное' : ''}
                    </button>
                    <button
                        onclick="closeGroundItemModal()"
                        class="px-4 py-2 border border-gray-400 rounded hover:bg-gray-100 dark:hover:bg-gray-800"
                    >
                        Закрыть
                    </button>
                </div>
            `;

            html += `</div>`;

            content.innerHTML = html;
            modal.classList.remove('hidden');
        } catch (error) {
            console.error('Ошибка:', error);
            alert('Произошла ошибка при загрузке информации о предмете');
        }
    }

    // Закрыть модальное окно осмотра предмета
    function closeGroundItemModal() {
        document.getElementById('groundItemModal').classList.add('hidden');
        currentPickUpItemId = null;
        currentPickUpMaxQuantity = 1;
        currentPickUpIsStackable = false;
    }

    // Показать детали предмета торговца
    async function showTradeItemDetailsFromMerchant(itemId) {
        // Находим предмет в списке товаров торговца
        const allItems = window.npcTradeAllItems || [];
        const item = allItems.find(i => (i.item_id || i.id) === itemId);
        
        if (item) {
            showTradeItemDetails(item);
        } else {
            alert('Предмет не найден');
        }
    }

    // Показать детали предмета из инвентаря для продажи
    async function showTradeItemDetailsFromInventory(itemInstanceId) {
        try {
            const response = await fetch(`/api/item-instances/${itemInstanceId}`);
            const itemInstance = await response.json();

            if (!response.ok) {
                alert('Не удалось загрузить информацию о предмете');
                return;
            }

            showTradeItemDetails(itemInstance.item);
        } catch (error) {
            console.error('Error loading item:', error);
            alert('Произошла ошибка при загрузке информации о предмете');
        }
    }

    // Показать детали предмета в торговле
    function showTradeItemDetails(item) {
        const modal = document.getElementById('tradeItemModal');
        const title = document.getElementById('tradeItemModalTitle');
        const content = document.getElementById('tradeItemModalContent');

        title.textContent = item.name;

        const rarityColors = {
            'common': 'text-gray-600 dark:text-gray-400',
            'uncommon': 'text-green-600 dark:text-green-400',
            'rare': 'text-blue-600 dark:text-blue-400',
            'epic': 'text-purple-600 dark:text-purple-400',
            'legendary': 'text-orange-600 dark:text-orange-400',
        };
        const rarityColor = rarityColors[item.rarity] || rarityColors.common;
        const rarityName = getRarityName(item.rarity);

        let html = `
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">${escapeHtml(item.description || 'Нет описания')}</p>
            <div class="space-y-2 text-sm border-t border-gray-200 dark:border-gray-700 pt-4">
                <div><strong>Тип:</strong> ${escapeHtml(item.type)}${item.subtype ? ' (' + escapeHtml(item.subtype) + ')' : ''}</div>
                <div><strong>Редкость:</strong> <span class="${rarityColor} capitalize">${rarityName}</span></div>
                <div><strong>Требуемый уровень:</strong> ${item.level_required || 1}</div>
                ${item.value > 0 ? `<div><strong>Базовая стоимость:</strong> ${item.value} золота</div>` : ''}
                ${item.weight > 0 ? `<div><strong>Вес:</strong> ${item.weight}</div>` : ''}
                ${item.stackable ? `<div><strong>Стакуемый:</strong> Да${item.max_stack ? ' (макс. ' + item.max_stack + ')' : ''}</div>` : '<div><strong>Стакуемый:</strong> Нет</div>'}
        `;

        // Требования к использованию
        if (item.requirements) {
            html += `<div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700"><strong>Требования:</strong></div>`;
            if (item.requirements.level) {
                html += `<div>Уровень: ${item.requirements.level}</div>`;
            }
            if (item.requirements.attributes) {
                const attrNames = {
                    'strength': 'Сила',
                    'agility': 'Ловкость',
                    'intelligence': 'Интеллект'
                };
                Object.entries(item.requirements.attributes).forEach(([attr, val]) => {
                    html += `<div>${attrNames[attr] || attr}: ${val}</div>`;
                });
            }
            if (item.requirements.skills) {
                Object.entries(item.requirements.skills).forEach(([skill, level]) => {
                    html += `<div>Навык ${skill}: ${level}</div>`;
                });
            }
        }

        // Специфичные данные предмета
        if (item.weapon_data) {
            html += `<div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700"><strong>Характеристики оружия:</strong></div>`;
            html += `<div>Урон: ${item.weapon_data.damage_min || 0}-${item.weapon_data.damage_max || 0}</div>`;
            if (item.weapon_data.attack_speed) {
                html += `<div>Скорость атаки: ${item.weapon_data.attack_speed}</div>`;
            }
            if (item.weapon_data.weapon_type) {
                const weaponTypes = {
                    'one_handed': 'Одноручное',
                    'two_handed': 'Двуручное',
                    'ranged': 'Дальнобойное'
                };
                html += `<div>Тип оружия: ${weaponTypes[item.weapon_data.weapon_type] || item.weapon_data.weapon_type}</div>`;
            }
            if (item.weapon_data.range) {
                html += `<div>Дальность: ${item.weapon_data.range}</div>`;
            }
            if (item.weapon_data.durability_max) {
                html += `<div>Максимальная прочность: ${item.weapon_data.durability_max}</div>`;
            }
        }

        if (item.armor_data) {
            html += `<div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700"><strong>Характеристики брони:</strong></div>`;
            html += `<div>Защита: ${item.armor_data.defense || 0}</div>`;
            if (item.armor_data.magic_defense) {
                html += `<div>Магическая защита: ${item.armor_data.magic_defense}</div>`;
            }
            if (item.armor_data.durability_max) {
                html += `<div>Максимальная прочность: ${item.armor_data.durability_max}</div>`;
            }
        }

        if (item.jewelry_data) {
            html += `<div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700"><strong>Характеристики бижутерии:</strong></div>`;
            if (item.jewelry_data.bonuses) {
                Object.entries(item.jewelry_data.bonuses).forEach(([stat, value]) => {
                    const statNames = {
                        'strength': 'Сила',
                        'agility': 'Ловкость',
                        'intelligence': 'Интеллект',
                        'health': 'Здоровье',
                        'mana': 'Мана'
                    };
                    html += `<div>${statNames[stat] || stat}: +${value}</div>`;
                });
            }
        }

        if (item.potion_data) {
            html += `<div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700"><strong>Эффект зелья:</strong></div>`;
            const effectTypes = {
                'health': 'Восстановление здоровья',
                'mana': 'Восстановление маны',
                'strength': 'Увеличение силы',
                'agility': 'Увеличение ловкости',
                'intelligence': 'Увеличение интеллекта'
            };
            html += `<div>Тип: ${effectTypes[item.potion_data.effect_type] || item.potion_data.effect_type || 'Неизвестно'}</div>`;
            html += `<div>Сила: ${item.potion_data.effect_power || 0}</div>`;
            if (item.potion_data.duration) {
                html += `<div>Длительность: ${item.potion_data.duration} сек</div>`;
            }
        }

        if (item.scroll_data) {
            html += `<div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700"><strong>Данные свитка:</strong></div>`;
            html += `<div>Заклинание ID: ${item.scroll_data.spell_id || 'Неизвестно'}</div>`;
            html += `<div>Одноразовый: ${item.scroll_data.is_consumable !== false ? 'Да' : 'Нет'}</div>`;
        }

        if (item.rune_data) {
            html += `<div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700"><strong>Данные руны:</strong></div>`;
            html += `<div>Заклинание ID: ${item.rune_data.spell_id || 'Неизвестно'}</div>`;
            html += `<div>Стоимость маны: ${item.rune_data.mana_cost_per_use || 0}</div>`;
        }

        if (item.resource_data) {
            html += `<div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700"><strong>Данные ресурса:</strong></div>`;
            if (item.resource_data.category) {
                html += `<div>Категория: ${item.resource_data.category}</div>`;
            }
            if (item.resource_data.quality) {
                html += `<div>Качество: ${item.resource_data.quality}</div>`;
            }
        }

        html += `</div>`;

        // Кнопка закрытия
        html += `
            <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                <button
                    onclick="closeTradeItemModal()"
                    class="w-full px-4 py-2 border border-gray-400 rounded hover:bg-gray-100 dark:hover:bg-gray-800"
                >
                    Закрыть
                </button>
            </div>
        `;

        content.innerHTML = html;
        modal.classList.remove('hidden');
    }

    // Закрыть модальное окно просмотра предмета в торговле
    function closeTradeItemModal() {
        document.getElementById('tradeItemModal').classList.add('hidden');
    }

    // Установить количество "Все" для поднятия
    function setPickUpQuantity(quantity) {
        const input = document.getElementById('pickUpQuantity');
        if (input) {
            input.value = quantity;
        }
    }

    // Поднять предмет из модального окна
    async function pickUpItemFromModal() {
        if (!currentPickUpItemId) {
            return;
        }

        let quantity = null;

        if (currentPickUpIsStackable && currentPickUpMaxQuantity > 1) {
            const input = document.getElementById('pickUpQuantity');
            if (input) {
                quantity = parseInt(input.value);
                if (isNaN(quantity) || quantity < 1 || quantity > currentPickUpMaxQuantity) {
                    alert('Укажите корректное количество (от 1 до ' + currentPickUpMaxQuantity + ')');
                    return;
                }
            }
        }

        closeGroundItemModal();
        await pickUpItem(currentPickUpItemId, currentPickUpMaxQuantity, currentPickUpIsStackable, quantity);
    }

    // Функция поднятия предмета с земли
    async function pickUpItem(itemInstanceId, maxQuantity, isStackable, quantity = null) {
        try {
            // Если количество не передано и предмет стакуемый, используем prompt (для обратной совместимости)
            if (quantity === null && isStackable && maxQuantity > 1) {
                const input = prompt(`Сколько предметов поднять? (доступно: ${maxQuantity})`, maxQuantity.toString());
                if (input === null) {
                    return; // Пользователь отменил
                }
                quantity = parseInt(input);
                if (isNaN(quantity) || quantity < 1 || quantity > maxQuantity) {
                    alert('Укажите корректное количество (от 1 до ' + maxQuantity + ')');
                    return;
                }
            }

            const body = {
                item_instance_id: itemInstanceId
            };

            if (quantity !== null) {
                body.quantity = quantity;
            }

            const response = await fetch(`/api/characters/{{ $character->id }}/items/pick-up`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(body)
            });

            const data = await response.json();

            if (response.ok) {
                alert(data.message || 'Предмет успешно поднят!');
                // Перезагружаем локацию для обновления списка предметов
                loadCurrentLocation();
            } else {
                alert('Ошибка: ' + (data.error || 'Не удалось поднять предмет'));
            }
        } catch (error) {
            console.error('Error picking up item:', error);
            alert('Произошла ошибка при поднятии предмета');
        }
    }

    // Закрытие модального окна по клику вне его
    document.getElementById('groundItemModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeGroundItemModal();
        }
    });

    // Закрытие модального окна торговли по клику вне его
    const tradeItemModal = document.getElementById('tradeItemModal');
    if (tradeItemModal) {
        tradeItemModal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeTradeItemModal();
            }
        });
    }

    // Загружаем локацию при загрузке страницы, если есть активная сессия
    @if($session)
        loadCurrentLocation();
        // Обновляем локацию каждые 30 секунд
        setInterval(loadCurrentLocation, 30000);
    @endif
</script>
@endpush
@endsection

