<?php

namespace App\Services;

/**
 * Legacy helper from the kelas / tahun ajaran model.
 * Kept as a no-op stub so leftover callers do not fatal while routes are retired.
 */
class PlaceDefaults
{
    public function activeYear(): never
    {
        throw new \RuntimeException('Tahun ajaran sudah dihapus dari operasi flat.');
    }
}
