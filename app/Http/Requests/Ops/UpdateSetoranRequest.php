<?php

namespace App\Http\Requests\Ops;

use App\Enums\SetoranCategory;
use App\Enums\SetoranStatus;
use App\Enums\SetoranSubtype;
use App\Models\QuranJuz;
use App\Models\QuranSurah;
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
        $subtype = SetoranSubtype::tryFrom((string) $this->input('subtype'));
        $needsQuran = in_array($subtype, [SetoranSubtype::Alquran, SetoranSubtype::Juz30], true);
        $needsIqro = $subtype === SetoranSubtype::Iqro;
        $needsDoa = $subtype === SetoranSubtype::Doa;

        return [
            'setoran_date' => ['required', 'date'],
            'category' => ['required', Rule::enum(SetoranCategory::class)],
            'subtype' => ['required', Rule::enum(SetoranSubtype::class)],
            'status' => ['required', Rule::enum(SetoranStatus::class)],
            'note' => ['nullable', 'string', 'max:500'],
            'correction_note' => ['required', 'string', 'max:500'],
            'quran_surah_id' => [$needsQuran ? 'required' : 'nullable', 'exists:quran_surahs,id'],
            'ayah_start' => [$needsQuran ? 'required' : 'nullable', 'integer', 'min:1'],
            'ayah_end' => [$needsQuran ? 'required' : 'nullable', 'integer', 'gte:ayah_start'],
            'iqro_level' => [$needsIqro ? 'required' : 'nullable', 'integer', 'min:1', 'max:6'],
            'iqro_page' => [$needsIqro ? 'required' : 'nullable', 'integer', 'min:1', 'max:100'],
            'doa_name' => [$needsDoa ? 'required' : 'nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $category = SetoranCategory::tryFrom((string) $this->input('category'));
            $subtype = SetoranSubtype::tryFrom((string) $this->input('subtype'));

            if ($category && $subtype && $subtype->category() !== $category) {
                $validator->errors()->add('subtype', 'Subtipe tidak cocok dengan jenis setoran.');
            }

            if (! in_array($subtype, [SetoranSubtype::Alquran, SetoranSubtype::Juz30], true)) {
                return;
            }

            $this->assertAyahWithinSurah($validator);

            if ($subtype === SetoranSubtype::Juz30) {
                $this->assertJuz30Surah($validator);
            }
        });
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

    protected function assertJuz30Surah(Validator $validator): void
    {
        $juz = QuranJuz::query()->find(30);
        if (! $juz) {
            return;
        }

        $surahId = (int) $this->input('quran_surah_id');
        if ($surahId < (int) $juz->start_surah_id || $surahId > (int) $juz->end_surah_id) {
            $validator->errors()->add(
                'quran_surah_id',
                'Hafalan Juz 30 hanya memakai surat An-Naba s.d. An-Nas.',
            );
        }
    }
}
