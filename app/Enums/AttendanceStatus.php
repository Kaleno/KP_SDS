<?php

namespace App\Enums;

enum AttendanceStatus: string
{
    case Hadir = 'hadir';
    case Izin = 'izin';
    case Sakit = 'sakit';
    case Alfa = 'alfa';

    public function label(): string
    {
        return match ($this) {
            self::Hadir => 'Hadir',
            self::Izin => 'Izin',
            self::Sakit => 'Sakit',
            self::Alfa => 'Alfa',
        };
    }

    public function buttonClass(): string
    {
        return match ($this) {
            self::Hadir => 'peer-checked:bg-teal-700 peer-checked:border-teal-700 peer-checked:text-white',
            self::Izin => 'peer-checked:bg-amber-500 peer-checked:border-amber-500 peer-checked:text-white',
            self::Sakit => 'peer-checked:bg-sky-600 peer-checked:border-sky-600 peer-checked:text-white',
            self::Alfa => 'peer-checked:bg-rose-600 peer-checked:border-rose-600 peer-checked:text-white',
        };
    }
}
