<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\PlayerProfile;
use App\Models\TeamModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TeamController extends Controller
{
    public function create(Division $division)
    {
        $division->load('tournament');

        return Inertia::render('Teams/Create', [
            'division' => $division,
        ]);
    }

    public function store(Request $request, Division $division)
    {
        $validated = $request->validate([
            'team_name' => 'nullable|string|max:255',
            'player_one_name' => 'required|string|max:255',
            'player_two_name' => 'nullable|string|max:255', // nullable for singles
        ]);

        $playerOne = PlayerProfile::create(['name' => $validated['player_one_name']]);

        $teamName = $validated['team_name']
            ?? $validated['player_one_name'] . (isset($validated['player_two_name']) ? '/' . $validated['player_two_name'] : '');

        $team = TeamModel::create([
            'tournament_id' => $division->tournament_id,
            'division_id' => $division->id,
            'name' => $teamName,
        ]);

        $team->players()->attach($playerOne->id);

        if (!empty($validated['player_two_name'])) {
            $playerTwo = PlayerProfile::create(['name' => $validated['player_two_name']]);
            $team->players()->attach($playerTwo->id);
        }

        return redirect()->route('tournaments.show', $division->tournament_id);
    }
}