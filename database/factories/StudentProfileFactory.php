<?php

namespace Database\Factories;

use App\EducationLevel;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentProfile>
 */
class StudentProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->student(),
            'education_level' => fake()->randomElement(EducationLevel::cases()),
            'school_name' => 'Sekolah '.fake()->lastName(),
            'birth_date' => fake()->dateTimeBetween('-18 years', '-7 years'),
            'address' => fake()->address(),
            'parent_name' => fake()->name(),
            'parent_phone' => '08'.fake()->numerify('##########'),
        ];
    }
}
