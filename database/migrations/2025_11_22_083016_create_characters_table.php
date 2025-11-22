<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('characters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name')->unique();
            $table->text('description')->nullable(); // Автоматически генерируется

            // Характеристики
            $table->unsignedTinyInteger('strength')->default(1);
            $table->unsignedTinyInteger('agility')->default(1);
            $table->unsignedTinyInteger('intelligence')->default(1);

            // Прогресс
            $table->unsignedTinyInteger('level')->default(1);
            $table->unsignedBigInteger('experience')->default(0);
            $table->unsignedTinyInteger('total_attributes_spent')->default(0);
            $table->unsignedTinyInteger('available_points')->default(2); // Стартовые очки

            // Здоровье и мана
            $table->unsignedSmallInteger('health_current');
            $table->unsignedSmallInteger('health_max');
            $table->unsignedSmallInteger('mana_current');
            $table->unsignedSmallInteger('mana_max');

            // Статус
            $table->boolean('is_criminal')->default(false);
            $table->timestamp('criminal_until')->nullable();
            $table->boolean('is_active')->default(true);

            // Локация (для будущего использования)
            $table->unsignedBigInteger('location_id')->nullable();

            $table->timestamps();

            $table->index('user_id');
            $table->index('is_active');
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('characters');
    }
};
