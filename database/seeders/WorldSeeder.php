<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\LocationExit;
use Illuminate\Database\Seeder;

class WorldSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Создание тестового мира...');

        // Очищаем существующие данные
        LocationExit::query()->delete();
        Location::query()->delete();

        // Создаем локации
        $locations = $this->createLocations();

        // Создаем переходы между локациями
        $this->createExits($locations);

        $this->command->info('Тестовый мир успешно создан!');
        $this->command->info('Всего локаций: '.count($locations));
    }

    /**
     * Создать все локации мира.
     */
    private function createLocations(): array
    {
        $locations = [];

        // ========== ГОРОД ==========
        // Главная площадь (0, 0) - стартовая локация
        $locations['main_square'] = Location::create([
            'name' => 'Главная площадь',
            'description' => 'Центральная площадь города. Здесь всегда многолюдно, торговцы предлагают свои товары, а путешественники собираются в группы для дальних походов.',
            'type' => 'square',
            'is_safe_zone' => true,
            'min_level' => null,
            'max_level' => null,
            'coordinate_x' => 0,
            'coordinate_y' => 0,
        ]);

        // Улица торговцев (0, -1)
        $locations['merchant_street'] = Location::create([
            'name' => 'Улица торговцев',
            'description' => 'Шумная улица, заполненная лавками и магазинами. Здесь можно купить все необходимое для путешествий.',
            'type' => 'street',
            'is_safe_zone' => true,
            'min_level' => null,
            'max_level' => null,
            'coordinate_x' => 0,
            'coordinate_y' => -1,
        ]);

        // Магазин оружия (0, -2)
        $locations['weapon_shop'] = Location::create([
            'name' => 'Магазин оружия',
            'description' => 'Мастерская оружейника. Стены увешаны различным оружием: мечи, топоры, луки. Запах металла и масла витает в воздухе.',
            'type' => 'shop',
            'is_safe_zone' => true,
            'min_level' => null,
            'max_level' => null,
            'coordinate_x' => 0,
            'coordinate_y' => -2,
        ]);

        // Банк (1, 0)
        $locations['bank'] = Location::create([
            'name' => 'Городской банк',
            'description' => 'Массивное каменное здание с толстыми стенами. Здесь хранятся сокровища и ценности горожан.',
            'type' => 'bank',
            'is_safe_zone' => true,
            'min_level' => null,
            'max_level' => null,
            'coordinate_x' => 1,
            'coordinate_y' => 0,
        ]);

        // Переулок оружейников (-1, -1)
        $locations['blacksmith_alley'] = Location::create([
            'name' => 'Переулок оружейников',
            'description' => 'Узкий переулок, где работают кузнецы. Слышен звон молотов и виден дым из кузниц.',
            'type' => 'street',
            'is_safe_zone' => true,
            'min_level' => null,
            'max_level' => null,
            'coordinate_x' => -1,
            'coordinate_y' => -1,
        ]);

        // Городские ворота (0, 1)
        $locations['city_gates'] = Location::create([
            'name' => 'Городские ворота',
            'description' => 'Массивные деревянные ворота с железными засовами. Стража внимательно следит за всеми входящими и выходящими.',
            'type' => 'building',
            'is_safe_zone' => true,
            'min_level' => null,
            'max_level' => null,
            'coordinate_x' => 0,
            'coordinate_y' => 1,
        ]);

        // ========== ДОРОГИ ==========
        // Дорога на север (0, 2)
        $locations['north_road'] = Location::create([
            'name' => 'Дорога на север',
            'description' => 'Широкая проезжая дорога, уходящая на север. По обочинам растут высокие деревья.',
            'type' => 'road',
            'is_safe_zone' => false,
            'min_level' => 1,
            'max_level' => null,
            'coordinate_x' => 0,
            'coordinate_y' => 2,
        ]);

        // Дорога на восток (2, 0)
        $locations['east_road'] = Location::create([
            'name' => 'Дорога на восток',
            'description' => 'Пыльная дорога, ведущая на восток. Вдали виднеются горы.',
            'type' => 'road',
            'is_safe_zone' => false,
            'min_level' => 1,
            'max_level' => null,
            'coordinate_x' => 2,
            'coordinate_y' => 0,
        ]);

        // Дорога на запад (-2, 0)
        $locations['west_road'] = Location::create([
            'name' => 'Дорога на запад',
            'description' => 'Извилистая дорога, уходящая на запад. По пути встречаются заброшенные фермы.',
            'type' => 'road',
            'is_safe_zone' => false,
            'min_level' => 1,
            'max_level' => null,
            'coordinate_x' => -2,
            'coordinate_y' => 0,
        ]);

        // ========== ЛЕС ==========
        // Лесная опушка (0, 3)
        $locations['forest_edge'] = Location::create([
            'name' => 'Лесная опушка',
            'description' => 'Граница между цивилизацией и диким лесом. Деревья становятся гуще, а тропинки уже.',
            'type' => 'forest',
            'is_safe_zone' => false,
            'min_level' => 2,
            'max_level' => null,
            'coordinate_x' => 0,
            'coordinate_y' => 3,
        ]);

        // Глухой лес (0, 4)
        $locations['deep_forest'] = Location::create([
            'name' => 'Глухой лес',
            'description' => 'Густой старый лес. Солнечный свет едва пробивается сквозь кроны деревьев. Здесь водятся опасные существа.',
            'type' => 'forest',
            'is_safe_zone' => false,
            'min_level' => 3,
            'max_level' => null,
            'coordinate_x' => 0,
            'coordinate_y' => 4,
        ]);

        // Лесная поляна (1, 4)
        $locations['forest_clearing'] = Location::create([
            'name' => 'Лесная поляна',
            'description' => 'Небольшая поляна посреди леса. Здесь можно отдохнуть и восстановить силы.',
            'type' => 'field',
            'is_safe_zone' => false,
            'min_level' => 3,
            'max_level' => null,
            'coordinate_x' => 1,
            'coordinate_y' => 4,
        ]);

        // ========== ГОРЫ ==========
        // Подножие гор (3, 0)
        $locations['mountain_foot'] = Location::create([
            'name' => 'Подножие гор',
            'description' => 'Скалистое подножие высоких гор. Воздух становится прохладнее, а тропа круче.',
            'type' => 'mountain',
            'is_safe_zone' => false,
            'min_level' => 4,
            'max_level' => null,
            'coordinate_x' => 3,
            'coordinate_y' => 0,
        ]);

        // Горная тропа (4, 0)
        $locations['mountain_path'] = Location::create([
            'name' => 'Горная тропа',
            'description' => 'Узкая тропа, вьющаяся между скал. Нужно быть осторожным, чтобы не сорваться в пропасть.',
            'type' => 'mountain',
            'is_safe_zone' => false,
            'min_level' => 5,
            'max_level' => null,
            'coordinate_x' => 4,
            'coordinate_y' => 0,
        ]);

        // Пещера в горах (4, 1)
        $locations['mountain_cave'] = Location::create([
            'name' => 'Пещера в горах',
            'description' => 'Темная пещера в скале. Изнутри доносится странный шум. Возможно, здесь обитают монстры.',
            'type' => 'cave',
            'is_safe_zone' => false,
            'min_level' => 6,
            'max_level' => null,
            'coordinate_x' => 4,
            'coordinate_y' => 1,
        ]);

        // ========== ПОДЗЕМЕЛЬЕ ==========
        // Вход в подземелье (4, 2)
        $locations['dungeon_entrance'] = Location::create([
            'name' => 'Вход в подземелье',
            'description' => 'Древний каменный вход в подземелье. Над аркой высечены странные символы. Отсюда веет холодом и опасностью.',
            'type' => 'dungeon',
            'is_safe_zone' => false,
            'min_level' => 7,
            'max_level' => null,
            'coordinate_x' => 4,
            'coordinate_y' => 2,
        ]);

        // Первый уровень подземелья (4, 3)
        $locations['dungeon_level1'] = Location::create([
            'name' => 'Первый уровень подземелья',
            'description' => 'Мрачные каменные коридоры. Стены покрыты мхом, а воздух наполнен запахом сырости и разложения.',
            'type' => 'dungeon',
            'is_safe_zone' => false,
            'min_level' => 8,
            'max_level' => null,
            'coordinate_x' => 4,
            'coordinate_y' => 3,
        ]);

        // ========== ДЕРЕВНЯ ==========
        // Деревня (-3, 0)
        $locations['village'] = Location::create([
            'name' => 'Деревня',
            'description' => 'Небольшая уютная деревня. Деревянные домики, курятники и огороды. Местные жители дружелюбны.',
            'type' => 'village',
            'is_safe_zone' => true,
            'min_level' => null,
            'max_level' => null,
            'coordinate_x' => -3,
            'coordinate_y' => 0,
        ]);

        // ========== РЕКА ==========
        // Берег реки (-1, 2)
        $locations['river_bank'] = Location::create([
            'name' => 'Берег реки',
            'description' => 'Тихий берег широкой реки. Вода чистая и прозрачная. Здесь можно порыбачить или просто отдохнуть.',
            'type' => 'river',
            'is_safe_zone' => false,
            'min_level' => 2,
            'max_level' => null,
            'coordinate_x' => -1,
            'coordinate_y' => 2,
        ]);

        // ========== ЗАМОК ==========
        // Замок (0, -3)
        $locations['castle'] = Location::create([
            'name' => 'Замок',
            'description' => 'Величественный замок на холме. Высокие башни и крепкие стены. Здесь обитает местный правитель.',
            'type' => 'castle',
            'is_safe_zone' => false,
            'min_level' => 10,
            'max_level' => null,
            'coordinate_x' => 0,
            'coordinate_y' => -3,
        ]);

        return $locations;
    }

    /**
     * Создать переходы между локациями.
     */
    private function createExits(array $locations): void
    {
        // ========== ГОРОД ==========
        // Главная площадь
        $this->createExit($locations['main_square'], $locations['merchant_street'], 'south', 'на улицу торговцев');
        $this->createExit($locations['main_square'], $locations['bank'], 'east', 'в банк');
        $this->createExit($locations['main_square'], $locations['city_gates'], 'north', 'к городским воротам');

        // Улица торговцев
        $this->createExit($locations['merchant_street'], $locations['main_square'], 'north', 'на главную площадь');
        $this->createExit($locations['merchant_street'], $locations['weapon_shop'], 'south', 'в магазин оружия');
        $this->createExit($locations['merchant_street'], $locations['blacksmith_alley'], 'west', 'в переулок оружейников');

        // Магазин оружия
        $this->createExit($locations['weapon_shop'], $locations['merchant_street'], 'north', 'на улицу торговцев');

        // Банк
        $this->createExit($locations['bank'], $locations['main_square'], 'west', 'на главную площадь');

        // Переулок оружейников
        $this->createExit($locations['blacksmith_alley'], $locations['merchant_street'], 'east', 'на улицу торговцев');

        // Городские ворота
        $this->createExit($locations['city_gates'], $locations['main_square'], 'south', 'в город');
        $this->createExit($locations['city_gates'], $locations['north_road'], 'north', 'на дорогу на север');

        // ========== ДОРОГИ ==========
        // Дорога на север
        $this->createExit($locations['north_road'], $locations['city_gates'], 'south', 'к городским воротам');
        $this->createExit($locations['north_road'], $locations['forest_edge'], 'north', 'к лесной опушке');

        // Дорога на восток
        $this->createExit($locations['east_road'], $locations['main_square'], 'west', 'в город');
        $this->createExit($locations['east_road'], $locations['mountain_foot'], 'east', 'к подножию гор');

        // Дорога на запад
        $this->createExit($locations['west_road'], $locations['main_square'], 'east', 'в город');
        $this->createExit($locations['west_road'], $locations['village'], 'west', 'в деревню');

        // ========== ЛЕС ==========
        // Лесная опушка
        $this->createExit($locations['forest_edge'], $locations['north_road'], 'south', 'на дорогу');
        $this->createExit($locations['forest_edge'], $locations['deep_forest'], 'north', 'в глухой лес');

        // Глухой лес
        $this->createExit($locations['deep_forest'], $locations['forest_edge'], 'south', 'к лесной опушке');
        $this->createExit($locations['deep_forest'], $locations['forest_clearing'], 'east', 'на лесную поляну');

        // Лесная поляна
        $this->createExit($locations['forest_clearing'], $locations['deep_forest'], 'west', 'в глухой лес');

        // ========== ГОРЫ ==========
        // Подножие гор
        $this->createExit($locations['mountain_foot'], $locations['east_road'], 'west', 'на дорогу');
        $this->createExit($locations['mountain_foot'], $locations['mountain_path'], 'east', 'на горную тропу');

        // Горная тропа
        $this->createExit($locations['mountain_path'], $locations['mountain_foot'], 'west', 'к подножию');
        $this->createExit($locations['mountain_path'], $locations['mountain_cave'], 'south', 'в пещеру');

        // Пещера в горах
        $this->createExit($locations['mountain_cave'], $locations['mountain_path'], 'north', 'на горную тропу');
        $this->createExit($locations['mountain_cave'], $locations['dungeon_entrance'], 'south', 'к входу в подземелье');

        // ========== ПОДЗЕМЕЛЬЕ ==========
        // Вход в подземелье (закрыт, требует ключ)
        $this->createExit(
            $locations['dungeon_entrance'],
            $locations['mountain_cave'],
            'north',
            'к пещере',
            false
        );
        $this->createExit(
            $locations['dungeon_entrance'],
            $locations['dungeon_level1'],
            'south',
            'в подземелье',
            true, // Закрыт
            null, // required_item_id будет установлен позже, если нужен
            null, // required_skill
            null, // required_skill_level
            'Вход заперт магическим замком. Нужен специальный ключ или навык взлома.'
        );

        // Первый уровень подземелья
        $this->createExit($locations['dungeon_level1'], $locations['dungeon_entrance'], 'north', 'к выходу');

        // ========== ДЕРЕВНЯ ==========
        // Деревня
        $this->createExit($locations['village'], $locations['west_road'], 'east', 'на дорогу');

        // ========== РЕКА ==========
        // Берег реки
        $this->createExit($locations['river_bank'], $locations['city_gates'], 'south', 'к городским воротам');

        // ========== ЗАМОК ==========
        // Замок (закрыт, требует уровень 10)
        $this->createExit(
            $locations['castle'],
            $locations['merchant_street'],
            'north',
            'в город',
            false
        );
        // Вход в замок с главной площади (закрыт для низких уровней)
        $this->createExit(
            $locations['main_square'],
            $locations['castle'],
            'south',
            'в замок',
            false,
            null,
            null,
            null,
            'Величественный замок. Стража пропускает только опытных воинов.'
        );
    }

    /**
     * Создать переход между локациями.
     */
    private function createExit(
        Location $from,
        Location $to,
        string $direction,
        ?string $customName = null,
        bool $isLocked = false,
        ?int $requiredItemId = null,
        ?string $requiredSkill = null,
        ?int $requiredSkillLevel = null,
        ?string $description = null
    ): void {
        LocationExit::create([
            'from_location_id' => $from->id,
            'to_location_id' => $to->id,
            'direction' => $direction,
            'custom_name' => $customName,
            'is_locked' => $isLocked,
            'required_item_id' => $requiredItemId,
            'required_skill' => $requiredSkill,
            'required_skill_level' => $requiredSkillLevel,
            'description' => $description,
        ]);
    }
}
