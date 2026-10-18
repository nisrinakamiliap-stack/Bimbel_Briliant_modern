<?php

namespace Tests\Feature\Learning;

use App\EnrollmentStatus;
use App\LearningContentStatus;
use App\Models\Classroom;
use App\Models\Enrollment;
use App\Models\Material;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MaterialManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $teacher;

    private Classroom $classroom;

    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->admin = User::factory()->admin()->create();
        $this->teacher = User::factory()->teacher()->create(['name' => 'Sari Wahyuni']);
        $this->classroom = Classroom::factory()->create(['teacher_id' => $this->teacher->id, 'name' => 'SMA 12 IPA A']);
        $this->student = Enrollment::factory()->inClassroom($this->classroom)->withStatus(EnrollmentStatus::Approved)->create()->student;
    }

    /**
     * Data form materi yang valid.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return [
            'classroom_id' => (string) $this->classroom->id,
            'title' => 'Turunan Fungsi Aljabar',
            'description' => 'Pengantar turunan.',
            'content' => 'Turunan menyatakan laju perubahan.',
            'link_url' => '',
            'status' => 'published',
            ...$overrides,
        ];
    }

    /**
     * Test admin dapat menambahkan materi dengan berkas yang disimpan privat.
     */
    public function test_admin_can_create_material_with_private_file(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.learning.materials.store'), $this->payload([
            'file' => UploadedFile::fake()->create('modul.pdf', 200, 'application/pdf'),
        ]));

        $material = Material::firstOrFail();
        $response->assertRedirect(route('admin.learning.materials.show', $material));
        $response->assertSessionHas('success');

        $this->assertSame($this->admin->id, $material->author_id);
        $this->assertSame(LearningContentStatus::Published, $material->status);
        $this->assertNotNull($material->published_at);
        $this->assertSame('modul.pdf', $material->file_original_name);
        $this->assertStringStartsWith('learning/materials/', $material->file_path);
        Storage::disk('local')->assertExists($material->file_path);
        Storage::disk('public')->assertMissing($material->file_path);
    }

    /**
     * Test validasi materi: judul wajib, isi/tautan/berkas wajib salah satu, tipe berkas dan URL.
     */
    public function test_material_validation(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.learning.materials.store'), $this->payload(['title' => '', 'content' => '', 'classroom_id' => '']))
            ->assertSessionHasErrors(['title' => 'Judul materi wajib diisi.', 'classroom_id']);

        $this->actingAs($this->admin)
            ->post(route('admin.learning.materials.store'), $this->payload(['content' => '']))
            ->assertSessionHasErrors(['content' => 'Isi materi, tautan, atau berkas wajib diisi salah satu.']);

        $this->actingAs($this->admin)
            ->post(route('admin.learning.materials.store'), $this->payload(['file' => UploadedFile::fake()->create('virus.exe', 10, 'application/x-msdownload')]))
            ->assertSessionHasErrors(['file' => 'Berkas materi harus berupa PDF, Word, PowerPoint, Excel, gambar JPG/PNG, atau ZIP.']);

        $this->actingAs($this->admin)
            ->post(route('admin.learning.materials.store'), $this->payload(['file' => UploadedFile::fake()->create('besar.pdf', 11000, 'application/pdf')]))
            ->assertSessionHasErrors(['file' => 'Ukuran berkas materi maksimal 10 MB.']);

        $this->actingAs($this->admin)
            ->post(route('admin.learning.materials.store'), $this->payload(['link_url' => 'javascript:alert(1)']))
            ->assertSessionHasErrors('link_url');

        $this->assertDatabaseCount('materials', 0);
    }

    /**
     * Test daftar, filter, detail, dan edit materi.
     */
    public function test_index_show_edit_and_archive(): void
    {
        $material = Material::factory()->for($this->classroom)->create(['title' => 'Integral Tentu']);
        Material::factory()->draft()->create(['title' => 'Materi Draf Kelas Lain']);

        $this->actingAs($this->admin)->get(route('admin.learning.materials.index'))
            ->assertOk()->assertSee('Integral Tentu')->assertSee('SMA 12 IPA A')->assertSee('Guru: Sari Wahyuni');

        $titles = $this->actingAs($this->admin)->get(route('admin.learning.materials.index', ['status' => 'draft']))->viewData('materials')->pluck('title')->all();
        $this->assertSame(['Materi Draf Kelas Lain'], $titles);

        $this->actingAs($this->admin)->get(route('admin.learning.materials.show', $material))->assertOk()->assertSee('Integral Tentu');
        $this->actingAs($this->admin)->get(route('admin.learning.materials.edit', $material))->assertOk()->assertSee('value="Integral Tentu"', false);

        $this->actingAs($this->admin)
            ->put(route('admin.learning.materials.update', $material), $this->payload(['title' => 'Integral Tentu (Revisi)', 'status' => 'archived']))
            ->assertRedirect(route('admin.learning.materials.show', $material));

        $material->refresh();
        $this->assertSame('Integral Tentu (Revisi)', $material->title);
        $this->assertSame(LearningContentStatus::Archived, $material->status);
        $this->assertModelExists($material);

        $this->actingAs($this->admin)->delete('/admin/pembelajaran/materi/'.$material->id)->assertMethodNotAllowed();
    }

    /**
     * Test mengganti berkas menghapus berkas lama dari penyimpanan.
     */
    public function test_replacing_file_removes_old_file(): void
    {
        $this->actingAs($this->admin)->post(route('admin.learning.materials.store'), $this->payload(['file' => UploadedFile::fake()->create('lama.pdf', 10, 'application/pdf')]));
        $material = Material::firstOrFail();
        $oldPath = $material->file_path;

        $this->actingAs($this->admin)->put(route('admin.learning.materials.update', $material), $this->payload(['file' => UploadedFile::fake()->create('baru.pdf', 10, 'application/pdf')]));

        $material->refresh();
        $this->assertSame('baru.pdf', $material->file_original_name);
        Storage::disk('local')->assertMissing($oldPath);
        Storage::disk('local')->assertExists($material->file_path);
    }

    /**
     * Test akses berkas materi berdasarkan role dan kelas.
     */
    public function test_material_file_access_is_restricted_by_class(): void
    {
        $this->actingAs($this->admin)->post(route('admin.learning.materials.store'), $this->payload(['file' => UploadedFile::fake()->create('modul.pdf', 10, 'application/pdf')]));
        $material = Material::firstOrFail();
        $url = route('learning.files.material', $material);

        $this->actingAs($this->admin)->get($url)->assertOk()->assertDownload('modul.pdf');
        $this->actingAs($this->teacher)->get($url)->assertOk();
        $this->actingAs($this->student)->get($url)->assertOk();

        $otherStudent = Enrollment::factory()->inClassroom(Classroom::factory()->create())->withStatus(EnrollmentStatus::Approved)->create()->student;
        $pendingStudent = Enrollment::factory()->inClassroom($this->classroom)->create()->student;
        foreach ([$otherStudent, $pendingStudent, User::factory()->teacher()->create()] as $user) {
            $this->actingAs($user)->get($url)->assertForbidden();
        }

        $material->update(['status' => LearningContentStatus::Draft]);
        $this->actingAs($this->student)->get($url)->assertForbidden();

        auth()->logout();
        $this->get($url)->assertRedirect(route('login'));
    }

    /**
     * Test murid hanya melihat materi terbit dari kelasnya di halaman pembelajaran murid.
     */
    public function test_student_sees_only_published_materials_of_own_class(): void
    {
        $published = Material::factory()->for($this->classroom)->create(['title' => 'Materi Terbit Kelas Saya']);
        Material::factory()->for($this->classroom)->draft()->create(['title' => 'Materi Draf Kelas Saya']);
        Material::factory()->create(['title' => 'Materi Kelas Lain']);

        $this->actingAs($this->student)->get(route('student.learning.index'))
            ->assertOk()->assertSee('Materi Terbit Kelas Saya')->assertDontSee('Materi Draf Kelas Saya')->assertDontSee('Materi Kelas Lain');

        $this->actingAs($this->student)->get(route('student.learning.materials.show', $published))->assertOk()->assertSee('Materi Terbit Kelas Saya');
        $this->actingAs($this->student)->get(route('student.learning.materials.show', Material::where('title', 'Materi Kelas Lain')->first()))->assertForbidden();
    }

    /**
     * Test guru dan murid tidak dapat membuka halaman pengelolaan materi admin.
     */
    public function test_non_admin_users_cannot_access_admin_material_pages(): void
    {
        $material = Material::factory()->for($this->classroom)->create();

        foreach ([$this->teacher, $this->student] as $user) {
            $this->actingAs($user)->get(route('admin.learning.materials.index'))->assertForbidden();
            $this->actingAs($user)->post(route('admin.learning.materials.store'), $this->payload())->assertForbidden();
            $this->actingAs($user)->put(route('admin.learning.materials.update', $material), $this->payload())->assertForbidden();
        }

        $this->assertDatabaseCount('materials', 1);
    }

    /**
     * Test kelas yang memiliki materi tidak dapat dihapus.
     */
    public function test_class_with_materials_cannot_be_deleted(): void
    {
        $classroom = Classroom::factory()->create();
        Material::factory()->for($classroom)->create();

        $this->actingAs($this->admin)->delete(route('admin.classes.destroy', $classroom))->assertSessionHas('error');
        $this->assertModelExists($classroom);
    }
}
