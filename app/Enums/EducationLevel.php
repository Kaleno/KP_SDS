<?php

namespace App\Enums;

enum EducationLevel: string
{
    case Tk = 'tk';
    case Sd = 'sd';
    case Smp = 'smp';
    case Sma = 'sma';
    case S1 = 's1';
    case S2 = 's2';
    case S3 = 's3';
    case Pondok = 'pondok';

    public function label(): string
    {
        return match ($this) {
            self::Tk => 'TK',
            self::Sd => 'SD',
            self::Smp => 'SMP',
            self::Sma => 'SMA',
            self::S1 => 'S1',
            self::S2 => 'S2',
            self::S3 => 'S3',
            self::Pondok => 'Pondok',
        };
    }

    /**
     * @return list<self>
     */
    public static function ordered(): array
    {
        return [
            self::Tk,
            self::Sd,
            self::Smp,
            self::Sma,
            self::S1,
            self::S2,
            self::S3,
            self::Pondok,
        ];
    }
}
