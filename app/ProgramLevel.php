<?php

namespace App;

enum ProgramLevel: string
{
    case Sd = 'sd';
    case Smp = 'smp';
    case Sma = 'sma';
    case Umum = 'umum';

    /**
     * Label jenjang program untuk ditampilkan di antarmuka.
     */
    public function label(): string
    {
        return match ($this) {
            self::Sd => 'SD',
            self::Smp => 'SMP',
            self::Sma => 'SMA',
            self::Umum => 'Umum / Lintas Jenjang',
        };
    }
}
