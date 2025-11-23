<?php

namespace App\Console\Commands;

use App\Models\ActiveNpc;
use App\Models\Npc;
use App\Models\NpcSpawn;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SpawnNpcs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'npcs:spawn';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Spawn NPCs based on spawn configurations';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting NPC spawn process...');

        $spawns = NpcSpawn::with(['npc.stats', 'location'])
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
                $spawned = $this->spawnNpc($spawn);
                if ($spawned) {
                    $spawnedCount++;
                    $spawn->last_spawn_at = now();
                    $spawn->save();
                    $this->info("Spawned NPC {$spawn->npc->name} in location {$spawn->location->name}");
                }
            } catch (\Exception $e) {
                Log::error('Failed to spawn NPC', [
                    'spawn_id' => $spawn->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                $this->error("Failed to spawn NPC for spawn {$spawn->id}: {$e->getMessage()}");
            }
        }

        $this->info("Checked {$checkedCount} spawns, spawned {$spawnedCount} NPCs.");

        return Command::SUCCESS;
    }

    /**
     * Создать активного NPC из спавна.
     */
    protected function spawnNpc(NpcSpawn $spawn): bool
    {
        if (! $spawn->npc || ! $spawn->npc->stats) {
            $this->warn("Spawn {$spawn->id} has no NPC template or stats.");

            return false;
        }

        return DB::transaction(function () use ($spawn) {
            $npcTemplate = $spawn->npc;
            $npcStats = $npcTemplate->stats;

            // Создаем активного NPC
            $activeNpc = ActiveNpc::create([
                'npc_id' => $npcTemplate->id,
                'location_id' => $spawn->location_id,
                'spawn_id' => $spawn->id,
                'health_current' => $npcStats->health_max,
                'mana_current' => $npcStats->mana_max,
                'spawned_at' => now(),
                'is_active' => true,
            ]);

            return $activeNpc !== null;
        });
    }
}
