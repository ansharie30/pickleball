<?php

namespace App\Http\Controllers;

use App\Models\Court;
use App\Models\MatchModel;
use App\Models\TeamModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class QuickMatchController extends Controller
{
    public function create(Court $court)
    {
        return Inertia::render('QuickMatch/Create', [
            'court' => $court,
        ]);
    }

    public function store(Request $request, Court $court)
    {
        $validated = $request->validate([
            'team_a_name' => 'required|string|max:255',
            'team_b_name' => 'required|string|max:255',
        ]);

        // Casual games have no tournament — tournament_id nullable
        $teamA = TeamModel::create(['name' => $validated['team_a_name']]);
        $teamB = TeamModel::create(['name' => $validated['team_b_name']]);

        $match = MatchModel::create([
            'court_id' => $court->id,
            'team_a_id' => $teamA->id,
            'team_b_id' => $teamB->id,
            'status' => 'scheduled',
            'round' => 'Casual Game',
        ]);

        $court->update(['status' => 'in_use']);

        return redirect()->route('matches.show', $match);
    }
}