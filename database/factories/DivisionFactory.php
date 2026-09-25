<?php

namespace Database\Factories;

use App\Models\Division;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Division>
 */
class DivisionFactory extends Factory
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
            'name' => fake()->randomElement(['Men\'s Doubles', 'Women\'s Doubles', 'Mixed Doubles']) . ' 3.5',
            'skill_level' => '3.5',
        ];
    }
}
