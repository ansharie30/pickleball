<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\PlayerProfile;
use App\Models\TeamModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TeamController extends Controller
{
    private const POSITIONS = [
        'Basketball' => ['Point Guard', 'Shooting Guard', 'Small Forward', 'Power Forward', 'Center'],
        'Volleyball' => ['Setter', 'Outside Hitter', 'Opposite Hitter', 'Middle Blocker', 'Libero', 'Defensive Specialist'],
    ];

    public function create(Division $division)
    {
        $division->load('tournament.sport');

        return Inertia::render('Teams/Create', [
            'division' => $division,
            'players' => PlayerProfile::whereNotNull('user_id')->orderBy('name')->get(['id', 'name']),
            'positions' => self::POSITIONS[$division->tournament->sport->name ?? ''] ?? [],
        ]);
    }

    public function store(Request $request, Division $division)
    {
        $division->load('tournament.sport');
        $teamSize = (int) $division->tournament->team_size;
        $positions = self::POSITIONS[$division->tournament->sport->name ?? ''] ?? [];
        $rules = [
            'team_name' => 'nullable|string|max:255',
        ];

        if ($teamSize > 2) {
            $rules['players'] = 'required|array|size:' . $teamSize;
            $rules['players.*'] = 'required|integer|distinct|exists:player_profiles,id';
            $rules['jersey_numbers'] = 'required|array|size:' . $teamSize;
            $rules['jersey_numbers.*'] = 'required|integer|min:0|max:99';
            if ($positions) {
                $rules['positions'] = 'required|array|size:' . $teamSize;
                $rules['positions.*'] = ['required', 'string', Rule::in($positions)];
            }
        } else {
            $rules['player_one_id'] = 'required|exists:player_profiles,id';
            $rules['player_two_id'] = 'nullable|different:player_one_id|exists:player_profiles,id';
            $rules['player_one_jersey_number'] = 'required|integer|min:0|max:99';
            $rules['player_two_jersey_number'] = 'nullable|required_with:player_two_id|integer|min:0|max:99';
            if ($positions) {
                $rules['player_one_position'] = ['required', 'string', Rule::in($positions)];
                $rules['player_two_position'] = ['nullable', 'required_with:player_two_id', 'string', Rule::in($positions)];
            }
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
            fn ($playerId, $index) => [$playerId => [
                'jersey_number' => $jerseyNumbers[$index],
                'position' => $teamSize > 2
                    ? ($validated['positions'][$index] ?? null)
                    : ($index === 0
                        ? ($validated['player_one_position'] ?? null)
                        : ($validated['player_two_position'] ?? null)),
            ]]
        )->all());

        return redirect()->route('tournaments.show', $division->tournament_id);
    }

    public function edit(TeamModel $team)
    {
        $team->load(['players', 'division.tournament.sport']);

        $players = PlayerProfile::query()
            ->whereNotNull('user_id')
            ->orWhereIn('id', $team->players->pluck('id'))
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Teams/Edit', [
            'team' => $team,
            'players' => $players,
            'positions' => self::POSITIONS[$team->division->tournament->sport->name ?? ''] ?? [],
        ]);
    }

    public function update(Request $request, TeamModel $team)
    {
        $team->load('division.tournament');
        $teamSize = (int) $team->division->tournament->team_size;
        $positions = self::POSITIONS[$team->division->tournament->sport->name ?? ''] ?? [];
        $playerRules = ['required', 'array', 'min:1'];

        if ($teamSize > 2) {
            $playerRules[] = 'size:' . $teamSize;
        } else {
            $playerRules[] = 'max:2';
        }

        $rules = [
            'team_name' => 'nullable|string|max:255',
            'players' => $playerRules,
            'players.*.player_profile_id' => 'required|integer|distinct|exists:player_profiles,id',
            'players.*.jersey_number' => 'required|integer|min:0|max:99',
        ];
        if ($positions) {
            $rules['players.*.position'] = ['required', 'string', Rule::in($positions)];
        }

        $validated = $request->validate($rules);

        $roster = collect($validated['players']);
        $selectedPlayers = PlayerProfile::whereIn(
            'id',
            $roster->pluck('player_profile_id')
        )->get()->keyBy('id');
        $teamName = trim($validated['team_name'] ?? '') ?: $roster
            ->map(fn ($player) => $selectedPlayers->get($player['player_profile_id'])->name)
            ->implode('/');

        DB::transaction(function () use ($team, $teamName, $roster) {
            $team->update(['name' => $teamName]);
            $team->players()->sync($roster->mapWithKeys(
                fn ($player) => [
                    $player['player_profile_id'] => [
                        'jersey_number' => $player['jersey_number'],
                        'position' => $player['position'] ?? null,
                    ],
                ]
            )->all());
        });

        return redirect()->route('tournaments.show', $team->tournament_id);
    }
}