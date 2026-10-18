<?php

namespace App\Notifications;

use App\Models\User;
use App\NotificationCategory;

/**
 * Akun murid baru dibuat (registrasi publik atau oleh admin). Dikirim ke admin.
 */
class NewStudentRegistered extends AppNotification
{
    public function __construct(public User $student) {}

    public function category(): NotificationCategory
    {
        return NotificationCategory::Registration;
    }

    public function title(): string
    {
        return 'Murid baru terdaftar';
    }

    public function message(): string
    {
        return "{$this->student->name} ({$this->student->email}) membuat akun murid.";
    }

    public function url(object $notifiable): ?string
    {
        return route('admin.students.show', $this->student);
    }

    public function dedupeKey(): string
    {
        return "student-registered:{$this->student->id}";
    }
}
