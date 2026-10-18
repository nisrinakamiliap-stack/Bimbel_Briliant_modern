<?php

namespace App\Models;

use App\AttendanceStatus;
use App\EnrollmentStatus;
use App\Models\Concerns\HasAttendanceCorrection;
use Carbon\CarbonInterface;
use Database\Factories\StudentAttendanceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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
     * Admin atau guru yang mencatat kehadiran.
     *
     * @return BelongsTo<User, $this>
     */
    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by')->withTrashed();
    }

    /**
     * Peserta sesi: murid dengan pendaftaran Diterima di kelas jadwal, terdaftar sebelum atau pada tanggal sesi.
     *
     * @return Collection<int, Enrollment>
     */
    public static function rosterFor(Schedule $schedule, CarbonInterface $date): Collection
    {
        return Enrollment::query()
            ->where('classroom_id', $schedule->classroom_id)
            ->where('status', EnrollmentStatus::Approved->value)
            ->where('enrolled_on', '<=', $date->format('Y-m-d'))
            ->with('student')
            ->get()
            ->sortBy(fn (Enrollment $enrollment) => $enrollment->student->name)
            ->values();
    }

    /**
     * Mencatat absensi satu sesi untuk murid peserta yang belum memiliki catatan.
     *
     * Catatan yang sudah ada tidak diubah (gunakan koreksi), sehingga pengiriman ulang formulir tidak
     * membuat atau menimpa data. Constraint unik di database menjadi pengaman terakhir.
     *
     * @param  array<int|string, array{status: string, notes?: string|null}>  $submitted  Status per ID pendaftaran.
     * @return array{0: int, 1: int} Jumlah catatan baru dan jumlah murid yang dilewati.
     *
     * @throws ValidationException
     */
    public static function recordSession(Schedule $schedule, CarbonInterface $date, array $submitted, User $recorder): array
    {
        $rosterByEnrollment = static::rosterFor($schedule, $date)->keyBy('id');
        $unknownEnrollmentIds = array_diff(array_map('intval', array_keys($submitted)), $rosterByEnrollment->keys()->all());

        if ($unknownEnrollmentIds !== []) {
            throw ValidationException::withMessages([
                'attendances' => 'Terdapat murid yang bukan peserta aktif kelas ini pada tanggal tersebut.',
            ]);
        }

        return DB::transaction(function () use ($schedule, $date, $submitted, $rosterByEnrollment, $recorder): array {
            Schedule::query()->whereKey($schedule->id)->lockForUpdate()->first();

            $alreadyRecordedStudentIds = static::query()
                ->where('schedule_id', $schedule->id)
                ->where('session_date', $date->format('Y-m-d'))
                ->pluck('student_id')
                ->all();

            $created = 0;
            $skipped = 0;

            foreach ($submitted as $enrollmentId => $entry) {
                /** @var Enrollment $enrollment */
                $enrollment = $rosterByEnrollment->get((int) $enrollmentId);

                if (in_array($enrollment->student_id, $alreadyRecordedStudentIds, true)) {
                    $skipped++;

                    continue;
                }

                $attendance = new static([
                    'status' => $entry['status'],
                    'notes' => filled($entry['notes'] ?? null) ? trim($entry['notes']) : null,
                ]);
                $attendance->schedule_id = $schedule->id;
                $attendance->session_date = $date;
                $attendance->student_id = $enrollment->student_id;
                $attendance->enrollment_id = $enrollment->id;
                $attendance->recorded_by = $recorder->id;
                $attendance->save();

                $alreadyRecordedStudentIds[] = $enrollment->student_id;
                $created++;
            }

            return [$created, $skipped];
        });
    }
}
