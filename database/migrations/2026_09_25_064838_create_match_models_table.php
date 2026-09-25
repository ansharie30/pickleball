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
        Schema::create('match_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained()->cascadeOnDelete();
            $table->foreignId('court_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('team_a_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('team_b_id')->constrained('teams')->cascadeOnDelete();
            $table->string('round')->nullable(); // e.g. "Quarterfinal", "Round 1"
            $table->enum('status', ['scheduled', 'in_progress', 'completed'])->default('scheduled');
            $table->foreignId('winner_team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('match_models');
    }
};
