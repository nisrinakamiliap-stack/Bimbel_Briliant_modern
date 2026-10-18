<?php

namespace App\Models;

use App\EnrollmentStatus;
use App\PaymentStatus;
use Carbon\CarbonImmutable;
use Database\Factories\EnrollmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

/**
 * Pendaftaran murid ke program bimbel, dengan penempatan kelas yang bersifat opsional.
 *
 * `student_id` hanya diisi sekali saat pendaftaran dibuat dan `status` hanya berubah melalui
 * {@see transitionTo()}, sehingga keduanya tidak dapat diisi melalui mass assignment.
 */
#[Fillable(['program_id', 'classroom_id', 'bill_amount', 'enrolled_on', 'admin_notes'])]
class Enrollment extends Model
{
    /** @use HasFactory<EnrollmentFactory> */
    use HasFactory;

    /**
     * Nilai default atribut model, selaras dengan default kolom di database.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'pending',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => EnrollmentStatus::class,
            'status_changed_at' => 'datetime',
            'bill_amount' => 'decimal:2',
        ];
    }

    /**
     * Transaksi pembayaran untuk pendaftaran ini.
     *
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'enrollment_id');
    }

    /**
     * Total pembayaran berhasil dalam sen. Memakai hasil `withSum(... as paid_total)` jika sudah dimuat.
     */
    public function paidCents(): int
    {
        $paidTotal = array_key_exists('paid_total', $this->attributes)
            ? $this->attributes['paid_total']
            : $this->payments()->where('status', PaymentStatus::Paid->value)->sum('amount');

        return Payment::toCents($paidTotal);
    }

    /**
     * Total transaksi yang masih menunggu verifikasi dalam sen.
     */
    public function waitingCents(): int
    {
        $waitingTotal = array_key_exists('waiting_total', $this->attributes)
            ? $this->attributes['waiting_total']
            : $this->payments()->where('status', PaymentStatus::WaitingVerification->value)->sum('amount');

        return Payment::toCents($waitingTotal);
    }

    /**
     * Sisa tagihan (tagihan dikurangi pembayaran berhasil) dalam sen; tidak pernah negatif.
     */
    public function remainingCents(): int
    {
        return max(0, Payment::toCents($this->bill_amount) - $this->paidCents());
    }

    /**
     * Batas nominal untuk transaksi baru: sisa tagihan dikurangi transaksi yang masih menunggu verifikasi.
     */
    public function payableCents(): int
    {
        return max(0, $this->remainingCents() - $this->waitingCents());
    }

    /**
     * Status tagihan: "Lunas", "Sebagian", atau "Belum Dibayar".
     *
     * Hanya pembayaran berhasil yang diperhitungkan; transaksi yang menunggu verifikasi tidak dihitung.
     */
    public function billingStatusLabel(): string
    {
        return match (true) {
            $this->remainingCents() === 0 => 'Lunas',
            $this->paidCents() > 0 => 'Sebagian',
            default => 'Belum Dibayar',
        };
    }

    /**
     * Kelas CSS badge status tagihan.
     */
    public function billingStatusBadgeClass(): string
    {
        return match ($this->billingStatusLabel()) {
            'Lunas' => 'is-success',
            'Sebagian' => 'is-pending',
            default => 'is-neutral',
        };
    }

    /**
     * Menentukan apakah pendaftaran sudah memiliki transaksi yang tidak ditolak.
     */
    public function hasRecordedPayments(): bool
    {
        return $this->payments()->where('status', '!=', PaymentStatus::Rejected->value)->exists();
    }

