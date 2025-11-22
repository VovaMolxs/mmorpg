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
        Schema::create('character_equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('character_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_instance_id')->constrained('item_instances')->cascadeOnDelete();

            // Слот экипировки
            $table->enum('slot', [
                'weapon_main',
                'weapon_offhand',
                'head',
                'chest',
                'legs',
                'hands',
                'feet',
                'amulet',
                'ring1',
                'ring2',
                'earring',
            ]);

            $table->timestamp('equipped_at')->useCurrent();

            $table->timestamps();

            // Уникальный индекс: один предмет в одном слоте
            $table->unique(['character_id', 'slot']);
            $table->index('character_id');
            $table->index('item_instance_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('character_equipment');
    }
};
