<?php

namespace Tests\Feature\Admin;

use App\EnrollmentStatus;
use App\Models\Classroom;
use App\Models\Enrollment;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EnrollmentManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $student;

    private Program $program;

    private Classroom $classroom;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->student = User::factory()->student()->create(['name' => 'Dewi Lestari', 'email' => 'dewi@example.com']);
        $this->program = Program::factory()->create(['name' => 'Bimbingan Belajar SMA']);
        $this->classroom = Classroom::factory()->for($this->program)->create(['name' => 'SMA 12 IPA A', 'capacity' => 2]);
    }

    /**
     * Data form pendaftaran yang valid.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return [
            'student_id' => (string) $this->student->id,
            'program_id' => (string) $this->program->id,
            'classroom_id' => (string) $this->classroom->id,
            'enrolled_on' => now()->format('Y-m-d'),
            'admin_notes' => 'Daftar melalui konsultasi WhatsApp.',
            ...$overrides,
        ];
    }

    /**
     * Membuat pendaftaran aktif di kelas utama untuk murid lain.
     */
    private function occupySeat(EnrollmentStatus $status = EnrollmentStatus::Approved): Enrollment
    {
        return Enrollment::factory()->inClassroom($this->classroom)->withStatus($status)->create();
    }

    /**
     * Test daftar pendaftaran menampilkan murid, program, dan kelas melalui relasi database.
     */
    public function test_index_lists_enrollments_with_relations(): void
    {
        Enrollment::factory()->inClassroom($this->classroom)->create([
            'student_id' => $this->student->id,
            'enrolled_on' => '2026-10-01',
        ]);
        Enrollment::factory()->for(Program::factory()->state(['name' => 'Program Tanpa Kelas']))->withStatus(EnrollmentStatus::Approved)->create();

        $response = $this->actingAs($this->admin)->get(route('admin.enrollments.index'));

        $response->assertOk();
        $response->assertViewIs('admin.enrollments.index');
        $response->assertSee('Pendaftaran Murid');
        $response->assertSee('Tambah Pendaftaran');
        $response->assertSee('Dewi Lestari');
        $response->assertSee('dewi@example.com');
        $response->assertSee('Bimbingan Belajar SMA');
        $response->assertSee('SMA 12 IPA A');
        $response->assertSee('01 Okt 2026');
        $response->assertSee('Menunggu Verifikasi');
        $response->assertSee('Program Tanpa Kelas');
        $response->assertSee('Belum ditentukan');
        $response->assertSee('Diterima');
    }

    /**
     * Test daftar pendaftaran memakai eager loading sehingga jumlah query tidak bertambah per baris.
     */
    public function test_index_avoids_n_plus_one_queries(): void
    {
        Enrollment::factory()->inClassroom($this->classroom)->create();

        $countQueries = function (): int {
            $count = 0;
            DB::listen(function () use (&$count): void {
                $count++;
            });
            $this->actingAs($this->admin)->get(route('admin.enrollments.index'))->assertOk();

            return $count;
        };

        // Permintaan pemanasan agar cache pengaturan lembaga tidak memengaruhi perbandingan.
        $countQueries();
        $queriesWithOne = $countQueries();
        Enrollment::factory()->count(5)->create();
        $queriesWithSix = $countQueries();

        $this->assertSame($queriesWithOne, $queriesWithSix);
    }

    /**
     * Test empty state saat belum ada pendaftaran.
     */
    public function test_index_shows_empty_state(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.enrollments.index'))
            ->assertOk()
            ->assertSee('Belum ada pendaftaran');
    }

    /**
     * Test pencarian berdasarkan nama atau email murid.
     */
    public function test_index_can_search_by_student_name_or_email(): void
    {
        Enrollment::factory()->create(['student_id' => $this->student->id]);
        Enrollment::factory()->for(User::factory()->student()->state(['name' => 'Rizky Pratama', 'email' => 'rizky@contoh.id']), 'student')->create();

        $names = fn (array $query): array => $this->actingAs($this->admin)
            ->get(route('admin.enrollments.index', $query))
            ->viewData('enrollments')
            ->map(fn (Enrollment $enrollment) => $enrollment->student->name)
            ->all();

        $this->assertSame(['Dewi Lestari'], $names(['q' => 'dewi']));
        $this->assertSame(['Rizky Pratama'], $names(['q' => 'contoh.id']));

        $this->actingAs($this->admin)
            ->get(route('admin.enrollments.index', ['q' => 'tidak-ada']))
            ->assertSee('Pendaftaran tidak ditemukan');
    }

    /**
     * Test filter program, kelas, status, dan rentang tanggal pendaftaran.
     */
    public function test_index_can_filter_by_program_class_status_and_date(): void
    {
        $otherProgram = Program::factory()->create();
        $inClass = Enrollment::factory()->inClassroom($this->classroom)->create(['enrolled_on' => '2026-09-10']);
        $approved = Enrollment::factory()->for($otherProgram)->withStatus(EnrollmentStatus::Approved)->create(['enrolled_on' => '2026-10-05']);
        $cancelled = Enrollment::factory()->for($otherProgram)->withStatus(EnrollmentStatus::Cancelled)->create(['enrolled_on' => '2026-10-15']);

        $ids = fn (array $query): array => $this->actingAs($this->admin)
            ->get(route('admin.enrollments.index', $query))
            ->viewData('enrollments')
            ->pluck('id')
            ->sort()
            ->values()
            ->all();

        $this->assertSame([$inClass->id], $ids(['program' => $this->program->id]));
        $this->assertSame([$inClass->id], $ids(['kelas' => $this->classroom->id]));
        $this->assertSame([$cancelled->id], $ids(['status' => 'cancelled']));
        $this->assertSame([$approved->id, $cancelled->id], $ids(['dari' => '2026-10-01']));
        $this->assertSame([$inClass->id, $approved->id], $ids(['sampai' => '2026-10-05']));
        $this->assertSame([$approved->id], $ids(['dari' => '2026-10-01', 'sampai' => '2026-10-10']));

        $this->actingAs($this->admin)
            ->get(route('admin.enrollments.index', ['dari' => '2026-10-10', 'sampai' => '2026-10-01']))
            ->assertSessionHasErrors('sampai');
    }

    /**
     * Test pagination membagi daftar pendaftaran per 10 data dan mempertahankan filter.
     */
    public function test_index_is_paginated(): void
    {
        Enrollment::factory()->for($this->program)->count(12)->create();

        $firstPage = $this->actingAs($this->admin)->get(route('admin.enrollments.index'));
        $this->assertCount(10, $firstPage->viewData('enrollments'));
        $firstPage->assertSee('Menampilkan 1–10 dari 12 data');

        $secondPage = $this->actingAs($this->admin)->get(route('admin.enrollments.index', ['page' => 2]));
        $this->assertCount(2, $secondPage->viewData('enrollments'));

        $this->actingAs($this->admin)
            ->get(route('admin.enrollments.index', ['status' => 'pending']))
            ->assertSee('status=pending&amp;page=2', false);
    }

    /**
     * Test form tambah hanya menampilkan murid aktif, program aktif, dan kelas aktif beserta keterisiannya.
     */
    public function test_create_page_lists_valid_options(): void
    {
        $deletedStudent = User::factory()->student()->create(['name' => 'Murid Terhapus']);
        $deletedStudent->delete();
        User::factory()->teacher()->create(['name' => 'Guru Bukan Murid']);
        Program::factory()->inactive()->create(['name' => 'Program Ditutup']);
        Classroom::factory()->for($this->program)->inactive()->create(['name' => 'Kelas Ditutup']);
        $this->occupySeat();

        $this->actingAs($this->admin)
            ->get(route('admin.enrollments.create', ['murid' => $this->student->id]))
            ->assertOk()
            ->assertSee('Dewi Lestari — dewi@example.com')
            ->assertSee('value="'.$this->student->id.'" selected', false)
            ->assertDontSee('Murid Terhapus')
            ->assertDontSee('Guru Bukan Murid')
            ->assertSee('Bimbingan Belajar SMA')
            ->assertDontSee('Program Ditutup')
            ->assertSee('SMA 12 IPA A — terisi 1/2')
            ->assertDontSee('Kelas Ditutup')
            ->assertDontSee('id="alert-missing-data"', false);
    }

    /**
     * Test form tambah memberi tahu admin jika belum ada murid atau program aktif.
     */
    public function test_create_page_warns_when_data_is_missing(): void
    {
        $this->student->delete();
        $this->program->update(['is_active' => false]);

        $this->actingAs($this->admin)
            ->get(route('admin.enrollments.create'))
            ->assertOk()
            ->assertSee('id="alert-missing-data"', false)
            ->assertSee('Tambahkan murid terlebih dahulu')
            ->assertSee('Buat atau aktifkan program terlebih dahulu');
    }

    /**
     * Test admin dapat membuat pendaftaran baru dengan status Menunggu Verifikasi.
     */
    public function test_admin_can_create_enrollment(): void
    {
        $userCountBefore = User::withTrashed()->count();

        $response = $this->actingAs($this->admin)->post(route('admin.enrollments.store'), $this->validPayload([
            'status' => 'approved',
        ]));

        $response->assertRedirect(route('admin.enrollments.index'));
        $response->assertSessionHas('success', 'Pendaftaran Dewi Lestari ke program Bimbingan Belajar SMA berhasil dibuat dengan status Menunggu Verifikasi.');

        $enrollment = Enrollment::firstOrFail();
        $this->assertTrue($enrollment->student->is($this->student));
        $this->assertTrue($enrollment->program->is($this->program));
        $this->assertTrue($enrollment->classroom->is($this->classroom));
        $this->assertSame(EnrollmentStatus::Pending, $enrollment->status);
        $this->assertSame(now()->format('Y-m-d'), $enrollment->enrolled_on->format('Y-m-d'));
        $this->assertSame('Daftar melalui konsultasi WhatsApp.', $enrollment->admin_notes);
        $this->assertSame($userCountBefore, User::withTrashed()->count(), 'Pendaftaran tidak boleh membuat akun murid baru.');
    }

    /**
     * Test kelas tidak wajib dipilih saat pendaftaran.
     */
    public function test_enrollment_can_be_created_without_class(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.enrollments.store'), $this->validPayload(['classroom_id' => '']))
            ->assertSessionHasNoErrors();

        $this->assertNull(Enrollment::firstOrFail()->classroom_id);
    }

    /**
     * Test validasi field wajib dan tanggal pendaftaran.
     */
    public function test_create_enrollment_validation(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.enrollments.store'), [])
            ->assertSessionHasErrors([
                'student_id' => 'Silakan pilih murid.',
                'program_id' => 'Silakan pilih program bimbel.',
                'enrolled_on' => 'Tanggal pendaftaran wajib diisi.',
            ]);

        $this->actingAs($this->admin)
            ->post(route('admin.enrollments.store'), $this->validPayload(['enrolled_on' => now()->addDay()->format('Y-m-d')]))
            ->assertSessionHasErrors(['enrolled_on' => 'Tanggal pendaftaran tidak boleh melebihi hari ini.']);

        $this->assertDatabaseCount('enrollments', 0);
    }

    /**
     * Test murid harus akun murid yang valid.
     */
    public function test_invalid_student_is_rejected(): void
    {
        $deletedStudent = User::factory()->student()->create();
        $deletedStudent->delete();

        foreach (['99999', (string) User::factory()->teacher()->create()->id, (string) $this->admin->id, (string) $deletedStudent->id] as $studentId) {
            $this->actingAs($this->admin)
                ->post(route('admin.enrollments.store'), $this->validPayload(['student_id' => $studentId]))
                ->assertSessionHasErrors(['student_id' => 'Murid tidak ditemukan atau akunnya sudah dihapus.']);
        }

        $this->assertDatabaseCount('enrollments', 0);
    }

    /**
     * Test program harus ada dan sedang menerima pendaftaran.
     */
    public function test_invalid_or_inactive_program_is_rejected(): void
    {
        $inactiveProgram = Program::factory()->inactive()->create();

        foreach (['99999', (string) $inactiveProgram->id] as $programId) {
            $this->actingAs($this->admin)
                ->post(route('admin.enrollments.store'), $this->validPayload(['program_id' => $programId, 'classroom_id' => '']))
                ->assertSessionHasErrors(['program_id' => 'Program bimbel tidak ditemukan atau sedang tidak menerima pendaftaran.']);
        }

        $this->assertDatabaseCount('enrollments', 0);
    }

    /**
     * Test kelas harus termasuk dalam program yang dipilih dan masih aktif.
     */
    public function test_class_must_belong_to_selected_program(): void
    {
        $otherProgramClass = Classroom::factory()->for(Program::factory())->create();
        $inactiveClass = Classroom::factory()->for($this->program)->inactive()->create();

        foreach ([$otherProgramClass, $inactiveClass] as $classroom) {
            $this->actingAs($this->admin)
                ->post(route('admin.enrollments.store'), $this->validPayload(['classroom_id' => (string) $classroom->id]))
                ->assertSessionHasErrors(['classroom_id' => 'Kelas tidak ditemukan, tidak aktif, atau bukan bagian dari program yang dipilih.']);
        }

        $this->assertDatabaseCount('enrollments', 0);
    }

    /**
     * Test pendaftaran ganda pada program yang sama ditolak selama pendaftaran sebelumnya masih aktif.
     */
    public function test_duplicate_active_enrollment_is_rejected(): void
    {
        foreach ([EnrollmentStatus::Pending, EnrollmentStatus::Approved] as $status) {
            $existing = Enrollment::factory()->for($this->program)->withStatus($status)->create(['student_id' => $this->student->id]);

            $this->actingAs($this->admin)
                ->post(route('admin.enrollments.store'), $this->validPayload(['classroom_id' => '']))
                ->assertSessionHasErrors(['student_id' => 'Murid ini sudah memiliki pendaftaran aktif (menunggu verifikasi atau diterima) di program yang sama.']);

            $existing->delete();
        }

        $this->assertDatabaseCount('enrollments', 0);
    }

    /**
     * Test murid boleh mendaftar lagi jika pendaftaran sebelumnya sudah ditolak/dibatalkan, atau di program lain.
     */
    public function test_new_enrollment_allowed_after_inactive_or_in_other_program(): void
    {
        Enrollment::factory()->for($this->program)->withStatus(EnrollmentStatus::Cancelled)->create(['student_id' => $this->student->id]);
        Enrollment::factory()->for($this->program)->withStatus(EnrollmentStatus::Rejected)->create(['student_id' => $this->student->id]);
        Enrollment::factory()->for(Program::factory())->withStatus(EnrollmentStatus::Approved)->create(['student_id' => $this->student->id]);

        $this->actingAs($this->admin)
            ->post(route('admin.enrollments.store'), $this->validPayload())
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('enrollments', 4);
    }

    /**
     * Test kelas penuh menolak pendaftaran baru; hanya pendaftaran aktif yang dihitung.
     */
    public function test_full_class_rejects_new_enrollment(): void
    {
        $this->occupySeat(EnrollmentStatus::Approved);
        $this->occupySeat(EnrollmentStatus::Pending);
        $this->occupySeat(EnrollmentStatus::Cancelled);
        $this->occupySeat(EnrollmentStatus::Rejected);

        $this->actingAs($this->admin)
            ->post(route('admin.enrollments.store'), $this->validPayload())
            ->assertSessionHasErrors(['classroom_id' => 'Kelas SMA 12 IPA A sudah penuh (2/2 murid). Pilih kelas lain atau kosongkan kelas terlebih dahulu.']);

        $this->assertSame(0, Enrollment::where('student_id', $this->student->id)->count());

        $this->actingAs($this->admin)
            ->post(route('admin.enrollments.store'), $this->validPayload(['classroom_id' => '']))
            ->assertSessionHasNoErrors();
    }

    /**
     * Test kursi kelas dilepas ketika pendaftaran dibatalkan, sehingga murid lain dapat masuk.
     */
    public function test_cancelling_frees_a_seat(): void
    {
        $first = $this->occupySeat();
        $this->occupySeat();

        $this->actingAs($this->admin)->patch(route('admin.enrollments.update-status', $first), ['status' => 'cancelled']);

        $this->actingAs($this->admin)
            ->post(route('admin.enrollments.store'), $this->validPayload())
            ->assertSessionHasNoErrors();
    }

    /**
     * Test nilai input sebelumnya tetap tampil saat validasi gagal.
     */
    public function test_old_input_is_kept_after_validation_failure(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.enrollments.create'))
            ->followingRedirects()
            ->post(route('admin.enrollments.store'), $this->validPayload([
                'enrolled_on' => now()->addWeek()->format('Y-m-d'),
                'admin_notes' => 'Catatan yang harus tetap ada',
            ]))
            ->assertOk()
            ->assertSee('Catatan yang harus tetap ada')
            ->assertSee('value="'.$this->student->id.'" selected', false)
            ->assertSee('Tanggal pendaftaran tidak boleh melebihi hari ini.');
    }

    /**
     * Test halaman detail menampilkan informasi pendaftaran dan relasinya.
     */
    public function test_admin_can_view_enrollment_detail(): void
    {
        $this->occupySeat();
        $enrollment = Enrollment::factory()->inClassroom($this->classroom)->create([
            'student_id' => $this->student->id,
            'enrolled_on' => '2026-10-02',
            'admin_notes' => 'Sudah konsultasi jadwal.',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.enrollments.show', $enrollment))
            ->assertOk()
            ->assertSee('Dewi Lestari')
            ->assertSee('dewi@example.com')
            ->assertSee('Bimbingan Belajar SMA')
            ->assertSee('SMA 12 IPA A')
            ->assertSee('terisi 2/2')
            ->assertSee('02 Oktober 2026')
            ->assertSee('Menunggu Verifikasi')
            ->assertSee('Sudah konsultasi jadwal.')
            ->assertSee('Terima')
            ->assertSee('Tolak')
            ->assertSee('Batalkan');
    }

    /**
     * Test alur status: menunggu → diterima → dibatalkan, tanpa menghapus data.
     */
    public function test_status_flow_pending_approved_cancelled(): void
    {
        $enrollment = Enrollment::factory()->inClassroom($this->classroom)->create(['student_id' => $this->student->id]);

        $this->actingAs($this->admin)
            ->from(route('admin.enrollments.show', $enrollment))
            ->patch(route('admin.enrollments.update-status', $enrollment), ['status' => 'approved'])
            ->assertRedirect(route('admin.enrollments.show', $enrollment))
            ->assertSessionHas('success', 'Status pendaftaran Dewi Lestari berhasil diubah menjadi Diterima.');
        $this->assertSame(EnrollmentStatus::Approved, $enrollment->fresh()->status);
        $this->assertNotNull($enrollment->fresh()->status_changed_at);

        $this->actingAs($this->admin)
            ->patch(route('admin.enrollments.update-status', $enrollment), ['status' => 'cancelled'])
            ->assertSessionHas('success', 'Status pendaftaran Dewi Lestari berhasil diubah menjadi Dibatalkan.');

        $enrollment->refresh();
        $this->assertModelExists($enrollment);
        $this->assertSame(EnrollmentStatus::Cancelled, $enrollment->status);
        $this->assertTrue($enrollment->classroom->is($this->classroom));
    }

    /**
     * Test pendaftaran yang menunggu verifikasi dapat ditolak.
     */
    public function test_pending_enrollment_can_be_rejected(): void
    {
        $enrollment = Enrollment::factory()->create();

        $this->actingAs($this->admin)
            ->patch(route('admin.enrollments.update-status', $enrollment), ['status' => 'rejected'])
            ->assertSessionHas('success');

        $this->assertSame(EnrollmentStatus::Rejected, $enrollment->fresh()->status);
    }

    /**
     * Test perpindahan status yang tidak diizinkan ditolak.
     */
    public function test_invalid_status_transitions_are_rejected(): void
    {
        $approved = Enrollment::factory()->withStatus(EnrollmentStatus::Approved)->create();
        $rejected = Enrollment::factory()->withStatus(EnrollmentStatus::Rejected)->create();
        $pending = Enrollment::factory()->create();

        $this->actingAs($this->admin)
            ->patch(route('admin.enrollments.update-status', $approved), ['status' => 'rejected'])
            ->assertSessionHasErrors(['status' => 'Status tidak dapat diubah dari Diterima menjadi Ditolak.']);
        $this->actingAs($this->admin)
            ->patch(route('admin.enrollments.update-status', $rejected), ['status' => 'approved'])
            ->assertSessionHasErrors('status');
        $this->actingAs($this->admin)
            ->patch(route('admin.enrollments.update-status', $pending), ['status' => 'pending'])
            ->assertSessionHasErrors('status');
        $this->actingAs($this->admin)
            ->patch(route('admin.enrollments.update-status', $pending), ['status' => 'lunas'])
            ->assertSessionHasErrors(['status' => 'Status tujuan tidak valid.']);

        $this->assertSame(EnrollmentStatus::Approved, $approved->fresh()->status);
        $this->assertSame(EnrollmentStatus::Rejected, $rejected->fresh()->status);
        $this->assertSame(EnrollmentStatus::Pending, $pending->fresh()->status);
    }

    /**
     * Test pendaftaran dibatalkan hanya dapat dibuka kembali secara eksplisit, dengan pemeriksaan ulang.
     */
    public function test_reopening_requires_explicit_action_and_rechecks_rules(): void
    {
        $cancelled = Enrollment::factory()->inClassroom($this->classroom)->withStatus(EnrollmentStatus::Cancelled)->create(['student_id' => $this->student->id]);

        $this->actingAs($this->admin)
            ->get(route('admin.enrollments.show', $cancelled))
            ->assertSee('Buka Kembali')
            ->assertDontSee('>Terima<', false);

        $this->occupySeat();
        $this->occupySeat();

        $this->actingAs($this->admin)
            ->patch(route('admin.enrollments.update-status', $cancelled), ['status' => 'pending'])
            ->assertSessionHasErrors('classroom_id');
        $this->assertSame(EnrollmentStatus::Cancelled, $cancelled->fresh()->status);

        $otherCancelled = Enrollment::factory()->for($this->program)->withStatus(EnrollmentStatus::Cancelled)->create(['student_id' => $this->student->id]);
        Enrollment::factory()->for($this->program)->create(['student_id' => $this->student->id]);

        $this->actingAs($this->admin)
            ->patch(route('admin.enrollments.update-status', $otherCancelled), ['status' => 'pending'])
            ->assertSessionHasErrors('student_id');
        $this->assertSame(EnrollmentStatus::Cancelled, $otherCancelled->fresh()->status);
    }

    /**
     * Test pendaftaran yang ditolak dapat dibuka kembali jika aturan terpenuhi.
     */
    public function test_rejected_enrollment_can_be_reopened(): void
    {
        $rejected = Enrollment::factory()->inClassroom($this->classroom)->withStatus(EnrollmentStatus::Rejected)->create();

        $this->actingAs($this->admin)
            ->patch(route('admin.enrollments.update-status', $rejected), ['status' => 'pending'])
            ->assertSessionHas('success');

        $this->assertSame(EnrollmentStatus::Pending, $rejected->fresh()->status);
    }

    /**
     * Test pendaftaran yang menunggu verifikasi dapat diganti program dan kelasnya, tetapi tidak muridnya.
     */
    public function test_pending_enrollment_can_change_program_and_class(): void
    {
        $enrollment = Enrollment::factory()->for($this->program)->create(['student_id' => $this->student->id]);
        $newProgram = Program::factory()->create();
        $newClass = Classroom::factory()->for($newProgram)->create(['capacity' => 5]);
        $otherStudent = User::factory()->student()->create();

        $response = $this->actingAs($this->admin)->put(route('admin.enrollments.update', $enrollment), $this->validPayload([
            'student_id' => (string) $otherStudent->id,
            'program_id' => (string) $newProgram->id,
            'classroom_id' => (string) $newClass->id,
            'enrolled_on' => '2026-10-10',
            'admin_notes' => 'Pindah program sesuai permintaan orang tua.',
        ]));

        $response->assertRedirect(route('admin.enrollments.show', $enrollment));
        $response->assertSessionHas('success', 'Data pendaftaran berhasil diperbarui.');

        $enrollment->refresh();
        $this->assertSame($this->student->id, $enrollment->student_id);
        $this->assertSame($newProgram->id, $enrollment->program_id);
        $this->assertSame($newClass->id, $enrollment->classroom_id);
        $this->assertSame('2026-10-10', $enrollment->enrolled_on->format('Y-m-d'));
        $this->assertSame(EnrollmentStatus::Pending, $enrollment->status);
    }

    /**
     * Test pendaftaran yang diterima tidak dapat diganti programnya, tetapi kelasnya bisa dengan cek kapasitas.
     */
    public function test_approved_enrollment_keeps_program_and_checks_capacity_on_class_change(): void
    {
        $enrollment = Enrollment::factory()->for($this->program)->withStatus(EnrollmentStatus::Approved)->create(['student_id' => $this->student->id]);
        $otherProgram = Program::factory()->create();
        $this->occupySeat();
        $this->occupySeat();
        $roomyClass = Classroom::factory()->for($this->program)->create(['capacity' => 10]);

        $this->actingAs($this->admin)
            ->get(route('admin.enrollments.edit', $enrollment))
            ->assertOk()
            ->assertDontSee('name="program_id"', false)
            ->assertSee('Program tidak dapat diganti');

        $this->actingAs($this->admin)
            ->put(route('admin.enrollments.update', $enrollment), $this->validPayload(['program_id' => (string) $otherProgram->id]))
            ->assertSessionHasErrors(['classroom_id' => 'Kelas SMA 12 IPA A sudah penuh (2/2 murid). Pilih kelas lain atau kosongkan kelas terlebih dahulu.']);

        $this->actingAs($this->admin)
            ->put(route('admin.enrollments.update', $enrollment), $this->validPayload(['program_id' => (string) $otherProgram->id, 'classroom_id' => (string) $roomyClass->id]))
            ->assertSessionHasNoErrors();

        $enrollment->refresh();
        $this->assertSame($this->program->id, $enrollment->program_id);
        $this->assertSame($roomyClass->id, $enrollment->classroom_id);
    }

    /**
     * Test mengubah catatan saja tidak memicu pemeriksaan kapasitas kelas yang sudah ditempati.
     */
    public function test_editing_notes_does_not_recheck_capacity_of_current_class(): void
    {
        $enrollment = Enrollment::factory()->inClassroom($this->classroom)->withStatus(EnrollmentStatus::Approved)->create(['student_id' => $this->student->id]);
        $this->occupySeat();
        $this->classroom->update(['capacity' => 2]);

        $this->actingAs($this->admin)
            ->put(route('admin.enrollments.update', $enrollment), $this->validPayload(['admin_notes' => 'Catatan baru']))
            ->assertSessionHasNoErrors();

        $this->assertSame('Catatan baru', $enrollment->fresh()->admin_notes);
    }

    /**
     * Test pendaftaran yang dibatalkan hanya dapat diubah catatan adminnya.
     */
    public function test_cancelled_enrollment_only_allows_notes_change(): void
    {
        $enrollment = Enrollment::factory()->for($this->program)->withStatus(EnrollmentStatus::Cancelled)->create([
            'student_id' => $this->student->id,
            'enrolled_on' => '2026-09-01',
        ]);

        $this->actingAs($this->admin)
            ->put(route('admin.enrollments.update', $enrollment), $this->validPayload(['enrolled_on' => '2026-10-01', 'admin_notes' => 'Mengundurkan diri.']))
            ->assertSessionHasNoErrors();

        $enrollment->refresh();
        $this->assertSame('Mengundurkan diri.', $enrollment->admin_notes);
        $this->assertNull($enrollment->classroom_id);
        $this->assertSame('2026-09-01', $enrollment->enrolled_on->format('Y-m-d'));
        $this->assertSame(EnrollmentStatus::Cancelled, $enrollment->status);
    }

    /**
     * Test pendaftaran tidak dapat dihapus melalui HTTP DELETE.
     */
    public function test_enrollments_cannot_be_deleted(): void
    {
        $enrollment = Enrollment::factory()->create();

        $this->actingAs($this->admin)
            ->delete('/admin/pendaftaran/'.$enrollment->id)
            ->assertMethodNotAllowed();

        $this->assertModelExists($enrollment);
    }

    /**
     * Test guru, murid, dan tamu tidak dapat mengakses modul pendaftaran admin.
     */
    public function test_non_admin_users_cannot_manage_enrollments(): void
    {
        $enrollment = Enrollment::factory()->create();

        foreach ([User::factory()->teacher()->create(), $this->student] as $user) {
            $this->actingAs($user)->get(route('admin.enrollments.index'))->assertForbidden();
            $this->actingAs($user)->get(route('admin.enrollments.create'))->assertForbidden();
            $this->actingAs($user)->post(route('admin.enrollments.store'), $this->validPayload())->assertForbidden();
            $this->actingAs($user)->get(route('admin.enrollments.show', $enrollment))->assertForbidden();
            $this->actingAs($user)->get(route('admin.enrollments.edit', $enrollment))->assertForbidden();
            $this->actingAs($user)->put(route('admin.enrollments.update', $enrollment), $this->validPayload())->assertForbidden();
            $this->actingAs($user)->patch(route('admin.enrollments.update-status', $enrollment), ['status' => 'approved'])->assertForbidden();
        }

        auth()->logout();
        $this->get(route('admin.enrollments.index'))->assertRedirect(route('login'));

        $this->assertSame(EnrollmentStatus::Pending, $enrollment->fresh()->status);
        $this->assertDatabaseCount('enrollments', 1);
    }

    /**
     * Test integrasi: program dan kelas yang memiliki pendaftaran tidak dapat dihapus.
     */
    public function test_program_and_class_with_enrollments_cannot_be_deleted(): void
    {
        $program = Program::factory()->create();
        Enrollment::factory()->for($program)->withStatus(EnrollmentStatus::Cancelled)->create();
        $this->occupySeat();

        $this->actingAs($this->admin)->delete(route('admin.programs.destroy', $program))->assertSessionHas('error');
        $this->actingAs($this->admin)->delete(route('admin.classes.destroy', $this->classroom))->assertSessionHas('error');

        $this->assertModelExists($program);
        $this->assertModelExists($this->classroom);
    }

    /**
     * Test integrasi: kelas menampilkan keterisian dan daftar murid, serta kapasitas tidak bisa diturunkan di bawah jumlah murid aktif.
     */
    public function test_class_pages_show_occupancy_and_capacity_cannot_drop_below_it(): void
    {
        Enrollment::factory()->inClassroom($this->classroom)->create(['student_id' => $this->student->id]);
        $this->occupySeat();
        $this->occupySeat(EnrollmentStatus::Cancelled);

        $this->actingAs($this->admin)
            ->get(route('admin.classes.index'))
            ->assertSee('2/2 murid')
            ->assertSee('Penuh');

        $this->actingAs($this->admin)
            ->get(route('admin.classes.show', $this->classroom))
            ->assertOk()
            ->assertSee('2/2 murid terisi')
            ->assertSee('Murid Terdaftar')
            ->assertSee('Dewi Lestari');

        $payload = [
            'name' => $this->classroom->name,
            'code' => $this->classroom->code,
            'program_id' => (string) $this->program->id,
            'teacher_id' => (string) $this->classroom->teacher_id,
            'capacity' => '1',
            'learning_mode' => 'offline',
            'is_active' => '1',
        ];

        $this->actingAs($this->admin)
            ->put(route('admin.classes.update', $this->classroom), $payload)
            ->assertSessionHasErrors(['capacity' => 'Kapasitas tidak boleh kurang dari jumlah murid aktif di kelas ini (2 murid).']);

        $this->actingAs($this->admin)
            ->put(route('admin.classes.update', $this->classroom), [...$payload, 'capacity' => '3'])
            ->assertSessionHasNoErrors();
        $this->assertSame(3, $this->classroom->fresh()->capacity);
    }

    /**
     * Test integrasi: detail murid menampilkan riwayat pendaftaran dan dashboard menampilkan pendaftaran terbaru.
     */
    public function test_student_page_and_dashboard_show_enrollments(): void
    {
        $enrollment = Enrollment::factory()->inClassroom($this->classroom)->withStatus(EnrollmentStatus::Approved)->create([
            'student_id' => $this->student->id,
            'enrolled_on' => '2026-10-03',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.students.show', $this->student))
            ->assertOk()
            ->assertSee('Riwayat Pendaftaran')
            ->assertSee('Bimbingan Belajar SMA')
            ->assertSee('SMA 12 IPA A')
            ->assertSee('Diterima')
            ->assertSee(route('admin.enrollments.create', ['murid' => $this->student->id]), false);

        $this->actingAs($this->admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSeeInOrder(['Pendaftaran Terbaru', 'Dewi Lestari', 'Bimbingan Belajar SMA', '03 Okt 2026', 'Diterima'])
            ->assertSee(route('admin.enrollments.show', $enrollment), false);
    }

    /**
     * Test halaman pendaftaran berada di bawah menu Manajemen Murid dengan tab Pendaftaran aktif.
     */
    public function test_sidebar_links_to_enrollment_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.enrollments.index'));

        $this->assertMatchesRegularExpression(
            '/href="'.preg_quote(route('admin.students.index'), '/').'"\s+class="admin-nav-item is-active"/',
            $response->getContent()
        );
        $this->assertMatchesRegularExpression(
            '/href="'.preg_quote(route('admin.enrollments.index'), '/').'"\s+class="is-active"\s+aria-current="page"\s*>Pendaftaran</',
            $response->getContent()
        );
    }

    /**
     * Test tab Riwayat Pendaftaran hanya menampilkan pendaftaran yang ditolak atau dibatalkan.
     */
    public function test_history_tab_lists_only_rejected_and_cancelled_enrollments(): void
    {
        $approvedStudent = User::factory()->student()->create(['name' => 'Murid Diterima']);
        $rejectedStudent = User::factory()->student()->create(['name' => 'Murid Ditolak']);
        $cancelledStudent = User::factory()->student()->create(['name' => 'Murid Batal']);
        Enrollment::factory()->for($approvedStudent, 'student')->create(['status' => EnrollmentStatus::Approved]);
        Enrollment::factory()->for($rejectedStudent, 'student')->create(['status' => EnrollmentStatus::Rejected]);
        Enrollment::factory()->for($cancelledStudent, 'student')->create(['status' => EnrollmentStatus::Cancelled]);

        $this->actingAs($this->admin)
            ->get(route('admin.enrollments.index', ['riwayat' => 1]))
            ->assertOk()
            ->assertSee('Riwayat Pendaftaran')
            ->assertSee('Murid Ditolak')
            ->assertSee('Murid Batal')
            ->assertDontSee('Murid Diterima');
    }
}
