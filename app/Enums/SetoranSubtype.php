<?php

namespace App\Enums;

enum SetoranSubtype: string
{
    case Iqro = 'iqro';
    case Alquran = 'alquran';
    case Doa = 'doa';
    case Juz30 = 'juz30';

    public function label(): string
    {
        return match ($this) {
            self::Iqro => 'Iqro',
            self::Alquran => 'Alquran',
            self::Doa => 'Doa',
            self::Juz30 => 'Juz 30',
        };
    }

    public function category(): SetoranCategory
    {
        return match ($this) {
            self::Iqro, self::Alquran => SetoranCategory::Bacaan,
            self::Doa, self::Juz30 => SetoranCategory::Hafalan,
        };
    }

    public function activityType(): ActivityType
    {
        return match ($this) {
            self::Iqro, self::Alquran => ActivityType::Ngaji,
            self::Doa, self::Juz30 => ActivityType::Hafalan,
        };
    }
}
