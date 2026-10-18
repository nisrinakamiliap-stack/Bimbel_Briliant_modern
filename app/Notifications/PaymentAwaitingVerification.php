<?php

namespace App\Notifications;

use App\Models\Payment;
use App\NotificationCategory;

/**
 * Transaksi pembayaran baru yang menunggu verifikasi. Dikirim ke admin.
 */
class PaymentAwaitingVerification extends AppNotification
{
    public function __construct(public Payment $payment) {}

    public function category(): NotificationCategory
    {
        return NotificationCategory::Payment;
    }

    public function title(): string
    {
        return 'Pembayaran menunggu verifikasi';
    }

    public function message(): string
    {
        $this->payment->loadMissing('enrollment.student');

        return "Transaksi {$this->payment->code} sebesar ".Payment::rupiah($this->payment->amount)." dari {$this->payment->enrollment->student->name} perlu diverifikasi.";
    }

    public function url(object $notifiable): ?string
    {
        return route('admin.payments.show', $this->payment);
    }

    public function dedupeKey(): string
    {
        return "payment-created:{$this->payment->id}";
    }
}
