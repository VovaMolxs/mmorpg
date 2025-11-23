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
        Schema::create('quests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->unsignedInteger('min_level')->nullable();
            $table->unsignedInteger('max_level')->nullable();
            $table->foreignId('quest_giver_npc_id')->nullable()->constrained('npcs')->nullOnDelete();
            $table->foreignId('turn_in_npc_id')->nullable()->constrained('npcs')->nullOnDelete();
            $table->foreignId('previous_quest_id')->nullable()->constrained('quests')->nullOnDelete();
            $table->unsignedBigInteger('faction_required_id')->nullable();
            $table->integer('reputation_required')->nullable();
            $table->timestamps();

            $table->index('quest_giver_npc_id');
            $table->index('turn_in_npc_id');
            $table->index('previous_quest_id');
            $table->index('min_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quests');
    }
};
