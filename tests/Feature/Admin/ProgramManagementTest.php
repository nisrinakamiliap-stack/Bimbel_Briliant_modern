<?php

namespace Tests\Feature\Admin;

use App\Models\Classroom;
use App\Models\Program;
use App\Models\User;
use App\ProgramLevel;
use Database\Seeders\ProgramSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgramManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    /**
     * Data form program yang valid.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return [
            'name' => 'Kelas Intensif UTBK',
            'level' => ProgramLevel::Umum->value,
            'description' => 'Persiapan intensif seleksi masuk PTN.',
            'price' => '750000',
            'duration_months' => '6',
            'is_active' => '1',
            ...$overrides,
        ];
    }

    /**
     * Test daftar program dapat dibuka dan menampilkan data dari database.
     */
    public function test_index_lists_programs_from_database(): void
    {
        Program::factory()->create(['name' => 'Bimbel Matematika SMP', 'level' => ProgramLevel::Smp, 'price' => 150000]);
        Program::factory()->inactive()->create(['name' => 'Program Lama']);

        $response = $this->actingAs($this->admin)->get(route('admin.programs.index'));

        $response->assertOk();
        $response->assertViewIs('admin.programs.index');
        $response->assertSee('Data Program Bimbel');
        $response->assertSee('Tambah Program');
        $response->assertSee('Bimbel Matematika SMP');
        $response->assertSee('Rp150.000');
        $response->assertSee('SMP');
        $response->assertSee('Program Lama');
        $response->assertSee('Tidak Aktif');
    }

    /**
     * Test empty state tampil jika belum ada program.
     */
    public function test_index_shows_empty_state_without_programs(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.programs.index'))
            ->assertOk()
            ->assertSee('Belum ada program');
    }

    /**
     * Test pencarian program berdasarkan nama.
     */
    public function test_index_can_search_by_name(): void
    {
        Program::factory()->create(['name' => 'Bimbingan Belajar SD']);
        Program::factory()->create(['name' => 'Kelas Privat Fisika']);

        $this->actingAs($this->admin)
            ->get(route('admin.programs.index', ['q' => 'privat']))
            ->assertSee('Kelas Privat Fisika')
            ->assertDontSee('Bimbingan Belajar SD');

        $this->actingAs($this->admin)
            ->get(route('admin.programs.index', ['q' => 'tidak-ada']))
            ->assertSee('Program tidak ditemukan');
    }

    /**
     * Test filter jenjang dan status.
     */
    public function test_index_can_filter_by_level_and_status(): void
    {
        Program::factory()->create(['name' => 'Program SD Aktif', 'level' => ProgramLevel::Sd]);
        Program::factory()->inactive()->create(['name' => 'Program SD Nonaktif', 'level' => ProgramLevel::Sd]);
        Program::factory()->create(['name' => 'Program SMA Aktif', 'level' => ProgramLevel::Sma]);

        $this->actingAs($this->admin)
            ->get(route('admin.programs.index', ['jenjang' => 'sd', 'status' => 'aktif']))
            ->assertSee('Program SD Aktif')
            ->assertDontSee('Program SD Nonaktif')
            ->assertDontSee('Program SMA Aktif');

        $this->actingAs($this->admin)
            ->get(route('admin.programs.index', ['status' => 'nonaktif']))
            ->assertSee('Program SD Nonaktif')
            ->assertDontSee('Program SMA Aktif');

        $this->actingAs($this->admin)
            ->get(route('admin.programs.index', ['status' => 'hapus']))
            ->assertSessionHasErrors('status');
    }

    /**
     * Test pagination membagi daftar program per 10 data dan mempertahankan query pencarian.
     */
    public function test_index_is_paginated(): void
    {
        Program::factory()->count(14)->create();

        $firstPage = $this->actingAs($this->admin)->get(route('admin.programs.index'));
        $this->assertCount(10, $firstPage->viewData('programs'));
        $firstPage->assertSee('Menampilkan 1–10 dari 14 data');

        $secondPage = $this->actingAs($this->admin)->get(route('admin.programs.index', ['page' => 2]));
        $this->assertCount(4, $secondPage->viewData('programs'));

        $this->actingAs($this->admin)
            ->get(route('admin.programs.index', ['q' => 'Program']))
            ->assertSee('q=Program&amp;page=2', false);
    }

    /**
     * Test admin dapat menambahkan program dan diarahkan kembali ke daftar program.
     */
    public function test_admin_can_create_program(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.programs.store'), $this->validPayload());

        $response->assertRedirect(route('admin.programs.index'));
        $response->assertSessionHas('success', 'Program Kelas Intensif UTBK berhasil ditambahkan.');

        $program = Program::where('name', 'Kelas Intensif UTBK')->firstOrFail();
        $this->assertSame(ProgramLevel::Umum, $program->level);
        $this->assertSame('750000.00', $program->price);
        $this->assertSame(6, $program->duration_months);
        $this->assertTrue($program->is_active);
        $this->assertSame('kelas-intensif-utbk', $program->slug);
    }

    /**
     * Test harga berformat rupiah diterima dan disimpan sebagai angka.
     */
    public function test_rupiah_formatted_price_is_normalized(): void
    {
        $this->actingAs($this->admin)->post(route('admin.programs.store'), $this->validPayload([
            'name' => 'Program Format Rupiah',
            'price' => 'Rp 1.250.000',
            'duration_months' => '',
        ]))->assertSessionHasNoErrors();

        $program = Program::where('name', 'Program Format Rupiah')->firstOrFail();
        $this->assertSame('1250000.00', $program->price);
        $this->assertNull($program->duration_months);
        $this->assertSame('Rp1.250.000', $program->formattedPrice());
    }

    /**
     * Test slug dari mass assignment diabaikan dan slug duplikat diberi akhiran angka.
     */
    public function test_slug_is_generated_and_not_mass_assignable(): void
    {
        Program::factory()->create(['name' => 'Kelas Intensif-UTBK']);

        $this->actingAs($this->admin)->post(route('admin.programs.store'), $this->validPayload(['slug' => 'slug-palsu']));

        $program = Program::where('name', 'Kelas Intensif UTBK')->firstOrFail();
        $this->assertSame('kelas-intensif-utbk-2', $program->slug);
    }

    /**
     * Test validasi field wajib, harga, jenjang, dan status.
     */
    public function test_create_program_validation(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.programs.store'), [])
            ->assertSessionHasErrors([
                'name' => 'Nama program wajib diisi.',
                'level' => 'Silakan pilih jenjang program.',
                'price' => 'Harga program wajib diisi.',
                'is_active' => 'Silakan pilih status program.',
            ]);

        $this->actingAs($this->admin)
            ->post(route('admin.programs.store'), $this->validPayload(['price' => '-5000']))
            ->assertSessionHasErrors(['price' => 'Harga tidak boleh negatif.']);

        $this->actingAs($this->admin)
            ->post(route('admin.programs.store'), $this->validPayload([
                'price' => 'gratis',
                'level' => 'kuliah',
                'duration_months' => '0',
                'is_active' => 'mungkin',
            ]))
            ->assertSessionHasErrors(['price', 'level', 'duration_months', 'is_active']);

        $this->assertDatabaseCount('programs', 0);
    }

    /**
     * Test nilai input sebelumnya tetap tampil saat validasi gagal.
     */
    public function test_old_input_is_kept_after_validation_failure(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.programs.create'))
            ->followingRedirects()
            ->post(route('admin.programs.store'), $this->validPayload(['price' => '-1', 'name' => 'Program Gagal Simpan']))
            ->assertOk()
            ->assertSee('value="Program Gagal Simpan"', false)
            ->assertSee('Harga tidak boleh negatif.');
    }

    /**
     * Test nama program harus unik.
     */
    public function test_program_name_must_be_unique(): void
    {
        Program::factory()->create(['name' => 'Kelas Intensif UTBK']);

        $this->actingAs($this->admin)
            ->post(route('admin.programs.store'), $this->validPayload())
            ->assertSessionHasErrors(['name' => 'Nama program ini sudah digunakan. Gunakan nama lain.']);
    }

    /**
     * Test halaman detail program menampilkan data dari database.
     */
    public function test_admin_can_view_program_detail(): void
    {
        $program = Program::factory()->create([
            'name' => 'Bimbel Bahasa Inggris',
            'price' => 275000,
            'duration_months' => 3,
            'description' => 'Fokus speaking dan grammar.',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.programs.show', $program))
            ->assertOk()
            ->assertSee('Bimbel Bahasa Inggris')
            ->assertSee('Rp275.000')
            ->assertSee('3 bulan')
            ->assertSee('Fokus speaking dan grammar.')
            ->assertSee('aman dihapus');
    }

    /**
     * Test form edit terisi data program yang dipilih.
     */
    public function test_edit_page_is_prefilled(): void
    {
        $program = Program::factory()->create(['name' => 'Program Edit', 'price' => 425000]);

        $this->actingAs($this->admin)
            ->get(route('admin.programs.edit', $program))
            ->assertOk()
            ->assertSee('value="Program Edit"', false)
            ->assertSee('value="425000"', false);
    }

    /**
     * Test admin dapat memperbarui program tanpa mengubah program lain.
     */
    public function test_admin_can_update_program(): void
    {
        $program = Program::factory()->create(['name' => 'Program Awal']);
        $otherProgram = Program::factory()->create(['name' => 'Program Lain', 'price' => 100000]);
        $originalSlug = $program->slug;

        $response = $this->actingAs($this->admin)->put(route('admin.programs.update', $program), $this->validPayload([
            'name' => 'Program Awal',
            'price' => '900000',
            'is_active' => '0',
        ]));

        $response->assertRedirect(route('admin.programs.show', $program));
        $response->assertSessionHas('success');

        $program->refresh();
        $this->assertSame('900000.00', $program->price);
        $this->assertFalse($program->is_active);
        $this->assertSame($originalSlug, $program->slug);
        $this->assertSame('100000.00', $otherProgram->fresh()->price);
    }

    /**
     * Test validasi edit: nama boleh tetap sama, tetapi tidak boleh memakai nama program lain.
     */
    public function test_update_validation(): void
    {
        $program = Program::factory()->create(['name' => 'Program Satu']);
        Program::factory()->create(['name' => 'Program Dua']);

        $this->actingAs($this->admin)
            ->put(route('admin.programs.update', $program), $this->validPayload(['name' => 'Program Satu']))
            ->assertSessionHasNoErrors();

        $this->actingAs($this->admin)
            ->put(route('admin.programs.update', $program), $this->validPayload(['name' => 'Program Dua', 'price' => '-1']))
            ->assertSessionHasErrors(['name', 'price']);

        $this->assertSame('Program Satu', $program->fresh()->name);
    }

    /**
     * Test status program dapat dinonaktifkan dan diaktifkan kembali.
     */
    public function test_admin_can_toggle_program_status(): void
    {
        $program = Program::factory()->create();

        $this->actingAs($this->admin)
            ->get(route('admin.programs.index'))
            ->assertSee('data-confirm-title="Nonaktifkan Program"', false);

        $this->actingAs($this->admin)
            ->from(route('admin.programs.index'))
            ->patch(route('admin.programs.toggle-status', $program))
            ->assertRedirect(route('admin.programs.index'))
            ->assertSessionHas('success', "Program {$program->name} berhasil dinonaktifkan.");
        $this->assertFalse($program->fresh()->is_active);

        $this->actingAs($this->admin)
            ->patch(route('admin.programs.toggle-status', $program))
            ->assertSessionHas('success', "Program {$program->name} berhasil diaktifkan.");
        $this->assertTrue($program->fresh()->is_active);
    }

    /**
     * Test program yang belum dipakai dapat dihapus melalui HTTP DELETE.
     */
    public function test_unused_program_can_be_deleted(): void
    {
        $program = Program::factory()->create();

        $this->actingAs($this->admin)
            ->get(route('admin.programs.index'))
            ->assertSee('data-confirm=', false);

        $this->actingAs($this->admin)
            ->delete(route('admin.programs.destroy', $program))
            ->assertRedirect(route('admin.programs.index'))
            ->assertSessionHas('success');

        $this->assertModelMissing($program);
    }

    /**
     * Test program yang sudah dipakai data lain tidak dihapus dan admin diarahkan untuk menonaktifkannya.
     */
    public function test_program_in_use_cannot_be_deleted(): void
    {
        $program = Program::factory()->create(['name' => 'Program Dipakai']);

        $this->app->bind(Program::class, fn () => new class extends Program
        {
            protected $table = 'programs';

            public function hasDependentRecords(): bool
            {
                return true;
            }
        });

        $this->actingAs($this->admin)
            ->get(route('admin.programs.show', $program))
            ->assertOk()->assertSee('tidak dapat dihapus')
            ->assertDontSee('id="btn-delete-program"', false);

        $this->actingAs($this->admin)
            ->from(route('admin.programs.show', $program))
            ->delete(route('admin.programs.destroy', $program))
            ->assertRedirect(route('admin.programs.show', $program))
            ->assertSessionHas('error', 'Program Program Dipakai tidak dapat dihapus karena sudah digunakan oleh kelas, pendaftaran, atau pembayaran. Nonaktifkan program ini agar tidak ditawarkan lagi.');

        $this->assertModelExists($program);
    }

    /**
     * Test program yang sudah memiliki kelas tidak dapat dihapus (relasi nyata ke tabel classes).
     */
    public function test_program_with_classes_cannot_be_deleted(): void
    {
        $program = Program::factory()->create(['name' => 'Program Berkelas']);
        Classroom::factory()->for($program)->withoutTeacher()->create();

        $this->actingAs($this->admin)
            ->get(route('admin.programs.show', $program))
            ->assertOk()->assertSee('tidak dapat dihapus');

        $this->actingAs($this->admin)
            ->delete(route('admin.programs.destroy', $program))
            ->assertSessionHas('error');

        $this->assertModelExists($program);
        $this->assertDatabaseCount('classes', 1);
    }

    /**
     * Test penghapusan wajib memakai HTTP DELETE.
     */
    public function test_delete_requires_delete_method(): void
    {
        $program = Program::factory()->create();

        $this->actingAs($this->admin)
            ->post('/admin/program/'.$program->id)
            ->assertMethodNotAllowed();

        $this->assertModelExists($program);
    }

    /**
     * Test guru, murid, dan tamu tidak dapat mengakses modul program.
     */
    public function test_non_admin_users_cannot_manage_programs(): void
    {
        $program = Program::factory()->create();

        foreach ([User::factory()->teacher()->create(), User::factory()->student()->create()] as $user) {
            $this->actingAs($user)->get(route('admin.programs.index'))->assertForbidden();
            $this->actingAs($user)->get(route('admin.programs.create'))->assertForbidden();
            $this->actingAs($user)->post(route('admin.programs.store'), $this->validPayload())->assertForbidden();
            $this->actingAs($user)->get(route('admin.programs.show', $program))->assertForbidden();
            $this->actingAs($user)->get(route('admin.programs.edit', $program))->assertForbidden();
            $this->actingAs($user)->put(route('admin.programs.update', $program), $this->validPayload())->assertForbidden();
            $this->actingAs($user)->patch(route('admin.programs.toggle-status', $program))->assertForbidden();
            $this->actingAs($user)->delete(route('admin.programs.destroy', $program))->assertForbidden();
        }

        auth()->logout();
        $this->get(route('admin.programs.index'))->assertRedirect(route('login'));
        $this->delete(route('admin.programs.destroy', $program))->assertRedirect(route('login'));

        $this->assertModelExists($program);
        $this->assertTrue($program->fresh()->is_active);
        $this->assertDatabaseCount('programs', 1);
    }

    /**
     * Test seeder mengimpor katalog program dari config tanpa duplikasi saat dijalankan ulang.
     */
    public function test_program_seeder_imports_config_catalog_once(): void
    {
        $this->seed(ProgramSeeder::class);

        $this->assertDatabaseCount('programs', count(config('bimbel.programs')));
        $this->assertDatabaseHas('programs', ['slug' => 'sd', 'level' => 'sd', 'price' => 350000]);
        $this->assertDatabaseHas('programs', ['slug' => 'persiapan-ujian', 'level' => 'umum']);

        Program::where('slug', 'sd')->update(['price' => 999000]);
        $this->seed(ProgramSeeder::class);

        $this->assertDatabaseCount('programs', count(config('bimbel.programs')));
        $this->assertDatabaseHas('programs', ['slug' => 'sd', 'price' => 999000]);
    }

    /**
     * Test menu Program Bimbel di sidebar aktif dan mengarah ke halaman daftar program.
     */
    public function test_sidebar_links_to_program_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.programs.index'));

        $this->assertMatchesRegularExpression(
            '/href="'.preg_quote(route('admin.programs.index'), '/').'"\s+class="admin-nav-item is-active"/',
            $response->getContent()
        );
    }
}
