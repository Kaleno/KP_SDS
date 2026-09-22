<?php

namespace App\Enums;

enum FinanceType: string
{
    case Pemasukan = 'pemasukan';
    case Pengeluaran = 'pengeluaran';

    public function label(): string
    {
        return match ($this) {
            self::Pemasukan => 'Pemasukan',
            self::Pengeluaran => 'Pengeluaran',
        };
    }
}
