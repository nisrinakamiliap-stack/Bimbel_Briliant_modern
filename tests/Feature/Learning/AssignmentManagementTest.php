<?php

namespace Tests\Feature\Learning;

use App\EnrollmentStatus;
use App\LearningContentStatus;
use App\Models\Assignment;
use App\Models\AssignmentGrade;
use App\Models\Classroom;
use App\Models\Enrollment;
use App\Models\Material;
use App\Models\Submission;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AssignmentManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Classroom $classroom;

    private Enrollment $dewi;

    private Enrollment $rizky;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        $this->travelTo(CarbonImmutable::parse('2026-10-19 09:00:00'));

        $this->admin = User::factory()->admin()->create();
        $this->classroom = Classroom::factory()->create(['name' => 'SMA 12 IPA A']);
        $this->dewi = Enrollment::factory()->inClassroom($this->classroom)->withStatus(EnrollmentStatus::Approved)
            ->for(User::factory()->student()->state(['name' => 'Dewi Lestari']), 'student')->create();
        $this->rizky = Enrollment::factory()->inClassroom($this->classroom)->withStatus(EnrollmentStatus::Approved)
            ->for(User::factory()->student()->state(['name' => 'Rizky Pratama']), 'student')->create();
    }

    /**
     * Data form tugas yang valid.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return [
            'classroom_id' => (string) $this->classroom->id,
            'material_id' => '',
            'title' => 'Latihan Turunan',
            'instructions' => 'Kerjakan soal 1-10.',
            'due_at' => '2026-10-26T20:00',
            'max_score' => '100',
            'allow_late_submission' => '1',
            'allow_resubmission' => '1',
            'status' => 'published',
            ...$overrides,
        ];
    }

    /**
     * Test admin dapat membuat tugas dengan tenggat di zona waktu aplikasi.
     */
    public function test_admin_can_create_assignment(): void
    {
        $material = Material::factory()->for($this->classroom)->create();

        $response = $this->actingAs($this->admin)->post(route('admin.learning.assignments.store'), $this->payload(['material_id' => (string) $material->id]));

        $assignment = Assignment::firstOrFail();
        $response->assertRedirect(route('admin.learning.assignments.show', $assignment));
        $this->assertSame('2026-10-26 20:00', $assignment->due_at->format('Y-m-d H:i'));
        $this->assertTrue($assignment->material->is($material));
        $this->assertTrue($assignment->allow_late_submission);
        $this->assertSame(100, $assignment->max_score);
        $this->assertSame($this->admin->id, $assignment->author_id);
        $this->assertNotNull($assignment->published_at);
    }

    /**
     * Test validasi tenggat, skala nilai, dan materi harus dari kelas yang sama.
     */
    public function test_assignment_validation(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.learning.assignments.store'), $this->payload(['due_at' => '2026-10-18T20:00']))
            ->assertSessionHasErrors(['due_at' => 'Tenggat harus lebih dari waktu saat ini.']);

        $this->actingAs($this->admin)
            ->post(route('admin.learning.assignments.store'), $this->payload(['due_at' => 'besok', 'max_score' => '0', 'title' => '', 'instructions' => '']))
            ->assertSessionHasErrors(['due_at', 'max_score', 'title', 'instructions']);

        $otherMaterial = Material::factory()->create();
        $this->actingAs($this->admin)
            ->post(route('admin.learning.assignments.store'), $this->payload(['material_id' => (string) $otherMaterial->id]))
            ->assertSessionHasErrors(['material_id' => 'Materi terkait harus berasal dari kelas yang sama.']);

        $this->assertDatabaseCount('assignments', 0);
    }

    /**
     * Test tenggat lama yang tidak diubah tetap dapat disimpan saat edit.
     */
    public function test_unchanged_past_deadline_can_be_kept_when_editing(): void
    {
        $assignment = Assignment::factory()->for($this->classroom)->create(['due_at' => '2026-10-10 20:00:00']);

        $this->actingAs($this->admin)
            ->put(route('admin.learning.assignments.update', $assignment), $this->payload(['due_at' => '2026-10-10T20:00', 'title' => 'Judul Baru']))
            ->assertSessionHasNoErrors();

        $this->assertSame('Judul Baru', $assignment->fresh()->title);
    }

    /**
     * Test detail tugas menampilkan murid yang sudah dan belum mengumpulkan.
     */
    public function test_show_lists_who_submitted_and_who_did_not(): void
    {
        $assignment = Assignment::factory()->for($this->classroom)->create();
        Submission::factory()->for($assignment)->byEnrollment($this->dewi)->create();

        $this->actingAs($this->admin)
            ->get(route('admin.learning.assignments.show', $assignment))
            ->assertOk()
            ->assertSee('1 dari 2 murid peserta sudah mengumpulkan.')
            ->assertSeeInOrder(['Dewi Lestari', 'Dikumpulkan', 'Rizky Pratama', 'Belum Mengumpulkan']);
    }

    /**
     * Test tugas tanpa pengumpulan dapat dihapus; tugas dengan pengumpulan tidak dapat dihapus.
     */
    public function test_assignment_deletion_respects_submission_history(): void
    {
        $empty = Assignment::factory()->for($this->classroom)->create();
        $this->actingAs($this->admin)->delete(route('admin.learning.assignments.destroy', $empty))->assertRedirect(route('admin.learning.assignments.index'));
        $this->assertModelMissing($empty);

        $withHistory = Assignment::factory()->for($this->classroom)->create();
        Submission::factory()->for($withHistory)->byEnrollment($this->dewi)->create();

        $this->actingAs($this->admin)->get(route('admin.learning.assignments.show', $withHistory))
            ->assertSee('Tugas yang sudah memiliki pengumpulan tidak dapat dihapus. Arsipkan tugas ini.')
            ->assertDontSee('id="btn-delete-assignment"', false);

        $this->actingAs($this->admin)->delete(route('admin.learning.assignments.destroy', $withHistory))
            ->assertSessionHas('error', 'Tugas yang sudah memiliki pengumpulan tidak dapat dihapus. Arsipkan tugas ini.');
        $this->assertModelExists($withHistory);
    }

    /**
     * Test tugas dengan pengumpulan tidak dapat dipindah kelas, dijadikan draf, atau diturunkan skalanya di bawah nilai tertinggi.
     */
    public function test_assignment_with_submissions_has_edit_restrictions(): void
    {
        $assignment = Assignment::factory()->for($this->classroom)->create(['due_at' => '2026-10-26 20:00:00']);
        $submission = Submission::factory()->for($assignment)->byEnrollment($this->dewi)->create();
        AssignmentGrade::factory()->for($submission)->create(['score' => 85]);

        $this->actingAs($this->admin)
            ->put(route('admin.learning.assignments.update', $assignment), $this->payload([
                'classroom_id' => (string) Classroom::factory()->create()->id,
                'status' => 'draft',
                'max_score' => '80',
            ]))
            ->assertSessionHasErrors(['classroom_id', 'status', 'max_score' => 'Skala nilai tidak boleh lebih kecil dari nilai tertinggi yang sudah diberikan (85).']);

        $this->actingAs($this->admin)
            ->put(route('admin.learning.assignments.update', $assignment), $this->payload(['status' => 'archived']))
            ->assertSessionHasNoErrors();

        $this->assertSame(LearningContentStatus::Archived, $assignment->fresh()->status);
        $this->assertModelExists($submission);
    }

    /**
     * Test murid hanya melihat tugas terbit dari kelasnya.
     */
    public function test_student_sees_only_published_assignments_of_own_class(): void
    {
        $mine = Assignment::factory()->for($this->classroom)->create(['title' => 'Tugas Kelas Saya']);
        Assignment::factory()->for($this->classroom)->draft()->create(['title' => 'Tugas Draf']);
        $other = Assignment::factory()->create(['title' => 'Tugas Kelas Lain']);

        $this->actingAs($this->dewi->student)->get(route('student.learning.index'))
            ->assertOk()->assertSee('Tugas Kelas Saya')->assertDontSee('Tugas Draf')->assertDontSee('Tugas Kelas Lain');
        $this->actingAs($this->dewi->student)->get(route('student.learning.assignments.show', $mine))->assertOk();
        $this->actingAs($this->dewi->student)->get(route('student.learning.assignments.show', $other))->assertForbidden();
    }

    /**
     * Test guru dan murid tidak dapat membuka pengelolaan tugas di panel admin.
     */
    public function test_non_admin_users_cannot_manage_assignments_in_admin_panel(): void
    {
        $assignment = Assignment::factory()->for($this->classroom)->create();

        foreach ([User::factory()->teacher()->create(), $this->dewi->student] as $user) {
            $this->actingAs($user)->get(route('admin.learning.assignments.index'))->assertForbidden();
            $this->actingAs($user)->post(route('admin.learning.assignments.store'), $this->payload())->assertForbidden();
            $this->actingAs($user)->delete(route('admin.learning.assignments.destroy', $assignment))->assertForbidden();
        }

        $this->assertModelExists($assignment);
    }

    /**
     * Test menu Pembelajaran di sidebar aktif dan tab tersedia.
     */
    public function test_learning_menu_and_tabs(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.learning.assignments.index'));

        $this->assertMatchesRegularExpression(
            '/href="'.preg_quote(route('admin.learning.materials.index'), '/').'"\s+class="admin-nav-item is-active"/',
            $response->getContent()
        );
        $response->assertSeeInOrder(['Materi Pembelajaran', 'Tugas', 'Pengumpulan Tugas', 'Penilaian Tugas']);
    }
}
