<?php

namespace App\Models;

use App\Weekday;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Database\Factories\ScheduleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Jadwal belajar mingguan sebuah kelas.
 *
 * Guru pengajar mengikuti guru kelas (classes.teacher_id) dan tidak disimpan ulang di sini.
 * Jam disimpan dalam format "H:i:s" dan tanggal berlaku dalam format "Y-m-d" agar
 * perbandingan bentrok konsisten di semua database.
 */
#[Fillable(['classroom_id', 'day_of_week', 'start_time', 'end_time', 'starts_on', 'ends_on', 'room', 'is_active'])]
class Schedule extends Model
{
    /** @use HasFactory<ScheduleFactory> */
    use HasFactory;

    /**
     * Relasi yang menandakan jadwal sudah dipakai dan tidak boleh dihapus permanen.
     *
     * @var list<string>
     */
    protected const DEPENDENT_RELATIONS = ['studentAttendances', 'teacherAttendances'];

    /**
     * Nilai default atribut model, selaras dengan default kolom di database.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'is_active' => true,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'day_of_week' => Weekday::class,
            'is_active' => 'boolean',
        ];
    }

    /**
     * Kelas pemilik jadwal.
     *
     * @return BelongsTo<Classroom, $this>
     */
    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    /**
     * Catatan absensi murid pada sesi-sesi jadwal ini.
     *
     * @return HasMany<StudentAttendance, $this>
     */
    public function studentAttendances(): HasMany
    {
        return $this->hasMany(StudentAttendance::class, 'schedule_id');
    }

    /**
     * Catatan check-in/check-out guru pada sesi-sesi jadwal ini.
     *
     * @return HasMany<TeacherAttendance, $this>
     */
    public function teacherAttendances(): HasMany
    {
        return $this->hasMany(TeacherAttendance::class, 'schedule_id');
    }

    /**
     * Menentukan apakah jadwal aktif ini berlangsung pada tanggal tertentu (hari dan masa berlaku cocok).
     */
    public function occursOn(CarbonInterface $date): bool
    {
        $date = CarbonImmutable::instance($date)->startOfDay();

        return $this->is_active
            && $this->day_of_week === Weekday::fromDate($date)
            && ($this->starts_on === null || $date->greaterThanOrEqualTo($this->starts_on))
            && ($this->ends_on === null || $date->lessThanOrEqualTo($this->ends_on));
    }

    /**
     * Waktu mulai sesi jadwal pada tanggal tertentu (zona waktu aplikasi).
     */
    public function sessionStartsAt(CarbonInterface $date): CarbonImmutable
    {
        return CarbonImmutable::instance($date)->setTimeFromTimeString($this->start_time);
    }

    /**
     * Waktu selesai sesi jadwal pada tanggal tertentu (zona waktu aplikasi).
     */
    public function sessionEndsAt(CarbonInterface $date): CarbonImmutable
    {
        return CarbonImmutable::instance($date)->setTimeFromTimeString($this->end_time);
    }

    /**
     * Jam mulai, disimpan sebagai "H:i:s".
     *
     * @return Attribute<string, string>
     */
    protected function startTime(): Attribute
    {
        return Attribute::make(set: fn (?string $value): ?string => static::normalizeTime($value));
    }

    /**
     * Jam selesai, disimpan sebagai "H:i:s".
     *
     * @return Attribute<string, string>
     */
    protected function endTime(): Attribute
    {
        return Attribute::make(set: fn (?string $value): ?string => static::normalizeTime($value));
    }

    /**
     * Tanggal mulai berlaku, disimpan sebagai "Y-m-d".
     *
     * @return Attribute<CarbonImmutable|null, mixed>
     */
    protected function startsOn(): Attribute
    {
        return static::dateOnlyAttribute();
    }

    /**
     * Tanggal berakhir, disimpan sebagai "Y-m-d".
     *
     * @return Attribute<CarbonImmutable|null, mixed>
     */
    protected function endsOn(): Attribute
    {
        return static::dateOnlyAttribute();
    }

    /**
     * Rentang jam untuk ditampilkan, misalnya "08:00–10:00".
     */
    public function timeRange(): string
    {
        return substr($this->start_time, 0, 5).'–'.substr($this->end_time, 0, 5);
    }

    /**
     * Ringkasan jadwal, misalnya "Senin 08:00–10:00".
     */
    public function summary(): string
    {
        return $this->day_of_week->label().' '.$this->timeRange();
    }

    /**
     * Membatasi query pada jadwal aktif yang waktunya bertabrakan dengan rentang yang diberikan.
     *
     * Dua jadwal bertabrakan jika harinya sama, jamnya tumpang tindih (jadwal yang selesai tepat
     * saat jadwal lain dimulai tidak dihitung), dan masa berlakunya beririsan. Tanggal kosong
     * berarti berlaku tanpa batas pada sisi tersebut.
     *
     * @param  Builder<Schedule>  $query
     */
    #[Scope]
    protected function overlapping(Builder $query, Weekday $day, string $startTime, string $endTime, ?string $startsOn, ?string $endsOn): void
    {
        $query->where('is_active', true)
            ->where('day_of_week', $day->value)
            ->where('start_time', '<', static::normalizeTime($endTime))
            ->where('end_time', '>', static::normalizeTime($startTime))
            ->when($endsOn !== null, fn (Builder $query) => $query->where(
                fn (Builder $query) => $query->whereNull('starts_on')->orWhere('starts_on', '<=', $endsOn)
            ))
            ->when($startsOn !== null, fn (Builder $query) => $query->where(
                fn (Builder $query) => $query->whereNull('ends_on')->orWhere('ends_on', '>=', $startsOn)
            ));
    }

