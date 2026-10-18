<?php

namespace App\Models;

use App\HonorariumStatus;
use App\PaymentMethod;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

/**
 * Honorarium guru untuk satu periode (bulan + tahun).
 *
 * Mendukung perhitungan tarif per sesi, penyesuaian (tunjangan/bonus), potongan,
 * serta pencatatan pembayaran penuh maupun bertahap (sebagian) dengan bukti pembayaran.
 */
#[Fillable(['session_count', 'rate_per_session', 'adjustments', 'deductions', 'notes'])]
class TeacherHonorarium extends Model
{
    /**
     * Nama tabel di database.
     *
     * @var string
     */
    protected $table = 'teacher_honorariums';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status'           => HonorariumStatus::class,
            'payment_method'   => PaymentMethod::class,
            'approved_at'      => 'datetime',
            'paid_at'          => 'datetime',
            'rate_per_session' => 'decimal:2',
            'base_amount'      => 'decimal:2',
            'adjustments'      => 'decimal:2',
            'deductions'       => 'decimal:2',
            'amount'           => 'decimal:2',
            'paid_amount'      => 'decimal:2',
        ];
    }

    /**
     * Tanggal pembayaran terakhir, disimpan sebagai "Y-m-d".
     *
     * @return Attribute<CarbonImmutable|null, mixed>
     */
    protected function paidOn(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): ?CarbonImmutable => $value === null
                ? null
                : CarbonImmutable::parse($value)->startOfDay(),
            set: fn (mixed $value): ?string => blank($value)
                ? null
                : CarbonImmutable::parse($value)->format('Y-m-d'),
        );
    }

    /**
     * Nama bulan periode untuk ditampilkan, misalnya "Oktober 2026".
     */
    public function periodLabel(): string
    {
        return CarbonImmutable::create($this->period_year, $this->period_month, 1)
            ->translatedFormat('F Y');
    }

    /**
     * Format rupiah, misalnya "Rp150.000".
     */
    public static function rupiah(string|float|int|null $amount): string
    {
        $amount   = (float) ($amount ?? 0);
        $decimals = fmod($amount, 1.0) === 0.0 ? 0 : 2;

        return 'Rp'.number_format($amount, $decimals, ',', '.');
    }

    /**
     * Mengubah input nominal berformat rupiah ("Rp 150.000", "150.000,50") menjadi angka.
     */
    public static function normalizeRupiahInput(mixed $amount): mixed
    {
        if (! is_string($amount)) {
            return $amount;
        }

        $amount = str_replace(' ', '', (string) preg_replace('/^rp\.?\s*/i', '', trim($amount)));

        if (preg_match('/^\d{1,3}(\.\d{3})+(,\d{1,2})?$/', $amount) === 1) {
            return str_replace(['.', ','], ['', '.'], $amount);
        }

        if (preg_match('/^\d+,\d{1,2}$/', $amount) === 1) {
            return str_replace(',', '.', $amount);
        }

        return $amount;
    }

    /**
     * Hitung ulang nominal honor pokok dan total neto.
     */
    public function recalculateAmount(): void
    {
        $sessions    = (int) $this->session_count;
        $rate        = (float) $this->rate_per_session;
        $adjustments = (float) ($this->adjustments ?? 0);
        $deductions  = (float) ($this->deductions ?? 0);

        $this->base_amount = $sessions * $rate;
        $this->amount      = max(0, $this->base_amount + $adjustments - $deductions);
    }

    /**
     * Sisa honorarium yang belum dibayar.
     */
    public function remainingAmount(): float
    {
        $net  = (float) $this->amount;
        $paid = (float) $this->paid_amount;

        return max(0.0, round($net - $paid, 2));
    }

    /**
     * Guru penerima honorarium, termasuk akun yang sudah dihapus.
     *
     * @return BelongsTo<User, $this>
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id')->withTrashed();
    }

    /**
     * Admin yang membuat catatan honorarium.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    /**
     * Admin yang menyetujui honorarium.
     *
     * @return BelongsTo<User, $this>
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by')->withTrashed();
    }

    /**
     * Admin yang mencatat pembayaran terakhir.
     *
     * @return BelongsTo<User, $this>
     */
    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by')->withTrashed();
    }

    /**
     * Riwayat transaksi pembayaran bertahap maupun penuh.
     *
     * @return HasMany<TeacherHonorariumPayment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(TeacherHonorariumPayment::class, 'teacher_honorarium_id')->latest('paid_on')->latest('id');
    }

    /**
     * Menyetujui honorarium yang masih berstatus Draft.
     *
     * @throws ValidationException
     */
    public function approve(User $admin): void
    {
        $fresh = static::query()->whereKey($this->id)->lockForUpdate()->firstOrFail();

        if (! $fresh->status->isApprovable()) {
            throw ValidationException::withMessages([
                'status' => "Honorarium ini sudah berstatus {$fresh->status->label()} dan tidak dapat disetujui.",
            ]);
        }

        $this->status      = HonorariumStatus::Approved;
        $this->approved_by = $admin->id;
        $this->approved_at = now();
        $this->save();
    }

    /**
     * Mencatat transaksi pembayaran (sebagian atau lunas).
     *
     * @throws ValidationException
     */
    public function recordPayment(
        User $admin,
        float $amount,
        PaymentMethod|string $method,
        string $paidOn,
        ?string $referenceNumber = null,
        ?string $proofPath = null,
        ?string $proofOriginalName = null,
        ?string $notes = null,
    ): TeacherHonorariumPayment {
        $fresh = static::query()->whereKey($this->id)->lockForUpdate()->firstOrFail();

        if (! $fresh->status->isPayable()) {
            throw ValidationException::withMessages([
                'status' => "Honorarium harus berstatus Disetujui atau Dibayar Sebagian sebelum dapat dicatat pembayarannya (status saat ini: {$fresh->status->label()}).",
            ]);
        }

        $remaining = $fresh->remainingAmount();

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'Nominal pembayaran harus lebih besar dari Rp0.',
            ]);
        }

        if ($amount > $remaining + 0.01) {
            throw ValidationException::withMessages([
                'amount' => 'Nominal pembayaran ('.static::rupiah($amount).') melebihi sisa honor yang belum dibayar ('.static::rupiah($remaining).').',
            ]);
        }

        $paymentMethod = $method instanceof PaymentMethod ? $method : PaymentMethod::from($method);

        $payment = new TeacherHonorariumPayment([
            'teacher_honorarium_id' => $fresh->id,
            'amount'                => $amount,
            'payment_method'        => $paymentMethod,
            'paid_on'               => $paidOn,
            'reference_number'      => $referenceNumber,
            'proof_path'            => $proofPath,
            'proof_original_name'   => $proofOriginalName,
            'notes'                 => $notes,
            'created_by'            => $admin->id,
        ]);
        $payment->save();

        $fresh->paid_amount    = (float) $fresh->payments()->sum('amount');
        $fresh->paid_on        = $paidOn;
        $fresh->payment_method = $paymentMethod;
        $fresh->paid_by        = $admin->id;
        $fresh->paid_at        = now();

        if (filled($notes) && blank($fresh->notes)) {
            $fresh->notes = $notes;
        }

        if ($fresh->remainingAmount() <= 0.0) {
            $fresh->status = HonorariumStatus::Paid;
        } else {
            $fresh->status = HonorariumStatus::PartiallyPaid;
        }

        $fresh->save();

        $this->refresh();

        return $payment;
    }

    /**
     * Menandai honorarium sebagai Dibayar penuh (kompatibilitas).
     *
     * @throws ValidationException
     */
    public function markPaid(User $admin, string $method, string $paidOn, ?string $notes): void
    {
        $remaining = $this->remainingAmount();
        $payAmount = $remaining > 0 ? $remaining : (float) $this->amount;

        $this->recordPayment(
            admin: $admin,
            amount: $payAmount,
            method: $method,
            paidOn: $paidOn,
            notes: $notes,
        );
    }
}
