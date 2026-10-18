<?php

namespace App;

/**
 * Status honorarium guru.
 *
 * Draft dibuat oleh admin; Disetujui menandakan honor telah dikonfirmasi untuk dibayarkan;
 * Dibayar berarti nominal telah ditransfer kepada guru. Status Dibayar bersifat final.
 */
enum HonorariumStatus: string
{
    case Draft = 'draft';
    case Approved = 'approved';
    case PartiallyPaid = 'partially_paid';
    case Paid = 'paid';

    /**
     * Label status untuk ditampilkan di antarmuka.
     */
    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Approved => 'Menunggu Pembayaran',
            self::PartiallyPaid => 'Dibayar Sebagian',
            self::Paid => 'Sudah Dibayar',
        };
    }

    /**
     * Kelas CSS badge status.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Draft => 'is-neutral',
            self::Approved, self::PartiallyPaid => 'is-pending',
            self::Paid => 'is-success',
        };
    }

    /**
     * Menentukan apakah honorarium masih dapat diedit oleh admin.
     */
    public function isEditable(): bool
    {
        return $this === self::Draft;
    }

    /**
     * Menentukan apakah honorarium dapat disetujui.
     */
    public function isApprovable(): bool
    {
        return $this === self::Draft;
    }

    /**
     * Menentukan apakah honorarium dapat menerima pembayaran.
     */
    public function isPayable(): bool
    {
        return $this === self::Approved || $this === self::PartiallyPaid;
    }
}
