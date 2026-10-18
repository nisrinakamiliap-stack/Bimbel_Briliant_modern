<?php

namespace App;

/**
 * Status publikasi materi dan tugas.
 *
 * Hanya konten berstatus Terbit yang dapat dilihat murid. Konten diarsipkan alih-alih dihapus
 * agar riwayat pembelajaran tetap utuh.
 */
enum LearningContentStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';

    /**
     * Label status untuk ditampilkan di antarmuka.
     */
    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draf',
            self::Published => 'Terbit',
            self::Archived => 'Diarsipkan',
        };
    }

    /**
     * Kelas CSS badge status.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Draft => 'is-pending',
            self::Published => 'is-success',
            self::Archived => 'is-neutral',
        };
    }
}
