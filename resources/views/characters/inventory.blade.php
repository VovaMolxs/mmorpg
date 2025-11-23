@extends('layouts.app')

@section('title', 'Инвентарь: ' . $character->name)

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <h1 class="text-2xl sm:text-3xl font-semibold break-words">Инвентарь: {{ $character->name }}</h1>
        <div class="flex flex-wrap gap-2 sm:gap-4 w-full sm:w-auto">
            <a
                href="{{ route('characters.show', $character) }}"
                class="px-3 sm:px-4 py-2 border border-[#19140035] dark:border-[#3E3E3A] rounded hover:border-[#1915014a] dark:hover:border-[#62605b] whitespace-nowrap text-sm sm:text-base"
            >
                Профиль
            </a>
            <a
                href="{{ route('characters.skills', $character) }}"
                class="px-3 sm:px-4 py-2 border border-[#19140035] dark:border-[#3E3E3A] rounded hover:border-[#1915014a] dark:hover:border-[#62605b] whitespace-nowrap text-sm sm:text-base"
            >
                Навыки
            </a>
        </div>
    </div>

    <!-- Экипировка -->
    <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4">Экипировка</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
            @foreach($equipmentSlots as $slotKey => $slotName)
                <div class="border border-[#e3e3e0] dark:border-[#3E3E3A] rounded p-4 min-h-[120px] flex flex-col">
                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">{{ $slotName }}</div>
                    <div class="flex-1 flex items-center justify-center">
                        @if(isset($equipment[$slotKey]))
                            @php
                                $equippedItem = $equipment[$slotKey]->itemInstance;
                                $item = $equippedItem->item;
                            @endphp
                            <div class="text-center w-full">
                                <div class="font-medium text-sm mb-1">{{ $item->name }}</div>
                                @if($item->subtype)
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">{{ $item->subtype }}</div>
                                @endif
                                @if($equippedItem->durability_current !== null)
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                                        Прочность: {{ $equippedItem->durability_current }}
                                    </div>
                                @endif
                                <button
                                    onclick="unequipItem({{ $equippedItem->id }})"
                                    class="text-xs px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700 w-full"
                                >
                                    Снять
                                </button>
                            </div>
                        @else
                            <div class="text-gray-400 dark:text-gray-600 text-xs text-center">Пусто</div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Инвентарь -->
    <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6">
        <h2 class="text-xl font-semibold mb-4">Инвентарь</h2>
        
        @if($inventoryItems->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($inventoryItems as $itemInstance)
                    @php
                        $item = $itemInstance->item;
                        $rarityColors = [
                            'common' => 'border-gray-400',
                            'uncommon' => 'border-green-400',
                            'rare' => 'border-blue-400',
                            'epic' => 'border-purple-400',
                            'legendary' => 'border-yellow-400',
                        ];
                        $rarityColor = $rarityColors[$item->rarity] ?? 'border-gray-400';
                    @endphp
                    <div class="border-2 {{ $rarityColor }} rounded-lg p-4 hover:shadow-lg transition-shadow">
                        <div class="mb-2">
                            <h3 class="font-medium">{{ $item->name }}</h3>
                            @if($item->subtype)
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $item->subtype }}</p>
                            @endif
                            <p class="text-xs text-gray-500 dark:text-gray-400 capitalize">{{ $item->rarity }}</p>
                        </div>

                        @if($item->description)
                            <p class="text-xs text-gray-600 dark:text-gray-400 mb-2 line-clamp-2">{{ $item->description }}</p>
                        @endif

                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-2 space-y-1">
                            @if($item->level_required > 1)
                                <div>Требуется уровень: {{ $item->level_required }}</div>
                            @endif
                            @if($itemInstance->quantity > 1)
                                <div>Количество: {{ $itemInstance->quantity }}</div>
                            @endif
                            @if($itemInstance->durability_current !== null)
                                <div>Прочность: {{ $itemInstance->durability_current }}</div>
                            @endif
                        </div>

                        <div class="flex flex-wrap gap-2 mt-3">
                            @if($item->getEquipmentSlot())
                                <button
                                    onclick="equipItem({{ $itemInstance->id }})"
                                    class="text-xs px-2 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 flex-1"
                                >
                                    Экипировать
                                </button>
                            @endif

                            @if($item->isPotion() || $item->isScroll() || $item->isRune())
                                <button
                                    onclick="useItem({{ $itemInstance->id }})"
                                    class="text-xs px-2 py-1 bg-green-600 text-white rounded hover:bg-green-700 flex-1"
                                >
                                    Использовать
                                </button>
                            @endif

                            <button
                                onclick="dropItem({{ $itemInstance->id }}, {{ $itemInstance->quantity }}, {{ $item->stackable ? 'true' : 'false' }})"
                                class="text-xs px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700 flex-1"
                            >
                                Выбросить
                            </button>

                            <button
                                onclick="showItemDetails({{ $itemInstance->id }})"
                                class="text-xs px-2 py-1 border border-gray-400 rounded hover:bg-gray-100 dark:hover:bg-gray-800 flex-1"
                            >
                                Подробнее
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-600 dark:text-gray-400 text-center py-8">Инвентарь пуст</p>
        @endif
    </div>
