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
        Schema::create('character_deaths', function (Blueprint $table) {
            $table->id();
            $table->foreignId('character_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('killed_by_id')->nullable();
            $table->string('killed_by_type')->nullable(); // npc, player, environment
            $table->string('death_cause')->nullable(); // combat, fall, poison, etc.
            $table->timestamp('died_at');
            $table->timestamps();

            $table->index('character_id');
            $table->index('location_id');
            $table->index('died_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('character_deaths');
    }
};
