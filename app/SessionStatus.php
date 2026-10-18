<?php

namespace App;

use Carbon\CarbonInterface;

/**
 * Status sebuah sesi mengajar pada tanggal tertentu, dihitung dari jam jadwal dan waktu server.
 */
enum SessionStatus: string
{
    case Upcoming = 'upcoming';
    case Ongoing = 'ongoing';
    case Finished = 'finished';

    /**
     * Menentukan status sesi berdasarkan waktu mulai, waktu selesai, dan waktu sekarang.
     */
    public static function at(CarbonInterface $startsAt, CarbonInterface $endsAt, CarbonInterface $now): self
    {
        if ($now->lessThan($startsAt)) {
            return self::Upcoming;
        }

        return $now->lessThan($endsAt) ? self::Ongoing : self::Finished;
    }

    /**
     * Label status untuk ditampilkan di antarmuka.
     */
    public function label(): string
    {
        return match ($this) {
            self::Upcoming => 'Akan Datang',
            self::Ongoing => 'Sedang Berlangsung',
            self::Finished => 'Selesai',
        };
    }

    /**
     * Kelas CSS badge status.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Upcoming => 'is-neutral',
            self::Ongoing => 'is-success',
            self::Finished => 'is-neutral',
        };
    }
}
