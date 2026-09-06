<?php

namespace App\Services;

use App\Models\Halaqah;
use App\Models\Schedule;

class ScheduleConflictChecker
{
    public function ustazOverlaps(Halaqah $halaqah, int $dayOfWeek, string $startTime, string $endTime, ?int $ignoreScheduleId = null): bool
    {
        $query = Schedule::query()
            ->where('is_active', true)
            ->where('day_of_week', $dayOfWeek)
            ->whereHas('halaqah', function ($halaqahQuery) use ($halaqah) {
                $halaqahQuery
                    ->where('ustaz_user_id', $halaqah->ustaz_user_id)
                    ->where('is_active', true);
            });

        if ($ignoreScheduleId) {
            $query->where('id', '!=', $ignoreScheduleId);
        }

        return $query->get()->contains(function (Schedule $schedule) use ($startTime, $endTime) {
            return $startTime < $schedule->end_time && $endTime > $schedule->start_time;
        });
    }
}
