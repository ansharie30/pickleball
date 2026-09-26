<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Division;
use App\Services\MatchGenerator;
use App\Models\MatchModel;

class TournamentController extends Controller
{
    public function index()
    {
        $tournaments = Tournament::with('venue')
            ->latest()
            ->get();

        return Inertia::render('Tournaments/Index', [
            'tournaments' => $tournaments,
        ]);
    }

    public function show(Tournament $tournament)
    {
        $tournament->load([
            'divisions.teams',
            'venue',
            'matches.teamA',
            'matches.teamB',
            'matches.court',
        ]);

        return Inertia::render('Tournaments/Show', [
            'tournament' => $tournament,
            'courts' => \App\Models\Court::all(['id', 'name', 'status']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Tournaments/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'format' => 'required|in:single_elimination,double_elimination,round_robin,pool_play',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $tournament = Tournament::create($validated);

        return redirect()->route('tournaments.show', $tournament);
    }

    public function generateMatches(Division $division, MatchGenerator $generator)
    {
        $alreadyGenerated = \App\Models\MatchModel::where('tournament_id', $division->tournament_id)
            ->whereIn('team_a_id', $division->teams->pluck('id'))
            ->exists();

        if ($alreadyGenerated) {
            return redirect()->back()->withErrors(['division' => 'Matches have already been generated for this division.']);
        }

        $format = $division->tournament->format;

        if ($format === 'round_robin') {
            $generator->generateRoundRobin($division);
        } elseif (in_array($format, ['single_elimination', 'double_elimination'])) {
            $generator->generateSingleElimination($division);
        }

        return redirect()->back()->with('success', 'Matches generated.');
    }

    public function standings(Division $division)
    {
        $division->load('teams.matchesAsTeamA', 'teams.matchesAsTeamB');

        $standings = $division->teams->map(function ($team) {
            $matches = $team->matchesAsTeamA->concat($team->matchesAsTeamB)
                ->where('status', 'completed');

            $wins = $matches->where('winner_team_id', $team->id)->count();
            $losses = $matches->count() - $wins;

            return [
                'team_id' => $team->id,
                'team_name' => $team->name,
                'wins' => $wins,
                'losses' => $losses,
                'matches_played' => $matches->count(),
            ];
        })->sortByDesc('wins')->values();

        return Inertia::render('Divisions/Standings', [
            'division' => $division,
            'standings' => $standings,
        ]);
    }

    public function createDivision(Tournament $tournament)
    {
        return Inertia::render('Divisions/Create', [
            'tournament' => $tournament,
        ]);
    }

    public function storeDivision(Request $request, Tournament $tournament)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'skill_level' => 'nullable|string|max:50',
        ]);

        $tournament->divisions()->create($validated);

        return redirect()->route('tournaments.show', $tournament);
    }

    public function bracket(Division $division)
    {
        $matches = \App\Models\MatchModel::with(['teamA', 'teamB'])
            ->where('division_id', $division->id)
            ->get();

        $rounds = $matches->groupBy('round');

        return Inertia::render('Tournaments/Bracket', [
            'division' => $division,
            'rounds' => $rounds,
        ]);
    }

    public function divisionMatches(Division $division)
    {
        $division->load('tournament');

        $matches = \App\Models\MatchModel::with(['teamA', 'teamB', 'court'])
            ->where('division_id', $division->id)
            ->orderBy('round')
            ->get();

        return Inertia::render('Divisions/Matches', [
            'division' => $division,
            'matches' => $matches,
        ]);
    }
    
}