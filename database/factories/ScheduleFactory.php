<?php

namespace Database\Factories;

use App\Models\Classroom;
use App\Models\Schedule;
use App\Weekday;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Schedule>
 */
class ScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startHour = fake()->numberBetween(7, 18);

        return [
            'classroom_id' => Classroom::factory(),
            'day_of_week' => fake()->randomElement(Weekday::cases()),
            'start_time' => sprintf('%02d:00', $startHour),
            'end_time' => sprintf('%02d:30', $startHour + 1),
            'starts_on' => null,
            'ends_on' => null,
            'room' => 'Ruang '.fake()->numberBetween(1, 10),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the schedule is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Set the day and time range of the schedule.
     */
    public function at(Weekday $day, string $startTime, string $endTime): static
    {
        return $this->state(fn (array $attributes) => [
            'day_of_week' => $day,
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]);
    }
}
