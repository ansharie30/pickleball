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
            $table->foreignId('sport_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->unsignedInteger('target_score')->nullable()->after('sport_id');
            $table->unsignedTinyInteger('win_by_margin')->nullable()->after('target_score');
            $table->unsignedTinyInteger('best_of')->nullable()->after('win_by_margin');
            $table->unsignedTinyInteger('team_size')->nullable()->after('best_of');
        });
    }

    public function down(): void
    {
        Schema::table('tournaments', function (Blueprint $table) {
            $table->dropForeign(['sport_id']);
            $table->dropColumn(['sport_id', 'target_score', 'win_by_margin', 'best_of', 'team_size']);
        });
    }
};
