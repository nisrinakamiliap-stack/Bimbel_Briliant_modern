<?php

namespace App\Models;

use App\EnrollmentStatus;
use App\LearningMode;
use Database\Factories\ClassroomFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * Kelas belajar dalam sebuah program bimbel, diampu oleh satu guru.
 *
 * Model diberi nama Classroom karena `Class` adalah kata kunci PHP; tabelnya tetap `classes`.
 */
#[Fillable(['program_id', 'teacher_id', 'name', 'code', 'capacity', 'learning_mode', 'description', 'is_active'])]
class Classroom extends Model
{
    /** @use HasFactory<ClassroomFactory> */
    use HasFactory;

    /**
     * Relasi yang menandakan kelas sudah dipakai dan tidak boleh dihapus permanen.
     *
     * Absensi terhubung melalui jadwal, sehingga sudah terlindungi oleh relasi `schedules`.
     *
     * @var list<string>
     */
    protected const DEPENDENT_RELATIONS = ['schedules', 'enrollments', 'materials', 'assignments'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'classes';

    /**
     * Nilai default atribut model, selaras dengan default kolom di database.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'learning_mode' => 'offline',
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
            'capacity' => 'integer',
            'learning_mode' => LearningMode::class,
            'is_active' => 'boolean',
        ];
    }

    /**
     * Program bimbel tempat kelas ini berada.
     *
     * @return BelongsTo<Program, $this>
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * Guru pengajar kelas.
     *
     * Termasuk akun guru yang sudah dihapus (soft delete) agar riwayat pengampu tetap terlihat.
     *
     * @return BelongsTo<User, $this>
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id')->withTrashed();
    }

    /**
     * Jadwal belajar mingguan kelas ini.
     *
     * @return HasMany<Schedule, $this>
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'classroom_id');
    }

    /**
     * Pendaftaran murid yang ditempatkan di kelas ini.
     *
     * @return HasMany<Enrollment, $this>
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'classroom_id');
    }

    /**
     * Murid peserta kelas (pendaftaran berstatus Diterima), tanpa duplikasi.
     *
     * @return Collection<int, User>
     */
    public function approvedStudents(): Collection
    {
        return $this->enrollments()
            ->where('status', EnrollmentStatus::Approved->value)
            ->with('student')
            ->get()
            ->pluck('student')
            ->filter(fn (?User $student) => $student !== null && ! $student->trashed())
            ->unique('id')
            ->values();
    }

    /**
     * Materi pembelajaran kelas ini.
     *
     * @return HasMany<Material, $this>
     */
    public function materials(): HasMany
    {
        return $this->hasMany(Material::class, 'classroom_id');
    }

    /**
     * Tugas kelas ini.
     *
     * @return HasMany<Assignment, $this>
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class, 'classroom_id');
    }

    /**
     * Menentukan apakah kelas sudah memiliki data terkait (jadwal, pendaftaran, absensi).
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
}
