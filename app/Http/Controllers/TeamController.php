<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\PlayerProfile;
use App\Models\TeamModel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TeamController extends Controller
{
    public function create(Division $division)
    {
        $division->load('tournament');

        return Inertia::render('Teams/Create', [
            'division' => $division,
            'players' => PlayerProfile::whereNotNull('user_id')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request, Division $division)
    {
        $validated = $request->validate([
            'team_name' => 'nullable|string|max:255',
            'player_one_id' => 'required|exists:player_profiles,id',
            'player_two_id' => 'nullable|different:player_one_id|exists:player_profiles,id',
        ]);

        $playerOne = PlayerProfile::findOrFail($validated['player_one_id']);
        $playerTwo = !empty($validated['player_two_id']) ? PlayerProfile::find($validated['player_two_id']) : null;

        $teamName = $validated['team_name']
            ?? $playerOne->name . ($playerTwo ? '/' . $playerTwo->name : '');

        $team = TeamModel::create([
            'tournament_id' => $division->tournament_id,
            'division_id' => $division->id,
            'name' => $teamName,
        ]);

        $team->players()->attach($playerOne->id);

        if ($playerTwo) {
            $team->players()->attach($playerTwo->id);
        }

        return redirect()->route('tournaments.show', $division->tournament_id);
    }
}