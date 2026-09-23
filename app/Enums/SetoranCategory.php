<?php

namespace App\Enums;

enum SetoranCategory: string
{
    case Bacaan = 'bacaan';
    case Hafalan = 'hafalan';

    public function label(): string
    {
        return match ($this) {
            self::Bacaan => 'Bacaan',
            self::Hafalan => 'Hafalan',
        };
    }

    /**
     * @return list<SetoranSubtype>
     */
    public function subtypes(): array
    {
        return match ($this) {
            self::Bacaan => [SetoranSubtype::Iqro, SetoranSubtype::Alquran],
            self::Hafalan => [SetoranSubtype::Doa, SetoranSubtype::Juz30],
        };
    }
}
