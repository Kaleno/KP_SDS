<?php

namespace App\Enums;

enum SantriStatus: string
{
    case Aktif = 'aktif';
    case Lulus = 'lulus';
    case Keluar = 'keluar';

    public function label(): string
    {
        return match ($this) {
            self::Aktif => 'Aktif',
            self::Lulus => 'Lulus',
            self::Keluar => 'Keluar',
        };
    }
}
