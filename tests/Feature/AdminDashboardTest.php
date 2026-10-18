<?php

namespace Tests\Feature;

use App\Models\Classroom;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test admin dapat membuka dashboard admin dengan layout panel admin.
     */
    public function test_admin_can_open_admin_dashboard(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Rina Admin']);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertViewIs('dashboard.admin');
        $response->assertSee('Dashboard Admin');
        $response->assertSee('Bimbel Sentra Edukasi');
        $response->assertSee('Rina Admin');
        $response->assertSee($admin->email);
    }

    /**
     * Test sidebar menampilkan seluruh menu dengan Dashboard sebagai menu aktif.
     */
    public function test_sidebar_shows_all_menus_with_dashboard_active(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertSeeInOrder([
            'Dashboard', 'Manajemen Murid', 'Data Guru', 'Program Bimbel', 'Data Kelas',
            'Jadwal Belajar', 'Pembayaran', 'Absensi', 'Pembelajaran', 'Notifikasi dan Pesan',
            'Laporan', 'Pengaturan',
        ]);
        $response->assertSee('aria-current="page"', false);
        $response->assertDontSee('aria-disabled="true"', false);
    }

    /**
     * Test statistik murid, guru, program, dan kelas dihitung dari database.
     */
    public function test_statistics_use_real_data(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->student()->count(3)->create();
        User::factory()->teacher()->count(2)->create();
        $program = Program::factory()->create();
        Program::factory()->create();
        Program::factory()->inactive()->create();
        Classroom::factory()->for($program)->withoutTeacher()->count(3)->create();
        Classroom::factory()->for($program)->withoutTeacher()->inactive()->create();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertViewHas('stats', [
            'students' => 3,
            'teachers' => 2,
            'programs' => 3,
            'active_programs' => 2,
            'classes' => 4,
            'active_classes' => 3,
        ]);
        $response->assertSee('2 program aktif');
        $response->assertSee('3 kelas aktif');
    }

    /**
     * Test empty state tampil saat belum ada pendaftaran, pembayaran, dan jadwal.
     */
    public function test_empty_states_are_shown_when_no_data_exists(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertSee('Belum ada pendaftaran');
        $response->assertSee('Tidak ada pembayaran tertunda');
        $response->assertSee('Belum ada jadwal mendatang');
    }

    /**
     * Test tombol logout pada dashboard terhubung ke route logout dan mengakhiri sesi.
     */
    public function test_logout_button_ends_admin_session(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertSee('action="'.route('logout').'"', false);

        $this->post(route('logout'))->assertRedirect(route('login'));
        $this->assertGuest();
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    /**
     * Test guru dan murid tidak dapat mengakses dashboard admin.
     */
    public function test_non_admin_users_are_forbidden(): void
    {
        $this->actingAs(User::factory()->teacher()->create())
            ->get(route('admin.dashboard'))
            ->assertForbidden();

        $this->actingAs(User::factory()->student()->create())
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    /**
     * Test tamu diarahkan ke halaman login.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    /**
     * Test admin yang login diarahkan ke dashboard admin.
     */
    public function test_admin_login_redirects_to_admin_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $this->post(route('login.store'), [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->get(route('admin.dashboard'))->assertOk();
    }
}