    /**
     * Mencari jadwal aktif lain yang bentrok untuk kelas atau guru kelas tersebut.
     *
     * @return array{type: 'classroom'|'teacher', schedule: Schedule}|null
     */
    public static function findConflict(
        Classroom $classroom,
        Weekday $day,
        string $startTime,
        string $endTime,
        ?string $startsOn,
        ?string $endsOn,
        ?int $ignoreScheduleId = null,
    ): ?array {
        $baseQuery = fn (): Builder => static::query()
            ->overlapping($day, $startTime, $endTime, $startsOn, $endsOn)
            ->when($ignoreScheduleId, fn (Builder $query, int $id) => $query->whereKeyNot($id))
            ->with(['classroom.teacher'])
            ->orderBy('start_time');

        $classroomConflict = $baseQuery()->where('classroom_id', $classroom->id)->first();
        if ($classroomConflict !== null) {
            return ['type' => 'classroom', 'schedule' => $classroomConflict];
        }

        if ($classroom->teacher_id === null) {
            return null;
        }

        $teacherConflict = $baseQuery()
            ->whereHas('classroom', fn (Builder $query) => $query->where('teacher_id', $classroom->teacher_id))
            ->first();

        return $teacherConflict === null ? null : ['type' => 'teacher', 'schedule' => $teacherConflict];
    }

    /**
     * Pesan bentrok yang menjelaskan jadwal mana yang bertabrakan.
     *
     * @param  array{type: 'classroom'|'teacher', schedule: Schedule}  $conflict
     */
    public static function conflictMessage(array $conflict): string
    {
        $other = $conflict['schedule'];
        $className = $other->classroom->name;

        if ($conflict['type'] === 'classroom') {
            return "Jadwal bentrok: kelas {$className} sudah memiliki jadwal {$other->summary()}.";
        }

        $teacherName = $other->classroom->teacher?->name ?? 'guru ini';

        return "Jadwal bentrok: {$teacherName} sudah mengajar kelas {$className} pada {$other->summary()}.";
    }

    /**
     * Mencari jadwal aktif kelas lain yang bentrok jika kelas jadwal ini diampu oleh guru tertentu.
     */
    public function conflictingScheduleForTeacher(int $teacherId): ?Schedule
    {
        return static::query()
            ->overlapping(
                $this->day_of_week,
                $this->start_time,
                $this->end_time,
                $this->starts_on?->format('Y-m-d'),
                $this->ends_on?->format('Y-m-d'),
            )
            ->where('classroom_id', '!=', $this->classroom_id)
            ->whereHas('classroom', fn (Builder $query) => $query->where('teacher_id', $teacherId))
            ->with('classroom')
            ->first();
    }

    /**
     * Waktu pertemuan berikutnya sejak waktu tertentu, atau null jika jadwal tidak aktif/sudah berakhir.
     */
    public function nextOccurrence(CarbonInterface $from): ?CarbonImmutable
    {
        if (! $this->is_active) {
            return null;
        }

        $from = CarbonImmutable::instance($from);
        $searchStart = $this->starts_on !== null && $this->starts_on->greaterThan($from->startOfDay())
            ? $this->starts_on
            : $from->startOfDay();

        $daysAhead = ($this->day_of_week->value - $searchStart->isoWeekday() + 7) % 7;
        $occurrence = $searchStart->addDays($daysAhead)->setTimeFromTimeString($this->start_time);

        if ($occurrence->lessThan($from)) {
            $occurrence = $occurrence->addWeek();
        }

        if ($this->ends_on !== null && $occurrence->startOfDay()->greaterThan($this->ends_on)) {
            return null;
        }

        return $occurrence;
    }

    /**
     * Menentukan apakah jadwal sudah memiliki data terkait (mis. absensi).
     */
    public function hasDependentRecords(): bool
    {
        foreach (static::DEPENDENT_RELATIONS as $relation) {
            if ($this->{$relation}()->exists()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Mengubah "08:00" atau "08:00:00" menjadi "08:00:00".
     */
    public static function normalizeTime(?string $time): ?string
    {
        if ($time === null || $time === '') {
            return null;
        }

        return strlen($time) === 5 ? $time.':00' : substr($time, 0, 8);
    }

    /**
     * Atribut tanggal yang disimpan tanpa jam ("Y-m-d").
     *
     * @return Attribute<CarbonImmutable|null, mixed>
     */
    private static function dateOnlyAttribute(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): ?CarbonImmutable => $value === null ? null : CarbonImmutable::parse($value)->startOfDay(),
            set: fn (mixed $value): ?string => blank($value) ? null : CarbonImmutable::parse($value)->format('Y-m-d'),
        );
    }
}
