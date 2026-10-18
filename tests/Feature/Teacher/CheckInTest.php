<?php

namespace Tests\Feature\Teacher;

use App\Models\Classroom;
use App\Models\Schedule;
use App\Models\TeacherAttendance;
use App\Models\User;
use App\TeacherAttendanceStatus;
use App\Weekday;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckInTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Senin, 19 Oktober 2026.
     */
    private const MONDAY = '2026-10-19';

    private User $teacher;

    private Schedule $schedule;

    protected function setUp(): void
    {
        parent::setUp();

        $this->teacher = User::factory()->teacher()->create();
        $classroom = Classroom::factory()->create(['teacher_id' => $this->teacher->id]);
        $this->schedule = Schedule::factory()->for($classroom)->at(Weekday::Monday, '08:00', '10:00')->create();
    }

    /**
     * Mengatur waktu server ke jam tertentu pada hari Senin.
     */
    private function at(string $time): void
    {
        $this->travelTo(CarbonImmutable::parse(self::MONDAY.' '.$time));
    }

    /**
     * Test guru dapat check-in tepat waktu; waktu dicatat oleh server, bukan dari request.
     */
    public function test_teacher_can_check_in_on_time_using_server_time(): void
    {
        $this->at('07:50:00');

        $this->actingAs($this->teacher)
            ->post(route('teacher.attendance.check-in', $this->schedule), ['checked_in_at' => '2020-01-01 00:00:00'])
            ->assertSessionHas('success', 'Check-in berhasil pukul 07:50 (Hadir).');

        $attendance = TeacherAttendance::firstOrFail();
        $this->assertSame($this->teacher->id, $attendance->teacher_id);
        $this->assertSame(self::MONDAY.' 07:50:00', $attendance->checked_in_at->format('Y-m-d H:i:s'));
        $this->assertSame(self::MONDAY, $attendance->session_date->format('Y-m-d'));
        $this->assertSame(TeacherAttendanceStatus::Present, $attendance->status);
        $this->assertNull($attendance->checked_out_at);
    }

    /**
     * Test check-in lebih dari 15 menit setelah jadwal dimulai dicatat sebagai terlambat.
     */
    public function test_late_check_in_is_marked_late(): void
    {
        $this->at('08:20:00');

        $this->actingAs($this->teacher)->post(route('teacher.attendance.check-in', $this->schedule));

        $this->assertSame(TeacherAttendanceStatus::Late, TeacherAttendance::firstOrFail()->status);
    }

    /**
     * Test jendela waktu check-in: terlalu awal, setelah jadwal selesai, atau di hari lain ditolak.
     */
    public function test_check_in_window_is_enforced(): void
    {
        $this->at('07:20:00');
        $this->actingAs($this->teacher)
            ->post(route('teacher.attendance.check-in', $this->schedule))
            ->assertSessionHasErrors(['schedule' => 'Check-in baru dibuka 30 menit sebelum jadwal dimulai (pukul 07:30).']);

        $this->at('10:01:00');
        $this->actingAs($this->teacher)
            ->post(route('teacher.attendance.check-in', $this->schedule))
            ->assertSessionHasErrors(['schedule' => 'Jadwal sudah selesai pukul 10:00. Hubungi admin jika perlu koreksi kehadiran.']);

        $this->travelTo(CarbonImmutable::parse('2026-10-20 08:00:00'));
        $this->actingAs($this->teacher)
            ->post(route('teacher.attendance.check-in', $this->schedule))
            ->assertSessionHasErrors(['schedule' => 'Jadwal ini tidak berlangsung hari ini sehingga tidak dapat di-check-in.']);

        $this->assertDatabaseCount('teacher_attendances', 0);
    }

    /**
     * Test check-in ditolak untuk jadwal nonaktif, kelas nonaktif, atau di luar masa berlaku.
     */
    public function test_check_in_requires_valid_active_schedule(): void
    {
        $this->at('08:00:00');

        $inactive = Schedule::factory()->for($this->schedule->classroom)->at(Weekday::Monday, '11:00', '12:00')->inactive()->create();
        $ended = Schedule::factory()->for($this->schedule->classroom)->at(Weekday::Monday, '07:30', '08:30')->create(['ends_on' => '2026-10-12']);

        foreach ([$inactive, $ended] as $schedule) {
            $this->actingAs($this->teacher)
                ->post(route('teacher.attendance.check-in', $schedule))
                ->assertSessionHasErrors('schedule');
        }

        $this->schedule->classroom->update(['is_active' => false]);
        $this->actingAs($this->teacher)
            ->post(route('teacher.attendance.check-in', $this->schedule))
            ->assertSessionHasErrors('schedule');

        $this->assertDatabaseCount('teacher_attendances', 0);
    }

    /**
     * Test guru hanya dapat check-in untuk jadwal kelas yang diampunya.
     */
    public function test_teacher_cannot_check_in_for_another_teachers_schedule(): void
    {
        $this->at('08:00:00');
        $otherTeacher = User::factory()->teacher()->create();

        $this->actingAs($otherTeacher)
            ->post(route('teacher.attendance.check-in', $this->schedule))
            ->assertSessionHasErrors(['schedule' => 'Anda hanya dapat check-in untuk jadwal kelas yang Anda ampu.']);

        $this->assertDatabaseCount('teacher_attendances', 0);
    }

    /**
     * Test check-in ganda untuk sesi yang sama ditolak.
     */
    public function test_double_check_in_is_rejected(): void
    {
        $this->at('07:55:00');
        $this->actingAs($this->teacher)->post(route('teacher.attendance.check-in', $this->schedule));

        $this->at('08:05:00');
        $this->actingAs($this->teacher)
            ->post(route('teacher.attendance.check-in', $this->schedule))
            ->assertSessionHasErrors(['schedule' => 'Anda sudah check-in untuk sesi ini.']);

        $this->assertDatabaseCount('teacher_attendances', 1);
        $this->assertSame('07:55', TeacherAttendance::firstOrFail()->checked_in_at->format('H:i'));
    }

    /**
     * Test jadwal berbeda pada hari yang sama dicatat sebagai sesi terpisah.
     */
    public function test_multiple_schedules_on_same_day_are_separate_sessions(): void
    {
        $afternoon = Schedule::factory()->for($this->schedule->classroom)->at(Weekday::Monday, '13:00', '15:00')->create();

        $this->at('08:00:00');
        $this->actingAs($this->teacher)->post(route('teacher.attendance.check-in', $this->schedule))->assertSessionHasNoErrors();
        $this->at('12:50:00');
        $this->actingAs($this->teacher)->post(route('teacher.attendance.check-in', $afternoon))->assertSessionHasNoErrors();

        $this->assertDatabaseCount('teacher_attendances', 2);
    }

    /**
     * Test check-out mencatat waktu server dan menghitung durasi mengajar.
     */
    public function test_teacher_can_check_out_after_check_in(): void
    {
        $this->at('07:58:00');
        $this->actingAs($this->teacher)->post(route('teacher.attendance.check-in', $this->schedule));

        $this->at('09:58:00');
        $this->actingAs($this->teacher)
            ->patch(route('teacher.attendance.check-out', $this->schedule), ['checked_out_at' => '2030-01-01 00:00:00'])
            ->assertSessionHas('success', 'Check-out berhasil pukul 09:58. Durasi mengajar: 2 jam.');

        $attendance = TeacherAttendance::firstOrFail();
        $this->assertSame(self::MONDAY.' 09:58:00', $attendance->checked_out_at->format('Y-m-d H:i:s'));
        $this->assertSame(120, $attendance->durationMinutes());
    }

    /**
     * Test check-out tanpa check-in, check-out ganda, atau oleh guru lain ditolak tanpa membuat catatan baru.
     */
    public function test_invalid_check_outs_are_rejected(): void
    {
        $this->at('09:00:00');

        $this->actingAs($this->teacher)
            ->patch(route('teacher.attendance.check-out', $this->schedule))
            ->assertSessionHasErrors(['schedule' => 'Belum ada check-in untuk sesi ini. Lakukan check-in terlebih dahulu.']);
        $this->assertDatabaseCount('teacher_attendances', 0);

        $this->actingAs($this->teacher)->post(route('teacher.attendance.check-in', $this->schedule));

        $otherTeacher = User::factory()->teacher()->create();
        $this->actingAs($otherTeacher)
            ->patch(route('teacher.attendance.check-out', $this->schedule))
            ->assertSessionHasErrors('schedule');
        $this->assertNull(TeacherAttendance::firstOrFail()->checked_out_at);

        $this->at('09:45:00');
        $this->actingAs($this->teacher)->patch(route('teacher.attendance.check-out', $this->schedule))->assertSessionHasNoErrors();
        $this->at('09:50:00');
        $this->actingAs($this->teacher)
            ->patch(route('teacher.attendance.check-out', $this->schedule))
            ->assertSessionHasErrors(['schedule' => 'Anda sudah check-out untuk sesi ini pada pukul 09:45.']);

        $this->assertDatabaseCount('teacher_attendances', 1);
        $this->assertSame('09:45', TeacherAttendance::firstOrFail()->checked_out_at->format('H:i'));
    }

    /**
     * Test endpoint check-in/check-out hanya untuk guru yang sudah login.
     */
    public function test_check_in_endpoints_require_teacher_role(): void
    {
        $this->at('08:00:00');

        foreach ([User::factory()->admin()->create(), User::factory()->student()->create()] as $user) {
            $this->actingAs($user)->post(route('teacher.attendance.check-in', $this->schedule))->assertForbidden();
            $this->actingAs($user)->patch(route('teacher.attendance.check-out', $this->schedule))->assertForbidden();
        }

        auth()->logout();
        $this->post(route('teacher.attendance.check-in', $this->schedule))->assertRedirect(route('login'));

        $this->assertDatabaseCount('teacher_attendances', 0);
    }
}
