<?php

namespace Tests\Feature;

use App\Models\Division;
use App\Models\MatchModel;
use App\Models\TeamModel;
use App\Models\Tournament;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MatchScheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_set_and_clear_a_nullable_match_schedule(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $tournament = Tournament::factory()->create();
        $division = Division::factory()->create(['tournament_id' => $tournament->id]);
        $teamA = TeamModel::factory()->create([
            'tournament_id' => $tournament->id,
            'division_id' => $division->id,
        ]);
        $teamB = TeamModel::factory()->create([
            'tournament_id' => $tournament->id,
            'division_id' => $division->id,
        ]);
        $match = MatchModel::create([
            'tournament_id' => $tournament->id,
            'division_id' => $division->id,
            'team_a_id' => $teamA->id,
            'team_b_id' => $teamB->id,
            'status' => 'scheduled',
        ]);

        $this->actingAs($admin)
            ->from(route('divisions.matches', $division))
            ->patch(route('matches.schedule', $match), [
                'scheduled_at' => '2030-01-02T15:30',
            ])
            ->assertRedirect(route('divisions.matches', $division));

        $this->assertSame('2030-01-02 15:30:00', $match->fresh()->scheduled_at->format('Y-m-d H:i:s'));

        $this->from(route('divisions.matches', $division))
            ->patch(route('matches.schedule', $match), [
                'scheduled_at' => '',
            ])
            ->assertRedirect(route('divisions.matches', $division));

        $this->assertNull($match->fresh()->scheduled_at);
    }
}
