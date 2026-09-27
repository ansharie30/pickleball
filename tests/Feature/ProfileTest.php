<?php

namespace Tests\Feature;

use App\Models\Division;
use App\Models\MatchModel;
use App\Models\PlayerProfile;
use App\Models\TeamModel;
use App\Models\Tournament;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrors('password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }

    public function test_player_detail_page_includes_tournaments_and_match_statuses(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $player = PlayerProfile::factory()->create(['user_id' => $admin->id]);

        $tournament = Tournament::factory()->create(['name' => 'Spring Open']);
        $division = Division::factory()->create(['tournament_id' => $tournament->id, 'name' => 'Open Doubles']);
        $teamA = TeamModel::factory()->create([
            'tournament_id' => $tournament->id,
            'division_id' => $division->id,
            'name' => 'Makers',
        ]);
        $teamB = TeamModel::factory()->create([
            'tournament_id' => $tournament->id,
            'division_id' => $division->id,
            'name' => 'Breakers',
        ]);

        $player->teams()->attach($teamA->id);

        $completedMatch = MatchModel::create([
            'tournament_id' => $tournament->id,
            'division_id' => $division->id,
            'team_a_id' => $teamA->id,
            'team_b_id' => $teamB->id,
            'status' => 'completed',
            'winner_team_id' => $teamA->id,
            'scheduled_at' => now(),
        ]);

        $inProgressMatch = MatchModel::create([
            'tournament_id' => $tournament->id,
            'division_id' => $division->id,
            'team_a_id' => $teamA->id,
            'team_b_id' => $teamB->id,
            'status' => 'in_progress',
            'scheduled_at' => now()->addHour(),
        ]);

        $scheduledMatch = MatchModel::create([
            'tournament_id' => $tournament->id,
            'division_id' => $division->id,
            'team_a_id' => $teamA->id,
            'team_b_id' => $teamB->id,
            'status' => 'scheduled',
            'scheduled_at' => now()->addHours(2),
        ]);

        $response = $this
            ->actingAs($admin)
            ->get(route('players.show', $player));

        $response
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Players/Show')
                ->where('tournaments.0.name', 'Spring Open')
                ->where('tournaments.0.matches.completed.0.id', $completedMatch->id)
                ->where('tournaments.0.matches.completed.0.result', 'win')
                ->where('tournaments.0.matches.in_progress.0.id', $inProgressMatch->id)
                ->where('tournaments.0.matches.scheduled.0.id', $scheduledMatch->id)
            );
    }

    public function test_player_portal_includes_rating_and_match_stats(): void
    {
        $user = User::factory()->create();
        $player = PlayerProfile::factory()->create([
            'user_id' => $user->id,
            'name' => 'Player One',
            'rating' => 1500,
        ]);

        $tournament = Tournament::factory()->create(['name' => 'Summer Circuit']);
        $division = Division::factory()->create(['tournament_id' => $tournament->id, 'name' => 'Open']);
        $teamA = TeamModel::factory()->create([
            'tournament_id' => $tournament->id,
            'division_id' => $division->id,
            'name' => 'Alpha',
        ]);
        $teamB = TeamModel::factory()->create([
            'tournament_id' => $tournament->id,
            'division_id' => $division->id,
            'name' => 'Bravo',
        ]);

        $player->teams()->attach($teamA->id);

        MatchModel::create([
            'tournament_id' => $tournament->id,
            'division_id' => $division->id,
            'team_a_id' => $teamA->id,
            'team_b_id' => $teamB->id,
            'status' => 'completed',
            'winner_team_id' => $teamA->id,
            'scheduled_at' => now(),
        ]);

        MatchModel::create([
            'tournament_id' => $tournament->id,
            'division_id' => $division->id,
            'team_a_id' => $teamA->id,
            'team_b_id' => $teamB->id,
            'status' => 'completed',
            'winner_team_id' => $teamB->id,
            'scheduled_at' => now()->addHour(),
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('player.portal'));

        $response
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Player/Portal')
                ->where('player.name', 'Player One')
                ->where('player.rating', 1500)
                ->where('stats.wins', 1)
                ->where('stats.losses', 1)
                ->where('stats.win_rate', 50)
                ->where('tournaments.0.matches.completed.0.result', 'win')
                ->where('tournaments.0.matches.completed.1.result', 'loss')
            );
    }
}
