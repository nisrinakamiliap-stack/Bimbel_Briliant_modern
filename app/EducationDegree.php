<?php

namespace App;

enum EducationDegree: string
{
    case Sma = 'sma';
    case D3 = 'd3';
    case D4 = 'd4';
    case S1 = 's1';
    case S2 = 's2';
    case S3 = 's3';

    /**
     * Label pendidikan terakhir untuk ditampilkan di antarmuka.
     */
    public function label(): string
    {
        return match ($this) {
            self::Sma => 'SMA/SMK Sederajat',
            self::D3 => 'Diploma 3 (D3)',
            self::D4 => 'Diploma 4 (D4)',
            self::S1 => 'Sarjana (S1)',
            self::S2 => 'Magister (S2)',
            self::S3 => 'Doktor (S3)',
        };
    }
}
