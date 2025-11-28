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
        Schema::create('bank_storages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('character_id')->constrained()->cascadeOnDelete();
            $table->foreignId('banker_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('slot_number'); // Номер ячейки (1-100+)
            $table->foreignId('item_instance_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_locked')->default(false); // Заблокирована ли ячейка
            $table->timestamp('deposited_at')->nullable(); // Время помещения
            $table->timestamp('withdrawn_at')->nullable(); // Время изъятия
            $table->timestamps();

            $table->unique(['character_id', 'banker_id', 'slot_number']);
            $table->index('character_id');
            $table->index('banker_id');
            $table->index('item_instance_id');
            $table->index('is_locked');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_storages');
    }
};
