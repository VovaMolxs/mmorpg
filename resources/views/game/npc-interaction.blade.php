<!-- Модальное окно для взаимодействия с NPC -->
<div id="npcInteractionModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-[#161615] rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-hidden flex flex-col">
        <!-- Заголовок -->
        <div class="flex justify-between items-center p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 bg-gray-200 dark:bg-gray-700 rounded-full flex items-center justify-center text-2xl font-bold">
                    <span id="npcModalAvatar">👤</span>
                </div>
                <div>
                    <h2 id="npcModalName" class="text-2xl font-bold"></h2>
                    <div id="npcModalBadges" class="flex flex-wrap gap-2 mt-1"></div>
                </div>
            </div>
            <button onclick="closeNpcInteractionModal()" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 text-2xl font-bold">✕</button>
        </div>

        <!-- Вкладки -->
        <div class="border-b border-gray-200 dark:border-gray-700">
            <div class="flex overflow-x-auto" id="npcModalTabs">
                <button onclick="switchNpcTab('overview')" class="npc-tab px-6 py-3 border-b-2 border-blue-600 text-blue-600 dark:text-blue-400 font-medium whitespace-nowrap" data-tab="overview">
                    Обзор
                </button>
                <button onclick="switchNpcTab('dialog')" class="npc-tab px-6 py-3 border-b-2 border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 whitespace-nowrap" data-tab="dialog">
                    Диалог
                </button>
                <button onclick="switchNpcTab('quests')" class="npc-tab px-6 py-3 border-b-2 border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 whitespace-nowrap" data-tab="quests">
                    Квесты
                </button>
                <button onclick="switchNpcTab('trade')" class="npc-tab px-6 py-3 border-b-2 border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 whitespace-nowrap" data-tab="trade">
                    Торговля
                </button>
                <button onclick="switchNpcTab('training')" class="npc-tab px-6 py-3 border-b-2 border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 whitespace-nowrap" data-tab="training">
                    Обучение
                </button>
            </div>
        </div>

        <!-- Содержимое вкладок -->
        <div class="flex-1 overflow-y-auto p-6">
            <!-- Вкладка: Обзор -->
            <div id="npcTabOverview" class="npc-tab-content hidden">
                <div class="space-y-6">
                    <!-- Описание NPC -->
                    <div>
                        <h3 class="text-lg font-semibold mb-2">Описание</h3>
                        <p id="npcOverviewDescription" class="text-gray-700 dark:text-gray-300"></p>
                    </div>

                    <!-- Статистика NPC -->
                    <div id="npcOverviewStats" class="hidden">
                        <h3 class="text-lg font-semibold mb-2">Характеристики</h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            <div class="bg-gray-50 dark:bg-gray-800 p-3 rounded">
                                <div class="text-sm text-gray-600 dark:text-gray-400">Уровень</div>
                                <div id="npcOverviewLevel" class="text-lg font-semibold"></div>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-800 p-3 rounded">
                                <div class="text-sm text-gray-600 dark:text-gray-400">Сила</div>
                                <div id="npcOverviewStrength" class="text-lg font-semibold"></div>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-800 p-3 rounded">
                                <div class="text-sm text-gray-600 dark:text-gray-400">Ловкость</div>
                                <div id="npcOverviewAgility" class="text-lg font-semibold"></div>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-800 p-3 rounded">
                                <div class="text-sm text-gray-600 dark:text-gray-400">Интеллект</div>
                                <div id="npcOverviewIntelligence" class="text-lg font-semibold"></div>
                            </div>
                        </div>

                        <!-- Здоровье и мана -->
                        <div class="mt-4 space-y-2">
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span>Здоровье</span>
                                    <span id="npcOverviewHealthText"></span>
                                </div>
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                                    <div id="npcOverviewHealthBar" class="bg-red-600 h-3 rounded-full transition-all duration-300"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span>Мана</span>
                                    <span id="npcOverviewManaText"></span>
                                </div>
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                                    <div id="npcOverviewManaBar" class="bg-blue-600 h-3 rounded-full transition-all duration-300"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Поведение и фракция -->
                    <div>
                        <h3 class="text-lg font-semibold mb-2">Информация</h3>
                        <div class="space-y-2 text-sm">
                            <div><span class="font-medium">Поведение:</span> <span id="npcOverviewBehavior"></span></div>
                            <div id="npcOverviewFaction" class="hidden"><span class="font-medium">Фракция:</span> <span></span></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Вкладка: Диалог -->
            <div id="npcTabDialog" class="npc-tab-content hidden">
                <div class="space-y-4">
                    <!-- История диалога -->
                    <div id="npcDialogHistory" class="space-y-3 max-h-64 overflow-y-auto p-4 bg-gray-50 dark:bg-gray-800 rounded-lg"></div>

                    <!-- Текущий диалог -->
                    <div id="npcDialogCurrent" class="hidden">
                        <div class="bg-blue-50 dark:bg-blue-900/30 p-4 rounded-lg mb-4">
                            <p id="npcDialogText" class="text-gray-800 dark:text-gray-200"></p>
                        </div>

                        <!-- Ответы -->
                        <div id="npcDialogAnswers" class="space-y-2"></div>
                    </div>

                    <!-- Нет доступных диалогов -->
                    <div id="npcDialogNoDialogs" class="text-center py-8 text-gray-500 dark:text-gray-400">
                        Нет доступных диалогов
                    </div>
                </div>
            </div>

            <!-- Вкладка: Квесты -->
            <div id="npcTabQuests" class="npc-tab-content hidden">
                <div class="space-y-6">
                    <!-- Квесты для сдачи -->
                    <div id="npcQuestsToTurnIn" class="hidden">
                        <h3 class="text-lg font-semibold mb-3">Квесты для сдачи</h3>
                        <div id="npcQuestsToTurnInList" class="space-y-4"></div>
                    </div>

                    <!-- Доступные квесты -->
                    <div id="npcQuestsAvailable" class="hidden">
                        <h3 class="text-lg font-semibold mb-3">Доступные квесты</h3>
                        <div id="npcQuestsAvailableList" class="space-y-4"></div>
                    </div>

                    <!-- Нет квестов -->
                    <div id="npcQuestsNone" class="text-center py-8 text-gray-500 dark:text-gray-400">
                        Нет доступных квестов
                    </div>
                </div>
            </div>

            <!-- Вкладка: Торговля -->
            <div id="npcTabTrade" class="npc-tab-content hidden">
                <div class="space-y-6">
                    <!-- Баланс игрока -->
                    <div class="bg-blue-50 dark:bg-blue-900/30 p-4 rounded-lg">
                        <div class="flex justify-between items-center">
                            <span class="font-medium">Ваше золото:</span>
                            <span id="npcTradePlayerGold" class="text-xl font-bold text-yellow-600 dark:text-yellow-400">0</span>
                        </div>
                    </div>

                    <!-- Подвкладки Купить/Продать -->
                    <div class="border-b border-gray-200 dark:border-gray-700">
                        <div class="flex">
                            <button
                                onclick="switchTradeSubTab('buy')"
                                id="tradeSubTabBuy"
                                class="px-6 py-3 border-b-2 border-blue-600 text-blue-600 dark:text-blue-400 font-medium"
                            >
                                Купить
                            </button>
                            <button
                                onclick="switchTradeSubTab('sell')"
                                id="tradeSubTabSell"
                                class="px-6 py-3 border-b-2 border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200"
                            >
                                Продать
                            </button>
                        </div>
                    </div>

                    <!-- Подвкладка: Купить -->
                    <div id="tradeSubTabBuyContent" class="trade-subtab-content">
                        <!-- Фильтры и поиск -->
                        <div class="flex gap-2 mb-4">
                            <input
                                type="text"
                                id="npcTradeSearch"
                                placeholder="Поиск товаров..."
                                class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-[#1C1C1A] text-gray-900 dark:text-gray-100"
                                onkeyup="filterNpcTradeItems()"
                            />
                            <select
                                id="npcTradeFilter"
                                onchange="filterNpcTradeItems()"
                                class="px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-[#1C1C1A] text-gray-900 dark:text-gray-100"
                            >
                                <option value="">Все типы</option>
                                <option value="weapon">Оружие</option>
                                <option value="armor">Броня</option>
                                <option value="jewelry">Бижутерия</option>
                                <option value="potion">Зелья</option>
                                <option value="resource">Ресурсы</option>
                            </select>
                        </div>

                        <!-- Список товаров -->
                        <div id="npcTradeItems" class="space-y-3"></div>

                        <!-- Нет товаров -->
                        <div id="npcTradeNoItems" class="text-center py-8 text-gray-500 dark:text-gray-400">
                            У этого торговца нет товаров
                        </div>
                    </div>

                    <!-- Подвкладка: Продать -->
                    <div id="tradeSubTabSellContent" class="trade-subtab-content hidden">
                        <!-- Фильтры и поиск -->
                        <div class="flex gap-2 mb-4">
                            <input
                                type="text"
                                id="npcSellSearch"
                                placeholder="Поиск предметов в инвентаре..."
                                class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-[#1C1C1A] text-gray-900 dark:text-gray-100"
                                onkeyup="filterPlayerInventoryItems()"
                            />
                            <select
                                id="npcSellFilter"
                                onchange="filterPlayerInventoryItems()"
                                class="px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-[#1C1C1A] text-gray-900 dark:text-gray-100"
                            >
                                <option value="">Все типы</option>
                                <option value="weapon">Оружие</option>
                                <option value="armor">Броня</option>
                                <option value="jewelry">Бижутерия</option>
                                <option value="potion">Зелья</option>
                                <option value="resource">Ресурсы</option>
                            </select>
                        </div>

                        <!-- Список предметов для продажи -->
                        <div id="npcSellItems" class="space-y-3"></div>

                        <!-- Нет предметов -->
                        <div id="npcSellNoItems" class="text-center py-8 text-gray-500 dark:text-gray-400">
                            Загрузка инвентаря...
                        </div>
                    </div>
                </div>
            </div>

            <!-- Вкладка: Обучение -->
            <div id="npcTabTraining" class="npc-tab-content hidden">
                <div class="space-y-6">
                    <!-- Список навыков -->
                    <div id="npcTrainingSkills" class="space-y-4"></div>

                    <!-- Нет навыков -->
                    <div id="npcTrainingNoSkills" class="text-center py-8 text-gray-500 dark:text-gray-400">
                        Нет доступных навыков для обучения
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для просмотра предмета в торговле -->
<div id="tradeItemModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-[#161615] rounded-lg p-6 max-w-md w-full max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
            <h3 id="tradeItemModalTitle" class="text-xl font-semibold"></h3>
            <button onclick="closeTradeItemModal()" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">✕</button>
        </div>
        <div id="tradeItemModalContent" class="space-y-4"></div>
    </div>
</div>

