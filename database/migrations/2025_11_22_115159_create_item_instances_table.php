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
        Schema::create('item_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedInteger('durability_current')->nullable(); // Текущая прочность (для брони/оружия)

            // Расположение предмета
            $table->enum('location_type', ['inventory', 'equipped', 'ground', 'container', 'vendor', 'auction'])->default('inventory');
            $table->unsignedBigInteger('location_id')->nullable(); // ID контейнера/персонажа/локации

            // Координаты на земле/в инвентаре
            $table->integer('position_x')->nullable();
            $table->integer('position_y')->nullable();

            // Время жизни для предметов на земле
            $table->timestamp('expires_at')->nullable();

            // Владелец для квестовых предметов
            $table->foreignId('owner_id')->nullable()->constrained('characters')->nullOnDelete();

            $table->timestamps();

            // Индексы для оптимизации
            $table->index(['location_type', 'location_id']);
            $table->index('expires_at');
            $table->index('owner_id');
            $table->index('item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_instances');
    }
};
