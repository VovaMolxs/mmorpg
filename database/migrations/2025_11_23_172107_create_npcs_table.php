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
        Schema::create('npcs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['trader', 'teacher', 'quest_giver', 'simple', 'hostile'])->default('simple');
            $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
            $table->text('description')->nullable();
            $table->boolean('is_merchant')->default(false);
            $table->boolean('is_teacher')->default(false);
            $table->boolean('is_quest_giver')->default(false);
            $table->boolean('is_hostile')->default(false);
            $table->unsignedBigInteger('faction_id')->nullable();
            $table->enum('ai_behavior', ['passive', 'neutral', 'aggressive'])->default('neutral');
            $table->unsignedInteger('respawn_time')->default(5); // В минутах
            $table->timestamps();

            $table->index('location_id');
            $table->index('type');
            $table->index('is_hostile');
            $table->index('faction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('npcs');
    }
};
