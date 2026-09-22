<?php

namespace App\Enums;

enum SantriTrack: string
{
    case Iqro = 'iqro';
    case Alquran = 'alquran';

    public function label(): string
    {
        return match ($this) {
            self::Iqro => 'Iqro',
            self::Alquran => 'Alquran',
        };
    }
}
