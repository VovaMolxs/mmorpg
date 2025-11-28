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
        Schema::table('resurrection_stones', function (Blueprint $table) {
            $table->integer('level_required')->default(1)->after('description');
            $table->integer('cooldown_minutes')->default(0)->after('level_required');
            $table->timestamp('last_used_at')->nullable()->after('cooldown_minutes');
            $table->string('visual_effect')->default('glow')->after('last_used_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('resurrection_stones', function (Blueprint $table) {
            $table->dropColumn(['level_required', 'cooldown_minutes', 'last_used_at', 'visual_effect']);
        });
    }
};
