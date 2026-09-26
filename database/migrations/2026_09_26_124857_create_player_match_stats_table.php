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
        Schema::create('player_match_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_model_id')->constrained('match_models')->cascadeOnDelete();
            $table->foreignId('player_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->unsignedInteger('points')->default(0);
            $table->unsignedInteger('assists')->default(0);
            $table->unsignedInteger('rebounds')->default(0);
            $table->timestamps();

            $table->unique(['match_model_id', 'player_profile_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_match_stats');
    }
};
