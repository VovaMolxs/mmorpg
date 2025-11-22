<?php

namespace App\Console\Commands;

use App\Models\ItemInstance;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CleanExpiredItems extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'items:clean-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Удалить предметы на земле с истекшим временем жизни';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Очистка истекших предметов...');

        $deleted = ItemInstance::where('location_type', 'ground')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', Carbon::now())
            ->whereNull('owner_id') // Квестовые предметы не удаляем
            ->delete();

        $this->info("Удалено предметов: {$deleted}");

        return Command::SUCCESS;
    }
}
