<?php

namespace Tests\Feature\Admin;

use App\Models\Classroom;
use App\Models\Program;
use App\Models\Schedule;
use App\Models\User;
use App\Weekday;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ScheduleManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $teacher;

    private Program $program;

    private Classroom $classroom;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->teacher = User::factory()->teacher()->create(['name' => 'Sari Wahyuni']);
        $this->program = Program::factory()->create(['name' => 'Bimbingan Belajar SMA']);
        $this->classroom = Classroom::factory()->for($this->program)->create([
            'teacher_id' => $this->teacher->id,
            'name' => 'SMA 12 IPA A',
        ]);
    }

    /**
     * Data form jadwal yang valid.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return [
            'classroom_id' => (string) $this->classroom->id,
            'day_of_week' => (string) Weekday::Monday->value,
            'start_time' => '08:00',
            'end_time' => '10:00',
            'starts_on' => '',
            'ends_on' => '',
            'room' => 'Ruang 3',
            'is_active' => '1',
            ...$overrides,
        ];
    }

    /**
     * Membuat kelas lain yang diampu oleh guru yang sama.
     */
    private function otherClassroomOfSameTeacher(): Classroom
    {
        return Classroom::factory()->for($this->program)->create([
            'teacher_id' => $this->teacher->id,
            'name' => 'SMA 11 IPA B',
        ]);
    }

    /**
     * Test daftar jadwal menampilkan kelas, program, dan guru melalui relasi database.
     */
    public function test_index_lists_schedules_with_relations(): void
    {
        Schedule::factory()->for($this->classroom)->at(Weekday::Tuesday, '13:00', '15:00')->create(['room' => 'Lab Fisika']);

        $response = $this->actingAs($this->admin)->get(route('admin.schedules.index'));

        $response->assertOk();
        $response->assertViewIs('admin.schedules.index');
        $response->assertSee('Jadwal Belajar');
        $response->assertSee('Tambah Jadwal');
        $response->assertSee('SMA 12 IPA A');
        $response->assertSee('Bimbingan Belajar SMA');
        $response->assertSee('Sari Wahyuni');
        $response->assertSee('Selasa');
        $response->assertSee('13:00');
        $response->assertSee('15:00');
        $response->assertSee('Lab Fisika');
    }

    /**
     * Test daftar jadwal memakai eager loading sehingga jumlah query tidak bertambah per baris.
     */
    public function test_index_avoids_n_plus_one_queries(): void
    {
        Schedule::factory()->for($this->classroom)->at(Weekday::Monday, '08:00', '09:00')->create();

        $countQueries = function (): int {
            $count = 0;
            DB::listen(function () use (&$count): void {
                $count++;
            });
            $this->actingAs($this->admin)->get(route('admin.schedules.index'))->assertOk();

            return $count;
        };

        // Permintaan pemanasan agar cache pengaturan lembaga tidak memengaruhi perbandingan.
        $countQueries();
        $queriesWithOneSchedule = $countQueries();
        foreach ([Weekday::Tuesday, Weekday::Wednesday, Weekday::Thursday, Weekday::Friday] as $day) {
            Schedule::factory()->at($day, '08:00', '09:00')->create();
        }
        $queriesWithFiveSchedules = $countQueries();

        $this->assertSame($queriesWithOneSchedule, $queriesWithFiveSchedules);
    }

    /**
     * Test empty state saat belum ada jadwal, dan saat belum ada kelas.
     */
    public function test_index_shows_empty_states(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.schedules.index'))
            ->assertSee('Belum ada jadwal');

        $this->classroom->delete();

        $this->actingAs($this->admin)
            ->get(route('admin.schedules.index'))
            ->assertSee('Belum ada kelas');
    }

    /**
     * Test pencarian berdasarkan nama kelas maupun nama guru.
     */
    public function test_index_can_search_by_class_or_teacher_name(): void
    {
        $otherTeacher = User::factory()->teacher()->create(['name' => 'Budi Santoso']);
        $otherClassroom = Classroom::factory()->for($this->program)->create(['teacher_id' => $otherTeacher->id, 'name' => 'SMP 9 Reguler']);
        Schedule::factory()->for($this->classroom)->at(Weekday::Monday, '08:00', '10:00')->create();
        Schedule::factory()->for($otherClassroom)->at(Weekday::Monday, '08:00', '10:00')->create();

        $resultClassNames = fn (array $query): array => $this->actingAs($this->admin)
            ->get(route('admin.schedules.index', $query))
            ->viewData('schedules')
            ->map(fn (Schedule $schedule) => $schedule->classroom->name)
            ->all();

        $this->assertSame(['SMP 9 Reguler'], $resultClassNames(['q' => 'SMP 9']));
        $this->assertSame(['SMA 12 IPA A'], $resultClassNames(['q' => 'Sari']));
        $this->assertSame(['SMP 9 Reguler'], $resultClassNames(['q' => 'Budi']));

        $this->actingAs($this->admin)
            ->get(route('admin.schedules.index', ['q' => 'tidak-ada']))
            ->assertSee('Jadwal tidak ditemukan');
    }

    /**
     * Test filter kelas, guru, hari, dan status.
     */
    public function test_index_can_filter_by_class_teacher_day_and_status(): void
    {
        $otherTeacher = User::factory()->teacher()->create();
        $otherClassroom = Classroom::factory()->for($this->program)->create(['teacher_id' => $otherTeacher->id, 'name' => 'Kelas Guru Lain']);
        Schedule::factory()->for($this->classroom)->at(Weekday::Monday, '08:00', '10:00')->create(['room' => 'Ruang Senin']);
        Schedule::factory()->for($this->classroom)->at(Weekday::Friday, '08:00', '10:00')->inactive()->create(['room' => 'Ruang Jumat']);
        Schedule::factory()->for($otherClassroom)->at(Weekday::Monday, '13:00', '14:00')->create(['room' => 'Ruang Guru Lain']);

        $this->actingAs($this->admin)
            ->get(route('admin.schedules.index', ['kelas' => $this->classroom->id]))
            ->assertSee('Ruang Senin')->assertSee('Ruang Jumat')->assertDontSee('Ruang Guru Lain');

        $this->actingAs($this->admin)
            ->get(route('admin.schedules.index', ['guru' => $otherTeacher->id]))
            ->assertSee('Ruang Guru Lain')->assertDontSee('Ruang Senin');

        $this->actingAs($this->admin)
            ->get(route('admin.schedules.index', ['hari' => Weekday::Friday->value]))
            ->assertSee('Ruang Jumat')->assertDontSee('Ruang Senin');

        $this->actingAs($this->admin)
            ->get(route('admin.schedules.index', ['status' => 'aktif']))
            ->assertSee('Ruang Senin')->assertDontSee('Ruang Jumat');

        $this->actingAs($this->admin)
            ->get(route('admin.schedules.index', ['hari' => 8]))
            ->assertSessionHasErrors('hari');
    }

    /**
     * Test pagination membagi jadwal per 10 data dan mempertahankan filter.
     */
    public function test_index_is_paginated(): void
    {
        foreach (range(7, 18) as $hour) {
            Schedule::factory()->for($this->classroom)->at(Weekday::Saturday, sprintf('%02d:00', $hour), sprintf('%02d:00', $hour + 1))->create();
        }

        $firstPage = $this->actingAs($this->admin)->get(route('admin.schedules.index'));
        $this->assertCount(10, $firstPage->viewData('schedules'));
        $firstPage->assertSee('Menampilkan 1–10 dari 12 data');

        $secondPage = $this->actingAs($this->admin)->get(route('admin.schedules.index', ['page' => 2]));
        $this->assertCount(2, $secondPage->viewData('schedules'));

        $this->actingAs($this->admin)
            ->get(route('admin.schedules.index', ['hari' => 6]))
            ->assertSee('hari=6&amp;page=2', false);
    }

    /**
     * Test tampilan kalender mingguan mengelompokkan jadwal per hari dari database.
     */
    public function test_calendar_view_groups_schedules_by_day(): void
    {
        Schedule::factory()->for($this->classroom)->at(Weekday::Wednesday, '15:30', '17:00')->create(['room' => 'Ruang Kalender']);

        $response = $this->actingAs($this->admin)->get(route('admin.schedules.index', ['tampilan' => 'kalender']));

        $response->assertOk();
        $response->assertSee('id="schedule-calendar"', false);
        $response->assertSeeInOrder(['Senin', 'Selasa', 'Rabu', '15:30–17:00', 'SMA 12 IPA A', 'Sari Wahyuni', 'Ruang Kalender', 'Kamis']);
        $response->assertSee('Menampilkan 1 jadwal dalam seminggu.');
    }

    /**
     * Test admin dapat menambahkan jadwal.
     */
    public function test_admin_can_create_schedule(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.schedules.store'), $this->validPayload([
            'starts_on' => '2026-11-02',
            'ends_on' => '2027-06-28',
        ]));

        $response->assertRedirect(route('admin.schedules.index'));
        $response->assertSessionHas('success', 'Jadwal SMA 12 IPA A pada Senin 08:00–10:00 berhasil ditambahkan.');

        $schedule = Schedule::firstOrFail();
        $this->assertTrue($schedule->classroom->is($this->classroom));
        $this->assertSame(Weekday::Monday, $schedule->day_of_week);
        $this->assertSame('08:00:00', $schedule->start_time);
        $this->assertSame('10:00:00', $schedule->end_time);
        $this->assertSame('2026-11-02', $schedule->starts_on->format('Y-m-d'));
        $this->assertSame('2027-06-28', $schedule->ends_on->format('Y-m-d'));
        $this->assertSame('Ruang 3', $schedule->room);
        $this->assertTrue($schedule->is_active);
        $this->assertDatabaseHas('schedules', ['starts_on' => '2026-11-02', 'ends_on' => '2027-06-28']);
    }

    /**
     * Test form tambah memilih kelas dari parameter dan menampilkan guru dari relasi kelas.
     */
    public function test_create_page_preselects_class_and_shows_teacher(): void
    {
        Classroom::factory()->for($this->program)->inactive()->create(['name' => 'Kelas Ditutup']);

        $this->actingAs($this->admin)
            ->get(route('admin.schedules.create', ['kelas' => $this->classroom->id]))
            ->assertOk()
            ->assertSee('SMA 12 IPA A — Bimbingan Belajar SMA — Guru: Sari Wahyuni')
            ->assertSee('value="'.$this->classroom->id.'" selected', false)
            ->assertDontSee('Kelas Ditutup')
            ->assertDontSee('id="alert-no-classroom"', false);
    }

    /**
     * Test form tambah memberi tahu admin jika belum ada kelas aktif.
     */
    public function test_create_page_warns_when_no_class_exists(): void
    {
        $this->classroom->update(['is_active' => false]);

        $this->actingAs($this->admin)
            ->get(route('admin.schedules.create'))
            ->assertOk()
            ->assertSee('id="alert-no-classroom"', false)
            ->assertSee('Tambahkan kelas terlebih dahulu');
    }

    /**
     * Test validasi field wajib, jam, dan tanggal berlaku.
     */
    public function test_create_schedule_validation(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.schedules.store'), [])
            ->assertSessionHasErrors([
                'classroom_id' => 'Silakan pilih kelas.',
                'day_of_week' => 'Silakan pilih hari pelaksanaan.',
                'start_time' => 'Jam mulai wajib diisi.',
                'end_time' => 'Jam selesai wajib diisi.',
                'is_active',
            ]);

        $this->actingAs($this->admin)
            ->post(route('admin.schedules.store'), $this->validPayload(['start_time' => '10:00', 'end_time' => '08:00']))
            ->assertSessionHasErrors(['end_time' => 'Jam selesai harus lebih akhir daripada jam mulai.']);

        $this->actingAs($this->admin)
            ->post(route('admin.schedules.store'), $this->validPayload(['start_time' => '09:00', 'end_time' => '09:00']))
            ->assertSessionHasErrors(['end_time' => 'Jam selesai harus lebih akhir daripada jam mulai.']);

        $this->actingAs($this->admin)
            ->post(route('admin.schedules.store'), $this->validPayload(['starts_on' => '2026-12-01', 'ends_on' => '2026-11-30']))
            ->assertSessionHasErrors(['ends_on' => 'Tanggal berakhir tidak boleh lebih awal daripada tanggal mulai berlaku.']);

        $this->actingAs($this->admin)
            ->post(route('admin.schedules.store'), $this->validPayload(['start_time' => '25:00', 'day_of_week' => '8', 'classroom_id' => '99999']))
            ->assertSessionHasErrors(['start_time', 'day_of_week', 'classroom_id']);

        $this->assertDatabaseCount('schedules', 0);
    }

    /**
     * Test kelas nonaktif tidak dapat dipilih untuk jadwal baru.
     */
    public function test_inactive_class_cannot_be_scheduled(): void
    {
        $inactiveClassroom = Classroom::factory()->for($this->program)->inactive()->create();

        $this->actingAs($this->admin)
            ->post(route('admin.schedules.store'), $this->validPayload(['classroom_id' => (string) $inactiveClassroom->id]))
            ->assertSessionHasErrors(['classroom_id' => 'Kelas tidak ditemukan atau sudah tidak aktif.']);
    }

    /**
     * Test nilai input sebelumnya tetap tampil saat validasi gagal.
     */
    public function test_old_input_is_kept_after_validation_failure(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.schedules.create'))
            ->followingRedirects()
            ->post(route('admin.schedules.store'), $this->validPayload(['room' => 'Ruang Gagal', 'start_time' => '11:00', 'end_time' => '10:00']))
            ->assertOk()
            ->assertSee('value="Ruang Gagal"', false)
            ->assertSee('value="11:00"', false)
            ->assertSee('Jam selesai harus lebih akhir daripada jam mulai.');
    }

    /**
     * Test bentrok jadwal kelas: kelas yang sama tidak boleh punya dua jadwal tumpang tindih.
     */
    public function test_class_schedule_conflict_is_rejected(): void
    {
        Schedule::factory()->for($this->classroom)->at(Weekday::Monday, '08:00', '10:00')->create();

        $this->actingAs($this->admin)
            ->post(route('admin.schedules.store'), $this->validPayload(['start_time' => '09:00', 'end_time' => '11:00']))
            ->assertSessionHasErrors(['start_time' => 'Jadwal bentrok: kelas SMA 12 IPA A sudah memiliki jadwal Senin 08:00–10:00.']);

        $this->assertDatabaseCount('schedules', 1);
    }

    /**
     * Test bentrok jadwal guru: guru yang sama tidak boleh mengajar dua kelas pada waktu yang tumpang tindih.
     */
    public function test_teacher_schedule_conflict_is_rejected(): void
    {
        Schedule::factory()->for($this->classroom)->at(Weekday::Monday, '08:00', '10:00')->create();
        $otherClassroom = $this->otherClassroomOfSameTeacher();

        $this->actingAs($this->admin)
            ->post(route('admin.schedules.store'), $this->validPayload([
                'classroom_id' => (string) $otherClassroom->id,
                'start_time' => '07:30',
                'end_time' => '08:30',
            ]))
            ->assertSessionHasErrors(['start_time' => 'Jadwal bentrok: Sari Wahyuni sudah mengajar kelas SMA 12 IPA A pada Senin 08:00–10:00.']);

        $this->assertDatabaseCount('schedules', 1);
    }

    /**
     * Test berbagai situasi yang tidak dianggap bentrok.
     */
    public function test_non_overlapping_schedules_are_allowed(): void
    {
        Schedule::factory()->for($this->classroom)->at(Weekday::Monday, '08:00', '10:00')->create();
        $otherClassroom = $this->otherClassroomOfSameTeacher();
        $differentTeacherClassroom = Classroom::factory()->for($this->program)->create();

        $allowedCases = [
            'selesai tepat saat jadwal lain dimulai' => ['start_time' => '10:00', 'end_time' => '12:00'],
            'berakhir tepat saat jadwal lain dimulai' => ['classroom_id' => (string) $otherClassroom->id, 'start_time' => '06:00', 'end_time' => '08:00'],
            'hari berbeda' => ['day_of_week' => (string) Weekday::Tuesday->value],
            'guru berbeda di jam yang sama' => ['classroom_id' => (string) $differentTeacherClassroom->id],
            'jadwal baru tidak aktif' => ['is_active' => '0'],
        ];

        foreach ($allowedCases as $case => $override) {
            $this->actingAs($this->admin)
                ->post(route('admin.schedules.store'), $this->validPayload($override))
                ->assertSessionHasNoErrors();

            $this->assertDatabaseCount('schedules', 2);
            Schedule::query()->latest('id')->first()->delete();
        }
    }

    /**
     * Test jadwal tidak aktif yang sudah tersimpan tidak dihitung sebagai bentrok.
     */
    public function test_inactive_existing_schedule_does_not_conflict(): void
    {
        Schedule::factory()->for($this->classroom)->at(Weekday::Monday, '08:00', '10:00')->inactive()->create();

        $this->actingAs($this->admin)
            ->post(route('admin.schedules.store'), $this->validPayload())
            ->assertSessionHasNoErrors();
    }

    /**
     * Test pemeriksaan bentrok memperhatikan masa berlaku jadwal.
     */
    public function test_conflict_check_respects_validity_dates(): void
    {
        Schedule::factory()->for($this->classroom)->at(Weekday::Monday, '08:00', '10:00')->create([
            'starts_on' => '2026-07-01',
            'ends_on' => '2026-12-31',
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.schedules.store'), $this->validPayload(['starts_on' => '2027-01-01']))
            ->assertSessionHasNoErrors();

        $this->actingAs($this->admin)
            ->post(route('admin.schedules.store'), $this->validPayload(['ends_on' => '2026-06-30']))
            ->assertSessionHasNoErrors();

        $this->actingAs($this->admin)
            ->post(route('admin.schedules.store'), $this->validPayload(['starts_on' => '2026-12-31', 'ends_on' => '2027-03-31']))
            ->assertSessionHasErrors('start_time');

        $this->actingAs($this->admin)
            ->post(route('admin.schedules.store'), $this->validPayload())
            ->assertSessionHasErrors('start_time');

        $this->assertDatabaseCount('schedules', 3);
    }

    /**
     * Test halaman detail jadwal menampilkan informasi dan relasinya.
     */
    public function test_admin_can_view_schedule_detail(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-10-19 07:00:00'));

        $schedule = Schedule::factory()->for($this->classroom)->at(Weekday::Monday, '08:00', '10:00')->create([
            'room' => 'Ruang Detail',
            'starts_on' => '2026-09-01',
            'ends_on' => '2027-06-30',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.schedules.show', $schedule))
            ->assertOk()
            ->assertSee('Senin, 08:00–10:00')
            ->assertSee('SMA 12 IPA A')
            ->assertSee('Bimbingan Belajar SMA')
            ->assertSee('Sari Wahyuni')
            ->assertSee('Ruang Detail')
            ->assertSee('01 September 2026')
            ->assertSee('30 Juni 2027')
            ->assertSee('19 Oktober 2026 · 08:00')
            ->assertSee('Kembali ke Daftar Jadwal')
            ->assertSee('aman dihapus');
    }

    /**
     * Test form edit terisi data jadwal yang dipilih.
     */
    public function test_edit_page_is_prefilled(): void
    {
        $schedule = Schedule::factory()->for($this->classroom)->at(Weekday::Thursday, '16:00', '17:30')->create([
            'starts_on' => '2026-11-05',
            'room' => 'Ruang Edit',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.schedules.edit', $schedule))
            ->assertOk()
            ->assertSee('value="16:00"', false)
            ->assertSee('value="17:30"', false)
            ->assertSee('value="2026-11-05"', false)
            ->assertSee('value="Ruang Edit"', false)
            ->assertSee('value="4" selected', false);
    }

    /**
     * Test admin dapat memperbarui jadwal tanpa mengubah jadwal lain.
     */
    public function test_admin_can_update_schedule(): void
    {
        $schedule = Schedule::factory()->for($this->classroom)->at(Weekday::Monday, '08:00', '10:00')->create();
        $otherSchedule = Schedule::factory()->for($this->classroom)->at(Weekday::Friday, '08:00', '10:00')->create();

        $response = $this->actingAs($this->admin)->put(route('admin.schedules.update', $schedule), $this->validPayload([
            'day_of_week' => (string) Weekday::Wednesday->value,
            'start_time' => '14:00',
            'end_time' => '16:00',
            'room' => 'Ruang Baru',
        ]));

        $response->assertRedirect(route('admin.schedules.show', $schedule));
        $response->assertSessionHas('success', 'Jadwal berhasil diperbarui.');

        $schedule->refresh();
        $this->assertSame(Weekday::Wednesday, $schedule->day_of_week);
        $this->assertSame('14:00:00', $schedule->start_time);
        $this->assertSame('Ruang Baru', $schedule->room);
        $this->assertSame(Weekday::Friday, $otherSchedule->fresh()->day_of_week);
        $this->assertSame('08:00:00', $otherSchedule->fresh()->start_time);
    }

    /**
     * Test jadwal yang diedit tidak dianggap bentrok dengan dirinya sendiri, tetapi tetap dicek terhadap jadwal lain.
     */
    public function test_update_excludes_current_schedule_from_conflict_check(): void
    {
        $schedule = Schedule::factory()->for($this->classroom)->at(Weekday::Monday, '08:00', '10:00')->create();
        Schedule::factory()->for($this->classroom)->at(Weekday::Monday, '13:00', '15:00')->create();

        $this->actingAs($this->admin)
            ->put(route('admin.schedules.update', $schedule), $this->validPayload(['start_time' => '08:30', 'end_time' => '10:30']))
            ->assertSessionHasNoErrors();

        $this->actingAs($this->admin)
            ->put(route('admin.schedules.update', $schedule), $this->validPayload(['start_time' => '12:00', 'end_time' => '14:00']))
            ->assertSessionHasErrors(['start_time' => 'Jadwal bentrok: kelas SMA 12 IPA A sudah memiliki jadwal Senin 13:00–15:00.']);

        $this->assertSame('08:30:00', $schedule->fresh()->start_time);
    }

    /**
     * Test status jadwal dapat dinonaktifkan dan diaktifkan kembali.
     */
    public function test_admin_can_toggle_schedule_status(): void
    {
        $schedule = Schedule::factory()->for($this->classroom)->at(Weekday::Monday, '08:00', '10:00')->create();

        $this->actingAs($this->admin)
            ->get(route('admin.schedules.index'))
            ->assertSee('data-confirm-title="Nonaktifkan Jadwal"', false);

        $this->actingAs($this->admin)
            ->from(route('admin.schedules.index'))
            ->patch(route('admin.schedules.toggle-status', $schedule))
            ->assertRedirect(route('admin.schedules.index'))
            ->assertSessionHas('success', 'Jadwal SMA 12 IPA A pada Senin 08:00–10:00 berhasil dinonaktifkan.');
        $this->assertFalse($schedule->fresh()->is_active);
        $this->assertModelExists($schedule);

        $this->actingAs($this->admin)
            ->patch(route('admin.schedules.toggle-status', $schedule))
            ->assertSessionHas('success', 'Jadwal SMA 12 IPA A pada Senin 08:00–10:00 berhasil diaktifkan.');
        $this->assertTrue($schedule->fresh()->is_active);
    }

    /**
     * Test jadwal nonaktif tidak dapat diaktifkan kembali jika kini bentrok dengan jadwal aktif lain.
     */
    public function test_reactivation_is_refused_when_it_would_conflict(): void
    {
        $inactiveSchedule = Schedule::factory()->for($this->classroom)->at(Weekday::Monday, '08:00', '10:00')->inactive()->create();
        Schedule::factory()->for($this->otherClassroomOfSameTeacher())->at(Weekday::Monday, '09:00', '11:00')->create();

        $this->actingAs($this->admin)
            ->patch(route('admin.schedules.toggle-status', $inactiveSchedule))
            ->assertSessionHas('error', 'Jadwal tidak dapat diaktifkan. Jadwal bentrok: Sari Wahyuni sudah mengajar kelas SMA 11 IPA B pada Senin 09:00–11:00.');

        $this->assertFalse($inactiveSchedule->fresh()->is_active);
    }

    /**
     * Test jadwal yang belum memiliki riwayat dapat dihapus melalui HTTP DELETE.
     */
    public function test_schedule_without_history_can_be_deleted(): void
    {
        $schedule = Schedule::factory()->for($this->classroom)->create();

        $this->actingAs($this->admin)
            ->delete(route('admin.schedules.destroy', $schedule))
            ->assertRedirect(route('admin.schedules.index'))
            ->assertSessionHas('success');

        $this->assertModelMissing($schedule);
        $this->assertModelExists($this->classroom);
    }

    /**
     * Test jadwal yang sudah memiliki riwayat tidak dihapus dan admin diarahkan untuk menonaktifkannya.
     */
    public function test_schedule_with_history_cannot_be_deleted(): void
    {
        $schedule = Schedule::factory()->for($this->classroom)->create();

        $this->app->bind(Schedule::class, fn () => new class extends Schedule
        {
            protected $table = 'schedules';

            public function hasDependentRecords(): bool
            {
                return true;
            }
        });

        $this->actingAs($this->admin)
            ->get(route('admin.schedules.show', $schedule))
            ->assertOk()->assertSee('tidak dapat dihapus')
            ->assertDontSee('id="btn-delete-schedule"', false);

        $this->actingAs($this->admin)
            ->delete(route('admin.schedules.destroy', $schedule))
            ->assertSessionHas('error');

        $this->assertModelExists($schedule);
    }

    /**
     * Test penghapusan wajib memakai HTTP DELETE.
     */
    public function test_delete_requires_delete_method(): void
    {
        $schedule = Schedule::factory()->for($this->classroom)->create();

        $this->actingAs($this->admin)
            ->post('/admin/jadwal/'.$schedule->id)
            ->assertMethodNotAllowed();

        $this->assertModelExists($schedule);
    }

    /**
     * Test guru, murid, dan tamu tidak dapat mengakses modul jadwal admin.
     */
    public function test_non_admin_users_cannot_manage_schedules(): void
    {
        $schedule = Schedule::factory()->for($this->classroom)->create();

        foreach ([$this->teacher, User::factory()->student()->create()] as $user) {
            $this->actingAs($user)->get(route('admin.schedules.index'))->assertForbidden();
            $this->actingAs($user)->get(route('admin.schedules.create'))->assertForbidden();
            $this->actingAs($user)->post(route('admin.schedules.store'), $this->validPayload())->assertForbidden();
            $this->actingAs($user)->get(route('admin.schedules.show', $schedule))->assertForbidden();
            $this->actingAs($user)->get(route('admin.schedules.edit', $schedule))->assertForbidden();
            $this->actingAs($user)->put(route('admin.schedules.update', $schedule), $this->validPayload())->assertForbidden();
            $this->actingAs($user)->patch(route('admin.schedules.toggle-status', $schedule))->assertForbidden();
            $this->actingAs($user)->delete(route('admin.schedules.destroy', $schedule))->assertForbidden();
        }

        auth()->logout();
        $this->get(route('admin.schedules.index'))->assertRedirect(route('login'));

        $this->assertModelExists($schedule);
        $this->assertDatabaseCount('schedules', 1);
    }

    /**
     * Test integrasi Data Kelas: mengganti guru kelas ditolak jika membuat guru baru bentrok jadwal.
     */
    public function test_changing_class_teacher_checks_schedule_conflicts(): void
    {
        $newTeacher = User::factory()->teacher()->create(['name' => 'Budi Santoso']);
        $newTeachersClass = Classroom::factory()->for($this->program)->create(['teacher_id' => $newTeacher->id, 'name' => 'Kelas Budi']);
        Schedule::factory()->for($newTeachersClass)->at(Weekday::Monday, '09:00', '11:00')->create();
        Schedule::factory()->for($this->classroom)->at(Weekday::Monday, '08:00', '10:00')->create();

        $payload = [
            'name' => $this->classroom->name,
            'code' => $this->classroom->code,
            'program_id' => (string) $this->program->id,
            'teacher_id' => (string) $newTeacher->id,
            'capacity' => '20',
            'learning_mode' => 'offline',
            'is_active' => '1',
        ];

        $this->actingAs($this->admin)
            ->put(route('admin.classes.update', $this->classroom), $payload)
            ->assertSessionHasErrors(['teacher_id' => 'Guru ini sudah mengajar kelas Kelas Budi pada Senin 09:00–11:00, bentrok dengan jadwal kelas ini pada Senin 08:00–10:00.']);
        $this->assertSame($this->teacher->id, $this->classroom->fresh()->teacher_id);

        $freeTeacher = User::factory()->teacher()->create();
        $this->actingAs($this->admin)
            ->put(route('admin.classes.update', $this->classroom), [...$payload, 'teacher_id' => (string) $freeTeacher->id])
            ->assertSessionHasNoErrors();
        $this->assertSame($freeTeacher->id, $this->classroom->fresh()->teacher_id);
    }

    /**
     * Test integrasi Data Kelas: kelas yang sudah memiliki jadwal tidak dapat dihapus, dan jadwal tampil di detail kelas.
     */
    public function test_class_with_schedules_is_protected_and_lists_schedules(): void
    {
        Schedule::factory()->for($this->classroom)->at(Weekday::Saturday, '09:00', '11:00')->create(['room' => 'Ruang Sabtu']);

        $this->actingAs($this->admin)
            ->get(route('admin.classes.show', $this->classroom))
            ->assertSee('Jadwal Kelas')
            ->assertSee('Sabtu')
            ->assertSee('09:00–11:00')
            ->assertSee('Ruang Sabtu')
            ->assertOk()->assertSee('tidak dapat dihapus');

        $this->actingAs($this->admin)
            ->delete(route('admin.classes.destroy', $this->classroom))
            ->assertSessionHas('error');

        $this->assertModelExists($this->classroom);
    }

    /**
     * Test pertemuan berikutnya dihitung dari hari, jam, dan masa berlaku jadwal.
     */
    public function test_next_occurrence_respects_day_time_and_validity(): void
    {
        $mondayMorning = CarbonImmutable::parse('2026-10-19 07:00:00');

        $monday = Schedule::factory()->for($this->classroom)->at(Weekday::Monday, '08:00', '10:00')->make();
        $this->assertSame('2026-10-19 08:00', $monday->nextOccurrence($mondayMorning)->format('Y-m-d H:i'));
        $this->assertSame('2026-10-26 08:00', $monday->nextOccurrence($mondayMorning->setTime(9, 0))->format('Y-m-d H:i'));

        $sunday = Schedule::factory()->for($this->classroom)->at(Weekday::Sunday, '10:00', '12:00')->make();
        $this->assertSame('2026-10-25 10:00', $sunday->nextOccurrence($mondayMorning)->format('Y-m-d H:i'));

        $startsLater = Schedule::factory()->for($this->classroom)->at(Weekday::Wednesday, '13:00', '14:00')->make(['starts_on' => '2026-11-10']);
        $this->assertSame('2026-11-11 13:00', $startsLater->nextOccurrence($mondayMorning)->format('Y-m-d H:i'));

        $ended = Schedule::factory()->for($this->classroom)->at(Weekday::Friday, '13:00', '14:00')->make(['ends_on' => '2026-10-18']);
        $this->assertNull($ended->nextOccurrence($mondayMorning));

        $inactive = Schedule::factory()->for($this->classroom)->at(Weekday::Monday, '08:00', '10:00')->inactive()->make();
        $this->assertNull($inactive->nextOccurrence($mondayMorning));
    }

    /**
     * Test dashboard admin menampilkan pertemuan terdekat dari jadwal aktif kelas aktif.
     */
    public function test_dashboard_shows_upcoming_schedules(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-10-19 07:00:00'));

        Schedule::factory()->for($this->classroom)->at(Weekday::Tuesday, '13:00', '15:00')->create(['room' => 'Ruang 5']);
        Schedule::factory()->for($this->classroom)->at(Weekday::Monday, '08:00', '10:00')->create(['room' => 'Ruang 1']);
        Schedule::factory()->for($this->classroom)->at(Weekday::Wednesday, '08:00', '10:00')->inactive()->create(['room' => 'Ruang Nonaktif']);
        $inactiveClassroom = Classroom::factory()->for($this->program)->inactive()->create(['name' => 'Kelas Tutup']);
        Schedule::factory()->for($inactiveClassroom)->at(Weekday::Monday, '13:00', '14:00')->create();

        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSeeInOrder(['SMA 12 IPA A · Ruang 1', '19 Okt 2026, 08:00', 'SMA 12 IPA A · Ruang 5', '20 Okt 2026, 13:00']);
        $response->assertDontSee('Ruang Nonaktif');
        $response->assertDontSee('Kelas Tutup');
    }

    /**
     * Test menu Jadwal di sidebar aktif dan mengarah ke halaman jadwal belajar.
     */
    public function test_sidebar_links_to_schedule_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.schedules.index'));

        $this->assertMatchesRegularExpression(
            '/href="'.preg_quote(route('admin.schedules.index'), '/').'"\s+class="admin-nav-item is-active"/',
            $response->getContent()
        );
    }
}
