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
                'player' => null,
                'teams' => [],
                'matches' => [],
                'standings' => [],
                'stats' => [
                    'wins' => 0,
                    'losses' => 0,
                    'matches_played' => 0,
                    'win_rate' => 0,
                ],
            ]);
        }

        $playerData = [
            'id' => $playerProfile->id,
            'name' => $playerProfile->name,
            'rating' => $playerProfile->rating,
        ];

        $teams = $playerProfile->teams()->with(['division.tournament'])->get();

        $wins = 0;
        $losses = 0;

        foreach ($teams as $team) {
            $teamMatches = $team->matchesAsTeamA->concat($team->matchesAsTeamB)
                ->where('status', 'completed');

            $wins += $teamMatches->where('winner_team_id', $team->id)->count();
            $losses += $teamMatches->count() - $teamMatches->where('winner_team_id', $team->id)->count();
        }

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

        $playerTeamIds = $teams->pluck('id');

        $tournaments = $teams
            ->map(fn ($team) => $team->division?->tournament)
            ->filter()
            ->unique('id')
            ->values()
            ->map(function ($tournament) use ($teams, $playerTeamIds) {
                $allMatches = collect();

                foreach ($teams as $team) {
                    $teamMatches = $team->matchesAsTeamA->concat($team->matchesAsTeamB)
                        ->where('tournament_id', $tournament->id)
                        ->values();

                    foreach ($teamMatches as $match) {
                        $allMatches->push($match);
                    }
                }

                $allMatches = $allMatches->unique('id')->values();
                $serializeMatch = function ($match) use ($playerTeamIds) {
                    $participatingTeamIds = $playerTeamIds->intersect([
                        $match->team_a_id,
                        $match->team_b_id,
                    ]);

                    return [
                        'id' => $match->id,
                        'tournament_id' => $match->tournament_id,
                        'court_id' => $match->court_id,
                        'team_a_id' => $match->team_a_id,
                        'team_b_id' => $match->team_b_id,
                        'round' => $match->round,
                        'status' => $match->status,
                        'winner_team_id' => $match->winner_team_id,
                        'result' => $match->status === 'completed' && $match->winner_team_id
                            ? ($participatingTeamIds->contains($match->winner_team_id) ? 'win' : 'loss')
                            : null,
                        'scheduled_at' => $match->scheduled_at ? $match->scheduled_at->format(DATE_ATOM) : null,
                        'team_a' => $match->teamA ? [
                            'id' => $match->teamA->id,
                            'name' => $match->teamA->name,
                        ] : null,
                        'team_b' => $match->teamB ? [
                            'id' => $match->teamB->id,
                            'name' => $match->teamB->name,
                        ] : null,
                        'court' => $match->court ? [
                            'id' => $match->court->id,
                            'name' => $match->court->name,
                        ] : null,
                    ];
                };

                $startDate = $tournament->start_date;
                if ($startDate instanceof \DateTimeInterface) {
                    $startDate = $startDate->format('Y-m-d');
                } elseif (is_array($startDate)) {
                    $startDate = null;
                }

                return [
                    'id' => $tournament->id,
                    'name' => $tournament->name,
                    'format' => $tournament->format,
                    'status' => $tournament->status,
                    'start_date' => $startDate,
                    'matches' => [
                        'completed' => $allMatches->where('status', 'completed')->values()->map($serializeMatch)->all(),
                        'in_progress' => $allMatches->where('status', 'in_progress')->values()->map($serializeMatch)->all(),
                        'scheduled' => $allMatches->where('status', 'scheduled')->values()->map($serializeMatch)->all(),
                    ],
                ];
            })
            ->values();

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
            'player' => $playerData,
            'teams' => $teams,
            'matches' => $matches,
            'tournaments' => $tournaments,
            'standings' => $standings,
            'stats' => [
                'wins' => $wins,
                'losses' => $losses,
                'matches_played' => $wins + $losses,
                'win_rate' => ($wins + $losses) > 0 ? round(($wins / ($wins + $losses)) * 100) : 0,
            ],
        ]);
    }
}