    /**
     * Murid yang mendaftar, termasuk akun yang sudah dihapus agar riwayat tetap terbaca.
     *
     * @return BelongsTo<User, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id')->withTrashed();
    }

    /**
     * Program yang dipilih.
     *
     * @return BelongsTo<Program, $this>
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    /**
     * Kelas penempatan, jika sudah ditentukan.
     *
     * @return BelongsTo<Classroom, $this>
     */
    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'classroom_id');
    }

    /**
     * Tanggal pendaftaran, disimpan sebagai "Y-m-d" agar filter tanggal konsisten di semua database.
     *
     * @return Attribute<CarbonImmutable|null, mixed>
     */
    protected function enrolledOn(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): ?CarbonImmutable => $value === null ? null : CarbonImmutable::parse($value)->startOfDay(),
            set: fn (mixed $value): ?string => blank($value) ? null : CarbonImmutable::parse($value)->format('Y-m-d'),
        );
    }

    /**
     * Membatasi query pada pendaftaran aktif (Menunggu Verifikasi atau Diterima).
     *
     * @param  Builder<Enrollment>  $query
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->whereIn('status', array_map(fn (EnrollmentStatus $status) => $status->value, EnrollmentStatus::activeStatuses()));
    }

    /**
     * Field yang masih boleh diubah berdasarkan status pendaftaran.
     *
     * Program hanya dapat diganti selama menunggu verifikasi dan belum ada transaksi pembayaran;
     * kelas dan total tagihan dapat diatur selama pendaftaran aktif; pendaftaran yang ditolak/dibatalkan
     * hanya dapat diubah catatannya.
     *
     * @return list<string>
     */
    public function editableFields(): array
    {
        $fields = match ($this->status) {
            EnrollmentStatus::Pending => ['program_id', 'classroom_id', 'bill_amount', 'enrolled_on', 'admin_notes'],
            EnrollmentStatus::Approved => ['classroom_id', 'bill_amount', 'enrolled_on', 'admin_notes'],
            EnrollmentStatus::Rejected, EnrollmentStatus::Cancelled => ['admin_notes'],
        };

        if (in_array('program_id', $fields, true) && $this->exists && $this->hasRecordedPayments()) {
            $fields = array_values(array_diff($fields, ['program_id']));
        }

        return $fields;
    }

    /**
     * Memastikan murid belum memiliki pendaftaran aktif di program yang sama dan kelas tujuan belum penuh.
     *
     * Wajib dipanggil di dalam transaksi database: baris murid dan kelas dikunci (SELECT ... FOR UPDATE)
     * sehingga dua pendaftaran yang diproses bersamaan tidak dapat melampaui kapasitas atau menggandakan pendaftaran.
     *
     * @throws ValidationException
     */
    public static function ensureCanEnroll(int $studentId, int $programId, ?int $classroomId, ?int $ignoreEnrollmentId = null): void
    {
        User::withTrashed()->whereKey($studentId)->lockForUpdate()->first();

        $hasActiveEnrollment = static::query()
            ->active()
            ->where('student_id', $studentId)
            ->where('program_id', $programId)
            ->when($ignoreEnrollmentId, fn (Builder $query, int $id) => $query->whereKeyNot($id))
            ->exists();

        if ($hasActiveEnrollment) {
            throw ValidationException::withMessages([
                'student_id' => 'Murid ini sudah memiliki pendaftaran aktif (menunggu verifikasi atau diterima) di program yang sama.',
            ]);
        }

        if ($classroomId === null) {
            return;
        }

        $classroom = Classroom::query()->whereKey($classroomId)->lockForUpdate()->firstOrFail();

        $occupiedSeats = static::query()
            ->active()
            ->where('classroom_id', $classroomId)
            ->where('student_id', '!=', $studentId)
            ->when($ignoreEnrollmentId, fn (Builder $query, int $id) => $query->whereKeyNot($id))
            ->distinct()
            ->count('student_id');

        if ($occupiedSeats >= $classroom->capacity) {
            throw ValidationException::withMessages([
                'classroom_id' => "Kelas {$classroom->name} sudah penuh ({$occupiedSeats}/{$classroom->capacity} murid). Pilih kelas lain atau kosongkan kelas terlebih dahulu.",
            ]);
        }
    }

    /**
     * Mengubah status pendaftaran sesuai alur yang diizinkan.
     *
     * Membuka kembali pendaftaran (kembali ke Menunggu Verifikasi) memeriksa ulang pendaftaran ganda
     * dan kapasitas kelas. Wajib dipanggil di dalam transaksi database.
     *
     * @throws ValidationException
     */
    public function transitionTo(EnrollmentStatus $target): void
    {
        if (! $this->status->canTransitionTo($target)) {
            throw ValidationException::withMessages([
                'status' => "Status tidak dapat diubah dari {$this->status->label()} menjadi {$target->label()}.",
            ]);
        }

        if ($target->isActive() && ! $this->status->isActive()) {
            static::ensureCanEnroll($this->student_id, $this->program_id, $this->classroom_id, $this->id);
        }

        $this->status = $target;
        $this->status_changed_at = now();
        $this->save();
    }
}
