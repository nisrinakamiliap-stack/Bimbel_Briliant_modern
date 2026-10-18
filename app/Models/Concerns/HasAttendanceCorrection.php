<?php

namespace App\Models\Concerns;

use App\Models\User;
use BackedEnum;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

/**
 * Koreksi status absensi oleh admin yang tetap dapat ditelusuri.
 *
 * Status sebelumnya, admin yang mengoreksi, waktu, dan alasan koreksi disimpan pada catatan.
 * Model pemakai wajib memiliki kolom `status`, `previous_status`, `corrected_by`, `corrected_at`,
 * `correction_reason`, dan `session_date`.
 */
trait HasAttendanceCorrection
{
    /**
     * Admin yang terakhir mengoreksi catatan.
     *
     * @return BelongsTo<User, $this>
     */
    public function corrector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'corrected_by')->withTrashed();
    }

    /**
     * Menentukan apakah catatan pernah dikoreksi.
     */
    public function wasCorrected(): bool
    {
        return $this->corrected_at !== null;
    }

    /**
     * Mengoreksi status kehadiran dengan alasan.
     *
     * @throws ValidationException
     */
    public function correctStatus(BackedEnum $status, string $reason, User $admin): void
    {
        if ($status === $this->status) {
            throw ValidationException::withMessages([
                'status' => 'Status baru sama dengan status saat ini. Pilih status yang berbeda untuk melakukan koreksi.',
            ]);
        }

        $this->previous_status = $this->status;
        $this->status = $status;
        $this->corrected_by = $admin->id;
        $this->corrected_at = now();
        $this->correction_reason = $reason;
        $this->save();
    }

    /**
     * Tanggal sesi, disimpan sebagai "Y-m-d" agar filter tanggal konsisten di semua database.
     *
     * @return Attribute<CarbonImmutable|null, mixed>
     */
    protected function sessionDate(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): ?CarbonImmutable => $value === null ? null : CarbonImmutable::parse($value)->startOfDay(),
            set: fn (mixed $value): ?string => blank($value) ? null : CarbonImmutable::parse($value)->format('Y-m-d'),
        );
    }
}
