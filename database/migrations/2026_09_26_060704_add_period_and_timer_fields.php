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
        Schema::table('tournaments', function (Blueprint $table) {
            $table->unsignedTinyInteger('periods')->nullable()->after('team_size'); // e.g. 4 quarters
            $table->unsignedInteger('period_minutes')->nullable()->after('periods'); // minutes per quarter
        });

        Schema::table('match_models', function (Blueprint $table) {
            $table->unsignedTinyInteger('current_period')->nullable()->after('status');
            $table->unsignedInteger('period_seconds_remaining')->nullable()->after('current_period');
            $table->boolean('timer_running')->default(false)->after('period_seconds_remaining');
            $table->timestamp('timer_started_at')->nullable()->after('timer_running');
        });

        Schema::table('game_models', function (Blueprint $table) {
            $table->boolean('is_draw')->default(false)->after('winner_team_id'); // for chess
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
