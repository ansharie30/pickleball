<?php

namespace Tests\Feature;

use App\Models\Division;
use App\Models\PlayerProfile;
use App\Models\Sport;
use App\Models\TeamModel;
use App\Models\Tournament;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamEditTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_edit_a_team_name_players_and_jersey_numbers(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $sport = Sport::create(['name' => 'Pickleball', 'scoring_type' => 'point_based']);
        $tournament = Tournament::factory()->create([
            'sport_id' => $sport->id,
            'team_size' => 2,
        ]);
        $division = Division::factory()->create(['tournament_id' => $tournament->id]);
        $team = TeamModel::factory()->create([
            'tournament_id' => $tournament->id,
            'division_id' => $division->id,
            'name' => 'Old Team Name',
        ]);
        $oldPlayers = PlayerProfile::factory()->count(2)->create();
        $newPlayers = PlayerProfile::factory()->count(2)->create();
        $team->players()->attach([
            $oldPlayers[0]->id => ['jersey_number' => 1],
            $oldPlayers[1]->id => ['jersey_number' => 2],
        ]);

        $this->actingAs($admin)
            ->get(route('teams.edit', $team))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Teams/Edit')
                ->where('team.name', 'Old Team Name')
                ->has('team.players', 2)
            );

        $this->actingAs($admin)
            ->patch(route('teams.update', $team), [
                'team_name' => 'New Team Name',
                'players' => [
                    ['player_profile_id' => $newPlayers[0]->id, 'jersey_number' => 11],
                    ['player_profile_id' => $newPlayers[1]->id, 'jersey_number' => 22],
                ],
            ])
            ->assertRedirect(route('tournaments.show', $tournament));

        $this->assertSame('New Team Name', $team->fresh()->name);
        $this->assertEqualsCanonicalizing(
            $newPlayers->modelKeys(),
            $team->fresh()->players->modelKeys()
        );
        $this->assertSame(11, (int) $team->fresh()->players->firstWhere('id', $newPlayers[0]->id)->pivot->jersey_number);
        $this->assertSame(22, (int) $team->fresh()->players->firstWhere('id', $newPlayers[1]->id)->pivot->jersey_number);
    }
}
