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
        Schema::create('dialogs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('npc_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_dialog_id')->nullable()->constrained('dialogs')->nullOnDelete();
            $table->text('text');
            $table->boolean('is_initial')->default(false);
            $table->unsignedInteger('min_level')->nullable();
            $table->foreignId('required_quest_id')->nullable()->constrained('quests')->nullOnDelete();
            $table->enum('required_quest_status', ['active', 'completed', 'failed', 'abandoned'])->nullable();
            $table->timestamps();

            $table->index('npc_id');
            $table->index('parent_dialog_id');
            $table->index('is_initial');
            $table->index(['npc_id', 'is_initial']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dialogs');
    }
};
