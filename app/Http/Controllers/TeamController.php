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
        $division->load('tournament.sport');

        return Inertia::render('Teams/Create', [
            'division' => $division,
            'players' => PlayerProfile::whereNotNull('user_id')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request, Division $division)
    {
        $division->load('tournament.sport');
        $teamSize = (int) $division->tournament->team_size;
        $rules = [
            'team_name' => 'nullable|string|max:255',
        ];

        if ($teamSize > 2) {
            $rules['players'] = 'required|array|size:' . $teamSize;
            $rules['players.*'] = 'required|integer|distinct|exists:player_profiles,id';
            $rules['jersey_numbers'] = 'required|array|size:' . $teamSize;
            $rules['jersey_numbers.*'] = 'required|integer|min:0|max:99';
        } else {
            $rules['player_one_id'] = 'required|exists:player_profiles,id';
            $rules['player_two_id'] = 'nullable|different:player_one_id|exists:player_profiles,id';
            $rules['player_one_jersey_number'] = 'required|integer|min:0|max:99';
            $rules['player_two_jersey_number'] = 'nullable|required_with:player_two_id|integer|min:0|max:99';
        }

        $validated = $request->validate($rules);
        $playerIds = $teamSize > 2
            ? $validated['players']
            : array_values(array_filter([
                $validated['player_one_id'],
                $validated['player_two_id'] ?? null,
            ]));
        $jerseyNumbers = $teamSize > 2
            ? $validated['jersey_numbers']
            : array_values(array_filter([
                $validated['player_one_jersey_number'],
                $validated['player_two_id'] ? $validated['player_two_jersey_number'] : null,
            ], fn ($number) => $number !== null));
        $selectedPlayers = PlayerProfile::whereIn('id', $playerIds)->get()->keyBy('id');

        $teamName = !empty($validated['team_name']) ? $validated['team_name'] : collect($playerIds)
            ->map(fn ($playerId) => $selectedPlayers->get($playerId)->name)
            ->implode('/');

        $team = TeamModel::create([
            'tournament_id' => $division->tournament_id,
            'division_id' => $division->id,
            'name' => $teamName,
        ]);

        $team->players()->attach(collect($playerIds)->mapWithKeys(
            fn ($playerId, $index) => [$playerId => ['jersey_number' => $jerseyNumbers[$index]]]
        )->all());

        return redirect()->route('tournaments.show', $division->tournament_id);
    }
}