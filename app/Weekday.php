<?php

namespace App;

use Carbon\CarbonInterface;

/**
 * Hari dalam seminggu dengan penomoran ISO-8601 (Senin = 1 sampai Minggu = 7).
 */
enum Weekday: int
{
    case Monday = 1;
    case Tuesday = 2;
    case Wednesday = 3;
    case Thursday = 4;
    case Friday = 5;
    case Saturday = 6;
    case Sunday = 7;

    /**
     * Nama hari dalam Bahasa Indonesia.
     */
    public function label(): string
    {
        return match ($this) {
            self::Monday => 'Senin',
            self::Tuesday => 'Selasa',
            self::Wednesday => 'Rabu',
            self::Thursday => 'Kamis',
            self::Friday => 'Jumat',
            self::Saturday => 'Sabtu',
            self::Sunday => 'Minggu',
        };
    }

    /**
     * Hari yang sesuai dengan tanggal tertentu.
     */
    public static function fromDate(CarbonInterface $date): self
    {
        return self::from($date->isoWeekday());
    }
}
