<?php

namespace Database\Factories;

use App\Models\Assignment;
use App\Models\Enrollment;
use App\Models\Submission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Submission>
 */
class SubmissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'assignment_id' => Assignment::factory(),
            'enrollment_id' => Enrollment::factory(),
            'student_id' => fn (array $attributes) => Enrollment::find($attributes['enrollment_id'])?->student_id,
            'answer_text' => fake()->paragraph(),
            'submitted_at' => now(),
            'is_late' => false,
        ];
    }

    /**
     * Pengumpulan untuk pendaftaran tertentu.
     */
    public function byEnrollment(Enrollment $enrollment): static
    {
        return $this->state(fn (array $attributes) => [
            'enrollment_id' => $enrollment->id,
            'student_id' => $enrollment->student_id,
        ]);
    }
}
