<?php

namespace App\Notifications;

use App\NotificationCategory;
use Illuminate\Notifications\Notification;

/**
 * Notifikasi aplikasi yang disimpan di database (Laravel Notifications, kanal `database`).
 *
 * Setiap notifikasi memiliki kunci unik peristiwa (`dedupe_key`) sehingga satu peristiwa tidak
 * pernah menghasilkan notifikasi ganda untuk penerima yang sama (lihat {@see NotificationSender}).
 */
abstract class AppNotification extends Notification
{
    /**
     * Kategori notifikasi.
     */
    abstract public function category(): NotificationCategory;

    /**
     * Judul singkat notifikasi.
     */
    abstract public function title(): string;

    /**
     * Isi notifikasi.
     */
    abstract public function message(): string;

    /**
     * Tautan tujuan saat notifikasi dibuka, atau null.
     */
    abstract public function url(object $notifiable): ?string;

    /**
     * Kunci unik peristiwa, misalnya "payment-created:12".
     */
    abstract public function dedupeKey(): string;

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Data yang disimpan pada kolom `data` tabel notifikasi.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'category' => $this->category()->value,
            'title' => $this->title(),
            'message' => $this->message(),
            'url' => $this->url($notifiable),
            'dedupe_key' => $this->dedupeKey(),
        ];
    }
}
