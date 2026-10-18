<?php

namespace Tests\Feature\Learning;

use App\EnrollmentStatus;
use App\Models\Assignment;
use App\Models\AssignmentGrade;
use App\Models\Classroom;
use App\Models\Enrollment;
use App\Models\Submission;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SubmissionAndGradingTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $teacher;

    private Classroom $classroom;

    private Enrollment $enrollment;

    private User $student;

    private Assignment $assignment;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        $this->travelTo(CarbonImmutable::parse('2026-10-19 09:00:00'));

        $this->admin = User::factory()->admin()->create();
        $this->teacher = User::factory()->teacher()->create(['name' => 'Sari Wahyuni']);
        $this->classroom = Classroom::factory()->create(['teacher_id' => $this->teacher->id]);
        $this->enrollment = Enrollment::factory()->inClassroom($this->classroom)->withStatus(EnrollmentStatus::Approved)
            ->for(User::factory()->student()->state(['name' => 'Dewi Lestari']), 'student')->create();
        $this->student = $this->enrollment->student;
        $this->assignment = Assignment::factory()->for($this->classroom)->create([
            'title' => 'Latihan Turunan',
            'due_at' => '2026-10-20 20:00:00',
            'max_score' => 100,
        ]);
    }

    /**
     * Murid lain di kelas lain.
     */
    private function otherClassStudent(): User
    {
        return Enrollment::factory()->inClassroom(Classroom::factory()->create())->withStatus(EnrollmentStatus::Approved)->create()->student;
    }

    /**
     * Test murid dapat mengumpulkan jawaban teks dan berkas; waktu dicatat oleh server.
     */
    public function test_student_can_submit_text_and_file(): void
    {
        $response = $this->actingAs($this->student)->post(route('student.learning.assignments.submit', $this->assignment), [
            'answer_text' => 'Jawaban saya.',
            'file' => UploadedFile::fake()->create('jawaban.pdf', 100, 'application/pdf'),
            'submitted_at' => '2020-01-01 00:00:00',
            'is_late' => '1',
        ]);

        $response->assertRedirect(route('student.learning.assignments.show', $this->assignment));
        $response->assertSessionHas('success', 'Tugas berhasil dikumpulkan pada 19 Oktober 2026, 09:00.');

        $submission = Submission::firstOrFail();
        $this->assertSame($this->student->id, $submission->student_id);
        $this->assertSame($this->enrollment->id, $submission->enrollment_id);
        $this->assertSame('2026-10-19 09:00:00', $submission->submitted_at->format('Y-m-d H:i:s'));
        $this->assertFalse($submission->is_late);
        $this->assertSame('jawaban.pdf', $submission->file_original_name);
        Storage::disk('local')->assertExists($submission->file_path);
    }

    /**
     * Test validasi pengumpulan: jawaban wajib dan tipe berkas.
     */
    public function test_submission_validation(): void
    {
        $this->actingAs($this->student)
            ->post(route('student.learning.assignments.submit', $this->assignment), ['answer_text' => ''])
            ->assertSessionHasErrors(['answer_text' => 'Isi jawaban teks atau unggah berkas jawaban.']);

        $this->actingAs($this->student)
            ->post(route('student.learning.assignments.submit', $this->assignment), ['file' => UploadedFile::fake()->create('skrip.php', 5, 'text/x-php')])
            ->assertSessionHasErrors('file');

        $this->assertDatabaseCount('submissions', 0);
    }

    /**
     * Test pengumpulan setelah tenggat: ditandai terlambat jika diizinkan, ditolak jika tidak.
     */
    public function test_late_submission_rules(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-10-21 08:00:00'));

        $this->actingAs($this->student)
            ->post(route('student.learning.assignments.submit', $this->assignment), ['answer_text' => 'Telat'])
            ->assertForbidden();
        $this->actingAs($this->student)->get(route('student.learning.assignments.show', $this->assignment))
            ->assertSee('Tenggat tugas sudah lewat dan tugas ini tidak menerima pengumpulan terlambat.');
        $this->assertDatabaseCount('submissions', 0);

        $this->assignment->update(['allow_late_submission' => true]);

        $this->actingAs($this->student)
            ->post(route('student.learning.assignments.submit', $this->assignment), ['answer_text' => 'Telat tapi boleh'])
            ->assertSessionHas('success', 'Tugas berhasil dikumpulkan pada 21 Oktober 2026, 08:00. Pengumpulan tercatat terlambat.');

        $this->assertTrue(Submission::firstOrFail()->is_late);
    }

    /**
     * Test pembaruan pengumpulan memperbarui baris yang sama dan mengganti berkas lama.
     */
    public function test_resubmission_updates_same_record(): void
    {
        $this->actingAs($this->student)->post(route('student.learning.assignments.submit', $this->assignment), [
            'answer_text' => 'Versi 1', 'file' => UploadedFile::fake()->create('v1.pdf', 10, 'application/pdf'),
        ]);
        $oldPath = Submission::firstOrFail()->file_path;

        $this->travelTo(CarbonImmutable::parse('2026-10-19 10:30:00'));
        $this->actingAs($this->student)->post(route('student.learning.assignments.submit', $this->assignment), [
            'answer_text' => 'Versi 2', 'file' => UploadedFile::fake()->create('v2.pdf', 10, 'application/pdf'),
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseCount('submissions', 1);
        $submission = Submission::firstOrFail();
        $this->assertSame('Versi 2', $submission->answer_text);
        $this->assertSame('v2.pdf', $submission->file_original_name);
        $this->assertSame(1, $submission->revision_count);
        $this->assertSame('10:30', $submission->submitted_at->format('H:i'));
        Storage::disk('local')->assertMissing($oldPath);
    }

    /**
     * Test pembaruan ditolak jika tugas tidak mengizinkan atau pengumpulan sudah dinilai.
     */
    public function test_resubmission_is_blocked_when_not_allowed_or_graded(): void
    {
        $submission = Submission::factory()->for($this->assignment)->byEnrollment($this->enrollment)->create(['answer_text' => 'Awal']);

        $this->assignment->update(['allow_resubmission' => false]);
        $this->actingAs($this->student)->post(route('student.learning.assignments.submit', $this->assignment), ['answer_text' => 'Ubah'])->assertForbidden();

        $this->assignment->update(['allow_resubmission' => true]);
        AssignmentGrade::factory()->for($submission)->create(['score' => 90]);
        $this->actingAs($this->student)->post(route('student.learning.assignments.submit', $this->assignment), ['answer_text' => 'Ubah'])->assertForbidden();

        $this->assertSame('Awal', $submission->fresh()->answer_text);
    }

    /**
     * Test database menolak dua pengumpulan untuk murid dan tugas yang sama.
     */
    public function test_database_rejects_duplicate_submission(): void
    {
        Submission::factory()->for($this->assignment)->byEnrollment($this->enrollment)->create();

        $this->expectException(UniqueConstraintViolationException::class);
        Submission::factory()->for($this->assignment)->byEnrollment($this->enrollment)->create();
    }

    /**
     * Test murid dari kelas lain, murid yang belum diterima, dan tugas draf tidak dapat dikumpulkan.
     */
    public function test_only_class_members_can_submit_published_assignments(): void
    {
        $pendingStudent = Enrollment::factory()->inClassroom($this->classroom)->create()->student;

        foreach ([$this->otherClassStudent(), $pendingStudent, $this->teacher, $this->admin] as $user) {
            $this->actingAs($user)->post(route('student.learning.assignments.submit', $this->assignment), ['answer_text' => 'x'])->assertForbidden();
        }

        $draft = Assignment::factory()->for($this->classroom)->draft()->create();
        $this->actingAs($this->student)->post(route('student.learning.assignments.submit', $draft), ['answer_text' => 'x'])->assertForbidden();

        $this->assertDatabaseCount('submissions', 0);
    }

    /**
     * Test akses berkas jawaban: pemilik, guru pengampu, dan admin saja.
     */
    public function test_submission_file_access_is_restricted(): void
    {
        $this->actingAs($this->student)->post(route('student.learning.assignments.submit', $this->assignment), [
            'file' => UploadedFile::fake()->create('jawaban.pdf', 10, 'application/pdf'),
        ]);
        $url = route('learning.files.submission', Submission::firstOrFail());

        $this->actingAs($this->student)->get($url)->assertOk()->assertDownload('jawaban.pdf');
        $this->actingAs($this->teacher)->get($url)->assertOk();
        $this->actingAs($this->admin)->get($url)->assertOk();

        $classmate = Enrollment::factory()->inClassroom($this->classroom)->withStatus(EnrollmentStatus::Approved)->create()->student;
        foreach ([$classmate, $this->otherClassStudent(), User::factory()->teacher()->create()] as $user) {
            $this->actingAs($user)->get($url)->assertForbidden();
        }
    }

    /**
     * Test admin dapat menilai, lalu mengubah nilai dengan riwayat nilai sebelumnya; tidak ada penilaian ganda.
     */
    public function test_admin_can_grade_and_regrade_submission(): void
    {
        $submission = Submission::factory()->for($this->assignment)->byEnrollment($this->enrollment)->create();

        $this->actingAs($this->admin)->get(route('admin.learning.submissions.show', $submission))
            ->assertOk()->assertSee('Beri Penilaian')->assertSee('Skala 0–100.');

        $this->actingAs($this->admin)
            ->post(route('admin.learning.grades.store', $submission), ['score' => '87,5', 'feedback' => 'Bagus, perhatikan langkah 3.'])
            ->assertRedirect(route('admin.learning.submissions.show', $submission))
            ->assertSessionHas('success', 'Nilai berhasil disimpan: 87,5.');

        $this->actingAs($this->admin)
            ->post(route('admin.learning.grades.store', $submission), ['score' => '92', 'feedback' => 'Sudah diperbaiki.'])
            ->assertSessionHas('success', 'Nilai berhasil diperbarui menjadi 92.');

        $this->assertDatabaseCount('assignment_grades', 1);
        $grade = AssignmentGrade::firstOrFail();
        $this->assertSame('92.00', $grade->score);
        $this->assertSame('87.50', $grade->previous_score);
        $this->assertSame(1, $grade->revision_count);
        $this->assertSame($this->admin->id, $grade->grader_id);
    }

    /**
     * Test rentang nilai mengikuti skala tugas.
     */
    public function test_score_range_follows_assignment_scale(): void
    {
        $this->assignment->update(['max_score' => 50]);
        $submission = Submission::factory()->for($this->assignment)->byEnrollment($this->enrollment)->create();

        foreach (['51' => 'Nilai tidak boleh melebihi skala maksimal tugas (50).', '-1' => 'Nilai tidak boleh kurang dari 0.', '' => 'Nilai wajib diisi.', 'A' => 'Nilai harus berupa angka.'] as $score => $message) {
            $this->actingAs($this->admin)
                ->post(route('admin.learning.grades.store', $submission), ['score' => (string) $score])
                ->assertSessionHasErrors(['score' => $message]);
        }

        $this->assertDatabaseCount('assignment_grades', 0);
    }

    /**
     * Test guru hanya dapat menilai pengumpulan kelas yang diampunya; murid tidak dapat menilai.
     */
    public function test_teacher_grading_is_limited_to_own_class(): void
    {
        $submission = Submission::factory()->for($this->assignment)->byEnrollment($this->enrollment)->create();

        $this->actingAs($this->teacher)
            ->post(route('teacher.learning.grades.store', $submission), ['score' => '80', 'feedback' => 'Baik'])
            ->assertSessionHas('success', 'Nilai 80 berhasil disimpan.');
        $this->assertSame($this->teacher->id, AssignmentGrade::firstOrFail()->grader_id);

        $this->actingAs(User::factory()->teacher()->create())
            ->post(route('teacher.learning.grades.store', $submission), ['score' => '10'])
            ->assertForbidden();
        $this->actingAs($this->student)
            ->post(route('teacher.learning.grades.store', $submission), ['score' => '100'])
            ->assertForbidden();
        $this->actingAs($this->student)
            ->post(route('admin.learning.grades.store', $submission), ['score' => '100'])
            ->assertForbidden();

        $this->assertSame('80.00', AssignmentGrade::firstOrFail()->score);
    }

    /**
     * Test murid melihat nilai dan umpan balik miliknya sendiri, tetapi tidak milik murid lain.
     */
    public function test_student_sees_own_grade_only(): void
    {
        $submission = Submission::factory()->for($this->assignment)->byEnrollment($this->enrollment)->create();
        AssignmentGrade::factory()->for($submission)->create(['score' => 88, 'feedback' => 'Rapi dan lengkap.']);

        $this->actingAs($this->student)->get(route('student.learning.assignments.show', $this->assignment))
            ->assertOk()->assertSee('Nilai: 88 / 100')->assertSee('Rapi dan lengkap.')->assertSee('Pengumpulan Anda sudah dinilai sehingga tidak dapat diubah lagi.');

        $classmate = Enrollment::factory()->inClassroom($this->classroom)->withStatus(EnrollmentStatus::Approved)->create()->student;
        $this->actingAs($classmate)->get(route('student.learning.assignments.show', $this->assignment))
            ->assertOk()->assertDontSee('Nilai: 88 / 100')->assertDontSee('Rapi dan lengkap.');
    }

    /**
     * Test daftar pengumpulan dan tab penilaian (default belum dinilai) beserta filternya.
     */
    public function test_submission_list_and_grading_tab_filters(): void
    {
        $graded = Submission::factory()->for($this->assignment)->byEnrollment($this->enrollment)->create();
        AssignmentGrade::factory()->for($graded)->create();
        $classmate = Enrollment::factory()->inClassroom($this->classroom)->withStatus(EnrollmentStatus::Approved)
            ->for(User::factory()->student()->state(['name' => 'Rizky Pratama']), 'student')->create();
        $ungradedLate = Submission::factory()->for($this->assignment)->byEnrollment($classmate)->create(['is_late' => true]);

        $ids = fn (string $route, array $query = []): array => $this->actingAs($this->admin)->get(route($route, $query))->viewData('submissions')->pluck('id')->sort()->values()->all();

        $this->assertSame([$graded->id, $ungradedLate->id], $ids('admin.learning.submissions.index'));
        $this->assertSame([$ungradedLate->id], $ids('admin.learning.grades.index'));
        $this->assertSame([$graded->id], $ids('admin.learning.grades.index', ['status' => 'dinilai']));
        $this->assertSame([$ungradedLate->id], $ids('admin.learning.submissions.index', ['status' => 'terlambat']));
        $this->assertSame([$ungradedLate->id], $ids('admin.learning.submissions.index', ['q' => 'Rizky']));
        $this->assertSame([], $ids('admin.learning.submissions.index', ['kelas' => Classroom::factory()->create()->id]));
    }

    /**
     * Test alur lengkap: tugas dibuat admin, murid mengumpulkan, guru menilai, murid melihat nilai.
     */
    public function test_end_to_end_learning_flow(): void
    {
        $this->actingAs($this->admin)->post(route('admin.learning.assignments.store'), [
            'classroom_id' => (string) $this->classroom->id,
            'title' => 'Proyek Akhir',
            'instructions' => 'Buat ringkasan.',
            'due_at' => '2026-10-25T23:59',
            'max_score' => '100',
            'allow_resubmission' => '1',
            'status' => 'published',
        ])->assertSessionHasNoErrors();
        $assignment = Assignment::where('title', 'Proyek Akhir')->firstOrFail();

        $this->actingAs($this->student)->post(route('student.learning.assignments.submit', $assignment), ['answer_text' => 'Ringkasan saya.'])->assertSessionHasNoErrors();
        $submission = Submission::where('assignment_id', $assignment->id)->firstOrFail();

        $this->actingAs($this->teacher)->post(route('teacher.learning.grades.store', $submission), ['score' => '95', 'feedback' => 'Luar biasa.'])->assertSessionHasNoErrors();

        $this->actingAs($this->student)->get(route('student.learning.assignments.show', $assignment))
            ->assertSee('Nilai: 95 / 100')->assertSee('Luar biasa.');
        $this->actingAs($this->student)->get(route('student.learning.index'))->assertSee('Nilai 95/100');
    }
}
