<?php

namespace Database\Seeders;

use App\AccountStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'username' => 'admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'account_status' => AccountStatus::Active,
                'is_admin' => true,
                'max_characters' => 10,
                'preferred_language' => 'en',
                'game_settings' => User::getDefaultGameSettings(),
                'time_played_total' => 0,
            ]
        );

        $this->command->info('Администратор создан:');
        $this->command->info('Email: admin@example.com');
        $this->command->info('Пароль: password');
    }
}
