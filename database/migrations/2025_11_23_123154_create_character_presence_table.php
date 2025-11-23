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
        Schema::create('character_presence', function (Blueprint $table) {
            $table->id();
            $table->foreignId('character_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_visible')->default(true);
            $table->timestamp('entered_world_at')->nullable();
            $table->timestamp('last_action_at')->nullable();
            $table->enum('status', ['online', 'afk', 'offline'])->default('offline');
            $table->timestamps();

            $table->unique('character_id');
            $table->index('location_id');
            $table->index('status');
            $table->index('is_visible');
            $table->index('last_action_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('character_presence');
    }
};
