<?php

namespace App;

enum UserRole: string
{
    case Admin = 'admin';
    case Teacher = 'teacher';
    case Student = 'student';

    /**
     * Nama route dashboard untuk setiap role.
     */
    public function dashboardRoute(): string
    {
        return match ($this) {
            self::Admin => 'admin.dashboard',
            self::Teacher => 'teacher.dashboard',
            self::Student => 'student.dashboard',
        };
    }

    /**
     * Label role dalam Bahasa Indonesia untuk ditampilkan di antarmuka.
     */
    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Teacher => 'Guru/Tentor',
            self::Student => 'Murid',
        };
    }
}
