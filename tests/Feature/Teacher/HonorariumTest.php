<?php

namespace Tests\Feature\Teacher;

use App\HonorariumStatus;
use App\Models\Classroom;
use App\Models\Schedule;
use App\Models\TeacherAttendance;
use App\Models\TeacherHonorarium;
use App\Models\TeacherHonorariumPayment;
use App\Models\User;
use App\PaymentMethod;
use App\TeacherAttendanceStatus;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HonorariumTest extends TestCase
{
    use RefreshDatabase;

    private User $teacher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(CarbonImmutable::parse('2026-10-19 10:00:00'));

        $this->teacher = User::factory()->teacher()->create([
            'name'  => 'Guru Sentra',
            'email' => 'guru@sentra.test',
        ]);
    }

    /**
     * Test tamu yang belum login diarahkan ke halaman login.
     */
    public function test_guest_cannot_access_teacher_honorarium(): void
    {
        $this->get(route('teacher.honorariums.index'))
            ->assertRedirect(route('login'));
    }

    /**
     * Test role murid tidak dapat mengakses portal honorarium guru (403).
     */
    public function test_student_cannot_access_teacher_honorarium(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student)
            ->get(route('teacher.honorariums.index'))
            ->assertForbidden();
    }

    /**
     * Test role admin tidak dapat mengakses portal honorarium guru (403).
     */
    public function test_admin_cannot_access_teacher_honorarium(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('teacher.honorariums.index'))
            ->assertForbidden();
    }

    /**
     * Test guru dapat membuka halaman Honorarium Saya dan melihat menu navbar yang tepat.
     */
    public function test_teacher_can_access_own_honorarium_index(): void
    {
        $this->actingAs($this->teacher)
            ->get(route('teacher.honorariums.index'))
            ->assertOk()
            ->assertSee('Honorarium Saya')
            ->assertSee('Portal Guru');
    }

    /**
     * Test jika belum ada konfigurasi tarif/penetapan dari admin, nominal ditampilkan sebagai belum tersedia.
     * Sesi mengajar yang dihitung dari absensi tetap ditampilkan tanpa membuat asumsi tarif fiktif.
     */
    public function test_shows_unconfigured_status_and_verified_sessions_when_no_honorarium_record(): void
    {
        $classroom = Classroom::factory()->create(['teacher_id' => $this->teacher->id]);
        $schedule1 = Schedule::factory()->create(['classroom_id' => $classroom->id]);
        $schedule2 = Schedule::factory()->create(['classroom_id' => $classroom->id]);

        // 2 sesi Hadir di bulan Oktober 2026
        TeacherAttendance::factory()->create([
            'teacher_id'   => $this->teacher->id,
            'schedule_id'  => $schedule1->id,
            'session_date' => '2026-10-05',
            'status'       => TeacherAttendanceStatus::Present,
        ]);
        TeacherAttendance::factory()->create([
            'teacher_id'   => $this->teacher->id,
            'schedule_id'  => $schedule2->id,
            'session_date' => '2026-10-12',
            'status'       => TeacherAttendanceStatus::Late, // Terlambat tetap mengajar
        ]);

        // 1 sesi Alpa (tidak boleh dihitung)
        TeacherAttendance::factory()->create([
            'teacher_id'   => $this->teacher->id,
            'schedule_id'  => $schedule1->id,
            'session_date' => '2026-10-19',
            'status'       => TeacherAttendanceStatus::Absent,
        ]);

        $response = $this->actingAs($this->teacher)
            ->get(route('teacher.honorariums.index', ['bulan' => 10, 'tahun' => 2026]));

        $response->assertOk();
        $response->assertSee('Belum Tersedia');
        $response->assertSee('Belum Ditetapkan');
        $response->assertSee('2 sesi'); // hanya 2 sesi yang valid
        $response->assertSee('Konfigurasi Tarif Belum Tersedia');
    }

    /**
     * Test guru dapat melihat rincian honorarium yang sudah dikonfigurasi dan disetujui admin.
     */
    public function test_teacher_can_view_configured_honorarium_details(): void
    {
        $admin = User::factory()->admin()->create();

        $honorarium = new TeacherHonorarium([
            'session_count'    => 8,
            'rate_per_session' => 50000,
            'adjustments'      => 20000,
            'deductions'       => 10000,
            'notes'            => 'Honor mengajar bulan Oktober',
        ]);
        $honorarium->teacher_id   = $this->teacher->id;
        $honorarium->period_month = 10;
        $honorarium->period_year  = 2026;
        $honorarium->recalculateAmount(); // base: 400.000, total: 410.000
        $honorarium->status       = HonorariumStatus::Approved;
        $honorarium->created_by   = $admin->id;
        $honorarium->approved_by  = $admin->id;
        $honorarium->approved_at  = now();
        $honorarium->save();

        $response = $this->actingAs($this->teacher)
            ->get(route('teacher.honorariums.index', ['bulan' => 10, 'tahun' => 2026]));

        $response->assertOk();
        $response->assertSee('Rp410.000');
        $response->assertSee('Rp50.000');
        $response->assertSee('8 sesi');
        $response->assertSee('Menunggu Pembayaran');
    }

    /**
     * Test pencatatan pembayaran sebagian (bertahap) menampilkan status Dibayar Sebagian dan sisa honor yang akurat.
     */
    public function test_teacher_sees_partial_payments_and_remaining_balance(): void
    {
        $admin = User::factory()->admin()->create();

        $honorarium = new TeacherHonorarium([
            'session_count'    => 10,
            'rate_per_session' => 50000,
            'adjustments'      => 0,
            'deductions'       => 0,
        ]);
        $honorarium->teacher_id   = $this->teacher->id;
        $honorarium->period_month = 10;
        $honorarium->period_year  = 2026;
        $honorarium->recalculateAmount(); // 500.000
        $honorarium->status       = HonorariumStatus::Approved;
        $honorarium->created_by   = $admin->id;
        $honorarium->approved_by  = $admin->id;
        $honorarium->approved_at  = now();
        $honorarium->save();

        // Admin mencatat pembayaran bertahap pertama sebesar 200.000
        $honorarium->recordPayment(
            admin: $admin,
            amount: 200000,
            method: PaymentMethod::BankTransfer,
            paidOn: '2026-10-18',
            referenceNumber: 'TRF-BCA-1001',
            notes: 'Pembayaran termin 1',
        );

        $response = $this->actingAs($this->teacher)
            ->get(route('teacher.honorariums.index', ['bulan' => 10, 'tahun' => 2026]));

        $response->assertOk();
        $response->assertSee('Dibayar Sebagian');
        $response->assertSee('Rp500.000'); // total
        $response->assertSee('Rp200.000'); // sudah dibayar
        $response->assertSee('Rp300.000'); // sisa
        $response->assertSee('TRF-BCA-1001');
    }

    /**
     * Test guru tidak dapat melihat detail honorarium milik guru lain (403 Forbidden).
     */
    public function test_teacher_cannot_view_another_teachers_honorarium(): void
    {
        $admin = User::factory()->admin()->create();
        $otherTeacher = User::factory()->teacher()->create(['name' => 'Guru Lain']);

        $honorariumOther = new TeacherHonorarium([
            'session_count'    => 5,
            'rate_per_session' => 60000,
        ]);
        $honorariumOther->teacher_id   = $otherTeacher->id;
        $honorariumOther->period_month = 10;
        $honorariumOther->period_year  = 2026;
        $honorariumOther->recalculateAmount();
        $honorariumOther->status       = HonorariumStatus::Draft;
        $honorariumOther->created_by   = $admin->id;
        $honorariumOther->save();

        $this->actingAs($this->teacher)
            ->get(route('teacher.honorariums.show', $honorariumOther))
            ->assertForbidden();
    }

    /**
     * Test guru dapat mengunduh bukti pembayaran miliknya, namun guru lain tidak dapat mengaksesnya.
     */
    public function test_teacher_can_download_own_payment_proof_but_not_others(): void
    {
        Storage::fake(TeacherHonorariumPayment::PROOF_DISK);

        $admin = User::factory()->admin()->create();

        $honorarium = new TeacherHonorarium([
            'session_count'    => 4,
            'rate_per_session' => 50000,
        ]);
        $honorarium->teacher_id   = $this->teacher->id;
        $honorarium->period_month = 10;
        $honorarium->period_year  = 2026;
        $honorarium->recalculateAmount();
        $honorarium->status       = HonorariumStatus::Approved;
        $honorarium->save();

        $file = UploadedFile::fake()->create('bukti_transfer.jpg', 150, 'image/jpeg');
        $path = $file->store('honorarium-proofs', TeacherHonorariumPayment::PROOF_DISK);

        $payment = $honorarium->recordPayment(
            admin: $admin,
            amount: 200000,
            method: PaymentMethod::BankTransfer,
            paidOn: '2026-10-18',
            proofPath: $path,
            proofOriginalName: 'bukti_transfer.jpg',
        );

        // Guru pemilik dapat mengunduh bukti
        $this->actingAs($this->teacher)
            ->get(route('teacher.honorariums.proof', $payment))
            ->assertOk();

        // Guru lain dilarang mengunduh bukti milik guru ini (403)
        $otherTeacher = User::factory()->teacher()->create();
        $this->actingAs($otherTeacher)
            ->get(route('teacher.honorariums.proof', $payment))
            ->assertForbidden();
    }
}
