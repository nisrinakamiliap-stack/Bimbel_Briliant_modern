<?php

namespace Tests\Feature\Admin;

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

class HonorariumManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $teacher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(CarbonImmutable::parse('2026-10-19 10:00:00'));

        $this->admin   = User::factory()->admin()->create(['name' => 'Admin Sentra']);
        $this->teacher = User::factory()->teacher()->create(['name' => 'Guru Budi']);
    }

    /**
     * Test role non-admin (tamu, murid, guru) dilarang mengakses modul honorarium admin.
     */
    public function test_non_admin_cannot_access_admin_honorarium(): void
    {
        $this->get(route('admin.honorariums.index'))->assertRedirect(route('login'));

        $student = User::factory()->student()->create();
        $this->actingAs($student)->get(route('admin.honorariums.index'))->assertForbidden();

        $this->actingAs($this->teacher)->get(route('admin.honorariums.index'))->assertForbidden();
    }

    /**
     * Test admin dapat membuka daftar honorarium dan melihat filter serta ringkasan.
     */
    public function test_admin_can_view_honorarium_index_and_filters(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.honorariums.index'))
            ->assertOk()
            ->assertSee('Honorarium Guru')
            ->assertSee('Catat Honorarium');
    }

    /**
     * Test endpoint pengecekan sesi mengajar menghitung kehadiran valid guru tanpa menduplikasi jadwal.
     */
    public function test_admin_can_check_sessions_endpoint(): void
    {
        $classroom = Classroom::factory()->create(['teacher_id' => $this->teacher->id]);
        $schedule1 = Schedule::factory()->create(['classroom_id' => $classroom->id]);
        $schedule2 = Schedule::factory()->create(['classroom_id' => $classroom->id]);

        TeacherAttendance::factory()->create([
            'teacher_id'   => $this->teacher->id,
            'schedule_id'  => $schedule1->id,
            'session_date' => '2026-10-02',
            'status'       => TeacherAttendanceStatus::Present,
        ]);
        TeacherAttendance::factory()->create([
            'teacher_id'   => $this->teacher->id,
            'schedule_id'  => $schedule2->id,
            'session_date' => '2026-10-09',
            'status'       => TeacherAttendanceStatus::Late,
        ]);
        TeacherAttendance::factory()->create([
            'teacher_id'   => $this->teacher->id,
            'schedule_id'  => $schedule1->id,
            'session_date' => '2026-10-16',
            'status'       => TeacherAttendanceStatus::Sick, // Tidak dihitung
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.honorariums.check-sessions', [
                'guru'  => $this->teacher->id,
                'bulan' => 10,
                'tahun' => 2026,
            ]));

        $response->assertOk()
            ->assertJson(['sessions' => 2]);
    }

    /**
     * Test admin dapat menetapkan honorarium guru dengan tarif, penyesuaian, dan potongan resmi.
     */
    public function test_admin_can_create_honorarium_with_rate_adjustments_and_deductions(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.honorariums.store'), [
            'teacher_id'       => $this->teacher->id,
            'period_month'     => 10,
            'period_year'      => 2026,
            'session_count'    => 6,
            'rate_per_session' => '50.000',
            'adjustments'      => '25.000',
            'deductions'       => '10.000',
            'notes'            => 'Penetapan honor resmi bulan Oktober',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('teacher_honorariums', [
            'teacher_id'       => $this->teacher->id,
            'period_month'     => 10,
            'period_year'      => 2026,
            'session_count'    => 6,
            'rate_per_session' => 50000.00,
            'base_amount'      => 300000.00,
            'adjustments'      => 25000.00,
            'deductions'       => 10000.00,
            'amount'           => 315000.00,
            'status'           => HonorariumStatus::Draft->value,
            'created_by'       => $this->admin->id,
        ]);
    }

    /**
     * Test admin tidak dapat membuat duplikat catatan honorarium untuk guru dan periode yang sama.
     */
    public function test_admin_cannot_create_duplicate_honorarium_for_same_period(): void
    {
        $existing = new TeacherHonorarium([
            'session_count'    => 4,
            'rate_per_session' => 50000,
        ]);
        $existing->teacher_id   = $this->teacher->id;
        $existing->period_month = 10;
        $existing->period_year  = 2026;
        $existing->recalculateAmount();
        $existing->status       = HonorariumStatus::Draft;
        $existing->created_by   = $this->admin->id;
        $existing->save();

        $response = $this->actingAs($this->admin)->post(route('admin.honorariums.store'), [
            'teacher_id'       => $this->teacher->id,
            'period_month'     => 10,
            'period_year'      => 2026,
            'session_count'    => 5,
            'rate_per_session' => '60.000',
        ]);

        $response->assertRedirect(route('admin.honorariums.show', $existing));
        $this->assertEquals(1, TeacherHonorarium::count());
    }

    /**
     * Test admin dapat menyetujui honorarium yang berstatus Draft menjadi Approved.
     */
    public function test_admin_can_approve_draft_honorarium(): void
    {
        $honorarium = new TeacherHonorarium([
            'session_count'    => 4,
            'rate_per_session' => 50000,
        ]);
        $honorarium->teacher_id   = $this->teacher->id;
        $honorarium->period_month = 10;
        $honorarium->period_year  = 2026;
        $honorarium->recalculateAmount();
        $honorarium->status       = HonorariumStatus::Draft;
        $honorarium->created_by   = $this->admin->id;
        $honorarium->save();

        $response = $this->actingAs($this->admin)
            ->patch(route('admin.honorariums.approve', $honorarium));

        $response->assertRedirect();

        $honorarium->refresh();
        $this->assertEquals(HonorariumStatus::Approved, $honorarium->status);
        $this->assertEquals($this->admin->id, $honorarium->approved_by);
        $this->assertNotNull($honorarium->approved_at);
    }

    /**
     * Test admin dapat mencatat pembayaran bertahap (sebagian) dan status berubah menjadi PartiallyPaid.
     */
    public function test_admin_can_record_partial_payment(): void
    {
        $honorarium = new TeacherHonorarium([
            'session_count'    => 6,
            'rate_per_session' => 50000, // 300.000
        ]);
        $honorarium->teacher_id   = $this->teacher->id;
        $honorarium->period_month = 10;
        $honorarium->period_year  = 2026;
        $honorarium->recalculateAmount();
        $honorarium->status       = HonorariumStatus::Approved;
        $honorarium->created_by   = $this->admin->id;
        $honorarium->save();

        // Pembayaran sebagian termin 1: Rp100.000
        $response = $this->actingAs($this->admin)
            ->post(route('admin.honorariums.record-payment', $honorarium), [
                'amount'           => '100.000',
                'paid_on'          => '2026-10-18',
                'payment_method'   => PaymentMethod::BankTransfer->value,
                'reference_number' => 'REF-TERMIN-1',
                'notes'            => 'Transfer termin pertama',
            ]);

        $response->assertRedirect();

        $honorarium->refresh();
        $this->assertEquals(HonorariumStatus::PartiallyPaid, $honorarium->status);
        $this->assertEquals(100000.00, (float) $honorarium->paid_amount);
        $this->assertEquals(200000.00, $honorarium->remainingAmount());

        $this->assertDatabaseHas('teacher_honorarium_payments', [
            'teacher_honorarium_id' => $honorarium->id,
            'amount'                => 100000.00,
            'reference_number'      => 'REF-TERMIN-1',
            'created_by'            => $this->admin->id,
        ]);
    }

    /**
     * Test admin mencatat pelunasan sehingga status menjadi Paid.
     */
    public function test_admin_can_record_full_payment_to_complete_honorarium(): void
    {
        $honorarium = new TeacherHonorarium([
            'session_count'    => 4,
            'rate_per_session' => 50000, // 200.000
        ]);
        $honorarium->teacher_id   = $this->teacher->id;
        $honorarium->period_month = 10;
        $honorarium->period_year  = 2026;
        $honorarium->recalculateAmount();
        $honorarium->status       = HonorariumStatus::Approved;
        $honorarium->created_by   = $this->admin->id;
        $honorarium->save();

        $response = $this->actingAs($this->admin)
            ->post(route('admin.honorariums.record-payment', $honorarium), [
                'amount'           => '200000',
                'paid_on'          => '2026-10-18',
                'payment_method'   => PaymentMethod::Cash->value,
                'reference_number' => 'KWITANSI-001',
            ]);

        $response->assertRedirect();

        $honorarium->refresh();
        $this->assertEquals(HonorariumStatus::Paid, $honorarium->status);
        $this->assertEquals(200000.00, (float) $honorarium->paid_amount);
        $this->assertEquals(0.00, $honorarium->remainingAmount());
    }

    /**
     * Test admin tidak dapat mencatat pembayaran yang melebihi sisa honorarium.
     */
    public function test_admin_cannot_record_payment_exceeding_remaining_amount(): void
    {
        $honorarium = new TeacherHonorarium([
            'session_count'    => 2,
            'rate_per_session' => 50000, // 100.000
        ]);
        $honorarium->teacher_id   = $this->teacher->id;
        $honorarium->period_month = 10;
        $honorarium->period_year  = 2026;
        $honorarium->recalculateAmount();
        $honorarium->status       = HonorariumStatus::Approved;
        $honorarium->save();

        $response = $this->actingAs($this->admin)
            ->post(route('admin.honorariums.record-payment', $honorarium), [
                'amount'         => '150000', // melebihi 100.000
                'paid_on'        => '2026-10-18',
                'payment_method' => PaymentMethod::BankTransfer->value,
            ]);

        $response->assertSessionHasErrors('amount');
        $honorarium->refresh();
        $this->assertEquals(0, $honorarium->paid_amount);
    }

    /**
     * Test admin dapat mengunggah bukti pembayaran dan mengunduhnya kembali secara aman.
     */
    public function test_admin_can_upload_and_download_payment_proof(): void
    {
        Storage::fake(TeacherHonorariumPayment::PROOF_DISK);

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

        $proof = UploadedFile::fake()->create('struk_atm.png', 200, 'image/png');

        $response = $this->actingAs($this->admin)
            ->post(route('admin.honorariums.record-payment', $honorarium), [
                'amount'           => '200000',
                'paid_on'          => '2026-10-18',
                'payment_method'   => PaymentMethod::BankTransfer->value,
                'reference_number' => 'ATM-09123',
                'proof'            => $proof,
            ]);

        $response->assertRedirect();

        $payment = TeacherHonorariumPayment::where('teacher_honorarium_id', $honorarium->id)->first();
        $this->assertNotNull($payment);
        $this->assertTrue($payment->hasProof());
        Storage::disk(TeacherHonorariumPayment::PROOF_DISK)->assertExists($payment->proof_path);

        // Admin mengunduh bukti
        $downloadResponse = $this->actingAs($this->admin)
            ->get(route('admin.honorariums.proof', $payment));

        $downloadResponse->assertOk();
    }
}
