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
        Schema::create('merchant_inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('npc_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(0); // Текущее количество
            $table->unsignedInteger('max_quantity')->default(0); // Максимальное количество (0 = безлимит)
            $table->unsignedInteger('base_price')->default(0); // Базовая цена покупки
            $table->unsignedInteger('base_sell_price')->default(0); // Базовая цена продажи торговцу
            $table->decimal('price_multiplier', 5, 2)->default(1.00); // Множитель цены (для динамического ценообразования)
            $table->unsignedInteger('purchase_limit_per_day')->nullable(); // Лимит покупок в день на игрока
            $table->boolean('is_available')->default(true); // Доступен ли предмет для покупки
            $table->timestamp('restocked_at')->nullable(); // Время последнего пополнения
            $table->timestamps();

            $table->unique(['npc_id', 'item_id']);
            $table->index('npc_id');
            $table->index('item_id');
            $table->index('is_available');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('merchant_inventories');
    }
};
