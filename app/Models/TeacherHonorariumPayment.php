<?php

namespace App\Models;

use App\PaymentMethod;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Transaksi pembayaran honorarium guru.
 *
 * Disimpan setiap kali admin mencatat pembayaran (penuh atau sebagian)
 * dengan bukti transfer dan nomor referensi.
 */
#[Fillable([
    'teacher_honorarium_id',
    'amount',
    'payment_method',
    'paid_on',
    'reference_number',
    'proof_path',
    'proof_original_name',
    'notes',
    'created_by',
])]
class TeacherHonorariumPayment extends Model
{
    /**
     * Nama tabel di database.
     *
     * @var string
     */
    protected $table = 'teacher_honorarium_payments';

    /**
     * Disk penyimpanan berkas bukti pembayaran honorarium.
     */
    public const PROOF_DISK = 'local';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_method' => PaymentMethod::class,
        ];
    }

    /**
     * Tanggal pembayaran, disimpan sebagai "Y-m-d".
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
     * Menentukan apakah transaksi memiliki file bukti pembayaran.
     */
    public function hasProof(): bool
    {
        return filled($this->proof_path);
    }

    /**
     * Menentukan apakah bukti pembayaran berupa gambar.
     */
    public function proofIsImage(): bool
    {
        return $this->hasProof() && in_array(
            strtolower(pathinfo((string) $this->proof_path, PATHINFO_EXTENSION)),
            ['jpg', 'jpeg', 'png', 'webp'],
            true
        );
    }

    /**
     * Format rupiah.
     */
    public static function rupiah(string|float|int|null $amount): string
    {
        return TeacherHonorarium::rupiah($amount);
    }

    /**
     * Honorarium induk.
     *
     * @return BelongsTo<TeacherHonorarium, $this>
     */
    public function honorarium(): BelongsTo
    {
        return $this->belongsTo(TeacherHonorarium::class, 'teacher_honorarium_id');
    }

    /**
     * Admin yang mencatat pembayaran.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }
}
