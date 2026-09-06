<?php

namespace App\Services;

final class JuzProgress
{
    public function __construct(
        public int $number,
        public int $lancarCount,
        public int $ayahTotal,
        public float $percent,
    ) {}
}