</div>

<!-- Модальное окно для деталей предмета -->
<div id="itemModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-[#161615] rounded-lg p-6 max-w-md w-full max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
            <h3 id="itemModalTitle" class="text-xl font-semibold"></h3>
            <button onclick="closeItemModal()" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">✕</button>
        </div>
        <div id="itemModalContent" class="space-y-2"></div>
    </div>
</div>

<!-- Модальное окно для выброса предмета -->
<div id="dropItemModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-[#161615] rounded-lg p-6 max-w-md w-full">
        <div class="flex justify-between items-center mb-4">
            <h3 id="dropItemModalTitle" class="text-xl font-semibold">Выбросить предмет</h3>
            <button onclick="closeDropItemModal()" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">✕</button>
        </div>
        <div id="dropItemModalContent" class="space-y-4">
            <p id="dropItemMessage" class="text-sm text-gray-600 dark:text-gray-400"></p>
            <div id="dropItemQuantityInput" class="hidden">
                <label for="dropQuantity" class="block text-sm font-medium mb-2">Количество:</label>
                <input
                    type="number"
                    id="dropQuantity"
                    min="1"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-[#1C1C1A] text-gray-900 dark:text-gray-100"
                />
            </div>
            <div class="flex gap-2 justify-end">
                <button
                    onclick="closeDropItemModal()"
                    class="px-4 py-2 border border-gray-400 rounded hover:bg-gray-100 dark:hover:bg-gray-800"
                >
                    Отмена
                </button>
                <button
                    onclick="confirmDropItem()"
                    class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"
                >
                    Выбросить
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Экипировать предмет
async function equipItem(itemInstanceId) {
    if (!confirm('Экипировать этот предмет?')) {
        return;
    }

    try {
        const response = await fetch(`/api/characters/{{ $character->id }}/items/equip`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                item_instance_id: itemInstanceId
            })
        });

        const data = await response.json();

        if (response.ok) {
            alert('Предмет успешно экипирован!');
            location.reload();
        } else {
            alert('Ошибка: ' + (data.error || 'Не удалось экипировать предмет'));
            if (data.details) {
                console.error('Детали ошибки:', data.details);
            }
        }
    } catch (error) {
        console.error('Ошибка:', error);
        alert('Произошла ошибка при экипировке предмета');
    }
}

// Снять предмет
async function unequipItem(itemInstanceId) {
    if (!confirm('Снять этот предмет?')) {
        return;
    }

    try {
        const response = await fetch(`/api/characters/{{ $character->id }}/items/${itemInstanceId}/unequip`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        const data = await response.json();

        if (response.ok) {
            alert('Предмет успешно снят!');
            location.reload();
        } else {
            alert('Ошибка: ' + (data.error || 'Не удалось снять предмет'));
        }
    } catch (error) {
        console.error('Ошибка:', error);
        alert('Произошла ошибка при снятии предмета');
    }
}

// Использовать предмет
async function useItem(itemInstanceId) {
    if (!confirm('Использовать этот предмет?')) {
        return;
    }

    try {
        const response = await fetch(`/api/characters/{{ $character->id }}/items/use`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                item_instance_id: itemInstanceId
            })
        });

        const data = await response.json();

        if (response.ok) {
            alert(data.message || 'Предмет использован!');
            location.reload();
        } else {
            alert('Ошибка: ' + (data.error || 'Не удалось использовать предмет'));
        }
    } catch (error) {
        console.error('Ошибка:', error);
        alert('Произошла ошибка при использовании предмета');
    }
}

