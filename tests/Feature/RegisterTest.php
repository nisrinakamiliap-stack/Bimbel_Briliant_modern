<?php

namespace Tests\Feature;

use App\Models\User;
use App\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Data registrasi yang valid.
     *
     * @return array{name: string, email: string, phone: string, password: string, password_confirmation: string}
     */
    private function validPayload(): array
    {
        return [
            'name' => 'Siti Rahmawati',
            'email' => 'siti@example.com',
            'phone' => '0812-3456-7890',
            'password' => 'Rahasia123',
            'password_confirmation' => 'Rahasia123',
        ];
    }

    /**
     * Test halaman registrasi dapat diakses tanpa pilihan role.
     */
    public function test_register_page_is_accessible_without_role_selection(): void
    {
        $response = $this->get(route('register'));

        $response->assertOk();
        $response->assertSee('Daftar Akun Murid');
        $response->assertSee('name="phone"', false);
        $response->assertSee('name="password_confirmation"', false);
        $response->assertDontSee('name="role"', false);
        $response->assertSee(route('login'), false);
    }

    /**
     * Test registrasi membuat akun murid, menyimpan password ter-hash, dan langsung login.
     */
    public function test_registration_creates_student_account(): void
    {
        $response = $this->post(route('register.store'), $this->validPayload());

        $response->assertRedirect(route('student.dashboard'));
        $response->assertSessionHas('success');

        $user = User::where('email', 'siti@example.com')->firstOrFail();

        $this->assertSame(UserRole::Student, $user->role);
        $this->assertSame('081234567890', $user->phone);
        $this->assertNotSame('Rahasia123', $user->password);
        $this->assertTrue(Hash::check('Rahasia123', $user->password));
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test notifikasi registrasi berhasil tampil di dashboard murid.
     */
    public function test_success_message_is_shown_after_registration(): void
    {
        $response = $this->followingRedirects()->post(route('register.store'), $this->validPayload());

        $response->assertOk();
        $response->assertSee('Registrasi berhasil!');
        $response->assertSee('Dashboard Murid');
    }

    /**
     * Test pengguna tidak dapat menentukan role melalui request registrasi.
     */
    public function test_role_cannot_be_set_through_register_request(): void
    {
        $this->post(route('register.store'), [
            ...$this->validPayload(),
            'role' => 'admin',
        ])->assertRedirect(route('student.dashboard'));

        $user = User::where('email', 'siti@example.com')->firstOrFail();

        $this->assertSame(UserRole::Student, $user->role);
    }

    /**
     * Test email yang sudah terdaftar ditolak.
     */
    public function test_duplicate_email_is_rejected(): void
    {
        User::factory()->create(['email' => 'siti@example.com']);

        $response = $this->post(route('register.store'), [
            ...$this->validPayload(),
            'email' => 'SITI@example.com',
        ]);

        $response->assertSessionHasErrors(['email' => 'Email ini sudah terdaftar. Silakan login atau gunakan email lain.']);
        $this->assertSame(1, User::where('email', 'siti@example.com')->count());
        $this->assertGuest();
    }

    /**
     * Test validasi field wajib registrasi.
     */
    public function test_registration_requires_all_fields(): void
    {
        $response = $this->post(route('register.store'), []);

        $response->assertSessionHasErrors(['name', 'email', 'phone', 'password']);
        $this->assertGuest();
    }

    /**
     * Test nomor telepon yang tidak valid ditolak.
     */
    public function test_invalid_phone_number_is_rejected(): void
    {
        $response = $this->post(route('register.store'), [
            ...$this->validPayload(),
            'phone' => '12345',
        ]);

        $response->assertSessionHasErrors('phone');
        $this->assertDatabaseMissing('users', ['email' => 'siti@example.com']);
    }

    /**
     * Test konfirmasi password harus cocok.
     */
    public function test_password_confirmation_must_match(): void
    {
        $response = $this->post(route('register.store'), [
            ...$this->validPayload(),
            'password_confirmation' => 'BerbedaSekali1',
        ]);

        $response->assertSessionHasErrors(['password' => 'Konfirmasi password tidak cocok.']);
    }

    /**
     * Test password lemah ditolak oleh kebijakan password.
     */
    public function test_weak_password_is_rejected(): void
    {
        $response = $this->post(route('register.store'), [
            ...$this->validPayload(),
            'password' => 'abc',
            'password_confirmation' => 'abc',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertDatabaseMissing('users', ['email' => 'siti@example.com']);
    }
}
