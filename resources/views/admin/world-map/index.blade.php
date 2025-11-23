@extends('layouts.app')

@section('title', 'Редактор карты мира')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <h1 class="text-2xl sm:text-3xl font-semibold">Редактор карты мира</h1>
        <div class="flex gap-2">
            <a
                href="{{ route('admin.characters.online') }}"
                class="px-4 py-2 bg-green-600 dark:bg-green-700 text-white rounded hover:bg-green-700 dark:hover:bg-green-600 whitespace-nowrap"
            >
                Онлайн персонажи
            </a>
            <a
                href="{{ route('admin.characters.index') }}"
                class="px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded hover:bg-gray-50 dark:hover:bg-[#0a0a0a] whitespace-nowrap"
            >
                Персонажи
            </a>
        </div>
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

    <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6 mb-6">
        <div class="mb-4">
            <h2 class="text-lg font-semibold mb-2">Легенда</h2>
            <div class="flex flex-wrap gap-4 text-sm">
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 border-2 border-gray-400 bg-gray-100 dark:bg-gray-800 rounded"></div>
                    <span>Пустая клетка</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 border-2 border-blue-500 bg-blue-100 dark:bg-blue-900 rounded"></div>
                    <span>Локация</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 border-2 border-green-500 bg-green-100 dark:bg-green-900 rounded"></div>
                    <span>Безопасная зона</span>
                </div>
            </div>
        </div>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
            Кликните на пустую клетку для создания новой локации. Кликните на существующую локацию для редактирования.
            Центр карты (0, 0) выделен красным цветом.
        </p>
    </div>

    <!-- Карта мира -->
    <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6 overflow-x-auto">
        <div class="inline-block">
            <!-- Координаты X -->
            <div class="flex">
                <div class="w-12"></div>
                @for($x = -10; $x < 10; $x++)
                    <div class="w-12 h-8 flex items-center justify-center text-xs font-medium text-gray-600 dark:text-gray-400
                        @if($x === 0) font-bold text-[#f53003] dark:text-[#FF4433] @endif">
                        {{ $x }}
                    </div>
                @endfor
            </div>
            
            <!-- Карта -->
            @for($y = -10; $y < 10; $y++)
                <div class="flex">
                    <!-- Координата Y -->
                    <div class="w-12 h-12 flex items-center justify-center text-xs font-medium text-gray-600 dark:text-gray-400
                        @if($y === 0) font-bold text-[#f53003] dark:text-[#FF4433] @endif">
                        {{ $y }}
                    </div>
                    
                    @for($x = -10; $x < 10; $x++)
                        @php
                            $key = "{$x}_{$y}";
                            $location = $locations[$key] ?? null;
                            $isEdge = $x === -10 || $x === 9 || $y === -10 || $y === 9;
                            $isEmpty = $location === null;
                            $isCenter = $x === 0 && $y === 0;
                        @endphp
                        <div
                            class="w-12 h-12 border-2 rounded cursor-pointer transition-all hover:scale-110 hover:z-10 relative
                                @if($isCenter) ring-2 ring-[#f53003] dark:ring-[#FF4433] ring-offset-1 @endif
                                @if($location)
                                    @if($location->is_safe_zone)
                                        border-green-500 bg-green-100 dark:bg-green-900
                                    @else
                                        border-blue-500 bg-blue-100 dark:bg-blue-900
                                    @endif
                                @else
                                    border-gray-400 bg-gray-100 dark:bg-gray-800
                                    @if($isEdge)
                                        hover:border-yellow-500 hover:bg-yellow-100 dark:hover:bg-yellow-900
                                    @endif
                                @endif
                            "
                            onclick="handleCellClick({{ $x }}, {{ $y }}, {{ $location ? $location->id : 'null' }})"
                            title="@if($location){{ $location->name }} ({{ $location->type }})@elseПустая клетка ({{ $x }}, {{ $y }})@endif"
                        >
                            @if($location)
                                <div class="w-full h-full flex items-center justify-center text-xs font-bold text-gray-800 dark:text-gray-200">
                                    {{ substr($location->name, 0, 2) }}
                                </div>
                            @elseif($isEdge)
                                <div class="w-full h-full flex items-center justify-center text-xs text-gray-500">
                                    +
                                </div>
                            @elseif($isCenter)
                                <div class="w-full h-full flex items-center justify-center text-xs font-bold text-[#f53003] dark:text-[#FF4433]">
                                    0
                                </div>
                            @endif
                        </div>
                    @endfor
                </div>
            @endfor
        </div>
    </div>
</div>

<!-- Модальное окно для создания/редактирования локации -->
<div id="locationModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-[#161615] rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-semibold" id="modalTitle">Создать локацию</h2>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form id="locationForm" class="space-y-4">
                @csrf
                <input type="hidden" id="location_id" name="location_id">
                <input type="hidden" id="coordinate_x" name="coordinate_x">
                <input type="hidden" id="coordinate_y" name="coordinate_y">

                <div>
                    <label for="name" class="block text-sm font-medium mb-1">Название *</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        required
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium mb-1">Описание</label>
                    <textarea
                        id="description"
                        name="description"
                        rows="3"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    ></textarea>
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
                            <option value="city">Город</option>
                            <option value="forest">Лес</option>
                            <option value="mountain">Гора</option>
                            <option value="dungeon">Подземелье</option>
                            <option value="river">Река</option>
                            <option value="road">Дорога</option>
                            <option value="field">Поле</option>
                            <option value="cave">Пещера</option>
                            <option value="village">Деревня</option>
                            <option value="castle">Замок</option>
                            <option value="building">Здание</option>
                            <option value="street">Улица</option>
                            <option value="square">Площадь</option>
                            <option value="bank">Банк</option>
                            <option value="shop">Магазин</option>
                        </select>
                    </div>

                    <div>
                        <label class="flex items-center mt-6">
                            <input
                                type="checkbox"
                                id="is_safe_zone"
                                name="is_safe_zone"
                                value="1"
                                class="mr-2"
                            >
                            <span class="text-sm">Безопасная зона</span>
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="min_level" class="block text-sm font-medium mb-1">Мин. уровень</label>
                        <input
                            type="number"
                            id="min_level"
                            name="min_level"
                            min="1"
                            max="255"
                            class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                        >
                    </div>

                    <div>
                        <label for="max_level" class="block text-sm font-medium mb-1">Макс. уровень</label>
                        <input
                            type="number"
                            id="max_level"
                            name="max_level"
                            min="1"
                            max="255"
                            class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                        >
                    </div>
                </div>

                <div>
                    <label for="image_url" class="block text-sm font-medium mb-1">URL изображения</label>
                    <input
                        type="text"
                        id="image_url"
                        name="image_url"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                </div>

                <div>
                    <label for="background_music" class="block text-sm font-medium mb-1">Фоновая музыка</label>
                    <input
                        type="text"
                        id="background_music"
                        name="background_music"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                </div>

                <!-- Управление выходами -->
                <div class="border-t border-[#e3e3e0] dark:border-[#3E3E3A] pt-4 mt-4">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Выходы из локации</h3>
                        <button
                            type="button"
                            onclick="addExit()"
                            class="px-3 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-700"
                        >
                            + Добавить выход
                        </button>
                    </div>
                    <div id="exitsContainer" class="space-y-3">
                        <!-- Выходы будут добавлены динамически -->
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                        По умолчанию выходы ведут в соседние клетки. Можно указать кастомное название и координаты целевой локации.
                    </p>
                </div>

                <div class="flex gap-4 pt-4">
                    <button
                        type="submit"
                        class="flex-1 px-4 py-2 bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded hover:bg-black dark:hover:bg-white"
                    >
                        Сохранить
                    </button>
                    <button
                        type="button"
                        onclick="closeModal()"
                        class="px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded hover:bg-gray-50 dark:hover:bg-[#0a0a0a]"
                    >
                        Отмена
                    </button>
                    <button
                        type="button"
                        id="deleteButton"
                        onclick="deleteLocation()"
                        class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 hidden"
                    >
                        Удалить
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let currentLocationId = null;

    function handleCellClick(x, y, locationId) {
        currentLocationId = locationId;
        document.getElementById('coordinate_x').value = x;
        document.getElementById('coordinate_y').value = y;

        if (locationId) {
            // Редактирование существующей локации
            loadLocation(locationId);
            document.getElementById('modalTitle').textContent = 'Редактировать локацию';
            document.getElementById('deleteButton').classList.remove('hidden');
        } else {
            // Создание новой локации
            document.getElementById('locationForm').reset();
            document.getElementById('location_id').value = '';
            document.getElementById('coordinate_x').value = x;
            document.getElementById('coordinate_y').value = y;
            document.getElementById('modalTitle').textContent = `Создать локацию (${x}, ${y})`;
            document.getElementById('deleteButton').classList.add('hidden');
            // Очищаем выходы для новой локации
            currentExits = [];
            renderExits();
        }

        document.getElementById('locationModal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('locationModal').classList.add('hidden');
        currentLocationId = null;
        currentExits = [];
        renderExits();
    }

    async function loadLocation(locationId) {
        try {
            const response = await fetch(`{{ url('admin/world-map') }}/${locationId}`);
            const data = await response.json();
            const location = data.location;

            document.getElementById('location_id').value = location.id;
            document.getElementById('name').value = location.name || '';
            document.getElementById('description').value = location.description || '';
            document.getElementById('type').value = location.type || '';
            document.getElementById('is_safe_zone').checked = location.is_safe_zone || false;
            document.getElementById('min_level').value = location.min_level || '';
            document.getElementById('max_level').value = location.max_level || '';
            document.getElementById('image_url').value = location.image_url || '';
            document.getElementById('background_music').value = location.background_music || '';

            // Загружаем выходы
            currentExits = data.exits || [];
            renderExits();
        } catch (error) {
            console.error('Error loading location:', error);
            alert('Ошибка при загрузке локации');
        }
    }

    function addExit() {
        currentExits.push({
            id: null,
            direction: '',
            custom_name: '',
            description: '',
            is_locked: false,
            to_location_id: '',
            to_coordinate_x: '',
            to_coordinate_y: '',
        });
        renderExits();
    }

    function removeExit(index) {
        currentExits.splice(index, 1);
        renderExits();
    }

    function renderExits() {
        const container = document.getElementById('exitsContainer');
        container.innerHTML = '';

        if (currentExits.length === 0) {
            container.innerHTML = '<p class="text-sm text-gray-500 dark:text-gray-400">Нет настроенных выходов</p>';
            return;
        }

        currentExits.forEach((exit, index) => {
            const exitDiv = document.createElement('div');
            exitDiv.className = 'border border-[#e3e3e0] dark:border-[#3E3E3A] rounded p-3 bg-gray-50 dark:bg-[#0a0a0a]';
            exitDiv.innerHTML = `
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium mb-1">Направление</label>
                        <select
                            class="w-full px-2 py-1 text-sm border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a]"
                            onchange="updateExit(${index}, 'direction', this.value)"
                        >
                            <option value="">Не выбрано</option>
                            <option value="north" ${exit.direction === 'north' ? 'selected' : ''}>Север</option>
                            <option value="south" ${exit.direction === 'south' ? 'selected' : ''}>Юг</option>
                            <option value="east" ${exit.direction === 'east' ? 'selected' : ''}>Восток</option>
                            <option value="west" ${exit.direction === 'west' ? 'selected' : ''}>Запад</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Кастомное название</label>
                        <input
                            type="text"
                            class="w-full px-2 py-1 text-sm border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a]"
                            placeholder="Например: Вход в портал"
                            value="${exit.custom_name || ''}"
                            onchange="updateExit(${index}, 'custom_name', this.value)"
                        >
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Целевая локация (координаты X)</label>
                        <input
                            type="number"
                            class="w-full px-2 py-1 text-sm border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a]"
                            placeholder="Оставьте пустым для соседней"
                            value="${exit.to_coordinate_x || (exit.to_location ? exit.to_location.coordinate_x : '')}"
                            onchange="updateExit(${index}, 'to_coordinate_x', this.value)"
                        >
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Целевая локация (координаты Y)</label>
                        <input
                            type="number"
                            class="w-full px-2 py-1 text-sm border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a]"
                            placeholder="Оставьте пустым для соседней"
                            value="${exit.to_coordinate_y || (exit.to_location ? exit.to_location.coordinate_y : '')}"
                            onchange="updateExit(${index}, 'to_coordinate_y', this.value)"
                        >
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium mb-1">Описание</label>
                        <input
                            type="text"
                            class="w-full px-2 py-1 text-sm border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a]"
                            placeholder="Описание выхода"
                            value="${exit.description || ''}"
                            onchange="updateExit(${index}, 'description', this.value)"
                        >
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="flex items-center">
                            <input
                                type="checkbox"
                                class="mr-1"
                                ${exit.is_locked ? 'checked' : ''}
                                onchange="updateExit(${index}, 'is_locked', this.checked)"
                            >
                            <span class="text-xs">Заблокирован</span>
                        </label>
                        <button
                            type="button"
                            onclick="removeExit(${index})"
                            class="ml-auto px-2 py-1 text-xs bg-red-600 text-white rounded hover:bg-red-700"
                        >
                            Удалить
                        </button>
                    </div>
                </div>
            `;
            container.appendChild(exitDiv);
        });
    }

    function updateExit(index, field, value) {
        if (currentExits[index]) {
            currentExits[index][field] = value;
        }
    }

    async function deleteLocation() {
        if (!currentLocationId) {
            return;
        }

        if (!confirm('Вы уверены, что хотите удалить эту локацию?')) {
            return;
        }

        try {
            const response = await fetch(`{{ url('admin/world-map') }}/${currentLocationId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            });

            const data = await response.json();

            if (response.ok) {
                alert(data.message || 'Локация успешно удалена');
                location.reload();
            } else {
                alert(data.error || 'Ошибка при удалении локации');
            }
        } catch (error) {
            console.error('Error deleting location:', error);
            alert('Ошибка при удалении локации');
        }
    }

    document.getElementById('locationForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const locationId = document.getElementById('location_id').value;
        const url = locationId
            ? `{{ url('admin/world-map') }}/${locationId}`
            : '{{ route("admin.world-map.store") }}';
        const method = locationId ? 'PUT' : 'POST';

        // Собираем данные формы
        const nameValue = document.getElementById('name').value.trim();
        const typeValue = document.getElementById('type').value;

        // Проверка обязательных полей перед отправкой
        if (!nameValue) {
            alert('Название локации обязательно');
            return;
        }
        if (!typeValue) {
            alert('Тип локации обязателен');
            return;
        }

        const formDataObj = {
            name: nameValue,
            description: document.getElementById('description').value.trim() || null,
            type: typeValue,
            is_safe_zone: document.getElementById('is_safe_zone').checked ? 1 : 0,
            min_level: document.getElementById('min_level').value ? parseInt(document.getElementById('min_level').value) : null,
            max_level: document.getElementById('max_level').value ? parseInt(document.getElementById('max_level').value) : null,
            image_url: document.getElementById('image_url').value.trim() || null,
            background_music: document.getElementById('background_music').value.trim() || null,
            coordinate_x: parseInt(document.getElementById('coordinate_x').value),
            coordinate_y: parseInt(document.getElementById('coordinate_y').value),
        };

        try {
            // Сначала сохраняем локацию
            const response = await fetch(url, {
                method: method,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formDataObj),
            });

            const data = await response.json();

            if (response.ok) {
                // Если локация сохранена и есть ID, сохраняем выходы
                const savedLocationId = locationId || data.location?.id;
                if (savedLocationId) {
                    try {
                        // Сохраняем выходы, даже если их нет (чтобы очистить старые)
                        await saveExits(savedLocationId);
                    } catch (exitError) {
                        console.error('Error saving exits:', exitError);
                        alert('Локация сохранена, но произошла ошибка при сохранении выходов: ' + exitError.message);
                        location.reload();
                        return;
                    }
                }
                
                alert(data.message || 'Локация успешно сохранена');
                location.reload();
            } else {
                const errors = data.errors || {};
                let errorMessage = data.error || 'Ошибка при сохранении локации';
                
                if (Object.keys(errors).length > 0) {
                    errorMessage = Object.values(errors).flat().join('\n');
                }
                
                alert(errorMessage);
            }
        } catch (error) {
            console.error('Error saving location:', error);
            alert('Ошибка при сохранении локации');
        }
    });

    async function saveExits(locationId) {
        try {
            // Фильтруем пустые выходы
            const validExits = currentExits.filter(exit => 
                exit.direction || exit.custom_name
            );

            const response = await fetch(`{{ url('admin/world-map') }}/${locationId}/exits`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    exits: validExits,
                }),
            });

            const data = await response.json();

            if (!response.ok) {
                console.error('Error saving exits:', data.error);
                throw new Error(data.error || 'Ошибка при сохранении выходов');
            }
        } catch (error) {
            console.error('Error saving exits:', error);
            throw error;
        }
    }

    // Закрытие модального окна при клике вне его
    document.getElementById('locationModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });
</script>
@endpush
@endsection