// Показать детали предмета
async function showItemDetails(itemInstanceId) {
    try {
        const response = await fetch(`/api/item-instances/${itemInstanceId}`);
        const itemInstance = await response.json();

        if (!response.ok) {
            alert('Не удалось загрузить информацию о предмете');
            return;
        }

        const item = itemInstance.item;
        const modal = document.getElementById('itemModal');
        const title = document.getElementById('itemModalTitle');
        const content = document.getElementById('itemModalContent');

        title.textContent = item.name;
        
        let html = `
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">${item.description || 'Нет описания'}</p>
            <div class="space-y-2 text-sm">
                <div><strong>Тип:</strong> ${item.type} ${item.subtype ? '(' + item.subtype + ')' : ''}</div>
                <div><strong>Редкость:</strong> <span class="capitalize">${item.rarity}</span></div>
                <div><strong>Требуемый уровень:</strong> ${item.level_required}</div>
                ${item.value > 0 ? `<div><strong>Стоимость:</strong> ${item.value}</div>` : ''}
                ${item.weight > 0 ? `<div><strong>Вес:</strong> ${item.weight}</div>` : ''}
                ${itemInstance.quantity > 1 ? `<div><strong>Количество:</strong> ${itemInstance.quantity}</div>` : ''}
        `;

        // Специфичные данные
        if (item.weapon_data) {
            html += `<div class="mt-4 pt-4 border-t border-gray-300 dark:border-gray-700"><strong>Характеристики оружия:</strong></div>`;
            html += `<div>Урон: ${item.weapon_data.damage_min}-${item.weapon_data.damage_max}</div>`;
            if (item.weapon_data.attack_speed) {
                html += `<div>Скорость атаки: ${item.weapon_data.attack_speed}</div>`;
            }
        }

        if (item.armor_data) {
            html += `<div class="mt-4 pt-4 border-t border-gray-300 dark:border-gray-700"><strong>Характеристики брони:</strong></div>`;
            html += `<div>Защита: ${item.armor_data.defense || 0}</div>`;
        }

        if (item.potion_data) {
            html += `<div class="mt-4 pt-4 border-t border-gray-300 dark:border-gray-700"><strong>Эффект зелья:</strong></div>`;
            html += `<div>Тип: ${item.potion_data.effect_type}</div>`;
            html += `<div>Сила: ${item.potion_data.effect_power}</div>`;
        }

        html += `</div>`;

        content.innerHTML = html;
        modal.classList.remove('hidden');
    } catch (error) {
        console.error('Ошибка:', error);
        alert('Произошла ошибка при загрузке информации о предмете');
    }
}

// Закрыть модальное окно
function closeItemModal() {
    document.getElementById('itemModal').classList.add('hidden');
}

// Закрытие модального окна по клику вне его
document.getElementById('itemModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeItemModal();
    }
});

// Переменные для выброса предмета
let currentDropItemId = null;
let currentDropMaxQuantity = 1;
let currentDropIsStackable = false;

// Показать модальное окно для выброса предмета
function dropItem(itemInstanceId, maxQuantity, isStackable) {
    currentDropItemId = itemInstanceId;
    currentDropMaxQuantity = maxQuantity;
    currentDropIsStackable = isStackable;

    const modal = document.getElementById('dropItemModal');
    const message = document.getElementById('dropItemMessage');
    const quantityInput = document.getElementById('dropItemQuantityInput');
    const quantityField = document.getElementById('dropQuantity');

    if (isStackable && maxQuantity > 1) {
        message.textContent = `Вы хотите выбросить предмет. У вас ${maxQuantity} шт.`;
        quantityInput.classList.remove('hidden');
        quantityField.value = 1;
        quantityField.max = maxQuantity - 1;
    } else {
        message.textContent = 'Вы уверены, что хотите выбросить этот предмет на землю?';
        quantityInput.classList.add('hidden');
    }

    modal.classList.remove('hidden');
}

// Закрыть модальное окно выброса
function closeDropItemModal() {
    document.getElementById('dropItemModal').classList.add('hidden');
    currentDropItemId = null;
    currentDropMaxQuantity = 1;
    currentDropIsStackable = false;
}

// Подтвердить выброс предмета
async function confirmDropItem() {
    if (!currentDropItemId) {
        return;
    }

    const quantityInput = document.getElementById('dropQuantity');
    let quantity = null;

    if (currentDropIsStackable && currentDropMaxQuantity > 1) {
        quantity = parseInt(quantityInput.value);
        if (isNaN(quantity) || quantity < 1 || quantity >= currentDropMaxQuantity) {
            alert('Укажите корректное количество (от 1 до ' + (currentDropMaxQuantity - 1) + ')');
            return;
        }
    }

    try {
        const body = {
            item_instance_id: currentDropItemId
        };

        if (quantity !== null) {
            body.quantity = quantity;
        }

        const response = await fetch(`/api/characters/{{ $character->id }}/items/drop`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(body)
        });

        const data = await response.json();

        if (response.ok) {
            alert(data.message || 'Предмет успешно выброшен на землю!');
            closeDropItemModal();
            location.reload();
        } else {
            alert('Ошибка: ' + (data.error || 'Не удалось выбросить предмет'));
        }
    } catch (error) {
        console.error('Ошибка:', error);
        alert('Произошла ошибка при выбрасывании предмета');
    }
}

// Закрытие модального окна выброса по клику вне его
document.getElementById('dropItemModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDropItemModal();
    }
});
</script>
@endsection

