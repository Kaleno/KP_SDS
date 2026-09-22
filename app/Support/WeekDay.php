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

    /**
     * @param  list<int>  $days
     */
    public static function summarize(array $days): string
    {
        $days = array_values(array_unique(array_map('intval', $days)));
        sort($days);

        if ($days === [1, 2, 3, 4, 5, 6, 7]) {
            return 'Setiap hari';
        }

        if ($days === [1, 2, 3, 4, 5]) {
            return 'Senin–Jumat';
        }

        return collect($days)
            ->map(fn (int $day): string => self::label($day))
            ->implode(', ');
    }
}
