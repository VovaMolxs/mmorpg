<?php

namespace App\Console\Commands;

use App\Models\ItemInstance;
use Illuminate\Console\Command;

class RechargeRunes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'items:recharge-runes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Восстановить заряды рун';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Восстановление зарядов рун...');

        $runes = ItemInstance::whereHas('item', function ($query) {
            $query->where('type', 'rune');
        })->with('item')->get();

        $recharged = 0;

        foreach ($runes as $rune) {
            $runeData = $rune->item->rune_data;
            if (! $runeData) {
                continue;
            }

            $maxCharges = $runeData['charges'] ?? 0;
            $rechargeTime = $runeData['recharge_time'] ?? 0; // в минутах

            // Получаем текущие заряды из rune_data экземпляра или используем максимум
            $currentCharges = $runeData['current_charges'] ?? $maxCharges;
            $lastRecharge = $runeData['last_recharge'] ?? null;

            // Если заряды не на максимуме и прошло достаточно времени
            if ($currentCharges < $maxCharges) {
                if ($lastRecharge === null || now()->diffInMinutes($lastRecharge) >= $rechargeTime) {
                    $runeData['current_charges'] = $maxCharges;
                    $runeData['last_recharge'] = now()->toDateTimeString();

                    // Обновляем rune_data в экземпляре (если храним там) или в item
                    // Для простоты, обновляем в item, но это не идеально
                    // В будущем лучше хранить текущие заряды в item_instances
                    $rune->item->rune_data = $runeData;
                    $rune->item->save();

                    $recharged++;
                }
            }
        }

        $this->info("Восстановлено зарядов для {$recharged} рун");

        return Command::SUCCESS;
    }
}
