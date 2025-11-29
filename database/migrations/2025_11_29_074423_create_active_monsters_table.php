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
        Schema::create('active_monsters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monster_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();
            $table->foreignId('spawn_id')->nullable()->constrained('monster_spawns')->nullOnDelete();
            $table->unsignedInteger('health_current');
            $table->unsignedInteger('mana_current');
            $table->timestamp('spawned_at');
            $table->timestamp('died_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('monster_id');
            $table->index('location_id');
            $table->index('spawn_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('active_monsters');
    }
};
