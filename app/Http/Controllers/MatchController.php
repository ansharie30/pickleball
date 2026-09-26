<?php

namespace App\Http\Controllers;

use App\Models\MatchModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MatchController extends Controller
{
    public function show(MatchModel $match)
    {
        $match->load(['teamA.players', 'teamB.players', 'games']);

        return Inertia::render('Matches/Show', [
            'match' => $match,
        ]);
    }

    public function updateScore(Request $request, MatchModel $match)
    {
        if ($match->status !== 'in_progress') {
            return back()->withErrors(['match' => 'This match has not started or is already completed.']);
        }

        $validated = $request->validate([
            'team' => 'required|in:a,b',
            'action' => 'required|in:increment,decrement',
        ]);

        $game = $match->games()->latest('game_number')->first();

        if (!$game) {
            $game = $match->games()->create(['game_number' => 1]);
        }

        $column = $validated['team'] === 'a' ? 'team_a_score' : 'team_b_score';

        if ($validated['action'] === 'increment') {
            $game->increment($column);
        } else {
            $game->decrement($column);
        }

        $game->refresh();

        // Win condition: first to 11, win by 2
        $winningScore = 11;
        $a = $game->team_a_score;
        $b = $game->team_b_score;

        if (($a >= $winningScore || $b >= $winningScore) && abs($a - $b) >= 2) {
            $winnerTeamId = $a > $b ? $match->team_a_id : $match->team_b_id;
            $game->update(['winner_team_id' => $winnerTeamId]);

            $this->checkMatchCompletion($match);
        }

        broadcast(new \App\Events\ScoreUpdated($match->fresh(['games'])))->toOthers();

        return back();
    }

    public function start(MatchModel $match)
    {
        if ($match->status !== 'scheduled') {
            return back()->withErrors(['match' => 'Only scheduled matches can be started.']);
        }

        $match->update(['status' => 'in_progress']);

        return back();
    }

    private function checkMatchCompletion(MatchModel $match): void
    {
        $games = $match->games()->get();

        $teamAWins = $games->where('winner_team_id', $match->team_a_id)->count();
        $teamBWins = $games->where('winner_team_id', $match->team_b_id)->count();

        if ($teamAWins === 2 || $teamBWins === 2) {
            $winnerId = $teamAWins === 2 ? $match->team_a_id : $match->team_b_id;

            $match->update([
                'status' => 'completed',
                'winner_team_id' => $winnerId,
            ]);

            // Only advance brackets for elimination formats (round_robin has no "next round")
            if ($match->tournament && in_array($match->tournament->format, ['single_elimination', 'double_elimination'])) {
                $this->advanceWinner($match, $winnerId);
            }
        } else {
            $match->games()->create([
                'game_number' => $games->count() + 1,
            ]);
        }
    }

    private function advanceWinner(MatchModel $match, int $winnerId): void
    {
        $sameRoundMatches = MatchModel::where('tournament_id', $match->tournament_id)
            ->where('division_id', $match->division_id)
            ->where('round', $match->round)
            ->get();

        $allCompleted = $sameRoundMatches->every(fn ($m) => $m->status === 'completed');

        if (!$allCompleted) {
            return;
        }

        $winners = $sameRoundMatches->pluck('winner_team_id')->values();

        if ($winners->count() < 2) {
            return;
        }

        // How many matches will the NEXT round have?
        $nextRoundMatchCount = intdiv($winners->count(), 2);

        // Total rounds remaining after this one, based on how many matches are left.
        // If next round has 1 match, that's the Final. If 2 matches, that's Semifinal, etc.
        $nextRoundName = $this->roundNameForMatchCount($nextRoundMatchCount);

        for ($i = 0; $i < $winners->count(); $i += 2) {
            $teamA = $winners[$i] ?? null;
            $teamB = $winners[$i + 1] ?? null;

            if ($teamA && $teamB) {
                MatchModel::create([
                    'tournament_id' => $match->tournament_id,
                    'division_id' => $match->division_id,
                    'team_a_id' => $teamA,
                    'team_b_id' => $teamB,
                    'round' => $nextRoundName,
                    'status' => 'scheduled',
                ]);
            }
        }
    }

    /**
     * Given how many matches exist in a round, return its name.
     * 1 match = Final, 2 matches = Semifinal, 4 matches = Quarterfinal, etc.
     */
    private function roundNameForMatchCount(int $matchCount): string
    {
        return match ($matchCount) {
            1 => 'Final',
            2 => 'Semifinal',
            4 => 'Quarterfinal',
            default => $matchCount * 2 . '-Team Round',
        };
    }

    public function publicShow(MatchModel $match)
    {
        $match->load(['teamA', 'teamB', 'games']);

        return Inertia::render('Matches/PublicShow', [
            'match' => $match,
        ]);
    }

    public function assignCourt(Request $request, \App\Models\MatchModel $match)
    {
        $validated = $request->validate([
            'court_id' => 'required|exists:courts,id',
        ]);

        $match->update(['court_id' => $validated['court_id']]);

        \App\Models\Court::find($validated['court_id'])->update(['status' => 'in_use']);

        return back();
    }

    public function edit(MatchModel $match)
    {
        $match->load(['teamA', 'teamB', 'division.teams']);

        return Inertia::render('Matches/Edit', [
            'match' => $match,
            'availableTeams' => $match->division ? $match->division->teams : [],
        ]);
    }

    public function update(Request $request, MatchModel $match)
    {
        $validated = $request->validate([
            'team_a_id' => 'required|exists:teams,id',
            'team_b_id' => 'required|different:team_a_id|exists:teams,id',
            'scheduled_at' => 'nullable|date',
            'status' => 'required|in:scheduled,in_progress,completed',
        ]);

        $match->update($validated);

        return redirect()->route('matches.show', $match)->with('success', 'Match updated.');
    }

    public function destroy(MatchModel $match)
    {
        $match->games()->delete();
        $match->delete();

        return redirect()->back()->with('success', 'Match deleted.');
    }

    public function publicIndex()
    {
        $matches = MatchModel::with(['teamA', 'teamB', 'court', 'tournament'])
            ->orderByRaw("CASE status WHEN 'in_progress' THEN 0 WHEN 'scheduled' THEN 1 WHEN 'completed' THEN 2 END")
            ->latest('updated_at')
            ->get();

        return Inertia::render('Matches/PublicIndex', [
            'matches' => $matches,
        ]);
    }
}