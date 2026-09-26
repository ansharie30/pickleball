<?php

namespace Database\Seeders;

use App\Models\Sport;
use Illuminate\Database\Seeder;

class SportsSeeder extends Seeder
{
    public function run(): void
    {
        Sport::updateOrCreate(['name' => 'Pickleball'], ['scoring_type' => 'point_based']);
        Sport::updateOrCreate(['name' => 'Badminton'], ['scoring_type' => 'point_based']);
        Sport::updateOrCreate(['name' => 'Volleyball'], ['scoring_type' => 'set_based']);
        Sport::updateOrCreate(['name' => 'Basketball'], ['scoring_type' => 'timed_period']);
        Sport::updateOrCreate(['name' => 'Chess'], ['scoring_type' => 'result_based']);
    }
}