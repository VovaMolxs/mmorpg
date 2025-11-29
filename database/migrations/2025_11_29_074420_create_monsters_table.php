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
        Schema::create('monsters', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['beast', 'humanoid', 'undead', 'elemental', 'demon', 'magical'])->default('beast');
            $table->unsignedInteger('level')->default(1);
            $table->enum('rank', ['normal', 'elite', 'boss', 'world_boss'])->default('normal');
            $table->unsignedBigInteger('faction_id')->nullable();
            $table->enum('ai_behavior', ['passive', 'neutral', 'aggressive'])->default('neutral');
            $table->unsignedInteger('respawn_time')->default(5); // В минутах
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('type');
            $table->index('level');
            $table->index('rank');
            $table->index('faction_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monsters');
    }
};
