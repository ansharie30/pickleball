<?php

namespace Database\Factories;

use App\Models\TeamModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeamModel>
 */
class TeamModelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tournament_id' => \App\Models\Tournament::factory(),
            'division_id' => \App\Models\Division::factory(),
            'name' => fake()->lastName() . '/' . fake()->lastName(),
        ];
    }
}
