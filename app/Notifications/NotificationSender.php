<?php

namespace App\Notifications;

use App\Models\User;
use App\UserRole;
use Illuminate\Support\Collection;

/**
 * Mengirim notifikasi tanpa duplikasi: penerima yang sudah memiliki notifikasi dengan kunci
 * peristiwa yang sama dilewati. Pengguna yang memicu peristiwa tidak menerima notifikasinya sendiri.
 */
class NotificationSender
{
    /**
     * Mengirim notifikasi ke daftar penerima, kecuali pemicu peristiwa dan penerima yang sudah menerimanya.
     *
     * @param  iterable<User>|User  $recipients
     */
    public static function send(iterable|User $recipients, AppNotification $notification, ?User $actor = null): int
    {
        $actor ??= auth()->user();
        $sent = 0;

        $recipients = $recipients instanceof User ? [$recipients] : $recipients;

        foreach (Collection::make($recipients)->unique('id') as $recipient) {
            if ($actor !== null && $recipient->is($actor)) {
                continue;
            }

            $alreadyNotified = $recipient->notifications()
                ->where('data->dedupe_key', $notification->dedupeKey())
                ->exists();

            if ($alreadyNotified) {
                continue;
            }

            $recipient->notify($notification);
            $sent++;
        }

        return $sent;
    }

    /**
     * Seluruh admin aktif (akun belum dihapus).
     *
     * @return Collection<int, User>
     */
    public static function admins(): Collection
    {
        return User::query()->where('role', UserRole::Admin->value)->get();
    }
}
