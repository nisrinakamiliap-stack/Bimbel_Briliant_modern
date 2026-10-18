<?php

namespace Database\Factories;

use App\LearningContentStatus;
use App\Models\Classroom;
use App\Models\Material;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Material>
 */
class MaterialFactory extends Factory
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
            'title' => 'Materi '.fake()->unique()->words(3, true),
            'description' => fake()->sentence(),
            'content' => fake()->paragraphs(2, true),
            'status' => LearningContentStatus::Published,
        ];
    }

    /**
     * Indicate that the material is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => ['status' => LearningContentStatus::Draft]);
    }
}
