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
        Schema::create('corpse_containers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('character_id')->constrained()->cascadeOnDelete();
            $table->foreignId('death_id')->constrained('character_deaths')->cascadeOnDelete();
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();
            $table->string('corpse_type'); // innocent, criminal, monster, npc
            $table->json('items_data'); // JSON с предметами из инвентаря и экипировки
            $table->timestamp('expires_at'); // Время исчезновения через 60 минут
            $table->boolean('is_looted')->default(false); // Полностью ли разграблен
            $table->timestamps();

            $table->index('character_id');
            $table->index('death_id');
            $table->index('location_id');
            $table->index('expires_at');
            $table->index('is_looted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('corpse_containers');
    }
};
