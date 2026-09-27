<?php

namespace Tests\Feature;

use App\Models\Division;
use App\Models\MatchModel;
use App\Models\PlayerProfile;
use App\Models\Sport;
use App\Models\TeamModel;
use App\Models\Tournament;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class MatchSubstitutionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_save_basketball_starters_and_cannot_use_players_from_the_other_team(): void
    {
        Event::fake();
        [$match, $teamA, $teamB] = $this->makeMatch('Basketball', 'timed_period');
        $starters = $this->addPlayers($teamA, 7);
        $opponentPlayers = $this->addPlayers($teamB, 7);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->patch(route('matches.substitute', $match), [
                'team' => 'a',
                'starter_ids' => $starters->take(5)->pluck('id')->all(),
            ])
            ->assertRedirect();

        $this->assertSame(
            $starters->take(5)->pluck('id')->all(),
            $match->fresh()->starting_lineups['a']
        );

        $this->actingAs($admin)
            ->from(route('matches.show', $match))
            ->patch(route('matches.substitute', $match), [
                'team' => 'a',
                'starter_ids' => $starters->take(4)->pluck('id')->push($opponentPlayers->first()->id)->all(),
            ])
            ->assertSessionHasErrors('starter_ids');

        $this->assertSame(
            $starters->take(5)->pluck('id')->all(),
            $match->fresh()->starting_lineups['a']
        );
    }

    public function test_volleyball_substitutions_require_six_starters(): void
    {
        Event::fake();
        [$match, $teamA] = $this->makeMatch('Volleyball', 'set_based');
        $players = $this->addPlayers($teamA, 8);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->patch(route('matches.substitute', $match), [
                'team' => 'a',
                'starter_ids' => $players->take(6)->pluck('id')->all(),
            ])
            ->assertRedirect();

        $this->assertCount(6, $match->fresh()->starting_lineups['a']);

        $this->actingAs($admin)
            ->from(route('matches.show', $match))
            ->patch(route('matches.substitute', $match), [
                'team' => 'a',
                'starter_ids' => $players->take(5)->pluck('id')->all(),
            ])
            ->assertSessionHasErrors('starter_ids');
    }

    public function test_non_admin_cannot_substitute_players(): void
    {
        [$match] = $this->makeMatch('Basketball', 'timed_period');
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch(route('matches.substitute', $match), [
                'team' => 'a',
                'starter_ids' => [],
            ])
            ->assertForbidden();
    }

    private function makeMatch(string $sportName, string $scoringType): array
    {
        $sport = Sport::create(['name' => $sportName, 'scoring_type' => $scoringType]);
        $tournament = Tournament::factory()->create(['sport_id' => $sport->id]);
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
            'status' => 'in_progress',
        ]);

        return [$match, $teamA, $teamB];
    }

    private function addPlayers(TeamModel $team, int $count)
    {
        $players = PlayerProfile::factory()->count($count)->create();
        $team->players()->attach($players->modelKeys());

        return $players;
    }
}
