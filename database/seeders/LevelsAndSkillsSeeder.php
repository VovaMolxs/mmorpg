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
