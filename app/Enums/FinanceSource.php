<?php

namespace App\Enums;

enum FinanceSource: string
{
    case Spp = 'spp';
    case Manual = 'manual';

    public function label(): string
    {
        return match ($this) {
            self::Spp => 'SPP santri',
            self::Manual => 'Manual',
        };
    }
}
