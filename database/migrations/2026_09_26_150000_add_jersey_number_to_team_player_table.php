<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('team_player', function (Blueprint $table) {
            $table->unsignedTinyInteger('jersey_number')->nullable()->after('player_profile_id');
        });
    }

    public function down(): void
    {
        Schema::table('team_player', function (Blueprint $table) {
            $table->dropColumn('jersey_number');
        });
    }
};