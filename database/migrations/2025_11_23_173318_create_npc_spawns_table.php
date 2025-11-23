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
        Schema::create('npc_spawns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('npc_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('min_instances')->default(0);
            $table->unsignedTinyInteger('max_instances')->default(3);
            $table->unsignedInteger('respawn_time_min')->default(5); // В минутах
            $table->unsignedInteger('respawn_time_max')->default(15); // В минутах
            $table->unsignedTinyInteger('spawn_chance')->default(100); // 1-100
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_spawn_at')->nullable();
            $table->unsignedInteger('spawn_radius')->default(0); // Радиус спавна от центра локации
            $table->json('spawn_schedule')->nullable(); // Расписание спавна
            $table->timestamps();

            $table->index('npc_id');
            $table->index('location_id');
            $table->index('is_active');
            $table->index('last_spawn_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('npc_spawns');
    }
};
