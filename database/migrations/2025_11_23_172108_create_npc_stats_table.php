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
        Schema::create('npc_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('npc_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('level')->default(1);
            $table->unsignedSmallInteger('health_max');
            $table->unsignedSmallInteger('health_current');
            $table->unsignedSmallInteger('mana_max')->default(0);
            $table->unsignedSmallInteger('mana_current')->default(0);
            $table->unsignedTinyInteger('strength')->default(1);
            $table->unsignedTinyInteger('agility')->default(1);
            $table->unsignedTinyInteger('intelligence')->default(1);
            $table->unsignedSmallInteger('attack_power')->default(0);
            $table->unsignedSmallInteger('defense')->default(0);
            $table->unsignedSmallInteger('magic_defense')->default(0);
            $table->decimal('accuracy', 5, 2)->default(50.00); // Процент точности
            $table->decimal('dodge', 5, 2)->default(0.00); // Процент уворота
            $table->decimal('critical_chance', 5, 2)->default(5.00); // Процент шанса крита
            $table->decimal('critical_power', 5, 2)->default(1.50); // Множитель крита
            $table->unsignedInteger('experience_reward')->default(0);
            $table->unsignedInteger('gold_reward_min')->default(0);
            $table->unsignedInteger('gold_reward_max')->default(0);
            $table->timestamps();

            $table->unique('npc_id');
            $table->index('level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('npc_stats');
    }
};
