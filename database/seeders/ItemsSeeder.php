<?php

namespace Database\Seeders;

use App\Models\Item;
use Illuminate\Database\Seeder;

class ItemsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedWeapons();
        $this->seedArmor();
        $this->seedJewelry();
        $this->seedPotions();
        $this->seedResources();
        $this->seedConsumables();
        $this->seedRunes();
        $this->seedScrolls();
        $this->seedCurrency();

        $this->command->info('Предметы успешно созданы!');
    }

    /**
     * Создать оружие.
     */
    private function seedWeapons(): void
    {
        $weapons = [
            // Мечи - одноручные
            [
                'name' => 'Деревянный меч',
                'description' => 'Простой деревянный меч для начинающих воинов',
                'type' => 'weapon',
                'subtype' => 'sword',
                'rarity' => 'common',
                'level_required' => 1,
                'stackable' => false,
                'max_stack' => 1,
                'weight' => 1.5,
                'value' => 10,
                'requirements' => ['level' => 1],
                'weapon_data' => [
                    'damage_min' => 2,
                    'damage_max' => 5,
                    'attack_speed' => 1.2,
                    'weapon_type' => 'one_handed',
                    'range' => 1,
                ],
            ],
            [
                'name' => 'Железный меч',
                'description' => 'Надежный железный меч',
                'type' => 'weapon',
                'subtype' => 'sword',
                'rarity' => 'common',
                'level_required' => 3,
                'stackable' => false,
                'max_stack' => 1,
                'weight' => 2.0,
                'value' => 50,
                'requirements' => ['level' => 3, 'attributes' => ['strength' => 5]],
                'weapon_data' => [
                    'damage_min' => 5,
                    'damage_max' => 10,
                    'attack_speed' => 1.0,
                    'weapon_type' => 'one_handed',
                    'range' => 1,
                ],
            ],
            [
                'name' => 'Стальной меч',
                'description' => 'Острое стальное оружие',
                'type' => 'weapon',
                'subtype' => 'sword',
                'rarity' => 'uncommon',
                'level_required' => 5,
                'stackable' => false,
                'max_stack' => 1,
                'weight' => 2.5,
                'value' => 150,
                'requirements' => ['level' => 5, 'attributes' => ['strength' => 10]],
                'weapon_data' => [
                    'damage_min' => 10,
                    'damage_max' => 18,
                    'attack_speed' => 1.0,
                    'weapon_type' => 'one_handed',
                    'range' => 1,
                ],
            ],
            // Мечи - двуручные
            [
                'name' => 'Двуручный меч',
                'description' => 'Мощное двуручное оружие',
                'type' => 'weapon',
                'subtype' => 'sword',
                'rarity' => 'uncommon',
                'level_required' => 5,
                'stackable' => false,
                'max_stack' => 1,
                'weight' => 4.0,
                'value' => 200,
                'requirements' => ['level' => 5, 'attributes' => ['strength' => 15]],
                'weapon_data' => [
                    'damage_min' => 15,
                    'damage_max' => 25,
                    'attack_speed' => 1.5,
                    'weapon_type' => 'two_handed',
                    'range' => 1,
                ],
            ],
            [
                'name' => 'Большой двуручный меч',
                'description' => 'Огромный меч для настоящих воинов',
                'type' => 'weapon',
                'subtype' => 'sword',
                'rarity' => 'rare',
                'level_required' => 10,
                'stackable' => false,
                'max_stack' => 1,
                'weight' => 5.0,
                'value' => 500,
                'requirements' => ['level' => 10, 'attributes' => ['strength' => 25]],
                'weapon_data' => [
                    'damage_min' => 25,
                    'damage_max' => 40,
                    'attack_speed' => 1.8,
                    'weapon_type' => 'two_handed',
                    'range' => 1,
                ],
            ],
            // Топоры - боевые
            [
                'name' => 'Боевой топор',
                'description' => 'Тяжелый боевой топор',
                'type' => 'weapon',
                'subtype' => 'axe',
                'rarity' => 'common',
                'level_required' => 2,
                'stackable' => false,
                'max_stack' => 1,
                'weight' => 3.0,
                'value' => 30,
                'requirements' => ['level' => 2, 'attributes' => ['strength' => 8]],
                'weapon_data' => [
                    'damage_min' => 6,
                    'damage_max' => 12,
                    'attack_speed' => 1.3,
                    'weapon_type' => 'one_handed',
                    'range' => 1,
                ],
            ],
            [
                'name' => 'Секира',
                'description' => 'Мощная секира для рубки',
                'type' => 'weapon',
                'subtype' => 'axe',
                'rarity' => 'uncommon',
                'level_required' => 6,
                'stackable' => false,
                'max_stack' => 1,
                'weight' => 4.5,
                'value' => 180,
                'requirements' => ['level' => 6, 'attributes' => ['strength' => 18]],
                'weapon_data' => [
                    'damage_min' => 18,
                    'damage_max' => 28,
                    'attack_speed' => 1.6,
                    'weapon_type' => 'two_handed',
                    'range' => 1,
                ],
            ],
            // Кинжалы
            [
                'name' => 'Кинжал',
                'description' => 'Быстрый и легкий кинжал',
                'type' => 'weapon',
                'subtype' => 'dagger',
                'rarity' => 'common',
                'level_required' => 1,
                'stackable' => false,
                'max_stack' => 1,
                'weight' => 0.5,
                'value' => 15,
                'requirements' => ['level' => 1],
                'weapon_data' => [
                    'damage_min' => 1,
                    'damage_max' => 4,
                    'attack_speed' => 0.8,
                    'weapon_type' => 'one_handed',
                    'range' => 1,
                ],
            ],
            [
                'name' => 'Скрытный кинжал',
                'description' => 'Острый кинжал для скрытных атак',
                'type' => 'weapon',
                'subtype' => 'dagger',
                'rarity' => 'uncommon',
                'level_required' => 4,
                'stackable' => false,
                'max_stack' => 1,
                'weight' => 0.6,
                'value' => 100,
                'requirements' => ['level' => 4, 'attributes' => ['agility' => 10]],
                'weapon_data' => [
                    'damage_min' => 4,
                    'damage_max' => 9,
                    'attack_speed' => 0.6,
                    'weapon_type' => 'one_handed',
                    'range' => 1,
                ],
            ],
            // Луки
            [
                'name' => 'Простой лук',
                'description' => 'Деревянный лук для дальнего боя',
                'type' => 'weapon',
                'subtype' => 'bow',
                'rarity' => 'common',
                'level_required' => 2,
                'stackable' => false,
                'max_stack' => 1,
                'weight' => 1.0,
                'value' => 40,
                'requirements' => ['level' => 2, 'attributes' => ['agility' => 5]],
                'weapon_data' => [
                    'damage_min' => 3,
                    'damage_max' => 7,
                    'attack_speed' => 1.5,
                    'weapon_type' => 'ranged',
                    'range' => 5,
                ],
            ],
            [
                'name' => 'Длинный лук',
                'description' => 'Мощный лук с увеличенной дальностью',
                'type' => 'weapon',
                'subtype' => 'bow',
                'rarity' => 'uncommon',
                'level_required' => 6,
                'stackable' => false,
                'max_stack' => 1,
                'weight' => 1.5,
                'value' => 200,
                'requirements' => ['level' => 6, 'attributes' => ['agility' => 15]],
                'weapon_data' => [
                    'damage_min' => 12,
                    'damage_max' => 20,
                    'attack_speed' => 1.8,
                    'weapon_type' => 'ranged',
                    'range' => 8,
                ],
            ],
            // Арбалеты
            [
                'name' => 'Арбалет',
                'description' => 'Мощное дальнобойное оружие',
                'type' => 'weapon',
                'subtype' => 'crossbow',
                'rarity' => 'uncommon',
                'level_required' => 5,
                'stackable' => false,
                'max_stack' => 1,
                'weight' => 3.5,
                'value' => 250,
                'requirements' => ['level' => 5, 'attributes' => ['strength' => 12]],
                'weapon_data' => [
                    'damage_min' => 15,
                    'damage_max' => 25,
                    'attack_speed' => 2.5,
                    'weapon_type' => 'ranged',
                    'range' => 6,
                ],
            ],
            [
                'name' => 'Тяжелый арбалет',
                'description' => 'Очень мощный арбалет',
                'type' => 'weapon',
                'subtype' => 'crossbow',
                'rarity' => 'rare',
                'level_required' => 10,
                'stackable' => false,
                'max_stack' => 1,
                'weight' => 5.0,
                'value' => 600,
                'requirements' => ['level' => 10, 'attributes' => ['strength' => 20]],
                'weapon_data' => [
                    'damage_min' => 30,
                    'damage_max' => 45,
                    'attack_speed' => 3.0,
                    'weapon_type' => 'ranged',
                    'range' => 8,
                ],
            ],
            // Посохи - магические
            [
                'name' => 'Деревянный посох',
                'description' => 'Простой магический посох',
                'type' => 'weapon',
                'subtype' => 'staff',
                'rarity' => 'common',
                'level_required' => 1,
                'stackable' => false,
                'max_stack' => 1,
                'weight' => 2.0,
                'value' => 20,
                'requirements' => ['level' => 1, 'attributes' => ['intelligence' => 3]],
                'weapon_data' => [
                    'damage_min' => 2,
                    'damage_max' => 5,
                    'attack_speed' => 1.4,
                    'weapon_type' => 'two_handed',
                    'range' => 2,
                ],
            ],
            [
                'name' => 'Магический посох',
                'description' => 'Посох, усиливающий магию',
                'type' => 'weapon',
                'subtype' => 'staff',
                'rarity' => 'uncommon',
                'level_required' => 5,
                'stackable' => false,
                'max_stack' => 1,
                'weight' => 2.5,
                'value' => 300,
                'requirements' => ['level' => 5, 'attributes' => ['intelligence' => 15]],
                'weapon_data' => [
                    'damage_min' => 8,
                    'damage_max' => 15,
                    'attack_speed' => 1.2,
                    'weapon_type' => 'two_handed',
                    'range' => 3,
                ],
            ],
            [
                'name' => 'Боевой посох',
                'description' => 'Тяжелый посох для ближнего боя',
                'type' => 'weapon',
                'subtype' => 'staff',
                'rarity' => 'common',
                'level_required' => 3,
                'stackable' => false,
                'max_stack' => 1,
                'weight' => 3.0,
                'value' => 80,
                'requirements' => ['level' => 3],
                'weapon_data' => [
                    'damage_min' => 6,
                    'damage_max' => 12,
                    'attack_speed' => 1.3,
                    'weapon_type' => 'two_handed',
                    'range' => 1,
                ],
            ],
        ];

        foreach ($weapons as $weaponData) {
            Item::updateOrCreate(
                ['name' => $weaponData['name']],
                $weaponData
            );
        }

        $this->command->info('Оружие создано: '.count($weapons));
    }

    /**
     * Создать броню.
     */
    private function seedArmor(): void
    {
        $armor = [
            // Шлемы
            [
                'name' => 'Кожаный шлем',
                'description' => 'Легкий кожаный шлем',
                'type' => 'armor',
                'subtype' => 'helmet',
                'rarity' => 'common',
                'level_required' => 1,
                'stackable' => false,
                'max_stack' => 1,
                'weight' => 0.5,
                'value' => 15,
                'requirements' => ['level' => 1],
                'armor_data' => [
                    'defense' => 2,
                    'durability' => 50,
                    'durability_max' => 50,
                    'armor_type' => 'light',
                ],
            ],
            [
                'name' => 'Железный шлем',
                'description' => 'Надежный железный шлем',
                'type' => 'armor',
                'subtype' => 'helmet',
                'rarity' => 'common',
                'level_required' => 3,
                'stackable' => false,
                'max_stack' => 1,
                'weight' => 1.5,
                'value' => 60,
                'requirements' => ['level' => 3],
                'armor_data' => [
                    'defense' => 5,
                    'durability' => 100,
                    'durability_max' => 100,
                    'armor_type' => 'medium',
                ],
            ],
            [
                'name' => 'Магический шлем',
                'description' => 'Шлем, усиливающий магические способности',
                'type' => 'armor',
                'subtype' => 'helmet',
                'rarity' => 'uncommon',
                'level_required' => 5,
                'stackable' => false,
                'max_stack' => 1,
                'weight' => 0.8,
                'value' => 200,
                'requirements' => ['level' => 5, 'attributes' => ['intelligence' => 10]],
                'armor_data' => [
                    'defense' => 4,
                    'durability' => 80,
                    'durability_max' => 80,
                    'armor_type' => 'light',
                ],
            ],
            // Нагрудники
            [
                'name' => 'Кожаная куртка',
                'description' => 'Легкая кожаная защита',
                'type' => 'armor',
                'subtype' => 'chest',
                'rarity' => 'common',
                'level_required' => 1,
                'stackable' => false,
                'max_stack' => 1,
                'weight' => 2.0,
                'value' => 25,
                'requirements' => ['level' => 1],
                'armor_data' => [
                    'defense' => 3,
                    'durability' => 60,
                    'durability_max' => 60,
                    'armor_type' => 'light',
                ],
            ],
            [
                'name' => 'Железная кираса',
                'description' => 'Тяжелая железная броня',
                'type' => 'armor',
                'subtype' => 'chest',
                'rarity' => 'common',
                'level_required' => 4,
                'stackable' => false,
                'max_stack' => 1,
                'weight' => 5.0,
                'value' => 120,
                'requirements' => ['level' => 4, 'attributes' => ['strength' => 12]],
                'armor_data' => [
                    'defense' => 8,
                    'durability' => 150,
                    'durability_max' => 150,
                    'armor_type' => 'heavy',
                ],
            ],
            [
                'name' => 'Магический плащ',
                'description' => 'Плащ, усиливающий магию',
                'type' => 'armor',
                'subtype' => 'chest',
                'rarity' => 'uncommon',
                'level_required' => 6,
                'stackable' => false,
                'max_stack' => 1,
                'weight' => 1.0,
                'value' => 300,
                'requirements' => ['level' => 6, 'attributes' => ['intelligence' => 15]],
                'armor_data' => [
                    'defense' => 5,
                    'durability' => 100,
                    'durability_max' => 100,
                    'armor_type' => 'light',
                ],
            ],
            // Поножи
            [
                'name' => 'Кожаные поножи',
                'description' => 'Легкая защита для ног',
                'type' => 'armor',
                'subtype' => 'legs',
                'rarity' => 'common',
                'level_required' => 1,
                'stackable' => false,
                'max_stack' => 1,
                'weight' => 1.5,
                'value' => 20,
                'requirements' => ['level' => 1],
                'armor_data' => [
                    'defense' => 2,
                    'durability' => 50,
                    'durability_max' => 50,
                    'armor_type' => 'light',
                ],
            ],
            [
                'name' => 'Железные поножи',
                'description' => 'Тяжелые железные поножи',
                'type' => 'armor',
                'subtype' => 'legs',
                'rarity' => 'common',
                'level_required' => 4,
                'stackable' => false,
                'max_stack' => 1,
                'weight' => 3.0,
                'value' => 100,
                'requirements' => ['level' => 4],
                'armor_data' => [
                    'defense' => 6,
                    'durability' => 120,
                    'durability_max' => 120,
                    'armor_type' => 'medium',
                ],
            ],
            // Сапоги
            [
                'name' => 'Кожаные сапоги',
                'description' => 'Легкие сапоги для быстрого передвижения',
                'type' => 'armor',
                'subtype' => 'feet',
                'rarity' => 'common',
                'level_required' => 1,
                'stackable' => false,
                'max_stack' => 1,
                'weight' => 0.8,
                'value' => 18,
                'requirements' => ['level' => 1],
                'armor_data' => [
                    'defense' => 1,
                    'durability' => 40,
                    'durability_max' => 40,
                    'armor_type' => 'light',
                ],
            ],
            [
                'name' => 'Железные сапоги',
                'description' => 'Тяжелые, но надежные сапоги',
                'type' => 'armor',
                'subtype' => 'feet',
                'rarity' => 'common',
                'level_required' => 3,
                'stackable' => false,
                'max_stack' => 1,
                'weight' => 2.0,
                'value' => 50,
                'requirements' => ['level' => 3],
                'armor_data' => [
                    'defense' => 3,
                    'durability' => 80,
                    'durability_max' => 80,
                    'armor_type' => 'medium',
                ],
            ],
            [
                'name' => 'Сапоги проворства',
                'description' => 'Легкие сапоги, увеличивающие скорость',
                'type' => 'armor',
                'subtype' => 'feet',
                'rarity' => 'uncommon',
                'level_required' => 5,
                'stackable' => false,
                'max_stack' => 1,
                'weight' => 0.5,
                'value' => 180,
                'requirements' => ['level' => 5, 'attributes' => ['agility' => 12]],
                'armor_data' => [
                    'defense' => 2,
                    'durability' => 70,
                    'durability_max' => 70,
                    'armor_type' => 'light',
                ],
            ],
            // Перчатки
            [
                'name' => 'Кожаные перчатки',
                'description' => 'Легкие перчатки',
                'type' => 'armor',
                'subtype' => 'hands',
                'rarity' => 'common',
                'level_required' => 1,
                'stackable' => false,
                'max_stack' => 1,
                'weight' => 0.3,
                'value' => 12,
                'requirements' => ['level' => 1],
                'armor_data' => [
                    'defense' => 1,
                    'durability' => 30,
                    'durability_max' => 30,
                    'armor_type' => 'light',
                ],
            ],
            [
                'name' => 'Железные перчатки',
                'description' => 'Тяжелые железные перчатки',
                'type' => 'armor',
                'subtype' => 'hands',
                'rarity' => 'common',
                'level_required' => 3,
                'stackable' => false,
                'max_stack' => 1,
                'weight' => 1.0,
                'value' => 40,
                'requirements' => ['level' => 3],
                'armor_data' => [
                    'defense' => 2,
                    'durability' => 60,
                    'durability_max' => 60,
                    'armor_type' => 'medium',
                ],
            ],
        ];

        foreach ($armor as $armorData) {
            Item::updateOrCreate(
                ['name' => $armorData['name']],
                $armorData
            );
        }

        $this->command->info('Броня создана: '.count($armor));
    }

    /**
     * Создать бижутерию.
     */
    private function seedJewelry(): void
    {
        $jewelry = [
            // Кольца
            [
                'name' => 'Медное кольцо',
                'description' => 'Простое кольцо с небольшим бонусом',
                'type' => 'jewelry',
                'subtype' => 'ring',
                'rarity' => 'common',
                'level_required' => 1,
                'stackable' => false,
                'max_stack' => 1,
                'weight' => 0.1,
                'value' => 30,
                'requirements' => ['level' => 1],
                'jewelry_data' => [
                    'attributes_bonus' => ['strength' => 1],
                ],
            ],
            [
                'name' => 'Серебряное кольцо силы',
                'description' => 'Кольцо, увеличивающее силу',
                'type' => 'jewelry',
                'subtype' => 'ring',
                'rarity' => 'uncommon',
                'level_required' => 5,
                'stackable' => false,
                'max_stack' => 1,
                'weight' => 0.1,
                'value' => 250,
                'requirements' => ['level' => 5],
                'jewelry_data' => [
                    'attributes_bonus' => ['strength' => 3],
                ],
            ],
            [
                'name' => 'Кольцо ловкости',
                'description' => 'Кольцо, увеличивающее ловкость',
                'type' => 'jewelry',
                'subtype' => 'ring',
                'rarity' => 'uncommon',
                'level_required' => 5,
                'stackable' => false,
                'max_stack' => 1,
                'weight' => 0.1,
                'value' => 250,
                'requirements' => ['level' => 5],
                'jewelry_data' => [
                    'attributes_bonus' => ['agility' => 3],
                ],
            ],
            [
                'name' => 'Кольцо интеллекта',
                'description' => 'Кольцо, увеличивающее интеллект',
                'type' => 'jewelry',
                'subtype' => 'ring',
                'rarity' => 'uncommon',
                'level_required' => 5,
                'stackable' => false,
                'max_stack' => 1,
                'weight' => 0.1,
                'value' => 250,
                'requirements' => ['level' => 5],
                'jewelry_data' => [
                    'attributes_bonus' => ['intelligence' => 3],
                ],
            ],
            // Амулеты
            [
                'name' => 'Медный амулет',
                'description' => 'Простой амулет с магическими свойствами',
                'type' => 'jewelry',
                'subtype' => 'amulet',
                'rarity' => 'common',
                'level_required' => 2,
                'stackable' => false,
                'max_stack' => 1,
                'weight' => 0.2,
                'value' => 50,
                'requirements' => ['level' => 2],
                'jewelry_data' => [
                    'attributes_bonus' => ['intelligence' => 2],
                ],
            ],
            [
                'name' => 'Амулет защиты',
                'description' => 'Амулет, увеличивающий защиту',
                'type' => 'jewelry',
                'subtype' => 'amulet',
                'rarity' => 'uncommon',
                'level_required' => 6,
                'stackable' => false,
                'max_stack' => 1,
                'weight' => 0.2,
                'value' => 400,
                'requirements' => ['level' => 6],
                'jewelry_data' => [
                    'attributes_bonus' => ['strength' => 2, 'agility' => 2],
                ],
            ],
            [
                'name' => 'Амулет магии',
                'description' => 'Мощный магический амулет',
                'type' => 'jewelry',
                'subtype' => 'amulet',
                'rarity' => 'rare',
                'level_required' => 8,
                'stackable' => false,
                'max_stack' => 1,
                'weight' => 0.2,
                'value' => 800,
                'requirements' => ['level' => 8, 'attributes' => ['intelligence' => 20]],
                'jewelry_data' => [
                    'attributes_bonus' => ['intelligence' => 5],
                ],
            ],
            // Серьги
            [
                'name' => 'Серебряные серьги',
                'description' => 'Серьги с небольшими бонусами',
                'type' => 'jewelry',
                'subtype' => 'earring',
                'rarity' => 'common',
                'level_required' => 3,
                'stackable' => false,
                'max_stack' => 1,
                'weight' => 0.05,
                'value' => 40,
                'requirements' => ['level' => 3],
                'jewelry_data' => [
                    'attributes_bonus' => ['agility' => 1],
                ],
            ],
            [
                'name' => 'Золотые серьги',
                'description' => 'Дорогие серьги с хорошими бонусами',
                'type' => 'jewelry',
                'subtype' => 'earring',
                'rarity' => 'uncommon',
                'level_required' => 6,
                'stackable' => false,
                'max_stack' => 1,
                'weight' => 0.05,
                'value' => 300,
                'requirements' => ['level' => 6],
                'jewelry_data' => [
                    'attributes_bonus' => ['agility' => 2, 'intelligence' => 2],
                ],
            ],
        ];

        foreach ($jewelry as $jewelryData) {
            Item::updateOrCreate(
                ['name' => $jewelryData['name']],
                $jewelryData
            );
        }

        $this->command->info('Бижутерия создана: '.count($jewelry));
    }

    /**
     * Создать зелья.
     */
    private function seedPotions(): void
    {
        $potions = [
            // Зелья здоровья
            [
                'name' => 'Слабое зелье здоровья',
                'description' => 'Восстанавливает небольшое количество HP',
                'type' => 'potion',
                'subtype' => 'health',
                'rarity' => 'common',
                'level_required' => 1,
                'stackable' => true,
                'max_stack' => 20,
                'weight' => 0.2,
                'value' => 10,
                'requirements' => null,
                'potion_data' => [
                    'effect_type' => 'heal',
                    'effect_power' => 20,
                    'duration' => 0,
                    'cooldown' => 5,
                ],
            ],
            [
                'name' => 'Зелье здоровья',
                'description' => 'Восстанавливает среднее количество HP',
                'type' => 'potion',
                'subtype' => 'health',
                'rarity' => 'common',
                'level_required' => 3,
                'stackable' => true,
                'max_stack' => 20,
                'weight' => 0.2,
                'value' => 30,
                'requirements' => null,
                'potion_data' => [
                    'effect_type' => 'heal',
                    'effect_power' => 50,
                    'duration' => 0,
                    'cooldown' => 5,
                ],
            ],
            [
                'name' => 'Сильное зелье здоровья',
                'description' => 'Восстанавливает большое количество HP',
                'type' => 'potion',
                'subtype' => 'health',
                'rarity' => 'uncommon',
                'level_required' => 6,
                'stackable' => true,
                'max_stack' => 20,
                'weight' => 0.2,
                'value' => 80,
                'requirements' => null,
                'potion_data' => [
                    'effect_type' => 'heal',
                    'effect_power' => 100,
                    'duration' => 0,
                    'cooldown' => 5,
                ],
            ],
            // Зелья маны
            [
                'name' => 'Слабое зелье маны',
                'description' => 'Восстанавливает небольшое количество MP',
                'type' => 'potion',
                'subtype' => 'mana',
                'rarity' => 'common',
                'level_required' => 1,
                'stackable' => true,
                'max_stack' => 20,
                'weight' => 0.2,
                'value' => 10,
                'requirements' => null,
                'potion_data' => [
                    'effect_type' => 'mana',
                    'effect_power' => 15,
                    'duration' => 0,
                    'cooldown' => 5,
                ],
            ],
            [
                'name' => 'Зелье маны',
                'description' => 'Восстанавливает среднее количество MP',
                'type' => 'potion',
                'subtype' => 'mana',
                'rarity' => 'common',
                'level_required' => 3,
                'stackable' => true,
                'max_stack' => 20,
                'weight' => 0.2,
                'value' => 30,
                'requirements' => null,
                'potion_data' => [
                    'effect_type' => 'mana',
                    'effect_power' => 40,
                    'duration' => 0,
                    'cooldown' => 5,
                ],
            ],
            [
                'name' => 'Сильное зелье маны',
                'description' => 'Восстанавливает большое количество MP',
                'type' => 'potion',
                'subtype' => 'mana',
                'rarity' => 'uncommon',
                'level_required' => 6,
                'stackable' => true,
                'max_stack' => 20,
                'weight' => 0.2,
                'value' => 80,
                'requirements' => null,
                'potion_data' => [
                    'effect_type' => 'mana',
                    'effect_power' => 80,
                    'duration' => 0,
                    'cooldown' => 5,
                ],
            ],
            // Баффы
            [
                'name' => 'Зелье силы',
                'description' => 'Временно увеличивает силу',
                'type' => 'potion',
                'subtype' => 'buff',
                'rarity' => 'uncommon',
                'level_required' => 4,
                'stackable' => true,
                'max_stack' => 10,
                'weight' => 0.2,
                'value' => 50,
                'requirements' => null,
                'potion_data' => [
                    'effect_type' => 'buff_strength',
                    'effect_power' => 5,
                    'duration' => 300,
                    'cooldown' => 10,
                ],
            ],
            [
                'name' => 'Зелье ловкости',
                'description' => 'Временно увеличивает ловкость',
                'type' => 'potion',
                'subtype' => 'buff',
                'rarity' => 'uncommon',
                'level_required' => 4,
                'stackable' => true,
                'max_stack' => 10,
                'weight' => 0.2,
                'value' => 50,
                'requirements' => null,
                'potion_data' => [
                    'effect_type' => 'buff_agility',
                    'effect_power' => 5,
                    'duration' => 300,
                    'cooldown' => 10,
                ],
            ],
            [
                'name' => 'Зелье интеллекта',
                'description' => 'Временно увеличивает интеллект',
                'type' => 'potion',
                'subtype' => 'buff',
                'rarity' => 'uncommon',
                'level_required' => 4,
                'stackable' => true,
                'max_stack' => 10,
                'weight' => 0.2,
                'value' => 50,
                'requirements' => null,
                'potion_data' => [
                    'effect_type' => 'buff_intelligence',
                    'effect_power' => 5,
                    'duration' => 300,
                    'cooldown' => 10,
                ],
            ],
            [
                'name' => 'Зелье защиты',
                'description' => 'Временно увеличивает защиту',
                'type' => 'potion',
                'subtype' => 'buff',
                'rarity' => 'rare',
                'level_required' => 7,
                'stackable' => true,
                'max_stack' => 10,
                'weight' => 0.2,
                'value' => 150,
                'requirements' => null,
                'potion_data' => [
                    'effect_type' => 'buff_defense',
                    'effect_power' => 10,
                    'duration' => 600,
                    'cooldown' => 15,
                ],
            ],
        ];

        foreach ($potions as $potionData) {
            Item::updateOrCreate(
                ['name' => $potionData['name']],
                $potionData
            );
        }

        $this->command->info('Зелья созданы: '.count($potions));
    }

    /**
     * Создать ресурсы.
     */
    private function seedResources(): void
    {
        $resources = [
            // Травы
            [
                'name' => 'Лечебная трава',
                'description' => 'Обычная трава для алхимии',
                'type' => 'resource',
                'subtype' => 'herb',
                'rarity' => 'common',
                'level_required' => 1,
                'stackable' => true,
                'max_stack' => 100,
                'weight' => 0.1,
                'value' => 2,
                'requirements' => null,
                'resource_data' => [
                    'resource_type' => 'herb',
                    'quality' => 1,
                ],
            ],
            [
                'name' => 'Магическая трава',
                'description' => 'Редкая трава с магическими свойствами',
                'type' => 'resource',
                'subtype' => 'herb',
                'rarity' => 'uncommon',
                'level_required' => 3,
                'stackable' => true,
                'max_stack' => 100,
                'weight' => 0.1,
                'value' => 10,
                'requirements' => null,
                'resource_data' => [
                    'resource_type' => 'herb',
                    'quality' => 2,
                ],
            ],
            [
                'name' => 'Редкая трава',
                'description' => 'Очень редкая трава для продвинутой алхимии',
                'type' => 'resource',
                'subtype' => 'herb',
                'rarity' => 'rare',
                'level_required' => 6,
                'stackable' => true,
                'max_stack' => 100,
                'weight' => 0.1,
                'value' => 30,
                'requirements' => null,
                'resource_data' => [
                    'resource_type' => 'herb',
                    'quality' => 3,
                ],
            ],
            // Руда
            [
                'name' => 'Железная руда',
                'description' => 'Обычная железная руда для кузнечного дела',
                'type' => 'resource',
                'subtype' => 'ore',
                'rarity' => 'common',
                'level_required' => 1,
                'stackable' => true,
                'max_stack' => 100,
                'weight' => 1.0,
                'value' => 5,
                'requirements' => null,
                'resource_data' => [
                    'resource_type' => 'ore',
                    'quality' => 1,
                ],
            ],
            [
                'name' => 'Медная руда',
                'description' => 'Медная руда для создания бижутерии',
                'type' => 'resource',
                'subtype' => 'ore',
                'rarity' => 'common',
                'level_required' => 1,
                'stackable' => true,
                'max_stack' => 100,
                'weight' => 1.0,
                'value' => 3,
                'requirements' => null,
                'resource_data' => [
                    'resource_type' => 'ore',
                    'quality' => 1,
                ],
            ],
            [
                'name' => 'Серебряная руда',
                'description' => 'Дорогая серебряная руда',
                'type' => 'resource',
                'subtype' => 'ore',
                'rarity' => 'uncommon',
                'level_required' => 4,
                'stackable' => true,
                'max_stack' => 100,
                'weight' => 1.0,
                'value' => 20,
                'requirements' => null,
                'resource_data' => [
                    'resource_type' => 'ore',
                    'quality' => 2,
                ],
            ],
            [
                'name' => 'Золотая руда',
                'description' => 'Очень ценная золотая руда',
                'type' => 'resource',
                'subtype' => 'ore',
                'rarity' => 'rare',
                'level_required' => 7,
                'stackable' => true,
                'max_stack' => 100,
                'weight' => 1.0,
                'value' => 50,
                'requirements' => null,
                'resource_data' => [
                    'resource_type' => 'ore',
                    'quality' => 3,
                ],
            ],
            // Кожа
            [
                'name' => 'Кожа',
                'description' => 'Обычная кожа для кожевничества',
                'type' => 'resource',
                'subtype' => 'leather',
                'rarity' => 'common',
                'level_required' => 1,
                'stackable' => true,
                'max_stack' => 100,
                'weight' => 0.5,
                'value' => 3,
                'requirements' => null,
                'resource_data' => [
                    'resource_type' => 'leather',
                    'quality' => 1,
                ],
            ],
            [
                'name' => 'Толстая кожа',
                'description' => 'Прочная кожа для качественной брони',
                'type' => 'resource',
                'subtype' => 'leather',
                'rarity' => 'uncommon',
                'level_required' => 4,
                'stackable' => true,
                'max_stack' => 100,
                'weight' => 0.7,
                'value' => 15,
                'requirements' => null,
                'resource_data' => [
                    'resource_type' => 'leather',
                    'quality' => 2,
                ],
            ],
            [
                'name' => 'Драконья кожа',
                'description' => 'Очень редкая и прочная кожа',
                'type' => 'resource',
                'subtype' => 'leather',
                'rarity' => 'epic',
                'level_required' => 10,
                'stackable' => true,
                'max_stack' => 100,
                'weight' => 1.0,
                'value' => 200,
                'requirements' => null,
                'resource_data' => [
                    'resource_type' => 'leather',
                    'quality' => 4,
                ],
            ],
            // Драгоценные камни
            [
                'name' => 'Рубин',
                'description' => 'Красный драгоценный камень',
                'type' => 'resource',
                'subtype' => 'gem',
                'rarity' => 'uncommon',
                'level_required' => 3,
                'stackable' => true,
                'max_stack' => 50,
                'weight' => 0.1,
                'value' => 50,
                'requirements' => null,
                'resource_data' => [
                    'resource_type' => 'gem',
                    'quality' => 2,
                ],
            ],
            [
                'name' => 'Сапфир',
                'description' => 'Синий драгоценный камень',
                'type' => 'resource',
                'subtype' => 'gem',
                'rarity' => 'uncommon',
                'level_required' => 3,
                'stackable' => true,
                'max_stack' => 50,
                'weight' => 0.1,
                'value' => 50,
                'requirements' => null,
                'resource_data' => [
                    'resource_type' => 'gem',
                    'quality' => 2,
                ],
            ],
            [
                'name' => 'Изумруд',
                'description' => 'Зеленый драгоценный камень',
                'type' => 'resource',
                'subtype' => 'gem',
                'rarity' => 'uncommon',
                'level_required' => 3,
                'stackable' => true,
                'max_stack' => 50,
                'weight' => 0.1,
                'value' => 50,
                'requirements' => null,
                'resource_data' => [
                    'resource_type' => 'gem',
                    'quality' => 2,
                ],
            ],
            [
                'name' => 'Алмаз',
                'description' => 'Очень редкий и ценный камень',
                'type' => 'resource',
                'subtype' => 'gem',
                'rarity' => 'rare',
                'level_required' => 7,
                'stackable' => true,
                'max_stack' => 50,
                'weight' => 0.1,
                'value' => 200,
                'requirements' => null,
                'resource_data' => [
                    'resource_type' => 'gem',
                    'quality' => 3,
                ],
            ],
        ];

        foreach ($resources as $resourceData) {
            Item::updateOrCreate(
                ['name' => $resourceData['name']],
                $resourceData
            );
        }

        $this->command->info('Ресурсы созданы: '.count($resources));
    }

    /**
     * Создать расходники.
     */
    private function seedConsumables(): void
    {
        $consumables = [
            // Стрелы
            [
                'name' => 'Обычные стрелы',
                'description' => 'Стрелы для лука',
                'type' => 'consumable',
                'subtype' => 'arrow',
                'rarity' => 'common',
                'level_required' => 1,
                'stackable' => true,
                'max_stack' => 200,
                'weight' => 0.05,
                'value' => 1,
                'requirements' => null,
            ],
            [
                'name' => 'Железные стрелы',
                'description' => 'Более мощные стрелы',
                'type' => 'consumable',
                'subtype' => 'arrow',
                'rarity' => 'common',
                'level_required' => 3,
                'stackable' => true,
                'max_stack' => 200,
                'weight' => 0.05,
                'value' => 2,
                'requirements' => null,
            ],
            [
                'name' => 'Стальные стрелы',
                'description' => 'Очень мощные стрелы',
                'type' => 'consumable',
                'subtype' => 'arrow',
                'rarity' => 'uncommon',
                'level_required' => 6,
                'stackable' => true,
                'max_stack' => 200,
                'weight' => 0.05,
                'value' => 5,
                'requirements' => null,
            ],
            // Болты
            [
                'name' => 'Обычные болты',
                'description' => 'Болты для арбалета',
                'type' => 'consumable',
                'subtype' => 'bolt',
                'rarity' => 'common',
                'level_required' => 1,
                'stackable' => true,
                'max_stack' => 200,
                'weight' => 0.08,
                'value' => 2,
                'requirements' => null,
            ],
            [
                'name' => 'Железные болты',
                'description' => 'Более мощные болты',
                'type' => 'consumable',
                'subtype' => 'bolt',
                'rarity' => 'common',
                'level_required' => 3,
                'stackable' => true,
                'max_stack' => 200,
                'weight' => 0.08,
                'value' => 3,
                'requirements' => null,
            ],
            [
                'name' => 'Стальные болты',
                'description' => 'Очень мощные болты',
                'type' => 'consumable',
                'subtype' => 'bolt',
                'rarity' => 'uncommon',
                'level_required' => 6,
                'stackable' => true,
                'max_stack' => 200,
                'weight' => 0.08,
                'value' => 8,
                'requirements' => null,
            ],
        ];

        foreach ($consumables as $consumableData) {
            Item::updateOrCreate(
                ['name' => $consumableData['name']],
                $consumableData
            );
        }

        $this->command->info('Расходники созданы: '.count($consumables));
    }

    /**
     * Создать руны.
     */
    private function seedRunes(): void
    {
        $runes = [
            [
                'name' => 'Руна огня',
                'description' => 'Руна с заклинанием огня',
                'type' => 'rune',
                'subtype' => null,
                'rarity' => 'uncommon',
                'level_required' => 3,
                'stackable' => false,
                'max_stack' => 1,
                'weight' => 0.1,
                'value' => 150,
                'requirements' => ['level' => 3, 'attributes' => ['intelligence' => 10]],
                'rune_data' => [
                    'spell_id' => 'fire_bolt',
                    'charges' => 10,
                    'recharge_time' => 3600,
                    'mana_cost_per_use' => 5,
                ],
            ],
            [
                'name' => 'Руна льда',
                'description' => 'Руна с заклинанием льда',
                'type' => 'rune',
                'subtype' => null,
                'rarity' => 'uncommon',
                'level_required' => 4,
                'stackable' => false,
                'max_stack' => 1,
                'weight' => 0.1,
                'value' => 180,
                'requirements' => ['level' => 4, 'attributes' => ['intelligence' => 12]],
                'rune_data' => [
                    'spell_id' => 'ice_bolt',
                    'charges' => 10,
                    'recharge_time' => 3600,
                    'mana_cost_per_use' => 6,
                ],
            ],
            [
                'name' => 'Руна молнии',
                'description' => 'Руна с заклинанием молнии',
                'type' => 'rune',
                'subtype' => null,
                'rarity' => 'rare',
                'level_required' => 6,
                'stackable' => false,
                'max_stack' => 1,
                'weight' => 0.1,
                'value' => 400,
                'requirements' => ['level' => 6, 'attributes' => ['intelligence' => 18]],
                'rune_data' => [
                    'spell_id' => 'lightning_bolt',
                    'charges' => 15,
                    'recharge_time' => 3600,
                    'mana_cost_per_use' => 8,
                ],
            ],
            [
                'name' => 'Руна исцеления',
                'description' => 'Руна с заклинанием исцеления',
                'type' => 'rune',
                'subtype' => null,
                'rarity' => 'uncommon',
                'level_required' => 3,
                'stackable' => false,
                'max_stack' => 1,
                'weight' => 0.1,
                'value' => 200,
                'requirements' => ['level' => 3, 'attributes' => ['intelligence' => 10]],
                'rune_data' => [
                    'spell_id' => 'heal',
                    'charges' => 5,
                    'recharge_time' => 3600,
                    'mana_cost_per_use' => 10,
                ],
            ],
        ];

        foreach ($runes as $runeData) {
            Item::updateOrCreate(
                ['name' => $runeData['name']],
                $runeData
            );
        }

        $this->command->info('Руны созданы: '.count($runes));
    }

    /**
     * Создать свитки.
     */
    private function seedScrolls(): void
    {
        $scrolls = [
            [
                'name' => 'Свиток огненного шара',
                'description' => 'Одноразовое заклинание огненного шара',
                'type' => 'scroll',
                'subtype' => null,
                'rarity' => 'common',
                'level_required' => 2,
                'stackable' => true,
                'max_stack' => 50,
                'weight' => 0.1,
                'value' => 20,
                'requirements' => null,
                'scroll_data' => [
                    'spell_id' => 'fireball',
                    'is_consumable' => true,
                    'skill_required' => 'spellcasting',
                    'skill_level_required' => 1,
                ],
            ],
            [
                'name' => 'Свиток исцеления',
                'description' => 'Одноразовое заклинание исцеления',
                'type' => 'scroll',
                'subtype' => null,
                'rarity' => 'common',
                'level_required' => 2,
                'stackable' => true,
                'max_stack' => 50,
                'weight' => 0.1,
                'value' => 25,
                'requirements' => null,
                'scroll_data' => [
                    'spell_id' => 'heal',
                    'is_consumable' => true,
                    'skill_required' => 'spellcasting',
                    'skill_level_required' => 1,
                ],
            ],
            [
                'name' => 'Свиток молнии',
                'description' => 'Мощное одноразовое заклинание молнии',
                'type' => 'scroll',
                'subtype' => null,
                'rarity' => 'uncommon',
                'level_required' => 5,
                'stackable' => true,
                'max_stack' => 50,
                'weight' => 0.1,
                'value' => 60,
                'requirements' => null,
                'scroll_data' => [
                    'spell_id' => 'lightning',
                    'is_consumable' => true,
                    'skill_required' => 'spellcasting',
                    'skill_level_required' => 3,
                ],
            ],
            [
                'name' => 'Свиток телепортации',
                'description' => 'Одноразовое заклинание телепортации',
                'type' => 'scroll',
                'subtype' => null,
                'rarity' => 'rare',
                'level_required' => 8,
                'stackable' => true,
                'max_stack' => 50,
                'weight' => 0.1,
                'value' => 200,
                'requirements' => null,
                'scroll_data' => [
                    'spell_id' => 'teleport',
                    'is_consumable' => true,
                    'skill_required' => 'spellcasting',
                    'skill_level_required' => 5,
                ],
            ],
        ];

        foreach ($scrolls as $scrollData) {
            Item::updateOrCreate(
                ['name' => $scrollData['name']],
                $scrollData
            );
        }

        $this->command->info('Свитки созданы: '.count($scrolls));
    }

    /**
     * Создать валюту.
     */
    private function seedCurrency(): void
    {
        $currency = [
            [
                'name' => 'Медная монета',
                'description' => 'Самая мелкая монета',
                'type' => 'currency',
                'subtype' => 'coin',
                'rarity' => 'common',
                'level_required' => 1,
                'stackable' => true,
                'max_stack' => 10000,
                'weight' => 0.01,
                'value' => 1,
                'requirements' => null,
            ],
            [
                'name' => 'Серебряная монета',
                'description' => 'Средняя монета (1 серебро = 100 меди)',
                'type' => 'currency',
                'subtype' => 'coin',
                'rarity' => 'common',
                'level_required' => 1,
                'stackable' => true,
                'max_stack' => 10000,
                'weight' => 0.01,
                'value' => 100,
                'requirements' => null,
            ],
            [
                'name' => 'Золотая монета',
                'description' => 'Дорогая монета (1 золото = 100 серебра)',
                'type' => 'currency',
                'subtype' => 'coin',
                'rarity' => 'uncommon',
                'level_required' => 1,
                'stackable' => true,
                'max_stack' => 10000,
                'weight' => 0.01,
                'value' => 10000,
                'requirements' => null,
            ],
        ];

        foreach ($currency as $currencyData) {
            Item::updateOrCreate(
                ['name' => $currencyData['name']],
                $currencyData
            );
        }

        $this->command->info('Валюта создана: '.count($currency));
    }
}
