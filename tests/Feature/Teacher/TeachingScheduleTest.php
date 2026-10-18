<?php

namespace Tests\Feature\Teacher;

use App\Models\Classroom;
use App\Models\Schedule;
use App\Models\TeacherAttendance;
use App\Models\User;
use App\Weekday;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeachingScheduleTest extends TestCase
{
    use RefreshDatabase;

    private User $teacher;

    private Classroom $classroom;

    private Schedule $mondaySchedule;

    protected function setUp(): void
    {
        parent::setUp();

        $this->teacher = User::factory()->teacher()->create();
        $this->classroom = Classroom::factory()->create(['teacher_id' => $this->teacher->id, 'name' => 'SMP 9 Reguler']);
        $this->mondaySchedule = Schedule::factory()->for($this->classroom)->at(Weekday::Monday, '08:00', '10:00')->create(['room' => 'Ruang Lawu']);
        Schedule::factory()->for($this->classroom)->at(Weekday::Thursday, '15:00', '16:30')->create(['room' => 'Ruang Sindoro']);
        Schedule::factory()->for(Classroom::factory()->create(['name' => 'Kelas Guru Lain']))->at(Weekday::Monday, '08:00', '09:00')->create();
    }

    /**
     * Mengatur waktu server pada Senin, 19 Oktober 2026.
     */
    private function mondayAt(string $time): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-10-19 '.$time));
    }

    /**
     * Test tampilan mingguan menampilkan seluruh sesi minggu ini milik guru saja.
     */
    public function test_weekly_view_lists_own_sessions_for_the_week(): void
    {
        $this->mondayAt('07:00:00');

        $this->actingAs($this->teacher)
            ->get(route('teacher.schedules.index'))
            ->assertOk()
            ->assertSee('19 Okt sampai 25 Okt 2026')
            ->assertSeeInOrder(['Senin', 'Ruang Lawu', 'Kamis', 'Ruang Sindoro'])
            ->assertDontSee('Kelas Guru Lain');
    }

    /**
     * Test tampilan harian hanya menampilkan sesi pada tanggal yang dipilih.
     */
    public function test_daily_view_shows_selected_date_only(): void
    {
        $this->mondayAt('07:00:00');

        $this->actingAs($this->teacher)
            ->get(route('teacher.schedules.index', ['tampilan' => 'harian', 'tanggal' => '2026-10-22']))
            ->assertOk()
            ->assertSee('Kamis, 22 Oktober 2026')
            ->assertSee('Ruang Sindoro')
            ->assertDontSee('Ruang Lawu');

        $this->actingAs($this->teacher)
            ->get(route('teacher.schedules.index', ['tampilan' => 'harian', 'tanggal' => '2026-10-20']))
            ->assertOk()
            ->assertSee('Tidak ada jadwal pada hari ini');
    }

    /**
     * Test filter kelas milik guru lain ditolak di backend.
     */
    public function test_filtering_by_another_teachers_class_is_rejected(): void
    {
        $otherClassroom = Classroom::factory()->create();

        $this->actingAs($this->teacher)
            ->get(route('teacher.schedules.index', ['kelas' => $otherClassroom->id]))
            ->assertSessionHasErrors('kelas');
    }

    /**
     * Test status sesi mengikuti waktu server: akan datang, sedang berlangsung, lalu selesai.
     */
    public function test_session_status_follows_server_time(): void
    {
        $this->mondayAt('07:00:00');
        $this->actingAs($this->teacher)->get(route('teacher.schedules.index', ['tampilan' => 'harian']))
            ->assertSee('Akan Datang')
            ->assertSee('Check-in dibuka pukul 07:30')
            ->assertDontSee(route('teacher.attendance.check-in', $this->mondaySchedule), false);

        $this->mondayAt('08:30:00');
        $this->actingAs($this->teacher)->get(route('teacher.schedules.index', ['tampilan' => 'harian']))
            ->assertSee('Sedang Berlangsung')
            ->assertSee('is-ongoing', false)
            ->assertSee(route('teacher.attendance.check-in', $this->mondaySchedule), false);

        $this->mondayAt('11:00:00');
        $this->actingAs($this->teacher)->get(route('teacher.schedules.index', ['tampilan' => 'harian']))
            ->assertSee('Selesai')
            ->assertSee('Tidak ada check-in tercatat')
            ->assertDontSee(route('teacher.attendance.check-in', $this->mondaySchedule), false);
    }

    /**
     * Test check-in lalu check-out dari halaman jadwal tersimpan di backend dan tombolnya berganti.
     */
    public function test_check_in_and_check_out_from_schedule_page(): void
    {
        $this->mondayAt('07:55:00');

        $this->actingAs($this->teacher)
            ->from(route('teacher.schedules.index'))
            ->post(route('teacher.attendance.check-in', $this->mondaySchedule))
            ->assertRedirect(route('teacher.schedules.index'))
            ->assertSessionHas('success');

        $this->actingAs($this->teacher)
            ->get(route('teacher.schedules.index', ['tampilan' => 'harian']))
            ->assertSee('Check-in 07:55 (Hadir)')
            ->assertSee(route('teacher.attendance.check-out', $this->mondaySchedule), false)
            ->assertDontSee(route('teacher.attendance.check-in', $this->mondaySchedule), false);

        $this->mondayAt('10:05:00');
        $this->actingAs($this->teacher)
            ->from(route('teacher.schedules.index'))
            ->patch(route('teacher.attendance.check-out', $this->mondaySchedule))
            ->assertSessionHas('success');

        $attendance = TeacherAttendance::query()->sole();
        $this->assertSame('10:05', $attendance->checked_out_at->format('H:i'));

        $this->actingAs($this->teacher)
            ->get(route('teacher.schedules.index', ['tampilan' => 'harian']))
            ->assertSee('Check-out 10:05')
            ->assertDontSee(route('teacher.attendance.check-out', $this->mondaySchedule), false);
    }

    /**
     * Test check-in ganda ditolak dan pesannya tampil di halaman.
     */
    public function test_double_check_in_is_rejected_with_visible_message(): void
    {
        $this->mondayAt('08:00:00');

        $this->actingAs($this->teacher)->post(route('teacher.attendance.check-in', $this->mondaySchedule));

        $this->actingAs($this->teacher)
            ->from(route('teacher.schedules.index'))
            ->followingRedirects()
            ->post(route('teacher.attendance.check-in', $this->mondaySchedule))
            ->assertOk()
            ->assertSee('Anda sudah check-in untuk sesi ini.')
            ->assertDontSee('Periksa kembali isian yang ditandai merah');

        $this->assertSame(1, TeacherAttendance::query()->count());
    }

    /**
     * Test guru tidak dapat check-in pada jadwal kelas guru lain.
     */
    public function test_cannot_check_in_on_another_teachers_schedule(): void
    {
        $this->mondayAt('08:00:00');
        $otherSchedule = Schedule::query()->whereHas('classroom', fn ($query) => $query->where('name', 'Kelas Guru Lain'))->sole();

        $this->actingAs($this->teacher)
            ->post(route('teacher.attendance.check-in', $otherSchedule))
            ->assertSessionHasErrors('schedule');

        $this->assertSame(0, TeacherAttendance::query()->count());
    }
}
