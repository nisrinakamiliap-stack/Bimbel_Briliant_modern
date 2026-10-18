<?php

namespace Database\Factories;

use App\Models\Schedule;
use App\Models\TeacherAttendance;
use App\Models\User;
use App\TeacherAttendanceStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeacherAttendance>
 */
class TeacherAttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $checkedIn = now()->subWeek()->setTime(8, 0);

        return [
            'schedule_id' => Schedule::factory(),
            'session_date' => $checkedIn->format('Y-m-d'),
            'teacher_id' => User::factory()->teacher(),
            'checked_in_at' => $checkedIn,
            'checked_out_at' => $checkedIn->copy()->addMinutes(90),
            'status' => TeacherAttendanceStatus::Present,
        ];
    }
}
