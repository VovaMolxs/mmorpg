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
        Schema::create('monster_spawns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monster_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('min_instances')->default(1);
            $table->unsignedInteger('max_instances')->default(1);
            $table->unsignedInteger('respawn_time_min')->default(5); // В минутах
            $table->unsignedInteger('respawn_time_max')->default(10); // В минутах
            $table->unsignedInteger('spawn_chance')->default(100); // 1-100
            $table->unsignedInteger('spawn_radius')->default(0);
            $table->json('spawn_schedule')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_spawn_at')->nullable();
            $table->timestamps();

            $table->index('monster_id');
            $table->index('location_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monster_spawns');
    }
};
