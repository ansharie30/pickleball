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
        $player->load(
            'teams.division.tournament',
            'teams.matchesAsTeamA.teamA',
            'teams.matchesAsTeamA.teamB',
            'teams.matchesAsTeamA.tournament',
            'teams.matchesAsTeamB.teamA',
            'teams.matchesAsTeamB.teamB',
            'teams.matchesAsTeamB.tournament'
        );

        $playerData = [
            'id' => $player->id,
            'name' => $player->name,
            'rating' => $player->rating,
            'teams' => $player->teams->map(function ($team) {
                return [
                    'id' => $team->id,
                    'name' => $team->name,
                    'division' => $team->division ? [
                        'id' => $team->division->id,
                        'name' => $team->division->name,
                        'tournament' => $team->division->tournament ? [
                            'id' => $team->division->tournament->id,
                            'name' => $team->division->tournament->name,
                        ] : null,
                    ] : null,
                ];
            })->values()->all(),
        ];

        $playerTeamIds = $player->teams->pluck('id');

        $tournaments = $player->teams
            ->map(fn ($team) => $team->division?->tournament)
            ->filter()
            ->unique('id')
            ->values()
            ->map(function ($tournament) use ($player, $playerTeamIds) {
                $matches = $player->teams
                    ->flatMap(fn ($team) => $team->matchesAsTeamA->concat($team->matchesAsTeamB))
                    ->unique('id')
                    ->where('tournament_id', $tournament->id)
                    ->values();

                $startDate = $tournament->start_date;
                if ($startDate instanceof \DateTimeInterface) {
                    $startDate = $startDate->format('Y-m-d');
                } elseif (is_array($startDate)) {
                    $startDate = null;
                }

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

                return [
                    'id' => $tournament->id,
                    'name' => $tournament->name,
                    'format' => $tournament->format,
                    'status' => $tournament->status,
                    'start_date' => $startDate,
                    'matches' => [
                        'completed' => $matches->where('status', 'completed')->values()->map($serializeMatch)->all(),
                        'in_progress' => $matches->where('status', 'in_progress')->values()->map($serializeMatch)->all(),
                        'scheduled' => $matches->where('status', 'scheduled')->values()->map($serializeMatch)->all(),
                    ],
                ];
            })
            ->values();

        $matches = $player->teams->flatMap(function ($team) {
            return $team->matchesAsTeamA->concat($team->matchesAsTeamB);
        })->unique('id')->where('status', 'completed')->values();

        $standings = [];
        foreach ($player->teams as $team) {
            $division = $team->division;
            if (!$division) {
                continue;
            }

            $division->load('teams.matchesAsTeamA', 'teams.matchesAsTeamB');

            $divisionStandings = $division->teams->map(function ($divisionTeam) {
                $teamMatches = $divisionTeam->matchesAsTeamA->concat($divisionTeam->matchesAsTeamB)
                    ->where('status', 'completed');

                $wins = $teamMatches->where('winner_team_id', $divisionTeam->id)->count();

                return [
                    'team_id' => $divisionTeam->id,
                    'team_name' => $divisionTeam->name,
                    'wins' => $wins,
                    'losses' => $teamMatches->count() - $wins,
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

        $wins = 0;
        $losses = 0;

        foreach ($player->teams as $team) {
            $teamMatches = $team->matchesAsTeamA->concat($team->matchesAsTeamB)
                ->where('status', 'completed');

            $wins += $teamMatches->where('winner_team_id', $team->id)->count();
            $losses += $teamMatches->count() - $teamMatches->where('winner_team_id', $team->id)->count();
        }

        return Inertia::render('Players/Show', [
            'player' => $playerData,
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