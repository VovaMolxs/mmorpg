<?php

namespace App\Console\Commands;

use App\Models\CharacterSession;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupOldSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sessions:cleanup {--days=30 : Количество дней для хранения старых сессий}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Очистка старых завершенных сессий (запускать ежедневно)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $this->info("Очистка сессий старше {$days} дней...");

        $cutoffDate = now()->subDays($days);

        $deletedCount = DB::transaction(function () use ($cutoffDate) {
            return CharacterSession::where('is_online', false)
                ->whereNotNull('logout_at')
                ->where('logout_at', '<', $cutoffDate)
                ->delete();
        });

        $this->info("Удалено {$deletedCount} старых сессий.");

        // Также удаляем зависшие сессии (онлайн, но без активности более 24 часов)
        $stuckCutoffDate = now()->subDay();
        $stuckSessions = CharacterSession::where('is_online', true)
            ->where('last_activity_at', '<', $stuckCutoffDate)
            ->get();

        $stuckCount = 0;
        foreach ($stuckSessions as $session) {
            DB::transaction(function () use ($session) {
                $character = $session->character;
                $presence = $character->presence;

                $session->endSession();
                if ($presence) {
                    $presence->setOffline();
                }
            });
            $stuckCount++;
        }

        if ($stuckCount > 0) {
            $this->info("Исправлено {$stuckCount} зависших сессий.");
        }

        return Command::SUCCESS;
    }
}
