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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();

            // Тип и подтип предмета
            $table->string('type'); // weapon, armor, jewelry, resource, potion, consumable, currency, rune, scroll
            $table->string('subtype')->nullable(); // sword, helmet, herb, etc.
            $table->enum('rarity', ['common', 'uncommon', 'rare', 'epic', 'legendary'])->default('common');

            // Требования и характеристики
            $table->unsignedTinyInteger('level_required')->default(1);
            $table->boolean('stackable')->default(false);
            $table->unsignedInteger('max_stack')->default(1);
            $table->decimal('weight', 8, 2)->default(0);
            $table->unsignedBigInteger('value')->default(0); // Базовая стоимость

            // JSON поля для требований и специфичных данных
            $table->json('requirements')->nullable(); // attributes, skills, level
            $table->json('weapon_data')->nullable(); // damage_min, damage_max, attack_speed, weapon_type, range
            $table->json('armor_data')->nullable(); // defense, durability, durability_max, armor_type
            $table->json('jewelry_data')->nullable(); // attributes_bonus
            $table->json('potion_data')->nullable(); // effect_type, effect_power, duration, cooldown
            $table->json('rune_data')->nullable(); // spell_id, charges, recharge_time, mana_cost_per_use
            $table->json('scroll_data')->nullable(); // spell_id, is_consumable, skill_required, skill_level_required
            $table->json('resource_data')->nullable(); // resource_type, quality

            $table->timestamps();

            // Индексы для оптимизации
            $table->index(['type', 'subtype']);
            $table->index('rarity');
            $table->index('level_required');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
