<?php

namespace App\Notifications;

use App\NotificationCategory;

/**
 * Pengumuman yang dibuat admin untuk sekelompok penerima.
 */
class Announcement extends AppNotification
{
    public function __construct(
        public string $announcementId,
        public string $announcementTitle,
        public string $announcementMessage,
    ) {}

    public function category(): NotificationCategory
    {
        return NotificationCategory::Announcement;
    }

    public function title(): string
    {
        return $this->announcementTitle;
    }

    public function message(): string
    {
        return $this->announcementMessage;
    }

    public function url(object $notifiable): ?string
    {
        return null;
    }

    public function dedupeKey(): string
    {
        return "announcement:{$this->announcementId}";
    }
}
