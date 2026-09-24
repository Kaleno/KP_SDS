<?php

namespace App\Models;

use App\Enums\ActivityType;
use App\Enums\SetoranCategory;
use App\Enums\SetoranStatus;
use App\Enums\SetoranSubtype;
use App\Support\QuranCatalog;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'santri_id',
    'ustaz_user_id',
    'activity_type',
    'category',
    'subtype',
    'iqro_level',
    'iqro_page',
    'doa_name',
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
            'category' => SetoranCategory::class,
            'subtype' => SetoranSubtype::class,
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
     * @return BelongsTo<User, $this>
     */
    public function ustaz(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ustaz_user_id')->withTrashed();
    }

    /**
     * @return BelongsTo<QuranSurah, $this>
     */
    public function surah(): BelongsTo
    {
        return $this->belongsTo(QuranSurah::class, 'quran_surah_id');
    }

    public function isIqro(): bool
    {
        return $this->subtype === SetoranSubtype::Iqro || $this->iqro_level !== null;
    }

    public function ayahRange(): string
    {
        if ($this->subtype === SetoranSubtype::Doa) {
            return '—';
        }

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

    public function typeLabel(): string
    {
        $category = $this->category?->label() ?? 'Setoran';
        $subtype = $this->subtype?->label() ?? '';

        return trim($category.' · '.$subtype);
    }

    public function passageLabel(): string
    {
        return match ($this->subtype) {
            SetoranSubtype::Iqro => 'Iqro '.$this->iqro_level.' hlm. '.$this->iqro_page,
            SetoranSubtype::Doa => $this->doa_name ?: 'Doa',
            SetoranSubtype::Alquran, SetoranSubtype::Juz30 => $this->quranPassageLabel(),
            default => $this->isIqro()
                ? 'Iqro '.$this->iqro_level.' hlm. '.$this->iqro_page
                : $this->quranPassageLabel(),
        };
    }

    public function listPassage(): string
    {
        return match ($this->subtype) {
            SetoranSubtype::Iqro => 'Iqro '.$this->iqro_level.' hlm. '.$this->iqro_page,
            SetoranSubtype::Doa => $this->doa_name ?: 'Doa',
            SetoranSubtype::Alquran, SetoranSubtype::Juz30 => ($this->surah?->name_id ?? 'Surat').' '.$this->ayahRange(),
            default => $this->passageLabel(),
        };
    }

    private function quranPassageLabel(): string
    {
        $surah = $this->surah?->name_id ?? 'Surat';
        $base = $surah.' ayat '.$this->ayahRange();
        if ($this->quran_surah_id && $this->ayah_start) {
            $juz = QuranCatalog::juzForAyah((int) $this->quran_surah_id, (int) $this->ayah_start);
            if ($juz) {
                return $base.' · Juz '.$juz;
            }
        }

        return $base;
    }
}
