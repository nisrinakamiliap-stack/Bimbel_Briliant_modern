<?php

namespace Tests\Feature\Teacher;

use App\Models\TeacherProfile;
use App\Models\User;
use App\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    private User $teacher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->teacher = User::factory()->teacher()->create([
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'phone' => '081200000000',
        ]);
        TeacherProfile::factory()->for($this->teacher)->create(['subject' => 'Matematika', 'address' => 'Jl. Lama', 'bio' => null]);
    }

    /**
     * Test halaman profil menampilkan data akun guru yang login.
     */
    public function test_profile_page_shows_own_data(): void
    {
        $this->actingAs($this->teacher)
            ->get(route('teacher.profile.edit'))
            ->assertOk()
            ->assertSee('Budi Santoso')
            ->assertSee('budi@example.com')
            ->assertSee('Matematika')
            ->assertSee('081200000000');
    }

    /**
     * Test guru dapat memperbarui telepon, alamat, dan bio.
     */
    public function test_teacher_can_update_allowed_fields(): void
    {
        $this->actingAs($this->teacher)
            ->put(route('teacher.profile.update'), ['phone' => '0812-3456-7890', 'address' => 'Jl. Pemuda 10', 'bio' => 'Mengajar matematika SMA.'])
            ->assertRedirect(route('teacher.profile.edit'))
            ->assertSessionHas('success');

        $this->teacher->refresh();
        $this->assertSame('081234567890', $this->teacher->phone);
        $this->assertSame('Jl. Pemuda 10', $this->teacher->teacherProfile->address);
        $this->assertSame('Mengajar matematika SMA.', $this->teacher->teacherProfile->bio);
    }

    /**
     * Test nama, email, role, dan bidang ajar tidak dapat diubah dari halaman profil.
     */
    public function test_protected_fields_cannot_be_changed(): void
    {
        $this->actingAs($this->teacher)
            ->put(route('teacher.profile.update'), [
                'phone' => '081234567890',
                'name' => 'Nama Palsu',
                'email' => 'palsu@example.com',
                'role' => 'admin',
                'subject' => 'Fisika',
            ])
            ->assertSessionHasNoErrors();

        $this->teacher->refresh();
        $this->assertSame('Budi Santoso', $this->teacher->name);
        $this->assertSame('budi@example.com', $this->teacher->email);
        $this->assertSame(UserRole::Teacher, $this->teacher->role);
        $this->assertSame('Matematika', $this->teacher->teacherProfile->subject);
    }

    /**
     * Test nomor telepon divalidasi.
     */
    public function test_phone_is_validated(): void
    {
        $this->actingAs($this->teacher)
            ->put(route('teacher.profile.update'), ['phone' => '12345'])
            ->assertSessionHasErrors('phone');

        $this->assertSame('081200000000', $this->teacher->fresh()->phone);
    }

    /**
     * Test murid dan admin tidak dapat memakai endpoint profil guru.
     */
    public function test_non_teacher_cannot_update_teacher_profile(): void
    {
        foreach ([User::factory()->student()->create(), User::factory()->admin()->create()] as $user) {
            $this->actingAs($user)->put(route('teacher.profile.update'), ['phone' => '081234567890'])->assertForbidden();
        }
    }
}
