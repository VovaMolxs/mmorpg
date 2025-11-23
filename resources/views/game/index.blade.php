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

            <!-- Предметы в локации -->
            <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4">Предметы в локации</h2>
                <div id="items-content">
                    <p class="text-gray-600 dark:text-gray-400">Загрузка...</p>
                </div>
            </div>
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
                renderLocation(data.location);
                renderExits(data.exits || []);
                renderItems(data.items || []);
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
                // Обновляем локацию после перемещения
                renderLocation(data.location);
                renderExits(data.exits || []);
                // Перезагружаем предметы
                loadCurrentLocation();
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

    // Загружаем локацию при загрузке страницы, если есть активная сессия
    @if($session)
        loadCurrentLocation();
        // Обновляем локацию каждые 30 секунд
        setInterval(loadCurrentLocation, 30000);
    @endif
</script>
@endpush
@endsection

