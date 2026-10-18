<?php

namespace App;

/**
 * Status kehadiran murid pada sebuah sesi belajar.
 */
enum AttendanceStatus: string
{
    case Present = 'present';
    case Excused = 'excused';
    case Sick = 'sick';
    case Absent = 'absent';

    /**
     * Label status untuk ditampilkan di antarmuka.
     */
    public function label(): string
    {
        return match ($this) {
            self::Present => 'Hadir',
            self::Excused => 'Izin',
            self::Sick => 'Sakit',
            self::Absent => 'Alpa',
        };
    }

    /**
     * Kelas CSS badge status.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Present => 'is-success',
            self::Excused, self::Sick => 'is-pending',
            self::Absent => 'is-danger',
        };
    }
}
