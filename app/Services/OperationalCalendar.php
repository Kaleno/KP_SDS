<?php

namespace App\Services;

use App\Models\Holiday;
use Carbon\CarbonInterface;

class OperationalCalendar
{
    public function isOffDay(CarbonInterface $date): bool
    {
        $weekday = (int) $date->isoWeekday();

        if ($weekday === 6 || $weekday === 7) {
            return true;
        }

        return Holiday::query()->whereDate('date', $date->toDateString())->exists();
    }

    public function offDayMessage(CarbonInterface $date): string
    {
        $weekday = (int) $date->isoWeekday();

        if ($weekday === 6 || $weekday === 7) {
            return 'Hari libur (Sabtu/Minggu). Sesi tidak dibuka.';
        }

        $holiday = Holiday::query()->whereDate('date', $date->toDateString())->first();

        return $holiday
            ? 'Libur: '.$holiday->name
            : 'Hari libur.';
    }

    public function isFriday(CarbonInterface $date): bool
    {
        return (int) $date->isoWeekday() === 5;
    }
}
