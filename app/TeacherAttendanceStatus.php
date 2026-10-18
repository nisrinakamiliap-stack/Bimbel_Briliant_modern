<?php

namespace App;

/**
 * Status kehadiran guru pada sebuah sesi mengajar.
 *
 * Hadir/Terlambat ditentukan otomatis saat check-in; Izin/Sakit/Alpa hanya dapat ditetapkan
 * melalui koreksi admin dengan alasan.
 */
enum TeacherAttendanceStatus: string
{
    case Present = 'present';
    case Late = 'late';
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
            self::Late => 'Terlambat',
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
            self::Late, self::Excused, self::Sick => 'is-pending',
            self::Absent => 'is-danger',
        };
    }
}
