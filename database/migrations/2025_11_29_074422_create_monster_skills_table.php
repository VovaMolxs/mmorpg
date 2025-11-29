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
        Schema::create('monster_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monster_id')->constrained()->cascadeOnDelete();
            $table->string('skill_name');
            $table->enum('skill_type', ['attack', 'heal', 'buff', 'debuff', 'summon'])->default('attack');
            $table->enum('damage_type', ['physical', 'fire', 'ice', 'lightning', 'poison'])->nullable();
            $table->unsignedInteger('power');
            $table->unsignedInteger('mana_cost')->default(0);
            $table->unsignedInteger('cooldown')->default(0); // В секундах
            $table->unsignedInteger('chance_to_use')->default(100); // 1-100
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('monster_id');
            $table->index('skill_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monster_skills');
    }
};
