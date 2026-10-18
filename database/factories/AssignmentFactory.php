<?php

namespace Database\Factories;

use App\LearningContentStatus;
use App\Models\Assignment;
use App\Models\Classroom;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Assignment>
 */
class AssignmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'classroom_id' => Classroom::factory(),
            'title' => 'Tugas '.fake()->unique()->words(3, true),
            'instructions' => fake()->paragraph(),
            'due_at' => now()->addWeek(),
            'max_score' => 100,
            'allow_late_submission' => false,
            'allow_resubmission' => true,
            'status' => LearningContentStatus::Published,
        ];
    }

    /**
     * Indicate that the assignment is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => ['status' => LearningContentStatus::Draft]);
    }

    /**
     * Indicate that the assignment deadline has passed.
     */
    public function pastDue(): static
    {
        return $this->state(fn (array $attributes) => ['due_at' => now()->subDay()]);
    }
}
