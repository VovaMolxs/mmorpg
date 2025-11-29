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
        Schema::create('monster_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monster_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('health_max');
            $table->unsignedInteger('mana_max');
            $table->unsignedInteger('attack_power');
            $table->unsignedInteger('magic_power');
            $table->unsignedInteger('defense');
            $table->unsignedInteger('magic_defense');
            $table->decimal('accuracy', 5, 2)->default(0);
            $table->decimal('magic_accuracy', 5, 2)->default(0);
            $table->decimal('dodge', 5, 2)->default(0);
            $table->decimal('critical_chance', 5, 2)->default(0);
            $table->decimal('critical_power', 5, 2)->default(0);
            $table->unsignedInteger('experience_reward')->default(0);
            $table->enum('attack_type', ['physical', 'magical', 'hybrid'])->default('physical');
            $table->timestamps();

            $table->unique('monster_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monster_stats');
    }
};
