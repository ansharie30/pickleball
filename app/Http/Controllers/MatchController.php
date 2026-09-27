<?php

namespace App\Http\Controllers;

use App\Models\MatchModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class MatchController extends Controller
{
    public function show(MatchModel $match)
    {
        $match->load(['teamA.players', 'teamB.players', 'games', 'tournament.sport', 'playerStats']);

        return Inertia::render('Matches/Show', [
            'match' => $match,
            'canManageLineups' => request()->user()?->isAdmin() ?? false,
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

        $game = $this->currentGameFor($match);
        $column = $validated['team'] === 'a' ? 'team_a_score' : 'team_b_score';
        $delta = $validated['action'] === 'increment' ? 1 : -1;

        $this->applyScoreDelta($match, $game, $column, $delta);

        broadcast(new \App\Events\ScoreUpdated($match->fresh(['games', 'playerStats'])))->toOthers();

        return back();
    }

    private function checkMatchCompletion(MatchModel $match, int $bestOf): void
    {
        $games = $match->games()->get();
        $gamesNeededToWin = (int) ceil($bestOf / 2);

        $teamAWins = $games->where('winner_team_id', $match->team_a_id)->count();
        $teamBWins = $games->where('winner_team_id', $match->team_b_id)->count();

        if ($teamAWins === $gamesNeededToWin || $teamBWins === $gamesNeededToWin) {
            $match->update([
                'status' => 'completed',
                'winner_team_id' => $teamAWins === $gamesNeededToWin ? $match->team_a_id : $match->team_b_id,
            ]);

            if ($match->tournament && in_array($match->tournament->format, ['single_elimination', 'double_elimination'])) {
                $this->advanceWinner($match, $match->winner_team_id);
            }
        } else {
            $match->games()->create([
                'game_number' => $games->count() + 1,
            ]);
        }
    }

    public function start(MatchModel $match)
    {
        if ($match->status !== 'scheduled') {
            return back()->withErrors(['match' => 'Only scheduled matches can be started.']);
        }

        $match->update(['status' => 'in_progress']);

        return back();
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
        $match->load(['teamA.players', 'teamB.players', 'games', 'playerStats', 'tournament.sport']);

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

    public function updateSchedule(Request $request, MatchModel $match)
    {
        $validated = $request->validate([
            'scheduled_at' => 'nullable|date',
        ]);

        $match->update($validated);

        return back();
    }

    public function substitutePlayers(Request $request, MatchModel $match)
    {
        $match->loadMissing('tournament.sport');

        $lineupSize = match ($match->tournament?->sport?->name) {
            'Basketball' => 5,
            'Volleyball' => 6,
            default => abort(422, 'Substitutions are only available for basketball and volleyball.'),
        };

        if ($match->status === 'completed') {
            throw ValidationException::withMessages(['match' => 'Completed matches cannot be substituted.']);
        }

        $validated = $request->validate([
            'team' => 'required|in:a,b',
            'starter_ids' => 'required|array',
            'starter_ids.*' => 'required|integer|distinct',
        ]);

        $team = $validated['team'] === 'a' ? $match->teamA() : $match->teamB();
        $teamPlayerIds = $team->with('players')->firstOrFail()->players->modelKeys();
        $starterIds = array_map('intval', $validated['starter_ids']);
        $expectedCount = min($lineupSize, count($teamPlayerIds));

        if (count($starterIds) !== $expectedCount || array_diff($starterIds, $teamPlayerIds)) {
            throw ValidationException::withMessages([
                'starter_ids' => 'Select the correct number of unique players registered on this team.',
            ]);
        }

        $updatedMatch = DB::transaction(function () use ($match, $validated, $starterIds) {
            $lockedMatch = MatchModel::query()->lockForUpdate()->findOrFail($match->id);
            $lineups = $lockedMatch->starting_lineups ?? [];
            $lineups[$validated['team']] = $starterIds;
            $lockedMatch->update(['starting_lineups' => $lineups]);

            return $lockedMatch->fresh(['teamA.players', 'teamB.players', 'games', 'playerStats', 'tournament.sport']);
        });

        broadcast(new \App\Events\ScoreUpdated($updatedMatch))->toOthers();

        return back();
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

    public function basketballScore(Request $request, MatchModel $match)
    {
        $validated = $request->validate([
            'team' => 'required|in:a,b',
            'points' => 'required|integer|in:1,2,3,-1',
        ]);

        $game = $match->games()->latest('game_number')->first();
        if (!$game) {
            $game = $match->games()->create(['game_number' => 1]);
        }

        $column = $validated['team'] === 'a' ? 'team_a_score' : 'team_b_score';
        $game->increment($column, $validated['points']);

        broadcast(new \App\Events\ScoreUpdated($match->fresh(['games'])))->toOthers();

        return back();
    }

    public function startTimer(MatchModel $match)
    {
        $tournament = $match->tournament;

        if (!$match->current_period) {
            $match->current_period = 1;
            $match->period_seconds_remaining = (int) (($tournament->period_minutes ?? 10) * 60);
            $match->status = 'in_progress';
            $match->games()->firstOrCreate(['game_number' => 1]);
        }

        $match->timer_running = true;
        $match->timer_started_at = now();
        $match->save();

        broadcast(new \App\Events\ScoreUpdated($match->fresh(['games'])))->toOthers();

        return back();
    }

    public function pauseTimer(MatchModel $match)
    {
        $tournament = $match->tournament;
        $periodTotalSeconds = (int) (($tournament->period_minutes ?? 10) * 60);

        if ($match->timer_running && $match->timer_started_at) {
            $elapsed = now()->getTimestamp() - $match->timer_started_at->getTimestamp();
            $elapsed = max(0, $elapsed);
            $newRemaining = (int) $match->period_seconds_remaining - $elapsed;
            $match->period_seconds_remaining = max(0, min($periodTotalSeconds, $newRemaining));
        }

        $match->timer_running = false;
        $match->timer_started_at = null;
        $match->save();

        broadcast(new \App\Events\ScoreUpdated($match->fresh(['games'])))->toOthers();

        return back();
    }

    public function nextPeriod(MatchModel $match)
    {
        $tournament = $match->tournament;
        $totalPeriods = $tournament->periods ?? 4;

        $match->current_period = ($match->current_period ?? 1) + 1;
        $match->period_seconds_remaining = (int) (($tournament->period_minutes ?? 10) * 60);
        $match->timer_running = false;
        $match->timer_started_at = null;

        if ($match->current_period > $totalPeriods) {
            $game = $match->games()->latest('game_number')->first();
            $winnerTeamId = $game->team_a_score > $game->team_b_score ? $match->team_a_id : $match->team_b_id;
            $game->update(['winner_team_id' => $winnerTeamId]);
            $match->status = 'completed';
            $match->winner_team_id = $winnerTeamId;
            $match->current_period = $totalPeriods;

            if (in_array($tournament->format, ['single_elimination', 'double_elimination'])) {
                $this->advanceWinner($match, $winnerTeamId);
            }
        }

        $match->save();

        broadcast(new \App\Events\ScoreUpdated($match->fresh(['games'])))->toOthers();

        return back();
    }

    public function chessResult(Request $request, MatchModel $match)
    {
        if ($match->status === 'completed') {
            return back()->withErrors(['match' => 'This match is already completed.']);
        }

        $validated = $request->validate([
            'result' => 'required|in:a,b,draw',
        ]);

        $currentGame = $match->games()->latest('game_number')->first();
        if (!$currentGame) {
            $currentGame = $match->games()->create(['game_number' => 1]);
        }

        if ($validated['result'] === 'draw') {
            $currentGame->update(['is_draw' => true]);
        } else {
            $winnerId = $validated['result'] === 'a' ? $match->team_a_id : $match->team_b_id;
            $currentGame->update(['winner_team_id' => $winnerId]);
        }

        $tournament = $match->tournament;
        $bestOf = $tournament->best_of ?? 3;
        $gamesNeededToWin = (int) ceil($bestOf / 2);

        $games = $match->games()->get();
        $teamAWins = $games->where('winner_team_id', $match->team_a_id)->count();
        $teamBWins = $games->where('winner_team_id', $match->team_b_id)->count();

        if ($teamAWins >= $gamesNeededToWin || $teamBWins >= $gamesNeededToWin) {
            $winnerId = $teamAWins > $teamBWins ? $match->team_a_id : $match->team_b_id;
            $match->update(['status' => 'completed', 'winner_team_id' => $winnerId]);

            if (in_array($tournament->format, ['single_elimination', 'double_elimination'])) {
                $this->advanceWinner($match, $winnerId);
            }
        } elseif ($games->count() >= $bestOf) {
            // All games played, no majority (can happen with draws) — match ends as a draw
            $match->update(['status' => 'completed']);
        } else {
            $match->games()->create(['game_number' => $games->count() + 1]);
        }

        broadcast(new \App\Events\ScoreUpdated($match->fresh(['games'])))->toOthers();

        return back();
    }

    public function playerScore(Request $request, MatchModel $match)
    {
        if ($match->status === 'completed') {
            return back()->withErrors(['match' => 'This match is already completed.']);
        }

        $validated = $request->validate([
            'player_id' => 'required|exists:player_profiles,id',
            'team' => 'required|in:a,b',
            'action' => 'required|in:increment,decrement',
        ]);

        $teamId = $validated['team'] === 'a' ? $match->team_a_id : $match->team_b_id;
        $stat = $this->getOrCreateStat($match, $validated['player_id'], $teamId);

        $game = $this->currentGameFor($match);
        $column = $validated['team'] === 'a' ? 'team_a_score' : 'team_b_score';

        if ($validated['action'] === 'increment') {
            $stat->increment('points');
            $this->applyScoreDelta($match, $game, $column, 1);
        } else {
            if ($stat->points > 0) {
                $stat->decrement('points');
            }
            $this->applyScoreDelta($match, $game, $column, -1);
        }

        broadcast(new \App\Events\ScoreUpdated($match->fresh(['games', 'playerStats'])))->toOthers();

        return back();
    }

    public function basketballPlayerScore(Request $request, MatchModel $match)
    {
        if ($match->status === 'completed') {
            return back()->withErrors(['match' => 'This match is already completed.']);
        }

        $validated = $request->validate([
            'player_id' => 'required|exists:player_profiles,id',
            'team' => 'required|in:a,b',
            'points' => 'required|integer|in:1,2,3,-1',
        ]);

        $teamId = $validated['team'] === 'a' ? $match->team_a_id : $match->team_b_id;
        $stat = $this->getOrCreateStat($match, $validated['player_id'], $teamId);
        $points = $validated['points'];

        if ($points > 0) {
            $stat->increment('points', $points);
        } else {
            $stat->update(['points' => max(0, $stat->points + $points)]);
        }

        $game = $this->currentGameFor($match);
        $column = $validated['team'] === 'a' ? 'team_a_score' : 'team_b_score';
        $game->increment($column, $points);

        broadcast(new \App\Events\ScoreUpdated($match->fresh(['games', 'playerStats'])))->toOthers();

        return back();
    }

    public function basketballStat(Request $request, MatchModel $match)
    {
        $validated = $request->validate([
            'player_id' => 'required|exists:player_profiles,id',
            'team' => 'required|in:a,b',
            'stat' => 'required|in:assists,rebounds',
            'action' => 'required|in:increment,decrement',
        ]);

        $teamId = $validated['team'] === 'a' ? $match->team_a_id : $match->team_b_id;
        $stat = $this->getOrCreateStat($match, $validated['player_id'], $teamId);

        if ($validated['action'] === 'increment') {
            $stat->increment($validated['stat']);
        } elseif ($stat->{$validated['stat']} > 0) {
            $stat->decrement($validated['stat']);
        }

        broadcast(new \App\Events\ScoreUpdated($match->fresh(['games', 'playerStats'])))->toOthers();

        return back();
    }

    private function getOrCreateStat(MatchModel $match, int $playerId, int $teamId): \App\Models\PlayerMatchStat
    {
        return \App\Models\PlayerMatchStat::firstOrCreate(
            ['match_model_id' => $match->id, 'player_profile_id' => $playerId],
            ['team_id' => $teamId]
        );
    }

    private function currentGameFor(MatchModel $match)
    {
        $game = $match->games()->latest('game_number')->first();

        if (!$game) {
            $game = $match->games()->create(['game_number' => 1]);
        }

        return $game;
    }

    private function applyScoreDelta(MatchModel $match, $game, string $column, int $delta): void
    {
        if ($delta >= 0) {
            $game->increment($column, $delta);
        } else {
            $game->decrement($column, abs($delta));
        }

        $game->refresh();

        $tournament = $match->tournament;
        $winningScore = $tournament->target_score ?? 11;
        $winMargin = $tournament->win_by_margin ?? 2;
        $bestOf = $tournament->best_of ?? 3;

        $a = $game->team_a_score;
        $b = $game->team_b_score;

        if (($a >= $winningScore || $b >= $winningScore) && abs($a - $b) >= $winMargin) {
            $winnerTeamId = $a > $b ? $match->team_a_id : $match->team_b_id;
            $game->update(['winner_team_id' => $winnerTeamId]);
            $this->checkMatchCompletion($match, $bestOf);
        }
    }
}