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
        Schema::create('location_item_spawns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('min_quantity')->default(1);
            $table->unsignedInteger('max_quantity')->default(2);
            $table->unsignedInteger('respawn_time_min')->default(20); // в минутах
            $table->unsignedInteger('respawn_time_max')->default(30); // в минутах
            $table->unsignedInteger('max_instances')->default(1);
            $table->unsignedTinyInteger('spawn_chance')->default(100); // 1-100
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_spawn_at')->nullable();
            $table->timestamps();

            // Индексы для оптимизации
            $table->index(['is_active', 'last_spawn_at']);
            $table->index('location_id');
            $table->index('item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('location_item_spawns');
    }
};
