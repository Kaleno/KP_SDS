<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

final class DateLabel
{
    /**
     * Contoh: Rabu, 23 September 2026
     */
    public static function long(CarbonInterface|string $date): string
    {
        return Carbon::parse($date)
            ->locale(app()->getLocale())
            ->translatedFormat('l, j F Y');
    }

    /**
     * Contoh: 23 September 2026
     */
    public static function dayMonthYear(CarbonInterface|string $date): string
    {
        return Carbon::parse($date)
            ->locale(app()->getLocale())
            ->translatedFormat('j F Y');
    }
}
