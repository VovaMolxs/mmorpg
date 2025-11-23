<?php

namespace Database\Seeders;

use App\Models\Level;
use App\Models\Skill;
use Illuminate\Database\Seeder;

class LevelsAndSkillsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedLevels();
        $this->seedSkills();
    }

    /**
     * Seed levels table.
     */
    private function seedLevels(): void
    {
        $levels = [
            [
                'level' => 1,
                'experience_required' => 0,
                'reward_points' => 2,
                'description' => 'Новичок',
            ],
            [
                'level' => 2,
                'experience_required' => 40,
                'reward_points' => 1,
                'description' => 'Ученик',
            ],
            [
                'level' => 3,
                'experience_required' => 80,
                'reward_points' => 1,
                'description' => 'Опытный',
            ],
        ];

        // Генерируем уровни до 20 с удвоением опыта
        $experience = 160;
        for ($level = 4; $level <= 20; $level++) {
            $levels[] = [
                'level' => $level,
                'experience_required' => $experience,
                'reward_points' => 1,
                'description' => "Уровень {$level}",
            ];
            $experience *= 2;
        }

        foreach ($levels as $levelData) {
            Level::updateOrCreate(
                ['level' => $levelData['level']],
                $levelData
            );
        }

        $this->command->info('Уровни созданы: '.count($levels));
    }

    /**
     * Seed skills table.
     */
    private function seedSkills(): void
    {
        $skills = [
            [
                'name' => 'Меткость',
                'description' => 'Повышает точность атак',
                'category' => 'combat',
                'max_level' => 5,
                'is_starting_skill' => true,
                'required_level' => 1,
                'attribute_requirements' => null,
                'parent_skill_id' => null,
                'mana_cost' => 0,
                'cooldown' => 0,
            ],
            [
                'name' => 'Уклонение',
                'description' => 'Увеличивает способность уклоняться от атак',
                'category' => 'combat',
                'max_level' => 5,
                'is_starting_skill' => true,
                'required_level' => 1,
                'attribute_requirements' => null,
                'parent_skill_id' => null,
                'mana_cost' => 0,
                'cooldown' => 0,
            ],
            [
                'name' => 'Перевязка ран',
                'description' => 'Позволяет лечить раны',
                'category' => 'survival',
                'max_level' => 3,
                'is_starting_skill' => true,
                'required_level' => 1,
                'attribute_requirements' => null,
                'parent_skill_id' => null,
                'mana_cost' => 0,
                'cooldown' => 60,
            ],
            [
                'name' => 'Базовое колдовство',
                'description' => 'Основы магических искусств',
                'category' => 'magic',
                'max_level' => 3,
                'is_starting_skill' => true,
                'required_level' => 1,
                'attribute_requirements' => ['intelligence' => 2],
                'parent_skill_id' => null,
                'mana_cost' => 10,
                'cooldown' => 30,
            ],
            // Боевые навыки
            [
                'name' => 'defense',
                'description' => 'Увеличивает физическую защиту',
                'category' => 'combat',
                'max_level' => 10,
                'is_starting_skill' => false,
                'required_level' => 1,
                'attribute_requirements' => null,
                'parent_skill_id' => null,
                'mana_cost' => 0,
                'cooldown' => 0,
            ],
            [
                'name' => 'magic_resistance',
                'description' => 'Повышает сопротивление магическим атакам',
                'category' => 'magic',
                'max_level' => 8,
                'is_starting_skill' => false,
                'required_level' => 1,
                'attribute_requirements' => null,
                'parent_skill_id' => null,
                'mana_cost' => 0,
                'cooldown' => 0,
            ],
            [
                'name' => 'critical_strike',
                'description' => 'Увеличивает шанс и силу критических ударов',
                'category' => 'combat',
                'max_level' => 5,
                'is_starting_skill' => false,
                'required_level' => 1,
                'attribute_requirements' => null,
                'parent_skill_id' => null,
                'mana_cost' => 0,
                'cooldown' => 0,
            ],
            [
                'name' => 'magic_critical',
                'description' => 'Увеличивает шанс и силу магических критических ударов',
                'category' => 'magic',
                'max_level' => 5,
                'is_starting_skill' => false,
                'required_level' => 1,
                'attribute_requirements' => null,
                'parent_skill_id' => null,
                'mana_cost' => 0,
                'cooldown' => 0,
            ],
            [
                'name' => 'accuracy',
                'description' => 'Повышает точность всех типов атак',
                'category' => 'combat',
                'max_level' => 8,
                'is_starting_skill' => false,
                'required_level' => 1,
                'attribute_requirements' => null,
                'parent_skill_id' => null,
                'mana_cost' => 0,
                'cooldown' => 0,
            ],
            [
                'name' => 'magic_dodge',
                'description' => 'Увеличивает шанс уворота от магических атак',
                'category' => 'magic',
                'max_level' => 6,
                'is_starting_skill' => false,
                'required_level' => 1,
                'attribute_requirements' => null,
                'parent_skill_id' => null,
                'mana_cost' => 0,
                'cooldown' => 0,
            ],
            [
                'name' => 'awareness',
                'description' => 'Повышает наблюдательность и защиту от краж',
                'category' => 'survival',
                'max_level' => 7,
                'is_starting_skill' => false,
                'required_level' => 1,
                'attribute_requirements' => null,
                'parent_skill_id' => null,
                'mana_cost' => 0,
                'cooldown' => 0,
            ],
            [
                'name' => 'stealth',
                'description' => 'Увеличивает скрытность и шанс успеха скрытных действий',
                'category' => 'survival',
                'max_level' => 8,
                'is_starting_skill' => false,
                'required_level' => 1,
                'attribute_requirements' => null,
                'parent_skill_id' => null,
                'mana_cost' => 0,
                'cooldown' => 0,
            ],
            [
                'name' => 'stealing',
                'description' => 'Повышает шанс успешной кражи',
                'category' => 'social',
                'max_level' => 6,
                'is_starting_skill' => false,
                'required_level' => 1,
                'attribute_requirements' => null,
                'parent_skill_id' => null,
                'mana_cost' => 0,
                'cooldown' => 0,
            ],
            // Навыки владения оружием
            [
                'name' => 'sword_mastery',
                'description' => 'Владение мечами и увеличение урона',
                'category' => 'combat',
                'max_level' => 10,
                'is_starting_skill' => false,
                'required_level' => 1,
                'attribute_requirements' => null,
                'parent_skill_id' => null,
                'mana_cost' => 0,
                'cooldown' => 0,
            ],
            [
                'name' => 'axe_mastery',
                'description' => 'Владение топорами и увеличение урона',
                'category' => 'combat',
                'max_level' => 10,
                'is_starting_skill' => false,
                'required_level' => 1,
                'attribute_requirements' => null,
                'parent_skill_id' => null,
                'mana_cost' => 0,
                'cooldown' => 0,
            ],
            [
                'name' => 'dagger_mastery',
                'description' => 'Владение кинжалами и увеличение урона',
                'category' => 'combat',
                'max_level' => 10,
                'is_starting_skill' => false,
                'required_level' => 1,
                'attribute_requirements' => null,
                'parent_skill_id' => null,
                'mana_cost' => 0,
                'cooldown' => 0,
            ],
            [
                'name' => 'staff_mastery',
                'description' => 'Владение посохами и увеличение урона',
                'category' => 'combat',
                'max_level' => 10,
                'is_starting_skill' => false,
                'required_level' => 1,
                'attribute_requirements' => null,
                'parent_skill_id' => null,
                'mana_cost' => 0,
                'cooldown' => 0,
            ],
            [
                'name' => 'archery',
                'description' => 'Стрельба из лука и увеличение урона',
                'category' => 'combat',
                'max_level' => 10,
                'is_starting_skill' => false,
                'required_level' => 1,
                'attribute_requirements' => null,
                'parent_skill_id' => null,
                'mana_cost' => 0,
                'cooldown' => 0,
            ],
            [
                'name' => 'crossbow_mastery',
                'description' => 'Владение арбалетами и увеличение урона',
                'category' => 'combat',
                'max_level' => 10,
                'is_starting_skill' => false,
                'required_level' => 1,
                'attribute_requirements' => null,
                'parent_skill_id' => null,
                'mana_cost' => 0,
                'cooldown' => 0,
            ],
            [
                'name' => 'spellcasting',
                'description' => 'Колдовство и применение заклинаний',
                'category' => 'magic',
                'max_level' => 10,
                'is_starting_skill' => false,
                'required_level' => 1,
                'attribute_requirements' => null,
                'parent_skill_id' => null,
                'mana_cost' => 0,
                'cooldown' => 0,
            ],
            [
                'name' => 'health_regeneration',
                'description' => 'Увеличивает скорость восстановления здоровья',
                'category' => 'survival',
                'max_level' => 10,
                'is_starting_skill' => false,
                'required_level' => 1,
                'attribute_requirements' => null,
                'parent_skill_id' => null,
                'mana_cost' => 0,
                'cooldown' => 0,
            ],
            [
                'name' => 'mana_regeneration',
                'description' => 'Увеличивает скорость восстановления маны',
                'category' => 'magic',
                'max_level' => 10,
                'is_starting_skill' => false,
                'required_level' => 1,
                'attribute_requirements' => null,
                'parent_skill_id' => null,
                'mana_cost' => 0,
                'cooldown' => 0,
            ],
        ];

        foreach ($skills as $skillData) {
            Skill::updateOrCreate(
                ['name' => $skillData['name']],
                $skillData
            );
        }

        $this->command->info('Навыки созданы: '.count($skills));
    }
}
