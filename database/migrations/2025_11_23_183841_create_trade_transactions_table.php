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
        Schema::create('trade_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('character_id')->constrained()->cascadeOnDelete();
            $table->foreignId('npc_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['buy', 'sell']); // Тип сделки
            $table->unsignedInteger('quantity'); // Количество предметов
            $table->unsignedBigInteger('unit_price'); // Цена за единицу
            $table->unsignedBigInteger('total_price'); // Общая стоимость
            $table->unsignedBigInteger('character_gold_before'); // Золото персонажа до сделки
            $table->unsignedBigInteger('character_gold_after'); // Золото персонажа после сделки
            $table->json('metadata')->nullable(); // Дополнительные данные (item_instance_id, merchant_inventory_id и т.д.)
            $table->timestamps();

            $table->index('character_id');
            $table->index('npc_id');
            $table->index('item_id');
            $table->index('type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trade_transactions');
    }
};
