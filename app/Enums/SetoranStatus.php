<?php

namespace App\Enums;

enum SetoranStatus: string
{
    case Lulus = 'lulus';
    case Mengulang = 'mengulang';

    public function label(): string
    {
        return match ($this) {
            self::Lulus => 'Lulus',
            self::Mengulang => 'Mengulang',
        };
    }

    public function hint(): string
    {
        return match ($this) {
            self::Lulus => 'Diterima pengajar',
            self::Mengulang => 'Perlu diulang di pertemuan berikutnya',
        };
    }

    public function buttonClass(): string
    {
        return match ($this) {
            self::Lulus => 'ui-choice-lancar',
            self::Mengulang => 'ui-choice-ulang',
        };
    }
}
