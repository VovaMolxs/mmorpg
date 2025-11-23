<?php

namespace App\Console\Commands;

use App\Models\Character;
use Illuminate\Console\Command;

class RegenerateHealth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'characters:regenerate-health';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Регенерировать здоровье персонажей (запускать каждые 15 секунд)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Регенерация здоровья и маны персонажей...');

        // Получаем всех активных персонажей, у которых здоровье или мана не на максимуме
        $characters = Character::where('is_active', true)
            ->where(function ($query) {
                $query->whereColumn('health_current', '<', 'health_max')
                    ->orWhereColumn('mana_current', '<', 'mana_max');
            })
            ->get();

        $regeneratedHealth = 0;
        $regeneratedMana = 0;
        $totalHealed = 0;
        $totalRestoredMana = 0;

        foreach ($characters as $character) {
            $needsSave = false;

            // Регенерация здоровья
            if ($character->health_current < $character->health_max) {
                $regenerationRate = $character->calculateHealthRegenerationRate();
                $healed = $character->heal($regenerationRate);

                if ($healed > 0) {
                    $regeneratedHealth++;
                    $totalHealed += $healed;
                    $needsSave = true;
                }
            }

            // Регенерация маны
            if ($character->mana_current < $character->mana_max) {
                $manaRegenerationRate = $character->calculateManaRegenerationRate();
                $restoredMana = $character->restoreMana($manaRegenerationRate);

                if ($restoredMana > 0) {
                    $regeneratedMana++;
                    $totalRestoredMana += $restoredMana;
                    $needsSave = true;
                }
            }

            if ($needsSave) {
                $character->save();
            }
        }

        $this->info("Регенерировано здоровье для {$regeneratedHealth} персонажей (всего восстановлено: {$totalHealed} HP)");
        $this->info("Регенерировано маны для {$regeneratedMana} персонажей (всего восстановлено: {$totalRestoredMana} MP)");

        return Command::SUCCESS;
    }
}
