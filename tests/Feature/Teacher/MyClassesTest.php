<?php

namespace Tests\Feature\Teacher;

use App\AttendanceStatus;
use App\EnrollmentStatus;
use App\Models\Assignment;
use App\Models\Classroom;
use App\Models\Enrollment;
use App\Models\Material;
use App\Models\Schedule;
use App\Models\StudentAttendance;
use App\Models\User;
use App\Weekday;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MyClassesTest extends TestCase
{
    use RefreshDatabase;

    private User $teacher;

    private Classroom $classroom;

    protected function setUp(): void
    {
        parent::setUp();

        $this->teacher = User::factory()->teacher()->create();
        $this->classroom = Classroom::factory()->create(['teacher_id' => $this->teacher->id, 'name' => 'SMA 12 IPA A', 'capacity' => 20]);
    }

    /**
     * Test daftar kelas hanya berisi kelas yang diampu, dengan jumlah murid diterima dan jadwal.
     */
    public function test_index_lists_only_own_classes_with_real_counts(): void
    {
        Enrollment::factory()->inClassroom($this->classroom)->withStatus(EnrollmentStatus::Approved)->count(2)->create();
        Enrollment::factory()->inClassroom($this->classroom)->create();
        Schedule::factory()->for($this->classroom)->at(Weekday::Saturday, '09:00', '10:30')->create(['room' => 'Ruang Prau']);
        Classroom::factory()->create(['name' => 'Kelas Orang Lain']);

        $this->actingAs($this->teacher)
            ->get(route('teacher.classes.index'))
            ->assertOk()
            ->assertSee('SMA 12 IPA A')
            ->assertSee('2 murid terdaftar (kapasitas 20)')
            ->assertSee('Sabtu 09:00–10:30 · Ruang Prau')
            ->assertSee(route('teacher.classes.show', $this->classroom), false)
            ->assertDontSee('Kelas Orang Lain');
    }

    /**
     * Test empty state saat guru belum mengampu kelas.
     */
    public function test_index_empty_state_without_classes(): void
    {
        $this->actingAs(User::factory()->teacher()->create())
            ->get(route('teacher.classes.index'))
            ->assertOk()
            ->assertSee('Anda belum mengampu kelas');
    }

    /**
     * Test detail kelas menampilkan murid diterima, rekap absensi, materi, dan tugas kelas.
     */
    public function test_show_lists_students_attendance_materials_and_assignments(): void
    {
        $approved = Enrollment::factory()->inClassroom($this->classroom)->withStatus(EnrollmentStatus::Approved)->create(['enrolled_on' => '2026-09-01']);
        $approved->student->update(['name' => 'Dewi Lestari']);
        $pending = Enrollment::factory()->inClassroom($this->classroom)->create();
        $pending->student->update(['name' => 'Murid Menunggu']);
        $schedule = Schedule::factory()->for($this->classroom)->at(Weekday::Monday, '08:00', '09:00')->create();
        StudentAttendance::factory()->forSession($schedule, $approved, '2026-10-12')->create(['status' => AttendanceStatus::Sick]);
        Material::factory()->for($this->classroom)->create(['title' => 'Materi Turunan']);
        Assignment::factory()->for($this->classroom)->create(['title' => 'Latihan Limit']);

        $this->actingAs($this->teacher)
            ->get(route('teacher.classes.show', $this->classroom))
            ->assertOk()
            ->assertSee('Dewi Lestari')
            ->assertDontSee('Murid Menunggu')
            ->assertSeeInOrder(['Dewi Lestari', '0', '0', '1', '0'])
            ->assertSee('Materi Turunan')
            ->assertSee('Latihan Limit');
    }

    /**
     * Test guru tidak dapat membuka kelas milik guru lain melalui URL.
     */
    public function test_cannot_open_another_teachers_class(): void
    {
        $this->actingAs($this->teacher)
            ->get(route('teacher.classes.show', Classroom::factory()->create()))
            ->assertForbidden();
    }
}
