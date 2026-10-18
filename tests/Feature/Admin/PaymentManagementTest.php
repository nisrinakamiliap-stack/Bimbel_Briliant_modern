<?php

namespace Tests\Feature\Admin;

use App\EnrollmentStatus;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Program;
use App\Models\User;
use App\PaymentMethod;
use App\PaymentStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $student;

    private Enrollment $enrollment;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->admin = User::factory()->admin()->create(['name' => 'Admin Keuangan']);
        $this->student = User::factory()->student()->create(['name' => 'Dewi Lestari', 'email' => 'dewi@example.com']);
        $program = Program::factory()->create(['name' => 'Bimbingan Belajar SMA', 'price' => 500000]);
        $this->enrollment = Enrollment::factory()->for($program)->withStatus(EnrollmentStatus::Approved)->create([
            'student_id' => $this->student->id,
        ]);
    }

    /**
     * Data form pembayaran yang valid.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return [
            'enrollment_id' => (string) $this->enrollment->id,
            'amount' => '200000',
            'method' => PaymentMethod::BankTransfer->value,
            'paid_on' => now()->format('Y-m-d'),
            'notes' => 'Transfer dari rekening orang tua.',
            ...$overrides,
        ];
    }

    /**
     * Membuat transaksi untuk pendaftaran utama.
     */
    private function payment(PaymentStatus $status = PaymentStatus::WaitingVerification, int $amount = 100000, array $attributes = []): Payment
    {
        $factory = Payment::factory()->for($this->enrollment);
        $factory = match ($status) {
            PaymentStatus::Paid => $factory->paid($this->admin),
            PaymentStatus::Rejected => $factory->rejected(),
            PaymentStatus::WaitingVerification => $factory,
        };

        return $factory->create(['amount' => $amount, ...$attributes]);
    }

    /**
     * Test tagihan pendaftaran diambil dari harga program saat pendaftaran dibuat.
     */
    public function test_enrollment_bill_is_snapshot_of_program_price(): void
    {
        $program = Program::factory()->create(['price' => 750000]);

        $this->actingAs($this->admin)->post(route('admin.enrollments.store'), [
            'student_id' => (string) User::factory()->student()->create()->id,
            'program_id' => (string) $program->id,
            'enrolled_on' => now()->format('Y-m-d'),
            'bill_amount' => '1',
        ])->assertSessionHasNoErrors();

        $enrollment = Enrollment::where('program_id', $program->id)->firstOrFail();
        $this->assertSame('750000.00', $enrollment->bill_amount);

        $program->update(['price' => 900000]);
        $this->assertSame('750000.00', $enrollment->fresh()->bill_amount);
    }

    /**
     * Test daftar transaksi menampilkan data dari database melalui relasi pendaftaran.
     */
    public function test_index_lists_payments_with_relations(): void
    {
        $payment = $this->payment(PaymentStatus::Paid, 150000, ['paid_on' => '2026-10-05']);

        $response = $this->actingAs($this->admin)->get(route('admin.payments.index'));

        $response->assertOk();
        $response->assertViewIs('admin.payments.index');
        $response->assertSee('Pembayaran');
        $response->assertSee($payment->code);
        $response->assertSee('Dewi Lestari');
        $response->assertSee('Bimbingan Belajar SMA');
        $response->assertSee('Rp150.000');
        $response->assertSee('05 Okt 2026');
        $response->assertSee('Berhasil');
    }

    /**
     * Test ringkasan dihitung dari database: transaksi menunggu tidak dihitung sebagai berhasil.
     */
    public function test_summary_is_calculated_from_database(): void
    {
        $this->payment(PaymentStatus::Paid, 200000);
        $this->payment(PaymentStatus::WaitingVerification, 50000);
        $this->payment(PaymentStatus::Rejected, 90000);
        $paidOff = Enrollment::factory()->for(Program::factory()->state(['price' => 300000]))->create();
        Payment::factory()->for($paidOff)->paid()->create(['amount' => 300000]);
        Enrollment::factory()->for(Program::factory()->state(['price' => 400000]))->withStatus(EnrollmentStatus::Cancelled)->create();

        $response = $this->actingAs($this->admin)->get(route('admin.payments.index'));

        $response->assertViewHas('summary', [
            'paid_total' => 500000.0,
            'waiting_count' => 1,
            'waiting_total' => 50000.0,
            'outstanding_count' => 1,
            'outstanding_total' => 300000.0,
        ]);
        $response->assertSee('Rp500.000');
        $response->assertSee('1 transaksi');
        $response->assertSee('Sisa Rp300.000');
    }

    /**
     * Test daftar transaksi memakai eager loading sehingga jumlah query tidak bertambah per baris.
     */
    public function test_index_avoids_n_plus_one_queries(): void
    {
        $this->payment();

        $countQueries = function (): int {
            $count = 0;
            DB::listen(function () use (&$count): void {
                $count++;
            });
            $this->actingAs($this->admin)->get(route('admin.payments.index'))->assertOk();

            return $count;
        };

        // Permintaan pemanasan agar cache pengaturan lembaga tidak memengaruhi perbandingan.
        $countQueries();
        $queriesWithOne = $countQueries();
        Payment::factory()->count(4)->create();
        $queriesWithFive = $countQueries();

        $this->assertSame($queriesWithOne, $queriesWithFive, 'Query daftar transaksi dan ringkasan tidak boleh bertambah per baris.');
    }

    /**
     * Test empty state saat belum ada transaksi.
     */
    public function test_index_shows_empty_state(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.payments.index'))
            ->assertOk()
            ->assertSee('Belum ada transaksi');
    }

    /**
     * Test pencarian berdasarkan nama murid atau nomor transaksi.
     */
    public function test_index_can_search_by_student_or_code(): void
    {
        $dewiPayment = $this->payment();
        $otherPayment = Payment::factory()->for(Enrollment::factory()->for(User::factory()->student()->state(['name' => 'Rizky Pratama']), 'student'))->create();

        $codes = fn (array $query): array => $this->actingAs($this->admin)
            ->get(route('admin.payments.index', $query))
            ->viewData('payments')
            ->pluck('code')
            ->all();

        $this->assertSame([$dewiPayment->code], $codes(['q' => 'dewi']));
        $this->assertSame([$otherPayment->code], $codes(['q' => 'Rizky']));
        $this->assertSame([$otherPayment->code], $codes(['q' => $otherPayment->code]));
    }

    /**
     * Test filter status, metode, periode, murid, dan pendaftaran.
     */
    public function test_index_can_filter_payments(): void
    {
        $transfer = $this->payment(PaymentStatus::Paid, 100000, ['method' => PaymentMethod::BankTransfer, 'paid_on' => '2026-09-15']);
        $cash = $this->payment(PaymentStatus::WaitingVerification, 50000, ['method' => PaymentMethod::Cash, 'paid_on' => '2026-10-10']);
        $other = Payment::factory()->create(['method' => PaymentMethod::Cash, 'paid_on' => '2026-10-12']);

        $ids = fn (array $query): array => $this->actingAs($this->admin)
            ->get(route('admin.payments.index', $query))
            ->viewData('payments')
            ->pluck('id')
            ->sort()
            ->values()
            ->all();

        $this->assertSame([$transfer->id], $ids(['status' => 'paid']));
        $this->assertSame([$cash->id, $other->id], $ids(['metode' => 'cash']));
        $this->assertSame([$cash->id, $other->id], $ids(['dari' => '2026-10-01', 'sampai' => '2026-10-31']));
        $this->assertSame([$transfer->id, $cash->id], $ids(['murid' => $this->student->id]));
        $this->assertSame([$transfer->id, $cash->id], $ids(['pendaftaran' => $this->enrollment->id]));

        $this->actingAs($this->admin)
            ->get(route('admin.payments.index', ['dari' => '2026-10-31', 'sampai' => '2026-10-01']))
            ->assertSessionHasErrors('sampai');
    }

    /**
     * Test pagination membagi daftar transaksi per 10 data.
     */
    public function test_index_is_paginated(): void
    {
        Payment::factory()->count(12)->create();

        $firstPage = $this->actingAs($this->admin)->get(route('admin.payments.index'));
        $this->assertCount(10, $firstPage->viewData('payments'));
        $firstPage->assertSee('Menampilkan 1–10 dari 12 data');

        $this->assertCount(2, $this->actingAs($this->admin)->get(route('admin.payments.index', ['page' => 2]))->viewData('payments'));
    }

    /**
     * Test admin dapat mencatat pembayaran dengan bukti; transaksi berstatus Menunggu Verifikasi.
     */
    public function test_admin_can_record_payment_with_private_proof(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.payments.store'), $this->validPayload([
            'amount' => 'Rp 200.000',
            'proof' => UploadedFile::fake()->image('bukti-transfer.jpg'),
            'status' => 'paid',
        ]));

        $payment = Payment::firstOrFail();
        $response->assertRedirect(route('admin.payments.show', $payment));
        $response->assertSessionHas('success');

        $this->assertMatchesRegularExpression('/^PAY-\d{8}-[A-Z0-9]{6}$/', $payment->code);
        $this->assertSame('200000.00', $payment->amount);
        $this->assertSame(PaymentStatus::WaitingVerification, $payment->status);
        $this->assertSame(PaymentMethod::BankTransfer, $payment->method);
        $this->assertSame($this->admin->id, $payment->recorded_by);
        $this->assertSame('bukti-transfer.jpg', $payment->proof_original_name);
        $this->assertStringStartsWith('payment-proofs/', $payment->proof_path);
        Storage::disk('local')->assertExists($payment->proof_path);
        Storage::disk('public')->assertMissing($payment->proof_path);

        $this->assertSame(0, $this->enrollment->fresh()->paidCents());
        $this->assertSame('Belum Dibayar', $this->enrollment->fresh()->billingStatusLabel());
    }

    /**
     * Test validasi nominal, metode, tanggal, dan pendaftaran.
     */
    public function test_record_payment_validation(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.payments.store'), [])
            ->assertSessionHasErrors(['enrollment_id', 'amount', 'method', 'paid_on']);

        foreach (['0' => 'Nominal pembayaran harus lebih besar dari nol.', '-1000' => 'Nominal pembayaran harus lebih besar dari nol.', 'seratus' => 'Nominal harus berupa angka, contoh: 350000.'] as $amount => $message) {
            $this->actingAs($this->admin)
                ->post(route('admin.payments.store'), $this->validPayload(['amount' => (string) $amount]))
                ->assertSessionHasErrors(['amount' => $message]);
        }

        $this->actingAs($this->admin)
            ->post(route('admin.payments.store'), $this->validPayload([
                'method' => 'kartu_kredit',
                'paid_on' => now()->addDay()->format('Y-m-d'),
            ]))
            ->assertSessionHasErrors(['method', 'paid_on' => 'Tanggal pembayaran tidak boleh melebihi hari ini.']);

        $cancelled = Enrollment::factory()->withStatus(EnrollmentStatus::Cancelled)->create();
        $this->actingAs($this->admin)
            ->post(route('admin.payments.store'), $this->validPayload(['enrollment_id' => (string) $cancelled->id]))
            ->assertSessionHasErrors(['enrollment_id' => 'Pendaftaran tidak ditemukan atau sudah tidak aktif (ditolak/dibatalkan).']);

        $this->assertDatabaseCount('payments', 0);
    }

    /**
     * Test file bukti divalidasi tipe dan ukurannya.
     */
    public function test_proof_file_type_and_size_are_validated(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.payments.store'), $this->validPayload(['proof' => UploadedFile::fake()->create('script.php', 10, 'text/x-php')]))
            ->assertSessionHasErrors(['proof' => 'Bukti pembayaran harus berupa gambar JPG/PNG atau dokumen PDF.']);

        $this->actingAs($this->admin)
            ->post(route('admin.payments.store'), $this->validPayload(['proof' => UploadedFile::fake()->create('besar.pdf', 3000, 'application/pdf')]))
            ->assertSessionHasErrors(['proof' => 'Ukuran bukti pembayaran maksimal 2 MB.']);

        $this->assertDatabaseCount('payments', 0);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    /**
     * Test nominal tidak boleh melebihi sisa tagihan, termasuk transaksi yang masih menunggu verifikasi.
     */
    public function test_amount_cannot_exceed_payable_remaining_bill(): void
    {
        $this->payment(PaymentStatus::Paid, 300000);
        $this->payment(PaymentStatus::WaitingVerification, 150000);
        $this->payment(PaymentStatus::Rejected, 400000);

        $this->actingAs($this->admin)
            ->post(route('admin.payments.store'), $this->validPayload(['amount' => '60000']))
            ->assertSessionHasErrors(['amount' => 'Nominal tidak boleh melebihi sisa tagihan yang dapat dibayar (Rp50.000).']);

        $this->actingAs($this->admin)
            ->post(route('admin.payments.store'), $this->validPayload(['amount' => '50000']))
            ->assertSessionHasNoErrors();

        $this->actingAs($this->admin)
            ->post(route('admin.payments.store'), $this->validPayload(['amount' => '1']))
            ->assertSessionHasErrors(['amount' => 'Tagihan pendaftaran ini sudah lunas atau seluruh sisanya sedang menunggu verifikasi.']);
    }

    /**
     * Test pembayaran sebagian: sisa tagihan dan status Lunas dihitung dari pembayaran berhasil saja.
     */
    public function test_partial_payments_and_paid_off_status(): void
    {
        $first = $this->payment(PaymentStatus::WaitingVerification, 200000);
        $second = $this->payment(PaymentStatus::WaitingVerification, 300000);

        $this->assertSame('Belum Dibayar', $this->enrollment->fresh()->billingStatusLabel());

        $this->actingAs($this->admin)->patch(route('admin.payments.verify', $first))->assertSessionHas('success');
        $enrollment = $this->enrollment->fresh();
        $this->assertSame('Sebagian', $enrollment->billingStatusLabel());
        $this->assertSame(30000000, $enrollment->remainingCents());

        $this->actingAs($this->admin)->patch(route('admin.payments.verify', $second))->assertSessionHas('success');
        $enrollment = $this->enrollment->fresh();
        $this->assertSame('Lunas', $enrollment->billingStatusLabel());
        $this->assertSame(0, $enrollment->remainingCents());

        $this->actingAs($this->admin)
            ->get(route('admin.enrollments.show', $this->enrollment))
            ->assertSee('Lunas')
            ->assertDontSee('id="btn-record-payment"', false);
    }

    /**
     * Test verifikasi menyimpan status, waktu, dan admin pemverifikasi.
     */
    public function test_admin_can_verify_payment(): void
    {
        $payment = $this->payment(PaymentStatus::WaitingVerification, 200000);

        $this->actingAs($this->admin)
            ->get(route('admin.payments.show', $payment))
            ->assertOk()
            ->assertSee('id="btn-verify-payment"', false)
            ->assertSee('data-confirm-title="Verifikasi Pembayaran"', false);

        $this->actingAs($this->admin)
            ->from(route('admin.payments.show', $payment))
            ->patch(route('admin.payments.verify', $payment), ['reason' => 'Dana sudah masuk.'])
            ->assertRedirect(route('admin.payments.show', $payment))
            ->assertSessionHas('success', "Pembayaran {$payment->code} berhasil diverifikasi.");

        $payment->refresh();
        $this->assertSame(PaymentStatus::Paid, $payment->status);
        $this->assertSame($this->admin->id, $payment->processed_by);
        $this->assertNotNull($payment->processed_at);
        $this->assertStringContainsString('Catatan verifikasi: Dana sudah masuk.', $payment->notes);

        $this->actingAs($this->admin)
            ->get(route('admin.payments.show', $payment))
            ->assertSee('Diverifikasi Oleh')
            ->assertSee('Admin Keuangan')
            ->assertDontSee('id="btn-verify-payment"', false);
    }

    /**
     * Test penolakan wajib menyertakan alasan dan alasan tersebut ditampilkan.
     */
    public function test_admin_can_reject_payment_with_reason(): void
    {
        $payment = $this->payment();

        $this->actingAs($this->admin)
            ->patch(route('admin.payments.reject', $payment), ['reason' => ''])
            ->assertSessionHasErrors(['reason' => 'Alasan penolakan wajib diisi agar dapat disampaikan kepada murid.']);
        $this->assertSame(PaymentStatus::WaitingVerification, $payment->fresh()->status);

        $this->actingAs($this->admin)
            ->patch(route('admin.payments.reject', $payment), ['reason' => 'Nominal transfer tidak sesuai.'])
            ->assertSessionHas('success', "Pembayaran {$payment->code} ditolak.");

        $payment->refresh();
        $this->assertSame(PaymentStatus::Rejected, $payment->status);
        $this->assertSame('Nominal transfer tidak sesuai.', $payment->rejection_reason);
        $this->assertSame($this->admin->id, $payment->processed_by);

        $this->actingAs($this->admin)
            ->get(route('admin.payments.show', $payment))
            ->assertSee('Alasan Penolakan')
            ->assertSee('Nominal transfer tidak sesuai.');
    }

    /**
     * Test transaksi yang sudah diproses tidak dapat diproses ulang.
     */
    public function test_processed_payment_cannot_be_processed_again(): void
    {
        $paid = $this->payment(PaymentStatus::Paid, 100000);
        $rejected = $this->payment(PaymentStatus::Rejected, 100000);
        $originalProcessedAt = $paid->processed_at;

        $this->actingAs($this->admin)
            ->patch(route('admin.payments.verify', $paid))
            ->assertSessionHasErrors(['status' => "Transaksi {$paid->code} sudah diproses dengan status Berhasil dan tidak dapat diproses ulang."]);
        $this->actingAs($this->admin)
            ->patch(route('admin.payments.reject', $paid), ['reason' => 'Coba tolak'])
            ->assertSessionHasErrors('status');
        $this->actingAs($this->admin)
            ->patch(route('admin.payments.verify', $rejected))
            ->assertSessionHasErrors('status');

        $this->assertSame(PaymentStatus::Paid, $paid->fresh()->status);
        $this->assertEquals($originalProcessedAt, $paid->fresh()->processed_at);
        $this->assertSame(PaymentStatus::Rejected, $rejected->fresh()->status);
        $this->assertSame(10000000, $this->enrollment->fresh()->paidCents());
    }

    /**
     * Test verifikasi menolak transaksi yang akan membuat total berhasil melebihi tagihan.
     */
    public function test_verification_rechecks_remaining_bill(): void
    {
        $waiting = $this->payment(PaymentStatus::WaitingVerification, 400000);
        $this->enrollment->update(['bill_amount' => 300000]);

        $this->actingAs($this->admin)
            ->patch(route('admin.payments.verify', $waiting))
            ->assertSessionHasErrors('status');

        $this->assertSame(PaymentStatus::WaitingVerification, $waiting->fresh()->status);
    }

    /**
     * Test bukti pembayaran hanya dapat dibuka admin melalui route khusus.
     */
    public function test_proof_is_only_accessible_to_admin(): void
    {
        $this->actingAs($this->admin)->post(route('admin.payments.store'), $this->validPayload([
            'proof' => UploadedFile::fake()->image('bukti.png'),
        ]));
        $payment = Payment::firstOrFail();

        $this->actingAs($this->admin)
            ->get(route('admin.payments.proof', $payment))
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private');

        $this->actingAs($this->admin)
            ->get(route('admin.payments.show', $payment))
            ->assertSee('id="btn-view-proof"', false)
            ->assertSee('<img src="'.route('admin.payments.proof', $payment).'"', false);

        foreach ([$this->student, User::factory()->teacher()->create(), User::factory()->student()->create()] as $user) {
            $this->actingAs($user)->get(route('admin.payments.proof', $payment))->assertForbidden();
        }

        auth()->logout();
        $this->get(route('admin.payments.proof', $payment))->assertRedirect(route('login'));

        $withoutProof = $this->payment();
        $this->actingAs($this->admin)->get(route('admin.payments.proof', $withoutProof))->assertNotFound();
    }

    /**
     * Test detail transaksi menampilkan riwayat transaksi pendaftaran yang sama.
     */
    public function test_detail_shows_related_payment_history(): void
    {
        $first = $this->payment(PaymentStatus::Paid, 100000);
        $second = $this->payment(PaymentStatus::Rejected, 50000);
        $unrelated = Payment::factory()->create();

        $this->actingAs($this->admin)
            ->get(route('admin.payments.show', $first))
            ->assertOk()
            ->assertSee('Riwayat Transaksi Pendaftaran')
            ->assertSee($first->code)
            ->assertSee($second->code)
            ->assertDontSee($unrelated->code)
            ->assertSee('Sebagian');
    }

    /**
     * Test transaksi tidak dapat diubah atau dihapus.
     */
    public function test_payments_cannot_be_edited_or_deleted(): void
    {
        $payment = $this->payment();

        $this->actingAs($this->admin)->delete('/admin/pembayaran/'.$payment->id)->assertMethodNotAllowed();
        $this->actingAs($this->admin)->put('/admin/pembayaran/'.$payment->id, ['amount' => 1])->assertMethodNotAllowed();
        $this->actingAs($this->admin)->get('/admin/pembayaran/'.$payment->id.'/edit')->assertNotFound();

        $this->assertModelExists($payment);
        $this->assertSame('100000.00', $payment->fresh()->amount);
    }

    /**
     * Test guru, murid, dan tamu tidak dapat mengakses modul pembayaran admin.
     */
    public function test_non_admin_users_cannot_manage_payments(): void
    {
        $payment = $this->payment();

        foreach ([User::factory()->teacher()->create(), $this->student] as $user) {
            $this->actingAs($user)->get(route('admin.payments.index'))->assertForbidden();
            $this->actingAs($user)->get(route('admin.payments.create'))->assertForbidden();
            $this->actingAs($user)->post(route('admin.payments.store'), $this->validPayload())->assertForbidden();
            $this->actingAs($user)->get(route('admin.payments.show', $payment))->assertForbidden();
            $this->actingAs($user)->patch(route('admin.payments.verify', $payment))->assertForbidden();
            $this->actingAs($user)->patch(route('admin.payments.reject', $payment), ['reason' => 'x'])->assertForbidden();
        }

        auth()->logout();
        $this->get(route('admin.payments.index'))->assertRedirect(route('login'));

        $this->assertSame(PaymentStatus::WaitingVerification, $payment->fresh()->status);
        $this->assertDatabaseCount('payments', 1);
    }

    /**
     * Test form catat pembayaran hanya menawarkan pendaftaran aktif yang masih memiliki sisa tagihan.
     */
    public function test_create_page_lists_only_payable_enrollments(): void
    {
        $paidOff = Enrollment::factory()->for(Program::factory()->state(['name' => 'Program Lunas', 'price' => 100000]))->create();
        Payment::factory()->for($paidOff)->paid()->create(['amount' => 100000]);
        Enrollment::factory()->for(Program::factory()->state(['name' => 'Program Dibatalkan']))->withStatus(EnrollmentStatus::Cancelled)->create();

        $this->actingAs($this->admin)
            ->get(route('admin.payments.create', ['pendaftaran' => $this->enrollment->id]))
            ->assertOk()
            ->assertSee('Dewi Lestari — Bimbingan Belajar SMA — dapat dibayar Rp500.000 dari Rp500.000')
            ->assertSee('value="'.$this->enrollment->id.'" selected', false)
            ->assertDontSee('Program Lunas')
            ->assertDontSee('Program Dibatalkan');
    }

    /**
     * Test integrasi pendaftaran: tagihan dapat disesuaikan tetapi tidak di bawah pembayaran berhasil,
     * dan program tidak dapat diganti setelah ada transaksi.
     */
    public function test_enrollment_bill_adjustment_and_program_lock(): void
    {
        $pending = Enrollment::factory()->for(Program::factory()->state(['price' => 400000]))->create();
        Payment::factory()->for($pending)->paid()->create(['amount' => 150000]);
        $otherProgram = Program::factory()->create(['price' => 999000]);

        $this->actingAs($this->admin)
            ->get(route('admin.enrollments.edit', $pending))
            ->assertDontSee('name="program_id"', false)
            ->assertSee('name="bill_amount"', false);

        $this->actingAs($this->admin)
            ->put(route('admin.enrollments.update', $pending), [
                'program_id' => (string) $otherProgram->id,
                'enrolled_on' => $pending->enrolled_on->format('Y-m-d'),
                'bill_amount' => '100.000',
            ])
            ->assertSessionHasErrors(['bill_amount' => 'Total tagihan tidak boleh kurang dari pembayaran yang sudah berhasil (Rp150.000).']);

        $this->actingAs($this->admin)
            ->put(route('admin.enrollments.update', $pending), [
                'program_id' => (string) $otherProgram->id,
                'enrolled_on' => $pending->enrolled_on->format('Y-m-d'),
                'bill_amount' => 'Rp 350.000',
            ])
            ->assertSessionHasNoErrors();

        $pending->refresh();
        $this->assertSame('350000.00', $pending->bill_amount);
        $this->assertNotSame($otherProgram->id, $pending->program_id);
    }

    /**
     * Test integrasi: detail pendaftaran menampilkan tagihan dan transaksi, dashboard menampilkan transaksi menunggu.
     */
    public function test_enrollment_page_and_dashboard_show_payments(): void
    {
        $paid = $this->payment(PaymentStatus::Paid, 200000);
        $waiting = $this->payment(PaymentStatus::WaitingVerification, 75000, ['paid_on' => '2026-10-04']);

        $this->actingAs($this->admin)
            ->get(route('admin.enrollments.show', $this->enrollment))
            ->assertOk()
            ->assertSee('Tagihan &amp; Pembayaran', false)
            ->assertSee('Rp500.000')
            ->assertSee('Rp300.000')
            ->assertSee('Rp75.000')
            ->assertSee($paid->code)
            ->assertSee($waiting->code)
            ->assertSee('Sebagian')
            ->assertSee('id="btn-record-payment"', false);

        $this->actingAs($this->admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSeeInOrder(['Pembayaran Menunggu Verifikasi', 'Dewi Lestari', 'Rp75.000', '04 Okt 2026', 'Menunggu Verifikasi'])
            ->assertSee(route('admin.payments.show', $waiting), false);
    }

    /**
     * Test menu Pembayaran di sidebar aktif dan mengarah ke halaman pembayaran.
     */
    public function test_sidebar_links_to_payment_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.payments.index'));

        $this->assertMatchesRegularExpression(
            '/href="'.preg_quote(route('admin.payments.index'), '/').'"\s+class="admin-nav-item is-active"/',
            $response->getContent()
        );
    }
}
