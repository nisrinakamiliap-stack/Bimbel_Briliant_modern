<?php

namespace Tests\Feature\Admin;

use App\AttendanceStatus;
use App\EnrollmentStatus;
use App\Models\Assignment;
use App\Models\Classroom;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Program;
use App\Models\Schedule;
use App\Models\StudentAttendance;
use App\Models\Submission;
use App\Models\User;
use App\PaymentMethod;
use App\Weekday;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Program $program;

    private Classroom $classroom;

    private Enrollment $enrollment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo('2026-10-18 10:00:00');

        $this->admin = User::factory()->admin()->create();
        $this->program = Program::factory()->create(['name' => 'Bimbingan Belajar SMA', 'price' => 1000000]);
        $this->classroom = Classroom::factory()->for($this->program)->create(['name' => 'SMA 12 IPA A']);
        $student = User::factory()->student()->create(['name' => 'Dewi Lestari']);
        $this->enrollment = Enrollment::factory()->for($this->program)->inClassroom($this->classroom)->withStatus(EnrollmentStatus::Approved)->create([
            'student_id' => $student->id,
            'enrolled_on' => '2026-10-05',
        ]);
    }

    /**
     * Isi CSV dari respons unduhan tanpa BOM.
     */
    private function csvContent(TestResponse $response): string
    {
        $response->assertOk()->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        return ltrim($response->streamedContent(), "\xEF\xBB\xBF");
    }

    /**
     * Test laporan murid menghitung pendaftaran sesuai periode dari database.
     */
    public function test_student_report_counts_enrollments_in_period(): void
    {
        Enrollment::factory()->for($this->program)->withStatus(EnrollmentStatus::Rejected)->create(['enrolled_on' => '2026-10-10']);
        Enrollment::factory()->for($this->program)->create(['enrolled_on' => '2026-08-01']);

        $this->actingAs($this->admin)
            ->get(route('admin.reports.students', ['dari' => '2026-10-01', 'sampai' => '2026-10-31']))
            ->assertOk()
            ->assertViewHas('enrollmentCount', 2)
            ->assertViewHas('activeStudents', 1)
            ->assertViewHas('totalStudents', 3)
            ->assertViewHas('statusCounts', fn ($counts) => $counts['Diterima'] === 1 && $counts['Ditolak'] === 1);
    }

    /**
     * Test ekspor CSV laporan murid mengikuti filter periode.
     */
    public function test_student_report_csv_follows_filters(): void
    {
        $oldStudent = User::factory()->student()->create(['name' => 'Murid Agustus']);
        Enrollment::factory()->for($this->program)->create(['student_id' => $oldStudent->id, 'enrolled_on' => '2026-08-01']);

        $csv = $this->csvContent($this->actingAs($this->admin)
            ->get(route('admin.reports.students', ['dari' => '2026-10-01', 'sampai' => '2026-10-31', 'export' => 'csv'])));

        $this->assertStringContainsString('"Tanggal Daftar";"Nama Murid"', $csv);
        $this->assertStringContainsString('05/10/2026;"Dewi Lestari"', $csv);
        $this->assertStringContainsString('Rp1.000.000', $csv);
        $this->assertStringNotContainsString('Murid Agustus', $csv);
    }

    /**
     * Test pendapatan hanya menghitung pembayaran yang sudah diverifikasi (Berhasil).
     */
    public function test_payment_report_counts_only_verified_payments_as_revenue(): void
    {
        Payment::factory()->for($this->enrollment)->paid($this->admin)->create(['amount' => 400000, 'paid_on' => '2026-10-06', 'method' => PaymentMethod::Cash]);
        Payment::factory()->for($this->enrollment)->create(['amount' => 250000, 'paid_on' => '2026-10-07']);
        Payment::factory()->for($this->enrollment)->rejected()->create(['amount' => 100000, 'paid_on' => '2026-10-08']);
        Payment::factory()->for($this->enrollment)->paid($this->admin)->create(['amount' => 50000, 'paid_on' => '2026-09-20']);

        $this->actingAs($this->admin)
            ->get(route('admin.reports.payments', ['dari' => '2026-10-01', 'sampai' => '2026-10-31']))
            ->assertOk()
            ->assertViewHas('summary', fn (array $summary) => $summary['paid']['count'] === 1
                && (int) $summary['paid']['total'] === 400000
                && (int) $summary['waiting']['total'] === 250000
                && (int) $summary['rejected']['total'] === 100000
                && (int) $summary['outstanding']['total'] === 550000)
            ->assertSee('Rp400.000');
    }

    /**
     * Test ekspor CSV pembayaran mengikuti filter metode.
     */
    public function test_payment_report_csv_follows_method_filter(): void
    {
        $cash = Payment::factory()->for($this->enrollment)->paid($this->admin)->create(['amount' => 400000, 'paid_on' => '2026-10-06', 'method' => PaymentMethod::Cash]);
        $transfer = Payment::factory()->for($this->enrollment)->create(['amount' => 250000, 'paid_on' => '2026-10-07', 'method' => PaymentMethod::BankTransfer]);

        $csv = $this->csvContent($this->actingAs($this->admin)
            ->get(route('admin.reports.payments', ['dari' => '2026-10-01', 'sampai' => '2026-10-31', 'metode' => PaymentMethod::Cash->value, 'export' => 'csv'])));

        $this->assertStringContainsString($cash->code, $csv);
        $this->assertStringNotContainsString($transfer->code, $csv);
    }

    /**
     * Test laporan absensi hanya menghitung sesi yang sudah tercatat; jadwal yang belum berlangsung bukan alpa.
     */
    public function test_attendance_report_does_not_count_future_sessions_as_absent(): void
    {
        $schedule = Schedule::factory()->for($this->classroom)->at(Weekday::Monday, '08:00', '09:30')->create();
        StudentAttendance::factory()->forSession($schedule, $this->enrollment, '2026-10-12')->create(['status' => AttendanceStatus::Present]);

        $this->actingAs($this->admin)
            ->get(route('admin.reports.attendance', ['dari' => '2026-10-01', 'sampai' => '2026-10-31']))
            ->assertOk()
            ->assertViewHas('studentCounts', fn ($counts) => $counts['Hadir'] === 1 && $counts['Alpa'] === 0)
            ->assertViewHas('studentRecap', fn ($recap) => $recap->count() === 1 && $recap->first()['total'] === 1);

        $csv = $this->csvContent($this->actingAs($this->admin)
            ->get(route('admin.reports.attendance', ['dari' => '2026-10-01', 'sampai' => '2026-10-31', 'export' => 'csv'])));
        $this->assertStringContainsString('"Dewi Lestari";1;0;0;0;1;100%', $csv);
    }

    /**
     * Test laporan pembelajaran: murid belum mengumpulkan hanya dihitung setelah tenggat lewat.
     */
    public function test_learning_report_counts_missing_submissions_only_after_due_date(): void
    {
        $otherStudent = Enrollment::factory()->for($this->program)->inClassroom($this->classroom)->withStatus(EnrollmentStatus::Approved)->create();
        $pastDue = Assignment::factory()->for($this->classroom)->create(['title' => 'Tugas Lewat', 'due_at' => '2026-10-10 23:59:00']);
        Assignment::factory()->for($this->classroom)->create(['title' => 'Tugas Mendatang', 'due_at' => '2026-10-25 23:59:00']);
        Submission::factory()->for($pastDue)->byEnrollment($this->enrollment)->create(['submitted_at' => '2026-10-09 10:00:00']);

        $this->actingAs($this->admin)
            ->get(route('admin.reports.learning', ['dari' => '2026-10-01', 'sampai' => '2026-10-31']))
            ->assertOk()
            ->assertViewHas('rows', function ($rows) {
                $past = $rows->firstWhere('title', 'Tugas Lewat');
                $upcoming = $rows->firstWhere('title', 'Tugas Mendatang');

                return $past['participants'] === 2 && $past['submitted'] === 1 && $past['missing'] === 1 && $upcoming['missing'] === null;
            })
            ->assertViewHas('totals', fn (array $totals) => $totals['assignments'] === 2 && $totals['missing'] === 1 && $totals['ungraded'] === 1);

        $this->assertNotNull($otherStudent);
    }

    /**
     * Test nilai CSV yang diawali karakter formula diberi awalan agar tidak dieksekusi spreadsheet.
     */
    public function test_csv_export_neutralizes_formula_injection(): void
    {
        $this->enrollment->student->update(['name' => '=HYPERLINK("http://jahat.test")']);

        $csv = $this->csvContent($this->actingAs($this->admin)
            ->get(route('admin.reports.students', ['dari' => '2026-10-01', 'sampai' => '2026-10-31', 'export' => 'csv'])));

        $this->assertStringContainsString("'=HYPERLINK", $csv);
    }

    /**
     * Test periode tidak valid ditolak.
     */
    public function test_invalid_period_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.reports.payments', ['dari' => '2026-10-31', 'sampai' => '2026-10-01']))
            ->assertSessionHasErrors('sampai');
    }

    /**
     * Test guru dan murid tidak dapat membuka atau mengekspor laporan.
     */
    public function test_non_admin_cannot_open_or_export_reports(): void
    {
        foreach ([User::factory()->teacher()->create(), $this->enrollment->student] as $user) {
            foreach (['admin.reports.students', 'admin.reports.payments', 'admin.reports.attendance', 'admin.reports.learning'] as $route) {
                $this->actingAs($user)->get(route($route))->assertForbidden();
                $this->actingAs($user)->get(route($route, ['export' => 'csv']))->assertForbidden();
            }
        }
    }
}
