<?php

namespace App;

/**
 * Status pendaftaran murid beserta aturan perpindahan status.
 *
 * Alur: Menunggu Verifikasi → Diterima / Ditolak / Dibatalkan; Diterima → Dibatalkan.
 * Pendaftaran yang ditolak/dibatalkan hanya bisa dibuka kembali (menjadi Menunggu Verifikasi)
 * melalui tindakan eksplisit admin.
 */
enum EnrollmentStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';

    /**
     * Label status untuk ditampilkan di antarmuka.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu Verifikasi',
            self::Approved => 'Diterima',
            self::Rejected => 'Ditolak',
            self::Cancelled => 'Dibatalkan',
        };
    }

    /**
     * Kelas CSS badge status.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'is-pending',
            self::Approved => 'is-success',
            self::Rejected => 'is-danger',
            self::Cancelled => 'is-neutral',
        };
    }

    /**
     * Label tombol aksi untuk berpindah ke status ini.
     */
    public function actionLabel(): string
    {
        return match ($this) {
            self::Pending => 'Buka Kembali',
            self::Approved => 'Terima',
            self::Rejected => 'Tolak',
            self::Cancelled => 'Batalkan',
        };
    }

    /**
     * Pendaftaran aktif menempati kursi kelas dan dihitung dalam pemeriksaan pendaftaran ganda.
     */
    public function isActive(): bool
    {
        return in_array($this, self::activeStatuses(), true);
    }

    /**
     * Status yang dianggap aktif.
     *
     * @return list<self>
     */
    public static function activeStatuses(): array
    {
        return [self::Pending, self::Approved];
    }

    /**
     * Status tujuan yang diizinkan dari status ini.
     *
     * @return list<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Pending => [self::Approved, self::Rejected, self::Cancelled],
            self::Approved => [self::Cancelled],
            self::Rejected, self::Cancelled => [self::Pending],
        };
    }

    /**
     * Menentukan apakah perpindahan ke status tujuan diizinkan.
     */
    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }
}
