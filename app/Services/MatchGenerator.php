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
        $teams = $division->teams->shuffle()->values(); // random seeding
        $teamCount = $teams->count();

        if ($teamCount < 2) {
            return;
        }

        // Find next power of 2 to determine byes
        $bracketSize = 2 ** ceil(log($teamCount, 2));
        $byeCount = $bracketSize - $teamCount;

        // Give byes to the first N teams (simple approach — no seeding logic yet)
        $round1Teams = $teams->values();
        $matchesRound1 = [];

        $i = 0;
        while ($i < $round1Teams->count()) {
            if ($byeCount > 0) {
                // This team advances automatically — no match created yet
                $byeCount--;
                $i++;
                continue;
            }

            $teamA = $round1Teams[$i] ?? null;
            $teamB = $round1Teams[$i + 1] ?? null;

            if ($teamA && $teamB) {
                MatchModel::create([
                    'tournament_id' => $division->tournament_id,
                    'division_id' => $division->id,
                    'team_a_id' => $teamA->id,
                    'team_b_id' => $teamB->id,
                    'round' => 'Round 1',
                    'status' => 'scheduled',
                ]);
            }

            $i += 2;
        }
    }
}