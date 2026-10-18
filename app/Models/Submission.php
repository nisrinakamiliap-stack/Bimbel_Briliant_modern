<?php

namespace App\Models;

use Database\Factories\SubmissionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Pengumpulan tugas oleh seorang murid.
 *
 * Tugas, murid, pendaftaran, berkas, waktu kirim, dan status keterlambatan diatur oleh sistem
 * (waktu server) sehingga hanya jawaban teks yang dapat diisi melalui mass assignment.
 */
#[Fillable(['answer_text'])]
#[Hidden(['file_path'])]
class Submission extends Model
{
    /** @use HasFactory<SubmissionFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'is_late' => 'boolean',
            'revision_count' => 'integer',
        ];
    }

    /**
     * Tugas yang dikumpulkan.
     *
     * @return BelongsTo<Assignment, $this>
     */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class, 'assignment_id');
    }

    /**
     * Murid pengirim, termasuk akun yang sudah dihapus agar riwayat tetap terbaca.
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
     * Penilaian untuk pengumpulan ini.
     *
     * @return HasOne<AssignmentGrade, $this>
     */
    public function grade(): HasOne
    {
        return $this->hasOne(AssignmentGrade::class, 'submission_id');
    }

    /**
     * Status pengumpulan: "Dinilai", "Terlambat", atau "Dikumpulkan".
     */
    public function statusLabel(): string
    {
        return match (true) {
            $this->grade !== null => 'Dinilai',
            $this->is_late => 'Terlambat',
            default => 'Dikumpulkan',
        };
    }

    /**
     * Kelas CSS badge status pengumpulan.
     */
    public function statusBadgeClass(): string
    {
        return match ($this->statusLabel()) {
            'Dinilai' => 'is-success',
            'Terlambat' => 'is-danger',
            default => 'is-pending',
        };
    }

    /**
     * Membuat atau memperbarui pengumpulan murid untuk sebuah tugas dengan waktu server.
     *
     * Wajib dipanggil di dalam transaksi database setelah hak mengumpulkan diperiksa. Berkas lama
     * dipertahankan jika tidak ada berkas baru. Mengembalikan path berkas lama yang diganti (untuk
     * dihapus oleh pemanggil setelah transaksi berhasil).
     *
     * @return array{0: self, 1: string|null}
     */
    public static function submitFor(Assignment $assignment, User $student, Enrollment $enrollment, ?string $answerText, ?string $filePath, ?string $fileOriginalName): array
    {
        $submission = static::query()
            ->where('assignment_id', $assignment->id)
            ->where('student_id', $student->id)
            ->lockForUpdate()
            ->first();

        $replacedFilePath = null;

        if ($submission === null) {
            $submission = new self;
            $submission->assignment_id = $assignment->id;
            $submission->student_id = $student->id;
        } else {
            $submission->revision_count++;
        }

        $submission->enrollment_id = $enrollment->id;
        $submission->answer_text = filled($answerText) ? $answerText : null;

        if ($filePath !== null) {
            $replacedFilePath = $submission->file_path;
            $submission->file_path = $filePath;
            $submission->file_original_name = $fileOriginalName;
        }

        $submission->submitted_at = now();
        $submission->is_late = $assignment->isPastDue($submission->submitted_at);
        $submission->save();

        return [$submission, $replacedFilePath];
    }

    /**
     * Menentukan apakah pengumpulan memiliki berkas.
     */
    public function hasFile(): bool
    {
        return $this->file_path !== null;
    }
}
