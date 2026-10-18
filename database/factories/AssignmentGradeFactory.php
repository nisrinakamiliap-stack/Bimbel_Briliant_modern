<?php

namespace Database\Factories;

use App\Models\AssignmentGrade;
use App\Models\Submission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AssignmentGrade>
 */
class AssignmentGradeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'submission_id' => Submission::factory(),
            'score' => fake()->numberBetween(60, 100),
            'feedback' => fake()->sentence(),
            'graded_at' => now(),
        ];
    }
}
