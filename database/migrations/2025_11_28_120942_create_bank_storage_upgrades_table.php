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
        Schema::create('bank_storage_upgrades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('character_id')->constrained()->cascadeOnDelete();
            $table->foreignId('banker_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('additional_slots'); // Количество дополнительных слотов
            $table->unsignedInteger('purchase_cost'); // Стоимость покупки
            $table->timestamp('purchased_at'); // Время покупки
            $table->timestamp('expires_at')->nullable(); // Время истечения (если временное)
            $table->timestamps();

            $table->index('character_id');
            $table->index('banker_id');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_storage_upgrades');
    }
};
