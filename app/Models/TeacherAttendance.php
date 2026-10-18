<?php

namespace App\Models;

use App\Models\Concerns\HasAttendanceCorrection;
use App\TeacherAttendanceStatus;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Database\Factories\TeacherAttendanceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

/**
 * Catatan check-in/check-out guru pada satu sesi mengajar (jadwal + tanggal).
 *
 * Seluruh kolom diisi oleh sistem dengan waktu server; tidak ada atribut yang dapat diisi melalui
 * mass assignment. Guru disimpan pada catatan karena guru kelas dapat berganti di kemudian hari.
 */
class TeacherAttendance extends Model
{
    /** @use HasFactory<TeacherAttendanceFactory> */
    use HasAttendanceCorrection, HasFactory;

    /**
     * Check-in dibuka sejumlah menit ini sebelum jadwal dimulai.
     */
    public const CHECK_IN_OPENS_MINUTES_BEFORE = 30;

    /**
     * Check-in lebih dari sejumlah menit ini setelah jadwal dimulai dianggap terlambat.
     */
    public const LATE_AFTER_MINUTES = 15;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => TeacherAttendanceStatus::class,
            'previous_status' => TeacherAttendanceStatus::class,
            'checked_in_at' => 'datetime',
            'checked_out_at' => 'datetime',
            'corrected_at' => 'datetime',
        ];
    }

    /**
     * Jadwal (sesi) yang diajar.
     *
     * @return BelongsTo<Schedule, $this>
     */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class, 'schedule_id');
    }

    /**
     * Guru yang melakukan check-in, termasuk akun yang sudah dihapus agar riwayat tetap terbaca.
     *
     * @return BelongsTo<User, $this>
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id')->withTrashed();
    }

    /**
     * Durasi mengajar dalam menit, atau null jika belum check-out.
     */
    public function durationMinutes(): ?int
    {
        if ($this->checked_out_at === null) {
            return null;
        }

        return (int) $this->checked_in_at->diffInMinutes($this->checked_out_at);
    }

    /**
     * Durasi mengajar untuk ditampilkan, misalnya "1 jam 30 menit".
     */
    public function durationLabel(): string
    {
        $minutes = $this->durationMinutes();

        if ($minutes === null) {
            return '—';
        }

        $hours = intdiv($minutes, 60);
        $remainder = $minutes % 60;

        return trim(($hours > 0 ? "{$hours} jam " : '').($remainder > 0 || $hours === 0 ? "{$remainder} menit" : ''));
    }

    /**
     * Mencatat check-in guru untuk sesi jadwal hari ini menggunakan waktu server.
     *
     * Wajib dipanggil di dalam transaksi database. Aturan: jadwal milik kelas yang diampu guru tersebut,
     * jadwal dan kelas aktif, jadwal berlangsung hari ini, waktu berada di jendela check-in
     * (mulai {@see CHECK_IN_OPENS_MINUTES_BEFORE} menit sebelum jadwal sampai jadwal selesai), dan belum
     * pernah check-in untuk sesi yang sama.
     *
     * @throws ValidationException
     */
    public static function checkIn(User $teacher, Schedule $schedule, CarbonInterface $now): self
    {
        $now = CarbonImmutable::instance($now);
        $sessionDate = $now->startOfDay();
        $schedule->loadMissing('classroom');

        static::ensureTeachesSchedule($teacher, $schedule);

        if (! $schedule->classroom->is_active || ! $schedule->occursOn($sessionDate)) {
            throw ValidationException::withMessages([
                'schedule' => 'Jadwal ini tidak berlangsung hari ini sehingga tidak dapat di-check-in.',
            ]);
        }

        $startsAt = $schedule->sessionStartsAt($sessionDate);
        $endsAt = $schedule->sessionEndsAt($sessionDate);

        if ($now->lessThan($startsAt->subMinutes(self::CHECK_IN_OPENS_MINUTES_BEFORE))) {
            throw ValidationException::withMessages([
                'schedule' => 'Check-in baru dibuka '.self::CHECK_IN_OPENS_MINUTES_BEFORE.' menit sebelum jadwal dimulai (pukul '.$startsAt->subMinutes(self::CHECK_IN_OPENS_MINUTES_BEFORE)->format('H:i').').',
            ]);
        }

        if ($now->greaterThan($endsAt)) {
            throw ValidationException::withMessages([
                'schedule' => 'Jadwal sudah selesai pukul '.$endsAt->format('H:i').'. Hubungi admin jika perlu koreksi kehadiran.',
            ]);
        }

        Schedule::query()->whereKey($schedule->id)->lockForUpdate()->first();

        $alreadyCheckedIn = static::query()
            ->where('schedule_id', $schedule->id)
            ->where('session_date', $sessionDate->format('Y-m-d'))
            ->exists();

        if ($alreadyCheckedIn) {
            throw ValidationException::withMessages([
                'schedule' => 'Anda sudah check-in untuk sesi ini.',
            ]);
        }

        $attendance = new self;
        $attendance->schedule_id = $schedule->id;
        $attendance->session_date = $sessionDate;
        $attendance->teacher_id = $teacher->id;
        $attendance->checked_in_at = $now;
        $attendance->status = $now->greaterThan($startsAt->addMinutes(self::LATE_AFTER_MINUTES))
            ? TeacherAttendanceStatus::Late
            : TeacherAttendanceStatus::Present;
        $attendance->save();

        return $attendance;
    }

    /**
     * Mencatat check-out untuk sesi hari ini yang sebelumnya sudah di-check-in oleh guru yang sama.
     *
     * Tidak pernah membuat catatan baru. Wajib dipanggil di dalam transaksi database.
     *
     * @throws ValidationException
     */
    public static function checkOut(User $teacher, Schedule $schedule, CarbonInterface $now): self
    {
        $now = CarbonImmutable::instance($now);

        $attendance = static::query()
            ->where('schedule_id', $schedule->id)
            ->where('session_date', $now->format('Y-m-d'))
            ->where('teacher_id', $teacher->id)
            ->lockForUpdate()
            ->first();

        if ($attendance === null) {
            throw ValidationException::withMessages([
                'schedule' => 'Belum ada check-in untuk sesi ini. Lakukan check-in terlebih dahulu.',
            ]);
        }

        if ($attendance->checked_out_at !== null) {
            throw ValidationException::withMessages([
                'schedule' => 'Anda sudah check-out untuk sesi ini pada pukul '.$attendance->checked_out_at->format('H:i').'.',
            ]);
        }

        if ($now->lessThan($attendance->checked_in_at)) {
            throw ValidationException::withMessages([
                'schedule' => 'Waktu check-out tidak boleh lebih awal daripada check-in.',
            ]);
        }

        $attendance->checked_out_at = $now;
        $attendance->save();

        return $attendance;
    }

    /**
     * Memastikan guru adalah pengajar kelas pemilik jadwal.
     *
     * @throws ValidationException
     */
    private static function ensureTeachesSchedule(User $teacher, Schedule $schedule): void
    {
        if ($schedule->classroom->teacher_id !== $teacher->id) {
            throw ValidationException::withMessages([
                'schedule' => 'Anda hanya dapat check-in untuk jadwal kelas yang Anda ampu.',
            ]);
        }
    }
}
