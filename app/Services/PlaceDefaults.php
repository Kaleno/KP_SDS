<?php

namespace App\Services;

use App\Models\AcademicYear;

class PlaceDefaults
{
    public function activeYear(): AcademicYear
    {
        $year = AcademicYear::query()->aktif()->first();
        if ($year) {
            return $year;
        }

        $start = now()->month >= 7
            ? now()->copy()->month(7)->startOfMonth()
            : now()->copy()->subYear()->month(7)->startOfMonth();
        $end = $start->copy()->addYear()->subDay();

        $year = AcademicYear::query()->create([
            'name' => $start->year.'/'.$end->year,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'is_active' => false,
        ]);
        $year->markAsActive();

        return $year->fresh();
    }
}
