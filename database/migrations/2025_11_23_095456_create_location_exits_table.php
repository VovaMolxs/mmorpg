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
        Schema::create('location_exits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_location_id')->constrained('locations')->cascadeOnDelete();
            $table->foreignId('to_location_id')->constrained('locations')->cascadeOnDelete();
            $table->enum('direction', ['north', 'south', 'east', 'west']);
            $table->string('custom_name')->nullable();
            $table->boolean('is_locked')->default(false);
            $table->foreignId('required_item_id')->nullable()->constrained('items')->nullOnDelete();
            $table->string('required_skill')->nullable();
            $table->unsignedTinyInteger('required_skill_level')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['from_location_id', 'direction']);
            $table->index('to_location_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('location_exits');
    }
};
