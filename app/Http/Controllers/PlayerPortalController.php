<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class PlayerPortalController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $playerProfile = $user->playerProfile;

        if (!$playerProfile) {
            return Inertia::render('Player/Portal', [
                'teams' => [],
                'matches' => [],
                'standings' => [],
            ]);
        }

        $teams = $playerProfile->teams()->with(['division.tournament'])->get();

        $matches = collect();
        foreach ($teams as $team) {
            $teamMatches = \App\Models\MatchModel::with(['teamA', 'teamB', 'tournament', 'court'])
                ->where(function ($q) use ($team) {
                    $q->where('team_a_id', $team->id)->orWhere('team_b_id', $team->id);
                })
                ->get();

            foreach ($teamMatches as $match) {
                $matches->push($match);
            }
        }
        $matches = $matches->unique('id')->sortBy([
            fn ($m) => $m->status === 'in_progress' ? 0 : ($m->status === 'scheduled' ? 1 : 2),
        ])->values();

        // Standings per division this player's teams are in
        $standings = [];
        foreach ($teams as $team) {
            $division = $team->division;
            if (!$division) {
                continue;
            }

            $division->load('teams.matchesAsTeamA', 'teams.matchesAsTeamB');

            $divisionStandings = $division->teams->map(function ($t) {
                $tMatches = $t->matchesAsTeamA->concat($t->matchesAsTeamB)->where('status', 'completed');
                $wins = $tMatches->where('winner_team_id', $t->id)->count();
                return [
                    'team_id' => $t->id,
                    'team_name' => $t->name,
                    'wins' => $wins,
                    'losses' => $tMatches->count() - $wins,
                ];
            })->sortByDesc('wins')->values();

            $myRank = $divisionStandings->search(fn ($row) => $row['team_id'] === $team->id) + 1;

            $standings[] = [
                'division_name' => $division->name,
                'tournament_name' => $division->tournament->name ?? '',
                'my_team' => $team->name,
                'rank' => $myRank,
                'total_teams' => $divisionStandings->count(),
                'standings' => $divisionStandings,
            ];
        }

        return Inertia::render('Player/Portal', [
            'teams' => $teams,
            'matches' => $matches,
            'standings' => $standings,
        ]);
    }
}