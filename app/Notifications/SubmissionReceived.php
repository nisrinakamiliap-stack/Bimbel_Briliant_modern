<?php

namespace App\Notifications;

use App\Models\Submission;
use App\NotificationCategory;

/**
 * Murid mengumpulkan tugas untuk pertama kali. Dikirim ke guru pengampu kelas.
 */
class SubmissionReceived extends AppNotification
{
    public function __construct(public Submission $submission) {}

    public function category(): NotificationCategory
    {
        return NotificationCategory::Submission;
    }

    public function title(): string
    {
        return 'Pengumpulan tugas baru';
    }

    public function message(): string
    {
        $this->submission->loadMissing(['student', 'assignment']);

        return "{$this->submission->student->name} mengumpulkan tugas {$this->submission->assignment->title}".($this->submission->is_late ? ' (terlambat).' : '.');
    }

    /**
     * Dashboard Guru belum tersedia, sehingga belum ada halaman tujuan untuk guru.
     */
    public function url(object $notifiable): ?string
    {
        return null;
    }

    public function dedupeKey(): string
    {
        return "submission-created:{$this->submission->id}";
    }
}
