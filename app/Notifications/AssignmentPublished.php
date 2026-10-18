<?php

namespace App\Notifications;

use App\Models\Assignment;
use App\NotificationCategory;

/**
 * Tugas baru diterbitkan untuk kelas. Dikirim ke murid peserta kelas.
 */
class AssignmentPublished extends AppNotification
{
    public function __construct(public Assignment $assignment) {}

    public function category(): NotificationCategory
    {
        return NotificationCategory::Assignment;
    }

    public function title(): string
    {
        return 'Tugas baru: '.$this->assignment->title;
    }

    public function message(): string
    {
        $this->assignment->loadMissing('classroom');

        return "Tugas untuk kelas {$this->assignment->classroom->name} dengan tenggat {$this->assignment->due_at->translatedFormat('d F Y, H:i')}.";
    }

    public function url(object $notifiable): ?string
    {
        return route('student.learning.assignments.show', $this->assignment);
    }

    public function dedupeKey(): string
    {
        return "assignment-published:{$this->assignment->id}";
    }
}
