<?php

namespace App\Http\Controllers;

use App\Models\PlayerProfile;
use Inertia\Inertia;

class PlayerController extends Controller
{
    public function index()
    {   
        $players = PlayerProfile::with('teams.matchesAsTeamA', 'teams.matchesAsTeamB')->get();

        $players = $players->map(function ($player) {
            $wins = 0;
            $losses = 0;

            foreach ($player->teams as $team) {
                $teamMatches = $team->matchesAsTeamA->concat($team->matchesAsTeamB)
                    ->where('status', 'completed');

                $wins += $teamMatches->where('winner_team_id', $team->id)->count();
                $losses += $teamMatches->count() - $teamMatches->where('winner_team_id', $team->id)->count();
            }

            $matchesPlayed = $wins + $losses;

            return [
                'id' => $player->id,
                'name' => $player->name,
                'matches_played' => $matchesPlayed,
                'wins' => $wins,
                'losses' => $losses,
                'win_rate' => $matchesPlayed > 0 ? round(($wins / $matchesPlayed) * 100) : 0,
            ];
        });

        return Inertia::render('Players/Index', [
            'players' => $players,
        ]);
    }

    public function show(PlayerProfile $player)
    {
        $player->load('teams.matchesAsTeamA', 'teams.matchesAsTeamB');

        $matches = $player->teams->flatMap(function ($team) {
            return $team->matchesAsTeamA->concat($team->matchesAsTeamB);
        })->unique('id')->where('status', 'completed')->values();

        $wins = 0;
        $losses = 0;

        foreach ($player->teams as $team) {
            $teamMatches = $team->matchesAsTeamA->concat($team->matchesAsTeamB)
                ->where('status', 'completed');

            $wins += $teamMatches->where('winner_team_id', $team->id)->count();
            $losses += $teamMatches->count() - $teamMatches->where('winner_team_id', $team->id)->count();
        }

        return Inertia::render('Players/Show', [
            'player' => $player,
            'stats' => [
                'wins' => $wins,
                'losses' => $losses,
                'matches_played' => $wins + $losses,
                'win_rate' => ($wins + $losses) > 0 ? round(($wins / ($wins + $losses)) * 100) : 0,
            ],
        ]);
    }
}