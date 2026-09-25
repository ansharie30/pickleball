<?php

namespace App\Services;

use App\Models\Division;
use App\Models\MatchModel;

class MatchGenerator
{
    public function generateRoundRobin(Division $division): void
    {
        $teams = $division->teams;

        for ($i = 0; $i < $teams->count(); $i++) {
            for ($j = $i + 1; $j < $teams->count(); $j++) {
                MatchModel::create([
                    'tournament_id' => $division->tournament_id,
                    'division_id' => $division->id,
                    'team_a_id' => $teams[$i]->id,
                    'team_b_id' => $teams[$j]->id,
                    'round' => 'Round Robin',
                    'status' => 'scheduled',
                ]);
            }
        }
    }

    public function generateSingleElimination(Division $division): void
    {
        $teams = $division->teams->shuffle()->values();
        $teamCount = $teams->count();

        if ($teamCount < 2) {
            return;
        }

        $bracketSize = 2 ** ceil(log($teamCount, 2));
        $byeCount = $bracketSize - $teamCount;
        $totalRounds = (int) log($bracketSize, 2);

        $roundName = $this->roundNameForSize($totalRounds, 1);

        $i = 0;
        while ($i < $teams->count()) {
            if ($byeCount > 0) {
                $byeCount--;
                $i++;
                continue;
            }

            $teamA = $teams[$i] ?? null;
            $teamB = $teams[$i + 1] ?? null;

            if ($teamA && $teamB) {
                MatchModel::create([
                    'tournament_id' => $division->tournament_id,
                    'division_id' => $division->id,
                    'team_a_id' => $teamA->id,
                    'team_b_id' => $teamB->id,
                    'round' => $roundName,
                    'status' => 'scheduled',
                ]);
            }

            $i += 2;
        }
    }

    /**
     * Given the total number of rounds in a bracket, and the current round number
     * (1-indexed, counting from the first round), return the correct round label.
     * Example: totalRounds=2 -> Round 1 is "Semifinal", Round 2 is "Final".
     * Example: totalRounds=1 -> Round 1 is "Final".
     * Example: totalRounds=3 -> Round 1 "Quarterfinal", Round 2 "Semifinal", Round 3 "Final".
     */
    public function roundNameForSize(int $totalRounds, int $currentRound): string
    {
        $roundsFromEnd = $totalRounds - $currentRound; // 0 = final round

        return match ($roundsFromEnd) {
            0 => 'Final',
            1 => 'Semifinal',
            2 => 'Quarterfinal',
            default => 'Round ' . $currentRound,
        };
    }
}