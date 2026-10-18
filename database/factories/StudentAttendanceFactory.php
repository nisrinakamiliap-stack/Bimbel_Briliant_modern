<?php

namespace Database\Factories;

use App\AttendanceStatus;
use App\Models\Enrollment;
use App\Models\Schedule;
use App\Models\StudentAttendance;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentAttendance>
 */
class StudentAttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'schedule_id' => Schedule::factory(),
            'session_date' => now()->subWeek()->format('Y-m-d'),
            'enrollment_id' => Enrollment::factory(),
            'student_id' => fn (array $attributes) => Enrollment::find($attributes['enrollment_id'])?->student_id,
            'status' => AttendanceStatus::Present,
            'notes' => null,
        ];
    }

    /**
     * Catatan untuk pendaftaran tertentu pada jadwal dan tanggal tertentu.
     */
    public function forSession(Schedule $schedule, Enrollment $enrollment, string $date): static
    {
        return $this->state(fn (array $attributes) => [
            'schedule_id' => $schedule->id,
            'enrollment_id' => $enrollment->id,
            'student_id' => $enrollment->student_id,
            'session_date' => $date,
        ]);
    }
}
