<?php

namespace App\Enums;

enum SchoolLevel: string
{
    case Tk = 'tk';
    case Sd = 'sd';
    case Smp = 'smp';
    case Sma = 'sma';
    case Kuliah = 'kuliah';

    public function label(): string
    {
        return match ($this) {
            self::Tk => 'TK',
            self::Sd => 'SD',
            self::Smp => 'SMP',
            self::Sma => 'SMA',
            self::Kuliah => 'Kuliah',
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
            self::Kuliah,
        ];
    }
}
