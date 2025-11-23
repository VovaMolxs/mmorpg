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
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', [
                'city',
                'forest',
                'mountain',
                'dungeon',
                'river',
                'road',
                'field',
                'cave',
                'village',
                'castle',
                'building',
                'street',
                'square',
                'bank',
                'shop',
            ]);
            $table->boolean('is_safe_zone')->default(false);
            $table->unsignedTinyInteger('min_level')->nullable();
            $table->unsignedTinyInteger('max_level')->nullable();
            $table->integer('coordinate_x');
            $table->integer('coordinate_y');
            $table->string('image_url')->nullable();
            $table->string('background_music')->nullable();
            $table->timestamps();

            $table->unique(['coordinate_x', 'coordinate_y']);
            $table->index(['coordinate_x', 'coordinate_y']);
            $table->index('type');
            $table->index('is_safe_zone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
