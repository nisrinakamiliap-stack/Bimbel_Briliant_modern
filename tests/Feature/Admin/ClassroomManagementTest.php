<?php

namespace Tests\Feature\Admin;

use App\LearningMode;
use App\Models\Classroom;
use App\Models\Program;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ClassroomManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Program $program;

    private User $teacher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->program = Program::factory()->create(['name' => 'Bimbingan Belajar SMA']);
        $this->teacher = TeacherProfile::factory()
            ->for(User::factory()->teacher()->state(['name' => 'Sari Wahyuni']))
            ->create(['subject' => 'Matematika'])
            ->user;
    }

    /**
     * Data form kelas yang valid.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return [
            'name' => 'SMA 12 IPA Reguler',
            'code' => 'sma-ipa-01',
            'program_id' => (string) $this->program->id,
            'teacher_id' => (string) $this->teacher->id,
            'capacity' => '20',
            'learning_mode' => LearningMode::Offline->value,
            'description' => 'Kelas reguler persiapan ujian sekolah.',
            'is_active' => '1',
            ...$overrides,
        ];
    }

    /**
     * Test daftar kelas menampilkan program dan guru melalui relasi database.
     */
    public function test_index_lists_classes_with_relations(): void
    {
        Classroom::factory()->for($this->program)->create([
            'teacher_id' => $this->teacher->id,
            'name' => 'Kelas Intensif A',
            'capacity' => 15,
        ]);
        Classroom::factory()->for($this->program)->withoutTeacher()->inactive()->create(['name' => 'Kelas Tanpa Guru']);

        $response = $this->actingAs($this->admin)->get(route('admin.classes.index'));

        $response->assertOk();
        $response->assertViewIs('admin.classes.index');
        $response->assertSee('Data Kelas');
        $response->assertSee('Tambah Kelas');
        $response->assertSee('Kelas Intensif A');
        $response->assertSee('Bimbingan Belajar SMA');
        $response->assertSee('Sari Wahyuni');
        $response->assertSee('15 murid');
        $response->assertSee('Kelas Tanpa Guru');
        $response->assertSee('Belum ditentukan');
        $response->assertSee('Tidak Aktif');
    }

    /**
     * Test daftar kelas memakai eager loading sehingga jumlah query tidak bertambah per baris.
     */
    public function test_index_avoids_n_plus_one_queries(): void
    {
        Classroom::factory()->for($this->program)->count(3)->create();

        $queryCount = function (): int {
            $count = 0;
            DB::listen(function () use (&$count): void {
                $count++;
            });
            $this->actingAs($this->admin)->get(route('admin.classes.index'))->assertOk();

            return $count;
        };

        // Permintaan pemanasan agar cache pengaturan lembaga tidak memengaruhi perbandingan.
        $queryCount();
        $queriesWithThreeClasses = $queryCount();
        Classroom::factory()->for(Program::factory())->count(6)->create();
        $queriesWithNineClasses = $queryCount();

        $this->assertSame($queriesWithThreeClasses, $queriesWithNineClasses);
    }

    /**
     * Test empty state tampil jika belum ada kelas, termasuk saat belum ada program.
     */
    public function test_index_shows_empty_states(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.classes.index'))
            ->assertOk()
            ->assertSee('Belum ada kelas');

        $this->program->delete();

        $this->actingAs($this->admin)
            ->get(route('admin.classes.index'))
            ->assertSee('Belum ada program bimbel');
    }

    /**
     * Test pencarian kelas berdasarkan nama maupun kode.
     */
    public function test_index_can_search_by_name_or_code(): void
    {
        Classroom::factory()->for($this->program)->create(['name' => 'Kelas Fisika Dasar', 'code' => 'FIS-01']);
        Classroom::factory()->for($this->program)->create(['name' => 'Kelas Kimia Lanjut', 'code' => 'KIM-02']);

        $this->actingAs($this->admin)
            ->get(route('admin.classes.index', ['q' => 'fisika']))
            ->assertSee('Kelas Fisika Dasar')
            ->assertDontSee('Kelas Kimia Lanjut');

        $this->actingAs($this->admin)
            ->get(route('admin.classes.index', ['q' => 'KIM-02']))
            ->assertSee('Kelas Kimia Lanjut')
            ->assertDontSee('Kelas Fisika Dasar');

        $this->actingAs($this->admin)
            ->get(route('admin.classes.index', ['q' => 'tidak-ada']))
            ->assertSee('Kelas tidak ditemukan');
    }

    /**
     * Test filter berdasarkan program dan status.
     */
    public function test_index_can_filter_by_program_and_status(): void
    {
        $otherProgram = Program::factory()->create(['name' => 'Bimbingan Belajar SD']);
        Classroom::factory()->for($this->program)->create(['name' => 'Kelas SMA Aktif']);
        Classroom::factory()->for($this->program)->inactive()->create(['name' => 'Kelas SMA Nonaktif']);
        Classroom::factory()->for($otherProgram)->create(['name' => 'Kelas SD Aktif']);

        $this->actingAs($this->admin)
            ->get(route('admin.classes.index', ['program' => $this->program->id, 'status' => 'aktif']))
            ->assertSee('Kelas SMA Aktif')
            ->assertDontSee('Kelas SMA Nonaktif')
            ->assertDontSee('Kelas SD Aktif');

        $this->actingAs($this->admin)
            ->get(route('admin.classes.index', ['status' => 'nonaktif']))
            ->assertSee('Kelas SMA Nonaktif')
            ->assertDontSee('Kelas SD Aktif');

        $this->actingAs($this->admin)
            ->get(route('admin.classes.index', ['program' => 99999]))
            ->assertSessionHasErrors('program');
    }

    /**
     * Test pagination membagi daftar kelas per 10 data dan mempertahankan filter.
     */
    public function test_index_is_paginated(): void
    {
        Classroom::factory()->for($this->program)->withoutTeacher()->count(12)->create();

        $firstPage = $this->actingAs($this->admin)->get(route('admin.classes.index'));
        $this->assertCount(10, $firstPage->viewData('classrooms'));
        $firstPage->assertSee('Menampilkan 1–10 dari 12 data');

        $secondPage = $this->actingAs($this->admin)->get(route('admin.classes.index', ['page' => 2]));
        $this->assertCount(2, $secondPage->viewData('classrooms'));

        $this->actingAs($this->admin)
            ->get(route('admin.classes.index', ['program' => $this->program->id]))
            ->assertSee('program='.$this->program->id.'&amp;page=2', false);
    }

    /**
     * Test admin dapat menambahkan kelas dan diarahkan kembali ke daftar kelas.
     */
    public function test_admin_can_create_class(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.classes.store'), $this->validPayload());

        $response->assertRedirect(route('admin.classes.index'));
        $response->assertSessionHas('success', 'Kelas SMA 12 IPA Reguler berhasil ditambahkan.');

        $classroom = Classroom::where('name', 'SMA 12 IPA Reguler')->firstOrFail();
        $this->assertSame('SMA-IPA-01', $classroom->code);
        $this->assertTrue($classroom->program->is($this->program));
        $this->assertTrue($classroom->teacher->is($this->teacher));
        $this->assertSame(20, $classroom->capacity);
        $this->assertSame(LearningMode::Offline, $classroom->learning_mode);
        $this->assertTrue($classroom->is_active);
    }

    /**
     * Test kelas dapat dibuat tanpa kode dan tanpa guru.
     */
    public function test_class_can_be_created_without_code_and_teacher(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.classes.store'), $this->validPayload(['code' => '', 'teacher_id' => '']))
            ->assertSessionHasNoErrors();

        $classroom = Classroom::firstOrFail();
        $this->assertNull($classroom->code);
        $this->assertNull($classroom->teacher_id);
    }

    /**
     * Test validasi field wajib dan kapasitas.
     */
    public function test_create_class_validation(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.classes.store'), [])
            ->assertSessionHasErrors([
                'name' => 'Nama kelas wajib diisi.',
                'program_id' => 'Silakan pilih program bimbel.',
                'capacity' => 'Kapasitas kelas wajib diisi.',
                'learning_mode',
                'is_active',
            ]);

        foreach (['0' => 'Kapasitas minimal 1 murid.', '-5' => 'Kapasitas minimal 1 murid.', '2.5' => 'Kapasitas harus berupa bilangan bulat, contoh: 20.', 'dua puluh' => 'Kapasitas harus berupa bilangan bulat, contoh: 20.'] as $capacity => $message) {
            $this->actingAs($this->admin)
                ->post(route('admin.classes.store'), $this->validPayload(['capacity' => (string) $capacity]))
                ->assertSessionHasErrors(['capacity' => $message]);
        }

        $this->actingAs($this->admin)
            ->post(route('admin.classes.store'), $this->validPayload(['code' => 'KODE SALAH!', 'learning_mode' => 'hybrid']))
            ->assertSessionHasErrors(['code', 'learning_mode']);

        $this->assertDatabaseCount('classes', 0);
    }

    /**
     * Test ID program dan guru yang tidak valid ditolak.
     */
    public function test_invalid_program_or_teacher_is_rejected(): void
    {
        $student = User::factory()->student()->create();
        $deletedTeacher = User::factory()->teacher()->create();
        $deletedTeacher->delete();
        $inactiveProgram = Program::factory()->inactive()->create();

        $cases = [
            ['program_id' => '99999'],
            ['program_id' => (string) $inactiveProgram->id],
            ['teacher_id' => '99999'],
            ['teacher_id' => (string) $student->id],
            ['teacher_id' => (string) $this->admin->id],
            ['teacher_id' => (string) $deletedTeacher->id],
        ];

        foreach ($cases as $override) {
            $this->actingAs($this->admin)
                ->post(route('admin.classes.store'), $this->validPayload($override))
                ->assertSessionHasErrors(array_key_first($override));
        }

        $this->assertDatabaseCount('classes', 0);
    }

    /**
     * Test kode kelas harus unik (tanpa membedakan huruf besar/kecil).
     */
    public function test_class_code_must_be_unique(): void
    {
        Classroom::factory()->for($this->program)->create(['code' => 'SMA-IPA-01']);

        $this->actingAs($this->admin)
            ->post(route('admin.classes.store'), $this->validPayload(['code' => 'sma-ipa-01']))
            ->assertSessionHasErrors(['code' => 'Kode kelas ini sudah digunakan oleh kelas lain.']);
    }

    /**
     * Test nilai input sebelumnya tetap tampil saat validasi gagal.
     */
    public function test_old_input_is_kept_after_validation_failure(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.classes.create'))
            ->followingRedirects()
            ->post(route('admin.classes.store'), $this->validPayload(['name' => 'Kelas Gagal Simpan', 'capacity' => '0']))
            ->assertOk()
            ->assertSee('value="Kelas Gagal Simpan"', false)
            ->assertSee('Kapasitas minimal 1 murid.');
    }

    /**
     * Test form tambah menampilkan program aktif dan guru dari database.
     */
    public function test_create_page_lists_active_programs_and_teachers(): void
    {
        Program::factory()->inactive()->create(['name' => 'Program Ditutup']);

        $this->actingAs($this->admin)
            ->get(route('admin.classes.create'))
            ->assertOk()
            ->assertSee('Bimbingan Belajar SMA')
            ->assertDontSee('Program Ditutup')
            ->assertSee('Sari Wahyuni — Matematika')
            ->assertDontSee('id="alert-no-program"', false);
    }

    /**
     * Test form tambah memberi tahu admin jika belum ada program aktif.
     */
    public function test_create_page_warns_when_no_program_exists(): void
    {
        $this->program->update(['is_active' => false]);

        $this->actingAs($this->admin)
            ->get(route('admin.classes.create'))
            ->assertOk()
            ->assertSee('id="alert-no-program"', false)
            ->assertSee('Buat program terlebih dahulu');
    }

    /**
     * Test halaman detail kelas menampilkan informasi dan relasinya.
     */
    public function test_admin_can_view_class_detail(): void
    {
        $classroom = Classroom::factory()->for($this->program)->create([
            'teacher_id' => $this->teacher->id,
            'name' => 'Kelas Detail',
            'code' => 'DTL-01',
            'capacity' => 18,
            'learning_mode' => LearningMode::Online,
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.classes.show', $classroom))
            ->assertOk()
            ->assertSee('Kelas Detail')
            ->assertSee('DTL-01')
            ->assertSee('Bimbingan Belajar SMA')
            ->assertSee('Sari Wahyuni')
            ->assertSee('Matematika')
            ->assertSee('18 murid')
            ->assertSee('Online')
            ->assertSee('aman dihapus');
    }

    /**
     * Test kelas dengan guru yang akunnya sudah dihapus tetap menampilkan nama guru tersebut.
     */
    public function test_class_keeps_deleted_teacher_history(): void
    {
        $classroom = Classroom::factory()->for($this->program)->create(['teacher_id' => $this->teacher->id]);
        $this->teacher->delete();

        $this->actingAs($this->admin)
            ->get(route('admin.classes.show', $classroom))
            ->assertOk()
            ->assertSee('Sari Wahyuni')
            ->assertSee('Akun dihapus');

        $this->actingAs($this->admin)
            ->put(route('admin.classes.update', $classroom), $this->validPayload(['name' => 'Tetap Bisa Diedit']))
            ->assertSessionHasNoErrors();

        $this->assertSame($this->teacher->id, $classroom->fresh()->teacher_id);
    }

    /**
     * Test form edit terisi data kelas yang dipilih.
     */
    public function test_edit_page_is_prefilled(): void
    {
        $classroom = Classroom::factory()->for($this->program)->create(['name' => 'Kelas Edit', 'code' => 'EDT-01', 'capacity' => 12]);

        $this->actingAs($this->admin)
            ->get(route('admin.classes.edit', $classroom))
            ->assertOk()
            ->assertSee('value="Kelas Edit"', false)
            ->assertSee('value="EDT-01"', false)
            ->assertSee('value="12"', false);
    }

    /**
     * Test admin dapat memperbarui kelas tanpa mengubah kelas lain.
     */
    public function test_admin_can_update_class(): void
    {
        $classroom = Classroom::factory()->for($this->program)->withoutTeacher()->create(['code' => 'OLD-01']);
        $otherClassroom = Classroom::factory()->for($this->program)->create(['capacity' => 9]);

        $response = $this->actingAs($this->admin)->put(route('admin.classes.update', $classroom), $this->validPayload([
            'code' => 'OLD-01',
            'capacity' => '25',
            'learning_mode' => 'online',
            'is_active' => '0',
        ]));

        $response->assertRedirect(route('admin.classes.show', $classroom));
        $response->assertSessionHas('success');

        $classroom->refresh();
        $this->assertSame(25, $classroom->capacity);
        $this->assertSame($this->teacher->id, $classroom->teacher_id);
        $this->assertSame(LearningMode::Online, $classroom->learning_mode);
        $this->assertFalse($classroom->is_active);
        $this->assertSame(9, $otherClassroom->fresh()->capacity);
    }

    /**
     * Test validasi edit: kode boleh tetap sama, tetapi tidak boleh memakai kode kelas lain.
     */
    public function test_update_code_uniqueness_ignores_current_class(): void
    {
        $classroom = Classroom::factory()->for($this->program)->create(['code' => 'KLS-A']);
        Classroom::factory()->for($this->program)->create(['code' => 'KLS-B']);

        $this->actingAs($this->admin)
            ->put(route('admin.classes.update', $classroom), $this->validPayload(['code' => 'KLS-A']))
            ->assertSessionHasNoErrors();

        $this->actingAs($this->admin)
            ->put(route('admin.classes.update', $classroom), $this->validPayload(['code' => 'kls-b', 'capacity' => '0']))
            ->assertSessionHasErrors(['code', 'capacity']);

        $this->assertSame('KLS-A', $classroom->fresh()->code);
    }

    /**
     * Test kelas lama di program yang kini nonaktif tetap bisa diedit tanpa harus pindah program.
     */
    public function test_update_keeps_existing_inactive_program(): void
    {
        $classroom = Classroom::factory()->for($this->program)->create();
        $this->program->update(['is_active' => false]);

        $this->actingAs($this->admin)
            ->get(route('admin.classes.edit', $classroom))
            ->assertSee('Bimbingan Belajar SMA (tidak aktif)');

        $this->actingAs($this->admin)
            ->put(route('admin.classes.update', $classroom), $this->validPayload(['capacity' => '30']))
            ->assertSessionHasNoErrors();

        $this->assertSame(30, $classroom->fresh()->capacity);
    }

    /**
     * Test status kelas dapat dinonaktifkan dan diaktifkan kembali dengan konfirmasi.
     */
    public function test_admin_can_toggle_class_status(): void
    {
        $classroom = Classroom::factory()->for($this->program)->create(['name' => 'Kelas Toggle']);

        $this->actingAs($this->admin)
            ->get(route('admin.classes.index'))
            ->assertSee('data-confirm-title="Nonaktifkan Kelas"', false);

        $this->actingAs($this->admin)
            ->from(route('admin.classes.index'))
            ->patch(route('admin.classes.toggle-status', $classroom))
            ->assertRedirect(route('admin.classes.index'))
            ->assertSessionHas('success', 'Kelas Kelas Toggle berhasil dinonaktifkan.');
        $this->assertFalse($classroom->fresh()->is_active);

        $this->actingAs($this->admin)
            ->patch(route('admin.classes.toggle-status', $classroom))
            ->assertSessionHas('success', 'Kelas Kelas Toggle berhasil diaktifkan.');
        $this->assertTrue($classroom->fresh()->is_active);
    }

    /**
     * Test kelas yang belum memiliki riwayat dapat dihapus melalui HTTP DELETE.
     */
    public function test_class_without_history_can_be_deleted(): void
    {
        $classroom = Classroom::factory()->for($this->program)->create();

        $this->actingAs($this->admin)
            ->delete(route('admin.classes.destroy', $classroom))
            ->assertRedirect(route('admin.classes.index'))
            ->assertSessionHas('success');

        $this->assertModelMissing($classroom);
        $this->assertModelExists($this->program);
        $this->assertModelExists($this->teacher);
    }

    /**
     * Test kelas yang sudah memiliki riwayat tidak dihapus dan admin diarahkan untuk menonaktifkannya.
     */
    public function test_class_with_history_cannot_be_deleted(): void
    {
        $classroom = Classroom::factory()->for($this->program)->create(['name' => 'Kelas Berriwayat']);

        $this->app->bind(Classroom::class, fn () => new class extends Classroom
        {
            public function hasDependentRecords(): bool
            {
                return true;
            }
        });

        $this->actingAs($this->admin)
            ->get(route('admin.classes.show', $classroom))
            ->assertOk()->assertSee('tidak dapat dihapus')
            ->assertDontSee('id="btn-delete-class"', false);

        $this->actingAs($this->admin)
            ->from(route('admin.classes.show', $classroom))
            ->delete(route('admin.classes.destroy', $classroom))
            ->assertRedirect(route('admin.classes.show', $classroom))
            ->assertSessionHas('error', 'Kelas Kelas Berriwayat tidak dapat dihapus karena sudah memiliki jadwal, pendaftaran, atau absensi. Nonaktifkan kelas ini agar riwayatnya tetap tersimpan.');

        $this->assertModelExists($classroom);
    }

    /**
     * Test penghapusan wajib memakai HTTP DELETE.
     */
    public function test_delete_requires_delete_method(): void
    {
        $classroom = Classroom::factory()->for($this->program)->create();

        $this->actingAs($this->admin)
            ->post('/admin/kelas/'.$classroom->id)
            ->assertMethodNotAllowed();

        $this->assertModelExists($classroom);
    }

    /**
     * Test guru, murid, dan tamu tidak dapat mengakses modul kelas.
     */
    public function test_non_admin_users_cannot_manage_classes(): void
    {
        $classroom = Classroom::factory()->for($this->program)->create();

        foreach ([$this->teacher, User::factory()->student()->create()] as $user) {
            $this->actingAs($user)->get(route('admin.classes.index'))->assertForbidden();
            $this->actingAs($user)->get(route('admin.classes.create'))->assertForbidden();
            $this->actingAs($user)->post(route('admin.classes.store'), $this->validPayload())->assertForbidden();
            $this->actingAs($user)->get(route('admin.classes.show', $classroom))->assertForbidden();
            $this->actingAs($user)->get(route('admin.classes.edit', $classroom))->assertForbidden();
            $this->actingAs($user)->put(route('admin.classes.update', $classroom), $this->validPayload())->assertForbidden();
            $this->actingAs($user)->patch(route('admin.classes.toggle-status', $classroom))->assertForbidden();
            $this->actingAs($user)->delete(route('admin.classes.destroy', $classroom))->assertForbidden();
        }

        auth()->logout();
        $this->get(route('admin.classes.index'))->assertRedirect(route('login'));
        $this->post(route('admin.classes.store'), $this->validPayload())->assertRedirect(route('login'));

        $this->assertModelExists($classroom);
        $this->assertTrue($classroom->fresh()->is_active);
        $this->assertDatabaseCount('classes', 1);
    }

    /**
     * Test menu Kelas di sidebar aktif dan mengarah ke halaman daftar kelas.
     */
    public function test_sidebar_links_to_class_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.classes.index'));

        $this->assertMatchesRegularExpression(
            '/href="'.preg_quote(route('admin.classes.index'), '/').'"\s+class="admin-nav-item is-active"/',
            $response->getContent()
        );
    }
}
