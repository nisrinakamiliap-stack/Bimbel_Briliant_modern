<?php

namespace Tests\Feature\Admin;

use App\EducationLevel;
use App\Models\StudentProfile;
use App\Models\User;
use App\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudentManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    /**
     * Data form murid yang valid.
     *
     * @return array<string, string>
     */
    private function validPayload(array $overrides = []): array
    {
        return [
            'name' => 'Dewi Lestari',
            'email' => 'dewi@example.com',
            'phone' => '0812-1111-2222',
            'education_level' => EducationLevel::Smp->value,
            'school_name' => 'SMP Negeri 3',
            'birth_date' => '2012-05-17',
            'address' => 'Jl. Melati No. 5, Jakarta',
            'parent_name' => 'Bambang Lestari',
            'parent_phone' => '081333334444',
            ...$overrides,
        ];
    }

    /**
     * Test daftar murid hanya menampilkan akun ber-role murid dari database.
     */
    public function test_index_lists_only_students(): void
    {
        $student = User::factory()->student()->has(StudentProfile::factory(), 'studentProfile')->create(['name' => 'Rizky Murid']);
        User::factory()->teacher()->create(['name' => 'Pak Guru Tono']);

        $response = $this->actingAs($this->admin)->get(route('admin.students.index'));

        $response->assertOk();
        $response->assertViewIs('admin.students.index');
        $response->assertSee('Rizky Murid');
        $response->assertSee($student->email);
        $response->assertSee($student->studentProfile->education_level->label());
        $response->assertDontSee('Pak Guru Tono');
        $response->assertDontSee($this->admin->email.'</td>', false);
        $response->assertSee('Tambah Murid');
    }

    /**
     * Test empty state tampil jika belum ada murid.
     */
    public function test_index_shows_empty_state_without_students(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.students.index'))
            ->assertOk()
            ->assertSee('Belum ada data murid');
    }

    /**
     * Test pencarian berdasarkan nama maupun email.
     */
    public function test_index_can_search_by_name_or_email(): void
    {
        User::factory()->student()->create(['name' => 'Andi Pratama', 'email' => 'andi@example.com']);
        User::factory()->student()->create(['name' => 'Budi Santoso', 'email' => 'budi@contoh.id']);

        $this->actingAs($this->admin)
            ->get(route('admin.students.index', ['q' => 'andi']))
            ->assertSee('Andi Pratama')
            ->assertDontSee('Budi Santoso');

        $this->actingAs($this->admin)
            ->get(route('admin.students.index', ['q' => 'contoh.id']))
            ->assertSee('Budi Santoso')
            ->assertDontSee('Andi Pratama');

        $this->actingAs($this->admin)
            ->get(route('admin.students.index', ['q' => 'tidak-ada']))
            ->assertSee('Murid tidak ditemukan');
    }

    /**
     * Test filter berdasarkan jenjang pendidikan.
     */
    public function test_index_can_filter_by_education_level(): void
    {
        StudentProfile::factory()
            ->for(User::factory()->student()->state(['name' => 'Siswa SD Satu']))
            ->create(['education_level' => EducationLevel::Sd]);
        StudentProfile::factory()
            ->for(User::factory()->student()->state(['name' => 'Siswa SMA Dua']))
            ->create(['education_level' => EducationLevel::Sma]);

        $this->actingAs($this->admin)
            ->get(route('admin.students.index', ['jenjang' => 'sma']))
            ->assertSee('Siswa SMA Dua')
            ->assertDontSee('Siswa SD Satu');
    }

    /**
     * Test filter jenjang yang tidak valid ditolak.
     */
    public function test_index_rejects_invalid_filter(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.students.index', ['jenjang' => 'kuliah']))
            ->assertSessionHasErrors('jenjang');
    }

    /**
     * Test pagination membagi daftar murid per 10 data dan mempertahankan query pencarian.
     */
    public function test_index_is_paginated(): void
    {
        User::factory()->student()->count(12)->create();

        $firstPage = $this->actingAs($this->admin)->get(route('admin.students.index'));
        $firstPage->assertOk();
        $this->assertCount(10, $firstPage->viewData('students'));
        $firstPage->assertSee('Menampilkan 1–10 dari 12 data');

        $secondPage = $this->actingAs($this->admin)->get(route('admin.students.index', ['page' => 2]));
        $this->assertCount(2, $secondPage->viewData('students'));

        $searchPage = $this->actingAs($this->admin)->get(route('admin.students.index', ['q' => '@']));
        $searchPage->assertSee('q=%40&amp;page=2', false);
    }

    /**
     * Test admin dapat menambahkan murid beserta profilnya.
     */
    public function test_admin_can_create_student_with_profile(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.students.store'), $this->validPayload([
            'password' => 'Rahasia123',
            'password_confirmation' => 'Rahasia123',
        ]));

        $student = User::where('email', 'dewi@example.com')->firstOrFail();

        $response->assertRedirect(route('admin.students.show', $student));
        $response->assertSessionHas('success');

        $this->assertSame(UserRole::Student, $student->role);
        $this->assertSame('081211112222', $student->phone);
        $this->assertTrue(Hash::check('Rahasia123', $student->password));
        $this->assertDatabaseHas('student_profiles', [
            'user_id' => $student->id,
            'education_level' => 'smp',
            'school_name' => 'SMP Negeri 3',
            'parent_phone' => '081333334444',
        ]);
    }

    /**
     * Test role dari form tambah murid diabaikan.
     */
    public function test_role_cannot_be_set_when_creating_student(): void
    {
        $this->actingAs($this->admin)->post(route('admin.students.store'), $this->validPayload([
            'password' => 'Rahasia123',
            'password_confirmation' => 'Rahasia123',
            'role' => 'admin',
        ]));

        $this->assertSame(UserRole::Student, User::where('email', 'dewi@example.com')->firstOrFail()->role);
    }

    /**
     * Test validasi form tambah murid.
     */
    public function test_create_student_validation(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.students.store'), [])
            ->assertSessionHasErrors(['name', 'email', 'phone', 'education_level', 'password']);

        $this->actingAs($this->admin)
            ->post(route('admin.students.store'), $this->validPayload([
                'phone' => '123',
                'parent_phone' => 'abc',
                'education_level' => 'kuliah',
                'birth_date' => now()->addDay()->format('Y-m-d'),
                'password' => 'Rahasia123',
                'password_confirmation' => 'Berbeda123',
            ]))
            ->assertSessionHasErrors(['phone', 'parent_phone', 'education_level', 'birth_date', 'password']);

        $this->assertDatabaseCount('student_profiles', 0);
    }

    /**
     * Test email duplikat ditolak, termasuk email akun yang sudah dihapus.
     */
    public function test_create_student_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'dewi@example.com']);
        User::factory()->create(['email' => 'arsip@example.com'])->delete();

        $this->actingAs($this->admin)
            ->post(route('admin.students.store'), $this->validPayload(['email' => 'DEWI@example.com', 'password' => 'Rahasia123', 'password_confirmation' => 'Rahasia123']))
            ->assertSessionHasErrors(['email' => 'Email ini sudah digunakan oleh akun lain.']);

        $this->actingAs($this->admin)
            ->post(route('admin.students.store'), $this->validPayload(['email' => 'arsip@example.com', 'password' => 'Rahasia123', 'password_confirmation' => 'Rahasia123']))
            ->assertSessionHasErrors('email');
    }

    /**
     * Test halaman form tambah murid dapat dibuka.
     */
    public function test_create_page_is_accessible(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.students.create'))
            ->assertOk()
            ->assertSee('name="password"', false)
            ->assertDontSee('name="role"', false);
    }

    /**
     * Test halaman detail menampilkan data akun dan profil murid.
     */
    public function test_admin_can_view_student_detail(): void
    {
        $profile = StudentProfile::factory()->create(['school_name' => 'SD Harapan Bangsa']);

        $this->actingAs($this->admin)
            ->get(route('admin.students.show', $profile->user))
            ->assertOk()
            ->assertSee($profile->user->name)
            ->assertSee($profile->user->email)
            ->assertSee('SD Harapan Bangsa');
    }

    /**
     * Test detail murid yang belum punya profil menampilkan pesan yang sesuai.
     */
    public function test_detail_of_student_without_profile_shows_notice(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($this->admin)
            ->get(route('admin.students.show', $student))
            ->assertOk()
            ->assertSee('Profil belum dilengkapi');
    }

    /**
     * Test route murid tidak dapat dipakai untuk membuka akun admin atau guru.
     */
    public function test_student_routes_do_not_resolve_non_student_users(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($this->admin)->get(route('admin.students.show', $teacher))->assertNotFound();
        $this->actingAs($this->admin)->get(route('admin.students.edit', $this->admin))->assertNotFound();
        $this->actingAs($this->admin)->delete(route('admin.students.destroy', $teacher))->assertNotFound();

        $this->assertNotSoftDeleted($teacher);
    }

    /**
     * Test form edit terisi data murid yang dipilih.
     */
    public function test_edit_page_is_prefilled(): void
    {
        $profile = StudentProfile::factory()->create(['school_name' => 'SMA Tunas Muda']);

        $this->actingAs($this->admin)
            ->get(route('admin.students.edit', $profile->user))
            ->assertOk()
            ->assertSee('value="'.$profile->user->email.'"', false)
            ->assertSee('SMA Tunas Muda')
            ->assertDontSee('name="password"', false);
    }

    /**
     * Test admin dapat memperbarui murid tanpa mengubah password dan role.
     */
    public function test_admin_can_update_student(): void
    {
        $profile = StudentProfile::factory()->create();
        $student = $profile->user;
        $originalPassword = $student->password;

        $response = $this->actingAs($this->admin)->put(route('admin.students.update', $student), $this->validPayload([
            'email' => $student->email,
            'role' => 'admin',
            'password' => 'PasswordBaru123',
        ]));

        $response->assertRedirect(route('admin.students.show', $student));
        $response->assertSessionHas('success');

        $student->refresh();
        $this->assertSame('Dewi Lestari', $student->name);
        $this->assertSame(UserRole::Student, $student->role);
        $this->assertSame($originalPassword, $student->password);
        $this->assertSame(EducationLevel::Smp, $student->studentProfile->education_level);
        $this->assertSame(1, StudentProfile::where('user_id', $student->id)->count());
    }

    /**
     * Test edit murid yang belum punya profil akan membuat profil baru.
     */
    public function test_update_creates_profile_when_missing(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($this->admin)
            ->put(route('admin.students.update', $student), $this->validPayload(['email' => $student->email]))
            ->assertRedirect(route('admin.students.show', $student));

        $this->assertDatabaseHas('student_profiles', ['user_id' => $student->id, 'education_level' => 'smp']);
    }

    /**
     * Test aturan unique email mengecualikan murid yang sedang diedit, tetapi menolak email milik akun lain.
     */
    public function test_update_unique_email_rule(): void
    {
        $student = User::factory()->student()->create(['email' => 'saya@example.com']);
        User::factory()->create(['email' => 'orang-lain@example.com']);

        $this->actingAs($this->admin)
            ->put(route('admin.students.update', $student), $this->validPayload(['email' => 'saya@example.com']))
            ->assertSessionHasNoErrors();

        $this->actingAs($this->admin)
            ->put(route('admin.students.update', $student), $this->validPayload(['email' => 'orang-lain@example.com']))
            ->assertSessionHasErrors('email');

        $this->assertSame('saya@example.com', $student->fresh()->email);
    }

    /**
     * Test admin dapat menghapus murid secara soft delete; akun tidak bisa login lagi.
     */
    public function test_admin_can_soft_delete_student(): void
    {
        $profile = StudentProfile::factory()->create();
        $student = $profile->user;

        $this->actingAs($this->admin)
            ->get(route('admin.students.index'))
            ->assertSee('data-confirm=', false);

        $this->actingAs($this->admin)
            ->delete(route('admin.students.destroy', $student))
            ->assertRedirect(route('admin.students.index'))
            ->assertSessionHas('success');

        $this->assertSoftDeleted($student);
        $this->assertDatabaseHas('student_profiles', ['id' => $profile->id]);

        $this->actingAs($this->admin)
            ->get(route('admin.students.index'))
            ->assertDontSee($student->email);

        $this->actingAs($this->admin)->get(route('admin.students.show', $student))->assertNotFound();

        auth()->logout();
        $this->post(route('login.store'), ['email' => $student->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * Test penghapusan wajib memakai HTTP DELETE.
     */
    public function test_delete_requires_delete_method(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($this->admin)
            ->post('/admin/murid/'.$student->id)
            ->assertMethodNotAllowed();

        $this->assertNotSoftDeleted($student);
    }

    /**
     * Test guru dan murid tidak dapat mengakses fitur pengelolaan murid.
     */
    public function test_non_admin_users_cannot_manage_students(): void
    {
        $student = User::factory()->student()->create();

        foreach ([User::factory()->teacher()->create(), User::factory()->student()->create()] as $user) {
            $this->actingAs($user)->get(route('admin.students.index'))->assertForbidden();
            $this->actingAs($user)->get(route('admin.students.create'))->assertForbidden();
            $this->actingAs($user)->post(route('admin.students.store'), $this->validPayload())->assertForbidden();
            $this->actingAs($user)->get(route('admin.students.show', $student))->assertForbidden();
            $this->actingAs($user)->put(route('admin.students.update', $student), $this->validPayload())->assertForbidden();
            $this->actingAs($user)->delete(route('admin.students.destroy', $student))->assertForbidden();
        }

        $this->assertNotSoftDeleted($student);
        $this->assertDatabaseMissing('users', ['email' => 'dewi@example.com']);
    }

    /**
     * Test tamu diarahkan ke halaman login.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.students.index'))->assertRedirect(route('login'));
        $this->post(route('admin.students.store'), $this->validPayload())->assertRedirect(route('login'));
    }

    /**
     * Test menu Data Murid di sidebar aktif dan mengarah ke halaman daftar murid.
     */
    public function test_sidebar_links_to_student_page(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.dashboard'))
            ->assertSee('href="'.route('admin.students.index').'"', false);

        $this->actingAs($this->admin)
            ->get(route('admin.students.index'))
            ->assertSee('aria-current="page"', false);
    }
}
