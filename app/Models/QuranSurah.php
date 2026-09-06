<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['id', 'name_id', 'name_ar', 'ayah_count'])]
class QuranSurah extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'int';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'ayah_count' => 'integer',
        ];
    }

    /**
     * @return HasMany<QuranJuz, $this>
     */
    public function juzStartingHere(): HasMany
    {
        return $this->hasMany(QuranJuz::class, 'start_surah_id');
    }
}
