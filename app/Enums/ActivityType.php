<?php

namespace App\Enums;

enum ActivityType: string
{
    case Ngaji = 'ngaji';
    case Hafalan = 'hafalan';

    public function label(): string
    {
        return match ($this) {
            self::Ngaji => 'Ngaji',
            self::Hafalan => 'Hafalan',
        };
    }
}
