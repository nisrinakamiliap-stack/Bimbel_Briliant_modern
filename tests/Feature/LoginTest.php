<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test halaman login dapat diakses tanpa pilihan role.
     */
    public function test_login_page_is_accessible_without_role_selection(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertSee('Masuk ke Akun Anda');
        $response->assertSee('name="email"', false);
        $response->assertSee('name="password"', false);
        $response->assertSee('name="remember"', false);
        $response->assertDontSee('name="role"', false);
        $response->assertSee(route('register'), false);
    }

    /**
     * Test login admin diarahkan ke dashboard admin.
     */
    public function test_admin_is_redirected_to_admin_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->post(route('login.store'), [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    /**
     * Test login guru diarahkan ke dashboard guru.
     */
    public function test_teacher_is_redirected_to_teacher_dashboard(): void
    {
        $teacher = User::factory()->teacher()->create();

        $response = $this->post(route('login.store'), [
            'email' => $teacher->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('teacher.dashboard'));
        $this->assertAuthenticatedAs($teacher);
    }

    /**
     * Test login murid diarahkan ke dashboard murid.
     */
    public function test_student_is_redirected_to_student_dashboard(): void
    {
        $student = User::factory()->student()->create();

        $response = $this->post(route('login.store'), [
            'email' => $student->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('student.dashboard'));
        $this->assertAuthenticatedAs($student);
    }

    /**
     * Test role dari request diabaikan saat login; role tetap dibaca dari database.
     */
    public function test_role_in_login_request_is_ignored(): void
    {
        $student = User::factory()->student()->create();

        $response = $this->post(route('login.store'), [
            'email' => $student->email,
            'password' => 'password',
            'role' => 'admin',
        ]);

        $response->assertRedirect(route('student.dashboard'));
    }

    /**
     * Test login dengan password salah ditolak dengan pesan umum.
     */
    public function test_login_with_wrong_password_is_rejected(): void
    {
        $user = User::factory()->create();

        $response = $this->from(route('login'))->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password-salah',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['email' => 'Email atau password yang Anda masukkan salah.']);
        $this->assertGuest();
    }

    /**
     * Test email yang tidak terdaftar mendapat pesan yang sama dengan password salah.
     */
    public function test_unknown_email_gets_same_error_message(): void
    {
        $response = $this->from(route('login'))->post(route('login.store'), [
            'email' => 'tidak-terdaftar@example.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors(['email' => 'Email atau password yang Anda masukkan salah.']);
        $this->assertGuest();
    }

    /**
     * Test validasi input login wajib diisi.
     */
    public function test_login_requires_email_and_password(): void
    {
        $response = $this->post(route('login.store'), []);

        $response->assertSessionHasErrors(['email', 'password']);
        $this->assertGuest();
    }

    /**
     * Test login dibatasi setelah terlalu banyak percobaan gagal.
     */
    public function test_login_is_rate_limited_after_too_many_failed_attempts(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 5) as $attempt) {
            $this->post(route('login.store'), [
                'email' => $user->email,
                'password' => 'password-salah',
            ]);
        }

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString('Terlalu banyak percobaan login', session('errors')->first('email'));
        $this->assertGuest();
    }

    /**
     * Test sesi diregenerasi setelah login berhasil.
     */
    public function test_session_is_regenerated_after_login(): void
    {
        $user = User::factory()->create();

        $this->get(route('login'));
        $sessionIdBeforeLogin = session()->getId();

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertNotSame($sessionIdBeforeLogin, session()->getId());
    }

    /**
     * Test logout mengakhiri sesi pengguna.
     */
    public function test_logout_ends_the_session(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();

        $this->get(route('student.dashboard'))->assertRedirect(route('login'));
    }

    /**
     * Test logout tidak dapat dilakukan melalui GET.
     */
    public function test_logout_requires_post_request(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/logout')->assertMethodNotAllowed();
        $this->assertAuthenticatedAs($user);
    }
}
