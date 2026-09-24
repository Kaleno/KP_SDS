<?php

namespace App\Enums;

enum SantriStatus: string
{
    case Aktif = 'aktif';
    case Cuti = 'cuti';
    case Lulus = 'lulus';
    case Keluar = 'keluar';

    public function label(): string
    {
        return match ($this) {
            self::Aktif => 'Aktif belajar',
            self::Cuti => 'Cuti',
            self::Lulus => 'Lulus',
            self::Keluar => 'Keluar',
        };
    }

    public function allowsLogin(): bool
    {
        return match ($this) {
            self::Aktif, self::Lulus => true,
            self::Cuti, self::Keluar => false,
        };
    }

    public function badgeTone(): string
    {
        return match ($this) {
            self::Aktif => 'ok',
            self::Cuti => 'warn',
            self::Lulus => 'info',
            self::Keluar => 'muted',
        };
    }
}
