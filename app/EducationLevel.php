<?php

namespace App;

enum EducationLevel: string
{
    case Sd = 'sd';
    case Smp = 'smp';
    case Sma = 'sma';
    case Alumni = 'alumni';

    /**
     * Label jenjang pendidikan untuk ditampilkan di antarmuka.
     */
    public function label(): string
    {
        return match ($this) {
            self::Sd => 'SD',
            self::Smp => 'SMP',
            self::Sma => 'SMA',
            self::Alumni => 'Lulusan SMA/Sederajat',
        };
    }
}
