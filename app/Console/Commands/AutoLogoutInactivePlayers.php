<?php

namespace App\Console\Commands;

use App\Models\CharacterSession;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AutoLogoutInactivePlayers extends Command
{
    /**
     * Таймаут бездействия в минутах.
     */
    private const TIMEOUT_MINUTES = 15;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sessions:auto-logout';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Автоматический выход неактивных персонажей (запускать каждую минуту)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Проверка неактивных сессий...');

        $timeoutDate = now()->subMinutes(self::TIMEOUT_MINUTES);

        $expiredSessions = CharacterSession::where('is_online', true)
            ->whereNull('logout_at')
            ->where('last_activity_at', '<', $timeoutDate)
            ->with('character.presence')
            ->get();

        $loggedOutCount = 0;

        foreach ($expiredSessions as $session) {
            try {
                DB::transaction(function () use ($session) {
                    $character = $session->character;
                    $presence = $character->presence;

                    // Завершение сессии
                    $session->endSession();

                    // Обновление присутствия
                    if ($presence) {
                        $presence->setOffline();
                    }
                });

                $loggedOutCount++;
            } catch (\Exception $e) {
                $this->error("Ошибка при выходе персонажа {$session->character_id}: {$e->getMessage()}");
            }
        }

        if ($loggedOutCount > 0) {
            $this->info("Автоматически вышло {$loggedOutCount} неактивных персонажей.");
        } else {
            $this->info('Неактивных персонажей не найдено.');
        }

        return Command::SUCCESS;
    }
}
