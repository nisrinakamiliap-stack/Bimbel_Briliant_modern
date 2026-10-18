<?php

namespace Database\Factories;

use App\Models\Program;
use App\ProgramLevel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Program>
 */
class ProgramFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Program '.fake()->unique()->words(3, true),
            'level' => fake()->randomElement(ProgramLevel::cases()),
            'description' => fake()->sentence(15),
            'price' => fake()->numberBetween(20, 100) * 10000,
            'duration_months' => fake()->randomElement([null, 1, 3, 6, 12]),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the program is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
