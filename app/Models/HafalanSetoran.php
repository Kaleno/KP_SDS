<?php

namespace App\Models;

use App\Enums\ActivityType;
use App\Enums\SetoranStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'santri_id',
    'halaqah_id',
    'ustaz_user_id',
    'academic_year_id',
    'activity_type',
    'iqro_level',
    'iqro_page',
    'quran_surah_id',
    'setoran_date',
    'ayah_start',
    'ayah_end',
    'status',
    'note',
    'correction_note',
])]
class HafalanSetoran extends Model
{
    protected $table = 'hafalan_setoran';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'setoran_date' => 'date',
            'ayah_start' => 'integer',
            'ayah_end' => 'integer',
            'iqro_level' => 'integer',
            'iqro_page' => 'integer',
            'status' => SetoranStatus::class,
            'activity_type' => ActivityType::class,
        ];
    }

    /**
     * @return BelongsTo<SantriProfile, $this>
     */
    public function santri(): BelongsTo
    {
        return $this->belongsTo(SantriProfile::class, 'santri_id');
    }

    /**
     * @return BelongsTo<Halaqah, $this>
     */
    public function halaqah(): BelongsTo
    {
        return $this->belongsTo(Halaqah::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function ustaz(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ustaz_user_id');
    }

    /**
     * @return BelongsTo<QuranSurah, $this>
     */
    public function surah(): BelongsTo
    {
        return $this->belongsTo(QuranSurah::class, 'quran_surah_id');
    }

    /**
     * @return BelongsTo<AcademicYear, $this>
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function isIqro(): bool
    {
        return $this->iqro_level !== null;
    }

    public function ayahRange(): string
    {
        if ($this->isIqro()) {
            return 'hlm. '.$this->iqro_page;
        }

        if ($this->ayah_start === null) {
            return '—';
        }

        return $this->ayah_start === $this->ayah_end
            ? (string) $this->ayah_start
            : $this->ayah_start.'–'.$this->ayah_end;
    }

    public function passageLabel(): string
    {
        if ($this->isIqro()) {
            return 'Iqro '.$this->iqro_level.' hlm. '.$this->iqro_page;
        }

        $surah = $this->surah?->name_id ?? 'Surat';

        return $surah.' ayat '.$this->ayahRange();
    }
}
