<?php

namespace Database\Seeders;

use App\Models\Banker;
use App\Models\Location;
use App\Models\Npc;
use App\Models\NpcSpawn;
use App\Models\NpcStat;
use Illuminate\Database\Seeder;

class BankerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Создание банкиров...');

        // Получаем локации типа банк или площадь
        $bankLocations = Location::whereIn('type', ['bank', 'square', 'building'])
            ->where('is_safe_zone', true)
            ->get();

        if ($bankLocations->isEmpty()) {
            $this->command->warn('Не найдено подходящих локаций для банкиров. Создаем банкиров в первых доступных локациях.');
            $bankLocations = Location::where('is_safe_zone', true)
                ->take(3)
                ->get();
        }

        $bankerTypes = [
            [
                'name' => 'Банкир Игорь',
                'description' => 'Солидный мужчина в дорогом костюме, сидит за массивным столом. Перед ним разложены документы и счетные книги. Его взгляд внимателен и проницателен.',
                'storage_slots' => 20,
                'base_fee' => 0,
                'fee_per_slot' => 10,
                'max_upgrade_slots' => 100,
            ],
            [
                'name' => 'Главный банкир Владимир',
                'description' => 'Пожилой мужчина с седой бородой, одетый в роскошные одежды. Он управляет крупнейшим банком в регионе. Его слово - закон в финансовых вопросах.',
                'storage_slots' => 30,
                'base_fee' => 0,
                'fee_per_slot' => 8,
                'max_upgrade_slots' => 150,
            ],
            [
                'name' => 'Банкир Анна',
                'description' => 'Элегантная женщина средних лет в строгом деловом костюме. Она известна своей честностью и надежностью. Многие доверяют ей свои сбережения.',
                'storage_slots' => 25,
                'base_fee' => 0,
                'fee_per_slot' => 9,
                'max_upgrade_slots' => 120,
            ],
            [
                'name' => 'Гильдейский банкир Дмитрий',
                'description' => 'Банкир, специализирующийся на работе с гильдиями. Он предлагает особые условия для членов гильдий и крупных организаций.',
                'storage_slots' => 40,
                'base_fee' => 0,
                'fee_per_slot' => 7,
                'max_upgrade_slots' => 200,
            ],
            [
                'name' => 'Таемный банкир Тень',
                'description' => 'Загадочная фигура в темном плаще, скрывающая лицо под капюшоном. Он работает с теми, кому нужна полная конфиденциальность. Говорят, он хранит даже незаконные предметы.',
                'storage_slots' => 15,
                'base_fee' => 50,
                'fee_per_slot' => 20,
                'max_upgrade_slots' => 80,
            ],
        ];

        foreach ($bankLocations as $index => $location) {
            if ($index >= count($bankerTypes)) {
                break;
            }

            $bankerData = $bankerTypes[$index];

            // Создаем NPC-банкира
            $npc = Npc::create([
                'name' => $bankerData['name'],
                'type' => 'simple',
                'location_id' => $location->id,
                'description' => $bankerData['description'],
                'is_merchant' => false,
                'is_teacher' => false,
                'is_quest_giver' => false,
                'is_hostile' => false,
                'faction_id' => null,
                'ai_behavior' => 'passive',
                'respawn_time' => 5,
            ]);

            // Создаем статистику для банкира
            NpcStat::create([
                'npc_id' => $npc->id,
                'level' => 1,
                'health_max' => 100,
                'health_current' => 100,
                'mana_max' => 50,
                'mana_current' => 50,
                'strength' => 5,
                'agility' => 5,
                'intelligence' => 10,
                'attack_power' => 0,
                'defense' => 5,
                'magic_defense' => 5,
                'accuracy' => 50,
                'dodge' => 0,
                'critical_chance' => 0,
                'critical_power' => 1.0,
                'experience_reward' => 0,
                'gold_reward_min' => 0,
                'gold_reward_max' => 0,
            ]);

            // Создаем банкира
            $banker = Banker::create([
                'npc_id' => $npc->id,
                'location_id' => $location->id,
                'storage_slots' => $bankerData['storage_slots'],
                'base_fee' => $bankerData['base_fee'],
                'fee_per_slot' => $bankerData['fee_per_slot'],
                'max_upgrade_slots' => $bankerData['max_upgrade_slots'],
            ]);

            // Создаем спавн для банкира (банкиры всегда должны быть активны)
            NpcSpawn::firstOrCreate(
                [
                    'npc_id' => $npc->id,
                    'location_id' => $location->id,
                ],
                [
                    'min_instances' => 1,
                    'max_instances' => 1,
                    'respawn_time_min' => 1,
                    'respawn_time_max' => 5,
                    'spawn_chance' => 100,
                    'is_active' => true,
                    'spawn_radius' => 0,
                    'spawn_schedule' => ['type' => 'always'],
                ]
            );

            $this->command->info("Создан банкир: {$bankerData['name']} в локации: {$location->name}");
        }

        $this->command->info('Банкиры успешно созданы!');
    }
}
