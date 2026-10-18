<?php

namespace App\Models;

use App\LearningContentStatus;
use Database\Factories\MaterialFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\UploadedFile;

/**
 * Materi pembelajaran untuk satu kelas.
 *
 * Penulis dan berkas diatur oleh sistem sehingga tidak dapat diisi melalui mass assignment.
 */
#[Fillable(['classroom_id', 'title', 'description', 'content', 'link_url', 'status'])]
#[Hidden(['file_path'])]
class Material extends Model
{
    /** @use HasFactory<MaterialFactory> */
    use HasFactory;

    /**
     * Disk privat untuk berkas pembelajaran.
     */
    public const FILE_DISK = 'local';

    /**
     * Nilai default atribut model, selaras dengan default kolom di database.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'draft',
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
            'published_at' => 'datetime',
        ];
    }

    /**
     * Mencatat waktu publikasi pertama kali saat materi diterbitkan.
     */
    protected static function booted(): void
    {
        static::saving(function (Material $material): void {
            if ($material->status === LearningContentStatus::Published && $material->published_at === null) {
                $material->published_at = now();
            }
        });
    }

    /**
     * Kelas tujuan materi.
     *
     * @return BelongsTo<Classroom, $this>
     */
    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'classroom_id');
    }

    /**
     * Pengguna (guru/admin) yang membuat materi.
     *
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id')->withTrashed();
    }

    /**
     * Tugas yang terkait dengan materi ini.
     *
     * @return HasMany<Assignment, $this>
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class, 'material_id');
    }

    /**
     * Membatasi query pada materi yang sudah terbit.
     *
     * @param  Builder<Material>  $query
     */
    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where('status', LearningContentStatus::Published->value);
    }

    /**
     * Menentukan apakah materi memiliki berkas lampiran.
     */
    public function hasFile(): bool
    {
        return $this->file_path !== null;
    }

    /**
     * Mengganti atau menghapus berkas materi pada disk privat (belum disimpan ke database).
     *
     * Mengembalikan path berkas lama yang harus dihapus setelah perubahan berhasil disimpan, atau null.
     */
    public function replaceFile(?UploadedFile $file, bool $remove = false): ?string
    {
        if ($file === null && ! $remove) {
            return null;
        }

        $oldPath = $this->file_path;
        $this->file_path = null;
        $this->file_original_name = null;

        if ($file !== null) {
            $this->file_path = $file->store('learning/materials', self::FILE_DISK);
            $this->file_original_name = $file->getClientOriginalName();
        }

        return $oldPath;
    }
}
