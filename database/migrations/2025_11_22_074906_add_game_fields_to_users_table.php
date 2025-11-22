<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Для SQLite используем более простой подход
        if (DB::getDriverName() === 'sqlite') {
            // Создаем новую таблицу с нужной структурой
            DB::statement('CREATE TABLE users_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT NOT NULL UNIQUE,
                email TEXT NOT NULL UNIQUE,
                email_verified_at DATETIME,
                password TEXT NOT NULL,
                account_status TEXT NOT NULL DEFAULT "unverified",
                is_admin INTEGER NOT NULL DEFAULT 0,
                max_characters INTEGER NOT NULL DEFAULT 3,
                preferred_language TEXT NOT NULL DEFAULT "en",
                game_settings TEXT,
                last_login_at DATETIME,
                time_played_total INTEGER NOT NULL DEFAULT 0,
                remember_token TEXT,
                created_at DATETIME,
                updated_at DATETIME
            )');

            // Копируем данные из старой таблицы
            DB::statement('INSERT INTO users_new (id, username, email, email_verified_at, password, remember_token, created_at, updated_at)
                SELECT id, COALESCE(name, email), email, email_verified_at, password, remember_token, created_at, updated_at FROM users');

            // Удаляем старую таблицу и переименовываем новую
            DB::statement('DROP TABLE users');
            DB::statement('ALTER TABLE users_new RENAME TO users');
        } else {
            Schema::table('users', function (Blueprint $table) {
                // Добавляем username (временно nullable для копирования данных)
                $table->string('username')->nullable()->unique()->after('id');

                // Добавляем игровые поля
                $table->string('account_status')->default('unverified')->after('email_verified_at');
                $table->boolean('is_admin')->default(false)->after('account_status');
                $table->unsignedTinyInteger('max_characters')->default(3)->after('is_admin');
                $table->string('preferred_language', 10)->default('en')->after('max_characters');
                $table->json('game_settings')->nullable()->after('preferred_language');
                $table->timestamp('last_login_at')->nullable()->after('game_settings');
                $table->unsignedBigInteger('time_played_total')->default(0)->after('last_login_at');
            });

            // Копируем данные из name в username для существующих записей
            DB::statement('UPDATE users SET username = name WHERE username IS NULL');

            // Делаем username обязательным
            Schema::table('users', function (Blueprint $table) {
                $table->string('username')->nullable(false)->change();
            });

            // Удаляем старое поле name
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Восстанавливаем name
            $table->string('name')->after('id');
        });

        // Копируем данные обратно
        DB::statement('UPDATE users SET name = username');

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn([
                'username',
                'account_status',
                'is_admin',
                'max_characters',
                'preferred_language',
                'game_settings',
                'last_login_at',
                'time_played_total',
            ]);
        });
    }
};
