<?php

namespace Database\Factories;

use App\LearningMode;
use App\Models\Classroom;
use App\Models\Program;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Classroom>
 */
class ClassroomFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'program_id' => Program::factory(),
            'teacher_id' => User::factory()->teacher(),
            'name' => 'Kelas '.fake()->unique()->bothify('??-###'),
            'code' => strtoupper(fake()->unique()->bothify('KLS-####')),
            'capacity' => fake()->numberBetween(5, 30),
            'learning_mode' => fake()->randomElement(LearningMode::cases()),
            'description' => fake()->sentence(10),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the class is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the class has no assigned teacher yet.
     */
    public function withoutTeacher(): static
    {
        return $this->state(fn (array $attributes) => [
            'teacher_id' => null,
        ]);
    }
}
