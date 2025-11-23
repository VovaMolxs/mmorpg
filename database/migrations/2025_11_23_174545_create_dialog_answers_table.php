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
        Schema::create('dialog_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dialog_id')->constrained()->cascadeOnDelete();
            $table->string('text');
            $table->foreignId('next_dialog_id')->nullable()->constrained('dialogs')->nullOnDelete();
            $table->foreignId('quest_trigger_id')->nullable()->constrained('quests')->nullOnDelete();
            $table->foreignId('item_required_id')->nullable()->constrained('items')->nullOnDelete();
            $table->foreignId('skill_required')->nullable()->constrained('skills')->nullOnDelete();
            $table->unsignedInteger('skill_level_required')->nullable();
            $table->timestamps();

            $table->index('dialog_id');
            $table->index('next_dialog_id');
            $table->index('quest_trigger_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dialog_answers');
    }
};
