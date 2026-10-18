<?php

namespace App\Models;

use App\LearningContentStatus;
use Carbon\CarbonInterface;
use Database\Factories\AssignmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Tugas untuk satu kelas, dengan tenggat, skala nilai, dan aturan pengumpulan.
 *
 * Penulis dan lampiran diatur oleh sistem sehingga tidak dapat diisi melalui mass assignment.
 */
#[Fillable([
    'classroom_id', 'material_id', 'title', 'instructions', 'due_at', 'max_score',
    'allow_late_submission', 'allow_resubmission', 'status',
])]
#[Hidden(['attachment_path'])]
class Assignment extends Model
{
    /** @use HasFactory<AssignmentFactory> */
    use HasFactory;

    /**
     * Nilai default atribut model, selaras dengan default kolom di database.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'draft',
        'max_score' => 100,
        'allow_late_submission' => false,
        'allow_resubmission' => true,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => LearningContentStatus::class,
            'due_at' => 'datetime',
            'published_at' => 'datetime',
            'max_score' => 'integer',
            'allow_late_submission' => 'boolean',
            'allow_resubmission' => 'boolean',
        ];
    }

    /**
     * Mencatat waktu publikasi pertama kali saat tugas diterbitkan.
     */
    protected static function booted(): void
    {
        static::saving(function (Assignment $assignment): void {
            if ($assignment->status === LearningContentStatus::Published && $assignment->published_at === null) {
                $assignment->published_at = now();
            }
        });
    }

    /**
     * Kelas tujuan tugas.
     *
     * @return BelongsTo<Classroom, $this>
     */
    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'classroom_id');
    }

    /**
     * Materi terkait (opsional).
     *
     * @return BelongsTo<Material, $this>
     */
    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'material_id');
    }

    /**
     * Pengguna (guru/admin) yang membuat tugas.
     *
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id')->withTrashed();
    }

    /**
     * Pengumpulan tugas oleh murid.
     *
     * @return HasMany<Submission, $this>
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class, 'assignment_id');
    }

    /**
     * Membatasi query pada tugas yang sudah terbit.
     *
     * @param  Builder<Assignment>  $query
     */
    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where('status', LearningContentStatus::Published->value);
    }

    /**
     * Menentukan apakah tenggat sudah lewat pada waktu tertentu.
     */
    public function isPastDue(CarbonInterface $at): bool
    {
        return $at->greaterThan($this->due_at);
    }

    /**
     * Menentukan apakah tugas masih menerima pengumpulan pada waktu tertentu.
     */
    public function acceptsSubmissionsAt(CarbonInterface $at): bool
    {
        return $this->status === LearningContentStatus::Published
            && (! $this->isPastDue($at) || $this->allow_late_submission);
    }

    /**
     * Menentukan apakah tugas sudah memiliki pengumpulan (tidak boleh dihapus).
     */
    public function hasSubmissions(): bool
    {
        return $this->submissions()->exists();
    }

    /**
     * Menentukan apakah tugas memiliki lampiran.
     */
    public function hasAttachment(): bool
    {
        return $this->attachment_path !== null;
    }
}
