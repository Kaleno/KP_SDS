<?php

namespace App\Support;

use Carbon\Carbon;

final class DateQuery
{
    public static function ymd(mixed $value): ?string
    {
        if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }

        try {
            $date = Carbon::createFromFormat('!Y-m-d', $value);
        } catch (\Throwable) {
            return null;
        }

        if (! $date || $date->format('Y-m-d') !== $value) {
            return null;
        }

        return $date->toDateString();
    }
}
