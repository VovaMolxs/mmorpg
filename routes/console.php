<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Регенерация здоровья и маны персонажей каждую минуту
// Для запуска каждые 15 секунд добавьте в crontab:
// * * * * * cd /path-to-project && php artisan characters:regenerate-health >> /dev/null 2>&1
// */15 * * * * cd /path-to-project && php artisan characters:regenerate-health >> /dev/null 2>&1
Schedule::command('characters:regenerate-health')
    ->everyMinute()
    ->appendOutputTo(storage_path('logs/scheduler.log')); // Логирование для отладки

// Очистка истекших предметов на земле каждые 5 минут
Schedule::command('items:clean-expired')
    ->everyFiveMinutes()
    ->appendOutputTo(storage_path('logs/scheduler.log'));

// Спавн предметов в локациях каждые 5 минут
Schedule::command('items:spawn-location')
    ->everyFiveMinutes()
    ->appendOutputTo(storage_path('logs/scheduler.log'));

// Восстановление зарядов рун каждую минуту
Schedule::command('items:recharge-runes')
    ->everyMinute()
    ->appendOutputTo(storage_path('logs/scheduler.log'));

// Автоматический выход неактивных персонажей каждую минуту
Schedule::command('sessions:auto-logout')
    ->everyMinute()
    ->appendOutputTo(storage_path('logs/scheduler.log'));

// Очистка старых сессий ежедневно в 3:00
Schedule::command('sessions:cleanup')
    ->dailyAt('03:00')
    ->appendOutputTo(storage_path('logs/scheduler.log'));
