<?php

namespace Tests\Feature\Teacher;

use App\AttendanceStatus;
use App\EnrollmentStatus;
use App\Models\Classroom;
use App\Models\Enrollment;
use App\Models\Schedule;
use App\Models\StudentAttendance;
use App\Models\User;
use App\Weekday;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentAttendanceRecordingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Senin, 19 Oktober 2026.
     */
    private const MONDAY = '2026-10-19';

    private User $teacher;

    private Schedule $schedule;

    private Enrollment $first;

    private Enrollment $second;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(CarbonImmutable::parse(self::MONDAY.' 09:00:00'));

        $this->teacher = User::factory()->teacher()->create();
        $classroom = Classroom::factory()->create(['teacher_id' => $this->teacher->id, 'name' => 'SMA 12 IPA A']);
        $this->schedule = Schedule::factory()->for($classroom)->at(Weekday::Monday, '08:00', '10:00')->create();
        $this->first = Enrollment::factory()->inClassroom($classroom)->withStatus(EnrollmentStatus::Approved)->create(['enrolled_on' => '2026-09-01']);
        $this->first->student->update(['name' => 'Ani Pratiwi']);
        $this->second = Enrollment::factory()->inClassroom($classroom)->withStatus(EnrollmentStatus::Approved)->create(['enrolled_on' => '2026-09-01']);
        $this->second->student->update(['name' => 'Bima Saputra']);
    }

    /**
     * Data absensi satu sesi.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return [
            'schedule_id' => $this->schedule->id,
            'session_date' => self::MONDAY,
            'attendances' => [
                $this->first->id => ['status' => 'present'],
                $this->second->id => ['status' => 'sick', 'notes' => 'Demam'],
            ],
            ...$overrides,
        ];
    }

    /**
     * Test form pencatatan menampilkan sesi milik guru dan daftar murid peserta.
     */
    public function test_create_lists_own_sessions_and_roster(): void
    {
        Schedule::factory()->for(Classroom::factory()->create(['name' => 'Kelas Guru Lain']))->at(Weekday::Monday, '08:00', '09:00')->create();

        $this->actingAs($this->teacher)
            ->get(route('teacher.attendances.create', ['tanggal' => self::MONDAY, 'jadwal' => $this->schedule->id]))
            ->assertOk()
            ->assertSee('SMA 12 IPA A')
            ->assertDontSee('Kelas Guru Lain')
            ->assertSee('Ani Pratiwi')
            ->assertSee('Bima Saputra');
    }

    /**
     * Test guru dapat mencatat absensi sesi kelasnya dan dirinya tercatat sebagai pencatat.
     */
    public function test_teacher_can_record_attendance_for_own_session(): void
    {
        $this->actingAs($this->teacher)
            ->post(route('teacher.attendances.store'), $this->payload())
            ->assertRedirect(route('teacher.attendances.create', ['tanggal' => self::MONDAY, 'jadwal' => $this->schedule->id]))
            ->assertSessionHas('success');

        $this->assertSame(2, StudentAttendance::query()->count());
        $sick = StudentAttendance::query()->where('student_id', $this->second->student_id)->sole();
        $this->assertSame(AttendanceStatus::Sick, $sick->status);
        $this->assertSame('Demam', $sick->notes);
        $this->assertSame($this->teacher->id, $sick->recorded_by);
    }

    /**
     * Test pengiriman ulang tidak membuat absensi ganda dan tidak menimpa catatan.
     */
    public function test_resubmission_does_not_duplicate_or_overwrite(): void
    {
        $this->actingAs($this->teacher)->post(route('teacher.attendances.store'), $this->payload());

        $this->actingAs($this->teacher)
            ->post(route('teacher.attendances.store'), $this->payload(['attendances' => [
                $this->first->id => ['status' => 'absent'],
                $this->second->id => ['status' => 'absent'],
            ]]))
            ->assertSessionHas('success', fn (string $message) => str_contains($message, '2 murid dilewati'));

        $this->assertSame(2, StudentAttendance::query()->count());
        $this->assertSame(AttendanceStatus::Present, StudentAttendance::query()->where('student_id', $this->first->student_id)->sole()->status);
    }

    /**
     * Test guru tidak dapat mencatat absensi untuk jadwal kelas guru lain.
     */
    public function test_cannot_record_attendance_for_another_teachers_schedule(): void
    {
        $otherClassroom = Classroom::factory()->create();
        $otherSchedule = Schedule::factory()->for($otherClassroom)->at(Weekday::Monday, '08:00', '10:00')->create();
        $otherEnrollment = Enrollment::factory()->inClassroom($otherClassroom)->withStatus(EnrollmentStatus::Approved)->create(['enrolled_on' => '2026-09-01']);

        $this->actingAs($this->teacher)
            ->post(route('teacher.attendances.store'), [
                'schedule_id' => $otherSchedule->id,
                'session_date' => self::MONDAY,
                'attendances' => [$otherEnrollment->id => ['status' => 'present']],
            ])
            ->assertForbidden();

        $this->actingAs($this->teacher)
            ->get(route('teacher.attendances.create', ['tanggal' => self::MONDAY, 'jadwal' => $otherSchedule->id]))
            ->assertOk()
            ->assertSee('Sesi tidak tersedia');

        $this->assertSame(0, StudentAttendance::query()->count());
    }

    /**
     * Test murid dari kelas lain tidak dapat disisipkan ke absensi sesi ini.
     */
    public function test_students_outside_the_class_are_rejected(): void
    {
        $outsider = Enrollment::factory()->withStatus(EnrollmentStatus::Approved)->create();

        $this->actingAs($this->teacher)
            ->post(route('teacher.attendances.store'), $this->payload(['attendances' => [$outsider->id => ['status' => 'present']]]))
            ->assertSessionHasErrors('attendances');

        $this->assertSame(0, StudentAttendance::query()->count());
    }

    /**
     * Test absensi untuk tanggal mendatang atau sesi yang belum dimulai ditolak.
     */
    public function test_future_dates_and_unstarted_sessions_are_rejected(): void
    {
        $this->actingAs($this->teacher)
            ->post(route('teacher.attendances.store'), $this->payload(['session_date' => '2026-10-26']))
            ->assertSessionHasErrors('session_date');

        $this->travelTo(CarbonImmutable::parse(self::MONDAY.' 07:30:00'));
        $this->actingAs($this->teacher)
            ->post(route('teacher.attendances.store'), $this->payload())
            ->assertSessionHasErrors('schedule_id');

        $this->assertSame(0, StudentAttendance::query()->count());
    }

    /**
     * Test riwayat dan ringkasan hanya berisi absensi kelas yang diampu.
     */
    public function test_index_history_and_summary_are_scoped(): void
    {
        $this->actingAs($this->teacher)->post(route('teacher.attendances.store'), $this->payload());

        $otherClassroom = Classroom::factory()->create();
        $otherSchedule = Schedule::factory()->for($otherClassroom)->at(Weekday::Monday, '08:00', '10:00')->create();
        $otherEnrollment = Enrollment::factory()->inClassroom($otherClassroom)->withStatus(EnrollmentStatus::Approved)->create();
        $otherEnrollment->student->update(['name' => 'Murid Kelas Lain']);
        StudentAttendance::factory()->forSession($otherSchedule, $otherEnrollment, self::MONDAY)->create(['status' => AttendanceStatus::Absent]);

        $this->actingAs($this->teacher)
            ->get(route('teacher.attendances.index'))
            ->assertOk()
            ->assertSee('Ani Pratiwi')
            ->assertSee('Bima Saputra')
            ->assertDontSee('Murid Kelas Lain')
            ->assertViewHas('statusCounts', fn ($counts) => $counts['present'] === 1 && $counts['sick'] === 1 && $counts['absent'] === 0);
    }
}
