<?php

namespace App;

/**
 * Kategori notifikasi aplikasi.
 */
enum NotificationCategory: string
{
    case Registration = 'registration';
    case Enrollment = 'enrollment';
    case Payment = 'payment';
    case Schedule = 'schedule';
    case Assignment = 'assignment';
    case Submission = 'submission';
    case Grade = 'grade';
    case Announcement = 'announcement';

    /**
     * Label kategori untuk ditampilkan di antarmuka.
     */
    public function label(): string
    {
        return match ($this) {
            self::Registration => 'Murid Baru',
            self::Enrollment => 'Pendaftaran',
            self::Payment => 'Pembayaran',
            self::Schedule => 'Jadwal Belajar',
            self::Assignment => 'Tugas',
            self::Submission => 'Pengumpulan Tugas',
            self::Grade => 'Penilaian',
            self::Announcement => 'Pengumuman',
        };
    }

    /**
     * Kelas CSS badge kategori.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Payment, self::Submission => 'is-pending',
            self::Grade, self::Registration, self::Enrollment => 'is-success',
            default => 'is-neutral',
        };
    }
}
