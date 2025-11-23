<?php

namespace App\Console\Commands;

use App\Models\LocationItemSpawn;
use Illuminate\Console\Command;

class SpawnLocationItems extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'items:spawn-location';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Спавн предметов в локациях согласно настройкам';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Проверка спавна предметов в локациях...');

        $spawns = LocationItemSpawn::where('is_active', true)->get();
        $spawnedCount = 0;

        foreach ($spawns as $spawn) {
            if ($spawn->canSpawn()) {
                $instance = $spawn->spawn();
                if ($instance) {
                    $spawnedCount++;
                    $this->line("Создан предмет: {$spawn->item->name} в локации {$spawn->location->name} (x{$instance->quantity})");
                }
            }
        }

        $this->info("Создано предметов: {$spawnedCount}");

        return Command::SUCCESS;
    }
}
