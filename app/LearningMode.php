<?php

namespace App;

enum LearningMode: string
{
    case Offline = 'offline';
    case Online = 'online';

    /**
     * Label mode pembelajaran untuk ditampilkan di antarmuka.
     */
    public function label(): string
    {
        return match ($this) {
            self::Offline => 'Offline (Tatap Muka)',
            self::Online => 'Online',
        };
    }
}
