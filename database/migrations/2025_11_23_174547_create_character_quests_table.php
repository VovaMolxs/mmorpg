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
        Schema::create('character_quests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('character_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quest_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['active', 'completed', 'failed', 'abandoned'])->default('active');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('current_progress')->nullable();
            $table->timestamps();

            $table->index('character_id');
            $table->index('quest_id');
            $table->index('status');
            $table->index(['character_id', 'status']);
            // Уникальность только для активных квестов - один персонаж не может иметь один квест активным дважды
            $table->unique(['character_id', 'quest_id'], 'character_quest_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('character_quests');
    }
};
