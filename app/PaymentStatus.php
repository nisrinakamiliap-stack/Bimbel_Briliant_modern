<?php

namespace App;

/**
 * Status transaksi pembayaran (terpisah dari status pendaftaran).
 *
 * Transaksi baru selalu Menunggu Verifikasi; hanya transaksi berstatus ini yang dapat
 * diverifikasi (Berhasil) atau ditolak. Status Berhasil dan Ditolak bersifat final.
 */
enum PaymentStatus: string
{
    case WaitingVerification = 'waiting_verification';
    case Paid = 'paid';
    case Rejected = 'rejected';

    /**
     * Label status untuk ditampilkan di antarmuka.
     */
    public function label(): string
    {
        return match ($this) {
            self::WaitingVerification => 'Menunggu Verifikasi',
            self::Paid => 'Berhasil',
            self::Rejected => 'Ditolak',
        };
    }

    /**
     * Kelas CSS badge status.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::WaitingVerification => 'is-pending',
            self::Paid => 'is-success',
            self::Rejected => 'is-danger',
        };
    }

    /**
     * Menentukan apakah transaksi masih dapat diverifikasi atau ditolak.
     */
    public function isProcessable(): bool
    {
        return $this === self::WaitingVerification;
    }
}
