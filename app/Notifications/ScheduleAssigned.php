<?php

namespace App\Notifications;

use App\Models\Schedule;
use App\NotificationCategory;

/**
 * Jadwal belajar baru untuk kelas. Dikirim ke guru pengampu dan murid peserta kelas.
 */
class ScheduleAssigned extends AppNotification
{
    public function __construct(public Schedule $schedule) {}

    public function category(): NotificationCategory
    {
        return NotificationCategory::Schedule;
    }

    public function title(): string
    {
        return 'Jadwal belajar baru';
    }

    public function message(): string
    {
        $this->schedule->loadMissing('classroom');

        return "Kelas {$this->schedule->classroom->name} dijadwalkan setiap {$this->schedule->summary()}".($this->schedule->room ? " di {$this->schedule->room}." : '.');
    }

    public function url(object $notifiable): ?string
    {
        return null;
    }

    public function dedupeKey(): string
    {
        return "schedule-created:{$this->schedule->id}";
    }
}
