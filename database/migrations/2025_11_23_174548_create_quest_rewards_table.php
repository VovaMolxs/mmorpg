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
        Schema::create('quest_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quest_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['experience', 'gold', 'item', 'reputation', 'skill']);
            $table->unsignedBigInteger('reward_id')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->boolean('is_choice')->default(false);
            $table->timestamps();

            $table->index('quest_id');
            $table->index('type');
            $table->index('is_choice');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quest_rewards');
    }
};
