<?php

namespace Tests\Feature\Admin;

use App\AttendanceStatus;
use App\EnrollmentStatus;
use App\Models\Classroom;
use App\Models\Enrollment;
use App\Models\Program;
use App\Models\Schedule;
use App\Models\StudentAttendance;
use App\Models\User;
use App\Weekday;
use Carbon\CarbonImmutable;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentAttendanceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Senin, 19 Oktober 2026.
     */
    private const MONDAY = '2026-10-19';

    private User $admin;

    private Classroom $classroom;

    private Schedule $schedule;

    private Enrollment $dewi;

    private Enrollment $rizky;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(CarbonImmutable::parse(self::MONDAY.' 10:30:00'));

        $this->admin = User::factory()->admin()->create();
        $program = Program::factory()->create();
        $teacher = User::factory()->teacher()->create(['name' => 'Sari Wahyuni']);
        $this->classroom = Classroom::factory()->for($program)->create(['teacher_id' => $teacher->id, 'name' => 'SMA 12 IPA A']);
        $this->schedule = Schedule::factory()->for($this->classroom)->at(Weekday::Monday, '08:00', '10:00')->create();

        $this->dewi = $this->approvedEnrollment('Dewi Lestari');
        $this->rizky = $this->approvedEnrollment('Rizky Pratama');
    }

    /**
     * Pendaftaran diterima di kelas utama.
     */
    private function approvedEnrollment(string $name, ?Classroom $classroom = null, string $enrolledOn = '2026-09-01'): Enrollment
    {
        return Enrollment::factory()
            ->inClassroom($classroom ?? $this->classroom)
            ->withStatus(EnrollmentStatus::Approved)
            ->for(User::factory()->student()->state(['name' => $name]), 'student')
            ->create(['enrolled_on' => $enrolledOn]);
    }

    /**
     * Payload pencatatan absensi untuk sesi utama.
     *
     * @param  array<int, string>  $statuses
     * @return array<string, mixed>
     */
    private function payload(array $statuses, string $date = self::MONDAY, ?Schedule $schedule = null): array
    {
        return [
            'schedule_id' => (string) ($schedule ?? $this->schedule)->id,
            'session_date' => $date,
            'attendances' => collect($statuses)->mapWithKeys(fn (string $status, int $enrollmentId) => [$enrollmentId => ['status' => $status, 'notes' => '']])->all(),
        ];
    }

    /**
     * Test form pencatatan menampilkan jadwal yang berlangsung dan hanya murid peserta kelas.
     */
    public function test_create_page_shows_schedules_of_the_day_and_class_roster(): void
    {
        Schedule::factory()->at(Weekday::Tuesday, '08:00', '10:00')->create();
        $this->approvedEnrollment('Murid Kelas Lain', Classroom::factory()->create());
        Enrollment::factory()->inClassroom($this->classroom)->for(User::factory()->student()->state(['name' => 'Murid Belum Diterima']), 'student')->create();
        $this->approvedEnrollment('Murid Daftar Belakangan', null, '2026-10-25');

        $response = $this->actingAs($this->admin)->get(route('admin.attendances.students.create', ['tanggal' => self::MONDAY, 'jadwal' => $this->schedule->id]));

        $response->assertOk();
        $response->assertSee('08:00–10:00 · SMA 12 IPA A · Guru: Sari Wahyuni');
        $response->assertSee('Dewi Lestari');
        $response->assertSee('Rizky Pratama');
        $response->assertDontSee('Murid Kelas Lain');
        $response->assertDontSee('Murid Belum Diterima');
        $response->assertDontSee('Murid Daftar Belakangan');
        $response->assertSee('name="attendances['.$this->dewi->id.'][status]"', false);
    }

    /**
     * Test informasi saat kelas belum memiliki murid, tanggal tanpa jadwal, dan sesi yang belum dimulai.
     */
    public function test_create_page_shows_relevant_empty_states(): void
    {
        $emptyClassSchedule = Schedule::factory()->for(Classroom::factory())->at(Weekday::Monday, '07:00', '08:00')->create();
        $laterSchedule = Schedule::factory()->for($this->classroom)->at(Weekday::Monday, '13:00', '14:00')->create();

        $this->actingAs($this->admin)
            ->get(route('admin.attendances.students.create', ['tanggal' => self::MONDAY, 'jadwal' => $emptyClassSchedule->id]))
            ->assertSee('Belum ada murid di kelas ini');

        $this->actingAs($this->admin)
            ->get(route('admin.attendances.students.create', ['tanggal' => '2026-10-13']))
            ->assertSee('Tidak ada jadwal pada tanggal ini');

        $this->actingAs($this->admin)
            ->get(route('admin.attendances.students.create', ['tanggal' => self::MONDAY, 'jadwal' => $laterSchedule->id]))
            ->assertSee('Sesi belum dimulai');

        $this->actingAs($this->admin)
            ->get(route('admin.attendances.students.create', ['tanggal' => '2026-10-26']))
            ->assertSessionHasErrors('tanggal');
    }

    /**
     * Test admin dapat mencatat absensi murid untuk satu sesi.
     */
    public function test_admin_can_record_attendance_for_a_session(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.attendances.students.store'), $this->payload([
            $this->dewi->id => 'present',
            $this->rizky->id => 'sick',
        ]));

        $response->assertRedirect(route('admin.attendances.students.create', ['tanggal' => self::MONDAY, 'jadwal' => $this->schedule->id]));
        $response->assertSessionHas('success', 'Absensi 2 murid untuk SMA 12 IPA A pada 19 Oktober 2026 berhasil disimpan.');

        $record = StudentAttendance::where('student_id', $this->rizky->student_id)->firstOrFail();
        $this->assertSame(AttendanceStatus::Sick, $record->status);
        $this->assertSame($this->schedule->id, $record->schedule_id);
        $this->assertSame($this->rizky->id, $record->enrollment_id);
        $this->assertSame(self::MONDAY, $record->session_date->format('Y-m-d'));
        $this->assertSame($this->admin->id, $record->recorded_by);
    }

    /**
     * Test pengiriman ulang formulir tidak membuat atau menimpa catatan yang sudah ada.
     */
    public function test_resubmission_does_not_duplicate_or_overwrite(): void
    {
        $payload = $this->payload([$this->dewi->id => 'present', $this->rizky->id => 'present']);
        $this->actingAs($this->admin)->post(route('admin.attendances.students.store'), $payload);

        $this->actingAs($this->admin)
            ->post(route('admin.attendances.students.store'), $this->payload([$this->dewi->id => 'absent', $this->rizky->id => 'absent']))
            ->assertSessionHas('success', 'Absensi 0 murid untuk SMA 12 IPA A pada 19 Oktober 2026 berhasil disimpan. 2 murid dilewati karena sudah tercatat (gunakan koreksi untuk mengubah).');

        $this->assertDatabaseCount('student_attendances', 2);
        $this->assertSame(0, StudentAttendance::where('status', 'absent')->count());
    }

    /**
     * Test constraint unik di database menolak catatan ganda untuk sesi yang sama.
     */
    public function test_database_rejects_duplicate_attendance_for_same_session(): void
    {
        StudentAttendance::factory()->forSession($this->schedule, $this->dewi, self::MONDAY)->create();

        $this->expectException(UniqueConstraintViolationException::class);
        StudentAttendance::factory()->forSession($this->schedule, $this->dewi, self::MONDAY)->create();
    }

    /**
     * Test absensi ditolak untuk jadwal yang tidak berlangsung, tanggal mendatang, sesi yang belum dimulai, atau murid bukan peserta.
     */
    public function test_invalid_sessions_and_participants_are_rejected(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.attendances.students.store'), $this->payload([$this->dewi->id => 'present'], '2026-10-13'))
            ->assertSessionHasErrors(['schedule_id' => 'Jadwal ini tidak berlangsung pada Selasa, 13 Oktober 2026.']);

        $this->actingAs($this->admin)
            ->post(route('admin.attendances.students.store'), $this->payload([$this->dewi->id => 'present'], '2026-10-26'))
            ->assertSessionHasErrors(['session_date' => 'Absensi tidak dapat dicatat untuk tanggal yang akan datang.']);

        $later = Schedule::factory()->for($this->classroom)->at(Weekday::Monday, '13:00', '14:00')->create();
        $this->actingAs($this->admin)
            ->post(route('admin.attendances.students.store'), $this->payload([$this->dewi->id => 'present'], self::MONDAY, $later))
            ->assertSessionHasErrors(['schedule_id' => 'Sesi belum dimulai. Absensi dapat dicatat mulai pukul 13:00.']);

        $outsider = $this->approvedEnrollment('Murid Luar', Classroom::factory()->create());
        $this->actingAs($this->admin)
            ->post(route('admin.attendances.students.store'), $this->payload([$this->dewi->id => 'present', $outsider->id => 'present']))
            ->assertSessionHasErrors(['attendances' => 'Terdapat murid yang bukan peserta aktif kelas ini pada tanggal tersebut.']);

        $inactiveSchedule = Schedule::factory()->for($this->classroom)->at(Weekday::Monday, '06:00', '07:00')->inactive()->create();
        $this->actingAs($this->admin)
            ->post(route('admin.attendances.students.store'), $this->payload([$this->dewi->id => 'present'], self::MONDAY, $inactiveSchedule))
            ->assertSessionHasErrors('schedule_id');

        $this->actingAs($this->admin)
            ->post(route('admin.attendances.students.store'), $this->payload([$this->dewi->id => 'terlambat']))
            ->assertSessionHasErrors('attendances.'.$this->dewi->id.'.status');

        $this->assertDatabaseCount('student_attendances', 0);
    }

    /**
     * Test daftar riwayat menampilkan relasi murid, kelas, jadwal, dan guru.
     */
    public function test_index_lists_attendance_history(): void
    {
        StudentAttendance::factory()->forSession($this->schedule, $this->dewi, '2026-10-12')->create(['status' => AttendanceStatus::Excused, 'notes' => 'Acara keluarga']);

        $this->actingAs($this->admin)
            ->get(route('admin.attendances.students.index'))
            ->assertOk()
            ->assertSee('Absensi Murid')
            ->assertSee('Dewi Lestari')
            ->assertSee('Acara keluarga')
            ->assertSee('SMA 12 IPA A')
            ->assertSee('Guru: Sari Wahyuni')
            ->assertSee('Senin 08:00–10:00')
            ->assertSee('12 Okt 2026')
            ->assertSee('Izin');
    }

    /**
     * Test pencarian dan filter murid, guru, kelas, tanggal, dan status.
     */
    public function test_index_filters(): void
    {
        $otherTeacher = User::factory()->teacher()->create();
        $otherClass = Classroom::factory()->create(['teacher_id' => $otherTeacher->id]);
        $otherSchedule = Schedule::factory()->for($otherClass)->at(Weekday::Monday, '08:00', '10:00')->create();
        $other = $this->approvedEnrollment('Budi Santoso', $otherClass);

        $a = StudentAttendance::factory()->forSession($this->schedule, $this->dewi, '2026-10-05')->create(['status' => AttendanceStatus::Present]);
        $b = StudentAttendance::factory()->forSession($this->schedule, $this->rizky, '2026-10-12')->create(['status' => AttendanceStatus::Absent]);
        $c = StudentAttendance::factory()->forSession($otherSchedule, $other, '2026-10-12')->create(['status' => AttendanceStatus::Present]);

        $ids = fn (array $query): array => $this->actingAs($this->admin)
            ->get(route('admin.attendances.students.index', $query))
            ->viewData('records')->pluck('id')->sort()->values()->all();

        $this->assertSame([$a->id], $ids(['q' => 'dewi']));
        $this->assertSame([$a->id, $b->id], $ids(['kelas' => $this->classroom->id]));
        $this->assertSame([$c->id], $ids(['guru' => $otherTeacher->id]));
        $this->assertSame([$b->id, $c->id], $ids(['dari' => '2026-10-10', 'sampai' => '2026-10-12']));
        $this->assertSame([$b->id], $ids(['status' => 'absent']));
    }

    /**
     * Test rekap jumlah status dihitung dari database sesuai periode, per murid dan per kelas.
     */
    public function test_recap_counts_by_period_student_and_class(): void
    {
        StudentAttendance::factory()->forSession($this->schedule, $this->dewi, '2026-10-05')->create(['status' => AttendanceStatus::Present]);
        StudentAttendance::factory()->forSession($this->schedule, $this->dewi, '2026-10-12')->create(['status' => AttendanceStatus::Sick]);
        StudentAttendance::factory()->forSession($this->schedule, $this->rizky, '2026-10-12')->create(['status' => AttendanceStatus::Absent]);
        StudentAttendance::factory()->forSession($this->schedule, $this->dewi, '2026-09-28')->create(['status' => AttendanceStatus::Excused]);

        $response = $this->actingAs($this->admin)->get(route('admin.attendances.students.index', ['dari' => '2026-10-01']));
        $this->assertSame(['present' => 1, 'excused' => 0, 'sick' => 1, 'absent' => 1], $response->viewData('statusCounts')->all());

        $byStudent = $this->actingAs($this->admin)
            ->get(route('admin.attendances.students.index', ['tampilan' => 'rekap', 'kelompok' => 'murid']))
            ->assertOk()
            ->assertSee('id="attendance-recap"', false)
            ->viewData('recap');
        $dewiRow = $byStudent->firstWhere('group_name', 'Dewi Lestari');
        $this->assertSame([1, 1, 1, 0, 3], [(int) $dewiRow->present_count, (int) $dewiRow->excused_count, (int) $dewiRow->sick_count, (int) $dewiRow->absent_count, (int) $dewiRow->total_count]);

        $byClass = $this->actingAs($this->admin)
            ->get(route('admin.attendances.students.index', ['tampilan' => 'rekap', 'kelompok' => 'kelas', 'dari' => '2026-10-01']))
            ->assertOk()
            ->assertSee('SMA 12 IPA A')
            ->viewData('recap');
        $this->assertSame(3, (int) $byClass->first()->total_count);
    }

    /**
     * Test koreksi menyimpan status sebelumnya, admin, waktu, dan alasan, tanpa menghapus catatan.
     */
    public function test_admin_can_correct_attendance_with_reason(): void
    {
        $record = StudentAttendance::factory()->forSession($this->schedule, $this->dewi, '2026-10-12')->create(['status' => AttendanceStatus::Absent]);

        $this->actingAs($this->admin)
            ->get(route('admin.attendances.students.edit', $record))
            ->assertOk()
            ->assertSee('Koreksi Absensi Murid')
            ->assertSee('Dewi Lestari');

        $this->actingAs($this->admin)
            ->put(route('admin.attendances.students.update', $record), ['status' => 'sick', 'reason' => ''])
            ->assertSessionHasErrors(['reason' => 'Alasan koreksi wajib diisi agar perubahan dapat ditelusuri.']);

        $this->actingAs($this->admin)
            ->put(route('admin.attendances.students.update', $record), ['status' => 'absent', 'reason' => 'Tidak ada perubahan'])
            ->assertSessionHasErrors('status');

        $this->actingAs($this->admin)
            ->put(route('admin.attendances.students.update', $record), ['status' => 'sick', 'reason' => 'Surat dokter diserahkan.'])
            ->assertRedirect(route('admin.attendances.students.index'))
            ->assertSessionHas('success', 'Absensi Dewi Lestari pada 12 Oktober 2026 berhasil dikoreksi menjadi Sakit.');

        $record->refresh();
        $this->assertSame(AttendanceStatus::Sick, $record->status);
        $this->assertSame(AttendanceStatus::Absent, $record->previous_status);
        $this->assertSame($this->admin->id, $record->corrected_by);
        $this->assertNotNull($record->corrected_at);
        $this->assertSame('Surat dokter diserahkan.', $record->correction_reason);

        $this->actingAs($this->admin)
            ->get(route('admin.attendances.students.index'))
            ->assertSee('Dikoreksi dari Alpa');
    }

    /**
     * Test jadwal yang sudah memiliki absensi tidak dapat dihapus.
     */
    public function test_schedule_with_attendance_cannot_be_deleted(): void
    {
        StudentAttendance::factory()->forSession($this->schedule, $this->dewi, '2026-10-12')->create();

        $this->actingAs($this->admin)
            ->delete(route('admin.schedules.destroy', $this->schedule))
            ->assertSessionHas('error');

        $this->assertModelExists($this->schedule);
    }

    /**
     * Test catatan absensi tidak dapat dihapus melalui HTTP.
     */
    public function test_attendance_records_cannot_be_deleted(): void
    {
        $record = StudentAttendance::factory()->forSession($this->schedule, $this->dewi, '2026-10-12')->create();

        $this->actingAs($this->admin)
            ->delete('/admin/absensi/murid/'.$record->id)
            ->assertMethodNotAllowed();

        $this->assertModelExists($record);
    }

    /**
     * Test guru, murid, dan tamu tidak dapat mengakses absensi murid di panel admin.
     */
    public function test_non_admin_users_cannot_manage_student_attendance(): void
    {
        $record = StudentAttendance::factory()->forSession($this->schedule, $this->dewi, '2026-10-12')->create(['status' => AttendanceStatus::Absent]);

        foreach ([$this->classroom->teacher, $this->dewi->student] as $user) {
            $this->actingAs($user)->get(route('admin.attendances.students.index'))->assertForbidden();
            $this->actingAs($user)->get(route('admin.attendances.students.create'))->assertForbidden();
            $this->actingAs($user)->post(route('admin.attendances.students.store'), $this->payload([$this->dewi->id => 'present']))->assertForbidden();
            $this->actingAs($user)->put(route('admin.attendances.students.update', $record), ['status' => 'present', 'reason' => 'Ubah sendiri'])->assertForbidden();
        }

        auth()->logout();
        $this->get(route('admin.attendances.students.index'))->assertRedirect(route('login'));

        $this->assertSame(AttendanceStatus::Absent, $record->fresh()->status);
    }

    /**
     * Test menu Absensi di sidebar aktif.
     */
    public function test_sidebar_links_to_attendance_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.attendances.students.index'));

        $this->assertMatchesRegularExpression(
            '/href="'.preg_quote(route('admin.attendances.students.index'), '/').'"\s+class="admin-nav-item is-active"/',
            $response->getContent()
        );
    }
}
