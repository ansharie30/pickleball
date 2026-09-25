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
        if ($match->status === 'completed') {
            return back()->withErrors(['match' => 'This match is already completed.']);
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

        $nextRoundName = $this->nextRoundName($match->round);

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

    private function nextRoundName(string $currentRound): string
    {
        return match ($currentRound) {
            'Round 1' => 'Quarterfinal',
            'Quarterfinal' => 'Semifinal',
            'Semifinal' => 'Final',
            default => 'Next Round',
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
}