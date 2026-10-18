<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test tamu diarahkan ke halaman login saat membuka dashboard.
     */
    public function test_guest_is_redirected_to_login_from_dashboards(): void
    {
        foreach (['dashboard', 'admin.dashboard', 'teacher.dashboard', 'student.dashboard'] as $routeName) {
            $this->get(route($routeName))->assertRedirect(route('login'));
        }
    }

    /**
     * Test setiap role dapat membuka dashboard miliknya sendiri.
     */
    public function test_each_role_can_access_its_own_dashboard(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Admin');

        $this->actingAs(User::factory()->teacher()->create())
            ->get(route('teacher.dashboard'))
            ->assertOk()
            ->assertSee('Jadwal Mengajar Hari Ini');

        $this->actingAs(User::factory()->student()->create())
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Murid');
    }

    /**
     * Test murid tidak dapat mengakses dashboard admin maupun guru.
     */
    public function test_student_cannot_access_admin_or_teacher_dashboard(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($student)->get(route('teacher.dashboard'))->assertForbidden();
    }

    /**
     * Test guru tidak dapat mengakses dashboard admin.
     */
    public function test_teacher_cannot_access_admin_dashboard(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($teacher)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($teacher)->get(route('student.dashboard'))->assertForbidden();
    }

    /**
     * Test route dashboard umum mengarahkan ke dashboard sesuai role.
     */
    public function test_generic_dashboard_route_redirects_by_role(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('dashboard'))
            ->assertRedirect(route('admin.dashboard'));

        $this->actingAs(User::factory()->teacher()->create())
            ->get(route('dashboard'))
            ->assertRedirect(route('teacher.dashboard'));

        $this->actingAs(User::factory()->student()->create())
            ->get(route('dashboard'))
            ->assertRedirect(route('student.dashboard'));
    }

    /**
     * Test pengguna yang sudah login diarahkan dari halaman login dan register ke dashboard-nya.
     */
    public function test_authenticated_user_is_redirected_away_from_auth_pages(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($teacher)->get(route('login'))->assertRedirect(route('teacher.dashboard'));
        $this->actingAs($teacher)->get(route('register'))->assertRedirect(route('teacher.dashboard'));
        $this->actingAs($teacher)->post(route('register.store'), [])->assertRedirect(route('teacher.dashboard'));
    }

    /**
     * Test navbar menampilkan link login/register untuk tamu dan dashboard/logout untuk pengguna login.
     */
    public function test_navbar_shows_auth_links_based_on_state(): void
    {
        $this->get(route('program.index'))
            ->assertSee(route('login'), false)
            ->assertSee(route('register'), false);

        $this->actingAs(User::factory()->create())
            ->get(route('program.index'))
            ->assertSee(route('dashboard'), false)
            ->assertSee(route('logout'), false);
    }
}
