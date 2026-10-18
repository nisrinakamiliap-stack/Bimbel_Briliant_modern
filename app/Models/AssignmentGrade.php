<?php

namespace App\Models;

use Database\Factories\AssignmentGradeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Penilaian atas satu pengumpulan tugas (paling banyak satu per pengumpulan).
 */
#[Fillable(['score', 'feedback'])]
class AssignmentGrade extends Model
{
    /** @use HasFactory<AssignmentGradeFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'previous_score' => 'decimal:2',
            'graded_at' => 'datetime',
            'revision_count' => 'integer',
        ];
    }

    /**
     * Pengumpulan yang dinilai.
     *
     * @return BelongsTo<Submission, $this>
     */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class, 'submission_id');
    }

    /**
     * Penilai (guru/admin).
     *
     * @return BelongsTo<User, $this>
     */
    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'grader_id')->withTrashed();
    }

    /**
     * Menyimpan atau memperbarui nilai sebuah pengumpulan.
     *
     * Wajib dipanggil di dalam transaksi database. Baris pengumpulan dikunci sehingga dua penilaian
     * bersamaan tidak membuat nilai ganda; perubahan nilai menyimpan nilai sebelumnya.
     */
    public static function record(Submission $submission, string $score, ?string $feedback, User $grader): self
    {
        Submission::query()->whereKey($submission->id)->lockForUpdate()->first();

        $grade = static::query()->where('submission_id', $submission->id)->first();

        if ($grade === null) {
            $grade = new self;
            $grade->submission_id = $submission->id;
        } else {
            $grade->previous_score = $grade->score;
            $grade->revision_count++;
        }

        $grade->score = $score;
        $grade->feedback = filled($feedback) ? trim($feedback) : null;
        $grade->grader_id = $grader->id;
        $grade->graded_at = now();
        $grade->save();

        return $grade;
    }

    /**
     * Nilai untuk ditampilkan tanpa angka nol di belakang koma, misalnya "85" atau "87,5".
     */
    public function formattedScore(): string
    {
        $score = (float) $this->score;

        return fmod($score, 1.0) === 0.0 ? number_format($score, 0, ',', '.') : rtrim(rtrim(number_format($score, 2, ',', '.'), '0'), ',');
    }
}
