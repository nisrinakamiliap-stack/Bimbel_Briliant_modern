<?php

namespace Database\Factories;

use App\EducationDegree;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeacherProfile>
 */
class TeacherProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->teacher(),
            'subject' => fake()->randomElement(['Matematika', 'Bahasa Indonesia', 'Bahasa Inggris', 'Fisika', 'Kimia', 'Biologi']),
            'last_education' => fake()->randomElement(EducationDegree::cases()),
            'institution' => 'Universitas '.fake()->lastName(),
            'address' => fake()->address(),
            'bio' => fake()->sentence(12),
        ];
    }
}
