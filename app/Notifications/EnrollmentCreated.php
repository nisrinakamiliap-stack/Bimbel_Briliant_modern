<?php

namespace App\Notifications;

use App\Models\Enrollment;
use App\NotificationCategory;

/**
 * Pendaftaran baru menunggu verifikasi. Dikirim ke admin.
 */
class EnrollmentCreated extends AppNotification
{
    public function __construct(public Enrollment $enrollment) {}

    public function category(): NotificationCategory
    {
        return NotificationCategory::Enrollment;
    }

    public function title(): string
    {
        return 'Pendaftaran baru menunggu verifikasi';
    }

    public function message(): string
    {
        $this->enrollment->loadMissing(['student', 'program']);

        return "{$this->enrollment->student->name} mendaftar ke program {$this->enrollment->program->name}.";
    }

    public function url(object $notifiable): ?string
    {
        return route('admin.enrollments.show', $this->enrollment);
    }

    public function dedupeKey(): string
    {
        return "enrollment-created:{$this->enrollment->id}";
    }
}
