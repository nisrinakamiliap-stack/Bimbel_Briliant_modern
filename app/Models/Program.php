<?php

namespace App\Models;

use App\ProgramLevel;
use Database\Factories\ProgramFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Program bimbel yang ditawarkan, misalnya Bimbingan Belajar SD atau Persiapan Ujian.
 *
 * Kolom `slug` dibuat otomatis dari nama saat program dibuat dan tidak diubah lagi,
 * sehingga tidak bisa diisi melalui mass assignment.
 */
#[Fillable(['name', 'level', 'description', 'price', 'duration_months', 'is_active'])]
class Program extends Model
{
    /** @use HasFactory<ProgramFactory> */
    use HasFactory;

    /**
     * Relasi yang menandakan program sudah dipakai dan tidak boleh dihapus permanen.
     *
     * Tambahkan nama relasi di sini ketika modul Pembayaran dibuat, misalnya `payments`.
     *
     * @var list<string>
     */
    protected const DEPENDENT_RELATIONS = ['classes', 'enrollments'];

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
            'level' => ProgramLevel::class,
            'price' => 'decimal:2',
            'duration_months' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Membuat slug unik dari nama program saat program pertama kali disimpan.
     */
    protected static function booted(): void
    {
        static::creating(function (Program $program): void {
            if (blank($program->slug)) {
                $program->slug = static::uniqueSlugFrom($program->name);
            }
        });
    }

    /**
     * Kelas-kelas yang berada dalam program ini.
     *
     * @return HasMany<Classroom, $this>
     */
    public function classes(): HasMany
    {
        return $this->hasMany(Classroom::class, 'program_id');
    }

    /**
     * Pendaftaran murid ke program ini.
     *
     * @return HasMany<Enrollment, $this>
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'program_id');
    }

    /**
     * Menentukan apakah program sudah memiliki data terkait (kelas, pendaftaran, pembayaran).
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
     * Harga dalam format rupiah, misalnya "Rp150.000".
     */
    public function formattedPrice(): string
    {
        $price = (float) $this->price;
        $decimals = fmod($price, 1.0) === 0.0 ? 0 : 2;

        return 'Rp'.number_format($price, $decimals, ',', '.');
    }

    /**
     * Membuat slug unik dengan menambahkan angka jika slug sudah dipakai.
     */
    protected static function uniqueSlugFrom(string $name): string
    {
        $baseSlug = Str::slug($name) ?: 'program';
        $slug = $baseSlug;
        $suffix = 2;

        while (static::query()->where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
