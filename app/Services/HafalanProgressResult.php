<?php

namespace App\Services;

final class HafalanProgressResult
{
    public const QURAN_AYAH_TOTAL = 6236;

    /**
     * @param  list<JuzProgress>  $juz
     */
    public function __construct(
        public int $uniqueAyahCount,
        public int $quranAyahTotal,
        public float $totalPercent,
        public array $juz,
    ) {}

    public function juz(int $number): ?JuzProgress
    {
        foreach ($this->juz as $bar) {
            if ($bar->number === $number) {
                return $bar;
            }
        }

        return null;
    }

    public function leadingJuz(): ?JuzProgress
    {
        foreach ($this->juz as $bar) {
            if ($bar->lancarCount > 0) {
                return $bar;
            }
        }

        return null;
    }
}
