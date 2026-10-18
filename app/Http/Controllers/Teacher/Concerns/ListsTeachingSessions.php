<?php

namespace App\Http\Controllers\Teacher\Concerns;

use App\EnrollmentStatus;
use App\Models\Schedule;
use App\Models\TeacherAttendance;
use App\Models\User;
use App\SessionStatus;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Menyusun sesi mengajar guru (jadwal x tanggal) beserta status dan catatan check-in-nya.
 *
 * Kemampuan check-in/check-out di sini hanya untuk menampilkan tombol; aturan sebenarnya tetap
 * diperiksa di server oleh {@see TeacherAttendance::checkIn()} dan {@see TeacherAttendance::checkOut()}.
 */
trait ListsTeachingSessions
{
    /**
     * Sesi mengajar guru pada rentang tanggal, diurutkan dari yang paling awal.
     *
     * @return Collection<int, array{schedule: Schedule, date: CarbonImmutable, starts_at: CarbonImmutable, ends_at: CarbonImmutable, status: SessionStatus, attendance: TeacherAttendance|null, can_check_in: bool, can_check_out: bool}>
     */
    protected function teachingSessions(User $teacher, CarbonImmutable $from, CarbonImmutable $to, ?int $classroomId = null): Collection
    {
        $from = $from->startOfDay();
        $to = $to->startOfDay();

        $schedules = Schedule::query()
            ->where('is_active', true)
            ->whereHas('classroom', fn (Builder $query) => $query->where('teacher_id', $teacher->id)->where('is_active', true))
            ->when($classroomId, fn (Builder $query, int $id) => $query->where('classroom_id', $id))
            ->with([
                'classroom' => fn ($query) => $query->withCount(['enrollments as student_count' => fn (Builder $query) => $query->where('status', EnrollmentStatus::Approved->value)]),
                'classroom.program',
            ])
            ->orderBy('start_time')
            ->get();

        if ($schedules->isEmpty()) {
            return collect();
        }

        $attendances = TeacherAttendance::query()
            ->whereIn('schedule_id', $schedules->modelKeys())
            ->whereBetween('session_date', [$from->format('Y-m-d'), $to->format('Y-m-d')])
            ->get()
            ->keyBy(fn (TeacherAttendance $attendance) => $attendance->schedule_id.'|'.$attendance->session_date->format('Y-m-d'));

        $now = CarbonImmutable::now();
        $sessions = collect();

        for ($date = $from; $date->lessThanOrEqualTo($to); $date = $date->addDay()) {
            foreach ($schedules as $schedule) {
                if (! $schedule->occursOn($date)) {
                    continue;
                }

                $startsAt = $schedule->sessionStartsAt($date);
                $endsAt = $schedule->sessionEndsAt($date);
                $attendance = $attendances->get($schedule->id.'|'.$date->format('Y-m-d'));
                $isToday = $date->isSameDay($now);

                $sessions->push([
                    'schedule' => $schedule,
                    'date' => $date,
                    'starts_at' => $startsAt,
                    'ends_at' => $endsAt,
                    'status' => SessionStatus::at($startsAt, $endsAt, $now),
                    'attendance' => $attendance,
                    'can_check_in' => $isToday
                        && $attendance === null
                        && $now->greaterThanOrEqualTo($startsAt->subMinutes(TeacherAttendance::CHECK_IN_OPENS_MINUTES_BEFORE))
                        && $now->lessThanOrEqualTo($endsAt),
                    'can_check_out' => $isToday
                        && $attendance !== null
                        && $attendance->teacher_id === $teacher->id
                        && $attendance->checked_out_at === null,
                ]);
            }
        }

        return $sessions->sortBy('starts_at')->values();
    }
}
