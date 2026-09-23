<?php

namespace App\Http\Requests\Ops;

use App\Enums\SantriTrack;
use App\Enums\SetoranStatus;
use App\Models\HafalanSetoran;
use App\Models\QuranJuz;
use App\Models\QuranSurah;
use App\Models\SantriProfile;
use App\Services\OperationalCalendar;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateSetoranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('operate-daily') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isIqro = $this->targetSantri()?->track === SantriTrack::Iqro;

        return [
            'setoran_date' => ['required', 'date'],
            'status' => ['required', Rule::enum(SetoranStatus::class)],
            'note' => ['nullable', 'string', 'max:500'],
            'correction_note' => ['required', 'string', 'max:500'],
            'quran_surah_id' => [$isIqro ? 'nullable' : 'required', 'exists:quran_surahs,id'],
            'ayah_start' => [$isIqro ? 'nullable' : 'required', 'integer', 'min:1'],
            'ayah_end' => [$isIqro ? 'nullable' : 'required', 'integer', 'gte:ayah_start'],
            'iqro_level' => [$isIqro ? 'required' : 'nullable', 'integer', 'min:1', 'max:6'],
            'iqro_page' => [$isIqro ? 'required' : 'nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $santri = $this->targetSantri();
            if (! $santri || $santri->track === SantriTrack::Iqro) {
                return;
            }

            $this->assertAyahWithinSurah($validator);
            $this->assertFridayJuz30($validator);
        });
    }

    protected function targetSantri(): ?SantriProfile
    {
        /** @var HafalanSetoran|null $setoran */
        $setoran = $this->route('setoran');

        return $setoran?->santri ?? SantriProfile::query()->find($this->input('santri_id'));
    }

    protected function assertAyahWithinSurah(Validator $validator): void
    {
        $surah = QuranSurah::query()->find($this->input('quran_surah_id'));
        if (! $surah) {
            return;
        }

        if ((int) $this->input('ayah_start') > $surah->ayah_count) {
            $validator->errors()->add(
                'ayah_start',
                "Ayat awal melebihi jumlah ayat {$surah->name_id} ({$surah->ayah_count}).",
            );
        }

        if ((int) $this->input('ayah_end') > $surah->ayah_count) {
            $validator->errors()->add(
                'ayah_end',
                "Ayat akhir melebihi jumlah ayat {$surah->name_id} ({$surah->ayah_count}).",
            );
        }
    }

    protected function assertFridayJuz30(Validator $validator): void
    {
        $date = $this->date('setoran_date');
        if (! $date || ! app(OperationalCalendar::class)->isFriday($date)) {
            return;
        }

        $juz = QuranJuz::query()->find(30);
        if (! $juz) {
            return;
        }

        $surahId = (int) $this->input('quran_surah_id');
        if ($surahId < (int) $juz->start_surah_id || $surahId > (int) $juz->end_surah_id) {
            $validator->errors()->add(
                'quran_surah_id',
                'Hari Jumat untuk jalur Alquran hanya setoran hafalan juz 30.',
            );
        }
    }
}
