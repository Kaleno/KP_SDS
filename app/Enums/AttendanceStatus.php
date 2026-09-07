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

    public function hint(): string
    {
        return match ($this) {
            self::Hadir => 'Hadir di halaqah',
            self::Izin => 'Tidak hadir dengan izin',
            self::Sakit => 'Tidak hadir karena sakit',
            self::Alfa => 'Tidak hadir tanpa izin',
        };
    }

    public function buttonClass(): string
    {
        return match ($this) {
            self::Hadir => 'ui-choice-hadir',
            self::Izin => 'ui-choice-izin',
            self::Sakit => 'ui-choice-sakit',
            self::Alfa => 'ui-choice-alfa',
        };
    }
}
