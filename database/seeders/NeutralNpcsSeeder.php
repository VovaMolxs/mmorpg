<?php

namespace Database\Seeders;

use App\Models\Dialog;
use App\Models\DialogAnswer;
use App\Models\Location;
use App\Models\Npc;
use App\Models\NpcStat;
use Illuminate\Database\Seeder;

class NeutralNpcsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Создание нейтральных NPC с диалогами...');

        // Получаем локации по типам
        $locations = $this->getLocationsByType();

        // Создаем NPC для городов
        $this->createCityNpcs($locations['cities']);

        // Создаем NPC для деревень
        $this->createVillageNpcs($locations['villages']);

        // Создаем NPC для дорог
        $this->createRoadNpcs($locations['roads']);

        // Создаем NPC для специальных локаций
        $this->createSpecialLocationNpcs($locations['special']);

        $this->command->info('Нейтральные NPC успешно созданы!');
    }

    /**
     * Получить локации, сгруппированные по типам.
     */
    private function getLocationsByType(): array
    {
        $cities = Location::whereIn('type', ['square', 'street', 'shop', 'bank', 'building'])
            ->where('is_safe_zone', true)
            ->get();

        $villages = Location::where('type', 'village')
            ->where('is_safe_zone', true)
            ->get();

        $roads = Location::where('type', 'road')
            ->get();

        $special = Location::whereIn('type', ['forest', 'river', 'castle', 'cave'])
            ->get();

        return [
            'cities' => $cities,
            'villages' => $villages,
            'roads' => $roads,
            'special' => $special,
        ];
    }

    /**
     * Создать NPC для городов (8-12 NPC).
     */
    private function createCityNpcs($locations): void
    {
        if ($locations->isEmpty()) {
            return;
        }

        $cityLocations = $locations->shuffle()->values();
        $npcCount = min(12, max(8, $cityLocations->count()));

        // Торговцы (25%)
        $traderCount = (int) ceil($npcCount * 0.25);
        $this->createTraders($cityLocations->slice(0, $traderCount));

        // Учителя (15%)
        $teacherCount = (int) ceil($npcCount * 0.15);
        $this->createTeachers($cityLocations->slice($traderCount, $teacherCount));

        // Квестодатели (20%)
        $questGiverCount = (int) ceil($npcCount * 0.20);
        $this->createQuestGivers($cityLocations->slice($traderCount + $teacherCount, $questGiverCount));

        // Простые NPC (40%)
        $simpleCount = $npcCount - $traderCount - $teacherCount - $questGiverCount;
        $this->createSimpleNpcs($cityLocations->slice($traderCount + $teacherCount + $questGiverCount, $simpleCount));
    }

    /**
     * Создать NPC для деревень (3-6 NPC).
     */
    private function createVillageNpcs($locations): void
    {
        if ($locations->isEmpty()) {
            return;
        }

        foreach ($locations as $location) {
            $npcCount = rand(3, 6);
            $villageLocations = collect([$location]);

            // Старейшина (квестодатель)
            $this->createQuestGivers($villageLocations->take(1));

            // Торговец
            if ($npcCount > 1) {
                $this->createTraders($villageLocations->take(1));
            }

            // Простые жители
            $simpleCount = max(1, $npcCount - 2);
            $this->createSimpleNpcs($villageLocations->take($simpleCount));
        }
    }

    /**
     * Создать NPC для дорог (2-4 NPC).
     */
    private function createRoadNpcs($locations): void
    {
        if ($locations->isEmpty()) {
            return;
        }

        foreach ($locations as $location) {
            $npcCount = rand(2, 4);
            $roadLocations = collect([$location]);

            // Все простые NPC (странники, охотники)
            $this->createSimpleNpcs($roadLocations->take($npcCount));
        }
    }

    /**
     * Создать NPC для специальных локаций (1-2 NPC).
     */
    private function createSpecialLocationNpcs($locations): void
    {
        if ($locations->isEmpty()) {
            return;
        }

        foreach ($locations as $location) {
            $npcCount = rand(1, 2);
            $specialLocations = collect([$location]);

            // В зависимости от типа локации создаем соответствующих NPC
            if ($location->type === 'forest') {
                $this->createSimpleNpcs($specialLocations->take($npcCount), ['hunter', 'wanderer']);
            } elseif ($location->type === 'river') {
                $this->createSimpleNpcs($specialLocations->take($npcCount), ['fisherman']);
            } else {
                $this->createSimpleNpcs($specialLocations->take($npcCount));
            }
        }
    }

    /**
     * Создать торговцев.
     */
    private function createTraders($locations): void
    {
        $traderTypes = [
            [
                'name' => 'Борис Оружейник',
                'description' => 'Крупный мужчина с заляпанным сажей фартуком, стоит у наковальни. Его руки покрыты мозолями от долгой работы с металлом.',
                'subtype' => 'weaponsmith',
            ],
            [
                'name' => 'Алхимик Григорий',
                'description' => 'Худощавый старик в длинном плаще, окруженный колбами и склянками. От него пахнет травами и зельями.',
                'subtype' => 'potion',
            ],
            [
                'name' => 'Торговец Иван',
                'description' => 'Упитанный торговец с добродушной улыбкой. Его лавка завалена различными товарами.',
                'subtype' => 'food',
            ],
            [
                'name' => 'Брокер Сергей',
                'description' => 'Пронырливый человек в дорогой одежде. Он скупает и продает все подряд.',
                'subtype' => 'broker',
            ],
        ];

        foreach ($locations as $index => $location) {
            $traderData = $traderTypes[$index % count($traderTypes)];

            $npc = Npc::create([
                'name' => $traderData['name'],
                'type' => 'trader',
                'location_id' => $location->id,
                'description' => $traderData['description'],
                'is_merchant' => true,
                'is_teacher' => false,
                'is_quest_giver' => false,
                'is_hostile' => false,
                'faction_id' => null,
                'ai_behavior' => 'passive',
                'respawn_time' => 5,
            ]);

            $this->createNpcStats($npc, [
                'level' => rand(1, 5),
                'health_max' => rand(100, 150),
                'mana_max' => rand(50, 80),
            ]);

            $this->createTraderDialogs($npc, $traderData['subtype']);
        }
    }

    /**
     * Создать учителей навыков.
     */
    private function createTeachers($locations): void
    {
        $teacherTypes = [
            [
                'name' => 'Мастер Влад',
                'description' => 'Суровый воин в доспехах, с множеством шрамов. Его взгляд пронзителен, а движения точны.',
                'subtype' => 'combat',
            ],
            [
                'name' => 'Элвин Мудрый',
                'description' => 'Старый маг в длинных синих мантиях, читает древний фолиант. Его глаза светятся магической энергией.',
                'subtype' => 'magic',
            ],
            [
                'name' => 'Мастер-ремесленник Олег',
                'description' => 'Опытный мастер с мозолистыми руками. Он знает секреты всех ремесел.',
                'subtype' => 'crafting',
            ],
            [
                'name' => 'Тень',
                'description' => 'Загадочная фигура в темном плаще. Его движения бесшумны, а присутствие едва заметно.',
                'subtype' => 'stealth',
            ],
        ];

        foreach ($locations as $index => $location) {
            $teacherData = $teacherTypes[$index % count($teacherTypes)];

            $npc = Npc::create([
                'name' => $teacherData['name'],
                'type' => 'teacher',
                'location_id' => $location->id,
                'description' => $teacherData['description'],
                'is_merchant' => false,
                'is_teacher' => true,
                'is_quest_giver' => false,
                'is_hostile' => false,
                'faction_id' => null,
                'ai_behavior' => 'passive',
                'respawn_time' => 5,
            ]);

            $this->createNpcStats($npc, [
                'level' => rand(5, 10),
                'health_max' => rand(150, 200),
                'mana_max' => rand(100, 150),
            ]);

            $this->createTeacherDialogs($npc, $teacherData['subtype']);
        }
    }

    /**
     * Создать квестодателей.
     */
    private function createQuestGivers($locations): void
    {
        $questGiverTypes = [
            [
                'name' => 'Старейшина Олег',
                'description' => 'Седеющий старик с добрыми глазами, опирается на посох. Его лицо покрыто морщинами мудрости.',
                'subtype' => 'elder',
            ],
            [
                'name' => 'Капитан стражи Марк',
                'description' => 'Суровый воин в доспехах стражи. Его взгляд бдителен, а рука всегда на рукояти меча.',
                'subtype' => 'guard',
            ],
            [
                'name' => 'Исследователь Алиса',
                'description' => 'Молодая женщина в походной одежде с картами и компасом. Ее глаза горят любопытством.',
                'subtype' => 'explorer',
            ],
            [
                'name' => 'Коллекционер Артем',
                'description' => 'Эксцентричный человек, окруженный различными артефактами и диковинками.',
                'subtype' => 'collector',
            ],
        ];

        foreach ($locations as $index => $location) {
            $questGiverData = $questGiverTypes[$index % count($questGiverTypes)];

            $npc = Npc::create([
                'name' => $questGiverData['name'],
                'type' => 'quest_giver',
                'location_id' => $location->id,
                'description' => $questGiverData['description'],
                'is_merchant' => false,
                'is_teacher' => false,
                'is_quest_giver' => true,
                'is_hostile' => false,
                'faction_id' => null,
                'ai_behavior' => 'passive',
                'respawn_time' => 5,
            ]);

            $this->createNpcStats($npc, [
                'level' => rand(3, 8),
                'health_max' => rand(120, 180),
                'mana_max' => rand(60, 120),
            ]);

            $this->createQuestGiverDialogs($npc, $questGiverData['subtype']);
        }
    }

    /**
     * Создать простых NPC.
     */
    private function createSimpleNpcs($locations, array $subtypes = []): void
    {
        $simpleTypes = [
            [
                'name' => 'Горожанин',
                'description' => 'Обычный житель города, спешит по своим делам.',
                'subtype' => 'citizen',
            ],
            [
                'name' => 'Странник',
                'description' => 'Запыленный путник с дорожным посохом. Видно, что он много путешествовал.',
                'subtype' => 'wanderer',
            ],
            [
                'name' => 'Охотник',
                'description' => 'Мужественный человек с луком за спиной. На поясе висит добыча.',
                'subtype' => 'hunter',
            ],
            [
                'name' => 'Рыбак',
                'description' => 'Спокойный человек с удочкой. Он знает все лучшие места для рыбалки.',
                'subtype' => 'fisherman',
            ],
            [
                'name' => 'Торговец',
                'description' => 'Небольшой торговец с корзиной товаров.',
                'subtype' => 'merchant',
            ],
            [
                'name' => 'Стражник',
                'description' => 'Бдительный стражник, патрулирующий окрестности.',
                'subtype' => 'guard',
            ],
        ];

        // Если указаны подтипы, используем их
        if (! empty($subtypes)) {
            $filteredTypes = array_filter($simpleTypes, function ($type) use ($subtypes) {
                return in_array($type['subtype'], $subtypes);
            });
            if (! empty($filteredTypes)) {
                $simpleTypes = array_values($filteredTypes);
            }
        }

        foreach ($locations as $index => $location) {
            $simpleData = $simpleTypes[$index % count($simpleTypes)];

            $npc = Npc::create([
                'name' => $simpleData['name'].' '.($index + 1),
                'type' => 'simple',
                'location_id' => $location->id,
                'description' => $simpleData['description'],
                'is_merchant' => false,
                'is_teacher' => false,
                'is_quest_giver' => false,
                'is_hostile' => false,
                'faction_id' => null,
                'ai_behavior' => 'passive',
                'respawn_time' => 5,
            ]);

            $this->createNpcStats($npc, [
                'level' => rand(1, 3),
                'health_max' => rand(80, 120),
                'mana_max' => rand(30, 60),
            ]);

            $this->createSimpleNpcDialogs($npc, $simpleData['subtype']);
        }
    }

    /**
     * Создать статистику для NPC.
     */
    private function createNpcStats(Npc $npc, array $overrides = []): void
    {
        $level = $overrides['level'] ?? rand(1, 10);
        $healthMax = $overrides['health_max'] ?? rand(100, 200);
        $manaMax = $overrides['mana_max'] ?? rand(50, 100);

        NpcStat::create([
            'npc_id' => $npc->id,
            'level' => $level,
            'health_max' => $healthMax,
            'health_current' => $healthMax,
            'mana_max' => $manaMax,
            'mana_current' => $manaMax,
            'strength' => rand(5, 15),
            'agility' => rand(5, 15),
            'intelligence' => rand(5, 15),
            'attack_power' => rand(5, 20),
            'defense' => rand(5, 20),
            'magic_defense' => rand(5, 20),
            'accuracy' => rand(50, 80),
            'dodge' => rand(0, 15),
            'critical_chance' => rand(5, 15),
            'critical_power' => 1.5,
            'experience_reward' => 0,
            'gold_reward_min' => 0,
            'gold_reward_max' => 0,
        ]);
    }

    /**
     * Создать диалоги для торговца.
     */
    private function createTraderDialogs(Npc $npc, string $subtype): void
    {
        switch ($subtype) {
            case 'weaponsmith':
                $this->createWeaponsmithDialogs($npc);
                break;
            case 'potion':
                $this->createPotionTraderDialogs($npc);
                break;
            case 'food':
                $this->createFoodTraderDialogs($npc);
                break;
            case 'broker':
                $this->createBrokerDialogs($npc);
                break;
        }
    }

    /**
     * Создать диалоги для оружейника.
     */
    private function createWeaponsmithDialogs(Npc $npc): void
    {
        // Начальный диалог
        $initialDialog = Dialog::create([
            'npc_id' => $npc->id,
            'text' => 'Приветствую! Нужно новое оружие? У меня лучшие клинки в городе!',
            'is_initial' => true,
            'min_level' => 1,
        ]);

        // Диалог о торговле
        $tradeDialog = Dialog::create([
            'npc_id' => $npc->id,
            'parent_dialog_id' => $initialDialog->id,
            'text' => 'Взгляни на эти мечи - сталь высшего качества! Каждый клинок проверен лично мной. Что тебя интересует?',
            'is_initial' => false,
        ]);

        // Диалог о городе
        $cityDialog = Dialog::create([
            'npc_id' => $npc->id,
            'parent_dialog_id' => $initialDialog->id,
            'text' => 'В городе все спокойно, слава богу. Хотя в последнее время странные слухи ходят о лесах на севере...',
            'is_initial' => false,
        ]);

        // Диалог о ремесле
        $craftDialog = Dialog::create([
            'npc_id' => $npc->id,
            'parent_dialog_id' => $initialDialog->id,
            'text' => 'Я работаю с металлом уже тридцать лет. Секрет хорошего оружия - в правильной закалке и терпении.',
            'is_initial' => false,
        ]);

        // Завершающий диалог
        $farewellDialog = Dialog::create([
            'npc_id' => $npc->id,
            'parent_dialog_id' => $initialDialog->id,
            'text' => 'Хорошо, заходи еще! Всегда рад помочь.',
            'is_initial' => false,
        ]);

        // Ответы на начальный диалог
        DialogAnswer::create([
            'dialog_id' => $initialDialog->id,
            'text' => 'Покажи что у тебя есть',
            'next_dialog_id' => $tradeDialog->id,
        ]);

        DialogAnswer::create([
            'dialog_id' => $initialDialog->id,
            'text' => 'Как дела в городе?',
            'next_dialog_id' => $cityDialog->id,
        ]);

        DialogAnswer::create([
            'dialog_id' => $initialDialog->id,
            'text' => 'Расскажи о своем ремесле',
            'next_dialog_id' => $craftDialog->id,
        ]);

        DialogAnswer::create([
            'dialog_id' => $initialDialog->id,
            'text' => 'Спасибо, я просто смотрю',
            'next_dialog_id' => $farewellDialog->id,
        ]);

        // Ответы на диалог о торговле
        DialogAnswer::create([
            'dialog_id' => $tradeDialog->id,
            'text' => 'Покажи мечи',
            'next_dialog_id' => null,
        ]);

        DialogAnswer::create([
            'dialog_id' => $tradeDialog->id,
            'text' => 'Есть что-то для дальнего боя?',
            'next_dialog_id' => null,
        ]);

        DialogAnswer::create([
            'dialog_id' => $tradeDialog->id,
            'text' => 'Назад',
            'next_dialog_id' => $initialDialog->id,
        ]);

        // Ответы на диалог о городе
        DialogAnswer::create([
            'dialog_id' => $cityDialog->id,
            'text' => 'Что за слухи?',
            'next_dialog_id' => null,
        ]);

        DialogAnswer::create([
            'dialog_id' => $cityDialog->id,
            'text' => 'Понятно, спасибо',
            'next_dialog_id' => $initialDialog->id,
        ]);

        // Ответы на диалог о ремесле
        DialogAnswer::create([
            'dialog_id' => $craftDialog->id,
            'text' => 'Интересно, продолжай',
            'next_dialog_id' => null,
        ]);

        DialogAnswer::create([
            'dialog_id' => $craftDialog->id,
            'text' => 'Спасибо за рассказ',
            'next_dialog_id' => $initialDialog->id,
        ]);
    }

    /**
     * Создать диалоги для торговца зельями.
     */
    private function createPotionTraderDialogs(Npc $npc): void
    {
        $initialDialog = Dialog::create([
            'npc_id' => $npc->id,
            'text' => 'Добро пожаловать в мою лавку! Здесь ты найдешь зелья на любой случай жизни. Что тебе нужно?',
            'is_initial' => true,
            'min_level' => 1,
        ]);

        $tradeDialog = Dialog::create([
            'npc_id' => $npc->id,
            'parent_dialog_id' => $initialDialog->id,
            'text' => 'У меня есть зелья лечения, маны и даже временного усиления! Какое тебе нужно?',
            'is_initial' => false,
        ]);

        $recipeDialog = Dialog::create([
            'npc_id' => $npc->id,
            'parent_dialog_id' => $initialDialog->id,
            'text' => 'Секреты алхимии передаются из поколения в поколение. Главное - правильные пропорции и качественные ингредиенты.',
            'is_initial' => false,
        ]);

        DialogAnswer::create([
            'dialog_id' => $initialDialog->id,
            'text' => 'Покажи зелья',
            'next_dialog_id' => $tradeDialog->id,
        ]);

        DialogAnswer::create([
            'dialog_id' => $initialDialog->id,
            'text' => 'Расскажи о рецептах',
            'next_dialog_id' => $recipeDialog->id,
        ]);

        DialogAnswer::create([
            'dialog_id' => $initialDialog->id,
            'text' => 'До свидания',
            'next_dialog_id' => null,
        ]);
    }

    /**
     * Создать диалоги для торговца едой.
     */
    private function createFoodTraderDialogs(Npc $npc): void
    {
        $initialDialog = Dialog::create([
            'npc_id' => $npc->id,
            'text' => 'Привет! Хочешь что-нибудь перекусить? У меня свежий хлеб, сыр и даже мясо!',
            'is_initial' => true,
            'min_level' => 1,
        ]);

        $tradeDialog = Dialog::create([
            'npc_id' => $npc->id,
            'parent_dialog_id' => $initialDialog->id,
            'text' => 'Все самое свежее! Что тебе по душе?',
            'is_initial' => false,
        ]);

        DialogAnswer::create([
            'dialog_id' => $initialDialog->id,
            'text' => 'Покажи еду',
            'next_dialog_id' => $tradeDialog->id,
        ]);

        DialogAnswer::create([
            'dialog_id' => $initialDialog->id,
            'text' => 'Нет, спасибо',
            'next_dialog_id' => null,
        ]);
    }

    /**
     * Создать диалоги для брокера.
     */
    private function createBrokerDialogs(Npc $npc): void
    {
        $initialDialog = Dialog::create([
            'npc_id' => $npc->id,
            'text' => 'Здравствуй! Я скупаю и продаю различные предметы. Может, у тебя есть что-то интересное?',
            'is_initial' => true,
            'min_level' => 1,
        ]);

        $buyDialog = Dialog::create([
            'npc_id' => $npc->id,
            'parent_dialog_id' => $initialDialog->id,
            'text' => 'Покажи, что у тебя есть. Я оценю по справедливой цене!',
            'is_initial' => false,
        ]);

        $sellDialog = Dialog::create([
            'npc_id' => $npc->id,
            'parent_dialog_id' => $initialDialog->id,
            'text' => 'У меня есть много интересных вещей. Что тебя интересует?',
            'is_initial' => false,
        ]);

        DialogAnswer::create([
            'dialog_id' => $initialDialog->id,
            'text' => 'Хочу продать',
            'next_dialog_id' => $buyDialog->id,
        ]);

        DialogAnswer::create([
            'dialog_id' => $initialDialog->id,
            'text' => 'Хочу купить',
            'next_dialog_id' => $sellDialog->id,
        ]);

        DialogAnswer::create([
            'dialog_id' => $initialDialog->id,
            'text' => 'Просто смотрю',
            'next_dialog_id' => null,
        ]);
    }

    /**
     * Создать диалоги для учителя.
     */
    private function createTeacherDialogs(Npc $npc, string $subtype): void
    {
        switch ($subtype) {
            case 'combat':
                $this->createCombatTeacherDialogs($npc);
                break;
            case 'magic':
                $this->createMagicTeacherDialogs($npc);
                break;
            case 'crafting':
                $this->createCraftingTeacherDialogs($npc);
                break;
            case 'stealth':
                $this->createStealthTeacherDialogs($npc);
                break;
        }
    }

    /**
     * Создать диалоги для учителя боевых искусств.
     */
    private function createCombatTeacherDialogs(Npc $npc): void
    {
        $initialDialog = Dialog::create([
            'npc_id' => $npc->id,
            'text' => 'А, новый ученик? Хочешь научиться сражаться? Я могу обучить тебя основам боевого мастерства.',
            'is_initial' => true,
            'min_level' => 3,
        ]);

        $teachDialog = Dialog::create([
            'npc_id' => $npc->id,
            'parent_dialog_id' => $initialDialog->id,
            'text' => 'Хорошо! Первое правило боя - никогда не теряй бдительность. Второе - знай своего врага.',
            'is_initial' => false,
        ]);

        $philosophyDialog = Dialog::create([
            'npc_id' => $npc->id,
            'parent_dialog_id' => $initialDialog->id,
            'text' => 'Боевое искусство - это не только умение владеть оружием, но и дисциплина ума и духа.',
            'is_initial' => false,
        ]);

        DialogAnswer::create([
            'dialog_id' => $initialDialog->id,
            'text' => 'Да, хочу учиться',
            'next_dialog_id' => $teachDialog->id,
        ]);

        DialogAnswer::create([
            'dialog_id' => $initialDialog->id,
            'text' => 'Расскажи о боевых искусствах',
            'next_dialog_id' => $philosophyDialog->id,
        ]);

        DialogAnswer::create([
            'dialog_id' => $initialDialog->id,
            'text' => 'Может быть позже',
            'next_dialog_id' => null,
        ]);
    }

    /**
     * Создать диалоги для учителя магии.
     */
    private function createMagicTeacherDialogs(Npc $npc): void
    {
        $initialDialog = Dialog::create([
            'npc_id' => $npc->id,
            'text' => 'А, новый ученик? Интересуешься магическими искусствами? Я могу обучить тебя основам магии, если у тебя есть способности.',
            'is_initial' => true,
            'min_level' => 3,
        ]);

        $teachDialog = Dialog::create([
            'npc_id' => $npc->id,
            'parent_dialog_id' => $initialDialog->id,
            'text' => 'Магия - это искусство управления энергией. Начнем с простых заклинаний.',
            'is_initial' => false,
        ]);

        $theoryDialog = Dialog::create([
            'npc_id' => $npc->id,
            'parent_dialog_id' => $initialDialog->id,
            'text' => 'Магия существует в трех формах: элементальная, целительная и темная. Каждая требует особого подхода.',
            'is_initial' => false,
        ]);

        DialogAnswer::create([
            'dialog_id' => $initialDialog->id,
            'text' => 'Обучи меня магии',
            'next_dialog_id' => $teachDialog->id,
        ]);

        DialogAnswer::create([
            'dialog_id' => $initialDialog->id,
            'text' => 'Расскажи о теории магии',
            'next_dialog_id' => $theoryDialog->id,
        ]);

        DialogAnswer::create([
            'dialog_id' => $initialDialog->id,
            'text' => 'Я еще не готов',
            'next_dialog_id' => null,
        ]);
    }

    /**
     * Создать диалоги для учителя ремесел.
     */
    private function createCraftingTeacherDialogs(Npc $npc): void
    {
        $initialDialog = Dialog::create([
            'npc_id' => $npc->id,
            'text' => 'Привет! Хочешь научиться ремеслам? Я могу научить тебя создавать полезные вещи своими руками.',
            'is_initial' => true,
            'min_level' => 2,
        ]);

        $teachDialog = Dialog::create([
            'npc_id' => $npc->id,
            'parent_dialog_id' => $initialDialog->id,
            'text' => 'Ремесла включают кузнечное дело, алхимию, кожевничество и многое другое. Что тебя интересует?',
            'is_initial' => false,
        ]);

        DialogAnswer::create([
            'dialog_id' => $initialDialog->id,
            'text' => 'Хочу учиться',
            'next_dialog_id' => $teachDialog->id,
        ]);

        DialogAnswer::create([
            'dialog_id' => $initialDialog->id,
            'text' => 'Позже',
            'next_dialog_id' => null,
        ]);
    }

    /**
     * Создать диалоги для учителя скрытности.
     */
    private function createStealthTeacherDialogs(Npc $npc): void
    {
        $initialDialog = Dialog::create([
            'npc_id' => $npc->id,
            'text' => 'Ты хочешь научиться двигаться незаметно? Это полезный навык для выживания.',
            'is_initial' => true,
            'min_level' => 4,
        ]);

        $teachDialog = Dialog::create([
            'npc_id' => $npc->id,
            'parent_dialog_id' => $initialDialog->id,
            'text' => 'Скрытность - это искусство быть невидимым. Начнем с основ.',
            'is_initial' => false,
        ]);

        DialogAnswer::create([
            'dialog_id' => $initialDialog->id,
            'text' => 'Обучи меня',
            'next_dialog_id' => $teachDialog->id,
        ]);

        DialogAnswer::create([
            'dialog_id' => $initialDialog->id,
            'text' => 'Не сейчас',
            'next_dialog_id' => null,
        ]);
    }

    /**
     * Создать диалоги для квестодателя.
     */
    private function createQuestGiverDialogs(Npc $npc, string $subtype): void
    {
        switch ($subtype) {
            case 'elder':
                $this->createElderDialogs($npc);
                break;
            case 'guard':
                $this->createGuardDialogs($npc);
                break;
            case 'explorer':
                $this->createExplorerDialogs($npc);
                break;
            case 'collector':
                $this->createCollectorDialogs($npc);
                break;
        }
    }

    /**
     * Создать диалоги для старейшины.
     */
    private function createElderDialogs(Npc $npc): void
    {
        $initialDialog = Dialog::create([
            'npc_id' => $npc->id,
            'text' => 'Приветствую, путник. Наша деревня нуждается в помощи. Можешь ли ты помочь нам?',
            'is_initial' => true,
            'min_level' => 1,
        ]);

        $questDialog = Dialog::create([
            'npc_id' => $npc->id,
            'parent_dialog_id' => $initialDialog->id,
            'text' => 'Волки нападают на наши стада. Не мог бы ты помочь нам избавиться от них?',
            'is_initial' => false,
        ]);

        $infoDialog = Dialog::create([
            'npc_id' => $npc->id,
            'parent_dialog_id' => $initialDialog->id,
            'text' => 'Наша деревня существует уже много лет. Мы живем мирно, но иногда нужна помощь извне.',
            'is_initial' => false,
        ]);

        DialogAnswer::create([
            'dialog_id' => $initialDialog->id,
            'text' => 'Конечно, чем могу помочь?',
            'next_dialog_id' => $questDialog->id,
        ]);

        DialogAnswer::create([
            'dialog_id' => $initialDialog->id,
            'text' => 'Расскажи о деревне',
            'next_dialog_id' => $infoDialog->id,
        ]);

        DialogAnswer::create([
            'dialog_id' => $initialDialog->id,
            'text' => 'Извини, я спешу',
            'next_dialog_id' => null,
        ]);
    }

    /**
     * Создать диалоги для капитана стражи.
     */
    private function createGuardDialogs(Npc $npc): void
    {
        $initialDialog = Dialog::create([
            'npc_id' => $npc->id,
            'text' => 'Приветствую! Я капитан городской стражи. Если ты ищешь работу, у меня всегда есть задания для смелых.',
            'is_initial' => true,
            'min_level' => 2,
        ]);

        $questDialog = Dialog::create([
            'npc_id' => $npc->id,
            'parent_dialog_id' => $initialDialog->id,
            'text' => 'На дорогах появились разбойники. Нужен кто-то, кто сможет с ними разобраться.',
            'is_initial' => false,
        ]);

        DialogAnswer::create([
            'dialog_id' => $initialDialog->id,
            'text' => 'Какие задания у тебя есть?',
            'next_dialog_id' => $questDialog->id,
        ]);

        DialogAnswer::create([
            'dialog_id' => $initialDialog->id,
            'text' => 'Пока нет',
            'next_dialog_id' => null,
        ]);
    }

    /**
     * Создать диалоги для исследователя.
     */
    private function createExplorerDialogs(Npc $npc): void
    {
        $initialDialog = Dialog::create([
            'npc_id' => $npc->id,
            'text' => 'Привет! Я исследователь и ищу помощников для изучения неизведанных мест. Интересует?',
            'is_initial' => true,
            'min_level' => 3,
        ]);

        $questDialog = Dialog::create([
            'npc_id' => $npc->id,
            'parent_dialog_id' => $initialDialog->id,
            'text' => 'Мне нужно исследовать древние руины на севере. Поможешь?',
            'is_initial' => false,
        ]);

        DialogAnswer::create([
            'dialog_id' => $initialDialog->id,
            'text' => 'Да, интересно!',
            'next_dialog_id' => $questDialog->id,
        ]);

        DialogAnswer::create([
            'dialog_id' => $initialDialog->id,
            'text' => 'Не сейчас',
            'next_dialog_id' => null,
        ]);
    }

    /**
     * Создать диалоги для коллекционера.
     */
    private function createCollectorDialogs(Npc $npc): void
    {
        $initialDialog = Dialog::create([
            'npc_id' => $npc->id,
            'text' => 'О, посетитель! Я коллекционирую редкие артефакты. Может, у тебя есть что-то интересное?',
            'is_initial' => true,
            'min_level' => 2,
        ]);

        $questDialog = Dialog::create([
            'npc_id' => $npc->id,
            'parent_dialog_id' => $initialDialog->id,
            'text' => 'Мне нужны редкие кристаллы из пещер. Принесешь мне их?',
            'is_initial' => false,
        ]);

        DialogAnswer::create([
            'dialog_id' => $initialDialog->id,
            'text' => 'Какие предметы тебе нужны?',
            'next_dialog_id' => $questDialog->id,
        ]);

        DialogAnswer::create([
            'dialog_id' => $initialDialog->id,
            'text' => 'Просто смотрю',
            'next_dialog_id' => null,
        ]);
    }

    /**
     * Создать диалоги для простого NPC.
     */
    private function createSimpleNpcDialogs(Npc $npc, string $subtype): void
    {
        switch ($subtype) {
            case 'citizen':
                $this->createCitizenDialogs($npc);
                break;
            case 'wanderer':
                $this->createWandererDialogs($npc);
                break;
            case 'hunter':
                $this->createHunterDialogs($npc);
                break;
            case 'fisherman':
                $this->createFishermanDialogs($npc);
                break;
            case 'merchant':
                $this->createSimpleMerchantDialogs($npc);
                break;
            case 'guard':
                $this->createSimpleGuardDialogs($npc);
                break;
            default:
                $this->createGenericSimpleDialogs($npc);
        }
    }

    /**
     * Создать диалоги для горожанина.
     */
    private function createCitizenDialogs(Npc $npc): void
    {
        $initialDialog = Dialog::create([
            'npc_id' => $npc->id,
            'text' => 'Здравствуй! Прекрасный день, не правда ли?',
            'is_initial' => true,
            'min_level' => 1,
        ]);

        $cityDialog = Dialog::create([
            'npc_id' => $npc->id,
            'parent_dialog_id' => $initialDialog->id,
            'text' => 'В нашем городе всегда что-то происходит. Торговцы, путешественники, авантюристы...',
            'is_initial' => false,
        ]);

        DialogAnswer::create([
            'dialog_id' => $initialDialog->id,
            'text' => 'Да, действительно',
            'next_dialog_id' => $cityDialog->id,
        ]);

        DialogAnswer::create([
            'dialog_id' => $initialDialog->id,
            'text' => 'До свидания',
            'next_dialog_id' => null,
        ]);
    }

    /**
     * Создать диалоги для странника.
     */
    private function createWandererDialogs(Npc $npc): void
    {
        $initialDialog = Dialog::create([
            'npc_id' => $npc->id,
            'text' => 'Привет, путник! Я тоже путешествую по этим дорогам. Куда путь держишь?',
            'is_initial' => true,
            'min_level' => 1,
        ]);

        $travelDialog = Dialog::create([
            'npc_id' => $npc->id,
            'parent_dialog_id' => $initialDialog->id,
            'text' => 'Я видел много интересных мест. Леса на севере особенно красивы, но и опасны.',
            'is_initial' => false,
        ]);

        DialogAnswer::create([
            'dialog_id' => $initialDialog->id,
            'text' => 'Расскажи о дорогах',
            'next_dialog_id' => $travelDialog->id,
        ]);

        DialogAnswer::create([
            'dialog_id' => $initialDialog->id,
            'text' => 'Удачи в пути',
            'next_dialog_id' => null,
        ]);
    }

    /**
     * Создать диалоги для охотника.
     */
    private function createHunterDialogs(Npc $npc): void
    {
        $initialDialog = Dialog::create([
            'npc_id' => $npc->id,
            'text' => 'Привет! Я охотник. В этих лесах водится хорошая дичь.',
            'is_initial' => true,
            'min_level' => 2,
        ]);

        $huntDialog = Dialog::create([
            'npc_id' => $npc->id,
            'parent_dialog_id' => $initialDialog->id,
            'text' => 'Лучшее место для охоты - глухой лес. Но будь осторожен, там водятся не только олени.',
            'is_initial' => false,
        ]);

        DialogAnswer::create([
            'dialog_id' => $initialDialog->id,
            'text' => 'Где лучше охотиться?',
            'next_dialog_id' => $huntDialog->id,
        ]);

        DialogAnswer::create([
            'dialog_id' => $initialDialog->id,
            'text' => 'Удачи на охоте',
            'next_dialog_id' => null,
        ]);
    }

    /**
     * Создать диалоги для рыбака.
     */
    private function createFishermanDialogs(Npc $npc): void
    {
        $initialDialog = Dialog::create([
            'npc_id' => $npc->id,
            'text' => 'Добро пожаловать! Рыбалка - это искусство терпения. Хочешь попробовать?',
            'is_initial' => true,
            'min_level' => 1,
        ]);

        $fishDialog = Dialog::create([
            'npc_id' => $npc->id,
            'parent_dialog_id' => $initialDialog->id,
            'text' => 'Лучшее время для рыбалки - раннее утро. Рыба тогда особенно активна.',
            'is_initial' => false,
        ]);

        DialogAnswer::create([
            'dialog_id' => $initialDialog->id,
            'text' => 'Расскажи о рыбалке',
            'next_dialog_id' => $fishDialog->id,
        ]);

        DialogAnswer::create([
            'dialog_id' => $initialDialog->id,
            'text' => 'Может быть позже',
            'next_dialog_id' => null,
        ]);
    }

    /**
     * Создать диалоги для простого торговца.
     */
    private function createSimpleMerchantDialogs(Npc $npc): void
    {
        $initialDialog = Dialog::create([
            'npc_id' => $npc->id,
            'text' => 'Привет! Хочешь что-нибудь купить? У меня есть разные мелочи.',
            'is_initial' => true,
            'min_level' => 1,
        ]);

        DialogAnswer::create([
            'dialog_id' => $initialDialog->id,
            'text' => 'Покажи товары',
            'next_dialog_id' => null,
        ]);

        DialogAnswer::create([
            'dialog_id' => $initialDialog->id,
            'text' => 'Нет, спасибо',
            'next_dialog_id' => null,
        ]);
    }

    /**
     * Создать диалоги для простого стражника.
     */
    private function createSimpleGuardDialogs(Npc $npc): void
    {
        $initialDialog = Dialog::create([
            'npc_id' => $npc->id,
            'text' => 'Стой! Кто идет? О, извини, ты выглядишь мирно. Проходи.',
            'is_initial' => true,
            'min_level' => 1,
        ]);

        $infoDialog = Dialog::create([
            'npc_id' => $npc->id,
            'parent_dialog_id' => $initialDialog->id,
            'text' => 'В последнее время на дорогах неспокойно. Будь осторожен в путешествиях.',
            'is_initial' => false,
        ]);

        DialogAnswer::create([
            'dialog_id' => $initialDialog->id,
            'text' => 'Что происходит в округе?',
            'next_dialog_id' => $infoDialog->id,
        ]);

        DialogAnswer::create([
            'dialog_id' => $initialDialog->id,
            'text' => 'Спасибо',
            'next_dialog_id' => null,
        ]);
    }

    /**
     * Создать общие диалоги для простого NPC.
     */
    private function createGenericSimpleDialogs(Npc $npc): void
    {
        $initialDialog = Dialog::create([
            'npc_id' => $npc->id,
            'text' => 'Здравствуй! Чем могу помочь?',
            'is_initial' => true,
            'min_level' => 1,
        ]);

        DialogAnswer::create([
            'dialog_id' => $initialDialog->id,
            'text' => 'Ничем, просто поздороваться',
            'next_dialog_id' => null,
        ]);
    }
}
