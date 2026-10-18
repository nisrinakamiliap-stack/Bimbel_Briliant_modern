<?php

namespace Tests\Feature\Admin;

use App\Models\Classroom;
use App\Models\Schedule;
use App\Models\TeacherAttendance;
use App\Models\User;
use App\TeacherAttendanceStatus;
use App\Weekday;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherAttendanceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $teacher;

    private Schedule $schedule;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(CarbonImmutable::parse('2026-10-19 12:00:00'));

        $this->admin = User::factory()->admin()->create();
        $this->teacher = User::factory()->teacher()->create(['name' => 'Sari Wahyuni']);
        $classroom = Classroom::factory()->create(['teacher_id' => $this->teacher->id, 'name' => 'SMA 12 IPA A']);
        $this->schedule = Schedule::factory()->for($classroom)->at(Weekday::Monday, '08:00', '10:00')->create();
    }

    /**
     * Membuat catatan check-in/check-out pada tanggal tertentu.
     */
    private function record(string $date, string $in, ?string $out, TeacherAttendanceStatus $status = TeacherAttendanceStatus::Present, ?User $teacher = null, ?Schedule $schedule = null): TeacherAttendance
    {
        return TeacherAttendance::factory()->create([
            'schedule_id' => ($schedule ?? $this->schedule)->id,
            'teacher_id' => ($teacher ?? $this->teacher)->id,
            'session_date' => $date,
            'checked_in_at' => "{$date} {$in}",
            'checked_out_at' => $out === null ? null : "{$date} {$out}",
            'status' => $status,
        ]);
    }

    /**
     * Test daftar absensi guru menampilkan guru, kelas, tanggal, jam, durasi, dan status.
     */
    public function test_index_lists_teacher_attendance(): void
    {
        $this->record('2026-10-12', '07:55:00', '09:40:00');
        $this->record('2026-10-19', '08:20:00', null, TeacherAttendanceStatus::Late);

        $this->actingAs($this->admin)
            ->get(route('admin.attendances.teachers.index'))
            ->assertOk()
            ->assertSee('Absensi Guru')
            ->assertSee('Sari Wahyuni')
            ->assertSee('SMA 12 IPA A')
            ->assertSee('12 Okt 2026')
            ->assertSee('07:55')
            ->assertSee('09:40')
            ->assertSee('1 jam 45 menit')
            ->assertSee('Belum check-out')
            ->assertSee('Terlambat');
    }

    /**
     * Test filter guru, kelas, tanggal, dan status serta rekap status per periode.
     */
    public function test_filters_and_status_recap(): void
    {
        $otherTeacher = User::factory()->teacher()->create(['name' => 'Budi Santoso']);
        $otherSchedule = Schedule::factory()->for(Classroom::factory()->state(['teacher_id' => $otherTeacher->id]))->at(Weekday::Monday, '13:00', '14:00')->create();

        $a = $this->record('2026-10-05', '08:00:00', '10:00:00');
        $b = $this->record('2026-10-12', '08:30:00', '10:00:00', TeacherAttendanceStatus::Late);
        $c = $this->record('2026-10-12', '13:00:00', '14:00:00', TeacherAttendanceStatus::Present, $otherTeacher, $otherSchedule);

        $ids = fn (array $query): array => $this->actingAs($this->admin)
            ->get(route('admin.attendances.teachers.index', $query))
            ->viewData('records')->pluck('id')->sort()->values()->all();

        $this->assertSame([$c->id], $ids(['q' => 'Budi']));
        $this->assertSame([$a->id, $b->id], $ids(['kelas' => $this->schedule->classroom_id]));
        $this->assertSame([$b->id, $c->id], $ids(['dari' => '2026-10-10']));
        $this->assertSame([$b->id], $ids(['status' => 'late']));

        $counts = $this->actingAs($this->admin)
            ->get(route('admin.attendances.teachers.index', ['dari' => '2026-10-10']))
            ->viewData('statusCounts')->all();
        $this->assertSame(['present' => 1, 'late' => 1, 'excused' => 0, 'sick' => 0, 'absent' => 0], $counts);
    }

    /**
     * Test admin dapat mengoreksi status absensi guru; waktu check-in/out tetap tercatat.
     */
    public function test_admin_can_correct_teacher_attendance(): void
    {
        $record = $this->record('2026-10-12', '08:20:00', '10:00:00', TeacherAttendanceStatus::Late);

        $this->actingAs($this->admin)
            ->get(route('admin.attendances.teachers.edit', $record))
            ->assertOk()
            ->assertSee('08:20:00');

        $this->actingAs($this->admin)
            ->put(route('admin.attendances.teachers.update', $record), ['status' => 'present', 'reason' => 'Terlambat karena rapat koordinasi dengan admin.'])
            ->assertRedirect(route('admin.attendances.teachers.index'))
            ->assertSessionHas('success', 'Absensi Sari Wahyuni pada 12 Oktober 2026 berhasil dikoreksi menjadi Hadir.');

        $record->refresh();
        $this->assertSame(TeacherAttendanceStatus::Present, $record->status);
        $this->assertSame(TeacherAttendanceStatus::Late, $record->previous_status);
        $this->assertSame($this->admin->id, $record->corrected_by);
        $this->assertSame('2026-10-12 08:20:00', $record->checked_in_at->format('Y-m-d H:i:s'));

        $this->actingAs($this->admin)
            ->put(route('admin.attendances.teachers.update', $record), ['status' => 'hadir_banget', 'reason' => 'Status tidak valid'])
            ->assertSessionHasErrors('status');
    }

    /**
     * Test guru dan murid tidak dapat melihat atau mengoreksi absensi guru di panel admin.
     */
    public function test_non_admin_users_cannot_manage_teacher_attendance(): void
    {
        $record = $this->record('2026-10-12', '08:20:00', '10:00:00', TeacherAttendanceStatus::Late);

        foreach ([$this->teacher, User::factory()->student()->create()] as $user) {
            $this->actingAs($user)->get(route('admin.attendances.teachers.index'))->assertForbidden();
            $this->actingAs($user)->get(route('admin.attendances.teachers.edit', $record))->assertForbidden();
            $this->actingAs($user)->put(route('admin.attendances.teachers.update', $record), ['status' => 'present', 'reason' => 'Koreksi sendiri'])->assertForbidden();
        }

        $this->assertSame(TeacherAttendanceStatus::Late, $record->fresh()->status);
    }

    /**
     * Test catatan check-in yang dibuat lewat endpoint guru tampil di halaman admin.
     */
    public function test_check_in_from_teacher_endpoint_appears_for_admin(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-10-19 08:05:00'));
        $this->actingAs($this->teacher)->post(route('teacher.attendance.check-in', $this->schedule))->assertSessionHasNoErrors();

        $this->actingAs($this->admin)
            ->get(route('admin.attendances.teachers.index'))
            ->assertSee('Sari Wahyuni')
            ->assertSee('08:05')
            ->assertSee('Belum check-out');
    }
}
