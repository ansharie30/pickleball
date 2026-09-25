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
        Schema::create('game_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_model_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('game_number'); // 1, 2, 3 for best-of-3
            $table->unsignedTinyInteger('team_a_score')->default(0);
            $table->unsignedTinyInteger('team_b_score')->default(0);
            $table->foreignId('winner_team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_models');
    }
};
