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
            self::Lancar => 'ui-choice-lancar',
            self::Ulang => 'ui-choice-ulang',
            self::Perbaikan => 'ui-choice-perbaikan',
        };
    }
}
