<?php

namespace Tests\Feature\Admin;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SettingTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(Setting::LOGO_DISK);

        $this->admin = User::factory()->admin()->create();
    }

    /**
     * Data form pengaturan yang valid.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return [
            'institution_name' => 'Bimbel Sentra Edukasi Semarang',
            'institution_tagline' => 'Belajar terarah, hasil terukur',
            'institution_description' => 'Lembaga bimbingan belajar untuk SD, SMP, dan SMA.',
            'institution_address' => 'Jl. Pemuda No. 10, Semarang',
            'institution_phone' => '(024) 123-4567',
            'institution_whatsapp' => '+62 812-3456-7890',
            'institution_email' => 'info@sentraedukasi.test',
            'institution_hours' => 'Senin–Sabtu, 08.00–20.00',
            'app_timezone' => 'Asia/Makassar',
            ...$overrides,
        ];
    }

    /**
     * Test admin dapat membuka halaman pengaturan.
     */
    public function test_admin_can_open_settings_page(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.settings.edit'))
            ->assertOk()
            ->assertSee('Asia/Jakarta')
            ->assertSee('Simpan Pengaturan');
    }

    /**
     * Test halaman pengaturan tidak menampilkan rahasia aplikasi.
     */
    public function test_settings_page_never_exposes_secrets(): void
    {
        config(['database.connections.mysql.password' => 'SandiDatabaseRahasia']);

        $this->actingAs($this->admin)
            ->get(route('admin.settings.edit'))
            ->assertOk()
            ->assertDontSee(config('app.key'))
            ->assertDontSee('SandiDatabaseRahasia')
            ->assertDontSee('APP_KEY')
            ->assertDontSee('DB_PASSWORD');
    }

    /**
     * Test pengaturan tersimpan dan langsung tampil pada panel admin.
     */
    public function test_admin_can_update_settings(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.settings.update'), $this->validPayload())
            ->assertRedirect(route('admin.settings.edit'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('settings', ['key' => 'institution_name', 'value' => 'Bimbel Sentra Edukasi Semarang']);
        $this->assertDatabaseHas('settings', ['key' => 'app_timezone', 'value' => 'Asia/Makassar']);
        $this->assertSame('Asia/Makassar', Setting::timezone());

        $this->actingAs($this->admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Bimbel Sentra Edukasi Semarang');
    }

    /**
     * Test kunci di luar daftar yang diizinkan tidak pernah disimpan.
     */
    public function test_unknown_keys_are_ignored(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.settings.update'), $this->validPayload(['app_key' => 'curang', 'db_password' => 'curang', 'role' => 'admin']))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('settings', ['key' => 'app_key']);
        $this->assertDatabaseMissing('settings', ['key' => 'db_password']);
        $this->assertDatabaseMissing('settings', ['key' => 'role']);

        Setting::putMany(['app_key' => 'curang']);
        $this->assertDatabaseMissing('settings', ['key' => 'app_key']);
    }

    /**
     * Test validasi field pengaturan.
     */
    public function test_settings_are_validated(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.settings.update'), $this->validPayload([
                'institution_name' => '',
                'institution_email' => 'bukan-email',
                'institution_phone' => '0812<script>',
                'app_timezone' => 'Europe/London',
            ]))
            ->assertSessionHasErrors(['institution_name', 'institution_email', 'institution_phone', 'app_timezone']);

        $this->assertSame(0, Setting::query()->count());
    }

    /**
     * Test logo diunggah ke penyimpanan publik, logo lama dihapus saat diganti, dan dapat dikembalikan ke bawaan.
     */
    public function test_logo_can_be_uploaded_replaced_and_removed(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.settings.update'), $this->validPayload(['logo' => UploadedFile::fake()->image('logo.png', 200, 200)]))
            ->assertSessionHasNoErrors();

        $firstLogo = Setting::get('institution_logo_path');
        $this->assertNotNull($firstLogo);
        Storage::disk(Setting::LOGO_DISK)->assertExists($firstLogo);

        $this->actingAs($this->admin)
            ->put(route('admin.settings.update'), $this->validPayload(['logo' => UploadedFile::fake()->image('logo-baru.webp', 200, 200)]))
            ->assertSessionHasNoErrors();

        $secondLogo = Setting::get('institution_logo_path');
        $this->assertNotSame($firstLogo, $secondLogo);
        Storage::disk(Setting::LOGO_DISK)->assertMissing($firstLogo);
        Storage::disk(Setting::LOGO_DISK)->assertExists($secondLogo);

        $this->actingAs($this->admin)
            ->put(route('admin.settings.update'), $this->validPayload(['remove_logo' => '1']))
            ->assertSessionHasNoErrors();

        $this->assertNull(Setting::get('institution_logo_path'));
        Storage::disk(Setting::LOGO_DISK)->assertMissing($secondLogo);
        $this->assertSame(asset('logo.png'), Setting::institution()['logo_url']);
    }

    /**
     * Test logo dengan tipe atau ukuran yang tidak diizinkan ditolak.
     */
    public function test_invalid_logo_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.settings.update'), $this->validPayload(['logo' => UploadedFile::fake()->create('logo.php', 10, 'text/x-php')]))
            ->assertSessionHasErrors('logo');

        $this->actingAs($this->admin)
            ->put(route('admin.settings.update'), $this->validPayload(['logo' => UploadedFile::fake()->create('logo.svg', 10, 'image/svg+xml')]))
            ->assertSessionHasErrors('logo');

        $this->actingAs($this->admin)
            ->put(route('admin.settings.update'), $this->validPayload(['logo' => UploadedFile::fake()->image('besar.png')->size(2048)]))
            ->assertSessionHasErrors('logo');

        $this->assertSame([], Storage::disk(Setting::LOGO_DISK)->allFiles());
    }

    /**
     * Test guru dan murid tidak dapat membuka atau menyimpan pengaturan.
     */
    public function test_non_admin_cannot_access_settings(): void
    {
        foreach ([User::factory()->teacher()->create(), User::factory()->student()->create()] as $user) {
            $this->actingAs($user)->get(route('admin.settings.edit'))->assertForbidden();
            $this->actingAs($user)->put(route('admin.settings.update'), $this->validPayload())->assertForbidden();
        }

        $this->assertSame(0, Setting::query()->count());
    }
}
