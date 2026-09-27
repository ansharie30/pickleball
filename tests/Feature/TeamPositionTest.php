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

class TeamPositionTest extends TestCase
{
    use RefreshDatabase;

    public function test_basketball_registration_saves_a_position_for_every_player(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $sport = Sport::create(['name' => 'Basketball', 'scoring_type' => 'timed_period']);
        $tournament = Tournament::factory()->create([
            'sport_id' => $sport->id,
            'team_size' => 5,
        ]);
        $division = Division::factory()->create(['tournament_id' => $tournament->id]);
        $players = PlayerProfile::factory()->count(5)->create();

        $this->actingAs($admin)
            ->get(route('teams.create', $division))
            ->assertInertia(fn ($page) => $page
                ->component('Teams/Create')
                ->where('positions', ['Point Guard', 'Shooting Guard', 'Small Forward', 'Power Forward', 'Center'])
            );

        $this->actingAs($admin)
            ->post(route('teams.store', $division), [
                'team_name' => 'Court Five',
                'players' => $players->modelKeys(),
                'jersey_numbers' => [1, 2, 3, 4, 5],
                'positions' => [
                    'Point Guard',
                    'Shooting Guard',
                    'Small Forward',
                    'Power Forward',
                    'Center',
                ],
            ])
            ->assertRedirect(route('tournaments.show', $tournament));

        $team = TeamModel::where('division_id', $division->id)->firstOrFail();
        $this->assertSame(
            ['Point Guard', 'Shooting Guard', 'Small Forward', 'Power Forward', 'Center'],
            $team->players->sortBy('pivot.jersey_number')->pluck('pivot.position')->values()->all()
        );
    }

    public function test_volleyball_registration_saves_positions_and_team_edit_can_change_them(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $sport = Sport::create(['name' => 'Volleyball', 'scoring_type' => 'set_based']);
        $tournament = Tournament::factory()->create([
            'sport_id' => $sport->id,
            'team_size' => 6,
        ]);
        $division = Division::factory()->create(['tournament_id' => $tournament->id]);
        $players = PlayerProfile::factory()->count(6)->create();
        $positions = ['Setter', 'Outside Hitter', 'Opposite Hitter', 'Middle Blocker', 'Libero', 'Defensive Specialist'];

        $this->actingAs($admin)
            ->get(route('teams.create', $division))
            ->assertInertia(fn ($page) => $page
                ->component('Teams/Create')
                ->where('positions', $positions)
            );

        $this->actingAs($admin)
            ->post(route('teams.store', $division), [
                'team_name' => 'Net Six',
                'players' => $players->modelKeys(),
                'jersey_numbers' => [1, 2, 3, 4, 5, 6],
                'positions' => $positions,
            ])
            ->assertRedirect(route('tournaments.show', $tournament));

        $team = TeamModel::where('division_id', $division->id)->firstOrFail();
        $this->assertSame($positions, $team->players->sortBy('pivot.jersey_number')->pluck('pivot.position')->values()->all());

        $changedPositions = ['Libero', 'Setter', 'Outside Hitter', 'Opposite Hitter', 'Middle Blocker', 'Defensive Specialist'];
        $this->actingAs($admin)
            ->patch(route('teams.update', $team), [
                'team_name' => 'Net Six',
                'players' => $players->values()->map(fn ($player, $index) => [
                    'player_profile_id' => $player->id,
                    'jersey_number' => $index + 1,
                    'position' => $changedPositions[$index],
                ])->all(),
            ])
            ->assertRedirect(route('tournaments.show', $tournament));

        $this->assertSame($changedPositions, $team->fresh()->players->sortBy('pivot.jersey_number')->pluck('pivot.position')->values()->all());
    }
}
