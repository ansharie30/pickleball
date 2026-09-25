<?php

namespace Database\Seeders;

use App\Models\Tournament;
use App\Models\Division;
use App\Models\TeamModel;
use Illuminate\Database\Seeder;

class TournamentSeeder extends Seeder
{
    public function run(): void
    {
        $tournament = Tournament::factory()->create();

        $divisions = Division::factory(3)->create([
            'tournament_id' => $tournament->id,
        ]);

        foreach ($divisions as $division) {
            TeamModel::factory(4)->create([
                'tournament_id' => $tournament->id,
                'division_id' => $division->id,
            ]);
        }
    }
}