<?php

namespace Tests\Feature\Teacher;

use App\EnrollmentStatus;
use App\LearningContentStatus;
use App\Models\Assignment;
use App\Models\Classroom;
use App\Models\Enrollment;
use App\Models\Material;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LearningManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $teacher;

    private Classroom $classroom;

    private Classroom $otherClassroom;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(Material::FILE_DISK);
        $this->travelTo('2026-10-19 10:00:00');

        $this->teacher = User::factory()->teacher()->create();
        $this->classroom = Classroom::factory()->create(['teacher_id' => $this->teacher->id, 'name' => 'SMA 12 IPA A']);
        $this->otherClassroom = Classroom::factory()->create(['name' => 'Kelas Guru Lain']);
    }

    /**
     * Data form materi yang valid.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function materialPayload(array $overrides = []): array
    {
        return [
            'classroom_id' => (string) $this->classroom->id,
            'title' => 'Turunan Fungsi Aljabar',
            'description' => 'Pengantar turunan.',
            'content' => 'Turunan adalah laju perubahan.',
            'link_url' => '',
            'status' => 'published',
            ...$overrides,
        ];
    }

    /**
     * Data form tugas yang valid.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function assignmentPayload(array $overrides = []): array
    {
        return [
            'classroom_id' => (string) $this->classroom->id,
            'material_id' => '',
            'title' => 'Latihan Turunan',
            'instructions' => 'Kerjakan soal 1 sampai 10.',
            'due_at' => '2026-10-26T20:00',
            'max_score' => '100',
            'allow_late_submission' => '0',
            'allow_resubmission' => '1',
            'status' => 'draft',
            ...$overrides,
        ];
    }

    /**
     * Test daftar materi hanya berisi materi kelas yang diampu.
     */
    public function test_material_index_is_scoped_to_own_classes(): void
    {
        Material::factory()->for($this->classroom)->create(['title' => 'Materi Saya']);
        Material::factory()->for($this->otherClassroom)->create(['title' => 'Materi Orang Lain']);

        $this->actingAs($this->teacher)
            ->get(route('teacher.learning.materials.index'))
            ->assertOk()
            ->assertSee('Materi Saya')
            ->assertDontSee('Materi Orang Lain');
    }

    /**
     * Test form tambah materi hanya menawarkan kelas yang diampu.
     */
    public function test_material_form_offers_only_own_classes(): void
    {
        $this->actingAs($this->teacher)
            ->get(route('teacher.learning.materials.create'))
            ->assertOk()
            ->assertSee('SMA 12 IPA A')
            ->assertDontSee('Kelas Guru Lain');
    }

    /**
     * Test guru dapat menambah materi dengan berkas yang disimpan di disk privat.
     */
    public function test_teacher_can_create_material_with_private_file(): void
    {
        $this->actingAs($this->teacher)
            ->post(route('teacher.learning.materials.store'), $this->materialPayload(['file' => UploadedFile::fake()->create('modul.pdf', 200, 'application/pdf')]))
            ->assertSessionHasNoErrors();

        $material = Material::query()->sole();
        $this->assertSame($this->teacher->id, $material->author_id);
        $this->assertSame(LearningContentStatus::Published, $material->status);
        Storage::disk(Material::FILE_DISK)->assertExists($material->file_path);

        $this->actingAs($this->teacher)->get(route('teacher.learning.materials.show', $material))->assertOk()->assertSee('modul.pdf');
        $this->actingAs($this->teacher)->get(route('learning.files.material', $material))->assertOk();
    }

    /**
     * Test berkas materi dengan tipe berbahaya ditolak.
     */
    public function test_material_file_type_is_validated(): void
    {
        $this->actingAs($this->teacher)
            ->post(route('teacher.learning.materials.store'), $this->materialPayload(['file' => UploadedFile::fake()->create('skrip.php', 5, 'text/x-php')]))
            ->assertSessionHasErrors('file');

        $this->assertSame(0, Material::query()->count());
    }

    /**
     * Test guru tidak dapat membuat materi untuk kelas guru lain.
     */
    public function test_cannot_create_material_for_another_teachers_class(): void
    {
        $this->actingAs($this->teacher)
            ->post(route('teacher.learning.materials.store'), $this->materialPayload(['classroom_id' => (string) $this->otherClassroom->id]))
            ->assertForbidden();

        $this->assertSame(0, Material::query()->count());
    }

    /**
     * Test guru dapat mengedit dan mengarsipkan materinya.
     */
    public function test_teacher_can_update_and_archive_own_material(): void
    {
        $material = Material::factory()->for($this->classroom)->create();

        $this->actingAs($this->teacher)->get(route('teacher.learning.materials.edit', $material))->assertOk();
        $this->actingAs($this->teacher)
            ->put(route('teacher.learning.materials.update', $material), $this->materialPayload(['title' => 'Judul Baru', 'status' => 'archived']))
            ->assertRedirect(route('teacher.learning.materials.show', $material));

        $this->assertSame('Judul Baru', $material->fresh()->title);
        $this->assertSame(LearningContentStatus::Archived, $material->fresh()->status);
    }

    /**
     * Test guru tidak dapat melihat, mengedit, atau memindahkan materi kelas guru lain.
     */
    public function test_cannot_touch_another_teachers_material(): void
    {
        $foreign = Material::factory()->for($this->otherClassroom)->create(['title' => 'Materi Asing']);

        $this->actingAs($this->teacher)->get(route('teacher.learning.materials.show', $foreign))->assertForbidden();
        $this->actingAs($this->teacher)->get(route('teacher.learning.materials.edit', $foreign))->assertForbidden();
        $this->actingAs($this->teacher)->get(route('learning.files.material', $foreign))->assertForbidden();
        $this->actingAs($this->teacher)
            ->put(route('teacher.learning.materials.update', $foreign), $this->materialPayload())
            ->assertForbidden();

        $this->assertSame('Materi Asing', $foreign->fresh()->title);
        $this->assertSame($this->otherClassroom->id, $foreign->fresh()->classroom_id);
    }

    /**
     * Test guru dapat membuat tugas draf, lalu menerbitkannya sehingga murid kelas mendapat notifikasi.
     */
    public function test_teacher_can_create_and_publish_assignment(): void
    {
        $enrollment = Enrollment::factory()->inClassroom($this->classroom)->withStatus(EnrollmentStatus::Approved)->create();

        $this->actingAs($this->teacher)
            ->post(route('teacher.learning.assignments.store'), $this->assignmentPayload())
            ->assertSessionHasNoErrors();

        $assignment = Assignment::query()->sole();
        $this->assertSame(LearningContentStatus::Draft, $assignment->status);
        $this->assertSame('2026-10-26 20:00', $assignment->due_at->format('Y-m-d H:i'));
        $this->assertSame(0, $enrollment->student->notifications()->count());

        $this->actingAs($this->teacher)
            ->put(route('teacher.learning.assignments.update', $assignment), $this->assignmentPayload(['status' => 'published']))
            ->assertRedirect(route('teacher.learning.assignments.show', $assignment));

        $this->assertSame(1, $enrollment->student->notifications()->count());
    }

    /**
     * Test validasi tugas dijalankan di backend.
     */
    public function test_assignment_is_validated(): void
    {
        $this->actingAs($this->teacher)
            ->post(route('teacher.learning.assignments.store'), $this->assignmentPayload(['title' => '', 'due_at' => 'besok', 'max_score' => '0']))
            ->assertSessionHasErrors(['title', 'due_at', 'max_score']);
    }

    /**
     * Test guru tidak dapat membuat atau mengubah tugas kelas guru lain.
     */
    public function test_cannot_manage_another_teachers_assignment(): void
    {
        $this->actingAs($this->teacher)
            ->post(route('teacher.learning.assignments.store'), $this->assignmentPayload(['classroom_id' => (string) $this->otherClassroom->id]))
            ->assertForbidden();

        $foreign = Assignment::factory()->for($this->otherClassroom)->create();

        $this->actingAs($this->teacher)->get(route('teacher.learning.assignments.show', $foreign))->assertForbidden();
        $this->actingAs($this->teacher)->get(route('teacher.learning.assignments.edit', $foreign))->assertForbidden();
        $this->actingAs($this->teacher)->put(route('teacher.learning.assignments.update', $foreign), $this->assignmentPayload())->assertForbidden();
        $this->actingAs($this->teacher)->delete(route('teacher.learning.assignments.destroy', $foreign))->assertForbidden();

        $this->assertNotNull($foreign->fresh());
    }

    /**
     * Test tugas tanpa pengumpulan dapat dihapus, sedangkan tugas dengan pengumpulan tidak.
     */
    public function test_assignment_deletion_rules(): void
    {
        $empty = Assignment::factory()->for($this->classroom)->create();
        $withSubmission = Assignment::factory()->for($this->classroom)->create();
        $enrollment = Enrollment::factory()->inClassroom($this->classroom)->withStatus(EnrollmentStatus::Approved)->create();
        Submission::factory()->for($withSubmission)->byEnrollment($enrollment)->create();

        $this->actingAs($this->teacher)
            ->delete(route('teacher.learning.assignments.destroy', $empty))
            ->assertRedirect(route('teacher.learning.assignments.index'));
        $this->assertNull($empty->fresh());

        $this->actingAs($this->teacher)
            ->from(route('teacher.learning.assignments.show', $withSubmission))
            ->delete(route('teacher.learning.assignments.destroy', $withSubmission))
            ->assertSessionHas('error');
        $this->assertNotNull($withSubmission->fresh());
    }

    /**
     * Test detail tugas menampilkan murid yang sudah dan belum mengumpulkan.
     */
    public function test_assignment_show_lists_submitted_and_missing_students(): void
    {
        $assignment = Assignment::factory()->for($this->classroom)->create();
        $submitted = Enrollment::factory()->inClassroom($this->classroom)->withStatus(EnrollmentStatus::Approved)->create();
        $submitted->student->update(['name' => 'Ani Sudah Kirim']);
        $missing = Enrollment::factory()->inClassroom($this->classroom)->withStatus(EnrollmentStatus::Approved)->create();
        $missing->student->update(['name' => 'Bima Belum Kirim']);
        Submission::factory()->for($assignment)->byEnrollment($submitted)->create();

        $this->actingAs($this->teacher)
            ->get(route('teacher.learning.assignments.show', $assignment))
            ->assertOk()
            ->assertSee('1 dari 2 murid sudah mengumpulkan.')
            ->assertSeeInOrder(['Ani Sudah Kirim', 'Bima Belum Kirim', 'Belum Mengumpulkan']);
    }

    /**
     * Test daftar pengumpulan dan penilaian hanya dari kelas sendiri; penilaian default menampilkan yang belum dinilai.
     */
    public function test_submission_lists_are_scoped_and_grading_shows_ungraded(): void
    {
        $assignment = Assignment::factory()->for($this->classroom)->create(['title' => 'Tugas Saya']);
        $first = Enrollment::factory()->inClassroom($this->classroom)->withStatus(EnrollmentStatus::Approved)->create();
        $first->student->update(['name' => 'Citra Belum Dinilai']);
        $second = Enrollment::factory()->inClassroom($this->classroom)->withStatus(EnrollmentStatus::Approved)->create();
        $second->student->update(['name' => 'Dodi Sudah Dinilai']);
        Submission::factory()->for($assignment)->byEnrollment($first)->create();
        $graded = Submission::factory()->for($assignment)->byEnrollment($second)->create();
        $this->actingAs($this->teacher)->post(route('teacher.learning.grades.store', $graded), ['score' => '80']);
        Submission::factory()->for(Assignment::factory()->for($this->otherClassroom)->create(['title' => 'Tugas Kelas Lain']))->create();

        $this->actingAs($this->teacher)
            ->get(route('teacher.learning.submissions.index'))
            ->assertOk()
            ->assertSee('Citra Belum Dinilai')
            ->assertSee('Dodi Sudah Dinilai')
            ->assertDontSee('Tugas Kelas Lain');

        $this->actingAs($this->teacher)
            ->get(route('teacher.learning.grades.index'))
            ->assertOk()
            ->assertSee('Citra Belum Dinilai')
            ->assertDontSee('Dodi Sudah Dinilai');
    }

    /**
     * Test guru dapat menilai, memperbarui nilai, dan memberi umpan balik pada pengumpulan kelasnya.
     */
    public function test_teacher_can_grade_and_regrade_own_submission(): void
    {
        $assignment = Assignment::factory()->for($this->classroom)->create(['max_score' => 100]);
        $enrollment = Enrollment::factory()->inClassroom($this->classroom)->withStatus(EnrollmentStatus::Approved)->create();
        $submission = Submission::factory()->for($assignment)->byEnrollment($enrollment)->create();

        $this->actingAs($this->teacher)->get(route('teacher.learning.submissions.show', $submission))->assertOk()->assertSee('Beri Penilaian');

        $this->actingAs($this->teacher)
            ->from(route('teacher.learning.submissions.show', $submission))
            ->post(route('teacher.learning.grades.store', $submission), ['score' => '87,5', 'feedback' => 'Langkah kerja rapi.'])
            ->assertRedirect(route('teacher.learning.submissions.show', $submission))
            ->assertSessionHas('success');

        $this->actingAs($this->teacher)
            ->post(route('teacher.learning.grades.store', $submission), ['score' => '90'])
            ->assertSessionHasNoErrors();

        $grade = $submission->fresh()->grade;
        $this->assertSame('90.00', (string) $grade->score);
        $this->assertSame(1, $grade->revision_count);

        $this->actingAs($this->teacher)
            ->post(route('teacher.learning.grades.store', $submission), ['score' => '150'])
            ->assertSessionHasErrors('score');
    }

    /**
     * Test guru tidak dapat membuka atau menilai pengumpulan kelas guru lain.
     */
    public function test_cannot_view_or_grade_another_teachers_submission(): void
    {
        $foreign = Submission::factory()->for(Assignment::factory()->for($this->otherClassroom)->create())->create();

        $this->actingAs($this->teacher)->get(route('teacher.learning.submissions.show', $foreign))->assertForbidden();
        $this->actingAs($this->teacher)->post(route('teacher.learning.grades.store', $foreign), ['score' => '100'])->assertForbidden();

        $this->assertNull($foreign->fresh()->grade);
    }
}
