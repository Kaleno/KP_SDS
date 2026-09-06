<?php

namespace App\Support;

final class WeekDay
{
    /**
     * @return array<int, string>
     */
    public static function labels(): array
    {
        return [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ];
    }

    public static function label(int $day): string
    {
        return self::labels()[$day] ?? (string) $day;
    }
}
