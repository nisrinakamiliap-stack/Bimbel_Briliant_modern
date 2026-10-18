<?php

namespace Database\Factories;

use App\EnrollmentStatus;
use App\Models\Classroom;
use App\Models\Enrollment;
use App\Models\Program;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Enrollment>
 */
class EnrollmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => User::factory()->student(),
            'program_id' => Program::factory(),
            'classroom_id' => null,
            'bill_amount' => fn (array $attributes) => Program::find($attributes['program_id'])?->price ?? 0,
            'enrolled_on' => fake()->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
            'status' => EnrollmentStatus::Pending,
            'admin_notes' => null,
        ];
    }

    /**
     * Place the enrollment in a class of its program.
     */
    public function inClassroom(Classroom $classroom): static
    {
        return $this->state(fn (array $attributes) => [
            'program_id' => $classroom->program_id,
            'classroom_id' => $classroom->id,
        ]);
    }

    /**
     * Set the enrollment status.
     */
    public function withStatus(EnrollmentStatus $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
            'status_changed_at' => $status === EnrollmentStatus::Pending ? null : now(),
        ]);
    }
}
