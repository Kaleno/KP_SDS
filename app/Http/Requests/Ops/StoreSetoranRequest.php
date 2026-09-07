<?php

namespace App\Http\Requests\Ops;

use App\Enums\SetoranStatus;
use App\Models\QuranSurah;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreSetoranRequest extends FormRequest
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
        return [
            'santri_id' => ['required', 'exists:santri_profiles,id'],
            'quran_surah_id' => ['required', 'exists:quran_surahs,id'],
            'setoran_date' => ['required', 'date'],
            'ayah_start' => ['required', 'integer', 'min:1'],
            'ayah_end' => ['required', 'integer', 'gte:ayah_start'],
            'status' => ['required', Rule::enum(SetoranStatus::class)],
            'note' => ['nullable', 'string', 'max:500'],
            'sesi' => ['nullable', 'integer', 'exists:attendance_sessions,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $this->assertAyahWithinSurah($validator);
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
}
