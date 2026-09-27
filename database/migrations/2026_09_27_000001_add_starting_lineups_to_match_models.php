<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('match_models', function (Blueprint $table) {
            $table->json('starting_lineups')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('match_models', function (Blueprint $table) {
            $table->dropColumn('starting_lineups');
        });
    }
};
