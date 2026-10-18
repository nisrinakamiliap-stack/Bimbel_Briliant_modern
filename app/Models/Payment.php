<?php

namespace App\Models;

use App\PaymentMethod;
use App\PaymentStatus;
use Carbon\CarbonImmutable;
use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Transaksi pembayaran manual untuk sebuah pendaftaran.
 *
 * Kode transaksi, status, bukti, dan data verifikasi diatur oleh sistem sehingga tidak dapat
 * diisi melalui mass assignment. Perhitungan nominal memakai satuan sen (integer) agar bebas
 * dari kesalahan pembulatan bilangan pecahan.
 */
#[Fillable(['amount', 'method', 'paid_on', 'notes'])]
#[Hidden(['proof_path'])]
class Payment extends Model
{
    /** @use HasFactory<PaymentFactory> */
    use HasFactory;

    /**
     * Nilai default atribut model, selaras dengan default kolom di database.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'waiting_verification',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'method' => PaymentMethod::class,
            'status' => PaymentStatus::class,
            'processed_at' => 'datetime',
        ];
    }

    /**
     * Membuat kode transaksi unik saat transaksi pertama kali disimpan.
     */
    protected static function booted(): void
    {
        static::creating(function (Payment $payment): void {
            if (blank($payment->code)) {
                $payment->code = static::generateCode();
            }
        });
    }

    /**
     * Pendaftaran yang dibayar.
     *
     * @return BelongsTo<Enrollment, $this>
     */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }

    /**
     * Admin yang mencatat transaksi.
     *
     * @return BelongsTo<User, $this>
     */
    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by')->withTrashed();
    }

    /**
     * Admin yang memverifikasi atau menolak transaksi.
     *
     * @return BelongsTo<User, $this>
     */
    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by')->withTrashed();
    }

    /**
     * Tanggal pembayaran, disimpan sebagai "Y-m-d" agar filter periode konsisten di semua database.
     *
     * @return Attribute<CarbonImmutable|null, mixed>
     */
    protected function paidOn(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): ?CarbonImmutable => $value === null ? null : CarbonImmutable::parse($value)->startOfDay(),
            set: fn (mixed $value): ?string => blank($value) ? null : CarbonImmutable::parse($value)->format('Y-m-d'),
        );
    }

    /**
     * Menentukan apakah transaksi memiliki file bukti pembayaran.
     */
    public function hasProof(): bool
    {
        return $this->proof_path !== null;
    }

    /**
     * Menentukan apakah bukti pembayaran berupa gambar (dapat ditampilkan langsung).
     */
    public function proofIsImage(): bool
    {
        return $this->hasProof() && in_array(strtolower(pathinfo($this->proof_path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png'], true);
    }

    /**
     * Memverifikasi (Berhasil) atau menolak transaksi yang masih menunggu verifikasi.
     *
     * Wajib dipanggil di dalam transaksi database. Baris pembayaran dan pendaftaran dikunci lalu status
     * diperiksa ulang, sehingga transaksi yang sudah diproses tidak dapat diproses dua kali. Saat
     * diverifikasi, nominal diperiksa ulang terhadap sisa tagihan agar total berhasil tidak melebihi tagihan.
     *
     * @throws ValidationException
     */
    public function process(PaymentStatus $target, User $admin, ?string $reason): void
    {
        $fresh = static::query()->whereKey($this->id)->lockForUpdate()->firstOrFail();

        if (! $fresh->status->isProcessable()) {
            throw ValidationException::withMessages([
                'status' => "Transaksi {$fresh->code} sudah diproses dengan status {$fresh->status->label()} dan tidak dapat diproses ulang.",
            ]);
        }

        if ($target === PaymentStatus::Paid) {
            $enrollment = Enrollment::query()->whereKey($fresh->enrollment_id)->lockForUpdate()->firstOrFail();
            $remainingCents = $enrollment->remainingCents();

            if (static::toCents($fresh->amount) > $remainingCents) {
                throw ValidationException::withMessages([
                    'status' => 'Nominal transaksi '.static::rupiah($fresh->amount).' melebihi sisa tagihan '.static::rupiah($remainingCents / 100).'. Tolak transaksi ini dan catat ulang dengan nominal yang benar.',
                ]);
            }
        }

        $this->status = $target;
        $this->processed_by = $admin->id;
        $this->processed_at = now();
        $this->rejection_reason = $target === PaymentStatus::Rejected ? $reason : null;
        $this->notes = $target === PaymentStatus::Paid && filled($reason)
            ? trim(($this->notes ? $this->notes."\n" : '').'Catatan verifikasi: '.$reason)
            : $this->notes;
        $this->save();
    }

    /**
     * Mengubah nominal rupiah (string/float) menjadi sen (integer).
     */
    public static function toCents(string|float|int|null $amount): int
    {
        return (int) round(((float) ($amount ?? 0)) * 100);
    }

    /**
     * Mengubah input nominal berformat rupiah ("Rp 350.000", "350.000,50") menjadi angka ("350000", "350000.50").
     *
     * Input yang tidak dikenali dikembalikan apa adanya agar ditolak oleh aturan validasi.
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
     * Format rupiah, misalnya "Rp150.000" atau "Rp150.000,50".
     */
    public static function rupiah(string|float|int|null $amount): string
    {
        $amount = (float) ($amount ?? 0);
        $decimals = fmod($amount, 1.0) === 0.0 ? 0 : 2;

        return 'Rp'.number_format($amount, $decimals, ',', '.');
    }

    /**
     * Kode transaksi unik, misalnya "PAY-20261018-7KQ2XM".
     */
    protected static function generateCode(): string
    {
        do {
            $code = 'PAY-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (static::query()->where('code', $code)->exists());

        return $code;
    }
}
