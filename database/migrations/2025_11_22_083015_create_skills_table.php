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
        Schema::create('skills', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category'); // combat, magic, craft, survival, social
            $table->unsignedTinyInteger('max_level')->default(10);
            $table->boolean('is_starting_skill')->default(false);
            $table->unsignedTinyInteger('required_level')->default(1);
            $table->json('attribute_requirements')->nullable(); // {strength: 2, intelligence: 1}
            $table->foreignId('parent_skill_id')->nullable()->constrained('skills')->nullOnDelete();
            $table->unsignedSmallInteger('mana_cost')->default(0);
            $table->unsignedSmallInteger('cooldown')->default(0); // в секундах
            $table->timestamps();

            $table->index('category');
            $table->index('is_starting_skill');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skills');
    }
};
