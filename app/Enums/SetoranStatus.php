<?php

namespace App\Enums;

enum SetoranStatus: string
{
    case Lancar = 'lancar';
    case Ulang = 'ulang';
    case Perbaikan = 'perbaikan';

    public function label(): string
    {
        return match ($this) {
            self::Lancar => 'Lancar',
            self::Ulang => 'Ulang',
            self::Perbaikan => 'Perbaikan',
        };
    }

    public function hint(): string
    {
        return match ($this) {
            self::Lancar => 'Hafalan diterima pengajar',
            self::Ulang => 'Perlu diulang di pertemuan berikutnya',
            self::Perbaikan => 'Perlu diperbaiki di pertemuan berikutnya',
        };
    }

    public function buttonClass(): string
    {
        return match ($this) {
            self::Lancar => 'peer-checked:bg-teal-700 peer-checked:border-teal-700 peer-checked:text-white',
            self::Ulang => 'peer-checked:bg-amber-500 peer-checked:border-amber-500 peer-checked:text-white',
            self::Perbaikan => 'peer-checked:bg-sky-600 peer-checked:border-sky-600 peer-checked:text-white',
        };
    }
}
