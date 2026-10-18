<?php

namespace Tests\Feature\Admin;

use App\EducationDegree;
use App\Models\Classroom;
use App\Models\Program;
use App\Models\TeacherProfile;
use App\Models\User;
use App\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TeacherManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    /**
     * Data form guru yang valid.
     *
     * @param  array<string, string>  $overrides
     * @return array<string, string>
     */
    private function validPayload(array $overrides = []): array
    {
        return [
            'name' => 'Sari Wahyuni',
            'email' => 'sari@example.com',
            'phone' => '0812-5555-6666',
            'subject' => '  Matematika  ',
            'last_education' => EducationDegree::S2->value,
            'institution' => 'Universitas Indonesia',
            'address' => 'Jl. Kenanga No. 10, Jakarta',
            'bio' => 'Pengajar matematika berpengalaman 8 tahun.',
            ...$overrides,
        ];
    }

    /**
     * Payload tambah guru lengkap dengan password awal.
     *
     * @param  array<string, string>  $overrides
     * @return array<string, string>
     */
    private function createPayload(array $overrides = []): array
    {
        return $this->validPayload([
            'password' => 'Rahasia123',
            'password_confirmation' => 'Rahasia123',
            ...$overrides,
        ]);
    }

    /**
     * Test daftar guru hanya menampilkan akun ber-role guru dari database.
     */
    public function test_index_lists_only_teachers(): void
    {
        $profile = TeacherProfile::factory()
            ->for(User::factory()->teacher()->state(['name' => 'Budi Pengajar']))
            ->create(['subject' => 'Fisika']);
        User::factory()->student()->create(['name' => 'Murid Lain']);

        $response = $this->actingAs($this->admin)->get(route('admin.teachers.index'));

        $response->assertOk();
        $response->assertViewIs('admin.teachers.index');
        $response->assertSee('Budi Pengajar');
        $response->assertSee($profile->user->email);
        $response->assertSee('Fisika');
        $response->assertDontSee('Murid Lain');
        $response->assertSee('Tambah Guru');
    }

    /**
     * Test empty state tampil jika belum ada guru.
     */
    public function test_index_shows_empty_state_without_teachers(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.teachers.index'))
            ->assertOk()
            ->assertSee('Belum ada data guru');
    }

    /**
     * Test pencarian guru berdasarkan nama maupun email.
     */
    public function test_index_can_search_by_name_or_email(): void
    {
        User::factory()->teacher()->create(['name' => 'Dimas Prasetyo', 'email' => 'dimas@example.com']);
        User::factory()->teacher()->create(['name' => 'Ratna Kusuma', 'email' => 'ratna@sekolah.id']);

        $this->actingAs($this->admin)
            ->get(route('admin.teachers.index', ['q' => 'dimas']))
            ->assertSee('Dimas Prasetyo')
            ->assertDontSee('Ratna Kusuma');

        $this->actingAs($this->admin)
            ->get(route('admin.teachers.index', ['q' => 'sekolah.id']))
            ->assertSee('Ratna Kusuma')
            ->assertDontSee('Dimas Prasetyo');

        $this->actingAs($this->admin)
            ->get(route('admin.teachers.index', ['q' => 'tidak-ada']))
            ->assertSee('Guru tidak ditemukan');
    }

    /**
     * Test filter berdasarkan bidang keahlian dan opsi filter berasal dari database.
     */
    public function test_index_can_filter_by_subject(): void
    {
        TeacherProfile::factory()->for(User::factory()->teacher()->state(['name' => 'Guru Kimia Satu']))->create(['subject' => 'Kimia']);
        TeacherProfile::factory()->for(User::factory()->teacher()->state(['name' => 'Guru Biologi Dua']))->create(['subject' => 'Biologi']);

        $response = $this->actingAs($this->admin)->get(route('admin.teachers.index', ['bidang' => 'Kimia']));

        $response->assertSee('Guru Kimia Satu');
        $response->assertDontSee('Guru Biologi Dua');
        $response->assertViewHas('subjects', fn ($subjects) => $subjects->values()->all() === ['Biologi', 'Kimia']);
    }

    /**
     * Test pilihan filter bidang tidak memuat bidang milik guru yang sudah dihapus.
     */
    public function test_subject_options_exclude_deleted_teachers(): void
    {
        TeacherProfile::factory()->create(['subject' => 'Ekonomi']);
        TeacherProfile::factory()->create(['subject' => 'Sejarah'])->user->delete();

        $this->actingAs($this->admin)
            ->get(route('admin.teachers.index'))
            ->assertViewHas('subjects', fn ($subjects) => $subjects->values()->all() === ['Ekonomi']);
    }

    /**
     * Test pagination membagi daftar guru per 10 data dan mempertahankan query pencarian.
     */
    public function test_index_is_paginated(): void
    {
        User::factory()->teacher()->count(13)->create();

        $firstPage = $this->actingAs($this->admin)->get(route('admin.teachers.index'));
        $this->assertCount(10, $firstPage->viewData('teachers'));
        $firstPage->assertSee('Menampilkan 1–10 dari 13 data');

        $secondPage = $this->actingAs($this->admin)->get(route('admin.teachers.index', ['page' => 2]));
        $this->assertCount(3, $secondPage->viewData('teachers'));

        $this->actingAs($this->admin)
            ->get(route('admin.teachers.index', ['q' => '@']))
            ->assertSee('q=%40&amp;page=2', false);
    }

    /**
     * Test admin dapat menambahkan guru beserta profilnya.
     */
    public function test_admin_can_create_teacher_with_profile(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.teachers.store'), $this->createPayload());

        $teacher = User::where('email', 'sari@example.com')->firstOrFail();

        $response->assertRedirect(route('admin.teachers.show', $teacher));
        $response->assertSessionHas('success');

        $this->assertSame(UserRole::Teacher, $teacher->role);
        $this->assertSame('081255556666', $teacher->phone);
        $this->assertTrue(Hash::check('Rahasia123', $teacher->password));
        $this->assertDatabaseHas('teacher_profiles', [
            'user_id' => $teacher->id,
            'subject' => 'Matematika',
            'last_education' => 's2',
            'institution' => 'Universitas Indonesia',
        ]);
    }

    /**
     * Test guru baru dapat login dan diarahkan ke dashboard guru.
     */
    public function test_created_teacher_can_log_in(): void
    {
        $this->actingAs($this->admin)->post(route('admin.teachers.store'), $this->createPayload());
        auth()->logout();

        $this->post(route('login.store'), ['email' => 'sari@example.com', 'password' => 'Rahasia123'])
            ->assertRedirect(route('teacher.dashboard'));
    }

    /**
     * Test role dari form tambah guru diabaikan, sehingga tidak dapat membuat admin.
     */
    public function test_role_cannot_be_set_when_creating_teacher(): void
    {
        $this->actingAs($this->admin)->post(route('admin.teachers.store'), $this->createPayload(['role' => 'admin']));

        $this->assertSame(UserRole::Teacher, User::where('email', 'sari@example.com')->firstOrFail()->role);
        $this->assertSame(1, User::where('role', UserRole::Admin)->count());
    }

    /**
     * Test validasi form tambah guru.
     */
    public function test_create_teacher_validation(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.teachers.store'), [])
            ->assertSessionHasErrors(['name', 'email', 'phone', 'subject', 'password']);

        $this->actingAs($this->admin)
            ->post(route('admin.teachers.store'), $this->createPayload([
                'email' => 'bukan-email',
                'phone' => '021',
                'last_education' => 'sd',
                'password_confirmation' => 'Berbeda123',
            ]))
            ->assertSessionHasErrors(['email', 'phone', 'last_education', 'password']);

        $this->assertDatabaseCount('teacher_profiles', 0);
    }

    /**
     * Test email duplikat ditolak sehingga tidak terbentuk akun ganda.
     */
    public function test_create_teacher_rejects_duplicate_email(): void
    {
        User::factory()->student()->create(['email' => 'sari@example.com']);
        User::factory()->teacher()->create(['email' => 'arsip@example.com'])->delete();

        $this->actingAs($this->admin)
            ->post(route('admin.teachers.store'), $this->createPayload(['email' => 'SARI@example.com']))
            ->assertSessionHasErrors(['email' => 'Email ini sudah digunakan oleh akun lain.']);

        $this->actingAs($this->admin)
            ->post(route('admin.teachers.store'), $this->createPayload(['email' => 'arsip@example.com']))
            ->assertSessionHasErrors('email');

        $this->assertSame(1, User::withTrashed()->where('email', 'sari@example.com')->count());
        $this->assertDatabaseCount('teacher_profiles', 0);
    }

    /**
     * Test halaman form tambah guru dapat dibuka tanpa field role.
     */
    public function test_create_page_is_accessible(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.teachers.create'))
            ->assertOk()
            ->assertSee('name="password"', false)
            ->assertSee('name="subject"', false)
            ->assertDontSee('name="role"', false);
    }

    /**
     * Test halaman detail menampilkan data akun, profil, dan bagian kelas yang diampu.
     */
    public function test_admin_can_view_teacher_detail(): void
    {
        $profile = TeacherProfile::factory()->create([
            'subject' => 'Bahasa Inggris',
            'institution' => 'Universitas Gadjah Mada',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.teachers.show', $profile->user))
            ->assertOk()
            ->assertSee($profile->user->name)
            ->assertSee($profile->user->email)
            ->assertSee('Bahasa Inggris')
            ->assertSee('Universitas Gadjah Mada')
            ->assertSee('Belum mengampu kelas');
    }

    /**
     * Test detail guru menampilkan kelas yang diampunya melalui relasi database.
     */
    public function test_teacher_detail_lists_taught_classes(): void
    {
        $teacher = User::factory()->teacher()->create();
        $program = Program::factory()->create(['name' => 'Program Persiapan SNBT']);
        Classroom::factory()->for($program)->create(['teacher_id' => $teacher->id, 'name' => 'Kelas SNBT Saintek A']);
        Classroom::factory()->for($program)->create(['name' => 'Kelas Guru Lain']);

        $this->actingAs($this->admin)
            ->get(route('admin.teachers.show', $teacher))
            ->assertOk()
            ->assertSee('Kelas SNBT Saintek A')
            ->assertSee('Program Persiapan SNBT')
            ->assertDontSee('Kelas Guru Lain');
    }

    /**
     * Test route guru tidak dapat dipakai untuk membuka akun murid atau admin, dan sebaliknya.
     */
    public function test_teacher_routes_only_resolve_teachers(): void
    {
        $student = User::factory()->student()->create();
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($this->admin)->get(route('admin.teachers.show', $student))->assertNotFound();
        $this->actingAs($this->admin)->get(route('admin.teachers.edit', $this->admin))->assertNotFound();
        $this->actingAs($this->admin)->delete(route('admin.teachers.destroy', $student))->assertNotFound();
        $this->actingAs($this->admin)->get(route('admin.students.show', $teacher))->assertNotFound();

        $this->assertNotSoftDeleted($student);
    }

    /**
     * Test form edit terisi data guru yang dipilih tanpa field password.
     */
    public function test_edit_page_is_prefilled(): void
    {
        $profile = TeacherProfile::factory()->create(['subject' => 'Geografi']);

        $this->actingAs($this->admin)
            ->get(route('admin.teachers.edit', $profile->user))
            ->assertOk()
            ->assertSee('value="'.$profile->user->email.'"', false)
            ->assertSee('value="Geografi"', false)
            ->assertDontSee('name="password"', false);
    }

    /**
     * Test admin dapat memperbarui guru tanpa mengubah password dan role.
     */
    public function test_admin_can_update_teacher(): void
    {
        $profile = TeacherProfile::factory()->create();
        $teacher = $profile->user;
        $originalPassword = $teacher->password;

        $response = $this->actingAs($this->admin)->put(route('admin.teachers.update', $teacher), $this->validPayload([
            'email' => $teacher->email,
            'role' => 'admin',
            'password' => 'PasswordBaru123',
        ]));

        $response->assertRedirect(route('admin.teachers.show', $teacher));
        $response->assertSessionHas('success');

        $teacher->refresh();
        $this->assertSame('Sari Wahyuni', $teacher->name);
        $this->assertSame(UserRole::Teacher, $teacher->role);
        $this->assertSame($originalPassword, $teacher->password);
        $this->assertSame('Matematika', $teacher->teacherProfile->subject);
        $this->assertSame(1, TeacherProfile::where('user_id', $teacher->id)->count());
    }

    /**
     * Test edit guru yang belum punya profil akan membuat profil baru.
     */
    public function test_update_creates_profile_when_missing(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($this->admin)
            ->put(route('admin.teachers.update', $teacher), $this->validPayload(['email' => $teacher->email]))
            ->assertRedirect(route('admin.teachers.show', $teacher));

        $this->assertDatabaseHas('teacher_profiles', ['user_id' => $teacher->id, 'subject' => 'Matematika']);
    }

    /**
     * Test aturan unique email mengecualikan guru yang sedang diedit, tetapi menolak email akun lain.
     */
    public function test_update_unique_email_rule(): void
    {
        $teacher = User::factory()->teacher()->create(['email' => 'guru@example.com']);
        User::factory()->student()->create(['email' => 'murid-lain@example.com']);

        $this->actingAs($this->admin)
            ->put(route('admin.teachers.update', $teacher), $this->validPayload(['email' => 'guru@example.com']))
            ->assertSessionHasNoErrors();

        $this->actingAs($this->admin)
            ->put(route('admin.teachers.update', $teacher), $this->validPayload(['email' => 'murid-lain@example.com']))
            ->assertSessionHasErrors('email');

        $this->assertSame('guru@example.com', $teacher->fresh()->email);
    }

    /**
     * Test admin dapat menghapus guru secara soft delete; akun tidak bisa login lagi.
     */
    public function test_admin_can_soft_delete_teacher(): void
    {
        $profile = TeacherProfile::factory()->create();
        $teacher = $profile->user;

        $this->actingAs($this->admin)
            ->get(route('admin.teachers.index'))
            ->assertSee('data-confirm=', false);

        $this->actingAs($this->admin)
            ->delete(route('admin.teachers.destroy', $teacher))
            ->assertRedirect(route('admin.teachers.index'))
            ->assertSessionHas('success');

        $this->assertSoftDeleted($teacher);
        $this->assertDatabaseHas('teacher_profiles', ['id' => $profile->id]);

        $this->actingAs($this->admin)->get(route('admin.teachers.index'))->assertDontSee($teacher->email);
        $this->actingAs($this->admin)->get(route('admin.teachers.show', $teacher))->assertNotFound();

        auth()->logout();
        $this->post(route('login.store'), ['email' => $teacher->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * Test statistik Total Guru di dashboard admin ikut berubah setelah tambah dan hapus guru.
     */
    public function test_dashboard_teacher_count_follows_changes(): void
    {
        $teachers = User::factory()->teacher()->count(2)->create();

        $this->actingAs($this->admin)->get(route('admin.dashboard'))
            ->assertViewHas('stats', fn (array $stats) => $stats['teachers'] === 2);

        $this->actingAs($this->admin)->delete(route('admin.teachers.destroy', $teachers->first()));

        $this->actingAs($this->admin)->get(route('admin.dashboard'))
            ->assertViewHas('stats', fn (array $stats) => $stats['teachers'] === 1);
    }

    /**
     * Test penghapusan wajib memakai HTTP DELETE.
     */
    public function test_delete_requires_delete_method(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($this->admin)
            ->post('/admin/guru/'.$teacher->id)
            ->assertMethodNotAllowed();

        $this->assertNotSoftDeleted($teacher);
    }

    /**
     * Test guru dan murid tidak dapat mengakses fitur pengelolaan guru.
     */
    public function test_non_admin_users_cannot_manage_teachers(): void
    {
        $teacher = User::factory()->teacher()->create();

        foreach ([User::factory()->teacher()->create(), User::factory()->student()->create()] as $user) {
            $this->actingAs($user)->get(route('admin.teachers.index'))->assertForbidden();
            $this->actingAs($user)->get(route('admin.teachers.create'))->assertForbidden();
            $this->actingAs($user)->post(route('admin.teachers.store'), $this->createPayload())->assertForbidden();
            $this->actingAs($user)->get(route('admin.teachers.show', $teacher))->assertForbidden();
            $this->actingAs($user)->get(route('admin.teachers.edit', $teacher))->assertForbidden();
            $this->actingAs($user)->put(route('admin.teachers.update', $teacher), $this->validPayload())->assertForbidden();
            $this->actingAs($user)->delete(route('admin.teachers.destroy', $teacher))->assertForbidden();
        }

        $this->assertNotSoftDeleted($teacher);
        $this->assertDatabaseMissing('users', ['email' => 'sari@example.com']);
    }

    /**
     * Test tamu diarahkan ke halaman login.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.teachers.index'))->assertRedirect(route('login'));
        $this->post(route('admin.teachers.store'), $this->createPayload())->assertRedirect(route('login'));
    }

    /**
     * Test menu Data Guru di sidebar aktif dan mengarah ke halaman daftar guru.
     */
    public function test_sidebar_links_to_teacher_page(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.dashboard'))
            ->assertSee('href="'.route('admin.teachers.index').'"', false);

        $response = $this->actingAs($this->admin)->get(route('admin.teachers.index'));
        $response->assertSee('admin-nav-item is-active', false);
        $this->assertMatchesRegularExpression(
            '/href="'.preg_quote(route('admin.teachers.index'), '/').'"\s+class="admin-nav-item is-active"/',
            $response->getContent()
        );
    }
}
