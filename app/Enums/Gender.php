<?php

namespace App\Enums;

enum Gender: string
{
    case LakiLaki = 'laki-laki';
    case Perempuan = 'perempuan';

    public function label(): string
    {
        return match ($this) {
            self::LakiLaki => 'Laki-laki',
            self::Perempuan => 'Perempuan',
        };
    }
}
