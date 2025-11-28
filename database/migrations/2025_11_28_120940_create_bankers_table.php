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
        Schema::create('bankers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('npc_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('storage_slots')->default(20); // Базовое количество слотов хранения
            $table->unsignedInteger('base_fee')->default(0); // Базовая плата за хранение
            $table->unsignedInteger('fee_per_slot')->default(10); // Плата за дополнительный слот
            $table->unsignedInteger('max_upgrade_slots')->default(100); // Максимальное количество улучшенных слотов
            $table->timestamps();

            $table->unique('npc_id');
            $table->index('location_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bankers');
    }
};
