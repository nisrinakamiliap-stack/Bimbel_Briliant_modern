<?php

namespace App\Models;

use App\AttendanceStatus;
use App\Models\Concerns\HasAttendanceCorrection;
use Database\Factories\StudentAttendanceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Catatan kehadiran murid pada satu sesi belajar (jadwal + tanggal).
 *
 * Relasi jadwal, murid, pendaftaran, dan pencatat diisi oleh sistem sehingga tidak dapat
 * diisi melalui mass assignment. Perubahan status setelah tercatat dilakukan lewat koreksi.
 */
#[Fillable(['status', 'notes'])]
class StudentAttendance extends Model
{
    /** @use HasFactory<StudentAttendanceFactory> */
    use HasAttendanceCorrection, HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => AttendanceStatus::class,
            'previous_status' => AttendanceStatus::class,
            'corrected_at' => 'datetime',
        ];
    }

    /**
     * Jadwal (sesi) yang dihadiri.
     *
     * @return BelongsTo<Schedule, $this>
     */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class, 'schedule_id');
    }

    /**
     * Murid pemilik catatan, termasuk akun yang sudah dihapus agar riwayat tetap terbaca.
     *
     * @return BelongsTo<User, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id')->withTrashed();
    }

    /**
     * Pendaftaran yang menjadi dasar keikutsertaan murid di kelas.
     *
     * @return BelongsTo<Enrollment, $this>
     */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }

    /**
     * Admin yang mencatat kehadiran.
     *
     * @return BelongsTo<User, $this>
     */
    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by')->withTrashed();
    }
}
