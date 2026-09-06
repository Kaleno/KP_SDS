<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['number', 'start_surah_id', 'start_ayah', 'end_surah_id', 'end_ayah'])]
class QuranJuz extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    protected $table = 'quran_juz';

    protected $primaryKey = 'number';

    protected $keyType = 'int';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'number' => 'integer',
            'start_surah_id' => 'integer',
            'start_ayah' => 'integer',
            'end_surah_id' => 'integer',
            'end_ayah' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<QuranSurah, $this>
     */
    public function startSurah(): BelongsTo
    {
        return $this->belongsTo(QuranSurah::class, 'start_surah_id');
    }

    /**
     * @return BelongsTo<QuranSurah, $this>
     */
    public function endSurah(): BelongsTo
    {
        return $this->belongsTo(QuranSurah::class, 'end_surah_id');
    }
}
