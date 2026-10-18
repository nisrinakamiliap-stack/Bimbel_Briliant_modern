<?php

namespace App\Notifications;

use App\Models\AssignmentGrade;
use App\NotificationCategory;

/**
 * Pengumpulan murid dinilai atau nilainya diubah. Dikirim ke murid pemilik pengumpulan.
 *
 * Kunci peristiwa menyertakan revisi nilai sehingga perubahan nilai menghasilkan satu notifikasi
 * baru, sedangkan penyimpanan berulang untuk revisi yang sama tidak.
 */
class SubmissionGraded extends AppNotification
{
    public function __construct(public AssignmentGrade $grade) {}

    public function category(): NotificationCategory
    {
        return NotificationCategory::Grade;
    }

    public function title(): string
    {
        return $this->grade->revision_count > 0 ? 'Nilai tugas diperbarui' : 'Tugas Anda sudah dinilai';
    }

    public function message(): string
    {
        $this->grade->loadMissing('submission.assignment');
        $assignment = $this->grade->submission->assignment;

        return "Nilai {$this->grade->formattedScore()}/{$assignment->max_score} untuk tugas {$assignment->title}.";
    }

    public function url(object $notifiable): ?string
    {
        return route('student.learning.assignments.show', $this->grade->submission->assignment_id);
    }

    public function dedupeKey(): string
    {
        return "submission-graded:{$this->grade->submission_id}:{$this->grade->revision_count}";
    }
}
