<?php

namespace App\Console\Commands;

use App\Models\ActiveMonster;
use App\Models\MonsterSpawn;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SpawnMonsters extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monsters:spawn';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Spawn monsters based on spawn configurations';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting monster spawn process...');

        $spawns = MonsterSpawn::with(['monster.stats', 'location'])
            ->where('is_active', true)
            ->get();

        if ($spawns->isEmpty()) {
            $this->info('No active spawns found.');

            return Command::SUCCESS;
        }

        $spawnedCount = 0;
        $checkedCount = 0;

        foreach ($spawns as $spawn) {
            $checkedCount++;

            // Проверяем расписание спавна
            if (! $spawn->isWithinSchedule()) {
                $this->line("Spawn {$spawn->id} is outside schedule, skipping...");

                continue;
            }

            // Проверяем, нужно ли спавнить
            if (! $spawn->shouldSpawn()) {
                continue;
            }

            try {
                $spawned = $this->spawnMonster($spawn);
                if ($spawned) {
                    $spawnedCount++;
                    $spawn->last_spawn_at = now();
                    $spawn->save();
                    $this->info("Spawned monster {$spawn->monster->name} in location {$spawn->location->name}");
                }
            } catch (\Exception $e) {
                Log::error('Failed to spawn monster', [
                    'spawn_id' => $spawn->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                $this->error("Failed to spawn monster for spawn {$spawn->id}: {$e->getMessage()}");
            }
        }

        $this->info("Checked {$checkedCount} spawns, spawned {$spawnedCount} monsters.");

        return Command::SUCCESS;
    }

    /**
     * Создать активного монстра из спавна.
     */
    protected function spawnMonster(MonsterSpawn $spawn): bool
    {
        if (! $spawn->monster || ! $spawn->monster->stats) {
            $this->warn("Spawn {$spawn->id} has no monster template or stats.");

            return false;
        }

        return DB::transaction(function () use ($spawn) {
            $monsterTemplate = $spawn->monster;
            $monsterStats = $monsterTemplate->stats;

            // Создаем активного монстра
            $activeMonster = ActiveMonster::create([
                'monster_id' => $monsterTemplate->id,
                'location_id' => $spawn->location_id,
                'spawn_id' => $spawn->id,
                'health_current' => $monsterStats->health_max,
                'mana_current' => $monsterStats->mana_max,
                'spawned_at' => now(),
                'is_active' => true,
            ]);

            return $activeMonster !== null;
        });
    }
}
